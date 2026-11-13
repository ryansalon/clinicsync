<?php

namespace App\Services;

class SequenceGenerator
{
    /**
     * Increment the trailing digits of the latest value and re-pad it.
     *
     * Every human-readable number in the app (PT-, DR-, ENC-, RX-) shares this
     * shape, so the increment lives in one place instead of five.
     *
     * @param  string|null  $latest  Most recent value read from the sequence column
     * @param  string  $prefix  Literal prefix, e.g. "PT-"
     */
    public static function next(?string $latest, string $prefix): string
    {
        $sequence = 1;

        if (preg_match('/(\d+)$/', (string) $latest, $matches) === 1) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
