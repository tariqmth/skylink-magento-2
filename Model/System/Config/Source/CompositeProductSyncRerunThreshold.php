<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType as SkyLinkPriceGroupType;

class CompositeProductSyncRerunThreshold implements ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => 0,
                'label' => __('Never Skip'),
            ],
        ];

        for ($threshold = 5; $threshold <= 90; $threshold += 5) {
            $options[] = [
                'value' => (string) $threshold * 60,
                'label' => __('Skip for %1 minutes', $threshold),
            ];
        }

        return $options;
    }
}
