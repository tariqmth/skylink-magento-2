<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class SalesChannelId implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return array_map(function ($salesChannelId) {
            return ['value' => $salesChannelId, 'label' => __($salesChannelId)];
        }, range(1, 100));
    }
}
