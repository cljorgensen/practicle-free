<?php
// Initialize the session
if (session_status() == PHP_SESSION_NONE) {
  session_start();
} else {
  header("location: login.php", true, 301);
}

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
  header("location: login.php");
  exit;
}

?>
<?php

require_once "./inc/dbconnection.php";
require_once "./functions/functions.php";
require_once "./getdata.php";

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";
$donotlogin = 0;
$UserID = $_SESSION["id"];
$username = $_SESSION["username"];
$UserFullName = $_SESSION["userfullname"];
$_SESSION["locked"] = true;

$LockedStatus = getLockStatus($UserID);
if ($LockedStatus == 0) {
  $_SESSION["locked"] = false;
  session_start();
  $functions->sessiondbcreate();
  $cookiepage = $_COOKIE["practicle_visited_url"];
  //$RedirectPagego = "<meta http-equiv='refresh' content='0';url=$cookiepage>";
  //echo $RedirectPagego;
  header("location: $cookiepage", true, 301);
  exit;
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Check if password is empty
  if (empty(trim($_POST["pin"]))) {
    $password_err = "Please enter your password.";
  } else {
    $pin2 = trim($_POST["pin"]);
  }

  // Validate credentials
  if (empty($username_err) && empty($password_err)) {
    // Prepare a select statement
    $sql = "SELECT id, Username, Pin FROM users WHERE Username = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
      if ($stmt == false) {
        die("<pre>" . mysqli_error($conn) . PHP_EOL . $query . "</pre>");
      }
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "s", $param_username);
      //debug line
      //echo __line__ . ":: [" . mysqli_errno($conn) . "] " . mysqli_error($conn) . "<br>\n";
      // Set parameters
      $param_username = $username;

      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        //debug line    
        //echo __line__ . ":: [" . mysqli_errno($conn) . "] " . mysqli_error($conn) . "<br>\n";
        // Store result
        mysqli_stmt_store_result($stmt);
        //echo __line__ . ":: [" . mysqli_errno($conn) . "] " . mysqli_error($conn) . "<br>\n";
        // Check if username exists, if yes then verify password
        if (mysqli_stmt_num_rows($stmt) == 1) {
          // Bind result variables
          mysqli_stmt_bind_result($stmt, $id, $username, $pin1);
          if (mysqli_stmt_fetch($stmt)) {
            //echo __line__ . ":: [" . mysqli_errno($conn) . "] " . mysqli_error($conn) . "<br>\n";

            if ($pin1 == $pin2) {
              // Password is correct, so start a new session
              // Check first if user is active and is allowed to login
              $sql = "SELECT Active FROM users WHERE Username='" . $username . "';";
              $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
              while ($row = mysqli_fetch_array($result)) {
                if ($row['Active'] == '0') {
                  echo "<script> alert('Your account has been deactivated!'); </script>";
                  $donotlogin = 1;
                }
              }
              $UserID = $id;

              if ($donotlogin == 1) {
                session_destroy();
                header("location: login.php");
              }
              session_start();

              $_SESSION["locked"] = false;
              session_start();
              $functions->sessiondbcreate();
              //Create login log entry
              $LogActionText = "User " . $username . " logged in";
              createSystemLogEntry($UserID, $LogActionText);
              //Set LastLogin Date
              createLastLoginUser($UserID);
              // Redirect user to welcome page
              $cookiepage = $_COOKIE["practicle_visited_url"];
              //$RedirectPagego = "<meta http-equiv='refresh' content='0';url=$cookiepage>";
              //echo $RedirectPagego;
              header("location: $cookiepage", true, 301);
              exit;
            } else {
              // Display an error message if password is not valid
              $password_err = "The password you entered was not valid.";
            }
          }
        } else {
          // Display an error message if username doesn't exist
          $username_err = "No account found with that username.";
        }
      } else {
        echo "Oops! Something went wrong. Please try again later.";
      }
    }
  }
  // Close statement
  mysqli_stmt_close($stmt);
  //echo __line__ . ":: [" . mysqli_errno($conn) . "] " . mysqli_error($conn) . "<br>\n";
  // Close connection
  mysqli_close($conn);
}
$SystemName = $functions->getSettingValue(13);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="./images/logo.png">
  <link rel="icon" type="image/png" href="./images/favicon.ico">
  <title>
    <?php echo $SystemName ?>
  </title>
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Nucleo Icons -->
  <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="./assets/css/material-dashboard.css?v=3.0.3" rel="stylesheet" />
