<?php
// PHP configuration settings
$postMaxSize = '200M';
$uploadMaxSize = '200M';
ini_set('post_max_size', $postMaxSize);
ini_set('upload_max_filesize', $uploadMaxSize);

$min = "-min";
//$min = "";
// Set default timezone
date_default_timezone_set('Europe/Copenhagen');

require_once "./inc/dbconnection.php";
require_once "./functions/functions.php";
require_once "./functions/datatables.php";
require_once "./getdata.php";
require_once "./locales/i18n_setup.php";
require_once "./vendor/autoload.php";

// debug error level: low, medium, high
$functions->setDebugging("low");

// Check if the session has expired (based on last activity time)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $_SESSION['session_timeout_seconds']) {
  // Redirect to logout page
  header("Location: logout.php");
  exit();
}

// Update the last activity time for the current session
$_SESSION['last_activity'] = time();

$functions->checkActiveSession();
?>
<!DOCTYPE html>
<html lang="en">
<?php
// Set some variables from session
$UserSessionID = $_SESSION["id"];
$UserType = $_SESSION['usertype'];
$role_array = $_SESSION['memberofroles'];
$group_array = $_SESSION['memberofgroups'];

$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
$redirect_url = "";

$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));

// Check if the user is logged in, if not then redirect him to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION['ValidAuth'] !== 1) {
  header("location: logout.php");
  exit;
}

// Check if the user is logged in, if not then redirect him to login page
if ($_SESSION["locked"] == true) {
  header("location: lock.php");
  exit;
}

// Convert timeout_duration to seconds from minutes
$timeout_duration = intval($_SESSION['timeout_duration']) * 60;

$LockStatus = getLockStatus($UserSessionID);
if ($LockStatus == 1) {
  header("location: lock.php");
}

// Check if LAST_ACTIVITY is set
if (isset($_SESSION['LAST_ACTIVITY'])) {
  // Calculate time since last activity
  $time_since_last_activity = time() - $_SESSION['LAST_ACTIVITY'];

  if ($time_since_last_activity > $timeout_duration) {
    // If session has expired, destroy it and redirect to logout page
    session_unset();
    session_destroy();
    header("Location: logout.php");
    exit();
  } else {
    // Update last activity time if session is still active
    $_SESSION['LAST_ACTIVITY'] = time();
  }
}

// User Google Auth
$UserGoogleAuth = $functions->getUserSettingValue($UserSessionID, 31);

// Check if GoogleAuth is null (does not exist) instead of empty
if ($UserGoogleAuth === null || $UserGoogleAuth === "") {
  $DefaultGoogleAuth = (string) $functions->getSettingValue(31);
  $functions->createUserSetting($UserSessionID, $DefaultGoogleAuth, 31);
}
// User Google Auth End
// User Language
$UserLanguageID = $functions->getUserSettingValue($UserSessionID, 10);

if (!$UserLanguageID) {
  $DefaultUserLanguageID = $functions->getSettingValue(10);
  $functions->createUserSetting($UserSessionID, $DefaultUserLanguageID, 10);
  $UserLanguageCode = $functions->getLanguageCode($DefaultUserLanguageID);
} else {
  $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);
}
// User Language End
// TimeZone
$UserTimeZone = $functions->getUserSettingValue($UserSessionID, 11);

if (!$UserTimeZone) {
  $DefaultSettingID = $functions->getSettingValue(11);
  $functions->createUserSetting($UserSessionID, $DefaultSettingID, 11);
}

$DefaultTimeZoneID = $functions->getSettingValue(11);
if (!$DefaultTimeZoneID) {
  $DefaultTimeZoneID = 329;
}
$DefaultTimeZoneName = $functions->getDefaultTimeZoneName($DefaultTimeZoneID);

$HeadQuarterTime = $DefaultTimeZoneName;
date_default_timezone_set($DefaultTimeZoneName);
// End TimeZone
// Design
$UserDesign = $functions->getUserSettingValue($UserSessionID, 20);

if (!$UserDesign) {
  $DefaultDesign = $functions->getSettingValue(20);
  $functions->createUserSetting($UserSessionID, $DefaultDesign, 20);
  $theme = $DefaultDesign;
} else {
  $theme = $UserDesign;
}

if ($theme == "light") {
  $themecss = "";
} else {
  $theme = "dark";
  $themecss = "$theme-version";
}
// Design End
// Sidenav
$sideNav = "hidden";
$userSideNav = $functions->getUserSettingValue($UserSessionID, "12");

