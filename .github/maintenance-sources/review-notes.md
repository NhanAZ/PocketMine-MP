# Maintenance Source Review Notes

These notes record human or agent review of the generated source snapshot.
They are not generated automatically.

## 2026-07-02 Baseline

Snapshot: `.github/maintenance-sources/report.json`

| Source | Classification | Review |
|:--|:--|:--|
| `pmmp/PocketMine-MP` | `already-covered` | Fork baseline and `upstream/stable` are both `fe9f8bd801530ee23ac8e6fb9d8a1922846d5aff`; no root commits are missing. |
| `pmmp/Color` | `defer` | Eleven commits after the pin only update CI, supported test versions, and PHPStan development dependencies. No runtime source change was found. |
| `pmmp/Math` | `defer` | Most of the 18 commits are CI or development updates. Runtime changes include multiplication in place of exponentiation and VoxelRayTrace cleanup; review them in a focused package update instead of silently moving the pin. |
| `pmmp/BedrockBlockUpgradeSchema` | `defer` | The single commit updates `actions/setup-node`; package data is unchanged. |
| `pmmp/Log` | `defer` | Ten commits after the pin only update CI and static-analysis development dependencies. The locked source remains suitable for the planned import. |
| `pmmp/NBT` | `defer` | The single commit adds PHP 8.5 to CI and updates PHPStan; runtime source is unchanged. |
| `pmmp/RakLib` | `adapt` | Commit `7655018631147f0b5d473326fddc2faea105dffe` adds stateless anti-spoofing cookies to the RakNet handshake. It changes packet serialization and server behaviour and needs a focused compatibility and security review. |
| `pmmp/RakLibIpc` | `defer` | Seven commits update CI and static-analysis configuration; runtime IPC behaviour is unchanged. |
| `pmmp/Snooze` | `defer` | Eight commits update CI and static-analysis configuration; runtime behaviour is unchanged. |
| Protocol reference projects | `already-covered` | Current branch heads establish the initial observation baseline. Future movement is evidence for protocol research, not an automatic port request. |

The eight `review-needed` signals in the generated report are therefore accounted for.
Only the RakLib change is promoted to immediate follow-up work; Math runtime changes remain a separate deferred package review.
