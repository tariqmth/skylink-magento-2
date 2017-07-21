<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use ValueObjects\Enum\Enum;

class QuantityCalculation extends Enum
{
    const AVAILABLE = 'available';
    const AVAILABLE_ON_ORDER = 'available_on_order';

    public function getLabel()
    {
        $labels = [
            self::AVAILABLE => 'Available',
            self::AVAILABLE_ON_ORDER => 'Available Plus On Order',
        ];

        return $labels[$this->getValue()];
    }
}
