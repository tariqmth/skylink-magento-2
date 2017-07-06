<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Eta;

interface ConfigInterface
{
    /**
     * Returns whether ETA can be shown.
     *
     * @return bool
     */
    public function canShow();

    /**
     * Return the title for the ETA button.
     *
     * @return string
     */
    public function getButtonTitle();

    /**
     * Get the disclaimer label.
     *
     * @return string
     */
    public function getDisclaimerLabel();

    /**
     * Get hte "no date" label.
     *
     * @return string
     */
    public function getNoDateLabel();

    /**
     * Returns whether the stock status label should be replaced or not.
     *
     * @return bool
     */
    public function shouldReplaceProductStockStatus();

    /**
     * Get the label to replace stock status with when ETA is available for a product.
     *
     * @return string
     */
    public function getProductStockStatusLabel();
}
