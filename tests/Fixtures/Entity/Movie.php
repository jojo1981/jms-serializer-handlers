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

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Fixtures\Entity
 */
final class Movie
{
    /** @var string */
    private string $title;

    /** @var float */
    private float $rating;

    /**
     * @param string $title
     * @param float $rating
     */
    public function __construct(string $title, float $rating)
    {
        $this->title = $title;
        $this->rating = $rating;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return float
     */
    public function getRating(): float
    {
        return $this->rating;
    }
}
