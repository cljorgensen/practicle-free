<?php
// Ensure https is used
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
  $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  header('HTTP/1.1 301 Moved Permanently');
  header('Location: ' . $redirect);
  exit();
}

WebsiteGuard();
//---------------------------------------------------------------//
function WebsiteGuard()
{
  $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

  $blockedAgents = [
    'webzip', 'httrack', 'wget', 'FlickBot', 'downloader', 'productionbot', 'superbot', 'PersonaPilot', 'NPBot',
    'WebCopier', 'vayala', 'imagefetch', 'Microsoft URL Control', 'mac finder', 'emailreaper', 'emailsiphon',
    'emailwolf', 'emailmagnet', 'emailsweeper', 'Indy Library', 'FrontPage', 'cherry picker', 'netzip',
    'Share Program', 'TurnitinBot', 'full web bot', 'zeus', 'Googlebot', 'Bingbot', 'YandexBot', 'DuckDuckBot',
    'Applebot', 'Sogou Spider', 'Baiduspider', 'Exabot', 'facebookexternalhit', 'facebot', 'Python-urllib',
    'python-requests', 'python-httpx', 'python-urllib3', 'okhttp', 'HeadlessChrome', 'SemrushBot', 'AhrefsBot',
    'Nessus', 'MJ12bot', 'SEMrushBot', 'DotBot', 'linkdexbot', 'megaindex.ru', 'AdsBot-Google', 'Google-Read-Aloud',
    'PetalBot', 'rogerbot', 'AppleBot', 'Slackbot', 'Embedly', 'Baiduspider-image', 'Baiduspider-video',
    'Baiduspider-news', 'Baiduspider-favo', 'Baiduspider-cpro', 'Baiduspider-ads', 'Sogou Pic Spider',
    'Sogou head spider', 'Sogou web spider', 'Sogou Orion spider', 'Sogou-Test-Spider', 'Konqueror', 'Apache Nutch',
    'crawler4j', 'Google Web Preview', 'SeznamBot', 'ia_archiver', 'GigaBot', 'MetaURI', 'SISTRIX', 'SiteExplorer',
    'BacklinkCrawler', 'DotBot', 'ZoominfoBot', 'BLEXBot', 'Aboundex', 'XoviBot', 'BingPreview'
  ];

  foreach ($blockedAgents as $agent) {
    if (stripos($userAgent, $agent) !== false) {
      http_response_code(403);
      die("Bot behavior suspected... if this happens multiple times - please report this to practicle.dk by support mail<br><br>Please reload page\n");
    }
  }

  if (preg_match('~(bot|crawl)~i', $userAgent)) {
    http_response_code(403);
    die("Bot behavior suspected... if this happens multiple times - please report this to practicle.dk by support mail<br><br>Please reload page\n");
  }
}

require_once "./inc/dbconnection.php";
require_once "./functions/functions.php";
require_once "./vendor/autoload.php";
include_once "./locales/i18n_setup.php";

// Some security settings for the session
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'domain' => 'practicle.dk', // Adjust to your domain if necessary
  'secure' => true, // True since we have ensured HTTPS
  'httponly' => true,
  'samesite' => 'Strict'
]);

session_start();

// Function to generate a random CSRF token
function generateCsrfToken()
{
  return bin2hex(random_bytes(32));
}

// Store the token in the session
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = generateCsrfToken();
}

// Include the token in the form as a hidden input
$csrf_token = $_SESSION['csrf_token'];

$ipAddress = $_SERVER['REMOTE_ADDR'];
$currentTime = date('Y-m-d H:i:s');

