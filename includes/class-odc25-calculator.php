<?php

if (!defined('ABSPATH')) {
    exit;
}

class ODC25_Calculator
{
    public static function calculate(array $payload): array
    {
        $regular_rate = isset($payload['regular_rate']) ? (float) $payload['regular_rate'] : 0.0;
        $filing_status = isset($payload['filing_status']) ? sanitize_text_field($payload['filing_status']) : 'single';
        $method = isset($payload['method']) ? sanitize_text_field($payload['method']) : 'flsa';
        $magi = isset($payload['magi']) && $payload['magi'] !== '' ? (float) $payload['magi'] : null;

        $buckets = ODC25_Utils::sanitize_buckets($payload['buckets'] ?? []);
        $caps = ODC25_Utils::get_caps();
        $phaseout = ODC25_Utils::get_phaseout();

        $details = [];
        $total_premium = 0.0;
        $total_overtime_pay = 0.0;

        foreach ($buckets as $index => $bucket) {
            $multiplier = (float) $bucket['multiplier'];
            $overtime_pay = (float) $bucket['overtime_pay'];
            $hours = $bucket['hours'];

            if ($hours === null || $hours <= 0) {
                $hours = $regular_rate > 0 ? $overtime_pay / ($regular_rate * $multiplier) : 0.0;
            }

            $premium = $regular_rate * max(0, $multiplier - 1) * $hours;
            $total_premium += $premium;
            $total_overtime_pay += $overtime_pay;

            $details[] = [
                'bucket' => $index + 1,
                'multiplier' => $multiplier,
                'overtime_pay' => $overtime_pay,
                'hours' => $hours,
                'premium' => $premium,
            ];
        }

        $eligible = $method === 'full' ? $total_overtime_pay : $total_premium;
        $cap = $caps[$filing_status] ?? 0.0;
        $capped = $cap > 0 ? min($eligible, $cap) : $eligible;

        $phaseout_reduction = 0.0;
        $phaseout_info = $phaseout[$filing_status] ?? ['start' => 0, 'end' => 0];
        if ($magi !== null && $phaseout_info['end'] > $phaseout_info['start'] && $magi > $phaseout_info['start']) {
            $excess = min($magi, $phaseout_info['end']) - $phaseout_info['start'];
            $range = $phaseout_info['end'] - $phaseout_info['start'];
            $phaseout_reduction = $range > 0 ? ($excess / $range) * $capped : 0.0;
        }

        $deduction = max(0.0, $capped - $phaseout_reduction);

        return [
            'regular_rate' => $regular_rate,
            'filing_status' => $filing_status,
            'method' => $method,
            'magi' => $magi,
            'caps' => $caps,
            'phaseout' => $phaseout,
            'totals' => [
                'premium_total' => $total_premium,
                'overtime_total' => $total_overtime_pay,
                'eligible' => $eligible,
                'cap' => $cap,
                'capped' => $capped,
                'phaseout_reduction' => $phaseout_reduction,
                'deduction' => $deduction,
            ],
            'details' => $details,
        ];
    }
}
