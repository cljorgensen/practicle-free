<?php
require_once("../inc/dbconnection.php");
require_once("functions.php");

$ElementPath = $_GET['elementpath'];
$ElementRef = $_GET['elementref'];
$ElementID = $_GET['elementid'];
$UserID = $_GET['userid'];

$UploadMethod = "singlefile";
$ds          = DIRECTORY_SEPARATOR;
$folder = "../uploads/files_$ElementPath/";

if (!empty($_FILES)) {

    $filename_original = $_FILES['file']['name'];
    $tempFile = $_FILES['file']['tmp_name'];
    
    //Get clean filename without extension 
    $tmp = explode('.', $filename_original);
    $file_ext = strtolower(end($tmp));
    //Call randomizer
    $randomname = generateRandomString(20);
    $destination = $randomname.".".$file_ext;
    //Put filename together with extension
    $targetPath = $folder . $randomname . "." . $file_ext;
 
    // CHECK IF FILE ALREADY EXIST
    if (file_exists($targetPath)) {
        errorlog("$targetPath already exist","cifileupload.php");
    }

    // FILE SIZE CHECK
    if ($error == "") {
        // 1,000,000 = 1MB
        if ($_FILES["file"]["size"] > 50000000) {
            errorlog($_FILES["file"]["name"] . " - file size is too big! (Max 50 MB)", "cifileupload.php");
            echo $_FILES["file"]["name"] . " - file size is too big! (Max 50 MB)";
        }
    }
    // ALL CHECKS OK - MOVE FILE
    if ($error == "") {

        $value = folderExist($folder);
        
        if ($value == false) {
            mkdir($folder, 0777);
        }
        
        if (!move_uploaded_file($tempFile, $targetPath)) {
            errorlog("Error moving $tempFile to $targetPath", "cifileupload.php");
            echo "Error moving $tempFile to $targetPath";
        }

        if ($file_ext == "jpg"){
            list($width, $height) = getimagesize($targetPath);
            $new_width = 800;
            $new_height = ($height / $width) * $new_width;
            $newimage = imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefromjpeg($targetPath);
            imagecopyresampled($newimage, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            // Output
            imagejpeg($newimage, $targetPath, 100);
        }

        $filename_original = strtolower($filename_original);
    }

    if ($error == "" && ($ElementPath === "itsm" || $ElementPath === "cis" || $ElementPath === "projects" || $ElementPath === "projecttasks" || $ElementPath === "companies" || $ElementPath === "users" || $ElementPath === "temp")  && ($file_ext === "docx" || $file_ext === "odt" || $file_ext === "doc" || $file_ext === "pdf" || $file_ext === "txt" || $file_ext === "pptx")) {
        require("../classes/class.filetotext.php");
        $documenttoread = $folder . $destination;
        $docObj = new Filetotext("$documenttoread");
        $FileContentReturn = $docObj->convertToText();
        //Sanitize content before loading to database
        $FileContentReturn = mysqli_real_escape_string($conn, $FileContentReturn);
        if($ElementPath === "cis"){
            $sql = "INSERT INTO files_$ElementPath (FileName, FileNameOriginal, RelatedElementID, Date, RelatedUserID, RelatedType, FileContent)
                VALUES ('$destination','$filename_original','$ElementID',NOW(),'$UserID','$ElementRef','$FileContentReturn');";

            $LogActionText = "file $filename_original added";
            createCILogEntry($ElementID, $ElementRef, $UserID, $LogActionText);
        }
        elseif ($ElementPath === "itsm") {
            $sql = "INSERT INTO files_$ElementPath (FileName, FileNameOriginal, RelatedElementID, Date, RelatedUserID, RelatedType, FileContent)
                VALUES ('$destination','$filename_original','$ElementID',NOW(),'$UserID','$ElementRef','$FileContentReturn');";

            $LogActionText = "file $filename_original added";
            createITSMLogEntry($ElementID, $ElementRef, $UserID, $LogActionText);
        } 
        else {
            $sql = "INSERT INTO files_$ElementPath (FileName, FileNameOriginal, RelatedElementID, Date, RelatedUserID, FileContent)
                VALUES ('$destination','$filename_original','$ElementID',NOW(),'$UserID', '$FileContentReturn');";
        }
        
        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    } elseif (fnmatch("temp", $ElementPath)) {
        //Insert new file to DB table for temp elements
        $sql = "INSERT INTO files_temp (FileName, FileNameOriginal, Date, RelatedUserID, TempPath)
                VALUES ('$destination','$filename_original',NOW(),'$UserID','$targetPath');";
        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        
    } elseif ($error == "") {
        //Insert new file to DB table
        $sql = "INSERT INTO files_$ElementPath (FileName, FileNameOriginal, RelatedElementID, Date, RelatedUserID, RelatedType, FileContent)
            VALUES ('$destination','$filename_original','$ElementID',NOW(),'$UserID','$ElementRef',NULL);";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}
