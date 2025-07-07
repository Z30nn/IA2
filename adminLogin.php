<?php

    session_start();
    error_reporting(0);
    include('includes/dbconnection.php');
    include('includes/functions.php');

    // PHPMailer namespace imports
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    use PHPMailer\PHPMailer\SMTP;

    $showModal = false;
    $errorMsg = '';

    // Step 1: Handle login credentials
    if(isset($_POST['login']))
    {
        $staffId=$_POST['staffId'];
        $password=md5($_POST['password']);
        $suspicious = false;
        // Suspicious input detection (basic)
        $patterns = [
            '/\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|--|#|\*|;|\bor\b|\band\b|\'|\")\b/i',
            '/\b1=1\b/',
            '/\bOR\b.*=/',
        ];
        foreach([$staffId, $_POST['password']] as $input) {
            foreach($patterns as $pattern) {
                if(preg_match($pattern, $input)) {
                    $suspicious = true;
                    log_audit_event($con, $staffId, 'admin', 'attack_detected', 'Suspicious input detected in login: ' . $input);
                    break 2;
                }
            }
        }
        $query = mysqli_query($con,"select * from tbladmin where  staffId='$staffId' && password='$password'");
        $count = mysqli_num_rows($query);
        $row = mysqli_fetch_array($query);

        if($count > 0)
        {
            // Store user info in session (but not logged in yet)
            $_SESSION['pending_staffId']=$row['staffId'];
            $_SESSION['pending_emailAddress']=$row['emailAddress'];
            $_SESSION['pending_firstName']=$row['firstName'];
            $_SESSION['pending_lastName']=$row['lastName'];
            $_SESSION['pending_adminTypeId']=$row['adminTypeId'];

            // Generate code
            $code = rand(100000, 999999);
            $_SESSION['mfa_code'] = $code;
            $_SESSION['mfa_code_time'] = time();

            // Send email using PHPMailer
            require 'includes/PHPMailer/src/PHPMailer.php';
            require 'includes/PHPMailer/src/SMTP.php';
            require 'includes/PHPMailer/src/Exception.php';
            
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'ia2studentgrading@gmail.com'; // TODO: Replace with your Gmail address
                $mail->Password   = 'zrrw jbcl hvxd ltdw';      // TODO: Replace with your Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                //Recipients
                $mail->setFrom('ia2studentgrading@gmail.com', 'Admin Login');
                $mail->addAddress($row['emailAddress'], $row['firstName']);

                //Content
                $mail->isHTML(false);
                $mail->Subject = 'Your Login Verification Code';
                $mail->Body    = "Dear {$row['firstName']},\n\nYour verification code is: $code\n\nIf you did not request this, please ignore this email.";

                $mail->send();
            } catch (Exception $e) {
                $errorMsg = "<div class='alert alert-danger' role='alert'>Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}</div>";
            }

            log_audit_event($con, $staffId, 'admin', 'login_success', 'Admin login (MFA code sent)');
            $showModal = true;
        }
        else
        {
            $errorMsg = "<div class='alert alert-danger' role='alert'>Invalid Username/Password!</div>";
            log_audit_event($con, $staffId, 'admin', 'login_fail', 'Invalid username or password');
        }
    }

    // Step 2: Handle MFA code verification
    if(isset($_POST['verify_code'])) {
        $input_code = $_POST['mfa_code'];
        if(isset($_SESSION['mfa_code']) && $input_code == $_SESSION['mfa_code'] && time() - $_SESSION['mfa_code_time'] < 600) { // 10 min expiry
            // Move pending session vars to actual login
            $_SESSION['staffId'] = $_SESSION['pending_staffId'];
            $_SESSION['emailAddress'] = $_SESSION['pending_emailAddress'];
            $_SESSION['firstName'] = $_SESSION['pending_firstName'];
            $_SESSION['lastName'] = $_SESSION['pending_lastName'];
            $_SESSION['adminTypeId'] = $_SESSION['pending_adminTypeId'];
            // Clean up
            unset($_SESSION['pending_staffId'], $_SESSION['pending_emailAddress'], $_SESSION['pending_firstName'], $_SESSION['pending_lastName'], $_SESSION['pending_adminTypeId'], $_SESSION['mfa_code'], $_SESSION['mfa_code_time']);
            // Redirect
            log_audit_event($con, $_SESSION['staffId'], 'admin', 'mfa_success', 'MFA code verified, admin logged in');
            if($_SESSION['adminTypeId'] == 1) {
                echo "<script>window.location = ('superAdmin/index.php')</script>";
            } else if($_SESSION['adminTypeId'] == 2) {
                echo "<script>window.location = ('admin/index.php')</script>";
            }
            exit();
        } else {
            $errorMsg = "<div class='alert alert-danger' role='alert'>Invalid or expired code. Please try again.";
            $showModal = true;
            log_audit_event($con, $_SESSION['pending_staffId'] ?? 'unknown', 'admin', 'mfa_fail', 'Invalid or expired MFA code');
        }
    }
