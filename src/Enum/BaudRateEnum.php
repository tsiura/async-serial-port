<?php

declare(strict_types=1);

namespace Zeran\SerialPort\Enum;

enum BaudRateEnum: int
{
    case BAUD_9600 = 9600;
    case BAUD_14400 = 14400;
    case BAUD_19200 = 19200;
    case BAUD_38400 = 38400;
    case BAUD_56000 = 56000;
    case BAUD_57600 = 57600;
    case BAUD_115200 = 115200;
    case BAUD_128000 = 128000;
    case BAUD_230400 = 230400;
    case BAUD_256000 = 256000;
}
