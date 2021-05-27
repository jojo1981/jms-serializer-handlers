<?php
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Fixtures\Exception;

use DomainException;
use Jojo1981\Contracts\Exception\ValueExceptionInterface;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Fixtures\Exception
 */
final class ValueException extends DomainException implements ValueExceptionInterface
{
}
