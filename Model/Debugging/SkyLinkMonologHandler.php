<?php

namespace RetailExpress\SkyLink\Model\Debugging;

use Magento\Framework\App\ObjectManager;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Monolog\Formatter\NormalizerFormatter;
use RetailExpress\SkyLink\Api\Debugging\ConfigInterface;
use RetailExpress\SkyLink\Api\Debugging\SkyLinkMonologHandlerInterface;

/**
 * @todo See why dependencies injected through the constructor are breaking the admin login.
 */
class SkyLinkMonologHandler extends AbstractProcessingHandler implements SkyLinkMonologHandlerInterface
{
    use LogHelper;

    const IS_EXCEPTION_KEY = 'is_exception';

    private $config;

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        extract($record['formatted']);

        // We can't use isHandling() because we need to know some information
        // about the payload contained within teh given  "context" array.
        if (false === array_key_exists(self::CONTEXT_KEY, $context)) {
            return;
        }

        // Given that we've got this far, we'll remove the key from the "context" array
        unset($context[self::CONTEXT_KEY]);

        // We'll also remove the "is_exception" key from our logging as we really don't care for that
        unset($context[self::IS_EXCEPTION_KEY]);

        $this->getConnection()->insert(
            $this->getLogsTable(),
            [
                'channel' => $channel,
                'level' => $level,
                'message' => $message,
                'context' => json_encode($context),
                'logged_at' => $datetime,
                'captured' => $this->getConfig()->shouldCaptureLogs()
            ]
        );

        $this->purgeOldLogs();
    }

    protected function getDefaultFormatter()
    {
        return new NormalizerFormatter();
    }

    /**
     * This method takes into account the type of logging (captured or not) and will purge old entries
     * according to the type of logging. Becuase the aim for logging is to be fast, it does not purge
     * all types of logs (captured vs not captured), only the type that is trying to be logged
     * currently.
     *
     * @link http://stackoverflow.com/a/8303440/440966
     */
    private function purgeOldLogs()
    {
        // This query is not the most optimised in the world so we'll just purge according to the configured chance
        $purgingChance = $this->getConfig()->getPurgingChance();
        if ($purgingChance->toNative() < mt_rand(0, 1)) {
            return;
        }

        $captureLogs = $this->getConfig()->shouldCaptureLogs();
        $logsTokeep = $captureLogs ? $this->getConfig()->getCapturedLogsToKeep() : $this->getConfig()->getUncapturedLogsToKeep();

        // @todo see if we can use the query builder for this (I had a few attempts to no avail)
        $this->getConnection()
            ->query(<<<SQL
delete from `{$this->getLogsTable()}`
where `id` <= (
    select `id`
    from (
        select `id`
        from `{$this->getLogsTable()}`
        where `captured` = "{$captureLogs}"
        order by `id` desc
        limit 1 offset {$logsTokeep->toNative()}
    ) `foo`
)
and captured = "{$captureLogs}"
SQL
            );
    }

    private function getConfig()
    {
        if (null === $this->config) {
            $this->config = ObjectManager::getInstance()->get(ConfigInterface::class);
        }

        return $this->config;
    }
}
