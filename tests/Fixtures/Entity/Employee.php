<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Fixtures\Entity;

use Jojo1981\Contracts\HashableInterface;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Value\Age;
use function hash;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Fixtures\Entity
 */
final class Employee implements HashableInterface
{
    /** @var string */
    private string $name;

    /** @var Age */
    private Age $age;

    /**
     * @param string $name
     * @param Age $age
     */
    public function __construct(string $name, Age $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Age
     */
    public function getAge(): Age
    {
        return $this->age;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return hash('sha256', $this->getName() . '-' . $this->age->getValue());
    }
}
