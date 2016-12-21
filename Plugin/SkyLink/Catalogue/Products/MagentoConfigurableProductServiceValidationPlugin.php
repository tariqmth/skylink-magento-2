<?php

namespace RetailExpress\SkyLink\Plugin\SkyLink\Catalogue\Products;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\InputException;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkLoggerInterface;
use RetailExpress\SkyLink\Model\Catalogue\Products\MagentoConfigurableProductService;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\Matrix as SkyLinkMatrix;

class MagentoConfigurableProductServiceValidationPlugin
{
    const DUPLICATE_ATTRIBUTE_OPTIONS_REGEX = '/^Products "\d+" and "\d+" have the same set of attribute values\.$/';

    private $logger;

    public function __construct(SkyLinkLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function aroundCreateMagentoProduct(
        MagentoConfigurableProductService $subject,
        callable $proceed,
        SkyLinkMatrix $skyLinkMatrix,
        array $magentoSimpleProducts
    ) {
        return $this->handleValidationErrors(
            function () use ($proceed, $skyLinkMatrix, $magentoSimpleProducts) {
                return $proceed($skyLinkMatrix, $magentoSimpleProducts);
            },
            $skyLinkMatrix
        );
    }

    public function aroundUpdateMagentoProduct(
        MagentoConfigurableProductService $subject,
        callable $proceed,
        SkyLinkMatrix $skyLinkMatrix,
        ProductInterface $magentoConfigurableProduct,
        array $magentoSimpleProducts
    ) {
        return $this->handleValidationErrors(
            function () use ($proceed, $skyLinkMatrix, $magentoConfigurableProduct, $magentoSimpleProducts) {
                return $proceed($skyLinkMatrix, $magentoConfigurableProduct, $magentoSimpleProducts);
            },
            $skyLinkMatrix
        );
    }

    private function handleValidationErrors(callable $callback, SkyLinkMatrix $skyLinkMatrix)
    {
        try {
            return $callback();
        } catch (InputException $e) {
            if (1 === preg_match(self::DUPLICATE_ATTRIBUTE_OPTIONS_REGEX, $e->getMessage())) {
                $this->logger->error(__('Simple products destined to be linked with a Magento Configurable Product based on a SkyLink Product Matrix have all of the same attribute values and cannot both be used for the same Magento Configurable Product.'), [
                    'Error' => $e->getMessage(),
                    'SkyLink Matrix Product SKU' => $skyLinkMatrix->getSku(),
                ]);
            }

            throw $e;
        }
    }
}
