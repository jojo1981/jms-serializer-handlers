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

use JMS\Serializer\Exception\RuntimeException as JMSSerializerRuntimeException;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use Jojo1981\PhpTypes\AbstractType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\TypedCollection\Collection;
use LogicException;
use function count;

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
     * @param SerializationContext $context
     * @return null|array
     * @throws JMSSerializerRuntimeException
     */
    public function serializeCollection(
        SerializationVisitorInterface $visitor,
        Collection $collection,
        array $type,
        SerializationContext $context
    ): ?array {
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
     * @return Collection
     * @throws LogicException
     */
    public function deserializeCollection(DeserializationVisitorInterface $visitor, $data, array $type): Collection
    {
        $collectionType = $type['params'][0]['name'] ?? null;
        if (empty($collectionType)) {
            throw SerializationHandlerException::invalidConfigMissingTypeValue(Collection::class);
        }

        try {
            AbstractType::createFromTypeName($collectionType);
        } catch (TypeException $exception) {
            throw SerializationHandlerException::invalidConfigTypeValueInvalid(Collection::class, $collectionType, $exception);
        }

        $numberOfParameters = count($type['params']);
        if ($numberOfParameters > 1) {
            throw SerializationHandlerException::invalidConfigTooManyParameters(Collection::class, $numberOfParameters);
        }

        $type['name'] = 'array';

        return new Collection($collectionType, $visitor->visitArray($data, $type));
    }
}
