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

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use Jojo1981\PhpTypes\AbstractType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\TypedCollection\Collection;
use LogicException;
use function count;
use function sprintf;

/**
 * @package Jojo1981\JmsSerializerHandlers
 */
class TypedCollectionSerializationHandler implements SubscribingHandlerInterface
{
    /**
     * @return array[]
     */
    public static function getSubscribingMethods(): array
    {
        $methods = [];
        foreach (['json', 'xml', 'yml'] as $format) {
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
                'type' => Collection::class,
                'method' => 'serializeCollection',
            ];
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
                'type' => Collection::class,
                'method' => 'deserializeCollection',
            ];
        }

        return $methods;
    }

    /**
     * @param SerializationVisitorInterface $visitor
     * @param Collection $collection
     * @param array $type
     * @param Context $context
     * @return null|array
     */
    public function serializeCollection(SerializationVisitorInterface $visitor, Collection $collection, array $type, Context $context): ?array
    {
        $type['name'] = 'array';

        $context->stopVisiting($collection);
        $result = $visitor->visitArray($collection->toArray(), $type);

        $context->startVisiting($collection);

        return $result;
    }

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param mixed $data
     * @param array $type
     * @param Context $context
     * @return Collection
     * @throws LogicException
     */
    public function deserializeCollection(DeserializationVisitorInterface $visitor, $data, array $type, Context $context): Collection
    {
        $collectionType = $type['params'][0]['name'] ?? null;
        if (empty($collectionType)) {
            throw new SerializationHandlerException(sprintf(
                'Invalid config for serialization type: `%s` given. You MUST add a parameter which contains the value' .
                ' of for the type of the collection. This value can be a primitive type, fully qualified class name or' .
                ' fully qualified interface name',
                Collection::class
            ));
        }

        try {
            AbstractType::createFromTypeName($collectionType);
        } catch (TypeException $exception) {
            throw new SerializationHandlerException(
                sprintf(
                    'Invalid config for serialization type: `%s` given. The type parameter value: `%s` is not valid',
                    Collection::class,
                    $collectionType
                ),
                0,
                $exception
            );
        }

        $numberOfParameters = count($type['params']);
        if ($numberOfParameters > 1) {
            throw new SerializationHandlerException(sprintf(
                'Invalid config for serialization type: `%s` given. Too many parameters given. This config expect 1' .
                ' parameter, but got %d number of parameters given',
                Collection::class,
                $numberOfParameters
            ));
        }

        $type['name'] = 'array';

        return new Collection($collectionType, $visitor->visitArray($data, $type));
    }
}
