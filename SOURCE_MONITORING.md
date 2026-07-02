# Source Monitoring

This workflow keeps the fork aware of changes in canonical PocketMine-MP, PMMP-owned dependencies, and selected protocol reference projects.
Monitoring a source does not grant it authority over this fork and does not mean every remote change should be merged.

## Authority Order

Use this order when sources disagree:

1. Confirmed security requirements and reproducible Minecraft: Bedrock Edition behaviour.
2. The current fork root, including its documented intentional deviations.
3. Canonical `pmmp/PocketMine-MP` changes that still fit the fork.
4. Canonical PMMP-owned dependency repositories.
5. Independent protocol implementations used as corroborating evidence.

The fork root is the final implementation target.
Canonical upstream remains the primary change feed, but agents must port changes deliberately instead of allowing broad merges to erase fork decisions.

## Monitored Sources

The source manifest is `.github/maintenance-sources/sources.json`.
It contains:

- the canonical PocketMine-MP root and the fork's current upstream baseline
- imported PMMP packages and their pinned source commits
- independent projects used only for protocol research

PMMP-owned packages that are still installed from Composer are discovered automatically from `composer.lock`.
This prevents a newly added dependency from silently escaping the audit.

The current protocol reference set is:

| Project | Primary value | Boundary |
|:--|:--|:--|
| [Cloudburst Protocol](https://github.com/CloudburstMC/Protocol) | Protocol structures, packet fields, and serializer behaviour | Java implementation; translate semantics into PMMP architecture. |
| [PowerNukkitX](https://github.com/PowerNukkitX/PowerNukkitX) | End-to-end server handling and gameplay integration | Java server; do not map classes mechanically. |
| [Dragonfly](https://github.com/df-mc/dragonfly) | Independent packet and server behaviour | Go server; use as corroboration, not a sole authority. |
| [Endstone](https://github.com/EndstoneMC/endstone) | Behaviour and version-support observations from a different server architecture | C++ project; implementation details may not transfer to PMMP. |

Repositories, branches, and declared licenses were verified through the GitHub API when the manifest was created.
The audit refreshes repository metadata so branch or license changes remain visible.

## Refresh Command

Run:

```powershell
$env:GITHUB_TOKEN = gh auth token
php tools/audit-maintenance-sources.php
Remove-Item Env:GITHUB_TOKEN
```

The command updates:

- `.github/maintenance-sources/report.md`
- `.github/maintenance-sources/report.json`

Reviewed classifications are recorded manually in `.github/maintenance-sources/review-notes.md`.

The report records monitored branch heads for canonical sources.
A branch-head difference is a review signal, not proof that the pinned version is obsolete.

## Required Cadence

Refresh the report:

- before a protocol update
- before importing or updating a PMMP-owned dependency
- before a broad upstream sync
- after a large maintenance session
- when the scheduled source-audit workflow reports drift

Agents may reuse a report generated earlier the same day when no source-sensitive work occurred.

## Root Review

For canonical PocketMine-MP:

1. Fetch `upstream/stable`.
2. Confirm the merge base and list commits absent from the fork.
3. Classify each relevant change as `adopt`, `adapt`, `already-covered`, `defer`, or `reject-with-reason`.
4. Port one coherent change at a time and preserve authorship where practical.
5. Record intentional conflicts in `FORK_DEVIATIONS.md`.

Useful commands:

```powershell
git fetch upstream stable
git merge-base HEAD upstream/stable
git log --oneline HEAD..upstream/stable
git log --first-parent --oneline upstream/stable..HEAD
```

Do not use a zero-commit result as proof that dependency repositories or peer protocol projects are unchanged.
They have separate histories and must be checked through the source report.

## Dependency Review

Before importing or updating a package:

1. Compare the lock or local pin with the canonical repository branch, then inspect recent tags and releases manually.
2. Read commits since the pin, especially security, compatibility, and PHP-version changes.
3. Decide whether to preserve the current lock version or update it as a separate reviewed change.
4. Import from the canonical package repository and keep its source commit recorded in the manifest.
5. Refresh the report after changing a package pin.

Local ownership does not freeze an imported package.
Its canonical repository remains a monitored input until this fork explicitly declares an independent package line.

## Scheduled Audit

`.github/workflows/maintenance-source-audit.yml` runs daily and regenerates the reports.
It fails when the generated snapshot differs from the committed snapshot, making remote movement visible without automatically modifying the fork.

After an alert, an agent must inspect the changes before committing a refreshed report.
Never update the report only to make the workflow green without reading the changed sources.
Record the result as `adopt`, `adapt`, `already-covered`, `defer`, or `reject-with-reason` in the review notes.
