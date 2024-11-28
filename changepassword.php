<?php
require_once "./inc/dbconnection.php";
require_once "./functions/functions.php";
require_once "./vendor/autoload.php";
include_once "./locales/i18n_setup.php";
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
    echo $SystemName = $functions->getSettingValue(13);
    ?>
  </title>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="./assets/css/material-dashboard.css?v=3.0.3" rel="stylesheet" />
</head>
<?php
$SubmitCode = $_GET["controlstring"];
$controlstringFromDB = getControlCode($SubmitCode);
if (empty($controlstringFromDB)) {
  header("location: index.php");
} else {
  $sql = "SELECT UserID FROM changepassword WHERE ControlString = '$SubmitCode'";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
  while ($row = mysqli_fetch_array($result)) {
    $UserID = $row['UserID'];
  }
}
?>
<?php

if (isset($_POST['submitchangepassword'])) {

  if ($_POST["newpassword1"] == $_POST["newpassword2"]) {
    $Password = $_POST["newpassword1"];
    $NewPassword = changePassword($Password, $UserID);
    deleteUserChangePasswordEntry($UserID);
    $redirectpage = "user_settings.php";
    $redirectpagego = "<meta http-equiv='refresh' content='1';url='$redirectpage'><p><b><div class='alert alert-success'><strong>User password changed</strong></div></b></p>";
    echo $redirectpagego;
  } else {
    echo "Your passwords doesnt match - please try again";
  }
}
?>

<body class="bg-gray-200">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
    <div class="container">
      <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 text-white" href="<?php echo $SystemURL ?>">
        <?php
        echo $SystemName = $functions->getSettingValue(13);
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
                <h3 class="font-weight-bolder text-white"><?php echo _("Reset password") ?></h3>
                <p class="mb-0 text-sm text-white"><?php echo _("Password must contain: Minimum 8 characters atleast 1 Alphabet and 1 Number") ?></p>
              </div>
            </div>
            <div class="card-body py-4">
              <form method="POST">
                <div class="col-xl-12 col-lg-12 col-md-12">
                  <div class="input-group input-group-static mb-4">
                    <input type="password" id="newpassword1" name="newpassword1" class="form-control" placeholder="<?php echo _("New Password") ?>" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Password must contain: Minimum 8 characters atleast 1 Alphabet and 1 Number") ?>" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                  </div>
                  <div class="input-group input-group-static mb-4">
                    <input type="password" id="newpassword2" name="newpassword2" class="form-control" placeholder="<?php echo _("Repeat") ?>" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Password must contain: Minimum 8 characters atleast 1 Alphabet and 1 Number") ?>" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}">
                  </div>
                  <div class="text-center">
                    <input type='submit' name='submitchangepassword' class="btn bg-gradient-warning w-100 mt-4 mb-0" value='Change'>
                  </div>
                </div>
              </form>
            </div>
          </div>
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