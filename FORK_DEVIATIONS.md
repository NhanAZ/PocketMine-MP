# Fork Deviations

This file tracks intentional differences from upstream PocketMine-MP.
It is not a criticism of upstream; it is a map for keeping this fork maintainable.

Baseline checked on 2026-07-02:

- Upstream baseline: `upstream/stable` at `fe9f8bd801530ee23ac8e6fb9d8a1922846d5aff`
- Fork branch: `stable`
- Fork head at audit time: `931025935392e3c0bcb85390a19722a7e8282531`

Use first-parent history for fork-level audit because package subtree imports include their original histories.

```powershell
git log --first-parent --oneline upstream/stable..HEAD
```

Current first-parent fork commits:

- `931025935` Add community intake workflow
- `c90650f95` Document protocol update workflow
- `3f0d4c39a` Use local path package for pocketmine errorhandler
- `4290d7d85` Import pocketmine/errorhandler package
- `60f77a942` Use local path package for pocketmine color
- `e2261e57e` Import pocketmine/color package
- `b0ba209a3` Plan dependency consolidation strategy
- `f8993a573` Document baseline verification workflow
- `a170743de` Establish fork governance and agent workflow

## Intentional Deviations

### Fork Identity and Governance

Files:

- `README.md`
- `FORK_POLICY.md`
- `SECURITY.md`
- `CONTRIBUTING.md`
- `AGENTS.md`
- `ROADMAP.md`
- `MAINTAINING.md`

Reason:

The repository is maintained as NhanAZ's personal experimental fork.
The docs make the fork status explicit, keep attribution, warn production users, and define AI-assisted maintenance rules.

### Upstream-Only Automation Disabled

Files:

- `.github/workflows/branch-sync-cron-trigger.yml`
- `.github/workflows/crowdin-download-cron-trigger.yml`
- `.github/workflows/crowdin-upload-trigger.yml`
- `.github/workflows/discord-release-notify.yml`
- `.github/workflows/docker-image-publish.yml`
- `.github/workflows/draft-release.yml`
- `.github/workflows/team-pr-auto-approve.yml`
- `.github/workflows/update-updater-api.yml`
- `.github/workflows/support.yml`
- `.github/FUNDING.yml`
- `.github/CODEOWNERS`

Reason:

These upstream workflows depend on upstream-owned secrets, repositories, Discord channels, Docker credentials, or release infrastructure.
They are disabled or redirected until fork-owned infrastructure exists.

### Community Intake

Files:

- `COMMUNITY_INTAKE.md`
- `.github/ISSUE_TEMPLATE/*`
- `.github/PULL_REQUEST_TEMPLATE.md`
- `.github/labels.yml`

Reason:

The fork accepts outside reports and PRs directly.
Templates and labels guide protocol reports, plugin compatibility reports, regressions, security redirects, and AI-agent-sized tasks.

### Protocol Update Workflow

Files:

- `PROTOCOL_UPDATES.md`

Reason:

Protocol updates are a core fork goal.
The workflow documents inputs, generated outputs, codegen, automated checks, client smoke tests, and plugin compatibility risk notes.

### Dependency Consolidation

Files:

- `DEPENDENCY_CONSOLIDATION.md`
- `composer.json`
- `composer.lock`
- `packages/color/`
- `packages/errorhandler/`
- `packages/math/`

Reason:

The fork is moving PMMP-owned dependencies into this repository using Composer path packages first.
`pocketmine/color`, `pocketmine/errorhandler`, and `pocketmine/math` are imported and wired locally.
The math package is pinned to upstream commit `dc132d93595b32e9f210d78b3c8d43c662a5edbf` (version `1.0.0`).
Risk is low because its public package version and autoload namespace are unchanged; rollback is the math subtree import plus its path-repository wiring commit.

## Review Later

- Remaining upstream links in docs should be reviewed and either kept as ecosystem links or marked as upstream resources.
- Disabled release/publishing workflows should eventually be replaced by fork-owned workflows or removed.
- Imported package `.github` folders may be kept for source fidelity, but should be revisited if they confuse repository-wide GitHub tooling.
- `VersionInfo::GITHUB_URL` still points to upstream; decide whether this should change when fork releases begin.

## Rule For New Deviations

When a change intentionally diverges from upstream, add:

- files or area
- reason
- risk level
- rollback note
- related issue, PR, or commit
