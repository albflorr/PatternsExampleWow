<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use AzerothEngine\Behavioral\Command\CastSpellCommand;
use AzerothEngine\Behavioral\Command\CommandQueue;
use AzerothEngine\Behavioral\Command\HealCommand;
use AzerothEngine\Behavioral\Command\MacroCommand;
use AzerothEngine\Behavioral\Observer\CombatNotifier;
use AzerothEngine\Behavioral\Observer\ConsoleLogger;
use AzerothEngine\Behavioral\Observer\FileLogger;
use AzerothEngine\Behavioral\Observer\UIUpdater;
use AzerothEngine\Behavioral\State\CharacterStateContext;
use AzerothEngine\Behavioral\Strategy\DamageCalculationContext;
use AzerothEngine\Behavioral\Strategy\FileStorageStrategy;
use AzerothEngine\Behavioral\Strategy\MemoryStorageStrategy;
use AzerothEngine\Behavioral\Strategy\StorageContext;
use AzerothEngine\Behavioral\TemplateMethod\DailyQuestCompletion;
use AzerothEngine\Behavioral\TemplateMethod\SimpleQuestCompletion;
use AzerothEngine\Creational\Builder\CharacterBuilder;
use AzerothEngine\Creational\Builder\CharacterDirector;
use AzerothEngine\Creational\Factory\AllianceCharacterFactory;
use AzerothEngine\Creational\Factory\CharacterFactoryRegistry;
use AzerothEngine\Creational\Factory\EquipmentFactoryRegistry;
use AzerothEngine\Creational\Factory\HordeCharacterFactory;
use AzerothEngine\Creational\Prototype\MonsterPrototypeManager;
use AzerothEngine\Creational\Singleton\EngineConfig;
use AzerothEngine\Creational\Singleton\GameWorld;
use AzerothEngine\Domain\Enum\CharacterClass;
use AzerothEngine\Domain\Enum\CharacterState;
use AzerothEngine\Domain\Enum\DamageType;
use AzerothEngine\Domain\Enum\Race;
use AzerothEngine\Domain\Model\PlayerCharacter;
use AzerothEngine\Domain\Model\Skill;
use AzerothEngine\Domain\ValueObject\Attributes;
use AzerothEngine\Domain\ValueObject\GoldAmount;
use AzerothEngine\Domain\ValueObject\Item;
use AzerothEngine\Domain\ValueObject\ItemId;
use AzerothEngine\Domain\ValueObject\ItemLevel;
use AzerothEngine\Domain\ValueObject\ItemQuality;
use AzerothEngine\Domain\ValueObject\ItemSlot;
use AzerothEngine\Domain\ValueObject\ManaPoints;
use AzerothEngine\Domain\ValueObject\HealthPoints;
use AzerothEngine\Structural\Adapter\DamageCalculatorInterface;
use AzerothEngine\Structural\Adapter\LegacyDamageCalculatorAdapter;
use AzerothEngine\Structural\Adapter\ModernDamageCalculator;
use AzerothEngine\Structural\Composite\QuestComposite;
use AzerothEngine\Structural\Composite\QuestLog;
use AzerothEngine\Structural\Composite\QuestObjective;
use AzerothEngine\Structural\Decorator\EnchantmentFactory;
use AzerothEngine\Structural\Decorator\FireEnchantment;
use AzerothEngine\Structural\Decorator\FrostEnchantment;
use AzerothEngine\Structural\Facade\GameCombatFacade;
use AzerothEngine\Structural\Proxy\AccountSecurityProxy;
use AzerothEngine\Structural\Proxy\GameService;
use AzerothEngine\Structural\Proxy\LoggingProxy;
use AzerothEngine\Context\Combat\CombatService;
use AzerothEngine\Context\Inventory\InventoryService;
use AzerothEngine\Context\Inventory\InMemoryInventoryRepository;
use AzerothEngine\Context\Inventory\InventoryAggregate;
use AzerothEngine\Context\Billing\BillingService;
use AzerothEngine\Context\Billing\InMemorySubscriptionRepository;
use AzerothEngine\Context\Billing\RealMoneyPayment;
use AzerothEngine\Context\Billing\InGameGoldPayment;
use AzerothEngine\Context\Billing\AuctionHouseService;
use AzerothEngine\Domain\Repository\InMemoryPlayerRepository;
use AzerothEngine\Domain\Repository\InMemoryItemRepository;

