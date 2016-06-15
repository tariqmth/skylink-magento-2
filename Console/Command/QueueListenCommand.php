<?php

namespace RetailExpress\SkyLinkMagento2\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueListenCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('skylink:queue:listen')
            ->setDescription('Listen to a SkyLink queue')
            ->addArgument('connection', InputArgument::OPTIONAL, 'The name of connection')
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL, 'The queue to listen on', null)
            ->addOption('delay', null, InputOption::VALUE_OPTIONAL, 'Amount of time to delay failed jobs', 0)
            ->addOption('memory', null, InputOption::VALUE_OPTIONAL, 'The memory limit in megabytes', 128)
            ->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'Seconds a job may run before timing out', 60)
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Seconds to wait before checking queue for jobs', 3)
            ->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'Number of times to attempt a job before logging it failed', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}

