<?php

class Turnto_Admin_Helper_Data extends Mage_Core_Helper_Data
{
 
   protected $_activated = null;
   protected $_feed = null;

   public function isFeedActivated()
   {
       
        $feed_url = $this->getFeedUrl();
        return $this->checkFeed($feed_url);

             
   }

   public function checkFeed($url = null)
   {
      
     	if(!$url)
	     	 return null;

        if(!$xml = simplexml_load_file($url)){
          
            $active = false;
		}else{
            $this->_feed = simplexml_load_file($url);
            $active = true;
		}

       return $active;
   }

   public function getFeedUrl()
   {

   	    $url = Mage::getStoreConfig('turnto_admin/feedconfig/url');
   	    $site_key = Mage::getStoreConfig('turnto_admin/feedconfig/site_key');
   	    $site_auth = Mage::getStoreConfig('turnto_admin/feedconfig/site_auth');

   	    if(!$url || !$site_key || !$site_auth)
   	    	return null;

   	    $feed_url = rtrim($url,'/').'/'.$site_key.'/'.$site_auth.'/turnto-skuaveragerating.xml';
   	   //var_dump($feed_url);

   	    return $feed_url;
   }

  
   public function loadRatings($url = null)
   {

	      if(!$this->_feed){

		      if(!$url)
		      	  $url = $this->getFeedUrl();
		     
		      if(!$this->checkFeed($url))
		           return null;
	      }

  	      $i=0;

	      #xml feed product 'sku' attribute contains product_id 
	      $key_field = 'sku';

	      foreach($this->_feed->products->product as $product) 
	      {

	         if(!$product[$key_field]){
                 Mage::log('Product sku is missing in the feed...', null, 'turnto_product_ratings.log');
                 continue;
	         }
	         	

	      	 $rating = Mage::getModel('turnto_admin/rating')
                                  ->getCollection()
                                  ->addFieldToFilter('product_id', $product[$key_field])
                                  ->getFirstItem();

             try{
              
                  if(!$rating->getCreatedAt())
                  	   $rating->setCreatedAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                  

                  $rating->setProductId($product[$key_field])
                  //  ->setSku($product['sku'])
                    ->setReviewCount($product['review_count'])
                    ->setUpdatedAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'))
                    ->setRating($product[$i])
                    ->save();

             }catch(Exception $e){
             	Mage::log('Error updating product rating for product id:'.$product[$key_field].'. Error details:'.$e->getMessage(), null, 'turnto_product_ratings.log');

             }

                       
			 $i++;
		  }
	
   }

   public function getLastUpdated()
   {

        $updated = null;

   	    $resource = Mage::getSingleton('core/resource');
    	$read = $resource->getConnection('core_read');

        $query = "SELECT MAX(updated_at) as updated FROM ".Mage::getSingleton('core/resource')->getTableName('turnto_admin/rating');

        $result = $read->fetchAll($query);

        if($result[0]['updated'])
              $updated = $result[0]['updated'];
          	 
       // var_dump($query); var_dump($result); exit;

        return $updated;
   }
}