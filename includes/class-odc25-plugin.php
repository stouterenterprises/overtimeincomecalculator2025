<?php

if (!defined('ABSPATH')) {
    exit;
}

class ODC25_Plugin
{
    private static ?ODC25_Plugin $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        register_activation_hook(ODC25_PATH . 'overtime-deduction-calculator-2025.php', [$this, 'activate']);
        add_action('init', [$this, 'init']);
        add_action('plugins_loaded', [$this, 'load_textdomain']);
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain(ODC25_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__), 2) . '/languages');
    }

    public function init(): void
    {
        ODC25_Page::register();
        ODC25_Frontend::register();
        ODC25_Admin::register();
        ODC25_Exporter::register();
        ODC25_Importer::register();

        add_shortcode('overtime_deduction_2025', [ODC25_Frontend::class, 'render_shortcode']);

        if (function_exists('register_block_type')) {
            register_block_type(ODC25_PATH . 'blocks/calculator');
        }
    }

    public function activate(): void
    {
        ODC25_Page::maybe_create_page();
        ODC25_Page::maybe_set_notice();
    }
}
