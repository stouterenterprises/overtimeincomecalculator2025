<?php

if (!defined('ABSPATH')) {
    exit;
}

class ODC25_Utils
{
    public static function get_filing_statuses(): array
    {
        return [
            'single' => __('Single', ODC25_TEXT_DOMAIN),
            'mfj' => __('Married Filing Jointly', ODC25_TEXT_DOMAIN),
            'mfs' => __('Married Filing Separately', ODC25_TEXT_DOMAIN),
            'hoh' => __('Head of Household', ODC25_TEXT_DOMAIN),
        ];
    }

    public static function get_caps(): array
    {
        $defaults = [
            'single' => 12000,
            'mfj' => 24000,
            'mfs' => 12000,
            'hoh' => 18000,
        ];

        return apply_filters('odc25_caps', $defaults);
    }

    public static function get_phaseout(): array
    {
        $defaults = [
            'single' => ['start' => 100000, 'end' => 150000],
            'mfj' => ['start' => 200000, 'end' => 250000],
            'mfs' => ['start' => 100000, 'end' => 125000],
            'hoh' => ['start' => 150000, 'end' => 200000],
        ];

        return apply_filters('odc25_phaseout', $defaults);
    }

    public static function sanitize_buckets(array $buckets): array
    {
        $clean = [];
        foreach ($buckets as $bucket) {
            $multiplier = isset($bucket['multiplier']) ? (float) $bucket['multiplier'] : 0.0;
            $overtime_pay = isset($bucket['overtime_pay']) ? (float) $bucket['overtime_pay'] : 0.0;
            $hours = isset($bucket['hours']) ? (float) $bucket['hours'] : null;

            if ($multiplier <= 0 || $overtime_pay <= 0) {
                continue;
            }

            $clean[] = [
                'multiplier' => $multiplier,
                'overtime_pay' => $overtime_pay,
                'hours' => $hours,
            ];
        }

        return $clean;
    }
}
