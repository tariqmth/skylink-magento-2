<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Attributes;

trait EdsAttributeOptionIdWorkaround
{
    /**
     * Simple a placeholder for a generic attribute option ID used for identifying the command with EDS - EDS only
     * tells us an attribute option ID, not the entire attribute code so we end up syncing all attributes. The
     * option ID is placed against the last command so that once it completes, we can consider the EDS
     * entity completed. Dumb, but it works.
     *
     * @var string
     */
    public $skyLinkAttributeOptionId;
}
