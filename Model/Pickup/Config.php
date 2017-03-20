<?php

namespace RetailExpress\SkyLink\Model\Pickup;

use InvalidArgumentException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use RetailExpress\SkyLink\Api\Pickup\ConfigInterface;
use RetailExpress\SkyLink\Sdk\Outlets\OutletId as SkyLinkOutletId;

class Config implements ConfigInterface
{
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutletIdsForWebsite(PickupGroup $pickupGroup, $websiteCode)
    {
        $value = $pickupGroup->getValue();

        switch ($value) {
            case 'one':
            case 'two':
                $outletIds = $this->scopeConfig->getValue(
                    "carriers/skylinkpickup/group_{$value}_outlets",
                    'website',
                    $websiteCode
                );
                break;

            default:
                throw new InvalidArgumentException('Unsupported Pickup Group provided.');
        }

        return array_map(function ($outletId) {
            return new SkyLinkOutletId($outletId);
        }, explode(',', $outletIds));
    }
}
