<?php

namespace RetailExpress\SkyLink\Api\Eds;

use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Eds\ChangeSet;
use RetailExpress\SkyLink\Eds\ChangeSetId;
use RetailExpress\SkyLink\Exceptions\Eds\NotAllEntitiesProcessedException;

interface ChangeSetRepository
{
    /**
     * Saves the given Change Set for retrieval later.
     *
     * @param ChangeSet $changeSet
     */
    public function save(ChangeSet $changeSet);

    /**
     * Finds a Change Set by it's given ID.
     *
     * @param ChangeSetId $changeSetId
     *
     * @return ChangeSet
     *
     * @throws NoSuchEntityException
     */
    public function find(ChangeSetId $changeSetId);

    /**
     * Process an Entity ID for a given Change Set with an ID provided.
     *
     * @events skylink_eds_entity_was_processed
     *
     * @param ChangeSetId $changeSetId
     * @param object      $entityId
     *
     * @throws NoSuchEntityException
     */
    public function processEntityId(ChangeSetId $changeSetId, $entityId);

    /**
     * Mark an entire Change Set as processed.
     *
     * @events skylink_eds_change_set_was_processed
     *
     * @param ChangeSetId $changeSetId
     *
     * @throws NotAllEntitiesProcessedException
     */
    public function process(ChangeSetId $changeSetId);
}
