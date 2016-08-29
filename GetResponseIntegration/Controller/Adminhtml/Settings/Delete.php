<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;

class Delete extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();

        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        $settings->load($storeId, 'id_shop')->delete();

        $account = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Account');
        $account->load($storeId, 'id_shop')->delete();

        $webform = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');
        $webform->load($storeId, 'id_shop')->delete();

        $automation = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Automation');
        $automations = $automation->getCollection()->addFieldToFilter('id_shop', $storeId);
        foreach ($automations as $automation) {
            $automation->delete();
        }

        $this->messageManager->addSuccessMessage('You disconnected your Magento from GetResponse.');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('GetResponse Account');

        return $resultPage;
    }
}