<?php

/**
 * Salesfire Page Block
 *
 * @category   Salesfire
 * @package    Salesfire_Salesfire
 * @version.   1.1.2
 */
class Salesfire_Salesfire_Block_Script extends Mage_Core_Block_Template
{
    protected $order;
    protected $product;

    /**
     * Get current order
     *
     * @return order
     */
    protected function getOrder()
    {
        if ($this->order) {
            return $this->order;
        }

        $ids = Mage::registry('salesfire_order_ids');
        $orderId = (is_array($ids) ? reset($ids) : null);

        if (empty($orderId)) {
            return null;
        }

        return $this->order = Mage::getModel('sales/order')->load($orderId);
    }

    /**
     * Get current product
     *
     * @return product
     */
    protected function getProduct()
    {
        if ($this->product) {
            return $this->product;
        }

        $ids = Mage::registry('salesfire_product_ids');
        $productId = (is_array($ids) ? reset($ids) : null);
        if (empty($productId)) {
            return null;
        }

        return $this->product = Mage::getModel('catalog/product')->load($productId);
    }

    /**
     * Render salesfire scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (! Mage::helper('salesfire')->isAvailable()) {
            return '';
        }

        $formatter = new \Salesfire\Formatter(Mage::helper('salesfire')->getSiteId());

        // Display transaction (set by Salesfire_Salesfire_Model_Observer)
        if ($order = $this->getOrder()) {
            $transaction = new \Salesfire\Types\Transaction([
                'id'       => $order->getEntityId(),
                'shipping' => round($order->getShippingAmount(), 2),
                'currency' => $order->getOrderCurrencyCode(),
                'coupon'   => $order->getCouponCode(),
            ]);

            foreach ($order->getAllVisibleItems() as $product) {
                $variant = '';
                $options = $product->getProductOptions();
                if (!empty($options) && !empty($options['attribute_info'])) {
                    $variant = implode(', ', array_map(function ($item) {
                        return $item['label'].': '.$item['value'];
                    }, $options);
                }

                $transaction->addProduct(new \Salesfire\Types\Product([
                    'sku'        => $product->getProductId(),
                    'parent_sku' => $product->getProductId(),
                    'name'       => $product->getName(),
                    'price'      => round($product->getPrice(), 2),
                    'tax'        => round($product->getTaxAmount(), 2),
                    'quantity'   => round($product->getQtyOrdered()),
                    'variant'    => $variant,
                ]));
            }

            $formatter->addTransaction($transaction);
        }

        // Display product view (set by Salesfire_Salesfire_Model_Observer)
        if ($product = $this->getProduct()) {

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
