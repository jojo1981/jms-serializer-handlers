<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Fixtures\Value;

use Jojo1981\Contracts\Exception\ValueExceptionInterface;
use Jojo1981\Contracts\ValueInterface;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Exception\ValueException;
use function get_class;
use function gettype;
use function is_int;
use function sprintf;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Fixtures\Value
 */
final class Age implements ValueInterface
{
    /** @var int */
    private int $value;

    /**
     * @param int|float|string $value
     * @throws ValueExceptionInterface
     */
    public function __construct($value)
    {
        $this->value = $this->assertValue($value);
    }

    /**
     * @return int
     */
    public function getValue(): int
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
     * @param int|float|string $value
     * @return int
     * @throws ValueExceptionInterface
     */
    private function assertValue($value): int
    {
        if (!is_int($value)) {
            throw new ValueException(sprintf(
                'Invalid value given for: %s, value must be of type integer but is of type: %s.',
                __CLASS__,
                gettype($value)
            ));
        }
        if ($value < 0 || $value > 120) {
            throw new ValueException(sprintf(
                'Invalid value given for: %s, value must be higher than or equal to 0 and lower than or equal to 120 but value is: %d.',
                __CLASS__,
                $value
            ));
        }

        return $value;
    }
}
