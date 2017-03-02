<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductNameAttribute as SkyLinkProductNameAttribute;

class ProductNameAttribute implements ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return array_map(function ($value) {
            return [
                'value' => $value,
                'label' => SkyLinkProductNameAttribute::get($value)->getLabel(),
            ];
        }, SkyLinkProductNameAttribute::getConstants());
    }
}
