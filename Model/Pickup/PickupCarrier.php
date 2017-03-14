<?php

namespace RetailExpress\SkyLink\Model\Pickup;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory as RateMethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory as RateResultFactory;
use Psr\Log\LoggerInterface;
use RetailExpress\SkyLink\Api\Pickup\MagentoPickupGroupChooserInterface;
use RetailExpress\SkyLink\Api\Pickup\PickupManagementInterface;
use RetailExpress\SkyLink\Api\Pickup\PickupOutletRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;

class PickupCarrier extends AbstractCarrier implements CarrierInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_isFixed = true;

    private $pickupOutletRepository;

    private $magentoProductRepository;

    private $magentoPickupGroupChooser;

    private $pickupManagement;

    private $rateResultFactory;

    private $rateMethodFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RateErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        PickupOutletRepositoryInterface $pickupOutletRepository,
        ProductRepositoryInterface $magentoProductRepository,
        MagentoPickupGroupChooserInterface $magentoPickupGroupChooser,
        PickupManagementInterface $pickupManagement,
        RateResultFactory $rateResultFactory,
        RateMethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->pickupOutletRepository = $pickupOutletRepository;
        $this->magentoProductRepository = $magentoProductRepository;
        $this->magentoPickupGroupChooser = $magentoPickupGroupChooser;
        $this->pickupManagement = $pickupManagement;
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;

        // Set our code
        $this->_code = $this->pickupManagement->getMagentoShippingCarrierCode();

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        // Let's determine the pickup group
        $magentoProducts = $this->getMagentoProducts($request);
        $pickupGroup = $this->magentoPickupGroupChooser->choosePickupGroup($magentoProducts);

        // If we got a "none" Pickup Group, we can't offer this method
        if ($pickupGroup->sameValueAs(PickupGroup::get('none'))) {
            return false;
        }

        // Grab appropriate outlets for the pickup group
        $outlets = $this->pickupOutletRepository->getListForPickupGroup($pickupGroup);

        if (count($outlets) === 0) {
            return false;
        }

        /* @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        array_map(function (SkyLinkOutlet $skyLinkOutlet) use ($result) {

            /* @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->pickupManagement->getMagentoShippingMethodCode($skyLinkOutlet));
            $method->setMethodTitle($this->pickupManagement->getMagentoShippingMethodTitle($skyLinkOutlet));

            $method->setPrice(0);
            $method->setCost(0);

            $result->append($method);
        }, $outlets);

        return $result;
    }

    private function getMagentoProducts(RateRequest $request)
    {
        $products = [];

        array_map(function (CartItemInterface $cartItem) use (&$products) {
            $sku = $cartItem->getSku();

            // If there's no product or we've already got the product, skip out
            if (null === $sku || array_key_exists($sku, $products)) {
            }

            $products[$sku] = $this->magentoProductRepository->get($sku);
        }, $request->getAllItems());

        return array_values($products);
    }
}
