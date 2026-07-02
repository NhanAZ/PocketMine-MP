# Protocol Update Workflow

This document defines the repeatable workflow for Minecraft: Bedrock Edition protocol updates in this fork.
Protocol work is high risk: it touches login, packet serialization, item/block runtime data, world compatibility, and plugins that use internal network classes.

Do not assume that the version in an issue, message, or old branch is the latest version.
At the start of each protocol update, verify the current target version from release notes, Bedrock client versions, upstream package tags, or upstream commits.
Refresh [SOURCE_MONITORING.md](SOURCE_MONITORING.md) before selecting implementation evidence.

## Current Baseline

Current repository baseline:

- PocketMine-MP: `5.44.3+dev`
- `pocketmine/bedrock-protocol`: `58.0.0+bedrock-1.26.30`
- `pocketmine/bedrock-data`: `6.7.0+bedrock-1.26.30`
- `pocketmine/bedrock-block-upgrade-schema`: `5.2.0`
- `pocketmine/bedrock-item-upgrade-schema`: `1.17.0`
- Minecraft: Bedrock Edition display version from the phar baseline: `v26.30`

## Inputs

Protocol updates may require changes from these sources:

| Input | Current location | Purpose |
|:--|:--|:--|
| BedrockProtocol package | `vendor/pocketmine/bedrock-protocol` | Packet classes, serializers, packet pool, protocol constants, login/game packet formats. |
| BedrockData package | `vendor/pocketmine/bedrock-data` | Canonical block states, item dictionary, biome IDs, entity IDs, creative inventory, recipes, tags, protocol info. |
| Block upgrade schema | `vendor/pocketmine/bedrock-block-upgrade-schema` | Saved block data upgrades across Minecraft versions. |
| Item upgrade schema | `vendor/pocketmine/bedrock-item-upgrade-schema` | Saved item data upgrades across Minecraft versions. |
| PMMP integration code | `src/network/mcpe`, `src/data/bedrock`, `src/world/format` | Connects protocol/data packages to runtime server behaviour. |
| Captured packets | `tools/generate-bedrock-data-from-packets.php` input files | Optional data extraction from decoded vanilla client/server packets. |

When the fork imports Bedrock packages into `packages/`, keep the logical source names above.
The working paths may change from `vendor/pocketmine/...` to local Composer path packages, but the update phases stay the same.

## Evidence Hierarchy

Protocol details are often incomplete in any single project.
Use evidence in this order:

1. Reproducible vanilla client or Bedrock Dedicated Server observations, including decoded packet captures.
2. Canonical PMMP BedrockProtocol, BedrockData, and PocketMine-MP changes.
3. Official Minecraft release information for version and feature context.
4. Cloudburst Protocol for an independent protocol-library interpretation.
5. PowerNukkitX, Dragonfly, and Endstone for independent server behaviour and integration evidence.

No peer implementation is a drop-in specification.
For a high-risk packet field, require either reproducible packet evidence or agreement between at least two independent implementations before changing serialization.

## Cross-Implementation Translation

Agents may study reputable implementations listed in [SOURCE_MONITORING.md](SOURCE_MONITORING.md), but must translate semantics rather than convert code mechanically.

For each borrowed protocol insight:

1. Record the repository, commit, file, and target Bedrock version.
2. Describe the observed wire behaviour: field order, scalar type, optional condition, default, enum value, and version guard.
3. Corroborate it with another source or a packet capture when the change affects login, encryption, StartGame, inventory, world data, or packet framing.
4. Implement the behaviour using PocketMine-MP naming, serializers, types, and architecture.
5. Add encode/decode, round-trip, fixture, or integration tests where practical.
6. Record disagreements between sources instead of choosing the most convenient implementation silently.

Do not copy code across repositories unless the license, attribution, and project policy clearly permit it.
Even when licenses are compatible, prefer an independent implementation from documented behaviour because Java, Go, C++, and PHP projects have different invariants.

Use this evidence record in the issue or PR:

```text
Protocol evidence:
- Target Bedrock version/protocol:
- Primary observation or PMMP source:
- Corroborating project and commit:
- File or symbol inspected:
- Wire behaviour inferred:
- Source disagreements or uncertainty:
- PMMP-native implementation choice:
- Tests or packet fixtures added:
```

## Generated Outputs

These generated files are commonly affected by BedrockData or protocol data changes:

| Output | Generator | Main input |
|:--|:--|:--|
| `generated/data/bedrock/BedrockDataFiles.php` | `build/codegen/bedrockdata-path-consts.php` | file list under `BEDROCK_DATA_PATH` |
| `generated/data/bedrock/BiomeIds.php` | `build/codegen/biome-ids.php` | `BedrockDataFiles::BIOME_ID_MAP_JSON` |
| `generated/data/bedrock/block/BlockTypeNames.php` | `build/codegen/block-serializer-consts.php` | `vendor/pocketmine/bedrock-data/canonical_block_states.nbt` |
| `generated/data/bedrock/block/BlockStateNames.php` | `build/codegen/block-serializer-consts.php` | `vendor/pocketmine/bedrock-data/canonical_block_states.nbt` |
| `generated/data/bedrock/block/BlockStateStringValues.php` | `build/codegen/block-serializer-consts.php` | `vendor/pocketmine/bedrock-data/canonical_block_states.nbt` |
| `generated/data/bedrock/item/ItemTypeNames.php` | `build/codegen/item-type-names.php` | `vendor/pocketmine/bedrock-data/required_item_list.json` |
| `generated/lang/*` | `build/codegen/known-translation-apis.php` | `resources/translations/eng.ini` |
| generated registry accessors | `build/codegen/registry-interface.php src generated` | source classes with registry generation tags |

