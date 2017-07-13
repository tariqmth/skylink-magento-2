<?php

namespace RetailExpress\SkyLink\Setup;

use Magento\Eav\Setup\EavSetup;

trait DataHelper
{
    private function addAttributeToDefaultGroupInAllSets(EavSetup $eavSetup, $magentoAttributeCode, $entityType)
    {
        foreach ($eavSetup->getAllAttributeSetIds($entityType) as $attributeSetId) {
            $eavSetup->addAttributeToGroup(
                $entityType,
                $attributeSetId,
                $eavSetup->getDefaultAttributeGroupId($entityType, $attributeSetId),
                $magentoAttributeCode
            );
        }
    }
}
