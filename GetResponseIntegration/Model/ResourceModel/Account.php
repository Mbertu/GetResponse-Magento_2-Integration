<?php
/**
 * Created by PhpStorm.
 * User: mzubrzycki
 * Date: 16/12/15
 * Time: 09:41
 */
namespace GetResponse\GetResponseIntegration\Model\ResourceModel;

class Account extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
    protected function _construct()
    {
        $this->_init('getresponse_account', 'id');
    }
}