<?php
error_reporting(0);
    session_start();
    include('includes/dbconnection.php');
    include('includes/functions.php');

    if(isset($_POST['login']))
    {
        $matricNo = trim($_POST['matricNo']);
        $password = trim($_POST['password']);
        $errorMsg = '';
        $suspicious = false;
        // Suspicious input detection (basic)
        $patterns = [
            '/\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|--|#|\*|;|\bor\b|\band\b|\'|\")\b/i',
            '/\b1=1\b/',
            '/\bOR\b.*=/',
        ];
        foreach([$matricNo, $password] as $input) {
            foreach($patterns as $pattern) {
                if(preg_match($pattern, $input)) {
                    $suspicious = true;
                    log_audit_event($con, $matricNo, 'student', 'attack_detected', 'Suspicious input detected in login: ' . $input);
                    break 2;
                }
            }
        }
        // Basic input validation
        if(empty($matricNo) || empty($password)) {
            $errorMsg = "<div class='alert alert-danger' role='alert'>Please fill in all fields!</div>";
            log_audit_event($con, $matricNo, 'student', 'login_fail', 'Empty matricNo or password');
        } else {
            // Use prepared statements to prevent SQL injection
            $stmt = mysqli_prepare($con, "SELECT * FROM tblstudent WHERE matricNo = ?");
            mysqli_stmt_bind_param($stmt, "s", $matricNo);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_array($result);

            if($row && password_verify($password, $row['password']))
            {
                $_SESSION['matricNo']=$row['matricNo'];
                $_SESSION['firstName']=$row['firstName'];
                $_SESSION['lastName']=$row['lastName'];
                log_audit_event($con, $matricNo, 'student', 'login_success', 'Student login successful');
                echo "<script type = \"text/javascript\">
                    window.location = (\"student/index.php\")
                   </script>";  

                // if($row['roleId'] == 2){ //if user is Hod
                
                //     echo "<script type = \"text/javascript\">
                //     window.location = (\"hod/index.php\")
                //     </script>";  
                // }
                // else if($row['roleId'] == 3){ //if user is Dean
                
                //     echo "<script type = \"text/javascript\">
                //     window.location = (\"dean/index.php\")
                //     </script>";  
                // }
            }
            else
            {
                $errorMsg = "<div class='alert alert-danger' role='alert'>Invalid Username/Password!</div>";
                log_audit_event($con, $matricNo, 'student', 'login_fail', 'Invalid username or password');
            }
            mysqli_stmt_close($stmt);
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

    <link rel="apple-touch-icon" href="https://i.imgur.com/QRAUqs9.png">
    <link rel="shortcut icon" href="img/favicon2.jpeg" />
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
                        <!-- <img class="align-content" src="images/staffGreen.png" alt=""> -->
                    </a>
                </div>
                <div class="login-form">
                    <form method="Post" Action="">
                            <?php echo $errorMsg; ?>
                        <strong><h2 align="center">Student Login</h2></strong><hr>
                        <div class="form-group">
                            <label>Matric Number</label>
                            <input type="text" name="matricNo" Required class="form-control" placeholder="Matric Number">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" Required class="form-control" placeholder="Password">
                        </div>
                        <div class="checkbox">
                           <label class="pull-left">
                                <a href="index.php">Go Back</a>
                            </label>
                            <label class="pull-right">
                                <a href="#">Forgot Password?</a><!-- Log on to freeprojectscodes.com for more projects! -->
                            </label>
                        </div>
                        <br><!-- Log on to freeprojectscodes.com for more projects! -->
                        <button type="submit" name="login" class="btn btn-success btn-flat m-b-30 m-t-30">Log in</button>
                        <!-- <div class="social-login-content">
                            <div class="social-button">
                                <button type="button" class="btn social facebook btn-flat btn-addon mb-3"><i class="ti-facebook"></i>Sign in with facebook</button>
                                <button type="button" class="btn social twitter btn-flat btn-addon mt-2"><i class="ti-twitter"></i>Sign in with twitter</button>
                            </div>
                        </div> -->
                        <!-- <div class="register-link m-t-15 text-center">
                            <p>Don't have account ? <a href="#"> Sign Up Here</a></p>
                        </div> -->
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

</body>
</html>
