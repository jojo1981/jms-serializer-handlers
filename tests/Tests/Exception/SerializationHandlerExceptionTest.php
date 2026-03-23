<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2026 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Tests\Exception;

use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException as PHPUnitInvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\UnknownClassOrInterfaceException;
use Throwable;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests\Exception
 */
final class SerializationHandlerExceptionTest extends TestCase
{
    /**
     * @return void
     * @throws ExpectationFailedException
     * @throws UnknownClassOrInterfaceException
     * @throws PHPUnitException
     */
    public function testInvalidConfigMissingTypeValue(): void
    {
        $exception = SerializationHandlerException::invalidConfigMissingTypeValue('HandlerClass');
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        self::assertInstanceOf(SerializationHandlerException::class, $exception);
        self::assertStringContainsString('HandlerClass', $exception->getMessage());
        self::assertStringContainsString('MUST add a parameter', $exception->getMessage());
    }

    /**
     * @return void
     * @throws PHPUnitException
     * @throws ExpectationFailedException
     * @throws PHPUnitInvalidArgumentException
     * @throws Exception
     * @throws UnknownClassOrInterfaceException
     * @throws NoPreviousThrowableException
     */
    public function testInvalidConfigTypeValueInvalid(): void
    {
        $prev = $this->createMock(Throwable::class);
        $exception = SerializationHandlerException::invalidConfigTypeValueInvalid('HandlerClass', 'badType', $prev);
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        self::assertInstanceOf(SerializationHandlerException::class, $exception);
        self::assertStringContainsString('HandlerClass', $exception->getMessage());
        self::assertStringContainsString('badType', $exception->getMessage());
        self::assertSame($prev, $exception->getPrevious());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testInvalidConfigTooManyParameters(): void
    {
        $exception = SerializationHandlerException::invalidConfigTooManyParameters('HandlerClass', 3);
        self::assertStringContainsString('Too many parameters', $exception->getMessage());
        self::assertStringContainsString('3', $exception->getMessage());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testInvalidConfigMissingName(): void
    {
        $exception = SerializationHandlerException::invalidConfigMissingName('HandlerClass');
        self::assertStringContainsString('Missing name', $exception->getMessage());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testInvalidConfigParameterCountIsIncorrectZero(): void
    {
        $exception = SerializationHandlerException::invalidConfigParameterCountIsIncorrect('HandlerClass', 0, 'ValueClass');
        self::assertStringContainsString('used without a type parameter', $exception->getMessage());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testInvalidConfigParameterCountIsIncorrectMany(): void
    {
        $exception = SerializationHandlerException::invalidConfigParameterCountIsIncorrect('HandlerClass', 2, 'ValueClass');
        self::assertStringContainsString('used with too many parameters', $exception->getMessage());
        self::assertStringContainsString('2', $exception->getMessage());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testInvalidConfigParameterTypeValueClassIsNotExistingClass(): void
    {
        $exception = SerializationHandlerException::invalidConfigParameterTypeValueClassIsNotExistingClass('HandlerClass', 'ValueClass');
        self::assertStringContainsString('not an existing class name', $exception->getMessage());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testInvalidConfigParameterTypeValueClassDoesNotImplementValueInterface(): void
    {
        $exception = SerializationHandlerException::invalidConfigParameterTypeValueClassDoesNotImplementValueInterface('HandlerClass', 'ValueClass',
            'Interface');
        self::assertStringContainsString('not an instance of', $exception->getMessage());
        self::assertStringContainsString('Interface', $exception->getMessage());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testInvalidConfigParameterInnerTypeNotValid(): void
    {
        $exception = SerializationHandlerException::invalidConfigParameterInnerTypeNotValid('HandlerClass', 'ValueClass', 'badType');
        self::assertStringContainsString('invalid inner type', $exception->getMessage());
        self::assertStringContainsString('badType', $exception->getMessage());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testDeserializeTypeNameMissingInData(): void
    {
        $exception = SerializationHandlerException::deserializeTypeNameMissingInData('HandlerClass');
        self::assertStringContainsString('Missing key', $exception->getMessage());
        self::assertStringContainsString('__typename', $exception->getMessage());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testInvalidClassNameConfigured(): void
    {
        $exception = SerializationHandlerException::invalidClassNameConfigured('HandlerClass', 'ClassName', ['A', 'B', 'C']);
        self::assertStringContainsString('Class name: `ClassName`', $exception->getMessage());
        self::assertStringContainsString('A, B, C', $exception->getMessage());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testNoTypesConfigured(): void
    {
        $exception = SerializationHandlerException::noTypesConfigured('HandlerClass');
        self::assertStringContainsString('No types are configured', $exception->getMessage());
    }
}
