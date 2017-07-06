<?php

namespace RetailExpress\SkyLink\Block\Catalogue\Eta;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use RetailExpress\SkyLink\Api\Catalogue\Eta\ConfigInterface as EtaConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Eta\HelperInterface as EtaHelperInterface;

class Button extends Template
{
    private $registry;

    private $config;

    private $helper;

    private $magentoProduct;

    public function __construct(
        TemplateContext $context,
        Registry $registry,
        EtaConfigInterface $config,
        EtaHelperInterface $helper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->config = $config;
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    public function canShow()
    {
        return $this->helper->canShow($this->getMagentoProduct());
    }

    public function getTitle()
    {
        return $this->config->getButtonTitle();
    }

    public function hasDisclaimerLabel()
    {
        return strlen($this->getDisclaimerLabel()) > 0;
    }

    public function getDisclaimerLabel()
    {
        return $this->config->getDisclaimerLabel();
    }

    public function getNoDateLabel()
    {
        return $this->config->getNoDateLabel();
    }

    public function getEtaUrl()
    {
        return sprintf(
            '%s?%s',
            $this->getUrl('skylink/catalogue_eta/index'),
            http_build_query(['magento_product_id' => $this->getMagentoProduct()->getId()])
        );
    }

    public function getMagentoProduct()
    {
        if (null === $this->magentoProduct) {
            $this->magentoProduct = $this->registry->registry('product');
        }

        return $this->magentoProduct;
    }
}
