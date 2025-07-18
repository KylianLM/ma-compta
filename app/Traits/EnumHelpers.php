<?php

declare(strict_types=1);

namespace App\Traits;

trait EnumHelpers
{
    /** Get all enum values. */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Get all enum names. */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /** Get enum as associative array [value => name]. */
    public static function toArray(): array
    {
        return array_combine(self::values(), self::names());
    }

    /** Get enum as associative array [value => label] for UI. */
    public static function toSelectArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = method_exists($case, 'label') ? $case->label() : $case->name;
        }

        return $result;
    }

    /**
     * Check if a value exists in the enum.
     */
    public static function hasValue(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    /**
     * Get enum case by value (with fallback).
     */
    public static function fromValue(string $value, ?self $default = null): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        return $default;
    }

    /** Get random enum case. */
    public static function random(): self
    {
        $cases = self::cases();

        return $cases[array_rand($cases)];
    }
}
