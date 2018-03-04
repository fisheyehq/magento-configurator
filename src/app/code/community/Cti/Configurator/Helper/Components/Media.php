<?php

class Cti_Configurator_Helper_Components_Media extends Cti_Configurator_Helper_Components_Abstract
{
    protected $_componentName = 'media';
    protected $_coreConfigModel;

    public function __construct()
    {
        $this->_coreConfigModel = Mage::getModel('core/config');
    }

    protected function _processComponent($data)
    {

        try {
            // no media set
            if(!isset($data['media'])) {
                throw new Exception('Could not find a media node');
            }

            $currentPath = Mage::getBaseDir('media');

            foreach ($data['media'] as $name=>$childNode) {
                $this->_createChildFolderFileItem($currentPath,$name,$childNode);
            }

        } catch (Exception $e) {

        }
    }

    private function _createChildFolderFileItem($currentPath,$name,$node,$nest = 0)
    {
        try {

            // Update the current path to new path
            $newPath = $currentPath.DIRECTORY_SEPARATOR.$name;

            // If doesn't file/folder exists
            if (!file_exists($newPath)) {

                // If the node does not have a name
                if (!is_numeric($name)) {

                    // Then it is a directory so create it
                    mkdir($newPath, 0777, true);
                    $this->log("Created new media directory $name",$nest);
                }
            } else {

                // If the node does not have a name
                if (!is_numeric($name)) {

                    $this->log("Directory exists: $name",$nest);
                }
            }

            // If the node does not have a name
            if (!is_numeric($name)) {

                $nest++;

                // Loop through the children nodes
                foreach ($node as $childName=>$childNode) {

                    // Create a child folder
                    $this->_createChildFolderFileItem($newPath,$childName,$childNode,$nest);
                }
            } else {

                if (!isset($node['name'])) {
                    throw new Exception("No name set for child item in $currentPath");
                }

                if (!isset($node['location'])) {
                    throw new Exception("No location set for child item in $currentPath");
                }

                $newPath = $currentPath.DIRECTORY_SEPARATOR.$node['name'];

                if (file_exists($newPath)) {
                    throw new Exception("File already exists $newPath");
                }

                // Get the contents of the file
                $this->log("Downloading contents of file from ".$node['location'],$nest);
                $fileContents = file_get_contents($node['location']);

                // Save it in the new path
                file_put_contents($newPath,$fileContents);
                $this->log("Created new file $newPath",$nest);

            }


        } catch (Exception $e) {
            $this->log($e->getMessage(),$nest);
        }

    }
}
