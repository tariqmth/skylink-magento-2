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

    public function __construct(
        SkyLinkAttributeCodeRepositoryInterface $skyLinkAttributeCodeRepository,
        CommandBusInterface $commandBus
    ) {
        $this->skyLinkAttributeCodeRepository = $skyLinkAttributeCodeRepository;
        $this->commandBus = $commandBus;

        parent::__construct('retail-express:skylink:bulk-attributes');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription('Gets a list of Attributes from Retail Express and queues a command for each one to sync');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output);
        $progressBar->start();

        /* @var SkyLinkAttributeCode[] $skyLinkAttributeCodes */
        $skyLinkAttributeCodes = $this->skyLinkAttributeCodeRepository->getList();

        // Loop over our Price Groups and add dispatch a command to sync each
        array_walk($skyLinkAttributeCodes, function (SkyLinkAttributeCode $skyLinkAttributeCode) use ($progressBar) {

            $command = new SyncSkyLinkAttributeToMagentoAttributeCommand();
            $command->skyLinkAttributeCode = (string) $skyLinkAttributeCode;

            $this->commandBus->handle($command);

            $progressBar->advance();
        });

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(sprintf(<<<'MESSAGE'
<info>%s Retail Express Attributes have had commands queued to sync them.
Ensure that an instance of 'retail-express:command-bus:consume-queue attributes' is running to perform the actual sync.</info>
MESSAGE
            ,
            count($skyLinkAttributeCodes)
        ));
    }
}
