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

class SubscribeFromRegister implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Registry $coreRegistry
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

        if ($settings['active_subscription'] != true) {
            return $this;
        }
        $api_key = $block->getApiKey();
        $customer = $observer->getEvent()->getCustomer();

        $subscriber = $this->_objectManager->create('Magento\Newsletter\Model\Subscriber');
        $subscriber->loadByEmail($customer->getEmail());

        if ($subscriber->isSubscribed() == true) {
            $params = [];
            $params['campaign'] = ['campaignId' => $settings['campaign_id']];
            $params['name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
            $params['email'] = $customer->getEmail();

            if (!empty($settings['cycle_day'])) {
                $params['dayOfCycle'] = $settings['cycle_day'];
            }

            $client = new GetResponseAPI3($api_key);

            $response = $client->addContact($params);
        }

        return $this;
    }
}
