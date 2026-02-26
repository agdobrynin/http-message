<?php

declare(strict_types=1);

namespace Tests\Kaspi\HttpMessage;

use Generator;

use function chr;

class DatasetReasonPhrase
{
    public static function reasonPhraseSuccess(): Generator
    {
        yield ["\t all right   ", 'all right'];

        yield ['all  right', 'all  right'];

        yield ['all    right', 'all    right'];
    }

    public static function reasonPhraseFail(): Generator
    {
        yield 'char 8' => [chr(8)];

        yield 'new line in phrase' => ["\r"];

        yield 'carriage return in phrase' => [chr(13)];
    }
}
