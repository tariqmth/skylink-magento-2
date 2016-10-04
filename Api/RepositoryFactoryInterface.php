<?php

namespace RetailExpress\SkyLink\Api;

interface RepositoryFactoryInterface
{
    /**
     * Get an instance of the appropriate Catalogue Attribute Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCatalogueAttributeRepository();

    /**
     * Get an instance of the appropriate Catalogue Product Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCatalogueProductRepository();

    /**
     * Get an instance of the appropriate Customer Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCustomerRepository();

    /**
     * Get an instance of the appropriate Outlet Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createOutletRepository();

    /**
     * Get an instance of the appropriate Sales Order Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createSalesOrderRepository();

    /**
     * Get an instance of the appropriate Sales Payment Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createSalesPaymentMethodRepository();

    /**
     * Get an instance of the appropriate Voucher Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createVoucherRepository();
}
