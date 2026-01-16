<?php

if (!defined('ABSPATH')) {
    exit;
}

$statuses = ODC25_Utils::get_filing_statuses();
?>
<div class="odc25-calculator" aria-live="polite">
    <h2><?php echo esc_html__('2025 Overtime Deduction Calculator', ODC25_TEXT_DOMAIN); ?></h2>
    <p class="odc25-note"><?php echo esc_html__('Enter the minimum required paystub totals. No personal data is stored on the server. Drafts are saved locally in your browser.', ODC25_TEXT_DOMAIN); ?></p>
    <form class="odc25-form" onsubmit="return false;">
        <div class="odc25-grid">
            <div class="odc25-field">
                <label for="odc25-regular-rate"><?php echo esc_html__('Regular hourly rate (R)', ODC25_TEXT_DOMAIN); ?></label>
                <input id="odc25-regular-rate" type="number" step="0.01" min="0" inputmode="decimal" required />
            </div>
            <div class="odc25-field">
                <label for="odc25-filing-status"><?php echo esc_html__('Filing status', ODC25_TEXT_DOMAIN); ?></label>
                <select id="odc25-filing-status">
                    <?php foreach ($statuses as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="odc25-field">
                <label for="odc25-magi"><?php echo esc_html__('MAGI estimate (optional)', ODC25_TEXT_DOMAIN); ?></label>
                <input id="odc25-magi" type="number" step="0.01" min="0" inputmode="decimal" />
            </div>
            <div class="odc25-field">
                <label><?php echo esc_html__('Calculation method', ODC25_TEXT_DOMAIN); ?></label>
                <div>
                    <label>
                        <input type="radio" name="odc25_method" value="flsa" checked />
                        <?php echo esc_html__('FLSA-only premium (default)', ODC25_TEXT_DOMAIN); ?>
                    </label><br />
                    <label>
                        <input type="radio" name="odc25_method" value="full" />
                        <?php echo esc_html__('Full overtime pay', ODC25_TEXT_DOMAIN); ?>
                    </label>
                </div>
            </div>
        </div>

        <div class="odc25-section">
            <h3><?php echo esc_html__('Overtime buckets', ODC25_TEXT_DOMAIN); ?> <span class="odc25-bucket-count">0/10</span></h3>
            <div class="odc25-buckets"></div>
            <div class="odc25-actions">
                <button type="button" class="button" data-odc25-add-bucket><?php echo esc_html__('Add bucket', ODC25_TEXT_DOMAIN); ?></button>
                <button type="button" class="button" data-odc25-clear><?php echo esc_html__('Clear saved draft', ODC25_TEXT_DOMAIN); ?></button>
            </div>
        </div>

        <div class="odc25-section">
            <div class="odc25-field">
                <label for="odc25-notes"><?php echo esc_html__('Notes (optional)', ODC25_TEXT_DOMAIN); ?></label>
                <textarea id="odc25-notes" rows="3"></textarea>
            </div>
        </div>

        <div class="odc25-section odc25-results is-hidden">
            <h3><?php echo esc_html__('Estimate summary', ODC25_TEXT_DOMAIN); ?></h3>
            <div class="odc25-summary"></div>
            <h4><?php echo esc_html__('Audit trail', ODC25_TEXT_DOMAIN); ?></h4>
            <table class="odc25-audit">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo esc_html__('Multiplier', ODC25_TEXT_DOMAIN); ?></th>
                        <th><?php echo esc_html__('OT Pay', ODC25_TEXT_DOMAIN); ?></th>
                        <th><?php echo esc_html__('Hours', ODC25_TEXT_DOMAIN); ?></th>
                        <th><?php echo esc_html__('Premium', ODC25_TEXT_DOMAIN); ?></th>
                    </tr>
                </thead>
                <tbody class="odc25-audit-body"></tbody>
            </table>
        </div>

        <div class="odc25-section">
            <h3><?php echo esc_html__('Export packet', ODC25_TEXT_DOMAIN); ?></h3>
            <p class="odc25-note"><?php echo esc_html__('Exports are generated on demand and are rate-limited for security.', ODC25_TEXT_DOMAIN); ?></p>
            <div class="odc25-actions">
                <button type="button" class="button button-primary" data-odc25-export="pdf"><?php echo esc_html__('Download PDF', ODC25_TEXT_DOMAIN); ?></button>
                <button type="button" class="button" data-odc25-export="csv"><?php echo esc_html__('Download CSV', ODC25_TEXT_DOMAIN); ?></button>
                <button type="button" class="button" data-odc25-export="json"><?php echo esc_html__('Download JSON', ODC25_TEXT_DOMAIN); ?></button>
                <button type="button" class="button" data-odc25-export="html"><?php echo esc_html__('Printable HTML', ODC25_TEXT_DOMAIN); ?></button>
            </div>
        </div>

        <div class="odc25-section">
            <h3><?php echo esc_html__('Import from packet CSV', ODC25_TEXT_DOMAIN); ?></h3>
            <div class="odc25-field">
                <label for="odc25-import-text"><?php echo esc_html__('Paste CSV', ODC25_TEXT_DOMAIN); ?></label>
                <textarea id="odc25-import-text" rows="4"></textarea>
            </div>
            <div class="odc25-field">
                <label for="odc25-import-file"><?php echo esc_html__('Or upload CSV file', ODC25_TEXT_DOMAIN); ?></label>
                <input id="odc25-import-file" type="file" accept=".csv" />
            </div>
            <div class="odc25-actions">
                <button type="button" class="button" data-odc25-import><?php echo esc_html__('Import CSV', ODC25_TEXT_DOMAIN); ?></button>
                <button type="button" class="button" data-odc25-template><?php echo esc_html__('Download CSV template', ODC25_TEXT_DOMAIN); ?></button>
            </div>
        </div>
    </form>
</div>
