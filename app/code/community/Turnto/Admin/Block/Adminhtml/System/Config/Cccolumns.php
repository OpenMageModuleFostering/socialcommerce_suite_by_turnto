<?php

class Turnto_Admin_Block_Adminhtml_System_Config_Cccolumns
{
    public function __construct()
    {

    }

    public function toOptionArray() {
        return array(
            array('value' => 'auto', 'label' => Mage::helper('adminhtml')->__('Auto')),
            array('value' => '1', 'label' => Mage::helper('adminhtml')->__('1')),
            array('value' => '2', 'label' => Mage::helper('adminhtml')->__('2')),
            array('value' => '3', 'label' => Mage::helper('adminhtml')->__('3')),
            array('value' => '4', 'label' => Mage::helper('adminhtml')->__('4')),
        );
    }
}