<?php

namespace GetResponse\GetResponseIntegration\Helper;

/**
 * Class Data
 * @package GetResponse\GetResponseIntegration\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterfac
     */
    protected $_scopeConfig;

    CONST ENABLE    = 'getresponse_getresponseintegration/general/enable';
    CONST API_KEY   = 'getresponse_getresponseintegration/general/api_key';

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);

        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @return mixed
     */
    public function getEnable()
    {
        return $this->_scopeConfig->getValue(self::ENABLE);
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->_scopeConfig->getValue(self::API_KEY);
    }
}