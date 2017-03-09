<?php

namespace RetailExpress\SkyLink\Block\Adminhtml\Mappings;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Payment\Model\MethodInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\MagentoPaymentMethodRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\SkyLinkPaymentMethodRepositoryInterface;

class MagentoPaymentMethod extends Template
{
    private $magentoPaymentMethodRepository;

    private $skyLinkPaymentMethodRepository;

    public function __construct(
        TemplateContext $templateContext,
        MagentoPaymentMethodRepositoryInterface $magentoPaymentMethodRepository,
        SkyLinkPaymentMethodRepositoryInterface $skyLinkPaymentMethodRepository
    ) {
        parent::__construct($templateContext);
        $this->magentoPaymentMethodRepository = $magentoPaymentMethodRepository;
        $this->skyLinkPaymentMethodRepository = $skyLinkPaymentMethodRepository;
    }

    /**
     * @return MethodInterface[]
     */
    public function getMagentoPaymentMethods()
    {
        return $this->magentoPaymentMethodRepository->getList();
    }

    /**
     * @return \RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethod[]
     */
    public function getSkyLinkPaymentMethods()
    {
        return $this->skyLinkPaymentMethodRepository->getList();
    }

    /**
     * @return \RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethod|null
     */
    public function getSkyLinkPaymentMethodForMagentoPaymentMethod(MethodInterface $magentoPaymentMethod)
    {
        return $this
            ->skyLinkPaymentMethodRepository
            ->getSkyLinkPaymentMethodForMagentoPaymentMethod($magentoPaymentMethod);
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/saveSkyLinkPaymentMethod');
    }
}
