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

use DOMText;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use JMS\Serializer\XmlSerializationVisitor;
use Jojo1981\Contracts\ValueInterface;
use Jojo1981\JmsSerializerHandlers\Exception\SerializationHandlerException;
use function count;
use function get_class;
use function in_array;
use function is_a;

/**
 * @package Jojo1981\JmsSerializerHandlers
 */
abstract class AbstractValueSerializationHandler implements SubscribingHandlerInterface
{
    /**
     * @param SerializationVisitorInterface $visitor
     * @param ValueInterface $valueObject
     * @return int|float|string|DomText
     */
    final public function serializeValue(SerializationVisitorInterface $visitor, ValueInterface $valueObject): DOMText|float|int|string
    {
        if ($visitor instanceof XmlSerializationVisitor) {
            return $visitor->getDocument()->createTextNode((string) $valueObject->getValue());
        }

        return $valueObject->getValue();
    }

    /**
     * @param DeserializationVisitorInterface $visitor
     * @param mixed $data
     * @param array $type
     * @return ValueInterface
     * @throws SerializationHandlerException
     */
    public function deserializeValue(DeserializationVisitorInterface $visitor, mixed $data, array $type): ValueInterface
    {
        $this->assertType($type);
        if (null !== $innerType = $type['params'][0]['name'] ?? null) {
            if (in_array($innerType, ['integer', 'int'])) {
                $data = $visitor->visitInteger($data, ['name' => 'integer', 'params' => []]);
            }
            if ('string' === $innerType) {
                $data = $visitor->visitString($data, ['name' => 'string', 'params' => []]);
            }
        }
        $valueClass = $type['name'] ?? null;

        return new $valueClass($data);
    }

    /**
     * @param array $type
     * @return void
     * @throws SerializationHandlerException
     */
    public function assertType(array $type): void
    {
        $valueClass = $type['name'] ?? null;
        $className = get_class($this);
        if (empty($valueClass)) {
            throw SerializationHandlerException::invalidConfigMissingName($className);
        }
        $numberOfParams = count($type['params'] ?? []);
        if (1 !== $numberOfParams) {
            throw SerializationHandlerException::invalidConfigParameterCountIsIncorrect($className, $numberOfParams, $valueClass);
        }
        if (!class_exists($valueClass)) {
            throw SerializationHandlerException::invalidConfigParameterTypeValueClassIsNotExistingClass($className, $valueClass);
        }
        if (false === is_a($valueClass, ValueInterface::class, true)) {
            throw SerializationHandlerException::invalidConfigParameterTypeValueClassDoesNotImplementValueInterface(
                $className,
                $valueClass,
                ValueInterface::class
            );
        }
        $innerType = $type['params'][0]['name'] ?? null;
        if (null !== $innerType && !in_array($innerType, ['integer', 'int', 'string'])) {
            throw SerializationHandlerException::invalidConfigParameterInnerTypeNotValid($className, $valueClass, $innerType);
        }
    }
}
