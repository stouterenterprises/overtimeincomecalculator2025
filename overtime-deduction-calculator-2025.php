<?php
/**
 * Plugin Name: 2025 Overtime Deduction Calculator
 * Description: Mobile-first calculator and export toolkit for estimating the 2025 qualified overtime deduction.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Author: Overtime Deduction Calculator Team
 * Text Domain: overtime-deduction-calculator-2025
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ODC25_VERSION', '1.0.0');
define('ODC25_PATH', plugin_dir_path(__FILE__));
define('ODC25_URL', plugin_dir_url(__FILE__));
define('ODC25_TEXT_DOMAIN', 'overtime-deduction-calculator-2025');
equire_once ODC25_PATH . 'includes/class-odc25-autoloader.php';
ODC25_Autoloader::register();

ODC25_Plugin::instance();
