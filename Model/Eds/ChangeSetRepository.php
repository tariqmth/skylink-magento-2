<?php

namespace RetailExpress\SkyLink\Model\Eds;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use RetailExpress\SkyLink\Api\Eds\ChangeSetRepositoryInterface;
use RetailExpress\SkyLink\Eds\ChangeSet;
use RetailExpress\SkyLink\Eds\Entity;
use RetailExpress\SkyLink\Eds\ChangeSetId;

class ChangeSetRepository implements ChangeSetRepositoryInterface
{
    use ChangeSetHelpers;

    private $eventManger;

    public function __construct(
        ResourceConnection $resourceConnection,
        EventManagerInterface $eventManger
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->eventManger = $eventManger;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ChangeSet $changeSet)
    {
        if ($this->changeSetExists($changeSet->getId())) {
            throw new \RuntimeException("@todo, move method to the service and call it register(), as Change Sets cannot be persisted once registered.");
        }

        // Save the Change Set
        $this->connection->insert(
            $this->getChangeSetsTable(),
            [
                'change_set_id' => $changeSet->getId()
            ]
        );

        // Save all entities
        array_map(function (Entity $entity) use ($changeSet) {
            $this->connection->insert(
                $this->getChangeSetEntitiesTable(),
                [
                    'change_set_id' => $changeSet->getId(),
                    'entity_type' => $entity->getType(),
                    'entity_id' => $entity->getId(),
                ]
            );
        }, $changeSet->getEntities());

        $this->eventManger->dispatch(
            'retail_express_skylink_eds_change_set_registered',
            [
                'change_set' => $changeSet,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function find(ChangeSetId $changeSetId)
    {
        if (!$this->changeSetExists($changeSetId)) {
            throw NoSuchEntityException::singleField('change_set_id', $changeSetId);
        }

        $changeSet = new ChangeSet($changeSetId);

        $entitiesPayload = $this->connection->fetchAll(
            $this->connection
                ->select()
                ->from($this->getChangeSetEntitiesTable())
                ->where('change_set_id = ?', $changeSetId)
        );

        array_walk($entitiesPayload, function (array $entityPayload) use (&$changeSet) {
            $entity = Entity::fromNative(
                $entityPayload['entity_type'],
                $entityPayload['entity_id'],
                $entityPayload['processed_at']
            );

            $changeSet = $changeSet->withEntity($entity);
        });

        return $changeSet;
    }

    private function changeSetExists(ChangeSetId $changeSetId)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getChangeSetsTable())
                ->where('change_set_id = ?', $changeSetId)
        );
    }
}
