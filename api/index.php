<?php

// Pastikan direktori cache dan view di /tmp sudah dibuat untuk serverless Vercel
$directories = [
    '/tmp/views',
    '/tmp/cache',
    '/tmp/sessions',
    '/tmp/logs',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Normalisasi SCRIPT_NAME untuk runtime Vercel agar request /api/... tidak terpotong
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require __DIR__ . '/../public/index.php';
