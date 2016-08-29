<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class RegistrationPost extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (!empty($data)) {
            if (isset($data['gr_sync_order_data']) && isset($data['gr_custom_fields'])) {
                $customs = $data['gr_custom_fields'];

                foreach ($customs as $field => $name) {
                    if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', $name)) {
                        $this->messageManager->addErrorMessage('There is a problem with one of your custom field name! Field name
                        must be composed using up to 32 characters, only a-z (lower case), numbers and "_".');
                        $resultPage = $this->resultPageFactory->create();
                        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
                        $resultPage->getConfig()->getTitle()->prepend('Subscribe via registration page');

                        return $resultPage;
                    }
                }
            }
            $campaign_id = $data['campaign_id'];
            if (empty($campaign_id)) {
                $this->messageManager->addErrorMessage('You need to choose a campaign!');
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
                $resultPage->getConfig()->getTitle()->prepend('Subscribe via registration page');

                return $resultPage;
            }
            $enabled = $data['enabled'];
            $update = (isset($data['gr_sync_order_data'])) ? $data['gr_sync_order_data'] : 0;
            $cycle_day = (isset($data['gr_autoresponder']) && $data['gr_autoresponder'] == 1 && $data['cycle_day'] != '') ? $data['cycle_day'] : '';
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');

            $settings->load($storeId, 'id_shop')
                ->setCampaignId($campaign_id)
                ->setActiveSubscription($enabled)
                ->setUpdate($update)
                ->setCycleDay($cycle_day)
                ->save();

            if ($update == 1) {
                $customs = (isset($data['gr_custom_fields'])) ? $data['gr_custom_fields'] : array();
                $this->updateCustoms($customs);
            }

            $this->messageManager->addSuccessMessage('Subscription settings successfully saved.');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('Subscribe via registration page');

        return $resultPage;
    }

    public function updateCustoms($customs)
    {
        if (is_array($customs)) {
            $model = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Customs');
            $all_customs = $model->getCollection()->addFieldToFilter('default', false);
            foreach ($all_customs as $custom) {
                if (isset($customs[$custom->getCustomField()])) {
                    $custom->setCustomName($customs[$custom->getCustomField()])->setActiveCustom(1)->save();
                } else {
                    $custom->setActiveCustom('0')->save();
                }
            }
        }
    }
}