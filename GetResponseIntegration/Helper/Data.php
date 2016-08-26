<?php
/**
 * Created by PhpStorm.
 * User: mzubrzycki
 * Date: 16/12/15
 * Time: 09:48
 */

namespace GetResponse\GetResponseIntegration\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterfac
     */
    protected $_scopeConfig;

    CONST ENABLE    = 'getresponse_getresponseintegration/general/enable';
    CONST API_KEY   = 'getresponse_getresponseintegration/general/api_key';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);

        $this->_scopeConfig = $scopeConfig;
    }

    public function getEnable()
    {
        return $this->_scopeConfig->getValue(self::ENABLE);
    }

    public function getApiKey()
    {
        return $this->_scopeConfig->getValue(self::API_KEY);
    }
}