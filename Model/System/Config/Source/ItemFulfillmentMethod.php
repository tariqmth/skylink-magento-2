<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Sdk\Sales\Orders\ItemFulfillmentMethod as SkyLinkItemFulfillmentMethod;

class ItemFulfillmentMethod implements ArrayInterface
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
                'label' => SkyLinkItemFulfillmentMethod::get($value)->getLabel(),
            ];
        }, SkyLinkItemFulfillmentMethod::getNonAutomaticFulfilling());
    }
}
