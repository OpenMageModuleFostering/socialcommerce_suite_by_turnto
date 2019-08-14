<?php

class Turnto_Admin_Block_Adminhtml_System_Config_Setuptype
{
    public function __construct()
    {

    }

    public function toOptionArray() {
        return array(
            array('value' => 'overlay', 'label' => Mage::helper('adminhtml')->__('Overlay')),
            array('value' => 'dynamicEmbed', 'label' => Mage::helper('adminhtml')->__('Dynamic Embed')),
            array('value' => 'staticEmbed', 'label' => Mage::helper('adminhtml')->__('Static Embed')),
        );
    }
}