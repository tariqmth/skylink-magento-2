<?php

namespace RetailExpress\SkyLink\Api\Sales\Payments;

use Magento\Payment\Model\MethodInterface;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethodId as SkyLinkPaymentMethodId;

interface SkyLinkPaymentMethodRepositoryInterface
{
    /**
     * Get all Payment Methods available in Retail Express.
     *
     * @return \RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethod[]
     */
    public function getList();

    /**
     * Get the given Payment Method.
     *
     * @param SkyLinkPaymentMethodId $paymentMethodId
     *
     * @return \RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethod
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(SkyLinkPaymentMethodId $paymentMethodId);

    /**
     * Gets the mapped SkyLink Payment Method for the Magento Payment Method.
     *
     * @param MethodInterface $magentoPaymentMethod
     *
     * @return \RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethod|null
     */
    public function getSkyLinkPaymentMethodForMagentoPaymentMethod(MethodInterface $magentoPaymentMethod);
}
