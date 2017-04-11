<?php

namespace RetailExpress\SkyLink\Api\Debugging;

interface LogManagerInterface
{
    /**
     * Gets a list of log entries, with an optional ID to pass in as the "since"
     * parameter.
     *
     * @param int|null $sinceId
     *
     * @return array
     */
    public function getList($sinceId = null);

    /**
     * Clears all logs.
     *
     * @return void
     */
    public function clearAll();
}
