<?php
$installer = $this;
/* @var MageProfis_Customer_Model_Resource_Setup $installer */

$installer->startSetup();

$installer->getConnection()
->addColumn($installer->getTable('sales/order'), 'check_missing_names', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    //'nullable'  => false,
    'default'   => 0,
    'length'    => 2,
    'after'     => null, // column name to insert new column after
    'comment'   => 'Checked if names are missing?'
));   

$installer->endSetup();
