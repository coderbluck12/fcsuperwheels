<?php
session_start();
include_once('inc/functions.php');
include_once('inc/access_log.php');

if(isset($_SESSION['current_user']))
{
  $username = $_SESSION['current_user'];
  redirect_to('dashboard.php');
}
if(isset($_POST['login'])) {
    if(empty($_POST['username']) || empty($_POST['password'])) {
        $comment = '<div class="alert alert-danger" role="alert">You cannot leave username or password field empty</div>';
        log_access('LOGIN_FAILED', 'index.php', null, $_POST['username'] ?? 'Unknown');
    } else {
        // Prepare the query with placeholders
        $query = "SELECT * FROM `user` WHERE `username` = :username AND `password` = :password AND `status` = 1 LIMIT 1";
        $stmt = $pdo->prepare($query);
        
        // Bind the parameters
        $stmt->bindParam(':username', $_POST['username']);
        $stmt->bindParam(':password', $_POST['password']);
        
        // Execute the query
        $stmt->execute();
        
        // Fetch the results
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if a user was found
        if($result) {
            //$login_level = $result['level'];
           /* if($login_level == $access_level) {*/
                $_SESSION['current_user'] = $_POST['username'];
                log_access('LOGIN', 'index.php', $result['id'], $_POST['username']);
                header('Location:dashboard.php');
                exit; // Terminate script execution after redirection
            /*} else {
                $comment = '<div class="alert alert-danger" role="alert" style="text-align:center;"><b>Access denied</b>, you do not have the authorization of a <b>'.ucwords($access_level).'</b> . Contact <b>IT</b> if this is a mistake <br /><a href="../">Go back home</a></div>';
            }*/
        } else {
            $comment = '<div class="alert alert-danger" role="alert" style="text-align:center;"><b>Access denied</b>, invalid login credentials detected. Contact <b>IT</b> if this is a mistake</div>';
            log_access('LOGIN_FAILED', 'index.php', null, $_POST['username']);
        }
    }
}

if(isset($_GET['exp'])) {
    $comment = '<div class="alert alert-danger" role="alert" style="text-align:center;"><b>Access denied</b>, Your session has <b>expired</b>. Re-enter your login details to continue</div>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keywords" content="">

    <!-- Title Page-->
    <title>Firschoice Superwheels Auto - Admin login</title>

    <!-- Fontfaces CSS-->
    <link href="css/font-face.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">

    <!-- Bootstrap CSS-->
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">

    <!-- Vendor CSS-->
    <link href="vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="vendor/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet" media="all">
    <link href="vendor/wow/animate.css" rel="stylesheet" media="all">
    <link href="vendor/css-hamburgers/hamburgers.min.css" rel="stylesheet" media="all">
    <link href="vendor/slick/slick.css" rel="stylesheet" media="all">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="vendor/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="css/theme.css" rel="stylesheet" media="all">

</head>

<body class="animsition" style="overflow:show;">
    <div class="page-wrapper">
        <div class="page-content--bge5">
            <div class="container">

                <div class="login-wrap">
                    <div class="login-content">
                      <div class="login-logo">
                          <a href="../">
                             <img src="sp_logo.png" width="70%">  
                          </a>
                          <h1>ADMIN LOGIN</h1>

                      </div>
                      <?php if(isset($comment)) { echo $comment; }?>
                        <div class="login-form">
                            <form action="index.php" method="post">
                                <div class="form-group">
                                    <label>Username</label>
                                    <input class="au-input au-input--full" type="text" name="username" required>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input class="au-input au-input--full" type="password" name="password" required>
                                </div>
                                <button class="au-btn au-btn--block au-btn--blue m-b-20" type="submit" name="login">sign in</button>
                            </form>
                            <label>
                                <a href="../">Back to Main Website</a>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Jquery JS-->
    <script src="vendor/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="vendor/bootstrap-4.1/popper.min.js"></script>
    <script src="vendor/bootstrap-4.1/bootstrap.min.js"></script>
    <!-- Vendor JS       -->
    <script src="vendor/slick/slick.min.js">
    </script>
    <script src="vendor/wow/wow.min.js"></script>
    <script src="vendor/animsition/animsition.min.js"></script>
    <script src="vendor/bootstrap-progressbar/bootstrap-progressbar.min.js">
    </script>
    <script src="vendor/counter-up/jquery.waypoints.min.js"></script>
    <script src="vendor/counter-up/jquery.counterup.min.js">
    </script>
    <script src="vendor/circle-progress/circle-progress.min.js"></script>
    <script src="vendor/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="vendor/chartjs/Chart.bundle.min.js"></script>
    <script src="vendor/select2/select2.min.js">
    </script>

    <!-- Main JS-->
    <script src="js/main.js"></script>

</body>

</html>
<!-- end document-->
