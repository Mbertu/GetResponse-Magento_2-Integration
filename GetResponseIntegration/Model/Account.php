<?php
/**
 * Created by PhpStorm.
 * User: mzubrzycki
 * Date: 16/12/15
 * Time: 09:27
 */

namespace GetResponse\GetResponseIntegration\Model;

class Account extends \Magento\Framework\Model\AbstractModel {
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('GetResponse\GetResponseIntegration\Model\ResourceModel\Account', 'id');
    }
}