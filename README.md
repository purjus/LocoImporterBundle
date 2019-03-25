Installation
============

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require purjus/loco-importer-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Purjus\LocoImporterBundle\PurjusLocoImporterBundle(),
        ];

        // ...
    }

    // ...
}
```

### Step 3: Configure the Bundle

Then, configure the bundle by adding the following configuration to `app/config/config.yml` for non-flex application, or `config/packages/purjus_loco_importer.yaml` for flex applications :

```yaml
purjus_loco_importer:
    projects:
        my_project_name:
            key: '<localise.biz API Key>'
            file: 'translations/messages' # path to the file, relative to project root, without locale nor extension
            # here, the imported files will go to "translations/messages.<locale>.yaml"
```
