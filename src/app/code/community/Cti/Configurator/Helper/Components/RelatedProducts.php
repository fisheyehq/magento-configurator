<?php
class Cti_Configurator_Helper_Components_RelatedProducts extends Cti_Configurator_Helper_Components_Abstract {

    protected $_componentName = 'related_products';

    public function __construct() {
        $this->_filePaths[] = Mage::getBaseDir() . DS . 'app' . DS . 'etc' . DS . 'configurator' . DS . 'related-products.yaml';
    }

    protected function _processComponent($data) {
        if (isset($data['related'])) {

            foreach ($data['related'] as $mainSku=>$data) {

                $_productId = Mage::getModel('catalog/product')->getIdBySku($mainSku);

                try {

                    if (!$_productId) {
                        throw new Exception("Product $mainSku does not exist");
                    }

                    $this->_relateProducts(Mage::getModel('catalog/product')->load($_productId),$data);
                } catch (Exception $e) {
                    $this->log($e->getMessage());
                }

            }
        }
    }

    private function _relateProducts(Mage_Catalog_Model_Product $_product,$data) {

        $this->log($this->__('Relating products for %s',$_product->getSku()));

        $relatableData = array();

        foreach ($data as $i=>$relatedSku) {
            $relatedProductId = Mage::getModel('catalog/product')->getIdBySku($relatedSku);

            if (!$relatedProductId) {
                throw new Exception($this->__('No product with sku: %s'.$relatedSku));
            }

            $relatableData[$relatedProductId] = array(
                'position' => $i
            );

            $this->log($this->__('Related %s',$relatedSku),1);

        }


        $_product->setRelatedLinkData($relatableData);
        $_product->save();
        $this->log($this->__('Finished relating products for %s',$_product->getSku()));
    }

}