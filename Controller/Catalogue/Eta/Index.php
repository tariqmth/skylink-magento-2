<?php

namespace RetailExpress\SkyLink\Controller\Catalogue\Eta;

use DateTimeImmutable;
use DateTimeZone;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Catalogue\Eta\EtaQty as SkyLinkEtaQty;
use RetailExpress\SkyLink\Sdk\Catalogue\Eta\EtaRepositoryFactory as SkyLinkEtaRepositoryFactory;
use RetailExpress\SkyLink\Sdk\Catalogue\Products\ProductId as SkyLinkProductId;

class Index extends Action
{
    private $config;

    private $baseMagentoProductRepository;

    private $skyLinkEtaRepositoryFactory;

    private $storeManager;

    private $jsonResultFactory;

    private $dateTime;

    private $timezone;

    public function __construct(
        Context $context,
        ConfigInterface $config,
        ProductRepositoryInterface $baseMagentoProductRepository,
        SkyLinkEtaRepositoryFactory $skyLinkEtaRepositoryFactory,
        StoreManagerInterface $storeManager,
        JsonResultFactory $jsonResultFactory,
        DateTime $dateTime,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context);

        $this->config = $config;
        $this->baseMagentoProductRepository = $baseMagentoProductRepository;
        $this->skyLinkEtaRepositoryFactory = $skyLinkEtaRepositoryFactory;
        $this->storeManager = $storeManager;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->dateTime = $dateTime;
        $this->timezone = $timezone;
    }

    public function execute()
    {
        /* @var \Magento\Framework\Controller\Result\Json $jsonResult */
        $jsonResult = $this->jsonResultFactory->create();

        /* @var string $magentoProductIdString */
        $magentoProductIdString = $this->getRequest()->getQueryValue('magento_product_id');

        if (null === $magentoProductIdString) {
            return $jsonResult
                ->setHttpResponseCode(400)
                ->setData(['message' => __('Magento Product ID required to retrieve it\'s ETA.')]);
        }

        /* @var SkyLinkProductId|null $skyLinkProductId */
        $skyLinkProductId = $this->getSkyLinkProductId($magentoProductIdString);

        if (null === $skyLinkProductId) {
            return $jsonResult
                ->setHttpResponseCode(422)
                ->setData(['message' => __('The given Magento Product is not associated with a SkyLink Product ID.')]);
        }

        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Eta\EtaRepository $skyLinkEtaRepository */
        $skyLinkEtaRepository = $this->skyLinkEtaRepositoryFactory->create();

        /* @var SkyLinkEtaQty $skyLinkEtaQty */
        $skyLinkEtaQty = new SkyLinkEtaQty($this->getRequest()->getQueryValue('qty', 1));

        /* @var \RetailExpress\SkyLink\Sdk\Catalogue\Eta\Eta|null $skyLinkEta */
        $skyLinkEta = $skyLinkEtaRepository->find(
            $skyLinkProductId,
            $skyLinkEtaQty,
            $this->config->getSalesChannelIdForWebsite($this->storeManager->getWebsite()->getCode())
        );

        if (null === $skyLinkEta) {
            return $jsonResult->setHttpResponseCode(204); // No content = no ETA
        }

        return $jsonResult->setData([
            'date' => $this->getFormattedLocalDate($skyLinkEta->getDate()),
        ]);
    }

    private function getSkyLinkProductId($magentoProductIdString)
    {
        /* @var \Magento\Catalog\Api\Data\ProductInterface $magentoProduct */
        $magentoProduct = $this->baseMagentoProductRepository->getById($magentoProductIdString);

        /* @var \Magento\Framework\Api\AttributeInterface|null $skyLinkProductIdAttribute */
        $skyLinkProductIdAttribute = $magentoProduct->getCustomAttribute('skylink_product_id');

        if (null === $skyLinkProductIdAttribute) {
            return null;
        }

        return new SkyLinkProductId($skyLinkProductIdAttribute->getValue());
    }

    private function getFormattedLocalDate(DateTimeImmutable $date)
    {
        $timezone = new DateTimeZone($this->timezone->getConfigTimezone());
        $date = $date->setTimezone($timezone);

        return $this->dateTime->formatDate($date->getTimestamp(), false);
    }
}
