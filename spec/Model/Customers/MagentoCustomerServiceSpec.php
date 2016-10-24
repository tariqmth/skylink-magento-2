<?php

namespace spec\RetailExpress\SkyLink\Model\Customers;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerMapperInterface;
use RetailExpress\SkyLink\Model\Customers\MagentoCustomerService;

class MagentoCustomerServiceSpec extends ObjectBehavior
{
    private $magentoAccountManagement;

    private $magentoCustomerRepository;

    private $magentoCustomerFactory;

    private $magentoAddressFactory;

    private $magentoCustomerMapper;

    public function let(
        AccountManagementInterface $magentoAccountManagement,
        CustomerRepositoryInterface $magentoCustomerRepository,
        CustomerInterfaceFactory $magentoCustomerFactory,
        AddressInterfaceFactory $magentoAddressFactory,
        MagentoCustomerMapperInterface $magentoCustomerMapper
    ) {
        $this->magentoAccountManagement = $magentoAccountManagement;
        $this->magentoCustomerRepository = $magentoCustomerRepository;
        $this->magentoCustomerFactory = $magentoCustomerFactory;
        $this->magentoAddressFactory = $magentoAddressFactory;
        $this->magentoCustomerMapper = $magentoCustomerMapper;

        $this->beConstructedWith(
            $this->magentoAccountManagement,
            $this->magentoCustomerRepository,
            $this->magentoCustomerFactory,
            $this->magentoAddressFactory,
            $this->magentoCustomerMapper
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MagentoCustomerService::class);
    }

    public function it_registers_a_magento_customer(
        SkyLinkCustomer $skyLinkCustomer,
        CustomerInterface $magentoCustomer,
        AddressInterface $magentoBillingAddress,
        AddressInterface $magentoShippingAddress
    ) {
        $this->magentoCustomerFactory->create()->shouldBeCalled()->willReturn($magentoCustomer);
        $this->magentoAddressFactory->create()->shouldBeCalled()->willReturn(
            $magentoBillingAddress,
            $magentoShippingAddress
        );

        $magentoBillingAddress->setIsDefaultBilling(true)->shouldBeCalled();
        $magentoShippingAddress->setIsDefaultShipping(true)->shouldBeCalled();

        $magentoCustomer->setAddresses([$magentoBillingAddress, $magentoShippingAddress])->shouldBeCalled();

        $this->magentoCustomerMapper->mapMagentoCustomer($magentoCustomer, $skyLinkCustomer)->shouldBeCalled();

        $this->registerMagentoCustomer($skyLinkCustomer);
    }

    public function it_updates_a_magento_customer_with_default_addresses(
        SkyLinkCustomer $skyLinkCustomer,
        CustomerInterface $magentoCustomer
    ) {
        $magentoCustomer->getDefaultBilling()->willReturn('1');
        $magentoCustomer->getDefaultShipping()->willReturn('2');

        $this->magentoCustomerMapper->mapMagentoCustomer($magentoCustomer, $skyLinkCustomer)->shouldBeCalled();

        $this->magentoCustomerRepository->save($magentoCustomer)->shouldBeCalled();

        $this->updateMagentoCustomer($magentoCustomer, $skyLinkCustomer);
    }

    public function it_provides_default_addresses_when_updating_a_magento_customer(
        SkyLinkCustomer $skyLinkCustomer,
        CustomerInterface $magentoCustomer,
        AddressInterface $magentoBillingAddress,
        AddressInterface $magentoShippingAddress
    ) {
        $magentoCustomer->getDefaultBilling()->willReturn(null);
        $magentoCustomer->getDefaultShipping()->willReturn(null);

        $this->magentoAddressFactory->create()->shouldBeCalled()->willReturn(
            $magentoBillingAddress,
            $magentoShippingAddress
        );

        $magentoBillingAddress->setIsDefaultBilling(true)->shouldBeCalled();
        $magentoShippingAddress->setIsDefaultShipping(true)->shouldBeCalled();

        $magentoCustomer->getAddresses()->willReturn([]);
        $magentoCustomer->setAddresses([$magentoBillingAddress, $magentoShippingAddress])->shouldBeCalled();

        $this->magentoCustomerMapper->mapMagentoCustomer($magentoCustomer, $skyLinkCustomer)->shouldBeCalled();

        $this->magentoCustomerRepository->save($magentoCustomer)->shouldBeCalled();

        $this->updateMagentoCustomer($magentoCustomer, $skyLinkCustomer);
    }
}
