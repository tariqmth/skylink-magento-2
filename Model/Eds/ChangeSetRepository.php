<?php

namespace RetailExpress\SkyLink\Model\Eds;

use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Api\Eds\ChangeSetRepository as ChangeSetRepositoryInterface;
use RetailExpress\SkyLink\Eds\ChangeSet;
use RetailExpress\SkyLink\Eds\ChangeSetId;
use RetailExpress\SkyLink\Exceptions\Eds\NotAllEntitiesProcessedException;
use RetailExpress\SkyLink\Model\ResourceModel\Eds\ChangeSetResourceModel;

class ChangeSetRepository implements ChangeSetRepositoryInterface
{
    private $resourceModel;

    public function __construct(ChangeSetResourceModel $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ChangeSet $changeSet)
    {
        $this->resourceModel->saveChangeSet($changeSet);
    }

    /**
     * {@inheritdoc}
     */
    public function find(ChangeSetId $changeSetId)
    {
        $changeSet = $this->resourceModel->findChangeSet($changeSetId);

        if (null === $changeSet) {
            throw NoSuchEntityException::singleField('change_set_id', $changeSetId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processEntityId(ChangeSetId $changeSetId, $entityId)
    {
        $updatedRows = $this->resourceModel->processChangeSetEntityId($changeSetId, $entityId);

        if (0 === $updatedRows) {
            throw NoSuchEntityException::doubleField(
                'change_set_id',
                $changeSetId,
                'entity_id',
                $entityId
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(ChangeSetId $changeSetId)
    {
        $updatedRows = $this->resourceModel->processChangeSet($changeSetId);

        if (0 === $updatedRows || false === $updatedRows) {
            throw NotAllEntitiesProcessedException::withChangeSetId($changeSetId);
        }
    }
}