if (!$userSideNav) {
  $functions->createUserSetting($UserSessionID, $sideNav, 12);
} else {
  $sideNav = $userSideNav;
}
// Sidenav End

$CITypeID = "";
if (!empty($_GET['ciid'])) {
  $CITypeID = $_GET['ciid'];
  $CITableName = getCITableName($CITypeID);
}

$ITSMTypeID = "";
if (!empty($_GET['itsmid'])) {
  $ITSMTypeID = $_GET['itsmid'];
}

$ElementID = "";
if (!empty($_GET['elementid'])) {
  $ElementID = $_GET['elementid'];
}

$functions->renewdbsession();

//If usertype 2 ensure to not create sidebar menue
if ($UserType == 2) {
  $NoSideBarMenue = true;
} else {
  $NoSideBarMenue = false;
}

//If usertype 2 ensure to only allow view of helpdesk page
if ($UserType == 2 &&  $CurrentPage !== "helpdesk.php") {
  echo "you are user type 2 - redirecting to helpdesk";
  header("location: helpdesk.php");
}

// Define System name
$SystemName = $functions->getSettingValue(13);

?>
<?php
$functions->handleRequestUri($UserType);
// Update ectivity
$_SESSION['LAST_ACTIVITY'] = time();
?>

<?php
$cookie_name = "practicle_visited_url";
// Program to display URL of current page.
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
  $cookie_value = "https";
else $cookie_value = "http";

// Here append the common URL characters.
$cookie_value .= "://";

// Append the host(domain name, ip) to the URL.
$cookie_value .= $_SERVER['HTTP_HOST'];

// Append the requested resource location to the URL
$cookie_value .= $_SERVER['REQUEST_URI'];

//$cookie_value = substr($cookie_value, 1);
setcookie($cookie_name, $cookie_value, time() + (86400 * 14), "/"); // 86400 = 1 day

?>

<head>
  <?php
  echo $functions->designGetHeader($SystemName);
  ?>
  <!-- PWA Manifest -->
  <link rel="manifest" href="/manifest.php">

  <!-- Fonts and icons -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700" />
  <!-- Nucleo Icons -->
  <link href="./assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="./assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="./assets/fontawesome-free-6.1.1-web/js/all.min.js"></script>
  <!-- kit.fontawesome.com -->
  <script src="./assets/js/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
  <!-- CSS Files -->
  <link id="pagestyle" href="./assets/css/material-dashboard.css" rel="stylesheet" />
  <link id="pagestyle" href="./assets/js/DataTables/datatables.min.css" rel="stylesheet" />
  <link id="pagestyle" href="./assets/js/DataTables/SearchBuilder-1.5.0/css/searchBuilder.dataTables.min.css" rel="stylesheet" />
  <link id="pagestyle" href="./assets/js/DataTables/DateTime-1.5.1/css/dataTables.dateTime.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/js/jquery-ui-1.13.2/jquery-ui.min.css">
  <!-- JQuery -->
  <script src="./assets/js/core/jquery-3.7.1.min.js"></script>
  <!-- JQuery UI -->
  <script src="./assets/js/jquery-ui-1.13.2/jquery-ui.min.js"></script>
  <!-- Material sweet alerts -->
  <script src="./assets/js/plugins/sweetalert2.min.js"></script>
  <!--  Notifications Plugin -->
  <script src="./assets/js/plugins/bootstrap-notify.js"></script>
  <link rel="stylesheet" href="./assets/css/toastr.css">
  <script src="./assets/js/plugins/toastr.min.js"></script>
  <!-- Core JS Files -->
  <script src="./assets/js/DataTables/datatables.min.js"></script>
  <script src="./assets/js/DataTables/SearchBuilder-1.5.0/js/dataTables.searchBuilder.min.js"></script>
  <script src="./assets/js/DataTables/SearchBuilder-1.5.0/js/searchBuilder.dataTables.min.js"></script>
  <script src="./assets/js/DataTables/DateTime-1.5.1/js/dataTables.dateTime.min.js"></script>
  <script src="./assets/js/plugins/moment.min.js"></script>
  <script src="./assets/js/core/popper.min.js"></script>
  <script src="./assets/js/core/bootstrap.min.js"></script>
  <!-- DropZone -->
  <link href="./assets/css/dropzone.css" type="text/css" rel="stylesheet" />
  <script src="./assets/js/dropzone.js"></script>
  <!-- spotlight image viewer -->
  <script src="./assets/js/spotlight.bundle.js"></script>
  <!-- Include Select2 CSS -->
  <link rel="stylesheet" href="./assets/css/select2.css" />
  <link rel="stylesheet" href="./assets/css/select2-bootstrap-5-theme.min.css" />
  <!-- Include Select2 JS -->
  <script src="./assets/js/select2.full.min.js"></script>
