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

    /** @var Book[]|Movie[] */
    private array $media;

    /**
     * @param string $name
     * @param Age $age
     * @param Book[]|Movie[] $media
     */
    public function __construct(string $name, Age $age, array $media = [])
    {
        $this->name = $name;
        $this->age = $age;
        $this->media = $media;
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
     * @return Book[]|Movie[]
     */
    public function getMedia(): array
    {
        return $this->media;
    }

    /**
     * @param Book[]|Movie[] $media
     * @return void
     */
    public function setMedia(array $media): void
    {
        $this->media = $media;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return hash('sha256', $this->getName() . '-' . $this->age->getValue());
    }
}
