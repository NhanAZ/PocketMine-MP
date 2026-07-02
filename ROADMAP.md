# Roadmap

This is the live state and work queue for NhanAZ's personal PocketMine-MP fork.
Update it during every completed maintenance unit; do not create separate task-context Markdown files.

## Current State

- Fork governance, contribution, security, and production-risk warnings are established.
- Fork-specific changelog tracking is established in `changelogs/fork.md` and backfilled from `a170743de838581487bcf91bc7026fe786de752b` through `6c78bb0b0`.
- `pocketmine/color`, `pocketmine/errorhandler`, `pocketmine/log`, and `pocketmine/math` are local Composer path packages.
- Source monitoring covers the PMMP root, PMMP-owned dependencies, and four protocol reference projects.
- The current root matches `upstream/stable` at `fe9f8bd801530ee23ac8e6fb9d8a1922846d5aff`.
- The source audit has 19 entries and eight reviewed package-drift signals.
- PowerNukkitX advanced one reference-only voxel-shape commit on 2026-07-02; it was deferred as unrelated to the active TypeConverter task.
- RakLib anti-spoofing cookies were reviewed and deferred until a tagged release or the planned local RakLib import.
- The upstream backlog snapshot contains 450 open items and a scored top-40 shortlist.
- Upstream issue #6284 was triaged and implemented: spawnable tile network NBT now receives a `TypeConverter` context and converter-scoped serialized cache.
- Upstream issue #6711 was triaged and implemented: inventories now reject plain `Item` instances using block-item type IDs before they can crash later during serialization.
- Upstream issue #6580 was reviewed and deferred: the real LevelDB compaction fix requires region-sharded DBs or a world-format migration, not a small safe patch.
- Upstream issue #6808 was triaged and implemented: core entities now save with Bedrock entity IDs while retaining legacy Java/PM aliases for loading older worlds.
- Upstream issue #5632 was triaged and implemented: missing Bedrock blockstate properties now fall back to the registered default state for that block ID while still rejecting wrong property types.
- Upstream issue #6832 was triaged and implemented: chunk loading now skips invalid loaded tiles before block-state sync when their saved position is outside world bounds, duplicated, or in an unavailable chunk.
- Upstream issue #6861 was triaged and implemented: invalid firework rocket `Flight` values loaded from saved NBT now fail as `SavedDataLoadingException` instead of escaping safe item loading as `InvalidArgumentException`.
- Full repository PHPUnit, PHPStan, PHP-CS-Fixer 3.75 dry-run, and `git diff --check` are clean after the #6832 invalid loaded-tile work.
- Fork CI health is the top active gate; the latest red runs were PHP-CS-Fixer import order, PHPStan CLI argv handling, and Docker missing local `packages/` path repositories.
- User-facing links were reviewed: fork actions point to `NhanAZ/PocketMine-MP`; PMMP docs, packages, changelog links, and source attribution remain labeled upstream or ecosystem references.
- Release automation is intentionally conservative: source builds and local phars only; GitHub Releases, Docker publishing, updater metadata, Discord, Crowdin, branch sync, and upstream RestrictedActions-style workflows are disabled until fork-owned destinations are configured.
- Protocol verification on 2026-07-02 found the fork current for Bedrock `1.26.30` / protocol `1001`; `BossEventPacket` now has root PHPUnit fixtures for the 1.26.30 fixed-field wire format.

## Priority

1. Red GitHub Actions on the active fork branch.
2. Confirmed security issue or crash regression.
3. Current Bedrock protocol compatibility.
4. A focused PMMP dependency stabilization or import.
5. A useful canonical upstream fix.
6. One actionable upstream issue or pull request.
7. Release, community, maintenance, or documentation cleanup.

Before source-sensitive work, refresh `.github/maintenance-sources/report.json`.
The fork root remains authoritative, canonical PMMP remains the primary change feed, and peer projects remain evidence only.

## Ready Queue

### 1. Review Remaining Upstream Links