echo "==================================================\n";
echo "      ⚔️  AZEROTH ENGINE - MMORPG SIMULATION  ⚔️\n";
echo "==================================================\n\n";

// ================================================================
// 1. SINGLETON PATTERNS - GameWorld & EngineConfig
// ================================================================
echo "📌 SINGLETON PATTERNS\n";
echo "----------------------\n";

$gameWorld = GameWorld::getInstance();
$engineConfig = EngineConfig::getInstance();

echo "Game Version: {$engineConfig->get('game_version')}\n";
echo "Max Level: {$engineConfig->getMaxLevel()}\n";
echo "PvP Enabled: " . ($engineConfig->isPvpEnabled() ? 'Yes' : 'No') . "\n";
echo "Experience Rate: {$engineConfig->getExperienceRate()}x\n\n";

// ================================================================
// 2. FACTORY METHOD - Character Creation
// ================================================================
echo "📌 FACTORY METHOD - CHARACTER CREATION\n";
echo "---------------------------------------\n";

CharacterFactoryRegistry::register('horde', new HordeCharacterFactory());
CharacterFactoryRegistry::register('alliance', new AllianceCharacterFactory());

$orcWarrior = CharacterFactoryRegistry::createCharacter('horde', 'Grommash', Race::Orc, CharacterClass::Warrior);
$humanMage = CharacterFactoryRegistry::createCharacter('alliance', 'Jaina', Race::Human, CharacterClass::Mage);
$nightElfHunter = CharacterFactoryRegistry::createCharacter('alliance', 'Tyrande', Race::NightElf, CharacterClass::Hunter);

echo "Created: {$orcWarrior}\n";
echo "Created: {$humanMage}\n";
echo "Created: {$nightElfHunter}\n\n";

// ================================================================
// 3. ABSTRACT FACTORY - Equipment Creation
// ================================================================
echo "📌 ABSTRACT FACTORY - EQUIPMENT\n";
echo "--------------------------------\n";

EquipmentFactoryRegistry::register('horde', new \AzerothEngine\Creational\Factory\HordeEquipmentFactory());
EquipmentFactoryRegistry::register('alliance', new \AzerothEngine\Creational\Factory\AllianceEquipmentFactory());

$hordeSet = EquipmentFactoryRegistry::createFullSet('horde', 20);
$allianceSet = EquipmentFactoryRegistry::createFullSet('alliance', 20);

echo "Horde Level 20 Set:\n";
foreach ($hordeSet as $slot => $item) {
    echo "  {$slot}: {$item->name} ({$item->quality->value}) - {$item->attributes}\n";
}

echo "\nAlliance Level 20 Set:\n";
foreach ($allianceSet as $slot => $item) {
    echo "  {$slot}: {$item->name} ({$item->quality->value}) - {$item->attributes}\n";
}
echo "\n";

// ================================================================
// 4. BUILDER PATTERN - Complex Character Construction
// ================================================================
echo "📌 BUILDER PATTERN - CHARACTER BUILDER\n";
echo "---------------------------------------\n";

$director = new CharacterDirector();
$builder = new CharacterBuilder();

$starterOrc = $director->createStarterCharacter($builder, 'Thrall', Race::Orc, CharacterClass::Warrior);
echo "Starter: {$starterOrc}\n";

$builder2 = new CharacterBuilder();
$veteranHuman = $director->createVeteranCharacter($builder2, 'Arthas', Race::Human, CharacterClass::Mage, 40);
echo "Veteran: {$veteranHuman}\n";

