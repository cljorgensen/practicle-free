<!DOCTYPE html>
<html lang="en">
<?php

// Clear browser cache to ensure no sensitive data is stored
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//header("Content-Security-Policy: default-src 'self';script-src 'self'");
header("X-XSS-Protection: '1; mode=block'");
header("X-Content-Type-Options: 'nosniff'");

$domain = $_SERVER['HTTP_HOST'];

session_set_cookie_params([
  "lifetime" => 0, // 0 means "until the browser is closed"
  "path" => "/",
  "domain" => "$domain", // Set to your domain if needed
  "secure" => true, // Ensure the cookie is sent over HTTPS only
  "httponly" => true, // Make the cookie inaccessible to JavaScript
  "samesite" => "Strict" // Optional: Prevent the cookie from being sent with cross-site requests
]);

require_once "./inc/dbconnection.php";
require_once "./functions/functions.php";
include_once "./locales/i18n_setup.php";

// Get session timeout from the settings
$session_timeout = intval($functions->getSettingValue(63)); // Timeout in minutes
$session_timeout_seconds = $session_timeout * 60; // Convert to seconds

// Set the session GC max lifetime (in case session is stored in server-side session handler)
ini_set('session.gc_maxlifetime', $session_timeout_seconds);

session_start();
$_SESSION['session_timeout_seconds'] = $session_timeout_seconds;
// debug error level: low, medium, high
$functions->setDebugging("low");

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Secure CSRF token
}

$csrf_token = $_SESSION['csrf_token'];

// Check if the user is already logged in, if yes then redirect him to welcome page
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
  $cookie_name = "practicle_visited_url";
  if (!empty($_COOKIE[$cookie_name])) {
    $url = $_COOKIE[$cookie_name];
    header("location: $url");
  } else {
    header("location: index.php");
    exit;
  }
}

?>

<link rel="stylesheet" type="text/css" href="./assets/js/cookie_consent/cookieconsent.min.css" />
<script src="./assets/js/cookie_consent/cookieconsent.min.js"></script>
<script src="./jspracticle/initiate_cookie.js"></script>
<script src="./jspracticle/service-worker-register-min.js"></script>
<script src="./jspracticle/login-min.js"></script>

<?php
// Define variables and initialize with empty values
$username = $password = $UserID =  "";
$username_err = $password_err = "";
$donotlogin = 0;

// Check if all required extensions are loaded
$missingExtensions = checkRequiredExtensions();
$PracticleExtensionLoaded = empty($missingExtensions);

