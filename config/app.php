<?php
// config/app.php - pengaturan aplikasi
// BASE_URL = alamat folder project dari web root, dideteksi otomatis.
// Contoh: project di C:\xampp\htdocs\iot-monitor  ->  '/iot-monitor'
// Jadi folder project boleh diberi nama apa saja (tidak harus iot-monitor).

$_root = str_replace('\\', '/', (string)realpath(__DIR__ . '/..'));
$_doc  = str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));

define('BASE_URL', ($_doc !== '' && strncasecmp($_root, $_doc, strlen($_doc)) === 0)
    ? substr($_root, strlen($_doc))
    : '/iot-monitor');   // cadangan kalau web root tidak terdeteksi

unset($_root, $_doc);
