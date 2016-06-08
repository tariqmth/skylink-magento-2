<?php

namespace RetailExpress\SkyLinkMagento2\Model;

use RetailExpress\SkyLink\Apis\V2 as V2Api;
use RetailExpress\SkyLink\Catalogue\Attributes\V2AttributeRepository;
use RetailExpress\SkyLink\Catalogue\Products\MatrixPolicyMapper;
use RetailExpress\SkyLink\Catalogue\Products\V2ProductRepository;
use RetailExpress\SkyLink\Customers\V2CustomerRepository;
use RetailExpress\SkyLink\Outlets\V2OutletRepository;
use RetailExpress\SkyLink\Sales\Orders\V2OrderRepository;
use RetailExpress\SkyLink\Sales\Payments\V2PaymentMethodRepository;
use RetailExpress\SkyLink\Vouchers\V2VoucherRepository;
use ValueObjects\Number\Integer;

/**
 * @todo Add DI - it isn't playing nicely with value objects so just hardcoding instead.
 */
class SkyLink
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get an instance of the appropriate Catalogue Attribute Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCatalogueAttributeRepository()
    {
        $this->assertValidApiVersion();

        if ($this->isV2Api()) {
            return new V2AttributeRepository($this->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Catalogue Product Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCatalogueProductRepository()
    {
        $this->assertValidApiVersion();

        if ($this->isV2Api()) {
            return new V2ProductRepository(new MatrixPolicyMapper(), $this->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Customer Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCustomerRepository()
    {
        $this->assertValidApiVersion();

        if ($this->isV2Api()) {
            return new V2CustomerRepository($this->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Outlet Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createOutletRepository()
    {
        $this->assertValidApiVersion();

        if ($this->isV2Api()) {
            return new V2OutletRepository($this->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Sales Order Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createSalesOrderRepository()
    {
        $this->assertValidApiVersion();

        if ($this->isV2Api()) {
            return new V2OrderRepository($this->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Sales Payment Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createSalesPaymentMethodRepository()
    {
        $this->assertValidApiVersion();

        if ($this->isV2Api()) {
            return new V2PaymentMethodRepository($this->createV2Api());
        }
    }

    /**
     * Get an instance of the appropriate Voucher Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createVoucherRepository()
    {
        $this->assertValidApiVersion();

        if ($this->isV2Api()) {
            return new V2VoucherRepository($this->createV2Api());
        }
    }

    /**
     * Assert a valid, supported API version is configured and run the provided callback against that API version.
     *
     * @return mixed
     */
    private function assertValidApiVersion()
    {
        if (!$this->isV2Api()) {
            throw new InvalidArgumentException('Only supported version of the Retail Express API is the V2 API.');
        }
    }

    /**
     * Get the API version as configured.
     *
     * @return int
     */
    private function getApiVersion()
    {
        return $this->config->getApiVersion();
    }

    /**
     * Determine if the current API version is the V2 API
     *
     * @return bool
     */
    private function isV2Api()
    {
        return $this->config->getApiVersion()->sameValueAs(new Integer(2));
    }

    /**
     * Create a V2 API object that is used throughout V2 API repositories.
     *
     * @return V2Api
     */
    private function createV2Api()
    {
        return new V2Api(
            $this->config->getV2ApiUrl(),
            $this->config->getV2ApiClientId(),
            $this->config->getV2ApiUsername(),
            $this->config->getV2ApiPassword()
        );
    }
}
