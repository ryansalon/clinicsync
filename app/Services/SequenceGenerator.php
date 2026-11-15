<?php

namespace App\Services;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

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

    /**
     * Run a sequence-allocating transaction and retry on unique-constraint collisions
     * (e.g. concurrent first-of-day rows under Read Committed isolation).
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public static function retryTransaction(Closure $callback, int $attempts = 3): mixed
    {
        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return DB::transaction($callback);
            } catch (QueryException $exception) {
                $isUniqueCollision = in_array((string) ($exception->errorInfo[0] ?? $exception->getCode()), ['23000', '23505'], true)
                    || str_contains(strtolower($exception->getMessage()), 'unique');

                if (! $isUniqueCollision || $attempt === $attempts) {
                    throw $exception;
                }
            }
        }

        return null;
    }
}
