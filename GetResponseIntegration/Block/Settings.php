<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;

/**
 * Class Settings
 * @package GetResponse\GetResponseIntegration\Block
 */
class Settings extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Settings constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);

        $this->_objectManager = $objectManager;
    }

    /**
     * @return mixed
     */
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

    /**
     * @return mixed
     */
    public function getSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        return $settings->load($storeId, 'id_shop')->getData();
    }

    /**
     * @return mixed
     */
    public function getWebformSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $webform_settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');
        return $webform_settings->load($storeId, 'id_shop')->getData();
    }

    /**
     * @return mixed
     */
    public function getAccountInfo()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $account = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Account');
        return $account->load($storeId, 'id_shop');
    }

    /**
     * @return array
     */
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

    /**
     * @return mixed
     */
    public function getActiveCustoms()
    {
        $customs = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Customs');
        return $customs->getCollection()->addFieldToFilter('active_custom', true);
    }

    /**
     * @return bool
     */
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

    /**
     * @return int
     */
    public function getLastPostedApiAccount()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['getresponse_360_account']) && 1 == $data['getresponse_360_account']) {
            return $data['getresponse_360_account'];
        }
        return 0;
    }

    /**
     * @return bool
     */
    public function getLastPostedApiUrl()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['getresponse_api_url'])) {
            return $data['getresponse_api_url'];
        }
        return false;
    }

    /**
     * @return bool
     */
    public function getLastPostedApiDomain()
    {
        $data = $this->getRequest()->getPostValue();
        if (!empty($data['getresponse_api_domain'])) {
            return $data['getresponse_api_domain'];
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getAutomations()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Automation');
        return $settings->getCollection()
            ->addFieldToFilter('id_shop', $storeId);
    }

    /**
     * @return bool|int
     */
    public function checkApiKey()
    {
        if (empty($this->getApiKey())) {
            return 0;
        }

        $response = $this->getClient()->ping();

        if (isset($response->accountId)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $model = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        return $model->load($storeId, 'id_shop')->getApiKey();
    }

    /**
     * @return GetResponseAPI3
     */
    public function getClient()
    {
        $settings = $this->getSettings();
        return new GetResponseAPI3($settings['api_key'], $settings['api_url'], $settings['api_domain']);
    }
}
