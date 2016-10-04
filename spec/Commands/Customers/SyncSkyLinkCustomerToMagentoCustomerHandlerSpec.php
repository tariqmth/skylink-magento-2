<?php

namespace spec\RetailExpress\SkyLink\Commands\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Customers\CustomerNotFoundException;
use RetailExpress\SkyLink\Customers\CustomerRepository as SkylinkCustomerRepository;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerRepositoryInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerServiceInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkCustomerToMagentoCustomerCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkCustomerToMagentoCustomerHandler;

class SyncSkyLinkCustomerToMagentoCustomerHandlerSpec extends ObjectBehavior
{
    private $skyLinkCustomerRepository;

    private $magentoCustomerRepository;

    private $magentoCustomerService;

    private $skyLinkCustomerId;

    private $syncSkyLinkCustomerToMagentoCustomerCommand;

    public function let(
        SkylinkCustomerRepository $skyLinkCustomerRepository,
        MagentoCustomerRepositoryInterface $magentoCustomerRepository,
        MagentoCustomerServiceInterface $magentoCustomerService
    ) {
        $this->skyLinkCustomerRepository = $skyLinkCustomerRepository;
        $this->magentoCustomerRepository = $magentoCustomerRepository;
        $this->magentoCustomerService = $magentoCustomerService;

        $this->beConstructedWith(
            $this->skyLinkCustomerRepository,
            $this->magentoCustomerRepository,
            $this->magentoCustomerService
        );

        $this->skyLinkCustomerId = new SkyLinkCustomerId(124001);
        $this->syncSkyLinkCustomerToMagentoCustomerCommand = new SyncSkyLinkCustomerToMagentoCustomerCommand();
        $this->syncSkyLinkCustomerToMagentoCustomerCommand->skyLinkCustomerId = $this->skyLinkCustomerId->toNative();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SyncSkyLinkCustomerToMagentoCustomerHandler::class);
    }

    public function it_should_pass_on_exception_when_retrieving_non_existent_customer(
        CustomerNotFoundException $customerNotFoundException
    ) {
        $this->skyLinkCustomerRepository
            ->find($this->skyLinkCustomerId)
            ->willThrow(CustomerNotFoundException::class);

        $this->shouldThrow(CustomerNotFoundException::class)->duringHandle($this->syncSkyLinkCustomerToMagentoCustomerCommand);
    }

    public function it_should_update_an_existing_magento_customer_customer_if_one_is_found(
        SkyLinkCustomer $skyLinkCustomer,
        CustomerInterface $magentoCustomer
    ) {
        $this->skyLinkCustomerRepository->find($this->skyLinkCustomerId)->willReturn($skyLinkCustomer);
        $this
            ->magentoCustomerRepository
            ->findBySkyLinkCustomerId($this->skyLinkCustomerId)
            ->willReturn($magentoCustomer);

        $this->magentoCustomerService->updateMagentoCustomer($magentoCustomer, $skyLinkCustomer)->shouldBeCalled();

        $this->handle($this->syncSkyLinkCustomerToMagentoCustomerCommand);
    }

    public function it_should_register_a_new_magento_customer_if_none_is_found(SkyLinkCustomer $skyLinkCustomer)
    {
        $this->skyLinkCustomerRepository->find($this->skyLinkCustomerId)->willReturn($skyLinkCustomer);

        $this->magentoCustomerService->registerMagentoCustomer($skyLinkCustomer)->shouldBeCalled();

        $this->handle($this->syncSkyLinkCustomerToMagentoCustomerCommand);
    }
}
