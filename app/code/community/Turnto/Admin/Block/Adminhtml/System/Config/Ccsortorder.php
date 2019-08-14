<?php

class Turnto_Admin_Block_Adminhtml_System_Config_Ccsortorder
{
    public function __construct()
    {

    }

    public function toOptionArray() {
        return array(
            array('value' => 'most recent', 'label' => Mage::helper('adminhtml')->__('Most Recent')),
            array('value' => 'longest', 'label' => Mage::helper('adminhtml')->__('Longest'))
        );
    }
}