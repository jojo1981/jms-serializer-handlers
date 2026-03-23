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

use DateInvalidTimeZoneException;
use InvalidArgumentException;
use JMS\Serializer\Exception\InvalidArgumentException as JmsSerializerInvalidArgumentException;
use JMS\Serializer\Exception\LogicException as JmsSerializerLogicException;
use JMS\Serializer\Exception\NotAcceptableException;
use JMS\Serializer\Exception\RuntimeException as JmsSerializerRuntimeException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use Jojo1981\Contracts\Exception\ValueExceptionInterface;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use Jojo1981\JmsSerializerHandlers\TypedCollectionSerializationHandler;
use Jojo1981\TypedCollection\Collection;
use Jojo1981\TypedCollection\Exception\CollectionException;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Collection\Company;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
final class TypedCollectionSerializationHandlerTest extends AbstractSerializationTestCase
{
    /**
     * @param HandlerRegistryInterface $handlerRegistry
     * @return void
     */
    protected function configureHandlers(HandlerRegistryInterface $handlerRegistry): void
    {
        $handlerRegistry->registerSubscribingHandler(new TypedCollectionSerializationHandler());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testGetSubscribingMethodsShouldReturnTheRightSubscribingConfiguration(): void
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
     * @return void
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws DateInvalidTimeZoneException
     */
    public function testInvalidConfigurationMissingParametersShouldThrowAnSerializationHandlerException(): void
    {
        $this->expectExceptionObject(SerializationHandlerException::invalidConfigMissingTypeValue(Collection::class));
        $this->getSerializer()->deserialize('[]', Collection::class, 'json');
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws DateInvalidTimeZoneException
     */
    public function testInvalidConfigurationTypeParameterHasInvalidTypeValueShouldThrowAnSerializationHandlerException(): void
    {
        $this->expectExceptionObject(SerializationHandlerException::invalidConfigTypeValueInvalid(Collection::class, 'invalidType'));
        $this->getSerializer()->deserialize('[]', Collection::class . '<invalidType>', 'json');
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws DateInvalidTimeZoneException
     */
    public function testInvalidConfigurationTooManyParametersShouldThrowAnSerializationHandlerException(): void
    {
        $this->expectExceptionObject(SerializationHandlerException::invalidConfigTooManyParameters(Collection::class, 3));
        $this->getSerializer()->deserialize('[]', Collection::class . '<string, arg2, arg3>', 'json');
    }

    /**
     * @return void
     * @throws CollectionException
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function testToArrayShouldConvertTheCompanyObjectIntoAnArray(): void
    {
        $companyObject = self::getCompanyObject();
        $companyArray = self::getCompanyArray();

        self::assertEquals($companyArray, $this->getSerializer()->toArray($companyObject));
    }

    /**
     * @return void
     * @throws CollectionException
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function testFromArrayShouldConvertAnArrayIntoACompanyObject(): void
    {
        $companyObject = self::getCompanyObject();
        $companyArray = self::getCompanyArray();

        self::assertEquals($companyObject, $this->getSerializer()->fromArray($companyArray, Company::class));
    }

    /**
     * @return void
     * @throws CollectionException
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function testDeserializeShouldConvertJsonStringIntoACompanyObject(): void
    {
        $companyObject = self::getCompanyObject();
        $jsonString = self::getJsonString();

        self::assertEquals($companyObject, $this->getSerializer()->deserialize($jsonString, Company::class, 'json'));
    }

    /**
     * @return void
     * @throws CollectionException
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function testSerializeShouldConvertACompanyIntoAJsonString(): void
    {
        $companyObject = self::getCompanyObject();
        $jsonString = self::getJsonString();

        self::assertEquals($jsonString, $this->getSerializer()->serialize($companyObject, 'json'));
    }

    /**
     * @return void
     * @throws CollectionException
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function testDeserializeWithMissingCollectionDataShouldCreateClassWithEmptyCollection(): void
    {
        $companyObject = self::getCompanyObject(false);
        $jsonString = self::getJsonStringWithMissingEmployees();

        self::assertEquals($companyObject, $this->getSerializer()->deserialize($jsonString, Company::class, 'json'));
    }

    /**
     * @return void
     * @throws CollectionException
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function testDeserializeNullValueForCollectionShouldBeSetAsEmptyCollection(): void
    {
        $companyObject = self::getCompanyObject(false);
        $jsonString = self::getJsonStringWithEmployeesAsNullValue();

        self::assertEquals($companyObject, $this->getSerializer()->deserialize($jsonString, Company::class, 'json'));
    }

    /**
     * @return void
     * @throws CollectionException
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function testDeserializeShouldConvertXmlStringIntoACompanyObject(): void
    {
        $companyObject = self::getCompanyObject();
        $xmlString = self::getXmlString();

        self::assertEquals($companyObject, $this->getSerializer()->deserialize($xmlString, Company::class, 'xml'));
    }

    /**
     * @return void
     * @throws CollectionException
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function testSerializeShouldConvertACompanyObjectIntoAJsonString(): void
    {
        $companyObject = self::getCompanyObject();
        $xmlString = self::getXmlString();

        self::assertEquals($xmlString, $this->getSerializer()->serialize($companyObject, 'xml'));
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws DateInvalidTimeZoneException
     */
    public function testDeserializeShouldConvertYamlStringIntoACompanyObject(): void
    {
        $this->expectExceptionObject(
            new UnsupportedFormatException('The format "yml" is not supported for deserialization.')
        );

        $this->getSerializer()->deserialize(self::getYamlString(), Company::class, 'yml');
    }

    /**
     * @return void
     * @throws CollectionException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     * @throws DateInvalidTimeZoneException
     */
    public function testSerializeShouldConvertACompanyObjectIntoAYamlString(): void
    {
        $this->expectExceptionObject(
            new UnsupportedFormatException('The format "yml" is not supported for serialization.')
        );

        $companyObject = self::getCompanyObject();
        $this->getSerializer()->serialize($companyObject, 'yml');
    }

    /**
     * @param bool $withEmployees
     * @return Company
     * @throws ValueExceptionInterface
     * @throws CollectionException
     */
    private function getCompanyObject(bool $withEmployees = true): Company
    {
        return self::getCollectionCompanyObject($withEmployees);
    }
}
