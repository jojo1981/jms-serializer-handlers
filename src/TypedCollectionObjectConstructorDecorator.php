<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\JmsSerializerHandlers;

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use Jojo1981\TypedCollection\Collection;
use Jojo1981\TypedCollection\Exception\CollectionException;
use ReflectionException;
use ReflectionProperty;

/**
 * @package Jojo1981\JmsSerializerHandlers
 */
final class TypedCollectionObjectConstructorDecorator implements ObjectConstructorInterface
{
    /** @var ObjectConstructorInterface */
    private ObjectConstructorInterface $objectConstructor;

    /**
     * @param ObjectConstructorInterface $objectConstructor
     */
    public function __construct(ObjectConstructorInterface $objectConstructor)
    {
        $this->objectConstructor = $objectConstructor;
    }

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param ClassMetadata $metadata
     * @param mixed $data
     * @param array $type
     * @param DeserializationContext $context
     * @return object
     * @throws ReflectionException
     * @throws CollectionException
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        $object = $this->objectConstructor->construct($visitor, $metadata, $data, $type, $context);
        /** @var PropertyMetadata $property */
        foreach ($metadata->propertyMetadata as $property) {
            if (null === $property->type) {
                continue;
            }

            $reflectionProperty = new ReflectionProperty($property->class, $property->name);
            $reflectionProperty->setAccessible(true);

            if (Collection::class === $property->type['name']) {
                $innerType = $property->type['params'][0]['name'];
                $reflectionProperty->setValue($object, new Collection($innerType));
            } elseif ('array' === $property->type['name']) {
                $reflectionProperty->setValue($object, []);
            } elseif ($property->skipWhenEmpty && (null !== $reflectionType = $reflectionProperty->getType()) && $reflectionType->allowsNull()) {
                /** @noinspection PhpRedundantOptionalArgumentInspection */
                $reflectionProperty->setValue($object, null);
            }
        }

        return $object;
    }
}
