<?php

class Turnto_Admin_IndexController extends Mage_Core_Controller_Front_Action{
    public function indexAction(){
        $resource = Mage::getSingleton('core/resource');     
		$readConnection = $resource->getConnection('core_read');
		$params = $this->getRequest()->getParams();
		$storeId = $params['storeId'];
		$websiteId = $params['websiteId'];
		$baseUrl =  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
                $baseMediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product';
		if(!isset($storeId)){
			$storeId = 1;
		}
		$baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
               	$baseMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product';
	        	
		if(!isset($websiteId)){
			$websiteId=1;
		}	

		echo "SKU\tIMAGEURL\tTITLE\tPRICE\tCURRENCY\tACTIVE\tITEMURL\tCATEGORY\tKEYWORDS\tREPLACEMENTSKU\tINSTOCK\tVIRTUALPARENTCODE\tCATEGORYPATHJSON\tISCATEGORY";
		echo "\n";
	        
                $products = Mage::getModel('catalog/product')->setStoreId($storeId)->getCollection()->addAttributeToSelect('name')->addAttributeToSelect('*')->addAttributeToSelect('price')->addAttributeToSelect('image')->addWebsiteFilter($websiteId);
                $products->setPageSize(100);
                $pages = $products->getLastPageNumber();
    			$currentPage = 1;
                if ($products) {
			do {
				$products->setCurPage($currentPage);
         		foreach ($products as $product) {
					$parents = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
	   				if(isset($parents[0]))
	   				{
	       					// skip products with a parent
	       					continue;
	  				}
					$product->setStoreId($storeId);
					echo $product->getSku();
					echo "\t";
					//echo Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage() );
					if($product->getImage() != null && $product->getImage() != "no_selection") {
						echo Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage() );
					} else if ($product->getSmallImage() != null && $product->getSmallImage() != "no_selection") {
						echo Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getSmallImage() );
					} else if ($product->getThumbnail() != null && $product->getThumbnail() != "no_selection") {
						echo Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getThumbnail() );
					}
					echo "\t";
					echo $product->getName();
					echo "\t";
					echo $product->getPrice();
					echo "\t";
					//CURRENCY
					echo "\t";
					//ACTIVE
					echo 'Y';
					echo "\t";
					//ITEMURL
					echo $product->getProductUrl();
					echo "\t";
					//CATEGORY
					$ids = $product->getCategoryIds();
					echo (isset($ids[0]) ? $ids[0] : '');
					echo "\t";
					// KEYWORDS
					echo "\t";
					// REPLACEMENTSKU
					echo "\t";
					//VIRTUALPARENTCODE
					echo "\t";
					//CATEGORYPATHJSON
					echo "\t";
					//ISCATEGORY
					echo "n";
					echo "\n";
				}
				$currentPage++;
				$products->clear();
			} while ($currentPage <= $pages); 
		}

		$categories = Mage::getModel('catalog/category')->setStoreId($storeId)->getCollection()->addAttributeToSelect('*');
		if ($categories) {
			foreach ($categories as $category) {
				if ($category->getId() == 1) {
        	                     	continue;
    	                        }
				$category->setStoreId($storeId);
				echo $category->getId();
				echo "\t";
				//IMAGEURL
				echo "\t";
				//TITLE
				echo $category->getName();
				echo "\t";
				//PRICE
				echo "\t";
				//CURRENCY
				echo "\t";
				//ACTIVE
				echo "Y";
				echo "\t";
				//ITEMURL
				echo $category->getUrl();
				echo "\t";
				//CATEGORY
				echo $category->getParentCategory()->getId();
				echo "\t";
				//KEYWORDS
				echo "\t";
				//REPLACEMENTSKU
				echo "\t";
				//VIRTUALPARENTCODE
				echo "\t";
				//CATEGORYPATHJSON
				echo "\t";
				//ISCATEGORY
				echo "Y";
				echo "\n";
	
			}
		}	


		return;

	
  }
}


