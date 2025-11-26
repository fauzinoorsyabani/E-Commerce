<?php
ob_start();

// ❌ HAPUS session_start()
// header.php sudah menjalankannya

include_once("header.php"); // jgn lupa include_once

// file config & functions sudah dipanggil di header.php
// jadi TIDAK BOLEH dipanggil lagi di sini

$log_file = __DIR__ . "/../inc/logs/security.log";

// handle clear log
if(isset($_GET['action']) && $_GET['action'] === 'clear') {
    if(file_exists($log_file)) {
        file_put_contents($log_file, "");
    }
    header("location: security-log.php?cleared=1");
    exit;
}

// handle download
if(isset($_GET['action']) && $_GET['action'] === 'download') {
    if(file_exists($log_file)) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="security.log"');
        readfile($log_file);
        exit;
    } else {
        header("location: security-log.php?err=nodata");
        exit;
    }
}

// read log file
$raw_lines = file_exists($log_file)
    ? file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
    : [];

// parse function
function parse_log_line($line) {
    $parsed = [
        'timestamp' => '',
        'ip' => '',
        'action' => '',
        'details' => '',
        'agent' => '',
        'raw' => $line
    ];

    if(preg_match('/^\[?([0-9\-:\s]+)\]/', $line, $m)) {
        $parsed['timestamp'] = trim($m[1]);
    }

    if(preg_match('/IP:\s*([^\s|]+)/', $line, $m)) {
        $parsed['ip'] = $m[1];
    }

    if(preg_match('/ACTION:\s*([^|]+)/i', $line, $m)) {
        $parsed['action'] = trim($m[1]);
    }

    if(preg_match('/DETAILS:\s*([^|]+)/i', $line, $m)) {
        $parsed['details'] = trim($m[1]);
    }

    if(preg_match('/AGENT:\s*(.+)$/i', $line, $m)) {
        $parsed['agent'] = trim($m[1]);
    }

    return $parsed;
}

$logs = array_reverse(array_map('parse_log_line', $raw_lines));
?>

<!-------------------- HTML BELOW ------------------------>

<section class="content-header">
    <h1>Security Logs</h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Log Viewer</h3>

            <div class="box-tools pull-right">
                <a href="security-log.php?action=download" class="btn btn-default btn-sm">
                    <i class="fa fa-download"></i> Download
                </a>
                <a href="security-log.php?action=clear" class="btn btn-danger btn-sm" onclick="return confirm('Clear log?');">
                    <i class="fa fa-trash"></i> Clear
                </a>
            </div>
        </div>

        <div class="box-body">
            <table id="logsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Timestamp</th>
                        <th>IP</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Agent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($logs as $l): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($l['timestamp']) ?></td>
                        <td><?= htmlspecialchars($l['ip']) ?></td>
                        <td><?= htmlspecialchars($l['action']) ?></td>
                        <td><?= htmlspecialchars($l['details']) ?></td>
                        <td><?= htmlspecialchars($l['agent']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
