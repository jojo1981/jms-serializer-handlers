<?php
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2021 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Tests;

use Doctrine\Common\Annotations\AnnotationException;
use InvalidArgumentException;
use JMS\Serializer\Exception\InvalidArgumentException as JmsInvalidArgumentException;
use JMS\Serializer\Exception\LogicException as JmsLogicException;
use JMS\Serializer\Exception\NotAcceptableException;
use JMS\Serializer\Exception\RuntimeException as JmsRuntimeException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use Jojo1981\JmsSerializerHandlers\TypedSetSerializationHandler;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\Set;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException as SebastianBergmannInvalidArgumentException;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Set\Company;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Set\Employee;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
class TypedSetSerializationHandlerTest extends TestCase
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
        $this->serializer = (new SerializerBuilder())
            ->setCacheDir(__DIR__ . '/../../var/cache')
            ->setDebug(true)
            ->addMetadataDirs([
                'tests\Jojo1981\JmsSerializerHandlers\Fixtures' => __DIR__ . '/../resources'
            ])
            ->addDefaultHandlers()
            ->configureHandlers(static function (HandlerRegistry $handlerRegistry): void {
                $handlerRegistry->registerSubscribingHandler(new TypedSetSerializationHandler());
            })
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
            ['direction' => 1, 'format' => 'json', 'type' => Set::class, 'method' => 'serializeSet'],
            ['direction' => 2, 'format' => 'json', 'type' => Set::class, 'method' => 'deserializeSet'],
            ['direction' => 1, 'format' => 'xml', 'type' => Set::class, 'method' => 'serializeSet'],
            ['direction' => 2, 'format' => 'xml', 'type' => Set::class, 'method' => 'deserializeSet'],
            ['direction' => 1, 'format' => 'yml', 'type' => Set::class, 'method' => 'serializeSet'],
            ['direction' => 2, 'format' => 'yml', 'type' => Set::class, 'method' => 'deserializeSet'],
        ];

        self::assertEquals($expectedResult, TypedSetSerializationHandler::getSubscribingMethods());
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
            'Invalid config for serialization type: `Jojo1981\TypedSet\Set` given. You MUST add a ' .
            'parameter which contains the value of for the type of the collection. This value can be a primitive type,' .
            ' fully qualified class name or fully qualified interface name'
        ));

        $this->serializer->deserialize('[]', Set::class, 'json');
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
            'Invalid config for serialization type: `Jojo1981\TypedSet\Set` given. The type parameter ' .
            'value: `invalidType` is not valid'
        ));

        $this->serializer->deserialize('[]', Set::class . '<invalidType>', 'json');
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
            'Invalid config for serialization type: `Jojo1981\TypedSet\Set` given. Too many parameters ' .
            'given. This config expect 1 parameter, but got 3 number of parameters given'
        ));

        $this->serializer->deserialize('[]', Set::class . '<string, arg2, arg3>', 'json');
    }

    /**
     * @test
     *
     * @return void
     * @throws HandlerException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ExpectationFailedException
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
     * @throws HandlerException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ExpectationFailedException
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
     * @throws HandlerException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ExpectationFailedException
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
     * @throws HandlerException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ExpectationFailedException
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
     * @throws HandlerException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ExpectationFailedException
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
     * @throws HandlerException
     * @throws JmsLogicException
     * @throws JmsRuntimeException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ExpectationFailedException
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
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws HandlerException
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
     * @return Company
     * @throws HandlerException
     * @throws SetException
     */
    private function getCompanyObject(): Company
    {
        $companyObject = new Company('Apple Computer, Inc.');
        $companyObject->getEmployees()->addAll([
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
