<?php

namespace Purjus\LocoImporterBundle\Command;

use Purjus\LocoImporterBundle\Importer\TranslationImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTranslationsCommand extends Command
{
    /** @var array */
    private $projects;
    /** @var TranslationImporter */
    private $importer;
    /** @var OutputInterface */
    private $output;

    public function __construct(TranslationImporter $importer, array $projects)
    {
        parent::__construct();
        $this->importer = $importer;
        $this->projects = $projects;
    }

    protected function configure()
    {
        $this
            ->setName('purjus:loco:import')
            ->setDescription('Updates translation files from loco');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $output->writeln('Getting translations...');

        foreach ($this->projects as $project => $translationConfig) {
            $output->writeln(sprintf('Getting translations for %s', $project));
            $this->handleConfig($translationConfig);
        }

        $output->writeln('Terminated');
    }

    private function handleConfig(array $translationConfig)
    {
        $locales = $this->importer->getLocales($translationConfig['key']);

        $this->output->writeln(sprintf('%d locales found', count($locales)));

        foreach ($locales as $locale) {
            $this->handleLocale($translationConfig['key'], $locale['code'], $translationConfig['file']);
        }
    }

    private function handleLocale(string $apiKey, string $localeCode, string $file)
    {
        $this->output->writeln(sprintf('Handling locale "%s"', $localeCode));

        if (false === ($filePath = $this->importer->importFile($apiKey, $localeCode, $file))) {
            $this->output->writeln(sprintf('Error while importing file "%s" with locale "%s"', $file, $localeCode));
        }

        $this->output->writeln(sprintf('File "%s" overriden', $filePath));
    }
}
