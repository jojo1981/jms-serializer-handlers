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

use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\NotAcceptableException;
use JMS\Serializer\Exception\RuntimeException as JmsSerializerRuntimeException;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use function array_key_exists;
use function array_map;
use function array_merge;
use function get_class;
use function in_array;

/**
 * @package Jojo1981\JmsSerializerHandlers
 */
final class UnionSerializationHandler implements SubscribingHandlerInterface
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
                'type' => 'union',
                'method' => 'serializeUnion'
            ];
            $methods[] = [
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
                'type' => 'union',
                'method' => 'deserializeUnion'
            ];
        }

        return $methods;
    }

    /**
     * @param SerializationVisitorInterface $visitor
     * @param object $union
     * @param array $type
     * @param SerializationContext $context
     * @return array|null
     * @throws NotAcceptableException
     * @throws SerializationHandlerException
     * @throws JmsSerializerRuntimeException
     * @noinspection PhpUnusedParameterInspection
     */
    public function serializeUnion(SerializationVisitorInterface $visitor, object $union, array $type, SerializationContext $context): ?array
    {
        $className = get_class($union);
        $this->assertClassNameIsValid($className, $type['params']);

        $context->stopVisiting($union);
        $result = $context->getNavigator()->accept($union, $this->getNewTypeFromParams($className, $type['params']));
        $result = array_merge(['__typename' => $className], $result);
        $context->startVisiting($union);

        return $result;
    }

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param array $data
     * @param array $type
     * @param DeserializationContext $context
     * @return mixed
     * @throws SerializationHandlerException
     * @throws NotAcceptableException
     * @noinspection PhpUnusedParameterInspection
     */
    public function deserializeUnion(DeserializationVisitorInterface $visitor, array $data, array $type, DeserializationContext $context)
    {
        if (!array_key_exists('__typename', $data)) {
            throw SerializationHandlerException::deserializeTypeNameMissingInData(self::class);
        }
        $className = $data['__typename'];
        $this->assertClassNameIsValid($className, $type['params']);
        unset($data['__typename']);

        return $context->getNavigator()->accept($data, ['name' => $className, 'params' => []]);
    }

    /**
     * @param string $className
     * @param array[] $params
     * @return void
     * @throws SerializationHandlerException
     */
    private function assertClassNameIsValid(string $className, array $params): void
    {
        $types = $this->getTypesFromParams($params);
        if (!in_array($className, $types)) {
            throw SerializationHandlerException::invalidClassNameConfigured(self::class, $className, $types);
        }
    }

    /**
     * @param array[] $params
     * @return string[]
     * @throws SerializationHandlerException
     */
    private function getTypesFromParams(array $params): array
    {
        $types = array_map(
            static function (array $item): string {
                return $item['name'];
            },
            $params
        );
        if (empty($types)) {
            throw SerializationHandlerException::noTypesConfigured(self::class);
        }

        return $types;
    }

    /**
     * @param string $className
     * @param array $params
     * @return array
     */
    private function getNewTypeFromParams(string $className, array $params): array
    {
        foreach ($params as $param) {
            if ($param['name'] === $className) {
                return $param;
            }
        }

        return [];
    }
}
