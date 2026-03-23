<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 John Doe <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Tests;

use DateInvalidTimeZoneException;
use InvalidArgumentException;
use JMS\Serializer\Context;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\InvalidArgumentException as JmsSerializerInvalidArgumentException;
use JMS\Serializer\Exception\LogicException;
use JMS\Serializer\Exception\NotAcceptableException;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\HandlerRegistryInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use JMS\Serializer\VisitorInterface;
use Jojo1981\Contracts\Exception\ValueExceptionInterface;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use Jojo1981\JmsSerializerHandlers\TypedCollectionSerializationHandler;
use Jojo1981\JmsSerializerHandlers\TypedSetSerializationHandler;
use Jojo1981\JmsSerializerHandlers\UnionSerializationHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Exception as PHPUnitException;
use PHPUnit\Framework\ExpectationFailedException;
use Prophecy\Exception\Doubler\DoubleException;
use Prophecy\Exception\Doubler\InterfaceNotFoundException;
use Prophecy\Exception\InvalidArgumentException as ProphecyInvalidArgumentException;
use Prophecy\Exception\Prophecy\MethodProphecyException;
use Prophecy\Exception\Prophecy\ObjectProphecyException;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionClass;
use ReflectionException;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Entity\Author;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Entity\Book;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Entity\Employee;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Entity\MediaContainer;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Entity\Movie;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Value\Age;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Tests
 */
final class UnionSerializerHandlerTest extends AbstractSerializationTestCase
{
    use ProphecyTrait;

    /**
     * @param HandlerRegistryInterface $handlerRegistry
     * @return void
     */
    protected function configureHandlers(HandlerRegistryInterface $handlerRegistry): void
    {
        $handlerRegistry->registerSubscribingHandler(new TypedCollectionSerializationHandler());
        $handlerRegistry->registerSubscribingHandler(new TypedSetSerializationHandler());
        $handlerRegistry->registerSubscribingHandler(new UnionSerializationHandler());
    }

    /**
     * @return void
     * @throws ExpectationFailedException
     */
    public function testGetSubscribingMethodsShouldReturnTheRightSubscribingConfiguration(): void
    {
        $expectedResult = [
            ['direction' => 1, 'format' => 'json', 'type' => 'union', 'method' => 'serializeUnion'],
            ['direction' => 2, 'format' => 'json', 'type' => 'union', 'method' => 'deserializeUnion'],
            ['direction' => 1, 'format' => 'xml', 'type' => 'union', 'method' => 'serializeUnion'],
            ['direction' => 2, 'format' => 'xml', 'type' => 'union', 'method' => 'deserializeUnion'],
            ['direction' => 1, 'format' => 'yml', 'type' => 'union', 'method' => 'serializeUnion'],
            ['direction' => 2, 'format' => 'yml', 'type' => 'union', 'method' => 'deserializeUnion']
        ];

        self::assertEquals($expectedResult, UnionSerializationHandler::getSubscribingMethods());
    }

    /**
     * @return void
     * @throws InterfaceNotFoundException
     * @throws NotAcceptableException
     * @throws ObjectProphecyException
     * @throws RuntimeException
     * @throws SerializationHandlerException
     * @throws DoubleException
     */
    public function testSerializeUnionWithInvalidConfiguration(): void
    {
        $this->expectExceptionObject(SerializationHandlerException::noTypesConfigured(UnionSerializationHandler::class));

        /** @var ObjectProphecy|SerializationVisitorInterface $serializationVisitorInterface */
        $serializationVisitorInterface = $this->prophesize(SerializationVisitorInterface::class);

        /** @var ObjectProphecy|SerializationContext $serializationContext */
        $serializationContext = $this->prophesize(SerializationContext::class);

        $data = new Book('Design patterns', new Author('John Doe'));
        $type = ['name' => 'union', 'params' => []];

        $unionSerializationHandler = new UnionSerializationHandler();
        $unionSerializationHandler->serializeUnion($serializationVisitorInterface->reveal(), $data, $type, $serializationContext->reveal());
    }