</head>

<?php
header('Content-Type: text/html; charset=utf-8');
?>
<script src="./jspracticle/service-worker-register.js"></script>
<script type="text/javascript">
  function locksession() {
    userid = '<?php echo $UserSessionID ?>';
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?lockSession=" + userid, true);
    xmlhttp.send();
    window.location.href = 'lock.php';
  }

  function updateSessionActive(status) {
    userid = '<?php echo $UserSessionID ?>';
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?updateSessionActive=" + userid + '&status=' + status, true);
    xmlhttp.send();
  }

  function updateClientSessionIP() {
    var ip = '<?php echo $_SERVER['REMOTE_ADDR']; ?>';
    userid = '<?php echo $UserSessionID ?>';
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?updateClientSessionIP=" + userid + '&ip=' + ip, true);
    xmlhttp.send();
  }

  function getSessionActive() {
    var activestatus = "";
    userid = '<?php echo $UserSessionID ?>';
    var url = './getdata.php?getSessionActive=' + userid;

    $.ajax({
      url: url,
      data: {
        data: userid
      },
      type: 'GET',
      success: function(data) {
        if (data) {
          var obj = JSON.parse(data);
          for (var i = 0; i < obj.length; i++) {
            var activestatus = obj[i].Active;
          }
        }
      }
    });

    return activestatus;
  }

  //function that automatically locks the page if inactive

  updateSessionActive(1);
  updateClientSessionIP();
</script>

<script src="./assets/js/ckeditor5_superbuild/ckeditor.js"></script>
<script src="./jspracticle/initiators<?php echo $min ?>.js"></script>
<script src="./jspracticle/functions<?php echo $min ?>.js"></script>
<script src="./jspracticle/functions_cmdb<?php echo $min ?>.js"></script>
<script src="./jspracticle/functions_itsm<?php echo $min ?>.js"></script>
<script src="./jspracticle/functions_forms<?php echo $min ?>.js"></script>
<script src="./jspracticle/functions_project<?php echo $min ?>.js"></script>
<script src="./jspracticle/functions_news<?php echo $min ?>.js"></script>

</div>

<?php
$Background = getBackgroundUser();
if ($Background == 1) {
  echo "<style>
      .main-panel {
        background-image: url('/assets/img/content.jpg') !important; 
      }
      </style>";
}
?>
</head>

<?php
$ActiveModules[] = $functions->getActiveModules();
?>

