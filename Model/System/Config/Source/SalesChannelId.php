<?php

namespace RetailExpress\SkyLinkMagento2\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class SalesChannelId implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];

        foreach (range(1, 100) as $salesChannelId) {
            $options[] = ['value' => $salesChannelId, 'lable' => __($salesChannelId)];
        }

        return $options;
    }
}
