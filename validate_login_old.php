<?php include("./header.php"); ?>
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
<form method="post" action="validate_login.php">
  <h4>Google Authenticator</h4><br>
  <?php
  if ($error_message != "") {
    echo '<div class="alert alert-danger"><strong>Error: </strong> ' . $error_message . '</div>';
  }
  ?>
  <div class="form-group">
    <div class="input-group">
      <div class="input-group-prepend">
        <span class="input-group-text text-white">
          <i class="material-icons">fiber_pin</i>
        </span>
      </div>
      <input type="text" id="code" name="code" class="form-control has-feedback-left text-white" placeholder="Enter Authentication Code" autofocus>
    </div>
  </div>
  <div class="form-group">
    <input type="submit" name="btnValidate" class="btn btn-success" value="Validate">
  </div>
</form>
<?php include("./footer.php"); ?>