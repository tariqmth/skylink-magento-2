<?php

namespace RetailExpress\SkyLink\Sdk\Catalogue\Products;

use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkProductTypeRepositoryInterface;
use RetailExpress\SkyLink\Api\Catalogue\Products\SkyLinkMatrixPolicyRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeOption as SkyLinkAttributeOption;

class MatrixPolicyMapperFactory
{
    private $skyLinkProductTypeRepository;

    private $skyLinkMatrixPolicyRepository;

    public function __construct(
        SkyLinkProductTypeRepositoryInterface $skyLinkProductTypeRepository,
        SkyLinkMatrixPolicyRepositoryInterface $skyLinkMatrixPolicyRepository
    ) {
        $this->skyLinkProductTypeRepository = $skyLinkProductTypeRepository;
        $this->skyLinkMatrixPolicyRepository = $skyLinkMatrixPolicyRepository;
    }

    public function create()
    {
        $skyLinkMatrixPolicyMapper = new MatrixPolicyMapper();

        // Build up the Matrix Policy Mapper using the configured mappings
        array_map(function (SkyLinkAttributeOption $skyLinkProductType) use (&$skyLinkMatrixPolicyMapper) {

            /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Products\MatrixPolicy $skyLinkMatrixPolicy */
            $skyLinkMatrixPolicy = $this
                ->skyLinkMatrixPolicyRepository
                ->getMatrixPolicyForProductType($skyLinkProductType);

            $skyLinkMatrixPolicyMapper = $skyLinkMatrixPolicyMapper
                ->withPolicyForProductType($skyLinkMatrixPolicy, $skyLinkProductType);

        }, $this->skyLinkProductTypeRepository->getList());

        return $skyLinkMatrixPolicyMapper;
    }
}
