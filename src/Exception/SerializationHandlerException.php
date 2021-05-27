<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace Jojo1981\JmsSerializerHandlers\Exception;

use DomainException;
use Throwable;
use function sprintf;

/**
 * @package Jojo1981\JmsSerializerHandlers\Exception
 */
final class SerializationHandlerException extends DomainException
{
    /**
     * Private constructor, prevent getting an instance of this class using the new keyword from outside the lexical scope of this class.
     *
     * @param string $message
     * @param Throwable|null $previousException
     */
    private function __construct(string $message, ?Throwable $previousException = null)
    {
        parent::__construct($message, 0, $previousException);
    }

    /**
     * @param string $className
     * @return self
     */
    public static function invalidConfigMissingTypeValue(string $className): self
    {
        return new self(sprintf(
            'Invalid config for serialization type: `%s` given. You MUST add a parameter which contains the value' .
            ' of for the type of the collection. This value can be a primitive type, fully qualified class name or' .
            ' fully qualified interface name',
            $className
        ));
    }

    /**
     * @param string $className
     * @param string $type
     * @param Throwable|null $previousException
     * @return self
     */
    public static function invalidConfigTypeValueInvalid(string $className, string $type, ?Throwable $previousException = null): self
    {
        return new self(
            sprintf('Invalid config for serialization type: `%s` given. The type parameter value: `%s` is not valid', $className, $type),
            $previousException
        );
    }

    /**
     * @param string $className
     * @param int $numberOfParameters
     * @return self
     */
    public static function invalidConfigTooManyParameters(string $className, int $numberOfParameters): self
    {
        return new self(sprintf(
            'Invalid config for serialization type: `%s` given. Too many parameters given. This config expect 1' .
            ' parameter, but got %d number of parameters given',
            $className,
            $numberOfParameters
        ));
    }

    /**
     * @param string $className
     * @return self
     */
    public static function invalidConfigMissingName(string $className): self
    {
        return new self(sprintf('Invalid configuration for handler: `%s`. Missing name', $className));
    }

    /**
     * @param string $className
     * @param int $numberOfParameters
     * @param string $valueClass
     * @return self
     */
    public static function invalidConfigParameterCountIsIncorrect(string $className, int $numberOfParameters, string $valueClass): self
    {
        if (0 === $numberOfParameters) {
            $message = sprintf(
                'Invalid configuration for handler: `%s`. Type: `%s` used without a type parameter.',
                $className,
                $valueClass
            );
        } else {
            $message = sprintf(
                'Invalid configuration for handler: `%s`. Type: `%s` used with too many parameters. Expect 1 parameter,' .
                ' but got %d number of parameters.',
                $className,
                $valueClass,
                $numberOfParameters
            );
        }

        return new self($message);
    }

    /**
     * @param string $className
     * @param string $valueClass
     * @return static
     */
    public static function invalidConfigParameterTypeValueClassIsNotExistingClass(string $className, string $valueClass): self
    {
        return new self(sprintf(
            'Invalid configuration for handler: `%s`. Type: `%s` used, but is not an existing class name or interface.',
            $className,
            $valueClass
        ));
    }

    /**
     * @param string $className
     * @param string $valueClass
     * @param string $interface
     * @return self
     */
    public static function invalidConfigParameterTypeValueClassDoesNotImplementValueInterface(
        string $className,
        string $valueClass,
        string $interface
    ): self {
        return new self(sprintf(
            'Invalid configuration for handler: `%s`. Type: `%s` used, but is not an instance of: `%s`',
            $className,
            $valueClass,
            $interface
        ));
    }

    /**
     * @param string $className
     * @param string $valueClass
     * @param string $innerType
     * @return self
     */
    public static function invalidConfigParameterInnerTypeNotValid(
        string $className,
        string $valueClass,
        string $innerType
    ): self {
        return new self(sprintf(
            'Invalid configuration for handler: `%s`. Type: `%s` with invalid inner type: `%s`, Valid types are: %s.',
            $className,
            $valueClass,
            $innerType,
            'integer, float or string'
        ));
    }
}
