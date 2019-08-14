<?php

class Turnto_Admin_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        header('Content-type: text/plain; charset=utf-8');
        try {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $params = $this->getRequest()->getParams();

            $storeId = 1;
            if (isset($params['storeId'])) {
                $storeId = $params['storeId'];
            }

            $websiteId = 1;
            if (isset($params['websiteId'])) {
                $websiteId = $params['websiteId'];
            }

            echo "SKU\tIMAGEURL\tTITLE\tPRICE\tCURRENCY\tACTIVE\tITEMURL\tCATEGORY\tKEYWORDS\tREPLACEMENTSKU\tINSTOCK\tVIRTUALPARENTCODE\tCATEGORYPATHJSON\tISCATEGORY\tBRAND\tUPC\tMPN\tISBN\tEAN\tJAN\tASIN";
            echo "\n";

            $pageSize = 100;
            $count = Mage::getModel('catalog/product')
                ->getCollection()
                ->addStoreFilter($storeId)
                ->addWebsiteFilter($websiteId)
                ->addAttributeToFilter('type_id', array('eq' => 'simple'))
                ->getSize();

            $page = 1;
            $pages = ceil($count / $pageSize);
            $parentIdToGtins = array();
            $upcCode = Mage::getStoreConfig('turnto_admin/general/upc_attribute');
            $mpnCode = Mage::getStoreConfig('turnto_admin/general/mpn_attribute');
            $isbnCode = Mage::getStoreConfig('turnto_admin/general/isbn_attribute');
            $eanCode = Mage::getStoreConfig('turnto_admin/general/ean_attribute');
            $janCode = Mage::getStoreConfig('turnto_admin/general/jan_attribute');
            $asinCode = Mage::getStoreConfig('turnto_admin/general/asin_attribute');
            $brandCode = Mage::getStoreConfig('turnto_admin/general/brand_attribute');

            do {
                $collection = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addWebsiteFilter($websiteId)
                    ->addStoreFilter($storeId)
                    ->addAttributeToFilter('type_id', array('eq' => 'simple'))
                    ->setPageSize($pageSize)
                    ->setCurPage($page);

                foreach ($collection as $product) {
                    $parents = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                    if (isset($parents[0])) {
                        foreach ($parents as $parentId) {
                            if (!array_key_exists($parentId, $parentIdToGtins)) {
                                $parentIdToGtins[$parentId] = array('upc' => array(), 'mpn' => array(), 'isbn' => array(), 'jan' => array(), 'ean' => array(), 'asin' => array());
                            }

                            self::pushValueIfNotNull($parentIdToGtins[$parentId]['upc'], self::getProductAttributeValue($product, $storeId, $upcCode));
                            //MPN
                            self::pushValueIfNotNull($parentIdToGtins[$parentId]['mpn'], self::getProductAttributeValue($product, $storeId, $mpnCode));
                            //ISBN
                            self::pushValueIfNotNull($parentIdToGtins[$parentId]['isbn'], self::getProductAttributeValue($product, $storeId, $isbnCode));
                            //EAN
                            self::pushValueIfNotNull($parentIdToGtins[$parentId]['ean'], self::getProductAttributeValue($product, $storeId, $eanCode));
                            //JAN
                            self::pushValueIfNotNull($parentIdToGtins[$parentId]['jan'], self::getProductAttributeValue($product, $storeId, $janCode));
                            //ASIN
                            self::pushValueIfNotNull($parentIdToGtins[$parentId]['asin'], self::getProductAttributeValue($product, $storeId, $asinCode));
                        }
                    } else {
                        self::outputProduct($product, $parentIdToGtins, $storeId, $upcCode, $mpnCode, $isbnCode, $eanCode, $janCode, $asinCode, $brandCode);
                    }
                }
                $page++;
                $collection->clear();
            } while ($page <= $pages);

            // other products
            $count = Mage::getModel('catalog/product')
                ->getCollection()
                ->addStoreFilter($storeId)
                ->addWebsiteFilter($websiteId)
                ->addAttributeToFilter('type_id', array('neq' => 'simple'))
                ->getSize();
            $page = 1;
            $pages = ceil($count / $pageSize);

            do {
                $collection = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addAttributeToSelect('*')
                    ->addWebsiteFilter($websiteId)
                    ->addStoreFilter($storeId)
                    ->addAttributeToFilter('type_id', array('neq' => 'simple'))
                    ->setPageSize($pageSize)
                    ->setCurPage($page);

                foreach ($collection as $product) {
                    self::outputProduct($product, $parentIdToGtins, $storeId, $upcCode, $mpnCode, $isbnCode, $eanCode, $janCode, $asinCode, $brandCode);
                }
                $page++;
                $collection->clear();
            } while ($page <= $pages);


            $categories = Mage::getModel('catalog/category')->setStoreId($storeId)->getCollection()->addAttributeToSelect('*');
            if ($categories) {
                foreach ($categories as $category) {
                    $category->setStoreId($storeId);
                    echo 'mag_category_'.$category->getId();
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
                    echo $category->getParentCategory()->getId() ? 'mag_category_'.$category->getParentCategory()->getId() : '';
                    echo "\t";
                    //KEYWORDS
                    echo "\t";
                    //REPLACEMENTSKU
                    echo "\t";
                    //VIRTUALPARENTCODE
                    echo "\t";
                    //INSTOCK
                    echo "\t";
                    //CATEGORYPATHJSON
                    echo "\t";
                    //ISCATEGORY
                    echo "Y";
                    echo "\t";
                    //BRAND
                    echo "\t";
                    //UPC
                    echo "\t";
                    //MPN
                    echo "\t";
                    //ISBN
                    echo "\t";
                    //EAN
                    echo "\t";
                    //JAN
                    echo "\t";
                    //ASIN
                    echo "\n";
                }
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        return;


    }

    private function getGTINsCommaSeparated($gtins) {
        return join(',', $gtins);
    }

    private function getProductAttributeValue($product, $storeId, $code) {
        if ($code != null && $code != '') {
            $attributeText = $product->getAttributeText($code);
            if (!$attributeText) {
                $attributeText = $product->getData($code);
            }
            if ($attributeText != null && strcasecmp($attributeText, 'NULL') != 0) {
                return $attributeText;
            }
        }
        return '';
    }

    private function outputProduct($product, $parentIdToGtins, $storeId, $upcCode, $mpnCode, $isbnCode, $eanCode, $janCode, $asinCode, $brandCode) {
        //SKU
        echo $product->getSku();
        echo "\t";
        //echo Mage::getModel('catalog/product_media_config')->getMediaUrl( $product->getImage() );
        // IMAGEURL
        $imageUrl = null;
        if ($product->getImage() != null && $product->getImage() != "no_selection") {
            $imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
        } else if ($product->getSmallImage() != null && $product->getSmallImage() != "no_selection") {
            $imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getSmallImage());
        } else if ($product->getThumbnail() != null && $product->getThumbnail() != "no_selection") {
            $imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail());
        }
        if (!$imageUrl) {
            echo $product->getImageUrl();
        } else {
            echo $imageUrl;
        }

        echo "\t";
        //TITLE
        echo $product->getName();
        echo "\t";
        //PRICE
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
        echo(isset($ids[0]) ? 'mag_category_'.$ids[0] : '');
        echo "\t";
        // KEYWORDS
        echo "\t";
        // REPLACEMENTSKU
        echo "\t";
        //VIRTUALPARENTCODE
        echo "\t";
        //INSTOCK
        echo "\t";
        //CATEGORYPATHJSON
        echo "\t";
        //ISCATEGORY
        echo "n";
        echo "\t";
        //Brand
        echo self::getProductAttributeValue($product, $storeId, $brandCode);
        echo "\t";
        $productId = $product->getId();
        if ($parentIdToGtins[$productId]) {
            // this product is a parent for another product.  roll-up the GTINs
            // UPCs rolled up
            echo self::getGTINsCommaSeparated($parentIdToGtins[$productId]['upc']);
            echo "\t";
            // MPNs rolled up
            echo self::getGTINsCommaSeparated($parentIdToGtins[$productId]['mpn']);
            echo "\t";
            // ISBNs rolled up
            echo self::getGTINsCommaSeparated($parentIdToGtins[$productId]['isbn']);
            echo "\t";
            // EANs rolled up
            echo self::getGTINsCommaSeparated($parentIdToGtins[$productId]['ean']);
            echo "\t";
            // JANs rolled up
            echo self::getGTINsCommaSeparated($parentIdToGtins[$productId]['jan']);
            echo "\t";
            // ASINs rolled up
            echo self::getGTINsCommaSeparated($parentIdToGtins[$productId]['asin']);
        } else {
            // this is a simple product just output the single GTINs
            //UPC
            echo self::getProductAttributeValue($product, $storeId, $upcCode);
            echo "\t";
            //MPN
            echo self::getProductAttributeValue($product, $storeId, $mpnCode);
            echo "\t";
            //ISBN
            echo self::getProductAttributeValue($product, $storeId, $isbnCode);
            echo "\t";
            //EAN
            echo self::getProductAttributeValue($product, $storeId, $eanCode);
            echo "\t";
            //JAN
            echo self::getProductAttributeValue($product, $storeId, $janCode);
            echo "\t";
            //ASIN
            echo self::getProductAttributeValue($product, $storeId, $asinCode);
        }

        echo "\n";
    }

    private function pushValueIfNotNull(&$arr, $val) {
        if ($val != null && $val != '') {
            array_push($arr, $val);
        }
    }

    public function versionAction() {
        echo Mage::getConfig()->getNode()->modules->Turnto_Admin->version;
    }
}


