<?php

namespace RetailExpress\SkyLinkMagento2\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueWorkCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('skylink:queue:work')
            ->setDescription('Process the next job on a SkyLink queue')
            ->addArgument('connection', InputArgument::OPTIONAL, 'The name of connection')
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL, 'The queue to listen on')
            ->addOption('daemon', null, InputOption::VALUE_NONE, 'Run the worker in daemon mode')
            ->addOption('delay', null, InputOption::VALUE_OPTIONAL, 'Amount of time to delay failed jobs', 0)
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force the worker to run even in maintenance mode')
            ->addOption('memory', null, InputOption::VALUE_OPTIONAL, 'The memory limit in megabytes', 128)
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Number of seconds to sleep when no job is available', 3)
            ->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'Number of times to attempt a job before logging it failed', 0);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}

