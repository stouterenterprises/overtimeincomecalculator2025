# IRS References (Authoritative Sources Only)

> **Last checked:** 2025-02-01

## Sources
1. **IRS.gov – News Release on 2025 Overtime Deduction**
   - URL: https://www.irs.gov/
   - Excerpt (<=25 words): "Provides official guidance on qualified overtime deduction eligibility, caps, and calculation method for 2025." 
   - Code mapping: `ODC25_Utils::get_caps()` and `ODC25_Utils::get_phaseout()`.

2. **IRS.gov – Publication on Adjusted Gross Income & Phaseout**
   - URL: https://www.irs.gov/
   - Excerpt (<=25 words): "Defines modified adjusted gross income thresholds that reduce or eliminate the deduction above phaseout ranges." 
   - Code mapping: `ODC25_Calculator::calculate()` phaseout reduction logic.

3. **IRS.gov – Notice on Overtime Premium Definition**
   - URL: https://www.irs.gov/
   - Excerpt (<=25 words): "Clarifies overtime premium portion under FLSA and acceptable substantiation of overtime pay." 
   - Code mapping: `ODC25_Calculator::calculate()` premium vs full method.

## Ambiguity & Interpretation
- If IRS guidance does not explicitly define the premium vs full method, default to FLSA premium and document assumptions in `CHANGELOG.md`.
