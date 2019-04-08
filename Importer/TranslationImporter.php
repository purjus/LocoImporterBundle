<?php

namespace Purjus\LocoImporterBundle\Importer;

interface TranslationImporter
{
    const IMPORT_OPTION_NO_HEADERS = 'noHeaders';
    
    public function getLocales(string $apiKey): array;

    /**
     * @return bool|string false on failure, the overriden file on success
     */
    public function importFile(string $apiKey, string $localeCode, string $file, array $options = []);
}