?>

<!doctype html>
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Student Grading PHP</title>
    <meta name="description" content="Ela Admin - HTML5 Admin Template">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon">
    <!-- <link rel="shortcut icon" href="img/favicon2.jpeg" /> -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/normalize.css@8.0.0/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lykmapipo/themify-icons@0.1.2/css/themify-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pixeden-stroke-7-icon@1.2.3/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.2.0/css/flag-icon.min.css">
    <link rel="stylesheet" href="assets/css/cs-skin-elastic.css">
    <link rel="stylesheet" href="assets/css/style2.css">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800' rel='stylesheet' type='text/css'>

    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/html5shiv/3.7.3/html5shiv.min.js"></script> -->
</head>
<body class="bg-light">

    <div class="sufee-login d-flex align-content-center flex-wrap">
        <div class="container">
            <div class="login-content">
                <div class="login-logo">
                    <a href="index.html">
                        <!-- <img class="align-content" src="images/adminGreen.jpg" alt=""> -->
                    </a>
                </div>
                <div class="login-form">
                    <form method="Post" Action="">
                            <?php echo $errorMsg; ?>
                               <strong><h2 align="center">Administrator Login</h2></strong><hr>
                        <div class="form-group">
                            <label>Staff ID</label>
                            <input type="text" name="staffId" Required class="form-control" placeholder="Staff ID">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" Required class="form-control" placeholder="Password">
                        </div><!-- Log on to freeprojectscodes.com for more projects! -->
                        <div class="checkbox">
                           <label class="pull-left">
                                <a href="index.php">Go Back</a>
                            </label>
                            <label class="pull-right">
                                <a href="#">Forgot Password?</a>
                            </label>
                        </div>
                        <br>
                        <button type="submit" name="login" class="btn btn-success btn-flat m-b-30 m-t-30">Log in</button>
                    </form>
                </div>
            </div><!-- Log on to freeprojectscodes.com for more projects! -->
        </div>
    </div>

    <!-- MFA Modal -->
    <div class="modal" id="mfaModal" tabindex="-1" role="dialog" aria-labelledby="mfaModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="mfaModalLabel">Multi-Factor Authentication</h5>
          </div>
          <div class="modal-body">
            <p>A verification code has been sent to your email. Please enter it below:</p>
            <form method="post" id="mfaForm">
              <div class="form-group">
                <label for="mfa_code">Verification Code</label>
                <input type="text" class="form-control" id="mfa_code" name="mfa_code" required maxlength="6" pattern="[0-9]{6}">
              </div>
              <button type="submit" name="verify_code" class="btn btn-primary">Verify</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
    $(document).ready(function() {
        var showModal = <?php echo $showModal ? 'true' : 'false'; ?>;
        if(showModal) {
            $('#mfaModal').modal({backdrop: 'static', keyboard: false});
        }
    });
    </script>

</body>
</html>
