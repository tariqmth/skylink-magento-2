<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

interface ConfigInterface
{
    /**
     * Returns the cache time for attributes.
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getCacheTime();
}