</head>

<body class="bg-gray-200">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3  navbar-transparent mt-4">
    <div class="container">
      <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 text-white" href="./index.php">
        <?php echo $SystemName ?>
      </a>
      <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon mt-2">
          <span class="navbar-toggler-bar bar1"></span>
          <span class="navbar-toggler-bar bar2"></span>
          <span class="navbar-toggler-bar bar3"></span>
        </span>
      </button>
    </div>
  </nav>
  <!-- End Navbar -->
  <style>
    .pincode {
      -text-security: disc;
      -webkit-text-security: disc;
      -moz-text-security: disc;
    }
  </style>
  <?php $url = @$functions->getLoginPicture(); ?>
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('<?php echo $url ?>');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
            <div class="card">
              <div class="card-header text-center py-0">
                <div class="mt-n5">
                  <?php
                  $profilepicture = $functions->getProfilePicture($UserID);
                  ?>
                  <img class="avatar avatar-xxl shadow-lg" alt="Image placeholder" src="<?php echo $profilepicture ?>">
                </div>
              </div>
              <div class="card-body text-center">
                <h4 class="mb-0 font-weight-bolder"><?php echo $UserFullName; ?></h4>
                <p class="mb-4"><?php echo _("Enter pin to unlock") ?></p>
                <form role="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                  <div class="input-group input-group-dynamic">
                    <label class="form-label">Pin</label>
                    <input type="text" id="pin" name="pin" class="form-control pincode" inputmode="numeric" autocomplete="off" autofocus>
                    <span class="help-block"><?php echo $password_err; ?></span>
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn btn-lg bg-gradient-dark mt-4 mb-0"><?php echo _("Unlock") ?></button>
                  </div>
                </form>
                <p class="mt-4 text-sm text-center">
                  <?php echo _("Forgot pin?") ?>
                  <a href="javascript:sendPinToUser();" class="text-primary text-gradient font-weight-bold">Send</a>
                </p>
                <p id="answertext" name="answertext" class="mt-4 text-sm text-center"></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <footer class="footer py-5">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mb-4 mx-auto text-center">
          <a href="https://practicle.practicle.dk" target="_blank" class="text-secondary me-xl-5 me-3 mb-sm-0 mb-2">
            <?php echo _("Support") ?>
          </a>
          <a href="https://practicle.dk/newspage.php" target="_blank" class="text-secondary me-xl-5 me-3 mb-sm-0 mb-2">
            <?php echo _("News") ?>
          </a>
          <a href="https://practicle.dk/order.php" target="_blank" class="text-secondary me-xl-5 me-3 mb-sm-0 mb-2">
            <?php echo _("Try") ?>
          </a>
          <a href="https://practicle.dk/index.php#presentation" target="_blank" class="text-secondary me-xl-5 me-3 mb-sm-0 mb-2">
            <?php echo _("About") ?>
          </a>
        </div>
        <div class="col-lg-8 mx-auto text-center mb-4 mt-2">
          <a href="https://www.linkedin.com/company/practicle/" target="_blank" class="text-secondary me-xl-4 me-4">
            <span class="text-lg fab fa-linkedin"></span>
          </a>
          <a href="https://www.facebook.com/practicle.dk" target="_blank" class="text-secondary me-xl-4 me-4">
            <span class="text-lg fab fa-facebook"></span>
          </a>
        </div>
      </div>
      <div class="row">
        <div class="col-8 mx-auto text-center mt-1">
          <p class="mb-0 text-secondary">
            © <script>
              document.write(new Date().getFullYear())
            </script>
            <a href="https://practicle.dk" class="font-weight-bold" target="_blank">Practicle</a>
            - making better web for small business.
          </p>
        </div>
      </div>
    </div>
  </footer>
  <!--   Core JS Files   -->
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="./assets/js/material-dashboard.min.js?v=3.0.3"></script>
    <script>
    function sendPinToUser() {
      userid = '<?php echo $UserID ?>';

      if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
      } else {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      }

      xmlhttp.open("GET", "getdata.php?sendPinToUser=" + userid, true);
      xmlhttp.send();
      message = "Your pin is on its way to your email";
      document.getElementById('answertext').innerHTML = message;
    }
  </script>
</body>

</html>