<?php
// Ensure https is used
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
  $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  header('HTTP/1.1 301 Moved Permanently');
  header('Location: ' . $redirect);
  exit();
}

// Some security settings for the session
session_set_cookie_params([
  'lifetime' => 0, // 0 means "until the browser is closed"
  'path' => '/',
  'domain' => 'practicle.dk', // Set to your domain if needed
  'secure' => true, // Ensure the cookie is sent over HTTPS only
  'httponly' => true, // Make the cookie inaccessible to JavaScript
  'samesite' => 'Strict' // Optional: Prevent the cookie from being sent with cross-site requests
]);

session_start();

include_once('./inc/dbconnection.php');
include_once('./functions/functions.php');
include_once('./locales/i18n_setup.php');
include_once('./vendor/autoload.php');

// Store the token in the session
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = $functions->generateCsrfToken();
}

$SystemName = $functions->getSettingValue(13);

// Define variables and initialize with empty values
$username = $password = $confirm_password = $firstname = $lastname = "";
$username_err = $password_err = $confirm_password_err = $firstname_err = $lastname_err = $create_user_err = $create_user_success = "";

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Validate CSRF token
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
  }

  $ipAddress = $_SERVER['REMOTE_ADDR'];
  $currentTime = date('Y-m-d H:i:s');

  // Rate limiting: Check the number of registration attempts from the same IP within the last 2 hours
  $stmt = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE ip_address = ? AND request_time > DATE_SUB(?, INTERVAL 2 HOUR)");
  $stmt->bind_param("ss", $ipAddress, $currentTime);
  $stmt->execute();
  $stmt->bind_result($requestCount);
  $stmt->fetch();
  $stmt->close();

  if ($requestCount >= 1) {
    // Redirect to quarantine page
    header("Location: quarantine.php");
    exit();
  }

  // No spamming exists - continue
  $username_err = $firstname_err = $lastname_err = $password_err = $confirm_password_err = "";
  $username = $firstname = $lastname = $password = $confirm_password = "";

  // Validate username (email address)
  if (empty(trim($_POST["username"]))) {
    $username_err = "Please enter a valid email address";
  } elseif (!filter_var(trim($_POST["username"]), FILTER_VALIDATE_EMAIL)) {
    $username_err = "Please enter a valid email address";
  } else {
    // Prepare a select statement
    $sql = "SELECT id FROM users WHERE email = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "s", $param_username);

      // Set parameters
      $param_username = trim($_POST["username"]);

      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        /* store result */
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
          $username_err = "This email already exists";
        } else {
          $username = trim($_POST["username"]);
        }
      }
    }
    // Close statement
    mysqli_stmt_close($stmt);
  }

  // Validate firstname
  if (empty(trim($_POST["firstname"]))) {
    $firstname_err = "Please enter a firstname";
  } else {
    $firstname = trim($_POST["firstname"]);
  }

  // Validate lastname
  if (empty(trim($_POST["lastname"]))) {
    $lastname_err = "Please enter a lastname";
  } else {
    $lastname = trim($_POST["lastname"]);
  }

  // Validate password
  if (empty(trim($_POST["password"]))) {
    $password_err = "Please enter a password";
  } else {
    $password = trim($_POST["password"]);
    $uppercase = preg_match('@[A-Z]@', $password);
    $lowercase = preg_match('@[a-z]@', $password);
    $number = preg_match('@[0-9]@', $password);
    $specialChars = preg_match('@[^\w]@', $password);

    if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
      $password_err = "Password must be at least 8 characters and include at least one uppercase letter, one lowercase letter, one number, and one special character";
    }
  }

  // Validate confirm password
  if (empty(trim($_POST["confirm_password"]))) {
    $confirm_password_err = "Please confirm password";
  } else {
    $confirm_password = trim($_POST["confirm_password"]);
    if (empty($password_err) && ($password != $confirm_password)) {
      $confirm_password_err = "Passwords did not match";
    }
  }

  // Check input errors before inserting in database
  if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($firstname_err) && empty($lastname_err)) {
    // Prepare an insert statement
    $Company = $functions->getSettingValue(48);
    $sql = "INSERT INTO users (Username, Password, Email, CompanyID, RelatedUserTypeID, Firstname, Lastname, Active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($conn, $sql)) {
      // Set parameters
      $param_username = $username;
      $param_password = @$functions->SaltAndHashPasswordForCompare($password); // Creates a password hash
      $param_email = $username;
      $param_usertype = 2;
      $param_firstname = $firstname;
      $param_lastname = $lastname;
      $param_active = "1";

      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "sssiissi", $param_username, $param_password, $param_email, $Company, $param_usertype, $param_firstname, $param_lastname, $param_active);

      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        // Log the registration attempt
        $stmt = $conn->prepare("INSERT INTO registrations (ip_address, request_time) VALUES (?, NOW())");
        $stmt->bind_param("s", $ipAddress);
        $stmt->execute();
        $stmt->close();

        // Get the last inserted ID
        $last_id = mysqli_insert_id($conn);
        $Email = getUserEmailFromID($last_id);
        $SystemURL = $functions->getSettingValue(17);
        $SystemName = $functions->getSettingValue(13);
        $FirstName = getUserFirstName($last_id);
        $LastName = getUserLastName($last_id);

        $MailTemplateSubject = getMailTemplateSubject(8);
        $MailTemplateContent = getMailTemplateContent(8);

        $MailTemplateSubject = str_replace("<:systemname:>", $SystemName, $MailTemplateSubject);

        $MailTemplateContent = str_replace("<:firstname:>", $FirstName, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:lastname:>", $LastName, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:systemname:>", $SystemName, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:systemurl:>", $SystemURL, $MailTemplateContent);
        sendMailToSinglePerson($Email, "$FirstName $LastName", $MailTemplateSubject, $MailTemplateContent);

        // Redirect to login page
        header("location: login.php");
        $success = true; // flag that the user was created
      } else {
        $create_user_err = "Something went wrong. Please try again later";
      }
    }
    // Close statement
    mysqli_stmt_close($stmt);
  }
  // Close connection
  mysqli_close($conn);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="./images/logo.png">
  <link rel="icon" type="image/png" href="./images/favicon.ico">
  <title><?php echo $SystemName ?></title>
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

