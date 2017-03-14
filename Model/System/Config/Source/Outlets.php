<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Outlets\OutletRepositoryFactory as OutletRepositoryFactory;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;

class Outlets implements ArrayInterface
{
    private $request;

    private $websiteRepository;

    private $config;

    private $skyLinkOutletRepositoryFactory;

    public function __construct(
        RequestInterface $request,
        WebsiteRepositoryInterface $websiteRepository,
        ConfigInterface $config,
        OutletRepositoryFactory $skyLinkOutletRepositoryFactory
    ) {
        $this->request = $request;
        $this->websiteRepository = $websiteRepository;
        $this->config = $config;
        $this->skyLinkOutletRepositoryFactory = $skyLinkOutletRepositoryFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        /* @var \RetailExpress\SkyLink\Sdk\Outlets\OutletRepository $skyLinkOutletRepository */
        $skyLinkOutletRepository = $this->skyLinkOutletRepositoryFactory->create();

        /* @var \Magento\Store\Api\Data\WebsiteInterface|null $currentlyScopedWebsite */
        $currentlyScopedWebsite = $this->getCurrentlyScopedWebsite();

        if (null === $currentlyScopedWebsite) {
            $salesChannelId = $this->config->getSalesChannelId();
        } else {
            $salesChannelId = $this->config->getSalesChannelIdForWebsite($currentlyScopedWebsite->getCode());
        }

        /* @var \RetailExpress\SkyLink\Sdk\Outlets\Outlet[] $skyLinkOutlets */
        $skyLinkOutlets = $skyLinkOutletRepository->all($salesChannelId);

        return array_map(function (SkyLinkOutlet $skyLinkOutlet) {
            return [
                'value' => (string) $skyLinkOutlet->getId(),
                'label' => (string) $skyLinkOutlet->getName(),
            ];
        }, $skyLinkOutlets);
    }

    private function getCurrentlyScopedWebsite()
    {
        $websiteId = $this->request->getParam('website');

        if (null === $websiteId) {
            return;
        }

        return $this->websiteRepository->getById($websiteId);
    }
}
