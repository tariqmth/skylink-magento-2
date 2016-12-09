<?php

namespace RetailExpress\SkyLink\Api\Sales\Payments;

interface ConfigInterface
{
    /**
     * Returns the cache time for payments.
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getCacheTime();
}
