# 🎮 Guía de Elaboración: Engine de MMORPG en PHP 8.2+ (Unidad 1)

## 📌 1. Visión General del Proyecto

El **Azeroth Engine** es un motor de simulación de MMORPG estilo World of Warcraft implementado íntegramente en **PHP 8.2+** con tipado estricto (`declare(strict_types=1)`). Este proyecto sirve como demostración práctica e integral de todos los temas de la Unidad 1 de la asignatura "Diseño y Arquitectura de Software".

### Características Técnicas Principales

- **PHP 8.2+ con tipado fuerte**: Todo archivo inicia con `declare(strict_types=1);`
- **Características modernas**: `readonly properties`, `Enums`, `Match expressions`, `Constructor Property Promotion`, `Interfaces`, `Union/Intersection Types`
- **Cero uso de `mixed`**: Todos los tipos están explícitamente definidos mediante DTOs/Value Objects
- **Ejecutable vía CLI**: `php index.php` ejecuta la demostración completa

### Dominio de Aplicación

- **Entidades de Juego**: Personajes (Guerrero, Mago, Cazador), Atributos (Fuerza, Agilidad, Intelecto), Razas (Orcos, Humanos, Elfos Nocturnos, No-muertos)
- **Mecánicas**: Equipamiento de ítems, cálculo de daño por hechizos/ataques, estados de personaje (Normal, Aturdido, Muerto, Envenenado, Congelado), misiones y árbol de habilidades
- **Subdominios**: Combate, Inventario y Suscripciones/Pagos

---

## 📐 2. Mapeo de Contenidos del Sílabo (Semana a Semana)

### 🔹 Semana 1: Fundamentos y OOA/D

#### Conceptos Implementados

| Concepto | Implementación | Ubicación |
|----------|----------------|-----------|
| **Abstracción** | Clases base como `PlayerCharacter`, `Item`, `Quest` | `src/Domain/Model/` |
| **Encapsulamiento** | Propiedades `private`/`protected` con getters/setters | Todas las clases de dominio |
| **Herencia** | `Warrior`, `Mage`, `Hunter` heredan comportamiento base | `src/Domain/Model/Skill.php` |
| **Polimorfismo** | `DamageCalculatorInterface` con múltiples implementaciones | `src/Structural/Adapter/` |
| **Mapeo a Código** | Conceptos de dominio → Clases PHP concretas | `src/Domain/` |

#### Ejemplos Clave

- **PlayerCharacter** (`src/Domain/Model/PlayerCharacter.php`): Entidad principal con ID único, atributos mutables, equipamiento, oro, salud/maná
- **Value Objects** (`src/Domain/ValueObject/`): `HealthPoints`, `GoldAmount`, `ManaPoints`, `Attributes` - inmutables, autoregulados con validaciones
- **Enums** (`src/Domain/Enum/`): `CharacterClass`, `Race`, `CharacterState`, `DamageType`, `ItemSlot`, `ItemQuality`

---

### 🔹 Semana 2: Principios de Diseño, SOLID y Métricas

#### Principios SOLID Aplicados

| Principio | Ejemplo Concreto | Ubicación |
|-----------|------------------|-----------|
| **SRP** | Cada clase tiene una sola responsabilidad: `PlayerCharacter` (estado), `InventoryAggregate` (inventario), `CombatService` (combate) | Todas |
| **OCP** | `DamageCalculatorInterface` permite nuevas estrategias sin modificar código existente | `src/Structural/Adapter/`, `src/Behavioral/Strategy/` |
| **LSP** | `ModernDamageCalculator` y `LegacyDamageCalculatorAdapter` son intercambiables | `src/Structural/Adapter/` |
| **ISP** | Interfaces específicas: `PaymentMethodInterface`, `StorageStrategyInterface`, `ObserverInterface` | `src/Context/Billing/`, `src/Behavioral/Strategy/`, `src/Behavioral/Observer/` |
| **DIP** | `GameCombatFacade` depende de `DamageCalculatorInterface`, no de implementaciones | `src/Structural/Facade/GameCombatFacade.php:27` |

