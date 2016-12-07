<?php

namespace RetailExpress\SkyLink\Plugin\Sales\Invoices;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\InvoiceInterface;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Commands\Sales\Payments\CreateSkyLinkPaymentFromMagentoInvoiceCommand;

// @todo Stop wrapping the Invoice class directly as soon
// as Magento starts to use the InvoiceRepository for
// adding new invoices. This works, but isn't nice.
class InvoicePlugin
{
    /**
     * The command bus.
     *
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * Create a new Invoice Plugin.
     *
     * @param ResourceConnection  $resourceConnection
     * @param CommandBusInterface $commandBus
     */
    public function __construct(CommandBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * Around an invoice being saved.
     */
    public function aroundSave(InvoiceInterface $magentoInvoice, callable $proceed)
    {
        // Determine if we're new
        $isNew = !$magentoInvoice->getEntityId();

        // Call the original save method
        $magentoInvoice = $proceed();

        // Send a payment for the given invoice to Retail Express for new invoices
        if (true === $isNew) {
            $command = new CreateSkyLinkPaymentFromMagentoInvoiceCommand();
            $command->magentoInvoiceId = $magentoInvoice->getEntityId();
            $this->commandBus->handle($command);
        }

        return $magentoInvoice;
    }
}
