<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;

/**
 * Class Save
 * @package GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings
 */
class Save extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Save constructor.
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
        $apiErrorMsg = 'The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one.';

        $data = $this->getRequest()->getPostValue();
        if (!empty($data)) {
            if (!empty($data['getresponse_api_key'])) {

                $api_key = $data['getresponse_api_key'];
                $api_url = null;
                $api_domain = null;

                if (isset($data['getresponse_360_account']) && 1 == $data['getresponse_360_account']) {
                    $api_url = !empty($data['getresponse_api_url']) ? $data['getresponse_api_url'] : null;
                    $api_domain = !empty($data['getresponse_api_domain']) ? $data['getresponse_api_domain'] : null;
                }

                $client = new GetResponseAPI3($api_key, $api_url, $api_domain);
                $response = $client->ping();

                if (isset($response->accountId)) {
                    $this->storeData($response, $api_key, $api_url, $api_domain);
                    $this->messageManager->addSuccessMessage('You connected your Magento to GetResponse.');
                } else {
                    $this->messageManager->addErrorMessage($apiErrorMsg);
                }
            } else {
                $this->messageManager->addErrorMessage($apiErrorMsg);
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('GetResponse integration settings');

        return $resultPage;
    }

    /**
     * @param object $response
     * @param string $api_key
     * @param string $api_url
     * @param string $api_domain
     */
    private function storeData($response, $api_key, $api_url = null, $api_domain = null)
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');

        $settings->load($storeId, 'id_shop')
            ->setApiKey($api_key)
            ->setApiUrl($api_url)
            ->setApiDomain($api_domain)
            ->setIdShop($storeId)
            ->save();

        $account = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Account');

        $account->load($storeId, 'id_shop')
            ->setIdShop($storeId)
            ->setAccountId($response->accountId)
            ->setFirstName($response->firstName)
            ->setLastName($response->lastName)
            ->setEmail($response->email)
            ->setCompanyName($response->companyName)
            ->setPhone($response->phone)
            ->setState($response->state)
            ->setCity($response->city)
            ->setStreet($response->street)
            ->setZipCode($response->zipCode)
            ->setCountryCode($response->countryCode->countryCode)
            ->save();
    }

}