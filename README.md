# Zeran SerialPort

An asynchronous PHP library for serial port communication built on ReactPHP.

## Introduction

Zeran SerialPort is a modern PHP library that provides asynchronous serial port communication using ReactPHP's event loop. It supports various baud rates, parity settings, and platform-specific configurations for both Linux and Windows systems.

This library is ideal for applications that need to communicate with hardware devices through serial ports, such as:

- IoT devices
- Microcontrollers (Arduino, ESP32, etc.)
- Industrial equipment
- Legacy hardware with serial interfaces
- Embedded systems

## Requirements

- PHP 8.1 or higher (uses PHP enums)
- ReactPHP event loop
- PSR-3 compatible logger (optional)
- Appropriate permissions to access serial port devices

## Installation

You can install the package via composer:

```bash
composer require zeran/serial-port
```

## Basic Usage

Here's a simple example of how to use the library:

```php
<?php

use React\EventLoop\Loop;
use Zeran\SerialPort\AsyncSerialPort;
use Zeran\SerialPort\SerialPortConfiguration;
use Zeran\SerialPort\Enum\BaudRateEnum;
use Zeran\SerialPort\Enum\EventEnum;

// Create event loop
$loop = Loop::get();

// Configure serial port
$config = new SerialPortConfiguration(
    device: '/dev/ttyUSB0',  // Change to your device path
    platform: PHP_OS,
    baud: BaudRateEnum::BAUD_115200
);

// Optional: Configure additional parameters
$config->dataBits = \Zeran\SerialPort\Enum\BitsEnum::DATA_BITS_8;
$config->stopBits = \Zeran\SerialPort\Enum\BitsEnum::STOP_BITS_1;
$config->parity = \Zeran\SerialPort\Enum\ParityEnum::PARITY_NONE;
$config->xon = false;
$config->octs = false;
$config->rts = false;
$config->dtr = false;
$config->chunkSize = 256;

// Create serial port with event handler
$serialPort = new AsyncSerialPort($config, $loop, function (EventEnum $event, $data) {
    switch ($event) {
        case EventEnum::DATA->value:
            echo "Received data: " . bin2hex($data) . PHP_EOL;
            break;
        case EventEnum::ERROR->value:
            echo "Error: " . $data->getMessage() . PHP_EOL;
            break;
        case EventEnum::CLOSE->value:
            echo "Port closed" . PHP_EOL;
            break;
        case EventEnum::WRITE->value:
            echo "Data written successfully" . PHP_EOL;
            break;
    }
});

try {
    // Open the serial port
    $serialPort->open();
    
    // Write data to the port
    $serialPort->write("AT\r\n");
    
    // Run the event loop
    $loop->run();
} catch (\Zeran\SerialPort\Exception\SerialPortException $e) {
    echo "Serial port error: " . $e->getMessage() . PHP_EOL;
}
```

## Configuration

The `SerialPortConfiguration` class allows you to configure various aspects of the serial port:

### Required Parameters

- `device`: Path to the serial port device (e.g., `/dev/ttyUSB0` on Linux or `COM1` on Windows)
- `platform`: Operating system platform (usually you can use `PHP_OS`)
- `baud`: Baud rate (defaults to 115200)

### Optional Parameters

- `parity`: Parity mode (`PARITY_NONE`, `PARITY_EVEN`, `PARITY_ODD`, `PARITY_MARK`, or `PARITY_SPACE`)
- `stopBits`: Number of stop bits (`STOP_BITS_1` or `STOP_BITS_2`)
- `dataBits`: Number of data bits (`DATA_BITS_8` or `DATA_BITS_9`)
- `xon`: Enable/disable software flow control
- `octs`: Enable/disable hardware flow control
- `rts`: Enable/disable RTS (Request to Send) signal
- `dtr`: Enable/disable DTR (Data Terminal Ready) signal
- `chunkSize`: Size of read chunks in bytes

## Supported Baud Rates

The library supports the following baud rates through the `BaudRateEnum`:

- 9600
- 14400
- 19200
- 38400
- 56000
- 57600
- 115200
- 128000
- 230400
- 256000

Example:

```php
use Zeran\SerialPort\Enum\BaudRateEnum;

$config = new SerialPortConfiguration(
    device: '/dev/ttyUSB0',
    platform: PHP_OS,
    baud: BaudRateEnum::BAUD_9600
);
```

## Events

The library provides event-based communication through a callback function. The following events are available:

- `EventEnum::DATA`: Received data from the serial port
- `EventEnum::ERROR`: An error occurred
- `EventEnum::OPEN`: Port has been opened
- `EventEnum::CLOSE`: Port has been closed
- `EventEnum::WRITE`: Data has been written to the port

## API Reference

### AsyncSerialPort

Main class for serial port communication.

#### Methods

- `__construct(SerialPortConfiguration $configuration, LoopInterface $loop, callable $callback = null)`: Create a new serial port instance
- `open()`: Open the serial port
- `close()`: Close the serial port
- `write(string $message)`: Write data to the serial port
- `isOpened()`: Check if the serial port is open

### SerialPortConfiguration

Class for configuring the serial port parameters.

#### Properties

- `device`: Device path
- `platform`: Operating system platform
- `baud`: Baud rate (from BaudRateEnum)
- `parity`: Parity mode (from ParityEnum)
- `stopBits`: Number of stop bits (from BitsEnum)
- `dataBits`: Number of data bits (from BitsEnum)
- `xon`: Software flow control
- `octs`: Hardware flow control
- `rts`: RTS signal
- `dtr`: DTR signal
- `chunkSize`: Read chunk size

## Advanced Usage

### Using with PSR-3 Logger

The library supports PSR-3 logging through the `LoggerAwareTrait`:

```php
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Create a logger
$logger = new Logger('serial_port');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::DEBUG));

// Pass it to the serial port
$serialPort = new AsyncSerialPort($config, $loop, $callback);
$serialPort->setLogger($logger);
```

### Platform-Specific Considerations

#### Linux

On Linux, you may need to add your user to the `dialout` group to access serial ports without root privileges:

```bash
sudo usermod -a -G dialout $USER
```

You'll need to log out and log back in for the changes to take effect.

#### Windows

On Windows, you need to use COM port names (e.g., `COM1`, `COM2`, etc.) for the device path.

## Troubleshooting

### Common Issues

1. **Permission denied**: Ensure your user has permission to access the serial port.
2. **Device not found**: Verify the device path is correct. Use `ls /dev/tty*` on Linux or Device Manager on Windows.
3. **Invalid configuration**: Check that your baud rate, parity, and other settings match your device requirements.

### Debugging

Enable debugging by setting a logger:

```php
$logger = new Logger('serial_port');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$serialPort->setLogger($logger);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.