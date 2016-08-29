<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Webformpost
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Webformpost extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    /** @var GetResponseAPI3 */
    public $grApi;

    /**
     * Webformpost constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if (!empty($data)) {
            $publish = $data['publish'];
            $webform_id = $data['webform_id'];
            $webform_url = $data['webform_url'];
            $sidebar = $data['sidebar'];
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            $webform = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');

            $webform->load($storeId, 'id_shop')
                ->setIdShop($storeId)
                ->setActiveSubscription($publish)
                ->setUrl($webform_url)
                ->setWebformId($webform_id)
                ->setSidebar($sidebar)
                ->save();

            $this->messageManager->addSuccessMessage('Subscription settings successfully saved.');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('Subscribe via a form');

        return $resultPage;
    }
}