#### Justificación de Bajo Acoplamiento y Alta Cohesión

- **Inyección de dependencias** en constructores a través de interfaces (ej. `GameCombatFacade`, `BillingService`, `InventoryService`)
- **Bounded Contexts** separados: `CombatContext`, `InventoryContext`, `BillingContext` no comparten código
- **Agregados** encapsulan invariantes: `InventoryAggregate` gestiona equipamiento y bolsa atómicamente

---

### 🔹 Semana 3: Patrones Creacionales

| Patrón | Implementación | Caso de Uso en WoW |
|--------|----------------|---------------------|
| **Singleton** | `GameWorld` (instancia única del mundo), `EngineConfig` (configuración global) | Un solo mundo de juego, configuración centralizada |
| **Factory Method** | `CharacterFactory` → `HordeCharacterFactory` / `AllianceCharacterFactory` | Creación de personajes según facción |
| **Abstract Factory** | `EquipmentFactory` → `HordeEquipmentFactory` / `AllianceEquipmentFactory` | Familias de equipo temáticas (Horda vs Alianza) |
| **Builder** | `CharacterBuilder` + `CharacterDirector` | Construcción paso a paso: starter, veteran, twink |
| **Prototype** | `MonsterPrototypeManager` + `BeastPrototype`, `UndeadPrototype`, etc. | Clonación eficiente de hordas de NPCs |

#### Ubicación de Archivos

- `src/Creational/Singleton/GameWorld.php`
- `src/Creational/Singleton/EngineConfig.php`
- `src/Creational/Factory/CharacterFactory.php`
- `src/Creational/Factory/EquipmentFactory.php`
- `src/Creational/Builder/CharacterBuilder.php`
- `src/Creational/Prototype/MonsterPrototypeManager.php`

---

### 🔹 Semana 4: Patrones Estructurales y de Comportamiento

#### Patrones Estructurales

| Patrón | Implementación | Caso de Uso en WoW |
|--------|----------------|---------------------|
| **Adapter** | `LegacyDamageCalculatorAdapter` adapta calculadora legacy a `DamageCalculatorInterface` | Migración gradual de motor de daño antiguo |
| **Composite** | `QuestComposite` / `QuestObjective` / `QuestLog` | Misiones principales con submisiones y objetivos |
| **Decorator** | `FireEnchantment`, `FrostEnchantment`, `CrusaderEnchantment` sobre `BaseWeapon` | Encantamientos de armas: fuego, hielo, cruzado |
| **Facade** | `GameCombatFacade` simplifica `CombatService`, `DamageCalculator`, `StateMachine` | Iniciar batalla con una sola llamada |
| **Proxy** | `AccountSecurityProxy` (control de acceso por tier), `LoggingProxy` (auditoría) | Zonas premium solo para cuentas Veteran/Premium |

#### Patrones de Comportamiento

| Patrón | Implementación | Caso de Uso en WoW |
|--------|----------------|---------------------|
| **Strategy** | `DamageCalculationContext` con `PhysicalDamageStrategy`, `SpellDamageStrategy`, `DotDamageStrategy`; `StorageContext` con `FileStorageStrategy`, `MemoryStorageStrategy` | Cambio de algoritmo de daño físico vs mágico en runtime; almacenamiento en archivo vs memoria |
| **Observer** | `CombatNotifier` + `ConsoleLogger`, `FileLogger`, `UIUpdater`, `CharacterObserver` | Notificar UI/Log al recibir daño, subir nivel, completar misión |
| **Command** | `CastSpellCommand`, `HealCommand`, `BuffCommand`, `MacroCommand`, `CommandQueue` | Encapsular hechizos para ejecutar, encolar, deshacer, macros |
| **Template Method** | `QuestCompletionTemplate` → `SimpleQuestCompletion`, `ChainQuestCompletion`, `DailyQuestCompletion` | Esqueleto fijo para completar misiones con hooks personalizables |
| **State** | `CharacterStateContext` + `NormalState`, `StunnedState`, `DeadState`, `PoisonedState`, `FrozenState` | Estados del personaje con comportamiento distinto por estado |

