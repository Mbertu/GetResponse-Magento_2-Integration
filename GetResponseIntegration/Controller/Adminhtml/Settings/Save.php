<?php
namespace GetResponse\GetResponseIntegration\Controller\Adminhtml\Settings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;

class Save extends \Magento\Backend\App\Action
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
            if (!empty($data['getresponse_api_key'])) {
                $api_key = $data['getresponse_api_key'];
                $client = new GetResponseAPI3($api_key);
                $response = $client->ping();

                if (isset($response->accountId)) {
                    $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
                    $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');

                    $settings->load($storeId, 'id_shop')
                        ->setApiKey($api_key)
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

                    $this->messageManager->addSuccess('You connected your Magento to GetResponse.');
                } else {
                    $this->messageManager->addError('The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one.');
                }
            } else {
                $this->messageManager->addError('The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one.');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GetResponse_GetResponseIntegration::settings');
        $resultPage->getConfig()->getTitle()->prepend('GetResponse Account');

        return $resultPage;
    }

}