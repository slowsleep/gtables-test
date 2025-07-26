<?php

namespace App\Enums;

class Status
{
    public const ALLOWED = 'Allowed';
    public const PROHIBITED = 'Prohibited';

    public static function values(): array
    {
        return [
            self::ALLOWED,
            self::PROHIBITED,
        ];
    }
}
