<?php

namespace RetailExpress\SkyLinkMagento2\Commands;

use League\Tactician\Bernard\QueueableCommand;

class SyncCustomerCommand implements QueueableCommand
{
    public $retailExpressCustomerId;

    public function getName()
    {
        return 'sync_customer';
    }
}
