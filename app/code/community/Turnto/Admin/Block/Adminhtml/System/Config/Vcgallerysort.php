<?php

class Turnto_Admin_Block_Adminhtml_System_Config_Vcgallerysort
{
    public function __construct()
    {

    }

    public function toOptionArray() {
        return array(
            array('value' => 'mostvotes', 'label' => Mage::helper('adminhtml')->__('Most Votes')),
            array('value' => 'mostrecent', 'label' => Mage::helper('adminhtml')->__('Most Recent')),
            array('value' => 'longest', 'label' => Mage::helper('adminhtml')->__('Longest'))
        );
    }
}