<?php

declare(strict_types=1);

namespace Tests;

if (!\function_exists('skipErrorWithStr')) {
    /**
     * @param int $errNo use \E_WARNING \E_NOTICE and etc
     */
    function skipErrorWithStr(\Closure $closure, int $errNo, string $partOfMessage): void
    {
        \set_error_handler(static function ($errno, $errstr) use ($errNo, $partOfMessage) {
            if ($errno === $errNo && \str_contains($errstr, $partOfMessage)) {
                return true;
            }

            return false;
        }, E_ALL);

        $closure();

        \restore_error_handler();
    }
}
