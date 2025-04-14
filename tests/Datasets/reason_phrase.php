<?php

declare(strict_types=1);

\dataset('reason_phrase_success', [
    ["\t all right   ", 'all right'],
    ['all  right', 'all  right'],
    ['all    right', 'all    right'],
]);

\dataset('reason_phrase_fail', [
    'char 8' => [\chr(8)],
    'new line in phrase' => ["\r"],
    'carriage return in phrase' => [\chr(13)],
]);
