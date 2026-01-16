<?php
require_once dirname(__DIR__) . '/overtime-deduction-calculator-2025.php';

$payload = [
    'regular_rate' => 25,
    'filing_status' => 'single',
    'method' => 'flsa',
    'magi' => 110000,
    'buckets' => [
        ['multiplier' => 1.5, 'overtime_pay' => 2500, 'hours' => 100],
        ['multiplier' => 2.0, 'overtime_pay' => 1200, 'hours' => 24],
    ],
];

$result = ODC25_Calculator::calculate($payload);

$expected = [
    'premium_total' => 25 * 0.5 * 100 + 25 * 1.0 * 24,
    'overtime_total' => 3700,
    'eligible' => 25 * 0.5 * 100 + 25 * 1.0 * 24,
    'cap' => 12000,
];

echo "Premium total: {$result['totals']['premium_total']} (expected {$expected['premium_total']})\n";
echo "Overtime total: {$result['totals']['overtime_total']} (expected {$expected['overtime_total']})\n";
echo "Eligible: {$result['totals']['eligible']} (expected {$expected['eligible']})\n";
echo "Cap: {$result['totals']['cap']} (expected {$expected['cap']})\n";

$magi = 110000;
$phaseout = ODC25_Utils::get_phaseout()['single'];
$excess = min($magi, $phaseout['end']) - $phaseout['start'];
$range = $phaseout['end'] - $phaseout['start'];
$expected_reduction = ($excess / $range) * $result['totals']['capped'];

echo "Phaseout reduction: {$result['totals']['phaseout_reduction']} (expected {$expected_reduction})\n";