$builder3 = new CharacterBuilder();
$twinkElf = $director->createTwinkCharacter($builder3, 'Sylvanas', Race::NightElf, CharacterClass::Hunter, 19);
echo "Twink: {$twinkElf}\n\n";

// ================================================================
// 5. PROTOTYPE PATTERN - Monster Spawning
// ================================================================
echo "📌 PROTOTYPE PATTERN - MONSTER SPAWNING\n";
echo "----------------------------------------\n";

$monsterManager = new MonsterPrototypeManager();

$beast = $monsterManager->spawnMonster('beast_prototype', ['name' => 'Wild Boar', 'level' => 5]);
echo "Spawned: {$beast}\n";

$bandit = $monsterManager->spawnMonster('humanoid_prototype', ['name' => 'Defias Bandit', 'level' => 12]);
echo "Spawned: {$bandit}\n";

$horde = $monsterManager->spawnHorde('undead_prototype', 3, ['damage_multiplier' => 1.5]);
echo "Spawned Undead Horde ({$horde[0]->getName()} x" . count($horde) . ")\n\n";

// ================================================================
// 6. ADAPTER PATTERN - Legacy Damage Calculator
// ================================================================
echo "📌 ADAPTER PATTERN - DAMAGE CALCULATOR\n";
echo "---------------------------------------\n";

$legacyAdapter = new LegacyDamageCalculatorAdapter();
$modernCalculator = new ModernDamageCalculator();

$attacker = $orcWarrior;
$defender = $humanMage;

$legacyDamage = $legacyAdapter->calculateDamage($attacker, $defender, DamageType::Physical);
$modernDamage = $modernCalculator->calculateDamage($attacker, $defender, DamageType::Physical);

echo "Legacy Calculator Damage: {$legacyDamage}\n";
echo "Modern Calculator Damage: {$modernDamage}\n\n";

// ================================================================
// 7. COMPOSITE PATTERN - Quest System
// ================================================================
echo "📌 COMPOSITE PATTERN - QUEST SYSTEM\n";
echo "------------------------------------\n";

$questLog = new QuestLog();

$killBoars = new QuestObjective(
    'kill_boars',
    'Boar Hunt',
    'Kill 10 wild boars in the valley',
    1,
    GoldAmount::fromSilver(50),
    200,
    fn($c) => $c->getLevel() >= 1,
    fn($c) => "Boars killed: " . min(10, $c->getLevel() * 2) . "/10"
);

$collectHerbs = new QuestObjective(
    'collect_herbs',
    'Herbalism',
    'Collect 5 peacebloom herbs',
    1,
    GoldAmount::fromSilver(30),
    150,
    fn($c) => $c->getLevel() >= 1,
    fn($c) => "Herbs collected: " . min(5, $c->getLevel()) . "/5"
);

$mainQuest = new QuestComposite(
    'starting_zone_chain',
    'Starting Zone Adventures',
    'Complete all starting zone quests',
    GoldAmount::fromGold(1),
    500,
    null
);
$mainQuest->add($killBoars);
$mainQuest->add($collectHerbs);

$questLog->addQuest($killBoars);
$questLog->addQuest($collectHerbs);
$questLog->addQuest($mainQuest);

echo "Quest Log:\n";
echo $questLog->getQuestProgress($orcWarrior) . "\n";

// ================================================================
// 8. DECORATOR PATTERN - Weapon Enchantments
// ================================================================
echo "📌 DECORATOR PATTERN - WEAPON ENCHANTMENTS\n";
echo "-------------------------------------------\n";

$baseWeapon = new \AzerothEngine\Structural\Decorator\BaseWeapon(
    new Item(
        id: new ItemId('iron_axe'),
        name: 'Iron Axe',
        slot: ItemSlot::MainHand,
        quality: ItemQuality::Common,
        level: new ItemLevel(10),
        attributes: new Attributes(strength: 15, agility: 5),
        buyPrice: GoldAmount::fromGold(1),
        sellPrice: GoldAmount::fromSilver(25)
    )
);