<body class="">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
    <div class="container">
      <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 text-white" href="./register.php">
        <?php echo $SystemName ?>
      </a>
      <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon mt-2">
          <span class="navbar-toggler-bar bar1"></span>
          <span class="navbar-toggler-bar bar2"></span>
          <span class="navbar-toggler-bar bar3"></span>
        </span>
      </button>
      <div class="collapse navbar-collapse w-100 pt-3 pb-2 py-lg-0" id="navigation">
      </div>
    </div>
  </nav>
  <!-- End Navbar -->
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('https://images.unsplash.com/photo-1551214012-84f95e060dee?ixlib=rb-1.2.1&auto=format&fit=crop&w=1651&q=80');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-4 col-md-8 mx-auto">
            <div class="card z-index-0 fadeIn1 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                  <h4 class="text-white font-weight-bolder text-center mt-2 mb-0"><?php echo _("Sign up"); ?></h4>
                </div>
              </div>
              <form id="submitForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="card-body">
                  <div class="input-group input-group-dynamic mb-3">
                    <label class="form-label"></label>
                    <input type="text" id="firstname" name="firstname" placeholder="<?php echo _("Firstname"); ?>" class="form-control">
                  </div>
                  <div class="input-group input-group-dynamic mb-3">
                    <label class="form-label"></label>
                    <input type="text" id="lastname" name="lastname" placeholder="<?php echo _("Lastname"); ?>" class="form-control">
                  </div>
                  <div class="input-group input-group-dynamic mb-3">
                    <label class="form-label"></label>
                    <input type="email" id="username" name="username" placeholder="<?php echo _("Email"); ?>" class="form-control">
                  </div>
                  <?php if (!empty($username_err)) {
                    echo "<p class=\"text-sm mt-3 mb-0 text-warning\">" . $username_err . "</p>";
                  }
                  ?>
                  <div class="input-group input-group-dynamic mb-3">
                    <label class="form-label"></label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="<?php echo _("Password"); ?>" title="<?php echo _("minimum 8 characters, at least 1 alphabet, 1 number and 1 special character"); ?>">
                  </div>
                  <?php if (!empty($password_err)) {
                    echo "<p class=\"text-sm mt-3 mb-0 text-warning\">" . $password_err . "</p>";
                  }
                  ?>
                  <div class="input-group input-group-dynamic mb-3">
                    <label class="form-label"></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="<?php echo _("Repeat Password"); ?>" title="<?php echo _("minimum 8 characters, at least 1 alphabet, 1 number and 1 special character"); ?>">
                  </div>
                  <?php if (!empty($confirm_password_err)) {
                    echo "<p class=\"text-sm mt-3 mb-0 text-warning\">" . $confirm_password_err . "</p>";
                  }
                  ?>
                  <div class="text-center">
                    <button type="submit" id="submit" name="submit" class="btn bg-gradient-dark w-100 my-4 mb-2" value="Submit"><?php echo _("Sign up"); ?></button>
                  </div>
                  <?php if (!empty($create_user_err)) {
                    echo "<p class=\"text-sm mt-3 mb-0 text-danger\">" . $create_user_err . "</p>";
                  }
                  ?>
                  <?php if (!empty($create_user_success)) {
                    echo "<p class=\"text text-success\">" . $create_user_success . "</p>";
                  }
                  ?>
                  <br>
                  <p class="text-sm mt-3 mb-0"><?php echo _("Password must contain: minimum 8 characters, at least 1 alphabet, 1 number and 1 special character"); ?></p>
                  <br>
                  <p class="text-sm mt-3 mb-0"><?php echo _("Already have an account?"); ?> <a href="./login.php" class="text-dark font-weight-bolder"><?php echo _("Login"); ?></a></p>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <footer class="footer position-absolute bottom-2 py-2 w-100">
        <div class="container">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-12 col-md-6 my-auto">
              <div class="copyright text-center text-sm text-white text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>
                <a href="https://practicle.dk" class="font-weight-bold" target="_blank">Practicle</a>
                - making better web for small business.
              </div>
            </div>
            <div class="col-12 col-md-6">
              <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                <li class="nav-item">
                  <a href="https://support.practicle.dk" class="nav-link text-white" target="_blank">Support</a>
                </li>
                <li class="nav-item">
                  <a href="https://practicle.dk/newspage.php" class="nav-link text-white" target="_blank">News</a>
                </li>
                <li class="nav-item">
                  <a href="https://practicle.dk/order.php" class="nav-link text-white" target="_blank">Try</a>
                </li>
                <li class="nav-item">
                  <a href="https://practicle.dk/index.php#presentation" class="nav-link pe-0 text-white" target="_blank">About</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>
</body>
<?php
if (isset($success) && $success) {
  echo '<script>
        setTimeout(function() {
            window.location.href = "login.php";
        }, 3000); // 3000 milliseconds = 3 seconds
    </script>';
} else {
  // Your normal registration form HTML goes here
}
?>

</html>