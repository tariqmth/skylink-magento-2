<?php

namespace RetailExpress\SkyLink\Model\Eds;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use RetailExpress\SkyLink\Api\Eds\ChangeSetServiceInterface;
use RetailExpress\SkyLink\Eds\ChangeSet;
use RetailExpress\SkyLink\Eds\ChangeSetId;
use RetailExpress\SkyLink\Exceptions\Eds\NotAllEntitiesProcessedException;

class ChangeSetService implements ChangeSetServiceInterface
{
    use ChangeSetHelpers;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
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
