<?php
// Start Session
session_start();

// Database connection
require __DIR__ . '/inc/dbconnection.php';
require __DIR__ . '/functions/functions.php';
require_once 'classes/GoogleAuthenticator.php';

$ga = new PHPGangsta_GoogleAuthenticator();

$error_message = '';
$_SESSION['ValidAuth'] = 0;

if (isset($_POST['btnValidate'])) {

  $code = $_POST['code'];

  $UserID = $_SESSION["id"];
  $UsersSecret = getUserSecretCode($UserID);
  $oneCode = $ga->getCode($UsersSecret);

  if ($code == "") {
    $error_message = 'Please enter authentication code to validate!';
  } else {

    if ($oneCode === $code) {
      // success
      $_SESSION['ValidAuth'] = 1;
      //Create login log entry
      $LogActionText = "User " . $username . " logged in";
      createSystemLogEntry($UserID, $LogActionText);
      //Set LastLogin Date
      createLastLoginUser($UserID);
      // Redirect user to welcome page
      header("Location: index.php");
    } else {
      // fail
      $error_message = "Invalid Authentication Code!";
    }
  }
}
?>
<!DOCTYPE html>
<script src="./assets/js/core/jquery-3.6.0.min.js"></script>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="./images/logo.png">
  <link rel="icon" type="image/png" href="./images/favicon.ico">
  <title>
    <?php
    $SystemURL = $functions->getSettingValue(17);
    $SystemName = $functions->getSettingValue(13);
    ?>
  </title>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="./assets/css/material-dashboard.css?v=3.0.3" rel="stylesheet" />
</head>

<body class="bg-gray-200">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
    <div class="container">
      <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 text-white" href="<?php echo $SystemURL ?>">
        <?php
        echo $SystemName;
        ?>
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
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-50 m-3 border-radius-lg" style="background-image: url('https://images.unsplash.com/photo-1497996541515-6549b145cd90?ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&ixlib=rb-1.2.1&auto=format&fit=crop&w=1650&q=80');">
      <span class="mask bg-gradient-dark opacity-6"></span>
    </div>
    <div class="container mb-4">
      <div class="row mt-lg-n12 mt-md-n12 mt-n12 justify-content-center">
        <div class="col-xl-4 col-lg-5 col-md-7 mx-auto">
          <div class="card mt-8">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-warning shadow-warning border-radius-lg py-3 pe-1 text-center py-4">
                <h3 class="font-weight-bolder text-white"><?php echo _("2FA Validation"); ?></h3>
              </div>
            </div>
            <form method="post" action="validate_login.php">
              <?php
              if ($error_message != "") {
                echo '<div class="alert alert-danger"><strong>Error: </strong> ' . $error_message . '</div>';
              }
              ?>
              <div class="card-body py-4">
                <div class="input-group input-group-static mb-4">
                  <input type="text" id="code" name="code" class="form-control" placeholder="Enter Authentication Code" autocomplete="off" autofocus>
                </div>
                <div class="text-center">
                  <button type="submit" name="btnValidate" class="btn bg-gradient-warning w-100 mt-4 mb-0" value="Validate"><?php echo $functions->translate("go")?></button>
                </div>
              </div>


              
            </form>
          </div>
        </div>
      </div>
    </div>
    <footer class="footer py-4  ">
      <div class="container-fluid">
        <div class="row align-items-center justify-content-lg-between">
          <div class="col-lg-6 mb-lg-0 mb-4">
            <div class="copyright text-center text-sm text-muted text-lg-start">
              © <script>
                document.write(new Date().getFullYear())
              </script>
              made by
              <a href="https://practicle.dk" class="font-weight-bold" target="_blank">Practicle</a>
              - making better web for small business.
            </div>
          </div>
          <div class="col-lg-6">
            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
              <li class="nav-item">
                <a href="https://practicle.practicle.dk" class="nav-link text-muted" target="_blank">Support</a>
              </li>
              <li class="nav-item">
                <a href="https://practicle.dk/newspage.php" class="nav-link text-muted" target="_blank">News</a>
              </li>
              <li class="nav-item">
                <a href="./changelog.php" class="nav-link text-muted">Changelog</a>
              </li>
              <li class="nav-item">
                <a href="./upcomingfeatures.php" class="nav-link pe-0 text-muted">Upcoming features</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
    </div>
  </main>
  <script>
    function sendChangePasswordRequest() {
      var emailaddress = document.getElementById("emailaddress").value;
      var url = './forgotpassword.php?sendPasswordChangeRequest';

      $.ajax({
        url: url,
        data: {
          emailaddress: emailaddress
        },
        type: 'GET',
        success: function(data) {
          window.location.href = "./index.php";
        }
      });
    }
  </script>