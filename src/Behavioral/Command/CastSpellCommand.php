<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Command;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\Model\Skill;
use AzerothEngine\Behavioral\Strategy\DamageCalculationContext;

interface CommandInterface
{
    public function execute(): CommandResult;
    public function undo(): CommandResult;
    public function getName(): string;
    public function canExecute(): bool;
}

class CommandResult
{
    public function __construct(
        public bool $success,
        public string $message,
        public mixed $data = null
    ) {}
}

abstract class BaseCommand implements CommandInterface
{
    protected PlayerCharacter $caster;
    protected PlayerCharacter $target;
    protected Skill $skill;
    protected DamageCalculationContext $damageContext;
    protected ?CommandResult $lastResult = null;

    public function __construct(
        PlayerCharacter $caster,
        PlayerCharacter $target,
        Skill $skill,
        DamageCalculationContext $damageContext
    ) {
        $this->caster = $caster;
        $this->target = $target;
        $this->skill = $skill;
        $this->damageContext = $damageContext;
    }

    public function getName(): string
    {
        return $this->skill->name;
    }

    public function canExecute(): bool
    {
        return $this->skill->canCast($this->caster) && $this->caster->canAct();
    }

    abstract public function execute(): CommandResult;
    abstract public function undo(): CommandResult;
}

class CastSpellCommand extends BaseCommand
{
    private int $damageDealt = 0;
    private int $manaSpent = 0;
    private int $targetPreviousHealth = 0;
    private int $casterPreviousMana = 0;

    public function execute(): CommandResult
    {
        if (!$this->canExecute()) {
            return new CommandResult(false, "Cannot cast {$this->skill->name}: requirements not met");
        }

        $this->casterPreviousMana = $this->caster->getMana()->current;
        $this->targetPreviousHealth = $this->target->getHealth()->current;

        $this->caster->spendMana($this->skill->manaCost);
        $this->manaSpent = $this->skill->manaCost;

        $baseDamage = $this->skill->calculateDamage($this->caster);
        $this->damageDealt = $this->damageContext->calculateDamage($this->caster, $this->target, $this->skill->damageType, $baseDamage);

        $this->target->takeDamage($this->damageDealt);

        return new CommandResult(
            true,
            "{$this->caster->getName()} casts {$this->skill->name} on {$this->target->getName()} for {$this->damageDealt} {$this->skill->damageType->value} damage",
            [
                'damage' => $this->damageDealt,
                'mana_spent' => $this->manaSpent,
                'target_health' => $this->target->getHealth()->current,
            ]
        );
    }

    public function undo(): CommandResult
    {
        if ($this->lastResult === null || !$this->lastResult->success) {
            return new CommandResult(false, 'Nothing to undo');
        }

        $this->target->heal($this->damageDealt);
        $this->caster->restoreMana($this->manaSpent);

        return new CommandResult(
            true,
            "Undid {$this->skill->name}: restored {$this->damageDealt} HP to {$this->target->getName()} and {$this->manaSpent} mana to {$this->caster->getName()}",
            [
                'healed' => $this->damageDealt,
                'mana_restored' => $this->manaSpent,
            ]
        );
    }
}

class HealCommand extends BaseCommand
{
    private int $healAmount = 0;
    private int $targetPreviousHealth = 0;
    private int $casterPreviousMana = 0;

    public function execute(): CommandResult
    {
        if (!$this->canExecute()) {
            return new CommandResult(false, "Cannot cast {$this->skill->name}: requirements not met");
        }

        $this->casterPreviousMana = $this->caster->getMana()->current;
        $this->targetPreviousHealth = $this->target->getHealth()->current;

        $this->caster->spendMana($this->skill->manaCost);

        $spellPower = $this->caster->getSpellPower();
        $this->healAmount = (int) ($spellPower * 1.5);

        $maxHeal = $this->target->getHealth()->maximum - $this->target->getHealth()->current;
        $actualHeal = min($this->healAmount, $maxHeal);

        $this->target->heal($actualHeal);

        return new CommandResult(
            true,
            "{$this->caster->getName()} casts {$this->skill->name} on {$this->target->getName()} healing for {$actualHeal} HP",
            [
                'healed' => $actualHeal,
                'mana_spent' => $this->skill->manaCost,
                'target_health' => $this->target->getHealth()->current,
            ]
        );
    }

    public function undo(): CommandResult
    {
        if ($this->lastResult === null || !$this->lastResult->success) {
            return new CommandResult(false, 'Nothing to undo');
        }

        $this->target->takeDamage($this->healAmount);
        $this->caster->restoreMana($this->skill->manaCost);

        return new CommandResult(
            true,
            "Undid {$this->skill->name}: removed {$this->healAmount} HP from {$this->target->getName()} and restored {$this->skill->manaCost} mana to {$this->caster->getName()}",
            [
                'damage' => $this->healAmount,
                'mana_restored' => $this->skill->manaCost,
            ]
        );
    }
}

class BuffCommand extends BaseCommand
{
    private string $buffName;
    private int $duration;
    private array $statModifiers;

    public function __construct(
        PlayerCharacter $caster,
        PlayerCharacter $target,
        Skill $skill,
        DamageCalculationContext $damageContext,
        string $buffName,
        int $duration,
        array $statModifiers
    ) {
        parent::__construct($caster, $target, $skill, $damageContext);
        $this->buffName = $buffName;
        $this->duration = $duration;
        $this->statModifiers = $statModifiers;
    }

    public function execute(): CommandResult
    {
        if (!$this->canExecute()) {
            return new CommandResult(false, "Cannot cast {$this->skill->name}: requirements not met");
        }

        $this->caster->spendMana($this->skill->manaCost);
        $this->target->applyEffect($this->buffName, $this->duration);

        return new CommandResult(
            true,
            "{$this->caster->getName()} casts {$this->skill->name} on {$this->target->getName()} applying {$this->buffName} for {$this->duration} seconds",
            [
                'buff' => $this->buffName,
                'duration' => $this->duration,
                'mana_spent' => $this->skill->manaCost,
            ]
        );
    }

    public function undo(): CommandResult
    {
        $this->target->removeEffect($this->buffName);
        $this->caster->restoreMana($this->skill->manaCost);

        return new CommandResult(
            true,
            "Undid {$this->skill->name}: removed {$this->buffName} from {$this->target->getName()} and restored {$this->skill->manaCost} mana",
            [
                'buff_removed' => $this->buffName,
                'mana_restored' => $this->skill->manaCost,
            ]
        );
    }
}