    /**
     * @return void
     * @throws SerializationHandlerException
     * @throws DoubleException
     * @throws InterfaceNotFoundException
     * @throws ObjectProphecyException
     * @throws NotAcceptableException
     */
    public function testDeserializeUnionWithInvalidConfiguration(): void
    {
        $this->expectExceptionObject(SerializationHandlerException::noTypesConfigured(UnionSerializationHandler::class));

        /** @var ObjectProphecy|DeserializationVisitorInterface $deserializationVisitorInterface */
        $deserializationVisitorInterface = $this->prophesize(DeserializationVisitorInterface::class);

        /** @var ObjectProphecy|DeserializationContext $deserializationContext */
        $deserializationContext = $this->prophesize(DeserializationContext::class);

        $data = ['__typename' => Book::class];
        $type = ['name' => 'union', 'params' => []];

        $unionSerializationHandler = new UnionSerializationHandler();
        $unionSerializationHandler->deserializeUnion($deserializationVisitorInterface->reveal(), $data, $type, $deserializationContext->reveal());
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws LogicException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws DateInvalidTimeZoneException
     */
    public function testDeserializeWithInvalidDataMissingTypeName(): void
    {
        $this->expectExceptionObject(SerializationHandlerException::deserializeTypeNameMissingInData(UnionSerializationHandler::class));
        $this->getSerializer()->deserialize('{"media":{"title":"Design Patterns","rating":8.7}}', MediaContainer::class, 'json');
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws LogicException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws DateInvalidTimeZoneException
     */
    public function testDeserializeWithInvalidDataUseInvalidDataForConfiguredTypeNameForUnionType(): void
    {
        $className = Author::class;
        $types = [Book::class, Movie::class];
        $this->expectExceptionObject(SerializationHandlerException::invalidClassNameConfigured(UnionSerializationHandler::class, $className, $types));
        $this->getSerializer()->deserialize(
            '{"media":{"__typename":"tests\\\\Jojo1981\\\\JmsSerializerHandlers\\\\Fixtures\\\\Entity\\\\Author","name":"John Doe"}}',
            MediaContainer::class,
            'json'
        );
    }

    /**
     * @param string $jsonString
     * @param string $type
     * @param mixed $expectedResult
     * @return void
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     * @throws JmsSerializerInvalidArgumentException
     * @throws LogicException
     * @throws NotAcceptableException
     * @throws RuntimeException
     * @throws UnsupportedFormatException
     * @throws DateInvalidTimeZoneException
     */
    #[DataProvider('getSuccessFullyTestData')]
    public function testSuccessFully(string $jsonString, string $type, mixed $expectedResult): void
    {
        self::assertEquals($expectedResult, $this->getSerializer()->deserialize($jsonString, $type, 'json'));
    }

    /**
     * @return void
     * @throws DoubleException
     * @throws ExpectationFailedException
     * @throws InterfaceNotFoundException
     * @throws NotAcceptableException
     * @throws ObjectProphecyException
     * @throws RuntimeException
     * @throws SerializationHandlerException
     * @throws PHPUnitException
     * @throws ProphecyInvalidArgumentException
     * @throws MethodProphecyException
     */
    public function testSerializeUnionReturnsMergedResultWithTypename(): void
    {
        $union = new Book('Design patterns', new Author('John Doe'));
        $params = [
            ['name' => Book::class, 'params' => []],
            ['name' => Movie::class, 'params' => []]
        ];
        $type = ['name' => 'union', 'params' => $params];

        // Use Prophecy for visitor and context
        $visitorProphecy = $this->prophesize(SerializationVisitorInterface::class);
        $visitor = $visitorProphecy->reveal();
        $contextProphecy = $this->prophesize(SerializationContext::class);
        $contextProphecy->stopVisiting($union)->shouldBeCalled();
        $contextProphecy->startVisiting($union)->shouldBeCalled();

        // Real GraphNavigatorInterface stub
        $navigator = new class implements GraphNavigatorInterface {
            public function accept(mixed $data, array|null $type = null, Context $context = null): array
            {
                return ['title' => $data->getTitle(), 'author' => ['name' => $data->getAuthor()->getName()]];
            }

            public function initialize(VisitorInterface $visitor, Context $context): void
            {
            }
        };
        $contextProphecy->getNavigator()->willReturn($navigator);
        $context = $contextProphecy->reveal();

        $handler = new UnionSerializationHandler();
        $result = $handler->serializeUnion($visitor, $union, $type, $context);
        self::assertArrayHasKey('__typename', $result);
        self::assertSame(Book::class, $result['__typename']);
        self::assertSame('Design patterns', $result['title']);
        self::assertEquals(['name' => 'John Doe'], $result['author']);
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws ExpectationFailedException
     */
    public function testGetNewTypeFromParamsReturnsEmptyArrayIfClassNameNotFound(): void
    {
        $handler = new ReflectionClass(UnionSerializationHandler::class);
        $method = $handler->getMethod('getNewTypeFromParams');
        $params = [
            ['name' => Book::class, 'params' => []],
            ['name' => Movie::class, 'params' => []]
        ];
        $result = $method->invoke(new UnionSerializationHandler(), 'NonExistentClass', $params);
        self::assertSame([], $result);
    }

    /**
     * @return array[]
     * @throws ValueExceptionInterface
     */
    public static function getSuccessFullyTestData(): array
    {
        return [
            [self::getTestJsonString0(), Employee::class, self::getExpectedResult0()],
            [self::getTestJsonString1(), MediaContainer::class, self::getExpectedResult1()],
            [self::getTestJsonString2(), MediaContainer::class, self::getExpectedResult2()]
        ];
    }

    /**
     * @return string
     */
    private static function getTestJsonString0(): string
    {
        return <<<JSON
{
    "name": "Jane Doe",
    "age": 35,
    "media": [
        {
            "__typename": "tests\\\\Jojo1981\\\\JmsSerializerHandlers\\\\Fixtures\\\\Entity\\\\Book",
            "title": "Design patterns",
            "author": {
                "name": "John Doe"
            }
        },
        {
            "__typename": "tests\\\\Jojo1981\\\\JmsSerializerHandlers\\\\Fixtures\\\\Entity\\\\Movie",
            "title": "The matrix",
            "rating": 8.7
        }
    ]
}
JSON;
    }

    /**
     * @return Employee
     * @throws ValueExceptionInterface
     */
    private static function getExpectedResult0(): Employee
    {
        return new Employee(
            'Jane Doe',
            new Age(35),
            [
                new Book('Design patterns', new Author('John Doe')),
                new Movie('The matrix', 8.7)
            ]
        );
    }

    /**
     * @return string
     */
    private static function getTestJsonString1(): string
    {
        return <<<JSON
{
    "media": {
        "__typename": "tests\\\\Jojo1981\\\\JmsSerializerHandlers\\\\Fixtures\\\\Entity\\\\Book",
        "title": "Design patterns",
        "author": {
            "name": "John Doe"
        }
    }
}
JSON;
    }

    /**
     * @return MediaContainer
     */
    private static function getExpectedResult1(): MediaContainer
    {
        return new MediaContainer(new Book('Design patterns', new Author('John Doe')));
    }

    /**
     * @return string
     */
    private static function getTestJsonString2(): string
    {
        return <<<JSON
{
    "media": {
        "__typename": "tests\\\\Jojo1981\\\\JmsSerializerHandlers\\\\Fixtures\\\\Entity\\\\Movie",
        "title": "The matrix",
        "rating": 8.7
    }
}
JSON;
    }

    /**
     * @return MediaContainer
     */
    private static function getExpectedResult2(): MediaContainer
    {
        return new MediaContainer(new Movie('The matrix', 8.7));
    }
}
