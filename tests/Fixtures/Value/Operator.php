<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2026 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Fixtures\Value;

use Jojo1981\Contracts\Exception\ValueExceptionInterface;
use Jojo1981\Contracts\ValueInterface;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Exception\ValueException;
use function gettype;
use function in_array;
use function is_string;
use function sprintf;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Fixtures\Value
 */
class Operator implements ValueInterface
{
    /** @var string */
    private string $value;

    /**
     * @param int|float|string $value
     * @throws ValueExceptionInterface
     */
    public function __construct($value)
    {
        $this->value = $this->assertValue($value);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param ValueInterface $otherValue
     * @return bool
     */
    public function match(ValueInterface $otherValue): bool
    {
        return get_class($this) === get_class($otherValue) && $this->getValue() === $otherValue->getValue();
    }

    /**
     * @param float|int|string $value
     * @return string
     * @throws ValueExceptionInterface
     */
    private function assertValue(float|int|string $value): string
    {
        if (!is_string($value)) {
            throw new ValueException(sprintf(
                'Invalid value given for: %s, value must be of type string but is of type: %s.',
                __CLASS__,
                gettype($value)
            ));
        }
        if (!in_array($value, ['+', '-', '*', '/'], true)) {
            throw new ValueException(sprintf(
                'Invalid value given for: %s, value must be one of the following: +, -, *, / but is: %s.',
                __CLASS__,
                $value
            ));
        }

        return $value;
    }
}
