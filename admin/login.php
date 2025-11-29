<?php
ob_start();
session_start();
include("inc/config.php");
include("inc/functions.php");
include("inc/CSRF_Protect.php");
$csrf = new CSRF_Protect();
$error_message = '';

/* ==========================================================
   SIMPLE LOGGER (ADMIN)
   - Format: [time] [IP:x.x.x.x] PESAN
   - Lokasi file: /inc/logs/security.log
   ========================================================== */
function write_log($event) {
    // root/inc/logs/security.log
    $file = dirname(__DIR__) . "/inc/logs/security.log";

    // Buat folder kalau belum ada
    if (!file_exists(dirname($file))) {
        mkdir(dirname($file), 0777, true);
    }

    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
    $time = date("Y-m-d H:i:s");

    $log = "[$time] [IP:$ip] $event" . PHP_EOL;

    file_put_contents($file, $log, FILE_APPEND);
}
/* ========================================================== */

if (isset($_POST['form1'])) {

    if (empty($_POST['email']) || empty($_POST['password'])) {
        $error_message = 'Email and/or Password can not be empty<br>';
    } else {

        // INPUT TANPA FILTER (sengaja vulnerable)
        $email    = $_POST['email'];
        $password = $_POST['password'];

        // QUERY vulnerable SQL Injection
        $query = "SELECT * FROM tbl_user WHERE email='$email' AND status='Active'";

        // catat percobaan login
        write_log("LOGIN ATTEMPT → email='$email' | RAW_SQL=\"$query\"");

        // eksekusi query
        $statement = $pdo->query($query);
        $total     = $statement->rowCount();
        $result    = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($total == 0) {
            write_log("FAILED LOGIN → email='$email' (NOT FOUND)");
            $error_message .= 'Email Address does not match<br>';
        } else {

            foreach ($result as $row) {
                $row_password = $row['password'];
            }

            // BYPASS password (buat demo SQLi)
            write_log("SUCCESS LOGIN via SQLi → email='$email' | user_id=" . $row['id']);

            $_SESSION['user'] = $row;
            header("location: index.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Login</title>

    <meta content="width=device-width, initial-scale=1, maximum-scale=1" name="viewport">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/ionicons.min.css">
    <link rel="stylesheet" href="css/AdminLTE.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body class="hold-transition login-page sidebar-mini">

<div class="login-box">
    <div class="login-logo">
        <b>Admin Panel</b>
    </div>
    <div class="login-box-body">
        <p class="login-box-msg">Log in to start your session</p>

        <?php 
        if ($error_message != '') {
            echo '<div class="error">'.$error_message.'</div>';
        }
        ?>

        <form action="" method="post">
            <?php $csrf->echoInputField(); ?>
            <div class="form-group has-feedback">
                <input class="form-control" placeholder="Email address" name="email" type="text">
            </div>
            <div class="form-group has-feedback">
                <input class="form-control" placeholder="Password" name="password" type="password">
            </div>
            <div class="row">
                <div class="col-xs-4 col-xs-offset-8">
                    <input type="submit" class="btn btn-success btn-block btn-flat login-button" name="form1" value="Log In">
                </div>
            </div>
        </form>
    </div>
</div>

<script src="js/jquery-2.2.3.min.js"></script>
<script src="js/bootstrap.min.js"></script>

</body>
</html>
