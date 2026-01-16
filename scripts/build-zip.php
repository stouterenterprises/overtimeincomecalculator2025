<?php
$root = dirname(__DIR__);
$plugin_slug = 'overtime-deduction-calculator-2025';
$zip_name = $plugin_slug . '-' . date('Ymd-His') . '.zip';
$zip_path = $root . DIRECTORY_SEPARATOR . $zip_name;

$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Unable to create ZIP.\n");
    exit(1);
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($files as $file) {
    $file_path = $file->getRealPath();
    if (strpos($file_path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) !== false) {
        continue;
    }

    if (basename($file_path) === $zip_name) {
        continue;
    }

    $relative = $plugin_slug . DIRECTORY_SEPARATOR . substr($file_path, strlen($root) + 1);

    if ($file->isDir()) {
        $zip->addEmptyDir($relative);
    } else {
        $zip->addFile($file_path, $relative);
    }
}

$zip->close();

fwrite(STDOUT, "Created {$zip_name}\n");
