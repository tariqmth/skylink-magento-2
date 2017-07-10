<?php

namespace RetailExpress\SkyLink\Console\Command;

use DateTimeImmutable;
use DateTimeZone;
use RetailExpress\CommandBus\Api\CommandBusInterface;
use RetailExpress\SkyLink\Api\Catalogue\Attributes\SkyLinkAttributeCodeRepositoryInterface;
use RetailExpress\SkyLink\Api\ConfigInterface;
use RetailExpress\SkyLink\Commands\Catalogue\Attributes\SyncSkyLinkAttributeToMagentoAttributeCommand;
use RetailExpress\SkyLink\Sdk\Catalogue\Attributes\AttributeCode as SkyLinkAttributeCode;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BulkAttributesCommand extends Command
{
    private $skyLinkAttributeCodeRepository;

    private $commandBus;

    private $adminEmulator;

    public function __construct(
        SkyLinkAttributeCodeRepositoryInterface $skyLinkAttributeCodeRepository,
        CommandBusInterface $commandBus,
        AdminEmulator $adminEmulator
    ) {
        $this->skyLinkAttributeCodeRepository = $skyLinkAttributeCodeRepository;
        $this->commandBus = $commandBus;
        $this->adminEmulator = $adminEmulator;

        parent::__construct('retail-express:skylink:bulk-attributes');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Gets a list of Attributes from Retail Express and queues a command for each one to sync')
            ->addOption('inline', null, InputOption::VALUE_NONE, 'Flag to sync inline rather than queue a command');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var bool $shouldBeQueued */
        $shouldBeQueued = $this->shouldBeQueued($input);

        if (true === $shouldBeQueued) {
            $output->writeln('Fetching Attributes from Retail Express...');
        } else {
            $output->writeln('Syncing Attributes from Retail Express...');
        }

        /* @var SkyLinkAttributeCode[] $skyLinkAttributeCodes */
        $skyLinkAttributeCodes = $this->skyLinkAttributeCodeRepository->getList();

        $progressBar = new ProgressBar($output, count($skyLinkAttributeCodes));
        $progressBar->start();

        // Loop over our Price Groups and add dispatch a command to sync each
        array_walk(
            $skyLinkAttributeCodes,
            function (SkyLinkAttributeCode $skyLinkAttributeCode) use ($shouldBeQueued, $progressBar) {

                $command = new SyncSkyLinkAttributeToMagentoAttributeCommand();
                $command->skyLinkAttributeCode = (string) $skyLinkAttributeCode;
                $command->shouldBeQueued = $shouldBeQueued;

                if (true === $shouldBeQueued) {
                    $this->commandBus->handle($command);
                } else {
                    $this->adminEmulator->onAdmin(function () use ($command) {
                        $this->commandBus->handle($command);
                    });
                }

                $progressBar->advance();
            }
        );

        $progressBar->finish();
        $output->writeln('');

        if (true === $shouldBeQueued) {
            $output->writeln(sprintf(<<<'MESSAGE'
<info>%s Retail Express Attributes have had commands queued to sync them.
Ensure that an instance of 'retail-express:command-bus:consume-queue attributes' is running to perform the actual sync.</info>
MESSAGE
                ,
                count($skyLinkAttributeCodes)
            ));
        } else {
            $output->writeln(sprintf('<info>%s Attributes have been synced.</info>', count($skyLinkAttributeCodes)));
        }
    }

    /**
     * Determines if the command should be qeueud.
     *
     * @return bool
     */
    private function shouldBeQueued(InputInterface $input)
    {
        return !$input->getOption('inline');
    }
}
