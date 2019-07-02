<?php

/**
 * Salesfire Feed Generator
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version.   1.2.0
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
        $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
        $bundlePriceModel = Mage::getModel('bundle/product_price');

        $entityTypeId = Mage::getModel('eav/entity')
                ->setType('catalog_product')
                ->getTypeId();
        $attributeSetName = 'Default';
        $defaultAttributeSetId = Mage::getModel('eav/entity_attribute_set')
                ->getCollection()
                ->setEntityTypeFilter($entityTypeId)
                ->addFieldToFilter('attribute_set_name', $attributeSetName)
                ->getFirstItem()
                ->getAttributeSetId();

        $defaultAttributes = Mage::getModel('catalog/product_attribute_api')->items($defaultAttributeSetId);
        $defaultAttributeCodes = array();
        foreach ($defaultAttributes as $attributes) {
            $defaultAttributeCodes[] = $attributes['code'];
        }

        $storeCollection = Mage::getModel('core/store')->getCollection();
        foreach ($storeCollection as $store)
        {
            $storeId = $store->getId();
            Mage::app()->setCurrentStore($storeId);

            if (! Mage::helper('salesfire')->isAvailable($storeId)) {
                continue;
            }

            if (! Mage::helper('salesfire')->isFeedEnabled($storeId)) {
                continue;
            }

            $siteId = Mage::helper('salesfire')->getSiteId($storeId);
            $brand_code = Mage::helper('salesfire')->getBrandCode($storeId);
            $gender_code = Mage::helper('salesfire')->getGenderCode($storeId);
            $default_brand = Mage::helper('salesfire')->getDefaultBrand($storeId);

            @unlink(Mage::getBaseDir('media').'/catalog/'.$siteId.'.temp.xml');

            $mediaUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

            $currency = $store->getCurrentCurrencyCode();

            $brands = array();

            $this->printLine($siteId, '<?xml version="1.0" encoding="utf-8" ?>', 0);
            $this->printLine($siteId, '<productfeed site="'.Mage::getBaseUrl().'" date-generated="'.gmdate('c').'">', 0);

            $categories = $this->getCategories($storeId);
            if (! empty($categories)) {
                $this->printLine($siteId, '<categories>', 1);
                foreach ($categories as $category) {
                    $parent = $category->getParentCategory()->setStoreId($storeId);
                    if ($category->getLevel() <= 1) {
                        continue;
                    }

                    $this->printLine($siteId, '<category id="category_'.$category->getId().'"'.($parent && $parent->getLevel() > 1 ? ' parent="category_'.$parent->getId(). '"' : '').'>', 2);

                    $this->printLine($siteId, '<name><![CDATA['.$this->escapeString($category->getName()).']]></name>', 3);

                    $description = $category->getDescription();
                    if (! empty($description)) {
                        $this->printLine($siteId, '<description><![CDATA['.$this->escapeString($description).']]></description>', 3);
                    }

                    $this->printLine($siteId, '<url href="'.$category->getUrl().'" />', 3);

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

                if ($page == 1 && $count) {
                    $this->printLine($siteId, '<products>', 1);
                }

                foreach ($products as $product) {
                    $this->printLine($siteId, '<product id="product_'.$product->getId().'">', 2);

                    $this->printLine($siteId, '<title><![CDATA[' . $this->escapeString($product->getName()) . ']]></title>', 3);

                    $this->printLine($siteId, '<description><![CDATA[' . $this->escapeString(substr(Mage::helper('core')->escapeHtml(strip_tags($product->getDescription())), 0, 5000)) . ']]></description>', 3);

                    $this->printLine($siteId, '<price currency="' . $currency . '">' . $this->getProductPrice($product, $currency, $bundlePriceModel) . '</price>', 3);

                    $this->printLine($siteId, '<sale_price currency="' . $currency . '">' . $this->getProductSalePrice($product, $currency, $bundlePriceModel) . '</sale_price>', 3);

                    $this->printLine($siteId, '<mpn><![CDATA['.$this->escapeString($product->getSku()).']]></mpn>', 3);

                    $this->printLine($siteId, '<url href="' . $product->getProductUrl() . '" primary="true" />', 3);

                    if (! empty($gender_code)) {
                        $gender = $product->getResource()->getAttribute($gender_code)->setStoreId($storeId)->getFrontend()->getValue($product);
                        if ($gender != 'No') {
                            $this->printLine($siteId, '<gender><![CDATA['.$this->escapeString($gender).']]></gender>', 3);
                        }
                    }

                    if (! empty($brand_code)) {
                        $brand = $product->getResource()->getAttribute($brand_code)->setStoreId($storeId)->getFrontend()->getValue($product);
                        if ($brand != 'No') {
                            if (! isset($brands[$brand])) {
                                $brands[$brand] = count($brands) + 1;
                            }
                            $brandId = $brands[$brand];

                            $this->printLine($siteId, '<brand id="brand_'.$brandId.'" />', 3);
                        }
                    } else if (! empty($default_brand)) {
                        if (! isset($brands[$default_brand])) {
                            $brands[$default_brand] = count($brands) + 1;
                        }
                        $brandId = $brands[$brand];

                        $this->printLine($siteId, '<brand id="brand_'.$brandId.'" />', 3);
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

                    $attributeSetId = $product->getAttributeSetId();
                    $specificAttributes = Mage::getModel('catalog/product_attribute_api')->items($attributeSetId);
                    $attributeCodes = array();
                    foreach ($specificAttributes as $attributes) {
                        $attributeCodes[] = $attributes['code'];
                    }

                    $currentAttributes = array_diff($attributeCodes, $defaultAttributeCodes);

                    $this->printLine($siteId, '<variants>', 3);

                    if ($product->isConfigurable()) {
                        $childProducts = Mage::getModel('catalog/product_type_configurable')
                            ->getUsedProductCollection($product)
                            ->addAttributeToSelect('*');

                        if (count($childProducts) > 0) {
                            foreach ($childProducts as $childProduct) {
                                $this->printLine($siteId, '<variant id="product_variant_'.$childProduct->getId().'">', 4);

                                foreach($currentAttributes as $attribute) {
                                    $attribute = trim($attribute);

                                    $text = $childProduct->getResource()->getAttribute($attribute)->setStoreId($storeId)->getFrontend()->getValue($childProduct);

                                    if ($text != 'No') {
                                        $this->printLine($siteId, '<'.$attribute.'><![CDATA['.$this->escapeString($text).']]></'.$attribute.'>', 5);
                                    }
                                }

                                $this->printLine($siteId, '<mpn><![CDATA['.$this->escapeString($childProduct->getSku()).']]></mpn>', 5);

                                $this->printLine($siteId, '<stock>'.($childProduct->getStockItem() && $childProduct->getStockItem()->getIsInStock() ? ($childProduct->getStockItem()->getQty() > 0 ? (int) $childProduct->getData('stock_item')->getData('qty') : 1) : 0).'</stock>', 5);

                                $this->printLine($siteId, '<url href="'.$product->getProductUrl().'" />', 5);

                                $image = $childProduct->getImage();
                                if (! empty($image)) {
                                    $this->printLine($siteId, '<images>', 5);
                                    $this->printLine($siteId, '<image src="'.$mediaUrl.'catalog/product'.$image.'" primary="true" />', 6);
                                    $this->printLine($siteId, '</images>', 5);
                                }

                                $this->printLine($siteId, '</variant>', 4);
                            }
                        }
                    } else {
                        $this->printLine($siteId, '<variant id="product_variant_'.$product->getId().'">', 4);

                        foreach($currentAttributes as $attribute) {
                            $attribute = trim($attribute);

                            $text = $product->getResource()->getAttribute($attribute)->setStoreId($storeId)->getFrontend()->getValue($product);

                            if ($text != 'No') {
                                $this->printLine($siteId, '<'.$attribute.'><![CDATA['.$this->escapeString($text).']]></'.$attribute.'>', 5);
                            }
                        }

                        $this->printLine($siteId, '<mpn><![CDATA['.$this->escapeString($product->getSku()).']]></mpn>', 5);

                        $this->printLine($siteId, '<stock>'.($product->getStockItem() && $product->getStockItem()->getIsInStock() ? ($product->getStockItem()->getQty() > 0 ? (int) $product->getData('stock_item')->getData('qty') : 1) : 0).'</stock>', 5);

                        $this->printLine($siteId, '<url href="'.$product->getProductUrl().'" />', 5);

                        $image = $product->getImage();
                        if (! empty($image)) {
                            $this->printLine($siteId, '<images>', 5);
                            $this->printLine($siteId, '<image src="'.$mediaUrl.'catalog/product'.$image.'" primary="true" />', 6);
                            $this->printLine($siteId, '</images>', 5);
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

            if (count($brands) > 0) {
                $this->printLine($siteId, '<brands count="'.(count($brands)).'">', 1);

                foreach ($brands as $brand => $brandId) {
                    $this->printLine($siteId, '<brand id="brand_'.$brandId.'">', 2);
                    $this->printLine($siteId, '<name><![CDATA['.$this->escapeString($brand).']]></name>', 3);
                    $this->printLine($siteId, '</brand>', 2);
                }

                $this->printLine($siteId, '</brands>', 1);
            }

            $this->printLine($siteId, '</productfeed>', 0);

            @rename(Mage::getBaseDir('media').'/catalog/'.$siteId.'.temp.xml', Mage::getBaseDir('media').'/catalog/'.$siteId.'.xml');
            @unlink(Mage::getBaseDir('media').'/catalog/'.$siteId.'.temp.xml');
        }

        exit;
    }

    public function getCategories($storeId)
    {
        $rootid     = Mage::app()->getStore($storeId)->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('path', array('like'=> "1/".$rootid."/%"))
            ->addAttributeToSelect('*')
            ->addIsActiveFilter();

        return $categories;
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

        if (!empty($curPage))
        {
            $collection->setCurPage($curPage);
            $collection->setPageSize($pageSize);
        }

        $collection->clear();

        return $collection;
    }

    protected function getProductPrice($product, $currency, $bundlePriceModel)
    {
        switch($product->getTypeId())
        {
            case 'grouped':
                return Mage::helper('tax')->getPrice($product, $product->getMinimalPrice());
            break;

            case 'bundle':
                return $bundlePriceModel->getTotalPrices($product,'min',1);
            break;

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
            break;

            case 'bundle':
                return $bundlePriceModel->getTotalPrices($product,'min',1);
            break;

            default:
                if ($product->getSpecialPrice()) {
                    return Mage::helper('tax')->getPrice($product, $product->getSpecialPrice());
                }
                return Mage::helper('tax')->getPrice($product, $product->getFinalPrice());
        }
    }
}
