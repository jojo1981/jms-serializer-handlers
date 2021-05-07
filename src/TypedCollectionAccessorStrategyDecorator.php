<?php
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\JmsSerializerHandlers;

use JMS\Serializer\Accessor\AccessorStrategyInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\SerializationContext;
use Jojo1981\TypedCollection\Collection;
use Jojo1981\TypedCollection\Exception\CollectionException;

/**
 * @package Jojo1981\JmsSerializerHandlers
 */
final class TypedCollectionAccessorStrategyDecorator  implements AccessorStrategyInterface
{
    /** @var AccessorStrategyInterface */
    private AccessorStrategyInterface $accessorStrategy;

    /**
     * @param AccessorStrategyInterface $accessorStrategy
     */
    public function __construct(AccessorStrategyInterface $accessorStrategy)
    {
        $this->accessorStrategy = $accessorStrategy;
    }

    /**
     * @param object $object
     * @param PropertyMetadata $metadata
     * @param SerializationContext $context
     * @return mixed
     */
    public function getValue(object $object, PropertyMetadata $metadata, SerializationContext $context)
    {
        return $this->accessorStrategy->getValue($object, $metadata, $context);
    }

    /**
     * @param object $object
     * @param mixed $value
     * @param PropertyMetadata $metadata
     * @param DeserializationContext $context
     * @return void
     * @throws CollectionException
     */
    public function setValue(object $object, $value, PropertyMetadata $metadata, DeserializationContext $context): void
    {
        if (null === $value && null !== $metadata->type && Collection::class === $metadata->type['name']) {
            $innerType = $metadata->type['params'][0]['name'];
            $value = new Collection($innerType);
        }

        $this->accessorStrategy->setValue($object, $value, $metadata, $context);
    }
}
