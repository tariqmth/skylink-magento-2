<?php

namespace RetailExpress\SkyLink\Api\Sales\Orders;

interface SkyLinkGuestCustomerServiceInterface
{
    /**
     * Returns the Guest Customer ID.
     *
     * @return \RetailExpress\SkyLink\Sdk\Customers\CustomerId
     */
    public function getGuestCustomerId();
}
