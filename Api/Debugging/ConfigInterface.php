<?php

namespace RetailExpress\SkyLink\Api\Debugging;

interface ConfigInterface
{
    /**
     * Returns if new log entries should be captured.
     *
     * @return bool
     */
    public function shouldCaptureLogs();

    /**
     * Returns the number of uncaptured logs to keep.
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getUncapturedLogsToKeep();

    /**
     * Returns the number of captured logs to keep.
     *
     * @return \ValueObjects\Number\Integer
     */
    public function getCapturedLogsToKeep();

    /**
     * Returns the chance that logs should be purged.
     *
     * @return \ValueObjects\Number\Real
     */
    public function getPurgingChance();
}
