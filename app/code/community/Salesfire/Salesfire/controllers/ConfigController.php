<?php

/**
 * Salesfire Config
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version    1.2.13
 */
class Salesfire_Salesfire_ConfigController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, 1);
        $response = $this->getResponse();
        $response->setBody(json_encode(array(
            'version'           => Mage::helper('salesfire')->getVersion(),
            'is_enabled'        => Mage::helper('salesfire')->isEnabled() ? '1' : '0',
            'site_id'           => Mage::helper('salesfire')->getSiteId(),
            'is_feed_enabled'   => Mage::helper('salesfire')->isFeedEnabled() ? '1' : '0',
            'magento_version'   => Mage::getVersion(),
        )));
    }
}
