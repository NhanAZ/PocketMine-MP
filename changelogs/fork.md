# Fork Changelog

This file tracks NhanAZ fork-specific maintenance and implementation changes.
Upstream PocketMine-MP release notes remain in the numbered changelog files.

## 2026-07-03

### Fixes

- Implemented upstream issue #5603: nether-brick fences no longer extend collision boxes toward wooden fences, while different wooden fence variants continue connecting to each other.
- Implemented upstream issue #5463: updater channel suggestions use `VersionInfo::BUILD_CHANNEL`, correctly distinguish alpha/beta releases from stable builds, and report the actual prerelease channel.
- Implemented upstream issue #6712: invalid saved food, exhaustion, saturation, and hunger tick-timer values now raise `SavedDataLoadingException` instead of leaking `InvalidArgumentException` from strict hunger ranges.
- Implemented upstream issue #6714: projectile hit handling now stops immediately if a plugin closes the projectile during `ProjectileHitBlockEvent` or `ProjectileHitEntityEvent`, avoiding post-close hit logic and world movement updates.
- Implemented upstream issue #2626: creative-mode predicted block destroys now restore `PlayerInteractEvent::LEFT_CLICK_BLOCK` when the client skips repeated start-break actions, without duplicating normal start-break left-click events.
- Implemented upstream issue #4206: replacement chunks passed to `World::setChunk()` now rebase their existing tile coordinates to the target chunk before save or conflict handling.
- Implemented upstream issue #6800: common inventory searches now cache the search item's prepared NBT for matching instead of rebuilding it on every slot comparison.
- Implemented upstream issue #6818: startup timezone initialization now treats `date.timezone=UTC` as PHP's possible unset/invalid fallback and continues into auto-detection, while `Etc/UTC` remains available for explicit UTC deployments.
- Implemented upstream issue #3272: async light population now locks and keeps the target chunk loaded while calculating, then discards stale light results if main-thread terrain changes break the lock before completion.
- Implemented upstream issue #3974: chunk population now uses a server-wide task slot limiter so many loaded worlds cannot each consume the full configured population concurrency at once.
- Implemented upstream issue #6926: command integer parsing now checks numeric-string bounds before integer conversion, preventing PHP 8.5 out-of-range float-string warnings while preserving existing decimal, exponent, clamp, and localized error behaviour.
- Implemented upstream issue #6661: enchantment instances reject levels outside `1 ... 32767`, preventing invalid item enchantment data and delayed gameplay or `TAG_Short` serialization failures.
- Implemented upstream issue #6782: schema-converted blockitems without state NBT now resolve registered block defaults instead of assuming network legacy meta `0`; all seven legacy skull variants retain their correct item type.
- Implemented the safe deserializer portion of upstream issue #6654: global block and item deserializers now reject outputs that lack matching persistent serializers, catching incomplete plugin registration during load instead of later during save.
- Implemented upstream issue #4673: RakLib socket creation failures now raise `SocketException`, allowing unsupported IPv6/address-family startup errors to report as controlled network-start failures instead of thread crash dumps.
- Implemented upstream issue #1537: chests now refuse to open when a `Living` entity is standing above the chest or either half of a double chest, while non-living entities do not block opening.

### Maintenance

- Refreshed the upstream intake snapshot to 419 open issues and 31 open pull requests, retaining a scored 40-item shortlist.
- Reconfirmed the upstream intake snapshot at 419 open issues and 31 open pull requests; the top-40 shortlist is unchanged and exhausted, so the next candidates are being read from an extended scored view without adding another repo JSON file.
- Reviewed upstream issue #2549 as already covered by later canonical player generation throttling and concentric chunk ordering; added `ChunkSelector` ring-order/completeness regression coverage and clarified the global `population-queue-size` setting.
- Implemented upstream issue #5638: `PlayerAuthInputPacket` now reports timing breakdowns for input flags, movement, item-use transactions, item-stack requests, and block actions.
- Implemented upstream issue #5064: added `settings.query-player-list`, defaulting to true, so GS4 Query can keep reporting player counts while hiding player names from long-query responses when disabled.
- Refreshed source monitoring; PowerNukkitX commit `0d0a3b4f9362f4188d3e2b65df47bc93b4f41975` is reviewed as reference-only map-image evidence.
- Reviewed upstream issue #6062 and deferred cross-thread dynamic block type ID allocation until a shared allocator/registry or type-ID redesign is available.
- Fixed the GitHub Code Style follow-up for #3272 by applying PHP-CS-Fixer native-function import style in `WorldTest`; follow-up commit `4c941c578` restored green CI.

