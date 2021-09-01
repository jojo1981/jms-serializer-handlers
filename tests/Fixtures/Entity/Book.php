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
final class Book
{
    /** @var string */
    private string $title;

    /** @var Author */
    private Author $author;

    /**
     * @param string $title
     * @param Author $author
     */
    public function __construct(string $title, Author $author)
    {
        $this->title = $title;
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return Author
     */
    public function getAuthor(): Author
    {
        return $this->author;
    }
}
