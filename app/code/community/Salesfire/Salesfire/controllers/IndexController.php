<?php

/**
 * Salesfire Index
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version    1.2.2
 */
class Salesfire_Salesfire_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->setFlag('', self::FLAG_NO_POST_DISPATCH, 1);
        $response = $this->getResponse();
        $response->setBody(implode(',', [
            '1.2.2',
            Mage::helper('salesfire')->isEnabled() ? '1' : '0',
            Mage::helper('salesfire')->getSiteId(),
            Mage::helper('salesfire')->isFeedEnabled() ? '1' : '0',
            Mage::getVersion(),
        ]));
    }
}
