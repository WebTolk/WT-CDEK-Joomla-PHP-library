# Brief

## Task

Load the project-local development flow from `.webtolk`, load and apply Joomla platform knowledge, determine where the project work stopped, summarize what is already done, and fill the required artifacts for the current stage.

## Requested Outcome

A current, artifact-backed status snapshot that explains the active flow root, completed migration work, current stage, missing or residual risks, and next handoff.

## Problem Statement

The active development-flow state existed mostly in `.webtolk` logs and bootstrap context. The current project context was still generic, so the flow needed a project-specific Joomla/PHP package binding before future implementation or assurance work.

## Stakeholders

- Project maintainer: WebTolk / Sergey Tolkachyov.
- Runtime consumers: Joomla sites using the WT CDEK library package.
- Development agents: Codex, PhpStorm MCP, Serena, DevTools, shell fallback when allowed.

## Constraints

- Use project-local `.webtolk` as the active development-flow root.
- Use Joomla platform knowledge before design, implementation, assurance or delivery work.
- Use PhpStorm MCP and Serena before shell for code and symbol inspection where available.
- Fill required artifacts before handoff.
- Do not modify package source code for this status-only task.

## Inputs Provided

- User instruction to load `.webtolk` development flow and platform knowledge.
- `E:/.agents/AGENTS.md` shared instructions.
- `.webtolk/config/config.yaml`, `.webtolk/rules/axioms.md`, `.webtolk/rules/base.md`.
- `.webtolk/logs/task-log.md`, `.webtolk/logs/verification-log.md`, `.webtolk/context/project-context.yaml`.
- Joomla platform contract and toolkit files from `E:/.agents`.

## Assumptions

- Current task is an intake plus investigation/status recovery task, not an implementation task.
- The package source code is intentionally left untouched.
- The currently unknown Joomla test instance is a tracked gap, not a blocker for reporting flow status.

## Success Criteria

- Active stage and next handoff are recorded.
- Required intake and investigation artifacts exist and are populated.
- Project context is bound to this Joomla package.
- Platform knowledge sources are named in the artifacts.
- Shell fallback and permission notes are logged.
