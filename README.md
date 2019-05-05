Handlers for the JMS Serializer 
=====================

[![Build Status](https://travis-ci.com/jojo1981/jms-serializer-handlers.svg?branch=master)](https://travis-ci.com/jojo1981/jms-serializer-handlers)
[![Coverage Status](https://coveralls.io/repos/github/jojo1981/jms-serializer-handlers/badge.svg)](https://coveralls.io/github/jojo1981/jms-serializer-handlers)
[![Latest Stable Version](https://poser.pugx.org/jojo1981/jms-serializer-handlers/v/stable)](https://packagist.org/packages/jojo1981/jms-serializer-handlers)
[![Total Downloads](https://poser.pugx.org/jojo1981/jms-serializer-handlers/downloads)](https://packagist.org/packages/jojo1981/jms-serializer-handlers)
[![License](https://poser.pugx.org/jojo1981/jms-serializer-handlers/license)](https://packagist.org/packages/jojo1981/jms-serializer-handlers)

Author: Joost Nijhuis <[jnijhuis81@gmail.com](mailto:jnijhuis81@gmail.com)>

This library is an extension for the `jms/serializer` package and contains custom handlers which add support to work with some 3th party libraries.
More information about JMS Serializer Handlers can be found [here](https://jmsyst.com/libs/serializer/master/handlers).

This library adds support for:
- instances of `Jojo1981\TypedCollection\Collection` from the package `jojo1981/typed-collection`.

## Installation

### Library

```bash
git clone https://github.com/jojo1981/jms-serializer-handlers.git
```

### Composer

[Install PHP Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require jojo1981/jms-serializer-handlers
```

## Usage

```php
<?php

require 'vendor/autoload.php';

$serializer = (new \JMS\Serializer\SerializerBuilder())
    ->addDefaultHandlers()
    ->configureHandlers(static function (\JMS\Serializer\Handler\HandlerRegistry $handlerRegistry): void {
        $handlerRegistry->registerSubscribingHandler(
            new \Jojo1981\JmsSerializerHandlers\TypedCollectionSerializationHandler()
        );
    })
    ->build();
```