echo "Base Weapon: {$baseWeapon->getName()} - DPS: " . number_format($baseWeapon->calculateDPS($orcWarrior), 1) . "\n";

$fireWeapon = new FireEnchantment($baseWeapon, 30, 0.2);
echo "Fire Enchanted: {$fireWeapon->getName()} - DPS: " . number_format($fireWeapon->calculateDPS($orcWarrior), 1) . "\n";

$frostWeapon = new FrostEnchantment($fireWeapon, 20, 0.25, 0.6);
echo "Frost + Fire: {$frostWeapon->getName()} - DPS: " . number_format($frostWeapon->calculateDPS($orcWarrior), 1) . "\n";

$crusaderWeapon = EnchantmentFactory::applyEnchantment($frostWeapon, 'crusader', ['strength' => 100, 'chance' => 0.05]);
echo "Crusader + Frost + Fire: {$crusaderWeapon->getName()} - DPS: " . number_format($crusaderWeapon->calculateDPS($orcWarrior), 1) . "\n\n";

// ================================================================
// 9. FACADE PATTERN - Combat System
// ================================================================
echo "📌 FACADE PATTERN - COMBAT FACADE\n";
echo "----------------------------------\n";

$combatFacade = new GameCombatFacade();
$notifier = new CombatNotifier();
$notifier->addObserver(new ConsoleLogger());

$combatFacade->addCombatObserver(fn($event, $data) => $notifier->notify($event, $data));

echo "Starting combat between {$orcWarrior->getName()} and {$humanMage->getName()}...\n";
$result = $combatFacade->startCombat($orcWarrior, $humanMage);
echo "Result: {$result}\n\n";

// ================================================================
// 10. PROXY PATTERN - Account Security
// ================================================================
echo "📌 PROXY PATTERN - ACCOUNT SECURITY\n";
echo "------------------------------------\n";

$gameService = new GameService();
$securityProxy = new AccountSecurityProxy($gameService);
$loggingProxy = new LoggingProxy($securityProxy);

echo "Free account trying to enter Naxxramas:\n";
$loggingProxy->enterZone($orcWarrior, 'naxxramas');

echo "\nFree account trying to purchase legendary item:\n";
$legendaryItem = new Item(
    id: new ItemId('thunderfury'),
    name: 'Thunderfury, Blessed Blade of the Windseeker',
    slot: ItemSlot::MainHand,
    quality: ItemQuality::Legendary,
    level: new ItemLevel(60),
    attributes: new Attributes(strength: 100, agility: 100),
    buyPrice: GoldAmount::fromGold(1000),
    sellPrice: GoldAmount::fromGold(200)
);
$loggingProxy->purchaseItem($orcWarrior, $legendaryItem);

echo "\nVeteran account (level 60) accessing premium zone:\n";
$veteranMage = $director->createVeteranCharacter(new CharacterBuilder(), 'Khadgar', Race::Human, CharacterClass::Mage, 60);
$loggingProxy->enterZone($veteranMage, 'naxxramas');
echo "\n";

// ================================================================
// 11. STRATEGY PATTERN - Damage Calculation & Storage
// ================================================================
echo "📌 STRATEGY PATTERN - DAMAGE & STORAGE\n";
echo "---------------------------------------\n";

$damageContext = new DamageCalculationContext();
echo "Default Strategy: {$damageContext->getCurrentStrategyName()}\n";

$damageContext->setStrategy(new \AzerothEngine\Behavioral\Strategy\SpellDamageStrategy());
echo "Spell Strategy: {$damageContext->getCurrentStrategyName()}\n";

$damageContext->setStrategy(new \AzerothEngine\Behavioral\Strategy\PhysicalDamageStrategy());
echo "Physical Strategy: {$damageContext->getCurrentStrategyName()}\n";

// Storage Strategy
$storageContext = new StorageContext(new MemoryStorageStrategy());
$storageContext->save('player_data', ['name' => 'Test', 'level' => 10]);
echo "\nMemory Storage: " . $storageContext->getStrategyName() . "\n";
echo "Loaded: " . json_encode($storageContext->load('player_data')) . "\n";

