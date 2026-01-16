# AI Context Rules

- Minimum inputs only: regular rate, filing status, overtime buckets (multiplier + YTD OT pay). Optional hours, MAGI, notes.
- Never hardcode permalinks; always use stored page ID and `get_permalink()`.
- Preserve zero-build setup and bundled vendor libraries.
- Update IRS references and "Last checked" dates in `REFERENCES_IRS.md`.
- Keep exports nonce-protected and rate-limited.
