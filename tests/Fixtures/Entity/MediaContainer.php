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
final class MediaContainer
{
    /** @var Book|Movie */
    private $media;

    /**
     * @param Book|Movie $media
     */
    public function __construct($media)
    {
        $this->media = $media;
    }

    /**
     * @return Book|Movie
     */
    public function getMedia()
    {
        return $this->media;
    }
}
