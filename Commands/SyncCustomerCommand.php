<?php

namespace RetailExpress\SkyLinkMagento2\Commands;

class SyncCustomerCommand
{
    public $retailExpressCustomerId;

    public function __construct($retailExpressCustomerId)
    {
        $this->retailExpressCustomerId = $retailExpressCustomerId;
    }
}
