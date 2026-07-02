# Sustainable Maintenance

This fork should stay understandable after many small human and AI-assisted changes.
The purpose of this document is to make maintenance audits repeatable and to prevent useful experiments from becoming permanent clutter.

## Current Audit Snapshot

Last audit: 2026-07-02 on `stable`.

Local snapshot:

- Tracked files: 1864
- Imported local packages under `packages/`: `color`, `errorhandler`, `math`
- GitHub workflow files: 18
- PHP `TODO`/`FIXME`/`HACK` markers in `src/` and `packages/`: 570
- PHP `TODO`/`FIXME`/`HACK` markers in `src/network/mcpe` and `src/data/bedrock`: 147

Fork-level divergence from upstream should be read with first-parent history because subtree imports include package histories.

```powershell
git fetch upstream stable
git log --first-parent --oneline upstream/stable..HEAD
git diff --name-status upstream/stable...HEAD
```

## Audit Cadence

Run this audit:

- before protocol update releases
- after importing or removing dependencies
- after large AI-assisted work
- at least once per month while the fork is active

## Audit Checklist

1. Confirm workspace state:

```powershell
git status --short --ignored
Get-Process php -ErrorAction SilentlyContinue
```

Only expected ignored output should remain, usually `vendor/`.

2. Refresh upstream view:

```powershell
git fetch upstream stable
git log --first-parent --oneline upstream/stable..HEAD
git rev-list --left-right --count upstream/stable...HEAD
```

Use `--first-parent` for fork-level history.
Use normal history when auditing imported package history.
Then refresh [SOURCE_MONITORING.md](SOURCE_MONITORING.md) so canonical dependency and protocol-reference changes are not hidden by a clean root comparison.

3. Check intentional deviations:

```powershell
git diff --name-status upstream/stable...HEAD
```

Compare the result to [FORK_DEVIATIONS.md](FORK_DEVIATIONS.md).
New differences should be added there if intentional, or converted into cleanup tasks if accidental.

4. Check repository size and hotspots:

```powershell
Get-ChildItem -Recurse -File -Force |
  Where-Object { $_.FullName -notmatch '\\.git\\|\\vendor\\' } |
  Sort-Object Length -Descending |
  Select-Object -First 25 @{Name='SizeKB';Expression={[math]::Round($_.Length/1KB,1)}}, FullName

(rg -n "TODO|FIXME|HACK" src packages -g "*.php" | Measure-Object).Count
(rg -n "TODO|FIXME|HACK" src\network\mcpe src\data\bedrock -g "*.php" | Measure-Object).Count
```

Large generated files are expected.
Large handwritten files, duplicated conversion logic, and growing network/protocol workarounds should become review tasks.

5. Check stale experiments:

- unfinished files under `tools/`
- temporary scripts not referenced by docs
- disabled workflows that should either be re-enabled for the fork or removed
- imported packages that are no longer wired through Composer
- local artifacts such as phars, logs, plugin test output, or temporary data directories
- whether repository activity has become large enough to justify moving from a personal account to a dedicated organization

6. Check dependency imports:

```powershell
git ls-files packages
composer show pocketmine/* --locked
composer validate
```

Local package imports should match [DEPENDENCY_CONSOLIDATION.md](DEPENDENCY_CONSOLIDATION.md).
Their source pins should also match `.github/maintenance-sources/sources.json`.

7. Run verification proportional to risk:

- docs-only audit updates: `git diff --check`
- Composer or package wiring: `composer validate`, PHPStan, PHPUnit, phar build
- protocol/data work: follow [PROTOCOL_UPDATES.md](PROTOCOL_UPDATES.md)

## Cleanup Rules

- Do not mix broad cleanup with protocol updates.
- Do not rewrite a large subsystem only because it looks messy.
- Prefer small cleanup PRs with one reason each.
- Delete stale experiments once their purpose is gone.
- Keep generated files generated; improve the generator instead of hand-editing generated output.
- Record intentional fork drift in [FORK_DEVIATIONS.md](FORK_DEVIATIONS.md).
- Keep [NEXT_TASKS.md](NEXT_TASKS.md) short enough that the next agent can choose a useful task quickly.

## AI Agent Maintenance Prompt

```text
Run a sustainable maintenance audit. Use SUSTAINABLE_MAINTENANCE.md, compare fork drift against FORK_DEVIATIONS.md, update NEXT_TASKS.md with small actionable follow-ups, and do not start broad refactors. Report commands run, findings, skipped checks, and the next three useful tasks.
```