- [x] Classify `pmmp.io`, `github.com/pmmp`, Discord, and PocketMine links as ecosystem, upstream, or fork-owned.
- [x] Keep useful ecosystem links, label upstream support clearly, and use fork links for fork-specific actions.

### 2. Decide Fork Release Automation

- [x] Decide whether the fork needs GitHub Releases, phars, Docker images, updater metadata, or source builds only.
- [x] Remove unused upstream-only workflows.
- [x] Enable publishing only with fork-owned credentials and destinations.
- [x] Add a compact release checklist and rollback note.

### 3. Perform A Real Protocol Update

- [x] Verify the current Bedrock version and protocol number from current sources.
- [x] Refresh source monitoring and record exact evidence commits.
- [ ] Use packet evidence or two independent implementations for high-risk fields.
  - 2026-07-02: PMMP BedrockProtocol `b7863bd60042723b91c3cb87ac37309a4fec1309`, Endstone protocol-docs `f00805e98363c6c3024ffd73a56caa5a723cca3a`, and Cloudburst Protocol `f8295d3258fcb4e5c707d852dac981e964b336aa` agree on Bedrock `1.26.30` / protocol `1001`; PMMP and Cloudburst agree on the v1001 `BossEventPacket` fixed-field layout.
- [ ] Update local protocol/data packages and root integration separately from cleanup.
  - No BedrockProtocol bump is available yet because the installed package is already at canonical branch head. Continue watching for the next BedrockProtocol or BedrockData drift before editing protocol code.
- [ ] Regenerate data with `composer run update-codegen` and review generated diffs.
- [ ] Run PHPStan, PHPUnit, phar build, version check, and client smoke tests.
  - 2026-07-02 checks completed: targeted `BossEventPacketTest`, full `tests/phpunit`, and full PHPStan on PHP 8.2. Phar build, version check, and client smoke test were skipped because no protocol or data package changed.
- [ ] Record packet/API/plugin/world risks and rollback guidance.
  - Current risk: protocol compatibility appears current, but no live client smoke test has been performed in this unit.

### 4. Triage One Upstream Item

- [x] Refresh `open-items.json` and `priority-shortlist.json` with the two backlog tools.
  - 2026-07-02: refreshed 419 open issues and 31 open pull requests from `pmmp/PocketMine-MP`; top shortlist remains 40 items.
- [x] Open one high-scoring original issue or PR.
  - Opened issue #6284, "Tile network NBT serialization needs a TypeConverter context".
  - Opened issue #6711, "No serializer registered for pocketmine\item\Item (-10282) Grass".
  - Opened issue #6580, "LevelDB compaction performance issue with large worlds (especially converted worlds)".
  - Opened issue #6808, "Entities are still saved with Java 1.10 IDs".
  - Opened issue #5632, "Update blocks due to missing blockstate properties produced by converters".
  - Opened issue #6832, "Loaded tiles with positions outside the world crash the server".
  - Opened issue #6750, "Server crashed".
  - Opened issue #6861, "Crash when loading firework from disk with negative flight multiplier".
- [x] Classify relevance and create only the smallest actionable fork task or port.
  - Classified as actionable network/protocol architecture work and implemented the smallest local change: pass `TypeConverter` through tile spawn NBT serialization and cache serialized spawn compounds per converter.
  - Classified #6711 as actionable core validation work and implemented the smallest local guard: `BaseInventory` rejects non-null plain `Item` objects whose type ID maps to a block type ID unless they are real `ItemBlock` instances.
  - Deferred #6580 because the least destructive upstream direction is splitting worlds into region DBs while keeping Mojang keys, which needs an explicit format design, converter path, rollback plan, and large-world benchmarks.
  - Classified #6808 as actionable core save-format work and implemented the smallest local change: make Bedrock entity IDs the default save IDs while preserving legacy aliases for deserialization.
  - Classified #5632 as actionable world-load tolerance work and implemented the smallest local change: registered blockstate deserializers now supply default property values for missing states while preserving failures for unknown IDs, unread properties, invalid values, and wrong tag types.
  - Classified #6832 as actionable crash/corruption handling work and implemented the smallest local change: invalid loaded tiles are logged and skipped before the chunk block-state post-processing path can read out-of-bounds coordinates.
  - Deferred #6750 because the public issue is unconfirmed and the actionable crash dumps are private to upstream maintainers.
  - Classified #6861 as actionable saved-data crash handling and implemented the smallest local change: validate firework rocket `Flight` during item NBT deserialization and convert invalid values to `SavedDataLoadingException`.
