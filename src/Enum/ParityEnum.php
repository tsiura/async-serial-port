<?php

declare(strict_types=1);

namespace Zeran\SerialPort\Enum;

enum ParityEnum: string
{
    case PARITY_NONE = 'n';
    case PARITY_EVEN = 'e';
    case PARITY_ODD = 'o';
    case PARITY_MARK = 'm';
    case PARITY_SPACE = 's';

    public static function getAvailable(): array
    {
        return [
            self::PARITY_NONE,
            self::PARITY_EVEN,
            self::PARITY_ODD,
            self::PARITY_MARK,
            self::PARITY_SPACE,
        ];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::getAvailable(), true);
    }
}
