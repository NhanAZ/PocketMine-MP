# AGENTS.md

This is the durable rule set for AI-assisted work in NhanAZ's personal PocketMine-MP fork.
Keep it short. Active work belongs in `ROADMAP.md`, not here.

## Work Loop

1. Read `ROADMAP.md`, then inspect the code and Git state relevant to the selected task.
2. Choose the smallest ready task unless a confirmed security issue, crash, or current protocol break is more urgent.
3. Keep protocol work, dependency imports, upstream ports, and broad cleanup separate.
4. Follow existing PocketMine-MP architecture, style, and tooling.
5. Run checks proportional to risk and state any skipped or failing checks.
6. Update `ROADMAP.md` live when work is completed, discovered, deferred, or reprioritized.
7. Commit and push each completed unit when the maintainer has requested that workflow.

## Engineering Rules

- Preserve licenses, attribution, original authorship, and useful source history.
- Do not overwrite unrelated user changes or hide generated diffs.
- Treat networking, protocol, world data, security, plugin API, threading, and shutdown code as high risk.
- Add tests for behavioural changes where practical; provide manual checks for gameplay or client compatibility.
- Keep security reports private until a fix is ready.
- AI-generated code is untrusted until reviewed, explained, and tested.

## Sources And Drift

- The fork root is the implementation authority; canonical PMMP is the primary change feed.
- Before source-sensitive work, run `php tools/audit-maintenance-sources.php` with `GITHUB_TOKEN` set and inspect `.github/maintenance-sources/report.json`.
- Record source decisions in `.github/maintenance-sources/reviews.json` as `adopt`, `adapt`, `already-covered`, `defer`, or `reject-with-reason`.
- For dependency imports, compare the lock pin with the canonical branch, verify the license, preserve history, use a Composer path repository, and import one package at a time.
- Upstream backlog JSON is a triage aid. Read the original issue or PR before acting and never mass-create fork issues.
- Add a reviewed upstream issue or PR to `ROADMAP.md` automatically only when it is actionable and ready for this fork.

## Protocol Rules

- Verify the exact Bedrock version and protocol number at task time; never rely on memory.
- Prefer packet captures, canonical PMMP protocol/data changes, and reproducible behaviour.
- Cloudburst Protocol, PowerNukkitX, Dragonfly, and Endstone are corroborating evidence, not drop-in specifications.
- For high-risk packet fields, require a capture or agreement between at least two independent implementations.
- Translate wire behaviour into PMMP-native PHP; do not mechanically convert foreign code.
- Record sources, commits, field order/types/conditions, uncertainty, generated changes, tests, and plugin risks in the issue, PR, or commit.

## Documentation Budget

- Do not create Markdown by default.
- `AGENTS.md` stores durable rules; `ROADMAP.md` stores live state and next work.
- Add other Markdown only for a distinct user, security, release, changelog, or concrete developer-reference need.
- Prefer updating an existing document, code comments, tests, issue/PR text, or structured JSON over creating agent-context files.
- Generated audits and backlog snapshots must be JSON-only.

End tasks with a concise summary of changes, checks, risks, and the next roadmap item.