if (!$PracticleExtensionLoaded) {
  // Get the default PHP extension directory path
  $extensionDir = ini_get('extension_dir');
  $phpVersion = phpversion();

  echo "<p>Required PHP extensions are not loaded and need to be installed and loaded first!</p>";

  // Iterate over missing extensions and provide installation steps
  foreach ($missingExtensions as $extension) {
    if ($extension === 'practiclefunctions') {
      echo "<p>Please follow these steps to install the <strong>Practicle extension</strong>:</p>
                    <ol class='text-start' style='display: inline-block; text-align: left;'>
                        <li>Ensure you have downloaded the <a href=\"\inc\practiclefunctions.so\" 
                            download=\"practiclefunctions.so\" 
                            target=\"_blank\">
                            practiclefunctions.so
                        </a> file.</li>
                        <li>Copy the <b>practiclefunctions.so</b> file to your PHP extension directory here: <code>$extensionDir</code></li>
                        <li>Edit your <b>php.ini</b> file and add the line: <code>extension=practiclefunctions.so</code></li>
                        <li>You can also add it here:
                            <ul>
                                <li>/etc/php/$phpVersion/apache2/conf.d/20-practicle.ini</li>
                                <li>/etc/php/$phpVersion/fpm/conf.d/20-practicle.ini</li>
                                <li>/etc/php/$phpVersion/cli/conf.d/20-practicle.ini</li>
                            </ul>
                            Activate it for both CLI and Apache2/FPM as needed.</li>
                        <li>Restart your web server to load the new extension.</li>
                        <li>Verify by checking the output of <code>phpinfo()</code> or by refreshing this page.</li>
                    </ol>";
    } elseif ($extension === 'imap') {
      echo "<hr>
                    <p>Please follow these steps to enable the <strong>PHP IMAP extension</strong>:</p>
                    <ol class='text-start' style='display: inline-block; text-align: left;'>
                        <li>Locate your <b>php.ini</b> file (usually found in <code>/etc/php/$phpVersion/cli/php.ini</code> and <code>/etc/php/$phpVersion/apache2/php.ini</code>).</li>
                        <li>Open the <b>php.ini</b> file in a text editor.</li>
                        <li>Find the line that says <code>extension=imap</code>. If it's commented out (i.e., has a semicolon <code>;</code> at the beginning), remove the semicolon.</li>
                        <li>If the line is missing, add <code>extension=imap.so</code> to the file.</li>
                        <li>Restart your web server (e.g., <code>sudo systemctl restart apache2</code>).</li>
                        <li>Verify the IMAP extension is enabled by checking the output of <code>phpinfo()</code> or running <code>php -m | grep imap</code>.</li>
                    </ol>";
    } else {
      echo "<hr>
                    <p>The <strong>$extension</strong> extension is missing. Please refer to your PHP version's documentation to install and enable it.</p>
                    <p>Steps (general):</p>
                    <ol class='text-start' style='display: inline-block; text-align: left;'>
                        <li>Install the extension (e.g., <code>sudo apt install php-$extension</code> or equivalent for your OS).</li>
                        <li>Add <code>extension=$extension.so</code> to your <b>php.ini</b> file if necessary.</li>
                        <li>Restart your web server to apply the changes.</li>
                        <li>Verify the extension is enabled by checking the output of <code>phpinfo()</code> or <code>php -m | grep $extension</code>.</li>
                    </ol>";
    }
  }

  echo "<p><a href='./install.php' target='_blank'>Refresh</a> once you have installed the missing extensions.</p>";
  die();
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: logout.php");
    exit();
  }

  if ($PracticleExtensionLoaded == false) {
    $password_err = "Practicle extension is not loaded!";
  }

  $username = trim($_POST["username"]);
  $username = mysqli_real_escape_string($conn, $username);

  // Check if username is empty
  if (empty($username)) {
    $username_err = "Please enter username or email.";
  }

  $password = trim($_POST["password"]);

  // Check if password is empty
  if (empty($password)) {
    $password_err = "Please enter password.";
  }

  // Validate credentials
  if (empty($username_err) && empty($password_err)) {
    // Prepare a select statement
    $sql = "SELECT id, Username, Password FROM users WHERE Username = ? OR Email = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
      // Bind variables to the prepared statement as parameters
      mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_email);

      // Set parameters
      $param_username = $username;
      $param_email = $username;

      // Attempt to execute the prepared statement
      if (mysqli_stmt_execute($stmt)) {
        // Store result
        mysqli_stmt_store_result($stmt);

        // Check if username exists, if yes then verify password
        if (mysqli_stmt_num_rows($stmt) == 1) {
          // Bind result variables
          mysqli_stmt_bind_result($stmt, $id, $username, $databasePassword);

          if (mysqli_stmt_fetch($stmt)) {
            // Lets grap the UserID
            $UserID = $id;
            // Check if user is in Active Directory
            $LDAPStatus = $functions->getSettingValue(56);
            $validatedViaLDAP = false;
 
            if ($LDAPStatus == "1") {
              $validatedViaLDAP = getAuthenticatedFromLDAP($username, $password);
              if ($validatedViaLDAP == true) {
                $databasePassword = $password;
                $hashedPassword = $databasePassword;
              } else {
                $hashedPassword = $functions->SaltAndHashPasswordForCompare($password);
              }
            } else {
              $hashedPassword = $functions->SaltAndHashPasswordForCompare($password);
            }

            $count = 0;

            $ip = $_SERVER["REMOTE_ADDR"];

            mysqli_query($conn, "DELETE FROM logins WHERE TimeStamp < (now() - interval 90 day)");
            
            $sql = "SELECT COUNT(*) AS ANTAL
                    FROM logins
                    WHERE IP LIKE ? AND UserName LIKE ? AND TimeStamp > (now() - interval 5 minute)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $ip, $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result->num_rows === 0) {
              exit('No rows');
            }

            while ($row = mysqli_fetch_assoc($result)) {
              $count = $row['ANTAL'];
            }
            //$count = $functions->getNumberOfLogins($ip, $username);
            if ($count > 5) {
              session_destroy();
              // Redirect to quarantine page
              header("Location: quarantine.php");
              exit();
            }

            if ($hashedPassword !== $databasePassword && $count < 6) {
              $sql = "INSERT INTO logins (UserName,IP,TimeStamp) VALUES (?,?,CURRENT_TIMESTAMP)";
              $stmt = mysqli_prepare($conn, $sql);
              mysqli_stmt_bind_param($stmt, "ss", $username, $ip);
              mysqli_stmt_execute($stmt);
            }

            if ($hashedPassword === $databasePassword && $count < 6) {
              $functions->setUserSessionVariables($UserID);

              if ($_SESSION['Active'] == "0" || $_SESSION['Active'] == "") {
                echo "<script> alert('Your account has been deactivated!'); </script>";
                session_destroy();
                header("location: login.php");
                exit;
              }
              
              mysqli_free_result($result);

              $_SESSION["locked"] = false;
              $_SESSION["loggedin"] = true;
              $_SESSION['ValidAuth'] = 1;
              $_SESSION['timeout_duration'] = intval($functions->getSettingValue(63)) - 1;
              $_SESSION['LAST_ACTIVITY'] = time();

              $TeamID = $functions->getUserTeam($UserID);
              $TeamName = $functions->getUserTeamName($UserID);

              if (!empty($TeamID)) {
                $_SESSION['teamid'] = $TeamID;
                $_SESSION['Teamname'] = $TeamName;
              } else {
                $_SESSION['teamid'] = 0;
                $_SESSION['Teamname'] = "";
              }

              $functions->instantiateUserGroupsRoles($UserID);

              $LeaderOfArray = array();
              $sql = "SELECT users.ID AS UserID
                      FROM users
                      WHERE users.RelatedManager = $UserID AND users.Active = 1";
              $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

              while ($row = mysqli_fetch_array($result)) {
                $LeaderOfArray[] = $row['UserID'];
              }

              mysqli_free_result($result);

              $_SESSION['LeaderOfArray'] = array_unique($LeaderOfArray);

              createLastLoginUser($UserID);
              if (strcmp($_SESSION['googlesecretcode'], "0Km#9kQyfI1CLkthWhDb#F") !== 0) {
                //$functions->sessiondbcreate();
                header("Location: validate_login.php"); // Redirect user to validate auth code
                exit;
              } elseif (strcmp($_SESSION['googlesecretcode'], "0Km#9kQyfI1CLkthWhDb#F") === 0) {
                //$functions->sessiondbcreate();

                if ($_SESSION['usertype'] !== 2) {
                  $cookie_name = "practicle_visited_url";
                  echo "2: cookie_name practicle_visited_url<br>";
                  // Check if the redirect URL is set in the session
                  if (isset($_SESSION['redirect_url'])) {
                    // Redirect the user back to the stored URL
                    $redirect_url = $_SESSION['redirect_url'];
                    unset($_SESSION['redirect_url']); // Remove the stored URL from session  
                    header("Location: $redirect_url");
                    exit();
                  }

                  if (!empty($_COOKIE[$cookie_name])) {
                    header("location: $_COOKIE[$cookie_name]");
                    exit;
                  } else {
                    header("location: index.php");
                    exit;
                  }
                } else {
                  header("Location: helpdesk.php"); // Redirect user to validate auth code
                  exit;
                }
              } else {
                header("Location: logout.php"); // Redirect user to validate auth code
                exit;
              }
            } else {
              // Display an error message if password is not valid
              $password_err = "The password you entered was not valid.";
            }
          }
        } else {
          // Display an error message if username doesn't exist
          $username_err = "No account found with that username or email.";
        }
      } else {
        echo "<script>alert('Something went wrong... please try again later')</script>";
      }
    } else {
      die("<pre>" . mysqli_error($conn) . PHP_EOL . $sql . "</pre>");
    }
  }
}
$SystemName = $functions->getSettingValue(13);
?>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Practicle - et ITIL webbaseret system til styring af henvendelser, bestillinger, ændringer, projekter, opgaver og meget mere">
  <meta property="og:type" content="Practicle" />
  <meta property="og:title" content="Management system til store og små virksomheder" />
  <meta property="og:description" content="Et ITIL understøttende moderne web baseret system til styring af henvendelser, bestillinger, ændringer, projekter, opgaver og meget mere" />
  <meta property="og:image" content="https://practicle.dk/assets/images/practicle_logo_only_small.png" />
  <meta property="og:url" content="https://www.practicle.dk/" />
  <meta property="og:site_name" content="Practicle" />
  <!-- PWA -->
  <link rel="manifest" href="/manifest.php">
  <!-- ios support -->
  <link rel="apple-touch-icon" href="images/icons/icon-72x72.png" />
  <link rel="apple-touch-icon" href="images/icons/icon-96x96.png" />
  <link rel="apple-touch-icon" href="images/icons/icon-128x128.png" />
  <link rel="apple-touch-icon" href="images/icons/icon-144x144.png" />
  <link rel="apple-touch-icon" href="images/icons/icon-152x152.png" />
  <link rel="apple-touch-icon" href="images/icons/icon-192x192.png" />
  <link rel="apple-touch-icon" href="images/icons/icon-384x384.png" />
  <link rel="apple-touch-icon" href="images/icons/icon-512x512.png" />
  <meta name="apple-mobile-web-app-status-bar" content="#1a2035" />
  <meta name="theme-color" content="#1a2035" />
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
  <script src="./assets/fontawesome-free-6.1.1-web/js/all.min.js"></script>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="./assets/css/material-dashboard.css?v=3.0.4" rel="stylesheet" />
