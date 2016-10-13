<?php

namespace RetailExpress\SkyLink\Model\Eds;

trait ChangeSetHelpers
{
    private $connection;

    private function getChangeSetsTable()
    {
        return $this->connection->getTableName('retail_express_skylink_eds_change_sets');
    }

    private function getChangeSetEntitiesTable()
    {
        return $this->connection->getTableName('retail_express_skylink_eds_change_set_entities');
    }
}
