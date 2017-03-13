<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Sdk\Customers\PriceGroups\PriceGroupType as SkyLinkPriceGroupType;

class ConfigurableProductMatchThreshold implements ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        $options = [];

        for ($threshold = 0.25; $threshold <= 0.75; $threshold += 0.05) {
            $options[] = [
                'value' => (string) $threshold, // Need a string for the default value to work
                'label' => __('%1%', $threshold * 100),
            ];
        }

        return $options;
    }
}
