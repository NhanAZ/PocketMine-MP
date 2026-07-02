# Fork Changelog

This file tracks NhanAZ fork-specific maintenance and implementation changes.
Upstream PocketMine-MP release notes remain in the numbered changelog files.

## 2026-07-02

Backfilled changes after `a170743de838581487bcf91bc7026fe786de752b` through `6c78bb0b0`.

### Fixes

- Implemented upstream issue #6284: spawnable tile network NBT now receives a `TypeConverter` context and caches serialized compounds per converter.
- Implemented upstream issue #6711: inventories reject plain `Item` instances whose type ID maps to a block item before they can crash later during serialization.
- Implemented upstream issue #6808: core entities now save with Bedrock entity IDs while retaining legacy Java/PM aliases for loading older worlds.
- Implemented upstream issue #5632: missing Bedrock blockstate properties now fall back to the registered default state for that block ID while wrong tag types still fail.
- Implemented upstream issue #6832: chunk loading skips invalid loaded tiles before block-state sync when saved positions are out of bounds, duplicated, or in unavailable chunks.
- Implemented upstream issue #6861: invalid firework rocket `Flight` values loaded from saved NBT now raise `SavedDataLoadingException` instead of leaking `InvalidArgumentException` past safe item loading.
- Implemented upstream issue #6130: dedicated Query socket bind failures now follow the localized network-start failure path instead of escaping as an unhandled exception and producing a crash dump.
- Implemented upstream issue #5342 by adapting canonical commit `c4fb8832fe99e042801dca60f124b7938c94036f`: formatted translation parameters can restore their surrounding base format, preventing custom item-name colours from leaking through `/give` sender and operator audit output.
- Implemented upstream issue #4830 after reviewing abandoned PRs #5095 and #6900: dropped items now use block landing behaviour and bounce on slime, while XP orbs and other non-living entities are excluded from slime bounce and landing sounds remain living-only.

### Protocol

- Verified the fork current for Minecraft: Bedrock Edition 1.26.30 / protocol 1001 using PMMP BedrockProtocol, Endstone protocol docs, and Cloudburst Protocol evidence.
- Added root PHPUnit fixtures for the 1.26.30 `BossEventPacket` fixed-field wire format.

### Dependencies

- Imported `pocketmine/callback-validator` 1.0.4 at canonical commit `143fa6e13254f1ab90c31b223982016f95635c37` as a local Composer path package with its MIT license, package metadata, tests, subtree history, and canonical source monitoring.
- Imported `pocketmine/binaryutils` 0.2.7 at canonical commit `14c044afa33cb581b4a6d1ea04a87e0bc99e824b` as a local Composer path package with its LGPL-3.0 license, package metadata, tests, subtree history, and canonical source monitoring.
- Previously imported `pocketmine/color`, `pocketmine/errorhandler`, `pocketmine/math`, and `pocketmine/log` remain local Composer path packages.

### Maintenance

- Added baseline verification, protocol update, source drift, upstream backlog intake, backlog prioritization, community intake, and sustainable maintenance workflows.
- Consolidated agent context into `AGENTS.md` and `ROADMAP.md`.
- Restored full PHPStan coverage for backlog tooling and fixed fork CI failures.
- Clarified fork-owned links and upstream ecosystem links.
- Disabled upstream-only release publishing workflows until fork-owned destinations and credentials are configured.

### Reviewed Or Deferred

- Reviewed upstream issue #6580 and deferred it because the LevelDB compaction fix needs a world-format or region-sharded DB design, migration path, rollback plan, and benchmarks.
- Reviewed upstream issue #6750 and deferred it because the public report is unconfirmed and the useful crash dumps are private to upstream maintainers.
- Reviewed RakLib anti-spoofing cookies and deferred them until a tagged release or the planned local RakLib import.
- Reviewed PowerNukkitX RakNet pacing and cookie configuration drift as reference-only evidence for the planned RakLib import.

### Checks

- Targeted tests were run for the implemented upstream fixes.
- Full repository PHPUnit, PHPStan, PHP-CS-Fixer 3.75 dry-run, JSON validation, generated-file collision checks, and `git diff --check` were clean after the #6832 invalid loaded-tile work.
- For #6861, targeted `ItemTest` regression coverage, touched-file PHPStan, and `git diff --check` were clean; local PHP-CS-Fixer was not available outside CI.
- For #6130, the targeted bind-failure test, full PHPUnit (221 tests, 72,558 assertions), full PHPStan, PHP-CS-Fixer 3.75 dry-run, JSON and translation validation, generated-file collision check, PHP lint, and `git diff --check` passed.
- For #5342, server-rendered and client-translated regression tests, full PHPUnit (223 tests, 72,563 assertions), full PHPStan, PHP-CS-Fixer 3.75 dry-run, translation validation, generated-file collision check, PHP lint, and `git diff --check` passed.
- For #4830, targeted item/XP-orb/sneaking/sound bounce tests, full PHPUnit (227 tests, 72,572 assertions), full PHPStan, PHP-CS-Fixer 3.75 dry-run, translation validation, generated-file collision check, PHP lint, and `git diff --check` passed; live client gameplay testing remains pending.
- For the `callback-validator` import, its PHPUnit suite (104 tests, 499 assertions), package and root PHPStan, root PHPUnit (227 tests, 72,572 assertions), PHP-CS-Fixer 3.75 dry-run, Composer validation, source audit, JSON and translation validation, code generation, generated-file collision check, and `git diff --check` passed. Composer validation retains the pre-existing deprecated `LGPL-3.0` SPDX identifier warning.
- For the `binaryutils` import, its PHPUnit suite (4 tests, 4 assertions), package and root PHPStan, root PHPUnit (227 tests, 72,572 assertions), PHP-CS-Fixer 3.75 dry-run, source audit, JSON and translation validation, code generation, generated-file collision check, Composer install dry-run, and `git diff --check` passed. Composer validation retains the pre-existing deprecated `LGPL-3.0` SPDX identifier warning.
