<?php

namespace RetailExpress\SkyLink\Model\System\Config\Source;

trait NumberFormatter
{
    /**
     * @var \Magento\Framework\Intl\NumberFormatterFactory
     */
    private $numberFormatterFactory;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \NumberFromatter
     */
    private $numberFormatter;

    private function getNumberFormatter()
    {
        if (null === $this->numberFormatter) {
            /* @var \NumberFormatter $numberFormatter */
            $this->numberFormatter = $this->numberFormatterFactory->create(
                $this->localeResolver->getLocale(),
                \NumberFormatter::DECIMAL
            );
        }

        return $this->numberFormatter;
    }
}
