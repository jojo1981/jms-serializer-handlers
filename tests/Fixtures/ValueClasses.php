<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Fixtures;

use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Value\Age;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Fixtures
 */
final class ValueClasses
{
    /** @var string[] */
    public const VALUES = [
        Age::class
    ];

    /**
     * Private constructor, prevent getting an instance of this class using the new keyword from outside the lexical scope of this class
     */
    private function __construct()
    {
        // Nothing to do here
    }
}
