<?php

namespace spec\RetailExpress\SkyLink\Commands\Customers;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use RetailExpress\SkyLink\Api\Customers\SkyLinkCustomerBuilderInterface;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerCommand;
use RetailExpress\SkyLink\Commands\Customers\SyncMagentoCustomerToSkyLinkCustomerHandler;
use RetailExpress\SkyLink\Sdk\Customers\Customer as SkyLinkCustomer;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepository as SkylinkCustomerRepository;
use RetailExpress\SkyLink\Sdk\Customers\CustomerRepositoryFactory as SkylinkCustomerRepositoryFactory;

class SyncMagentoCustomerToSkyLinkCustomerHandlerSpec extends ObjectBehavior
{
    private $baseMagentoCustomerRepository;

    private $skyLinkCustomerRepositoryFactory;

    private $skyLinkCustomerRepository;

    private $skyLinkCustomerBuilder;

    private $magentoCustomerId;

    private $command;

    public function let(
        CustomerRepositoryInterface $baseMagentoCustomerRepository,
        SkylinkCustomerRepositoryFactory $skyLinkCustomerRepositoryFactory,
        SkylinkCustomerRepository $skyLinkCustomerRepository,
        SkyLinkCustomerBuilderInterface $skyLinkCustomerBuilder
    ) {
        $this->baseMagentoCustomerRepository = $baseMagentoCustomerRepository;
        $this->skyLinkCustomerRepositoryFactory = $skyLinkCustomerRepositoryFactory;
        $this->skyLinkCustomerRepository = $skyLinkCustomerRepository;
        $this->skyLinkCustomerBuilder = $skyLinkCustomerBuilder;

        $this->beConstructedWith(
            $this->baseMagentoCustomerRepository,
            $this->skyLinkCustomerRepositoryFactory,
            $this->skyLinkCustomerBuilder
        );

        $this->skyLinkCustomerRepositoryFactory->create()->willReturn($this->skyLinkCustomerRepository);

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

        $this->skyLinkCustomerRepository->add($skyLinkCustomer)->shouldBeCalled();

        $this->handle($this->command);
    }
}
