# Scope

## In Scope

- Load and interpret `.webtolk` development-flow configuration, rules, logs and current cursor.
- Load Joomla platform contract and required Joomla toolkit knowledge.
- Identify the last completed work recorded in flow logs and memory.
- Create required artifacts for intake, orchestration and investigation/status recovery.
- Update project context from bootstrap values to project-specific values.
- Log tool fallback and artifact updates.

## Out Of Scope

- Source code changes in `lib_webtolk_wtcdek`, `plg_system_wtcdek`, `plg_task_updatewtcdekdata`, `script.php`, or manifests.
- Runtime browser or Joomla stand verification.
- Release packaging or version bumping.
- CDEK API behavior changes.

## Affected Areas

- `.webtolk/context/project-context.yaml`
- `.webtolk/logs/task-log.md`
- `.webtolk/logs/agent-log.md`
- `.webtolk/logs/verification-log.md`
- `.webtolk/logs/tool-telemetry.ndjson`
- `docs/briefs/`
- `docs/reports/`

## Non-Goals

- Do not restore the old `.agents` root.
- Do not delete legacy audit snapshots inside `.webtolk`.
- Do not infer a Joomla test instance that is not recorded in the project context.
- Do not run package build or QA gates without a separate implementation/release task.

## Risk Boundaries

- `.webtolk` is ignored by git, so flow artifacts are local project state.
- `docs/briefs` and `docs/reports` directory creation required elevated permissions due to local ACL behavior.
- Shell was used as fallback for bootstrap/file-system inspection because flow files are not PHP symbols and PhpStorm MCP discovery was not initially available.

## Required Artifacts

- `brief`: `docs/briefs/2026-06-09-flow-status-brief.md`
- `scope`: `docs/briefs/2026-06-09-flow-status-scope.md`
- `stage-decision`: `docs/reports/2026-06-09-flow-stage-decision.md`
- `artifact-index`: `docs/reports/2026-06-09-artifact-index.md`
- `investigation-report`: `docs/reports/2026-06-09-flow-status-investigation-report.md`
- `impact-analysis`: `docs/reports/2026-06-09-flow-status-impact-analysis.md`

## Exit Criteria

- User can resume from `docs/reports/2026-06-09-flow-stage-decision.md`.
- Logs show what was loaded, what was created, and what remains unknown.
- Future stages can rely on `.webtolk/context/project-context.yaml` instead of bootstrap defaults.
