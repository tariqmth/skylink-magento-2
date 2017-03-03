<?php

namespace RetailExpress\SkyLink\Model\Catalogue\Attributes;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend;
use Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\MagentoAttributeTypeManagerInterface;
use RetailExpress\SkyLink\Exceptions\Products\UnsupportedAttributeTypeException;
use Zend_Db_Expr;

class MagentoAttributeTypeManager implements MagentoAttributeTypeManagerInterface
{
    private $searchCriteriaBuilderFactory;

    public function __construct(SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory)
    {
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteriaBuilder(MagentoAttributeType $magentoAttributeType)
    {
        /* @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();

        // Apply all common filters
        array_map(function (array $filter) use ($searchCriteriaBuilder) {
            call_user_func_array([$searchCriteriaBuilder, 'addFilter'], $filter);
        }, $this->getCommonFilters());

        switch (true) {
            case $magentoAttributeType->sameValueAs(MagentoAttributeType::get('freeform')):
                $searchCriteriaBuilder->addFilter('frontend_input', ['text', 'textarea'], 'in');
                break;

            case $magentoAttributeType->sameValueAs(MagentoAttributeType::get('configurable')):
                $searchCriteriaBuilder->addFilter('frontend_input', 'select');
                $searchCriteriaBuilder->addFilter('is_global', ScopedAttributeInterface::SCOPE_GLOBAL);
                break;

                return $searchCriteriaBuilder;
        }

        return $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(ProductAttributeInterface $magentoAttribute)
    {
        // I think "if" statements are the easiest to read here. We are simply reversing the
        // filters applied in the above method to determine what type an attribute is.

        // Test for configurable type
        if ($this->passesCommonFilters($magentoAttribute) &&
            $magentoAttribute->getScope() === 'global' &&
            $magentoAttribute->getFrontendInput() === 'select'
        ) {
            return MagentoAttributeType::get('configurable');
        }

        if ($this->passesCommonFilters($magentoAttribute) &&
            in_array($magentoAttribute->getFrontendInput(), ['text', 'textarea'])
        ) {
            return MagentoAttributeType::get('freeform');
        }

        throw UnsupportedAttributeTypeException::withMagentoAttribute($magentoAttribute);
    }

    private function getCommonFilters()
    {
        $filters = [
            ['frontend_label', '', 'neq'],
            ['backend_model', new Zend_Db_Expr('null'), 'is'],
            ['frontend_model', new Zend_Db_Expr('null'), 'is'],
            ['is_unique', 0],
        ];

        array_map(functioN ($skyLinkReservedAttributeCode) use (&$filters) {
            $filters[] = ['attribute_code', $skyLinkReservedAttributeCode, 'neq'];
        }, $this->getSkyLinkReservedAttributeCodes());

        return $filters;
    }

    private function passesCommonFilters(ProductAttributeInterface $magentoAttribute)
    {
        return null !== $magentoAttribute->getDefaultFrontendLabel() &&
            $this->usesAcceptableModel($magentoAttribute->getBackendModel()) &&
            $this->usesAcceptableModel($magentoAttribute->getFrontendModel()) &&
            false == $magentoAttribute->getIsUnique() && // Can't use strict comparison because we get a number/string or boolean!
            false === in_array($magentoAttribute->getAttributeCode(), $this->getSkyLinkReservedAttributeCodes());
    }

    private function usesAcceptableModel($model)
    {
        return in_array($model, [
            null,
            DefaultBackend::class,
            DefaultFrontend::class,
        ], true); // Scrict comparison
    }

    /**
     * @todo this needs to be centralised!
     */
    private function getSkyLinkReservedAttributeCodes()
    {
        return ['skylink_product_id', 'qty_on_order'];
    }
}
