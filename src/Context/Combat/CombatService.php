<?php

declare(strict_types=1);

namespace AzerothEngine\Context\Combat;

use AzerothEngine\Behavioral\Strategy\DamageCalculationContext;
use AzerothEngine\Behavioral\Strategy\PhysicalDamageStrategy;
use AzerothEngine\Behavioral\Strategy\SpellDamageStrategy;
use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\Model\Skill;
use AzerothEngine\Structural\Adapter\DamageCalculatorInterface;
use AzerothEngine\Structural\Adapter\ModernDamageCalculator;
use AzerothEngine\Structural\Facade\GameCombatFacade;

class CombatService
{
    private GameCombatFacade $combatFacade;
    private DamageCalculationContext $damageContext;

    public function __construct()
    {
        $calculator = new ModernDamageCalculator();
        $this->combatFacade = new GameCombatFacade($calculator);
        $this->damageContext = new DamageCalculationContext();
    }

    public function initiateCombat(PlayerCharacter $attacker, PlayerCharacter $defender): \AzerothEngine\Structural\Facade\CombatResult
    {
        return $this->combatFacade->startCombat($attacker, $defender);
    }

    public function castSpell(PlayerCharacter $caster, PlayerCharacter $target, Skill $skill): \AzerothEngine\Structural\Facade\SkillResult
    {
        return $this->combatFacade->executeSkill($caster, $target, $skill);
    }

    public function autoAttack(PlayerCharacter $attacker, PlayerCharacter $defender): \AzerothEngine\Structural\Facade\SkillResult
    {
        return $this->combatFacade->autoAttack($attacker, $defender);
    }

    public function heal(PlayerCharacter $healer, PlayerCharacter $target, int $amount): \AzerothEngine\Structural\Facade\HealResult
    {
        return $this->combatFacade->heal($healer, $target, $amount);
    }

    public function calculateDamage(PlayerCharacter $attacker, PlayerCharacter $defender, DamageType $type, int $baseDamage): int
    {
        return $this->damageContext->calculateDamage($attacker, $defender, $type, $baseDamage);
    }

    public function setDamageStrategy(string $strategy): void
    {
        $this->damageContext->setStrategy(match ($strategy) {
            'physical' => new PhysicalDamageStrategy(),
            'spell' => new SpellDamageStrategy(),
            default => new PhysicalDamageStrategy(),
        });
    }
}