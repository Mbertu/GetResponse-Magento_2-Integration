<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Symfony\Component\Config\Definition\Exception\Exception;

class Process extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $all_custom_fields;

    public $grApi;

    public $stats = [
        'count'      => 0,
        'added'      => 0,
        'updated'    => 0,
        'error'      => 0
    ];

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Settings');
        $data = $this->getRequest()->getPostValue();

        $campaign = $data['campaign_id'];
        if (empty($campaign)) {
            $this->messageManager->addError('You need to choose a campaign!');
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::export');
            $resultPage->getConfig()->getTitle()->prepend('Export customer data on demand');

            return $resultPage;
        }

        if (isset($data['gr_sync_order_data']) && isset($data['gr_custom_fields'])) {
            $customs = $data['gr_custom_fields'];

            foreach ($customs as $field => $name) {
                if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', $name)) {
                    $this->messageManager->addError('There is a problem with one of your custom field name! Field name
                    must be composed using up to 32 characters, only a-z (lower case), numbers and "_".');
                    $resultPage = $this->resultPageFactory->create();
                    $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::export');
                    $resultPage->getConfig()->getTitle()->prepend('Export customer data on demand');

                    return $resultPage;
                }
            }
        } else {
            $customs = [];
        }

        $customers = $block->getCustomers();

        $api_key = $block->getApiKey();
        $this->grApi = new GetResponseAPI3($api_key);

        $this->all_custom_fields = $this->getCustomFields();

        foreach ($customers as $customer) {
            $customer = $customer->getData();
            $this->stats['count']++;
            $custom_fields = [];
            foreach ($customs as $field => $name) {
                if (!empty($customer[$field])) {
                    $custom_fields[$name] = $customer[$field];
                }
            }
            $custom_fields['ref'] = 'Magento2 GetResponse Integration Plugin';

            if ($data['gr_autoresponder'] == 1 && $data['cycle_day'] != '') {
                $cycle_day = (int) $data['cycle_day'];
            } else {
                $cycle_day = null;
            }

            $response = $this->addContact($campaign, $customer['firstname'], $customer['lastname'], $customer['email'], $cycle_day, $custom_fields);
        }

        $this->messageManager->addSuccess(
            'Contacts export process has completed (' .
            'created: ' . $this->stats['added'] .
            ', updated: ' . $this->stats['updated'] .
            ', not added: ' . $this->stats['error'] . ')'
        );

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::export');
        $resultPage->getConfig()->getTitle()->prepend('Export customer data on demand');

        return $resultPage;
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
            $response = $this->grApi->updateContact($contact->contactId, $params);
            if (isset($response->message)) {
                $this->stats['error']++;
            } else {
                $this->stats['updated']++;
            }
            return $response;
        } else {
            $params['customFieldValues'] = $this->setCustoms($user_customs);
            $response = $this->grApi->addContact($params);
            if (isset($response->message)) {
                $this->stats['error']++;
            } else {
                $this->stats['added']++;
            }
            return $response;
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