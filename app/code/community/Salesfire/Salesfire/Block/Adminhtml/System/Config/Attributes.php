<?php

/**
 * Salesfire Admin Attribute Dropdowns
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version    1.2.7
 */
class Salesfire_Salesfire_Block_Adminhtml_System_Config_Attributes
{
    public function toOptionArray($withEmpty = true)
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->addVisibleFilter()
            ->getItems();

        $options = array_map(function($attribute) {
            return [
                'value' => $attribute->attribute_code,
                'label' => $attribute->frontend_label . ' (' . $attribute->attribute_code . ')',
            ];
        }, $attributes);

        return [
            '' => '(None)',
        ] + $options;
    }
}
