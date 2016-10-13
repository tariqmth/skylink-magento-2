<?php

namespace RetailExpress\SkyLink\Model\Eds;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use RetailExpress\SkyLink\Api\Eds\ChangeSetServiceInterface;
use RetailExpress\SkyLink\Eds\Entity as EdsEntity;
use RetailExpress\SkyLink\Exceptions\Eds\NotAllEntitiesProcessedException;

class ChangeSetService implements ChangeSetServiceInterface
{
    use ChangeSetHelpers;

    private $dateTime;

    private $eventManager;

    public function __construct(
        ResourceConnection $resourceConnection,
        DateTime $dateTime,
        EventManagerInterface $eventManager
    ) {
        $this->connection = $resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->dateTime = $dateTime;
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritdoc}
     */
    public function processEntity(EdsEntity $edsEntity)
    {
        if (!$this->entityExists($edsEntity)) {
            throw new NoSuchEntityException(__(
                'Could not find a "%1#%2" entity for Change Set #%3',
                $edsEntity->getType(),
                $edsEntity->getId(),
                $edsEntity->getChangeSet()->getId()
            ));
        }

        $this->connection->update(
            $this->getChangeSetEntitiesTable(),
            ['processed_at' => $this->dateTime->gmtDate('Y-m-d H:i:s')],
            [
                'change_set_id = ?' => $edsEntity->getChangeSet()->getId(),
                'entity_type = ?' => $edsEntity->getType(),
                'entity_id = ?' => $edsEntity->getId(),
            ]
        );

        $this->eventManager->dispatch(
            'retail_express_skylink_eds_entity_was_processed',
            [
                'entity' => $edsEntity,
            ]
        );
    }

    private function entityExists(EdsEntity $edsEntity)
    {
        return (bool) $this->connection->fetchOne(
            $this->connection
                ->select()
                ->from($this->getChangeSetEntitiesTable())
                ->where('change_set_id = ?', $edsEntity->getChangeSet()->getId())
                ->where('entity_type = ?', $edsEntity->getType())
                ->where('entity_id = ?', $edsEntity->getId())
        );
    }
}
