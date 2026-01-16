# 2025 Overtime Deduction Calculator

A production-ready WordPress plugin that embeds a mobile-first calculator for estimating the 2025 qualified overtime deduction, producing audit-ready exports, and supporting CSV imports. No build steps required.

## Quick Install
1. Download the repository or release ZIP.
2. Upload the `overtime-deduction-calculator-2025` folder to `/wp-content/plugins/`.
3. Activate **2025 Overtime Deduction Calculator**.
4. Visit the auto-created page titled **2025 Overtime Deduction Calculator**.

## Shortcode
`[overtime_deduction_2025]`

## Block
Search for **2025 Overtime Deduction Calculator** in the block editor.

## Features
- Minimum-input workflow: regular rate, filing status, overtime buckets.
- Up to 10 overtime buckets with multipliers and YTD totals.
- Optional MAGI phaseout estimate and notes.
- FLSA-only premium method (default) or full overtime pay comparison.
- Export packet: PDF, CSV, JSON, printable HTML.
- Import from single CSV packet.
- No server storage by default; local browser draft storage only.

## Filters
- `odc25_caps` to adjust deduction caps.
- `odc25_phaseout` to adjust phaseout ranges.
- `odc25_rate_limit_max` and `odc25_rate_limit_window` to adjust export rate limits.

## Support
See `RELEASE.md` for troubleshooting guidance.
