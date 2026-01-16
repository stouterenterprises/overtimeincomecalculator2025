<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once ODC25_PATH . 'vendor/odc-pdf/ODC25_Pdf.php';

class ODC25_Exporter
{
    public static function register(): void
    {
        add_action('wp_ajax_odc25_export', [self::class, 'handle_export']);
        add_action('wp_ajax_nopriv_odc25_export', [self::class, 'handle_export']);
    }

    public static function handle_export(): void
    {
        check_ajax_referer('odc25_export', 'nonce');

        if (!ODC25_Rate_Limiter::check('export')) {
            wp_send_json_error(['message' => __('Rate limit exceeded. Please wait and try again.', ODC25_TEXT_DOMAIN)], 429);
        }

        $payload = isset($_POST['payload']) ? json_decode(wp_unslash($_POST['payload']), true) : [];
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'json';

        $result = ODC25_Calculator::calculate($payload);
        $packet = self::build_packet($payload, $result);

        switch ($format) {
            case 'csv':
                self::send_csv($packet);
                break;
            case 'html':
                self::send_html($packet);
                break;
            case 'pdf':
                self::send_pdf($packet);
                break;
            default:
                wp_send_json_success($packet);
        }
    }

    public static function build_packet(array $payload, array $result): array
    {
        return [
            'meta' => [
                'generated_at' => current_time('mysql'),
                'plugin_version' => ODC25_VERSION,
                'method' => $result['method'],
                'filing_status' => $result['filing_status'],
                'regular_rate' => $result['regular_rate'],
                'magi' => $result['magi'],
                'notes' => sanitize_textarea_field($payload['notes'] ?? ''),
            ],
            'buckets' => $result['details'],
            'totals' => $result['totals'],
            'caps' => $result['caps'],
            'phaseout' => $result['phaseout'],
            'summary' => self::build_summary($result),
        ];
    }

    public static function build_summary(array $result): array
    {
        return [
            'deduction' => $result['totals']['deduction'],
            'eligible' => $result['totals']['eligible'],
            'cap' => $result['totals']['cap'],
            'phaseout_reduction' => $result['totals']['phaseout_reduction'],
        ];
    }

    private static function send_csv(array $packet): void
    {
        $filename = 'odc25-packet-' . gmdate('Ymd-His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, ['META']);
        foreach ($packet['meta'] as $key => $value) {
            fputcsv($output, [$key, $value]);
        }

        fputcsv($output, []);
        fputcsv($output, ['BUCKETS']);
        fputcsv($output, ['bucket', 'multiplier', 'overtime_pay', 'hours', 'premium']);
        foreach ($packet['buckets'] as $bucket) {
            fputcsv($output, [$bucket['bucket'], $bucket['multiplier'], $bucket['overtime_pay'], $bucket['hours'], $bucket['premium']]);
        }

        fputcsv($output, []);
        fputcsv($output, ['TOTALS']);
        foreach ($packet['totals'] as $key => $value) {
            fputcsv($output, [$key, $value]);
        }

        fclose($output);
        exit;
    }

    private static function send_html(array $packet): void
    {
        header('Content-Type: text/html');
        echo self::render_html($packet);
        exit;
    }

    private static function send_pdf(array $packet): void
    {
        $pdf = new ODC25_Pdf();
        $pdf->add_title('2025 Overtime Deduction Packet');
        $pdf->add_text('Generated: ' . $packet['meta']['generated_at']);
        $pdf->add_text('Prepared by 2025 Overtime Deduction Calculator / ' . $packet['meta']['generated_at']);
        $pdf->add_text('Summary');
        $pdf->add_text('Filing status: ' . $packet['meta']['filing_status']);
        $pdf->add_text('Regular rate: $' . number_format((float) $packet['meta']['regular_rate'], 2));
        $pdf->add_text('Method: ' . $packet['meta']['method']);
        $pdf->add_text('Eligible amount: $' . number_format((float) $packet['totals']['eligible'], 2));
        $pdf->add_text('Cap: $' . number_format((float) $packet['totals']['cap'], 2));
        $pdf->add_text('Phaseout reduction: $' . number_format((float) $packet['totals']['phaseout_reduction'], 2));
        $pdf->add_text('Deduction estimate: $' . number_format((float) $packet['totals']['deduction'], 2));
        $pdf->add_text('Signature: ______________________________');

        $pdf->add_page();
        $pdf->add_title('Detail Ledger');
        $pdf->add_text('Buckets');
        foreach ($packet['buckets'] as $bucket) {
            $line = sprintf(
                'Bucket %d | Multiplier %.2f | OT Pay $%.2f | Hours %.2f | Premium $%.2f',
                $bucket['bucket'],
                $bucket['multiplier'],
                $bucket['overtime_pay'],
                $bucket['hours'],
                $bucket['premium']
            );
            $pdf->add_text($line);
        }

        $pdf_output = $pdf->output();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename=odc25-packet-' . gmdate('Ymd-His') . '.pdf');
        header('Content-Length: ' . strlen($pdf_output));
        echo $pdf_output;
        exit;
    }

    private static function render_html(array $packet): string
    {
        ob_start();
        ?>
        <html>
        <head>
            <meta charset="utf-8" />
            <title>2025 Overtime Deduction Packet</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 24px; color: #1f2937; }
                h1 { font-size: 22px; }
                table { width: 100%; border-collapse: collapse; margin: 16px 0; }
                th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; }
                th { background: #f3f4f6; }
            </style>
        </head>
        <body>
            <h1>2025 Overtime Deduction Packet</h1>
            <p>Generated: <?php echo esc_html($packet['meta']['generated_at']); ?></p>
            <p>Prepared by 2025 Overtime Deduction Calculator / <?php echo esc_html($packet['meta']['generated_at']); ?></p>
            <h2>Summary</h2>
            <table>
                <tbody>
                    <tr><th>Filing Status</th><td><?php echo esc_html($packet['meta']['filing_status']); ?></td></tr>
                    <tr><th>Regular Rate</th><td><?php echo esc_html(number_format((float) $packet['meta']['regular_rate'], 2)); ?></td></tr>
                    <tr><th>Method</th><td><?php echo esc_html($packet['meta']['method']); ?></td></tr>
                    <tr><th>Deduction</th><td><?php echo esc_html(number_format((float) $packet['totals']['deduction'], 2)); ?></td></tr>
                </tbody>
            </table>
            <h2>Buckets</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Multiplier</th>
                        <th>OT Pay</th>
                        <th>Hours</th>
                        <th>Premium</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packet['buckets'] as $bucket) : ?>
                        <tr>
                            <td><?php echo esc_html($bucket['bucket']); ?></td>
                            <td><?php echo esc_html(number_format((float) $bucket['multiplier'], 2)); ?></td>
                            <td><?php echo esc_html(number_format((float) $bucket['overtime_pay'], 2)); ?></td>
                            <td><?php echo esc_html(number_format((float) $bucket['hours'], 2)); ?></td>
                            <td><?php echo esc_html(number_format((float) $bucket['premium'], 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h2>Totals</h2>
            <table>
                <tbody>
                    <?php foreach ($packet['totals'] as $key => $value) : ?>
                        <tr>
                            <th><?php echo esc_html(ucwords(str_replace('_', ' ', $key))); ?></th>
                            <td><?php echo esc_html(number_format((float) $value, 2)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>Signature: ______________________________</p>
        </body>
        </html>
        <?php
        return (string) ob_get_clean();
    }
}
