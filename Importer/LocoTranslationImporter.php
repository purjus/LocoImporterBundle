<?php

namespace Purjus\LocoImporterBundle\Importer;

final class LocoTranslationImporter implements TranslationImporter
{
    /** @var string */
    private $kernelRootDir;
    
    public function __construct(string $kernelRootDir)
    {
        $this->kernelRootDir = $kernelRootDir;
    }

    private function requestLoco(string $apiKey, string $url): string
    {
        $auth = base64_encode($apiKey.':');
        $context = stream_context_create([
            'http' => ['header' => 'Authorization: Basic '.$auth],
        ]);

        return file_get_contents('https://localise.biz/api/'.ltrim($url, '/'), false, $context);
    }
    
    public function getLocales(string $apiKey): array
    {
        $locales = json_decode($this->requestLoco($apiKey, 'locales'), true);

        if (null === $locales || JSON_ERROR_NONE !== json_last_error()) {
            return [];
        }

        return $locales;
    }
    
    public function importFile(string $apiKey, string $localeCode, string $file)
    {
        $translationContent = $this->requestLoco($apiKey, sprintf('export/locale/%s.yml?format=symfony', $localeCode));

        $filePath = sprintf('%s/../%s.%s.yml', $this->kernelRootDir, $file, $localeCode);
        $fileRealPath = realpath($filePath);

        if (false === $fileRealPath) {
            throw new \InvalidArgumentException(sprintf('Error when getting realpath of "%s". Maybe the file does not exists ?', $filePath));
        }

        $appRealPath = realpath(sprintf('%s/..', $this->kernelRootDir));

        if (0 !== strpos(realpath($fileRealPath), $appRealPath)) {
            throw new \InvalidArgumentException(sprintf('Filepath must be in app scope. Can\'t import file in "%s"', $fileRealPath));
        }

        return false !== file_put_contents($fileRealPath, $translationContent) ? $fileRealPath : false;
    }
}
