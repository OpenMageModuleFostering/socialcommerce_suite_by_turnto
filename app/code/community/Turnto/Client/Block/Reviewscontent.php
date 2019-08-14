<?php

class Turnto_Client_Block_Reviewscontent extends Mage_Core_Block_Template
{
    const TURNTO_REVIEWS_STATIC_CACHE_TAG = 'TURNTO_REVIEWS_STATIC_CACHE_TAG';
    const TURNTO_REVIEW_STATIC_CACHE_KEY = 'TURNTO_REVIEW_STATIC_CACHE_KEY';
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
                $this::TURNTO_REVIEWS_STATIC_CACHE_TAG,
                Mage_Catalog_Model_Product::CACHE_TAG,
                Mage::app()->getStore()->getId(),
                (int)Mage::app()->getStore()->isCurrentlySecure(),
                Mage::getDesign()->getPackageName(),
                Mage::getDesign()->getTheme('template'),
                Mage::getStoreConfig('turnto_admin/reviews/reviews_setup_type')
            ),
            'cache_key' => $this::TURNTO_REVIEW_STATIC_CACHE_KEY . $helper->getProduct()->getId()
        ));
    }

    public function getReviewsHtml()
    {
        $setupType = Mage::getStoreConfig('turnto_admin/reviews/reviews_setup_type');
        Mage::log('setupType: ' . $setupType . ', dynamicEmbed='.$this::TURNTO_DYNAMIC_EMBED.', staticEmbed='.$this::TURNTO_STATIC_EMBED, null, 'reviewscontent.log');
        if ($setupType == $this::TURNTO_DYNAMIC_EMBED) {
            return '<div id="TurnToReviewsContent"></div>';
        } else if ($setupType == $this::TURNTO_STATIC_EMBED) {
            Mage::log('HERE', null, 'reviewscontent.log');
            $helper = Mage::helper('turnto_client_helper/data');
            $sku = $helper->getSku($this->getProduct());
            $reviewsUrl = Mage::getStoreConfig('turnto_admin/general/static_url') . '/sitedata/' . Mage::getStoreConfig('turnto_admin/general/site_key') . '/v' . $helper->getVersionForPath() . '/' . $sku . '/d/catitemreviewshtml';
            return $helper->loadFile($reviewsUrl);
        }

        return '';
    }
}
