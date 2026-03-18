<?php
header('Content-Type: application/json');

$tempDir = __DIR__ . "/temp"; // adjust path if needed
$deleted = [];

if (is_dir($tempDir)) {
    foreach (glob($tempDir . "/preview_*.*") as $file) {
        if (@unlink($file)) {
            $deleted[] = basename($file);
        }
    }
}

echo json_encode([
    "success" => true,
    "deleted" => $deleted
]);
