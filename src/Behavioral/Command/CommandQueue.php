<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Command;

/** @var CommandInterface[] */
class CommandQueue
{
    /** @var CommandInterface[] */
    private array $queue = [];
    /** @var CommandInterface[] */
    private array $history = [];
    private int $maxHistorySize = 50;

    public function enqueue(CommandInterface $command): void
    {
        $this->queue[] = $command;
    }

    public function executeNext(): ?CommandResult
    {
        if (empty($this->queue)) {
            return null;
        }

        $command = array_shift($this->queue);
        $result = $command->execute();

        if ($result->success) {
            $this->history[] = $command;
            if (count($this->history) > $this->maxHistorySize) {
                array_shift($this->history);
            }
        }

        return $result;
    }

    public function executeAll(): array
    {
        $results = [];
        while (!empty($this->queue)) {
            $results[] = $this->executeNext();
        }
        return $results;
    }

    public function undoLast(): ?CommandResult
    {
        if (empty($this->history)) {
            return null;
        }

        $command = array_pop($this->history);
        return $command->undo();
    }

    public function getQueueSize(): int
    {
        return count($this->queue);
    }

    public function getHistorySize(): int
    {
        return count($this->history);
    }

    public function clear(): void
    {
        $this->queue = [];
    }
}