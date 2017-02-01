<?php

namespace RetailExpress\SkyLink\Model\Eav\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class PickupGroup extends AbstractSource
{
    const VALUE_NONE = 'none';
    const VALUE_1 = '1';
    CONST VALUE_BOTH = 'both';

    /**
     * {@inheritdoc}
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                [
                    'value' => self::VALUE_NONE,
                    'label' => __('No Pickup Allowed'),
                ],
                [
                    'value' => self::VALUE_1,
                    'label' => __('Pickup from Group 1 Only'),
                ],
                [
                    'value' => self::VALUE_NONE,
                    'label' => __('Pickup from Group 1 or Group 2'),
                ],
            ];
        }

        return $this->_options;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionArray()
    {
        $options = [];

        array_map(function (array $option) use (&$options) {
            $options[$option['value']] = $option['label'];
        }, $this->getAllOptions());

        return $options;
    }
}
