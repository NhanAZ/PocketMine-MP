# Fork Policy

This repository is a personal, community-oriented fork of PocketMine-MP maintained by NhanAZ.
It exists to keep the project usable, experiment with faster maintenance workflows, and accept practical contributions without claiming to replace the official PocketMine-MP project.

## Principles

- Keep the fork neutral and respectful toward upstream maintainers, contributors, and other forks.
- Preserve the LGPL-3.0 license, copyright notices, and attribution.
- Prefer small, reviewable changes over large mixed changes.
- Move quickly on maintenance and protocol compatibility, but do not merge changes that cannot be understood or tested.
- Keep production risk visible. This fork may contain unstable changes, security bugs, or breaking behaviour.
- Prefer one coherent repository for core server maintenance. PMMP-owned dependencies may be imported into this repository over time when doing so reduces release friction.

## Scope

The fork may accept:

- Protocol updates for new Minecraft: Bedrock Edition versions.
- Bug fixes, crash fixes, and compatibility fixes.
- Focused quality-of-life improvements for maintainers, developers, and server owners.
- Carefully reviewed feature work that fits PocketMine-MP's architecture.
- Ports of useful ideas from plugins, upstream issues, upstream pull requests, or other forks when licensing permits.
- Dependency consolidation work that keeps source history and attribution clear where practical.

The fork should avoid:

- Hostile messaging toward upstream or other projects.
- Unrelated rewrites that make future maintenance harder.
- Large feature drops without tests, playtesting notes, or a clear rollback path.
- Obscure vendored code with unclear origin or incompatible licensing.

## Contribution Policy

Pull requests are welcome, including AI-assisted pull requests, provided the author takes responsibility for the result.

Contributors should:

- Explain what changed and why.
- Link related issues, upstream PRs, commits, plugins, or reports when relevant.
- Describe tests performed, including manual in-game testing when PHPUnit cannot cover the behaviour.
- Disclose AI assistance when it materially shaped the code or pull request text.
- Avoid mixing unrelated changes in one PR.
- Keep code and public documentation in English.

AI assistance is allowed, but "AI wrote it" is not a substitute for review.
The contributor and maintainer must be able to explain the code, its risks, and its compatibility impact.

## Upstream Sync

Upstream PocketMine-MP commits may be synced into this fork when useful.
Sync work should preserve upstream commit references where possible and call out conflicts, behaviour changes, or fork-specific deviations.

When a fork change intentionally diverges from upstream, document why in the pull request or commit message.

## Releases

Releases are best-effort and may move faster than upstream when protocol compatibility requires it.
Production users should review changelogs, test on staging servers, and keep backups before upgrading.

## Security

Security reports should follow [SECURITY.md](SECURITY.md).
Do not report exploitable vulnerabilities in public issues.

