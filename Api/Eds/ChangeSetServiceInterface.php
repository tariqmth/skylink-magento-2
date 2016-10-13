<?php

namespace RetailExpress\SkyLink\Api\Eds;

use RetailExpress\SkyLink\Eds\Entity as EdsEntity;

interface ChangeSetServiceInterface
{
    /**
     * Process an Change Set Entity
     *
     * @events retail_express_skylink_eds_entity_was_processed
     *
     * @param EdsEntity $edsEntity
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processEntity(EdsEntity $edsEntity);
}
