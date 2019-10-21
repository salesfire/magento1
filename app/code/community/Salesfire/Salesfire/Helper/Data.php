<?php

/**
 * Salesfire data helper
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version    1.2.3
 */
class Salesfire_Salesfire_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_GENERAL_ENABLED      = 'salesfire/general/is_enabled';
    const XML_PATH_GENERAL_SITE_ID      = 'salesfire/general/site_id';
    const XML_PATH_FEED_ENABLED         = 'salesfire/feed/is_enabled';
    const XML_PATH_FEED_DEFAULT_BRAND   = 'salesfire/feed/default_brand';
    const XML_PATH_FEED_BRAND_CODE      = 'salesfire/feed/brand_code';
    const XML_PATH_FEED_GENDER_CODE     = 'salesfire/feed/gender_code';
    const XML_PATH_FEED_COLOUR_CODE     = 'salesfire/feed/colour_code';
    const XML_PATH_FEED_AGE_GROUP_CODE  = 'salesfire/feed/age_group_code';
    const XML_PATH_FEED_ATTRIBUTE_CODES = 'salesfire/feed/attribute_codes';

    /**
     * Whether salesfire is ready to use
     *
     * @param mixed $storeId
     * @return bool
     */
    public function isAvailable($storeId = null)
    {
        $siteId = $this->getSiteId($storeId);
        return ! empty($siteId) && $this->isEnabled($storeId);
    }

    /**
     * Get salesfire enabled flag
     *
     * @param string $storeId
     * @return string
     */
    public function isEnabled($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_GENERAL_ENABLED, $storeId);
    }

    /**
     * Get salesfire site id
     *
     * @param string $storeId
     * @return string
     */
    public function getSiteId($storeId = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_GENERAL_SITE_ID, $storeId));
    }

    /**
     * Get salesfire feed enabled flag
     *
     * @param string $storeId
     * @return string
     */
    public function isFeedEnabled($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_FEED_ENABLED, $storeId);
    }

    /**
     * Get the default brand
     *
     * @param string $storeId
     * @return string
     */
    public function getDefaultBrand($storeId = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_FEED_DEFAULT_BRAND, $storeId));
    }

    /**
     * Get the product brand attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getBrandCode($storeId = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_FEED_BRAND_CODE, $storeId));
    }

    /**
     * Get the product gender attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getGenderCode($storeId = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_FEED_GENDER_CODE, $storeId));
    }

    /**
     * Get the product age group attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getAgeGroupCode($storeId = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_FEED_AGE_GROUP_CODE, $storeId));
    }

    /**
     * Get the product age group attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getAttributeCodes($storeId = null)
    {
        return explode(',', trim(Mage::getStoreConfig(self::XML_PATH_FEED_ATTRIBUTE_CODES, $storeId)));
    }
}
