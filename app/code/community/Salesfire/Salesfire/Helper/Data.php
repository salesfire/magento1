<?php

/**
 * Salesfire data helper
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version    1.2.8
 */
class Salesfire_Salesfire_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_GENERAL_ENABLED          = 'salesfire/general/is_enabled';
    const XML_PATH_GENERAL_SITE_ID          = 'salesfire/general/site_id';
    const XML_PATH_FEED_ENABLED             = 'salesfire/feed/is_enabled';
    const XML_PATH_FEED_DEFAULT_BRAND       = 'salesfire/feed/default_brand';
    const XML_PATH_FEED_BRAND_CODE          = 'salesfire/feed/brand_code';
    const XML_PATH_FEED_DESCRIPTION_CODE    = 'salesfire/feed/description_code';
    const XML_PATH_FEED_IMAGE_CODE          = 'salesfire/feed/image_code';
    const XML_PATH_FEED_GENDER_CODE         = 'salesfire/feed/gender_code';
    const XML_PATH_FEED_COLOUR_CODE         = 'salesfire/feed/colour_code';
    const XML_PATH_FEED_AGE_GROUP_CODE      = 'salesfire/feed/age_group_code';
    const XML_PATH_FEED_ATTRIBUTE_CODES     = 'salesfire/feed/attribute_codes';

    protected static $errorHandlerEnabled = false;

    protected static $errorHandlerRegistered = false;

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
     * Get the product description attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getDescriptionCode($storeId = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_FEED_DESCRIPTION_CODE, $storeId));
    }

    /**
     * Get the product image attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getImageCode($storeId = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_FEED_IMAGE_CODE, $storeId));
    }

    /**
     * Get the product colour attribute code
     *
     * @param string $storeId
     * @return string
     */
    public function getColourCode($storeId = null)
    {
        return trim(Mage::getStoreConfig(self::XML_PATH_FEED_COLOUR_CODE, $storeId));
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

    public static function listenForErrors($enabled = false)
    {
        static::$errorHandlerEnabled = $enabled;

        if ($enabled && ! static::$errorHandlerRegistered) {
            register_shutdown_function(array('Salesfire_Salesfire_Helper_Data', 'errorHandler'));

            static::$errorHandlerRegistered = true;
        }
    }

    public static function errorHandler()
    {
        if (! static::$errorHandlerEnabled) {
            return;
        }

        $error = error_get_last();

        if ($error !== null) {
            $stores = Mage::getModel('core/store')->getCollection();

            $siteUuids = array();

            foreach ($stores as $store) {
                $storeId = $store->getId();

                Mage::app()->setCurrentStore($storeId);

                if ($siteId = Mage::helper('salesfire')->getSiteId($storeId)) {
                    $siteUuids[] = $siteId;
                }
            }

            $payload = json_encode(array(
                'client'        => 'salesfire-magento-1',
                'site_uuids'    => $siteUuids,
                'error' => array(
                    'type'  => $error["type"],
                    'file'  => $error["file"],
                    'line'  => $error["line"],
                    'str'   => $error["message"],
                ),
            ));

            $ch = curl_init('https://api.salesfire.co.uk/extensions/errors');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            curl_close($ch);
        }
    }
}
