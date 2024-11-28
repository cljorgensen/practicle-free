<?php
include_once "./functions/functions.php";
include_once "./inc/dbconnection.php";

$uploadDirectory = "./uploads/import/";

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileType = $_FILES['file']['type'];
    $filePath = $_FILES['file']['tmp_name'];

    if ($fileType === 'text/csv' || $fileType === 'application/json') {
        // Move the uploaded file to the specified directory
        $fileName = $_FILES['file']['name'];
        $destination = $uploadDirectory . $fileName;
        move_uploaded_file($filePath, $destination);

        if ($fileType === 'text/csv') {
            $headers = getCSVHeaders($destination);
        } elseif ($fileType === 'application/json') {
            $headers = getJSONHeaders($destination);
        }
        echo json_encode($headers);

        // If the file was uploaded via JavaScript, delete it after processing
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            unlink($destination);
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid file type. Please upload a CSV or JSON file.'));
    }
} else {
    http_response_code(500);
    echo json_encode(array('error' => 'File upload failed.'));
}

function getCSVHeaders($filePath)
{
    $file = fopen($filePath, 'r');
    $headers = fgetcsv($file);
    fclose($file);
    return $headers;
}

function getJSONHeaders($filePath)
{
    $jsonData = file_get_contents($filePath);
    $jsonArray = json_decode($jsonData, true);
    return array_keys($jsonArray[0]);
}
