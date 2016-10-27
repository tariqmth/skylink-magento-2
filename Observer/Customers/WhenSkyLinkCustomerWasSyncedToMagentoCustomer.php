<?php

namespace RetailExpress\SkyLink\Observer\Customers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLink\Api\Eds\ChangeSetRepositoryInterface;
use RetailExpress\SkyLink\Api\Eds\ChangeSetServiceInterface;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Eds\ChangeSetId;
use RetailExpress\SkyLink\Eds\Entity as EdsEntity;
use RetailExpress\SkyLink\Eds\EntityType as EdsEntityType;

class WhenSkyLinkCustomerWasSyncedToMagentoCustomer implements ObserverInterface
{
    private $changeSetRepository;

    private $changeSetService;

    public function __construct(
        ChangeSetRepositoryInterface $changeSetRepository,
        ChangeSetServiceInterface $changeSetService
    ) {
        $this->changeSetRepository = $changeSetRepository;
        $this->changeSetService = $changeSetService;
    }

    public function execute(Observer $observer)
    {
        /* @var \RetailExpress\SkyLink\Commands\Customers\SyncSkyLinkCustomerToMagentoCustomerCommand $command */
        $command = $observer->getData('command');

        // Only continue if we're dealing with a command that was triggered by EDS
        if (null === $command->changeSetId) {
            return;
        }

        $skyLinkCustomerId = new SkyLinkCustomerId($command->skyLinkCustomerId);

        $changeSetId = new ChangeSetId($command->changeSetId);
        $changeSet = $this->changeSetRepository->find($changeSetId);

        $matchingEntities = array_values(array_filter(
            $changeSet->getEntities(),
            function (EdsEntity $edsEntity) use ($skyLinkCustomerId) {
                return $edsEntity->getType()->sameValueAs(EdsEntityType::get('customer')) &&
                $edsEntity->getId()->sameValueAs($skyLinkCustomerId);
            }
        ));

        if (count($matchingEntities) !== 1) {
            throw new \RuntimeException('@todo implement custom exception for when there are too many entities (which I dont think can happen due to DB unique indexes) or if there are no matches');
        }

        $this->changeSetService->processEntity($matchingEntities[0]);
    }
}
