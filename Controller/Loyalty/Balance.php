<?php

namespace RetailExpress\SkyLink\Controller\Loyalty;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use RetailExpress\SkyLink\Sdk\Customers\CustomerId as SkyLinkCustomerId;
use RetailExpress\SkyLink\Sdk\Loyalty\LoyaltyRepositoryFactory;

class Balance extends Action
{
    private $customerSession;

    private $magentoCustomerRepository;

    private $skyLinkLoyaltyRepositoryFactory;

    private $jsonResultFactory;

    public function __construct(
        Context $context,
        Session $customerSession,
        CustomerRepositoryInterface $magentoCustomerRepository,
        LoyaltyRepositoryFactory $skyLinkLoyaltyRepositoryFactory,
        JsonResultFactory $jsonResultFactory
    ) {
        parent::__construct($context);

        $this->customerSession = $customerSession;
        $this->magentoCustomerRepository = $magentoCustomerRepository;
        $this->skyLinkLoyaltyRepositoryFactory = $skyLinkLoyaltyRepositoryFactory;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {
        /* @var \Magento\Framework\Controller\Result\Json $jsonResult */
        $jsonResult = $this->jsonResultFactory->create();

        $magentoCustomerId = $this->customerSession->getCustomerId();
        $magentoCustomerId = 1; // @todo remove

        if (null === $magentoCustomerId) {
            return $jsonResult->setHttpResponseCode(403);
        }

        /* @var \Magento\Customer\Api\Data\CustomerInterface $magentoCustomer */
        $magentoCustomer = $this->magentoCustomerRepository->getById($magentoCustomerId);

        $skyLinkCustomerIdAttribute = $magentoCustomer->getCustomAttribute('skylink_customer_id');

        /* @var \RetailExpress\SkyLink\Sdk\Loyalty\LoyaltyRepository $skyLinkLoyaltyRepository */
        $skyLinkLoyaltyRepository = $this->skyLinkLoyaltyRepositoryFactory->create();

        /* @var \Magento\Framework\Api\AttributeInterface|null $skyLinkCUstomerIdAttribute */
        $skyLinkCustomerIdAttribute = $magentoCustomer->getCustomAttribute('skylink_customer_id');

        if (null === $skyLinkCustomerIdAttribute) {
            return $jsonResult
                ->setHttpResponseCode(422)
                ->setData(['message' => __('The given Magento Customer is not associated with a SkyLink Customer ID.')]);
        }

        $skyLinkCustomerId = new SkyLinkCustomerId($skyLinkCustomerIdAttribute->getValue());

        /* @var \RetailExpress\SkyLink\Sdk\Loyalty\Loyalty $loyaltayBalacne */
        $loyaltyBalance = $skyLinkLoyaltyRepository->find($skyLinkCustomerId);

        return $jsonResult->setData(['loyalty_balance' => $loyaltyBalance->toNative()]);
    }
}
