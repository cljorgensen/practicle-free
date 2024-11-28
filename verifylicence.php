<?php
include_once("./inc/dbconnection.php");
include_once("./functions/functions.php");

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Pragma: no-cache"); // HTTP/1.0
header("Expires: 0"); // Proxies

if (isset($_GET['getLicenceKey'])) {
    $customerLicenceKey = $_GET['getLicenceKey']; // Capture the license key from the query string

    try {
        // Retrieve the customer license details
        $licenseDetails = $functions->retrieveCustomerLicenceInfo($customerLicenceKey);
        $decodedDetails = json_decode($licenseDetails, true);

        if (isset($decodedDetails['LicenceKey'])) {
            // Update LicenceLastVerified timestamp
            $sqlUpdate = "UPDATE licences SET LicenceLastVerified = NOW() WHERE LicenceKey = ?";

            try {
                $functions->selectQuery($sqlUpdate, [$customerLicenceKey]);
            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                $functions->errorLog($errorMessage, "verifyLicenseUpdate");
                throw $e;
            }

            // Ensure LicenceInfo is properly formatted as an array
            $decodedDetails['LicenceInfo'] = json_decode($decodedDetails['LicenceInfo'], true);

            // Return the full license details as JSON
            echo json_encode($decodedDetails, JSON_PRETTY_PRINT);
        } else {
            echo json_encode(["status" => "Invalid license details"]);
        }
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}

if (isset($_GET['registerLicenceKey'])) {
    $customerLicenceKey = $_GET['licenceKey'];
    $customerFirstname = $_GET['firstname'];
    $customerLastname = $_GET['lastname'];
    $customerEmail = $_GET['email'];
    $customerCountry = $_GET['country'];
    $customerCompany = $_GET['company'];
    $licenseDetails = "{\"users\":\"0\",\"companies\":\"0\",\"teams\":\"0\",\"groups\":\"0\",\"modules\":\"0\",\"projects\":\"0\",\"itsm\":\"0\",\"cmdb\":\"0\",\"cmdbtypes\":\"0\"}";

    // Validate UUID format for the license key
    if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $customerLicenceKey)) {
        echo json_encode(["status" => "error", "message" => "Invalid license details"]);
        exit;
    }

    // Check if the license key exists in the database
    $sql = "SELECT * FROM licences WHERE LicenceKey = ?";
    $existingLicense = $functions->selectQuery($sql, [$customerLicenceKey]);

    // If license key doesn't exist, return an error
    if (!empty($existingLicense)) {
        echo json_encode(["status" => "error", "message" => "Invalid license details"]);
        exit;
    }

    try {
        // Create license
        $sql = "INSERT INTO licences(Firstname, Lastname, Email, Country, Company, LicenceKey, LicenceInfo) VALUES (?,?,?,?,?,?,?)";

        try {
            $functions->selectQuery($sql, [
                $customerFirstname,
                $customerLastname,
                $customerEmail,
                $customerCountry,
                $customerCompany,
                $customerLicenceKey,
                $licenseDetails
            ]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $functions->errorLog($errorMessage, "verifyLicenseUpdate");
            throw $e;
        }

        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}