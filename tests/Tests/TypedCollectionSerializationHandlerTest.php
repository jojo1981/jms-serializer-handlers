<?php
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Tests;

use JMS\Serializer\Exception\InvalidArgumentException as JmsInvalidArgumentException;
use JMS\Serializer\Exception\RuntimeException as JmsRuntimeException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use Jojo1981\JmsSerializerHandlers\TypedCollectionSerializationHandler;
use Jojo1981\TypedCollection\Collection;
use Jojo1981\TypedCollection\Exception\CollectionException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Company;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Employee;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
class TypedCollectionSerializationHandlerTest extends TestCase
{
    /** @var Serializer */
    private $serializer;

    /**
     * @return void
     * @throws JmsRuntimeException
     * @throws JmsInvalidArgumentException
     */
    protected function setUp(): void
    {
        $this->serializer = (new SerializerBuilder())
            ->setCacheDir(__DIR__ . '/../../var/cache')
            ->setDebug(true)
            ->addMetadataDirs([
                'tests\Jojo1981\JmsSerializerHandlers\Fixtures' => __DIR__ . '/../resources'
            ])
            ->addDefaultHandlers()
            ->configureHandlers(static function (HandlerRegistry $handlerRegistry): void {
                $handlerRegistry->registerSubscribingHandler(new TypedCollectionSerializationHandler());
            })
            ->build();
    }

    /**
     * @test
     *
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     * @return void
     */
    public function getSubscribingMethodsShouldReturnTheRightSubscribingConfiguration(): void
    {
        $expectedResult = [
            ['direction' => 1, 'format' => 'json', 'type' => Collection::class, 'method' => 'serializeCollection'],
            ['direction' => 2, 'format' => 'json', 'type' => Collection::class, 'method' => 'deserializeCollection'],
            ['direction' => 1, 'format' => 'xml', 'type' => Collection::class, 'method' => 'serializeCollection'],
            ['direction' => 2, 'format' => 'xml', 'type' => Collection::class, 'method' => 'deserializeCollection'],
            ['direction' => 1, 'format' => 'yml', 'type' => Collection::class, 'method' => 'serializeCollection'],
            ['direction' => 2, 'format' => 'yml', 'type' => Collection::class, 'method' => 'deserializeCollection'],
        ];

        self::assertEquals($expectedResult, TypedCollectionSerializationHandler::getSubscribingMethods());
    }

    /**
     * @test
     *
     * @return void
     */
    public function invalidConfigurationMissingParametersShouldThrowAnSerializationHandlerException(): void
    {
        $this->expectExceptionObject(new SerializationHandlerException(
            'Invalid config for serialization type: `Jojo1981\TypedCollection\Collection` given. You MUST add a ' .
            'parameter which contains the value of for the type of the collection. This value can be a primitive type,' .
            ' fully qualified class name or fully qualified interface name'
        ));

        $this->serializer->deserialize('[]', Collection::class, 'json');
    }

    /**
     * @test
     *
     * @return void
     */
    public function invalidConfigurationTypeParameterHasInvalidTypeValueShouldThrowAnSerializationHandlerException(): void
    {
        $this->expectExceptionObject(new SerializationHandlerException(
            'Invalid config for serialization type: `Jojo1981\TypedCollection\Collection` given. The type parameter ' .
            'value: `invalidType` is not valid'
        ));

        $this->serializer->deserialize('[]', Collection::class . '<invalidType>', 'json');
    }

    /**
     * @test
     *
     * @return void
     */
    public function invalidConfigurationTooManyParametersShouldThrowAnSerializationHandlerException(): void
    {
        $this->expectExceptionObject(new SerializationHandlerException(
            'Invalid config for serialization type: `Jojo1981\TypedCollection\Collection` given. Too many parameters ' .
            'given. This config expect 1 parameter, but got 3 number of parameters given'
        ));

        $this->serializer->deserialize('[]', Collection::class . '<string, arg2, arg3>', 'json');
    }

