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

use Doctrine\Common\Annotations\AnnotationException;
use InvalidArgumentException;
use JMS\Serializer\Accessor\DefaultAccessorStrategy;
use JMS\Serializer\Construction\UnserializeObjectConstructor;
use JMS\Serializer\Exception\InvalidArgumentException as JmsInvalidArgumentException;
use JMS\Serializer\Exception\LogicException as JmsLogicException;
use JMS\Serializer\Exception\NotAcceptableException;
use JMS\Serializer\Exception\RuntimeException as JmsRuntimeException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use Jojo1981\JmsSerializerHandlers\TypedCollectionAccessorStrategyDecorator;
use Jojo1981\JmsSerializerHandlers\TypedCollectionObjectConstructorDecorator;
use Jojo1981\JmsSerializerHandlers\TypedCollectionSerializationHandler;
use Jojo1981\TypedCollection\Collection;
use Jojo1981\TypedCollection\Exception\CollectionException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException as SebastianBergmannInvalidArgumentException;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Collection\Company;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Collection\Employee;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
class TypedCollectionSerializationHandlerTest extends TestCase
{
    /** @var Serializer|null */
    private ?Serializer $serializer = null;

    /**
     * @return void
     * @throws JmsRuntimeException
     * @throws AnnotationException
     * @throws InvalidArgumentException
     * @throws JmsLogicException
     * @throws JmsInvalidArgumentException
     */
    protected function setUp(): void
    {
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
            ->configureHandlers(static function (HandlerRegistry $handlerRegistry): void {
                $handlerRegistry->registerSubscribingHandler(new TypedCollectionSerializationHandler());
            })
            ->setObjectConstructor(new TypedCollectionObjectConstructorDecorator(new UnserializeObjectConstructor()))
            ->build();
    }

    /**
     * @test
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws SebastianBergmannInvalidArgumentException
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
     * @throws JmsRuntimeException
     * @throws UnsupportedFormatException
     * @throws NotAcceptableException
     * @throws JmsLogicException
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
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws JmsLogicException
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
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws JmsLogicException
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
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws UnsupportedFormatException
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
     * @return void
     * @throws ExpectationFailedException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws UnsupportedFormatException
     * @throws CollectionException
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
     * @return void
     * @throws ExpectationFailedException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws UnsupportedFormatException
     * @throws CollectionException
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
     * @return void
     * @throws ExpectationFailedException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws UnsupportedFormatException
     * @throws CollectionException
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
     * @return void
     * @throws ExpectationFailedException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws UnsupportedFormatException
     * @throws CollectionException
     */
    public function deserializeWithMissingCollectionDataShouldCreateClassWithEmptyCollection(): void
    {
        $companyObject = $this->getCompanyObject(false);
        $jsonString = $this->getJsonStringWithMissingEmployees();

        self::assertEquals($companyObject, $this->serializer->deserialize($jsonString, Company::class, 'json'));
    }

    /**
     * @test
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws UnsupportedFormatException
     * @throws CollectionException
     */
    public function deserializeNullValueForCollectionShouldBeSetAsEmptyCollection(): void
    {
        $companyObject = $this->getCompanyObject(false);
        $jsonString = $this->getJsonStringWithEmployeesAsNullValue();

        self::assertEquals($companyObject, $this->serializer->deserialize($jsonString, Company::class, 'json'));
    }

    /**
     * @test
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws UnsupportedFormatException
     * @throws CollectionException
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
     * @return void
     * @throws ExpectationFailedException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws UnsupportedFormatException
     * @throws CollectionException
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
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws JmsLogicException
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
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
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
     * @param bool $withEmployees
     * @return Company
     * @throws CollectionException
     */
    private function getCompanyObject(bool $withEmployees = true): Company
    {
        $companyObject = new Company('Apple Computer, Inc.');
        if ($withEmployees) {
            $companyObject->getEmployees()->pushElements([
                new Employee('Joost Nijhuis'),
                new Employee('John Doe')
            ]);
        }

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
    private function getJsonStringWithMissingEmployees(): string
    {
        return '{"name":"Apple Computer, Inc."}';
    }

    /**
     * @return string
     */
    private function getJsonStringWithEmployeesAsNullValue(): string
    {
        return '{"name":"Apple Computer, Inc.","employees": null}';
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
