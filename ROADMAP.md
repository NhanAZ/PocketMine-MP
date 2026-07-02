# Roadmap

This roadmap turns the fork into a repeatable maintenance workflow.
It is intentionally practical: each phase can be handled by one or more AI agent prompts, then reviewed by NhanAZ before merge or release.

## Phase 0: Identity and Safety Rails

Goal: make the fork's status, risks, and contribution rules clear.

Todo:

- [x] Add a visible fork warning to the README.
- [x] Add a fork policy.
- [x] Adapt the security policy for this fork.
- [x] Add AI agent workflow rules.
- [x] Update PR metadata to allow responsible AI-assisted work.
- [x] Disable upstream-only automation that depends on private upstream infrastructure.
- [ ] Review all remaining upstream-only links and decide which should stay, change, or be marked as upstream.

Agent prompt:

```text
Audit repository-facing documentation for fork identity. Keep upstream attribution intact, avoid hostile wording, and make sure users can tell this is a personal experimental fork. Return a diff, remaining upstream-only links, tests/checks run, and follow-up tasks.
```

## Phase 1: Reproducible Baseline

Goal: prove the fork can build, test, and release before changing behaviour.

Todo:

- [x] Install dependencies from a clean checkout.
- [x] Run PHPStan.
- [x] Run PHPUnit.
- [x] Build a server phar.
- [x] Verify GitHub Actions still match the fork's needs.
- [x] Document the exact release command sequence.

Baseline notes are recorded in [MAINTAINING.md](MAINTAINING.md).

Agent prompt:

```text
Verify the fork baseline without feature changes. Run dependency install, static analysis, PHPUnit, and phar build if the environment supports them. Fix only repository-specific breakage. Report exact commands, failures, skipped checks, and the smallest follow-up needed.
```

## Phase 2: Dependency Consolidation Plan

Goal: prepare the all-in-one direction without dumping code into the tree blindly.

Todo:

- [x] Inventory Composer dependencies owned by pmmp.
- [x] Classify each dependency as keep-external, import-later, or import-now.
- [x] Decide the repository layout for imported packages.
- [x] Pick an import method that preserves history where practical.
- [x] Create one pilot import plan, likely for a small package before BedrockProtocol.

The consolidation strategy is documented in [DEPENDENCY_CONSOLIDATION.md](DEPENDENCY_CONSOLIDATION.md).
The `pocketmine/color` pilot import and the follow-up `pocketmine/errorhandler` import have been completed using local Composer path packages.

Agent prompt:

```text
Analyze PMMP-owned dependencies in composer.json and propose a staged monorepo import plan. For each dependency, identify namespace, release risk, update frequency, licensing, import difficulty, and recommended phase. Do not import code yet unless explicitly asked.
```

## Phase 3: Protocol Velocity

Goal: make Minecraft: Bedrock Edition protocol updates fast, reviewable, and repeatable.

Todo:

- [x] Document protocol update inputs and generated outputs.
- [x] Map codegen commands and required data files.
- [x] Create a protocol update checklist.
- [x] Add smoke-test notes for joining with a real Bedrock client.
- [x] Track plugin compatibility risks for each protocol update.

Protocol update workflow is documented in [PROTOCOL_UPDATES.md](PROTOCOL_UPDATES.md).

Agent prompt:

```text
Prepare a protocol update for the latest Minecraft: Bedrock Edition version. Identify required BedrockProtocol and BedrockData changes, update generated files, run codegen, run tests, and produce a compatibility/risk note. Do not hide generated diffs.
```

## Phase 4: Community Intake

Goal: accept outside issues and PRs without turning maintenance into chaos.

Todo:

- [ ] Tune issue templates for fork-specific bug reports.
- [ ] Add labels for protocol, regression, security, plugin compatibility, upstream sync, and agent task.
- [ ] Add review expectations for small, medium, and high-risk PRs.
- [ ] Add a changelog habit for merged user-facing changes.
- [ ] Decide how to close duplicate or unsupported requests kindly.

Agent prompt:

```text
Triage this issue or PR for the fork. Classify risk, reproduction quality, affected versions, plugin involvement, security sensitivity, upstream relevance, and next action. If code is needed, make the smallest testable change and report tests run.
```

## Phase 5: Sustainable Maintenance

Goal: prevent vibe-coded growth from making the project impossible to understand.

Todo:

- [ ] Periodically audit large files, duplicated logic, and unclear generated code.
- [ ] Track fork-specific deviations from upstream.
- [ ] Keep an open "next useful tasks" list.
- [ ] Prefer deleting stale experiments over carrying them forever.
- [ ] Revisit whether a dedicated organization is useful after the fork has real activity.

Agent prompt:

```text
Do a maintenance audit of the fork. Identify confusing growth, duplicated logic, stale experiments, untested high-risk areas, and fork-specific deviations from upstream. Propose small cleanup PRs, but do not start broad rewrites without approval.
```
