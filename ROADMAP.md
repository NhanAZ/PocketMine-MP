# Roadmap

This is the live state and work queue for NhanAZ's personal PocketMine-MP fork.
Update it during every completed maintenance unit; do not create separate task-context Markdown files.

## Current State

- Fork governance, contribution, security, and production-risk warnings are established.
- `pocketmine/color`, `pocketmine/errorhandler`, `pocketmine/log`, and `pocketmine/math` are local Composer path packages.
- Source monitoring covers the PMMP root, PMMP-owned dependencies, and four protocol reference projects.
- The current root matches `upstream/stable` at `fe9f8bd801530ee23ac8e6fb9d8a1922846d5aff`.
- The source audit has 19 entries and eight reviewed package-drift signals.
- RakLib anti-spoofing cookies were reviewed and deferred until a tagged release or the planned local RakLib import.
- The upstream backlog snapshot contains 450 open items and a scored top-40 shortlist.
- Full repository PHPStan is clean after typing the JSON backlog tools.
- Fork CI health is the top active gate; the latest red runs were PHP-CS-Fixer import order, PHPStan CLI argv handling, and Docker missing local `packages/` path repositories.
- User-facing links were reviewed: fork actions point to `NhanAZ/PocketMine-MP`; PMMP docs, packages, changelog links, and source attribution remain labeled upstream or ecosystem references.

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

- [ ] Decide whether the fork needs GitHub Releases, phars, Docker images, updater metadata, or source builds only.
- [ ] Remove unused upstream-only workflows.
- [ ] Enable publishing only with fork-owned credentials and destinations.
- [ ] Add a compact release checklist and rollback note.

### 3. Perform A Real Protocol Update

- [ ] Verify the current Bedrock version and protocol number from current sources.
- [ ] Refresh source monitoring and record exact evidence commits.
- [ ] Use packet evidence or two independent implementations for high-risk fields.
- [ ] Update local protocol/data packages and root integration separately from cleanup.
- [ ] Regenerate data with `composer run update-codegen` and review generated diffs.
- [ ] Run PHPStan, PHPUnit, phar build, version check, and client smoke tests.
- [ ] Record packet/API/plugin/world risks and rollback guidance.

### 4. Triage One Upstream Item

- [ ] Refresh `open-items.json` and `priority-shortlist.json` with the two backlog tools.
- [ ] Open one high-scoring original issue or PR.
- [ ] Classify relevance and create only the smallest actionable fork task or port.
- [ ] Preserve source links and authorship; do not copy whole discussions.

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

- **Foundation hardening: active.** Remaining: links, disabled workflows, release checklist, and changelog habit.
- **Self-contained dependencies: active.** Four packages are local; protocol and network packages remain.
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
4. If a push touches workflow-covered code, run the closest local checks before pushing.
5. Update this file live, including discoveries and deferrals.
6. Commit and push when requested.
