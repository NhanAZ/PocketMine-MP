# Community Intake

This fork is maintained on a best-effort basis.
The goal is to respond practically and keep useful reports moving without creating hard response-time promises.

## Intake Principles

- Be neutral and respectful, including toward upstream PocketMine-MP and other forks.
- Ask for the smallest missing detail that would make the report actionable.
- Prefer reproduction steps, crash dumps, commits, and test plugins over long discussions.
- Keep public security details out of issues and pull requests.
- Close unsupported or duplicate reports kindly and with a short reason.
- Move useful ideas into small, reviewable issues or pull requests.
- Use [UPSTREAM_INTAKE.md](UPSTREAM_INTAKE.md) before turning upstream issues or pull requests into fork work.

## Label Taxonomy

Core labels used by this fork:

| Label | Use |
|:--|:--|
| `Category: Protocol` | Minecraft: Bedrock Edition protocol compatibility, packet serialization, generated Bedrock data. |
| `Type: Regression` | Behaviour worked in an earlier known commit/version and now fails. |
| `Category: Plugin Compatibility` | Behaviour that affects plugin loading, API expectations, or common plugin patterns. |
| `Category: Upstream Sync` | Ports, cherry-picks, or conflict resolution from upstream PocketMine-MP or PMMP-owned packages. |
| `Agent Task` | Small, well-scoped work suitable for an AI agent to investigate or implement. |
| `Security: Private Report Needed` | A public issue appears security-sensitive and should be moved to private reporting. |

Existing upstream-style labels such as `Status: Unconfirmed`, `Status: Reproduced`, `Status: Waiting on Author`, `Resolution: Duplicate`, and `Resolution: Abandoned` remain useful.

## Issue Triage

First pass:

1. Identify the report type: bug, crash, protocol, plugin compatibility, feature, support, security, upstream sync.
2. Check whether the report includes exact version, commit hash, PHP version, OS, plugins, and reproduction steps.
3. Add the most specific category/type labels.
4. If the report may be exploitable, remove public details where possible and ask the reporter to use `SECURITY.md`.
5. If it is actionable, either reproduce it or mark what evidence is still missing.

Action states:

- `Status: Unconfirmed`: report has not been reproduced.
- `Status: Reproduced`: behaviour was reproduced, cause not yet known.
- `Status: Debugged`: cause is known, fix not yet merged.
- `Status: Waiting on Author`: reporter or PR author needs to provide information or changes.
- `Status: Blocked`: depends on another task or upstream information.

Resolution states:

- `Resolution: Fixed`: fixed by a merged commit.
- `Resolution: Duplicate`: same as another issue.
- `Resolution: Cannot Reproduce`: reproduction failed with available information.
- `Resolution: Works As Intended`: current behaviour is intentional.
- `Resolution: Lacks Basic Information`: required details were never provided.
- `Resolution: Abandoned`: PR or issue stopped moving after maintainer feedback.

## Pull Request Review Expectations

Small PRs:

- One bug fix, small docs change, or small generated update.
- One reviewer pass is usually enough.
- Tests may be minimal if the risk is clearly low.

Medium PRs:

- Touches runtime behaviour, generated Bedrock data, Composer dependencies, or multiple related files.
- Needs automated checks and a clear test note.
- Should explain backwards compatibility and rollback risk.

High-risk PRs:

- Protocol updates, world format changes, security fixes, networking, threading, authentication, compression, encryption, or public API changes.
- Must include automated checks, manual testing notes where relevant, and compatibility risk notes.
- Protocol PRs must follow [PROTOCOL_UPDATES.md](PROTOCOL_UPDATES.md).

## Changelog Habit

User-facing changes should update or prepare a changelog note when they affect:

- Minecraft: Bedrock Edition compatibility
- plugin API or plugin behaviour
- world loading/saving
- commands or configuration
- security-sensitive behaviour
- release/distribution workflow

If the exact release target is unclear, put a short release-note draft in the PR description under "Follow-up".

## Kind Closures

Duplicate:

```text
Thanks for the report. This looks like the same issue as #123, so I am closing this one to keep discussion in one place.
Please add any extra reproduction details to the existing issue.
```

Missing information:

```text
Thanks for taking the time to report this. I cannot investigate it yet without exact version information and reproduction steps.
I am closing this for now, but it can be reopened if those details are added.
```

Support request:

```text
This looks like a support request rather than a confirmed bug in the fork.
The issue tracker is kept for actionable bugs and development work, so I am closing it for now.
```

Security:

```text
This may be security-sensitive, so it should not be discussed in a public issue.
Please follow SECURITY.md and send a private report with reproduction details.
```
