<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Tests;

use Doctrine\Common\Annotations\AnnotationException;
use InvalidArgumentException;
use JMS\Serializer\Accessor\DefaultAccessorStrategy;
use JMS\Serializer\Construction\UnserializeObjectConstructor;
use JMS\Serializer\Exception\InvalidArgumentException as JmsInvalidArgumentException;
use JMS\Serializer\Exception\LogicException as JmsLogicException;
use JMS\Serializer\Exception\RuntimeException as JmsRuntimeException;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Jojo1981\Contracts\Exception\ValueExceptionInterface;
use Jojo1981\JmsSerializerHandlers\TypedCollectionAccessorStrategyDecorator;
use Jojo1981\JmsSerializerHandlers\TypedCollectionObjectConstructorDecorator;
use Jojo1981\TypedCollection\Exception\CollectionException;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Collection\Company as CollectionCompany;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Entity\Employee;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Serialization\ValueSerializationHandler;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Set\Company as SetCompany;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Value\Age;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
abstract class AbstractSerializationTest extends TestCase
{
    /** @var Serializer|null */
    private ?Serializer $serializer = null;

    /**
     * @return Serializer
     * @throws InvalidArgumentException
     * @throws JmsInvalidArgumentException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws AnnotationException
     */
    final protected function getSerializer(): Serializer
    {
        if (null === $this->serializer) {
            $propertyNamingStrategy = new SerializedNameAnnotationStrategy(new CamelCaseNamingStrategy());
            $accessorStrategy = new TypedCollectionAccessorStrategyDecorator(new DefaultAccessorStrategy());

            $this->serializer = (new SerializerBuilder())
                ->setDebug(true)
                ->setCacheDir(__DIR__ . '/../../var/cache')
                ->setAccessorStrategy($accessorStrategy)
                ->setPropertyNamingStrategy($propertyNamingStrategy)
                ->addMetadataDirs([
                    'tests\Jojo1981\JmsSerializerHandlers\Fixtures' => __DIR__ . '/../resources'
                ])
                ->addDefaultHandlers()
                ->configureHandlers(function (HandlerRegistryInterface $handlerRegistry): void {
                    $handlerRegistry->registerSubscribingHandler(new ValueSerializationHandler());
                    $this->configureHandlers($handlerRegistry);
                })
                ->setObjectConstructor(new TypedCollectionObjectConstructorDecorator(new UnserializeObjectConstructor()))
                ->build();
        }

        return $this->serializer;
    }

    /**
     * @param HandlerRegistryInterface $handlerRegistry
     * @return void
     */
    abstract protected function configureHandlers(HandlerRegistryInterface $handlerRegistry): void;

    /**
     * @param bool $withEmployees
     * @return SetCompany
     * @throws RuntimeException
     * @throws SetException
     * @throws ValueExceptionInterface
     * @throws HandlerException
     */
    final protected function getSetCompanyObject(bool $withEmployees): SetCompany
    {
        $companyObject = new SetCompany('Apple Computer, Inc.');
        if ($withEmployees) {
            $companyObject->getEmployees()->addAll([
                new Employee('Joost Nijhuis', new Age(40)),
                new Employee('John Doe', new Age(25))
            ]);
        }

        return $companyObject;
    }

    /**
     * @param bool $withEmployees
     * @return CollectionCompany
     * @throws ValueExceptionInterface
     * @throws RuntimeException
     * @throws CollectionException
     */
    final protected function getCollectionCompanyObject(bool $withEmployees): CollectionCompany
    {
        $companyObject = new CollectionCompany('Apple Computer, Inc.');
        if ($withEmployees) {
            $companyObject->getEmployees()->pushElements([
                new Employee('Joost Nijhuis', new Age(40)),
                new Employee('John Doe', new Age(25))
            ]);
        }

        return $companyObject;
    }

    /**
     * @return array[]
     */
    final protected function getCompanyArray(): array
    {
        return [
            'name' => 'Apple Computer, Inc.',
            'employees' => [
                ['name' => 'Joost Nijhuis', 'age' => 40],
                ['name' => 'John Doe', 'age' => 25]
            ]
        ];
    }

    /**
     * @return string
     */
    final protected function getJsonString(): string
    {
        return '{"name":"Apple Computer, Inc.","employees":[{"name":"Joost Nijhuis","age":40},{"name":"John Doe","age":25}]}';
    }

    /**
     * @return string
     */
    final protected function getXmlString(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<company name="Apple Computer, Inc.">
  <employees>
    <employee name="Joost Nijhuis" age="40"/>
    <employee name="John Doe" age="25"/>
  </employees>
</company>

XML;
    }

    /**
     * @return string
     */
    final protected function getYamlString(): string
    {
        return <<<YAML
name: 'Apple Computer, Inc.'
employees:
    -
        name: 'Joost Nijhuis'
        age: 40
    -
        name: 'John Doe'
        age: 25

YAML;
    }

    /**
     * @return string
     */
    final protected function getJsonStringWithMissingEmployees(): string
    {
        return '{"name":"Apple Computer, Inc."}';
    }

    /**
     * @return string
     */
    final protected function getJsonStringWithEmployeesAsNullValue(): string
    {
        return '{"name":"Apple Computer, Inc.","employees": null}';
    }
}
