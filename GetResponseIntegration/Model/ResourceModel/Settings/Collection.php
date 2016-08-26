<?php
/**
 * Created by PhpStorm.
 * User: mzubrzycki
 * Date: 16/12/15
 * Time: 09:42
 */
namespace GetResponse\GetResponseIntegration\Model\ResourceModel\Settings;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('GetResponse\GetResponseIntegration\Model\Settings', 'GetResponse\GetResponseIntegration\Model\ResourceModel\Settings');
    }
}