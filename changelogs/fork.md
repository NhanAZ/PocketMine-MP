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

### Protocol

- Verified the fork current for Minecraft: Bedrock Edition 1.26.30 / protocol 1001 using PMMP BedrockProtocol, Endstone protocol docs, and Cloudburst Protocol evidence.
- Added root PHPUnit fixtures for the 1.26.30 `BossEventPacket` fixed-field wire format.

### Dependencies

- Imported `pocketmine/color`, `pocketmine/errorhandler`, `pocketmine/math`, and `pocketmine/log` as local Composer path packages while preserving licenses, package metadata, and useful source history.

### Maintenance

- Added baseline verification, protocol update, source drift, upstream backlog intake, backlog prioritization, community intake, and sustainable maintenance workflows.
- Consolidated agent context into `AGENTS.md` and `ROADMAP.md`.
- Restored full PHPStan coverage for backlog tooling and fixed fork CI failures.
- Clarified fork-owned links and upstream ecosystem links.
- Disabled upstream-only release publishing workflows until fork-owned destinations and credentials are configured.

### Reviewed Or Deferred

- Reviewed upstream issue #6580 and deferred it because the LevelDB compaction fix needs a world-format or region-sharded DB design, migration path, rollback plan, and benchmarks.
- Reviewed RakLib anti-spoofing cookies and deferred them until a tagged release or the planned local RakLib import.

### Checks

- Targeted tests were run for the implemented upstream fixes.
- Full repository PHPUnit, PHPStan, PHP-CS-Fixer 3.75 dry-run, JSON validation, generated-file collision checks, and `git diff --check` were clean after the #6832 invalid loaded-tile work.
