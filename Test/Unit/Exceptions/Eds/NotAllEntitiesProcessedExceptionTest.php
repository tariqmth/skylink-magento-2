<?php

namespace RetailExpress\SkyLink\Test\Unit\Exceptions\Eds;

use PHPUnit_Framework_TestCase;
use RetailExpress\SkyLink\Exceptions\Eds\NotAllEntitiesProcessedException;
use RetailExpress\SkyLink\Eds\ChangeSetId;

class NotAllEntitiesProcessedExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testItCanBeInitialised()
    {
        $uuidString = ChangeSetId::generateAsString();
        $changeSetId = new ChangeSetId($uuidString);

        $exception = NotAllEntitiesProcessedException::withChangeSetId($changeSetId);

        $expected = "Not all Entity IDs have been processed for Change Set \"{$uuidString}\".";

        $this->assertEquals($expected, $exception->getMessage());
    }
}
