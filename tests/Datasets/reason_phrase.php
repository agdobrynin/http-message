<?php

declare(strict_types=1);

\dataset('reason_phrase_success', [
    'trimmed phrase' => ['reasonPhrase' => "\t all right   ", 'expect' => 'all right'],
    'as is phrase with double space in middle' => ['reasonPhrase' => 'all  right', 'expect' => 'all  right'],
    'as is phrase with tab in middle' => ['reasonPhrase' => 'all    right', 'expect' => 'all    right'],
]);

\dataset('reason_phrase_fail', [
    'char 8' => ['reasonPhrase' => \chr(8)],
    'new line in phrase' => ['reasonPhrase' => "\r"],
    'carriage return in phrase' => ['reasonPhrase' => \chr(13)],
]);
