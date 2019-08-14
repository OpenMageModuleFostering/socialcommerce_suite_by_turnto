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

    public function isHistoricalOrderFeedPushEnabled() {
        return Mage::getStoreConfig('turnto_admin/historicalfeedconfig/enabled') != null;
    }

    public function checkFeed($url = null)
    {

        if (!$url)
            return null;

        if (!$xml = simplexml_load_file($url)) {

            $active = false;
        } else {
            $this->_feed = simplexml_load_file($url);
            $active = true;
        }

        return $active;
    }

    public function getFeedUrl()
    {

        $url = Mage::getStoreConfig('turnto_admin/feedconfig/url');
        $site_key = Mage::getStoreConfig('turnto_admin/general/site_key');
        $site_auth = Mage::getStoreConfig('turnto_admin/general/site_auth');

        if (!$url || !$site_key || !$site_auth)
            return null;

        $feed_url = rtrim($url, '/') . '/' . $site_key . '/' . $site_auth . '/turnto-skuaveragerating.xml';

        return $feed_url;
    }


    public function loadRatings($url = null)
    {

        if (!$this->_feed) {

            if (!$url)
                $url = $this->getFeedUrl();

            if (!$this->checkFeed($url))
                return null;
        }


        $key_field = 'sku';

        foreach ($this->_feed->products->product as $prod_rating) {

            if (!$prod_rating[$key_field]) {
                Mage::log('Product sku (id) is missing in the feed. Feed product record is invalid...', null, 'turnto_product_ratings.log');
                continue;
            }


            $rating = Mage::getModel('turnto_admin/rating')
                ->getCollection()
                //    ->addFieldToFilter('product_id', $prod_rating[$key_field])
                ->addFieldToFilter($key_field, $prod_rating[$key_field])
                ->getFirstItem();

            try {


                # udpate bv_average_rating attribute

                $bvProductExternalId = $prod_rating[$key_field];

                $productAverageRating = (string)$prod_rating;
                //     echo $productAverageRating; echo '<br>';
                $productReviewCount = $prod_rating['review_count'];
                $productRatingRange = 5;

                // $product = Bazaarvoice_Helper_Data::getProductFromProductExternalId($bvProductExternalId);
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $bvProductExternalId);

                if ($product) {

                    $productAverageRating = (int)round($productAverageRating);

                    $attribute = Mage::getModel('catalog/product')->getResource()->getAttribute("bv_average_rating");

                    if ($attribute) {

                        $productAverageRating = $attribute->getSource()->getOptionId($productAverageRating);
                        $product->setBvAverageRating($productAverageRating);
                        $product->setBvReviewCount($productReviewCount);
                        $product->setBvRatingRange($productRatingRange);
                        $product->getResource()->saveAttribute($product, 'bv_average_rating');
                        $product->getResource()->saveAttribute($product, 'bv_review_count');
                        $product->getResource()->saveAttribute($product, 'bv_rating_range');
                    }

                    # save rating for tne product
                    if (!$rating->getCreatedAt())
                        $rating->setCreatedAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'));

                    $rating->setSku($prod_rating[$key_field])
                        ->setProductId($product->getId())
                        ->setReviewCount($prod_rating['review_count'])
                        ->setUpdatedAt(Mage::getModel('core/date')->date('Y-m-d H:i:s'))
                        ->setRating((string)$prod_rating)
                        ->save();


                } else {
                    Mage::log('Could not find Magento product for xml file product id:' . $bvProductExternalId, null, 'turnto_product_ratings.log');
                }

            } catch (Exception $e) {
                Mage::log('Error updating product rating for product id:' . $prod_rating[$key_field] . '. Error details:' . $e->getMessage(), null, 'turnto_product_ratings.log');

            }

        }

        #cleanup turnto_product_ratings table
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');

        $table_name = Mage::getConfig()->getTablePrefix() . 'turnto_products_ratings';

        $sql = "DELETE FROM " . $table_name . " WHERE sku = ''";

        try {
            $result = $write->query($sql);

        } catch (Exception $e) {
            Mage::log('Error:' . $e->getMessage(), null, 'turnto_product_ratings.log');
        }

    }

    public function getLastUpdated()
    {

        $updated = null;

        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');

        $query = "SELECT MAX(updated_at) as updated FROM " . Mage::getSingleton('core/resource')->getTableName('turnto_admin/rating');

        $result = $read->fetchAll($query);

        if ($result[0]['updated'])
            $updated = $result[0]['updated'];

        // var_dump($query); var_dump($result); exit;

        return $updated;
    }

    public function generateHistoricalOrdersFeed($startDate, $storeId, $fileName) {
        $path = Mage::getBaseDir('media') . DS . 'turnto/';
        if (!file_exists($path)) {
            mkdir($path, 0755);
        }

        $handle = fopen($path . $fileName, 'w');

        if (!$handle) {
            Mage::throwException($this->__('Could not create historical feed file in directory ' . $path));
        }

        fwrite($handle, "ORDERID\tORDERDATE\tEMAIL\tITEMTITLE\tITEMURL\tITEMLINEID\tZIP\tFIRSTNAME\tLASTNAME\tSKU\tPRICE\tITEMIMAGEURL\tDELIVERYDATE");
        fwrite($handle, "\n");

        $fromDate = date('Y-m-d H:i:s', strtotime($startDate));
        Mage::app();
        $orders = Mage::getModel('sales/order')
            ->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addAttributeToFilter('created_at', array('from'=>$fromDate))
            ->addAttributeToSort('entity_id', 'ASC')
            ->setPageSize(100);

        $pages = $orders->getLastPageNumber();

        for ($curPage = 1; $curPage <= $pages; $curPage++) {
            $orders->setCurPage($curPage);
            $orders->load();
            foreach ($orders as $order) {
                $itemlineid = 0;
                foreach ($order->getAllVisibleItems() as $item) {
                    //ORDERID
                    fwrite($handle, $order->getId());
                    fwrite($handle, "\t");
                    //ORDERDATE
                    fwrite($handle, $order->getCreatedAtDate()->toString('Y-MM-d'));
                    fwrite($handle, "\t");
                    //EMAIL
                    fwrite($handle, $order->getCustomerEmail());
                    fwrite($handle, "\t");
                    //ITEMTITLE
                    fwrite($handle, $item->getName());
                    fwrite($handle, "\t");
                    //ITEMURL
                    $product = $item->getProduct();
                    fwrite($handle, $product->getProductUrl());
                    fwrite($handle, "\t");
                    //ITEMLINEID
                    fwrite($handle, $itemlineid++);
                    fwrite($handle, "\t");
                    //ZIP
                    fwrite($handle, $order->getShippingAddress()->getPostcode());
                    fwrite($handle, "\t");
                    //FIRSTNAME
                    $name = explode(' ', $order->getCustomerName());
                    fwrite($handle, $name[0]);
                    fwrite($handle, "\t");
                    //LASTNAME
                    if (isset($name[1])) {
                        fwrite($handle, $name[1]);
                    }
                    fwrite($handle, "\t");
                    //SKU
                    fwrite($handle, $item->getSku());
                    fwrite($handle, "\t");
                    //PRICE
                    fwrite($handle, $item->getOriginalPrice());
                    fwrite($handle, "\t");
                    //ITEMIMAGEURL
                    if ($product->getImage() != null && $product->getImage() != "no_selection") {
                        fwrite($handle, Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage()));
                    } else if ($product->getSmallImage() != null && $product->getSmallImage() != "no_selection") {
                        fwrite($handle, Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getSmallImage()));
                    } else if ($product->getThumbnail() != null && $product->getThumbnail() != "no_selection") {
                        fwrite($handle, Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail()));
                    }
                    fwrite($handle, "\t");

                    //DELIVERYDATE
                    $shipDate = $this->getDateOfShipmentContainingItem($order, $item);
                    if ($shipDate != null) {
                        fwrite($handle, $shipDate->toString('Y-MM-d'));
                    }

                    fwrite($handle, "\n");
                }
            }
            $orders->clear();
        }

        fclose($handle);
    }

    private function getDateOfShipmentContainingItem($order, $item) {
        // get the shipments for this order
        $shipments = $order->getShipmentsCollection();
        foreach ($shipments as $shipment) {
            // get the items in this shipment
            $items = $shipment->getItemsCollection();
            foreach ($items as $it) {
                // check if this shipment contains the item that was passed in
                if ($item->getId() == $it->getOrderItemId()) {
                    return $shipment->getCreatedAtDate();
                }
            }
        }

        return null;
    }

    public function pushHistoricalOrdersFeed() {
        $path = Mage::getBaseDir('media') . DS . 'turnto/';
        if (!file_exists($path)) {
            mkdir($path, 0755);
        }

        $logFile = 'turnto_historical_feed_job.log';

        Mage::log('Started pushHistoricalOrdersFeed', null, $logFile);

        try {
            $fileName = 'magento_auto_histfeed.csv';
            $storeId = Mage::getStoreConfig('turnto_admin/general/storeId');
            $storeId = $storeId ? $storeId : 1;
            $this->generateHistoricalOrdersFeed("-2 days", $storeId, $fileName);

            $file = $path . $fileName;
            $siteKey = Mage::getStoreConfig('turnto_admin/general/site_key');
            $authKey = Mage::getStoreConfig('turnto_admin/general/site_auth');
            $baseUrl = Mage::getStoreConfig('turnto_admin/general/url');
            if (!$baseUrl) {
                $baseUrl = "http://www.turnto.com";
            }
            $url = $baseUrl . "/feedUpload/postfile";
            $feedStyle = "tab-style.1";

            Mage::log('Filename: "' . $fileName . '"', null, $logFile);
            Mage::log('Store Id: "' . $storeId . '"', null, $logFile);
            Mage::log('siteKey: "' . $siteKey . '"', null, $logFile);
            Mage::log('authKey: "' . $authKey . '"', null, $logFile);

            if (!$siteKey || !$authKey) {
                Mage::log('No siteKey or authKey found in configuration', null, $logFile);
                return;
            }

            if (!file_exists($file)) {
                Mage::log('Could not find the newly created historical feed file. Are the write permission correct on /media/turnto?', null, $logFile);
                return;
            }
            $fields = array('siteKey' => $siteKey, 'authKey' => $authKey, 'feedStyle' => $feedStyle, 'file' => "@$file");

            Mage::log('Attempting to post file to ' . $url, null, $logFile);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            $response = curl_exec($ch);
            Mage::log('Response from server: ' . $response, null, $logFile);
            curl_close($ch);

            Mage::log('Ended pushHistoricalOrdersFeed', null, $logFile);

            echo $response;
        } catch (Exception $e) {
            Mage::log('Exception caught: ' . $e->getMessage(), null, $logFile);
        }
    }
}