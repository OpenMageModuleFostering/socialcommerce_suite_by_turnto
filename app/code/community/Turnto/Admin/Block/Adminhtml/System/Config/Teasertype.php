<?php

class Turnto_Admin_Block_Adminhtml_System_Config_Teasertype
{
    public function __construct()
    {

    }

    public function toOptionArray() {
        return array(
            array('value' => 'iTeaserFunc', 'label' => Mage::helper('adminhtml')->__('Item Teaser')),
            array('value' => 'itemInputTeaserFunc', 'label' => Mage::helper('adminhtml')->__('Item Input Teaser'))
        );
    }
}