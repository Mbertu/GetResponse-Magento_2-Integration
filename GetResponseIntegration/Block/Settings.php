<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;

class Settings extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\HelloWorld\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);

        $this->_objectManager = $objectManager;
    }

    public function getCustomers()
    {
        $customers = $this->_objectManager->get('Magento\Customer\Model\Customer');
        $customers = $customers->getCollection()
                               ->joinAttribute('street', 'customer_address/street', 'default_billing', null, 'left')
                               ->joinAttribute('postcode', 'customer_address/postcode', 'default_billing', null, 'left')
                               ->joinAttribute('city', 'customer_address/city', 'default_billing', null, 'left')
                               ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left')
                               ->joinAttribute('country', 'customer_address/country_id', 'default_billing', null, 'left')
                               ->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left')
                               ->joinAttribute('birthday', 'customer/dob', 'entity_id', null, 'left');
        return $customers;
    }

    public function getSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        return $settings->load($storeId, 'id_shop')->getData();
    }

    public function getWebformSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $webform_settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');
        return $webform_settings->load($storeId, 'id_shop')->getData();
    }

    public function getAccountInfo()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $account = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Account');
        return $account->load($storeId, 'id_shop');
    }

    public function getAllFormsFromGr()
    {
        $settings = $this->getSettings();
        $forms = [];

        $client = new GetResponseAPI3($settings['api_key'], $settings['api_url'], $settings['api_domain']);

        $newForms = $client->getForms(array('query' => array('status' => 'enabled')));
        foreach ($newForms as $form) {
            if ($form->status == 'published') {
                $forms['forms'][] = $form;
            }
        }
        $oldWebforms = $client->getWebForms();
        foreach ($oldWebforms as $webform) {
            if ($webform->status == 'enabled') {
                $forms['webforms'][] = $webform;
            }
        }

        return $forms;
    }

    public function getActiveCustoms()
    {
        $customs = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Customs');
        return $customs->getCollection()->addFieldToFilter('active_custom', true);
    }

    public function getLastPostedApiKey()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data)) {
            if (isset($data['getresponse_api_key'])) {
                return $data['getresponse_api_key'];
            }
        }
        return false;
    }

    public function getLastPostedApiAccount()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['getresponse_360_account']) && 1 == $data['getresponse_360_account']) {
            return $data['getresponse_360_account'];
        }
        return 0;
    }

    public function getLastPostedApiUrl()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['getresponse_api_url'])) {
            return $data['getresponse_api_url'];
        }
        return false;
    }

    public function getLastPostedApiDomain()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['getresponse_api_domain'])) {
            return $data['getresponse_api_domain'];
        }
        return false;
    }

    public function getAutomations()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Automation');
        return $settings->getCollection()
            ->addFieldToFilter('id_shop', $storeId);
    }

    public function checkApiKey()
    {
        $settings = $this->getSettings();

        if (empty($settings['api_key'])) {
            return 0;
        }

        $client = new GetResponseAPI3($settings['api_key'], $settings['api_url'], $settings['api_domain']);
        $response = $client->ping();

        if (isset($response->accountId)) {
            return true;
        } else {
            return false;
        }
    }

    public function getApiKey()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $model = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        return $model->load($storeId, 'id_shop')->getApiKey();
    }
}