- [x] Preserve source links and authorship; do not copy whole discussions.
  - Sources: https://github.com/pmmp/PocketMine-MP/issues/6284, https://github.com/pmmp/PocketMine-MP/issues/6711, https://github.com/pmmp/PocketMine-MP/issues/6580, https://github.com/pmmp/PocketMine-MP/issues/6808 by dktapps, https://github.com/pmmp/PocketMine-MP/issues/5632 by GH-PM with a dktapps root-cause comment, https://github.com/pmmp/PocketMine-MP/issues/6832 by dktapps, https://github.com/pmmp/PocketMine-MP/issues/6750 by LeonMazzoli with private upstream crash dumps, and https://github.com/pmmp/PocketMine-MP/issues/6861 by dktapps; original issue comments were reviewed before implementation or deferral.
  - Checks: targeted `SpawnableTest`, targeted `BaseInventoryTest`, targeted `EntityFactoryTest`, targeted `BlockSerializerDeserializerTest`, targeted `WorldTest`, targeted `ItemTest`, full PHPUnit, full PHPStan, PHP-CS-Fixer 3.75 dry-run, JSON validation, `git diff --check`, and generated-file collision check. For #6861 specifically, touched-file PHPStan and `git diff --check` were clean; local PHP-CS-Fixer was unavailable and remains delegated to CI.
  - The next shortlist candidates are #6130, #5342, and #4830, but each original issue must be opened and reviewed before any work is added.

## Dependency Track

Completed: `color`, `errorhandler`, `log`, `math`.

Remaining order:

1. `callback-validator`
2. `binaryutils`
3. `nbt`
4. Bedrock data and upgrade schemas
5. `bedrock-protocol`
6. `snooze`, `raklib`, `raklib-ipc`

Import one package at a time. Preserve its license, source layout, Composer metadata, pin, and useful history.
Local ownership does not stop monitoring its canonical PMMP repository.
Re-evaluate RakLib commit `765501863` during its import or when a release containing it is tagged; retain a configurable cookie interval because of the documented OVH caveat.

## Milestones

- **Foundation hardening: active.** Changelog habit is established; keep `changelogs/fork.md` current for each completed unit.
- **Self-contained dependencies: active.** Four packages are local; protocol and network packages remain.
- **Protocol velocity: active.** Evidence rules are ready; one real verified protocol update is still required.
- **Fork releases: planned.** Automated publishing is disabled; release naming and artifact policy remain before public releases.
- **Community throughput: planned.** Keep intake neutral, focused, reproducible, and fast without mass-importing upstream noise.

## Maintenance Commands

```powershell
$env:GITHUB_TOKEN = gh auth token
php tools/audit-maintenance-sources.php
php tools/fetch-upstream-backlog.php
php tools/prioritize-upstream-backlog.php
Remove-Item Env:GITHUB_TOKEN
```

Generated maintenance data belongs under `.github/maintenance-sources/` and `.github/upstream-intake/` as JSON.

## Continuation

When asked to continue this roadmap:

1. Read `AGENTS.md` and this file only, then inspect code relevant to the first ready task.
2. Refresh external data only when the task depends on it.
3. Complete one focused unit with proportional checks.
4. If a push touches workflow-covered code, run the closest local checks before pushing.
5. Update this file live, including discoveries and deferrals.
6. Commit and push when requested.
