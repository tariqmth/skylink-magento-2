<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Products;

use OutOfRangeException;
use ValueObjects\Number\Real;

class ConfigurableProductMatchThreshold extends Real
{
    public function __construct($value)
    {
        parent::__construct($value);

        if ($this->value < 0.5 || $this->value > 1) {
            throw new OutOfRangeException("Configurable Product Match Threshold should be between 0.5 and 1, {$this->value} given.");
        }
    }
}
