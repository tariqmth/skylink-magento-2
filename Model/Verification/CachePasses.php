<?php

namespace RetailExpress\SkyLinkMagento2\Model\Verification;

trait CachePasses
{
    private $passes;

    private function cachePasses(callable $callback)
    {
        if (null === $this->passes) {
            $this->passes = $callback();
        }

        return $this->passes;
    }
}
