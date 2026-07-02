# AGENTS.md

This file defines how AI agents should work in this fork.
The goal is fast, practical maintenance without losing reviewability.

## Project Intent

This is NhanAZ's personal, experimental fork of PocketMine-MP.
It is not official PocketMine-MP.
Agents must keep wording neutral, preserve attribution, and avoid hostile references to upstream maintainers, contributors, or other forks.

## Default Workflow

1. Read the relevant local files before changing code.
2. Keep changes small and focused.
3. Prefer existing PocketMine-MP style, architecture, and tooling.
4. Preserve license headers, copyright notices, and source attribution.
5. Treat protocol, world format, networking, security, and public API changes as high risk.
6. Run the narrowest useful checks first, then broader checks when risk is high.
7. End every task with a concise report: what changed, tests run, remaining risks, and suggested next tasks.

## Allowed Inputs

Agents may be asked to work from:

- Issues or pull requests from this fork.
- Issues, pull requests, commits, or releases from upstream PocketMine-MP.
- Useful ideas from plugins or other forks, when licensing allows reuse.
- Private security reports, emails, or DMs summarized by the maintainer.
- A maintainer's direct idea or experiment.

Issue and pull request triage must follow [COMMUNITY_INTAKE.md](COMMUNITY_INTAKE.md).
Upstream backlog triage must follow [UPSTREAM_INTAKE.md](UPSTREAM_INTAKE.md).
Sustainable maintenance audits must follow [SUSTAINABLE_MAINTENANCE.md](SUSTAINABLE_MAINTENANCE.md), and intentional fork drift must be recorded in [FORK_DEVIATIONS.md](FORK_DEVIATIONS.md).

## AI-Assisted Code Rules

AI assistance is allowed in this fork.
However, agents must not treat generated code as correct just because it compiles.

Agents must:

- Explain the reason for non-trivial code changes.
- Call out assumptions and uncertain behaviour.
- Avoid inventing APIs or protocol details.
- Prefer source-backed changes for Minecraft protocol updates.
- Add or update tests when behaviour can be tested automatically.
- Provide manual test steps for gameplay or client compatibility changes.

## High-Risk Areas

Be extra careful with:

- `src/network/mcpe/`
- packet serialization and deserialization
- runtime IDs, item IDs, block states, and generated data
- world loading, saving, and format upgrades
- authentication, encryption, compression, and connection handling
- plugin API signatures and documented behaviour
- threading, async tasks, and shutdown behaviour

High-risk changes should include a rollback plan or a clear reason why rollback is simple.

Protocol updates must follow [PROTOCOL_UPDATES.md](PROTOCOL_UPDATES.md).
Do not assume the latest Minecraft: Bedrock Edition version from memory; verify it at task time and record the source used.

## Upstream Sync Tasks

When syncing from upstream:

- Record the upstream commit, PR, or issue reference.
- Use the upstream backlog snapshot when selecting issue or pull request work.
- Preserve original authorship where practical.
- Prefer cherry-pick or subtree-style history over copy-paste when possible.
- Document conflicts and fork-specific deviations.
- Run checks related to the touched area.
- Update [NEXT_TASKS.md](NEXT_TASKS.md) when the sync creates or resolves follow-up work.

## Dependency Consolidation Tasks

When importing a dependency into this repository:

- Confirm license compatibility first.
- Identify namespace, Composer package name, source repository, and current version.
- Prefer an import method that preserves useful history.
- Update autoloading and build scripts in the same PR.
- Keep one dependency import per PR unless there is a strong reason to combine them.

## Security Tasks

Do not disclose private vulnerability details in public issues, PRs, or changelogs before a fix is ready.
If a report appears exploitable, prioritize reproduction, impact, affected versions, and a minimal patch.

## Required Final Report Format

Use this shape at the end of each task:

```text
Summary:
- ...

Checks:
- ...

Risks:
- ...

Next:
- ...
```
