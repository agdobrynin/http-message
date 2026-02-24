<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage;

use Generator;
use stdClass;

class DatasetMessageBody
{
    public static function messageBodySuccess(): Generator
    {
        yield 'from string' => [
            null,
            '',
        ];

        yield 'from StreamInterface' => [
            StreamAdapter::make('welcome to class'),
            'welcome to class',
        ];
    }

    public static function messageBodyWrong(): Generator
    {
        yield [(object) ['aaaa']];

        yield [1.234];

        yield [0xFF];

        yield [[]];

        yield [new stdClass()];
    }

    public static function protocolSuccess(): Generator
    {
        yield 'set #1' => ['2.0'];

        yield 'set #2' => ['1.0'];

        yield 'set #3' => ['2.12'];
    }
}
