# Roadmap

This is the live state and work queue for NhanAZ's personal PocketMine-MP fork.
Update it during every completed maintenance unit; do not create separate task-context Markdown files.

## Current State

- Fork governance, contribution, security, and production-risk warnings are established.
- `pocketmine/color`, `pocketmine/errorhandler`, and `pocketmine/math` are local Composer path packages.
- Source monitoring covers the PMMP root, PMMP-owned dependencies, and four protocol reference projects.
- The current root matches `upstream/stable` at `fe9f8bd801530ee23ac8e6fb9d8a1922846d5aff`.
- The source audit has 19 entries and eight reviewed package-drift signals.
- The upstream backlog snapshot contains 450 open items and a scored top-40 shortlist.
- Full PHPStan currently has 21 known errors in the two upstream backlog tools after removing Markdown renderers.

## Priority

1. Confirmed security issue or crash regression.
2. Current Bedrock protocol compatibility.
3. A focused PMMP dependency stabilization or import.
4. A useful canonical upstream fix.
5. One actionable upstream issue or pull request.
6. Release, community, maintenance, or documentation cleanup.

Before source-sensitive work, refresh `.github/maintenance-sources/report.json`.
The fork root remains authoritative, canonical PMMP remains the primary change feed, and peer projects remain evidence only.

## Ready Queue

### 1. Review RakLib Anti-Spoofing Cookies

Upstream commit: `7655018631147f0b5d473326fddc2faea105dffe`.

- [ ] Review packet serializer and server handshake changes.
- [ ] Check compatibility with the locked RakLib version and current root integration.
- [ ] Evaluate the OVH compatibility caveat and rotation defaults.
- [ ] Decide to port, defer until release, or import RakLib before adapting it.
- [ ] Keep implementation separate from unrelated imports.

### 2. Restore Full PHPStan

- [ ] Type the JSON structures in `tools/fetch-upstream-backlog.php`.
- [ ] Type the JSON structures in `tools/prioritize-upstream-backlog.php`.
- [ ] Replace integer-or-false conditions with explicit comparisons.
- [ ] Confirm both JSON generators remain valid.
- [ ] Run `vendor\bin\phpstan.bat analyse --no-progress` with no errors.

### 3. Import `pocketmine/log`

- [ ] Import locked commit `e6c912c` from `pmmp/Log` into `packages/log` with history.
- [ ] Add a Composer path repository without changing the package version.
- [ ] Confirm its classmap autoload works after `composer install`.
- [ ] Run Composer validation, PHPStan, PHPUnit, and a phar build.
- [ ] Update the source manifest and this roadmap.

### 4. Review Remaining Upstream Links

- [ ] Classify `pmmp.io`, `github.com/pmmp`, Discord, and PocketMine links as ecosystem, upstream, or fork-owned.
- [ ] Keep useful ecosystem links, label upstream support clearly, and use fork links for fork-specific actions.

### 5. Decide Fork Release Automation

- [ ] Decide whether the fork needs GitHub Releases, phars, Docker images, updater metadata, or source builds only.
- [ ] Remove unused upstream-only workflows.
- [ ] Enable publishing only with fork-owned credentials and destinations.
- [ ] Add a compact release checklist and rollback note.

### 6. Perform A Real Protocol Update

- [ ] Verify the current Bedrock version and protocol number from current sources.
- [ ] Refresh source monitoring and record exact evidence commits.
- [ ] Use packet evidence or two independent implementations for high-risk fields.
- [ ] Update local protocol/data packages and root integration separately from cleanup.
- [ ] Regenerate data with `composer run update-codegen` and review generated diffs.
- [ ] Run PHPStan, PHPUnit, phar build, version check, and client smoke tests.
- [ ] Record packet/API/plugin/world risks and rollback guidance.

### 7. Triage One Upstream Item

- [ ] Refresh `open-items.json` and `priority-shortlist.json` with the two backlog tools.
- [ ] Open one high-scoring original issue or PR.
- [ ] Classify relevance and create only the smallest actionable fork task or port.
- [ ] Preserve source links and authorship; do not copy whole discussions.

## Dependency Track

Completed: `color`, `errorhandler`, `math`.

Remaining order:

1. `log`
2. `callback-validator`
3. `binaryutils`
4. `nbt`
5. Bedrock data and upgrade schemas
6. `bedrock-protocol`
7. `snooze`, `raklib`, `raklib-ipc`

Import one package at a time. Preserve its license, source layout, Composer metadata, pin, and useful history.
Local ownership does not stop monitoring its canonical PMMP repository.

## Milestones

- **Foundation hardening: active.** Remaining: links, disabled workflows, release checklist, changelog habit, clean PHPStan.
- **Self-contained dependencies: active.** Three packages are local; protocol and network packages remain.
- **Protocol velocity: active.** Evidence rules are ready; one real verified protocol update is still required.
- **Fork releases: planned.** Naming, artifacts, automation, notes, and rollback policy remain.
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
4. Update this file live, including discoveries and deferrals.
5. Commit and push when requested.
