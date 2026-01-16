<?php

if (!defined('ABSPATH')) {
    exit;
}

class ODC25_Admin
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'register_menu']);
    }

    public static function register_menu(): void
    {
        add_management_page(
            __('2025 Overtime Deduction Calculator', ODC25_TEXT_DOMAIN),
            __('2025 Overtime Deduction Calculator', ODC25_TEXT_DOMAIN),
            'manage_options',
            'odc25-settings',
            [self::class, 'render_settings']
        );
    }

    public static function render_settings(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['odc25_recreate']) && check_admin_referer('odc25_recreate')) {
            $page_id = ODC25_Page::maybe_create_page();
            if ($page_id) {
                add_settings_error('odc25', 'odc25_recreate', __('Calculator page recreated.', ODC25_TEXT_DOMAIN), 'updated');
            }
        }

        settings_errors('odc25');

        $page_id = absint(get_option(ODC25_Page::OPTION_PAGE_ID));
        $page_link = $page_id ? get_permalink($page_id) : '';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('2025 Overtime Deduction Calculator', ODC25_TEXT_DOMAIN); ?></h1>
            <p><?php echo esc_html__('Use this panel to open or recreate the calculator page.', ODC25_TEXT_DOMAIN); ?></p>
            <p>
                <?php if ($page_link) : ?>
                    <a class="button button-primary" href="<?php echo esc_url($page_link); ?>" target="_blank" rel="noopener">
                        <?php echo esc_html__('Open Calculator Page', ODC25_TEXT_DOMAIN); ?>
                    </a>
                <?php endif; ?>
                <a class="button" href="<?php echo esc_url(admin_url('post.php?post=' . $page_id . '&action=edit')); ?>">
                    <?php echo esc_html__('Edit Page', ODC25_TEXT_DOMAIN); ?>
                </a>
            </p>
            <form method="post">
                <?php wp_nonce_field('odc25_recreate'); ?>
                <p>
                    <button class="button" name="odc25_recreate" value="1">
                        <?php echo esc_html__('Recreate Calculator Page', ODC25_TEXT_DOMAIN); ?>
                    </button>
                </p>
            </form>
            <hr />
            <h2><?php echo esc_html__('Export rate limits', ODC25_TEXT_DOMAIN); ?></h2>
            <p><?php echo esc_html__('Exports are rate-limited to protect your site. You can adjust limits with filters.', ODC25_TEXT_DOMAIN); ?></p>
            <code>odc25_rate_limit_max</code> / <code>odc25_rate_limit_window</code>
        </div>
        <?php
    }
}
