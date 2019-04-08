<?php

namespace Purjus\LocoImporterBundle\Command;

use Purjus\LocoImporterBundle\Importer\TranslationImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setDescription('Updates translation files from loco')
            ->addOption(TranslationImporter::IMPORT_OPTION_NO_HEADERS, null, InputOption::VALUE_NONE, 'If set, headers of translation files will be removed');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $output->writeln('Getting translations...');

        foreach ($this->projects as $project => $translationConfig) {
            $output->writeln(sprintf('Getting translations for %s', $project));
            $this->handleConfig($translationConfig, $input->getOptions());
        }

        $output->writeln('Terminated');
    }

    private function handleConfig(array $translationConfig, array $options = [])
    {
        $locales = $this->importer->getLocales($translationConfig['key']);

        $this->output->writeln(sprintf('%d locales found', count($locales)));

        foreach ($locales as $locale) {
            $this->handleLocale($translationConfig['key'], $locale['code'], $translationConfig['file'], $options);
        }
    }

    private function handleLocale(string $apiKey, string $localeCode, string $file, array $options = [])
    {
        $this->output->writeln(sprintf('Handling locale "%s"', $localeCode));

        if (false === ($filePath = $this->importer->importFile($apiKey, $localeCode, $file, $options))) {
            $this->output->writeln(sprintf('Error while importing file "%s" with locale "%s"', $file, $localeCode));
        }

        $this->output->writeln(sprintf('File "%s" overriden', $filePath));
    }
}
