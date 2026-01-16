# Build Instructions

This plugin ships pre-built assets and bundled vendor code. No build steps are required for end users.

## Maintainer Build Steps
1. Update code and documentation as needed.
2. Run any manual tests in `/tests/manual-test.php`.
3. Use `scripts/build-zip.php` to generate a release ZIP.

## AI UPDATE PROTOCOL (MANDATORY)
1. Verify calculations via `/tests/manual-test.php` expected outputs.
2. Re-check IRS sources in `REFERENCES_IRS.md` and update the “Last checked” date.
3. If IRS caps/thresholds/definitions change, update code constants and documentation, and add a changelog entry.
4. Ensure no hardcoded permalinks; page ID safety preserved.
5. Bump the version and rebuild the ZIP-ready structure.
