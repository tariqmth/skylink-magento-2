<?php

namespace RetailExpress\SkyLink\Commands;

use League\Tactician\Bernard\QueueableCommand;

class SyncCustomerCommand implements QueueableCommand
{
    public $retailExpressCustomerId;

    public function getName()
    {
        return 'sync_customer';
    }
}
