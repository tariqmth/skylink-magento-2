<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType as SkyLinkPriceGroupType;

class PurgingChance implements ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        $options = [];

        for ($threshold = 0.1; $threshold <= 1; $threshold += 0.1) {
            $options[] = [
                'value' => (string) $threshold,
                'label' => __('%1%', $threshold * 100),
            ];
        }

        return $options;
    }
}
