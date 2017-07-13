<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\DefaultAttributeMappingProviderInterface;

class DefaultAttributeMappingProvider implements DefaultAttributeMappingProviderInterface
{
    /**
     * @var array
     */
    private $mappings;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Create a Default Attribute Mappings Provider instance.
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultMappings()
    {
        if (null === $this->mappings) {
            $this->mappings = $this->scopeConfig->getValue('skylink/default_attribute_mappings');
        }

        return $this->mappings;
    }
}
