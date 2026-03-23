<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2026 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Tests;

use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\UnknownClassOrInterfaceException;
use Prophecy\Exception\Doubler\DoubleException;
use Prophecy\Exception\Doubler\InterfaceNotFoundException;
use Prophecy\Exception\InvalidArgumentException;
use Prophecy\Exception\Prophecy\MethodProphecyException;
use Prophecy\Exception\Prophecy\ObjectProphecyException;
use Prophecy\PhpUnit\ProphecyTrait;
use stdClass;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Serialization\ValueSerializationHandler;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Value\Age;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Value\Operator;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
final class AbstractSerializationTestCoverageTest extends AbstractSerializationTestCase
{
    use ProphecyTrait;

    /**
     * @param HandlerRegistryInterface $handlerRegistry
     * @return void
     */
    protected function configureHandlers(HandlerRegistryInterface $handlerRegistry): void
    {
        // No-op for coverage
    }

    /**
     * @return void
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     * @throws SerializationHandlerException
     * @throws Exception
     * @throws UnknownClassOrInterfaceException
     * @throws DoubleException
     * @throws InterfaceNotFoundException
     * @throws InvalidArgumentException
     * @throws MethodProphecyException
     * @throws ObjectProphecyException
     */
    public function testAssertTypeCoversAllBranches(): void
    {
        $handler = new ValueSerializationHandler();

        // Covers: visitString branch in deserializeValue (line 58)
        $visitorProphecy = $this->prophesize(DeserializationVisitorInterface::class);
        $visitorProphecy->visitString('foo', ['name' => 'string', 'params' => []])->willReturn('+');
        $visitor = $visitorProphecy->reveal();
        $typeString = [
            'name' => Operator::class,
            'params' => [['name' => 'string']]
        ];
        $result = $handler->deserializeValue($visitor, 'foo', $typeString);
        self::assertInstanceOf(Operator::class, $result);
        self::assertSame('+', $result->getValue());

        // Covers: missing 'name' key (line 76)
        $typeMissingName = ['params' => [['name' => 'int']]];
        try {
            $handler->assertType($typeMissingName);
            $this->fail('Expected exception for missing name not thrown');
        } catch (SerializationHandlerException $e) {
            self::assertStringContainsString('Missing name', $e->getMessage());
        }

        // Covers: param count error (line 80)
        $typeMissingParams = ['name' => Age::class];
        try {
            $handler->assertType($typeMissingParams);
            $this->fail('Expected exception for missing params not thrown');
        } catch (SerializationHandlerException $e) {
            self::assertStringContainsString('used without a type parameter', $e->getMessage());
        }

        // Covers: class does not exist (line 83)
        $typeNonExistentClass = ['name' => 'NonExistentClass', 'params' => [['name' => 'int']]];
        try {
            $handler->assertType($typeNonExistentClass);
            $this->fail('Expected exception for non-existent class not thrown');
        } catch (SerializationHandlerException $e) {
            self::assertStringContainsString('not an existing class name', $e->getMessage());
        }

        // Covers: does not implement ValueInterface (lines 86-90)
        $typeNotValueInterface = ['name' => stdClass::class, 'params' => [['name' => 'int']]];
        try {
            $handler->assertType($typeNotValueInterface);
            $this->fail('Expected exception for not implementing ValueInterface not thrown');
        } catch (SerializationHandlerException $e) {
            self::assertStringContainsString('not an instance of', $e->getMessage());
        }

        // Covers: invalid inner type (line 94)
        $typeInvalidInnerType = ['name' => Age::class, 'params' => [['name' => 'float']]];
        try {
            $handler->assertType($typeInvalidInnerType);
            $this->fail('Expected exception for invalid inner type not thrown');
        } catch (SerializationHandlerException $e) {
            self::assertStringContainsString('invalid inner type', $e->getMessage());
        }
    }
}
