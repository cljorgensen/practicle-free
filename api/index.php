<?php
declare(strict_types=1);
header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: 0"); // Proxies

// Important PHP settings
$postMaxSize = '100M';
$uploadMaxSize = '200M';
// Update the PHP configuration settings
ini_set('post_max_size', $postMaxSize);
ini_set('upload_max_filesize', $uploadMaxSize);

spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});
include_once "../inc/dbconnection.php";
include_once "../functions/functions.php";
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

header("Content-type: application/json; charset=UTF-8");

$parts = explode("/", $_SERVER["REQUEST_URI"]);
$entities = array("companies","users","teams","groups","itsm","cmdb","cmdb_relations");

$TokenReceived = $parts[3] ?? null;

$type = "";
$entity = "";
$id = "";

// Validate the received token
$sql = "SELECT id FROM api_keys WHERE api_key = ? AND status = 1 AND expiry_date >= CURDATE();";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $TokenReceived);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo "You are not allowed";
    exit;
}

mysqli_stmt_close($stmt);

if (!in_array($parts[4], $entities)) {
    http_response_code(404);
    exit;
}

$entity = $parts[4];
$id = $parts[5] ?? null;

$database = new Database("$dbservername", "$dbname", "$dbusername", "$dbpassword", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

switch ($entity) {
    case "companies":
        $gateway = new CompanyGateway($database);
        $controller = new CompanyController($gateway);
        break;
    case "groups":
        $gateway = new GroupGateway($database);
        $controller = new GroupController($gateway);
        break;
    case "teams":
        $gateway = new TeamGateway($database);
        $controller = new TeamController($gateway);
        break;
    case "users":
        $gateway = new UserGateway($database);
        $controller = new UserController($gateway);
        break;
    case "itsm":
        $type = $parts[4];
        $entity = $parts[5];
        $id = $parts[6] ?? null;
        $gateway = new ITSMGateway($database);
        $controller = new ITSMController($gateway);
        break;
    case "cmdb":
        $type = $parts[4];
        $entity = $parts[5];
        $id = $parts[6] ?? null;
        $gateway = new CMDBGateway($database);
        $controller = new CMDBController($gateway);
        break;
    case "cmdb_relations":
        $type = $parts[4];
        $entity = $parts[5];
        $id = $parts[6] ?? null;
        $gateway = new CMDBRelationGateway($database);
        $controller = new CMDBRelationController($gateway);
        break;
    default:
        echo "Wrong entity type";
        exit;
}

$controller->processRequest($_SERVER["REQUEST_METHOD"], $id, $entity);
