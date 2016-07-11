<?php

namespace RetailExpress\SkyLink\Magento2\Exceptions\Eds;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use RetailExpress\SkyLink\Eds\ChangeSetId;

class NotAllEntitiesProcessedException extends LocalizedException
{
    /**
     * Create a new Exception by passing through a Change Set ID.
     *
     * @param  ChangeSetId
     *
     * @return NotAllEntitiesProcessedException
     */
    public static function withChangeSetId(ChangeSetId $changeSetId)
    {
        return new self(
            new Phrase(
                'Not all Entity IDs have been processed for Change Set "%1".',
                [$changeSetId]
            )
        );
    }
}
