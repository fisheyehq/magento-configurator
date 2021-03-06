<?php

class Cti_Configurator_Helper_Components_Sql extends Cti_Configurator_Helper_Components_Abstract
{
    protected $_componentName = 'sql';

    protected function _processComponent($data)
    {
        if (!isset($data['sql'])) {
            return;
        }

        foreach ($data['sql'] as $file) {
            try {
                $path = Mage::getBaseDir().$file;
                if (!file_exists($path)) {
                    throw new Exception($file.' does not exist for SQL execution');
                }

                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $query = file_get_contents($path);
                $writeConnection->query($query);

                $this->log($this->__("Executed sql script %s",$file));

            } catch (Exception $e) {
                $this->log($this->__("Error executing script %s: %s",$file,$e->getMessage()));
                throw new Exception($e->getMessage());
            }
        }
    }
}