<body class="g-sidenav-show bg-gray-200 <?php echo $themecss ?> g-sidenav-hidden" id="bodysize">
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-expanded="false" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="./index.php">
        <img src="./images/logo.png" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold text-white"><?php echo $SystemName ?></span>
      </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item mb-2 mt-0">
          <a data-bs-toggle="collapse" href="#ProfileNav" class="nav-link text-white" aria-controls="ProfileNav" role="button" aria-expanded="false">
            <?php
            $profilepicture = $functions->getProfilePicture($UserSessionID);
            ?>
            <img src="<?php echo $profilepicture ?>" class="avatar">
            <span class="nav-link-text ms-2 ps-1"><?php echo $_SESSION["userfullname"]; ?></span>
          </a>
          <div class="collapse" id="ProfileNav">
            <ul class="nav">
              <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo "user_profile.php?userid=" . $_SESSION['id'] ?>">
                  <span class="sidenav-normal ms-1 ps-1"><?php echo _("Profile"); ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-white" href="user_settings.php">
                  <span class="sidenav-normal ms-1 ps-1"><?php echo _("Settings"); ?></span>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link text-white" href="logout.php">
                  <span class="nav-link-text ms-1 ps-1"><?php echo _("Logout"); ?></span>
                </a>
              </li>
              <?php
              $State = $functions->getSettingValue(64);

              if ($State == 0) { ?>
                <li class="nav-item">
                  <a class="nav-link text-white" href="javascript:locksession();">
                    <span class="nav-link-text ms-1 ps-1"><?php echo _("Lock"); ?></span>
                  </a>
                </li>
              <?php
              }
              ?>

            </ul>
          </div>
        </li>
        <hr class="horizontal light mb-2 mt-0">
        <?php

        if ($NoSideBarMenue == false) {
          /*get modules menu */
          $TempArray = array();
          $ExcludedModules = array();
          $ActiveMenuModules = $functions->getActiveModulesMenu();

          foreach ($ActiveMenuModules as $Key => $Value) {
            $ID = $Value["ID"];
            $MenuPage = "./" . $Value["MenuPage"];
            $Name = $Value["Name"];
            $Name = _("$Name");
            $TypeIcon = $Value["TypeIcon"];
            $TempArray[] = array("ID" => $Value["ID"], "Name" => $Name, "TypeIcon" => $TypeIcon, "MenuPage" => $MenuPage);
            $ExcludedModules[] = $ID;
          }

          $ActiveITSMModules = $functions->getActiveITSMModulesMenu();
          foreach ($ActiveITSMModules as $Key => $Value) {
            $ID = $Value["ID"];
            $Name = $Value["Name"];
            $Name = _("$Name");
            $TypeIcon = $Value["TypeIcon"];
            $MenuPage = $Value["MenuPage"];
            $Type = $Value["Type"];
            if ($Type == "2") {
              $MenuPage = "./" . $MenuPage;
            } else {
              $MenuPage = "./" . $MenuPage . $ID;
            }
            $GroupID = $Value["GroupID"];

            // Check if the user belongs to any of the groups associated with the role
            if (in_array("100001", $group_array) || in_array("$GroupID", $group_array) || empty($GroupID)) {
              $TempArray[] = array("ID" => $ID, "Name" => $Name, "TypeIcon" => $TypeIcon, "MenuPage" => $MenuPage);
            }

            if ($Type !== "1") {
              $ExcludedModules[] = $ID;
            }
          }

          usort($TempArray, fn($a, $b) => $a['Name'] <=> $b['Name']);

          foreach ($TempArray as $Key => $Value) {
            $ID = $Value["ID"];

            $Badge = "";
            $NewElements = "";

            if (!in_array($ID, $ExcludedModules)) {
              $NewElements = getNewUnhandledElements($ID);
              if ($NewElements !== 0) {
                $Badge = "<span class=\"badge rounded-pill bg-danger\">$NewElements</span>";
              }
            }

            $Name = $Value["Name"];
            $TypeIcon = $Value["TypeIcon"];
            $MenuPage = $Value["MenuPage"];

            echo "<li class=\"nav-item mb-2 mt-0\">
                    <a href=\"$MenuPage\" class=\"nav-link text-white\" aria-controls=\"$Name\" role=\"button\" aria-expanded=\"false\">
                      <i class=\"$TypeIcon\"></i>
                      <span class=\"nav-link-text ms-2 ps-1\">$Name</span>&nbsp;$Badge
                    </a>
                  </li>";
          }
        }


        ?>

        <?php
        if ($NoSideBarMenue == false) { ?>
          <hr class="horizontal light mt-0 mb-2">
          <li class="nav-item mb-2 mt-0">
            <a href="./organization.php" class="nav-link text-white" aria-controls="Organization" role="button" aria-expanded="false">
              <i class="fa-solid fa-sitemap"></i>
              <span class="nav-link-text ms-2 ps-1"><?php echo _("Organization"); ?></span>
            </a>
          </li>
        <?php } ?>

        <?php

        if (!empty($_SESSION['LeaderOfArray'])) { ?>

          <hr class="horizontal light mt-0 mb-2">
          <li class="nav-item mb-2 mt-0">
            <a href="./manager_timeregistrations.php" class="nav-link text-white" aria-controls="Organization" role="button" aria-expanded="false">
              <i class="fa-solid fa-people-roof"></i>
              <span class="nav-link-text ms-2 ps-1"><?php echo _("Teams timeregistrations"); ?></span>
            </a>
          </li>

        <?php } ?>
        <?php
        if (in_array("100001", $group_array)) { ?>

          <hr class="horizontal light mb-2 mt-0">
          <li class="nav-item">
            <a data-bs-toggle="collapse" href="#administration" class="nav-link text-white" aria-controls="administration" role="button" aria-expanded="false">
              <i class='fas fa-toolbox'></i>
              <span class="nav-link-text ms-2 ps-1"><?php echo _("Administration"); ?></span>
            </a>
            <div class="collapse" id="administration">
              <ul class="nav">
                <li class="nav-item">
                  <ul class="nav nav-sm flex-column">
                    <?php
                    $menuItems = array(
                      ucfirst($functions->translate("Users")) => "./administration_users.php",
                      ucfirst($functions->translate("Teams")) => "./administration_teams.php",
                      ucfirst($functions->translate("Groups")) => "./administration_groups.php",
                      ucfirst($functions->translate("Roles")) => "./administration_roles.php",
                      ucfirst($functions->translate("Companies")) => "./administration_companies.php",
                    );

                    $sortedMenuItems = array_keys($menuItems);
                    array_multisort($sortedMenuItems, SORT_STRING);

                    foreach ($sortedMenuItems as $menuItem) {
                      echo navMenu02($menuItems[$menuItem], $menuItem);
                    }
                    ?>
                  </ul>
                </li>
                <li class="nav-item">
                  <a class="nav-link text-white collapsed" data-bs-toggle="collapse" aria-expanded="false" href="#Features">
                    <span class="sidenav-normal ms-2 ps-1"><?php echo _("Features"); ?></span>
                  </a>
                  <div class="collapse" id="Features">
                    <ul class="nav nav-sm flex-column">
                      <?php
                      $menuItems = array(
                        ucfirst($functions->translate("Forms")) => "./administration_formsbuilder.php",
                        ucfirst($functions->translate("Workflows")) => "./administration_workflows.php",
                        ucfirst($functions->translate("Modules")) => "./administration_modules.php",
                        ucfirst($functions->translate("Assets")) => "./administration_cmdb.php",
                      );

                      $sortedMenuItems = array_keys($menuItems);
                      array_multisort($sortedMenuItems, SORT_STRING);

                      foreach ($sortedMenuItems as $menuItem) {
                        echo navMenu02($menuItems[$menuItem], $menuItem);
                      }
                      ?>
                    </ul>
                  </div>
                </li>
                <li class="nav-item">
                  <a class="nav-link text-white collapsed" data-bs-toggle="collapse" aria-expanded="false" href="#System">
                    <span class="sidenav-normal ms-2 ps-1"><?php echo _("System"); ?></span>
                  </a>
                  <div class="collapse" id="System">
                    <ul class="nav nav-sm flex-column">
                      <?php
                      $menuItems = array(
                        ucfirst($functions->translate("Mail templates")) => "./administration_mail_templates.php",
                        ucfirst($functions->translate("Settings")) => "./administration_settings.php",
                        ucfirst($functions->translate("Maintenance")) => "./administration_maintenance.php",
                        ucfirst($functions->translate("API")) => "./administration_api.php",
                        ucfirst($functions->translate("Active Directory")) => "./administration_ad.php",
                        ucfirst($functions->translate("Helpdesk")) => "./helpdesk.php",
                        ucfirst($functions->translate("System Configurator")) => "./administration_wizzard.php",
                        ucfirst($functions->translate("Languages")) => "./administration_languages.php"
                      );

                      $sortedMenuItems = array_keys($menuItems);
                      array_multisort($sortedMenuItems, SORT_STRING);

                      foreach ($sortedMenuItems as $menuItem) {
                        echo navMenu02($menuItems[$menuItem], $menuItem);
                      }
                      ?>
                    </ul>
                  </div>
                </li>
              </ul>
            </div>
          </li>
        <?php } ?>
        <?php
        echo $functions->designSuperAdmin($UserSessionID);
        ?>
      </ul>
    </div>
  </aside>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include("./navbar.php"); ?>

    <script>
      $(document).ready(function() {
        var redirect_url = '<?php echo $redirect_url ?>';
        if (redirect_url) {
          window.location.href = redirect_url;
        }
        let itsmid = '<?php echo $ITSMTypeID ?>';
        let ciid = '<?php echo $CITypeID ?>';
        let elementid = '<?php echo $ElementID ?>';

        if (ciid && elementid) {
          runModalViewCI(elementid, ciid, '1');
        }
        if (itsmid && elementid) {
          viewITSM(elementid, itsmid, 1, 'modal');
        }
      });
    </script>
    <!-- Fixed Variables -->
    <div id="UserLanguageCode" data-value="<?php echo $UserLanguageCode; ?>" hidden></div>
    <div id="CurrentPage" data-value="<?php echo $CurrentPage; ?>" hidden></div>
