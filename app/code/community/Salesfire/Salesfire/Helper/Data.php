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
     * @param mixed $store
     * @return bool
     */
    public function isAvailable($store = null)
    {
        $siteId = Mage::getStoreConfig(self::XML_PATH_GENERAL_SITE_ID, $store);
        return $siteId && Mage::getStoreConfigFlag(self::XML_PATH_GENERAL_ENABLED, $store);
    }

    /**
     * Get salesfire site id
     *
     * @param string $store
     * @return string
     */
    public function getSiteId($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_GENERAL_SITE_ID, $store);
    }

    /**
     * Get script tag
     *
     * @param string $store
     * @return string
     */
    public function getScriptTag($store = null)
    {
        $formatter = new \Salesfire\Formatter($this->getSiteId());

        // Display transaction (set by Salesfire_Salesfire_Model_Observer)
        $ids = Mage::registry('salesfire_order_ids');
        $orderId = (is_array($ids) ? reset($ids) : null);
        if (! empty($orderId)) {
            $order = Mage::getModel('sales/order')->load($orderId);

            $transaction = new \Salesfire\Types\Transaction([
                'id' => $order->entity_id,
                'shipping' => round($order->getShippingAmount(), 2),
                'currency' => $order->getOrderCurrencyCode(),
                'coupon'   => $order->getCouponCode(),
            ]);

            foreach ($order->getAllVisibleItems() as $product) {
                $transaction->addProduct(new \Salesfire\Types\Product([
                    'sku'        => $product->getProductId(),
                    'parent_sku' => $product->getProductId(),
                    'name'       => $product->getName(),
                    'price'      => round($product->getPrice(), 2),
                    'tax'        => round($product->getTaxAmount(), 2),
                    'quantity'   => round($product->getQtyOrdered()),
                    'variant'    => implode(", ", array_map(function($item) {return $item['label'].': '.$item['value'];}, $product->getProductOptions()['attributes_info']))
                ]));
            }

            $formatter->addTransaction($transaction);
        }

        // Display product view (set by Salesfire_Salesfire_Model_Observer)
        $ids = Mage::registry('salesfire_product_ids');
        $productId = (is_array($ids) ? reset($ids) : null);
        if (! empty($productId)) {
            $product = Mage::getModel('catalog/product')->load($productId);

            // Calculate product tax
            $price = round(Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), false), 2);
            $tax = round(Mage::helper('tax')->getPrice($product, $product->getFinalPrice(), true), 2) - $price;

            $formatter->addProductView(new \Salesfire\Types\Product([
                'sku'        => $product->getId(),
                'parent_sku' => $product->getId(),
                'name'       => $product->getName(),
                'price'      => $price,
                'tax'        => $tax,
            ]));
        }

        return $formatter->toScriptTag();
    }
}
