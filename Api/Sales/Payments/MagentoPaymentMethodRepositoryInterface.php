<?php

namespace RetailExpress\SkyLink\Api\Sales\Payments;

interface MagentoPaymentMethodRepositoryInterface
{
    /**
     * Get a list of all available payment methods.
     *
     * @return \Magento\Payment\Model\MethodInterface[]
     */
    public function getList();

    /**
     * Gets the given payment method.
     *
     * @return \Magento\Payment\Model\MethodInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($magentoPaymentMethodCode);
}
