<?php

namespace Salesfire;

use Salesfire\Types\Transaction;
use Salesfire\Types\Product;

class Formatter
{
    /**
     * @var array The site identifier
     */
    public $uuid;

    /**
     * @var array The events to format
     */
    public $events = array();

    /**
     * @param string The site identifier
     * @return \Salesfire\Formatter
     */
    public function __construct($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param \Salesfire\Types\Transaction $transaction
     * @return \Salesfire\Formatter
     */
    public function addTransaction(Transaction $transaction)
    {
        if (! isset($this->events['ecommerce'])) {
            $this->events['ecommerce'] = array();
        }

        $this->events['ecommerce']['purchase'] = $transaction->toArray();

        return $this;
    }

    /**
     * @param \Salesfire\Types\Product $product
     * @return \Salesfire\Formatter
     */
    public function addProductView(Product $product)
    {
        if (! isset($this->events['ecommerce'])) {
            $this->events['ecommerce'] = array();
        }

        $this->events['ecommerce']['view'] = $product->toArray();

        return $this;
    }

    /**
     * @param \Salesfire\Types\Product $product
     * @return \Salesfire\Formatter
     */
    public function addBasketAdd(Product $product)
    {
        if (! isset($this->events['ecommerce'])) {
            $this->events['ecommerce'] = array();
        }

        $this->events['ecommerce']['add'] = $product->toArray();

        return $this;
    }

    /**
     * @param \Salesfire\Types\Product $product
     * @return \Salesfire\Formatter
     */
    public function addBasketRemove(Product $product)
    {
        if (! isset($this->events['ecommerce'])) {
            $this->events['ecommerce'] = array();
        }

        $this->events['ecommerce']['remove'] = $product->toArray();

        return $this;
    }

    public function __toArray()
    {
        return $this->events;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->events, JSON_PRETTY_PRINT);
    }

    /**
     * @return string
     */
    public function toScriptTag()
    {
        $script = '';

        if (! empty($this->events)) {
            $script .= "<script>\n";
            $script .= "sfDataLayer = window.sfDataLayer || [];\n";
            $script .= "sfDataLayer.push(" . $this->toJson() . ");\n";
            $script .= "</script>\n";
        }

        $script .= "<script async src='https://cdn.salesfire.co.uk/code/" . $this->getUuid() . ".js'></script>\n";

        return $script;
    }

}