### Checks

- Targeted fence-family regression coverage passed (1 test, 5 assertions), along with full PHPUnit (233 tests, 72,606 assertions), full PHPStan, code generation, translation validation, generated-file collision, JSON, syntax, and whitespace checks. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for implementation commit `ba48d1481`.
- Recorded the non-blocking `pmmp/setup-php-action@3.2.0` Node.js 20 deprecation warning for a focused workflow maintenance follow-up.
- For #5463, targeted channel-resolution coverage (1 test, 7 assertions), full PHPUnit (234 tests, 72,613 assertions), full PHPStan, code generation, translation validation, generated-file collision, JSON, syntax, and whitespace checks passed. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for implementation commit `5179fb448`.
- For #6712, targeted hunger-data coverage (5 tests, 12 assertions), full PHPUnit (239 tests, 72,625 assertions), full PHPStan, code generation, translation validation, generated-file collision, JSON, syntax, and whitespace checks passed. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for implementation commit `9f43f9497`.
- For #6714, targeted projectile-close coverage (2 tests, 8 assertions), full PHPUnit (241 tests, 72,633 assertions), touched-file and full PHPStan, code generation, translation validation, generated-file collision, JSON, syntax, and whitespace checks passed. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for style follow-up commit `9270fb248` after the initial implementation run failed only PHP-CS-Fixer import ordering.
- For #5638, syntax checks, touched-file PHPStan, full PHPStan, full PHPUnit (241 tests, 72,633 assertions), code generation, translation validation, generated-file collision, and whitespace checks passed locally. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for implementation commit `13fba9671`.
- For #5064, targeted Query PHPUnit (4 tests, 18 assertions), touched-file PHPStan, full PHPUnit (244 tests, 72,647 assertions), full PHPStan, code generation, translation validation, generated-file collision, JSON, and whitespace checks passed locally. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for implementation commit `3213aae4e`.
- For #2626, targeted handler PHPUnit (3 tests, 10 assertions), touched-file PHPStan, full PHPUnit (247 tests, 72,657 assertions), full PHPStan, syntax, and whitespace checks passed locally. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for implementation commit `032d76f49`.
- For #4206, targeted `WorldTest` (2 tests, 9 assertions), touched-file PHPStan, full PHPUnit (248 tests, 72,663 assertions), full PHPStan, syntax, JSON, and whitespace checks passed locally. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for implementation commit `1276dd876`.
- For #6800, targeted `BaseInventoryTest` (11 tests, 28 assertions), touched-file PHPStan, full PHPUnit (250 tests, 72,667 assertions), full PHPStan, and syntax checks passed locally. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for implementation commit `c1956b066`.
- For #6818, targeted `TimezoneTest` (2 tests, 7 assertions), touched-file PHPStan, full PHPUnit (252 tests, 72,674 assertions), full PHPStan, syntax, JSON, and whitespace checks passed locally. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for implementation commit `425d075bf`.
- For #3272, targeted `WorldTest` (4 tests, 22 assertions), touched-file PHPStan, full PHPUnit (254 tests, 72,687 assertions), full PHPStan, syntax, JSON, and whitespace checks passed locally. Fork CI then passed its PHP 8.1-8.5 matrix, PHP-CS-Fixer, integration, generated-code, translation, ShellCheck, and Docker jobs for follow-up commit `4c941c578` after the initial implementation run failed only PHP-CS-Fixer native-function import style.
- For #3974, targeted `WorldManagerTest`/`WorldTest` (6 tests, 33 assertions), full PHPUnit (256 tests, 72,698 assertions), touched-file and full PHPStan, syntax, code generation, translation validation, generated-file collision, JSON, whitespace, GitHub CI, PHP-CS-Fixer, and Docker checks passed for commit `05a6694b5`.
- For #2549, targeted `ChunkSelectorTest` (1 test, 1,569 assertions), full PHPUnit (257 tests, 74,267 assertions), touched-file and full PHPStan, syntax, code generation, translation validation, generated-file collision, JSON, whitespace, GitHub CI, PHP-CS-Fixer, and Docker checks passed for commit `2c705b83f`.
- For #5821, source audit, review JSON validation, whitespace validation, GitHub CI, PHP-CS-Fixer, and Docker checks passed for decision commit `e8cc27251`; code tests were not required because no runtime source changed.
- For #6926, targeted `VanillaCommandTest` (4 tests, 8 assertions), full PHPUnit (261 tests, 74,275 assertions), touched-file and full PHPStan, syntax, code generation, translation validation, generated-file collision, 114-file JSON validation, whitespace, PHP 8.1-8.5 CI, PHP-CS-Fixer, integration, and Docker checks passed for commit `5fddf6251`.
- For #5805, source audit, review JSON validation, whitespace, GitHub CI, PHP-CS-Fixer, and Docker checks passed for decision commit `32ad9a0c1`; runtime-specific tests were not required because no source code changed.
- For #2731, source audit, review JSON validation, whitespace, GitHub CI, PHP-CS-Fixer, and Docker checks passed for decision commit `4b8e3df70`; runtime-specific tests were not required because no source code changed.
- For #6661, targeted `ItemTest` (16 tests, 29 assertions), full PHPUnit (264 tests, 74,281 assertions), full PHPStan, syntax, code generation, translation validation, generated-file collision, 114-file JSON validation, source audit, Composer validation/install dry-run, whitespace, PHP 8.1-8.5 CI, PHP-CS-Fixer, integration, and Docker checks passed for commit `f30646a6b`.
- For #6782, targeted item-upgrader and item serializer/deserializer PHPUnit (9 tests, 11,770 assertions), full PHPUnit (271 tests, 74,309 assertions), touched-file and full PHPStan, syntax, code generation, translation validation, generated-file collision, 114-file JSON validation, source audit, Composer validation/install dry-run, whitespace, PHP 8.1-8.5 CI, PHP-CS-Fixer, integration, and Docker checks passed for commit `6f4d51ff7`.
- For #6654, targeted block/item serializer-deserializer PHPUnit (7 tests, 23,142 assertions), full PHPUnit (273 tests, 74,313 assertions), full PHPStan, syntax, code generation, generated-file/report diff checks, 119-file non-vendor JSON validation, source audit, whitespace, PHP 8.1-8.5 CI, PHP-CS-Fixer, integration, and Docker checks passed for commit `212435707`. Composer strict validation still reports only the pre-existing deprecated root `LGPL-3.0` SPDX warning.
- For #4673, RakLib socket syntax, targeted RakLib/Query PHPUnit (6 tests, 33 assertions), full PHPUnit (273 tests, 74,313 assertions), full PHPStan, code generation, translation validation, generated-file collision, 119-file non-vendor JSON validation, source audit, whitespace, PHP 8.1-8.5 CI, PHP-CS-Fixer, integration, and Docker checks passed for commit `32b06ac70`. Direct RakLib package PHPStan through the root config still reports unrelated pre-existing package issues; Composer strict validation still reports only the pre-existing deprecated root `LGPL-3.0` and RakLib `GPL-3.0` SPDX warnings.
- For #1537, targeted `ChestTest` (3 tests, 9 assertions), block PHPUnit (13 tests, 45,699 assertions), full PHPUnit (276 tests, 74,322 assertions), targeted and full PHPStan, syntax, code generation, translation validation, generated-file collision, 119-file non-vendor JSON validation, source audit, whitespace, PHP 8.1-8.5 CI, PHP-CS-Fixer, integration, and Docker checks passed for commit `7a657f0b4`. Composer strict validation still reports only the pre-existing deprecated root `LGPL-3.0` SPDX warning.

