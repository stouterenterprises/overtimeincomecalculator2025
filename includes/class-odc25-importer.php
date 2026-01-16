<?php

if (!defined('ABSPATH')) {
    exit;
}

class ODC25_Importer
{
    public static function register(): void
    {
        add_action('wp_ajax_odc25_import', [self::class, 'handle_import']);
        add_action('wp_ajax_nopriv_odc25_import', [self::class, 'handle_import']);
    }

    public static function handle_import(): void
    {
        check_ajax_referer('odc25_import', 'nonce');

        $csv = isset($_POST['csv']) ? wp_unslash($_POST['csv']) : '';
        if (empty($csv)) {
            wp_send_json_error(['message' => __('No CSV data provided.', ODC25_TEXT_DOMAIN)], 400);
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($csv));
        $section = '';
        $meta = [];
        $buckets = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $columns = str_getcsv($line);
            $label = strtoupper(trim($columns[0] ?? ''));

            if ($label === 'META') {
                $section = 'meta';
                continue;
            }

            if ($label === 'BUCKETS') {
                $section = 'buckets';
                continue;
            }

            if ($label === 'bucket') {
                continue;
            }

            if ($section === 'meta') {
                $key = sanitize_key($columns[0] ?? '');
                $meta[$key] = sanitize_text_field($columns[1] ?? '');
            }

            if ($section === 'buckets') {
                $buckets[] = [
                    'multiplier' => (float) ($columns[1] ?? 0),
                    'overtime_pay' => (float) ($columns[2] ?? 0),
                    'hours' => isset($columns[3]) ? (float) $columns[3] : null,
                ];
            }
        }

        $payload = [
            'regular_rate' => isset($meta['regular_rate']) ? (float) $meta['regular_rate'] : 0,
            'filing_status' => $meta['filing_status'] ?? 'single',
            'magi' => $meta['magi'] ?? '',
            'notes' => $meta['notes'] ?? '',
            'method' => $meta['method'] ?? 'flsa',
            'buckets' => $buckets,
        ];

        wp_send_json_success(['payload' => $payload]);
    }
}
