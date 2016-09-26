<?php

namespace RetailExpress\SkyLink\Magento2\Api\Eds;

use RetailExpress\SkyLink\Eds\ChangeSetId;

interface ChangeSetServiceInterface
{
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