</head>
<?php $url = $functions->getLoginPicture(); ?>

<body class="bg-gray-200">
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg position-absolute top-0 z-index-3 w-100 shadow-none my-3 navbar-transparent mt-4">
    <div class="container">
      <a class="navbar-brand font-weight-bolder ms-lg-0 ms-3 text-white" href="./login.php">
        <?php echo $SystemName; ?>
      </a>
      <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon mt-2">
          <span class="navbar-toggler-bar bar1"></span>
          <span class="navbar-toggler-bar bar2"></span>
          <span class="navbar-toggler-bar bar3"></span>
        </span>
      </button>
    </div>
    </div>
  </nav>
  <?php
  $FacebookUrl = $functions->getSettingValue(45);
  $HomepageUrl = $functions->getSettingValue(46);
  $LinkedIn = $functions->getSettingValue(47);
  $LDAPEnabled = $functions->getSettingValue(56);
  
  if ($LDAPEnabled == "1") {
    $Domain = $functions->getSettingValue(57);
  } else {
    $Domain = "";
  }
  ?>
  <!-- End Navbar -->
  <main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-100" style="background-image: url('<?php echo $url ?>');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container my-auto">
        <div class="row">
          <div class="col-lg-4 col-md-8 col-12 mx-auto">
            <div class="card z-index-0 fadeIn1 fadeInBottom">
              <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                <div class="bg-gradient-primary shadow-primary border-radius-lg py-3 pe-1">
                  <h4 class="text-white font-weight-bolder text-center mt-2 mb-0"><?php echo _("Sign in") ?></h4>
                  <div class="row mt-3">
                    <div class="col-2 text-center ms-auto">
                      <a class="btn btn-link px-3" href="<?php echo $FacebookUrl ?>" target="_blank">
                        <i class="fa-brands fa-facebook-square text-white text-lg"></i>
                      </a>
                    </div>
                    <div class="col-2 text-center px-1">
                      <a class="btn btn-link px-3" href="<?php echo $HomepageUrl ?>" target="_blank">
                        <i class="fa-solid fa-earth-europe text-white text-lg"></i>
                      </a>
                    </div>
                    <div class="col-2 text-center me-auto">
                      <a class="btn btn-link px-3" href="<?php echo $LinkedIn ?>" target="_blank">
                        <i class="fa-brands fa-linkedin text-white text-lg"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <form role="form" class="text-start" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <div class="input-group input-group-outline mb-3">
                    <input type="text" name="username" id="username" class="form-control ps-3" placeholder="<?php echo _("Username") . " " . _("or") . " " . _("email") ?>" autocomplete="on">
                  </div>
                  <div class="input-group input-group-outline mb-3">
                    <input type="password" name="password" id="password" class="form-control ps-3" placeholder="<?php echo _("Password") ?>" autocomplete="on">
                  </div>
                  <div>
                    <small><?php echo _($Domain) ?></small>
                  </div>
                  <div class="text-center">
                    <button type="submit" class="btn bg-gradient-primary w-100 my-4 mb-2"><?php echo _("Sign in") ?></button>
                  </div>
                  <p class="mt-4 text-sm text-center">
                    <?php echo _("Dont have an account?") ?>
                    <a href="./register.php" class="text-primary text-gradient font-weight-bold"><?php echo _("Sign up") ?></a>
                  </p>
                  <p class="mt-4 text-sm text-center">
                    <?php echo _("Forgot password?") ?>
                    <a href="./forgotpassword.php" class="text-primary text-gradient font-weight-bold"><?php echo _("Reset now") ?></a>
                  </p>
                  <?php
                  if (!$PracticleExtensionLoaded) {
                    // Get the default PHP extension directory path
                    $extensionDir = ini_get('extension_dir');
                    $phpVersion = phpversion();

                    echo "<p>Required PHP extensions are not loaded and need to be installed and loaded first!</p>";

                    if (in_array('practiclefunctions', $missingExtensions)) {
                      echo "<p>Please follow these steps to install the <strong>practicle extension</strong>:</p>
                <ol class='text-start' style='display: inline-block; text-align: left;'>
                    <li>Ensure you have downloaded the <a href=\"https://support.practicle.dk/backups/releases/hekx85klqcs5yhw7vfw5mq9sak0g/practiclefunctions_$phpVersion.so\" 
                        download=\"practiclefunctions.so\" 
                        target=\"_blank\">
                        practiclefunctions.so
                    </a> file.</li>
                    <li>Copy the <b>practiclefunctions.so</b> file to your PHP extension directory here: <code>$extensionDir</code></li>
                    <li>Edit your <b>php.ini</b> file and add the line: <code>extension=practiclefunctions.so</code></li>
                    <li>You can also add it here:
                    <ul>
                        <li>/etc/php/$phpVersion/apache2/conf.d/20-practicle.ini or /etc/php/$phpVersion/fpm/conf.d/20-practicle.ini</li>
                        <li>/etc/php/$phpVersion/cli/conf.d/20-practicle.ini (you need to activate it both for cli and apache2/fpm)</code></li>
                    </ul>
                    <li>Restart your web server to load the new extension.</li>
                    <li>Verify by checking the output of <code>phpinfo()</code> or this page again.</li>
                </ol>";
                    }

                    // Guide for PHP IMAP extension if missing
                    if (in_array('imap', $missingExtensions)) {
                      echo "<hr>
                <p>Please follow these steps to enable the <strong>PHP IMAP extension</strong>:</p>
                <ol class='text-start' style='display: inline-block; text-align: left;'>
                    <li>Locate your <b>php.ini</b> file (usually found in <code>/etc/php/$phpVersion/cli/php.ini</code> and <code>/etc/php/$phpVersion/apache2/php.ini), you need to activate it both for cli and apache2</code>.</li>
                    <li>Open the <b>php.ini</b> file in a text editor.</li>
                    <li>Find the line that says <code>extension=imap</code>. If it's commented out (i.e., has a semicolon <code>;</code> at the beginning), remove the semicolon.</li>
                    <li>If the line is missing, add <code>extension=imap.so</code> to the file.</li>
                    <li>Restart your web server (e.g., <code>sudo systemctl restart apache2</code>).</li>
                    <li>Verify the IMAP extension is enabled by checking the output of <code>phpinfo()</code> or running <code>php -m | grep imap</code>.</li>
                </ol>";
                    }

                    echo "<p><a href='./install.php' target='_blank'>refresh</a></p>";
                    die();
                  }
                  ?>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="footer position-absolute bottom-2 py-2 w-100">
        <div class="container">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-12 col-md-6 my-auto">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <?php echo date('Y'); ?>
                <a href="https://practicle.dk" class="text-sm text-muted text-lg-start" target="_blank"> Practicle</a>
              </div>
            </div>
            <div class="col-12 col-md-6">
              <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                <li class="nav-item">
                  <a href="https://support.practicle.dk" class="nav-link text-white" target="_blank"><?php echo _("Support") ?></a>
                </li>
                <li class="nav-item">
                  <a href="https://practicle.dk/order.php" class="nav-link text-white" target="_blank"><?php echo _("Try") ?></a>
                </li>
                <li class="nav-item">
                  <a href="https://practicle.dk/index.php#presentation" class="nav-link pe-0 text-white" target="_blank"><?php echo _("About") ?></a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>
  <!--   Core JS Files   -->
  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>

</body>
</html>