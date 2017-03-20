<?php

namespace RetailExpress\SkyLink\Api\Sales\Orders;

use RetailExpress\SkyLink\Sdk\Sales\Orders\OrderId as SkyLinkOrderId;

interface MagentoOrderRepositoryInterface
{
    /**
     * Gets a list of active Magento Orders that have a SkyLink Order ID.
     *
     * @return \Magento\Sales\Api\Data\OrderInterface[]
     */
    public function getListOfActiveWithSkyLinkOrderIds();

    /**
     * Finds a Magento Order by the given SkyLink Order ID.
     *
     * @param SkyLinkOrderId $skyLinkOrderId
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     *
     * @throws \RetailExpress\SkyLink\Exceptions\Sales\Orders\NoMagentoOrderForSkyLinkOrderIdException
     */
    public function findBySkyLinkOrderId(SkyLinkOrderId $skyLinkOrderId);
}
