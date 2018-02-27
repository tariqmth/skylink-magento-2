<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Eta;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Eta\ConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Eta\HelperInterface;

class Helper implements HelperInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Create a new ETA Helper.
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function canShow(ProductInterface $magentoProduct)
    {
        return $this->config->canShow() &&
            $this->hasSkyLinkProductId($magentoProduct) &&
            $this->hasNoneAvailable($magentoProduct) &&
            $this->hasSomeOnOrder($magentoProduct);
    }

    /**
     * @return bool
     */
    private function hasSkyLinkProductId(ProductInterface $magentoProduct)
    {
        return null !== $magentoProduct->getCustomAttribute('skylink_product_id');
    }

    /**
     * @return bool
     */
    private function hasNoneAvailable(ProductInterface $magentoProduct)
    {
        return 0 <= $magentoProduct->getCustomAttribute('qty_available')->getValue();
    }

    /**
     * @return bool
     */
    private function hasSomeOnOrder(ProductInterface $magentoProduct)
    {
        return 0 < $magentoProduct->getCustomAttribute('qty_on_order')->getValue();
    }
}
