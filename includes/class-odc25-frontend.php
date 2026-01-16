<?php

if (!defined('ABSPATH')) {
    exit;
}

class ODC25_Frontend
{
    public static function register(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_assets']);
    }

    public static function enqueue_assets(): void
    {
        if (!self::should_enqueue()) {
            return;
        }

        wp_enqueue_style('odc25-styles', ODC25_URL . 'assets/css/odc25-style.css', [], ODC25_VERSION);
        wp_enqueue_script('odc25-app', ODC25_URL . 'assets/js/odc25-app.js', ['wp-element'], ODC25_VERSION, true);

        $data = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'export_nonce' => wp_create_nonce('odc25_export'),
            'import_nonce' => wp_create_nonce('odc25_import'),
            'caps' => ODC25_Utils::get_caps(),
            'phaseout' => ODC25_Utils::get_phaseout(),
            'statuses' => ODC25_Utils::get_filing_statuses(),
            'page_link' => ODC25_Page::get_page_link(),
        ];

        wp_localize_script('odc25-app', 'odc25Data', $data);
    }

    public static function should_enqueue(): bool
    {
        if (is_admin()) {
            return false;
        }

        if (is_singular()) {
            $post = get_post();
            if ($post && (has_shortcode($post->post_content, 'overtime_deduction_2025') || has_block('odc25/calculator', $post))) {
                return true;
            }
        }

        return false;
    }

    public static function render_shortcode(): string
    {
        ob_start();
        include ODC25_PATH . 'templates/calculator.php';
        return (string) ob_get_clean();
    }
}
