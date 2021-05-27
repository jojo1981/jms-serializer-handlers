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

use Doctrine\Common\Annotations\AnnotationException;
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
use SebastianBergmann\RecursionContext\InvalidArgumentException as SebastianBergmannInvalidArgumentException;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Set\Company;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
final class TypedSetSerializationHandlerTest extends AbstractSerializationTest
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
     * @throws InvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws JmsSerializerInvalidArgumentException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws AnnotationException
     */
    public function invalidConfigurationMissingParametersShouldThrowAnSerializationHandlerException(): void
    {
        $this->expectExceptionObject(SerializationHandlerException::invalidConfigMissingTypeValue(Set::class));
        $this->getSerializer()->deserialize('[]', Set::class, 'json');
    }

    /**
     * @test
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws JmsSerializerInvalidArgumentException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws AnnotationException
     */
    public function invalidConfigurationTypeParameterHasInvalidTypeValueShouldThrowAnSerializationHandlerException(): void
    {
        $this->expectExceptionObject(SerializationHandlerException::invalidConfigTypeValueInvalid(Set::class, 'invalidType'));
        $this->getSerializer()->deserialize('[]', Set::class . '<invalidType>', 'json');
    }

    /**
     * @test
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws JmsSerializerInvalidArgumentException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws AnnotationException
     */
    public function invalidConfigurationTooManyParametersShouldThrowAnSerializationHandlerException(): void
    {
        $this->expectExceptionObject(SerializationHandlerException::invalidConfigTooManyParameters(Set::class, 3));
        $this->getSerializer()->deserialize('[]', Set::class . '<string, arg2, arg3>', 'json');
    }

    /**
     * @test
     *
     * @return void
     * @throws AnnotationException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws JmsSerializerInvalidArgumentException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function toArrayShouldConvertTheCompanyObjectIntoAnArray(): void
    {
        $companyObject = $this->getCompanyObject();
        $companyArray = $this->getCompanyArray();

        self::assertEquals($companyArray, $this->getSerializer()->toArray($companyObject));
    }

    /**
     * @test
     *
     * @return void
     * @throws AnnotationException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws JmsSerializerInvalidArgumentException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function fromArrayShouldConvertAnArrayIntoACompanyObject(): void
    {
        $companyObject = $this->getCompanyObject();
        $companyArray = $this->getCompanyArray();

        self::assertEquals($companyObject, $this->getSerializer()->fromArray($companyArray, Company::class));
    }

    /**
     * @test
     *
     * @return void
     * @throws AnnotationException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws JmsSerializerInvalidArgumentException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function deserializeShouldConvertJsonStringIntoACompanyObject(): void
    {
        $companyObject = $this->getCompanyObject();
        $jsonString = $this->getJsonString();

        self::assertEquals($companyObject, $this->getSerializer()->deserialize($jsonString, Company::class, 'json'));
    }

    /**
     * @test
     *
     * @return void
     * @throws AnnotationException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws JmsSerializerInvalidArgumentException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function serializeShouldConvertACompanyIntoAJsonString(): void
    {
        $companyObject = $this->getCompanyObject();
        $jsonString = $this->getJsonString();

        self::assertEquals($jsonString, $this->getSerializer()->serialize($companyObject, 'json'));
    }

    /**
     * @test
     *
     * @return void
     * @throws AnnotationException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws JmsSerializerInvalidArgumentException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function deserializeShouldConvertXmlStringIntoACompanyObject(): void
    {
        $companyObject = $this->getCompanyObject();
        $xmlString = $this->getXmlString();

        self::assertEquals($companyObject, $this->getSerializer()->deserialize($xmlString, Company::class, 'xml'));
    }

    /**
     * @test
     *
     * @return void
     * @throws AnnotationException
     * @throws ExpectationFailedException
     * @throws HandlerException
     * @throws InvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws JmsSerializerInvalidArgumentException
     * @throws NotAcceptableException
     * @throws SebastianBergmannInvalidArgumentException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     */
    public function serializeShouldConvertACompanyObjectIntoAJsonString(): void
    {
        $companyObject = $this->getCompanyObject();
        $xmlString = $this->getXmlString();

        self::assertEquals($xmlString, $this->getSerializer()->serialize($companyObject, 'xml'));
    }

    /**
     * @test
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws JmsSerializerInvalidArgumentException
     * @throws NotAcceptableException
     * @throws UnsupportedFormatException
     * @throws AnnotationException
     */
    public function deserializeShouldConvertYamlStringIntoACompanyObject(): void
    {
        $this->expectExceptionObject(
            new UnsupportedFormatException('The format "yml" is not supported for deserialization.')
        );

        $this->getSerializer()->deserialize($this->getYamlString(), Company::class, 'yml');
    }

    /**
     * @test
     *
     * @return void
     * @throws HandlerException
     * @throws JmsSerializerLogicException
     * @throws JmsSerializerRuntimeException
     * @throws NotAcceptableException
     * @throws SetException
     * @throws UnsupportedFormatException
     * @throws ValueExceptionInterface
     * @throws AnnotationException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     */
    public function serializeShouldConvertACompanyObjectIntoAYamlString(): void
    {
        $this->expectExceptionObject(
            new UnsupportedFormatException('The format "yml" is not supported for serialization.')
        );

        $companyObject = $this->getCompanyObject();
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
        return $this->getSetCompanyObject(true);
    }
}
