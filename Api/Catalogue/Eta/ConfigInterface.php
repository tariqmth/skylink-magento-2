<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Eta;

interface ConfigInterface
{
    /**
     * Returns whether ETA can be used.
     *
     * @return bool
     */
    public function canUse();
}
