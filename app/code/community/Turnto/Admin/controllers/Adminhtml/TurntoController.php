<?php

class Turnto_Admin_Adminhtml_TurntoController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $resource = Mage::getSingleton('core/resource');

        Mage::register('websites', Mage::app()->getWebsites());
        Mage::register('stores', Mage::app()->getStores());

        $this->_addLeft($this->getLayout()->createBlock('Turnto_Admin_Block_ShowTabsAdminBlock'));

        $this->renderLayout();
    }

    public function redirectAction()
    {
        $this->_redirectUrl('http://www.turnto.com');
    }

    public function ratingsAction()
    {

        Mage::helper('adminhelper1')->loadRatings();
        $message = $this->__('Products Ratings successfully updated.');
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
        $this->_redirect('*/*/', array('active_tab' => 'turnto_ratings_feed_tab'));

    }

    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        $catalogFeed = true;
        $websiteId = $post['websiteId'];
        $storeId = $post['storeId'];
        $path = Mage::getBaseDir('media') . DS . 'turnto/';

        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }

            if ($post['feed_type'] == 'historical') {
                /* form processing */
                $startDate = $post['start_date'];

                if ($startDate == null || $startDate == "") {
                    Mage::getSingleton('adminhtml/session')->addError("Start Date is required");
                    $this->_redirect('*/*/', array('active_tab' => 'turnto_hist_feed_tab'));
                    return;
                }

                $helper = Mage::helper('adminhelper1');
                $helper->generateHistoricalOrdersFeed($startDate, $storeId, 'histfeed.csv');

                $message = $this->__('The historical feed was successfully generated. Click the &quot;Download historical feed&quot; link to download.');
                $catalogFeed = false;
            } else {
                /* form processing */
                if (isset($websiteId)) {
                    $split = explode('_', $websiteId);
                    $websiteId = $split[0];
                    $storeId = $split[1];
                } else {
                    $websiteId = 1;
                }

                if (!isset($storeId)) {
                    $storeId = 1;
                }

                $handle = fopen($path . 'catfeed.csv', 'w');

                fwrite($handle, "SKU\tIMAGEURL\tTITLE\tPRICE\tCURRENCY\tACTIVE\tITEMURL\tCATEGORY\tKEYWORDS\tREPLACEMENTSKU\tINSTOCK\tVIRTUALPARENTCODE\tCATEGORYPATHJSON\tISCATEGORY");
                fwrite($handle, "\n");

                $products = Mage::getModel('catalog/product')->setStoreId($storeId)->getCollection()->addAttributeToSelect('name')->addAttributeToSelect('*')->addAttributeToSelect('price')->addAttributeToSelect('image')->addWebsiteFilter($websiteId);
                if ($products) {
                    foreach ($products as $product) {
                        $product->setStoreId($storeId);
                        fwrite($handle, $product->getSku());
                        fwrite($handle, "\t");
                        if ($product->getImage() != null && $product->getImage() != "no_selection") {
                            fwrite($handle, Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage()));
                        } else if ($product->getSmallImage() != null && $product->getSmallImage() != "no_selection") {
                            fwrite($handle, Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getSmallImage()));
                        } else if ($product->getThumbnail() != null && $product->getThumbnail() != "no_selection") {
                            fwrite($handle, Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail()));
                        }
                        fwrite($handle, "\t");
                        fwrite($handle, $product->getName());
                        fwrite($handle, "\t");
                        fwrite($handle, $product->getPrice());
                        fwrite($handle, "\t");
                        //CURRENCY
                        fwrite($handle, "\t");
                        //ACTIVE
                        fwrite($handle, "Y");
                        fwrite($handle, "\t");
                        //ITEMURL
                        fwrite($handle, $product->getProductUrl());
                        fwrite($handle, "\t");
                        //CATEGORY
                        $ids = $product->getCategoryIds();
                        fwrite($handle, (isset($ids[0]) ? $ids[0] : ''));
                        fwrite($handle, "\t");
                        // KEYWORDS
                        fwrite($handle, "\t");
                        // REPLACEMENTSKU
                        fwrite($handle, "\t");
                        //VIRTUALPARENTCODE
                        fwrite($handle, "\t");
                        //CATEGORYPATHJSON
                        fwrite($handle, "\t");
                        //ISCATEGORY
                        fwrite($handle, "n");
                        fwrite($handle, "\n");
                    }
                }

                $categories = Mage::getModel('catalog/category')->setStoreId($storeId)->getCollection()->addAttributeToSelect('name');
                if ($categories) {
                    foreach ($categories as $category) {
                        if ($category->getId() == 1) {
                            continue;
                        }
                        $category->setStoreId($storeId);
                        fwrite($handle, $category->getId());
                        fwrite($handle, "\t");
                        //IMAGEURL
                        fwrite($handle, "\t");
                        //TITLE
                        fwrite($handle, $category->getName());
                        fwrite($handle, "\t");
                        //PRICE
                        fwrite($handle, "\t");
                        //CURRENCY
                        fwrite($handle, "\t");
                        //ACTIVE
                        fwrite($handle, "Y");
                        fwrite($handle, "\t");
                        //ITEMURL
                        fwrite($handle, $category->getUrl());
                        fwrite($handle, "\t");
                        //CATEGORY
                        fwrite($handle, $category->getParentCategory()->getId());
                        fwrite($handle, "\t");
                        //KEYWORDS
                        fwrite($handle, "\t");
                        //REPLACEMENTSKU
                        fwrite($handle, "\t");
                        //VIRTUALPARENTCODE
                        fwrite($handle, "\t");
                        //CATEGORYPATHJSON
                        fwrite($handle, "\t");
                        //ISCATEGORY
                        fwrite($handle, "Y");
                        fwrite($handle, "\n");

                    }
                }

                fclose($handle);

                $message = $this->__('The catalog feed was successfully generated. Click the &quot;Download catalog feed&quot; link to download.');
            }
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        if ($catalogFeed) {
            $this->_redirect('*/*/', array('active_tab' => 'turnto_catalog_feed_tab'));
        } else {
            $this->_redirectUrl(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/turnto/histfeed.csv');
        }
    }
}
