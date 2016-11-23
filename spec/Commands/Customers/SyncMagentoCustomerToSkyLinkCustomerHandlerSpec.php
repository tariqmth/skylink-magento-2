<?php

namespace spec\RetailExpress\SkyLink\Commands\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PhpSpec\ObjectBehavior;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerBuilderInterface;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerServiceInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerHandler;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepository as SkylinkCustomerRepository;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepositoryFactory as SkylinkCustomerRepositoryFactory;

class SyncMagentoCustomerToSkyLinkCustomerHandlerSpec extends ObjectBehavior
{
    private $baseMagentoCustomerRepository;

    private $skyLinkCustomerBuilder;

    private $skyLinkCustomerService;

    private $magentoCustomerId;

    private $command;

    public function let(
        CustomerRepositoryInterface $baseMagentoCustomerRepository,
        SkyLinkCustomerBuilderInterface $skyLinkCustomerBuilder,
        SkyLinkCustomerServiceInterface $skyLinkCustomerService
    ) {
        $this->baseMagentoCustomerRepository = $baseMagentoCustomerRepository;
        $this->skyLinkCustomerBuilder = $skyLinkCustomerBuilder;
        $this->skyLinkCustomerService = $skyLinkCustomerService;

        $this->beConstructedWith(
            $this->baseMagentoCustomerRepository,
            $this->skyLinkCustomerBuilder,
            $this->skyLinkCustomerService
        );

        $this->magentoCustomerId = 1;
        $this->command = new SyncMagentoCustomerToSkyLinkCustomerCommand();
        $this->command->magentoCustomerId = $this->magentoCustomerId;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(SyncMagentoCustomerToSkyLinkCustomerHandler::class);
    }

    public function it_should_pass_on_an_exception_when_magento_customer_does_not_exist()
    {
        $this
            ->baseMagentoCustomerRepository
            ->getById($this->magentoCustomerId)
            ->willThrow(NoSuchEntityException::class);

        $this->shouldThrow(NoSuchEntityException::class)->duringHandle($this->command);
    }

    public function it_builds_a_skylink_customer_instance_and_passes_it_to_the_skylink_repository(
        CustomerInterface $magentoCustomer,
        SkyLinkCustomer $skyLinkCustomer
    ) {
        $this->baseMagentoCustomerRepository->getById($this->magentoCustomerId)->willReturn($magentoCustomer);

        $this->skyLinkCustomerBuilder->buildFromMagentoCustomer($magentoCustomer)->willReturn($skyLinkCustomer);

        $this->skyLinkCustomerService->pushSkyLinkCustomer($skyLinkCustomer, $magentoCustomer)->shouldBeCalled();

        $this->handle($this->command);
    }
}
