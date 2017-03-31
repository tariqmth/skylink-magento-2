<?php

namespace RetailExpress\SkyLink\Commands\Sales\Payments;

use RetailExpress\CommandBus\Api\Queues\NormallyQueuedCommand;
use RetailExpress\CommandBus\Api\Queues\QueueableCommand;

class CreateSkyLinkPaymentFromMagentoInvoiceCommand extends NormallyQueuedCommand
{
    public $magentoInvoiceId;

    /**
     * Get the queue this command belongs to.
     *
     * @return string
     */
    public function getQueue()
    {
        return 'payments';
    }

    /**
     * Get the name of the command on the queue.
     *
     * @return string
     */
    public function getName()
    {
        return 'create_sylink_payment_from_magento_invoice';
    }
}
