<?php

namespace RetailExpress\SkyLink\Observer\Eds;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use RetailExpress\SkyLink\Api\Eds\ChangeSetRepositoryInterface;
use RetailExpress\SkyLink\Eds\ChangeSetServiceFactory;

class WhenEdsEntityWasProcessed implements ObserverInterface
{
    private $changeSetRepository;

    private $changeSetServiceFactory;

    public function __construct(
        ChangeSetRepositoryInterface $changeSetRepository,
        ChangeSetServiceFactory $changeSetServiceFactory
    ) {
        $this->changeSetRepository = $changeSetRepository;
        $this->changeSetServiceFactory = $changeSetServiceFactory;
    }

    public function execute(Observer $observer)
    {
        /* @var \RetailExpress\SkyLink\Eds\ChangeSetService $changeSetService */
        $changeSetService = $this->changeSetServiceFactory->create();

        // @todo do we need to reload the Change Set? Would it ever be outdated
        // or not include all entities? (note: an entity can be created with a
        // reference to a Change Set without being fetched from the db).
        $cachedChangeSet = $observer->getData('entity')->getChangeSet();
        $changeSetId = $cachedChangeSet->getId();

        $changeSet = $this->changeSetRepository->find($changeSetId);

        // Move on if the Change Set is not processed
        if (!$changeSet->isProcessed()) {
            return;
        }

        // $this->changeSetService->process($changeSet);
    }
}
