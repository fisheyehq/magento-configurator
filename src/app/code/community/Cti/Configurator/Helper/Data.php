<?php

class Cti_Configurator_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function processComponents($environment = false, $cliLogging = false)
    {
        $config = Mage::getConfig()->getNode('global/configurator_processors');
        if ($config->hasChildren()) {
            foreach ($config->asArray() as $class) {
                foreach ($class as $alias) {
                    /* @var $helper Cti_Configurator_Helper_Components_Abstract */
                    $helper = Mage::helper($alias);
                    if ($cliLogging) $helper->enableCliLog();
                    $helper->process($environment);
                }
            }
        }
    }

    public function processComponent($component, $environment = false, $cliLogging = false)
    {

        $node = 'global/configurator_processors/components/'.$component;
        $config = Mage::getConfig()->getNode($node);

        /* @var $helper Cti_Configurator_Helper_Components_Abstract */
        $helper = Mage::helper($config);
        if ($cliLogging) $helper->enableCliLog();
        $helper->process($environment);
    }

    public function getComponents()
    {
        $components = array();
        $config = Mage::getConfig()->getNode('global/configurator_processors');
        if ($config->hasChildren()) {
            foreach ($config->asArray() as $class) {
                foreach($class as $alias=>$name) {
                    $components[] = $alias;
                }
            }
        }
        return $components;
    }
}
