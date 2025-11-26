<?php
require_once('header.php');

// Lokasi file log (gabungan admin + customer)
$log_file = dirname(__DIR__) . "/inc/logs/security.log";

// Clear log
if (isset($_GET['clear']) && $_GET['clear'] == "1") {
    file_put_contents($log_file, "");
    header("location: logs.php?cleared=1");
    exit;
}

// Baca log mentah
$logs_raw = file_exists($log_file) ? file($log_file, FILE_IGNORE_NEW_LINES) : [];

// PARSER format lama & baru
$logs = [];

foreach ($logs_raw as $line) {

    $timestamp = "";
    $ip        = "";
    $action    = "";
    $details   = "";

    // FORMAT 1: [time] [IP:127.0.0.1] PESAN ...
    if (preg_match("/\[(.*?)\]\s+\[IP:(.*?)\]\s+(.*)/", $line, $m)) {
        $timestamp = $m[1];
        $ip        = $m[2];
        $action    = $m[3];
        $details   = "-";
    }
    // FORMAT 2: [time] | IP: 127.0.0.1 | ACTION: ... | DETAILS: ... | AGENT: ...
    elseif (preg_match("/\[(.*?)\]\s+\|\s+IP:\s+(.*?)\s+\|\s+ACTION:\s+(.*?)\s+\|\s+DETAILS:\s+(.*?)\s+\|/i", $line, $m)) {
        $timestamp = $m[1];
        $ip        = $m[2];
        $action    = $m[3];
        $details   = $m[4];
    }

    if ($timestamp !== "") {
        $logs[] = [
            "timestamp" => $timestamp,
            "ip"        => $ip,
            "action"    => $action,
            "details"   => $details,
            "raw"       => $line
        ];
    }
}
?>

<section class="content-header">
    <h1>Security Logs</h1>
</section>

<section class="content">

    <?php if(isset($_GET['cleared'])): ?>
        <div class="alert alert-success">Log berhasil dihapus.</div>
    <?php endif; ?>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Log Aktivitas</h3>

            <div class="box-tools pull-right">
                <a href="?clear=1" 
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Yakin ingin menghapus semua log?')">
                    Clear Log
                </a>

                <a href="../inc/logs/security.log" download class="btn btn-primary btn-sm">
                    Download Log
                </a>

                <a href="logs-pdf.php" target="_blank" class="btn btn-warning btn-sm">Download PDF Report</a> 
            </div>
        </div>

        <div class="box-body">

            <style>
                #logTable th, #logTable td {
                    vertical-align: middle !important;
                }
                .status-badge {
                    display: inline-block;
                    padding: 2px 8px;
                    border-radius: 12px;
                    font-size: 11px;
                    font-weight: 600;
                    color: #fff;
                }
                .status-SUCCESS { background: #00a65a; } /* hijau */
                .status-FAILED  { background: #dd4b39; } /* merah */
                .status-ATTEMPT { background: #f39c12; } /* kuning */
                .status-OTHER   { background: #777;   }  /* abu */
                .filter-input {
                    margin-top: 3px;
                    font-size: 12px;
                    height: 28px;
                }
            </style>

            <table class="table table-bordered table-striped" id="logTable">
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th data-col="timestamp">
                            Timestamp
                            <input type="text" id="filter-timestamp" class="form-control input-sm filter-input" placeholder="Filter Timestamp">
                        </th>
                        <th data-col="ip" style="width:150px;">
                            IP
                            <input type="text" id="filter-ip" class="form-control input-sm filter-input" placeholder="Filter IP">
                        </th>
                        <th data-col="status" style="width:120px;">
                            Status
                            <select id="filter-status" class="form-control input-sm filter-input">
                                <option value="">All</option>
                                <option value="SUCCESS">SUCCESS</option>
                                <option value="FAILED">FAILED</option>
                                <option value="ATTEMPT">ATTEMPT</option>
                                <option value="OTHER">OTHER</option>
                            </select>
                        </th>
                        <th data-col="detail">
                            Detail
                            <input type="text" id="filter-detail" class="form-control input-sm filter-input" placeholder="Filter Detail">
                        </th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php 
                    $no = 1;
                    foreach (array_reverse($logs) as $log):

                        // tentukan status dari isi action
                        $status = "OTHER";
                        $upper  = strtoupper($log["action"]);
                        if (strpos($upper,'SUCCESS') !== false) $status = "SUCCESS";
                        elseif (strpos($upper,'FAILED') !== false) $status = "FAILED";
                        elseif (strpos($upper,'ATTEMPT') !== false) $status = "ATTEMPT";
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($log["timestamp"]); ?></td>
                        <td><?php echo htmlspecialchars($log["ip"]); ?></td>

                        <td data-status="<?php echo $status; ?>">
                            <span class="status-badge status-<?php echo $status; ?>">
                                <?php echo $status; ?>
                            </span>
                        </td>

                        <td><?php echo htmlspecialchars($log["raw"]); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>

        </div>
    </div>

</section>

<script>
// Filter sederhana tanpa DataTables
document.addEventListener('DOMContentLoaded', function () {
    const rows      = Array.from(document.querySelectorAll('#logTable tbody tr'));
    const tsInput   = document.getElementById('filter-timestamp');
    const ipInput   = document.getElementById('filter-ip');
    const stSelect  = document.getElementById('filter-status');
    const detInput  = document.getElementById('filter-detail');

    function applyFilters() {
        const tsVal  = tsInput.value.toLowerCase();
        const ipVal  = ipInput.value.toLowerCase();
        const stVal  = stSelect.value;
        const detVal = detInput.value.toLowerCase();

        rows.forEach(row => {
            const tsText   = row.children[1].innerText.toLowerCase();
            const ipText   = row.children[2].innerText.toLowerCase();
            const statusTd = row.querySelector('td[data-status]');
            const stRow    = statusTd ? statusTd.getAttribute('data-status') : '';
            const detText  = row.children[4].innerText.toLowerCase();

            let show = true;
            if (tsVal && !tsText.includes(tsVal))   show = false;
            if (ipVal && !ipText.includes(ipVal))   show = false;
            if (stVal && stRow !== stVal)           show = false;
            if (detVal && !detText.includes(detVal)) show = false;

            row.style.display = show ? '' : 'none';
        });
    }

    tsInput.addEventListener('keyup', applyFilters);
    ipInput.addEventListener('keyup', applyFilters);
    detInput.addEventListener('keyup', applyFilters);
    stSelect.addEventListener('change', applyFilters);
});
</script>

<?php require_once('footer.php'); ?>
