<?php

namespace RetailExpress\SkyLink\Api\Sales\Payments;

use Magento\Payment\Model\MethodInterface;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethod as SkyLinkPaymentMethod;

interface SkyLinkPaymentMethodServiceInterface
{
    /**
     * Maps the given Magento Payment Method to the given SkyLink Payment Method.
     *
     * @param SkyLinkPaymentMethod $skyLinkPaymentMethod
     * @param MethodInterface      $magentoPaymentMethod
     */
    public function mapSkyLinkPaymentMethodForMagentoPaymentMethod(
        SkyLinkPaymentMethod $skyLinkPaymentMethod,
        MethodInterface $magentoPaymentMethod
    );
}
