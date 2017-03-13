<?php

namespace RetailExpress\SkyLink\Model\System\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ResourceConnection;

class GuestCustomerId extends Value
{
    public function afterSave()
    {
        $chosenCustomerId = $this->getValue();

        // If there's no Customer ID, we'll delete any configuration around it
        // so that default values are used. @todo determine if this needs a
        // cache refresh?
        if ('' === $chosenCustomerId) {
            $this->getResource()->getConnection()->delete(
                $this->getResource()->getTable('core_config_data'),
                [
                    'path = ?' => 'checkout/options/guest_checkout',
                ]
            );
        }

        return parent::afterSave();
    }
}