// Rate limiting: Check the number of requests from the same IP and email within the last hour
$stmt = $conn->prepare("SELECT COUNT(*) FROM password_reset_requests WHERE (ip_address = ?) AND request_time > DATE_SUB(?, INTERVAL 20 MINUTE)");
$stmt->bind_param("ss", $ipAddress, $currentTime);
$stmt->execute();
$stmt->bind_result($requestCount);
$stmt->fetch();
$stmt->close();
if ($requestCount >= 3) {
  // Redirect to quarantine page
  header("Location: quarantine.php");
  exit();
}
$message = '';
if (isset($_POST["sendPasswordChangeRequest"])) {
  // Validate CSRF token
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
  }

  $EmailAddress = $_POST["emailaddress"];
  $UsersID = $functions->getUserIDFromEmail($EmailAddress);

  if ($UsersID === false) {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $currentTime = date('Y-m-d H:i:s');

    // Rate limiting: Check the number of requests from the same IP and email within the last hour
    $stmt = $conn->prepare("SELECT COUNT(*) FROM password_reset_requests WHERE (email = ? OR ip_address = ?) AND request_time > DATE_SUB(?, INTERVAL 20 MINUTE)");
    $stmt->bind_param("sss", $EmailAddress, $ipAddress, $currentTime);
    $stmt->execute();
    $stmt->bind_result($requestCount);
    $stmt->fetch();
    $stmt->close();

    if ($requestCount >= 3) {
      // Redirect to quarantine page
      header("Location: quarantine.php");
      exit();
    } else {
      // Log the request
      $stmt = $conn->prepare("INSERT INTO password_reset_requests (email, ip_address, request_time) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $EmailAddress, $ipAddress, $currentTime);
      $stmt->execute();
      $stmt->close();
    }
    $message = "No user with that email address. Your request has been logged, you have 3 retries in all before reaching a 20 minute quarantine state.";
  } else {
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $currentTime = date('Y-m-d H:i:s');

    // Rate limiting: Check the number of requests from the same IP and email within the last hour
    $stmt = $conn->prepare("SELECT COUNT(*) FROM password_reset_requests WHERE (email = ? OR ip_address = ?) AND request_time > DATE_SUB(?, INTERVAL 20 MINUTE)");
    $stmt->bind_param("sss", $EmailAddress, $ipAddress, $currentTime);
    $stmt->execute();
    $stmt->bind_result($requestCount);
    $stmt->fetch();
    $stmt->close();

    if ($requestCount >= 3) {
      // Redirect to quarantine page
      header("Location: quarantine.php");
      exit();
    } else {
      // Log the request
      $stmt = $conn->prepare("INSERT INTO password_reset_requests (email, ip_address, request_time) VALUES (?, ?, ?)");
      $stmt->bind_param("sss", $EmailAddress, $ipAddress, $currentTime);
      $stmt->execute();
      $stmt->close();

      // Proceed with the password reset
      changePasswordSubmit($UsersID);
      $message = 'Password reset submitted - check your email for further instructions.<br><br><a href="./login.php">Back to login</a>';
    }
  }
}
?>
<!DOCTYPE html>
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
    echo $SystemName;
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
                <h3 class="font-weight-bolder text-white"><?php echo _("Forgot password"); ?></h3>
                <p class="mb-0 text-sm text-white"><?php echo _("You will receive an e-mail typical within 60 seconds"); ?></p>
              </div>
            </div>
            <div class="card-body py-4">
              <form method="post" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="input-group input-group-static mb-4">
                  <label>Email</label>
                  <input type="email" id="emailaddress" name="emailaddress" class="form-control" placeholder="" required>
                </div>
                <div class="text-center">
                  <button type="submit" name="sendPasswordChangeRequest" class="btn bg-gradient-warning w-100 mt-4 mb-0"><?php echo _("Reset"); ?></button>
                </div>
              </form>
              <?php if ($message) : ?>
                <div class="text-center mt-4">
                  <p class="text"><?php echo $message; ?></p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <footer class="footer py-4">
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
                <a href="https://support.practicle.dk" class="nav-link text-muted" target="_blank">Support</a>
              </li>
              <li class="nav-item">
                <a href="https://practicle.dk/newspage.php" class="nav-link text-muted" target="_blank">News</a>
              </li>
              <li class="nav-item">
                <a href="./changelog.php" class="nav-link text-muted">Changelog</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
  </main>
</body>

</html>