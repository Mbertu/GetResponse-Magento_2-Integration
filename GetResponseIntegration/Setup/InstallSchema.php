<?php
/**
 * Created by PhpStorm.
 * User: mzubrzycki
 * Date: 16/12/15
 * Time: 09:23
 */

namespace GetResponse\GetResponseIntegration\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table = $installer->getConnection()
        ->newTable($installer->getTable('getresponse_settings'))
        ->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )
        ->addColumn(
            'id_shop',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Id shop'
        )
        ->addColumn(
            'api_key',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'GR Api Key'
        )
        ->addColumn(
            'active_subscription',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['default' => true, 'nullable' => false],
            'Active subscription'
        )
        ->addColumn(
            'update',
            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
            null,
            ['default' => true, 'nullable' => false],
            'Update custom fields'
        )
        ->addColumn(
            'cycle_day',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'GR campaign cycle day'
        )
        ->addColumn(
            'campaign_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'GR campaign id'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('getresponse_account'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'id_shop',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Id shop'
            )
            ->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Id'
            )
            ->addColumn(
                'first_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            )
            ->addColumn(
                'last_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Last name'
            )
            ->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Email'
            )
            ->addColumn(
                'company_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Company name'
            )
            ->addColumn(
                'phone',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Phone'
            )
            ->addColumn(
                'state',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            )
            ->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            )
            ->addColumn(
                'street',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            )
            ->addColumn(
                'zip_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            )
            ->addColumn(
                'country_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('getresponse_customs'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'id_shop',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Id shop'
            )
            ->addColumn(
                'custom_field',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Custom field'
            )
            ->addColumn(
                'custom_value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Custom field value'
            )
            ->addColumn(
                'custom_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Custom field name'
            )
            ->addColumn(
                'default',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['default' => true, 'nullable' => false],
                'default field'
            )
            ->addColumn(
                'active_custom',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['default' => true, 'nullable' => false],
                'Active custom field'
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('getresponse_webform'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'id_shop',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Id shop'
            )
            ->addColumn(
                'webform_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Webform id'
            )
            ->addColumn(
                'active_subscription',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Active subscription flag'
            )
            ->addColumn(
                'sidebar',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Sidebar name'
            )
            ->addColumn(
                'style',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['default' => true, 'nullable' => false],
                'Webform style'
            )
            ->addColumn(
                'url',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Url to webform'
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('getresponse_automation'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'id_shop',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Id shop'
            )
            ->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Category id'
            )
            ->addColumn(
                'campaign_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Campaign id'
            )
            ->addColumn(
                'action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Action type'
            )
            ->addColumn(
                'cycle_day',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'GR campaign cycle day'
            )
            ->addColumn(
                'active',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'active flag'
            );
        $installer->getConnection()->createTable($table);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $stores = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStores();
        foreach ($stores as $store) {
            $columns = ['id', 'id_shop', 'custom_field', 'custom_value', 'custom_name', 'default', 'active_custom'];
            $installer->getConnection()->insertArray('getresponse_customs', $columns, [1, $store->getId(), 'firstname', 'firstname', 'firstname', 1, 1]);
            $installer->getConnection()->insertArray('getresponse_customs', $columns, [2, $store->getId(), 'lastname', 'lastname', 'lastname', 1, 1]);
            $installer->getConnection()->insertArray('getresponse_customs', $columns, [3, $store->getId(), 'email', 'email', 'email', 1, 0]);
            $installer->getConnection()->insertArray('getresponse_customs', $columns, [4, $store->getId(), 'street', 'street', 'magento_street', 0, 0]);
            $installer->getConnection()->insertArray('getresponse_customs', $columns, [9, $store->getId(), 'birthday', 'birthday', 'magento_birthday', 0, 0]);
            $installer->getConnection()->insertArray('getresponse_customs', $columns, [5, $store->getId(), 'postcode', 'postcode', 'magento_postcode', 0, 0]);
            $installer->getConnection()->insertArray('getresponse_customs', $columns, [6, $store->getId(), 'city', 'city', 'magento_city', 0, 0]);
            $installer->getConnection()->insertArray('getresponse_customs', $columns, [7, $store->getId(), 'telephone', 'telephone', 'magento_phone', 0, 0]);
            $installer->getConnection()->insertArray('getresponse_customs', $columns, [8, $store->getId(), 'country', 'country', 'magento_country', 0, 0]);
        }

        $installer->endSetup();
    }
}