<?php

// Fungsi utama logging
function log_event($action, $details = "") {
    $file = __DIR__ . "/logs/security.log";

    if (!file_exists(dirname($file))) {
        mkdir(dirname($file), 0777, true);
    }

    $ip    = $_SERVER['REMOTE_ADDR']     ?? 'UNKNOWN_IP';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN_AGENT';
    $time  = date("Y-m-d H:i:s");

    $line = "[$time] | IP: $ip | ACTION: $action | DETAILS: $details | AGENT: $agent" . PHP_EOL;

    file_put_contents($file, $line, FILE_APPEND);
}

// Alias
function addLog($action, $details="") {
    log_event($action, $details);
}

// Deteksi SQL Injection sederhana (opsional)
function detectSQLi($input) {
    if (!is_string($input)) return false;

    $patterns = [
        "/\b(or|and)\b/i",
        "/--/",
        "/#/i",
        "/;/",
        "/'/",
        "/1=1/i",
        "/union/i",
        "/select/i",
        "/insert/i",
        "/update/i",
        "/delete/i",
        "/drop/i"
    ];

    foreach ($patterns as $p) {
        if (preg_match($p, $input)) {
            return true;
        }
    }
    return false;
}
