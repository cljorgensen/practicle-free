<?php
require_once "./inc/dbconnection.php";
require_once "./functions/functions.php";

// Validate $functions object
if (!isset($functions)) {
    die(json_encode(["error" => "Functions object not defined."]));
}

$SystemName = $functions->getSettingValue(13);

if (!$SystemName) {
    die(json_encode(["error" => "SystemName could not be retrieved."]));
}

$themeColor = "#1a2035";

$manifest = [
    "name" => "$SystemName",
    "short_name" => "$SystemName",
    "start_url" => "index.php",
    "display" => "standalone",
    "background_color" => "$themeColor",
    "theme_color" => "$themeColor",
    "orientation" => "any",
    "icons" => [
        [
            "src" => "/images/icons/icon-72x72.png",
            "type" => "image/png",
            "sizes" => "72x72"
        ],
        [
            "src" => "/images/icons/icon-96x96.png",
            "type" => "image/png",
            "sizes" => "96x96"
        ],
        [
            "src" => "/images/icons/icon-128x128.png",
            "type" => "image/png",
            "sizes" => "128x128"
        ],
        [
            "src" => "/images/icons/icon-144x144.png",
            "type" => "image/png",
            "sizes" => "144x144"
        ],
        [
            "src" => "/images/icons/icon-152x152.png",
            "type" => "image/png",
            "sizes" => "152x152"
        ],
        [
            "src" => "/images/icons/icon-192x192.png",
            "type" => "image/png",
            "sizes" => "192x192"
        ],
        [
            "src" => "/images/icons/icon-384x384.png",
            "type" => "image/png",
            "sizes" => "384x384"
        ],
        [
            "src" => "/images/icons/icon-512x512.png",
            "type" => "image/png",
            "sizes" => "512x512"
        ],
        [
            "src" => "/images/icons/maskable_icon_x192.png",
            "sizes" => "192x192",
            "type" => "image/png",
            "purpose" => "maskable"
        ],
        [
            "src" => "/images/icons/maskable_icon_x512.png",
            "sizes" => "512x512",
            "type" => "image/png",
            "purpose" => "maskable"
        ]
    ]
];

// Validate and output JSON
header('Content-Type: application/json');
echo json_encode($manifest, JSON_PRETTY_PRINT);
if (json_last_error() !== JSON_ERROR_NONE) {
    die('JSON encoding error: ' . json_last_error_msg());
}
