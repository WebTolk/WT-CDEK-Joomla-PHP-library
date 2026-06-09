# Evolution Note: Packaging Excludes After `.webtolk` Migration

## Observation

After migrating the project-local development-flow root to `.webtolk`, the shared packager still used the old exclude set and initially included `.webtolk/` in the release ZIP. The workspace also contains `.apidoc.cdek.ru/`, which should not ship in Joomla release packages.

## Local Project Update

`.webtolk/build/package.config.json` now excludes:

- `.webtolk/`
- `.apidoc.cdek.ru/`

## Reusable Knowledge

When a Joomla project migrates from legacy flow roots to `.webtolk`, release package configuration must be checked and updated together with `.gitignore`; otherwise hidden local flow artifacts can enter release archives.
