<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use RetailExpress\SkyLink\Api\Outlets\SkyLinkOutletRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;

class Outlets implements ArrayInterface
{
    private $skyLinkOutletRepository;

    public function __construct(SkyLinkOutletRepositoryInterface $skyLinkOutletRepository)
    {
        $this->skyLinkOutletRepository = $skyLinkOutletRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return array_map(function (SkyLinkOutlet $skyLinkOutlet) {
            return [
                'value' => (string) $skyLinkOutlet->getId(),
                'label' => (string) $skyLinkOutlet->getName(),
            ];
        }, $this->skyLinkOutletRepository->getList());
    }
}
