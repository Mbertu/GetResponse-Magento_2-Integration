<?php
/**
 * Created by PhpStorm.
 * User: mzubrzycki
 * Date: 16/12/15
 * Time: 09:27
 */

namespace GetResponse\GetResponseIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;

class SubscribeFromOrder implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $all_custom_fields;

    public $grApi;
    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Save order into registry to use it in the overloaded controller.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Settings');
        $settings = $block->getSettings();
        $automations = $block->getAutomations();

        if ($settings['active_subscription'] != true) {
            return $this;
        }

        $active_customs = $block->getActiveCustoms();

        $order_id = $observer->getOrderIds();
        $order_id = (int) array_pop($order_id);

        $order_object = $this->_objectManager->get('Magento\Sales\Model\Order');
        $order = $order_object->load($order_id);

        $customer_id = $order->getCustomerId();

        $customer_object = $this->_objectManager->get('Magento\Customer\Model\Customer');
        $customer = $customer_object->load($customer_id);

        $address_object = $this->_objectManager->get('Magento\Customer\Model\Address');
        $address = $address_object->load($customer->getDefaultBilling());
        if (empty($address->getData()['entity_id'])) {
            $address_object = $this->_objectManager->create('Magento\Customer\Model\Address');
            $address = $address_object->load($customer->getDefaultShipping());
        }

        $data = array_merge($address->getData(), $customer->getData());

        $custom_fields = [];
        foreach ($active_customs as $custom) {
            if ($custom['custom_field'] == 'birthday') {
                $custom['custom_field'] = 'dob';
            }
            if ($custom['custom_field'] == 'country') {
                $custom['custom_field'] = 'country_id';
            }
            if (!empty($data[$custom['custom_field']])) {
                $custom_fields[$custom['custom_name']] = $data[$custom['custom_field']];
            }
        }
        $custom_fields['ref'] = 'Magento2 GetResponse Integration Plugin';

        $subscriber = $this->_objectManager->create('Magento\Newsletter\Model\Subscriber');
        $subscriber->loadByEmail($customer->getEmail());

        if ($subscriber->isSubscribed() == true) {
            $api_key = $block->getApiKey();
            $this->grApi = new GetResponseAPI3($api_key);

            $this->all_custom_fields = $this->getCustomFields();

            $move_subscriber = false;

            if (!empty($automations)) {
                $category_ids = [];
                foreach ($automations as $a) {
                    if ($a['active'] == 1) {
                        $category_ids[$a['category_id']] = [
                            'category_id' => $a['category_id'],
                            'action' => $a['action'],
                            'campaign_id' => $a['campaign_id'],
                            'cycle_day' => $a['cycle_day']
                        ];
                    }
                }
                $automations_categories = array_keys($category_ids);

                foreach ($order->getItems() as $item) {
                    $product_id = $item->getData()['product_id'];
                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
                    $product_categories = $product->getCategoryIds();

                    if ($category = array_intersect($product_categories, $automations_categories)) {
                        foreach ($category as $c) {
                            if ($category_ids[$c]['action'] == 'move') {
                                $move_subscriber = true;
                            }
                            $response = $this->addContact($category_ids[$c]['campaign_id'], $customer->getFirstname(), $customer->getLastname(), $customer->getEmail(), $category_ids[$c]['cycle_day'], $custom_fields);
                        }
                    }
                }
                if ($move_subscriber) {
                    $results = (array) $this->grApi->getContacts(array(
                        'query' => array(
                            'email'      => $customer->getEmail(),
                            'campaignId' => $settings['campaign_id']
                        )
                    ));
                    $contact = array_pop($results);
                    if (!empty($contact) && isset($contact->contactId)) {
                        $this->grApi->deleteContact($contact->contactId);
                    }
                }
            }
            if (!$move_subscriber) {
                $response = $this->addContact($settings['campaign_id'], $customer->getFirstname(), $customer->getLastname(), $customer->getEmail(), $settings['cycle_day'], $custom_fields);
            }
        }

        return $this;
    }



    /**
     * Add (or update) contact to gr campaign
     *
     * @param       $campaign
     * @param       $firstname
     * @param       $lastname
     * @param       $email
     * @param int   $cycle_day
     * @param array $user_customs
     *
     * @return mixed
     */
    public function addContact($campaign, $firstname, $lastname, $email, $cycle_day = 0, $user_customs = array())
    {
        $name = trim($firstname) . ' ' . trim($lastname);

        $params = array(
            'name'       => $name,
            'email'      => $email,
            'campaign'   => array('campaignId' => $campaign),
            'ipAddress'  => $_SERVER['REMOTE_ADDR']
        );

        if (!empty($cycle_day)) {
            $params['dayOfCycle'] = (int) $cycle_day;
        }

        $results = (array) $this->grApi->getContacts(array(
            'query' => array(
                'email'      => $email,
                'campaignId' => $campaign
            )
        ));

        $contact = array_pop($results);

        // if contact already exists in gr account
        if (!empty($contact) && isset($contact->contactId)) {
            $results = $this->grApi->getContact($contact->contactId);
            if (!empty($results->customFieldValues)) {
                $params['customFieldValues'] = $this->mergeUserCustoms($results->customFieldValues, $user_customs);
            } else {
                $params['customFieldValues'] = $this->setCustoms($user_customs);
            }
            return $this->grApi->updateContact($contact->contactId, $params);
        } else {
            $params['customFieldValues'] = $this->setCustoms($user_customs);
            return $this->grApi->addContact($params);
        }
    }

    /**
     * Merge user custom fields selected on WP admin site with those from gr account
     * @param $results
     * @param $user_customs
     *
     * @return array
     */
    public function mergeUserCustoms($results, $user_customs)
    {
        $custom_fields = array();

        if (is_array($results)) {
            foreach ($results as $customs) {
                $value = $customs->value;
                if (in_array($customs->name, array_keys($user_customs))) {
                    $value = array($user_customs[$customs->name]);
                    unset($user_customs[$customs->name]);
                }

                $custom_fields[] = array(
                    'customFieldId' => $customs->customFieldId,
                    'value'         => $value
                );
            }
        }

        return array_merge($custom_fields, $this->setCustoms($user_customs));
    }

    /**
     * Set user custom fields
     * @param $user_customs
     *
     * @return array
     */
    public function setCustoms($user_customs)
    {
        $custom_fields = array();

        if (empty($user_customs)) {
            return $custom_fields;
        }

        foreach ($user_customs as $name => $value) {
            // if custom field is already created on gr account set new value
            if (in_array($name, array_keys($this->all_custom_fields))) {
                $custom_fields[] = array(
                    'customFieldId' => $this->all_custom_fields[$name],
                    'value'         => array($value)
                );
            } else {
                $custom = $this->grApi->addCustomField(array(
                    'name'   => $name,
                    'type'   => "text",
                    'hidden' => "false",
                    'values' => array($value),
                ));

                if (!empty($custom) && !empty($custom->customFieldId)) {
                    $custom_fields[] = array(
                        'customFieldId' => $custom->customFieldId,
                        'value'         => array($value)
                    );
                }
            }
        }

        return $custom_fields;
    }

    /**
     * Get all user custom fields from gr account
     * @return array
     */
    public function getCustomFields()
    {
        $all_customs = array();
        $results     = $this->grApi->getCustomFields();
        if (!empty($results)) {
            foreach ($results as $ac) {
                if (isset($ac->name) && isset($ac->customFieldId)) {
                    $all_customs[$ac->name] = $ac->customFieldId;
                }
            }
        }

        return $all_customs;
    }

}
