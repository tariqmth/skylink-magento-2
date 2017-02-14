<?php

namespace RetailExpress\SkyLink\Block\Catalogue\Eta;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use RetailExpress\SkyLink\Api\Catalogue\Eta\ConfigInterface as EtaConfigInterface;

class Button extends Template
{
    private $registry;

    private $stockRegistry;

    private $etaConfig;

    private $product;

    private $skyLinkProductId;

    private $stockItem;

    public function __construct(
        TemplateContext $context,
        Registry $registry,
        StockRegistryInterface $stockRegistry,
        EtaConfigInterface $etaConfig,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->stockRegistry = $stockRegistry;
        $this->etaConfig = $etaConfig;

        parent::__construct($context, $data);
    }

    public function canShow()
    {
        return $this->etaConfig->canUse() &&
            true === $this->hasSkyLinkProductId() &&
            false == $this->getMagentoStockItem()->getIsInStock();
    }

    public function getEtaUrl()
    {
        return sprintf(
            '%s?%s',
            $this->getUrl('skylink/catalogue_eta/index'),
            http_build_query(['magento_product_id' => $this->getMagentoProductId()])
        );
    }

    public function hasSkyLinkProductId()
    {
        return null !== $this->getMagentoProduct()->getCustomAttribute('skylink_product_id');
    }

    public function getMagentoProduct()
    {
        if (null === $this->product) {
            $this->product = $this->registry->registry('product');
        }

        return $this->product;
    }

    public function getMagentoProductId()
    {
        return $this->getMagentoProduct()->getId();
    }

    public function getMagentoStockItem()
    {
        if (null === $this->stockItem) {
            $this->stockItem = $this->stockRegistry->getStockItem($this->getMagentoProductId());
        }

        return $this->stockItem;
    }
}
