<?php

namespace RetailExpress\SkyLink\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory as RateErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory as RateMethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory as RateResultFactory;
use Psr\Log\LoggerInterface;
use RetailExpress\SkyLink\Api\Outlets\SkyLinkOutletRepositoryInterface;
use RetailExpress\SkyLink\Sdk\Outlets\Outlet as SkyLinkOutlet;

class Pickup extends AbstractCarrier implements CarrierInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_code = 'skylink_pickup';

    /**
     * {@inheritdoc}
     */
    protected $_isFixed = true;

    private $skyLinkOutletRepository;

    private $rateResultFactory;

    private $rateMethodFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RateErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        SkyLinkOutletRepositoryInterface $skyLinkOutletRepository,
        RateResultFactory $rateResultFactory,
        RateMethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->skyLinkOutletRepository = $skyLinkOutletRepository;
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['skylink_pickup' => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /* @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        array_map(function (SkyLinkOutlet $skyLinkOutlet) use ($result) {

            /* @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
            $method = $this->rateMethodFactory->create();

            $method->setCarrier('skylink_pickup');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod(sprintf('outlet_%s', $skyLinkOutlet->getId()));
            $method->setMethodTitle(sprintf(
                '%s - %s',
                $skyLinkOutlet->getName(),
                $skyLinkOutlet->getAddress()
            ));

            $method->setPrice(0);
            $method->setCost(0);

            $result->append($method);

        }, $this->skyLinkOutletRepository->getList());

        return $result;
    }
}
