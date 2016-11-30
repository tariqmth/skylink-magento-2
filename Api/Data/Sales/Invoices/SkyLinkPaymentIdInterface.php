<?php

namespace RetailExpress\SkyLink\Api\Data\Sales\Invoices;

use Magento\Framework\Api\ExtensionAttributesInterface;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentId as SkyLinkPaymentId;

interface SkyLinkPaymentIdInterface extends ExtensionAttributesInterface
{
    /**
     * Gets the SkyLink Payment ID.
     *
     * @return SkyLinkPaymentId
     */
    public function getSkyLinkPaymentId();

    /**
     * Sets the SkyLink Payment ID.
     *
     * @param SkyLinkPaymentId $skyLinkPaymentId
     */
    public function setSkyLinkPaymentId(SkyLinkPaymentId $skyLinkPaymentId);
}
