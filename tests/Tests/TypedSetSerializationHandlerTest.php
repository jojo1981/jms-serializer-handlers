<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2021 Joost Nijhuis <jnijhuis81@gmail.com>
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
use Jojo1981\JmsSerializerHandlers\TypedSetSerializationHandler;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\Set;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Set\Company;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
final class TypedSetSerializationHandlerTest extends AbstractSerializationTestCase
{
    /**
     * @param HandlerRegistryInterface $handlerRegistry
     * @return void
     */
    protected function configureHandlers(HandlerRegistryInterface $handlerRegistry): void
    {
        $handlerRegistry->registerSubscribingHandler(new TypedSetSerializationHandler());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testGetSubscribingMethodsShouldReturnTheRightSubscribingConfiguration(): void
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
        $this->expectExceptionObject(SerializationHandlerException::invalidConfigMissingTypeValue(Set::class));
        $this->getSerializer()->deserialize('[]', Set::class, 'json');
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
        $this->expectExceptionObject(SerializationHandlerException::invalidConfigTypeValueInvalid(Set::class, 'invalidType'));
        $this->getSerializer()->deserialize('[]', Set::class . '<invalidType>', 'json');
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
        $this->expectExceptionObject(SerializationHandlerException::invalidConfigTooManyParameters(Set::class, 3));
        $this->getSerializer()->deserialize('[]', Set::class . '<string, arg2, arg3>', 'json');
    }

    /**
     * @return void
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws SetException
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
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws SetException
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
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws SetException
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
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws SetException
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
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws SetException
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
     * @throws DateInvalidTimeZoneException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws SetException
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
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws SetException
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
     * @return Company
     * @throws SetException
     * @throws ValueExceptionInterface
     * @throws HandlerException
     */
    private function getCompanyObject(): Company
    {
        return self::getSetCompanyObject(true);
    }
}
