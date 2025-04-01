<?php

declare(strict_types=1);

namespace Zeran\SerialPort\Enum;

enum BitsEnum: int
{
    case STOP_BITS_1 = 1;
    case STOP_BITS_2 = 2;
    case DATA_BITS_8 = 8;
    case DATA_BITS_9 = 9;

    public static function getAvailable(): array
    {
        return [
            self::STOP_BITS_1,
            self::STOP_BITS_2,
            self::DATA_BITS_8,
            self::DATA_BITS_9,
        ];
    }

    public static function isValid(int $value): bool
    {
        return in_array($value, self::getAvailable(), true);
    }
}
