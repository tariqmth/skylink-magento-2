<?php

namespace RetailExpress\SkyLink\Api\Eds;

use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Eds\ChangeSet;
use RetailExpress\SkyLink\Eds\ChangeSetId;

interface ChangeSetRepositoryInterface
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
}
