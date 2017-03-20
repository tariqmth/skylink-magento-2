<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Sdk\Sales\Orders\ItemDeliveryMethod as SkyLinkItemDeliveryMethod;

class ItemDeliveryMethod implements ArrayInterface
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
                'label' => SkyLinkItemDeliveryMethod::get($value)->getLabel(),
            ];
        }, SkyLinkItemDeliveryMethod::getNonAutomaticFulfilling());
    }
}
