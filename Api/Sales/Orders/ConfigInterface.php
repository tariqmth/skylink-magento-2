<?php

namespace RetailExpress\SkyLink\Api\Sales\Orders;

interface ConfigInterface
{
    /**
     * Returns the threshold used for recaching bulk order calls.
     *
     * @return \RetailExpress\SkyLink\Sdk\V2OrderShim\RecacheThreshold
     */
    public function getBulkOrderRecacheThreshold();
}