    /**
     * @test
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws JmsRuntimeException
     * @throws CollectionException
     */
    public function toArrayShouldConvertTheCompanyObjectIntoAnArray(): void
    {
        $companyObject = $this->getCompanyObject();
        $companyArray = $this->getCompanyArray();

        self::assertEquals($companyArray, $this->serializer->toArray($companyObject));
    }

    /**
     * @test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws CollectionException
     * @return void
     */
    public function fromArrayShouldConvertAnArrayIntoACompanyObject(): void
    {
        $companyObject = $this->getCompanyObject();
        $companyArray = $this->getCompanyArray();

        self::assertEquals($companyObject, $this->serializer->fromArray($companyArray, Company::class));
    }

    /**
     * @test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws CollectionException
     * @return void
     */
    public function deserializeShouldConvertJsonStringIntoACompanyObject(): void
    {
        $companyObject = $this->getCompanyObject();
        $jsonString = $this->getJsonString();

        self::assertEquals($companyObject, $this->serializer->deserialize($jsonString, Company::class, 'json'));
    }

    /**
     * @test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws CollectionException
     * @return void
     */
    public function serializeShouldConvertACompanyIntoAJsonString(): void
    {
        $companyObject = $this->getCompanyObject();
        $jsonString = $this->getJsonString();

        self::assertEquals($jsonString, $this->serializer->serialize($companyObject, 'json'));
    }

    /**
     * @test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws CollectionException
     * @return void
     */
    public function deserializeShouldConvertXmlStringIntoACompanyObject(): void
    {
        $companyObject = $this->getCompanyObject();
        $xmlString = $this->getXmlString();

        self::assertEquals($companyObject, $this->serializer->deserialize($xmlString, Company::class, 'xml'));
    }

    /**
     * @test
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws CollectionException
     * @return void
     */
    public function serializeShouldConvertACompanyObjectIntoAJsonString(): void
    {
        $companyObject = $this->getCompanyObject();
        $xmlString = $this->getXmlString();

        self::assertEquals($xmlString, $this->serializer->serialize($companyObject, 'xml'));
    }

    /**
     * @test
     *
     * @return void
     */
    public function deserializeShouldConvertYamlStringIntoACompanyObject(): void
    {
        $this->expectExceptionObject(
            new UnsupportedFormatException('The format "yml" is not supported for deserialization.')
        );

        $this->serializer->deserialize($this->getYamlString(), Company::class, 'yml');
    }

    /**
     * @test
     *
     * @return void
     * @throws CollectionException
     */
    public function serializeShouldConvertACompanyObjectIntoAYamlString(): void
    {
        $this->expectExceptionObject(
            new UnsupportedFormatException('The format "yml" is not supported for serialization.')
        );

        $companyObject = $this->getCompanyObject();
        $this->serializer->serialize($companyObject, 'yml');
    }

    /**
     * @throws CollectionException
     * @return Company
     */
    private function getCompanyObject(): Company
    {
        $companyObject = new Company('Apple Computer, Inc.');
        $companyObject->getEmployees()->pushElements([
            new Employee('Joost Nijhuis'),
            new Employee('John Doe')
        ]);

        return $companyObject;
    }

    /**
     * @return array[]
     */
    private function getCompanyArray(): array
    {
        return [
            'name' => 'Apple Computer, Inc.',
            'employees' => [
                ['name' => 'Joost Nijhuis'],
                ['name' => 'John Doe']
            ]
        ];
    }

    /**
     * @return string
     */
    private function getJsonString(): string
    {
        return '{"name":"Apple Computer, Inc.","employees":[{"name":"Joost Nijhuis"},{"name":"John Doe"}]}';
    }

    /**
     * @return string
     */
    private function getXmlString(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<company name="Apple Computer, Inc.">
  <employees>
    <employee name="Joost Nijhuis"/>
    <employee name="John Doe"/>
  </employees>
</company>

XML;
    }

    /**
     * @return string
     */
    private function getYamlString(): string
    {
        return <<<YAML
name: 'Apple Computer, Inc.'
employees:
    -
        name: 'Joost Nijhuis'
    -
        name: 'John Doe'

YAML;

    }
}