### Reviewed Or Deferred

- Reviewed #4649 and deferred deterministic cross-chunk population until a shared ownership or staging design can cover trees, ores, and other conflicting features.
- Reviewed #6814 and #6831 together and deferred global entity-physics changes until Vanilla Bedrock trajectories, per-axis drag, PvP compatibility, and plugin migration are addressed as one design.
- Reviewed #2041 and deferred magma surface damage with #2731 until collision directions can identify contacted block faces without broad hot-path scans.
- Reviewed upstream issue #2731 and deferred cactus top-contact damage until movement collision directions can support a dedicated surface-contact action without the adjacent-block false positives or broad AABB cost seen in abandoned PRs #4243 and #6347.
- Reviewed upstream pull request #5805 and deferred its specialized compression-worker pool because the incomplete 2023 branch lacks current integration, tests, idle-pool cleanup, bounded queue safeguards, and a safe fallback for forced login compression.
- Reviewed upstream issue #5821 and deferred increasing leaf-decay distance until a cached distance-state design, bounded neighbour propagation, generated-tree coverage, traversal benchmarks, and block-state/plugin compatibility plan replace the current recursive random-tick search.
- Reviewed upstream issue #5410 and deferred flat-world height changes until representative pre-1.18, post-1.18, and PMMP-created worlds establish `FlatWorldLayers` version and Y-offset semantics with round-trip and live-client evidence.
- Reviewed upstream issue #7035 and rejected the unsupported suggestion to raise the LevelDB world NetworkVersion ceiling without an affected world or matching chunk, blockstate, and upgrade-schema evidence. Current network protocol `1001` does not imply safe support for worlds newer than the deliberate `924` storage ceiling.
- Reviewed upstream issue #5385 and related #1567, then deferred the death-screen rejoin fix because the correct direction requires moving respawn state and `PlayerRespawnEvent` into the login/pre-`StartGamePacket` path, which needs a separate plugin-compatibility plan.
- Reviewed upstream issue #6781 and deferred the large long-lived array GC-performance work because it needs a runtime or collection-design strategy with benchmarks, not a small registry-wrapper patch.
- Reviewed upstream issue #6062 and deferred `BlockTypeIds::newId()` cross-thread synchronization because a safe fix needs a shared allocator/registry or a broader type-ID design instead of another process-local static counter.

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
- Imported `pocketmine/nbt` 1.2.0 at canonical commit `51b8d6a97065fb93e0b4f660b65164b6e1ed2fff` as a local Composer path package with its LGPL-3.0 license, package metadata, tests, subtree history, and canonical source monitoring.
- Imported `pocketmine/bedrock-block-upgrade-schema` 5.2.0+bedrock-1.21.110 at canonical commit `5d7889c9a1cdf9e3cd814d2a104ad69b75116ec7` as a local Composer path package with its CC0-1.0 license, package metadata, schema files, subtree history, and canonical source monitoring; deferred the later `actions/setup-node`-only master commit `581e29ffecfc846e982203d473f5901e995f7165`.
- Imported `pocketmine/bedrock-data` 6.7.0+bedrock-1.26.30 at canonical commit `bdb44a48fb6beffb6e9f6864f06d2232eb62b6a3` as a local Composer path package with its CC0-1.0 license, package metadata, data files, subtree history, and canonical source monitoring.
- Imported `pocketmine/bedrock-item-upgrade-schema` 1.17.0+bedrock-1.26.20 at canonical commit `e19685d2e7e76eb7446115c556df34e5d627d072` as a local Composer path package with its CC0-1.0 license, package metadata, schema files, subtree history, and canonical source monitoring.
- Imported `pocketmine/bedrock-protocol` 58.0.0+bedrock-1.26.30 at canonical commit `b7863bd60042723b91c3cb87ac37309a4fec1309` as a local Composer path package with its LGPL-3.0 license, package metadata, protocol sources, tests, tools, subtree history, and canonical source monitoring.
- Imported `pocketmine/snooze` 0.5.0 at canonical commit `a86d9ee60ce44755d166d3c7ba4b8b8be8360915` as a local Composer path package with its LGPL-3.0 license, package metadata, static-analysis config, thread-notification sources, subtree history, and canonical source monitoring.
- Imported `pocketmine/raklib` 1.2.1 at canonical commit `669eb4d1e644f91437323ef24ce3ee985182b829` as a local Composer path package with its GPL-3.0 license, package metadata, protocol/server sources, tools, static-analysis config, subtree history, and canonical source monitoring.
- Adapted RakLib canonical commit `7655018631147f0b5d473326fddc2faea105dffe`: offline handshake anti-spoofing cookies are supported behind `network.raklib-cookie-rotation-interval`, which defaults to `0` to preserve legacy and OVH-sensitive compatibility unless explicitly enabled.
- Imported `pocketmine/raklib-ipc` 1.0.1 at canonical commit `ce632ef2c6743e71eddb5dc329c49af6555f90bc` as a local Composer path package with its GPL-3.0 license, package metadata, IPC channel sources, static-analysis config, subtree history, and canonical source monitoring.
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
- Reviewed NBT stable commit `5429a21c9321eb154f7fcdaa789d2b5ccbcc6ea3` and deferred it because it only updates PHP 8.5 CI and PHPStan after the imported 1.2.0 lock pin.
- Reviewed and adapted RakLib anti-spoofing cookies with root handshake regression coverage and default-disabled PMMP configuration.
- Reviewed RakLib IPC post-tag commits through `dd79c4344640ab0d086f08ad47fe83f52b7d3371`, adapted the PHPStan-only type assertion in `RakLibToUserThreadMessageReceiver`, and deferred the remaining CI/dev PHPStan updates.
- Reviewed PowerNukkitX RakNet pacing and cookie configuration drift as reference-only evidence for the RakLib anti-spoofing follow-up.
- Reviewed PowerNukkitX commit `efd90f359f2ef6b7f80921b32afefa4761fe7082` as reference-only evidence for future cross-level entity ticking triage.

