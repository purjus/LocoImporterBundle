<?php

namespace Purjus\LocoImporterBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTranslationsCommand extends Command
{
    private $locoTranslationsApiKeys;
    private $kernelRootDir;

    public function __construct(string $kernelRootDir, array $locoTranslationsApiKeys)
    {
        parent::__construct();
        $this->kernelRootDir = $kernelRootDir;
        $this->locoTranslationsApiKeys = $locoTranslationsApiKeys;
    }

    protected function configure()
    {
        $this
            ->setName('purjus:loco:import')
            ->setDescription('Updates translation files from loco');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Getting translations...');

        foreach ($this->locoTranslationsApiKeys as $project => $translationConfig) {
            $output->writeln(sprintf('Getting translations for %s', $project));
            $this->handleConfig($translationConfig);
        }

        $output->writeln('Terminated');
    }

    private function handleConfig(array $translationConfig)
    {
        $locales = $this->getLocales($translationConfig['key']);

        $this->writeln(sprintf('%d locales found', count($locales)));

        foreach ($locales as $locale) {
            $this->handleLocale($translationConfig['key'], $locale['code'], $translationConfig['file']);
        }
    }

    private function requestLoco(string $apiKey, string $url): Client
    {
        $auth = base64_encode($url.':');
        $context = stream_context_create([
            'http' => ['header' => 'Authorization: Basic '.$auth],
        ]);
        return file_get_contents('https://localise.biz/api/'.ltrim($url, '/'), false, $context);
    }

    private function getLocales(string $apiKey): array
    {
        $locales = json_decode($this->requestLoco($apiKey, 'locales'), true);

        if (null === $locales || JSON_ERROR_NONE !== json_last_error()) {
            return [];
        }

        return $locales;
    }

    private function handleLocale(string $apiKey, string $localeCode, string $file)
    {
        $this->writeln(sprintf('Handling locale "%s"', $localeCode));

        $translationContent = $this->requestLoco($apiKey, sprintf('export/locale/%s.yml?format=symfony', $localeCode));

        $filePath = sprintf('%s/../%s.%s.yml', $this->kernelRootDir, $file, $localeCode);

        file_put_contents($filePath, $translationContent);

        $this->writeln(sprintf('File "%s" overriden', $filePath));
    }
}
