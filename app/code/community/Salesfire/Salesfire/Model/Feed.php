<?php

/**
 * Salesfire Feed Generator
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version.   1.2.15
 */
class Salesfire_Salesfire_Model_Feed extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('salesfire/feed');
    }

    public function printLine($siteId, $text, $tab=0)
    {
        file_put_contents(Mage::getBaseDir('media').'/catalog/'.$siteId.'.temp.xml', str_repeat("\t", $tab) . $text . "\n", FILE_APPEND);
    }

    public function escapeString($text)
    {
        return html_entity_decode(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', utf8_encode($text))));
    }

    public function generate()
    {
        Salesfire_Salesfire_Helper_Data::listenForErrors(true);

        $this->runGenerator();

        Salesfire_Salesfire_Helper_Data::listenForErrors(false);
    }

    private function runGenerator()
    {
        $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
        $bundlePriceModel = Mage::getModel('bundle/product_price');

        $storeCollection = Mage::getModel('core/store')->getCollection();
        foreach ($storeCollection as $store) {
            $storeId = $store->getId();
            Mage::app()->setCurrentStore($storeId);

            if (! Mage::helper('salesfire')->isAvailable($storeId)) {
                continue;
            }

            if (! Mage::helper('salesfire')->isFeedEnabled($storeId)) {
                continue;
            }

            $siteId             = Mage::helper('salesfire')->getSiteId($storeId);
            $brand_code         = Mage::helper('salesfire')->getBrandCode($storeId);
            $gender_code        = Mage::helper('salesfire')->getGenderCode($storeId);
            $age_group_code     = Mage::helper('salesfire')->getAgeGroupCode($storeId);
            $colour_code        = Mage::helper('salesfire')->getColourCode($storeId);
            $attribute_codes    = Mage::helper('salesfire')->getAttributeCodes($storeId);
            $default_brand      = Mage::helper('salesfire')->getDefaultBrand($storeId);

            @unlink(Mage::getBaseDir('media').'/catalog/'.$siteId.'.temp.xml');

            $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

            $currency = $store->getCurrentCurrencyCode();

            $brands = array();

            $this->printLine($siteId, '<?xml version="1.0" encoding="utf-8" ?>', 0);
            $this->printLine($siteId, '<productfeed site="'.Mage::getBaseUrl().'" date-generated="'.gmdate('c').'" version="'.Mage::helper('salesfire')->getVersion().'">', 0);

            $categories = $this->getCategories($storeId);

            if (! empty($categories)) {
                $this->printLine($siteId, '<categories>', 1);
                foreach ($categories as $category) {
                    $parent = $category->getParentCategory()->setStoreId($storeId);
                    if ($category->getLevel() <= 1) {
                        continue;
                    }

                    $this->printLine($siteId, '<category id="category_' . $category->getId() . '"' . ($parent && $parent->getLevel() > 1 ? ' parent="category_'.$parent->getId(). '"' : '') . '>', 2);

                    $this->printLine($siteId, '<id>' . $this->escapeString($category->getId()) . '</id>', 3);

                    $this->printLine($siteId, '<name><![CDATA['.$this->escapeString($category->getName()).']]></name>', 3);

                    $this->printLine($siteId, '<breadcrumb><![CDATA['.$this->escapeString($this->getCategoryBreadcrumb($storeId, $category)).']]></breadcrumb>', 3);

                    $description = $category->getDescription();
                    if (! empty($description)) {
                        $this->printLine($siteId, '<description><![CDATA['.$this->escapeString($description).']]></description>', 3);
                    }

                    $this->printLine($siteId, '<link><![CDATA[' . $category->getUrl() . ']]></link>', 3);

                    $keywords = $category->getMetaKeywords();
                    if (! empty($keywords)) {
                        $this->printLine($siteId, '<keywords>', 3);
                        foreach (explode(',', $keywords) as $keyword) {
                            $this->printLine($siteId, '<keyword><![CDATA['.$this->escapeString($keyword).']]></keyword>', 4);
                        }

                        $this->printLine($siteId, '</keywords>', 3);
                    }

                    $this->printLine($siteId, '</category>', 2);
                }

                $this->printLine($siteId, '</categories>', 1);
            }

            $page = 1;
            do {
                $products = $this->getVisibleProducts($storeId, $page);
                $count = count($products);

                $backendModel = $products->getResource()->getAttribute('media_gallery')->getBackend();

                if ($page == 1 && $count) {
                    $this->printLine($siteId, '<products>', 1);
                }

                foreach ($products as $product) {
                    $this->printLine($siteId, '<product id="product_'.$product->getId().'">', 2);

                    $this->printLine($siteId, '<id>' . $product->getId() . '</id>', 3);

                    $this->printLine($siteId, '<title><![CDATA[' . $this->escapeString($product->getName()) . ']]></title>', 3);

                    $description = '';
                    if (! empty($description_code)) {
                        $description = $this->getAttributeValue($storeId, $product, $description_code);
                    } else {
                        $description = $product->getDescription() ?: $product->getShortDescription();
                    }

                    $this->printLine($siteId, '<description><![CDATA[' . $this->escapeString(substr(Mage::helper('core')->escapeHtml(strip_tags($description)), 0, 5000)) . ']]></description>', 3);

                    $this->printLine($siteId, '<price currency="' . $currency . '">' . $this->getProductPrice($product, $currency, $bundlePriceModel) . '</price>', 3);

                    $this->printLine($siteId, '<sale_price currency="' . $currency . '">' . $this->getProductSalePrice($product, $currency, $bundlePriceModel) . '</sale_price>', 3);

                    $this->printLine($siteId, '<mpn><![CDATA['.$this->escapeString($product->getSku()).']]></mpn>', 3);

                    $this->printLine($siteId, '<link><![CDATA[' . $product->getProductUrl() . ']]></link>', 3);

                    if (! empty($gender_code)) {
                        $gender = $this->getAttributeValue($storeId, $product, $gender_code);
                        if(! empty($gender)) {
                            $this->printLine($siteId, '<gender><![CDATA['.$this->escapeString($gender).']]></gender>', 3);
                        }
                    }

                    if (! empty($age_group_code)) {
                        $age_group = $this->getAttributeValue($storeId, $product, $age_group_code);
                        if(! empty($age_group)) {
                            $this->printLine($siteId, '<age_group><![CDATA['.$this->escapeString($age_group).']]></age_group>', 3);
                        }
                    }

                    if (! empty($brand_code)) {
                        $brand = $this->getAttributeValue($storeId, $product, $brand_code);
                        if(! empty($brand)) {
                            $this->printLine($siteId, '<brand><![CDATA[' . $this->escapeString($brand) . ']]></brand>', 3);
                        }
                    } else if (! empty($default_brand)) {
                        $this->printLine($siteId, '<brand><![CDATA[' . $this->escapeString($default_brand) . ']]></brand>', 3);
                    }

                    $categories = $product->getCategoryIds();
                    if (! empty($categories)) {
                        $this->printLine($siteId, '<categories>', 3);
                        foreach ($categories as $categoryId) {
                            $this->printLine($siteId, '<category id="category_'.$categoryId.'" />', 4);
                        }

                        $this->printLine($siteId, '</categories>', 3);
                    }

                    $keywords = $product->getMetaKeywords();
                    if (! empty($keywords)) {
                        $this->printLine($siteId, '<keywords>', 3);
                        foreach (explode(',', $keywords) as $keyword) {
                            $this->printLine($siteId, '<keyword><![CDATA['.$this->escapeString($keyword).']]></keyword>', 4);
                        }

                        $this->printLine($siteId, '</keywords>', 3);
                    }

                    $this->printLine($siteId, '<variants>', 3);

                    if ($product->isConfigurable()) {
                        $childProducts = Mage::getModel('catalog/product_type_configurable')
                            ->getUsedProductCollection($product)
                            ->addAttributeToSelect('*');

                        if (! empty($childProducts)) {
                            foreach ($childProducts as $childProduct) {
                                $this->printLine($siteId, '<variant>', 4);

                                $this->printLine($siteId, '<id>' . $childProduct->getId() . '</id>', 5);

                                foreach ($attribute_codes as $attribute) {
                                    $attribute = trim($attribute);

                                    if (empty($attribute) || in_array($attribute, array('id', 'mpn', 'link', 'image', 'stock', $colour_code, $gender_code, $age_group_code, $brand_code))) {
                                        continue;
                                    }

                                    $attribute_text = $this->getAttributeValue($storeId, $childProduct, $attribute);
                                    if (! empty($attribute_text)) {
                                        $this->printLine($siteId, '<'.$attribute.'><![CDATA['.$this->escapeString($attribute_text).']]></'.$attribute.'>', 5);
                                    }
                                }

                                if (! empty($colour_code)) {
                                    $colour = $this->getAttributeValue($storeId, $childProduct, $colour_code);
                                    if (! empty($colour)) {
                                        $this->printLine($siteId, '<colour><![CDATA['.$this->escapeString($colour).']]></colour>', 5);
                                    }
                                }

                                $this->printLine($siteId, '<mpn><![CDATA['.$this->escapeString($childProduct->getSku()).']]></mpn>', 5);

                                $this->printLine($siteId, '<stock>'.($childProduct->getStockItem() && $childProduct->getStockItem()->getIsInStock() ? ($childProduct->getStockItem()->getQty() > 0 ? (int) $childProduct->getData('stock_item')->getData('qty') : 1) : 0).'</stock>', 5);

                                $this->printLine($siteId, '<link><![CDATA[' . $product->getProductUrl() . ']]></link>', 5);

                                $image = $this->getProductImage($storeId, $product, $childProduct, $backendModel);
                                if (! empty($image)) {
                                    $this->printLine($siteId, '<image><![CDATA[' . $image . ']]></image>', 5);
                                }

                                $this->printLine($siteId, '</variant>', 4);
                            }
                        }
                    } else {
                        $this->printLine($siteId, '<variant>', 4);

                        $this->printLine($siteId, '<id>' . $product->getId() . '</id>', 5);

                        foreach ($attribute_codes as $attribute) {
                            $attribute = trim($attribute);

                            if (empty($attribute) || in_array($attribute, array('id', 'mpn', 'link', 'image', 'stock', $colour_code, $gender_code, $age_group_code, $brand_code))) {
                                continue;
                            }

                            $attribute_text = $this->getAttributeValue($storeId, $product, $attribute);
                            if (! empty($attribute_text)) {
                                $this->printLine($siteId, '<'.$attribute.'><![CDATA['.$this->escapeString($attribute_text).']]></'.$attribute.'>', 5);
                            }
                        }

                        if (! empty($colour_code)) {
                            $colour = $this->getAttributeValue($storeId, $product, $colour_code);
                            if (! empty($colour)) {
                                $this->printLine($siteId, '<colour><![CDATA['.$this->escapeString($colour).']]></colour>', 5);
                            }
                        }

                        $this->printLine($siteId, '<mpn><![CDATA['.$this->escapeString($product->getSku()).']]></mpn>', 5);

                        $this->printLine($siteId, '<stock>'.($product->getStockItem() && $product->getStockItem()->getIsInStock() ? ($product->getStockItem()->getQty() > 0 ? (int) $product->getData('stock_item')->getData('qty') : 1) : 0).'</stock>', 5);

                        $this->printLine($siteId, '<link><![CDATA[' . $product->getProductUrl() . ']]></link>', 5);

                        $image = $this->getProductImage($storeId, $product, $product, $backendModel);
                        if (! empty($image)) {
                            $this->printLine($siteId, '<image><![CDATA[' . $image . ']]></image>', 5);
                        }

                        $this->printLine($siteId, '</variant>', 4);
                    }

                    $this->printLine($siteId, '</variants>', 3);

                    $this->printLine($siteId, '</product>', 2);
                }

                $page++;
            } while ($count >= 100);

            if ($count || $page > 1) {
                $this->printLine($siteId, '</products>', 1);
            }

            $this->printLine($siteId, '</productfeed>', 0);

            @rename(Mage::getBaseDir('media').'/catalog/'.$siteId.'.temp.xml', Mage::getBaseDir('media').'/catalog/'.$siteId.'.xml');
            @unlink(Mage::getBaseDir('media').'/catalog/'.$siteId.'.temp.xml');
        }
    }

    public function getCategories($storeId)
    {
        $rootCategoryId = Mage::app()->getStore($storeId)->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->setStoreId($storeId)
            ->addFieldToFilter('is_active', 1)
            ->addAttributeToFilter('path', array('like' => "1/{$rootCategoryId}/%"))
            ->addAttributeToSelect('*');

        return $categories;
    }

    public function getCategoryBreadcrumb($storeId, $category, $breadcrumb='')
    {
        if (! empty($breadcrumb)) {
            $breadcrumb = ' > ' . $breadcrumb;
        }

        $breadcrumb = $category->getName() . $breadcrumb;

        $parent = $category->getParentCategory()->setStoreId($storeId);
        if ($parent && $parent->getLevel() > 1) {
            return $this->getCategoryBreadcrumb($storeId, $parent, $breadcrumb);
        }

        return $breadcrumb;
    }

    protected function getVisibleProducts($storeId, $curPage=1, $pageSize=100)
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
             ->addAttributeToSelect('*')
             ->addAttributeToFilter('status', 1)        // Enabled
             ->addAttributeToFilter('visibility', 4)    // Catalog/search
             ->addStoreFilter($storeId)
             ->setStoreId($storeId)
             ->addMinimalPrice()
             ->setPageSize($pageSize);  // Sets number of products to 1000

        if (! empty($curPage)) {
            $collection->setCurPage($curPage);
            $collection->setPageSize($pageSize);
        }

        $collection->clear();

        return $collection;
    }

    protected function getProductPrice($product, $currency, $bundlePriceModel)
    {
        switch ($product->getTypeId()) {
            case 'grouped':
                return Mage::helper('tax')->getPrice($product, $product->getMinimalPrice());
            case 'bundle':
                return $bundlePriceModel->getTotalPrices($product, 'min', 1);
            default:
                return Mage::helper('tax')->getPrice($product, $product->getPrice());
        }
    }

    protected function getProductSalePrice($product, $currency, $bundlePriceModel)
    {
        switch($product->getTypeId())
        {
            case 'grouped':
                return Mage::helper('tax')->getPrice($product, $product->getMinimalPrice());
            case 'bundle':
                return $bundlePriceModel->getTotalPrices($product, 'min', 1);
            default:
                if ($product->getSpecialPrice()) {
                    return Mage::helper('tax')->getPrice($product, $product->getSpecialPrice());
                }
                return Mage::helper('tax')->getPrice($product, $product->getFinalPrice());
        }
    }

    protected function getAttributeValue($storeId, $product, $attribute) {
        $attribute_obj = $product->getResource()->getAttribute($attribute);

        if(! empty($attribute_obj)) {
            $attribute_text = $attribute_obj->setStoreId($storeId)->getFrontend()->getValue($product);

            if ($attribute_text != 'No' && $attribute_text != 'no_selection') {
                return $attribute_text;
            }
        }

        return null;
    }

    protected function getProductImage($storeId, $product, $childProduct, $backendModel) {
        $image_code = Mage::helper('salesfire')->getImageCode($storeId);

        $image = null;
        if (! empty($image_code)) {
            $image = $this->getAttributeValue($storeId, $childProduct, $image_code);

            if (empty($image)) {
                $image = $this->getAttributeValue($storeId, $product, $image_code);
            }
        }

        if (empty($image)) {
            $image = $childProduct->getImage();
            if (empty($image) || $image == 'no_selection') {
                $image = $product->getImage();
                if ($image == 'no_selection') {
                    $image = null;
                }
            }
        }

        if (empty($image)) {
            $image = $childProduct->getThumbnail();
            if (empty($image) || $image == 'no_selection') {
                $image = $product->getThumbnail();
                if ($image == 'no_selection') {
                    $image = null;
                }
            }
        }

        if (empty($image)) {
            $image = $childProduct->getSmallImage();
            if (empty($image) || $image == 'no_selection') {
                $image = $product->getSmallImage();
                if ($image == 'no_selection') {
                    $image = null;
                }
            }
        }

        if (empty($image)) {
            $backendModel->afterLoad($childProduct);
            $imageGallery = $childProduct->getMediaGalleryImages();

            if (! $imageGallery->getSize()) {
                $backendModel->afterLoad($product);
                $imageGallery = $product->getMediaGalleryImages();
            }

            if ($imageGallery->getSize()) {
                $firstImage = $imageGallery->getFirstItem();
                if ($firstImage) {
                    $image = $firstImage['url'];
                }
            }
        } else {
            $image = Mage::getSingleton('catalog/product_media_config')->getMediaUrl($image);
        }

        return $image;
    }
}
