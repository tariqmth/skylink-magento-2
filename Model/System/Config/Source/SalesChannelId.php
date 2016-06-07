<?php

namespace RetailExpress\SkyLinkMagento2\Model\System\Config\Source;

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
            $options[] = ['value' => $salesChannelId, 'label' => __($salesChannelId)];
        }

        return $options;
    }
}
