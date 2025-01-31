<?php

namespace App\Enums;

enum FileSizeUnit: string
{
    case B = 'B';
    case KB = 'KB';
    case MB = 'MB';
    case GB = 'GB';
    case TB = 'TB';
    case PB = 'PB';

    /**
     * Get the exponent for the unit (e.g., KB => 1, MB => 2, etc.)
     */
    public function exponent(): int
    {
        return match ($this) {
            self::B => 0,
            self::KB => 1,
            self::MB => 2,
            self::GB => 3,
            self::TB => 4,
            self::PB => 5,
        };
    }
}
