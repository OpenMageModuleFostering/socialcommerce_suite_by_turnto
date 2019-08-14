<?php

class Turnto_Client_Block_Qacontent extends Mage_Core_Block_Template
{
    const TURNTO_QA_STATIC_CACHE_TAG = 'TURNTO_QA_STATIC_CACHE_TAG';
    const TURNTO_QA_STATIC_CACHE_KEY = 'TURNTO_QA_STATIC_CACHE_KEY';

    const TURNTO_STATIC_EMBED = 'staticEmbed';
    const TURNTO_DYNAMIC_EMBED = 'dynamicEmbed';

    public function __construct()
    {
        $helper = Mage::helper('turnto_client_helper/data');
        //parent::construct();
        $this->addData(array(
            // defaults to 15 minutes
            'cache_lifetime' => Mage::getStoreConfig('turnto_admin/general/static_cache_time') ? intval(Mage::getStoreConfig('turnto_admin/general/static_cache_time')) : 900,
            'cache_tags' => array(
                $this::TURNTO_QA_STATIC_CACHE_TAG,
                Mage_Catalog_Model_Product::CACHE_TAG,
                Mage::app()->getStore()->getId(),
                (int)Mage::app()->getStore()->isCurrentlySecure(),
                Mage::getDesign()->getPackageName(),
                Mage::getDesign()->getTheme('template'),
                Mage::getStoreConfig('turnto_admin/qa/qa_setup_type')
                // todo: add tra version so that it clears the cache with the version is changed?
            ),
            'cache_key' => $this::TURNTO_QA_STATIC_CACHE_KEY . $helper->getProduct()->getId()
        ));
    }

    public function getQAHtml()
    {
        $setupType = Mage::getStoreConfig('turnto_admin/qa/qa_setup_type');
        if ($setupType == $this::TURNTO_DYNAMIC_EMBED) {
            return '<div id="TurnToContent"></div>';
        } else if ($setupType == $this::TURNTO_STATIC_EMBED) {
            $helper = Mage::helper('turnto_client_helper/data');
            $sku = $helper->getSku($this->getProduct());
            return $helper->loadFile(Mage::getStoreConfig('turnto_admin/general/static_url') . '/sitedata/' . Mage::getStoreConfig('turnto_admin/general/site_key') . '/v' . $helper->getVersionForPath() . '/' . urlencode($sku) . '/d/catitemhtml');
        }

        return '';
    }
}
