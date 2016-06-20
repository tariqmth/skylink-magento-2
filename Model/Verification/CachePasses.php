<?php

namespace RetailExpress\SkyLink\Model\Verification;

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
