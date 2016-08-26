<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public $grApi;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $block = $this->_objectManager->create('GetResponse\GetResponseIntegration\Block\Export');
        $checkApiKey = $block->checkApiKey();
        if ($checkApiKey === false) {
            $this->messageManager->addWarning('Your API key is not valid! Please update your settings.');
        } elseif ($checkApiKey === 0) {
            $this->messageManager->addWarning('Your API key is empty. In order to use this function you need to save your API key');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::export');
        $resultPage->getConfig()->getTitle()->prepend('Export customers to GetResponse');

        return $resultPage;
    }
}