<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    public static function toMinor(string|int|float|null $amount, int $factor = 100): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        if ($factor < 1) {
            throw new InvalidArgumentException('Minor unit factor must be positive.');
        }

        $decimals = 0;
        $probe = $factor;
        while ($probe > 1 && $probe % 10 === 0) {
            $probe = intdiv($probe, 10);
            $decimals++;
        }

        if ($probe !== 1) {
            throw new InvalidArgumentException('Minor unit factor must be a power of 10.');
        }

        $value = trim((string) $amount);
        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            throw new InvalidArgumentException('Invalid monetary amount.');
        }

        $negative = str_starts_with($value, '-');
        if ($negative) {
            $value = substr($value, 1);
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $fraction = substr(str_pad($fraction, $decimals, '0'), 0, $decimals);

        $minor = ((int) $whole * $factor) + ($fraction === '' ? 0 : (int) $fraction);

        return $negative ? -$minor : $minor;
    }

    public static function fromMinor(int $minor, int $factor = 100): string
    {
        if ($factor < 1) {
            throw new InvalidArgumentException('Minor unit factor must be positive.');
        }

        $decimals = 0;
        $probe = $factor;
        while ($probe > 1 && $probe % 10 === 0) {
            $probe = intdiv($probe, 10);
            $decimals++;
        }

        if ($probe !== 1) {
            throw new InvalidArgumentException('Minor unit factor must be a power of 10.');
        }

        $negative = $minor < 0;
        $minor = abs($minor);
        $whole = intdiv($minor, $factor);
        $fraction = $minor % $factor;

        $value = $decimals > 0
            ? $whole . '.' . str_pad((string) $fraction, $decimals, '0', STR_PAD_LEFT)
            : (string) $whole;

        return $negative ? '-' . $value : $value;
    }
}