#### Ubicación de Archivos

- Estructurales: `src/Structural/Adapter/`, `src/Structural/Composite/`, `src/Structural/Decorator/`, `src/Structural/Facade/`, `src/Structural/Proxy/`
- Comportamiento: `src/Behavioral/Strategy/`, `src/Behavioral/Observer/`, `src/Behavioral/Command/`, `src/Behavioral/TemplateMethod/`, `src/Behavioral/State/`

---

### 🔹 Semana 5: DDD (Domain-Driven Design) y Gobernanza

#### Contextos Delimitados (Bounded Contexts)

```
src/Context/
├── Combat/          # Combate, daño, habilidades
├── Inventory/       # Inventario, equipamiento, bolsa
└── Billing/         # Suscripciones, pagos, casa de subastas
```

Cada contexto tiene su propio modelo, repositorios y servicios, sin dependencias cruzadas.

#### Patrones Tácticos DDD

| Patrón | Implementación | Descripción |
|--------|----------------|-------------|
| **Entidad** | `PlayerCharacter` | ID único mutable, ciclo de vida completo |
| **Value Objects** | `HealthPoints`, `GoldAmount`, `ManaPoints`, `Attributes`, `Item` | Inmutables (`readonly`), validaciones internas |
| **Agregado** | `InventoryAggregate` | Raíz de agregado, consistencia transaccional (equipar + bolsa) |
| **Repositorios** | `PlayerRepositoryInterface`, `InventoryRepositoryInterface`, `SubscriptionRepositoryInterface` | Abstracción de persistencia |

#### Ejemplos de Implementación

- **PlayerCharacter** (`src/Domain/Model/PlayerCharacter.php:18`): `readonly` no aplicable (estado mutable), pero ID inmutable
- **HealthPoints** (`src/Domain/ValueObject/HealthPoints.php:1`): `readonly class`, validaciones en constructor, métodos `heal()`, `damage()`, `withCurrent()`
- **InventoryAggregate** (`src/Context/Inventory/InventoryAggregate.php`): Raíz de agregado, métodos `equipItem()`, `unequipItem()`, `addToBag()` mantienen invariantes
- **InMemoryPlayerRepository** (`src/Domain/Repository/InMemoryPlayerRepository.php`): Implementación en memoria, fácilmente intercambiable por BD

#### ADR (Architecture Decision Record)

**ADR-001: Elección de Arquitectura por Bounded Contexts**

- **Fecha**: 2026-09-26
- **Estado**: Aceptado
- **Contexto**: El motor MMORPG requiere separar dominios complejos (Combate, Inventario, Facturación)
- **Decisión**: Implementar Bounded Contexts separados con comunicación solo vía Domain Events / Application Services
- **Consecuencias**: 
  - ✅ Bajo acoplamiento entre dominios
  - ✅ Equipos pueden trabajar en contextos independientes
  - ✅ Modelos optimizados por contexto (ej. `PlayerCharacter` distinto en Combat vs Billing)
  - ⚠️ Requiere sincronización eventual entre contextos

---

## 🚀 3. Instrucciones de Ejecución

### Requisitos

- PHP 8.2 o superior
- Extensiones estándar (JSON, DateTime)

### Ejecutar la Demostración Completa

```bash
cd D:\xampp\htdocs\ejemplo
php index.php
```

### Salida Esperada

La ejecución muestra la demostración secuencial de todos los patrones:

