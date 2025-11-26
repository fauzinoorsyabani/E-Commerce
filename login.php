<?php 
require_once('header.php'); 
include("inc/logger.php"); 
?>

<?php
// Ambil banner
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);
$banner_login = $result['banner_login'];
?>

<?php
/* ============================================================
   LOGIN CUSTOMER (VULNERABLE + LOGGING)
   ============================================================ */
if(isset($_POST['form1'])) {

    if(empty($_POST['cust_email']) || empty($_POST['cust_password'])) {
        $error_message = LANG_VALUE_132.'<br>';
    } else {

        // Ambil input tanpa filter (VULNERABLE)
        $cust_email = $_POST['cust_email'];
        $cust_password = $_POST['cust_password'];

        // LOG: Attempt
        log_event("LOGIN ATTEMPT (CUSTOMER) email='$cust_email'");

        // Query rentan SQL Injection
        $query = "SELECT * FROM tbl_customer WHERE cust_email = '$cust_email'";
        $statement = $pdo->query($query);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $total  = count($result);

        if($total > 0) {

            $row = $result[0];

            if($row['cust_status'] == 0) {

                // LOG: akun non aktif
                log_event("LOGIN BLOCKED (CUSTOMER NOT ACTIVE) email='$cust_email'");
                $error_message .= LANG_VALUE_148.'<br>';

            } else {

                // LOG: sukses
                log_event("LOGIN SUCCESS (CUSTOMER) email='$cust_email' | ID=".$row['cust_id']);

                $_SESSION['customer'] = $row;
                header("location: ".BASE_URL."dashboard.php");
                exit;
            }

        } else {

            // LOG: gagal
            log_event("LOGIN FAILED (CUSTOMER) email='$cust_email' → NOT FOUND");

            $error_message .= LANG_VALUE_133.'<br>';
        }
    }
}
?>

<div class="page-banner" style="background-color:#444;background-image: url(assets/uploads/<?php echo $banner_login; ?>);">
    <div class="inner">
        <h1><?php echo LANG_VALUE_10; ?></h1>
    </div>
</div>

<div class="page">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="user-content">

                    <form action="" method="post" novalidate>
                        <?php $csrf->echoInputField(); ?>                  

                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">

                                <?php
                                if(isset($error_message) && $error_message != '') {
                                    echo "<div class='error' style='padding:10px;background:#f1f1f1;margin-bottom:20px;'>".$error_message."</div>";
                                }
                                ?>

                                <div class="form-group">
                                    <label><?php echo LANG_VALUE_94; ?> *</label>
                                    <input type="email" class="form-control" name="cust_email">
                                </div>

                                <div class="form-group">
                                    <label><?php echo LANG_VALUE_96; ?> *</label>
                                    <input type="password" class="form-control" name="cust_password">
                                </div>

                                <div class="form-group">
                                    <input type="submit" class="btn btn-success" value="<?php echo LANG_VALUE_4; ?>" name="form1">
                                </div>

                                <a href="forget-password.php" style="color:#e4144d;"><?php echo LANG_VALUE_97; ?>?</a>

                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>
