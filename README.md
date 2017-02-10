#BFW Logger
Advanced log module for BFW framework

BFW Logger manage your log records by sending its into log files and allow you to rotate, compress and flush your logs (all fully configurable).
This module implements the PSR-3 interface and default classes, so you can use PSR-3 log levels and PSR-3 logger interface methods. 

##Before installing

You must have installed BFW framework before using this log module. You can find more information about BFW here : https://github.com/bulton-fr/bfw

---

##Installation

We recommand using composer for installing "BFW Logger". You can get composer by executing this command line :
```bash
$ curl -sS https://getcomposer.org/installer | php
```

Create (or open) the "composer.json" file at the root of your project and add :
```json
{
    "require": {
        "dremecker/bfw-logger": "~2.0"
    }
}
```

Then launch module installation by executing:
```bash
$ php composer.phar install
```

And launch BFW module loading :
```bash
$ /vendor/bin/bfw_installModules
```


##Configuring BFW Logger

After a successfull install, you will find in "app/configs/bfw-logger" the configs.php file that is used to configure options for the logger and the log handlers and allows you to create your defaults channels

This file is commented and full of multiple examples, so we will not go further for the moment.


---

##Using BFW Logger

The logger instance is initialised for each page request. You can access it with:
```php
<?php


$logger = \BFW\Application::getInstance()->getModule('bfw-logger')->logger;
```

Before sending message to the logger, you must set a channel. For doing so:
```php
<?php

$logger->setChannel('ChannelName');
```

Because BFW Logger implements PSR-3 standarts, you can call PSR-3 logger interface methods just like this:
```php
<?php

$logger->setChannel('ChannelName');

$logger->warning('Warning Message');
$logger->error('Error Message !');
```

PSR-3 log() method is also available (see PSR-3 standarts for more informations):
```php
<?php

$logger->setChannel('ChannelName');

$logger->log($PSR3_LogLevel, 'Message', $context);
```

You can also call BFW Logger archiveLogFiles() method for processing log files archiving. We recommend you to call this method into a crontab php script for avoiding latency due to compression (if you have enabled it), and huge file rotation. This method purpose is to offer you a way to replace logrotate, in a very light and simple form, if you cannot access it on your actual hosting service.

As you may have notice, you always have to set channel before using it. It's a bit different with archiveLogFiles() method. If you have not preset channels in configuration file, you will have to set ALL CHANNELS USED IN YOUR PROJECT that you want to archive before calling archiveLogFiles() method just like this:
```php
<?php

$logger->setChannel('ChannelName-1');
$logger->setChannel('ChannelName-2');

$logger->archiveLogFiles();
```

If all your channels have been setup into configuration file, you can just call archiveLogFiles() method without anything else:
```php
<?php

$logger->archiveLogFiles();
```
