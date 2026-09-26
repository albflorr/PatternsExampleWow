<?php

declare(strict_types=1);

namespace AzerothEngine\Behavioral\Observer;

use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\Model\Skill;

interface ObserverInterface
{
    public function update(string $event, array $data): void;
}

interface ObservableInterface
{
    public function addObserver(ObserverInterface|callable $observer): void;
    public function removeObserver(ObserverInterface|callable $observer): void;
    public function notify(string $event, array $data): void;
}

class CombatNotifier implements ObservableInterface
{
    /** @var (ObserverInterface|callable)[] */
    private array $observers = [];

    public function addObserver(ObserverInterface|callable $observer): void
    {
        $this->observers[] = $observer;
    }

    public function removeObserver(ObserverInterface|callable $observer): void
    {
        $this->observers = array_filter($this->observers, fn($o) => $o !== $observer);
    }

    public function notify(string $event, array $data): void
    {
        foreach ($this->observers as $observer) {
            if ($observer instanceof ObserverInterface) {
                $observer->update($event, $data);
            } else {
                $observer($event, $data);
            }
        }
    }

    public function notifyCombatStart(PlayerCharacter $attacker, PlayerCharacter $defender): void
    {
        $this->notify('combat_start', [
            'attacker' => $attacker->getName(),
            'defender' => $defender->getName(),
            'timestamp' => time(),
        ]);
    }

    public function notifyCombatEnd(PlayerCharacter $attacker, PlayerCharacter $defender, \AzerothEngine\Structural\Facade\CombatResult $result): void
    {
        $this->notify('combat_end', [
            'attacker' => $attacker->getName(),
            'defender' => $defender->getName(),
            'winner' => $result->getWinner()?->getName() ?? 'draw',
            'rounds' => $result->rounds,
            'timestamp' => time(),
        ]);
    }

    public function notifyDamageDealt(PlayerCharacter $attacker, PlayerCharacter $defender, int $damage, DamageType $type, string $source): void
    {
        $this->notify('damage_dealt', [
            'attacker' => $attacker->getName(),
            'defender' => $defender->getName(),
            'damage' => $damage,
            'type' => $type->value,
            'source' => $source,
            'defender_hp' => $defender->getHealth()->current,
            'defender_max_hp' => $defender->getHealth()->maximum,
            'timestamp' => time(),
        ]);
    }

    public function notifyHealing(PlayerCharacter $healer, PlayerCharacter $target, int $amount): void
    {
        $this->notify('healing', [
            'healer' => $healer->getName(),
            'target' => $target->getName(),
            'amount' => $amount,
            'target_hp' => $target->getHealth()->current,
            'target_max_hp' => $target->getHealth()->maximum,
            'timestamp' => time(),
        ]);
    }

    public function notifyLevelUp(PlayerCharacter $character, int $newLevel): void
    {
        $this->notify('level_up', [
            'character' => $character->getName(),
            'new_level' => $newLevel,
            'timestamp' => time(),
        ]);
    }

    public function notifySkillCast(PlayerCharacter $caster, Skill $skill, PlayerCharacter $target): void
    {
        $this->notify('skill_cast', [
            'caster' => $caster->getName(),
            'skill' => $skill->name,
            'target' => $target->getName(),
            'timestamp' => time(),
        ]);
    }

    public function notifyItemLooted(PlayerCharacter $character, string $itemName, int $quantity): void
    {
        $this->notify('item_looted', [
            'character' => $character->getName(),
            'item' => $itemName,
            'quantity' => $quantity,
            'timestamp' => time(),
        ]);
    }

    public function notifyQuestCompleted(PlayerCharacter $character, string $questTitle): void
    {
        $this->notify('quest_completed', [
            'character' => $character->getName(),
            'quest' => $questTitle,
            'timestamp' => time(),
        ]);
    }
}

class ConsoleLogger implements ObserverInterface
{
    public function update(string $event, array $data): void
    {
        $timestamp = date('H:i:s');
        $output = "[{$timestamp}] ";

        switch ($event) {
            case 'combat_start':
                $output .= "⚔️ Combat started: {$data['attacker']} vs {$data['defender']}";
                break;
            case 'combat_end':
                $output .= "🏁 Combat ended: {$data['winner']} wins after {$data['rounds']} rounds";
                break;
            case 'damage_dealt':
                $hpPercent = $data['defender_max_hp'] > 0
                    ? round(($data['defender_hp'] / $data['defender_max_hp']) * 100)
                    : 0;
                $output .= "💥 {$data['attacker']} deals {$data['damage']} {$data['type']} damage to {$data['defender']} ({$hpPercent}% HP)";
                break;
            case 'healing':
                $output .= "✨ {$data['healer']} heals {$data['target']} for {$data['amount']} HP";
                break;
            case 'level_up':
                $output .= "🎉 {$data['character']} reached level {$data['new_level']}!";
                break;
            case 'skill_cast':
                $output .= "✨ {$data['caster']} casts {$data['skill']} on {$data['target']}";
                break;
            case 'item_looted':
                $output .= "🎒 {$data['character']} looted {$data['quantity']}x {$data['item']}";
                break;
            case 'quest_completed':
                $output .= "✅ {$data['character']} completed quest: {$data['quest']}";
                break;
            default:
                $output .= "Event: {$event} - " . json_encode($data);
        }

        echo $output . "\n";
    }
}

class FileLogger implements ObserverInterface
{
    private string $logFile;

    public function __construct(string $logFile = 'combat.log')
    {
        $this->logFile = $logFile;
    }

    public function update(string $event, array $data): void
    {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'data' => $data,
        ];
        file_put_contents($this->logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);
    }
}

class UIUpdater implements ObserverInterface
{
    private array $uiState = [];

    public function update(string $event, array $data): void
    {
        $this->uiState[$event] = array_merge($data, ['last_update' => time()]);
    }

    public function getUIState(string $event): array
    {
        return $this->uiState[$event] ?? [];
    }

    public function getAllUIState(): array
    {
        return $this->uiState;
    }
}

class CharacterObserver implements ObserverInterface
{
    private PlayerCharacter $character;

    public function __construct(PlayerCharacter $character)
    {
        $this->character = $character;
    }

    public function update(string $event, array $data): void
    {
        if ($event === 'damage_dealt' && $data['defender'] === $this->character->getName()) {
            if ($this->character->getHealth()->getPercentage() < 20) {
                echo "[{$this->character->getName()}] WARNING: Low health! ({$data['defender_hp']}/{$data['defender_max_hp']})\n";
            }
        }

        if ($event === 'healing' && $data['target'] === $this->character->getName()) {
            echo "[{$this->character->getName()}] Received healing: {$data['amount']} HP\n";
        }

        if ($event === 'level_up' && $data['character'] === $this->character->getName()) {
            echo "[{$this->character->getName()}] Level up! Now level {$data['new_level']}\n";
        }
    }
}