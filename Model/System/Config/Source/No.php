<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Config\Model\Config\Source\Yesno;

class No extends Yesno implements ArrayInterface
{
    public function toOptionArray()
    {
        return array_values(array_filter(
            parent::toOptionArray(),
            function ($option) {
                return 0 === $option['value'];
            }
        ));
    }

    public function toArray()
    {
        return array_intersect_key(parent::toArray(), array_flip([0]));
    }
}
