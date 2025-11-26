<?php
require_once __DIR__ . '/../vendor/autoload.php'; 
use Dompdf\Dompdf;
use Dompdf\Options;

// Ambil log file
$log_file = __DIR__ . "/../inc/logs/security.log";
$logs_raw = file_exists($log_file) ? file($log_file, FILE_IGNORE_NEW_LINES) : [];

$logs = [];

// Parsing format lama & baru
foreach ($logs_raw as $line) {

    if (preg_match("/\[(.*?)\] \[IP:(.*?)\] (.*)/", $line, $m)) {
        $logs[] = [
            "timestamp" => $m[1],
            "ip"        => $m[2],
            "event"     => $m[3]
        ];
    }
    elseif (preg_match("/\[(.*?)\] \| IP\: (.*?) \| ACTION\: (.*?) \| DETAILS\: (.*?) \|/i", $line, $m)) {
        $logs[] = [
            "timestamp" => $m[1],
            "ip"        => $m[2],
            "event"     => $m[3] . " | " . $m[4]
        ];
    }
}

// ============ HTML PDF ===============

$logo_path = __DIR__ . "/../assets/uploads/logo-ecomart.png";
$logo_base64 = "./assets/uploads/logo-ecomart.png";
if (file_exists($logo_path)) {
    $logo_base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
}

ob_start();
?>

<style>
body {
    font-family: DejaVu Sans, sans-serif;
    margin: 0;
    padding: 0;
}
.cover {
    text-align: center;
    margin-top: 170px;
}
.cover img {
    width: 150px;
    margin-bottom: 20px;
}
.h1 {
    font-size: 38px;
    font-weight: bold;
}
.h2 {
    font-size: 20px;
    margin-top: 10px;
    color: #555;
}
.footer {
    position: fixed;
    bottom: 20px;
    width: 100%;
    text-align: center;
    font-size: 12px;
    color: #888;
}
.table-title {
    font-size: 22px;
    font-weight: bold;
    margin-top: 30px;
    text-align: center;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}
th, td {
    padding: 8px;
    border: 1px solid #444;
    font-size: 12px;
}
th {
    background: #000;
    color: #fff;
}
.status-success { color: green; font-weight: bold; }
.status-failed { color: red; font-weight: bold; }
.status-attempt { color: orange; font-weight: bold; }
</style>

<!-- COVER PAGE -->
<div class="cover">
    <?php if($logo_base64): ?>
        <img src="<?php echo $logo_base64; ?>">
    <?php endif; ?>

    <div class="h1">Security Log Report</div>
    <div class="h2">SQL Injection Attack Evidence & Analysis</div>
    <div class="h2">Generated on: <?php echo date("Y-m-d H:i:s"); ?></div>
    <div class="h2">By: Raja Jawa X Konoha</div>
</div>

<div style="page-break-after: always;"></div>

<!-- PAGE 2: TABEL LOG DETAIL -->
<div class="table-title">Detailed Activity Log</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Timestamp</th>
            <th>IP</th>
            <th>Status</th>
            <th>Event Detail</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        foreach($logs as $log):

            $upper = strtoupper($log["event"]);
            $status = "OTHER";
            $class = "";

            if (str_contains($upper, "SUCCESS")) { 
                $status = "SUCCESS"; 
                $class = "status-success"; 
            }
            elseif (str_contains($upper, "FAILED")) {
                $status = "FAILED";
                $class = "status-failed";
            }
            elseif (str_contains($upper, "ATTEMPT")) {
                $status = "ATTEMPT";
                $class = "status-attempt";
            }
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo $log["timestamp"]; ?></td>
            <td><?php echo $log["ip"]; ?></td>
            <td><span class="<?php echo $class; ?>"><?php echo $status; ?></span></td>
            <td><?php echo htmlspecialchars($log["event"]); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="footer">
    Security Log Report — Generated Automatically
</div>

<?php
$html = ob_get_clean();

// DOMPDF OPTIONS
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');

$dompdf->render();
$dompdf->stream("Security_Log_Report.pdf", ["Attachment" => true]);
exit;
?>
