<?php
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Fixtures;

use Jojo1981\TypedCollection\Collection;
use Jojo1981\TypedCollection\Exception\CollectionException;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Fixtures
 */
class Company
{
    /** @var string */
    private $name;

    /** @var Collection|Employee[] */
    private $employees;

    /**
     * @param string $name
     * @throws CollectionException
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->employees = new Collection(Employee::class);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Collection|Employee[]
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }
}