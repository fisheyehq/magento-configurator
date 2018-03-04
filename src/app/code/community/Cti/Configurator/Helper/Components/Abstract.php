<?php
abstract class Cti_Configurator_Helper_Components_Abstract extends Mage_Core_Helper_Abstract {

    protected $_componentName;
    protected $_data = [];
    protected $_filePaths = [];
    protected $_cliLog;

    public function __construct() {
        $this->_cliLog = false;
    }

    public function process() {

        try {

            if (is_null($this->_componentName)) {
                throw new Exception('Component name is not defined');
            }

            Mage::dispatchEvent('configurator_process_before',array('object'=>$this));
            Mage::dispatchEvent($this->_componentName.'_configurator_process_before',array('object'=>$this));

            foreach ($this->_filePaths as $filePath) {
                $this->_data = array_merge_recursive($this->_data, $this->_processFile($filePath));
            }
            $this->_processComponent($this->_data);

            Mage::dispatchEvent('configurator_process_after',array('object'=>$this));
            Mage::dispatchEvent($this->_componentName.'_configurator_process_after',array('object'=>$this));

        } catch (Exception $e) {

            Mage::logException($e);
        }

    }

    public function enableCliLog() {
        $this->_cliLog = true;
    }

    protected function log($msg,$nest = 0,$logLevel = 0) {
        if ($this->_cliLog) {
            for($i = 0; $i < $nest; $i++) {
                echo ' | ';
            }

            echo $msg .PHP_EOL;
        }
    }

    protected function _processFile($filePath) {

        // Check if file exists
        if (!file_exists($filePath)) {
            $this->log('No ' . $this->_componentName . ' component file found at: ' . $filePath . '. Skipping...');
            return [];
        }

        // Decode the YAML File
        $yaml = new Zend_Config_Yaml($filePath, NULL, ['ignore_constants' => true]);

        return $yaml->toArray();
    }

    abstract protected function _processComponent($data);

}