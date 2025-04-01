<?php

declare(strict_types=1);

namespace Zeran\SerialPort\Enum;

enum EventEnum: string
{
    case DATA = 'data';
    case ERROR = 'error';
    case OPEN = 'open';
    case CLOSE = 'close';
    case WRITE = 'write';
}
