<?php

namespace RetailExpress\SkyLinkMagento2\Controller\Dev;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use RetailExpress\SkyLinkMagento2\Model\RepositoryFactory as SkyLinkRepositoryFactory;
use RetailExpress\SkyLinkMagento2\Model\Config as SkyLinkConfig;

class Repositories extends Action
{
    private $skyLinkRepositoryFactory;

    public function __construct(Context $context, SkyLinkRepositoryFactory $skyLinkRepositoryFactory)
    {
        $this->skyLinkRepositoryFactory = $skyLinkRepositoryFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        var_dump(
            $this->skyLinkRepositoryFactory->createCatalogueAttributeRepository(),
            $this->skyLinkRepositoryFactory->createCatalogueProductRepository(),
            $this->skyLinkRepositoryFactory->createCustomerRepository(),
            $this->skyLinkRepositoryFactory->createOutletRepository(),
            $this->skyLinkRepositoryFactory->createSalesOrderRepository(),
            $this->skyLinkRepositoryFactory->createSalesPaymentMethodRepository(),
            $this->skyLinkRepositoryFactory->createVoucherRepository()
        );
    }
}
