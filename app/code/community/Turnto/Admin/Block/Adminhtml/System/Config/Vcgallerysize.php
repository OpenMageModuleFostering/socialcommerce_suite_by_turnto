<?php

class Turnto_Admin_Block_Adminhtml_System_Config_Vcgallerysize
{
    public function __construct()
    {

    }

    public function toOptionArray() {
        return array(
            array('value' => 'small', 'label' => Mage::helper('adminhtml')->__('Small')),
            array('value' => 'large', 'label' => Mage::helper('adminhtml')->__('Large'))
        );
    }
}