# Dependency Consolidation Plan

This fork aims to keep PocketMine-MP maintenance self-contained while avoiding a risky one-shot vendor dump.
The first target is PMMP-owned Composer dependencies.
Third-party dependencies should remain Composer-managed unless there is a separate reason to vendor them.

## Current PMMP-Owned Dependencies

Source references are taken from `composer.lock`.

| Package | Version | License | Source reference | Risk | Recommendation |
|:--|:--|:--|:--|:--|:--|
| `pocketmine/bedrock-block-upgrade-schema` | `5.2.0` | `CC0-1.0` | `5d7889c` | Low | Import early as data package. |
| `pocketmine/bedrock-data` | `6.7.0+bedrock-1.26.30` | `CC0-1.0` | `bdb44a4` | Medium | Import early after one pilot; high release value, generated data is large. |
| `pocketmine/bedrock-item-upgrade-schema` | `1.17.0` | `CC0-1.0` | `e19685d` | Low | Import with the other Bedrock data/schema packages. |
| `pocketmine/bedrock-protocol` | `58.0.0+bedrock-1.26.30` | `LGPL-3.0` | `b7863bd` | High | Import after data packages; protocol churn and generated packet code need focused checks. |
| `pocketmine/binaryutils` | `0.2.7` | `LGPL-3.0` | `14c044a` | Medium | Import before NBT, protocol, and RakLib direct-root work. |
| `pocketmine/callback-validator` | `1.0.4` | `MIT` | `143fa6e` | Low | Import as a small utility package if eliminating all PMMP-owned fetches. |
| `pocketmine/color` | `0.3.1` | `LGPL-3.0` | `a0421f1` | Low | Best pilot import: one source file, no package dependencies. |
| `pocketmine/errorhandler` | `0.7.1` | `LGPL-3.0` | `84c9ec8` | Low | Imported and wired through a local path package. |
| `pocketmine/log` | `0.4.0` | `LGPL-3.0` | `e6c912c` | Low | Import early, but note it uses classmap autoloading. |
| `pocketmine/math` | `1.0.0` | `LGPL-3.0` | `dc132d9` | Low | Import early; small and stable. |
| `pocketmine/nbt` | `1.2.0` | `LGPL-3.0` | `51b8d6a` | Medium | Import after `binaryutils`; used by protocol and world/data paths. |
| `pocketmine/raklib` | `1.2.1` | `GPL-3.0` | `669eb4d` | High | Import late; keep license boundaries explicit and test networking carefully. |
| `pocketmine/raklib-ipc` | `1.0.1` | `GPL-3.0` | `ce632ef` | High | Import with or after RakLib; IPC/threading behaviour is high risk. |
| `pocketmine/snooze` | `0.5.0` | `LGPL-3.0` | `a86d9ee` | Medium | Import after baseline utility packages; depends on `ext-pmmpthread`. |

## Recommended Layout

Use a `packages/` directory and preserve each package's original structure.

```text
packages/
  bedrock-block-upgrade-schema/
  bedrock-data/
  bedrock-item-upgrade-schema/
  bedrock-protocol/
  binaryutils/
  callback-validator/
  color/
  errorhandler/
  log/
  math/
  nbt/
  raklib/
  raklib-ipc/
  snooze/
```

Each imported package should keep:

- original `composer.json`
- original license file
- original README when present
- original source layout
- an import note in the PR or commit message with source repository and commit

## Import Method

Use Composer `path` repositories as the first consolidation step.
This keeps source code in this repository while minimizing build and autoload churn.

Example after importing `pocketmine/color` into `packages/color`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "packages/color",
      "options": {
        "symlink": false
      }
    }
  ]
}
```

Then keep the existing `require` entry for `pocketmine/color`.
Composer will install the package from the local path instead of fetching it externally.

Why this first:

- `vendor/pocketmine/...` paths continue to exist after `composer install`.
- Existing codegen scripts can keep using current vendor paths during early imports.
- Each package can be imported, tested, and reverted independently.
- Later, the fork can decide whether to replace path packages with direct root autoload mappings.

## Pilot Import

Pilot package: `pocketmine/color`.

Status: imported into `packages/color` and wired through a Composer `path` repository.

Reasons:

- small package
- LGPL-3.0, compatible with the main project license
- no package dependencies
- used by protocol-related code, so it proves local package replacement without touching protocol first
- low blast radius if Composer path repository configuration needs adjustment

Pilot checklist:

1. [x] Import `https://github.com/pmmp/Color.git` at `a0421f1e9e0b0c619300fb92d593283378f6a5e1` into `packages/color`.
2. [x] Add a Composer path repository for `packages/color` with `"symlink": false`.
3. [x] Run the smallest Composer update that rewrites the lock source to the path repository.
4. [x] Confirm `composer install --prefer-dist --no-interaction` works.
5. [x] Run PHPStan and PHPUnit.
6. [x] Build a phar.
7. [x] Commit and push the import separately from unrelated changes.

## Suggested Import Order

1. `pocketmine/color` as the pilot.
2. Small utilities: `log`, `math`, `callback-validator`.
3. Binary/data foundations: `binaryutils`, `nbt`.
4. Bedrock data/schema packages: `bedrock-data`, `bedrock-block-upgrade-schema`, `bedrock-item-upgrade-schema`.
5. `bedrock-protocol`.
6. Thread/network packages: `snooze`, `raklib`, `raklib-ipc`.

Do not combine high-risk imports with protocol updates.
The fork should be able to bisect dependency import problems independently from Minecraft compatibility work.
