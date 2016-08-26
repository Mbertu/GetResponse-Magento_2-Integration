<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Settings');
        $checkApiKey = $block->checkApiKey();
        if ($checkApiKey === false) {
            $this->messageManager->addWarning('Your API key is not valid! Please update your settings.');
        } elseif ($checkApiKey === 0) {
            $this->messageManager->addWarning('Your API key is empty. Please save your API key');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('GetResponse integration settings');

        return $resultPage;
    }

}