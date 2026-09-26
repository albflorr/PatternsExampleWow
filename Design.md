# 📐 Diagrama de Clases — Azeroth Engine

> Diagrama de clases completo de la solución, generado en **PlantUML**.
> Para visualizarlo: copiar el bloque de código en [www.plantuml.com/plantuml](http://www.plantuml.com/plantuml)
> o usar la extensión *PlantUML* de VS Code / IntelliJ.

```plantuml
@startuml AzerothEngine

' ============================================================
'  AZEROTH ENGINE - DIAGRAMA DE CLASES COMPLETO
'  PHP 8.2+ | declare(strict_types=1) | Unidad 1 Diseño y Arquitectura
' ============================================================

title 🏰 Azeroth Engine — Diagrama de Clases Completo (PHP 8.2+)

skinparam classAttributeIconSize 0
skinparam classBackgroundColor #FEFEFE
skinparam classBorderColor #2E5C8A
skinparam classArrowColor #2E5C8A
skinparam shadowing false
skinparam linetype ortho
hide empty members

' ============================================================
'  ENUMERATIONS
' ============================================================
package "Domain :: Enum" #F3E5F5 {

    enum CharacterClass {
        Warrior = 'warrior'
        Mage = 'mage'
        Hunter = 'hunter'
        --
        +getPrimaryAttribute() : string
        +getBaseHealth() : int
        +getBaseMana() : int
    }

    enum Race {
        Orc = 'orc'
        Human = 'human'
        NightElf = 'night_elf'
        Undead = 'undead'
        --
        +getFaction() : string
        +getRacialBonus() : array
    }

    enum CharacterState {
        Normal = 'normal'
        Stunned = 'stunned'
        Dead = 'dead'
        Poisoned = 'poisoned'
        Frozen = 'frozen'
        --
        +canAct() : bool
        +canMove() : bool
    }

    enum DamageType {
        Physical
        Fire
        Frost
        Arcane
        Shadow
        Nature
        Holy
        --
        +getResistanceAttribute() : string
    }
}

package "Domain :: ValueObject :: Enums" #F3E5F5 {

    enum ItemSlot {
        Head
        Neck
        Shoulder
        Back
        Chest
        Wrist
        Hands
        Waist
        Legs
        Feet
        Finger1
        Finger2
        Trinket1
        Trinket2
        MainHand
        OffHand
        Ranged
    }

    enum ItemQuality {
        Poor
        Common
        Uncommon
        Rare
        Epic
        Legendary
        --
        +getColor() : string
    }

    enum ArmorType {
        Cloth
        Leather
        Mail
        Plate
        Shield
    }

    enum WeaponType {
        Sword
        Axe
        Mace
        Dagger
        Staff
        Bow
        Gun
        Crossbow
        Wand
        Fist
        Polearm
        Shield
    }
}

' ============================================================
'  VALUE OBJECTS  (immutables — readonly class)
' ============================================================
package "Domain :: ValueObject" #E8F5E9 {

    class HealthPoints <<readonly>> {
        +int $current
        +int $maximum
        --
        +__construct(int $current, int $maximum)
        +full(int $maximum) : self
        +empty(int $maximum) : self
        +isFull() : bool
        +isEmpty() : bool
        +getPercentage() : float
        +withCurrent(int) : self
        +withMaximum(int) : self
        +heal(int $amount) : self
        +damage(int $amount) : self
        +__toString() : string
    }

    class ManaPoints <<readonly>> {
        +int $current
        +int $maximum
        --
        +__construct(int $current, int $maximum)
        +full(int $maximum) : self
        +empty() : self
        +isFull() : bool
        +isEmpty() : bool
        +getPercentage() : float
        +withCurrent(int) : self
        +withMaximum(int) : self
        +spend(int $amount) : self
        +restore(int $amount) : self
        +canAfford(int $cost) : bool
        +__toString() : string
    }

    class GoldAmount <<readonly>> {
        +int $copper
        --
        +__construct(int $copper)
        +fromGold(int) : self
        +fromSilver(int) : self
        +fromCopper(int) : self
        +getGold() : int
        +getSilver() : int
        +getCopper() : int
        +add(GoldAmount) : self
        +subtract(GoldAmount) : self
        +canAfford(GoldAmount) : bool
        +multiply(int $factor) : self
        +__toString() : string
    }

    class Attributes <<readonly>> {
        +int $strength
        +int $agility
        +int $intellect
        +int $stamina
        +int $spirit
        +int $armor
        +int $fireResistance
        +int $frostResistance
        +int $arcaneResistance
        +int $shadowResistance
        +int $natureResistance
        +int $holyResistance
        --
        +__construct(int ... = 0)
        +base(CharacterClass) : self
        +add(Attributes) : self
        +getPrimaryForClass(CharacterClass) : int
        +getHealthBonus() : int
        +getManaBonus() : int
        +getAttackPower() : int
        +getSpellPower() : int
        +getCritChance() : float
        +__toString() : string
    }

    class ItemId <<readonly>> {
        +string $value
        --
        +__construct(string $value)
        +__toString() : string
    }

    class ItemLevel <<readonly>> {
        +int $value
        --
        +__construct(int $value)
    }

    class Item <<readonly>> {
        +ItemId $id
        +string $name
        +ItemSlot $slot
        +ItemQuality $quality
        +ItemLevel $level
        +Attributes $attributes
        +GoldAmount $buyPrice
        +GoldAmount $sellPrice
        +bool $isEquippable
        --
        +__construct(...)
    }
}

' ============================================================
'  DOMAIN MODEL  (Entidades)
' ============================================================
package "Domain :: Model" #E3F2FD {

    class PlayerCharacter <<Entity>> {
        -string $id
        -string $name
        -CharacterClass $class
        -Race $race
        -int $level
        -int $experience
        -HealthPoints $health
        -ManaPoints $mana
        -Attributes $baseAttributes
        -Attributes $equipmentAttributes
        -array $equipment
        -GoldAmount $gold
        -CharacterState $state
        -array $activeEffects
        --
        +__construct(string $id, string $name, CharacterClass, Race, int $level = 1, int $experience = 0, ?GoldAmount, ?CharacterState)
        +getId() : string
        +getName() : string
        +getClass() : CharacterClass
        +getRace() : Race
        +getLevel() : int
        +getExperience() : int
        +getHealth() : HealthPoints
        +getMana() : ManaPoints
        +getGold() : GoldAmount
        +getState() : CharacterState
        +getTotalAttributes() : Attributes
        +getBaseAttributes() : Attributes
        +getEquipmentAttributes() : Attributes
        +getEquipment() : array
        +getEquippedItem(ItemSlot) : ?Item
        +equipItem(Item) : void
        +unequipSlot(ItemSlot) : ?Item
        +addExperience(int) : void
        +takeDamage(int) : void
        +heal(int) : void
        +spendMana(int) : void
        +restoreMana(int) : void
        +addGold(GoldAmount) : void
        +spendGold(GoldAmount) : void
        +setState(CharacterState) : void
        +die() : void
        +revive() : void
        +canAct() : bool
        +applyEffect(string, int) : void
        +removeEffect(string) : void
        +hasEffect(string) : bool
        +tickEffects() : void
        +getAttackPower() : int
        +getSpellPower() : int
        +getCritChance() : float
        +__toString() : string
    }

    class Skill <<readonly>> {
        +string $id
        +string $name
        +CharacterClass $requiredClass
        +int $requiredLevel
        +DamageType $damageType
        +int $baseDamage
        +int $manaCost
        +float $coefficient
        +int $cooldownSeconds
        +string $description
        --
        +__construct(...)
        +calculateDamage(PlayerCharacter) : int
        +canCast(PlayerCharacter) : bool
        +warriorSkills() : array
        +mageSkills() : array
        +hunterSkills() : array
    }

    abstract class Quest {
        -string $id
        -string $title
        -string $description
        -int $requiredLevel
        -GoldAmount $rewardGold
        -?Item $rewardItem
        -int $experienceReward
        -bool $isCompleted
        --
        +__construct(string, string, string, int, GoldAmount, int, ?Item)
        +getId() : string
        +getTitle() : string
        +getDescription() : string
        +getRequiredLevel() : int
        +getRewardGold() : GoldAmount
        +getExperienceReward() : int
        +getRewardItem() : ?Item
        +isCompleted() : bool
        +markCompleted() : void
        +{abstract} canStart(PlayerCharacter) : bool
        +{abstract} isComplete(PlayerCharacter) : bool
        +{abstract} getProgress(PlayerCharacter) : string
    }

    class SimpleQuest {
        -callable $completionCheck
        -callable $progressCheck
    }

    class CompositeQuest {
        -array $subQuests
        --
        +addSubQuest(Quest) : void
        +getSubQuests() : array
    }
}

' ============================================================
'  REPOSITORIES  (DIP — interfaces + in-memory)
' ============================================================
package "Domain :: Repository" #FFF3E0 {

    interface PlayerRepositoryInterface {
        +save(PlayerCharacter) : void
        +findById(string) : ?PlayerCharacter
        +findByName(string) : ?PlayerCharacter
        +findAll() : array
        +delete(string) : void
        +exists(string) : bool
    }

    class InMemoryPlayerRepository {
        -array $players
        --
        +save(PlayerCharacter) : void
        +findById(string) : ?PlayerCharacter
        +findByName(string) : ?PlayerCharacter
        +findAll() : array
        +delete(string) : void
        +exists(string) : bool
    }

    interface ItemRepositoryInterface {
        +save(Item) : void
        +findById(ItemId) : ?Item
        +findBySlot(ItemSlot) : array
        +findByLevelRange(int, int) : array
        +findAll() : array
    }

    class InMemoryItemRepository {
        -array $items
        --
        +save(Item) : void
        +findById(ItemId) : ?Item
        +findBySlot(ItemSlot) : array
        +findByLevelRange(int, int) : array
        +findAll() : array
    }

    interface QuestRepositoryInterface {
        +save(Quest) : void
        +findById(string) : ?Quest
        +findByLevel(int) : array
        +findAll() : array
    }

    class InMemoryQuestRepository {
        -array $quests
        --
        +save(Quest) : void
        +findById(string) : ?Quest
        +findByLevel(int) : array
        +findAll() : array
    }
}

' ============================================================
'  CREATIONAL :: Singleton
' ============================================================
package "Creational :: Singleton" #FFF9C4 {

    class GameWorld <<Singleton>> {
        -{static} ?GameWorld $instance
        -array $onlineCharacters
        -array $zonePlayers
        -string $serverTime
        -int $maxPlayers
        -bool $maintenanceMode
        --
        -__construct()
        -{static} getInstance() : GameWorld
        -__clone()
        +__wakeup()
        +addPlayer(PlayerCharacter) : void
        +removePlayer(string) : void
        +getPlayer(string) : ?PlayerCharacter
        +getOnlineCount() : int
        +getPlayersInZone(string) : array
        +movePlayerToZone(string, string) : void
        +isMaintenanceMode() : bool
        +setMaintenanceMode(bool) : void
        +getServerTime() : string
        +broadcastMessage(string) : void
        +getFactionBalance() : array
    }

    class EngineConfig <<Singleton>> {
        -{static} ?EngineConfig $instance
        -array $config
        --
        -__construct()
        -{static} getInstance() : EngineConfig
        +get(string, mixed) : mixed
        +set(string, mixed) : void
        +getAll() : array
        +getMaxLevel() : int
        +isPvpEnabled() : bool
        +getExperienceRate() : float
    }
}

' ============================================================
'  CREATIONAL :: Factory Method
' ============================================================
package "Creational :: Factory (Factory Method)" #FFF9C4 {

    interface CharacterFactoryInterface {
        +createCharacter(string $name, Race, CharacterClass) : PlayerCharacter
    }

    abstract class CharacterFactory {
        -int $characterCounter
        --
        +createCharacter(string $name, Race, CharacterClass) : PlayerCharacter
        -{abstract} validateRaceClassCombination(Race, CharacterClass) : void
        -{abstract} createCharacterInstance(string, string, Race, CharacterClass) : PlayerCharacter
        -{abstract} initializeCharacter(PlayerCharacter) : void
        -generateCharacterId(Race, CharacterClass) : string
    }

    class HordeCharacterFactory {
        --
        +validateRaceClassCombination(Race, CharacterClass) : void
        +createCharacterInstance(string, string, Race, CharacterClass) : PlayerCharacter
        +initializeCharacter(PlayerCharacter) : void
    }

    class AllianceCharacterFactory {
        --
        +validateRaceClassCombination(Race, CharacterClass) : void
        +createCharacterInstance(string, string, Race, CharacterClass) : PlayerCharacter
        +initializeCharacter(PlayerCharacter) : void
    }

    class CharacterFactoryRegistry {
        -{static} array $factories
        --
        +{static} register(string, CharacterFactoryInterface) : void
        +{static} getFactory(string) : ?CharacterFactoryInterface
        +{static} createCharacter(string, string, Race, CharacterClass) : PlayerCharacter
    }
}

' ============================================================
'  CREATIONAL :: Abstract Factory
' ============================================================
package "Creational :: Factory (Abstract Factory)" #FFF9C4 {

    interface EquipmentFactoryInterface {
        +createHelmet(int $level) : Item
        +createChest(int $level) : Item
        +createLegs(int $level) : Item
        +createBoots(int $level) : Item
        +createGloves(int $level) : Item
        +createMainHand(int $level) : Item
        +createOffHand(int $level) : Item
    }

    abstract class EquipmentFactory {
        -string $faction
        -array $prefixes
        -array $materials
        --
        +__construct(string $faction)
        -createArmor(string, ItemSlot, int, ArmorType, Attributes) : Item
        -createWeapon(string, ItemSlot, int, WeaponType, Attributes) : Item
        -determineQuality(int) : ItemQuality
        -calculateBuyPrice(int, ItemQuality) : GoldAmount
        -calculateSellPrice(int, ItemQuality) : GoldAmount
    }

    class HordeEquipmentFactory {
        --
        +createHelmet(int) : Item
        +createChest(int) : Item
        +createLegs(int) : Item
        +createBoots(int) : Item
        +createGloves(int) : Item
        +createMainHand(int) : Item
        +createOffHand(int) : Item
    }

    class AllianceEquipmentFactory {
        --
        +createHelmet(int) : Item
        +createChest(int) : Item
        +createLegs(int) : Item
        +createBoots(int) : Item
        +createGloves(int) : Item
        +createMainHand(int) : Item
        +createOffHand(int) : Item
    }

    class EquipmentFactoryRegistry {
        -{static} array $factories
        --
        +{static} register(string, EquipmentFactoryInterface) : void
        +{static} getFactory(string) : ?EquipmentFactoryInterface
        +{static} createFullSet(string, int) : array
    }
}

' ============================================================
'  CREATIONAL :: Builder
' ============================================================
package "Creational :: Builder" #FFF9C4 {

    class CharacterBuilder {
        -string $name
        -?Race $race
        -?CharacterClass $class
        -int $level
        -?GoldAmount $gold
        -array $skills
        -array $equipment
        -array $talents
        -Attributes $customAttributes
        --
        +__construct()
        +setName(string) : self
        +setRace(Race) : self
        +setClass(CharacterClass) : self
        +setLevel(int) : self
        +setGold(GoldAmount) : self
        +addSkill(Skill) : self
        +addSkills(array) : self
        +addTalent(string, int) : self
        +addCustomAttributes(Attributes) : self
        +equipItem(Item) : self
        +equipFullSet(string, int) : self
        +build() : PlayerCharacter
        -validate() : void
        -generateId() : string
    }

    class CharacterDirector {
        --
        +createStarterCharacter(CharacterBuilder, string, Race, CharacterClass) : PlayerCharacter
        +createVeteranCharacter(CharacterBuilder, string, Race, CharacterClass, int) : PlayerCharacter
        +createTwinkCharacter(CharacterBuilder, string, Race, CharacterClass, int) : PlayerCharacter
        -getStarterSkills(CharacterClass) : array
        -getAllSkillsForLevel(CharacterClass, int) : array
    }
}

' ============================================================
'  CREATIONAL :: Prototype
' ============================================================
package "Creational :: Prototype" #FFF9C4 {

    interface PrototypeInterface {
        +clone() : self
    }

    abstract class MonsterPrototype {
        -string $id
        -string $name
        -int $level
        -Race $race
        -HealthPoints $health
        -ManaPoints $mana
        -Attributes $attributes
        -int $experienceReward
        -GoldAmount $goldDrop
        -array $lootTable
        -DamageType $damageType
        -int $baseDamage
        -float $attackSpeed
        -array $abilities
        --
        +__construct(string, string, int, Race, HealthPoints, ManaPoints, Attributes, int, GoldAmount, DamageType, int, float)
        +clone() : self
        -generateUniqueId() : string
        +getId() : string
        +getName() : string
        +getLevel() : int
        +setLevel(int) : void
        +setName(string) : void
        +setBaseDamage(int) : void
        +setHealth(HealthPoints) : void
        +getRace() : Race
        +getHealth() : HealthPoints
        +getMana() : ManaPoints
        +getAttributes() : Attributes
        +getExperienceReward() : int
        +getGoldDrop() : GoldAmount
        +getLootTable() : array
        +getDamageType() : DamageType
        +getBaseDamage() : int
        +getAttackSpeed() : float
        +getAbilities() : array
        +addLoot(string, float, int, int) : void
        +addAbility(string, array) : void
        +takeDamage(int) : void
        +isDead() : bool
        +__toString() : string
    }

    class BeastPrototype
    class HumanoidPrototype
    class UndeadPrototype
    class DragonkinPrototype

    class MonsterPrototypeManager {
        -array $prototypes
        --
        +__construct()
        +registerPrototype(MonsterPrototype) : void
        +spawnMonster(string, array) : MonsterPrototype
        +spawnHorde(string, int, array) : array
        +getPrototype(string) : ?MonsterPrototype
        +getAvailablePrototypes() : array
    }
}

' ============================================================
'  STRUCTURAL :: Adapter
' ============================================================
package "Structural :: Adapter" #E1F5FE {

    interface DamageCalculatorInterface {
        +calculateDamage(PlayerCharacter, PlayerCharacter, DamageType) : int
        +calculateHealing(PlayerCharacter, PlayerCharacter) : int
    }

    class LegacyDamageCalculator <<legacy>> {
        +computePhysicalDamage(array, array) : int
        +computeSpellDamage(array, array, string) : int
        +computeHealing(array) : int
    }

    class LegacyDamageCalculatorAdapter <<Adapter>> {
        -LegacyDamageCalculator $legacyCalculator
        --
        +__construct(?LegacyDamageCalculator)
        +calculateDamage(PlayerCharacter, PlayerCharacter, DamageType) : int
        +calculateHealing(PlayerCharacter, PlayerCharacter) : int
        -extractStats(PlayerCharacter) : array
    }

    class ModernDamageCalculator {
        --
        +calculateDamage(PlayerCharacter, PlayerCharacter, DamageType) : int
        +calculateHealing(PlayerCharacter, PlayerCharacter) : int
        -calculateMitigation(int, int, int) : float
        -calculateCritMultiplier(PlayerCharacter) : float
    }
}

' ============================================================
'  STRUCTURAL :: Composite
' ============================================================
package "Structural :: Composite" #E1F5FE {

    interface QuestComponentInterface {
        +getId() : string
        +getTitle() : string
        +getDescription() : string
        +canStart(PlayerCharacter) : bool
        +isComplete(PlayerCharacter) : bool
        +getProgress(PlayerCharacter) : string
        +getRewardGold() : GoldAmount
        +getExperienceReward() : int
        +getRewardItem() : ?Item
        +markCompleted() : void
        +isCompleted() : bool
    }

    abstract class QuestComponent {
        -string $id
        -string $title
        -string $description
        -GoldAmount $rewardGold
        -int $experienceReward
        -?Item $rewardItem
        -bool $isCompleted
        --
        +__construct(string, string, string, GoldAmount, int, ?Item)
        +getId() : string
        +getTitle() : string
        +getDescription() : string
        +getRewardGold() : GoldAmount
        +getExperienceReward() : int
        +getRewardItem() : ?Item
        +markCompleted() : void
        +isCompleted() : bool
    }

    class QuestObjective <<Leaf>> {
        -int $requiredLevel
        -Closure $completionCheck
        -Closure $progressCheck
        --
        +canStart(PlayerCharacter) : bool
        +isComplete(PlayerCharacter) : bool
        +getProgress(PlayerCharacter) : string
    }

    class QuestComposite <<Composite>> {
        -array $children
        --
        +add(QuestComponentInterface) : void
        +remove(QuestComponentInterface) : void
        +getChildren() : array
        +canStart(PlayerCharacter) : bool
        +isComplete(PlayerCharacter) : bool
        +getProgress(PlayerCharacter) : string
    }

    class QuestLog {
        -array $quests
        --
        +addQuest(QuestComponentInterface) : void
        +removeQuest(string) : void
        +getQuest(string) : ?QuestComponentInterface
        +getActiveQuests() : array
        +getCompletedQuests() : array
        +getAvailableQuests(PlayerCharacter) : array
        +getQuestProgress(PlayerCharacter) : string
        +completeQuest(string, PlayerCharacter) : bool
    }
}

' ============================================================
'  STRUCTURAL :: Decorator
' ============================================================
package "Structural :: Decorator" #E1F5FE {

    interface WeaponInterface {
        +getName() : string
        +getBaseDamage() : int
        +getDamageType() : DamageType
        +getAttributes() : Attributes
        +getAttackSpeed() : float
        +calculateDPS(PlayerCharacter) : float
    }

    class BaseWeapon implements WeaponInterface {
        -Item $item
        --
        +__construct(Item $item)
        +getName() : string
        +getBaseDamage() : int
        +getDamageType() : DamageType
        +getAttributes() : Attributes
        +getAttackSpeed() : float
        +calculateDPS(PlayerCharacter) : float
    }

    abstract class WeaponDecorator implements WeaponInterface {
        -WeaponInterface $weapon
        --
        +__construct(WeaponInterface $weapon)
        +getName() : string
        +getBaseDamage() : int
        +getDamageType() : DamageType
        +getAttributes() : Attributes
        +getAttackSpeed() : float
        +calculateDPS(PlayerCharacter) : float
    }

    class FireEnchantment {
        -int $fireDamage
        -float $procChance
        --
        +__construct(WeaponInterface, int = 20, float = 0.15)
        +getName() : string
        +getDamageType() : DamageType
        +getAttributes() : Attributes
        +calculateDPS(PlayerCharacter) : float
        +getFireDamage() : int
        +getProcChance() : float
    }

    class FrostEnchantment {
        -int $frostDamage
        -float $slowChance
        -float $slowPercent
        --
        +__construct(WeaponInterface, int = 15, float = 0.2, float = 0.5)
        +getFrostDamage() : int
        +getSlowChance() : float
    }

    class ArcaneEnchantment {
        -int $arcaneDamage
        -float $manaBurnChance
        -int $manaBurnAmount
        --
        +__construct(WeaponInterface, int = 25, float = 0.1, int = 30)
    }

    class LifestealEnchantment {
        -float $lifestealPercent
        --
        +__construct(WeaponInterface, float = 0.1)
        +getHealAmount(int $damageDealt) : int
    }

    class CrusaderEnchantment {
        -int $strengthBonus
        -float $procChance
        -int $duration
        --
        +__construct(WeaponInterface, int = 100, float = 0.05, int = 15)
    }

    class EnchantmentFactory {
        --
        +{static} applyEnchantment(WeaponInterface, string, array) : WeaponInterface
        +{static} applyMultiple(WeaponInterface, array) : WeaponInterface
    }
}

' ============================================================
'  STRUCTURAL :: Facade
' ============================================================
package "Structural :: Facade" #E1F5FE {

    class GameCombatFacade <<Facade>> {
        -DamageCalculatorInterface $damageCalculator
        -CombatNotifier $notifier
        -DamageCalculationContext $damageContext
        -array $combatLog
        -int $round
        --
        +__construct(?DamageCalculatorInterface)
        +startCombat(PlayerCharacter, PlayerCharacter) : CombatResult
        +executeSkill(PlayerCharacter, PlayerCharacter, Skill) : SkillResult
        +autoAttack(PlayerCharacter, PlayerCharacter) : SkillResult
        +heal(PlayerCharacter, PlayerCharacter, int) : HealResult
        +getCombatLog() : array
        +addCombatObserver(callable) : void
        -validateCombatants(PlayerCharacter, PlayerCharacter) : void
        -processRound(PlayerCharacter, PlayerCharacter, CharacterStateContext, CharacterStateContext) : void
        -isCombatOver(PlayerCharacter, PlayerCharacter) : bool
        -determineResult(PlayerCharacter, PlayerCharacter) : CombatResult
        -log(string) : void
    }

    class CombatResult {
        +{static} ATTACKER_WINS
        +{static} DEFENDER_WINS
        +{static} DRAW
        +string $outcome
        +PlayerCharacter $attacker
        +PlayerCharacter $defender
        +int $rounds
        +array $log
        --
        +getWinner() : ?PlayerCharacter
        +__toString() : string
    }

    class SkillResult {
        +bool $success
        +int $damage
        +string $message
    }

    class HealResult {
        +int $amount
        +string $message
    }
}

' ============================================================
'  STRUCTURAL :: Proxy
' ============================================================
package "Structural :: Proxy" #E1F5FE {

    interface GameServiceInterface {
        +enterZone(PlayerCharacter, string) : bool
        +purchaseItem(PlayerCharacter, Item) : bool
        +accessBank(PlayerCharacter) : array
        +joinBattleground(PlayerCharacter) : bool
        +useAuctionHouse(PlayerCharacter) : bool
        +sendMail(PlayerCharacter, PlayerCharacter, string, string, ?Item, GoldAmount) : bool
    }

    class GameService <<RealSubject>> {
        +enterZone(PlayerCharacter, string) : bool
        +purchaseItem(PlayerCharacter, Item) : bool
        +accessBank(PlayerCharacter) : array
        +joinBattleground(PlayerCharacter) : bool
        +useAuctionHouse(PlayerCharacter) : bool
        +sendMail(PlayerCharacter, PlayerCharacter, string, string, ?Item, GoldAmount) : bool
    }

    enum AccountTier {
        Free
        Premium
        Veteran
        GM
    }

    class AccountSecurityProxy <<Proxy>> {
        -GameServiceInterface $realService
        -array $premiumZones
        -array $veteranZones
        --
        +__construct(?GameServiceInterface)
        +enterZone(PlayerCharacter, string) : bool
        +purchaseItem(PlayerCharacter, Item) : bool
        +accessBank(PlayerCharacter) : array
        +joinBattleground(PlayerCharacter) : bool
        +useAuctionHouse(PlayerCharacter) : bool
        +sendMail(PlayerCharacter, PlayerCharacter, string, string, ?Item, GoldAmount) : bool
        -getAccountTier(PlayerCharacter) : AccountTier
        -hasAccess(AccountTier, AccountTier) : bool
    }

    class LoggingProxy <<Proxy>> {
        -GameServiceInterface $realService
        --
        +__construct(GameServiceInterface)
        -log(string, array) : void
    }
}

' ============================================================
'  BEHAVIORAL :: Strategy
' ============================================================
package "Behavioral :: Strategy" #FCE4EC {

    interface DamageStrategyInterface <<Strategy>> {
        +calculate(PlayerCharacter, PlayerCharacter, int, DamageType) : int
        +getName() : string
    }

    class PhysicalDamageStrategy {
        -calculateArmorMitigation(int, int, int) : float
        -calculateCrit(PlayerCharacter) : float
        --
        +calculate(PlayerCharacter, PlayerCharacter, int, DamageType) : int
        +getName() : string
    }

    class SpellDamageStrategy {
        -calculateResistanceMitigation(int, int, int) : float
        -calculateSpellCrit(PlayerCharacter) : float
        --
        +calculate(PlayerCharacter, PlayerCharacter, int, DamageType) : int
        +getName() : string
    }

    class DotDamageStrategy {
        -calculateResistanceMitigation(int, int, int) : float
        --
        +calculate(PlayerCharacter, PlayerCharacter, int, DamageType) : int
        +getName() : string
    }

    class DamageCalculationContext <<Context>> {
        -DamageStrategyInterface $strategy
        --
        +__construct(?DamageStrategyInterface)
        +setStrategy(DamageStrategyInterface) : void
        +calculateDamage(PlayerCharacter, PlayerCharacter, DamageType, int) : int
        +getCurrentStrategyName() : string
        -selectStrategy(DamageType) : DamageStrategyInterface
    }

    interface StorageStrategyInterface <<Strategy>> {
        +save(string, mixed) : bool
        +load(string) : mixed
        +delete(string) : bool
        +exists(string) : bool
        +getName() : string
    }

    class MemoryStorageStrategy {
        -array $storage
        --
        +save(string, mixed) : bool
        +load(string) : mixed
        +delete(string) : bool
        +exists(string) : bool
        +getName() : string
    }

    class FileStorageStrategy {
        -string $directory
        --
        +__construct(string $directory = 'data')
        +save(string, mixed) : bool
        +load(string) : mixed
        +delete(string) : bool
        +exists(string) : bool
        +getName() : string
        -sanitizeKey(string) : string
    }

    class DatabaseStorageStrategy {
        -PDO $pdo
        --
        +__construct(PDO)
        -createTable() : void
        +save(string, mixed) : bool
        +load(string) : mixed
        +delete(string) : bool
        +exists(string) : bool
        +getName() : string
    }

    class StorageContext <<Context>> {
        -StorageStrategyInterface $strategy
        --
        +__construct(?StorageStrategyInterface)
        +setStrategy(StorageStrategyInterface) : void
        +save(string, mixed) : bool
        +load(string) : mixed
        +delete(string) : bool
        +exists(string) : bool
        +getStrategyName() : string
    }
}

' ============================================================
'  BEHAVIORAL :: Observer
' ============================================================
package "Behavioral :: Observer" #FCE4EC {

    interface ObserverInterface {
        +update(string $event, array $data) : void
    }

    interface ObservableInterface {
        +addObserver(ObserverInterface|callable) : void
        +removeObserver(ObserverInterface|callable) : void
        +notify(string, array) : void
    }

    class CombatNotifier implements ObservableInterface <<Subject>> {
        -array $observers
        --
        +addObserver(ObserverInterface|callable) : void
        +removeObserver(ObserverInterface|callable) : void
        +notify(string, array) : void
        +notifyCombatStart(PlayerCharacter, PlayerCharacter) : void
        +notifyCombatEnd(PlayerCharacter, PlayerCharacter, CombatResult) : void
        +notifyDamageDealt(PlayerCharacter, PlayerCharacter, int, DamageType, string) : void
        +notifyHealing(PlayerCharacter, PlayerCharacter, int) : void
        +notifyLevelUp(PlayerCharacter, int) : void
        +notifySkillCast(PlayerCharacter, Skill, PlayerCharacter) : void
        +notifyItemLooted(PlayerCharacter, string, int) : void
        +notifyQuestCompleted(PlayerCharacter, string) : void
    }

    class ConsoleLogger implements ObserverInterface {
        +update(string, array) : void
    }

    class FileLogger implements ObserverInterface {
        -string $logFile
        --
        +__construct(string $logFile = 'combat.log')
        +update(string, array) : void
    }

    class UIUpdater implements ObserverInterface {
        -array $uiState
        --
        +update(string, array) : void
        +getUIState(string) : array
        +getAllUIState() : array
    }

    class CharacterObserver implements ObserverInterface {
        -PlayerCharacter $character
        --
        +__construct(PlayerCharacter)
        +update(string, array) : void
    }
}

' ============================================================
'  BEHAVIORAL :: Command
' ============================================================
package "Behavioral :: Command" #FCE4EC {

    interface CommandInterface <<Command>> {
        +execute() : CommandResult
        +undo() : CommandResult
        +getName() : string
        +canExecute() : bool
    }

    class CommandResult {
        +bool $success
        +string $message
        +mixed $data
        --
        +__construct(bool, string, mixed = null)
    }

    abstract class BaseCommand {
        -PlayerCharacter $caster
        -PlayerCharacter $target
        -Skill $skill
        -DamageCalculationContext $damageContext
        -?CommandResult $lastResult
        --
        +__construct(PlayerCharacter, PlayerCharacter, Skill, DamageCalculationContext)
        +getName() : string
        +canExecute() : bool
        +{abstract} execute() : CommandResult
        +{abstract} undo() : CommandResult
    }

    class CastSpellCommand {
        -int $damageDealt
        -int $manaSpent
        -int $targetPreviousHealth
        -int $casterPreviousMana
        --
        +execute() : CommandResult
        +undo() : CommandResult
    }

    class HealCommand {
        -int $healAmount
        -int $targetPreviousHealth
        -int $casterPreviousMana
        --
        +execute() : CommandResult
        +undo() : CommandResult
    }

    class BuffCommand {
        -string $buffName
        -int $duration
        -array $statModifiers
        --
        +__construct(PlayerCharacter, PlayerCharacter, Skill, DamageCalculationContext, string, int, array)
        +execute() : CommandResult
        +undo() : CommandResult
    }

    class CommandQueue <<Invoker>> {
        -array $queue
        -array $history
        -int $maxHistorySize
        --
        +enqueue(CommandInterface) : void
        +executeNext() : ?CommandResult
        +executeAll() : array
        +undoLast() : ?CommandResult
        +getQueueSize() : int
        +getHistorySize() : int
        +clear() : void
    }

    class MacroCommand {
        -array $commands
        -string $name
        --
        +__construct(string $name)
        +addCommand(CommandInterface) : void
        +execute() : CommandResult
        +undo() : CommandResult
        +getName() : string
        +canExecute() : bool
    }
}

' ============================================================
'  BEHAVIORAL :: Template Method
' ============================================================
package "Behavioral :: TemplateMethod" #FCE4EC {

    abstract class QuestCompletionTemplate <<TemplateMethod>> {
        --
        +{final} completeQuest(PlayerCharacter) : QuestCompletionResult
        -onPreCompletion(PlayerCharacter) : void
        -{abstract} validateCompletion(PlayerCharacter) : bool
        -{abstract} calculateRewards(PlayerCharacter) : QuestRewards
        -grantRewards(PlayerCharacter, QuestRewards) : void
        -onPostCompletion(PlayerCharacter, QuestRewards) : void
        -{abstract} canStart(PlayerCharacter) : bool
    }

    class QuestRewards {
        +int $experience
        +GoldAmount $gold
        +array $items
        +array $reputation
        --
        +__construct(int = 0, ?GoldAmount, array = [], array = [])
        +add(QuestRewards) : self
    }

    class QuestCompletionResult {
        +bool $success
        +string $message
        +?QuestRewards $rewards
        --
        +__construct(bool, string, ?QuestRewards = null)
    }

    class SimpleQuestCompletion {
        -string $questId
        -string $questTitle
        -int $requiredLevel
        -int $experienceReward
        -GoldAmount $goldReward
        -?Item $itemReward
        -Closure $objectiveCheck
        --
        +__construct(string, string, int, int, GoldAmount, Closure, ?Item)
        -validateCompletion(PlayerCharacter) : bool
        -calculateRewards(PlayerCharacter) : QuestRewards
        -canStart(PlayerCharacter) : bool
        -onPostCompletion(PlayerCharacter, QuestRewards) : void
    }

    class ChainQuestCompletion {
        -array $chain
        -int $currentStep
        --
        +addStep(QuestCompletionTemplate) : void
        -validateCompletion(PlayerCharacter) : bool
        -calculateRewards(PlayerCharacter) : QuestRewards
        -canStart(PlayerCharacter) : bool
        -onPostCompletion(PlayerCharacter, QuestRewards) : void
    }

    class DailyQuestCompletion {
        -string $questId
        -string $title
        -int $requiredLevel
        -int $baseExperience
        -GoldAmount $baseGold
        -Closure $objectiveCheck
        -array $completedToday
        --
        +__construct(string, string, int, int, GoldAmount, Closure)
        -validateCompletion(PlayerCharacter) : bool
        -calculateRewards(PlayerCharacter) : QuestRewards
        -canStart(PlayerCharacter) : bool
        -onPostCompletion(PlayerCharacter, QuestRewards) : void
    }
}

' ============================================================
'  BEHAVIORAL :: State
' ============================================================
package "Behavioral :: State" #FCE4EC {

    interface StateInterface <<State>> {
        +enter(PlayerCharacter) : void
        +exit(PlayerCharacter) : void
        +update(PlayerCharacter) : void
        +onDamageTaken(PlayerCharacter, int) : void
        +onHeal(PlayerCharacter, int) : void
        +canAttack() : bool
        +canMove() : bool
        +canCast() : bool
        +getState() : CharacterState
    }

    abstract class BaseState {
        -CharacterState $state
        --
        +__construct(CharacterState $state)
        +enter(PlayerCharacter) : void
        +exit(PlayerCharacter) : void
        +update(PlayerCharacter) : void
        +onDamageTaken(PlayerCharacter, int) : void
        +onHeal(PlayerCharacter, int) : void
        +canAttack() : bool
        +canMove() : bool
        +canCast() : bool
        +getState() : CharacterState
    }

    class NormalState {
        --
        +canAttack() : bool
        +canMove() : bool
        +canCast() : bool
        +onDamageTaken(PlayerCharacter, int) : void
    }

    class StunnedState {
        -int $remainingDuration
        --
        +__construct(int $duration = 3)
        +enter(PlayerCharacter) : void
        +update(PlayerCharacter) : void
    }

    class DeadState {
        -int $releaseTimer
        --
        +enter(PlayerCharacter) : void
        +update(PlayerCharacter) : void
    }

    class PoisonedState {
        -int $tickDamage
        -int $remainingDuration
        -int $tickInterval
        --
        +__construct(int $duration = 15, int $tickDamage = 10)
        +update(PlayerCharacter) : void
        +onHeal(PlayerCharacter, int) : void
    }

    class FrozenState {
        -int $remainingDuration
        --
        +__construct(int $duration = 5)
        +update(PlayerCharacter) : void
        +onDamageTaken(PlayerCharacter, int) : void
    }

    class CharacterStateContext <<Context>> {
        -PlayerCharacter $character
        -StateInterface $currentState
        --
        +__construct(PlayerCharacter)
        +transitionTo(CharacterState, array) : void
        +tick() : void
        +onDamageTaken(int) : void
        +onHeal(int) : void
        +canAttack() : bool
        +canMove() : bool
        +canCast() : bool
        +getCurrentState() : CharacterState
        +__toString() : string
        -createState(CharacterState, array) : ?StateInterface
    }
}

' ============================================================
'  BOUNDED CONTEXT :: COMBAT
' ============================================================
package "Context :: Combat" #D1C4E9 {

    class CombatService {
        -GameCombatFacade $combatFacade
        -DamageCalculationContext $damageContext
        --
        +__construct()
        +initiateCombat(PlayerCharacter, PlayerCharacter) : CombatResult
        +castSpell(PlayerCharacter, PlayerCharacter, Skill) : SkillResult
        +autoAttack(PlayerCharacter, PlayerCharacter) : SkillResult
        +heal(PlayerCharacter, PlayerCharacter, int) : HealResult
        +calculateDamage(PlayerCharacter, PlayerCharacter, DamageType, int) : int
        +setDamageStrategy(string) : void
    }

    interface CombatRepositoryInterface {
        +saveCombatLog(string, array) : void
        +getCombatLog(string) : ?array
    }

    class InMemoryCombatRepository {
        -array $logs
        --
        +saveCombatLog(string, array) : void
        +getCombatLog(string) : ?array
    }
}

' ============================================================
'  BOUNDED CONTEXT :: INVENTORY
' ============================================================
package "Context :: Inventory" #D1C4E9 {

    class InventoryAggregate <<Aggregate Root>> {
        -string $characterId
        -array $equippedItems
        -array $bagItems
        -int $bagCapacity
        -int $version
        --
        +__construct(string $characterId)
        +getCharacterId() : string
        +getEquippedItems() : array
        +getBagItems() : array
        +getBagCapacity() : int
        +getBagUsedSlots() : int
        +getBagFreeSlots() : int
        +getVersion() : int
        +equipItem(Item) : bool
        +unequipItem(ItemSlot) : ?Item
        +addToBag(Item) : bool
        +removeFromBag(ItemId) : ?Item
        +moveToBag(ItemSlot) : bool
        +expandBag(int) : void
        +getTotalWeight() : int
    }

    interface InventoryRepositoryInterface {
        +save(InventoryAggregate) : void
        +findByCharacterId(string) : ?InventoryAggregate
        +delete(string) : void
    }

    class InMemoryInventoryRepository {
        -array $inventories
        --
        +save(InventoryAggregate) : void
        +findByCharacterId(string) : ?InventoryAggregate
        +delete(string) : void
    }

    class InventoryService {
        -InventoryRepositoryInterface $repository
        --
        +__construct(InventoryRepositoryInterface)
        +getInventory(string) : InventoryAggregate
        +equipItem(string, Item) : bool
        +unequipItem(string, ItemSlot) : ?Item
        +addItemToBag(string, Item) : bool
        +removeItemFromBag(string, ItemId) : ?Item
        +expandBag(string, int) : void
    }
}

' ============================================================
'  BOUNDED CONTEXT :: BILLING
' ============================================================
package "Context :: Billing" #D1C4E9 {

    interface PaymentMethodInterface {
        +charge(GoldAmount) : PaymentResult
        +refund(GoldAmount) : PaymentResult
        +getName() : string
    }

    class PaymentResult {
        +bool $success
        +string $transactionId
        +string $message
        --
        +__construct(bool, string, string)
    }

    class InGameGoldPayment implements PaymentMethodInterface {
        -PlayerCharacter $character
        --
        +__construct(PlayerCharacter)
        +charge(GoldAmount) : PaymentResult
        +refund(GoldAmount) : PaymentResult
        +getName() : string
    }

    class RealMoneyPayment implements PaymentMethodInterface {
        -string $accountId
        -float $balance
        --
        +__construct(string, float = 0.0)
        +charge(GoldAmount) : PaymentResult
        +refund(GoldAmount) : PaymentResult
        +getName() : string
        +getBalance() : float
        +addFunds(float) : void
    }

    class Subscription <<Entity>> {
        -string $accountId
        -string $tier
        -DateTimeImmutable $startDate
        -DateTimeImmutable $endDate
        -bool $autoRenew
        -string $paymentMethodId
        --
        +__construct(string, string, DateTimeImmutable, DateTimeImmutable, bool, string)
        +getAccountId() : string
        +getTier() : string
        +getStartDate() : DateTimeImmutable
        +getEndDate() : DateTimeImmutable
        +isAutoRenew() : bool
        +isActive() : bool
        +getDaysRemaining() : int
        +renew(int $months = 1) : void
        +cancel() : void
        +getPaymentMethodId() : string
    }

    interface SubscriptionRepositoryInterface {
        +findByAccountId(string) : ?Subscription
        +save(Subscription) : void
    }

    class InMemorySubscriptionRepository {
        -array $subscriptions
        --
        +findByAccountId(string) : ?Subscription
        +save(Subscription) : void
    }

    class BillingService {
        -SubscriptionRepositoryInterface $subscriptionRepository
        --
        +__construct(SubscriptionRepositoryInterface)
        +purchaseSubscription(string, string, int, PaymentMethodInterface) : PaymentResult
        +cancelSubscription(string) : bool
        +getSubscriptionStatus(string) : array
        +processRenewals(PaymentMethodInterface) : array
    }

    class AuctionListing <<Entity>> {
        +string $id
        +string $sellerId
        +string $sellerName
        +Item $item
        +GoldAmount $startingBid
        +GoldAmount $currentBid
        +?GoldAmount $buyoutPrice
        +DateTimeImmutable $startTime
        +DateTimeImmutable $endTime
        +GoldAmount $deposit
        +?string $currentBidderId
        +?string $currentBidderName
    }

    class BidResult {
        +bool $success
        +string $transactionId
        +string $message
        --
        +__construct(bool, string, string)
    }

    class AuctionHouseService {
        -array $listings
        -float $commissionRate
        --
        +createListing(PlayerCharacter, Item, GoldAmount, GoldAmount, int) : AuctionListing
        +placeBid(string, PlayerCharacter, GoldAmount) : BidResult
        +buyout(string, PlayerCharacter) : BidResult
        +getActiveListings() : array
        +getListing(string) : ?AuctionListing
        -finalizeSale(AuctionListing, PlayerCharacter, GoldAmount) : void
    }
}

' ============================================================
'  RELACIONES
' ============================================================

' --- Herencia: Enums ---
CharacterClass ..> DamageType
Race ..> CharacterState

' --- Value Objects composed within Item ---
Item *-- ItemId
Item *-- ItemSlot
Item *-- ItemQuality
Item *-- ItemLevel
Item *-- Attributes
Item *-- GoldAmount

' --- PlayerCharacter composition ---
PlayerCharacter *-- HealthPoints
PlayerCharacter *-- ManaPoints
PlayerCharacter *-- GoldAmount
PlayerCharacter *-- Attributes : base + equipment
PlayerCharacter o-- "0..17" Item : equipment
PlayerCharacter --> CharacterClass
PlayerCharacter --> Race
PlayerCharacter --> CharacterState

' --- Skill ---
Skill --> CharacterClass
Skill --> DamageType

' --- Quest model ---
Quest <|-- SimpleQuest
Quest <|-- CompositeQuest
CompositeQuest o-- "*" Quest : subQuests

' --- Repositories (DIP) ---
PlayerRepositoryInterface <|.. InMemoryPlayerRepository
ItemRepositoryInterface <|.. InMemoryItemRepository
QuestRepositoryInterface <|.. InMemoryQuestRepository
InMemoryPlayerRepository ..> PlayerCharacter
InMemoryItemRepository ..> Item
InMemoryQuestRepository ..> Quest

' --- Singleton ---
GameWorld o-- "*" PlayerCharacter : onlineCharacters
CharacterFactory ..> GameWorld : registra jugador
CharacterBuilder ..> GameWorld : registra jugador

' --- Factory Method ---
CharacterFactoryInterface <|.. CharacterFactory
CharacterFactory <|-- HordeCharacterFactory
CharacterFactory <|-- AllianceCharacterFactory
CharacterFactory ..> PlayerCharacter : crea
HordeCharacterFactory ..> Race : valida
AllianceCharacterFactory ..> Race : valida
CharacterFactoryRegistry o-- "*" CharacterFactoryInterface

' --- Abstract Factory ---
EquipmentFactoryInterface <|.. EquipmentFactory
EquipmentFactory <|-- HordeEquipmentFactory
EquipmentFactory <|-- AllianceEquipmentFactory
EquipmentFactory ..> Item : crea
EquipmentFactory ..> Attributes
HordeEquipmentFactory ..> ArmorType : Plate / Axe
AllianceEquipmentFactory ..> WeaponType : Sword / Shield
EquipmentFactoryRegistry o-- "*" EquipmentFactoryInterface
CharacterBuilder ..> EquipmentFactoryRegistry : equipFullSet

' --- Builder ---
CharacterDirector ..> CharacterBuilder : construye
CharacterBuilder ..> PlayerCharacter : build()
CharacterBuilder o-- "*" Skill
CharacterBuilder o-- "*" Item

' --- Prototype ---
PrototypeInterface <|.. MonsterPrototype
MonsterPrototype <|-- BeastPrototype
MonsterPrototype <|-- HumanoidPrototype
MonsterPrototype <|-- UndeadPrototype
MonsterPrototype <|-- DragonkinPrototype
MonsterPrototypeManager o-- "*" MonsterPrototype : prototypes
MonsterPrototypeManager ..> MonsterPrototype : clone()
MonsterPrototype --> Race
MonsterPrototype --> DamageType
MonsterPrototype *-- HealthPoints
MonsterPrototype *-- ManaPoints
MonsterPrototype *-- GoldAmount

' --- Adapter (LSP: ambos son DamageCalculatorInterface) ---
DamageCalculatorInterface <|.. LegacyDamageCalculatorAdapter
DamageCalculatorInterface <|.. ModernDamageCalculator
LegacyDamageCalculatorAdapter o-- LegacyDamageCalculator : adapta
LegacyDamageCalculatorAdapter ..> PlayerCharacter : extrae stats

' --- Composite ---
QuestComponentInterface <|.. QuestComponent
QuestComponent <|-- QuestObjective
QuestComponent <|-- QuestComposite
QuestComposite o-- "*" QuestComponentInterface : children
QuestLog o-- "*" QuestComponentInterface
QuestObjective ..> PlayerCharacter

' --- Decorator ---
WeaponInterface <|.. BaseWeapon
WeaponInterface <|.. WeaponDecorator
BaseWeapon o-- Item
WeaponDecorator o-- WeaponInterface : componente envuelto
WeaponDecorator <|-- FireEnchantment
WeaponDecorator <|-- FrostEnchantment
WeaponDecorator <|-- ArcaneEnchantment
WeaponDecorator <|-- LifestealEnchantment
WeaponDecorator <|-- CrusaderEnchantment
EnchantmentFactory ..> WeaponInterface : crea decorators
FireEnchantment ..> Attributes
CrusaderEnchantment ..> Attributes

' --- Facade (oculta subsistemas) ---
GameCombatFacade *-- DamageCalculatorInterface : inyecta (DIP)
GameCombatFacade *-- CombatNotifier
GameCombatFacade *-- DamageCalculationContext
GameCombatFacade ..> CombatResult : devuelve
GameCombatFacade ..> SkillResult : devuelve
GameCombatFacade ..> HealResult : devuelve
GameCombatFacade ..> CharacterStateContext : orquestra
GameCombatFacade ..> Skill
GameCombatFacade ..> PlayerCharacter
CombatNotifier ..> CombatResult : notifica

' --- Proxy ---
GameServiceInterface <|.. GameService
GameServiceInterface <|.. AccountSecurityProxy
GameServiceInterface <|.. LoggingProxy
AccountSecurityProxy o-- GameServiceInterface : realService
AccountSecurityProxy --> AccountTier
LoggingProxy o-- GameServiceInterface : realService
AccountSecurityProxy ..> PlayerCharacter
AccountSecurityProxy ..> Item

' --- Strategy: Damage ---
DamageStrategyInterface <|.. PhysicalDamageStrategy
DamageStrategyInterface <|.. SpellDamageStrategy
DamageStrategyInterface <|.. DotDamageStrategy
DamageCalculationContext o-- DamageStrategyInterface : strategy
DamageCalculationContext ..> PhysicalDamageStrategy : selectStrategy()
DamageCalculationContext ..> SpellDamageStrategy : selectStrategy()
PhysicalDamageStrategy ..> PlayerCharacter
SpellDamageStrategy ..> PlayerCharacter
DotDamageStrategy ..> PlayerCharacter

' --- Strategy: Storage ---
StorageStrategyInterface <|.. MemoryStorageStrategy
StorageStrategyInterface <|.. FileStorageStrategy
StorageStrategyInterface <|.. DatabaseStorageStrategy
StorageContext o-- StorageStrategyInterface : strategy
FileStorageStrategy ..> mixed : serializa JSON
DatabaseStorageStrategy ..> PDO : persiste SQL

' --- Observer ---
ObserverInterface <|.. ConsoleLogger
ObserverInterface <|.. FileLogger
ObserverInterface <|.. UIUpdater
ObserverInterface <|.. CharacterObserver
ObservableInterface <|.. CombatNotifier
CombatNotifier o-- "*" ObserverInterface : observers
CombatNotifier ..> PlayerCharacter
CombatNotifier ..> Skill
CharacterObserver o-- PlayerCharacter
ConsoleLogger ..> UIUpdater

' --- Command ---
CommandInterface <|.. BaseCommand
CommandInterface <|.. MacroCommand
BaseCommand <|-- CastSpellCommand
BaseCommand <|-- HealCommand
BaseCommand <|-- BuffCommand
BaseCommand o-- PlayerCharacter : caster
BaseCommand o-- PlayerCharacter : target
BaseCommand o-- Skill
BaseCommand *-- DamageCalculationContext
CommandQueue o-- "*" CommandInterface : queue + history
MacroCommand o-- "*" CommandInterface : commands

' --- Template Method ---
QuestCompletionTemplate <|-- SimpleQuestCompletion
QuestCompletionTemplate <|-- ChainQuestCompletion
QuestCompletionTemplate <|-- DailyQuestCompletion
QuestCompletionTemplate ..> QuestCompletionResult : devuelve
QuestCompletionTemplate ..> QuestRewards
QuestCompletionTemplate ..> PlayerCharacter
QuestRewards *-- GoldAmount
SimpleQuestCompletion o-- QuestRewards
ChainQuestCompletion o-- "*" QuestCompletionTemplate : chain
DailyQuestCompletion ..> DateTimeImmutable

' --- State ---
StateInterface <|.. BaseState
BaseState <|-- NormalState
BaseState <|-- StunnedState
BaseState <|-- DeadState
BaseState <|-- PoisonedState
BaseState <|-- FrozenState
CharacterStateContext o-- StateInterface : currentState
CharacterStateContext o-- PlayerCharacter
CharacterStateContext ..> NormalState : createState()
CharacterStateContext ..> StunnedState : createState()
CharacterStateContext ..> DeadState : createState()
CharacterStateContext ..> PoisonedState : createState()
CharacterStateContext ..> FrozenState : createState()
BaseState --> CharacterState

' --- Bounded Context: Combat ---
CombatService *-- GameCombatFacade
CombatService *-- DamageCalculationContext
CombatRepositoryInterface <|.. InMemoryCombatRepository
CombatService ..> PlayerCharacter
CombatService ..> Skill
CombatService ..> DamageType
CombatService ..> CombatResult

' --- Bounded Context: Inventory ---
InventoryRepositoryInterface <|.. InMemoryInventoryRepository
InMemoryInventoryRepository o-- "*" InventoryAggregate
InventoryService *-- InventoryRepositoryInterface : inyecta (DIP)
InventoryService ..> InventoryAggregate : devuelve
InventoryAggregate o-- "*" Item : equippedItems
InventoryAggregate o-- "*" Item : bagItems
InventoryAggregate ..> ItemSlot
InventoryAggregate ..> ItemId

' --- Bounded Context: Billing ---
PaymentMethodInterface <|.. InGameGoldPayment
PaymentMethodInterface <|.. RealMoneyPayment
PaymentResult ..> PaymentMethodInterface : devuelto por
SubscriptionRepositoryInterface <|.. InMemorySubscriptionRepository
InMemorySubscriptionRepository o-- "*" Subscription
BillingService *-- SubscriptionRepositoryInterface : inyecta (DIP)
BillingService ..> PaymentMethodInterface
BillingService ..> Subscription
BillingService ..> PaymentResult
InGameGoldPayment o-- PlayerCharacter
RealMoneyPayment ..> GoldAmount
AuctionHouseService o-- "*" AuctionListing
AuctionHouseService ..> AuctionListing : crea
AuctionHouseService ..> BidResult : devuelve
AuctionListing o-- Item
AuctionListing o-- GoldAmount
AuctionHouseService o-- PlayerCharacter

@enduml
```

---

## 📖 Notas de Lectura del Diagrama

### Leyenda de Estereotipos

| Estereotipo | Significado |
|-------------|-------------|
| `<<Entity>>` | Entidad DDD con identidad persistente (ID único) |
| `<<Aggregate Root>>` | Raíz de agregado: único punto de entrada transaccional |
| `<<readonly>>` | Value Object inmutable (`readonly class` de PHP 8.2) |
| `<<Singleton>>` | Instancia única global |
| `<<Adapter>>` | Adapta una API legacy a la interfaz moderna |
| `<<Facade>>` | Punto de entrada simplificado a subsistemas |
| `<<Proxy>>` | Controla acceso al objeto real |
| `<<Strategy>>` / `<<Context>>` | Algoritmo intercambiable / su selector |
| `<<Subject>>` / `ObserverInterface` | Patrón Observer |
| `<<Command>>` / `<<Invoker>>` | Patrón Command |
| `<<TemplateMethod>>` | Esqueleto de algoritmo con hooks |
| `<<State>>` | Estado del personaje |
| `<<Leaf>>` / `<<Composite>>` | Nodo hoja / nodo compuesto |
| `<<RealSubject>>` | Objeto real envuelto por el Proxy |

### Tipos de Relación Utilizados

| Flecha | Relación | Ejemplo |
|--------|----------|---------|
| `A <\|-- B` | Herencia (B hereda de A) | `BaseState <\|-- PoisonedState` |
| `A *-- B` | Composición (ciclo de vida atómico) | `Item *-- ItemId` |
| `A o-- B` | Agregación (ciclo de vida independiente) | `CombatNotifier o-- "*" ObserverInterface` |
| `A ..> B` | Dependencia (uso, no composición) | `GameWorld ..> PlayerCharacter` |
| `A <\|.. B` | Realización (implementa interfaz) | `StorageContext <\|.. StorageStrategyInterface` |
| `A ..\|> B` | Flecha punteada de interfaz | (reservada) |

### Relaciones Clave del Diseño

1. **DIP (Dependency Inversion)**: `GameCombatFacade`, `InventoryService`, `BillingService` y `CombatService` dependen de **interfaces**, nunca de implementaciones concretas.
2. **LSP (Liskov Substitution)**: `LegacyDamageCalculatorAdapter` y `ModernDamageCalculator` son intercambiables donde se espera `DamageCalculatorInterface`.
3. **Alta Cohesión**: Cada *Bounded Context* (`Combat`, `Inventory`, `Billing`) tiene su modelo, repositorios y servicios sin dependencias cruzadas.
4. **Invariantes protegidas**: `InventoryAggregate` mantiene la consistencia entre equipamiento y bolsa; los Value Objects validan en su constructor.
