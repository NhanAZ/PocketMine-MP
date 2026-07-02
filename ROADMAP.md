# Roadmap

This is the living roadmap for NhanAZ's personal PocketMine-MP fork.
The bootstrap phases are complete, so this file now focuses on what should happen next and how agents should choose work without growing the fork into an unclear pile of experiments.

This roadmap is not a promise of support, a criticism of upstream, or a release guarantee.
Concrete one-task work items live in [NEXT_TASKS.md](NEXT_TASKS.md).

## Current State

Completed foundation:

- Fork identity, production warning, contribution rules, and security policy are documented.
- AI agent rules are documented in [AGENTS.md](AGENTS.md).
- Baseline verification and release command notes are documented in [MAINTAINING.md](MAINTAINING.md).
- Community issue and PR intake is documented in [COMMUNITY_INTAKE.md](COMMUNITY_INTAKE.md).
- Upstream issue and pull request intake is documented in [UPSTREAM_INTAKE.md](UPSTREAM_INTAKE.md).
- Protocol update workflow is documented in [PROTOCOL_UPDATES.md](PROTOCOL_UPDATES.md).
- Sustainable maintenance audits are documented in [SUSTAINABLE_MAINTENANCE.md](SUSTAINABLE_MAINTENANCE.md).
- Intentional fork drift is tracked in [FORK_DEVIATIONS.md](FORK_DEVIATIONS.md).
- `pocketmine/color` and `pocketmine/errorhandler` are imported as local Composer path packages.

Current direction:

- Keep the fork personal and practical.
- Preserve upstream attribution and licenses.
- Merge useful outside reports and PRs quickly when they are small, reproducible, and reviewable.
- Move PMMP-owned dependencies into this repository gradually.
- Make protocol updates faster, but only with source-backed verification.

## Decision Rules

When choosing the next task, prefer work in this order:

1. Fix a confirmed security issue or crash regression.
2. Restore compatibility with the current Minecraft: Bedrock Edition protocol after verifying the target version at task time.
3. Import or stabilize one PMMP-owned dependency.
4. Sync one useful upstream fix.
5. Triage one useful upstream issue or pull request into fork work.
6. Improve community intake, release notes, or maintenance clarity.
7. Delete stale experiments or reduce confusing fork drift.

Do not combine unrelated high-risk work.
Protocol updates, dependency imports, and broad cleanup should be separate commits or PRs.

## Milestone 1: Foundation Hardening

Goal: make the fork trustworthy enough for repeated solo and AI-assisted maintenance.

Status: active.

Deliverables:

- [ ] Review remaining upstream-only links and mark which are upstream resources, ecosystem links, or fork-owned links.
- [ ] Decide which disabled upstream workflows should be removed, replaced, or kept disabled.
- [ ] Add a lightweight release checklist for fork builds.
- [ ] Add a changelog habit for merged user-facing changes.
- [ ] Run a sustainable maintenance audit after every large agent session.

Done when:

- A new contributor or AI agent can tell what is fork-owned, what is upstream-owned, and what checks are expected before release.

## Milestone 2: Self-Contained PMMP Dependencies

Goal: reduce external PMMP-owned repository dependency without a risky one-shot vendor dump.

Status: active.

Import order:

1. `pocketmine/math`
2. `pocketmine/log`
3. `pocketmine/callback-validator`
4. `pocketmine/binaryutils`
5. `pocketmine/nbt`
6. Bedrock data and upgrade schema packages
7. `pocketmine/bedrock-protocol`
8. `pocketmine/snooze`, `pocketmine/raklib`, and `pocketmine/raklib-ipc`

Rules:

- Import one dependency at a time unless there is a strong technical reason.
- Preserve license files, source layout, Composer metadata, and useful history where practical.
- Use Composer path repositories first.
- Run broader checks for packages touching protocol, NBT, threading, networking, or generated data.
- Update [DEPENDENCY_CONSOLIDATION.md](DEPENDENCY_CONSOLIDATION.md) and [FORK_DEVIATIONS.md](FORK_DEVIATIONS.md) after each import.

