<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2020 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\JmsSerializerHandlers;

use JMS\Serializer\Exception\RuntimeException as JmsSerializerRuntimeException;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use Jojo1981\PhpTypes\AbstractType;
use Jojo1981\PhpTypes\Exception\TypeException;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Handler\Exception\HandlerException;
use Jojo1981\TypedSet\Set;
use function count;

/**
 * @package Jojo1981\JmsSerializerHandlers
 */
class TypedSetSerializationHandler implements SubscribingHandlerInterface
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
                'type' => Set::class,
                'method' => 'serializeSet'
            ];
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
                'type' => Set::class,
                'method' => 'deserializeSet'
            ];
        }

        return $methods;
    }

    /**
     * @param SerializationVisitorInterface $visitor
     * @param Set $set
     * @param array $type
     * @param SerializationContext $context
     * @return null|array
     * @throws JmsSerializerRuntimeException
     */
    public function serializeSet(SerializationVisitorInterface $visitor, Set $set, array $type, SerializationContext $context): ?array
    {
        $type['name'] = 'array';

        $context->stopVisiting($set);
        $result = $visitor->visitArray($set->toArray(), $type);

        $context->startVisiting($set);

        return $result;
    }

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param mixed $data
     * @param array $type
     * @return Set
     * @throws SerializationHandlerException
     * @throws SetException
     * @throws HandlerException
     */
    public function deserializeSet(DeserializationVisitorInterface $visitor, $data, array $type): Set
    {
        $collectionType = $type['params'][0]['name'] ?? null;
        if (empty($collectionType)) {
            throw SerializationHandlerException::invalidConfigMissingTypeValue(Set::class);
        }

        try {
            AbstractType::createFromTypeName($collectionType);
        } catch (TypeException $exception) {
            throw SerializationHandlerException::invalidConfigTypeValueInvalid(Set::class, $collectionType, $exception);
        }

        $numberOfParameters = count($type['params']);
        if ($numberOfParameters > 1) {
            throw SerializationHandlerException::invalidConfigTooManyParameters(Set::class, $numberOfParameters);
        }

        $type['name'] = 'array';

        return new Set($collectionType, $visitor->visitArray($data, $type));
    }
}
