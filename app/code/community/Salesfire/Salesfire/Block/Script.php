<?php

/**
 * Salesfire Page Block
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version.   1.1.0
 */
class Salesfire_Salesfire_Block_Script extends Mage_Core_Block_Template
{
    /**
     * Return the site code in correct format
     *
     * @return string
     */
    protected function _getSiteId()
    {
        $siteId = Mage::helper('salesfire')->getSiteId();
        return trim($siteId);
    }

    /**
     * Is salesfire available
     *
     * @return bool
     */
    protected function _isAvailable()
    {
        return Mage::helper('salesfire')->isAvailable();
    }

    /**
     * Render salesfire scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_isAvailable()) {
            return '';
        }

        return Mage::helper('salesfire')->getScriptTag();
    }
}
