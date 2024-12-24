<?php
$Version = "3.93.127";
global $Version;
$uuid = generateUUID();
global $uuid;
$logFile = './installation.log';
global $logFile;

$extensionDir = ini_get('extension_dir');

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

function checkRequiredExtensions()
{
    $requiredExtensions = [
        'practiclefunctions' => 'Practicle extension',
        'imap'               => 'PHP IMAP extension',
        'mysqli'             => 'MySQLi extension',
        'pdo'                => 'PDO extension',
        'pdo_mysql'          => 'PDO MySQL extension',
        'curl'               => 'cURL extension',
        'gd'                 => 'GD extension',
        'mbstring'           => 'Multibyte String extension',
        'openssl'            => 'OpenSSL extension',
        'zip'                => 'Zip extension',
        'json'               => 'JSON extension',
        'hash'               => 'Hash extension',
        'fileinfo'           => 'Fileinfo extension',
        'random'             => 'Random extension',
        'sodium'             => 'Sodium extension',
        'date'               => 'Date extension'
    ];

    $missingExtensions = [];

    foreach ($requiredExtensions as $extension => $name) {
        if (!extension_loaded($extension)) {
            $missingExtensions[] = $extension;
        }
    }

    // Return array of missing extensions
    return $missingExtensions;
}

function generateUUID()
{
    $data = random_bytes(16); // Generate 16 random bytes

    // Set the version to 0100 (UUIDv4)
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    // Set the variant to 10xx
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    // Format the UUID as a string with proper hyphenation
    return sprintf(
        '%08s-%04s-%04s-%04s-%12s',
        bin2hex(substr($data, 0, 4)), // 32 bits for "time_low"
        bin2hex(substr($data, 4, 2)), // 16 bits for "time_mid"
        bin2hex(substr($data, 6, 2)), // 16 bits for "time_hi_and_version"
        bin2hex(substr($data, 8, 2)), // 16 bits for "clk_seq_hi_res" and "clk_seq_low"
        bin2hex(substr($data, 10, 6)) // 48 bits for "node"
    );
}