Use the Composer script for the normal full pass:

```powershell
composer run update-codegen
```

If protocol work only changes Bedrock data, still run the full codegen script unless there is a clear reason to run a narrower command.
Generated diffs are part of the review surface and must not be hidden.

## Update Checklist

Use one issue or PR per protocol target version.

1. Verify target version:
   - Confirm the target Minecraft: Bedrock Edition version and protocol number.
   - Record sources checked and the date checked.
   - Confirm whether the update is stable, preview/beta, or emergency compatibility.
   - Refresh the maintenance source report and record relevant peer-reference commits.

2. Update dependencies or local packages:
   - Update `pocketmine/bedrock-protocol`.
   - Update `pocketmine/bedrock-data`.
   - Update block/item upgrade schemas if they changed.
   - If packages are local under `packages/`, import or merge the upstream package commits before changing root Composer constraints.
   - Run the smallest Composer update command that changes only the needed packages.

3. Inspect protocol constants:
   - Check `ProtocolInfo::CURRENT_PROTOCOL`.
   - Check `ProtocolInfo::MINECRAFT_VERSION`.
   - Check `ProtocolInfo::MINECRAFT_VERSION_NETWORK`.
   - Check packet pool additions/removals and serializer signature changes.

4. Regenerate derived files:
   - Run `composer run update-codegen`.
   - Review generated diffs for missing block states, item names, biome IDs, or data file path constants.
   - Run PHP-CS-Fixer if generated code needs style fixup.

5. Review integration code:
   - Search for new or removed packet classes referenced by handlers.
   - Check `src/network/mcpe/handler`.
   - Check `src/network/mcpe/convert`.
   - Check item/block translators and world data version handling.
   - Check login, resource pack, compression, encryption, and StartGame paths.

6. Run automated checks:
   - `composer validate`
   - `vendor\bin\phpstan.bat analyse --no-progress`
   - `vendor\bin\phpunit.bat tests\phpunit`
   - release-style phar build from [MAINTAINING.md](MAINTAINING.md)
   - `php PocketMine-MP.phar --version`

7. Run client smoke tests:
   - Join with the target Bedrock client on Windows or mobile.
   - Confirm incompatible older/newer clients get a clean protocol mismatch message.
   - Spawn in a fresh world.
   - Move, chat, break/place simple blocks, open inventory, pick up/drop an item.
   - Place or inspect one newly changed block or item from the target version if applicable.
   - Load at least one existing world backup in a staging copy.
   - Join with a minimal plugin installed.

8. Record compatibility risk:
   - Note internal packet or serializer changes.
   - Note plugins likely to break if they use `pocketmine\network\mcpe`.
   - Note world/data upgrade risks.
   - Note whether protocol-only compatibility was changed without gameplay support.

9. Prepare release notes:
   - Include target Bedrock version and protocol number.
   - Mention known missing gameplay features separately from connection compatibility.
   - Mention plugin compatibility risk for internal network API users.
   - Include rollback advice: keep backups and test staging servers first.

## Client Smoke Test Record

Copy this into the PR or issue when testing a protocol update:

```text
Target Bedrock version:
Protocol number:
Server commit:
Client platform:
Client build/version source:

Startup:
- [ ] Phar starts
- [ ] /version reports expected Minecraft version/protocol
- [ ] Clean protocol mismatch for unsupported client

Join flow:
- [ ] Login succeeds
- [ ] Resource pack flow completes or cleanly skips
- [ ] Spawn succeeds
- [ ] Move/look updates work
- [ ] Chat works

Gameplay sanity:
- [ ] Break/place basic block
- [ ] Inventory open/close
- [ ] Pick up/drop item
- [ ] Interact with changed/new item or block, if relevant
- [ ] Existing staging world loads
- [ ] Minimal plugin loads

Risks observed:
- ...
```

## Plugin Compatibility Risk Template

Use this for each protocol PR:

```text
Plugin compatibility:
- Public API change: yes/no
- Internal network API change: yes/no
- Packet class added/removed/renamed:
- Serializer signature changes:
- Item/block runtime ID impact:
- Known affected plugin patterns:
- Mitigation or migration note:
```

## Agent Rules For Protocol Updates

Agents working on protocol updates must:

- Read this file before editing protocol, data, or generated files.
- Verify the target version using current sources at task time.
- Use the evidence hierarchy and record cross-implementation sources.
- Never mechanically translate code from another server project.
- Avoid mixing protocol updates with unrelated refactors.
- Keep generated diffs visible.
- Run at least PHPStan, PHPUnit, and phar build unless blocked.
- Document skipped checks and why they were skipped.
- End with the final report shape required by [AGENTS.md](AGENTS.md).
