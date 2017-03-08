<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Eta;

interface ConfigInterface
{
    /**
     * Returns whether ETA can be used.
     *
     * @return bool
     */
    public function canUse();

    /**
     * Return the title for the ETA button.
     *
     * @return string
     */
    public function getButtonTitle();

    /**
     * Get the disclaimer for the button.
     *
     * @return string
     */
    public function getButtonDisclaimer();
}