$fileStorage = new StorageContext(new FileStorageStrategy('data'));
$fileStorage->save('config', ['version' => '1.0', 'debug' => true]);
echo "\nFile Storage: " . $fileStorage->getStrategyName() . "\n";
echo "Loaded: " . json_encode($fileStorage->load('config')) . "\n\n";

// ================================================================
// 12. OBSERVER PATTERN - Combat Events
// ================================================================
echo "📌 OBSERVER PATTERN - COMBAT EVENTS\n";
echo "------------------------------------\n";

$combatNotifier = new CombatNotifier();
$combatNotifier->addObserver(new ConsoleLogger());
$combatNotifier->addObserver(new FileLogger('events.log'));
$uiUpdater = new UIUpdater();
$combatNotifier->addObserver($uiUpdater);
$combatNotifier->addObserver(new \AzerothEngine\Behavioral\Observer\CharacterObserver($orcWarrior));

$combatNotifier->notifyCombatStart($orcWarrior, $humanMage);
$combatNotifier->notifyDamageDealt($orcWarrior, $humanMage, 150, DamageType::Physical, 'Heroic Strike');
$combatNotifier->notifyHealing($humanMage, $orcWarrior, 80);
$combatNotifier->notifyLevelUp($orcWarrior, 5);
$combatNotifier->notifySkillCast($humanMage, Skill::mageSkills()[1], $orcWarrior);
echo "\n";

// ================================================================
// 13. COMMAND PATTERN - Spell Casting
// ================================================================
echo "📌 COMMAND PATTERN - SPELL CASTING\n";
echo "-----------------------------------\n";

$damageContext = new DamageCalculationContext();
$commandQueue = new CommandQueue();

// Usamos a Arthas (mago humano nivel 40) como lanzador: domina Fireball (nivel 1),
// Frostbolt (nivel 4) y Arcane Explosion (nivel 14).
// Con Jaina (nivel 1) solo seria posible lanzar Fireball.
$caster = $veteranHuman;
$target = $veteranMage;

$fireball = Skill::mageSkills()[0]; // Fireball  - nivel 1
$frostbolt = Skill::mageSkills()[1]; // Frostbolt - nivel 4
$arcaneBlast = Skill::mageSkills()[2]; // Arcane Explosion - nivel 14

echo "Caster: {$caster->getName()} (nivel {$caster->getLevel()}, {$caster->getMana()} mana)\n";
echo "Target: {$target->getName()} (nivel {$target->getLevel()}, {$target->getHealth()} HP)\n\n";

echo "Queueing spells...\n";
$commandQueue->enqueue(new CastSpellCommand($caster, $target, $fireball, $damageContext));
$commandQueue->enqueue(new CastSpellCommand($caster, $target, $frostbolt, $damageContext));

echo "Executing queued spells:\n";
while ($commandQueue->getQueueSize() > 0) {
    $result = $commandQueue->executeNext();
    if ($result !== null) {
        echo "  {$result->message}\n";
    }
}

echo "\nUndoing last spell:\n";
$undoResult = $commandQueue->undoLast();
if ($undoResult !== null) {
    echo "  {$undoResult->message}\n";
    echo "  Target HP tras el undo: {$target->getHealth()}\n";
} else {
    echo "  Nothing to undo (no spell was executed successfully)\n";
}

// Macro Command: combo de 3 hechizos ejecutados como una unidad
$macro = new MacroCommand('Mage Burst Combo');
$macro->addCommand(new CastSpellCommand($caster, $target, $fireball, $damageContext));
$macro->addCommand(new CastSpellCommand($caster, $target, $frostbolt, $damageContext));
$macro->addCommand(new CastSpellCommand($caster, $target, $arcaneBlast, $damageContext));

