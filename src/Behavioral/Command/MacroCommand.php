<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Command;

/** @var CommandInterface[] */
class MacroCommand implements CommandInterface
{
    /** @var CommandInterface[] */
    private array $commands = [];
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addCommand(CommandInterface $command): void
    {
        $this->commands[] = $command;
    }

    public function execute(): CommandResult
    {
        $results = [];
        foreach ($this->commands as $command) {
            if (!$command->canExecute()) {
                return new CommandResult(false, "Macro {$this->name} failed: {$command->getName()} cannot execute");
            }
            $results[] = $command->execute();
        }

        return new CommandResult(
            true,
            "Macro {$this->name} executed " . count($this->commands) . " commands",
            ['results' => $results]
        );
    }

    public function undo(): CommandResult
    {
        $undone = 0;
        foreach (array_reverse($this->commands) as $command) {
            $result = $command->undo();
            if ($result->success) {
                $undone++;
            }
        }

        return new CommandResult(
            true,
            "Undid {$undone} commands from macro {$this->name}",
            ['undone' => $undone]
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function canExecute(): bool
    {
        foreach ($this->commands as $command) {
            if (!$command->canExecute()) {
                return false;
            }
        }
        return true;
    }
}