<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductPriceAttribute as SkyLinkProductPriceAttribute;

class ProductPriceAttribute implements ArrayInterface
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
                'label' => SkyLinkProductPriceAttribute::get($value)->getLabel(),
            ];
        }, SkyLinkProductPriceAttribute::getConstants());
    }
}
