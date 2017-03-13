<?php

namespace RetailExpress\SkyLink\Block\Adminhtml\Sales\Order;

use Magento\Backend\Block\Template\Context as TemplateContext;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Helper\Admin as AdminSalesHelper;
use RetailExpress\SkyLink\Model\Sales\Orders\OrderExtensionAttributes;

class View extends AbstractOrder
{
    use OrderExtensionAttributes;

    public function __construct(
        TemplateContext $templateContext,
        Registry $registry,
        AdminSalesHelper $adminSalesHelper,
        OrderExtensionFactory $orderExtensionFactory,
        array $data = []
    ) {
        parent::__construct($templateContext, $registry, $adminSalesHelper, $data);

        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    public function getSkyLinkOrderId()
    {
        $magentoOrder = $this->getOrder();

        return $this->getOrderExtensionAttributes($magentoOrder)->getSkylinkOrderId();
    }
}
