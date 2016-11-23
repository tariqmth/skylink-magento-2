<?php

namespace RetailExpress\SkyLink\Model\Sales\Orders;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Orders\MagentoOrderAddressExtractorInterface;
use RetailExpress\SkyLink\Exceptions\Sales\Orders\NoMagentoShippingAddressException;

class MagentoOrderAddressExtractor implements MagentoOrderAddressExtractorInterface
{
    private $magentoOrderAddressRepository;

    private $searchCriteriaBuilder;

    public function __construct(
        OrderAddressRepositoryInterface $magentoOrderAddressRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->magentoOrderAddressRepository = $magentoOrderAddressRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function extractBillingAddress(OrderInterface $magentoOrder)
    {
        return $magentoOrder->getBillingAddress();
    }

    /**
     * {@inheritdoc}
     */
    public function extractShippingAddress(OrderInterface $magentoOrder)
    {
        // Filter by the given order and address type
        $this->searchCriteriaBuilder->addFilter(OrderAddressInterface::PARENT_ID, $magentoOrder->getEntityId());
        $this->searchCriteriaBuilder->addFilter(OrderAddressInterface::ADDRESS_TYPE, 'shipping');

        /* @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder->create();

        /* @var \\Magento\Sales\Api\Data\OrderAddressSearchResultInterface $searchResults */
        $searchResults = $this->magentoOrderAddressRepository->getList($searchCriteria);

        // There should always be only one shipping address
        if (1 !== $searchResults->getTotalCount()) {
            throw NoMagentoShippingAddressException::withMagentoOrder($magentoOrder);
        }

        return current($searchResults->getItems());
    }
}
