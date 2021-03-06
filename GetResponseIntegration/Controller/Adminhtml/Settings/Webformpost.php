<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Webformpost
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Webformpost extends Action
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
            $publish = isset($data['publish']) ? $data['publish'] : 0;
            $webform_id = isset($data['webform_id']) ? $data['webform_id'] : null;
            $webform_url = isset($data['webform_url']) ? $data['webform_url'] : null;
            $sidebar = isset($data['sidebar']) ? $data['sidebar'] : null;
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