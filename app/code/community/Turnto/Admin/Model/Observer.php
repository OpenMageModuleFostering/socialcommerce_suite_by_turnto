<?php

class Turnto_Admin_Model_Observer
{
 
    public function getProductsRatingsFeed()
    {
        
        if(Mage::helper('adminhelper1')->isFeedActivated())
            	  Mage::helper('adminhelper1')->loadRatings();

        //return $this;
    }


    public function pushHistoricalOrdersFeed() {
        $helper = Mage::helper('adminhelper1');
        if ($helper->isHistoricalOrderFeedPushEnabled()) {
            // get historical orders for the past 2 years
            $helper->pushHistoricalOrdersFeed();
        }
    }
 
 
}