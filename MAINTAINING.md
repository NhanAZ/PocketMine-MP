# Maintaining This Fork

This document records repeatable maintenance commands for NhanAZ's personal PocketMine-MP fork.

## Baseline Verification

Last verified: 2026-07-02 on `stable` at commit `a170743de838581487bcf91bc7026fe786de752b`.

Environment used:

- PHP 8.2.30 ZTS, PocketMine-oriented Windows build
- Composer 2.9.5
- Submodules checked out
- `vendor/` generated from `composer.lock`

Commands and results:

```powershell
composer install --prefer-dist --no-interaction
```

Result: passed. Composer installed 49 packages including dev dependencies.

```powershell
vendor\bin\phpstan.bat analyse --no-progress
```

Result: passed with no errors. Runtime was about 112 seconds.

```powershell
vendor\bin\phpunit.bat tests\phpunit
```

Result: passed. PHPUnit reported 190 tests and 72503 assertions.

## Local Phar Build

The phar builder refuses to run while dev dependencies are installed, so use the release-style dependency set first.

```powershell
composer install --no-dev --classmap-authoritative --ignore-platform-reqs --no-interaction
php "-dphar.readonly=0" build\server-phar.php --out PocketMine-MP.phar
php PocketMine-MP.phar --version
composer install --prefer-dist --no-interaction
```

Verified result:

- `build/server-phar.php` created `PocketMine-MP.phar`.
- `php PocketMine-MP.phar --version` reported PocketMine-MP 5.44.3+dev for Minecraft: Bedrock Edition v26.30.
- Dev dependencies were restored afterwards for normal development.

On Windows, a fully automated smoke test using piped stdin may not stop the server cleanly because the console reader can ignore the pipeline.
For non-interactive startup smoke tests, prefer GitHub Actions, Docker CI, or a purpose-built test wrapper that can terminate the server process safely.

## GitHub Actions Notes

The main CI workflows remain enabled.
Fork-owned release publishing should be configured before enabling Docker image publishing, Discord release notifications, Crowdin sync, updater API publishing, or upstream-style RestrictedActions dispatches.

## Protocol Updates

Use [PROTOCOL_UPDATES.md](PROTOCOL_UPDATES.md) for Minecraft: Bedrock Edition protocol updates.
Protocol update PRs should include generated diffs, automated check results, client smoke-test notes, and plugin compatibility risk notes.

## Community Intake

Use [COMMUNITY_INTAKE.md](COMMUNITY_INTAKE.md) for issue triage, pull request review expectations, label meanings, changelog habits, and kind closure text.
