<?php

namespace RetailExpress\SkyLink\Plugin\Magento\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\WebsiteFactory as MagentoProductWebsiteFactory;
use Magento\Framework\Registry;
use RetailExpress\SkyLink\Model\Catalogue\Products\ProductInterfaceAsserter;
use RetailExpress\SkyLink\Plugin\SkyLink\Catalogue\Products\SyncSkyLinkProductToMagentoProductHandlerWebsiteIdWorkaroundPlugin;

class ProductRepositoryWebsiteIdWorkaroundPlugin
{
    use ProductInterfaceAsserter;

    private $registry;

    private $magentoProductWebsiteFactory;

    public function __construct(
        Registry $registry,
        MagentoProductWebsiteFactory $magentoProductWebsiteFactory
    ) {
        $this->registry = $registry;
        $this->magentoProductWebsiteFactory = $magentoProductWebsiteFactory;
    }

    public function aroundSave(
        ProductRepositoryInterface $subject,
        callable $proceed,
        ProductInterface $magentoProduct,
        $saveOptions = false
    ) {
        $this->assertImplementationOfProductInterface($magentoProduct);

        if ($this->needsWorkaround()) {
            $properWebsiteIds = $magentoProduct->getWebsiteIds();
        }

        $magentoProduct = $proceed($magentoProduct, $saveOptions);

        if ($this->needsWorkaround()) {
            $this->restoreWebsiteIds($magentoProduct, $properWebsiteIds);
        }

        return $magentoProduct;
    }

    private function restoreWebsiteIds(Product &$magentoProduct, array $properWebsiteIds)
    {
        $incorrectWebsiteIds = array_map('intval', $magentoProduct->getWebsiteIds());
        $properWebsiteIds = array_map('intval', $properWebsiteIds);

        /* @var \Magento\Catalog\Model\Product\Website $magentoProductWebsite */
        $magentoProductWebsite = $this->magentoProductWebsiteFactory->create();

        /* @var int[] $removeFromWebsiteIds */
        $removeFromWebsiteIds = array_diff($incorrectWebsiteIds, $properWebsiteIds);

        /* @var int[] $addToWebsiteIds */
        $addToWebsiteIds = array_diff($properWebsiteIds, $incorrectWebsiteIds);

        if (count($removeFromWebsiteIds) > 0) {
            $magentoProductWebsite->removeProducts($removeFromWebsiteIds, [$magentoProduct->getId()]);
        }

        if (count($addToWebsiteIds) > 0) {
            $magentoProductWebsite->addProducts($addToWebsiteIds, [$magentoProduct->getId()]);
        }

        $magentoProduct->setWebsiteIds($properWebsiteIds);
    }

    private function needsWorkaround()
    {
        $key = SyncSkyLinkProductToMagentoProductHandlerWebsiteIdWorkaroundPlugin::REGISTRY_KEY;

        return null !== $this->registry->registry($key);
    }
}
