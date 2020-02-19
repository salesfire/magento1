<?php

namespace Salesfire\Types;

class Product
{

    /**
     * @var string The child product sku
     */
    public $sku;

    /**
     * @var string The parent product sku
     */
    public $parent_sku;

    /**
     * @var string The product name
     */
    public $name;

    /**
     * @var string The product variant eg. size, colour
     */
    public $variant;

    /**
     * @var string The product brand
     */
    public $brand;

    /**
     * @var string The products main category
     */
    public $category;

    /**
     * @var float The 2 decimal place product ex vat price
     */
    public $price;

    /**
     * @var float The 2 decimal place product tax
     */
    public $tax;

    /**
     * @var int The quantity of products
     */
    public $quantity;

    /**
     * @var string The currency code (ISO 4217)
     */
    public $currency;

    /**
     * @var string The coupon code
     */
    public $coupon;

    /**
     * @param array The data to be set from array
     * @return void
     */
    public function __construct(array $data = array())
    {
        foreach ($data as $key => $val) {
            $this->{$key} = $val;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string The sku to set
     * @return \Salesfire\Types\Product
     */
    public function setSku(string $sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return string
     */
    public function getParentSku()
    {
        return $this->parent_sku;
    }

    /**
     * @param string The parent sku to set
     * @return \Salesfire\Types\Product
     */
    public function setParentSku(string $parent_sku)
    {
        $this->parent_sku = $parent_sku;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string The name to set
     * @return \Salesfire\Types\Product
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * @param string The variant to set
     * @return \Salesfire\Types\Product
     */
    public function setVariant(string $variant)
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string The brand to set
     * @return \Salesfire\Types\Product
     */
    public function setBrand(string $brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string The category to set
     * @return \Salesfire\Types\Product
     */
    public function setCategory(string $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string The price to set
     * @return \Salesfire\Types\Product
     */
    public function setPrice(float $price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param string The tax to set
     * @return \Salesfire\Types\Product
     */
    public function setTax(float $tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string The quantity to set
     * @return \Salesfire\Types\Product
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * @param string The coupon to set
     * @return \Salesfire\Types\Product
     */
    public function setCoupon(string $coupon)
    {
        $this->coupon = $coupon;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string The currency to set
     * @return \Salesfire\Types\Product
     */
    public function setCurrency(string $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter(array(
            'sku'        => $this->sku,
            'parent_sku' => $this->parent_sku,
            'name'       => $this->name,
            'variant'    => $this->variant,
            'brand'      => $this->brand,
            'category'   => $this->category,
            'price'      => $this->price,
            'quantity'   => $this->quantity,
            'coupon'     => $this->coupon,
            'currency'   => $this->currency,
        ), function ($value) {
            return ! empty($value);
        });
    }

}
