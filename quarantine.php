<?php
session_destroy();
?>

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
                                <h3 class="font-weight-bolder text-white"><?php echo _("Quarantene state"); ?></h3>
                            </div>
                        </div>
                        <div class="card-body py-4">
                            <p class="mb-0 text-sm"><?php echo _("Your acticity has resulted in quarantine - please try again in 20 minutes."); ?></p>
                            <br>
                            <p class="mb-0 text-sm"><?php echo _("If you believe this is an error, please contact support."); ?></p>
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
                </div>
            </div>
        </footer>
    </main>
</body>

</html>