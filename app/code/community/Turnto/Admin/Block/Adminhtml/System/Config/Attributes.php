<?php

class Turnto_Admin_Block_Adminhtml_System_Config_Attributes
{
    public function __construct()
    {

    }

    private static $validTypes = array('varchar');

    public function toOptionArray() {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');

        $attributesForDisplay = array();

        array_push($attributesForDisplay, array('value' => '', 'label' => ''));
        foreach ($attributes as $productAttr) { /** @var Mage_Catalog_Model_Resource_Eav_Attribute $productAttr */
            if (in_array($productAttr->getBackendType(), self::$validTypes)) {
                if ($productAttr->getFrontendLabel() != null && $productAttr->getFrontendLabel() != '') {
                    array_push($attributesForDisplay, array('value' => $productAttr->getAttributeCode(), 'label' => $productAttr->getFrontendLabel()));
                }
            }
        }

        return $attributesForDisplay;
    }
}