echo "\nExecuting Macro: {$macro->getName()}\n";
$macroResult = $macro->execute();
echo "  {$macroResult->message}\n";
if ($macroResult->success) {
    echo "  Target HP tras el combo: {$target->getHealth()}\n";
    echo "  Undoing the whole macro:\n";
    $macroUndo = $macro->undo();
    echo "  {$macroUndo->message}\n";
    echo "  Target HP tras el undo del macro: {$target->getHealth()}\n";
}
echo "\n";

// ================================================================
// 14. TEMPLATE METHOD - Quest Completion
// ================================================================
echo "📌 TEMPLATE METHOD - QUEST COMPLETION\n";
echo "--------------------------------------\n";

$simpleQuest = new SimpleQuestCompletion(
    'daily_boars',
    'Daily Boar Hunt',
    10,
    1000,
    GoldAmount::fromGold(2),
    fn($c) => $c->getLevel() >= 10,
    null
);

echo "Attempting daily quest:\n";
$result = $simpleQuest->completeQuest($orcWarrior);
echo "  {$result->message}\n";
if ($result->rewards) {
    echo "  Rewards: {$result->rewards->experience} XP, {$result->rewards->gold}\n";
}

// Daily Quest
$dailyQuest = new DailyQuestCompletion(
    'daily_heroic',
    'Daily Heroic Dungeon',
    60,
    5000,
    GoldAmount::fromGold(10),
    fn($c) => $c->getLevel() >= 60
);

echo "\nAttempting daily quest (requires level 60):\n";
// $veteranHuman es Arthas (nivel 40) -> requisitos incumplidos a proposito.
// $veteranMage es Khadgar (nivel 60)  -> la mision se completa.
$result = $dailyQuest->completeQuest($veteranHuman);
echo "  Arthas (nivel {$veteranHuman->getLevel()}): {$result->message}\n";

$result = $dailyQuest->completeQuest($veteranMage);
echo "  Khadgar (nivel {$veteranMage->getLevel()}): {$result->message}\n";
if ($result->rewards) {
    echo "  Rewards: {$result->rewards->experience} XP, {$result->rewards->gold}\n";
}
echo "\n";

// ================================================================
// 15. STATE PATTERN - Character States
// ================================================================
echo "📌 STATE PATTERN - CHARACTER STATES\n";
echo "------------------------------------\n";

$stateContext = new CharacterStateContext($orcWarrior);
echo "Initial: {$stateContext}\n";

echo "\nApplying Stun...\n";
$stateContext->transitionTo(CharacterState::Stunned, ['duration' => 3]);
echo "Can attack: " . ($stateContext->canAttack() ? 'Yes' : 'No') . "\n";
echo "Can move: " . ($stateContext->canMove() ? 'Yes' : 'No') . "\n";

echo "\nTicking state (1s)...\n";
$stateContext->tick();
echo "State: {$stateContext}\n";

echo "\nTicking state (2s)...\n";
$stateContext->tick();
$stateContext->tick();
echo "State: {$stateContext}\n";

echo "\nApplying Poison...\n";
$stateContext->transitionTo(CharacterState::Poisoned, ['duration' => 6, 'tick_damage' => 20]);
echo "State: {$stateContext}\n";

echo "\nTicking poison (3 ticks)...\n";
$stateContext->tick();
$stateContext->tick();
$stateContext->tick();
echo "Health: {$orcWarrior->getHealth()}\n";
echo "State: {$stateContext}\n\n";

// ================================================================
// 16. DDD - BOUNDED CONTEXTS
// ================================================================
echo "📌 DDD - BOUNDED CONTEXTS\n";
echo "--------------------------\n";

// Combat Context
$combatService = new CombatService();
echo "Combat Context initialized\n";

// Inventory Context
$inventoryRepo = new InMemoryInventoryRepository();
$inventoryService = new InventoryService($inventoryRepo);
$inventory = $inventoryService->getInventory($orcWarrior->getId());
$inventory->equipItem($hordeSet['main_hand']);
$inventory->addToBag($hordeSet['off_hand']);
echo "Inventory Context: {$inventory->getBagUsedSlots()}/{$inventory->getBagCapacity()} slots used\n";

