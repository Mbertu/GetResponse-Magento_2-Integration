<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class AutomationPost extends \Magento\Backend\App\Action
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
            // Toggling status
            if (isset($data['toggle_status'])) {
                $automation_id = (empty($data['automation_id'])) ? '' : $data['automation_id'];
                $status = ($data['toggle_status'] == 'true') ? 1 : 0;
                $automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');
                $automation->load($automation_id)
                    ->setActive($status)
                    ->save();

                $automation_status = $automation->load($automation_id)->getActive();

                if ($automation_status == $status) {
                    echo json_encode(array('success' => 'true', 'msg' => 'Status successfully changed!'));
                    die;
                } else {
                    echo json_encode(array('success' => 'false', 'msg' => 'Something went wrong!'));
                    die;
                }
            }

            // Deleting automation
            if (isset($data['delete_automation'])) {
                $automation_id = $data['automation_id'];
                $automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');
                $automation->load($automation_id)->delete();
                echo json_encode(array('success' => 'true', 'msg' => 'Automation successfully deleted!'));
                die;
            }

            $campaign_id = (empty($data['campaign_id'])) ? '' : $data['campaign_id'];
            $category_id = (empty($data['category'])) ? '' : $data['category'];
            $action = (empty($data['action'])) ? '' : $data['action'];
            $cycle_day = (isset($data['gr_autoresponder']) && $data['gr_autoresponder'] == 1 && $data['cycle_day'] != '') ? $data['cycle_day'] : '';

            //editing
            if (isset($data['edit_automation'])) {
                $campaign_id = (empty($data['campaign_id_edit'])) ? '' : $data['campaign_id_edit'];
                $cycle_day = (isset($data['cycle_day_edit']) && $data['cycle_day_edit'] != '') ? $data['cycle_day_edit'] : '';
                $automation_id = (empty($data['automation_id'])) ? '' : $data['automation_id'];
                $automation = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Automation');
                $automation->load($automation_id)
                    ->setCategoryId($category_id)
                    ->setCampaignId($campaign_id)
                    ->setCycleDay($cycle_day)
                    ->setAction($action)
                    ->save();

                $this->messageManager->addSuccess('Campaign rules have been changed');
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
                $resultPage->getConfig()->getTitle()->prepend('Campaign rules');

                return $resultPage;
            }

            if (empty($campaign_id) || empty($category_id)) {
                $this->messageManager->addError('You need to choose a campaign and category!');
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
                $resultPage->getConfig()->getTitle()->prepend('Campaign rules');

                return $resultPage;
            }
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            $automation = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Automation');

            $automations_count = $automation->getCollection()
                ->addFieldToFilter('id_shop', $storeId)
                ->addFieldToFilter('category_id', $category_id);

            if (count($automations_count) > 0) {
                $this->messageManager->addError('Automation has not been created. Rule for chosen category already exist.');
                $resultPage = $this->resultPageFactory->create();
                $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
                $resultPage->getConfig()->getTitle()->prepend('Campaign rules');

                return $resultPage;
            }

            $automation->setIdShop($storeId)
                ->setCategoryId($category_id)
                ->setCampaignId($campaign_id)
                ->setActive(1)
                ->setCycleDay($cycle_day)
                ->setAction($action)
                ->save();

            $this->messageManager->addSuccess('New automation rule has been created!');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('Campaign rules');

        return $resultPage;
    }
}