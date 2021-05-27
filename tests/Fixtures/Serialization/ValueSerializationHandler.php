<?php declare(strict_types=1);
/*
 * This file is part of the jojo1981/jms-serializer-handlers package
 *
 * Copyright (c) 2019 Joost Nijhuis <jnijhuis81@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed in the root of the source code
 */
namespace tests\Jojo1981\JmsSerializerHandlers\Fixtures\Serialization;

use JMS\Serializer\GraphNavigatorInterface;
use Jojo1981\JmsSerializerHandlers\AbstractValueSerializationHandler;
use tests\Jojo1981\JmsSerializerHandlers\Fixtures\ValueClasses;

/**
 * @package tests\Jojo1981\JmsSerializerHandlers\Fixtures\Serialization
 */
final class ValueSerializationHandler extends AbstractValueSerializationHandler
{
    /**
     * @return array[]
     */
    public static function getSubscribingMethods(): array
    {
        $methods = [];
        foreach (ValueClasses::VALUES as $type) {
            foreach (['json', 'xml', 'yml'] as $format) {
                $methods[] = [
                    'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                    'format' => $format,
                    'type' => $type,
                    'method' => 'serializeValue',
                ];
                $methods[] = [
                    'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                    'format' => $format,
                    'type' => $type,
                    'method' => 'deserializeValue',
                ];
            }
        }

        return $methods;
    }
}
