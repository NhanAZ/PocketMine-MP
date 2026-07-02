# Maintaining This Fork

This is a command reference for local development and release checks.
Agent rules and live work are kept in `AGENTS.md` and `ROADMAP.md`.

## Development Checks

```powershell
composer install --prefer-dist --no-interaction
composer validate
vendor\bin\phpstan.bat analyse --no-progress
vendor\bin\phpunit.bat tests\phpunit
```

Full PHPStan and targeted analysis for changed code must pass before merging.

## Phar Build

The phar builder requires the release dependency set:

```powershell
composer install --no-dev --classmap-authoritative --ignore-platform-reqs --no-interaction
php "-dphar.readonly=0" build\server-phar.php --out PocketMine-MP.phar
php PocketMine-MP.phar --version
composer install --prefer-dist --no-interaction
```

Restore development dependencies even when a build or version check fails.
On Windows, avoid piped-stdin server smoke tests because the console reader may not stop cleanly.

## Maintenance Data

```powershell
$env:GITHUB_TOKEN = gh auth token
php tools/audit-maintenance-sources.php
php tools/fetch-upstream-backlog.php
php tools/prioritize-upstream-backlog.php
Remove-Item Env:GITHUB_TOKEN
```

These commands write JSON under `.github/maintenance-sources/` and `.github/upstream-intake/`.
Inspect changed sources or upstream items before updating snapshots or creating work.

## Publishing

Main CI remains enabled.
Current release mode is source builds and local phar builds only.
GitHub Releases, Docker publishing, updater metadata, Discord announcements, Crowdin automation, branch sync, and upstream RestrictedActions-style release workflows remain disabled.

Before publishing anything manually:

1. Confirm CI and Docker image CI are green on the exact commit.
2. Confirm the changelog, version, supported Bedrock version, and rollback notes are ready.
3. Build a phar with the release dependency set and run `php PocketMine-MP.phar --version`.
4. Smoke-test on a staging server with the target Bedrock client and representative plugins.
5. Publish only to fork-owned destinations, then watch issues and Actions for regressions.

Rollback means unpublishing or marking the release unsafe, deleting or moving a bad tag when appropriate, reverting the release commit, and posting a short warning with the last known good commit.
