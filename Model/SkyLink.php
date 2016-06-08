<?php

namespace RetailExpress\SkyLinkMagento2\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManager\ConfigInterface as ObjectManagerConfigInterface;
use ValueObjects\Number\Integer;
use RetailExpress\SkyLink\Apis\V2 as V2Api;
use RetailExpress\SkyLink\Catalogue\Attributes\V2AttributeRepository;
use RetailExpress\SkyLink\Catalogue\Products\MatrixPolicyMapper;
use RetailExpress\SkyLink\Catalogue\Products\V2ProductRepository;
use RetailExpress\SkyLink\Customers\V2CustomerRepository;
use RetailExpress\SkyLink\Outlets\V2OutletRepository;
use RetailExpress\SkyLink\Sales\Orders\V2OrderRepository;
use RetailExpress\SkyLink\Sales\Payments\V2PaymentMethodRepository;
use RetailExpress\SkyLink\ValueObjects\SalesChannelId;
use RetailExpress\SkyLink\Vouchers\V2VoucherRepository;

/**
 * @todo Add DI - it isn't playing nicely with value objects so just hardcoding instead.
 * @todo This class is a factory class but also an accessor for config values, maybe this should be split?
 */
class SkyLink
{
    private $scopeConfig;

    private $instanceName;

    public function __construct(ScopeConfigInterface $scopeConfig, $instanceName = SkyLink::class)
    {
        $this->scopeConfig = $scopeConfig;
        $this->instanceName = $instanceName;
    }

    /**
     * Return an instance of the appropriate Catalogue Attribute Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCatalogueAttributeRepository()
    {
        return $this->onValidApiVersion(function (Integer $apiVersion) {
            if ($apiVersion->sameValueAs(new Integer(2))) {
                return new V2AttributeRepository($this->createV2Api());
            }
        });
    }

    /**
     * Return an instance of the appropriate Catalogue Product Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCatalogueProductRepository()
    {
        return $this->onValidApiVersion(function (Integer $apiVersion) {
            if ($apiVersion->sameValueAs(new Integer(2))) {
                return new V2ProductRepository(new MatrixPolicyMapper(), $this->createV2Api());
            }
        });
    }

    /**
     * Return an instance of the appropriate Customer Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createCustomerRepository()
    {
        return $this->onValidApiVersion(function (Integer $apiVersion) {
            if ($apiVersion->sameValueAs(new Integer(2))) {
                return new V2CustomerRepository($this->createV2Api());
            }
        });
    }

    /**
     * Return an instance of the appropriate Outlet Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createOutletRepository()
    {
        return $this->onValidApiVersion(function (Integer $apiVersion) {
            if ($apiVersion->sameValueAs(new Integer(2))) {
                return new V2OutletRepository($this->createV2Api());
            }
        });
    }

    /**
     * Return an instance of the appropriate Sales Order Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createSalesOrderRepository()
    {
        return $this->onValidApiVersion(function (Integer $apiVersion) {
            if ($apiVersion->sameValueAs(new Integer(2))) {
                return new V2OrderRepository($this->createV2Api());
            }
        });
    }

    /**
     * Return an instance of the appropriate Sales Payment Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createSalesPaymentMethodRepository()
    {
        return $this->onValidApiVersion(function (Integer $apiVersion) {
            if ($apiVersion->sameValueAs(new Integer(2))) {
                return new V2PaymentMethodRepository($this->createV2Api());
            }
        });
    }

    /**
     * Return an instance of the appropriate Voucher Repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function createVoucherRepository()
    {
        return $this->onValidApiVersion(function (Integer $apiVersion) {
            if ($apiVersion->sameValueAs(new Integer(2))) {
                return new V2VoucherRepository($this->createV2Api());
            }
        });
    }

    /**
     * Return an instance of the appropriate catalogue product repository according to store configuration.
     *
     * @return \RetailExpress\SkyLink\Catalogue\Attributes\AttributeRepository
     */
    public function getSalesChannelId()
    {
        return new SalesChannelId($this->scopeConfig->getValue('skylink/general/sales_channel_id'));
    }

    /**
     * Assert a valid, supported API version is configured and run the provided callback against that API version.
     *
     * @param  callable $callback
     * @return mixed
     */
    private function onValidApiVersion(callable $callback)
    {
        $apiVersion = $this->getApiVersion();

        if (!$apiVersion->sameValueAs(new Integer(2))) {
            throw new InvalidArgumentException('Only supported version of the Retail Express API is version 2.');
        }

        return $callback($apiVersion);
    }

    /**
     * Get the API version as configured.
     *
     * @return Integer
     */
    private function getApiVersion()
    {
        return new Integer($this->scopeConfig->getValue('skylink/api/version'));
    }

    /**
     * Create a V2 API object that is used throughout V2 API repositories.
     *
     * @return V2Api
     */
    private function createV2Api()
    {
        return V2Api::fromNative(
            $this->scopeConfig->getValue('skylink/api/version_2_url'),
            $this->scopeConfig->getValue('skylink/api/version_2_client_id'),
            $this->scopeConfig->getValue('skylink/api/version_2_username'),
            $this->scopeConfig->getValue('skylink/api/version_2_password')
        );
    }
}