Done when:

- PMMP-owned dependencies needed for protocol velocity are available inside this repository and can be updated without waiting on external fork logistics.

## Milestone 3: Protocol Velocity

Goal: make Minecraft: Bedrock Edition compatibility updates fast and reviewable.

Status: planned, with workflow ready.

Deliverables:

- [ ] Perform one real protocol update using [PROTOCOL_UPDATES.md](PROTOCOL_UPDATES.md).
- [ ] Record the exact Bedrock version, protocol number, sources used, generated files changed, and smoke-test result.
- [ ] Keep generated data diffs reviewable by separating them from unrelated cleanup.
- [ ] Document plugin compatibility risks for each protocol update.
- [ ] Identify which imported packages must become first-class protocol update targets.

Rules:

- Do not rely on remembered protocol numbers or Minecraft versions.
- Verify current target information at task time.
- Prefer source-backed changes from BedrockProtocol, BedrockData, upstream commits, official release information, or reproducible packet/data observations.

Done when:

- The fork has completed at least one protocol update with documented verification and a reproducible agent workflow.

## Milestone 4: Fork Releases

Goal: make fork builds repeatable without pretending to be official PocketMine-MP.

Status: planned.

Deliverables:

- [ ] Decide release naming and version suffix policy.
- [ ] Decide whether fork releases need GitHub Releases, phar artifacts, Docker images, updater metadata, or only source builds.
- [ ] Replace upstream-only release automation with fork-owned automation where useful.
- [ ] Add release notes that clearly separate upstream syncs, fork fixes, protocol updates, and known risks.
- [ ] Document rollback guidance for server owners testing the fork.

Done when:

- A maintainer can produce a fork release from a clean checkout and users can understand the risk level before trying it.

## Milestone 5: Community Throughput

Goal: accept reports and PRs quickly without turning maintenance into chaos.

Status: planned.

Deliverables:

- [ ] Use labels from `.github/labels.yml` consistently.
- [ ] Refresh the upstream backlog snapshot regularly using [UPSTREAM_INTAKE.md](UPSTREAM_INTAKE.md).
- [ ] Keep the upstream priority shortlist fresh before starting triage batches.
- [ ] Convert useful upstream issues and pull requests into small fork issues or PRs.
- [ ] Keep issue responses short, neutral, and action-oriented.
- [ ] Convert good plugin compatibility reports into reproducible tests or documented compatibility notes.
- [ ] Accept small PRs quickly when checks pass and risk is clear.
- [ ] Keep unsupported requests kind, brief, and closed when they do not serve the fork.

Done when:

- The fork can process outside reports without losing focus on protocol updates and maintainability.

## Parking Lot

Ideas that should not distract from active milestones yet:

- Moving the fork from the personal account to a dedicated organization.
- Replacing Composer path packages with direct root autoload mappings.
- Building large fork-specific branding or website material.
- Broad rewrites of networking, world storage, or plugin API internals.
- Importing third-party dependencies that are not PMMP-owned.

Revisit the organization question only after the fork has sustained real outside activity, repeated releases, or multiple trusted maintainers.

## Agent Prompts

Roadmap selection:

```text
Choose the next task from ROADMAP.md and NEXT_TASKS.md. Prefer the smallest task that moves an active milestone forward. Do not combine protocol updates, dependency imports, and broad cleanup. Report why this task is next, what files changed, checks run, and the next useful follow-up.
```

Dependency import:

```text
Import one PMMP-owned dependency using DEPENDENCY_CONSOLIDATION.md. Preserve license and source metadata, use a Composer path repository, update the lock file narrowly, run appropriate checks, and update FORK_DEVIATIONS.md and NEXT_TASKS.md.
```

Protocol update:

```text
Prepare a protocol update using PROTOCOL_UPDATES.md. Verify the current target Minecraft: Bedrock Edition version and protocol number at task time, identify source-backed data changes, keep generated diffs visible, run checks, and report manual client smoke-test steps.
```
