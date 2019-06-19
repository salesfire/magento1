<?php

/**
 * Salesfire Index
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version    1.2.0
 */
class Salesfire_Salesfire_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        echo implode(',', [
            '1.2.0',
            Mage::helper('salesfire')->isEnabled() ? '1' : '0',
            Mage::helper('salesfire')->getSiteId(),
            Mage::helper('salesfire')->isFeedEnabled() ? '1' : '0',
        ]);
        exit;
    }
}
