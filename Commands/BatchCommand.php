<?php

namespace RetailExpress\SkyLink\Commands;

trait BatchCommand
{
    /**
     * An optional Batch ID that this command is associated with (such as a Change Set).
     *
     * @var string
     */
    public $batchId;
}
