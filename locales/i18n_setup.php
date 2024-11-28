<?php
try {
    $projectDir = dirname(__DIR__);
    // Create an instance of the class with both the connection and current directory
    $functions = new PracticleFunctions\Practiclefunctions($conn, $projectDir);

    // Make $functions available globally if needed
    global $functions;
} catch (Exception $e) {
    echo ("Error: " . $e->getMessage());
}

function valid($locale) {
    return in_array($locale, ['en_US', 'da_DK', 'es_ES', 'de_DE', 'fr_FR','fi_FI', 'it_IT','tr_TR', 'zh_CN', 'ru_RU', 'ja_JP', 'pt_PT']);
}

// Check if the session variable is set
if (isset($_SESSION["id"])) {
    $UserSessionID = $_SESSION["id"];
    $UserLanguageID = $functions->getUserLanguage($UserSessionID);

    if (!$UserLanguageID) {
        $UserLanguageID = $functions->getDefaultUserLanguage();
    }

    $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);
    $language = $UserLanguageCode;
    $languageshort = substr($language, 0, strpos($language, "_"));

    // here we define the global system locale given the found language
    putenv("LANG=$language");

    // this might be useful for date functions (LC_TIME) or money formatting (LC_MONETARY), for instance
    setlocale(LC_ALL, $language);

    // this will make Gettext look for ../locales/<lang>/LC_MESSAGES/main.mo
    bindtextdomain('main', './locales');

    // indicates in what encoding the file should be read
    bind_textdomain_codeset('main', 'UTF-8');

    // here we indicate the default domain the gettext() calls will respond to
    textdomain('main');

}
