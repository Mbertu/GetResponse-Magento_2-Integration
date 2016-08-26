<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Helper\GetResponseAPI3;

class Export extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\HelloWorld\Helper\Data
     */
    protected $helper;

    public $stats;

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
        return $customers->getCollection();
    }

    public function getActiveCustoms()
    {
        $customs = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Customs');
        return $customs->getCollection()->addFieldToFilter('active_custom', true);
    }

    public function getDefaultCustoms()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $customs = $this->_objectManager->get('GetResponse\GetResponseIntegration\Model\Customs');
        return $customs->getCollection($storeId, 'id_shop');
    }

    public function getCampaigns()
    {
        $client = new GetResponseAPI3($this->getApiKey());
        return $client->getCampaigns();
    }

    public function getCampaign($id)
    {
        $client = new GetResponseAPI3($this->getApiKey());
        return $client->getCampaign($id);
    }

    public function getAccountFromFields()
    {
        $client = new GetResponseAPI3($this->getApiKey());
        return $client->getAccountFromFields();
    }

    public function getSubscriptionConfirmationsSubject($lang)
    {
        $client = new GetResponseAPI3($this->getApiKey());
        return $client->getSubscriptionConfirmationsSubject($lang);
    }

    public function getSubscriptionConfirmationsBody($lang)
    {
        $client = new GetResponseAPI3($this->getApiKey());
        return $client->getSubscriptionConfirmationsBody($lang);
    }

    public function getSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Settings');
        return $settings->load($storeId, 'id_shop')->getData();
    }

    public function getAutomations()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Automation');
        return $settings->getCollection()
            ->addFieldToFilter('id_shop', $storeId);
    }

    public function getCategoryName($category_id)
    {
        $_categoryHelper = $this->_objectManager->get('\Magento\Catalog\Model\Category');
        return $_categoryHelper->load($category_id)->getName();
    }

    public function getStoreCategories()
    {
        $_categoryHelper = $this->_objectManager->get('\Magento\Catalog\Helper\Category');
        $categories = $_categoryHelper->getStoreCategories(true, false, true);

//        $categories = $this->_objectManager->get('Magento\Catalog\Model\Category');
//        $categories = $categories->getCollection()
//            ->joinAttribute('name','catalog_category/name','entity_id',null,'left');

        return $categories;
    }

    /**
     * @param $category \Magento\Catalog\Helper\Category|\Magento\Catalog\Model\Category
     */
    public function getSubcategories($category)
    {
        if ($category->hasChildren()) {
            $childrenCategories = $category->getChildren();
            foreach ($childrenCategories as $childrenCategory) {
                $string = '';
                for ($i = $childrenCategory->getLevel(); $i > 2; $i--) {
                    $string .= '-';
                }
                echo '<option value="' . $childrenCategory->getEntityId() . '"> ' .
                    $string . ' ' . $childrenCategory->getName() . '</option>';
                $this->getSubcategories($childrenCategory);
            }
        }
    }

    public function getAutoresponders()
    {
        $api_key = $api_key = $this->getApiKey();
        $params = array('query' => array('triggerType' => 'onday', 'status' => 'active'));
        $client = new GetResponseAPI3($api_key);
        $result = $client->getAutoresponders($params);
        $autoresponders = [];

        if (!empty($result)) {
            foreach ($result as $autoresponder) {
                if (isset($autoresponder->triggerSettings->selectedCampaigns[0])) {
                    $autoresponders[$autoresponder->triggerSettings->selectedCampaigns[0]][] = [
                        'name' => $autoresponder->name,
                        'subject' => $autoresponder->subject,
                        'dayOfCycle' => $autoresponder->triggerSettings->dayOfCycle
                    ];
                }
            }
        }

        return $autoresponders;
    }

    public function getStoreLanguage()
    {
        return $this->_scopeConfig->getValue('general/locale/code');
    }

    public function checkApiKey()
    {
        $api_key = $this->getApiKey();
        $client = new GetResponseAPI3($api_key);
        if (empty($api_key)) {
            return 0;
        }
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