if (isset($_GET['startInstall'])) {
    // Clear the log file at the start of the installation
    file_put_contents($logFile, "Progress: 0%\n");

    $formData = $_POST["formData"];
    // Initialize an empty array to store the form data
    $formDataArray = [];

    // Loop through the formData array and extract the name-value pairs
    foreach ($formData as $item) {
        $formDataArray[$item['name']] = $item['value'];
    }

    // Extract individual form fields from the formDataArray
    $dbhost = $formDataArray['dbhost'] ?? '';
    $dbport = $formDataArray['dbport'] ?? '';
    $dbname = $formDataArray['dbname'] ?? '';
    $dbuser = $formDataArray['dbuser'] ?? '';
    $dbpass = $formDataArray['dbpass'] ?? '';
    $firstname = $formDataArray['firstname'] ?? '';
    $lastname = $formDataArray['lastname'] ?? '';
    $username = $formDataArray['username'] ?? '';
    $userpass = $formDataArray['userpass'] ?? '';
    $email = $formDataArray['email'] ?? '';
    $company = $formDataArray['company'] ?? '';
    $country = $formDataArray['country'] ?? '';

    if (!$userpass) {
        $userpass = bin2hex(random_bytes(8));
    }

    // Connect to MySQL using admin credentials
    $mysqli = new mysqli($dbhost, $dbuser, $dbpass, '', $dbport);

    if (!$mysqli) {
        file_put_contents($logFile, "Connection failed: " . mysqli_connect_error() . "\n", FILE_APPEND);
        die();
    }

    // Check for a connection error
    if ($mysqli->connect_error) {
        // Log the specific connection error to the installation.log file
        file_put_contents($logFile, "Connection failed to the database server" . $mysqli->connect_error . "\n", FILE_APPEND);
        die();
    }

    // Step 2: Installation started
    file_put_contents($logFile, "Progress: 1%\n", FILE_APPEND);
    file_put_contents($logFile, "Installation started\n", FILE_APPEND);

    // Query to retrieve existing users that match 'practicle####' and delete them in case of installation re-run
    $getUsersQuery = "SELECT CONCAT('\'', user, '\'@\'', host, '\'') AS user_host
                  FROM mysql.user
                  WHERE user LIKE 'practicle____'
                  AND LENGTH(user) = 13;";

    // Execute the query
    if ($result = $mysqli->query($getUsersQuery)) {
        // Loop through the users and drop them
        while ($row = $result->fetch_assoc()) {
            $userHost = $row['user_host'];

            // Drop the existing user
            $dropUserQuery = "DROP USER $userHost";
            if (!$mysqli->query($dropUserQuery)) {
                file_put_contents($logFile, "Failed to drop user: " . $mysqli->error . "\n", FILE_APPEND);
            } else {
                file_put_contents($logFile, "Progress: 0%\n", FILE_APPEND);
            }
        }
    } else {
        file_put_contents($logFile, "Failed to retrieve users: " . $mysqli->error . "\n", FILE_APPEND);
        die();
    }

    // Generate a random MySQL username and password
    $newDbUser = 'practicle' . rand(1000, 9999);
    $newDbPass = bin2hex(random_bytes(8));

    // Create the MySQL user
    $createUserQuery = "CREATE USER '$newDbUser'@'localhost' IDENTIFIED BY '$newDbPass';";

    if (!$mysqli->query($createUserQuery)) {
        file_put_contents($logFile, "Failed to create MySQL user: " . $mysqli->error . "\n", FILE_APPEND);
        die();
    } else {
        file_put_contents($logFile, "Created MySQL user '$newDbUser'@'localhost'\n", FILE_APPEND);
        file_put_contents($logFile, "Progress: 1%\n", FILE_APPEND);
    }

    // Drop database if exists
    $sql = "DROP DATABASE IF EXISTS $dbname;";

    if (!$mysqli->query($sql)) {
        file_put_contents($logFile, "Failed to drop database: " . $mysqli->error . "\n", FILE_APPEND);
        die();
    }

    // Drop database if exists
    $sql = "CREATE DATABASE $dbname
            CHARACTER SET utf8mb4
            COLLATE utf8mb4_unicode_ci;";

    if (!$mysqli->query($sql)) {
        file_put_contents($logFile, "Failed to create database: " . $mysqli->error . "\n", FILE_APPEND);
        die();
    } else {
        file_put_contents($logFile, "Created database $dbname\n", FILE_APPEND);
    }
    file_put_contents($logFile, "Progress: 1%\n", FILE_APPEND);
    // Create PDO connection
    try {
        $dsn = "mysql:dbname=$dbname;host=$dbhost;port=$dbport;charset=utf8"; // assuming utf8 charset here, adjust as necessary
        $pdoconn = new PDO($dsn, $dbuser, $dbpass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // fetch associative arrays by default
        ]);

        // you can set charset here if the DSN was not working
        // $pdoconn->exec("set names utf8");
    } catch (PDOException $e) {
        // Consider logging error to a file instead of echoing
        file_put_contents($logFile, "Can't connect to the database. Error: " . $e->getMessage() . "\n", FILE_APPEND);
        exit;
    }

    // Download and execute SQL dump
    $sqlFileUrl = "https://downloads.practicle.dk/practicle_base_$Version.sql";

    // Use cURL to download the SQL file content
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sqlFileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $sqlContent = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        file_put_contents($logFile, "Error downloading SQL file: $error\n", FILE_APPEND);
        die();
    }

    // Log installation progress
    file_put_contents($logFile, "Installing database... (do not refresh this page)\n", FILE_APPEND);

    // Split SQL content into individual lines
    $sqlScript = explode("\n", $sqlContent);

    // Initialize progress session variable
    if (!isset($_SESSION['progress'])) {
        $_SESSION['progress'] = 1; // Initialize if not set
    }

    try {
        // Drop the database. BE VERY CAREFUL WITH THIS COMMAND.
        $pdoconn->exec("DROP DATABASE IF EXISTS `$dbname`");
        // Create the database
        $pdoconn->exec("CREATE DATABASE `$dbname`");
        // Select the database
        $pdoconn->exec("USE `$dbname`");

        // Disable foreign key checks
        $pdoconn->exec("SET FOREIGN_KEY_CHECKS=0");

        // Initialize variables for tracking progress
        $query = '';
        $totalStatements = count($sqlScript);  // Total number of lines in the SQL script
        $progressStep = 95 / $totalStatements; // Calculate progress per line
        $currentProgress = 1;
        $lastLoggedProgress = -1;  // To ensure unique progress logging

        // Execute each SQL statement one by one
        foreach ($sqlScript as $index => $line) {
            $startWith = substr(trim($line), 0, 2);
            $endWith = substr(trim($line), -1, 1);

            if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
                continue;  // Skip comments and empty lines
            }

            // Append line to the current query
            $query .= $line;

            // If line ends with a semicolon, it's the end of a query statement
            if ($endWith == ';') {
                $pdoconn->exec($query);  // Execute the SQL statement
                $query = '';  // Clear query for the next statement

                // Update progress
                $currentProgress = round(($index + 1) * $progressStep); // Calculate progress percentage

                // Log progress only if it has increased
                if ($currentProgress > $lastLoggedProgress) {
                    file_put_contents($logFile, "Progress: $currentProgress%\n", FILE_APPEND);
                    $lastLoggedProgress = $currentProgress;  // Update the last logged progress
                    $_SESSION['progress'] = $currentProgress;  // Store progress in session
                }
            }
        }
        // Enable foreign key checks again
        $pdoconn->exec("SET FOREIGN_KEY_CHECKS=1");

        // Log final progress
        file_put_contents($logFile, "Database installed\n", FILE_APPEND);
    } catch (PDOException $e) {
        // Log any database errors
        file_put_contents($logFile, "Database error: " . $e->getMessage() . "\n", FILE_APPEND);
        exit;
    }

    // Grant privileges
    $grantPrivilegesQuery = "GRANT ALL PRIVILEGES ON `$dbname`.* TO '$newDbUser'@'localhost'";
    if (!$mysqli->query($grantPrivilegesQuery)) {
        file_put_contents($logFile, "Failed to grant privileges: " . $mysqli->error . "\n", FILE_APPEND);
        exit;
    } else {
        file_put_contents($logFile, "Granted privileges on $dbname to '$newDbUser'@'localhost'\n", FILE_APPEND);
        $_SESSION['progress'] = 96;
        file_put_contents($logFile, "Progress: 96%\n", FILE_APPEND);
    }

    // Fluch privileges
    $flushPrivilegesQuery = "FLUSH PRIVILEGES;";
    if (!$mysqli->query($flushPrivilegesQuery)) {
        file_put_contents($logFile, "Failed to flush privileges: " . $mysqli->error . "\n", FILE_APPEND);
        die();
    }

    // Generate the dbconnection.php file with the custom port
    $dbConfigContent = "<?php
