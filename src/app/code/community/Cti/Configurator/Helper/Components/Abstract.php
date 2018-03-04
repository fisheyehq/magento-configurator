<?php

abstract class Cti_Configurator_Helper_Components_Abstract extends Mage_Core_Helper_Abstract
{
    protected $_cliLog;
    protected $_componentName;
    protected $_data = [];
    protected $_environment;
    protected $_filePaths = [];

    public function __construct()
    {
        $this->_cliLog = false;
    }

    public function process($environment)
    {
        $this->_environment = $environment;
        $this->_setFileSources();

        try {
            if (is_null($this->_componentName)) {
                throw new Exception('Component name is not defined');
            }

            Mage::dispatchEvent('configurator_process_before',array('object'=>$this));
            Mage::dispatchEvent($this->_componentName.'_configurator_process_before',array('object'=>$this));

            foreach ($this->_filePaths as $filePath) {
                $this->_data = array_merge_recursive($this->_data, $this->_processFile($filePath));
            }

            if (empty($this->_filePaths)) {
                $this->log($this->__('No configuration files found for ' . $this->_componentName . '. Skipping...'));
                return;
            }
            $this->_processComponent($this->_data);

            Mage::dispatchEvent('configurator_process_after',array('object'=>$this));
            Mage::dispatchEvent($this->_componentName.'_configurator_process_after',array('object'=>$this));

        } catch (Exception $e) {
            Mage::logException($e);
        }

    }

    public function enableCliLog()
    {
        $this->_cliLog = true;
    }

    protected function log($msg,$nest = 0,$logLevel = 0)
    {
        if ($this->_cliLog) {
            for($i = 0; $i < $nest; $i++) {
                echo ' | ';
            }

            echo $msg .PHP_EOL;
        }
    }

    private function _getMasterYaml()
    {
        $masterYaml = new Zend_Config_Yaml(
            Mage::getBaseDir() . DS . 'app' . DS . 'etc' . DS . 'master.yaml',
            null,
            ['ignore_constants' => true]
        );
        return $masterYaml->toArray();
    }

    private function _setFileSources()
    {
        $masterYaml = $this->_getMasterYaml();

        if (!array_key_exists($this->_componentName, $masterYaml)) {
            return;
        }
        $section = $masterYaml[$this->_componentName];

        if (array_key_exists('sources', $section)) {
            $this->_filePaths = array_merge($this->_filePaths, $section['sources']);
        }

        if (!array_key_exists('env', $section)) {
            $this->_logEnvironmentFileNotFound();
            return;
        }

        if (!array_key_exists($this->_environment, $section['env'])) {
            $this->_logEnvironmentFileNotFound();
            return;
        }

        if (array_key_exists('sources', $section['env'][$this->_environment])) {
            $this->_filePaths = array_merge(
                $this->_filePaths,
                $section['env'][$this->_environment]['sources']
            );
        }
    }

    protected function _logEnvironmentFileNotFound()
    {
        $this->log($this->__('No configuration files found for %s for the environment \'%s\'. Skipping...', $this->_componentName, $this->_environment));
    }

    protected function _processFile($filePath)
    {
        $absolutePath = Mage::getBaseDir() . DS . $filePath;

        // Check if file exists
        if (!file_exists($absolutePath)) {
            $this->log('No ' . $this->_componentName . ' component file found at: ' . $absolutePath . '. Skipping...');
            return [];
        }

        // Decode the YAML File
        $yaml = new Zend_Config_Yaml($absolutePath, NULL, ['ignore_constants' => true]);

        return $yaml->toArray();
    }

    abstract protected function _processComponent($data);
}
