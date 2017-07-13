<?php

namespace RetailExpress\SkyLink\Api\Catalogue\Attributes;

interface DefaultAttributeMappingProviderInterface
{
    /**
     * Get the default Attribute mappings, in the following form:
     *
     * [
     *     "skylink_attribute_code" => "magento_attribute_code",
     * ]
     *
     * @return array
     */
    public function getDefaultMappings();
}
