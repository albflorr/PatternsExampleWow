<?php

declare(strict_types=1);

namespace AzerothEngine\Structural\Facade;

use AzerothEngine\Behavioral\Observer\CombatNotifier;
use AzerothEngine\Behavioral\State\CharacterStateContext;
use AzerothEngine\Behavioral\Strategy\DamageCalculationContext;
use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\Model\Skill;
use AzerothEngine\Structural\Adapter\DamageCalculatorInterface;
use AzerothEngine\Structural\Adapter\ModernDamageCalculator;

class GameCombatFacade
{
    private DamageCalculatorInterface $damageCalculator;
    private CombatNotifier $notifier;
    private DamageCalculationContext $damageContext;
    private array $combatLog = [];
    private int $round = 0;

    public function __construct(?DamageCalculatorInterface $calculator = null)
    {
        $this->damageCalculator = $calculator ?? new ModernDamageCalculator();
        $this->notifier = new CombatNotifier();
        $this->damageContext = new DamageCalculationContext();
    }

    public function startCombat(PlayerCharacter $attacker, PlayerCharacter $defender): CombatResult
    {
        $this->validateCombatants($attacker, $defender);
        $this->round = 0;
        $this->combatLog = [];

        $attackerState = new CharacterStateContext($attacker);
        $defenderState = new CharacterStateContext($defender);

        $this->notifier->notifyCombatStart($attacker, $defender);

        while (!$this->isCombatOver($attacker, $defender)) {
            $this->round++;
            $this->processRound($attacker, $defender, $attackerState, $defenderState);

            if ($this->round > 100) {
                $this->log('Combat round limit reached, ending in draw');
                break;
            }
        }

        $result = $this->determineResult($attacker, $defender);
        $this->notifier->notifyCombatEnd($attacker, $defender, $result);

        return $result;
    }

    public function executeSkill(PlayerCharacter $caster, PlayerCharacter $target, Skill $skill): SkillResult
    {
        if (!$skill->canCast($caster)) {
            return new SkillResult(false, 0, 'Cannot cast skill: requirements not met');
        }

        $caster->spendMana($skill->manaCost);
        $damage = $this->damageContext.calculateDamage($caster, $target, $skill->damageType, $skill->calculateDamage($caster));

        if ($skill->damageType === DamageType::Physical) {
            $target->takeDamage($damage);
        } else {
            $target->takeDamage($damage);
        }

        $this->notifier->notifyDamageDealt($caster, $target, $damage, $skill->damageType, $skill->name);

        return new SkillResult(true, $damage, "{$skill->name} hits {$target->getName()} for {$damage} {$skill->damageType->value} damage");
    }

    public function autoAttack(PlayerCharacter $attacker, PlayerCharacter $defender): SkillResult
    {
        $damage = $this->damageCalculator->calculateDamage($attacker, $defender, DamageType::Physical);
        $defender->takeDamage($damage);

        $this->notifier->notifyDamageDealt($attacker, $defender, $damage, DamageType::Physical, 'Auto Attack');

        return new SkillResult(true, $damage, "Auto attack hits {$defender->getName()} for {$damage} damage");
    }

    public function heal(PlayerCharacter $healer, PlayerCharacter $target, int $amount): HealResult
    {
        $healing = $this->damageCalculator->calculateHealing($healer, $target);
        $actualHeal = min($healing, $target->getHealth()->maximum - $target->getHealth()->current);
        $target->heal($actualHeal);

        $this->notifier->notifyHealing($healer, $target, $actualHeal);

        return new HealResult($actualHeal, "{$healer->getName()} heals {$target->getName()} for {$actualHeal}");
    }

    private function validateCombatants(PlayerCharacter $attacker, PlayerCharacter $defender): void
    {
        if ($attacker->getId() === $defender->getId()) {
            throw new \InvalidArgumentException('Cannot attack yourself');
        }
        if (!$attacker->canAct()) {
            throw new \InvalidArgumentException('Attacker cannot act');
        }
        if ($defender->getState() === CharacterState::Dead) {
            throw new \InvalidArgumentException('Target is already dead');
        }
    }

    private function processRound(
        PlayerCharacter $attacker,
        PlayerCharacter $defender,
        CharacterStateContext $attackerState,
        CharacterStateContext $defenderState
    ): void {
        $attackerState->tick();
        $defenderState->tick();

        if ($attacker->canAct()) {
            $this->autoAttack($attacker, $defender);
        }

        if ($defender->canAct() && $defender->getState() !== CharacterState::Dead) {
            $this->autoAttack($defender, $attacker);
        }

        $attacker->tickEffects();
        $defender->tickEffects();
    }

    private function isCombatOver(PlayerCharacter $attacker, PlayerCharacter $defender): bool
    {
        return $attacker->getState() === CharacterState::Dead || $defender->getState() === CharacterState::Dead;
    }

    private function determineResult(PlayerCharacter $attacker, PlayerCharacter $defender): CombatResult
    {
        if ($attacker->getState() === CharacterState::Dead && $defender->getState() === CharacterState::Dead) {
            return new CombatResult(CombatResult::DRAW, $attacker, $defender, $this->round, $this->combatLog);
        }
        if ($attacker->getState() === CharacterState::Dead) {
            return new CombatResult(CombatResult::DEFENDER_WINS, $attacker, $defender, $this->round, $this->combatLog);
        }
        if ($defender->getState() === CharacterState::Dead) {
            return new CombatResult(CombatResult::ATTACKER_WINS, $attacker, $defender, $this->round, $this->combatLog);
        }
        return new CombatResult(CombatResult::DRAW, $attacker, $defender, $this->round, $this->combatLog);
    }

    private function log(string $message): void
    {
        $this->combatLog[] = "[Round {$this->round}] {$message}";
    }

    public function getCombatLog(): array
    {
        return $this->combatLog;
    }

    public function addCombatObserver(callable $observer): void
    {
        $this->notifier->addObserver($observer);
    }
}

class CombatResult
{
    public const ATTACKER_WINS = 'attacker_wins';
    public const DEFENDER_WINS = 'defender_wins';
    public const DRAW = 'draw';

    public function __construct(
        public string $outcome,
        public PlayerCharacter $attacker,
        public PlayerCharacter $defender,
        public int $rounds,
        public array $log
    ) {}

    public function getWinner(): ?PlayerCharacter
    {
        return match ($this->outcome) {
            self::ATTACKER_WINS => $this->attacker,
            self::DEFENDER_WINS => $this->defender,
            default => null,
        };
    }

    public function __toString(): string
    {
        $winner = $this->getWinner();
        return "Combat ended after {$this->rounds} rounds. " .
            ($winner ? "Winner: {$winner->getName()}" : "Draw");
    }
}

class SkillResult
{
    public function __construct(
        public bool $success,
        public int $damage,
        public string $message
    ) {}
}

class HealResult
{
    public function __construct(
        public int $amount,
        public string $message
    ) {}
}