// Billing Context
$subRepo = new InMemorySubscriptionRepository();
$billingService = new BillingService($subRepo);
$realMoney = new RealMoneyPayment('account_123', 50.0);
$billingService->purchaseSubscription('account_123', 'premium', 1, $realMoney);
$status = $billingService->getSubscriptionStatus('account_123');
echo "Billing Context: Subscription {$status['tier']} - {$status['days_remaining']} days remaining\n";

// Auction House
$auctionHouse = new AuctionHouseService();
$auctionHouse->createListing($orcWarrior, $hordeSet['main_hand'], GoldAmount::fromGold(5), GoldAmount::fromGold(20));
echo "Auction House: " . count($auctionHouse->getActiveListings()) . " active listings\n\n";

// ================================================================
// 17. REPOSITORY PATTERN
// ================================================================
echo "📌 REPOSITORY PATTERN\n";
echo "---------------------\n";

$playerRepo = new InMemoryPlayerRepository();
$playerRepo->save($orcWarrior);
$playerRepo->save($humanMage);
$playerRepo->save($nightElfHunter);

echo "Total players in repository: " . count($playerRepo->findAll()) . "\n";
$found = $playerRepo->findByName('Grommash');
echo "Found by name: " . ($found ? $found->getName() : 'Not found') . "\n";

$itemRepo = new InMemoryItemRepository();
foreach ($hordeSet as $item) {
    $itemRepo->save($item);
}
foreach ($allianceSet as $item) {
    $itemRepo->save($item);
}
echo "Total items in repository: " . count($itemRepo->findAll()) . "\n";
echo "Plate items: " . count($itemRepo->findBySlot(ItemSlot::Chest)) . "\n\n";

// ================================================================
// FINAL SUMMARY
// ================================================================
echo "==================================================\n";
echo "           SIMULATION COMPLETE ✓\n";
echo "==================================================\n";
echo "\nPatterns Demonstrated:\n";
echo "  ✓ Singleton (GameWorld, EngineConfig)\n";
echo "  ✓ Factory Method (CharacterFactory)\n";
echo "  ✓ Abstract Factory (EquipmentFactory)\n";
echo "  ✓ Builder (CharacterBuilder)\n";
echo "  ✓ Prototype (MonsterPrototypeManager)\n";
echo "  ✓ Adapter (LegacyDamageCalculatorAdapter)\n";
echo "  ✓ Composite (QuestLog/QuestComposite)\n";
echo "  ✓ Decorator (Weapon Enchantments)\n";
echo "  ✓ Facade (GameCombatFacade)\n";
echo "  ✓ Proxy (AccountSecurityProxy, LoggingProxy)\n";
echo "  ✓ Strategy (DamageCalculation, Storage)\n";
echo "  ✓ Observer (CombatNotifier)\n";
echo "  ✓ Command (CastSpellCommand, MacroCommand)\n";
echo "  ✓ Template Method (QuestCompletion)\n";
echo "  ✓ State (CharacterStateMachine)\n";
echo "  ✓ DDD (Bounded Contexts: Combat, Inventory, Billing)\n";
echo "  ✓ Entities (PlayerCharacter)\n";
echo "  ✓ Value Objects (HealthPoints, GoldAmount, ManaPoints, Attributes, Item)\n";
echo "  ✓ Aggregates (InventoryAggregate)\n";
echo "  ✓ Repositories (Player, Item, Quest, Inventory, Subscription)\n";
echo "\nPHP 8.2+ Features Used:\n";
echo "  ✓ declare(strict_types=1)\n";
echo "  ✓ Enums (CharacterClass, Race, CharacterState, DamageType, etc.)\n";
echo "  ✓ Readonly classes (Value Objects)\n";
echo "  ✓ Constructor Property Promotion\n";
echo "  ✓ Match Expressions\n";
echo "  ✓ Union/Intersection Types\n";
echo "  ✓ Typed Properties\n";
echo "\n";