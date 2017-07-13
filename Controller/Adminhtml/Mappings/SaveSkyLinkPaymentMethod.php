<?php

namespace RetailExpress\SkyLink\Controller\Adminhtml\Mappings;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use RetailExpress\SkyLink\Api\Sales\Payments\MagentoPaymentMethodRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\SkyLinkPaymentMethodRepositoryInterface;
use RetailExpress\SkyLink\Api\Sales\Payments\SkyLinkPaymentMethodServiceInterface;
use RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethodId as SkyLinkPaymentMethodId;

class SaveSkyLinkPaymentMethod extends Action
{
    private $magentoPaymentMethodRepository;

    private $skyLinkPaymentMethodRepository;

    private $skyLinkPaymentMethodService;

    public function __construct(
        Context $context,
        MagentoPaymentMethodRepositoryInterface $magentoPaymentMethodRepository,
        SkyLinkPaymentMethodRepositoryInterface $skyLinkPaymentMethodRepository,
        SkyLinkPaymentMethodServiceInterface $skyLinkPaymentMethodService
    ) {
        parent::__construct($context);
        $this->magentoPaymentMethodRepository = $magentoPaymentMethodRepository;
        $this->skyLinkPaymentMethodRepository = $skyLinkPaymentMethodRepository;
        $this->skyLinkPaymentMethodService = $skyLinkPaymentMethodService;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        array_walk(
            $data['skylink_payment_method_mappings'],
            function ($skyLinkPaymentMethodId, $magentoPaymentMethodCode) {

                /* @var \Magento\Payment\Model\MethodInterface $magentoPaymentMethod */
                $magentoPaymentMethod = $this->magentoPaymentMethodRepository->get($magentoPaymentMethodCode);

                /* @var \RetailExpress\SkyLink\Sdk\Sales\Payments\PaymentMethod $skyLinkPaymentMethod */
                $skyLinkPaymentMethod = $this
                    ->skyLinkPaymentMethodRepository
                    ->getById(new SkyLinkPaymentMethodId($skyLinkPaymentMethodId));

                $this->skyLinkPaymentMethodService->mapSkyLinkPaymentMethodForMagentoPaymentMethod(
                    $skyLinkPaymentMethod,
                    $magentoPaymentMethod
                );
            }
        );

        $this->messageManager->addSuccess(__('Successfully mapped Magento Payment Methods to SkyLink Payment Methods.'));

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');

        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('RetailExpress_SkyLink::skylink_mappings_save');
    }
}
