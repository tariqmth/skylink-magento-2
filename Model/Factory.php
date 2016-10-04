<?php

namespace RetailExpress\SkyLink\Model;

use ValueObjects\Number\Integer;

trait Factory
{
    private function assertV2Api(Integer $apiVersion)
    {
        if (!$apiVersion->sameValueAs(new Integer(2))) {
            throw new UnexpectedValueException("Only supported version of the Retail Express API is the V2 API.");
        }
    }
}
