<?php

namespace RetailExpress\SkyLink\Observer\Eds;

use RetailExpress\SkyLink\Api\Eds\ChangeSetRepositoryInterface;
use RetailExpress\SkyLink\Api\Eds\ChangeSetServiceInterface;
use RetailExpress\SkyLink\Eds\ChangeSetId;
use RetailExpress\SkyLink\Eds\Entity as EdsEntity;
use RetailExpress\SkyLink\Eds\EntityType as EdsEntityType;
use ValueObjects\ValueObjectInterface;

trait EntityProcessor
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

    /**
     * Gets the matching EDS entity from a Change Set with the provided ID, who's
     * Entity ID matches the given Comparison ID.
     *
     * @param ChangeSetId          $changeSetId
     * @param EdsEntityType        $edsEntityType
     * @param ValueObjectInterface $comparisonId
     *
     * @return EdsEntity
     */
    public function getMatchingEdsEntity(
        ChangeSetId $changeSetId,
        EdsEntityType $comparisonEntityType,
        ValueObjectInterface $comparisonId
    ) {
        // Firstly, find the Change Set to match the given ID
        $changeSet = $this->changeSetRepository->find($changeSetId);

        // Now, loop through all of the entities and compare
        $matchingEntities = array_filter(
            $changeSet->getEntities(),
            function (EdsEntity $edsEntity) use ($comparisonEntityType, $comparisonId) {
                return $edsEntity->getType()->sameValueAs($comparisonEntityType) &&
                    $edsEntity->getId()->sameValueAs($comparisonId);
            }
        );

        // Check there's only one entity
        if (count($matchingEntities) !== 1) {
            throw new \RuntimeException('@todo implement custom exception for when there are too many entities (which I dont think can happen due to DB unique indexes) or if there are no matches');
        }

        return current($matchingEntities);
    }
}
