<?php

class Cti_Configurator_Helper_Components_CustomerGroups extends Cti_Configurator_Helper_Components_Abstract
{
    protected $_componentName = 'customer_groups';

    protected function _processComponent($data)
    {
        if (isset($data['groups'])) {
            foreach ($data['groups'] as $i=>$_group) {
                $group = Mage::getModel('customer/group')->load($_group['customer_group_code'],'customer_group_code');

                if (!$group->getId() && $group->getId() != "0") {
                    $group = Mage::getModel('customer/group')->setCustomerGroupCode($_group['customer_group_code']);
                }

                try {

                    $taxClass = $this->_getTaxClass($_group['tax_class'],Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER);
                    if (!$taxClass) {
                        throw new Exception($this->__('Customer tax class with name %s does not exist',$_group['tax_class']));
                    }

                    if ($group->getTaxClassId() != $taxClass->getId()) {
                        $group
                            ->setTaxClassId($taxClass->getId())
                            ->save();
                        $this->log($this->__('Saved Customer Group %s',$_group['customer_group_code']));
                    }

                } catch (Exception $e) {
                    $this->log($e->getMessage());
                }
            }
        }
    }

    /**
     * @param $name
     * @param $type
     *
     * @return Mage_Tax_Model_Class
     */
    private function _getTaxClass($name,$type)
    {
        $taxClasses = Mage::getResourceModel('tax/class_collection')
            ->addFieldToFilter('class_type',$type)
            ->addFieldToFilter('class_name',$name);
        if ($taxClasses->count()) {
            return $taxClasses->getFirstItem();
        }
        return false;
    }
}
