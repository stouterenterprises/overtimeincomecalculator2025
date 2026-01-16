<?php

if (!defined('ABSPATH')) {
    exit;
}

class ODC25_Page
{
    public const OPTION_PAGE_ID = 'odc25_page_id';
    public const NOTICE_OPTION = 'odc25_activation_notice';
    public const PAGE_TITLE = '2025 Overtime Deduction Calculator';
    public const SHORTCODE = '[overtime_deduction_2025]';

    public static function register(): void
    {
        add_action('admin_notices', [self::class, 'render_notice']);
    }

    public static function maybe_set_notice(): void
    {
        update_option(self::NOTICE_OPTION, 1);
    }

    public static function maybe_create_page(): int
    {
        $page_id = absint(get_option(self::OPTION_PAGE_ID));
        if ($page_id && get_post_status($page_id)) {
            return $page_id;
        }

        $existing = get_page_by_title(self::PAGE_TITLE, OBJECT, 'page');
        if ($existing && $existing->ID) {
            update_option(self::OPTION_PAGE_ID, $existing->ID);
            return $existing->ID;
        }

        $page_id = wp_insert_post([
            'post_title' => self::PAGE_TITLE,
            'post_content' => self::SHORTCODE,
            'post_status' => 'publish',
            'post_type' => 'page',
        ]);

        if (!is_wp_error($page_id)) {
            update_option(self::OPTION_PAGE_ID, $page_id);
            return (int) $page_id;
        }

        return 0;
    }

    public static function render_notice(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!get_option(self::NOTICE_OPTION)) {
            return;
        }

        delete_option(self::NOTICE_OPTION);

        $page_id = absint(get_option(self::OPTION_PAGE_ID));
        $page_link = $page_id ? get_permalink($page_id) : '';
        $settings_link = admin_url('tools.php?page=odc25-settings');
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php echo esc_html__('2025 Overtime Deduction Calculator is ready.', ODC25_TEXT_DOMAIN); ?>
                <?php if ($page_link) : ?>
                    <a href="<?php echo esc_url($page_link); ?>" target="_blank" rel="noopener">
                        <?php echo esc_html__('Open calculator page', ODC25_TEXT_DOMAIN); ?>
                    </a>
                <?php endif; ?>
                | <a href="<?php echo esc_url($settings_link); ?>">
                    <?php echo esc_html__('Settings', ODC25_TEXT_DOMAIN); ?>
                </a>
            </p>
        </div>
        <?php
    }

    public static function get_page_link(): string
    {
        $page_id = absint(get_option(self::OPTION_PAGE_ID));
        if (!$page_id) {
            return '';
        }
        return (string) get_permalink($page_id);
    }
}
