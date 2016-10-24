<?php

namespace spec\RetailExpress\SkyLink\Commands\Customers;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Sdk\Customers\CustomerNotFoundException;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepository as SkyLinkCustomerRepository;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepositoryFactory as SkylinkCustomerRepositoryFactory;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerRepositoryInterface;
use RetailExpress\SkyLink\Api\Customers\MagentoCustomerServiceInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkCustomerToMagentoCustomerCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkCustomerToMagentoCustomerHandler;

class SyncSkyLinkCustomerToMagentoCustomerHandlerSpec extends ObjectBehavior
{
    const EVENT_NAME = 'retail_express_skylink_skylink_customer_was_synced_to_magento_customer';

    private $skyLinkCustomerRepositoryFactory;

    private $skyLinkCustomerRepository;

    private $magentoCustomerRepository;

    private $magentoCustomerService;

    private $eventManager;

    private $skyLinkCustomerId;

    private $syncSkyLinkCustomerToMagentoCustomerCommand;

    public function let(
        SkylinkCustomerRepositoryFactory $skyLinkCustomerRepositoryFactory,
        SkyLinkCustomerRepository $skyLinkCustomerRepository,
        MagentoCustomerRepositoryInterface $magentoCustomerRepository,
        MagentoCustomerServiceInterface $magentoCustomerService,
        EventManagerInterface $eventManager
    ) {
        $this->skyLinkCustomerRepositoryFactory = $skyLinkCustomerRepositoryFactory;
        $this->skyLinkCustomerRepository = $skyLinkCustomerRepository;
        $this->magentoCustomerRepository = $magentoCustomerRepository;
        $this->magentoCustomerService = $magentoCustomerService;
        $this->eventManager = $eventManager;

        $this->beConstructedWith(
            $this->skyLinkCustomerRepositoryFactory,
            $this->magentoCustomerRepository,
            $this->magentoCustomerService,
            $this->eventManager
        );

        $this->skyLinkCustomerRepositoryFactory->create()->willReturn($this->skyLinkCustomerRepository);

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

        $this->eventManager->dispatch(self::EVENT_NAME, [
            'command' => $this->syncSkyLinkCustomerToMagentoCustomerCommand,
            'skylink_customer' => $skyLinkCustomer,
            'magento_customer' => $magentoCustomer,
        ])->shouldBeCalled();

        $this->handle($this->syncSkyLinkCustomerToMagentoCustomerCommand);
    }

    public function it_should_register_a_new_magento_customer_if_none_is_found(
        SkyLinkCustomer $skyLinkCustomer,
        CustomerInterface $magentoCustomer
    ) {
        $this->skyLinkCustomerRepository->find($this->skyLinkCustomerId)->willReturn($skyLinkCustomer);

        $this->magentoCustomerService->registerMagentoCustomer($skyLinkCustomer)->shouldBeCalled()->willReturn($magentoCustomer);

        $this->eventManager->dispatch(self::EVENT_NAME, [
            'command' => $this->syncSkyLinkCustomerToMagentoCustomerCommand,
            'skylink_customer' => $skyLinkCustomer,
            'magento_customer' => $magentoCustomer,
        ])->shouldBeCalled();

        $this->handle($this->syncSkyLinkCustomerToMagentoCustomerCommand);
    }
}
