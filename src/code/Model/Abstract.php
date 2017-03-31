<?php

class Loewenstark_OrderAddMissingName_Model_Abstract
{
    /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function _writeConnection()
    {
        return $this->_getConnection('core_write');
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function _readConnection()
    {
        return $this->_getConnection('core_read');
    }

    /**
     * @param string $name
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getConnection($name)
    {
        return $this->_resource()->getConnection($name);
    }

    /**
     * 
     * @param string $modelEntity
     * @return string
     */
    protected function getTableName($modelEntity)
    {
        return $this->_resource()->getTableName($modelEntity);
    }

    /**
     * @return Mage_Core_Model_Resource
     */
    protected function _resource()
    {
        return Mage::getSingleton('core/resource');
    }
}