### Checks

- Targeted tests were run for the implemented upstream fixes.
- Full repository PHPUnit, PHPStan, PHP-CS-Fixer 3.75 dry-run, JSON validation, generated-file collision checks, and `git diff --check` were clean after the #6832 invalid loaded-tile work.
- For #6861, targeted `ItemTest` regression coverage, touched-file PHPStan, and `git diff --check` were clean; local PHP-CS-Fixer was not available outside CI.
- For #6130, the targeted bind-failure test, full PHPUnit (221 tests, 72,558 assertions), full PHPStan, PHP-CS-Fixer 3.75 dry-run, JSON and translation validation, generated-file collision check, PHP lint, and `git diff --check` passed.
- For #5342, server-rendered and client-translated regression tests, full PHPUnit (223 tests, 72,563 assertions), full PHPStan, PHP-CS-Fixer 3.75 dry-run, translation validation, generated-file collision check, PHP lint, and `git diff --check` passed.
- For #4830, targeted item/XP-orb/sneaking/sound bounce tests, full PHPUnit (227 tests, 72,572 assertions), full PHPStan, PHP-CS-Fixer 3.75 dry-run, translation validation, generated-file collision check, PHP lint, and `git diff --check` passed; live client gameplay testing remains pending.
- For the `callback-validator` import, its PHPUnit suite (104 tests, 499 assertions), package and root PHPStan, root PHPUnit (227 tests, 72,572 assertions), PHP-CS-Fixer 3.75 dry-run, Composer validation, source audit, JSON and translation validation, code generation, generated-file collision check, and `git diff --check` passed. Composer validation retains the pre-existing deprecated `LGPL-3.0` SPDX identifier warning.
- For the `binaryutils` import, its PHPUnit suite (4 tests, 4 assertions), package and root PHPStan, root PHPUnit (227 tests, 72,572 assertions), PHP-CS-Fixer 3.75 dry-run, source audit, JSON and translation validation, code generation, generated-file collision check, Composer install dry-run, and `git diff --check` passed. Composer validation retains the pre-existing deprecated `LGPL-3.0` SPDX identifier warning.
- For the `nbt` import, its PHPUnit suite (75 tests, 151 assertions), package and root PHPStan, root PHPUnit (227 tests, 72,572 assertions), PHP-CS-Fixer 3.75 dry-run, source audit, JSON and translation validation, code generation, generated-file collision check, Composer install dry-run, and `git diff --check` passed. Composer validation retains the pre-existing deprecated `LGPL-3.0` SPDX identifier warning.
- For the `bedrock-data` import, package Composer validation, root PHPUnit (227 tests, 72,572 assertions), root PHPStan, PHP-CS-Fixer 3.75 dry-run, source audit, JSON and translation validation, code generation, generated-file collision check, Composer install dry-run, and `git diff --check` passed. Composer validation retains the pre-existing deprecated `LGPL-3.0` SPDX identifier warning.
- For the `bedrock-item-upgrade-schema` import, package Composer validation, root PHPUnit (227 tests, 72,572 assertions), root PHPStan, PHP-CS-Fixer 3.75 dry-run, source audit, JSON and translation validation, code generation, generated-file collision check, Composer install dry-run, and `git diff --check` passed. Composer validation retains the pre-existing deprecated `LGPL-3.0` SPDX identifier warning.
- For the `bedrock-block-upgrade-schema` import, package Composer validation, root PHPUnit (227 tests, 72,572 assertions), root PHPStan, PHP-CS-Fixer 3.75 dry-run, source audit, JSON and translation validation, code generation, generated-file collision check, Composer install dry-run, and `git diff --check` passed. Composer validation retains the pre-existing deprecated `LGPL-3.0` SPDX identifier warning.
- For the `bedrock-protocol` import, its PHPUnit suite, package PHPStan, root PHPUnit (227 tests, 72,572 assertions), root PHPStan, PHP-CS-Fixer 3.75 dry-run, source audit, JSON and translation validation, code generation, generated-file collision check, Composer install dry-run, and `git diff --check` passed. Its create-method generator was executed and reviewed, but the generated fully-qualified type-hint churn was left uncommitted to preserve the canonical tag source. Composer validation retains the pre-existing deprecated `LGPL-3.0` SPDX identifier warning.
- For the `snooze` import, package PHPStan, root PHPUnit (227 tests, 72,572 assertions), root PHPStan, PHP-CS-Fixer 3.75 dry-run, source audit, JSON and translation validation, code generation, generated-file collision check, Composer install dry-run, and `git diff --check` passed. Composer validation retains the pre-existing deprecated `LGPL-3.0` SPDX identifier warning.
- For the `raklib` import, package PHPStan, root PHPUnit (227 tests, 72,572 assertions), root PHPStan, PHP-CS-Fixer 3.75 dry-run, source audit, JSON and translation validation, code generation, generated-file collision check, Composer install dry-run, and `git diff --check` passed. Composer validation only reports deprecated SPDX identifier warnings for RakLib's `GPL-3.0` and the root `LGPL-3.0`.
- For the `raklib-ipc` import, package PHPStan, root PHPUnit (227 tests, 72,572 assertions), root PHPStan, PHP-CS-Fixer 3.75 dry-run, source audit, JSON and translation validation, code generation, generated-file collision check, Composer install dry-run, and `git diff --check` passed. Composer validation only reports deprecated SPDX identifier warnings for RakLib IPC's `GPL-3.0` and the root `LGPL-3.0`.
- For the RakLib anti-spoofing cookie follow-up, targeted root PHPUnit (5 tests, 29 assertions), full root PHPUnit (232 tests, 72,601 assertions), RakLib package PHPStan, root PHPStan, source audit, code generation, translation validation, generated-file collision check, Composer validation, Composer install dry-run, JSON validation, syntax checks, and `git diff --check` passed. PHP-CS-Fixer 3.75 dry-run was skipped because `php-cs-fixer` is not installed in this workspace.
- Fixed the GitHub Code Style follow-up for the RakLib cookie test by removing a redundant native-return-type PHPDoc line caught by PHP-CS-Fixer.
