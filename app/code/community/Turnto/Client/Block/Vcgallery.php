<?php

class Turnto_Client_Block_Vcgallery extends Mage_Core_Block_Template
{
    const TURNTO_VC_GALLERY_CACHE_TAG = 'TURNTO_VC_GALLERY_CACHE_TAG';
    const TURNTO_VC_GALLERY_CACHE_KEY = 'TURNTO_VC_GALLERY_CACHE_KEY';

    public function __construct()
    {
        $helper = Mage::helper('turnto_client_helper/data');
        //parent::construct();
        $this->addData(array(
            // defaults to 15 minutes
            'cache_lifetime' => Mage::getStoreConfig('turnto_admin/general/vcgallery_cache_time') ? intval(Mage::getStoreConfig('turnto_admin/general/vcgallery_cache_time')) : 900,
            'cache_tags' => array(
                $this::TURNTO_VC_GALLERY_CACHE_TAG,
                Mage_Catalog_Model_Product::CACHE_TAG,
                Mage::app()->getStore()->getId(),
                (int)Mage::app()->getStore()->isCurrentlySecure(),
                Mage::getDesign()->getPackageName(),
                Mage::getDesign()->getTheme('template')
            ),
            'cache_key' => $this::TURNTO_VC_GALLERY_CACHE_KEY . $this->getProduct()->getId()
        ));
    }

    public function getProduct()
    {
        return Mage::registry('current_product');
    }
}
