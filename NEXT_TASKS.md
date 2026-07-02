# Next Tasks

This file keeps the next useful work visible so the fork does not drift into a large, unclear pile of experiments.
Keep tasks small enough for one focused PR unless explicitly marked otherwise.

## Ready

### Review RakLib Anti-Spoofing Cookies

Reason: the initial source audit found one unpinned runtime/network change, upstream RakLib commit `765501863`, which adds stateless cookies to the RakNet handshake.

Checklist:

- Read the upstream commit and its packet/server changes.
- Check compatibility with the locked RakLib version and PocketMine-MP's current RakLib integration.
- Review the documented OVH compatibility caveat and default rotation behaviour.
- Decide whether to port now, defer until a RakLib release, or import RakLib before adapting it.
- Keep any implementation separate from unrelated dependency imports.

### Restore Full PHPStan Baseline

Reason: the upstream backlog tools added after the last clean baseline currently produce 33 PHPStan errors, which hides regressions in later work.

Checklist:

- Add precise array shapes and iterable value types to `tools/fetch-upstream-backlog.php`.
- Add precise array shapes and iterable value types to `tools/prioritize-upstream-backlog.php`.
- Replace integer-or-false conditions with explicit comparisons.
- Run both tools and confirm their generated JSON and Markdown remain valid.
- Run the full `vendor\bin\phpstan.bat analyse --no-progress` command.

### Import `pocketmine/log`

Reason: small PMMP-owned dependency, but it uses classmap autoloading and should be imported separately from `math`.

Checklist:

- Import `https://github.com/pmmp/Log.git` at the version in `composer.lock` into `packages/log`.
- Add a Composer path repository for `packages/log`.
- Confirm classmap autoload works after clean `composer install`.
- Run checks and phar build.

### Review Remaining Upstream Links

Reason: Phase 0 left this as a known follow-up.

Checklist:

- Search docs and templates for `pmmp.io`, `github.com/pmmp`, `discord.gg`, and `pocketminemp`.
- Keep ecosystem/resource links when useful.
- Mark upstream-only links clearly.
- Replace fork-specific links with `NhanAZ/PocketMine-MP`.

### Replace Disabled Release Workflows

Reason: several upstream-only workflows are intentionally disabled.

Checklist:

- Decide whether fork releases need Docker images, Discord announcements, updater JSON, or Crowdin sync.
- Remove workflows that will not be used.
- Re-enable only workflows backed by fork-owned secrets and infrastructure.

### Triage Upstream Backlog

Reason: upstream issues and pull requests can seed useful fork work without copying the whole discussion into this tracker.

Checklist:

- Refresh `.github/upstream-intake/open-items.md` with `php tools/fetch-upstream-backlog.php`.
- Refresh `.github/upstream-intake/priority-shortlist.md` with `php tools/prioritize-upstream-backlog.php`.
- Pick one high-scoring `protocol-and-network`, `bug-regression-crash`, or `upstream-pr-review` item.
- Open the upstream item and decide whether it is actionable for this fork.
- Create a small fork issue or implementation plan with an upstream source link.
- Do not mass-create fork issues.

## Needs Current Information

### Perform A Real Protocol Update

Reason: protocol velocity is the fork's main practical value.

Checklist:

- Verify the current target Minecraft: Bedrock Edition version and protocol number at task time.
- Follow `PROTOCOL_UPDATES.md`.
- Do not rely on remembered version information.

### Sync Useful Upstream Commits

Reason: upstream may still land fixes worth carrying.

Checklist:

- Fetch upstream.
- Review commits since the last sync point.
- Cherry-pick or port one coherent fix at a time.
- Record conflicts and deviations.

## Maintenance

### Review Maintenance Source Drift

Reason: root, dependency, and peer protocol changes must remain visible before source-sensitive work.

Checklist:

- Run `php tools/audit-maintenance-sources.php` with `GITHUB_TOKEN` set.
- Inspect every changed branch head, then review relevant tags and releases instead of committing the report blindly.
- Record `adopt`, `adapt`, `already-covered`, `defer`, or `reject-with-reason` in `.github/maintenance-sources/review-notes.md`.
- Create a focused task for actionable changes.
- Commit the refreshed report only after review.

### Run Sustainable Maintenance Audit

Reason: keep the fork comprehensible after repeated agent work.

Checklist:

- Follow `SUSTAINABLE_MAINTENANCE.md`.
- Update `FORK_DEVIATIONS.md` if intentional drift changed.
- Update this file with the next three useful tasks.
