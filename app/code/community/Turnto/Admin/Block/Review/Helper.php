<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Review
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Review helper
 *
 * @category   Mage
 * @package    Mage_Review
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Turnto_Admin_Block_Review_Helper extends Mage_Review_Block_Helper
{
    
    protected $_turnto_rating = null;

    public function getRatingSummary()
    {
        
        if(Mage::getStoreConfig('turnto_admin/feedconfig/enabled')){
            
             $rating = $this->_getTurntoRating();
               return $rating->getRating()*20;

        }else{
            //return $this->getProduct()->getRatingSummary()->getRatingSummary();
            return parent::getRatingSummary();
        }

        
    }

    public function getReviewsCount()
    {
        
         if(Mage::getStoreConfig('turnto_admin/feedconfig/enabled')){

              $rating = $this->_getTurntoRating();
              if($rating->getReviewCount()){

                  return $rating->getReviewCount();
              }else{

                  return 0;
              }

         }else{
              //return $this->getProduct()->getRatingSummary()->getReviewsCount();
             return parent::getReviewsCount();
         }
    }

    protected function _getTurntoRating()
    {
         if($this->_turnto_rating)
            return $this->_turnto_rating;

         $this->_turnto_rating = Mage::getModel('turnto_admin/rating')
                                  ->getCollection()
                                  ->addFieldToFilter('product_id', $this->getProduct()->getId())
                                  ->getFirstItem();
         

         return $this->_turnto_rating;
    }

}
