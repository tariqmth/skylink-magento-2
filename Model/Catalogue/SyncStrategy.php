<?php

namespace RetailExpress\SkyLink\Model\Catalogue;

use ValueObjects\Enum\Enum;

class SyncStrategy extends Enum
{
    const INITIAL = 'initial';
    const ALWAYS = 'always';

    public function getLabel()
    {
        $labels = [
            self::INITIAL => 'Initial',
            self::ALWAYS => 'Always',
        ];

        return $labels[$this->getValue()];
    }
}
