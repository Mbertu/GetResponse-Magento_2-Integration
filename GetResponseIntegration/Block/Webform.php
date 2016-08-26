<?php
namespace GetResponse\GetResponseIntegration\Block;



class Webform extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context, $data);

        $this->_objectManager = $objectManager;
    }

    public function getWebformSettings()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $webform_settings = $this->_objectManager->create('GetResponse\GetResponseIntegration\Model\Webform');
        return $webform_settings->load($storeId, 'id_shop')->getData();
    }
}