// DB connection used by default
\$dbservername = '$dbhost';
\$dbusername = '$newDbUser';
\$dbpassword = '$newDbPass';
\$dbname = '$dbname';
\$port = '$dbport';  // Custom port value
\$timezone = 'Europe/Copenhagen';  // Adjust timezone as necessary

// MySQLi connection
\$conn = mysqli_connect(\$dbservername, \$dbusername, \$dbpassword, \$dbname, \$port);
if (\$conn->character_set_name() != 'utf8') {
\$conn->set_charset('utf8');
}

if (\$conn->connect_error) {
die('Connection failed: ' . \$conn->connect_error);
}

// Set the timezone for MySQLi connection
\$conn->query(\"SET time_zone = '\$timezone'\");
global \$conn;

// Define global \$projectDir
\$projectDir = dirname(__DIR__);
global \$projectDir;

// Include the PracticleFunctions class
try {
    // Create an instance of the class with both the connection and current directory
    \$functions = new PracticleFunctions\Practiclefunctions(\$conn, \$projectDir);

    // Make \$functions available globally if needed
    global \$functions;
} catch (Exception \$e) {
    echo (\"Error: \" . \$e->getMessage());
}

// Create PDO connection
try {
\$dsn = \"mysql:dbname=\$dbname;host=\$dbservername;port=\$port;charset=utf8\"; // using utf8 charset here
\$pdoconn = new PDO(\$dsn, \$dbusername, \$dbpassword, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Set the timezone for PDO connection
\$pdoconn->exec(\"SET time_zone = '\$timezone'\");
} catch (PDOException \$e) {
exit(\"Can't connect to the database. Error: \" . \$e->getMessage());
}

// Use the connection
function getDbConnection() {
global \$pdoconn;
return \$pdoconn;
}
?>";

    // Save the dbconnection.php file
    if (!file_put_contents('./inc/dbconnection.php', $dbConfigContent)) {
        file_put_contents($logFile, "Failed to create dbconnection.php file\n", FILE_APPEND);
        die(); // Stop execution if file creation fails
    } else {
        file_put_contents($logFile, "dbconnection.php file created successfully\n", FILE_APPEND);
        file_put_contents($logFile, "Progress: 97%\n", FILE_APPEND);
    }

    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    $length = 20;

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }

    // Generate salt value for the application
    if (!$mysqli->select_db($dbname)) {
        file_put_contents($logFile, "Database selection failed: " . $mysqli->error . "\n", FILE_APPEND);
        die();
    }

    // Prepare the statement for updating the salt
    $CreateNewSaltQuery = "UPDATE settings SET SettingValue = ? WHERE ID = 4";
    if ($stmt = $mysqli->prepare($CreateNewSaltQuery)) {

        // Bind the random string parameter to the prepared statement
        $stmt->bind_param("s", $randomString);

        // Execute the prepared statement
        if ($stmt->execute()) {
        } else {
            file_put_contents($logFile, "Failed to create new Salt: " . $stmt->error . "\n", FILE_APPEND);
            die();
        }

        // Close the statement
        $stmt->close();
    } else {
        file_put_contents($logFile, "Failed to prepare statement: " . $mysqli->error . "\n", FILE_APPEND);
        die();
    }

    // Hash the Practicle user password
    $Saltedpassword = (trim($randomString) . trim($userpass));
    $HashedPassword = hash('sha512', $Saltedpassword);

    $RelatedCompanyID = "1";

    $JobTitel = "Elite administrator";
    $RelatedUserTypeID = "1";
    $StartDate = date('Y-m-d H:i:s');
    $NewPin = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);

    // Create the new Practicle user
    $CreateNewUserQuery = "INSERT INTO users (ID, Firstname, Lastname, Username, Password, Email, CompanyID, RelatedUserTypeID, JobTitel, StartDate, pin)
            VALUES ('1','$firstname','$lastname','$username','$HashedPassword','$email',$RelatedCompanyID,$RelatedUserTypeID,'$JobTitel','$StartDate','$NewPin');";

    $mysqli->select_db($dbname);

    if (!$mysqli->query($CreateNewUserQuery)) {
        file_put_contents($logFile, "Failed to create new user: " . $mysqli->error . "\n", FILE_APPEND);
        die();
    }
    file_put_contents($logFile, "Created Practicle administrative user: $firstname $lastname ($username) \n", FILE_APPEND);

    // Make User Administator
    $MakeUserAdminQuery = "INSERT INTO usersgroups(UserID, GroupID) VALUES (1,100001);";

    if (!$mysqli->query($MakeUserAdminQuery)) {
        file_put_contents($logFile, "Failed to make new user administrator: " . $mysqli->error . "\n", FILE_APPEND);
        die();
    }

    $set1 = "UPDATE settings SET SettingValue = '$uuid' WHERE ID = 5;";
    if (!$mysqli->query($set1)) {
        file_put_contents($logFile, "Failed to update setting 5: $uuid" . $mysqli->error . "\n", FILE_APPEND);
        die();
    }

    $set2 = "UPDATE settings SET SettingValue = '$Version' WHERE ID = 6;";
    if (!$mysqli->query($set2)) {
        file_put_contents($logFile, "Failed to update setting 6: $Version" . $mysqli->error . "\n", FILE_APPEND);
        die();
    }

    $set3 = "UPDATE settings SET SettingValue = '$company' WHERE ID = 13;";
    if (!$mysqli->query($set3)) {
        file_put_contents($logFile, "Failed to update setting 13: to $company" . $mysqli->error . "\n", FILE_APPEND);
        die();
    }

    file_put_contents($logFile, "Progress: 98%\n", FILE_APPEND);
    // Build the URL with query parameters
    $url = "https://support.practicle.dk/verifylicence.php?registerLicenceKey" .
        "&licenceKey=" . urlencode($uuid) .
        "&firstname=" . urlencode($firstname) .
        "&lastname=" . urlencode($lastname) .
        "&email=" . urlencode($email) .
        "&country=" . urlencode($country) .
        "&company=" . urlencode($company);

    // Initialize cURL
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verify SSL certificate
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout after 10 seconds

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        echo json_encode(["status" => "error", "message" => $error_msg]);
    } else {
        // Decode the JSON response
        $decodedResponse = json_decode($response, true);

        // Check if the response is a valid JSON array
        if (is_array($decodedResponse)) {
            // Handle the response based on the status
            if ($decodedResponse['status'] === 'success') {
                echo json_encode(["status" => "success"]);
            } elseif ($decodedResponse['status'] === 'error') {
                // If there's an error, include the error message from the server response
                $errorMessage = $decodedResponse['message'] ?? 'Unknown error';
                echo json_encode(["status" => "error", "message" => $errorMessage]);
            } else {
                echo json_encode(["status" => "error", "message" => "Unexpected response format"]);
            }
        } else {
            // If the response is not a valid JSON, return an error
            echo json_encode(["status" => "error", "message" => "Invalid response from server"]);
        }
    }

    // Close cURL session
    curl_close($ch);
    file_put_contents($logFile, "Registered licence at practicle\n", FILE_APPEND);
    file_put_contents($logFile, "Progress: 99%\n", FILE_APPEND);
    file_put_contents($logFile, "Installation completed successfully\n\n", FILE_APPEND);
    file_put_contents($logFile, "Username: $username and Password: $userpass to login\n", FILE_APPEND);
    file_put_contents($logFile, "Remember to rename install.php to *.php.disabled or remove it\n\n", FILE_APPEND);
    file_put_contents($logFile, "<a href='login.php' target=\"_blank\">Go to site and Login</a>\n", FILE_APPEND);
    file_put_contents($logFile, "Progress: 100%\n", FILE_APPEND);
    exit;
}

if (isset($_GET['getProgress'])) {
    // Define the path to the progress log file
    $logFile = './installation.log';

    // Check if the log file exists and is readable
    if (file_exists($logFile) && is_readable($logFile)) {
        // Read the file content and convert each line to an array
        $progress = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Return the progress as a JSON response
        echo json_encode($progress);
    } else {
        // If the file doesn't exist or is not readable, return an empty array
        echo json_encode([]);
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install Practicle</title>
    <link rel="icon" href="/images/favicon.ico">
    <link id="pagestyle" href="./assets/css/material-dashboard.css?v=3.0.4" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="text-center mt-5">
                    <img src="./images/practicle_logo_black.png" alt="Practicle Logo" style="max-width: 200px; height: auto;">
                    <h5 class="mt-3">Installation</h5>
                </div>
                <!-- Informational Message -->
                <div class="text-center mt-3">
                    <button class="btn btn-info btn-sm" data-bs-toggle="collapse" href="#collapseNotice" role="button" aria-expanded="false" aria-controls="collapseNotice">
                        Readme First
                    </button>
                </div>
                <div class="collapse" id="collapseNotice">
                    <div class="card card-body">
                        <p>By clicking “Install,” you agree to these terms:</p>
                        <p>This software is provided free of charge and without built in limitations. You are not allowed to sell this software, modify it, or misuse it in any way. Use of this software is at your own discretion, and Practicle is not liable for any data loss or errors arising from its use. We do not have or provide any support for this free version.</p>
                        <p>To enhance our software and better understand user needs, practicle will regularly transmit the following statistics:</p>
                        <small>
                            <ul>
                                <li>Number of active users</li>
                                <li>Number of active companies</li>
                                <li>Number of active teams</li>
                                <li>Number of active groups</li>
                                <li>Number of active elements</li>
                                <li>Number of active projects</li>
                                <li>Number of active assets types (cmdb)</li>
                                <li>Number of active assets (cmdb)</li>
                            </ul>
                        </small>
                        <p>Your informations provided in this installer will be registered. Please keep user information anonymous if you prefer that. No further personal or sensitive information is collected, transmitted or registered.</p>
                        <small>* If you are interested in participating in this project, have reports on security issues or have requests for features (hourly fee of 200 dollars per hour) - please contact Practicle via our contact form <a href="https://practicle.dk/index.php#contact-us" target="_blank">here</a></small>
                        <small>* If you are using our software on daily basis - please provide us with a recommendation here <a href="https://www.linkedin.com/company/29293708/" target="_blank">LinkedIn</a> and here <a href="https://www.facebook.com/practicle.dk/" target="_blank">Facebook</a></small>
                        <small>* Yes, Practicle is a hobby project. While it may gain commercial traction in the future, please use it at your own risk.</small>
                    </div>
                </div>
                <form id="installForm" class="mt-4 p-4 border rounded bg-light">
                    <h5 class="text-center">Database</h5>
                    <small class="d-block text-center">( db installation )</small>
                    <div class="row mt-5">
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="dbhost">Database Host (FQDN)</label>
                                <input type="text" class="form-control" id="dbhost" name="dbhost" value="localhost" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="dbport">Database Port</label>
                                <input type="text" class="form-control" id="dbport" name="dbport" value="3306" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="dbname">Database Name</label>
                                <input type="text" class="form-control" id="dbname" name="dbname" value="practicle_prod" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="dbuser">Database Admin User</label>
                                <input type="text" class="form-control" id="dbuser" name="dbuser" value="mysql-pracadm" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="dbpass">Database Admin Password</label>
                                <input type="password" class="form-control" id="dbpass" name="dbpass" required>
                            </div>
                        </div>
                    </div>
                    <h5 class="text-center">Administrator</h5>
                    <small class="d-block text-center">( administrative system user )</small>
                    <div class="row mt-5">
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="firstname">First Name</label>
                                <input type="text" class="form-control" id="firstname" name="firstname" value="Claus" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="lastname">Last Name</label>
                                <input type="text" class="form-control" id="lastname" name="lastname" value="Jørgensen" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="cjo" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="userpass">Password</label>
                                <input type="password" class="form-control" id="userpass" name="userpass" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="email">Email</lab0el>
                                    <input type="email" class="form-control" id="email" name="email" value="claus@salat.dk" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="company">Company</label>
                                <input type="company" class="form-control" id="company" name="company" value="salat" required>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-4 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="country">Country</label>
                                <input type="country" class="form-control" id="country" name="country" value="Denmark" required>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-lg-6 col-sm-6 col-xs-12 text-start">
                        <p class="mb-0">v <?php echo $Version; ?> (free)</p>
                    </div>
                    <div class="col-lg-6 col-sm-6 col-xs-12 text-end">
                        <button type="button" onclick="startInstallation()" class="btn btn-sm btn-primary" id="installButton" disabled>Install</button>
                    </div>
                </div>

                <div id="progressContainer" style="width: 100%; background-color: #e0e0e0; margin: 10px 0;" hidden>
                    <div id="progressBar" style="width: 0%; height: 30px; background-color: #008000; transition: width 0.5s;"></div>
                    <div id="statusBox" style="height: 200px; overflow-y: auto; background-color: #f8f9fa;"></div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const collapseNotice = document.getElementById("collapseNotice");
            const installButton = document.getElementById("installButton");

            // Enable button when Notice is expanded
            collapseNotice.addEventListener("shown.bs.collapse", function() {
                installButton.disabled = false;
            });
        });

        function startInstallation() {
            document.getElementById("progressContainer").hidden = false; // Show status box

            // Set progress bar to 0% before starting installation
            $('#progressBar').css('width', '0%');

            const formData = $("#installForm").serializeArray();
            const statusBox = $('#statusBox');
            pollForProgress();

            // Start the installation process
            $.ajax({
                url: "./install.php?startInstall",
                type: 'POST',
                data: {
                    formData: formData
                },
                success: function() {
                    console.log('Installation script called');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error:', textStatus, errorThrown);
                    statusBox.append(`<p>An error occurred: ${errorThrown}</p>`);
                }
            });
        }

        function pollForProgress() {
            const statusBox = $('#statusBox');

            // Ensure the progress bar starts at 0%
            $('#progressBar').css('width', '0%');

            // Poll the backend for progress every 500 milliseconds
            const intervalId = setInterval(function() {
                $.ajax({
                    url: "./install.php?getProgress", // Endpoint to get progress
                    type: 'GET',
                    success: function(data) {
                        const progress = JSON.parse(data);

                        // Update the progress bar
                        const lastMessage = progress[progress.length - 1];
                        const percentageMatch = lastMessage?.match(/\d+/); // Extract the percentage if available
                        const percentage = percentageMatch ? parseInt(percentageMatch[0]) : 0;

                        // Set the width of the progress bar
                        $('#progressBar').css('width', percentage + '%');

                        // Clear the statusBox and show all progress messages
                        statusBox.html('');
                        progress.forEach(function(message) {
                            if (!message.includes("Progress:")) {
                                statusBox.append(`${message}<br>`);
                            }
                        });

                        // Automatically scroll to the bottom of the statusBox
                        statusBox.scrollTop(statusBox[0].scrollHeight);

                        // Check if installation is complete
                        const isCompleted = progress.includes("Installation completed successfully");

                        // Stop polling only if the final message "Installation completed successfully" has been logged
                        if (isCompleted) {
                            clearInterval(intervalId);
                            console.log('Installation completed successfully, polling stopped.');
                        }

                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error:', textStatus, errorThrown);
                        statusBox.append(`<p>An error occurred in getting progress: ${errorThrown}</p>`);
                    }
                });
            }, 500); // Poll every 500 milliseconds
        }
    </script>
</body>

</html>
