<?php

/**
 * Salesfire Feed URL Block
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version    1.2.0
 */
class Salesfire_Salesfire_Block_Adminhtml_System_Config_Feed extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Render Information element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $code = Mage::getSingleton('adminhtml/config_data')->getStore();
        $storeId = Mage::getModel('core/store')->load($code)->getId();

        if (Mage::helper('salesfire')->isAvailable($storeId)) {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/'.Mage::helper('salesfire')->getSiteId($storeId).'.xml';
            return '<p>The feed will be generated at midnight each day, you can find it located here: <a href="'.$url.'">'.$url.'</a></p>';
        }

        return '<p>The feed will be generated at midnight each day.</p>';
    }
}
