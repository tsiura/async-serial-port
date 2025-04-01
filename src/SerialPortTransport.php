<?php

declare(strict_types=1);

namespace Zeran\SerialPort;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;
use React\Stream\DuplexResourceStream;
use React\Stream\DuplexStreamInterface;
use Zeran\SerialPort\Enum\EventEnum;
use Zeran\SerialPort\Exception\SerialPortException;

final class AsyncSerialPort
{
    use LoggerAwareTrait;

    private ?DuplexStreamInterface $stream = null;

    public function __construct(
        private readonly SerialPortConfiguration $configuration,
        private readonly LoopInterface $loop,
        private readonly mixed $callback = null,
    ) {
        $this->logger = new NullLogger();
        if (null !== $callback && !is_callable($callback)) {
            throw new SerialPortException('Invalid type for callback provided');
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function isOpened(): bool
    {
        return null !== $this->stream && ($this->stream->isReadable() || $this->stream->isWritable());
    }

    /**
     * @return void
     * @throws SerialPortException
     */
    public function open(): void
    {
        if ($this->isOpened()) {
            throw new SerialPortException('Device already opened');
        }

        setlocale(LC_ALL, 'en_US');

        if (!is_readable($this->configuration->device)) {
            throw new SerialPortException(sprintf('Device [%s] is not readable', $this->configuration->device));
        }

        $command = $this->getCommand($this->configuration->platform);

        exec($command);

        $this->logger->debug(sprintf('Configuring tty with [%s]', $command));

        $resource = fopen($this->configuration->device, 'r+b');
        if (false === $resource) {
            throw new SerialPortException(sprintf('Can not open resource [%s]', $this->configuration->device));
        }

        $this->stream = new DuplexResourceStream($resource, $this->loop, $this->configuration->chunkSize);

        $this->stream->on('data', fn (string $data) => $this->event(EventEnum::DATA, $data));
        $this->stream->on('error', fn (\Throwable $e) => $this->event(EventEnum::ERROR, $e));
        $this->stream->on('close', fn () => $this->close());
    }

    public function close(): void
    {
        if ($this->isOpened()) {
            $this->stream->close();
            $this->event(EventEnum::CLOSE);
        }

        $this->stream = null;
    }

    /**
     * @param string $message
     * @return bool
     * @throws SerialPortException
     */
    public function write(string $message): bool
    {
        if (!$this->isOpened()) {
            throw new SerialPortException('Invalid stream exception');
        }

        $result = $this->stream->write($message);
        $this->event(EventEnum::WRITE, $result);

        return $result;
    }

    /**
     * @param string $platform
     * @return string
     * @throws SerialPortException
     */
    private function getCommand(string $platform): string
    {
        if (strtolower(substr($platform, 0, 5)) === 'linux') {
            return sprintf(
                '/bin/stty -F %s %d raw -echo -echoe -echok -clocal cs8 %s',
                $this->configuration->device,
                $this->configuration->baud,
                $this->configuration->octs ? 'crtscts' : '-crtscts'
            );
        } elseif (strtolower(substr($platform, 0, 7)) === 'windows') {
            return sprintf(
                'mode %s BAUD=%d PARITY=%s DATA=%d STOP=%d xon=%s octs=%s rts=%s dtr=%s',
                $this->configuration->device,
                $this->configuration->baud->value,
                $this->configuration->parity->value,
                $this->configuration->dataBits->value,
                $this->configuration->stopBits->value,
                $this->configuration->xon ? 'on' : 'off',
                $this->configuration->octs ? 'on' : 'off',
                $this->configuration->rts ? 'on' : 'off',
                $this->configuration->dtr ? 'on' : 'off'
            );
        }

        throw new SerialPortException('Unknown platform ' . $platform);
    }

    private function event(EventEnum $event, mixed $data = null): void
    {
        if (null === $this->callback) {
            return;
        }

        $callable = $this->callback;
        $callable($event->value, $data);
    }
}
