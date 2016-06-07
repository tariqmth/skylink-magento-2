<?php

namespace RetailExpress\SkyLinkMagento2\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ApiVersion implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 2, 'label' => __('2')],
        ];
    }
}
