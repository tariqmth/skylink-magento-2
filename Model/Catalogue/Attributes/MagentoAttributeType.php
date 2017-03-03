<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use ValueObjects\Enum\Enum;

class MagentoAttributeType extends Enum
{
    const FREEFORM = 'freeform';
    const CONFIGURABLE = 'configurable';

    public function usesOptions()
    {
        return $this->getValue() === 'configurable';
    }

    public function getLabel()
    {
        $labels = [
            self::FREEFORM => __('Freeform Text-Based'),
            self::CONFIGURABLE => __('Used for Configurable Products'),
        ];

        return $labels[$this->getValue()];
    }
}
