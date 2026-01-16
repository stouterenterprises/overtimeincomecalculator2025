<?php

if (!defined('ABSPATH')) {
    exit;
}

class ODC25_Rate_Limiter
{
    public static function check(string $action): bool
    {
        $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        $key = 'odc25_rl_' . md5($action . '|' . $ip);
        $max = (int) apply_filters('odc25_rate_limit_max', 10);
        $window = (int) apply_filters('odc25_rate_limit_window', HOUR_IN_SECONDS);

        $entry = get_transient($key);
        if (!$entry) {
            set_transient($key, ['count' => 1, 'start' => time()], $window);
            return true;
        }

        $count = isset($entry['count']) ? (int) $entry['count'] : 0;
        if ($count >= $max) {
            return false;
        }

        $entry['count'] = $count + 1;
        set_transient($key, $entry, $window);
        return true;
    }
}
