<?php

/**
 * Salesfire data helper
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version.   1.1.0
 */
class Salesfire_Salesfire_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_GENERAL_ENABLED = 'salesfire/general/is_enabled';
    const XML_PATH_GENERAL_SITE_ID = 'salesfire/general/site_id';

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
}
