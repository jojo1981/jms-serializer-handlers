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

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use Jojo1981\JmsSerializerHandlers\TypedCollectionObjectConstructorDecorator;
use Jojo1981\TypedCollection\Collection;
use Jojo1981\TypedCollection\Exception\CollectionException;
use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\UnknownClassOrInterfaceException;
use ReflectionException;
use stdClass;
use function get_class;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
final class TypedCollectionObjectConstructorDecoratorTest extends TestCase
{
    /**
     * @return void
     * @throws NoPreviousThrowableException
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws Exception
     * @throws ReflectionException
     * @throws CollectionException
     */
    public function testDelegatesToWrappedObjectConstructor(): void
    {
        $wrapped = $this->createMock(ObjectConstructorInterface::class);
        $visitor = $this->createMock(DeserializationVisitorInterface::class);
        $metadata = $this->createMock(ClassMetadata::class);
        $context = $this->createMock(DeserializationContext::class);
        $data = ['foo' => 'bar'];
        $type = ['name' => 'SomeClass'];
        $expectedObject = new stdClass();
        $wrapped->expects($this->once())
            ->method('construct')
            ->with($visitor, $metadata, $data, $type, $context)
            ->willReturn($expectedObject);
        $decorator = new TypedCollectionObjectConstructorDecorator($wrapped);
        $result = $decorator->construct($visitor, $metadata, $data, $type, $context);
        self::assertSame($expectedObject, $result);
    }

    /**
     * @return void
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ReflectionException
     * @throws PHPUnitException
     * @throws UnknownClassOrInterfaceException
     * @throws CollectionException
     */
    public function testInitializesCollectionProperty(): void
    {
        $wrapped = $this->createMock(ObjectConstructorInterface::class);
        $visitor = $this->createMock(DeserializationVisitorInterface::class);
        $context = $this->createMock(DeserializationContext::class);
        $object = new class {
            /** @var Collection<int>> */
            public Collection $collection;
        };
        $property = new PropertyMetadata('SomeClass', 'collection');
        $property->type = [
            'name' => Collection::class,
            'params' => [['name' => 'int']]
        ];
        $property->class = get_class($object);
        $property->name = 'collection';
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->propertyMetadata = [$property];
        $wrapped->method('construct')->willReturn($object);
        $decorator = new TypedCollectionObjectConstructorDecorator($wrapped);
        $result = $decorator->construct($visitor, $metadata, [], [], $context);
        self::assertInstanceOf(Collection::class, $result->collection);
        self::assertSame('int', $result->collection->getType());
    }

    /**
     * @return void
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ReflectionException
     * @throws CollectionException
     */
    public function testInitializesArrayProperty(): void
    {
        $wrapped = $this->createMock(ObjectConstructorInterface::class);
        $visitor = $this->createMock(DeserializationVisitorInterface::class);
        $context = $this->createMock(DeserializationContext::class);
        $object = new class {
            public array $arr;
        };
        $property = new PropertyMetadata('SomeClass', 'arr');
        $property->type = ['name' => 'array'];
        $property->class = get_class($object);
        $property->name = 'arr';
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->propertyMetadata = [$property];
        $wrapped->method('construct')->willReturn($object);
        $decorator = new TypedCollectionObjectConstructorDecorator($wrapped);
        $result = $decorator->construct($visitor, $metadata, [], [], $context);
        self::assertSame([], $result->arr);
    }

    /**
     * @return void
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ReflectionException
     * @throws CollectionException
     */
    public function testSkipsPropertyWithNoType(): void
    {
        $wrapped = $this->createMock(ObjectConstructorInterface::class);
        $visitor = $this->createMock(DeserializationVisitorInterface::class);
        $context = $this->createMock(DeserializationContext::class);
        $object = new class {
            public string $foo = 'bar';
        };
        $property = new PropertyMetadata('SomeClass', 'foo');
        $property->type = null;
        $property->class = get_class($object);
        $property->name = 'foo';
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->propertyMetadata = [$property];
        $wrapped->method('construct')->willReturn($object);
        $decorator = new TypedCollectionObjectConstructorDecorator($wrapped);
        $result = $decorator->construct($visitor, $metadata, [], [], $context);
        self::assertSame('bar', $result->foo);
    }

    /**
     * @return void
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     * @throws ReflectionException
     * @throws CollectionException
     */
    public function testSetsNullablePropertyToNullWhenSkipWhenEmptyIsTrue(): void
    {
        $wrapped = $this->createMock(ObjectConstructorInterface::class);
        $visitor = $this->createMock(DeserializationVisitorInterface::class);
        $context = $this->createMock(DeserializationContext::class);
        $object = new class {
            public ?string $nullableString = 'not-null';
        };
        $property = new PropertyMetadata('SomeClass', 'nullableString');
        $property->type = ['name' => 'string'];
        $property->class = get_class($object);
        $property->name = 'nullableString';
        $property->skipWhenEmpty = true;
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->propertyMetadata = [$property];
        $wrapped->method('construct')->willReturn($object);
        $decorator = new TypedCollectionObjectConstructorDecorator($wrapped);
        $result = $decorator->construct($visitor, $metadata, [], [], $context);
        self::assertNull($result->nullableString);
    }
}
