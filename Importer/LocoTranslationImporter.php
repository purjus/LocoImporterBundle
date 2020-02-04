<?php

namespace Purjus\LocoImporterBundle\Importer;

final class LocoTranslationImporter implements TranslationImporter
{
    const DEFAULT_FORMAT = 'symfony';
    const DEFAULT_STATUS = 'translated';

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
    
    public function importFile(string $apiKey, string $localeCode, string $file, string $format, string $status, array $options = [])
    {
        $translationContent = $this->requestLoco(
            $apiKey,
            sprintf('export/locale/%s.yml?format=%s&status=%s', $localeCode, $format, $status)
        );

        if (
            array_key_exists(TranslationImporter::IMPORT_OPTION_NO_HEADERS, $options) &&
            $options[TranslationImporter::IMPORT_OPTION_NO_HEADERS]
        ) {
            $translationContent = preg_replace('/^\#\ .*\n*/m', '', $translationContent);
        }

        $filePath = sprintf('%s/../%s.%s.yml', $this->kernelRootDir, $file, $localeCode);
        $fileRealPath = realpath($filePath);

        if (false === $fileRealPath) {
            if (
                array_key_exists(TranslationImporter::IMPORT_OPTION_CREATE_FILES, $options) &&
                $options[TranslationImporter::IMPORT_OPTION_CREATE_FILES]
            ) {
                touch($filePath);
                $fileRealPath = realpath($filePath);
            } else {
                throw new \InvalidArgumentException(sprintf('Error when getting realpath of "%s". Maybe the file does not exists ?', $filePath));
            }
        }

        return false !== file_put_contents($fileRealPath, $translationContent) ? $fileRealPath : false;
    }
}
