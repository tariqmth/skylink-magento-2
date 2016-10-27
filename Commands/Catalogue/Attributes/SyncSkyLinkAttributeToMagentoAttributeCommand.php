<?php

namespace RetailExpress\SkyLink\Commands\Catalogue\Attributes;

class SyncSkyLinkAttributeToMagentoAttributeCommand
{
    /**
     * The Magento Attribute Code.
     *
     * @var string
     */
    public $magentoAttributeCode;

    /**
     * The SkyLink Attribute Code.
     *
     * @var string
     */
    public $skyLinkAttributeCode;
}
