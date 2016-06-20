<?php

namespace RetailExpress\SkyLink\Model;

use RetailExpress\SkyLink\Catalogue\Attributes\V2AttributeRepository;
use RetailExpress\SkyLink\Catalogue\Products\MatrixPolicyMapper;
use RetailExpress\SkyLink\Catalogue\Products\V2ProductRepository;
use RetailExpress\SkyLink\Customers\V2CustomerRepository;
use RetailExpress\SkyLink\Outlets\V2OutletRepository;
use RetailExpress\SkyLink\Sales\Orders\V2OrderRepository;
use RetailExpress\SkyLink\Sales\Payments\V2PaymentMethodRepository;
use RetailExpress\SkyLink\Vouchers\V2VoucherRepository;

/**
 * @todo Add DI - it isn't playing nicely with value objects so just hardcoding instead.
 */
class RepositoryFactory
{
    private $apiFactory;

    public function __construct(ApiFactory $apiFactory)
    {
        $this->apiFactory = $apiFactory;
    }

    /**
     * Get an instance of the appropriate Catalogue Attribute Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCatalogueAttributeRepository()
    {
        $this->apiFactory->assertValidApiVersion();

        if ($this->apiFactory->isV2Api()) {
            return new V2AttributeRepository($this->apiFactory->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Catalogue Product Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCatalogueProductRepository()
    {
        $this->apiFactory->assertValidApiVersion();

        if ($this->apiFactory->isV2Api()) {
            return new V2ProductRepository(
                new MatrixPolicyMapper(),
                $this->apiFactory->createV2Api()
            );
        }
    }

    /**
     * Get an instance of the appropriate Customer Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCustomerRepository()
    {
        $this->apiFactory->assertValidApiVersion();

        if ($this->apiFactory->isV2Api()) {
            return new V2CustomerRepository($this->apiFactory->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Outlet Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createOutletRepository()
    {
        $this->apiFactory->assertValidApiVersion();

        if ($this->apiFactory->isV2Api()) {
            return new V2OutletRepository($this->apiFactory->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Sales Order Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createSalesOrderRepository()
    {
        $this->apiFactory->assertValidApiVersion();

        if ($this->apiFactory->isV2Api()) {
            return new V2OrderRepository($this->apiFactory->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Sales Payment Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createSalesPaymentMethodRepository()
    {
        $this->apiFactory->assertValidApiVersion();

        if ($this->apiFactory->isV2Api()) {
            return new V2PaymentMethodRepository($this->apiFactory->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Voucher Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createVoucherRepository()
    {
        $this->apiFactory->assertValidApiVersion();

        if ($this->apiFactory->isV2Api()) {
            return new V2VoucherRepository($this->apiFactory->createV2Api());
        }
    }
}
