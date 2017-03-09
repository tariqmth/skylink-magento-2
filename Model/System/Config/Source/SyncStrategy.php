<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Model\Catalogue\SyncStrategy as BaseSyncStrategy;

class SyncStrategy implements ArrayInterface
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
                'label' => BaseSyncStrategy::get($value)->getLabel(),
            ];
        }, BaseSyncStrategy::getConstants());
    }
}
