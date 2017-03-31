<?php

class Loewenstark_OrderAddMissingName_Model_Resource_Setup
extends Mage_Catalog_Model_Resource_Setup
{
    /**
     *
     * @return MageProfis_Customer_Model_Resource_Setup
     */
    public function startSetup()
    {
        parent::startSetup();
        return $this;
    }

    /**
     *
     * @param string $title
     * @param string $identifier
     * @param string $content
     * @param bool   $is_active
     * @param array $stores
     *
     * @return boolean
     */
    public function addStaticBlock($title, $identifier, $content, $is_active = 1, $stores = null)
    {
        if (is_null($stores)) {
            $stores = array(0);
        } elseif (!is_array($stores)) {
            $stores = array((int)$stores);
        }

        $identifier = trim($identifier);
        $collection = Mage::getModel('cms/block')->getCollection()
                ->addFieldToFilter('identifier', $identifier)
                ->addStoreFilter($stores);

        $data = array(
            'title' => $title,
            'identifier' => $identifier,
            'content' => $content,
            'is_active' => $is_active,
            'stores' => $stores
        );

        if (!$collection->count()) {
            Mage::getModel('cms/block')
                    ->setData($data)
                    ->save();

            return true;
        }

        return false;
    }

    /**
     *
     * @param string $identifier
     * @return MageProfis_Customer_Model_Resource_Setup
     */
    public function removeStaticBlock($identifier)
    {
        $identifier = trim($identifier);
        $collection = Mage::getModel('cms/block')->getCollection()
                ->addFieldToFilter('identifier', $identifier);
        if ($collection->count()) {
            foreach ($collection as $item) {
                $item->delete();
            }
        }
        return $this;
    }

    /**
     *
     * @param string $identifier
     * @return MageProfis_Customer_Model_Resource_Setup
     */
    public function removeCmsPage($identifier)
    {
        $identifier = trim($identifier);
        $collection = Mage::getModel('cms/page')->getCollection()
                ->addFieldToFilter('identifier', $identifier);
        if ($collection->count()) {
            foreach ($collection as $item) {
                $item->delete();
            }
        }
        return $this;
    }

    /**
     *
     * @param string $path
     * @return MageProfis_Customer_Model_Resource_Setup
     */
    public function removeConfigData($path)
    {
        $where = $this->getConnection()->quoteInto('path = ?', $path);
        $this->getConnection()->delete($this->getTable('core/config_data'), $where);
        return $this;
    }

    /**
     * Save configuration data
     * @tutorial added comparison feature before save
     *
     * @param string $path
     * @param string $value
     * @param int|string $scope
     * @param int $scopeId
     * @param int $inherit
     *
     * @return MageProfis_Customer_Model_Resource_Setup
     */
    public function setConfigData($path, $value, $scope = 'default', $scopeId = 0, $inherit = 0)
    {
        if(strcmp($this->getConfigData($path, $scopeId), $value) === 0) {
            return $this;
        }
        return parent::setConfigData($path, $value, $scope, $scopeId, $inherit);
    }


    /**
     * get Configurations directly from the Database
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     *
     * @return string
     */
    public function getConfigData($path, $scopeId = 0)
    {
        $query = $this->getConnection()->select()
            ->from($this->getTable('core/config_data'), 'value')
            ->where('path = ?', $path)
            ->where('scope_id = ?', intval($scopeId))
            ->limit(1);
        $count = clone $query;
        $count->reset(Zend_Db_Select::COLUMNS);
        $count->columns('COUNT(*)');
        if(intval($this->getConnection()->fetchOne($count)) > 0) {
            return $this->getConnection()->fetchOne($query);
        }
        return Mage::getStoreConfig($path, $scopeId); // returns values from Module config.xml / local.xml
    }

    /**
     * Boolean alias of
     *
     * @param string $path
     * @param int $scopeId
     * @return bool
     */
    public function getConfigDataFlag($path, $scopeId = 0)
    {
        return (bool) intval($this->getConfigData($path, $scopeId));
    }


    public function importCategoriesFromCsv()
    {
        // @FIXME: Implement function
    }

    /**
     * Prepare database after install/upgrade
     * added config reinit to endSetup
     *
     * @return MageProfis_Customer_Model_Resource_Setup
     */
    public function endSetup()
    {
        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
        return parent::endSetup();
    }
}