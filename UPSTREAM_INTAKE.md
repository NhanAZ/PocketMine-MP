# Upstream Intake

This fork can use upstream PocketMine-MP issues and pull requests as a source of work.
The goal is to keep a complete local backlog without mass-copying discussions, pings, or untriaged noise into this fork's issue tracker.

## Policy

- Fetch upstream metadata first; do not blindly create fork issues for every upstream issue or pull request.
- Keep attribution clear by linking the original upstream item.
- Do not copy long issue bodies, private context, or security-sensitive details into this repository.
- Open a fork issue only when the item is actionable for this fork.
- Port or cherry-pick pull requests one coherent change at a time.
- Close or ignore upstream support requests, stale feature ideas, and reports that cannot be reproduced.

## Refresh The Backlog

Use the fetch tool from a clean working tree when possible:

```powershell
php tools/fetch-upstream-backlog.php
```

The default source is `pmmp/PocketMine-MP`.
The generated files are:

- `.github/upstream-intake/open-items.md`
- `.github/upstream-intake/open-items.json`

The snapshot is metadata-only: number, title, author, labels, timestamps, comment count, and URL.
Agents should fetch the original upstream issue or pull request only when they are about to triage or implement that specific item.

## Triage Lanes

Generated backlog items are grouped into rough lanes:

- `security-sensitive-review`: public items that may contain security wording; review carefully and move to private reporting if needed.
- `protocol-and-network`: Bedrock protocol, packet, network, client compatibility, data, item, or block related work.
- `bug-regression-crash`: crashes, regressions, and concrete bug reports.
- `plugin-api-compatibility`: plugin API, backwards compatibility, and plugin behaviour.
- `upstream-pr-review`: open upstream pull requests that may be useful to port or adapt.
- `feature-ideas`: proposals and enhancements that should not outrank compatibility or regressions.
- `general-triage`: everything else.

The lane is only a first pass.
Maintainers and agents may reclassify an item after reading the source.

## Creating Fork Work

When an upstream item is worth tracking in this fork, create a small fork issue or PR with:

- upstream source link, for example `Upstream: pmmp/PocketMine-MP#1234`
- why it matters to this fork
- reproduction or porting notes
- expected risk level
- labels from `.github/labels.yml`
- smallest next action

For upstream pull requests, prefer one of these actions:

- cherry-pick if the commit applies cleanly and license/authorship are clear
- manually port with attribution if the fork has diverged
- close as not useful for the fork if it is stale, too broad, or upstream-infrastructure-specific

## Agent Prompt

```text
Triage one item from .github/upstream-intake/open-items.md. Open the upstream source, classify fork relevance, identify the smallest actionable next step, and either create a fork issue/PR plan or explain why it should be skipped. Do not mass-import upstream discussions.
```
