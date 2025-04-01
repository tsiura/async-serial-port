<?php

declare(strict_types=1);

namespace Zeran\SerialPort;

use Zeran\SerialPort\Enum\BaudRateEnum;
use Zeran\SerialPort\Enum\BitsEnum;
use Zeran\SerialPort\Enum\ParityEnum;

final class SerialPortConfiguration
{
    public BaudRateEnum $baud = BaudRateEnum::BAUD_9600;
    public ParityEnum $parity = ParityEnum::PARITY_NONE;
    public BitsEnum $stopBits = BitsEnum::STOP_BITS_1;
    public BitsEnum $dataBits = BitsEnum::DATA_BITS_8;
    public bool $xon = false;
    public bool $octs = false;
    public bool $rts = false;
    public bool $dtr = false;
    public string $device = '';
    public int $chunkSize = 256;
    public string $platform = '';

    public function __construct(string $device, string $platform, BaudRateEnum $baud = BaudRateEnum::BAUD_115200)
    {
        $this->device = $device;
        $this->platform = $platform;
        $this->baud = $baud;
    }
}