```
==================================================
      ⚔️  AZEROTH ENGINE - MMORPG SIMULATION  ⚔️
==================================================

📌 SINGLETON PATTERNS
----------------------
Game Version: 1.0.0
Max Level: 60
PvP Enabled: Yes
Experience Rate: 1x

📌 FACTORY METHOD - CHARACTER CREATION
---------------------------------------
Created: Grommash (Level 1 orc warrior) - HP: 320/320, Mana: 75/75, Gold: 50s
...

📌 STATE PATTERN - CHARACTER STATES
------------------------------------
[Grommash] Entered state: normal
Initial: Grommash is normal
Applying Stun...
[Grommash] Exited state: normal
[Grommash] Entered state: stunned
Can attack: No
Can move: No
...

📌 DDD - BOUNDED CONTEXTS
--------------------------
Combat Context initialized
Inventory Context: 1/16 slots used
Billing Context: Subscription premium - 29 days remaining
Auction House: 1 active listings

📌 REPOSITORY PATTERN
---------------------
Total players in repository: 3
Found by name: Grommash
Total items in repository: 14
Plate items: 2

==================================================
           SIMULATION COMPLETE ✓
==================================================
```

---

## 📁 Estructura de Archivos

```
ejemplo/
├── index.php                 # Script principal ejecutable
├── README.md                 # Esta guía
├── vendor/
│   └── autoload.php          # Autoloader PSR-4
└── src/
    ├── Domain/
    │   ├── Model/            # Entidades: PlayerCharacter, Skill, Quest
    │   ├── ValueObject/      # VOs: HealthPoints, GoldAmount, ManaPoints, Attributes, Item, ItemSlot, ItemQuality, ItemId, ItemLevel, ArmorType, WeaponType
    │   ├── Enum/             # Enums: CharacterClass, Race, CharacterState, DamageType, ItemSlot, ItemQuality
    │   └── Repository/       # Interfaces e implementaciones en memoria
    ├── Creational/
    │   ├── Singleton/        # GameWorld, EngineConfig
    │   ├── Factory/          # CharacterFactory, EquipmentFactory + Registries
    │   ├── Builder/          # CharacterBuilder, CharacterDirector
    │   └── Prototype/        # MonsterPrototypeManager, prototypes
    ├── Structural/
    │   ├── Adapter/          # LegacyDamageCalculatorAdapter, ModernDamageCalculator
    │   ├── Composite/        # QuestComposite, QuestObjective, QuestLog
    │   ├── Decorator/        # BaseWeapon, FireEnchantment, FrostEnchantment, etc.
    │   ├── Facade/           # GameCombatFacade
    │   └── Proxy/            # AccountSecurityProxy, LoggingProxy, GameService
    ├── Behavioral/
    │   ├── Strategy/         # DamageCalculationContext, StorageContext + strategies
    │   ├── Observer/         # CombatNotifier, ConsoleLogger, FileLogger, UIUpdater
    │   ├── Command/          # CastSpellCommand, HealCommand, MacroCommand, CommandQueue
    │   ├── TemplateMethod/   # QuestCompletionTemplate + implementations
    │   └── State/            # CharacterStateContext + state classes
    └── Context/
        ├── Combat/           # CombatService, CombatRepository
        ├── Inventory/        # InventoryService, InventoryAggregate, Repository
        └── Billing/          # BillingService, AuctionHouseService, PaymentMethods, Subscriptions
```

---

## ✨ Resumen de Cobertura

| Categoría | Patrones/Conceptos | Estado |
|-----------|-------------------|--------|
| **Semana 1** | Abstracción, Encapsulamiento, Herencia, Polimorfismo, Mapeo OOA/D | ✅ |
| **Semana 2** | SRP, OCP, LSP, ISP, DIP, DI, Cohesión/Acoplamiento | ✅ |
| **Semana 3** | Singleton, Factory Method, Abstract Factory, Builder, Prototype | ✅ |
| **Semana 4** | Adapter, Composite, Decorator, Facade, Proxy, Strategy, Observer, Command, Template Method, State | ✅ |
| **Semana 5** | Bounded Contexts, Entidades, Value Objects, Agregados, Repositorios, ADR | ✅ |
| **PHP 8.2+** | strict_types, readonly, enums, match, constructor promotion, union types | ✅ |

---

**Desarrollado como demostración académica para la Unidad 1: Diseño y Arquitectura de Software**  
**Tecnología**: PHP 8.2+ | **Arquitectura**: DDD + Patrones GoF + SOLID