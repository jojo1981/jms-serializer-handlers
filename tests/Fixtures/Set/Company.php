<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2021 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Fixtures\Set;

use Jojo1981\Contracts\HashableInterface;
use Jojo1981\TypedSet\Exception\SetException;
use Jojo1981\TypedSet\Set;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\Entity\Employee;
use function hash;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Set\Fixtures
 */
class Company implements HashableInterface
{
    /** @var string */
    private string $name;

    /** @var Set|Employee[] */
    private Set $employees;

    /**
     * @param string $name
     * @throws SetException
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->employees = new Set(Employee::class);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Set|Employee[]
     */
    public function getEmployees(): Set
    {
        return $this->employees;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return hash('sha256', $this->name);
    }
}
