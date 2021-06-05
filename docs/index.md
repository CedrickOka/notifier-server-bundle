Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require coka/notifier-server-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require coka/notifier-server-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Oka\Notifier\ServerBundle\OkaNotifierServerBundle::class => ['all' => true],
];
```

### Step 3: Create your SendReport class

The goal of this bundle is to  persist some `SendReport` class to a database (MongoDB, MySQL). 
Your first job, then, is to create the `SendReport` class for you application. 
This class can look and act however you want: add any
properties or methods you find useful. This is *your* `SendReport` class.

The bundle provides base classes which are already mapped for most fields
to make it easier to create your entity. Here is how you use it:

1. Extend the base `SendReport` class (from the `Model` folder)
2. Map the `id` field. It must be protected as it is inherited from the parent class.

**Warning:**

> When you extend from the mapped superclass provided by the bundle, don't
> redefine the mapping for the other fields as it is provided by the bundle.

Your `SendReport` class can live inside any bundle in your application. For example,
if you work at "Acme" company, then you might create a bundle called `AcmeNotifierBundle`
and place your `SendReport` class in it.

In the following sections, you'll see examples of how your `SendReport` class should
look, depending on how you're storing your documents.

**Warning:**

> If you override the __construct() method in your SendReport class, be sure
> to call parent::__construct(), as the base SendReport class depends on
> this to initialize some fields.

#### Doctrine MongoDB ODM SendReport class

you must persisting your document via the Doctrine MongoDB ODM, then your `SendReport` class
should live in the `App\Document` namespace of your bundle and look like this to
start:

##### Annotations

```php
<?php
// App\Document\SendReport.php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Oka\Notifier\ServerBundle\Model\SendReport as BaseSendReport;

/**
 * @MongoDB\Document(collection="send_report")
 */
class SendReport extends BaseSendReport
{
    /**
     * @MongoDB\Id()
     *
     * @var string
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        
        // your own logic
    }
}
```

##### XML

If you use xml to configure Doctrine you must add two files. The Document and the mongodb-odm.xml:

```php
<?php
// App\Document\SendReport.php

namespace App\Document;

use Oka\Notifier\ServerBundle\Model\SendReport as BaseSendReport;

class SendReport extends BaseSendReport
{
	public function __construct()
	{
		parent::__construct();
		
		// your own logic
	}
}
```

```xml
# config/doctrine/SendReport.mongodb-odm.xml
<?xml version="1.0" encoding="UTF-8"?>
<doctrine-mongo-mapping xmlns="http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping
                    http://doctrine-project.org/schemas/odm/doctrine-mongo-mapping.xsd"
>
    <document name="App\Document\SendReport">
        <id />
    </document>
</doctrine-mongo-mapping>
```

### Step 4: Configure the Bundle

Add the following configuration in the file `config/packages/oka_notifier_server.yaml`.

```yaml
# config/packages/oka_notifier_server.yaml
oka_notifier_server:
    channels:
        sms:
            enabled: true
        smpp:
            dsn: 'smpp://localhost'
        clickatell:
            url: 'http://localhost'
            token: 'secret'
        infobip:
            url: 'http://localhost'
            api_key: 'secret'
        firebase:
            enabled: true
    reporting:
        class_name: App\Document\SendReport
```

If enable notification reporting controller install "coka/pagination-bundle" symfony bundle and add the following configuration to your `config/packages/oka_pagination.yaml`.

```yaml
# config/packages/oka_pagination.yaml
oka_pagination:
    db_driver: mongodb
    filters:
        issuedAt:
            searchable: true
            orderable: true
    sort:
        order:
            issuedAt: desc
    pagination_managers:
        send_report:
            class: App\Document\SendReport
            filters:
                channel:
                    searchable: true
                    orderable: true
```

### Step 5: Update your database schema

Now that the bundle is configured, the last thing you need to do is update your
database schema because you have added a new document, the `SendReport` class which you
created in Step 4.

Run the following command.

```console
$ php bin/console doctrine:mongodb:schema:create
```

You now can access at the index page `http://acme.com/`!

## How use this?

Now that the bundle is installed you now send notification

```console
$ curl -i http://acme.com/v1/rest/notifications -X POST -H 'Content-Type: application/json' -d '{"notifications": [{"channels": ["sms"], "sender": "ACME", "receiver": "2250707070707", "message": "Hello World!"}]}'
```
