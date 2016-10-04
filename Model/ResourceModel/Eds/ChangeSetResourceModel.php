<?php

namespace RetailExpress\SkyLink\Model\ResourceModel\Eds;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use RetailExpress\SkyLink\Eds\ChangeSet;
use RetailExpress\SkyLink\Eds\ChangeSetId;

class ChangeSetResourceModel extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('retail_express_skylink_eds_change_sets', 'change_set_id');
    }

    public function saveChangeSet(ChangeSet $changeSet)
    {
        //
    }

    public function findChangeSet(ChangeSetId $changeSetId)
    {
        //
    }

    public function processChangeSetEntityId(ChangeSetId $changeSetId, $entityId)
    {
        //
    }

    public function processChangeSet(ChangeSetId $changeSetId)
    {
        //
    }
}
