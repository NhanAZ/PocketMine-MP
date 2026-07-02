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
Do not enable Docker, Discord, Crowdin, updater, or release publishing until fork-owned credentials and destinations are configured.
