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

use JMS\Serializer\Accessor\AccessorStrategyInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
use Jojo1981\JmsSerializerHandlers\TypedCollectionAccessorStrategyDecorator;
use Jojo1981\TypedCollection\Collection;
use Jojo1981\TypedCollection\Exception\CollectionException;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
final class TypedCollectionAccessorStrategyDecoratorTest extends TestCase
{
    /**
     * Test that getValue delegates to the wrapped accessor strategy.
     *
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws NoPreviousThrowableException
     */
    public function testGetValueDelegatesToWrappedStrategy(): void
    {
        $object = new stdClass();
        $metadata = $this->createMock(PropertyMetadata::class);
        $context = $this->createMock(SerializationContext::class);
        $expectedValue = 'foo';

        /** @var AccessorStrategyInterface|MockObject $wrapped */
        $wrapped = $this->createMock(AccessorStrategyInterface::class);
        $wrapped->expects($this->once())
            ->method('getValue')
            ->with($object, $metadata, $context)
            ->willReturn($expectedValue);

        $decorator = new TypedCollectionAccessorStrategyDecorator($wrapped);
        $result = $decorator->getValue($object, $metadata, $context);
        self::assertSame($expectedValue, $result);
    }

    /**
     * Test setValue delegates to the wrapped accessor strategy when not a collection.
     *
     * @return void
     * @throws NoPreviousThrowableException
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws CollectionException
     */
    public function testSetValueDelegatesToWrappedStrategyWhenNotCollection(): void
    {
        $object = new stdClass();
        $metadata = $this->createMock(PropertyMetadata::class);
        $context = $this->createMock(DeserializationContext::class);
        $value = 'bar';

        /** @var AccessorStrategyInterface|MockObject $wrapped */
        $wrapped = $this->createMock(AccessorStrategyInterface::class);
        $wrapped->expects($this->once())
            ->method('setValue')
            ->with($object, $value, $metadata, $context);

        $decorator = new TypedCollectionAccessorStrategyDecorator($wrapped);
        $decorator->setValue($object, $value, $metadata, $context);
    }

    /**
     * Test setValue creates a Collection if value is null and type is Collection.
     *
     * @return void
     * @throws NoPreviousThrowableException
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws CollectionException
     */
    public function testSetValueCreatesCollectionWhenValueIsNullAndTypeIsCollection(): void
    {
        $object = new stdClass();
        $metadata = $this->createMock(PropertyMetadata::class);
        $metadata->type = [
            'name' => Collection::class,
            'params' => [['name' => 'int']]
        ];
        $context = $this->createMock(DeserializationContext::class);

        /** @var AccessorStrategyInterface|MockObject $wrapped */
        $wrapped = $this->createMock(AccessorStrategyInterface::class);
        $wrapped->expects($this->once())
            ->method('setValue')
            ->with(
                $object,
                $this->callback(function ($collection) {
                    return $collection instanceof Collection && $collection->getType() === 'int';
                }),
                $metadata,
                $context
            );

        $decorator = new TypedCollectionAccessorStrategyDecorator($wrapped);
        $decorator->setValue($object, null, $metadata, $context);
    }
}
