<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Set default timezone
date_default_timezone_set('Europe/Copenhagen');
// debug error level: low, medium, high
$functions->setDebugging("low");

function getCurrentDB(){
    global $conn;
    global $functions;

    $result = mysqli_query($conn, "SELECT database() AS the_db") or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $db = $row['the_db'];
    }

    return $db;
}

function convertOnClickToHref($html) {
    // Regular expression to match onclick attribute
    $pattern = '/<a\s+([^>]*)onclick="([^"]+)"([^>]*)>/i';
    
    // Replace onclick attribute with href attribute
    $replacement = '<a $1$3>';
    
    // Perform the replacement
    $html = preg_replace($pattern, $replacement, $html);
    
    return $html;
}

function sendMailToSinglePerson($To, $ToName, $Subject, $Content, $attachmentPath = null, $attachmentName = null)
{
    global $conn;
    global $functions;
    include_once "../inc/dbconnection.php";
    include_once "../vendor/autoload.php";

    $sql = "SELECT ID, SettingValue
            FROM settings
            WHERE ID IN (13,26,27,28,29,30)";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        if ($row['ID'] == '13') {
            $FromName = $row['SettingValue'];
        }
        if ($row['ID'] == '26') {
            $Port = $row['SettingValue'];
        }
        if ($row['ID'] == '27') {
            $SMTPSecure = $row['SettingValue'];
        }
        if ($row['ID'] == '28') {
            $Host = $row['SettingValue'];
        }
        if ($row['ID'] == '29') {
            $Username = $row['SettingValue'];
            $From = $row['SettingValue'];
        }
        if ($row['ID'] == '30') {
            $Password = $row['SettingValue'];
        }
    }

    $mail = new PHPMailer(true);
    $mail->IsSMTP(true);
    $mail->CharSet        = "UTF-8";
    $mail->Encoding = 'quoted-printable';
    $mail->SMTPDebug     = 0;
    $mail->Port         = $Port;
    $mail->SMTPSecure     = "$SMTPSecure";
    $mail->SMTPAuth     = true;
    $mail->Mailer         = "smtp";
    $mail->Host          = "$Host";
    $mail->Username      = "$Username";
    $mail->Password       = "$Password";
    $mail->IsHTML(true);

    $mail->AddAddress($To, $ToName);
    $mail->SetFrom($From, $FromName);
    $mail->Subject = $Subject;
    $mail->MsgHTML($Content);

    // Add attachment if provided
    if (!empty($attachmentPath) && file_exists($attachmentPath)) {
        $mail->addAttachment($attachmentPath, $attachmentName);
    }

    if (!$mail->Send()) {
        echo "Error while sending Email.";
    }
}

function getRelatedManager($UserID)
{
    global $conn;
    global $functions;
    //Create New CI in cis table
    $sql = "SELECT RelatedManager FROM users WHERE users.ID = $UserID";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $RelatedManagerID = $row['RelatedManager'];
    }

    if (is_null($RelatedManagerID)) {
        $RelatedManagerID = "0";
    }
    return $RelatedManagerID;
}

function getRelatedSLAID($CompanyID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT RelatedSLAID FROM Companies WHERE ID = ?";

    // Parameters for the query
    $params = [$CompanyID];

    // Execute the query using selectQuery
    $result = $functions->selectQuery($sql, $params);

    // Check if a result was returned
    if (!empty($result) && isset($result[0]['RelatedSLAID'])) {
        return $result[0]['RelatedSLAID'];
    } else {
        $functions->errorlog("No RelatedSLAID found for CompanyID: $CompanyID", "getRelatedSLAID");
        return null;
    }
}

function getRelatedFormFieldName($FieldID)
{
    global $conn;
    global $functions;
    $sql = "SELECT FieldName FROM forms_fieldslist WHERE ID = $FieldID;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = $result->fetch_assoc()) {
        $FieldName = $row['FieldName'];
    }
    return $FieldName;
}

function getRelatedFormID($FieldID)
{
    global $conn;
    global $functions;
    $sql = "SELECT RelatedFormID FROM forms_fieldslist WHERE ID = $FieldID;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = $result->fetch_assoc()) {
        $RelatedFormID = $row['RelatedFormID'];
    }
    return $RelatedFormID;
}

function getRelatedFormTableName($FormID)
{
    global $conn;
    global $functions;
    $sql = "SELECT TableName FROM forms WHERE ID = $FormID;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = $result->fetch_assoc()) {
        $TableName = $row['TableName'];
    }
    return $TableName;
}

function getRelatedSLAIDFromBS($BSID)
{
    global $conn;
    global $functions;
    $sql = "SELECT RelatedSLA FROM businessservices WHERE ID = $BSID;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = $result->fetch_assoc()) {
        $RelatedSLA = $row['RelatedSLA'];
    }
    return $RelatedSLA;
}

function getSLAFromBS($BSID)
{
    global $conn;
    global $functions;
    $sql = "SELECT CIField22943447 FROM cmdb_ci_jsf03ynsyjuvoug WHERE ID = $BSID;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = $result->fetch_assoc()) {
        $CIField22943447 = $row['CIField22943447'];
    }
    return $CIField22943447;
}

function getRelatedBSFromTicket($TicketID)
{
    global $conn;
    global $functions;
    $sql = "SELECT RelatedBusinessService FROM tickets WHERE ID = $TicketID;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = $result->fetch_assoc()) {
        $RelatedBusinessService = $row['RelatedBusinessService'];
    }
    return $RelatedBusinessService;
}

function getSLANameFromBusinessService($BSID)
{
    global $conn;
    global $functions;

    $sql = "SELECT DISTINCT slaagreements.Name AS SLAName
            FROM slaagreements
            LEFT JOIN businessservices ON slaagreements.ID = businessservices.RelatedSLA
            WHERE businessservices.id = '$BSID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = $result->fetch_assoc()) {
        $SLAName = $row['SLAName'];
    }
    return $SLAName;
}

function getSLANameFromID($SLAID)
{
    global $conn;
    global $functions;

    $sql = "SELECT DISTINCT slaagreements.Name AS SLAName
            FROM slaagreements
            WHERE ID = '$SLAID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = $result->fetch_assoc()) {
        $SLAName = $row['SLAName'];
    }
    return $SLAName;
}

function getSLANameFromCompany($CompanyID)
{
    global $conn;
    global $functions;

    $sql = "SELECT DISTINCT slaagreements.Name AS SLAName
            FROM slaagreements
            LEFT JOIN Companies ON slaagreements.ID = companies.RelatedSLAID
            WHERE companies.id = '$CompanyID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = $result->fetch_assoc()) {
        $SLAName = $row['SLAName'];
    }
    return $SLAName;
}

function createProblemLogEntry($ProblemID, $UserID, $LogTypeID, $LogActionText)
{
    global $conn;
    global $functions;
    $sql = "INSERT INTO log_problems (LogTypeID, LogActionText, LogActionDate, RelatedProblemID, UserID) VALUES ('" . $LogTypeID . "','" . $LogActionText . "',NOW(),'" . $ProblemID . "','" . $UserID . "');";
    mysqli_query($conn, $sql);
}

function createRequestLogEntry($RequestID, $UserID, $LogTypeID, $LogActionText)
{
    global $conn;
    global $functions;
    $sql = "INSERT INTO logRequests (LogTypeID, LogActionText, LogActionDate, RelatedRequestID, UserID) VALUES ('" . $LogTypeID . "','" . $LogActionText . "',NOW(),'" . $RequestID . "','" . $UserID . "');";
    mysqli_query($conn, $sql);
}

function createChangeLogEntry($ChangeID, $UserID, $LogTypeID, $LogActionText)
{
    global $conn;
    global $functions;
    $sql = "INSERT INTO log_changes (LogTypeID, LogActionText, LogActionDate, RelatedChangeID, UserID) VALUES ('" . $LogTypeID . "','" . $LogActionText . "',NOW(),'" . $ChangeID . "','" . $UserID . "');";
    mysqli_query($conn, $sql);
}

function createDocLogEntry($docid, $UserID, $LogTypeID, $LogActionText)
{
    global $conn;
    global $functions;
    $sql = "INSERT INTO log_documents (LogTypeID, LogActionText, LogActionDate, RelatedDocID, UserID) VALUES ($LogTypeID,'$LogActionText',NOW(),$docid,$UserID)";
    mysqli_query($conn, $sql);
}

function createProjectLogEntry($projectid, $projecttaskid, $UserID, $LogTypeID, $LogActionText)
{
    global $conn;
    global $functions;
    if (empty($projecttaskid)) {
        $projecttaskid = "NULL";
    }

    $sql = "INSERT INTO projects_audit_log (Projectid, new_value, done_by)
                VALUES ('$projectid','$LogActionText','$UserID')";

    mysqli_query($conn, $sql);
}

function createProjectTaskLogEntry($projectid, $projecttaskid, $UserID, $LogTypeID, $LogActionText)
{
    global $conn;
    global $functions;
    $LogActionText = mysqli_real_escape_string($conn, $LogActionText);
    $sql = "INSERT INTO log_projecttasks (LogTypeID, LogActionText, LogActionDate, RelatedProjectID, RelatedProjectTaskID, UserID) 
                VALUES ($LogTypeID,'$LogActionText',NOW(),$projectid,$projecttaskid,$UserID)";
    mysqli_query($conn, $sql);
}

function createCILogEntry($CIID, $CITypeID, $UserID, $LogActionText)
{
    global $conn;
    global $functions;

    $LogActionText = mysqli_real_escape_string($conn, $LogActionText);
    $sql = "INSERT INTO log_cis (LogActionText, LogActionDate, RelatedElementID, RelatedType, RelatedUserID) VALUES ('$LogActionText',NOW(),$CIID,$CITypeID,$UserID);";
    if (!$conn->query($sql)) {
        throw new Exception("Error for function createCILogEntry: " . $conn->error);
    }
}

function createCILogBookEntry($CIID, $CITypeID, $UserID, $Content, $LogBookRelation)
{
    global $conn;
    global $functions;

    $Content = mysqli_real_escape_string($conn, $Content);
    $LogBookRelation = mysqli_real_escape_string($conn, $LogBookRelation);
    $sql = "INSERT INTO cmdb_logbook(RelatedCIType, RelatedCI, LogContent, Relation, RelatedUserID) 
            VALUES ($CITypeID,$CIID,'$Content','$LogBookRelation',$UserID);";

    if (!$conn->query($sql)) {
        throw new Exception("Error for function createCILogEntry: " . $conn->error);
    }
    else {
        return true;
    }
}

function deleteCILogBookEntry($LogBookID)
{
    global $conn;
    global $functions;

    $sql = "UPDATE cmdb_logbook
            SET Status = 0
            WHERE ID = $LogBookID;";

    if (!$conn->query($sql)) {
        throw new Exception("Error for function createCILogEntry: " . $conn->error);
    } else {
        return true;
    }
}

function createITSMLogEntry($ITSMID, $ITSMTypeID, $UserID, $LogActionText)
{
    global $conn;
    global $functions;

    $LogActionText = mysqli_real_escape_string($conn, $LogActionText);

    $sql = "INSERT INTO itsm_log (LogActionText, LogActionDate, RelatedElementID, RelatedType, RelatedUserID) VALUES (?, NOW(), ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $LogActionText, $ITSMID, $ITSMTypeID, $UserID);
        mysqli_stmt_execute($stmt);
        return true;
    } else {
        // Handle the prepare statement error
        $functions->errorlog('Prepare statement error: ' . mysqli_error($conn),'createITSMLogEntry');
        return false;
    }
}


function updateAndLogAssetChange($CIID, $ClassID, $ElementID, $ElementChange, $sql, $Previous, $UserID, $LogActionText)
{
    global $conn;
    global $functions;
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $LogTypeID = 2;
    createCILogEntry($CIID, $ClassID, $ElementID, $UserID, $LogTypeID, $LogActionText);
}

function createUserLogEntry($UserID, $UserSessionID, $LogTypeID, $LogActionText)
{
    global $conn;
    global $functions;

    $sql = "INSERT INTO log_users(LogTypeID, LogActionText, LogActionDate, RelatedUserID, UserID) VALUES ( $LogTypeID, '$LogActionText', NOW(), $UserID, $UserSessionID)";
    mysqli_query($conn, $sql);
}

function createCompanyLogEntry($CompanyID, $UserID, $LogTypeID, $LogActionText)
{
    global $conn;
    global $functions;
    $sql = "INSERT INTO log_companies (LogTypeID, LogActionText, LogActionDate, RelatedCompanyID, UserID) VALUES ('" . $LogTypeID . "','" . $LogActionText . "',NOW(),'" . $CompanyID . "','" . $UserID . "');";
    mysqli_query($conn, $sql);
}

function getOldValueOfCompany($CompanyID, $Field){
    global $conn;
    global $functions;

    $sql = "SELECT $Field FROM Companies WHERE ID = $CompanyID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["$Field"];
    }
    return $Value;
}

function getOldValueOfUser($UserID, $Field)
{
    global $conn;
    global $functions;

    $sql = "SELECT $Field FROM Users WHERE ID = $UserID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["$Field"];
    }
    return $Value;
}

function getCompanyPreValue($CompanyID, $Field)
{
    global $conn;
    global $functions;

    $sql = "SELECT $Field FROM Companies WHERE ID = $CompanyID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["$Field"];
    }
    return $Value;
}

function deleteCompanyLogs($CompanyID)
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM log_companies WHERE RelatedCompanyID = $CompanyID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteCompanyFiles($CompanyID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FileName FROM files_companies WHERE RelatedElementID = $CompanyID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $FileName = $row["FileName"];
        $Path = "./uploads/files_companies/$FileName";
        unlink($Path);
    }

    $sql = "DELETE FROM files_companies WHERE RelatedElementID = $CompanyID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteUserLogs($UsersID)
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM log_users WHERE RelatedUserID = $UsersID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteUserFiles($UsersID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FileName FROM files_users WHERE RelatedElementID = $UsersID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $FileName = $row["FileName"];
        $Path = "./uploads/files_users/$FileName";
        unlink($Path);
    }

    $sql = "DELETE FROM files_users WHERE RelatedElementID = $UsersID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createBSLogEntry($BSID, $UserID, $LogTypeID, $LogActionText)
{
    global $conn;
    global $functions;
    $sql = "INSERT INTO log_BS (LogTypeID, LogActionText, LogActionDate, RelatedBSID, UserID) VALUES ('" . $LogTypeID . "','" . $LogActionText . "',NOW(),'" . $BSID . "','" . $UserID . "');";
    $result = mysqli_query($conn, $sql);
}

function createUserFromEmail($EmailFrom, $FromName)
{
    global $conn;
    global $functions;

    $SaltedPassword = $functions->SaltAndHashPasswordForCompare($functions->generateRandomString(12));
    $NewPin = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
    $CompanyID = $functions->getSettingValue(48);

    $SystemName = $functions->getSettingValue(13);
    $parts = explode(" ", $FromName);

    if (count($parts) > 1) {
        $lastname = array_pop($parts);
        $firstname = implode(" ", $parts);
    } else {
        $firstname = $FromName;
        $lastname = " ";
    }

    $sql = "INSERT INTO users (Firstname, Lastname, Email, CompanyID, Username, Password, RelatedUserTypeID,Pin) VALUES ('$firstname','$lastname','$EmailFrom','$CompanyID','$EmailFrom','$SaltedPassword','2','$NewPin');";
    mysqli_query($conn, $sql);
    $last_id = mysqli_insert_id($conn);

    if (!empty($last_id)) {
        //sendMailToSinglePerson($EmailFrom, $FromName, "You have been registered", "Thank you for registering at $SystemName<br><br>To proceed please change password here:<br>$ChangePasswordURL");
        changePasswordSubmit($last_id);
    }
}

function createGroupLogEntry($GroupID, $UserID, $LogTypeID, $LogActionText)
{
    global $conn;
    global $functions;
    $sql = "INSERT INTO log_groups (LogTypeID, LogActionText, LogActionDate, RelatedGroupID, UserID) VALUES ('" . $LogTypeID . "','" . $LogActionText . "',NOW(),'" . $GroupID . "','" . $UserID . "');";
    mysqli_query($conn, $sql);
}

function incrementDocVersion($docid)
{
    global $conn;
    global $functions;
    $sql = "SELECT Version
            FROM knowledge_documents
            WHERE ID = $docid";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Currentversion = $row['Version'];
    }

    $NewVersion = $Currentversion + 1;
    $sql = "UPDATE knowledge_documents SET Version ='" . $NewVersion . "' WHERE ID='" . $docid . "';";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    return $NewVersion;
}

function CreateDocArchive($DocID, $ModuleID, $Version, $UserSessionID)
{
    global $conn;
    global $functions;

    $ITSMTableName = $functions->getITSMTableName($ModuleID);

    $sql = "SELECT Content FROM $ITSMTableName WHERE ID = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $DocID);
    mysqli_stmt_execute($stmt) or die('Query fail: ' . mysqli_error($conn));
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $Content = $row['Content'];
    } else {
        // Handle the case where the document doesn't exist
        return false;
    }

    $ContentFullText = html_entity_decode($Content);
    $ContentFullText = strip_tags(str_replace('<', ' <', $ContentFullText));
    $ContentFullText = filterStopWords($ContentFullText, $UserSessionID);

    $Content = htmlspecialchars_decode($Content);
    $Content = stripStringFromScrollbarTags($Content);

    $sql2 = "INSERT INTO itsm_knowledge_archive (RelatedDocumentID, RelatedModuleID, DocumentVersion, RelatedUser, Content, ContentFulltext) 
            VALUES (?, ?, ?, ?, ?, ?);";
    // Ensure variables are properly escaped to prevent SQL injection
    // This is critical for security when not using prepared statements
    $escapedDocID = mysqli_real_escape_string($conn, $DocID);
    $escapedModuleID = mysqli_real_escape_string($conn, $ModuleID);
    $escapedVersion = mysqli_real_escape_string($conn, $Version); // Assuming Version is a string. If not, casting might be unnecessary.
    $escapedUserSessionID = mysqli_real_escape_string($conn, $UserSessionID);
    $escapedContent = mysqli_real_escape_string($conn, $Content);
    $escapedContentFullText = mysqli_real_escape_string($conn, $ContentFullText);

    $sql3 = "INSERT INTO itsm_knowledge_archive (RelatedDocumentID, RelatedModuleID, DocumentVersion, RelatedUser, Content, ContentFulltext) 
            VALUES ('$escapedDocID', '$escapedModuleID', '$escapedVersion', '$escapedUserSessionID', '$escapedContent', '$escapedContentFullText');";

    $stmtArchive = mysqli_prepare($conn, $sql2);
    if (!$stmtArchive) {
        // Error preparing statement
        die('Prepare fail: ' . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($stmtArchive, "iisiss", $DocID, $ModuleID, $Version, $UserSessionID, $Content, $ContentFullText);
    if (!mysqli_stmt_execute($stmtArchive)) {
        // Error executing statement
        $Error = mysqli_error($conn);

        die('Execute fail: ' . mysqli_error($conn));
    }

    return true;
}

function setLastChangedOnDoc($docid, $UserID)
{
    global $conn;
    global $functions;
    $sql = "UPDATE knowledge_documents SET LastChanged=NOW(), LastChangedBy='" . $UserID . "' WHERE ID='" . $docid . "';";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createSystemLogEntry($UserID, $LogActionText)
{
    global $conn;
    global $functions;
    $sql = "INSERT INTO logsystem (LogTypeID, LogActionText, LogDate, UserID) VALUES ('3','" . $LogActionText . "',NOW(),'" . $UserID . "');";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createLastLoginUser($UserID)
{
    global $conn;
    global $functions;

    $sql = "UPDATE users Set LastLogon = NOW()
            WHERE ID=$UserID";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function CreateSaltValue()
{
    global $conn;
    global $functions;

    $controlString = $functions->generateRandomString(20);

    $sql = "UPDATE settings SET SettingValue = '$controlString'
            WHERE ID = 4;";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    return $controlString;
}

function checkForExistingPasswordChangeRequest($userID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ControlString FROM changepassword WHERE UserID = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $functions->errorlog("Failed to prepare the query: " . mysqli_error($conn), "checkForExistingPasswordChangeRequest");
        return false; // Handle the error accordingly
    }

    mysqli_stmt_bind_param($stmt, "s", $userID);

    if (!mysqli_stmt_execute($stmt)) {
        $functions->errorlog("Failed to execute the query: " . mysqli_stmt_error($stmt), "checkForExistingPasswordChangeRequest");
        return false; // Handle the error accordingly
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        $functions->errorlog("Failed to get the result set: " . mysqli_stmt_error($stmt), "checkForExistingPasswordChangeRequest");
        return false; // Handle the error accordingly
    }

    $controlString = null;

    if ($row = mysqli_fetch_assoc($result)) {
        $controlString = $row['ControlString'];
    }

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);

    return $controlString;
}

function changePasswordSubmit($userID)
{
    global $conn;
    global $functions;

    $exists = checkForExistingPasswordChangeRequest($userID);

    $controlString = $functions->generateRandomString(30);
    $systemURL = $functions->getSettingValue(17);
    $systemName = $functions->getSettingValue(13);
    $content = "";

    if (empty($exists)) {
        $sql = "INSERT INTO changepassword (UserID, ControlString) VALUES ('$userID', '$controlString')";
    } else {
        $sql = "UPDATE changepassword SET ControlString = '$controlString' WHERE UserID = '$userID'";
    }
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql2 = "SELECT Email, CONCAT(Firstname, ' ', Lastname) AS FullName FROM users WHERE ID = ?";

    $stmt2 = mysqli_prepare($conn, $sql2);
    if (!$stmt2) {
        $functions->errorlog("Failed to prepare the query: " . mysqli_error($conn), "changePasswordSubmit");
        return; // Handle the error accordingly
    }

    mysqli_stmt_bind_param($stmt2, "s", $userID);

    if (!mysqli_stmt_execute($stmt2)) {
        $functions->errorlog("Failed to execute the query: " . mysqli_stmt_error($stmt2), "changePasswordSubmit");
        return; // Handle the error accordingly
    }

    $result2 = mysqli_stmt_get_result($stmt2);
    if (!$result2) {
        $functions->errorlog("Failed to get the result set: " . mysqli_stmt_error($stmt2), "changePasswordSubmit");
        return; // Handle the error accordingly
    }

    $to = "";
    $toName = "";

    if ($row = mysqli_fetch_assoc($result2)) {
        $to = $row['Email'];
        $toName = $row['FullName'];
    }

    mysqli_free_result($result2);
    mysqli_stmt_close($stmt2);

    $subject = "Password change requested";
    $content = "You have requested a password change, please visit this link to change it:<br><br><a href=\"$systemURL/changepassword.php?controlstring=$controlString\">Click here</a><br><br>Kind Regards - $systemName";
    
    sendMailToSinglePerson($to, $toName, $subject, $content);
}


function changePassword($password, $userID)
{
    global $conn;
    global $functions;

    $newPassword = $functions->SaltAndHashPasswordForCompare($password);

    // Update the user's password
    $sql = "UPDATE users SET Password = ? WHERE ID = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $functions->errorlog("Failed to prepare the query: " . mysqli_error($conn), "changePassword");
        return null; // Handle the error accordingly
    }

    mysqli_stmt_bind_param($stmt, "ss", $newPassword, $userID);

    if (!mysqli_stmt_execute($stmt)) {
        $functions->errorlog("Failed to execute the query: " . mysqli_stmt_error($stmt), "changePassword");
        return null; // Handle the error accordingly
    }

    mysqli_stmt_close($stmt);

    deleteUserChangePasswordEntry($userID);
    return $newPassword;
}

function deleteUserChangePasswordEntry($userID)
{
    global $conn;
    global $functions;

    // Delete the user's password change entry
    $sql = "DELETE FROM changepassword WHERE UserID = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $functions->errorlog("Failed to prepare the query: " . mysqli_error($conn), "deleteUserChangePasswordEntry");
        return; // Handle the error accordingly
    }

    mysqli_stmt_bind_param($stmt, "s", $userID);

    if (!mysqli_stmt_execute($stmt)) {
        $functions->errorlog("Failed to execute the query: " . mysqli_stmt_error($stmt), "deleteUserChangePasswordEntry");
        return; // Handle the error accordingly
    }

    mysqli_stmt_close($stmt);
}

function createTestTickets($status, $number)
{

    global $conn;
    global $functions;
    $counter = 0;

    while ($counter <= $number) {
        $sql = "INSERT INTO tickets(Subject, Type, Status, Priority, ProblemText, DateCreated, CreatedByUserID, RelatedCompanyID, Responsible, ResponsibleTeam, RelatedCustomerID) VALUES ('Test ticket " . $counter . "',1,$status,3,'This is the problem text for ticket " . $counter . "',Now(),1,1,1,20,9)";
        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        $counter = $counter + 1;
    }
    return;
}

function convertToDanishTimeFormat($dateValue)
{
    $timestamp = strtotime($dateValue);
    $convertedFormat = date("d-m-Y G:i", $timestamp);
    return $convertedFormat;
}

function convertToDanishDateTimeFormat($dateValue)
{
    $timestamp = strtotime($dateValue);
    $convertedFormat = date("d-m-Y H:i", $timestamp);
    return $convertedFormat;
}

function convertFromDanishTimeFormat($dateValue)
{
    $timestamp = strtotime($dateValue);
    $convertedFormat = date("Y-m-d H:i:s", $timestamp);
    return $convertedFormat;
}

function convertToDanishDateFormat($dateValue)
{
    $timestamp = strtotime($dateValue);
    $convertedFormat = date("d-m-Y", $timestamp);
    return $convertedFormat;
}

function convertToTimeOnly($dateValue)
{
    $timestamp = strtotime($dateValue);
    $convertedFormat = date("G:i", $timestamp);
    return $convertedFormat;
}

function getTicketSLAID($CompanyID)
{
    global $conn;
    global $functions;

    $sql = "SELECT companies.ID, slaagreements.ID AS SLAAgreementID 
            FROM companies 
            INNER JOIN slaagreements ON companies.RelatedSLAID = slaagreements.ID 
            WHERE companies.ID = $CompanyID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $SLAAgreementID = $row['SLAAgreementID'];
    }
    
    return $SLAAgreementID;
}

function getProjectNameFromID($ProjectID)
{
    global $conn;
    global $functions;
    $sql = "SELECT projects.ID, projects.Name 
                FROM projects
                WHERE ID = $ProjectID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Name = $row['Name'];
    }
    return $Name;
}

function getProjectIDFromTaskID($ProjectTaskID)
{
    global $conn;
    global $functions;
    $sql = "SELECT project_tasks.RelatedProject
                FROM project_tasks
                WHERE ID = $ProjectTaskID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['RelatedProject'];
    }
    return $Value;
}

function getProjectTaskStartDate($ProjectTaskID)
{
    global $conn;
    global $functions;
    $sql = "SELECT project_tasks.Start
                FROM project_tasks
                WHERE ID = $ProjectTaskID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['Start'];
    }
    return $Value;
}

function getProjectTaskDeadlineDate($ProjectTaskID)
{
    global $conn;
    global $functions;
    $sql = "SELECT project_tasks.Deadline
            FROM project_tasks
            WHERE ID = $ProjectTaskID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['Deadline'];
    }
    return $Value;
}

function getProjectDeadlineDate($ProjectID)
{
    global $conn;
    global $functions;
    $sql = "SELECT projects.Deadline
            FROM projects
            WHERE ID = $ProjectID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['Deadline'];
    }
    return $Value;
}

function getProblemSLAID($CompanyID)
{
    global $conn;
    global $functions;
    $sql = "SELECT companies.ID, slaagreements.ID AS SLAAgreementID 
                FROM companies 
                INNER JOIN slaagreements ON companies.RelatedSLAID = slaagreements.ID 
                WHERE companies.ID = $CompanyID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $SLAAgreementID = $row['SLAAgreementID'];
    }
    return $SLAAgreementID;
}

function getUserRoleName($RoleID)
{
    global $conn;
    global $functions;
    $sql = "SELECT RoleName
            FROM roles
            WHERE roles.ID = $RoleID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $RoleName = $row['RoleName'];
    }
    return $RoleName;
}

function getRequestSLAID($CompanyID)
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT companies.ID, slaagreements.ID AS SLAAgreementID 
                FROM companies 
                INNER JOIN slaagreements ON companies.RelatedSLAID = slaagreements.ID 
                WHERE companies.ID = $CompanyID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $SLAAgreementID = $row['SLAAgreementID'];
    }
    return $SLAAgreementID;
}


/*
     * Givet en $ElementType (1 = Incident, 2 = Problem, 3 = Change)
     * Givet en $SLAName (Gold / Silver / Bronze (1,2,3)
     * Givet en $Prioritet (1,2,3,4)
     * Givet en $EventType (Recieved = 2, AssignedToTeam = 3, AssignedToTechnician = 4, InResoltuion = 5, Resolved = 6)
     * 
     * 
     * Find Reaktiontiden ud fra SLA Matrix.
     * 
     * Returnere (deadline) tidspunkt, og om den er overholdt 
     */
function GetElementStatus($ElementType, $SLAName, $Prioritet, $EventType, $CreateDateTime)
{
    $elementReaktionsTid = FindMatrixVærdiUdFraElementType($ElementType, $SLAName, $Prioritet, $EventType);

    // Find workday

    // Er det en normal åbningsdag ?

    // Eller findes der en speciel day (f.eks. 24 dec.)

    // start <-> slut  = $ReactionTimeMinutes

    // Edge-case, den lapper over...
}

// Antal minuter
function FindMatrixVærdiUdFraElementType($ElementType, $SLAName, $Prioritet, $EventType)
{
    // Silver & Urgent
    return 15;
}

function FindWorkInDatabase($date)
{
    return [];
}

function prepare_string($String)
{
    global $conn;
    global $functions;
    $String = mysqli_real_escape_string($conn, $String);
    return $String;
}

function getReactionTimeMinutes($ElementPriority, $ElementSLAID, $SLAAction, $ModuleID)
{
    global $conn;
    global $functions;
    //Get SLA Reaction time minutes for the ticket
    $ReactionTimeMinutes = "";
    $sql = "SELECT $SLAAction AS SLAActionMinutes FROM sla_reaction_time_matrix 
            WHERE RelatedElementSLAID = $ElementSLAID AND RelatedModuleID = $ModuleID AND RelatedPriorityID = $ElementPriority";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set reactiontimeinminutes for agreement and priority of ticket
        $ReactionTimeMinutes = $row['SLAActionMinutes'];
    }
    return $ReactionTimeMinutes;
}

function getReactionTimeChangeMinutes($ChangePriorityVal, $ChangeSLAID, $SLAAction)
{
    global $conn;
    global $functions;
    //Get SLA Reaction time minutes for the ticket
    $ReactionTimeMinutes = "";
    $sql = "SELECT " . $SLAAction . " AS SLAActionMinutes FROM changereactionmatrix WHERE RelatedChangeSLAID='" . $ChangeSLAID . "' AND RelatedPriorityID='" . $ChangePriorityVal . "';";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set reactiontimeinminutes for agreement and priority of ticket
        $ReactionTimeMinutes = $row['SLAActionMinutes'];
    }
    return $ReactionTimeMinutes;
}

function getDateTimeViolated($ElementCreatedDateVal, $ReactionTimeMinutes)
{
    global $conn;
    global $functions;
    //$date = date_create($ElementCreatedDateVal);
    $DateReachedForSLABreach = "";
    $ExitNow = 0;

    $sql = "select
                    if('$ElementCreatedDateVal' < timestamp(dates,timeend) and '$ElementCreatedDateVal' > timestamp(dates,timestart),
                    '$ElementCreatedDateVal', 
                    timestamp(dates,timestart)
                    ) as block_begin,
                    round(if('$ElementCreatedDateVal' < timestamp(dates,timeend) and '$ElementCreatedDateVal' > timestamp(dates,timestart),
                    time_to_sec(timediff(timeend, time('$ElementCreatedDateVal'))) / 60,
                    time_to_sec(timediff(timeend, timestart)) / 60
                    ),0) as block_length
                from (
                    select *
                    from (
                        select 
                        date_add(
                            date('$ElementCreatedDateVal'), 
                            interval (day + offset * 7) - dayofweek('$ElementCreatedDateVal') day) as dates, 
                        timestart, 
                        timeend
                        from workdays, week
                        where day > 0 and dates is null
                        and (day + (offset * 7)) >= dayofweek('$ElementCreatedDateVal')
                    ) as rolling_dates
                    where not exists (
                    select 1 
                    from workdays 
                    where rolling_dates.dates = dates
                    )
                    union 
                    select 
                    dates, 
                    timestart, 
                    timeend
                    from workdays
                    where day = 0
                ) as all_dates
                where timestamp(dates,timeend) > '$ElementCreatedDateVal'
                and time_to_sec(timediff(timeend, timestart)) > 0
                order by 1,2";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {

        if ($ExitNow === 1) {
            break;
        } else {
            $BlockBegin = $row['block_begin'];
            $BlockLength = $row['block_length'];
            //$BlockEnd = date_add(date_create($BlockBegin), date_interval_create_from_date_string("{$BlockLength} minutes"));
            $date = date_create($BlockBegin);
            
            $date2 = date_format($date, 'Y-m-d H:m');

            $BlockEnd = date_add($date, date_interval_create_from_date_string("{$BlockLength} minutes"));
            $date3 = date_format($BlockEnd, 'Y-m-d H:m');

            $ReactionTimeMinutes = ($ReactionTimeMinutes - $BlockLength);

            if ($ReactionTimeMinutes < 0) {
                //$MinutesLeft = ($MinutesLeft * -1);
                $DateReachedForSLABreach = date_add($BlockEnd, date_interval_create_from_date_string("{$ReactionTimeMinutes} minutes"));
                $ExitNow = 1;
            }
        }
    }

    $NewDateReachedForSLABreach = date_format($DateReachedForSLABreach, "Y-m-d H:i:s");
    return $NewDateReachedForSLABreach;
}

function getSLAViolated($TicketCreatedDateVal, $ReactionTimeMinutes)
{
    $SLAViolated = "False";
    //Get SLA Agreement ID for the company
    $DateTimeNow = date('Y-m-d H:i:s');
    $date = date_create($TicketCreatedDateVal);
    $SLAViolatedDateTime = date_add($date, date_interval_create_from_date_string("{$ReactionTimeMinutes} minutes"));

    if ($DateTimeNow < date_format($SLAViolatedDateTime, "Y-m-d H:i:s")) {
        $SLAViolated = "False";
    } else {
        $SLAViolated = "True";
    }

    return $SLAViolated;
}

// function getSLAViolatedDateTime($ElementCreatedDateVal, $ReactionTimeMinutes){
//     //Get SLA Agreement ID for the company
//     $date = date_create($ElementCreatedDateVal);
//     $SLAViolatedDateTime = date_add($date, date_interval_create_from_date_string("{$ReactionTimeMinutes} minutes"));
//     $SLAViolatedDateTime = date_format($SLAViolatedDateTime,'Y-m-d H:i:s');
//     return $SLAViolatedDateTime;
// }

function getSLAViolatedDateTime($ElementCreatedDateVal, $ReactionTimeMinutes)
{

    $SLAViolatedDateTime = getDateTimeViolated($ElementCreatedDateVal, $ReactionTimeMinutes);
    return $SLAViolatedDateTime;
}

function createTimelineSLAViolationDates($ElementID, $ModuleID, $RelatedStatusCodeID, $SLAViolationDate)
{
    global $functions;

    // Ensure SLAViolationDate is properly formatted
    if ($SLAViolationDate instanceof DateTime) {
        $SLAViolationDate = $SLAViolationDate->format("Y-m-d H:i:s");
    } else {
        $SLAViolationDate = date("Y-m-d H:i:s", strtotime($SLAViolationDate));
    }

    // SQL query with placeholders
    $sql = "INSERT INTO itsm_slatimelines (RelatedElementID, RelatedElementTypeID, RelatedStatusCodeID, SLAViolationDate) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE SLAViolationDate = VALUES(SLAViolationDate)";

    // Parameters for the query
    $params = [$ElementID, $ModuleID, $RelatedStatusCodeID, $SLAViolationDate];

    // Tables to lock
    $tables = ["itsm_slatimelines"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the query succeeded
    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to insert or update SLAViolationDate for ElementID: $ElementID, ModuleID: $ModuleID", "createTimelineSLAViolationDates");
        return false;
    }
}

function insertTimelineUpdatedDate($TicketID, $RelatedStatusCodeID, $SLAViolationDate)
{
    global $conn;
    global $functions;

    $sql = "INSERT INTO ticketslatimeline (RelatedTicketID, TimelineUpdatedDate, RelatedStatusCodeID, SLAViolationDate) VALUES ('" . $TicketID . "',NOW(),'" . $RelatedStatusCodeID . "','" . $SLAViolationDate . "');";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateTimelineUpdatedDate($ElementID, $ModuleID, $RelatedStatusCodeID)
{
    global $conn;
    global $functions;

    $sql = "UPDATE itsm_slatimelines SET TimelineUpdatedDate = NOW() 
            WHERE RelatedElementID='$ElementID' AND RelatedElementTypeID = '$ModuleID' AND RelatedStatusCodeID = '$RelatedStatusCodeID';";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateTimelineSLAViolationDate($ElementID, $ModuleID, $RelatedStatusCodeID, $SLAViolationDate)
{
    global $conn;
    global $functions;

    $sql = "UPDATE itsm_slatimelines SET SLAViolationDate = '$SLAViolationDate' 
            WHERE RelatedElementID = $ElementID AND RelatedElementTypeID = $ModuleID AND RelatedStatusCodeID = $RelatedStatusCodeID;";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function removeTimelineUpdatedDate($ElementID, $RelatedStatusCodeID, $TimelineTable)
{
    global $conn;
    global $functions;

    $sql = "UPDATE $TimelineTable SET TimelineUpdatedDate = NULL WHERE RelatedElementID = '$ElementID' AND RelatedStatusCodeID='$RelatedStatusCodeID';";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function removeProblemTimelineUpdatedDate($ProblemID, $RelatedStatusCodeID)
{
    global $conn;
    global $functions;

    $sql = "UPDATE problemslatimeline SET TimelineUpdatedDate = NULL WHERE RelatedElementID='$ProblemID' AND RelatedStatusCodeID='$RelatedStatusCodeID';";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getResolveDateTime($TicketID)
{
    global $conn;
    global $functions;
    $ResolveDate = "";
    $sql = "SELECT SLAViolationDate
        FROM ticketslatimeline
        WHERE RelatedElementID = $TicketID AND
        RelatedStatusCodeID = 6;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ResolveDate = $row['SLAViolationDate'];
    }
    return $ResolveDate;
}

function getITSMResolvedStatus($ITSMTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT DoneStatusID
            FROM modules
            WHERE ID = $ITSMTypeID;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $DoneStatusID = $row['DoneStatusID'];
    }
    return $DoneStatusID;
}

function getRequestResolveDateTime($RequestID)
{
    global $conn;
    global $functions;
    $ResolveDate = "";
    $sql = "SELECT SLAViolationDate
                FROM requestslatimeline
                WHERE RelatedElementID = $RequestID AND
                RelatedStatusCodeID = 6;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ResolveDate = $row['SLAViolationDate'];
    }
    return $ResolveDate;
}

function getReviewDateTime($ChangeID)
{
    global $conn;
    global $functions;
    $ReviewDate = "";
    $sql = "SELECT DeadlineReview
        FROM changes
        WHERE ID = $ChangeID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ReviewDate = $row['DeadlineReview'];
    }
    return $ReviewDate;
}

function getTicketCustomerID($TicketID)
{
    global $conn;
    global $functions;
    $query = "SELECT tickets.RelatedCustomerID FROM tickets WHERE tickets.ID = '" . $TicketID . "';";
    $result = mysqli_query($conn, $query) or die("Couldn't execute query. " . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['RelatedCustomerID'];
    }
    return $value;
}

function getProblemCustomerID($ProblemID)
{
    global $conn;
    global $functions;
    $query = "SELECT problems.RelatedCustomerID FROM problems WHERE problems.ID = '" . $ProblemID . "';";
    $result = mysqli_query($conn, $query) or die("Couldn't execute query. " . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['RelatedCustomerID'];
    }
    return $value;
}

function getChangeCustomerID($ChangeID)
{
    global $conn;
    global $functions;
    $query = "SELECT changes.RequestedByCustomerID FROM changes WHERE changes.ID = '$ChangeID';";
    $result = mysqli_query($conn, $query) or die("Couldn't execute query. " . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['RequestedByCustomerID'];
    }
    return $value;
}

function getTicketResonsbielID($TicketID)
{
    global $conn;
    global $functions;
    $query = "SELECT tickets.Responsible FROM tickets WHERE tickets.ID = '" . $TicketID . "';";
    $result = mysqli_query($conn, $query) or die("Couldn't execute query. " . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['Responsible'];
    }
    return $value;
}

function getTicketConsultantID($TicketID)
{
    global $conn;
    global $functions;
    $query = "SELECT tickets.RelatedConsultantID FROM tickets WHERE tickets.ID = '" . $TicketID . "';";
    $result = mysqli_query($conn, $query) or die("Couldn't execute query. " . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['RelatedConsultantID'];
    }
    return $value;
}

function getRequestCustomerID($RequestID)
{
    global $conn;
    global $functions;
    $query = "SELECT Requests.RelatedCustomerID FROM Requests WHERE Requests.ID = '" . $RequestID . "';";
    $result = mysqli_query($conn, $query) or die("Couldn't execute query. " . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['RelatedCustomerID'];
    }
    return $value;
}


function getRequestResonsbielID($RequestID)
{
    global $conn;
    global $functions;
    $query = "SELECT Requests.Responsible FROM Requests WHERE Requests.ID = '" . $RequestID . "';";
    $result = mysqli_query($conn, $query) or die("Couldn't execute query. " . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['Responsible'];
    }
    return $value;
}

function getRequestConsultantID($RequestID)
{
    global $conn;
    global $functions;
    $query = "SELECT Requests.RelatedConsultantID FROM Requests WHERE Requests.ID = '" . $RequestID . "';";
    $result = mysqli_query($conn, $query) or die("Couldn't execute query. " . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['RelatedConsultantID'];
    }
    return $value;
}

function getCurrentTechnicianName($UserID)
{
    global $conn;
    global $functions;
    //Find the name of current technician
    $sql = "SELECT CONCAT(Users.FirstName,' ', Users.LastName) AS UsersName FROM Users WHERE ID='" . $UserID . "';";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $TechnicianNameVal = $row['UsersName'];
    }
    return $TechnicianNameVal;
}

function updateTicketStatus($TicketID, $TicketStatusID)
{
    global $conn;
    global $functions;
    //Update status
    $sql = "UPDATE tickets SET Status = $TicketStatusID WHERE ID = $TicketID";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    updateKanbanTaskFromElement($TicketID, '1', $TicketStatusID);
}

function updateProblemStatus($ProblemID, $ProblemStatusID)
{
    global $conn;
    global $functions;
    //Update status
    $sql = "UPDATE problems SET Status=$ProblemStatusID WHERE ID=$ProblemID";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    updateKanbanTaskFromElement($ProblemID, '5', $ProblemStatusID);
}

function updateRequestStatus($RequestID, $RequestStatusID)
{
    global $conn;
    global $functions;
    //Update status
    $sql = "UPDATE requests SET Status = $RequestStatusID WHERE ID = $RequestID";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    updateKanbanTaskFromElement($RequestID, '7', $RequestStatusID);
}

function updateChangeStatus($ChangeID, $ChangeStatusID)
{
    global $conn;
    global $functions;
    $sql = "UPDATE changes SET changes.status = $ChangeStatusID WHERE changes.ID=$ChangeID";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    updateKanbanTaskFromElement($ChangeID, '2', $ChangeStatusID);
}

function updateTicketPriority($TicketID, $NewPriority)
{
    global $conn;
    global $functions;
    $sql = "UPDATE tickets SET tickets.Priority='" . $NewPriority . "' WHERE tickets.ID=" . $TicketID . ";";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateRequestPriority($RequestID, $NewPriority)
{
    global $conn;
    global $functions;
    $sql = "UPDATE requests SET Requests.Priority='" . $NewPriority . "' WHERE Requests.ID=" . $RequestID . ";";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateTicketType($TicketID, $NewType)
{
    global $conn;
    global $functions;
    $sql = "UPDATE tickets SET tickets.Type='" . $NewType . "' WHERE tickets.ID=" . $TicketID . ";";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateTeam($TicketID, $NewTeam)
{
    global $conn;
    global $functions;

    $sql = "UPDATE tickets SET tickets.ResponsibleTeam = $NewTeam WHERE tickets.ID=" . $TicketID . ";";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateRequestTeam($RequestID, $NewTeam)
{
    global $conn;
    global $functions;

    $sql = "UPDATE requests SET requests.ResponsibleTeam = $NewTeam WHERE requests.ID=" . $RequestID . ";";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateProblemTeam($ProblemID, $NewTeam)
{
    global $conn;
    global $functions;

    $sql = "UPDATE problems SET problems.ResponsibleTeam = $NewTeam WHERE problems.ID=" . $ProblemID . ";";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateTechnician($TicketID, $NewTechnician)
{
    global $conn;
    global $functions;
    $sql = "UPDATE tickets SET tickets.Responsible='" . $NewTechnician . "' WHERE tickets.ID=" . $TicketID . ";";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateRequestTechnician($RequestID, $NewTechnician)
{
    global $conn;
    global $functions;
    $sql = "UPDATE requests SET requests.Responsible='$NewTechnician' WHERE requests.ID=$RequestID;";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateChangeTechnician($ChangeID, $NewTechnician)
{
    global $conn;
    global $functions;
    $sql = "UPDATE changes SET changes.Responsible='" . $NewTechnician . "' WHERE changes.ID=" . $ChangeID . ";";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function checkIfTimelineEntryExists($TicketID, $TicketStatusID)
{
    global $conn;
    global $functions;
    $exists = "False";
    $sql = "SELECT ID, RelatedTicketID, RelatedStatusCodeID 
        FROM ticketslatimeline
        WHERE RelatedTicketID=" . $TicketID . " 
        AND RelatedStatusCodeID='" . $TicketStatusID . "';";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        if (!$row['ID']) {
            $exists = "FALSE";
        } else {
            $exists = "True";
        }
    }
    return $exists;
}

function checkIfTimelineEntryChangesExists($ChangeID, $ChangestatusID)
{
    global $conn;
    global $functions;
    $exists = "False";
    $sql = "SELECT ID, RelatedChangeID, RelatedStatusCodeID 
        FROM changes_timeline
        WHERE RelatedChangeID=" . $ChangeID . " 
        AND RelatedStatusCodeID='" . $ChangestatusID . "';";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        if (!$row['ID']) {
            $exists = "FALSE";
        } else {
            $exists = "True";
        }
    }
    return $exists;
}

function addtotaskslist($ElementID, $UserID, $ModuleID, $RedirectPage)
{
    global $conn;
    global $functions;
    
    $DateTimeNow = date('Y-m-d H:i:s');

    $ModuleName = getModuleNameFromModuleID($ModuleID);
    $ModuleName = $functions->translate($ModuleName);
    $SLASupported = $functions->getITSMSLASupport($ModuleID);
    $ModuleType = $functions->getITSMModuleType($ModuleID);

    $ModulesToExclude = [];
    if($ModuleType != "1"){
        $ModulesToExclude[] = "$ModuleID";
    }

    $ModulesToExclude[] = "6";
    $ModulesToExclude[] = "13";

    $RedirectPage = "javascript:viewITSM($ElementID,$ModuleID,'1','modal');";
    if (in_array($ModuleID, $ModulesToExclude)) {
        if ($ModuleID == "6") {
            $RedirectPage = "projects_view.php?projectid=$ElementID";
            $Subject = getProjectName($ElementID);
            $Responsible = getProjectManager($ElementID);
            $Deadline = getProjectDeadlineDate($ElementID);
        } else if ($ModuleID == "13") {
            $RedirectPage = "projects_tasks_view.php?projecttaskid=$ElementID";
            $Subject = getProjectTaskName($ElementID);
            $Responsible = getProjectTaskResponsible($ElementID);
            $Deadline = getProjectTaskDeadlineDate($ElementID);
        } else {
            $SubjectColumnName = getSubjectColumnFromModuleID($ModuleID);
            $MainTableName = getMainTableNameFromModuleID($ModuleID);
            $Subject = getSubjectFromModuleElementID($ElementID, $SubjectColumnName, $MainTableName);
            $Responsible = getResponsibleFromModuleID($ElementID, $MainTableName);
            if (empty($Responsible)) {
                $Responsible = "NULL";
            }
            $Deadline = date('Y-m-d H:i:s', time() + 604800);
        }
    } else {
        $SubjectColumnName = getSubjectColumnFromModuleID($ModuleID);
        $MainTableName = getMainTableNameFromModuleID($ModuleID);
        $Subject = getSubjectFromModuleElementID($ElementID, $SubjectColumnName, $MainTableName);
        $Responsible = getResponsibleFromModuleID($ElementID, $MainTableName);
        if (empty($Responsible)) {
            $Responsible = "NULL";
        }
        if($SLASupported == "1"){
            $Deadline = getDeadlineForSLAElementID($ElementID, $ModuleID);
        } else {
            $Deadline = date('Y-m-d H:i:s', time() + 604800);
        }
    }
    $Exists = checkIfTaskExists($UserID, $ElementID, $ModuleID);

    $Status = "1";
    if ($Exists == "No") {
       // Prepare the SQL statement with placeholders
        $stmt = $conn->prepare("INSERT INTO taskslist (ModuleName, Headline, RelatedUserID, Responsible, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Bind parameters to the placeholders
        // The 'sssi...' string describes the types of the parameters: 's' means string, 'i' means integer
        $stmt->bind_param('sssisssssi', $ModuleName, $Subject, $UserID, $Responsible, $RedirectPage, $DateTimeNow, $ElementID, $ModuleID, $Deadline, $Status);
        if ($stmt->execute()) {
        } else {
            $functions->errorlog("Error: " . $stmt->error,"addtotaskslist");
        }

        // Close statement
        $stmt->close();
    }

    return $Exists;
}

function getModuleRedirectPageModuleID($ModuleID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ElementViewPage
            FROM modules
            WHERE ID = $ModuleID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ElementViewPage = $row['ElementViewPage'];
    }

    return $ElementViewPage;
}

function getModuleNameFromModuleID($ModuleID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ShortElementName
            FROM itsm_modules
            WHERE ID = $ModuleID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ShortElementName = $row['ShortElementName'];
    }

    return $functions->translate(ucfirst($ShortElementName));
}

function getResponsibleFromElementID($ElementID, $MainTableName, $ResponsibleColumnName)
{
    global $conn;
    global $functions;

    $sql = "SELECT $ResponsibleColumnName
            FROM $MainTableName
            WHERE ID = $ElementID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Responsible = $row["$ResponsibleColumnName"];
    }

    return $Responsible;
}

function getResponsibleFromModuleID($ElementID, $MainTableName)
{
    global $conn;
    global $functions;

    $sql = "SELECT Responsible
            FROM $MainTableName
            WHERE ID = $ElementID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Responsible = $row['Responsible'];
    }

    return $Responsible;
}

function getSubjectColumnFromModuleID($ModuleID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldName
            FROM itsm_fieldslist
            WHERE RelatedTypeID = $ModuleID AND RelationShowField = '1'
            LIMIT 1";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldName = $row['FieldName'];
    }

    return $FieldName;
}

function getMainTableNameFromModuleID($ModuleID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TableName
            FROM itsm_modules
            WHERE ID = $ModuleID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $TableName = $row['TableName'];
    }

    return $TableName;
}

function getSubjectFromModuleElementID($ElementID, $SubjectColumnName, $MainTableName)
{
    global $conn;
    global $functions;

    $sql = "SELECT $SubjectColumnName
            FROM $MainTableName
            WHERE ID = $ElementID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Subject = $row["$SubjectColumnName"];
    }

    return $Subject;
}

function addrequesttotaskslist($ElementID, $UserID, $SubjectVal, $redirectpage, $CompanyNameVal, $CustomerNameVal, $CreatedDateVal, $DeadlineResolveVal, $ModuleTypeID, $ModuleName)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $CreatedDateVal = convertToDanishTimeFormat($CreatedDateVal);
        $DeadlineResolveValForNote = convertToDanishTimeFormat($DeadlineResolveVal);
        $SubjectVal = mysqli_real_escape_string($conn, $SubjectVal);

        //Get Module Icon
        $ModuleID = 7;
        $Exists = checkIfTaskExists($UserID, $ElementID, $ModuleID, $SubjectVal);

        $Note = "";

        if ($Exists == "No") {
            $Note = "<b>Company:</b> " . $CompanyNameVal . "<br><b>Customer:</b> " . $CustomerNameVal . "<br><b>Request Created:</b> " . $CreatedDateVal .
                "<br><b>Resolution deadline:</b> " . $DeadlineResolveValForNote . "<br><b>Request subject:</b> " . $SubjectVal;
            $Subject = $ModuleName;

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES ('" . $Note . "','" . $Subject . "','" . $SubjectVal . "','" . $UserID . "','" . $redirectpage . "','" . $DateTimeNow . "','" . $ElementID . "','7','$DeadlineResolveVal','1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Request: <b>" . $ElementID . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script type='text/javascript'>
            $(document).ready(function(e) {
                pnotify('Request: <b>" . $ElementID . "</b> added as task','success');
            });
            </script>";
    }
}

function addworkflowtotaskslist($ElementID, $ModuleTypeID, $UserID, $StepName, $redirectpage, $SubjectVal, $StepOrder, $Description, $WFTID)
{

    global $conn;
    global $functions;

    $DateTimeNow = date('Y-m-d H:i:s');
    $Deadline = getDeadlineForElementID($ElementID, $ModuleTypeID);
    $SubjectColumnName = getSubjectColumnFromModuleID($ModuleTypeID);
    $MainTableName = getMainTableNameFromModuleID($ModuleTypeID);
    $SubjectVal = mysqli_real_escape_string($conn, $SubjectVal);
    $NewTaskID = "";

    $sql = "INSERT INTO taskslist (ModuleName, Headline, RelatedUserID, Responsible, DateAdded, Deadline, RelatedElementID, RelatedElementTypeID, GoToLink, Status, Description, wftid) 
                VALUES ('$SubjectVal','$StepName','$UserID','$UserID','$DateTimeNow','$Deadline',$ElementID,$ModuleTypeID,'$redirectpage','1','$Description','$WFTID');";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $NewTaskID = $conn->insert_id;

    return $NewTaskID;
}

function addITSMWorkFlowToTasksList($ElementID, $ITSMTypeID, $UserID, $StepName, $redirectpage, $SubjectVal, $StepOrder, $Description, $WFTID)
{

    global $conn;
    global $functions;

    $DateTimeNow = date('Y-m-d H:i:s');
    $Deadline = getITSMDeadlineForElementID($ElementID, $ITSMTypeID);
    $SubjectColumnName = "Subject";
    $MainTableName = $functions->getITSMTableName($ITSMTypeID);
    $SubjectVal = mysqli_real_escape_string($conn, $SubjectVal);
    $Title = $functions->translate("WorkFlow task");
    $ModuleName = getModuleNameFromModuleID($ITSMTypeID);
    $ModuleName = "<i class=\"fa-solid fa-thumbtack\" title=\"$Title\"></i>&ensp;".$functions->translate($ModuleName);

    $NewTaskID = "";

    $sql = "INSERT INTO taskslist (ModuleName, Headline, RelatedUserID, Responsible, DateAdded, Deadline, RelatedElementID, RelatedElementTypeID, GoToLink, Status, Description, wftid) 
            VALUES ('$ModuleName','$StepName','$UserID','$UserID','$DateTimeNow','$Deadline',$ElementID,$ITSMTypeID,'$redirectpage','1','$Description','$WFTID');";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $NewTaskID = $conn->insert_id;

    return $NewTaskID;
}

function addchangeworkflowtotaskslist($ElementID, $ModuleTypeID, $UserID, $SubjectVal, $redirectpage, $CompanyNameVal, $CustomerNameVal, $CreatedDateVal, $DeadlineResolveVal, $ModuleName, $StepOrder, $ChangeSubjectVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();

    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                        window.alert('DB is full: $FreeSpace MB left');
                    </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $CreatedDateVal = convertFromDanishTimeFormat($CreatedDateVal);
        $DeadlineResolveValForNote = convertToDanishTimeFormat($DeadlineResolveVal);
        $DeadlineResolveVal = convertFromDanishTimeFormat($DeadlineResolveVal);
        $SubjectVal = $ModuleName . ": " . $StepOrder . " - " . $SubjectVal;
        $SubjectVal = mysqli_real_escape_string($conn, $SubjectVal);

        $Exists = checkIfTaskExists($UserID, $ElementID, $ModuleTypeID, $SubjectVal);

        $Note = "";

        if ($Exists == "No") {
            $Note = "<b>" . _("Change") . ":</b> " . $ElementID . "<br><b>" . _("Company") . ":</b> " . $CompanyNameVal . "<br><b>" . _("Customer") . ":</b> " . $CustomerNameVal . "<br><b>" . _("Request") . " " . _("created") . ":</b> " . $CreatedDateVal .
                "<br><b>" . _("Resolution") . " " . _("deadline") . ":</b> " . $DeadlineResolveValForNote . "<br><b>" . _("Request") . " " . _("subject") . ":</b> " . $ChangeSubjectVal;
            $Subject = $ModuleName;

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, DateAdded, Deadline, RelatedElementID, RelatedElementTypeID, GoToLink, Status) 
                            VALUES ('$Note','$Subject','$SubjectVal','$UserID','$DateTimeNow','$DeadlineResolveVal',$ElementID,$ModuleTypeID,'$redirectpage','1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            return;
        }
    }
}

function addchangetotaskslist($ElementID, $UserID, $SubjectVal, $ChangeValidReason, $redirectpage, $ChangeCompanyNameVal, $ChangeCustomerNameVal, $ChangeCreatedDateVal, $ChangeReviewDeadlineVal, $ChangeBackoutPlanVal, $ChangeStartVal, $ChangeEndVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $ChangeCreatedDateVal = convertToDanishTimeFormat($ChangeCreatedDateVal);
        $ChangeStartValForNote = convertFromDanishTimeFormat($ChangeStartVal);
        $ChangeReviewDeadlineValForNote = convertToDanishTimeFormat($ChangeReviewDeadlineVal);
        $ChangeBackoutPlanValForNote = convertToDanishTimeFormat($ChangeBackoutPlanVal);
        $SubjectVal = mysqli_real_escape_string($conn, $SubjectVal);

        $ModuleTypeID = 2;
        $Exists = checkIfTaskExists($UserID, $ElementID, $ModuleTypeID, $SubjectVal);

        $Note = "";

        if ($Exists == "No") {
            $Note = "<b>Company:</b> " . $ChangeCompanyNameVal . "<br><b>Requester:</b> " . $ChangeCustomerNameVal . "<br><b>" . _("Change") . " " . _("Created") . ":</b> " . $ChangeCreatedDateVal .
                "<br><b>" . _("Review") . " " . _("deadline") . ":</b> " . $ChangeReviewDeadlineValForNote . "<br><b>" . _("Change") . " " . _("start") . ":</b> " . $ChangeStartValForNote . "<br><b>" . _("Change") . " " . _("Backout") . ":</b> " . $ChangeBackoutPlanValForNote . "<br><b>" . _("Change") . " " . _("End") . ":</b> " . $ChangeEndVal . "<br><b>" . _("Change") . " " . _("description") . ":</b> " . $SubjectVal;
            $Subject = _("Change");
            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) 
                VALUES ('" . $Note . "','" . $Subject . "','" . $SubjectVal . "','" . $UserID . "','" . $redirectpage . "','" . $DateTimeNow . "','" . $ElementID . "','2','$ChangeStartValForNote','1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Change: <b>" . $ElementID . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script type='text/javascript'>
            $(document).ready(function(e) {
            pnotify('Change: <b>" . $ElementID . "</b> added as task','success');
            });
            </script>";
    }
}

function addproblemtotaskslist($ProblemIDVal, $UserID, $ProblemDescriptionVal, $redirectpage, $ProblemCompanyNameVal, $ProblemCreated_DateVal, $ProblemDeadlineVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $ProblemCreated_DateVal = convertToDanishTimeFormat($ProblemCreated_DateVal);
        $ProblemDeadline = $ProblemDeadlineVal;
        $ProblemDeadlineValForNote = convertToDanishTimeFormat($ProblemDeadlineVal);
        $ProblemDescriptionVal = mysqli_real_escape_string($conn, $ProblemDescriptionVal);

        $ModuleID = 5;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        $Exists = checkIfTaskExists($UserID, $ProblemIDVal, $ModuleID, $ProblemDescriptionVal);

        $url = "problems_view.php?elementid=$ProblemIDVal";
        $Note = "";

        if ($Exists == "No") {
            $Note = "<b>Company:</b> " . $ProblemCompanyNameVal . "<br><b>Problem Created:</b> " . $ProblemCreated_DateVal .
                "<br><b>Review deadline:</b> " . $ProblemDeadlineValForNote . "<br><b>Problem Description:</b> " . $ProblemDescriptionVal;
            $Subject = _("Problem");

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES ('$Note','$Subject','$ProblemDescriptionVal','" . $UserID . "','" . $url . "','"
                . $DateTimeNow . "','" . $ProblemIDVal . "','5','" . $ProblemDeadline . "','1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Problem: <b>" . $ProblemIDVal . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script type='text/javascript'>
            $(document).ready(function(e) {
            pnotify('Problem: <b>" . $ProblemIDVal . "</b> added as task','success');
            });
            </script>";
    }
}

function adddocumenttotaskslist($UserID, $DocumentID, $CategoryName, $Name, $Version, $RelatedGroupID, $RelatedApproverID, $RelatedOwnerFullName, $Content, $StatusName, $LastChanged, $LastChangedBy)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $LastChanged = convertFromDanishTimeFormat($LastChanged);
        $Name = mysqli_real_escape_string($conn, $Name);
        $Content = mysqli_real_escape_string($conn, $Content);

        $ModuleID = 3;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        $Exists = checkIfTaskExists($UserID, $DocumentID, $ModuleID, $CategoryName);

        $url = "knowledge_view.php?docid=$DocumentID";
        $Note = "";

        if ($Exists == "No") {
            $Note = "<b>Document:</b> " . $DocumentID . "<br><b>Name:</b> " . $Name . "<br><b>Version:</b> " . $Version .
                "<br><b>Category:</b> " . $CategoryName . "<br><b>Owner:</b> " . $RelatedOwnerFullName . "<br><b>Status:</b> " . $StatusName;
            $Subject = _("Document");

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES ('" . $Note . "','" . $Subject . "','$Name','" . $UserID . "','" . $url . "','"
                . $DateTimeNow . "','" . $DocumentID . "','3','" . $DateTimeNow . "','1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Document: <b>" . $DocumentID . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script type='text/javascript'>
            $(document).ready(function(e) {
            pnotify('Document: <b>" . $DocumentID . "</b> added as task','success');
            });
            </script>";
    }
}

function addServerToTasksList($UserID, $ServerID, $CINameVal, $RelatedCompanyNameVal, $ServerTypeID, $ModelNoVal, $RamVal, $CPUVal, $HarddiskspaceVal, $HostnameVal, $StartDateVal, $ExpiresVal, $ActiveVal, $RemovedVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $StartDateVal = $functions->convertFromDanishDateFormat($StartDateVal);
        $ExpiresVal = $functions->convertFromDanishDateFormat($ExpiresVal);

        $ModuleID = 4;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        $Exists = checkIfTaskExists($UserID, $ServerID, $ModuleID, $HostnameVal);

        $url = "ci_servers_view.php?elementid=$ServerID";
        $Note = "";

        if ($Exists == "No") {
            $Note = "<b>" . _("Company") . ":</b> " . $RelatedCompanyNameVal . "<br><b>" . _("Server Hostname") . ":</b> " . $HostnameVal . "<br><b>" . _("Server Start") . ":</b> " . $StartDateVal . "<br><b>" . _("Server End") . ":</b> " . $ExpiresVal;
            $Subject = _("Server") . " " . $ServerID;

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES ('" . $Note . "','" . $Subject . "','$HostnameVal','" . $UserID . "','" . $url . "','"
                . $DateTimeNow . "','" . $ServerID . "','4', NOW() + INTERVAL 1 DAY,'1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Server: <b>" . $ServerID . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script type='text/javascript'>pnotify('Server $ServerID added as task','success');</script>";
    }
}

function addWorkstationToTasksList($UserID, $WorkstationID, $CINameVal, $RelatedCompanyNameVal, $WorkstationTypeID, $ModelNoVal, $RamVal, $CPUVal, $HarddiskspaceVal, $HostnameVal, $StartDateVal, $ExpiresVal, $ActiveVal, $RemovedVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $StartDateVal = $functions->convertFromDanishDateFormat($StartDateVal);
        $ExpiresVal = $functions->convertFromDanishDateFormat($ExpiresVal);

        $ModuleID = 4;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        $Exists = checkIfTaskExists($UserID, $WorkstationID, $ModuleID, $HostnameVal);

        $url = "ci_workstations_view.php?elementid=$WorkstationID";
        $Note = "";

        if ($Exists == "No") {
            $Note = "<b>" . _("Company") . ":</b> " . $RelatedCompanyNameVal . "<br><b>" . _("Workstation Hostname") . ":</b> " . $HostnameVal . "<br><b>" . _("Workstation Start") . ":</b> " . $StartDateVal . "<br><b>" . _("Workstation End") . ":</b> " . $ExpiresVal;
            $Subject = _("Workstation") . " " . $WorkstationID;

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES ('" . $Note . "','" . $Subject . "','$HostnameVal','" . $UserID . "','" . $url . "','"
                . $DateTimeNow . "','" . $WorkstationID . "','4', NOW() + INTERVAL 1 DAY,'1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Workstation: <b>" . $WorkstationID . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script type='text/javascript'>pnotify('Workstation $WorkstationID added as task','success');</script>";
    }
}

function addMobilesubscriptionToTasksList($UserID, $IDVal, $MobilePhoneNumberVal, $SubscriptionFirmVal, $SubscriptionTypeIDVal, $SIMVal, $IMEIVal, $StartDateVal, $ExpiresVal, $ActiveVal, $RemovedVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $StartDateVal = convertToDanishDateFormat($StartDateVal);
        $ExpiresVal = convertToDanishDateFormat($ExpiresVal);

        $ModuleID = 4;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        $Exists = checkIfTaskExists($UserID, $IDVal, $ModuleID, $MobilePhoneNumberVal);

        $url = "ci_mobilesubscriptions_view.php?mobilesubscriptionid=$IDVal";
        $Note = "";
        if ($Exists == "No") {
            $Note = "<b>" . _("Company") . ":</b> " . $SubscriptionFirmVal . "<br><b>" . _("Phone number") . ":</b> " . $MobilePhoneNumberVal . "<br><b>" . _("Start") . ":</b> " . $StartDateVal . "<br><b>" . _("Expires") . ":</b> " . $ExpiresVal;
            $Subject = _("Mobile Subscription") . " " . $IDVal;

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES ('" . $Note . "','" . $Subject . "','$MobilePhoneNumberVal','" . $UserID . "','" . $url . "','"
                . $DateTimeNow . "','" . $IDVal . "','4', NOW() + INTERVAL 1 DAY,'1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Mobile Subscription: <b>" . $IDVal . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script type='text/javascript'>pnotify('Mobile Subscription $IDVal added as task','success');</script>";
    }
}

function addHandheldToTasksList($UserID, $HandheldID, $HandheldAssignedID, $CINameVal, $RelatedCompanyNameVal, $ModelNoVal, $SerialNumberVal, $ProducerIDVal, $HandheldTypeID, $StartDateVal, $ExpiresVal, $ActiveVal, $RemovedVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $StartDateVal = convertToDanishDateFormat($StartDateVal);
        $ExpiresVal = convertToDanishDateFormat($ExpiresVal);

        $ModuleID = 4;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        $Exists = checkIfTaskExists($UserID, $HandheldID, $ModuleID, $CINameVal);

        $url = "ci_handhelds_view.php?handheldid=$HandheldID";
        $Note = "";
        if ($Exists == "No") {
            $Note = "<b>" . _("Company") . ":</b> " . $RelatedCompanyNameVal . "<br><b>" . _("Handheld Name") . ":</b> " . $CINameVal . "<br><b>" . _("Start") . ":</b> " . $StartDateVal . "<br><b>" . _("Expires") . ":</b> " . $ExpiresVal;
            $Subject = _("Handheld") . " " . $HandheldID;

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES ('" . $Note . "','" . $Subject . "','$CINameVal','" . $UserID . "','" . $url . "','"
                . $DateTimeNow . "','" . $HandheldID . "','4', NOW() + INTERVAL 1 DAY,'1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Handheld: <b>" . $HandheldID . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script type='text/javascript'>pnotify('Handheld $HandheldID added as task','success');</script>";
    }
}

function addContractToTasksList($UserID, $IDVal, $ContractNameVal, $RelatedDocIDVal, $companynameVal, $ExpiresVal, $ActiveVal, $RemovedVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $ExpiresVal = convertToDanishDateFormat($ExpiresVal);

        $ModuleID = 4;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        $Exists = checkIfTaskExists($UserID, $IDVal, $ModuleID, $ContractNameVal);

        $url = "ci_contracts_view.php?contractid=$IDVal";
        $Note = "";
        if ($Exists == "No") {
            $Note = "<b>" . _("Company") . ":</b> " . $companynameVal . "<br><b>" . _("Contract Name") . ":</b> " . $ContractNameVal . "<br><b>" . _("Related Document") . ":</b> " . $RelatedDocIDVal . "<br><b>" . _("Expires") . ":</b> " . $ExpiresVal;
            $Subject = _("Contract") . " " . $IDVal;

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES ('" . $Note . "','" . $Subject . "','$ContractNameVal','" . $UserID . "','" . $url . "','"
                . $DateTimeNow . "','" . $IDVal . "','4', NOW() + INTERVAL 1 DAY,'1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Contract: <b>" . $IDVal . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script type='text/javascript'>
            $(document).ready(function(e) {
            pnotify('Contract', 'Contract: <b>" . $IDVal . "</b> added as task');
            });
            </script>";
    }
}

function addCertificateToTasksList($UserID, $CertificateID, $NameVal, $RelatedServerIDVal, $TypeVal, $ExpiresVal, $ActiveVal, $RemovedVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $ExpiresVal = convertToDanishDateFormat($ExpiresVal);

        $ModuleID = 4;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        $Exists = checkIfTaskExists($UserID, $CertificateID, $ModuleID, $NameVal);
        $Name = "Certificate " . $CertificateID;
        $url = "ci_certificates_view.php?certid=$CertificateID";
        $Note = "";
        if ($Exists == "No") {
            $Note = "<b>" . _("Certificate Name") . ":</b> " . $NameVal . "<br><b>" . _("Related Server") . ":</b> " . $RelatedServerIDVal . "<br><b>" . _("Expires") . ":</b> " . $ExpiresVal;
            $Subject = _("Certificate") . " " . $CertificateID;

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES ('" . $Note . "','" . $Subject . "','$NameVal','" . $UserID . "','" . $url . "','"
                . $DateTimeNow . "','" . $CertificateID . "','4', NOW() + INTERVAL 1 DAY,'1');";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Certificate: <b>" . $CertificateID . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script>pnotify('Certificate $CertificateID added to tasklist','success');</script>";
    }
}

function addProjectToTaskslist($ProjectID, $UserID, $ProjectNameVal, $ProjectCompanynameVal, $ProjectManagerVal, $ProjectStartVal, $ProjectDeadlineVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $ProjectStartVal = convertToDanishTimeFormat($ProjectStartVal);
        $ProjectDeadlineForDBInsert = $ProjectDeadlineVal;
        $ProjectDeadlineValTask = convertToDanishTimeFormat($ProjectDeadlineVal);
        $ProjectNameVal = mysqli_real_escape_string($conn, $ProjectNameVal);

        $Name = "Project " . $ProjectID;

        $ModuleID = 6;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        $Exists = checkIfTaskExists($UserID, $ProjectID, $ModuleID, $ProjectNameVal);

        $url = "projects_view.php?projectid=" . $ProjectID;
        $Note = "";

        if ($Exists == "No") {
            $Note = "<b>Project name:</b>$ProjectNameVal<br><b>Company:</b>$ProjectCompanynameVal<br><b>Project deadline:</b>$ProjectDeadlineValTask<br><b>Project Manager:</b>$ProjectManagerVal<br><b>Project Created:</b>$ProjectStartVal";
            $Subject = _("Project");

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) 
                        VALUES ('$Note','$Subject','$ProjectNameVal','$UserID','$url','$DateTimeNow','$ProjectID','$ModuleID','$ProjectDeadlineForDBInsert',1);";

            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Project: <b>" . $ProjectID . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script>pnotify('Project $ProjectID added to tasklist','success');</script>";
    }
}

function addProjectTaskToTaskslist($ProjectTaskID, $UserID, $ProjectTaskNameVal, $ProjectTaskResponsibleVal, $ProjectTaskStartVal, $ProjectTaskDeadlineVal, $ProjectIDVal, $ProjectNameVal)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $ProjectTaskStartVal = convertToDanishTimeFormat($ProjectTaskStartVal);
        $ProjectTaskDeadlineForDBInsert = $ProjectTaskDeadlineVal;
        $ProjectTaskDeadlineVal = convertToDanishTimeFormat($ProjectTaskDeadlineVal);
        $ProjectTaskNameVal = mysqli_real_escape_string($conn, $ProjectTaskNameVal);
        $ProjectNameVal = mysqli_real_escape_string($conn, $ProjectNameVal);
        $Name = "Project Task " . $ProjectTaskID;

        $ModuleID = 13;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        $Exists = checkIfTaskExists($UserID, $ProjectTaskID, $ModuleID, $ProjectTaskNameVal);

        $url = "projects_tasks_view.php?projecttaskid=" . $ProjectTaskID . "&projectid=" . $ProjectIDVal . "&projectname=" . $ProjectNameVal;
        $Note = "";

        if ($Exists == "No") {
            $Note = "<b>Project ID: </b>" . $ProjectIDVal . "<br><b>Project name: </b>" . $ProjectNameVal . "<br><b>Task name: </b>" . $ProjectTaskNameVal . "<br><b>Project Task Created: </b>" . $ProjectTaskStartVal . "<br><b>Task deadline: </b>" . $ProjectTaskDeadlineVal;
            $Subject = _("Project Task");

            $sql = "INSERT INTO taskslist (Note, Subject, Headline, RelatedUserID, GoToLink, DateAdded, RelatedElementID, RelatedElementTypeID, Deadline, Status) VALUES ('" . $Note . "','" . $Subject . "','$ProjectTaskNameVal','" . $UserID . "','" . $url . "','"
                . $DateTimeNow . "','" . $ProjectTaskID . "','13','" . $ProjectTaskDeadlineForDBInsert . "',1);";

            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        } else {
            echo "<script type='text/javascript'>
                $(document).ready(function(e) {
                pnotify('Project task: <b>" . $ProjectTaskID . "</b> allready added as task','danger');
                });
                </script>";
            return;
        }
        echo "<script>pnotify('Project task $ProjectTaskID added to tasklist','success');</script>";
    }
}

function getProjectTotalAmountEstimated($ProjectID)
{

    global $conn;
    global $functions;

    $sql = "SELECT SUM(EstimatedBudget) AS EstimatedBudget 
                FROM project_tasks
                WHERE RelatedProject = $ProjectID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $EstimatedBudget = $row['EstimatedBudget'];
    }
    return $EstimatedBudget;
}

function getProjectTotalAmountSpend($ProjectID)
{

    global $conn;
    global $functions;

    $sql = "SELECT SUM(BudgetSpend) AS BudgetSpend 
                    FROM project_tasks
                    WHERE RelatedProject = $ProjectID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $BudgetSpend = $row['BudgetSpend'];
    }
    return $BudgetSpend;
}

function deleteProjectTask($ProjectTaskID)
{

    global $conn;
    global $functions;

    $sql = "DELETE 
                FROM project_tasks
                WHERE project_tasks.ID = $ProjectTaskID";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getProjectTotalAmountSpendSprint($ProjectID, $RelatedCatagory)
{

    global $conn;
    global $functions;

    $sql = "SELECT SUM(BudgetSpend) AS BudgetSpend
                FROM project_tasks
                WHERE RelatedProject = $ProjectID AND RelatedCatagory = $RelatedCatagory";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $BudgetSpend = $row['BudgetSpend'];
    }
    return $BudgetSpend;
}

function getProjectTotalHoursSpend($ProjectID)
{

    global $conn;
    global $functions;
    $HoursSpend = "";
    $sql = "SELECT ROUND(SUM((time_registrations.TimeRegistered) * (1/60)), 2) AS HoursSpend
                FROM taskslist 
                INNER JOIN time_registrations ON taskslist.ID = time_registrations.RelatedTaskID
                INNER JOIN project_tasks ON taskslist.RelatedElementID = project_tasks.ID
                WHERE taskslist.RelatedElementTypeID = 13 AND project_tasks.RelatedProject = $ProjectID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $HoursSpend = $row['HoursSpend'];
    }
    return $HoursSpend;
}

function getProjectTotalHoursEstimatedOnSpend($ProjectID)
{

    global $conn;
    global $functions;
    $EstimatedHoursOnSpend = "";
    $sql = "SELECT SUM(project_tasks.EstimatedHours) AS EstimatedHoursOnSpend FROM project_tasks WHERE project_tasks.RelatedProject = $ProjectID AND project_tasks.Progress != 0";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $EstimatedHoursOnSpend = $row['EstimatedHoursOnSpend'];
    }
    return $EstimatedHoursOnSpend;
}

function getProjectTotalHoursEstimatedOnSpendTasks($ProjectID)
{

    global $conn;
    global $functions;
    $HoursEstimatedOnSpend = "";
    $sql = "SELECT ROUND(SUM(DISTINCT(project_tasks.EstimatedHours)), 2) AS HoursEstimatedOnSpend
                FROM taskslist 
                INNER JOIN time_registrations ON taskslist.ID = time_registrations.RelatedTaskID
                INNER JOIN project_tasks ON taskslist.RelatedElementID = project_tasks.ID
                WHERE taskslist.RelatedElementTypeID = 13 AND project_tasks.RelatedProject = $ProjectID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $HoursEstimatedOnSpend = $row['HoursEstimatedOnSpend'];
    }
    return $HoursEstimatedOnSpend;
}

function getProjectTotalHoursSpendSprint($ProjectID, $RelatedCatagory)
{

    global $conn;
    global $functions;
    $HoursSpend = "";
    $sql = "SELECT ROUND(SUM((time_registrations.TimeRegistered) * (1/60)), 2) AS HoursSpend
                FROM taskslist 
                INNER JOIN time_registrations ON taskslist.ID = time_registrations.RelatedTaskID
                INNER JOIN project_tasks ON taskslist.RelatedElementID = project_tasks.ID
                WHERE taskslist.RelatedElementTypeID = 13 AND project_tasks.RelatedProject = $ProjectID AND project_tasks.RelatedCatagory = $RelatedCatagory";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $HoursSpend = $row['HoursSpend'];
    }
    return $HoursSpend;
}

function getProjectTotalHoursEstimated($ProjectID)
{

    global $conn;
    global $functions;
    $TotalHoursEstimated = "";
    $sql = "SELECT ROUND(SUM(project_tasks.EstimatedHours), 2) AS TotalHoursEstimated
                FROM project_tasks 
                WHERE project_tasks.RelatedProject = $ProjectID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $TotalHoursEstimated = $row['TotalHoursEstimated'];
    }
    return $TotalHoursEstimated;
}

function getProjectTotalHoursEstimatedSprint($ProjectID, $RelatedCatagory)
{

    global $conn;
    global $functions;
    $TotalHoursEstimated = "";
    $sql = "SELECT ROUND(SUM(project_tasks.EstimatedHours), 2) AS TotalHoursEstimated
                FROM project_tasks 
                WHERE project_tasks.RelatedProject = $ProjectID AND project_tasks.RelatedCatagory = $RelatedCatagory";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $TotalHoursEstimated = $row['TotalHoursEstimated'];
    }
    return $TotalHoursEstimated;
}

function updateProjectTaskStatusToCompleted($elementid, $Status, $Progress)
{

    global $conn;
    global $functions;
    if ($Status == 5) {
        $sql = "UPDATE project_tasks SET STatus = $Status
            WHERE project_tasks.ID = $elementid";
    }
    if ($Status == 7) {
        $sql = "UPDATE project_tasks SET STatus = $Status, Progress = $Progress
                WHERE project_tasks.ID = $elementid";
    }
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getProjectTaskTotalHoursSpend($ProjectTaskID)
{

    global $conn;
    global $functions;
    $HoursSpend = "";
    $sql = "SELECT ROUND(SUM((time_registrations.TimeRegistered) * (1/60)), 2) AS HoursSpend
                FROM taskslist 
                INNER JOIN time_registrations ON taskslist.ID = time_registrations.RelatedTaskID
                INNER JOIN project_tasks ON taskslist.RelatedElementID = project_tasks.ID
                WHERE taskslist.RelatedElementTypeID = 13 AND project_tasks.ID = $ProjectTaskID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $HoursSpend = $row['HoursSpend'];
        if (empty($HoursSpend)) {
            $HoursSpend = 0;
        }
    }
    return $HoursSpend;
}

function addtowatchlist($TicketIDVal, $UserID, $Subject)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "incidents_view.php?elementid=" . $TicketIDVal;
        $sql = "INSERT INTO watchlist (Name, URL, RelatedUserID, RelatedModuleID, ElementName, ElementID) VALUES ('$Subject','$url','$UserID','1','Incident',$TicketIDVal);";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('Incident $TicketIDVal added to your watchlist','success');</script>";
    }
}

function addtofavoritelist($TicketIDVal, $UserID)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "incidents_view.php?elementid=" . $TicketIDVal;
        $Name = "Incident " . $TicketIDVal;
        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','1','Incident');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addRequestToWatchlist($RequestIDVal, $UserID, $Subject)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "requests_view.php?elementid=" . $RequestIDVal;
        $sql = "INSERT INTO watchlist (Name, URL, RelatedUserID, RelatedModuleID, ElementName, ElementID) VALUES ('$Subject','$url','$UserID','7','Request',$RequestIDVal);";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('Request $RequestIDVal added to your watchlist','success');</script>";
    }
}

function addRequestToFavoritelist($RequestIDVal, $UserID)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "requests_view.php?elementid=" . $RequestIDVal;
        $Name = "Request " . $RequestIDVal;

        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','7','Request');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addChangeToWatchlist($ChangeIDVal, $UserID, $Subject)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "changes_view.php?elementid=" . $ChangeIDVal;
        $sql = "INSERT INTO watchlist (Name, URL, RelatedUserID, RelatedModuleID, ElementName, ElementID) VALUES ('$Subject','$url','$UserID','2','Change',$ChangeIDVal);";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('Change $ChangeIDVal added to your watchlist','success');</script>";
    }
}

function addchangetofavoritelist($ChangeIDVal, $UserID)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "changes_view.php?elementid=" . $ChangeIDVal;
        $Name = "Change " . $ChangeIDVal;
        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','2','Change');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addProblemToWatchlist($ProblemIDVal, $UserID, $Subject)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "problems_view.php?elementid=" . $ProblemIDVal;
        $sql = "INSERT INTO watchlist (Name, URL, RelatedUserID, RelatedModuleID, ElementName, ElementID) VALUES ('$Subject','$url','$UserID','5','Problem',$ProblemIDVal);";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('Problem $ProblemIDVal added to your watchlist','success');</script>";
    }
}

function addproblemtofavoritelist($ProblemIDVal, $UserID)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "problems_view.php?elementid=" . $ProblemIDVal;
        $Name = "Problem " . $ProblemIDVal;
        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','5','Problem');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addServerToFavoriteList($IDVal, $UserID)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "ci_servers_view.php?elementid=" . $IDVal;
        $Name = _("Server") . " " . $IDVal;
        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ( '" . $Name . "', '" . $url . "', '" . $UserID . "', '4', 'Server');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addWorkstationToFavoriteList($IDVal, $UserID)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "ci_workstations_view.php?elementid=" . $IDVal;
        $Name = _("Workstation") . " " . $IDVal;
        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ( '" . $Name . "', '" . $url . "', '" . $UserID . "', '4', 'Workstation');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addHandheldToFavoriteList($IDVal, $UserID)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "ci_handhelds_view.php?handheldid=" . $IDVal;
        $Name = _("Handheld Device") . " " . $IDVal;
        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','4','Handheld');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addMobileSubscriptionToFavoriteList($IDVal, $UserID)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "ci_mobilesubscriptions_view.php?mobilesubscriptionid=" . $IDVal;
        $RelatedTypeName = _("Mobile Subscription");
        $Name = _("Mobile Subscription") . " " . $IDVal;
        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','4','$RelatedTypeName');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addContractToFavoriteList($IDVal, $UserID)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "ci_contracts_view.php?contractid=" . $IDVal;
        $RelatedTypeName = _("Contract");
        $Name = _("Contract") . " " . $IDVal;
        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','4','$RelatedTypeName');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addCertificateToFavoriteList($IDVal, $UserID)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "ci_certificates_view.php?certid=" . $IDVal;
        $RelatedTypeName = _("Certificate");
        $Name = _("Certificate") . " " . $IDVal;
        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','4','$RelatedTypeName');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addProjectToFavoriteList($IDVal, $UserID, $ProjectName)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $result = "True";
        $url = "projects_view.php?projectid=" . $IDVal;
        $Name = $Name = _("Project") . " " . $IDVal;
        $UrlResult = "";
        $sqlcheck = "SELECT URL AS Url FROM favorites WHERE URL = '" . $url . "'";
        $resultcheck = mysqli_query($conn, $sqlcheck) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($resultcheck)) {
            $UrlResult = $row['Url'];
        }
        if (empty($UrlResult)) {

            $sqlinsert = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','6','Project');";

            mysqli_query($conn, $sqlinsert) or die('Query fail: ' . mysqli_error($conn));
            $result = "True";
        } else {
            $result = "False";
        }
        return $result;
    }
}

function addDocumentToFavoriteList($DocID, $UserID, $DocName)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $result = "True";
        $url = "knowledge_view.php?docid=" . $DocID;
        $Name = $DocName;
        $UrlResult = "";
        $sqlcheck = "SELECT URL AS Url FROM favorites WHERE URL = '" . $url . "'";
        $resultcheck = mysqli_query($conn, $sqlcheck) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($resultcheck)) {
            $UrlResult = $row['Url'];
        }
        if (empty($UrlResult)) {

            $sqlinsert = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','3','Document');";

            mysqli_query($conn, $sqlinsert) or die('Query fail: ' . mysqli_error($conn));
            $result = "True";
        } else {
            $result = "False";
        }
        return $result;
    }
}

function addProjectToWatchList($IDVal, $UserID, $ProjectName)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $result = "True";
        $url = "projects_view.php?projectid=" . $IDVal;
        $UrlResult = "";
        $sqlcheck = "SELECT URL AS Url FROM watchlist WHERE URL = '" . $url . "'";
        $resultcheck = mysqli_query($conn, $sqlcheck) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($resultcheck)) {
            $UrlResult = $row['Url'];
        }
        if (empty($UrlResult)) {
            $sqlinsert = "INSERT INTO watchlist (Name, URL, RelatedUserID, RelatedModuleID, ElementName, ElementID) VALUES ('$ProjectName','" . $url . "','" . $UserID . "','6','Project',$IDVal);";

            mysqli_query($conn, $sqlinsert) or die('Query fail: ' . mysqli_error($conn));
            $result = "True";
        } else {
            $result = "False";
        }
        return $result;
    }
}

function addProjectTaskToFavoriteList($IDVal, $UserID, $ProjectNameVal, $ProjectID)
{

    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $url = "projects_tasks_view.php?projectid=$ProjectID&projecttaskid=$IDVal&projectname=$ProjectNameVal";
        $Name = "Project Task " . $IDVal;
        $sql = "INSERT INTO favorites (Name, URL, RelatedUserID, RelatedModuleID, ElementName) VALUES ('" . $Name . "','" . $url . "','" . $UserID . "','6','Project Task');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>pnotify('$Name added as favorite','success');</script>";
    }
}

function addasnewproblem($TicketIDVal, $UserID, $TicketSubjectVal)
{
    global $conn;
    global $functions;

    $ProblemID = 0;
    $TicketSubjectVal = mysqli_real_escape_string($conn, $TicketSubjectVal);
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        //Check if allready exists
        $sqlcheckifexists = "SELECT ID FROM problemstickets WHERE TicketID = '" . $TicketIDVal . "';";
        $result = mysqli_query($conn, $sqlcheckifexists) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $ProblemID = $row['ID'];
            echo $ProblemID;
        }

        if ($ProblemID > 0) {
            echo "<script>window.location.href = 'incidents_view.php?elementid=" . $TicketIDVal . "';</script>";
        } else {
            $DateTimeNow = date('Y-m-d H:i:s');
            $url = "incidents_view.php?elementid=" . $TicketIDVal;
            $Name = "Problem created from incident " . $TicketIDVal;
            $sqlcreatenew = "INSERT INTO problems (Name, Description, RelatedRespID, Created_Date, RelatedStatusID) VALUES ('" . $Name . "','" . $TicketSubjectVal . "','" . $UserID . "','" . $DateTimeNow . "','1');";
            $sqlgetproblem = "SELECT ID FROM problems WHERE Created_Date = '" . $DateTimeNow . "'";
            mysqli_query($conn, $sqlcreatenew) or die('Query fail: ' . mysqli_error($conn));

            $result = mysqli_query($conn, $sqlgetproblem) or die('Query fail: ' . mysqli_error($conn));
            while ($row = mysqli_fetch_array($result)) {
                $ProblemID = $row['ID'];
            }

            $sqlCreateRelatedTicket = "INSERT INTO problemstickets (ProblemID, TicketID) VALUES ('" . $ProblemID . "','" . $TicketIDVal . "');";
            mysqli_query($conn, $sqlCreateRelatedTicket) or die('Query fail: ' . mysqli_error($conn));
            echo "<script>window.location.href = 'incidents_view.php?elementid=" . $TicketIDVal . "';</script>";
        }
    }
}

function createnewtask($Subject, $Note, $TicketDeadlineResolveVal)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $UserID = $_SESSION["id"];
        $DateTimeNow = date('Y-m-d H:i:s');
        $tempdate = strtotime($TicketDeadlineResolveVal);
        $TicketDeadlineResolveVal = date('Y-m-d H:i:s');
        $Subject = mysqli_real_escape_string($conn, $Subject);
        $Note = mysqli_real_escape_string($conn, $Note);

        $sql = "INSERT INTO taskslist (Note, Subject, RelatedUserID, DateAdded, Deadline, RelatedTicketID, todo) VALUES ('" . $Note . "','" . $Subject . "','" . $UserID . "','"
            . $DateTimeNow . "','" . $TicketDeadlineResolveVal . "','yes');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        echo "<script>window.location.href = 'index.php';</script>";
    }
}

function updateTaskList($Subject, $Note, $Deadline, $Taskid)
{
    global $conn;
    global $functions;
    $UserID = $_SESSION["id"];
    $Subject = mysqli_real_escape_string($conn, $Subject);
    $Note = mysqli_real_escape_string($conn, $Note);

    $sql = "UPDATE taskslist SET Note='" . $Note . "', Subject='" . $Subject . "', Deadline='" . $Deadline . "' WHERE taskslist.ID='" . $Taskid . "';";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    echo "<script>window.location.href = 'index.php';</script>";
}

function createNewProjectActivity($Content, $ProjectTaskID, $UserSessionID)
{
    global $conn;
    global $functions;

    $UserSessionID = $_SESSION["id"];
    $Content = $_POST["Content"];

    $sql = "INSERT INTO projects_tasks_conversations (RelatedProjectTaskID, Message, RelatedUserID) VALUES (?, ?, ?);";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isi", $ProjectTaskID, $Content, $UserSessionID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function getUserProfilePicture($UserID)
{
    global $conn;
    global $functions;
    $UserID = $UserID;
    $sql = "SELECT ProfilePicture from users WHERE ID='$UserID';";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $PictureName = $row['ProfilePicture'];
    }
    return $PictureName;
}

function getTicketsOverdue()
{
    global $conn;
    global $functions;
    $UserID = $_SESSION["id"];
    $DateTimeNow = date('Y-m-d H:i:s');
    $OverdueTickets = array();

    $sql = "SELECT tickets.id AS ID, ticketstatuscodes.StatusName, ticketslatimeline.SLAViolationDate, CONCAT(users.Firstname,' ',users.Lastname) AS Responsible,
                teams.Teamname
                FROM tickets 
                LEFT JOIN ticketslatimeline ON tickets.id = ticketslatimeline.RelatedTicketID
                LEFT JOIN ticketstatuscodes ON tickets.Status = ticketstatuscodes.ID
                LEFT JOIN users ON tickets.Responsible = users.ID
                LEFT JOIN teams ON tickets.ResponsibleTeam = teams.ID
                WHERE ticketslatimeline.SLAViolated='yes' AND tickets.Status NOT BETWEEN 6 AND 8 AND tickets.Status=ticketslatimeline.RelatedStatusCodeID;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        if (!empty($row['ID'])) {
            $OverdueTickets[] = array(
                'TicketID' => $row['ID'], 'Status' => $row['StatusName'], 'SLAViolationDate' => $row['SLAViolationDate'], 'Responsible' => $row['Responsible'],
                'Teamname' => $row['Teamname']
            );
        }
    }
    mysqli_free_result($result);
    return $OverdueTickets;
}

function getTicketsSoonOverdue()
{
    global $conn;
    global $functions;
    $UserID = $_SESSION["id"];
    $DateTimeNow = date('Y-m-d H:i:s');
    $SoonOverdueTickets = array();

    $sqlSoon = "SELECT tickets.id AS ID, ticketstatuscodes.StatusName, ticketslatimeline.SLAViolationDate, CONCAT(users.Firstname,' ',users.Lastname) AS Responsible,
                teams.Teamname
                FROM tickets 
                LEFT JOIN ticketslatimeline ON tickets.id = ticketslatimeline.RelatedTicketID
                LEFT JOIN ticketstatuscodes ON tickets.Status = ticketstatuscodes.ID
                LEFT JOIN users ON tickets.Responsible = users.ID
                LEFT JOIN teams ON tickets.ResponsibleTeam = teams.ID
                WHERE ticketslatimeline.SLAViolatedSoon='yes' AND tickets.Status!=6 AND tickets.Status!=7 AND tickets.Status!=8 AND tickets.Status=ticketslatimeline.RelatedStatusCodeID;";
    $resultSoon = mysqli_query($conn, $sqlSoon) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($resultSoon)) {
        if (!empty($row['ID'])) {
            $SoonOverdueTickets[] = array(
                'TicketID' => $row["ID"], 'Status' => $row['StatusName'], 'SLAViolationDate' => $row['SLAViolationDate'], 'Responsible' => $row['Responsible'],
                'Teamname' => $row['Teamname']
            );
        }
    }
    mysqli_free_result($resultSoon);
    return $SoonOverdueTickets;
}

function createNewUser($UserFirstname, $UserLastname, $Email, $Username, $hashed_password, $RelatedCompanyID, $JobTitel, $RelatedUserTypeID, $RelatedManagerID, $StartDate, $NewPin)
{
    global $conn;
    global $functions;
    $NewUserCreatedID = "";

    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $UserFirstname = mysqli_real_escape_string($conn, $UserFirstname);
        $UserLastname = mysqli_real_escape_string($conn, $UserLastname);
        $StartDate = $functions->convertFromDanishDateFormat($StartDate);

        if ($RelatedManagerID == "") {
            $RelatedManagerID = "NULL";
        }

        $sql = "INSERT INTO users (Firstname, Lastname, Username, Password, Email, CompanyID, RelatedUserTypeID, JobTitel, RelatedManager, StartDate, pin) VALUES 
                    ('$UserFirstname','$UserLastname','$Username','$hashed_password','$Email',$RelatedCompanyID,$RelatedUserTypeID,'$JobTitel',$RelatedManagerID,'$StartDate','$NewPin');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        $last_id = $conn->insert_id;
        return $last_id;
    }
}

function deactivateUser($ID)
{
    global $conn;
    global $functions;
    $UserID = $_SESSION["id"];

    $sql = "UPDATE users SET InactiveDate=NOW(), Active='0' WHERE users.id='" . $ID . "';";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteProjectTaskFromTaskslist($ProjectTaskID)
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM taskslist WHERE RelatedElementID = $ProjectTaskID AND RelatedElementTypeID = 13;";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getMailCodeForModule($ModuleID)
{
    global $conn;
    global $functions;

    $sql = "SELECT MailCode
            FROM modules
            WHERE ID ='$ModuleID';";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $MailCode = $row['MailCode'];
    }
    return $MailCode;
}

function updateUserInformation($UserID, $Firstname, $Lastname, $Email, $Phone, $Birthday, $Linkedin)
{
    global $conn;
    global $functions;

    $Birthday = $functions->convertFromDanishDateFormat($Birthday);
    $sql = "UPDATE users SET Firstname='$Firstname', Lastname='$Lastname', Email='$Email', Phone='$Phone', Birthday='$Birthday', Linkedin='$Linkedin' WHERE ID = $UserID";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createWorkFlow($ElementID, $WorkFlowID, $UserID, $RedirectPage, $ModuleID)
{
    global $conn;
    global $functions;
    $NewWorkFlowID = "";

    //Prevent creating a new workflow if it already exists for this ticket
    $sql1 = "SELECT workflows.ID AS WorkFlowID, RelatedWorkFlowID
            FROM workflows
            WHERE workflows.RelatedElementID = $ElementID AND workflows.RelatedWorkFlowID = $WorkFlowID";

    $result1 = mysqli_query($conn, $sql1) or $functions->errorlog(mysqli_error($conn), "createWorkFlow");
    while ($row = mysqli_fetch_array($result1)) {
        if (!empty($row['WorkFlowID'])) {
            $errorMessage = "Workflow already exists for this element. Please remove the existing one first (Be aware - if you have deleted all tasks - the old workflow is still attached!)";
            $errorMessageForLog = "Workflow already exists for ITSM Type: $ModuleID ElementID: $ElementID.";
            $functions->errorlog($errorMessageForLog, "createWorkFlow");
            throw new Exception($errorMessage);
        };
    }

    //Create new WorkFlow with relation to the ticket
    $sql2 = "INSERT INTO workflows (RelatedElementID, RelatedWorkFlowID, RelatedElementTypeID) VALUES (?, ?, ?)";
    $stmt2 = mysqli_prepare($conn, $sql2);
    if ($stmt2 === false) {
        $errorMessage = "Failed to prepare the SQL statement for inserting into workflows table.";
        $functions->errorlog(mysqli_error($conn), "createWorkFlow");
        throw new Exception($errorMessage);
    }

    mysqli_stmt_bind_param($stmt2, "iii", $ElementID, $WorkFlowID, $ModuleID);
    if (!mysqli_stmt_execute($stmt2)) {
        $errorMessage = "Failed to execute the SQL statement for inserting into workflows table.";
        $functions->errorlog(mysqli_stmt_error($stmt2), "createWorkFlow");
        throw new Exception($errorMessage);
    }

    $NewWorkFlowID = $conn->insert_id;

    //Get all workflow steps to create them from the template.
    $sql3 = "SELECT workflowsteps_template.StepName AS StepName, workflowsteps_template.Description AS Description, workflowsteps_template.StepOrder AS StepOrder,
            users.ID AS UserID
            FROM workflowsteps_template
            INNER JOIN users ON workflowsteps_template.RelatedUserID = users.ID
            WHERE workflowsteps_template.RelatedWorkFlowID = $WorkFlowID
            ORDER BY workflowsteps_template.StepOrder ASC";

    $result3 = mysqli_query($conn, $sql3) or $functions->errorlog(mysqli_error($conn), "createWorkFlow");
    while ($row = mysqli_fetch_array($result3)) {
        $StepOrder = $row['StepOrder'];
        $StepName = $row['StepName'];
        $Description = $row['Description'];
        $UserID = $row['UserID'];
        $RedirectPageNew = mysqli_real_escape_string($conn, $RedirectPage);

        $sql4 = "INSERT INTO workflowsteps (RelatedWorkFlowID, StepOrder, StepName, Description, RelatedStatusID, RelatedUserID) 
                VALUES ('$NewWorkFlowID','$StepOrder','$StepName','$Description','1',$UserID);";

        mysqli_query($conn, $sql4) or $functions->errorlog(mysqli_error($conn), "createWorkFlow");
        $WFTID = $conn->insert_id;
        $ModuleName = "<i class=\"fa-solid fa-thumbtack\" title=\"Workflow Task\"></i> " . getModuleNameFromModuleID($ModuleID);

        $NewTaskID = addITSMWorkFlowToTasksList($ElementID, $ModuleID, $UserID, $StepName, $RedirectPageNew, $ModuleName, $StepOrder, $Description, $WFTID);
        updateWorkFlowStepWithTaskID($WFTID, $NewTaskID);
    }

    return $NewWorkFlowID;
}


function createWorkFlowForProblem($ProblemID, $WorkFlowID, $UserID, $TicketSubjectVal, $RedirectPage, $CompanyNameVal, $CustomerNameVal, $CreatedDateVal, $DeadlineResolveVal)
{
    global $conn;
    global $functions;
    $ModuleTypeID = 5;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        //Prevent to create new workflow if allready exists for this ticket
        $sql = "SELECT workflows.ID AS WorkFlowID, RelatedWorkFlowID
                    FROM workflows
                    WHERE workflows.RelatedElementID = $ProblemID AND workflows.RelatedWorkFlowID = $WorkFlowID";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            if (!empty($row['WorkFlowID'])) {
                echo "Workflow allready exists for this ticket, please delete the existing first";
                exit;
            };
        }

        //Create new WorkFlow with relation to ticket
        $sql = "INSERT INTO workflows (RelatedElementID, RelatedWorkFlowID, RelatedElementTypeID) VALUES ( $ProblemID, $WorkFlowID, $ModuleTypeID)";
        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        //Get newly created WorkFlow ID so we can relate workflow id to workflow steps
        $sql = "SELECT workflows.ID AS WorkFlowID
            FROM workflows
            WHERE workflows.RelatedElementID = $ProblemID";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $NewWorkFlowID = $row['WorkFlowID'];
        }

        //Get all workflow steps so we can create them from template.
        $sql = "SELECT workflowsteps_template.StepName AS StepName, workflowsteps_template.Description AS Description, workflowsteps_template.StepOrder AS StepOrder,
                    users.ID AS UserID
                    FROM workflowsteps_template
                    INNER JOIN users ON workflowsteps_template.RelatedUserID = users.ID
                    WHERE workflowsteps_template.RelatedWorkFlowID = $WorkFlowID
                    ORDER BY workflowsteps_template.StepOrder ASC";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $StepOrder = $row['StepOrder'];
            $StepName = $row['StepName'];
            $Description = $row['Description'];
            $UserID = $row['UserID'];
            $RedirectPageNew = $RedirectPage . "#WorkFlowTab";

            $sql2 = "INSERT INTO workflowsteps (RelatedWorkFlowID, StepOrder, StepName, Description, RelatedStatusID, RelatedUserID) VALUES ('$NewWorkFlowID','$StepOrder','$StepName','$Description','1',$UserID);";

            $result2 = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));
            $NewlyCreatedStepID = getNewlyCreatedStepID($NewWorkFlowID, $UserID);
            addworkflowtotaskslist($ProblemID, $ModuleTypeID, $UserID, $StepName, $RedirectPageNew, $CompanyNameVal, $CustomerNameVal, $CreatedDateVal, $DeadlineResolveVal, "wft: Problem", $StepOrder, $TicketSubjectVal);
            $NewlyCreatedTaskID = getNewlyCreatedTaskID($UserID);
            updateWorkFlowStepWithTaskID($NewlyCreatedStepID, $NewlyCreatedTaskID);
        }
    }
}

function createWorkFlowForRequest($RequestID, $WorkFlowID, $UserID, $RequestSubjectVal, $RedirectPage, $CompanyNameVal, $CustomerNameVal, $CreatedDateVal, $DeadlineResolveVal)
{

    global $conn;
    global $functions;
    $ModuleTypeID = 7;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        //Prevent to create new workflow if allready exists for this ticket
        $sql = "SELECT workflows.ID AS WorkFlowID, RelatedWorkFlowID
                    FROM workflows
                    WHERE workflows.RelatedElementID = $RequestID AND workflows.RelatedWorkFlowID = $WorkFlowID";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            if (!empty($row['WorkFlowID'])) {
                echo "Workflow allready exists for this ticket, please delete the existing first";
                exit;
            };
        }

        //Create new WorkFlow with relation to ticket
        $sql = "INSERT INTO workflows (RelatedElementID, RelatedWorkFlowID, RelatedElementTypeID) VALUES ( $RequestID, $WorkFlowID, $ModuleTypeID)";
        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        //Get newly created WorkFlow ID so we can relate workflow id to workflow steps
        $sql = "SELECT workflows.ID AS WorkFlowID
            FROM workflows
            WHERE workflows.RelatedElementID = $RequestID";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $NewWorkFlowID = $row['WorkFlowID'];
        }

        //Get all workflow steps so we can create them from template.
        $sql = "SELECT workflowsteps_template.StepName AS StepName, workflowsteps_template.Description AS Description, workflowsteps_template.StepOrder AS StepOrder,
                    users.ID AS UserID
                    FROM workflowsteps_template
                    INNER JOIN users ON workflowsteps_template.RelatedUserID = users.ID
                    WHERE workflowsteps_template.RelatedWorkFlowID = $WorkFlowID
                    ORDER BY workflowsteps_template.StepOrder ASC";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $StepOrder = $row['StepOrder'];
            $StepName = $row['StepName'];
            $Description = $row['Description'];
            $UserID = $row['UserID'];
            $RedirectPageNew = $RedirectPage . "#WorkFlowTab";

            $sql2 = "INSERT INTO workflowsteps (RelatedWorkFlowID, StepOrder, StepName, Description, RelatedStatusID, RelatedUserID) VALUES ('$NewWorkFlowID','$StepOrder','$StepName','$Description','1',$UserID);";

            $result2 = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));
            $NewlyCreatedStepID = getNewlyCreatedStepID($NewWorkFlowID, $UserID);
            addworkflowtotaskslist($RequestID, $ModuleTypeID, $UserID, $StepName, $RedirectPageNew, $CompanyNameVal, $CustomerNameVal, $CreatedDateVal, $DeadlineResolveVal, "wft: Request", $StepOrder, $RequestSubjectVal);
            $NewlyCreatedTaskID = getNewlyCreatedTaskID($UserID);
            updateWorkFlowStepWithTaskID($NewlyCreatedStepID, $NewlyCreatedTaskID);
        }
    }
}

function createWorkFlowForChange($ChangeID, $WorkFlowID, $UserID, $SubjectVal, $RedirectPage, $CompanyNameVal, $CustomerNameVal, $CreatedDateVal, $DeadlineResolveVal)
{
    global $conn;
    global $functions;
    $ModuleTypeID = 2;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                        window.alert('DB is full: $FreeSpace MB left');
                    </script>");
    } else {
        //Prevent to create new workflow if allready exists for this ticket
        $sql = "SELECT workflows.ID AS WorkFlowID, RelatedWorkFlowID
                    FROM workflows
                    WHERE workflows.RelatedElementID = $ChangeID AND workflows.RelatedWorkFlowID = $WorkFlowID";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            if (!empty($row['WorkFlowID'])) {
                echo "Workflow allready exists for this request, please delete the existing first";
                exit;
            };
        }

        //Create new WorkFlow with relation to request
        $sql = "INSERT INTO workflows (RelatedElementID, RelatedWorkFlowID, RelatedElementTypeID) VALUES ($ChangeID, $WorkFlowID, $ModuleTypeID)";
        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        //Get newly created WorkFlow ID so we can relate workflow id to workflow steps
        $sql = "SELECT workflows.ID AS WorkFlowID
                    FROM workflows
                    WHERE workflows.RelatedElementID = $ChangeID";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $NewWorkFlowID = $row['WorkFlowID'];
        }

        //Get all workflow steps so we can create them from template.
        $sql = "SELECT workflowsteps_template.StepName AS StepName, workflowsteps_template.Description AS Description, workflowsteps_template.StepOrder AS StepOrder,
                    users.ID AS UserID
                    FROM workflowsteps_template
                    INNER JOIN users ON workflowsteps_template.RelatedUserID = users.ID
                    WHERE workflowsteps_template.RelatedWorkFlowID = '$WorkFlowID'
                    ORDER BY workflowsteps_template.StepOrder ASC";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $StepOrder = $row['StepOrder'];
            $StepName = $row['StepName'];
            $Description = $row['Description'];
            $UserID = $row['UserID'];
            $RedirectPageNew = $RedirectPage . "#WorkFlowTab";

            $sql2 = "INSERT INTO workflowsteps (RelatedWorkFlowID, StepOrder, StepName, Description, RelatedStatusID, RelatedUserID) VALUES ('$NewWorkFlowID','$StepOrder','$StepName','$Description','1',$UserID);";
            $result2 = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));
            $NewlyCreatedStepID = getNewlyCreatedStepID($NewWorkFlowID, $UserID);

            addworkflowtotaskslist($ChangeID, $ModuleTypeID, $UserID, $StepName, $RedirectPageNew, $CompanyNameVal, $CustomerNameVal, $CreatedDateVal, $DeadlineResolveVal, "wft: Change", $StepOrder, $SubjectVal);
            $NewlyCreatedTaskID = getNewlyCreatedTaskID($UserID);
            updateWorkFlowStepWithTaskID($NewlyCreatedStepID, $NewlyCreatedTaskID);
        }
    }
}

function deleteWorkFlowForTicket($TicketID)
{
    global $conn;
    global $functions;

    //Prevent to create new workflow if allready exists for this ticket
    $sql = "DELETE FROM workflows WHERE RelatedElementID = $TicketID";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $sql = "DELETE FROM taskslist WHERE RelatedElementID = $TicketID AND RelatedElementTypeID = 1";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteWorkFlowForProblem($ProblemID)
{
    global $conn;
    global $functions;

    //Prevent to create new workflow if allready exists for this ticket
    $sql = "DELETE FROM workflows WHERE RelatedElementID = $ProblemID";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $sql = "DELETE FROM taskslist WHERE RelatedElementID = $ProblemID AND RelatedElementTypeID = 5";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteWorkFlowForRequest($RequestID)
{
    global $conn;
    global $functions;

    //Prevent to create new workflow if allready exists for this ticket
    $sql = "DELETE FROM workflows WHERE RelatedElementID = $RequestID";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $sql = "DELETE FROM taskslist WHERE RelatedElementID = $RequestID AND RelatedElementTypeID = 7";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteWorkFlowForChange($ChangeID)
{
    global $conn;
    global $functions;

    //Prevent to create new workflow if allready exists for this ticket
    $sql = "DELETE FROM workflows WHERE RelatedElementID = $ChangeID";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $sql = "DELETE FROM taskslist WHERE RelatedElementID = $ChangeID AND RelatedElementTypeID = 2";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createNewProblemEntry($UserID, $ProblemName, $ProblemDescription)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $ProblemName = mysqli_real_escape_string($conn, $ProblemName);
        $ProblemDescription = mysqli_real_escape_string($conn, $ProblemDescription);

        $sql = "INSERT INTO problems (Name, Description, RelatedRespID, Created_Date, RelatedStatusID) VALUES ('" . $ProblemName . "','" . $ProblemDescription . "','" . $UserID . "','" . $DateTimeNow . "','1');";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function changeProblemEntry($UserID, $ProblemID, $ProblemName, $ProblemDescription, $ProblemResp, $ProblemStatus)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');
    $ProblemName = mysqli_real_escape_string($conn, $ProblemName);
    $ProblemDescription = mysqli_real_escape_string($conn, $ProblemDescription);

    $sql = "UPDATE problems SET Name='" . $ProblemName . "', Description='" . $ProblemDescription . "', RelatedRespID='" . $ProblemResp . "', RelatedStatusID='" . $ProblemStatus . "'
                WHERE problems.ID = $ProblemID";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function changeModuleEntry($ModuleId, $ModuleName, $LicenseKey, $ModuleActive, $Description, $HelpText)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');

    $sql = "UPDATE modules SET ModuleName='" . $ModuleName . "', LicenseKey='" . $LicenseKey . "', ModuleActive='" . $ModuleActive . "', Description='" . $Description . "', HelpText='" . $HelpText . "'
                WHERE modules.ID = $ModuleId";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createNewServerEntry($CIID, $Model, $Ram, $CPU, $Description, $Harddisk, $Domain, $FQDN, $Producer, $OS, $Type)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {

        $Model = mysqli_real_escape_string($conn, $Model);
        $FQDN = mysqli_real_escape_string($conn, $FQDN);

        $sql = "INSERT INTO ci_servers(RelatedCI, ModelNo, Ram, CPU, Description, Harddiskspace, Domain, FQDN, RelatedProducerID, RelatedOSID, RelatedTypeID) 
            VALUES ($CIID,'$Model',$Ram,'$CPU','$Description',$Harddisk,'$Domain','$FQDN',$Producer,$OS,$Type)";

        if (mysqli_query($conn, $sql)) {
            // Obtain last inserted id
            $last_id = mysqli_insert_id($conn);
        } else {
            echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
        }

        //$last_id = mysqli_insert_id($conn);
    }
    return $last_id;
}

function createNewHandheldEntry($CIID, $Model, $SerialNumber, $Producer, $Type)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                window.alert('DB is full: $FreeSpace MB left');
                </script>");
    } else {

        $Model = mysqli_real_escape_string($conn, $Model);
        $SerialNumber = mysqli_real_escape_string($conn, $SerialNumber);

        $sql = "INSERT INTO ci_handhelds(RelatedCI, RelatedTypeID, ModelNo, SerialNumber, RelatedProducerID) 
                VALUES ($CIID,$Type,'$Model','$SerialNumber',$Producer)";

        if (mysqli_query($conn, $sql)) {
            // Obtain last inserted id
            $last_id = mysqli_insert_id($conn);
        } else {
            echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
        }
    }
    //$last_id = mysqli_insert_id($conn);
    return $last_id;
}

function createNewContractEntry($UserID, $ContractName, $RelatedDoc, $Company, $Expires)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $Expires = $functions->convertFromDanishDateFormat($Expires);
        if ($RelatedDoc == "-1") {
            $RelatedDoc = "NULL";
        }

        if ($Company == "-1") {
            $Company = "NULL";
        }

        $ContractName = mysqli_real_escape_string($conn, $ContractName);

        $sql = "INSERT INTO ci_contracts(ContractName, RelatedDocID, RelatedCompanyID, Expires, Active, Removed) 
                    VALUES ('$ContractName',$RelatedDoc,$Company,'$Expires',1,0)";

        if (mysqli_query($conn, $sql)) {
            // Obtain last inserted id
            $last_id = mysqli_insert_id($conn);
        } else {
            echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
        }
    }
    return $last_id;
}

function createNewCIBaseEntry($Name, $RelatedClassID, $RelatedCompanyID, $RelatedUserID, $StartDate, $Expires, $UserID)
{
    global $conn;
    global $functions;
    $last_id = "";

    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateTimeNow = date('Y-m-d H:i:s');
        $Expires = convertFromDanishTimeFormat($Expires);
        $StartDate = convertFromDanishTimeFormat($StartDate);

        $Name = mysqli_real_escape_string($conn, $Name);

        //Create New CI in cis table
        $sql = "INSERT INTO cis(Name, RelatedClassID, RelatedCompanyID, RelatedUserID, StartDate, Expires, CreatedBy)
                    VALUES ('$Name', $RelatedClassID, $RelatedCompanyID, $RelatedUserID, '$StartDate', '$Expires', $UserID)";

        if (mysqli_query($conn, $sql)) {
            // Obtain last inserted id
            $last_id = mysqli_insert_id($conn);
        } else {
            echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
        }

        //$last_id = mysqli_insert_id($conn);
    }
    return $last_id;
}

function createNewCertificateEntry($CIID, $Name, $RelatedServer, $Type, $UserID)
{
    global $conn;
    global $functions;

    $CertID = "";

    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {

        //Create New CI in cis table
        $sql = "INSERT INTO ci_certificates(RelatedCI, CertName, RelatedServerID, CertType)
                    VALUES ($CIID, '$Name', '$RelatedServer', '$Type')";

        if (mysqli_query($conn, $sql)) {
            // Obtain last inserted id
            $last_id = mysqli_insert_id($conn);
        } else {
            echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
        }

        //$last_id = mysqli_insert_id($conn);
    }
    return $last_id;
}

function createNewSubscriptionEntry($CIID, $SubscriptionFirm, $MobilePhoneNumber, $SIM, $IMEI, $Subtype, $RelatedHandheldID)
{
    global $conn;
    global $functions;

    $SubID = "";
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {

        $sql = "INSERT INTO ci_mobilesubscriptions(RelatedCI, SubscriptionFirm, MobilePhoneNumber, SIM, IMEI, SubscriptionType, RelatedHandheldID) VALUES ($CIID,$SubscriptionFirm,'$MobilePhoneNumber','$SIM','$IMEI','$Subtype','$RelatedHandheldID')";

        if (mysqli_query($conn, $sql)) {
            // Obtain last inserted id
            $last_id = mysqli_insert_id($conn);
        } else {
            echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
        }

        //$last_id = mysqli_insert_id($conn);
    }
    return $last_id;
}

function sendInternalMessage($To, $From, $Message)
{
    global $conn;
    global $functions;

    $sql = "INSERT INTO messages(ToUserID, FromUserID, Message, SendDate) 
                VALUES ($To,$From,'$Message',NOW())";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function changeServerEntry($UserID, $ServerID, $ModelNo, $Ram, $Harddiskspace, $Hostname, $Producer, $Location, $RelatedCompanyID, $BoughtDate, $OS, $CPU, $LicenceEnd, $RelatedTypeID, $Active)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');
    $ModelNo = mysqli_real_escape_string($conn, $ModelNo);
    $Hostname = mysqli_real_escape_string($conn, $Hostname);
    $Location = mysqli_real_escape_string($conn, $Location);
    $BoughtDate = convertFromDanishTimeFormat($BoughtDate);
    $LicenceEnd = convertFromDanishTimeFormat($LicenceEnd);

    $sql = "UPDATE ci_servers SET ModelNo='" . $ModelNo . "', Ram='" . $Ram . "', Harddiskspace='" . $Harddiskspace . "', Hostname='" . $Hostname . "'
        , RelatedProducerID='" . $Producer . "', Location='" . $Location . "', RelatedCompanyID='" . $RelatedCompanyID . "', BoughtDate='" . $BoughtDate . "', LicenceEnd='" . $LicenceEnd . "', RelatedOSID='" . $OS . "'
        , CPU='" . $CPU . "', RelatedTypeID='" . $RelatedTypeID . "', Active='" . $Active . "' WHERE ci_servers.ID = '" . $ServerID . "';";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function changeHandheldEntry($UserID, $HandheldID, $Type, $ModelNo, $SerialNumber, $Producer, $User, $Company, $BoughtDate, $EndOfLife, $Active)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');
    $ModelNo = mysqli_real_escape_string($conn, $ModelNo);
    $SerialNumber = mysqli_real_escape_string($conn, $SerialNumber);
    $BoughtDate = convertFromDanishTimeFormat($BoughtDate);
    $EndOfLife = convertFromDanishTimeFormat($EndOfLife);

    $sql = "UPDATE ci_handhelds SET Type=$Type, ModelNo='$ModelNo',SerialNumber='$SerialNumber',RelatedProducerID='$Producer',RelatedUserID=$User,RelatedCompanyID=$Company,BoughtDate='$BoughtDate',EndOfLife='$EndOfLife',Active=$Active
                WHERE ci_handhelds.ID = '" . $HandheldID . "';";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function changeMobileSubscriptionEntry($UserID, $SubID, $Firm, $PhoneNumber, $SIM, $IMEI, $User, $BoughtDate, $Expires, $Active)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');
    $SIM = mysqli_real_escape_string($conn, $SIM);
    $IMEI = mysqli_real_escape_string($conn, $IMEI);
    $Expires = convertFromDanishTimeFormat($Expires);
    $BoughtDate = convertFromDanishTimeFormat($BoughtDate);

    $sql = "UPDATE ci_mobilesubscriptions 
                SET SubscriptionFirm='$Firm',MobilePhoneNumber='$PhoneNumber',SIM='$SIM',IMEI='$IMEI',RelatedUserID=$User,BoughtDate='$BoughtDate',Expires='$Expires',Active=$Active,Removed=0
                WHERE ci_mobilesubscriptions.ID = $SubID";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function changeContractEntry($UserID, $ContractID, $Name, $RelatedDoc, $Company, $Expires, $Active)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');
    $Name = mysqli_real_escape_string($conn, $Name);
    $Expires = convertFromDanishTimeFormat($Expires);
    if ($RelatedDoc == "-1") {
        $RelatedDoc = "NULL";
    }

    if ($Company == "-1") {
        $Company = "NULL";
    }

    $sql = "UPDATE ci_contracts
                SET ContractName='$Name',RelatedDocID=$RelatedDoc,RelatedCompanyID=$Company,Expires='$Expires',Active=$Active
                WHERE ci_contracts.ID = $ContractID";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function changeCertificateEntry($UserID, $CertificateID, $Name, $RelatedServer, $Type, $Expires, $Active)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');
    $Name = mysqli_real_escape_string($conn, $Name);
    $Expires = convertFromDanishTimeFormat($Expires);
    if ($RelatedServer == "-1") {
        $RelatedServer = "NULL";
    }

    $sql = "UPDATE ci_certificates 
                SET Name='$Name',Expires='$Expires',RelatedServerID=$RelatedServer,Type='$Type',Active='$Active',Removed=0
                WHERE ci_certificates.ID = $CertificateID";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function retireCI($UserID, $CIIDVal)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');

    $sql = "UPDATE cis SET Active=0, Removed=1 WHERE cis.ID = $CIIDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function removeCertificate($UserID, $ciid)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');

    $sql = "UPDATE cis SET Active=0, Removed=1 WHERE cis.ID = '" . $ciid . "';";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function removeHandheldEntry($UserID, $PhoneID)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');

    $sql = "UPDATE ci_handhelds SET Active=0, Removed=1 WHERE ci_handhelds.ID = '" . $PhoneID . "';";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createNewWorkstationEntry($CIID, $Model, $Ram, $CPU, $Harddisk, $FQDN, $Producer, $OS, $Type)
{
    global $conn;
    global $functions;

    $last_id = "";
    $dbfull = isDBFull();

    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $Model = mysqli_real_escape_string($conn, $Model);
        $Ram = mysqli_real_escape_string($conn, $Ram);
        $CPU = mysqli_real_escape_string($conn, $CPU);
        $Harddisk = mysqli_real_escape_string($conn, $Harddisk);
        $FQDN = mysqli_real_escape_string($conn, $FQDN);

        $sql = "INSERT INTO ci_workstations(RelatedCI, ModelNo, Ram, CPU, Harddiskspace, Hostname, RelatedProducerID, RelatedOSID, RelatedTypeID) 
            VALUES ('$CIID','$Model','$Ram','$CPU','$Harddisk','$FQDN','$Producer','$OS','$Type')";

        if (mysqli_query($conn, $sql)) {
            // Obtain last inserted id
            $last_id = mysqli_insert_id($conn);
        } else {
            echo "ERROR: Could not execute $sql. " . mysqli_error($conn);
        }

        //$last_id = mysqli_insert_id($conn);
    }

    return $last_id;
}

function changeWorkstationEntry($UserID, $WorkstationID, $ModelNo, $Ram, $Harddiskspace, $Hostname, $Producer, $RelatedCompanyID, $RelatedUserID, $BoughtDate, $OS, $CPU, $LicenceEnd, $RelatedTypeID, $Active)
{
    global $conn;
    global $functions;
    $ModelNo = mysqli_real_escape_string($conn, $ModelNo);
    $Hostname = mysqli_real_escape_string($conn, $Hostname);

    $DateTimeNow = date('Y-m-d H:i:s');
    if ($RelatedUserID == "-1") {
        $RelatedUserID = "NULL";
    }
    $sql = "UPDATE ci_workstations SET ModelNo='" . $ModelNo . "', Ram='" . $Ram . "', Harddiskspace='" . $Harddiskspace . "', Hostname='" . $Hostname . "'
        , RelatedProducerID='" . $Producer . "', RelatedCompanyID='" . $RelatedCompanyID . "', RelatedUserID='" . $RelatedUserID . "', BoughtDate='" . $BoughtDate . "', LicenceEnd='" . $LicenceEnd . "', RelatedOSID='" . $OS . "'
        , CPU='" . $CPU . "', RelatedTypeID='" . $RelatedTypeID . "', Active='" . $Active . "' WHERE ci_workstations.ID = '" . $WorkstationID . "';";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function removeWorkstation($UserID, $CIIDVal)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');

    $sql = "UPDATE cis SET Active=0, Removed=1 WHERE cis.ID = $CIIDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteMobilePhoneEntry($UserID, $MobilePhoneID)
{
    global $conn;
    global $functions;
    $DateTimeNow = date('Y-m-d H:i:s');

    $sql = "DELETE FROM ci_mobilephones WHERE ci_mobilephones.ID = '" . $MobilePhoneID . "';";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getUserFirstName($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Firstname AS Firstname FROM users WHERE ID = $UserID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['Firstname'];
    }

    return $Value;
}

function getUserLastName($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Lastname AS Lastname FROM users WHERE ID = $UserID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['Lastname'];
    }

    return $Value;
}

function getUserPhone($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Phone FROM users WHERE ID = $UserID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Phone = $row['Phone'];
    }

    return $Phone;
}

function getUserName($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Username FROM users WHERE ID = $UserID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Username = $row['Username'];
    }

    return $Username;
}

function getUserIDFromUsername($Username)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID AS UserID FROM users WHERE Username = '$Username'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['UserID'];
    }

    return $value;
}

function getUserEmailFromID($ID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Email FROM users WHERE ID = $ID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['Email'];
    }

    return $Value;
}

function getUserSecretCode($UserID)
{
    global $conn;
    global $functions;
    $SettingValue = "";
    $sql = "SELECT google_secret_code FROM users WHERE ID = $UserID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $SettingValue = $row['google_secret_code'];
    }

    if (empty($SettingValue)) {
        $SettingValue = 'none';
    }

    return $SettingValue;
}

function getUserGoogleAuth($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT SettingValue FROM user_settings WHERE RelatedSettingID = '31' AND RelatedUserID = '" . $UserID . "';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $SettingValue = $row['SettingValue'];
    }

    if (empty($SettingValue)) {
        $SettingValue = 'none';
    }

    return $SettingValue;
}

function getUserQRUrl($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT QRUrl FROM users WHERE users.ID = $UserID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $SettingValue = $row['QRUrl'];
    }

    if (empty($SettingValue)) {
        $SettingValue = 'none';
    }

    return $SettingValue;
}

function updateLanguageUserSetting($UserID, $LanguageID)
{
    global $conn;
    global $functions;

    $sql = "UPDATE user_settings SET SettingValue = '" . $LanguageID . "' WHERE RelatedUserID = '" . $UserID . "' AND RelatedSettingID = '10';";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createGoogleAuthUserSetting($UserID, $UserGoogleAuth)
{
    global $conn;
    global $functions;

    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES ( '31', '" . $UserGoogleAuth . "','" . $UserID . "');";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateGoogleAuthUserSetting($UserID, $UserGoogleAuth)
{
    global $conn;
    global $functions;

    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES ( '31', '" . $UserGoogleAuth . "','" . $UserID . "');";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getDefaultTimeZoneName($DefaultTimeZoneID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TimeZoneName 
            FROM system_timezones 
            WHERE ID = $DefaultTimeZoneID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $DefaultTimeZoneName = $row['TimeZoneName'];
    }

    return $DefaultTimeZoneName;
}

function getUserTimeZoneID($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT SettingValue
            FROM user_settings
            WHERE RelatedSettingID = '11' AND RelatedUserID = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $UserID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $SettingValue);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (empty($SettingValue)) {
        $SettingValue = 'none';
    }

    return $SettingValue;
}

function getDefaultSettingValue($SettingID)
{
    global $conn;
    global $functions;

    //Get default language
    $sql = "SELECT SettingValue 
                FROM settings 
                WHERE ID = '$SettingID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $SettingValueID = $row['SettingValue'];
    }

    $sql = "SELECT Value 
                FROM settings_values 
                WHERE ID = '$SettingValueID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $SettingValue = $row['Value'];
    }

    return $SettingValue;
}

function getDefaultUserSettingValue($UserSettingValueID)
{
    global $conn;
    global $functions;

    $UserSettingValue = "";
    //Get user setting id
    $sql = "SELECT Value 
            FROM settings_values 
            WHERE ID = '$UserSettingValueID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $UserSettingValue = $row['Value'];
    }

    return $UserSettingValue;
}

function updateUserSetting($UserID, $UserSettingValueID, $RelatedSettingID)
{
    global $conn;
    global $functions;

    $sql = "UPDATE user_settings SET SettingValue = '" . $UserSettingValueID . "' WHERE RelatedUserID = '" . $UserID . "' AND RelatedSettingID = '$RelatedSettingID';";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createUserTimeZoneID($UserID, $UserTimeZoneID)
{
    global $conn;
    global $functions;

    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES ( '11', '" . $UserTimeZoneID . "','" . $UserID . "');";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}


function updateUserTimeZoneID($UserID, $UserTimeZoneID)
{
    global $conn;
    global $functions;

    $sql = "UPDATE user_settings SET SettingValue = '" . $UserTimeZoneID . "' WHERE RelatedUserID = '" . $UserID . "' AND RelatedSettingID = '11';";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getModuleTypeIconName($ModuleID)
{
    global $conn;
    global $functions;
    $ModuleIconName = "";

    $sql = "SELECT TypeIcon 
            FROM modules
            WHERE modules.ID='" . $ModuleID . "';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ModuleIconName = $row['TypeIcon'];
    }

    return $ModuleIconName;
}

function getITSMModuleTypeIcon($ModuleID)
{
    global $conn;
    global $functions;
    $ModuleIconName = "";

    $sql = "SELECT TypeIcon 
            FROM itsm_modules
            WHERE itsm_modules.ID = '$ModuleID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ModuleIconName = $row['TypeIcon'];
    }

    return $ModuleIconName;
}

function getITSMModuleSLA($ITSMTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT SLA 
            FROM itsm_modules
            WHERE itsm_modules.ID = '$ITSMTypeID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $SLA = $row['SLA'];
    }

    return $SLA;
}

function getITSMModuleIDFromArchive($ID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedModuleID
            FROM itsm_knowledge_archive
            WHERE itsm_knowledge_archive.ID = $ID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $RelatedModuleID = $row['RelatedModuleID'];
    }

    return $RelatedModuleID;
}

function getITSMIDFromArchive($ID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedDocumentID  
            FROM itsm_knowledge_archive
            WHERE itsm_knowledge_archive.ID = $ID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $RelatedDocumentID = $row['RelatedDocumentID'];
    }

    return $RelatedDocumentID;
}

function getITSMVersionFromArchive($ID)
{
    global $conn;
    global $functions;


    $sql = "SELECT DocumentVersion  
            FROM itsm_knowledge_archive
            WHERE itsm_knowledge_archive.ID = $ID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $DocumentVersion = $row['DocumentVersion'];
    }

    return $DocumentVersion;
}

function getITSMModuleTableNames()
{
    global $conn;
    global $functions;

    $sql = "SELECT ID, TableName
            FROM itsm_modules;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $TableName = $row['TableName'];
        $TableArray[] = array("ID" => $ID, "TableName" => $TableName);
    }

    return $TableArray;
}

function getITSMModuleShotName($ITSMTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ShortElementName
            FROM itsm_modules
            WHERE ID = $ITSMTypeID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ShortElementName = $row['ShortElementName'];
    }

    return $ShortElementName;
}

function getITSMModuleTableNamesExcludedThis($ITSMTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TableName
            FROM itsm_modules
            WHERE ID != $ITSMTypeID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $TableName = $row['TableName'];
        $TableArray[] = $TableName;
    }

    return $TableArray;
}

function getRelatedSprintName($SprintID)
{
    global $conn;
    global $functions;
    $value = "";

    $sql = "SELECT ShortName 
                FROM projects_tasks_catagories
                WHERE projects_sprints.ID='$SprintID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['ShortName'];
    }

    return $value;
}

function getRelatedCategoryName($RelatedCategoryID)
{
    global $conn;
    global $functions;
    $value = "";

    $sql = "SELECT ShortName 
                    FROM projects_tasks_categories
                    WHERE projects_tasks_categories.ID='$RelatedCategoryID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['ShortName'];
    }

    return $value;
}

function updateProblem($ProblemIDVal, $ProblemName, $ProblemDescription, $ProblemCompany, $ProblemResponsible, $ProblemPriority, $ProblemStatus, $ProblemDeadline)
{
    global $conn;
    global $functions;
    $ProblemName = mysqli_real_escape_string($conn, $ProblemName);
    $ProblemDescription = mysqli_real_escape_string($conn, $ProblemDescription);

    $sql = "UPDATE problems SET Name='" . $ProblemName . "',Description='" . $ProblemDescription . "',RelatedRespID='" . $ProblemResponsible . "',RelatedStatusID='" . $ProblemStatus . "',RelatedCompanyID='" . $ProblemCompany . "',RelatedPriorityID='" . $ProblemPriority . "',Deadline='" . $ProblemDeadline . "'
                WHERE ID='" . $ProblemIDVal . "'";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createProblemFromIncident($ProblemName, $ProblemDescription, $ProblemCompany, $ProblemResponsible, $ProblemPriority, $ProblemStatus, $ProblemDeadline, $TicketID)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $ProblemName = mysqli_real_escape_string($conn, $ProblemName);
        $ProblemDescription = mysqli_real_escape_string($conn, $ProblemDescription);
        //Create new problem
        $ProblemDeadline = $functions->convertFromDanishDateFormat($ProblemDeadline);
        $sql = "INSERT INTO problems(Name, Description, RelatedRespID, Created_Date, RelatedStatusID, RelatedCompanyID, RelatedPriorityID, Deadline) 
                    VALUES ('$ProblemName','$ProblemDescription','$ProblemResponsible',NOW(),'$ProblemStatus','$ProblemCompany','$ProblemPriority','$ProblemDeadline')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        //Get newly created problem id
        $sql = "SELECT MAX(ID) AS ID
                    FROM problems";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $NewProblemID = $row['ID'];
        }


        //Create relation between ticket and problem
        $sql = "INSERT INTO ticketproblems (TicketID, ProblemID)
                    VALUES ('" . $TicketID . "', '" . $NewProblemID . "')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        return $NewProblemID;
    }
}

function createRequestFromIncident($RequestName, $RequestDescription, $RequestCompany, $RequestResponsible, $RequestPriority, $RequestStatus, $RequestDeadline, $TicketID, $UserID)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $RequestName = mysqli_real_escape_string($conn, $RequestName);
        $RequestDescription = mysqli_real_escape_string($conn, $RequestDescription);
        //Create new Request
        $RequestDeadline = $functions->convertFromDanishDateFormat($RequestDeadline);
        $sql = "INSERT INTO requests(Subject, ProblemText, Responsible, DateCreated, CreatedByUserID, Status, RelatedCompanyID, Priority) 
                    VALUES ('$RequestName','$RequestDescription','$RequestResponsible',NOW(),$UserID,'$RequestStatus','$RequestCompany','$RequestPriority')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        //Get newly created Request id
        $sql = "SELECT MAX(ID) AS ID
                    FROM Requests";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $NewRequestID = $row['ID'];
        }


        //Create relation between ticket and Request
        $sql = "INSERT INTO ticketrequests (TicketID, RequestID)
                    VALUES ('" . $TicketID . "', '" . $NewRequestID . "')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        return $NewRequestID;
    }
}

function createProblemFromRequest($ProblemName, $ProblemDescription, $ProblemCompany, $ProblemResponsible, $ProblemPriority, $ProblemStatus, $ProblemDeadline, $RequestID)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $ProblemName = mysqli_real_escape_string($conn, $ProblemName);
        $ProblemDescription = mysqli_real_escape_string($conn, $ProblemDescription);
        //Create new problem
        $ProblemDeadline = $functions->convertFromDanishDateFormat($ProblemDeadline);
        $sql = "INSERT INTO problems(Name, Description, RelatedRespID, Created_Date, RelatedStatusID, RelatedCompanyID, RelatedPriorityID, Deadline) 
                    VALUES ('$ProblemName','$ProblemDescription','$ProblemResponsible',NOW(),'$ProblemStatus','$ProblemCompany','$ProblemPriority','$ProblemDeadline')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        //Get newly created problem id
        $sql = "SELECT MAX(ID) AS ID
                    FROM problems";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $NewProblemID = $row['ID'];
        }


        //Create relation between ticket and problem
        $sql = "INSERT INTO requestproblems (RequestID, ProblemID)
                    VALUES ('$RequestID', '$NewProblemID')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        return $NewProblemID;
    }
}

function createProblem($ProblemName, $ProblemDescription, $ProblemCompany, $ProblemResponsible, $ProblemPriority, $ProblemStatus, $ProblemDeadline, $TicketID)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $ProblemName = mysqli_real_escape_string($conn, $ProblemName);
        $ProblemDescription = mysqli_real_escape_string($conn, $ProblemDescription);
        //Create new problem
        $ProblemDeadline = $functions->convertFromDanishDateFormat($ProblemDeadline);
        $sql = "INSERT INTO problems(Name, Description, RelatedRespID, Created_Date, RelatedStatusID, RelatedCompanyID, RelatedPriorityID, Deadline) 
                    VALUES ('$ProblemName','$ProblemDescription','$ProblemResponsible',NOW(),'1','$ProblemCompany','$ProblemPriority','$ProblemDeadline')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        //Get newly created problem id
        $sql = "SELECT MAX(ID) AS ID
                    FROM problems";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $NewProblemID = $row['ID'];
        }


        //Create relation between ticket and problem
        $sql = "INSERT INTO ticketproblems (TicketID, ProblemID)
                    VALUES ('" . $TicketID . "', '" . $NewProblemID . "')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        return $NewProblemID;
    }
}

function createNewProblem($ProblemName, $ProblemDescription, $ProblemCompany, $ProblemResponsible, $ProblemPriority, $ProblemStatus, $ProblemDeadline)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $ProblemName = mysqli_real_escape_string($conn, $ProblemName);
        $ProblemDescription = mysqli_real_escape_string($conn, $ProblemDescription);
        //Create new problem

        $sql = "INSERT INTO problems(Name, Description, RelatedRespID, Created_Date, RelatedStatusID, RelatedCompanyID, RelatedPriorityID, Deadline) 
                    VALUES ('$ProblemName','$ProblemDescription','$ProblemResponsible',NOW(),'1','$ProblemCompany','$ProblemPriority','$ProblemDeadline')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        //Get newly created problem id
        $sql = "SELECT MAX(ID) AS ID
                    FROM problems";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        while ($row = mysqli_fetch_array($result)) {
            $NewProblemID = $row['ID'];
        }

        return $NewProblemID;
    }
}

function generateRandomString($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getExistingDemo($email)
{
    global $connbpage;
    $exists = 0;

    $sql = "SELECT ID
                FROM demo_orders
                WHERE CustEmail = '$email'";

    $result = mysqli_query($connbpage, $sql) or die('Query fail: ' . mysqli_error($connbpage));

    while ($row = mysqli_fetch_array($result)) {
        if (!empty($row['ID'])) {
            $exists = 1;
        }
    }
    return $exists;
}

function getNumberOfExpiredCIs($UserID)
{
    global $conn;
    global $functions;

    $Antal = 0;
    $TempAntal = 0;

    $sql = "SELECT TableName
            FROM cmdb_cis
            WHERE Active = 1";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $TableName = $row['TableName'];
        $sql2 = "SELECT COUNT(*) AS Antal
                FROM $TableName
                WHERE Active = '1' AND Removed = '0' AND (EndDate < CURDATE()) AND RelatedUserID = '$UserID'";

        $result2 = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_array($result2)) {
            $TempAntal = $row['Antal'];
            $Antal = $TempAntal + $Antal;
        }
    }
    return $Antal;
}

function getExpiredCIs($UserID)
{
    global $conn;
    global $functions;

    $ResultArray = [];

    $sql = "SELECT TableName
            FROM cmdb_cis
            WHERE Active = 1";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $TableName = $row['TableName'];
        $CITypeID = getCITypeIDFromTableName($TableName);
        $CITypeName = getCITypeName($CITypeID);
        $LookupField = getCILookupField($CITypeID);
        $Label = getCIFieldLabelFromFieldName($CITypeID, $LookupField);

        $sql2 = "SELECT ID, $LookupField AS `$Label`, EndDate
                FROM $TableName
                WHERE Active = '1' AND Removed = '0' AND (EndDate < CURDATE()) AND RelatedUserID = '$UserID'";

        $result2 = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_array($result2)) {
            $ID = $row["ID"];
            $Value = $row["$Label"];
            $EndDate = $row["EndDate"];
            $ResultArray[] = array("ID" => $ID, "Label" => $Value, "EndDate" => $EndDate, "TableName" => $TableName, "CITypeID" => $CITypeID, "CITypeName" => $CITypeName);
        }
    }

    return $ResultArray;
}

function getExistingOrder($email)
{
    global $connbpage;
    $exists = 0;

    $sql = "SELECT ID
                FROM orders
                WHERE CustEmail = '$email'";

    $result = mysqli_query($connbpage, $sql) or die('Query fail: ' . mysqli_error($connbpage));

    while ($row = mysqli_fetch_array($result)) {
        if (!empty($row['ID'])) {
            $exists = 1;
        }
    }
    return $exists;
}

function createDemoOrder($firstname, $lastname, $cvr, $email, $sitename)
{
    global $connbpage;

    $sql = "INSERT INTO demo_orders(OrderDate, firstname, lastname, CustEmail, CustCVR, sitename) VALUES (Now(),'$firstname','$lastname','$email','$cvr','$sitename')";

    mysqli_query($connbpage, $sql) or die('Query fail: ' . mysqli_error($connbpage));
}

function createOrder($package, $rentallength, $companyname, $sitename, $antalanvendere, $firstname, $lastname, $cvr, $email, $password)
{
    global $connbpage;

    $sql = "INSERT INTO orders(PackageOrdered, RentalLength, NumberOfUsers, Firstname, Lastname, CustEmail, CustCVR, Sitename, Password, Confirmed, Delivered) 
                VALUES ('$package',$rentallength,$antalanvendere,'$firstname','$lastname','$email','$cvr','$sitename','$password',0,0)";

    mysqli_query($connbpage, $sql) or die('Query fail: ' . mysqli_error($connbpage));
}

function ordersCreateConfirmEmail($email, $Code)
{
    global $connbpage;

    $sql = "INSERT INTO confirm_email (Email, Confirmed, Code) VALUES ('$email', '0', '$Code')";

    mysqli_query($connbpage, $sql) or die('Query fail: ' . mysqli_error($connbpage));
}

function ordersConfirmEmail($Code)
{
    global $connbpage;
    $email = "";

    $sql = "SELECT Email FROM confirm_email WHERE Code = '$Code'";
    $result = mysqli_query($connbpage, $sql) or die('Query fail: ' . mysqli_error($connbpage));
    while ($row = mysqli_fetch_array($result)) {
        $email = $row['Email'];
    }

    if (!empty($email)) {
        confirmOrderEmail($email);
        $sql = "DELETE FROM confirm_email WHERE Code = '$Code'";
        mysqli_query($connbpage, $sql) or die('Query fail: ' . mysqli_error($connbpage));
        return "Confirmed";
    }
}

function confirmOrderEmail($email)
{
    global $connbpage;

    $sql = "UPDATE orders SET Confirmed = 1 WHERE CustEmail = '$email'";

    mysqli_query($connbpage, $sql) or die('Query fail: ' . mysqli_error($connbpage));
}

function makeContactFormEntry($FromEmail, $FromFullName, $Content)
{
    global $connbpage;
    $To = "claus@practicle.dk";
    $ToName = "Claus Jørgensen";
    $Subject = "Besked fra kontaktformularen";

    $FromEmail = mysqli_real_escape_string($connbpage, $FromEmail);
    $FromFullName = mysqli_real_escape_string($connbpage, $FromFullName);
    $Content = mysqli_real_escape_string($connbpage, $Content);

    $sql = "INSERT INTO contactmessages(Name, FromEmail, Message) VALUES ('$FromFullName', '$FromEmail', '$Content')";

    mysqli_query($connbpage, $sql) or die('Query fail: ' . mysqli_error($connbpage));
}

function folderExist($folder)
{
    // Get canonicalized absolute pathname
    $path = realpath($folder);

    // If it exist, check if it's a directory
    if ($path !== false and is_dir($path)) {
        // Return canonicalized absolute pathname
        return $path;
    }

    // Path/folder does not exist
    return false;
}

function createFolder($folder)
{
    // Get canonicalized absolute pathname
    $path = realpath($folder);

    // If it exist, check if it's a directory
    if ($path !== false and is_dir($path)) {
        // Return canonicalized absolute pathname
        return $path;
    }

    // Path/folder does not exist
    return false;
}

function addTimeRegistration($UserID, $RelatedTask, $Time, $Date)
{

    global $conn;
    global $functions;

    $sql = "INSERT INTO time_registration (RelatedTaskID, RelatedUserID, TimeRegistered, DateWorked, DateRegistered)
                VALUES ( '$RelatedTask', '$UserID', '$Time', Now(), NOW())";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getFeed($feedUrl)
{

    $content = file_get_contents($feedUrl);
    $x = new SimpleXmlElement($content);

    echo "<ul>";

    foreach ($x->channel->item as $entry) {
        echo "<li><a href='$entry->link' title='$entry->title'>" . $entry->title . "</a></li>";
    }
    echo "</ul>";
}

function createNewTeam($UserID, $TeamName, $TeamColour, $TeamLeader, $TeamDescription)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $sql = "INSERT INTO teams (Teamname, Colour, TeamLeader, Description) 
                    VALUES ('$TeamName','$TeamColour', '$TeamLeader','$TeamDescription')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function createNews($UserID, $NewsCategory, $NewsWriter, $NewsHeadline, $NewsContent, $DateCreated)
{
    global $conn;
    global $functions;

    $NewsHeadline = mysqli_real_escape_string($conn, $NewsHeadline);
    $NewsContent = mysqli_real_escape_string($conn, $NewsContent);

    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $DateCreated = convertFromDanishTimeFormat($DateCreated);

        $sql = "INSERT INTO news(Headline, Content, CreatedByUserID, NewsWriter, DateCreated, RelatedCategory, CommentsAllowed, Active) 
                    VALUES ('$NewsHeadline','$NewsContent',$UserID,$NewsWriter,'$DateCreated',$NewsCategory,1,1)";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function updateTeamInformation($UserID, $TeamIDVal, $TeamnameVal, $TeamColourVal, $TeamLeaderVal, $TeamDescriptionVal, $TeamActiveVal)
{
    global $conn;
    global $functions;

    $sql = "UPDATE teams 
                SET Teamname='$TeamnameVal',Colour=$TeamColourVal,Active=$TeamActiveVal,TeamLeader=$TeamLeaderVal,Description='$TeamDescriptionVal'
                WHERE teams.ID = $TeamIDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateCorpUrlInformation($UserID, $CorpLinkIDVal, $NameVal, $URLVal, $ActiveVal)
{
    global $conn;
    global $functions;

    $sql = "UPDATE corp_links 
                SET Name='$NameVal',URL='$URLVal',Active=$ActiveVal
                WHERE corp_links.ID = $CorpLinkIDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createCorpUrl($UserID, $UrlName, $Link)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $sql = "INSERT INTO corp_links (Name, URL, Active)
                    VALUES ( '$UrlName','$Link',1)";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function createStandardAnswer($UserID, $Name, $AnswerText, $RelatedModule)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $sql = "INSERT INTO standardanswers (Name, RelatedModuleID, Answer, Active)
                    VALUES ( '$Name','$RelatedModule','$AnswerText',1)";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function updateStandardAnswerInformation($UserID, $AnswerID, $NameVal, $AnswerVal, $RelatedModuleIDVal, $ActiveVal)
{
    global $conn;
    global $functions;

    $sql = "UPDATE standardanswers 
                SET Name='$NameVal',RelatedModuleID='$RelatedModuleIDVal',Answer='$AnswerVal',Active='$ActiveVal' 
                WHERE ID = '$AnswerID'";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateRequestTemplateInformation($UserID, $StandardRequestIDVal, $Name, $Description, $RelatedWorkFlowTemplate, $Active)
{
    global $conn;
    global $functions;

    $sql = "UPDATE requests_templates
                SET Name='$Name',Description='$Description',RelatedWorkflowTemplate=$RelatedWorkFlowTemplate,Active='$Active'
                WHERE requests_templates.ID = $StandardRequestIDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteStandardChange($UserID, $StandardChangeIDVal)
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM changes_standards
                WHERE changes_standards.ID = $StandardChangeIDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteRequestTemplate($UserID, $StandardRequestIDVal)
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM requests_templates
                WHERE requests_templates.ID = $StandardRequestIDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateStandardChangeInformation($UserID, $IDVal, $NameVal, $AnswerVal, $RelatedModuleIDVal, $ActiveVal)
{
    global $conn;
    global $functions;

    $sql = "UPDATE standardanswers 
                SET Name='$NameVal',RelatedModuleID='$RelatedModuleIDVal',Answer='$AnswerVal',Active=$ActiveVal
                WHERE standardanswers.ID = $IDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateNewsItem($UserID, $NewsID, $Headline, $Content, $CreatedBy, $NewsWriter, $DateCreated, $Category, $CommentsAllowed, $Active)
{
    global $conn;
    global $functions;
    $DateCreated = convertFromDanishTimeFormat($DateCreated);
    $sql = "UPDATE news 
                SET Headline='$Headline',Content='$Content',NewsWriter=$NewsWriter,DateCreated='$DateCreated',RelatedCategory=$Category,CommentsAllowed=$CommentsAllowed,Active=$Active
                WHERE ID = $NewsID";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createNewUserGroup($UserID, $GroupName, $GroupDescription, $RelatedModule)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $sql = "INSERT INTO usergroups (GroupName, Description, RelatedModuleID) 
                VALUES ('$GroupName','$GroupDescription', '$RelatedModule')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function createNewUserRole($UserID, $RoleName, $RoleDescription)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $sql = "INSERT INTO roles (RoleName, Description) 
                    VALUES ('$RoleName','$RoleDescription')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function updateGroupInformation($UserID, $GroupIDVal, $GroupNameVal, $GroupDescriptionVal, $GroupStatusVal, $RelatedModuleVal)
{
    global $conn;
    global $functions;

    $sql = "UPDATE usergroups 
                SET GroupName='$GroupNameVal',Active=$GroupStatusVal,Description='$GroupDescriptionVal',RelatedModuleID='$RelatedModuleVal'
                WHERE usergroups.ID = $GroupIDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateRoleInformation($RoleIDVal, $RoleNameVal, $RoleDescriptionVal, $RoleActiveVal)
{
    global $conn;
    global $functions;

    $sql = "UPDATE roles 
            SET RoleName='$RoleNameVal',Active=$RoleActiveVal,Description='$RoleDescriptionVal'
            WHERE roles.ID = $RoleIDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createNewCompany($UserID, $CompanyNameVal, $WebpageVal, $AddressVal, $ZipCodeVal, $CountryVal, $EmailVal, $CityVal, $CBRVal, $PhoneVal, $CustomerAccountNumberVal, $RelatedSLAIDVal)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {

        $sql = "INSERT INTO companies(Companyname, WebPage, Phone, RelatedSLAID, CustomerAccountNumber, Address, ZipCode, City, Email, CBR, Country) 
                VALUES ('$CompanyNameVal','$WebpageVal','$PhoneVal','$RelatedSLAIDVal','$CustomerAccountNumberVal','$AddressVal','$ZipCodeVal','$CityVal','$EmailVal','$CBRVal','$CountryVal')";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function updateCompanyInformation($UserSessionID, $CompanyID, $CompanyName, $WebPage, $Address, $ZipCode, $Country, $Email, $City, $CBR, $Phone, $CustomerAccountNumber, $RelatedSLAID, $Active)
{
    global $conn;
    global $functions;

    $PreCompanyName = getCompanyPreValue($CompanyID, "CompanyName");
    $PreWebPage = getCompanyPreValue($CompanyID, "WebPage");
    $PreAddress = getCompanyPreValue($CompanyID, "Address");
    $PreZipCode = getCompanyPreValue($CompanyID, "ZipCode");
    $PreCountry = getCompanyPreValue($CompanyID, "Country");
    $PreEmail = getCompanyPreValue($CompanyID, "Email");
    $PreCity = getCompanyPreValue($CompanyID, "City");
    $PreCBR = getCompanyPreValue($CompanyID, "CBR");
    $PrePhone = getCompanyPreValue($CompanyID, "Phone");
    $PreCustomerAccountNumber = getCompanyPreValue($CompanyID, "CustomerAccountNumber");
    $PreRelatedSLAID = getCompanyPreValue($CompanyID, "RelatedSLAID");
    $PreActive = getCompanyPreValue($CompanyID, "Active");

    $sql = "UPDATE companies
            SET Companyname='$CompanyName',Active=$Active,WebPage='$WebPage',Phone='$Phone',RelatedSLAID='$RelatedSLAID',CustomerAccountNumber='$CustomerAccountNumber',Address='$Address',ZipCode='$ZipCode', City='$City',Email='$Email',CBR='$CBR',Country='$Country'
            WHERE ID = $CompanyID";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    if ($PreCompanyName !== $CompanyName) {
        $LogActionText = "CompanyName changed from $PreCompanyName to $CompanyName";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
    if ($PreWebPage !== $WebPage) {
        $LogActionText = "WebPage changed from $PreWebPage to $WebPage";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
        if ($PreAddress !== $Address) {
        $LogActionText = "Address changed from $PreAddress to $Address";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
        if ($PreZipCode !== $ZipCode) {
        $LogActionText = "ZipCode changed from $PreZipCode to $ZipCode";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
        if ($PreCountry !== $Country) {
        $LogActionText = "Country changed from $PreCountry to $Country";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
        if ($PreEmail !== $Email) {
        $LogActionText = "Email changed from $PreEmail to $Email";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
        if ($PreCity !== $City) {
        $LogActionText = "City changed from $PreCity to $City";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
        if ($PreCBR !== $CBR) {
        $LogActionText = "CBR changed from $PreCBR to $CBR";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }

    if ($PrePhone !== $Phone) {
        $LogActionText = "Phone changed from $PrePhone to $Phone";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
    if ($PreCustomerAccountNumber !== $CustomerAccountNumber) {
        $LogActionText = "CustomerAccountNumber changed from $PreCustomerAccountNumber to $CustomerAccountNumber";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
    if ($PreRelatedSLAID !== $RelatedSLAID) {
        $LogActionText = "SLA changed from $PreRelatedSLAID to $RelatedSLAID";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
    if ($PreActive !== $Active) {
        $LogActionText = "Active Status changed from $PreActive to $Active";
        createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);
    }
}

function updateBSInformation($UserID, $BSID, $ModalBSName, $ModalBSDescription, $ModalBSType, $ModalBSShared, $ModalBSSLA, $ModalBSActive, $ModalBSCompany, $ModalBSResponsible)
{
    global $conn;
    global $functions;

    if (empty($ModalBSResponsible)) {
        $ModalBSResponsible = "NULL";
    }

    if (empty($ModalBSCompany)) {
        $ModalBSCompany = "NULL";
    }
    $sql = "UPDATE businessservices
                SET Name='$ModalBSName',Description='$ModalBSDescription',Type='$ModalBSType',Shared='$ModalBSShared',RelatedSLA='$ModalBSSLA',RelatedCompany=$ModalBSCompany,Responsible=$ModalBSResponsible,Active='$ModalBSActive'
                WHERE ID = $BSID";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateCompanyNotes($UserID, $CompanyIDVal, $CompanyNotes)
{
    global $conn;
    global $functions;

    $sql = "UPDATE companies 
                SET Notes = '$CompanyNotes'
                WHERE companies.ID = $CompanyIDVal";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getTopMenuQuickIcons($UserID, $SettingID)
{
    global $conn;
    global $functions;
    $SettingValue = "";
    $sql = "SELECT SettingValue, Active 
                FROM settings
                WHERE Settings.ID = '$SettingID' AND Active IS TRUE";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $SettingValue = $row['SettingValue'];
        if ($SettingValue == "") {

            $sql = "SELECT SettingValue
                        FROM user_settings
                        WHERE RelatedSettingID = '$SettingID' AND RelatedUserID = $UserID";

            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            while ($row = mysqli_fetch_array($result)) {
                $SettingValue = $row['SettingValue'];
            }
        }
    }

    return $SettingValue;
}

function getElementViewPage($ModuleID)
{
    global $conn;
    global $functions;
    $SettingValue = "";
    $sql = "SELECT ElementViewPage 
                FROM modules
                WHERE modules.ID = $ModuleID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ElementViewPage = $row['ElementViewPage'];
    }

    return $ElementViewPage;
}

function addNewCertificate($UserID, $CertificateName)
{
    global $conn;
    global $functions;
    $dbfull = isDBFull();
    if ($dbfull == "Yes") {
        $FreeSpace = getFreeDBSize();
        echo ("<script LANGUAGE='JavaScript'>
                    window.alert('DB is full: $FreeSpace MB left');
                 </script>");
    } else {
        $sql = "INSERT INTO personal_certificates(RelatedUserID, Name) VALUES ('$UserID','$CertificateName')";
        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function getNewlyCreatedServer()
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT ID
                FROM ci_servers
                WHERE ID = (SELECT MAX(ID) FROM ci_servers);";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $ServerID = $row['ID'];
    }
    return $ServerID;
}

function getNewlyCreatedTaskID($UserID)
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT ID
                FROM taskslist
                WHERE ID = (SELECT MAX(ID) FROM taskslist) AND RelatedUserID = $UserID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $TaskID = $row['ID'];
    }
    return $TaskID;
}

function getNewlyCreatedStepID($NewWorkFlowID, $UserID)
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $StepID = "";
    $sql = "SELECT ID
                FROM workflowsteps
                WHERE ID = (SELECT MAX(ID) FROM workflowsteps) AND RelatedWorkFlowID = $NewWorkFlowID AND RelatedUserID = $UserID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $StepID = $row['ID'];
    }
    return $StepID;
}

function checkIfWorkFlowStepExists($TaskID)
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT ID
                FROM workflowsteps
                WHERE RelatedTaskID = $TaskID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $StepID = $row['ID'];
    }
    return $StepID;
}

function checkIfProjectTaskExists($ProjectTaskID)
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT ID
                FROM workflowsteps
                WHERE RelatedTaskID = $ProjectTaskID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $StepID = $row['ID'];
    }
    return $StepID;
}

function updateWorkflowStepIDStatus($WorkFlowStepID, $Status)
{
    global $conn;
    global $functions;
    if ($Status == 4) {
        $Status = 3;
    }
    //Get SLA Agreement ID for the company
    $sql = "UPDATE workflowsteps SET RelatedStatusID = $Status
                WHERE workflowsteps.ID = $WorkFlowStepID;";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateWorkFlowStepWithTaskID($NewlyCreatedStepID, $NewlyCreatedTaskID)
{
    global $conn;
    global $functions;

    $sql = "UPDATE workflowsteps SET RelatedTaskID = ?
            WHERE workflowsteps.ID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        $errorMessage = "Failed to prepare the SQL statement for updating workflowsteps table.";
        $functions->errorlog(mysqli_error($conn), "updateWorkFlowStepWithTaskID");
        throw new Exception($errorMessage);
    }

    mysqli_stmt_bind_param($stmt, "ii", $NewlyCreatedTaskID, $NewlyCreatedStepID);
    if (!mysqli_stmt_execute($stmt)) {
        $errorMessage = "Failed to execute the SQL statement for updating workflowsteps table.";
        $functions->errorlog(mysqli_stmt_error($stmt), "updateWorkFlowStepWithTaskID");
        throw new Exception($errorMessage);
    }

    mysqli_stmt_close($stmt);
}


function getNewlyCreatedHandheld()
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT ID
                FROM ci_handhelds
                WHERE ID = (SELECT MAX(ID) FROM ci_handhelds);";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $PhoneID = $row['ID'];
    }
    return $PhoneID;
}

function getNewlyCreatedCertificate()
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT ID
                FROM ci_certificates
                WHERE ID = (SELECT MAX(ID) FROM ci_certificates);";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $CertID = $row['ID'];
    }
    return $CertID;
}

function getNewlyCreatedWorkstation()
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT ID
                FROM ci_workstations
                WHERE ID = (SELECT MAX(ID) FROM ci_workstations);";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $CIID = $row['ID'];
    }
    return $CIID;
}

function getNewlyCreatedTicket($UserID)
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT MAX(ID) AS ID
                FROM tickets
                WHERE CreatedByUserID = $UserID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $ticketid = $row['ID'];
    }
    return $ticketid;
}

function getNewlyCreatedProblem($UserID)
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT MAX(ID) AS ID
                FROM problems
                WHERE CreatedByUserID = $UserID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $problemid = $row['ID'];
    }
    return $problemid;
}

function getNewlyCreatedRequest($UserID)
{
    global $conn;
    global $functions;
    //Get SLA Agreement ID for the company
    $sql = "SELECT MAX(ID) AS ID
                FROM Requests
                WHERE CreatedByUserID = $UserID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        //Set SLA agreement to correct SLA agreement ID
        $Requestid = $row['ID'];
    }
    return $Requestid;
}

function checkIfTaskExists($UserID, $ElementID, $ModuleID)
{
    global $conn;
    global $functions;
    $Exists = "";

    $sql = "SELECT ID
            FROM taskslist
            WHERE RelatedUserID = $UserID AND RelatedElementID = $ElementID AND RelatedElementTypeID = $ModuleID AND taskslist.Status != 4;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    if (mysqli_num_rows($result) == 0) {
        $Exists = "No";
    } else {
        $Exists = "Yes";
    }
    return $Exists;
}

function updateDocument($UserID, $DocumentID, $RelatedCategoryID, $Name, $RelatedGroupID, $RelatedReviewerID, $RelatedApproverID, $RelatedOwnerID, $Content, $RelatedStatusID, $LastChanged, $LastChangedBy, $Public, $PublicCategory, $ExpirationDate)
{
    global $conn;
    global $functions;
    $Content = mysqli_real_escape_string($conn, $Content);
    $Name = mysqli_real_escape_string($conn, $Name);
    $ExpirationDate = convertFromDanishTimeFormat($ExpirationDate);

    $sql = "UPDATE knowledge_documents
                SET RelatedCategory=$RelatedCategoryID,Name='$Name',RelatedGroupID=$RelatedGroupID,RelatedReviewerID=$RelatedReviewerID,RelatedApproverID=$RelatedApproverID,RelatedOwnerID=$RelatedOwnerID,Content='$Content',RelatedStatusID=$RelatedStatusID,
                LastChanged=NOW(),LastChangedBy=$UserID,Public='$Public',RelatedPublicCategoryID='$PublicCategory',ExpirationDate='$ExpirationDate'
                WHERE ID = $DocumentID;";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function regenerateUUIDOnProject($ProjectID)
{
    global $conn;
    global $functions;
    $UUID = guidv4();

    $sql = "UPDATE Projects SET UUID = '$UUID' WHERE ID = $ProjectID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    regenerateUUIDOnProjectTasks($UUID, $ProjectID);
}

function regenerateUUIDOnProjectTasks($UUID, $ProjectID)
{
    global $conn;
    global $functions;
    if (empty($UUID)) {
        $UUID = guidv4();
    }

    $sql = "UPDATE Project_tasks SET UUID = '$UUID' WHERE RelatedProject = '$ProjectID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function guidv4()
{
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function removeRelatedSprintID($SprintID)
{
    global $conn;
    global $functions;

    // Query statement
    $sql = "UPDATE project_tasks SET RelatedSprintID = NULL WHERE RelatedSprintID = ?";

    // Parameters
    $params = [$SprintID];

    // Tables to lock for manipulation
    $tables = ["project_tasks"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function createNewProjectSprint($SprintName, $StartDate, $Deadline, $Responsible, $SprintEstimatedBudget, $SprintDescription, $ProjectID, $Link, $Version)
{

    global $conn;
    global $functions;

    $StartDate = convertFromDanishTimeFormat($StartDate);
    $Deadline = convertFromDanishTimeFormat($Deadline);
    $SprintName = mysqli_real_escape_string($conn, $SprintName);
    $SprintDescription = mysqli_real_escape_string($conn, $SprintDescription);

    if ($SprintEstimatedBudget == "") {
        $SprintEstimatedBudget = 0;
    }

    $sql = "INSERT INTO projects_sprints(ShortName, RelatedProjectID, Start, Deadline, EstimatedBudget, Responsible, Description, Link, Version) 
                VALUES ('$SprintName',$ProjectID,'$StartDate','$Deadline','$SprintEstimatedBudget','$Responsible','$SprintDescription','$Link','$Version')";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $last_id = $conn->insert_id;

    return $last_id;
}

function getProjectIDFromSprint($SprintID)
{

    global $conn;
    global $functions;

    $sql = "SELECT RelatedProjectID
                FROM projects_sprints
                WHERE ID = $SprintID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['RelatedProjectID'];
    }

    return $Value;
}

function getProjectIDFromProjectTask($ProjectTaskID)
{

    global $conn;
    global $functions;

    $sql = "SELECT RelatedProject
            FROM project_tasks
            WHERE ID = $ProjectTaskID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['RelatedProject'];
    }

    return $Value;
}

function updateProjectSprint($SprintName, $StartDate, $Deadline, $Responsible, $SprintEstimatedBudget, $SprintDescription, $SprintID, $Link, $Version)
{

    global $conn;
    global $functions;

    $StartDate = convertFromDanishTimeFormat($StartDate);
    $Deadline = convertFromDanishTimeFormat($Deadline);
    $SprintName = mysqli_real_escape_string($conn, $SprintName);
    $SprintDescription = mysqli_real_escape_string($conn, $SprintDescription);

    if ($SprintEstimatedBudget == "") {
        $SprintEstimatedBudget = 0;
    }

    $sql = "UPDATE projects_sprints SET ShortName = '$SprintName', Start = '$StartDate', Deadline = '$Deadline', EstimatedBudget = '$SprintEstimatedBudget', Responsible = '$Responsible', Description = '$SprintDescription', Link='$Link', Version='$Version' 
                WHERE ID = '$SprintID'";


    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $last_id = $conn->insert_id;

    return $last_id;
}

function getProjectTotalEstimatedHours($ProjectID)
{
    global $conn;
    global $functions;

    $sql = "SELECT SUM(project_tasks.EstimatedHours) AS TotalEstimatedHours
                FROM project_tasks
                WHERE RelatedProject = $ProjectID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['TotalEstimatedHours'];
    }
    return $Value;
}

function getProjectCompletedEstimatedHours($ProjectID)
{
    global $conn;
    global $functions;

    $sql = "SELECT SUM(project_tasks.EstimatedHours) AS TotalEstimatedHours
                FROM project_tasks
                WHERE RelatedProject = $ProjectID AND Status = 7;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['TotalEstimatedHours'];
        if (empty($Value)) {
            $Value = 0;
        }
    }
    return $Value;
}

function getProjectTaskProgress($ProjectID)
{
    global $conn;
    global $functions;

    $sql = "SELECT DISTINCT (SELECT SUM(project_tasks.progress) FROM project_tasks WHERE project_tasks.RelatedProject = $ProjectID)/(SELECT COUNT(*) FROM project_tasks WHERE project_tasks.RelatedProject = $ProjectID) AS ProjectTaskProgress
                FROM projects
                LEFT JOIN project_tasks ON projects.ID = project_tasks.RelatedProject
                WHERE projects.ID = $ProjectID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['ProjectTaskProgress'];
        if (empty($Value)) {
            $Value = 0;
        }
    }
    return $Value;
}

function getNumberOfProjectTasks($ProjectID)
{
    global $conn;
    global $functions;

    $sql = "SELECT COUNT(project_tasks.ID) AS Antal
                FROM project_tasks
                WHERE project_tasks.RelatedProject = $ProjectID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['Antal'];
        if (empty($Value)) {
            $Value = 0;
        }
    }
    return $Value;
}

function getNumberOfProjectTasksCompleted($ProjectID)
{
    global $conn;
    global $functions;

    $sql = "SELECT COUNT(project_tasks.ID) AS Antal
                FROM project_tasks
                WHERE project_tasks.RelatedProject = $ProjectID AND project_tasks.Status = 7;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['Antal'];
        if (empty($Value)) {
            $Value = 0;
        }
    }
    return $Value;
}

function getNumberOfProjectSprints($ProjectID)
{
    global $conn;
    global $functions;

    $sql = "SELECT COUNT(projects_sprints.ID) AS Antal
                FROM projects_sprints
                WHERE projects_sprints.RelatedProjectID = $ProjectID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['Antal'];
        if (empty($Value)) {
            $Value = 0;
        }
    }
    return $Value;
}

function checkProjectSprintDateIntervals($StartDate, $Deadline, $ProjectID)
{
    global $conn;
    global $functions;

    $NoGo = false;
    $DatesIntervals = array();
    $SprintName = "";
    $CurrentSprintStartDate = convertFromDanishTimeFormat($StartDate);
    $CurrentSprintStartDate = date('Y-m-d H:m', strtotime($CurrentSprintStartDate));
    $CurrentSprintDeadline = convertFromDanishTimeFormat($Deadline);
    $CurrentSprintDeadline = date('Y-m-d H:m', strtotime($CurrentSprintDeadline));

    $sql = "SELECT ShortName, Start, Deadline
                FROM projects_sprints
                WHERE projects_sprints.RelatedProjectID = $ProjectID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $StartDate = $row['Start'];
        $StartDate = date('Y-m-d H:m', strtotime($StartDate));
        $Deadline = $row['Deadline'];
        $Deadline = date('Y-m-d H:m', strtotime($Deadline));
        $SprintName = $row['ShortName'];

        $DatesIntervals[] = array('StartDate' => $StartDate, 'Deadline' => $Deadline, 'SprintName' => $SprintName);
    }

    foreach ($DatesIntervals as $DatesInterval) {
        $NoGo = false;
        $StartDate = $DatesInterval['StartDate'];
        $Deadline = $DatesInterval['Deadline'];
        $SprintName = $DatesInterval['SprintName'];

        if (($CurrentSprintStartDate >= $StartDate) && ($CurrentSprintStartDate <= $Deadline)) {
            $NoGo = true;
        } else if (($CurrentSprintDeadline >= $StartDate) && ($CurrentSprintDeadline <= $Deadline)) {
            $NoGo = true;
        } else {
            $NoGo = false;
            $SprintName = "";
        }

        if ($NoGo == true) {
            break;
        }
    }

    return $SprintName;
}

function checkProjectSprintDateIntervalsForUpdateSprint($StartDate, $Deadline, $ProjectID, $SprintID)
{
    global $conn;
    global $functions;

    $NoGo = false;
    $DatesIntervals = array();
    $SprintName = "";
    $CurrentSprintStartDate = convertFromDanishTimeFormat($StartDate);
    $CurrentSprintStartDate = date('Y-m-d H:m', strtotime($CurrentSprintStartDate));
    $CurrentSprintDeadline = convertFromDanishTimeFormat($Deadline);
    $CurrentSprintDeadline = date('Y-m-d H:m', strtotime($CurrentSprintDeadline));

    $sql = "SELECT ID, ShortName, Start, Deadline
            FROM projects_sprints
            WHERE projects_sprints.RelatedProjectID = $ProjectID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $StartDate = $row['Start'];
        $StartDate = date('Y-m-d H:m', strtotime($StartDate));
        $Deadline = $row['Deadline'];
        $Deadline = date('Y-m-d H:m', strtotime($Deadline));
        $ID = $row['ID'];
        $SprintName = $row['ShortName'];

        $DatesIntervals[] = array('StartDate' => $StartDate, 'Deadline' => $Deadline, 'SprintName' => $SprintName, 'SprintID' => $ID);
    }

    foreach ($DatesIntervals as $DatesInterval) {
        $NoGo = false;
        $StartDate = $DatesInterval['StartDate'];
        $Deadline = $DatesInterval['Deadline'];
        $SprintName = $DatesInterval['SprintName'];
        $OverlapSprintID = $DatesInterval['SprintID'];

        if (($CurrentSprintStartDate >= $StartDate) && ($CurrentSprintStartDate <= $Deadline) && ($OverlapSprintID <> $SprintID)) {
            $NoGo = true;
        } else if (($CurrentSprintDeadline >= $StartDate) && ($CurrentSprintDeadline <= $Deadline) && ($OverlapSprintID <> $SprintID)) {
            $NoGo = true;
        } else {
            $NoGo = false;
            $SprintName = "";
        }

        if ($NoGo == true) {
            break;
        }
    }

    return $SprintName;
}

function createNewProject($ProjectManager, $ProjectStatus, $ProjectStart, $ProjectDeadline, $ProjectName, $ProjectDescription, $ProjectRelCustomer, $ProjectResponsible)
{
    global $conn;
    global $functions;

    $ProjectDeadline = convertFromDanishTimeFormat($ProjectDeadline);
    $ProjectStart = convertFromDanishTimeFormat($ProjectStart);
    $ProjectDescription = $ProjectDescription;
    $ProjectName = $ProjectName;

    // Query statement
    $sql = "INSERT INTO projects(Name, Start, Progress, Status, Deadline, Description, ProjectManager, RelatedCompanyID, ProjectResponsible, updated_by) 
            VALUES (?, ?, '0', ?, ?, ?, ?, ?, ?, ?);";

    // Parameters
    $params = [$ProjectName, $ProjectStart, $ProjectStatus, $ProjectDeadline, $ProjectDescription, $ProjectManager, $ProjectRelCustomer, $ProjectResponsible, $UserID];

    // Tables to lock for manipulation
    $tables = ["projects"];
    // Run the dmlQuery with decent transaction
    $result = $functions->dmlQuery($sql, $params, $tables);
    $lastId = $result['LastID'];
    return $lastId;
}

function createNewProjectTask($ProjectTaskEstimatedHours, $TaskResponsible, $ProjectTaskStatus, $ProjectTaskParent, $ProjectTaskStart, $ProjectTaskDeadline, $ProjectTaskEstimatedBudget, $ProjectTaskBudgetSpend, $ProjectTaskName, $ProjectTaskDescription, $ProjectID, $ProjectRelatedCategory, $ProjectTaskProgress, $ProjectPrivate)
{
    global $conn;
    global $functions;

    $ProjectTaskStart = convertFromDanishTimeFormat($ProjectTaskStart);
    $ProjectTaskDeadline = convertFromDanishTimeFormat($ProjectTaskDeadline);
    if(!$ProjectPrivate){
        $ProjectPrivate = 0;
    }
    // Query statement
    $sql = "INSERT INTO project_tasks(RelatedProject, TaskName, Description, Start, Deadline, Responsible, Status, ParentTask, EstimatedBudget, EstimatedHours, BudgetSpend, Progress, RelatedCategory, Private) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Parameters
    $params = [$ProjectID, $ProjectTaskName, $ProjectTaskDescription, $ProjectTaskStart, $ProjectTaskDeadline, $TaskResponsible, $ProjectTaskStatus, $ProjectTaskParent, $ProjectTaskEstimatedBudget, $ProjectTaskEstimatedHours, $ProjectTaskBudgetSpend, $ProjectTaskProgress, $ProjectRelatedCategory, $ProjectPrivate];

    // Tables to lock for manipulation
    $tables = ["project_tasks"];
    // Run the dmlQuery with decent transaction
    $result = $functions->dmlQuery($sql, $params, $tables);
    $lastId = $result['LastID'];
    return $lastId;
}

function updateProjectTask($ProjectID, $ProjectTaskID, $TaskResponsible, $ProjectTaskStatus, $ProjectTaskParent, $ProjectTaskStart, $ProjectTaskDeadline, $ProjectTaskEstimatedBudget, $ProjectTaskBudgetSpend, $ProjectTaskEstimatedHours, $ProjectTaskHoursSpend, $ProjectRelatedCategory, $ProjectTaskProgress, $ProjectTaskName, $ProjectTaskDescription, $ProjectPrivate)
{
    global $conn;
    global $functions;

    $UserID = $_SESSION['id'];
    $CompletedDate = "";

    $OldResponsible = getProjectTaskResponsible($ProjectTaskID);
    $ModuleID = "13";

    if (!empty($ProjectTaskDeadline)) {
        $ProjectTaskDeadline = convertFromDanishTimeFormat($ProjectTaskDeadline);
    } else {
        $ProjectTaskDeadline = NULL;
    }
    if (!empty($ProjectTaskStart)) {
        $ProjectTaskStart = convertFromDanishTimeFormat($ProjectTaskStart);
    } else {
        $ProjectTaskStart = NULL;
    }

    if ($ProjectTaskStatus == '7') {
        $CompletedDate = date('Y-m-d H:i:s');
    } else {
        $CompletedDate = NULL;
    }


    $sql = "UPDATE project_tasks "
            . "SET RelatedProject=?, ParentTask=?, TaskName=?, Description=?, Responsible=?, Status=?, EstimatedBudget=?, BudgetSpend=?, EstimatedHours=?, HoursSpend=?, Progress=?,"
            . "RelatedCategory=?, updated_by=?, Private=?, CompletedDate=?, Deadline=?, Start=? "
            . "WHERE project_tasks.ID = ?";

    $params = [$ProjectID,
        $ProjectTaskParent,
        $ProjectTaskName,
        $ProjectTaskDescription,
        $TaskResponsible,
        $ProjectTaskStatus,
        $ProjectTaskEstimatedBudget,
        $ProjectTaskBudgetSpend,
        $ProjectTaskEstimatedHours,
        $ProjectTaskHoursSpend,
        $ProjectTaskProgress,
        $ProjectRelatedCategory,
        $UserID,
        $ProjectPrivate,
        $CompletedDate,
        $ProjectTaskDeadline,
        $ProjectTaskStart,
        $ProjectTaskID];

    $tables = ["project_tasks"];
    $functions->dmlQuery($sql, $params, $tables);

    if(!empty($ProjectTaskDeadline)){
        updateKanbanTaskDeadline($ProjectTaskDeadline, $ProjectTaskID, $ModuleID);
    }

    if(!empty($TaskResponsible)){
        updateKanbanTaskResponsible($TaskResponsible, $OldResponsible, $ProjectTaskID, $ModuleID);
    }
}

function getRelatedProjectFromProjectTask($ProjectTaskID)
{

    global $conn;
    global $functions;

    $sql = "SELECT project_tasks.RelatedProject
            FROM project_tasks
            WHERE project_tasks.ID = $ProjectTaskID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ProjectID = $row['RelatedProject'];
    }

    return $ProjectID;
}

function getProjectTaskResponsible($ProjectTaskID)
{

    global $conn;
    global $functions;

    $sql = "SELECT project_tasks.Responsible
            FROM project_tasks
            WHERE project_tasks.ID = $ProjectTaskID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Responsible = $row['Responsible'];
    }

    return $Responsible;
}

function getRelatedTaskToProjectTask($ModuleID, $ElementID)
{
    global $conn;
    global $functions;

    $TaskIDArray = array();
    $sql = "SELECT ID FROM taskslist WHERE RelatedElementTypeID = '$ModuleID' AND RelatedElementID = '$ElementID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $TaskID = $row['ID'];
        array_push($TaskIDArray, $TaskID);
    }

    return $TaskIDArray;
}

function getRelatedProjectTaskFromProject($ProjectID)
{
    global $conn;
    global $functions;

    $ProjectTaskIDArray = array();
    $sql = "SELECT ID FROM project_tasks WHERE RelatedProject = '$ProjectID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ProjectTaskID = $row['ID'];
        array_push($ProjectTaskIDArray, $ProjectTaskID);
    }

    return $ProjectTaskIDArray;
}

function deleteRelatedTasks($TaskID)
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM taskslist WHERE ID = '$TaskID'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteRelatedTasksToProject($ModuleID, $ProjectID)
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM taskslist WHERE RelatedElementID = '$ProjectID' AND RelatedElementTypeID = '$ModuleID'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteRelatedTimeRegEntry($Task)
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM time_registrations WHERE RelatedTaskID = '$Task'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function createProjectBaselineLog($ProjectID)
{
    global $conn;
    global $functions;

    $sql = "INSERT INTO projects_baselines
                SELECT * FROM projects
                WHERE ID = '$ProjectID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateProject($ProjectName, $ProjectStatus, $ProjectRelCustomer, $ProjectDescription, $ProjectStart, $ProjectDeadline, $ProjectManager, $ProjectResponsible, $ProjectID)
{
    global $conn;
    global $functions;

    $UserID = $_SESSION['id'];
    $ModuleID = "6";

    $ProjectStart = convertFromDanishTimeFormat($ProjectStart);
    $ProjectDeadline = convertFromDanishTimeFormat($ProjectDeadline);
    $OldManager = getProjectManager($ProjectID);
    // Query statement
    $sql = "UPDATE projects 
            SET Name=?, Status=?, RelatedCompanyID=?, Start=?, Deadline=?,
            Description=?, ProjectManager=?, ProjectResponsible=?, updated_by=?
            WHERE ID=?";

    // Parameters
    $params = [$ProjectName, $ProjectStatus, $ProjectRelCustomer, $ProjectStart, $ProjectDeadline, $ProjectDescription, $ProjectManager, $ProjectResponsible, $UserID, $ProjectID];

    // Tables to lock for manipulation
    $tables = ["projects"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);

    // Update other related tasks
    updateKanbanTaskFromElement($ProjectID, '6', $ProjectStatus);
    $Deadline = getDeadlineForElementID($ProjectID, "6");
    updateKanbanTaskDeadline($Deadline, $ProjectID, "6");
    updateKanbanTaskResponsible($ProjectManager, $OldManager, $ProjectID, $ModuleID);
}

function deleteWorkFlowStep($StepID)
{
    global $conn;
    global $functions;

    // Query statement
     $sql = "DELETE FROM workflowsteps_template 
            WHERE workflowsteps_template.ID = ?;";

    // Parameters
    $params = [$StepID];

    // Tables to lock for manipulation
    $tables = ["workflowsteps_template"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function notgranted($CurrentPage)
{
    echo "<script type=\"text/javascript\">
        alert('You are not allowed access to this ressource $CurrentPage');
        </script>";
    echo "<script type=\"text/javascript\">
        window.location.href = 'index.php';
        </script>";
}

function notgrantedPage($ReturnPage, $Message)
{
    echo "<script type=\"text/javascript\">
        alert('$Message');
        </script>";
    echo "<script type=\"text/javascript\">
        window.location.href = '$ReturnPage';
        </script>";
}

function getElementName($RelatedModuleID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID, ShortElementName 
            FROM modules
            WHERE ID = $RelatedModuleID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['ShortElementName'];
    }
    return $value;
}

function getNotificationRecieverDetails($NotificationRecieverID)
{
    global $conn;
    global $functions;
    $Array = array();
    $sql =  "SELECT CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName, Email
            FROM users
            WHERE users.ID = $NotificationRecieverID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Array = ['FullName' => $row['FullName'], 'Email' => $row['Email']];
    }
    return $Array;
}

function getNotificationSystemURL()
{
    global $conn;
    global $functions;

    $sql = "SELECT SettingValue
            FROM settings
            WHERE settings.ID = 17";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['SettingValue'];
    }
    return $value;
}

function getNotificationElementViewPage($RelatedModuleID)
{
    global $conn;
    global $functions;

    $sql = sprintf("SELECT ElementViewPage
                FROM modules 
                WHERE ID = '%s'", $conn->real_escape_string($RelatedModuleID));

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['ElementViewPage'];
    }
    return $value;
}

function getNotificationsNumber($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT COUNT(notifications.ID) AS Numbers FROM notifications INNER JOIN
        users ON Users.ID = notifications.RelatedUserID
        WHERE notifications.RelatedUserID=$UserID AND notifications.ReadDate IS NULL;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = $result->fetch_assoc()) {
        $Numbers = $row["Numbers"];
    }
    mysqli_free_result($result);
    return $Numbers;
}

function getMessagessNumber($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT COUNT(messages.ID) AS Numbers FROM messages INNER JOIN
        users ON Users.ID = messages.ToUserID
        WHERE messages.ToUserID=$UserID AND messages.ReadDate IS NULL;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = $result->fetch_assoc()) {
        $Numbers = $row["Numbers"];
    }
    mysqli_free_result($result);
    return $Numbers;
}

function getMailTemplateSubject($TemplateID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Subject
            FROM mail_templates
            WHERE ID = '$TemplateID';";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = $result->fetch_assoc()) {
        $Subject = $row["Subject"];
    }
    mysqli_free_result($result);
    return $Subject;
}

function getMailTemplateContent($TemplateID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Content
                FROM mail_templates
                WHERE ID = '$TemplateID';";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = $result->fetch_assoc()) {
        $Content = $row["Content"];
    }
    mysqli_free_result($result);
    return $Content;
}

function createProjectBaseline($ProjectID)
{
    global $conn;
    global $functions;

    $sql1 = "INSERT INTO projects_baselines SELECT * FROM projects WHERE projects.ID = $ProjectID;";
    $result = mysqli_query($conn, $sql1) or die('Query fail: ' . mysqli_error($conn));

    $sql2 = "INSERT INTO project_tasks_baselines SELECT * FROM project_tasks WHERE project_tasks.RelatedProject = $ProjectID;";
    $result = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));
}

function createNewNotification($RelatedModuleID, $elementid, $NotificationRecieverID, $NotificationtypeID)
{
    global $conn;
    global $functions;
    //error_reporting(0);
    $ElementViewPage = getElementViewPage($RelatedModuleID) . $elementid;
    $elementname = getElementName($RelatedModuleID);
    $elementViewPage = getNotificationElementViewPage($RelatedModuleID);
    $URL = getNotificationSystemURL();
    $URL = $URL . $elementViewPage . $elementid;
    $RecieverInformationArray = getNotificationRecieverDetails($NotificationRecieverID);
    $FullName = $RecieverInformationArray['FullName'];
    $Email = $RecieverInformationArray['Email'];
    //Lets retrieve texts templates from notificationtype
    $sql = "SELECT ID, Subject, Body
                FROM notification_types
                WHERE ID = $NotificationtypeID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        //Lets substitute variables in templates with real text
        $Subject = $row['Subject'];
        $Body = $row['Body'];
        $Subject = str_replace("<:elementname:>", $elementname, $Subject);
        $Subject = str_replace("<:elementid:>", $elementid, $Subject);
        $Body = str_replace("<:recieverfullname:>", $FullName, $Body);
        $Body = str_replace("<:elementname:>", $elementname, $Body);
        $Body = str_replace("<:elementid:>", $elementid, $Body);
        $Body = str_replace("<:url:>", $URL, $Body);
        $sql = "INSERT INTO notifications(RelatedModuleID, RelatedTypeID, NotificationDate, NotificationSubject, NotificationBody, InternalUrl, RelatedUserID, RelatedElementID)
                    VALUES ($RelatedModuleID,$NotificationtypeID,NOW(),'$Subject','$Body','$ElementViewPage',$NotificationRecieverID,$elementid)";
        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function getAllDatabaseTriggerNames()
{
    global $conn;
    global $functions;
    global $dbname;

    $sql = "SELECT trigger_schema, trigger_name, action_statement
                FROM information_schema.triggers
                WHERE trigger_schema = '$dbname';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $TriggerName = $row['TRIGGER_NAME'];
        $TriggerArray[] = array('TriggerName' => $TriggerName);
    }

    return $TriggerArray;
}

function getAllActiveUsersUsernames()
{
    global $conn;
    global $functions;
    $TempUsersArray = array();

    $sql = "SELECT Username
            FROM users
            WHERE Active = 1;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Username = $row['Username'];        
        array_push($TempUsersArray, $Username);
    }

    return $TempUsersArray;
}

function getAllAdministratorsEmail()
{
    global $conn;
    global $functions;
    $TempUsersArray = array();

    $sql = "SELECT Email, CONCAT(users.Firstname,' ',users.Lastname) AS FullName
            FROM users
            LEFT JOIN usersgroups ON users.ID = usersgroups.UserID
            WHERE usersgroups.GroupID = 100001;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Email = $row['Email'];
        $FullName = $row['FullName'];
        $TempUsersArray[] = array("Email" => $Email, "Name" => $FullName);
    }

    return $TempUsersArray;
}

function deleteAllDatabaseTriggers()
{
    $NewTriggerArray = getAllDatabaseTriggerNames();

    foreach ($NewTriggerArray as $value) {
        $TriggerName = $value['TriggerName'];
        deleteDatabaseTrigger($TriggerName);
    }
}

function deleteDatabaseTrigger($TriggerName)
{
    global $conn;
    global $functions;
    global $dbname;

    $sql = "DROP TRIGGER $dbname.$TriggerName;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function sendITSMNotificationMailToUsers($MailTemplateID, $ITSMTypeID, $ITSMID, $Description, $Field, $PreValue, $NewValue){
    global $functions;

    $ResponsibleID = "";
    $ArrayITSMUsers = getITSMParticipantIDs($ITSMTypeID, $ITSMID);
    $PreValue = $functions->translate($PreValue);
    $NewValue = $functions->translate($NewValue);
    $ITSMTypeName = $ITSMTypeID;
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
    $ITSMSubject = $functions->getITSMFieldValue($ITSMID, "Subject", $ITSMTableName);

    $CustomerID = $functions->getITSMCustomer($ITSMTypeID, $ITSMID);

    if(!empty($CustomerID)){
        array_push($ArrayITSMUsers, $CustomerID);
    }

    $ResponsibleID = $functions->getITSMResponsible($ITSMID, $ITSMTypeID);

    if ($Field == "Responsible" && $NewValue !== "") {
        // Notify the new responsible person only if they are different from the current responsible person
        if ($ResponsibleID != $NewValue) {
            array_push($ArrayITSMUsers, $NewValue);
        }
    }

    $ArrayITSMUsers = array_unique($ArrayITSMUsers);

    foreach($ArrayITSMUsers as $value){
        $UserID = $value;
        $Email = getUserEmailFromID($UserID);
        $FullName = $functions->getUserFullName($UserID);
        $FirstName = getUserFirstName($UserID);
        $LastName = getUserLastName($UserID);
        $SystemName = $functions->getSettingValue(13);
        $SystemURL = $functions->getSettingValue(17);
        $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
        $ITSMTypeName = $functions->translate($ITSMTypeName);
        $MailCode = getMailCodeForModule($ITSMTypeID);
        $MailTemplateSubject = getMailTemplateSubject($MailTemplateID);
        $InternalViewLink = "<a href=\"$SystemURL/itsm-$ITSMTypeID-$ITSMID\">" . $functions->translate("Open") . " $ITSMTypeName: $ITSMID</a>";

        $MailTemplateSubject = str_replace("<:mailcode:>", $MailCode, $MailTemplateSubject);
        $MailTemplateSubject = str_replace("<:itsmnumber:>", $ITSMID, $MailTemplateSubject);
        $MailTemplateSubject = str_replace("<:systemname:>", $SystemName, $MailTemplateSubject);
        $MailTemplateSubject = str_replace("<:itsmtypename:>", $ITSMTypeName, $MailTemplateSubject);
        $MailTemplateSubject = str_replace("<:prepriority:>", $PreValue, $MailTemplateSubject);
        $MailTemplateSubject = str_replace("<:newpriority:>", $NewValue, $MailTemplateSubject);
        $MailTemplateSubject = str_replace("<:prestatusname:>", $PreValue, $MailTemplateSubject);
        $MailTemplateSubject = str_replace("<:newstatusname:>", $NewValue, $MailTemplateSubject);

        $MailTemplateContent = getMailTemplateContent($MailTemplateID);
        $MailTemplateContent = str_replace("<:firstname:>", $FirstName, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:lastname:>", $LastName, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:itsmnumber:>", $ITSMID, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:content:>", $Description, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:systemname:>", $SystemName, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:prepriority:>", $PreValue, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:newpriority:>", $NewValue, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:prestatusname:>", $PreValue, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:newstatusname:>", $NewValue, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:preslaname:>", $PreValue, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:newslaname:>", $NewValue, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:solution:>", $NewValue, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:systemurl:>", $SystemURL, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:itsmtypename:>", $ITSMTypeName, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:internalviewlink:>", $InternalViewLink, $MailTemplateContent);
        $MailTemplateContent = str_replace("<:subject:>", $ITSMSubject, $MailTemplateContent);

        sendMailToSinglePerson($Email, $FullName, $MailTemplateSubject, $MailTemplateContent);
    }
}

function getTableExcludeList(){
    //Define exclude list
    $ExcludeListDontTouch = array();
    array_push($ExcludeListDontTouch, "settings", "changelog", "changelog_types", "changepriorities", "changeslaagreements", "changes_comment_types", "changes_priorities", "changes_statuscodes", "changes_types", "chatrooms", "cmdb_cis", "cmdb_ci_default_fields", "cmdb_ci_fieldslist", "cmdb_ci_jsf03ynsyjuvoug");
    array_push($ExcludeListDontTouch, "cmdb_fieldslist_types", "colours", "companies", "countries", "debuglog", "designs", "forms_fieldslist_types", "incoming_mails", "invoicesstatuscodes", "invoices_paymentdeals", "invoices_services", "knowledge_categories", "knowledge_status", "languages");
    array_push($ExcludeListDontTouch, "loginpictures", "logtypes", "mail_templates", "modules", "news_categories", "notification_types", "passwordmanager_passwordtypes", "problemcommenttypes", "problempriorities", "problemslaagreements", "problemstatuscodes", "problemtypes", "projects_statuscodes");
    array_push($ExcludeListDontTouch, "releases", "requestcommenttypes", "requestpriorities", "requestslaagreements", "requeststatuscodes", "requesttypes", "roles", "settings", "settingstypes", "slaagreements", "sladaystoexlude", "sla_reaction_time_matrix", "standardanswers");
    array_push($ExcludeListDontTouch, "system_groups", "system_languages", "system_timezones", "taskslist_status", "ticketcommenttypes", "ticketpriorities", "ticketslaagreements", "ticketstatuscodes", "tickettypes", "users", "usersgroups", "usersroles", "users_quickmenu_choices");
    array_push($ExcludeListDontTouch, "usertypes", "week", "widgets", "workdays", "workflowstatus", "workflowsteps_template", "workflows_template", "myteamschanges", "myteamsrequests", "myteamstickets", "requestcustomers", "requestoverview","requestusers", "ticketcustomers", "ticketoverview","ticketoverview_small", "ticketusers");
    array_push($ExcludeListDontTouch, "itsm_default_fields", "itsm_fieldslist_types", "itsm_modules", "itsm_priorities", "itsm_slatimelines", "itsm_fieldslist", "itsm_sla_matrix", "itsm_statuscodes", "itsm_templates");

    return $ExcludeListDontTouch;
}

function resetInstallation()
{
    global $conn;
    global $functions;

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        $url = "https://";
    else
        $url = "http://";
    $url .= $_SERVER['HTTP_HOST'];
    $url .= $_SERVER['REQUEST_URI'];
    $curPageName = substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1);
    $url = str_replace($curPageName, "", $url);
    $url = rtrim($url, "/");
    $systemname = ucfirst(array_shift((explode('.', $_SERVER['HTTP_HOST']))));

    $sql = "TRUNCATE TABLE debuglog;";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $sql = "ALTER TABLE debuglog AUTO_INCREMENT = 1";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    deleteAllDatabaseTriggers();

    //Define exclude list
    $ExcludeListDontTouch = getTableExcludeList();

    //Get all tables
    $sql = "SHOW TABLES;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $TableName = $row[0];
        if (!in_array($TableName, $ExcludeListDontTouch)) {
            $removesql = "DELETE FROM $TableName;";
            mysqli_query($conn, $removesql) or die('Query fail: ' . mysqli_error($conn));
            $removesql = "ALTER TABLE $TableName AUTO_INCREMENT = 1";
            mysqli_query($conn, $removesql) or die('Query fail: ' . mysqli_error($conn));
        }
    }

    $ExcludeSpecifics = array();
    array_push($ExcludeSpecifics, "DELETE FROM cmdb_cis WHERE ID != 1;");
    array_push($ExcludeSpecifics, "ALTER TABLE cmdb_cis AUTO_INCREMENT = 1;");
    array_push($ExcludeSpecifics, "DELETE FROM cmdb_ci_fieldslist WHERE RelatedCITypeID != 1;");
    array_push($ExcludeSpecifics, "ALTER TABLE cmdb_ci_fieldslist AUTO_INCREMENT = 1;");
    array_push($ExcludeSpecifics, "DELETE FROM cmdb_ci_jsf03ynsyjuvoug WHERE ID != 1;");
    array_push($ExcludeSpecifics, "ALTER TABLE cmdb_ci_jsf03ynsyjuvoug AUTO_INCREMENT = 1;");
    array_push($ExcludeSpecifics, "DELETE FROM companies WHERE ID != 1;");
    array_push($ExcludeSpecifics, "ALTER TABLE companies AUTO_INCREMENT = 1;");
    array_push($ExcludeSpecifics, "DELETE FROM knowledge_categories WHERE ID > 17;");
    array_push($ExcludeSpecifics, "ALTER TABLE knowledge_categories AUTO_INCREMENT = 17;");
    array_push($ExcludeSpecifics, "DELETE FROM news_categories WHERE ID > 4;");
    array_push($ExcludeSpecifics, "ALTER TABLE news_categories AUTO_INCREMENT = 4;");
    array_push($ExcludeSpecifics, "DELETE FROM roles WHERE ID > 6;");
    array_push($ExcludeSpecifics, "ALTER TABLE roles AUTO_INCREMENT = 6;");
    array_push($ExcludeSpecifics, "DELETE FROM standardanswers WHERE ID > 8;");
    array_push($ExcludeSpecifics, "ALTER TABLE standardanswers AUTO_INCREMENT = 8;");
    array_push($ExcludeSpecifics, "DELETE FROM users WHERE ID != 1;");
    array_push($ExcludeSpecifics, "ALTER TABLE users AUTO_INCREMENT = 1;");
    array_push($ExcludeSpecifics, "DELETE FROM usersgroups WHERE UserID != 1;");
    array_push($ExcludeSpecifics, "DELETE FROM usersroles WHERE UserID != 1;");
    array_push($ExcludeSpecifics, "DELETE FROM workdays WHERE ID > 13;");
    array_push($ExcludeSpecifics, "ALTER TABLE workdays AUTO_INCREMENT = 13;");
    array_push($ExcludeSpecifics, "DELETE FROM workflowsteps_template WHERE RelatedWorkFlowID > 8;");
    array_push($ExcludeSpecifics, "DELETE FROM workflows_template WHERE ID != 8;");
    array_push($ExcludeSpecifics, "ALTER TABLE workflows_template AUTO_INCREMENT = 8;");
    array_push($ExcludeSpecifics, "DELETE FROM itsm_modules WHERE ID > 8;");
    array_push($ExcludeSpecifics, "ALTER TABLE itsm_modules AUTO_INCREMENT = 8;");
    array_push($ExcludeSpecifics, "DELETE FROM itsm_fieldslist WHERE RelatedTypeID NOT IN (1,2,3,4,5,6,7,8);");
    array_push($ExcludeSpecifics, "DELETE FROM itsm_templates WHERE ID NOT IN (1);");

    foreach ($ExcludeSpecifics as $sql){
        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }

    $sql = "UPDATE settings SET SettingValue = NULL WHERE settings.ID IN (36,37,40)";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "UPDATE settings SET SettingValue = 'send.one.com' WHERE settings.ID IN (28)";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $systemname = strtolower($systemname);
    $sql = "UPDATE settings SET SettingValue = '$systemname@practicle.dk' WHERE settings.ID IN (29)";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $randompassword = $functions->generateRandomString(30);
    $sql = "UPDATE settings SET SettingValue = '$randompassword' WHERE settings.ID IN (30)";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "UPDATE settings SET SettingValue = '$systemname@practicle.dk' WHERE settings.ID IN (39)";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "UPDATE settings SET SettingValue = '$randompassword' WHERE settings.ID IN (40)";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "UPDATE settings SET SettingValue = '500' WHERE settings.ID IN (23)";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "UPDATE settings SET SettingValue = '10' WHERE settings.ID IN (32)";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "UPDATE settings SET SettingValue = 0 WHERE settings.ID IN (34)";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "UPDATE settings SET SettingValue = 'https://hooks.slack.com/services/...' WHERE settings.ID = 35";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    //createDatabaseTriggers();

    resetITSMModules();
    resetCMDB();

    return true;

}

function resetDatabaseTableFromProd($TableName)
{
    global $conn;
    global $functions;

    echo "<code>";
    $sql = "SET foreign_key_checks = 0;";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "DROP TABLE IF EXISTS $TableName;";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $sql = "SHOW CREATE TABLE production.$TableName";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $CreateQuery = $row[1];
    }
    mysqli_query($conn, $CreateQuery) or die('Query fail: ' . mysqli_error($conn));
    $sql = "INSERT $TableName SELECT * FROM production.$TableName;";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "SET foreign_key_checks = 1;";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    echo "Dropped and created $TableName<br></code>";
}

function resetAllDatabaseTableFromProd()
{
    global $conn;
    global $functions;
    $exclude_list = array("companies", "users", "myteamschanges", "myteamstickets", "ticketcustomers", "ticketoverview", "ticketoverview_small", "ticketusers", "Toyota");

    $sql = "SELECT table_name FROM information_schema.tables
                WHERE table_schema = 'production';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $TableName = $row['table_name'];
        if (in_array("$TableName", $exclude_list)) {
        } else {
            resetDatabaseTableFromProd($TableName);
        }
    }
}

function upgradeBaseDatabaseTablesFromProd()
{
    global $conn;
    global $functions;

    resetDatabaseTableFromProd('changelog');
    resetDatabaseTableFromProd('changes_priorities');
    resetDatabaseTableFromProd('changelog_types');
    resetDatabaseTableFromProd('changeslaagreements');
    resetDatabaseTableFromProd('changes_comment_types');
    resetDatabaseTableFromProd('changes_statuscodes');
    resetDatabaseTableFromProd('ci_classes');
    resetDatabaseTableFromProd('ci_types');
    resetDatabaseTableFromProd('colours');
    resetDatabaseTableFromProd('designs');
    resetDatabaseTableFromProd('knowledge_status');
    resetDatabaseTableFromProd('languages');
    resetDatabaseTableFromProd('modules');
    resetDatabaseTableFromProd('news_categories');
    resetDatabaseTableFromProd('notification_types');
    resetDatabaseTableFromProd('passwordmanager_passwordtypes');
    resetDatabaseTableFromProd('problemstatuscodes');
    resetDatabaseTableFromProd('projects_statuscodes');
    resetDatabaseTableFromProd('releases');
    resetDatabaseTableFromProd('sla_reaction_time_matrix');
    resetDatabaseTableFromProd('settingstypes');
    resetDatabaseTableFromProd('system_languages');
    resetDatabaseTableFromProd('system_timezones');
    resetDatabaseTableFromProd('taskslist_status');
    resetDatabaseTableFromProd('ticketcommenttypes');
    resetDatabaseTableFromProd('ticketpriorities');
    resetDatabaseTableFromProd('slaagreements');
    resetDatabaseTableFromProd('ticketstatuscodes');
    resetDatabaseTableFromProd('tickettypes');
    resetDatabaseTableFromProd('usertypes');
    resetDatabaseTableFromProd('widgets');
}

function updateAllWebFilesFromProd()
{
    exec("cp -dfr ./inc/dbconnection.php ./inc/dbconnection_backup.php");
    $SourceFolder = "../../production/*";
    $DestinationFolder = "./";
    exec("cp -dfr $SourceFolder $DestinationFolder");
}

function installNewSite($sitename)
{
    global $conn;
    global $functions;
    $sql = "USE $sitename";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $sql = file_get_contents('./watchers/development_copy.sql');
    if ($conn->multi_query($sql)) {
        echo "success";
    } else {
        echo "error";
    }
}

function getBackgroundUser()
{
    global $conn;
    global $functions;
    $BackgroundValue = "";
    $UserID = $_SESSION["id"];
    $sql = "SELECT SettingValue 
                FROM user_settings
                WHERE RelatedSettingID = 22 AND RelatedUserID = $UserID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $BackgroundValue = $row['SettingValue'];
        if (empty($BackgroundValue)) {
            $BackgroundValue = getBackgroundDefault();
        }
    }
    return $BackgroundValue;
}

function getBackgroundDefault()
{
    global $conn;
    global $functions;
    $BackgroundDefaultValue = "";
    $UserID = $_SESSION["id"];
    $sql = "SELECT SettingValue 
                FROM settings
                WHERE ID = 22";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $BackgroundDefaultValue = $row['SettingValue'];
    }
    return $BackgroundDefaultValue;
}

function incrementDocumentVersion($ITSMTypeID, $ITSMID)
{
    global $conn;
    global $functions;

    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

    // Query statement
    $sql = "UPDATE $ITSMTableName SET Version = Version + 1 WHERE ID = ?;";

    // Parameters
    $params = [$ITSMID];

    // Tables to lock for manipulation
    $tables = ["$ITSMTableName"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);

    // Retrieve the new version number
    $selectSql = "SELECT Version FROM $ITSMTableName WHERE ID = $ITSMID;";
    $result = mysqli_query($conn, $selectSql) or die('Query fail: ' . mysqli_error($conn));

    // Check if the query was successful and a row was returned
    if ($row = mysqli_fetch_assoc($result)) {
        // Return the new version number
        return $row['Version'];
    } else {
        // Return false or null if the version number could not be retrieved
        return false;
    }
}

function getNewUnhandledElements($ID)
{
    global $conn;
    global $functions;

    $Number = 0;
    $ITSMTableName = $functions->getITSMTableName($ID);
    $ModuleType = $functions->getITSMModuleType($ID);

    if ($ModuleType !== "1") {
        return $Number;
    }

    // Define the columns to check
    $columns = ['Status', 'Responsible', 'Team'];

    // Check if the necessary columns exist
    if (!checkColumnsExist($ITSMTableName, $columns)) {
        return $Number;
    }

    // Construct the main SQL query
    $sql = "SELECT COUNT(*) AS Number
            FROM $ITSMTableName
            WHERE Status = 1 AND Responsible IS NULL AND Team IS NULL";

    // Use try-catch style error handling
    try {
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result) {
                $row = mysqli_fetch_assoc($result);
                if ($row) {
                    $Number = $row["Number"];
                }
            } else {
                throw new Exception("Query failed: " . mysqli_error($conn));
            }

            mysqli_stmt_close($stmt);
        } else {
            throw new Exception("Statement preparation failed: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        // Log the error and ensure $Number is returned as an empty string
        error_log($e->getMessage());
        $Number = 0; // Ensure $Number is an empty string in case of error
    }

    return $Number;
}

function checkColumnsExist($tableName, $columns)
{
    global $conn;
    global $functions;

    foreach ($columns as $column) {
        $sql = "SELECT 1 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $tableName, $column);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 0) {
                // Column does not exist
                mysqli_stmt_close($stmt);
                return false;
            }

            mysqli_stmt_close($stmt);
        } else {
            // Statement preparation failed
            error_log("Column check preparation failed: " . mysqli_error($conn));
            return false;
        }
    }

    return true;
}


function getModuleIcon($ModuleID)
{
    global $conn;
    global $functions;

    $sql = "SELECT itsm_modules.TypeIcon
            FROM itsm_modules
            WHERE ID = $ModuleID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $TypeIcon = $row["TypeIcon"];
    }
    return $TypeIcon;
}

function getNumberOfAllowedEmployees()
{
    global $conn;
    global $functions;

    $sql = "SELECT SettingValue FROM settings WHERE ID = 32";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $NumberOfAllowedEmployees = $row['SettingValue'];
    }
    return $NumberOfAllowedEmployees;
}

function getNumberOfEmployees()
{
    global $conn;
    global $functions;

    $sql = "SELECT COUNT(ID) AS NumberOfUsers FROM users WHERE RelatedUserTypeID = 1 AND active = 1";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $NumberOfEmployees = $row['NumberOfUsers'];
    }
    return $NumberOfEmployees;
}

function getNumberOfAllowedCompanies()
{
    global $conn;
    global $functions;

    $sql = "SELECT SettingValue FROM settings WHERE ID = 33";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['SettingValue'];
    }
    return $Value;
}

function getNumberOfCompanies()
{
    global $conn;
    global $functions;

    $sql = "SELECT COUNT(ID) AS NumberOfCompanies FROM companies WHERE active = 1";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['NumberOfCompanies'];
    }
    return $Value;
}

function getRelatedModuleIconName($RelatedElementTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TypeIcon
                FROM modules
                WHERE ID = $RelatedElementTypeID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ModuleIconName = $row['TypeIcon'];
    }
    return $ModuleIconName;
}

function getControlCode($ControlString)
{
    global $conn;
    global $functions;
    if (empty($ControlString)) {
        $redirectpage = "index.php";
        echo $redirectpagego = "<meta http-equiv='refresh' content='1';url='$redirectpage'>";
    } else {
        $sql = "SELECT ControlString
                    FROM changepassword
                    WHERE changepassword.ControlString = '$ControlString'";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_array($result)) {
            if (empty($row['ControlString'])) {
                echo "Code doesnt exist, please contact your administrator";
                $redirectpage = "index.php";
                echo $redirectpagego = "<meta http-equiv='refresh' content='1';url='$redirectpage'>";
            } else {
                $ControlString = $row['ControlString'];
            }
            return $ControlString;
        }
    }
}

function isPasswordChangeSubmitted($UserID)
{
    global $conn;
    global $functions;
    $Value = "";

    $sql = "SELECT UserID
            FROM changepassword
            WHERE changepassword.UserID = $UserID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        if (empty($row['UserID'])) {
            $Value = "";
        } else {
            $Value = "disabled";
        }
    }
    return $Value;
}

function removePasswordSubmit($UserID)
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM changepassword
            WHERE changepassword.UserID = $UserID";
    
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getTeamEmails($TeamID)
{
    global $conn;
    global $functions;

    $Array = array();

    $sql = "SELECT Email
                FROM usersteams 
                LEFT JOIN users ON usersteams.UserID = users.ID 
                WHERE usersteams.TeamID = '$TeamID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Email = $row['Email'];
        array_push($Array, $Email);
    }
    return $Array;
}

function sendMail($To, $ToName, $From, $FromName, $Subject, $Content)
{
    global $conn;
    global $functions;
    include_once "../inc/dbconnection.php";
    include_once "../vendor/autoload.php";

    $sql = "SELECT ID, SettingValue
                    FROM settings
                    WHERE ID IN (13,26,27,28,29,30)";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        if ($row['ID'] == '13') {
            $FromName = $row['SettingValue'];
        }
        if ($row['ID'] == '26') {
            $Port = $row['SettingValue'];
        }
        if ($row['ID'] == '27') {
            $SMTPSecure = $row['SettingValue'];
        }
        if ($row['ID'] == '28') {
            $Host = $row['SettingValue'];
        }
        if ($row['ID'] == '29') {
            $Username = $row['SettingValue'];
            $From = $row['SettingValue'];
        }
        if ($row['ID'] == '30') {
            $Password = $row['SettingValue'];
        }
    }

    $mail = new PHPMailer(true);
    $mail->IsSMTP(true);
    $mail->CharSet        = "UTF-8";
    $mail->Encoding = 'quoted-printable';
    $mail->SMTPDebug     = 0;
    $mail->Port         = $Port;
    $mail->SMTPSecure     = "$SMTPSecure";
    $mail->SMTPAuth     = true;
    $mail->Mailer         = "smtp";
    $mail->Host          = "$Host";
    $mail->Username      = "$Username";
    $mail->Password       = "$Password";
    $mail->IsHTML(true);

    foreach ($To as $Reciever) {
        $RecieverMail = $Reciever;
        $mail->addBCC($RecieverMail);
        //$mail->AddAddress($Reciever[0], $Reciever[1]);
    }
    //$mail->AddAddress($To, $ToName);
    $mail->SetFrom($From, $FromName);

    //$mail->AddCC("cc-recipient-email@domain", "cc-recipient-name");
    $mail->Subject = $Subject;
    $content = $Content;

    $mail->MsgHTML($content);
    if (!$mail->Send()) {
        //echo "Error while sending Email.";
        //var_dump($mail);
    } else {
        //echo "Email sent successfully";
    }
}

function sendMailBPage($To, $ToName, $Subject, $Content)
{

    $From = "noreply@practicle.dk";
    $FromName = "Practicle";

    require './classes/phpmailer/src/Exception.php';
    require './classes/phpmailer/src/PHPMailer.php';
    require './classes/phpmailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->IsSMTP();
    $mail->CharSet        = "UTF-8";
    $mail->SMTPDebug     = 0;
    $mail->Port         = 465;
    $mail->SMTPSecure     = "ssl";
    $mail->SMTPAuth     = true;
    $mail->Mailer         = "smtp";
    $mail->Host          = "send.one.com";
    $mail->Username      = "noreply@practicle.dk";
    $mail->Password       = "U!4dL0Sd4nAS5cSR49ve";

    $mail->IsHTML(true);
    $mail->AddAddress($To, $ToName);
    $mail->SetFrom($From, $FromName);

    //$mail->AddCC("cc-recipient-email@domain", "cc-recipient-name");
    $mail->addBcc("claus@practicle.dk", "Claus Jørgensen");
    $mail->Subject = $Subject;
    $content = $Content;

    $mail->MsgHTML($content);

    if (!$mail->Send()) {
        echo "Error while sending Email.";
        //var_dump($mail);
    } else {
        echo "Email sent successfully";
    }

    //Clear all addresses and attachments for the next iteration
    $mail->clearAddresses();
    $mail->clearAttachments();
}

function getUploadFolderSize($dir)
{
    $os = strtoupper(substr(PHP_OS, 0, 3));
    $SizeLeft = 0;

    $obj = null;

    // If on a Unix Host (Linux, Mac OS)
    if ($os !== 'WIN') {
        $io = popen('/usr/bin/du -sk ' . $dir, 'r');
        $size = fgets($io, 4096);
        $size = substr($size, 0, strpos($size, "\t"));
        $SizeLeft = round(intval($size) / 1000, 0);
        pclose($io);
        return intval($SizeLeft);
    }
    // If on a Windows Host (WIN32, WINNT, Windows)
    if ($os === 'WIN' && extension_loaded('com_dotnet')) {
        $obj = new COM('scripting.filesystemobject') or die("Could not initialise Object.");;
        if (is_object($obj)) {
            $ref = $obj->getfolder($dir);
            $SizeLeft = round(intval($ref->size) / 1000000, 0);
            return intval($SizeLeft);
        } else {
            echo 'can not create object';
        }
    }
}

function getLimitUploadSpace()
{
    global $conn;
    global $functions;
    $AllowedSize = 0;

    $sql = "SELECT SettingValue
                FROM settings
                WHERE ID = 23";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $AllowedSize = $row['SettingValue'];
    }
    return intval($AllowedSize);
}

function isUploadAllowed($dir)
{
    $result = "";
    $Limit = getLimitUploadSpace($dir);
    $UsedSpace = getUploadFolderSize($dir);

    if ($UsedSpace >= $Limit) {
        $result = "No";
    } else {
        $result = "Yes";
    }
    return $result;
}

function getFreeSpaceInUpload($dir)
{
    $Allowed = getLimitUploadSpace($dir);
    $Used = getUploadFolderSize($dir);
    $FreeSpace = $Allowed - $Used;
    return intval($FreeSpace);
}

function getCurrentDBSize()
{
    global $conn;
    global $functions;
    $sql = 'SELECT DATABASE()';
    $sqlresult = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($sqlresult);

    $active_db = $row[0];

    $sql = "SELECT table_schema '$active_db', SUM( data_length + index_length) / 1024 / 1024 'db_size_in_mb' FROM information_schema.TABLES WHERE table_schema='$active_db' GROUP BY table_schema ;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $data = mysqli_fetch_array($result);
    return intval($data);
}

function getLimitDBSIze()
{
    global $conn;
    global $functions;
    $AllowedSize = 0;

    $sql = "SELECT SettingValue
                FROM settings
                WHERE ID = 24";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $AllowedSize = $row['SettingValue'];
    }
    return intval($AllowedSize);
}

function getFreeDBSize()
{
    $Allowed = getLimitDBSIze();
    $Used = getCurrentDBSize();
    $FreeSpace = $Allowed - $Used;
    return intval($FreeSpace);
}

function isDBFull()
{
    $result = "";
    $Limit = getLimitDBSIze();
    $UsedSpace = getCurrentDBSize();

    if ($UsedSpace >= $Limit) {
        $result = "Yes";
    } else {
        $result = "No";
    }
    return $result;
}

function insertClipboardElementInTempDB($Elementtype, $RandomName)
{
    global $conn;
    global $functions;

    $sql = "INSERT INTO clipboardstemp (ID,ElementType,RandomName) VALUES ($Elementtype,$RandomName)";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    return $RandomName;
}

function createhtmlLinks($text)
{
    $text = preg_replace('/(http[s]{0,1}\:\/\/\S{4,})\s{0,}/ims', '<a href="$1" target="_blank">$1</a> ', $text);
    return $text;
}

function getSlackActivated()
{

    global $conn;
    global $functions;

    $sql = "SELECT SettingValue FROM settings WHERE ID = 34";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['SettingValue'];
    }

    return $Value;
}

function getSlackWebhookNewProblem()
{

    global $conn;
    global $functions;

    $sql = "SELECT SettingValue FROM settings WHERE ID = 38";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['SettingValue'];
    }

    return $Value;
}

function getRelatedProjectManager($ProjectTaskID)
{
    global $conn;
    global $functions;

    $sql = "SELECT projects.ProjectManager
                FROM project_tasks
                LEFT JOIN projects ON project_tasks.RelatedProject = Projects.ID
                WHERE project_tasks.ID = $ProjectTaskID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['ProjectManager'];
    }

    return $Value;
}

function stripStringFromScrollbarTags($String)
{
    $ReplaceString = "<div class=\"ps-scrollbar-x-rail\" style=\"left: 0px; bottom: 0px;\">";
    $String = str_replace($ReplaceString, "", $String);
    $ReplaceString = "<div class=\"ps-scrollbar-x\" tabindex=\"0\" style=\"left: 0px; width: 0px;\">";
    $String = str_replace($ReplaceString, "", $String);
    $ReplaceString = "<div class=\"ps-scrollbar-y-rail\" style=\"top: 0px; right: 0px;\">";
    $String = str_replace($ReplaceString, "", $String);
    $ReplaceString = "<div class=\"ps-scrollbar-y\" tabindex=\"0\" style=\"top: 0px; height: 0px;\">";
    $String = str_replace($ReplaceString, "", $String);
    $ReplaceString = "<div class=\"ps-scrollbar-y\" tabindex=\"0\" style=\"top: 0px; height: 0px;\">";
    $String = str_replace($ReplaceString, "", $String);
    $ReplaceString = "<div class=\"ps-scrollbar-x-rail\" style=\"left: 0px; bottom: 0px;\">";
    $String = str_replace($ReplaceString, "", $String);
    $ReplaceString = "<div class=\"ps-scrollbar-x\" tabindex=\"0\" style=\"left: 0px; width: 0px;\">";
    $String = str_replace($ReplaceString, "", $String);
    $ReplaceString = "</div></div></div></div>";
    $String = str_replace($ReplaceString, "", $String);

    return $String;
}

function getNumberOfDocApprovalActions($UserID)
{
    global $conn;
    global $functions;
    $Antal = 0;
    $sql = "SELECT COUNT(knowledge_documents.ID) AS Antal
                FROM knowledge_documents
                INNER JOIN knowledge_status ON knowledge_documents.RelatedStatusID = knowledge_status.ID
                WHERE (knowledge_documents.RelatedReviewerID = $UserID OR knowledge_documents.RelatedApproverID = $UserID) AND knowledge_documents.RelatedStatusID IN (3,4);";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Antal = $row['Antal'];
    }

    return $Antal;
}

function getNumberOfDocExpireActions($UserID)
{
    global $conn;
    global $functions;

    $ClosedStatus = $functions->getITSMClosedStatus(7);

    $Antal = 0;
    $sql = "SELECT COUNT(itsm_knowledge.ID) AS Antal
            FROM itsm_knowledge
            INNER JOIN itsm_statuscodes ON itsm_knowledge.Status = itsm_statuscodes.ID
            WHERE itsm_knowledge.Responsible = ? AND (itsm_knowledge.ExpirationDate <= CURDATE())  AND itsm_knowledge.Status NOT IN (".implode(",",$ClosedStatus).");";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $UserID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_array($result)) {
        $Antal = $row['Antal'];
    }

    return $Antal;
}

function getNumberOfCMDBExpired($UserID)
{
    global $conn;
    global $functions;
    $CMDBTypesArray = getCMDBTypes();
    $TempResultArray = array();

    foreach ($CMDBTypesArray as $Type) {
        $CMDBTypeID = $Type['CMDBTypeID'];
        $Name = $Type['Name'];
        $TableName = $Type['TableName'];
        $FieldName = $Type['FieldName'];

        $TempResultArray = array_merge($TempResultArray, getExpiredForCMDBType($CMDBTypeID, $Name, $TableName, $FieldName, $UserID));
    }
    return count($TempResultArray);
}

function getNumberOfChangesActions($UserID)
{
    global $conn;
    global $functions;
    $Antal = 0;

    $ClosedStatus = $functions->getITSMClosedStatus(3);

    $sql = "SELECT COUNT(itsm_changes.ID) AS Antal
            FROM itsm_changes
            WHERE itsm_changes.Authorizer = ? AND itsm_changes.Status NOT IN (" . implode(",", $ClosedStatus) . ");";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $UserID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $Antal);

    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    return $Antal;
}

function getNumberOfCIActions($UserID)
{
    global $conn;
    global $functions;
    $Antal = 0;

    // Prepare the SQL statement
    $sql = "SELECT COUNT(ID) AS Antal
            FROM cis
            WHERE (Expires <= CURDATE()) AND RelatedUserID = ?";

    // Initialize the prepared statement
    if ($stmt = mysqli_prepare($conn, $sql)) {

        // Bind the parameter to the prepared statement
        mysqli_stmt_bind_param($stmt, "i", $UserID); // "i" means the parameter is an integer

        // Execute the prepared statement
        mysqli_stmt_execute($stmt);

        // Get the result
        $result = mysqli_stmt_get_result($stmt);

        // Fetch the result and assign it to $Antal
        if ($row = mysqli_fetch_assoc($result)) {
            $Antal = $row['Antal'];
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } else {
        die('Prepare failed: ' . mysqli_error($conn));
    }

    return $Antal;
}

function getUserActiveStatus($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Active
            FROM users
            WHERE users.ID = $UserID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Active = $row['Active'];
    }
    return $Active;
}

function getUsersTheme($UserID)
{
    global $conn;
    global $functions;
    $Theme = "dark";
    //Lets check if it exists allready
    $sql = "SELECT SettingValue
                FROM user_settings
                WHERE RelatedUserID = $UserID AND RelatedSettingID = 20";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $numrows = mysqli_num_rows($result);

    if ($numrows > 0) {
        while ($row = mysqli_fetch_array($result)) {
            $Theme = $row['SettingValue'];
        }
    }
    return $Theme;
}

function createFormsTable($FormID, $FormsTableName)
{
    global $conn;
    global $functions;

    // Query statement
    $sql = "INSERT INTO forms_tables(RelatedFormID, TableName) VALUES (?,?)";

    // Parameters
    $params = [$FormID,$FormsTableName];

    // Tables to lock for manipulation
    $tables = ["forms_tables"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function createDatabaseTable($FormsTableName)
{
    global $conn;
    global $functions;

    // Query statement
    $sql = "CREATE TABLE $FormsTableName (ID INT NOT NULL AUTO_INCREMENT PRIMARY KEY)ENGINE=INNODB";

    // Parameters
    $params = [];

    // Tables to lock for manipulation
    $tables = ["$FormsTableName"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function deleteFormsTable($TableID)
{
    global $conn;
    global $functions;

    // Query statement
    $sql = "DELETE FROM forms_tables WHERE ID = ?";

    // Parameters
    $params = [$TableID];

    // Tables to lock for manipulation
    $tables = ["forms_tables"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function deleteDatabaseTable($TableName)
{
    global $conn;
    global $functions;

    // Query statement
    $sql = "DROP TABLE $TableName;";

    // Parameters
    $params = [];

    // Tables to lock for manipulation
    $tables = [];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function getTableNameFromID($TableID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TableName 
                FROM forms_tables
                WHERE forms_tables.ID = $TableID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $TableName = $row['TableName'];
    }
    return $TableName;
}

function getTableNameFromFormID($FormID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TableName 
            FROM forms
            WHERE forms.ID = $FormID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $TableName = $row['TableName'];
    }
    return $TableName;
}

function getRelatedWorkFlowID($FormID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedWorkFlow 
            FROM forms
            WHERE forms.ID = $FormID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $RelatedWorkFlow = $row['RelatedWorkFlow'];
    }
    return $RelatedWorkFlow;
}

function getFormColumnNameForTableView($FormID, $FieldName)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldLabel 
            FROM forms_fieldslist
            WHERE RelatedFormID = '$FormID' AND FieldName = '$FieldName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Label = $row['FieldLabel'];
    }
    return $Label;
}

function getCIColumnNameForTableView($CIID, $FieldName)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldLabel
            FROM cmdb_ci_fieldslist
            WHERE RelatedCITypeID = '$CIID' AND FieldName = '$FieldName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Label = $row['FieldLabel'];
    }
    return $Label;
}

function getCIFieldPreValue($CIID, $CITableName, $Field)
{
    global $conn;
    global $functions;

    $sql = "SELECT $Field
            FROM $CITableName
            WHERE ID = ?";

    // Initialize a prepared statement
    $stmt = mysqli_prepare($conn, $sql);
    
    // Bind the parameter
    mysqli_stmt_bind_param($stmt, "i", $CIID);

    // Execute the statement
    mysqli_stmt_execute($stmt);

    // Bind result variables
    mysqli_stmt_bind_result($stmt, $Value);

    // Fetch value
    mysqli_stmt_fetch($stmt);

    // Close statement
    mysqli_stmt_close($stmt);

    return $Value;
}

function getITSMFieldPreValue($ITSMID, $ITSMTableName, $Field)
{
    global $conn;
    global $functions; // Assuming $conn is your mysqli connection object

    // Prepare the SQL statement with placeholders
    $sql = "SELECT $Field
            FROM $ITSMTableName
            WHERE ID = ?";

    // Initialize a prepared statement
    $stmt = mysqli_prepare($conn, $sql);
    
    // Bind the parameter
    mysqli_stmt_bind_param($stmt, "i", $ITSMID);

    // Execute the statement
    mysqli_stmt_execute($stmt);

    // Bind result variables
    mysqli_stmt_bind_result($stmt, $Value);

    // Fetch value
    mysqli_stmt_fetch($stmt);

    // Close statement
    mysqli_stmt_close($stmt);

    return $Value;
}


function getFormsFieldPreValue($ITSMID, $formsTableName, $Field)
{
    global $conn;
    global $functions;

    $sql = "SELECT $Field
            FROM $formsTableName
            WHERE RelatedRequestID = '$ITSMID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row[0];
    }

    return $Value;
}

function getCIFieldLabelFromFieldName($CITypeID, $Field)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldLabel
            FROM cmdb_ci_fieldslist
            WHERE FieldName = '$Field' AND RelatedCITypeID = '$CITypeID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["FieldLabel"];
    }
    return $Value;
}

function getITSMFieldLabelFromFieldName($ITSMTypeID, $Field)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldLabel
            FROM itsm_fieldslist
            WHERE FieldName = '$Field' AND RelatedTypeID = '$ITSMTypeID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["FieldLabel"];
    }
    return $Value;
}

function getFormFieldLabelFromFieldName($formID, $Field)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldLabel
            FROM forms_fieldslist
            WHERE FieldName = '$Field' AND RelatedFormID = '$formID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["FieldLabel"];
    }
    return $Value;
}

function getTableIDFromFieldID($FieldID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedTableID 
                FROM forms_tables_fieldslist
                WHERE forms_tables_fieldslist.ID = $FieldID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $FieldID = $row['FieldID'];
    }
    return $FieldID;
}

function getFormIDFromTableID($formtableid)
{
    global $conn;
    global $functions;
    $RelatedFormID = "";

    $sql = "SELECT RelatedFormID 
            FROM forms_tables
            WHERE forms_tables.ID = $formtableid";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $RelatedFormID = $row['RelatedFormID'];
    }
    return $RelatedFormID;
}

function getFormTableEntryID($formsTableName, $ITSMID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID 
            FROM $formsTableName
            WHERE RelatedRequestID = $ITSMID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['ID'];
    }
    return $value;
}

function getFormModuleID($FormID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedModuleID	 
            FROM forms
            WHERE forms.ID = $FormID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $RelatedModuleID = $row['RelatedModuleID'];
    }
    return $RelatedModuleID;
}

function createFormsTableField($FieldLabel, $FieldName, $FieldType, $TableID, $FieldLength, $FieldWidth)
{
    global $conn;
    global $functions;

    // Query statement
    $sql = "INSERT INTO forms_tables_fieldslist(RelatedTableID, FieldName, Label, FieldType, FieldLength, FieldWidth) VALUES (?,?,?,?,?,?);";

    // Parameters
    $params = [$TableID,$FieldName,$FieldLabel,$FieldType,$FieldLength,$FieldWidth];

    // Tables to lock for manipulation
    $tables = ["forms_tables_fieldslist"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function createDatabaseTableField($fieldlabel, $fieldname, $fieldtype, $tableid, $fieldlength)
{
    global $conn;
    global $functions;

    $TableName = getTableNameFromID($tableid);
    $DBFieldDef = getFieldDBFieldDef($fieldtype);
    $fieldname = strtolower("$fieldname");

    // Query statement
    $sql = "ALTER TABLE $TableName ADD COLUMN $fieldname $DBFieldDef($fieldlength);";

    // Parameters
    $params = [];

    // Tables to lock for manipulation
    $tables = ["$TableName"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function getFieldDBFieldDef($FieldTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT DBFieldDef 
                FROM forms_tables_fieldslist_types
                WHERE forms_tables_fieldslist_types.ID = $FieldTypeID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $DBFieldDef = $row['DBFieldDef'];
    }
    return $DBFieldDef;
}

function deleteFormsTableField($FieldID)
{
    global $conn;
    global $functions;

    // Query statement
    $sql = "DELETE FROM forms_tables_fieldslist WHERE ID = ?:";

    // Parameters
    $params = [$FieldID];

    // Tables to lock for manipulation
    $tables = ["forms_tables_fieldslist"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function deleteDatabaseTableField($TableID, $FieldName)
{
    global $conn;
    global $functions;

    $TableName = strtolower(getTableNameFromID($TableID));
    $FieldName = strtolower($FieldName);

    // Query statement
    $sql = "ALTER TABLE $TableName DROP COLUMN $FieldName;";

    // Parameters
    $params = [];

    // Tables to lock for manipulation
    $tables = ["$TableName"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function getCompanyName($CompanyID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Companyname 
            FROM companies
            WHERE companies.ID = $CompanyID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $CompanyName = $row['Companyname'];
    }
    return $CompanyName;
}

function getCompanyID($CompanyName)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID
                FROM companies
                WHERE companies.Companyname = '$CompanyName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $CompanyID = $row['Companyname'];
    }
    return $CompanyID;
}

function getUserRelatedCompanyID($UsersID)
{
    global $conn;
    global $functions;

    $sql = "SELECT CompanyID
            FROM users
            WHERE users.ID = '$UsersID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $CompanyID = $row['CompanyID'];
    }
    return $CompanyID;
}

function getUserTypeName($UserTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TypeName 
                FROM usertypes
                WHERE usertypes.ID = $UserTypeID AND Active = 1";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $TypeName = $row['TypeName'];
    }
    return $TypeName;
}

function getRelatedElementName($ElementID)
{
    global $conn;
    global $functions;
    $ShortElementName = "";

    $sql = "SELECT ShortElementName 
                FROM modules
                WHERE modules.ID = $ElementID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ShortElementName = str_replace(' ', '', strtolower($row['ShortElementName']));
    }
    return $ShortElementName;
}

function getProjectName($ProjectID)
{
    global $conn;
    global $functions;
    $ProjectName = "";

    $sql = "SELECT Name 
                FROM Projects
                WHERE ID = $ProjectID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ProjectName = $row['Name'];
    }
    return $ProjectName;
}

function getProjectTaskName($ProjectTaskID)
{
    global $conn;
    global $functions;
    $ProjectName = "";

    $sql = "SELECT TaskName 
            FROM project_tasks
            WHERE ID = $ProjectTaskID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $TaskName = $row['TaskName'];
    }
    return $TaskName;
}

function getProjectManager($ProjectID)
{
    global $conn;
    global $functions;
    $ProjectManager = "";

    $sql = "SELECT ProjectManager 
                FROM Projects
                WHERE ID = $ProjectID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ProjectManager = $row['ProjectManager'];
    }
    return $ProjectManager;
}

function getProjectResponsible($ProjectID)
{
    global $conn;
    global $functions;
    $ProjectResponsible = "";

    $sql = "SELECT ProjectResponsible
                FROM Projects
                WHERE ID = $ProjectID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ProjectResponsible = $row['ProjectResponsible'];
    }
    return $ProjectResponsible;
}

function getDefaultLanguage()
{
    global $conn;
    global $functions;
    $DefaultLanguage = "";

    $sql = "SELECT SettingValue 
                FROM settings
                WHERE settings.ID=10";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $DefaultLanguage = $row['SettingValue'];
    }
    return $DefaultLanguage;
}

function getDefaultTimeZone()
{
    global $conn;
    global $functions;
    $DefaultTimeZone = "";

    $sql = "SELECT SettingValue 
                FROM settings
                WHERE settings.ID=11";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $DefaultTimeZone = $row['SettingValue'];
    }
    return $DefaultTimeZone;
}

function updatePinForUser($UserID, $Pin)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE users SET Pin = ? WHERE ID = ?";

    // Parameters for the query
    $params = [$Pin, $UserID];

    // Tables to lock
    $tables = ["users"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the update was successful
    if ($result['LastID'] >= 0) { // Affected rows will also reflect as LastID
        return true;
    } else {
        $functions->errorlog("Failed to update Pin for UserID: $UserID", "updatePinForUser");
        return false;
    }
}

function getModuleNameByID($ElementTypeID)
{
    global $conn;
    global $functions;
    $sql = "SELECT ShortElementName
                FROM modules
                WHERE ID = $ElementTypeID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ModuleName = $row['ShortElementName'];
    }
    return $ModuleName;
}

function getSessionInDB($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT userid 
            FROM Sessions
            WHERE userid = $UserID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $userid = $row['userid'];
    }
    return $userid;
}

function getLockStatus($UserID)
{
    global $conn;
    global $functions;
    $locked = "";

    $sql = "SELECT locked
            FROM sessions
            WHERE userid = $UserID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $locked = $row['locked'];
    }
    return $locked;
}

function setLockStatus($UserID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE sessions SET locked = 1 WHERE userid = ?";

    // Parameters for the query
    $params = [$UserID];

    // Tables to lock
    $tables = ["sessions"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the update was successful
    if ($result['LastID'] >= 0) { // Affected rows will also reflect as LastID
        return true;
    } else {
        $functions->errorlog("Failed to set lock status for UserID: $UserID", "setLockStatus");
        return false;
    }
}

function getTeamName($TeamID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Teamname 
            FROM teams
            WHERE ID = $TeamID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Teamname = $row['Teamname'];
    }
    return $Teamname;
}

function getITSMPriorityName($PriorityID)
{
    global $conn;
    global $functions;

    $sql = "SELECT PriorityName
            FROM itsm_priorities
            WHERE ID = $PriorityID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $PriorityName = $row['PriorityName'];
    }
    return $PriorityName;
}

function getITSMSLAName($SLAID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Name
            FROM slaagreements
            WHERE ID = $SLAID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Name = $row['Name'];
    }
    return $Name;
}

function getITSMSLAID($ITSMID, $ITSMTypeID)
{
    global $conn;
    global $functions;
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
    $sql = "SELECT SLA
            FROM $ITSMTableName
            WHERE ID = $ITSMID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $SLA = $row['SLA'];
    }
    return $SLA;
}

function getITSMParticipantIDs($ITSMTypeID, $ITSMID)
{
    global $conn;
    global $functions;

    $ArrayParticipants = [];

    $sql = "SELECT UserID
            FROM itsm_participants
            WHERE ModuleID = $ITSMTypeID AND ElementID = $ITSMID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ArrayParticipants = array($row['UserID']);
    }
    return $ArrayParticipants;
}

function getITSMStatusName($StatusID, $ITSMTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT StatusName
            FROM itsm_statuscodes
            WHERE StatusCode = $StatusID AND ModuleID = $ITSMTypeID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $StatusName = $row['StatusName'];
    }
    return $StatusName;
}

function getRelatedTaskIDFromWorkFlowStep($WFStepID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedTaskID
                FROM workflowsteps
                WHERE ID = $WFStepID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $RelatedTaskID = $row['RelatedTaskID'];
    }
    return $RelatedTaskID;
}

function getRelatedWorkFlowIDFromWorkFlowStep($WFStepID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedWorkFlowID
                FROM workflowsteps
                WHERE ID = $WFStepID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $RelatedWorkFlowID = $row['RelatedWorkFlowID'];
    }
    return $RelatedWorkFlowID;
}

function updateTaskFromWorkflowStep($TaskID, $UsersID, $ModalDeadline, $Status)
{
    global $functions;

    // Convert the deadline to the correct format
    $ModalDeadline = convertFromDanishTimeFormat($ModalDeadline);

    // SQL query with placeholders
    $sql = "UPDATE taskslist 
            SET RelatedUserID = ?, Deadline = ?, Status = ? 
            WHERE ID = ?";

    // Parameters for the query
    $params = [$UsersID, $ModalDeadline, $Status, $TaskID];

    // Tables to lock
    $tables = ["taskslist"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the update was successful
    if ($result['LastID'] >= 0) { // Affected rows will also reflect as LastID
        return true;
    } else {
        $functions->errorlog("Failed to update TaskID: $TaskID in taskslist", "updateTaskFromWorkflowStep");
        return false;
    }
}

function getNextStepOrder($RelatedWorkFlowIDID)
{
    global $conn;
    global $functions;

    $sql = "SELECT MAX(StepOrder) AS MaxStepOrder
                FROM workflowsteps
                WHERE RelatedWorkFlowID = $RelatedWorkFlowIDID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $MaxStepOrder = $row['MaxStepOrder'];
    }
    $MaxStepOrder = $MaxStepOrder + 1;
    return $MaxStepOrder;
}

function getElementSubject($ElementID, $ElementType)
{
    global $conn;
    global $functions;

    $sql = "SELECT dbtable
                FROM modules
                WHERE ID = $ElementType";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $dbtable = $row['dbtable'];
    }

    $sql = "SELECT Subject
                FROM $dbtable
                WHERE ID = $ElementID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Subject = $row['Subject'];
    }

    return $Subject;
}

function getElementRedirectPage($ElementType)
{
    global $conn;
    global $functions;

    $sql = "SELECT ElementViewPage
                FROM modules
                WHERE ID = $ElementType";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Page = $row['ElementViewPage'];
    }

    return $Page;
}

function getElementCompanyID($ElementID, $ElementType)
{
    global $conn;
    global $functions;

    $sql = "SELECT dbtable
                FROM modules
                WHERE ID = $ElementType";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $dbtable = $row['dbtable'];
    }

    $sql = "SELECT RelatedCompanyID
                FROM $dbtable
                WHERE ID = $ElementID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $RelatedCompanyID = $row['RelatedCompanyID'];
    }

    return $RelatedCompanyID;
}

function getElementCustomerID($ElementID, $ElementType)
{
    global $conn;
    global $functions;

    $sql = "SELECT dbtable
                FROM modules
                WHERE ID = $ElementType";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $dbtable = $row['dbtable'];
    }

    $sql = "SELECT RelatedCustomerID
                FROM $dbtable
                WHERE ID = $ElementID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $RelatedCustomerID = $row['RelatedCustomerID'];
    }

    return $RelatedCustomerID;
}

function getElementCreatedDate($ElementID, $ElementType)
{
    global $conn;
    global $functions;

    $sql = "SELECT dbtable
                FROM modules
                WHERE ID = $ElementType";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $dbtable = $row['dbtable'];
    }

    $sql = "SELECT DateCreated
                FROM $dbtable
                WHERE ID = $ElementID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $DateCreated = $row['DateCreated'];
    }

    return $DateCreated;
}

function getElementShortName($ElementType)
{
    global $conn;
    global $functions;

    $sql = "SELECT ShortElementName
            FROM itsm_modules
            WHERE ID = $ElementType";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ShortElementName = $row['ShortElementName'];
    }

    return $ShortElementName;
}

function getTemplatePublicStatus($TemplateID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Public
            FROM itsm_templates
            WHERE ID = $TemplateID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Public = $row['Public'];
    }

    return $Public;
}

function getElementFieldType($ITSMTypeID, $FieldName)
{
    global $conn;
    global $functions;
    $FieldType = "";

    $sql = "SELECT FieldType
            FROM itsm_fieldslist
            WHERE FieldName = '$FieldName' AND RelatedTypeID = '$ITSMTypeID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldType = $row['FieldType'];
    }

    return $FieldType;
}

function getElementFieldTypeForms($FieldName)
{
    global $conn;
    global $functions;
    $FieldType = "";

    $sql = "SELECT FieldType
            FROM forms_fieldslist
            WHERE FieldName = '$FieldName'
            LIMIT 1";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldType = $row['FieldType'];
    }

    return $FieldType;
}

function getElementDeadlineDate($ElementID, $ElementType)
{
    global $conn;
    global $functions;

    $sql = "SELECT slamatrixdbtable,resolveid
                FROM modules
                WHERE ID = $ElementType";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $dbtable = $row['slamatrixdbtable'];
        $resolveid = $row['resolveid'];
    }

    $sql = "SELECT SLAViolationDate
                FROM $dbtable
                WHERE RelatedElementID = $ElementID AND RelatedStatusCodeID = $resolveid";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $SLAViolationDate = $row['SLAViolationDate'];
    }

    return $SLAViolationDate;
}

function deleteTask($TaskID)
{
    global $conn;
    global $functions;

    // Query statement
    $sql = "DELETE FROM taskslist WHERE ID = ?;";

    // Parameters
    $params = [$TaskID];

    // Tables to lock for manipulation
    $tables = ["taskslist"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function deleteWorkFlowStepID($WorkFlowStepID)
{
    global $conn;
    global $functions;

    // Query statement
    $sql = "DELETE FROM workflowsteps WHERE ID = ?;";

    // Parameters
    $params = [$WorkFlowStepID];

    // Tables to lock for manipulation
    $tables = ["workflowsteps"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function getProjectParticipants($ElementID)
{
    global $conn;
    global $functions;
    $Users = [];

    $sql = "SELECT UserID
                FROM project_users
                WHERE ProjectID = $ElementID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Users[] = $row['UserID'];
    }

    $Users[] = getProjectManager($ElementID);
    $Users[] = getProjectResponsible($ElementID);

    return $Users;
}

function getProjectTaskParticipants($ElementID, $ProjectID)
{
    global $conn;
    global $functions;
    $Users = [];

    $sql = "SELECT UserID
                FROM project_tasks_users
                WHERE ProjectTaskID = $ElementID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Users[] = $row['UserID'];
    }

    $Users[] = getProjectManager($ProjectID);
    $Users[] = getProjectResponsible($ProjectID);
    $Users[] = getProjectTaskResponsible($ElementID);

    return $Users;
}

function getProjectTaskParticipantsForPrivate($ProjectTaskID, $UserSessionID)
{
    global $conn;
    global $functions;

    $sql = "SELECT UserID
                FROM project_tasks_users
                WHERE ProjectTaskID = $ProjectTaskID AND UserID = $UserSessionID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $IsParticipant = $row['UserID'];
    }

    return $IsParticipant;
}

function getProjectTaskPrivateStatus($ProjectTaskID)
{
    global $conn;
    global $functions;

    $sql = "SELECT project_tasks.Private AS PrivateStatus
                FROM project_tasks
                WHERE ID = $ProjectTaskID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $PrivateStatus = $row['PrivateStatus'];
    }

    return $PrivateStatus;
}

function getRequestTemplateIDFromName($TemplateName)
{
    global $conn;
    global $functions;
    $WFID = "";

    $sql = "SELECT ID, RelatedWorkflowTemplate
                FROM requests_templates
                WHERE Name = '$TemplateName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $WFID = $row['RelatedWorkflowTemplate'];
    }

    return $WFID;
}

function get_content($URL)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $URL);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function removeIMAPEmail($UID)
{
    global $conn;
    global $functions;

    $IMAPServer = "";
    $IMAPMailAddress = "";
    $IMAPPassword = "";
    $MoveToTrashFolder = "0";

    $sql = "SELECT ID, SettingValue
                FROM settings
                WHERE ID IN (38,39,40,41)";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        if ($row['ID'] == '38') {
            $IMAPServer = $row['SettingValue'];
        }
        if ($row['ID'] == '39') {
            $IMAPMailAddress = $row['SettingValue'];
        }
        if ($row['ID'] == '40') {
            $IMAPPassword = $row['SettingValue'];
        }
        if ($row['ID'] == '41') {
            $MoveToTrashFolder = $row['SettingValue'];
        }
    }

    $mbox = imap_open("$IMAPServer", "$IMAPMailAddress", "$IMAPPassword");

    $result = imap_setflag_full($mbox, $UID, "\\Seen", ST_UID);
    if ($MoveToTrashFolder == "1") {
        $result = imap_mail_move($mbox, $UID, 'INBOX.Trash', CP_UID);
    }

    imap_expunge($mbox);
    imap_close($mbox, CL_EXPUNGE);
}

function createITSMComment($ITSMID, $ITSMTypeID, $Comment, $UserID)
{
    global $conn;
    global $functions;

    $CustomerID = $functions->getITSMCustomer($ITSMTypeID, $ITSMID);

    // Query statement
    $sql = "INSERT INTO itsm_comments (RelatedElementID, ITSMType, UserID, Text, Internal) VALUES (?, ?, ?, ?, 0);";

    // Parameters
    $params = [$ITSMID,$ITSMTypeID,$UserID,$Comment];

    // Tables to lock for manipulation
    $tables = ["itsm_comments"];
    // Run the dmlQuery with decent transaction
    $functions->dmlQuery($sql, $params, $tables);
}

function checkIfUserExists($EmailAddress)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID
                FROM users
                WHERE Email = '$EmailAddress'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $UsersID = $row['ID'];
    }
    return $UsersID;
}

function getBusinessServiceTableName($BSID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TableName
            FROM cmdb_cis
            WHERE ID = '$BSID'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $value = $row['TableName'];
    }
    return $value;
}

function checkEmailForValidSubdomain($EmailAddress)
{
    global $conn;
    global $functions;
    $CompanyID = "";
    $UsersEmailDomain = explode('@', $EmailAddress)[1];

    $sql = "SELECT ID, Email
                FROM companies
                WHERE Active = '1'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        if ($UsersEmailDomain == explode('@', $row['Email'])[1]) {
            $CompanyID = $row['ID'];
        }
    }
    return $CompanyID;
}

function doesEmailExists($EmailAddress, $UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID, Firstname, Lastname
            FROM users
            WHERE Email = '$EmailAddress' AND ID != '$UserID'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $FullName = $functions->getUserFullNameWithUsername($ID);
    }
    return $FullName;
}

function doesCompanyEmailExists($EmailAddress,$CompanyID)
{
    global $conn;
    global $functions;

    $returnValue = "";

    $sql = "SELECT CompanyName
            FROM Companies
            WHERE Email = '$EmailAddress' AND ID != '$CompanyID'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $returnValue = $row['CompanyName'];
    }
    return $returnValue;
}

function doesCompanyEmailExistsBeforeCreate($Email, $CompanyName){
    global $conn;
    global $functions;

    $returnValue = "";

    $sql = "SELECT CompanyName
            FROM Companies
            WHERE Email = '$Email' AND CompanyName != '$CompanyName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $returnValue = $row['CompanyName'];
    }
    return $returnValue;
}

function doesUsernameExists($Username, $UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID, Firstname, Lastname
            FROM users
            WHERE Username = '$Username' AND ID != '$UserID'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $FullName = $functions->getUserFullNameWithUsername($ID);
    }
    return $FullName;
}

function doesCompanyNameExists($CompanyName)
{
    global $conn;
    global $functions;

    $sql = "SELECT CompanyName
            FROM Companies
            WHERE CompanyName = '$CompanyName'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $returnValue = $row['CompanyName'];
    }
    return $returnValue;
}

function doesSiteExist($SiteName)
{
    global $conn;
    global $functions;

    $SiteName = "practicle_" . $SiteName;

    $sql = "SHOW DATABASES LIKE '$SiteName';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $SiteNameQuery = $row[0];
    }
    return $SiteNameQuery;
}

function doesSiteInstallExist($SiteName)
{
    global $conn;
    global $functions;

    $sql = "SELECT SiteName
                    FROM bpage.site_installation
                    WHERE SiteName = '$SiteName';";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $returnvalue = $row['SiteName'];
    }

    return $returnvalue;
}

function getITSMFormsTableName($FormsID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TableName
            FROM Forms
            WHERE ID = '$FormsID';";
 
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $TableName = $row['TableName'];
    }

    return $TableName;
}

function navMenu01($module)
{
    global $conn;
    global $functions;
    $ModuleType = $functions->getModuleType($module);

    $navMenu01 = "<div class='float-end'>
            <ul class='navbar-nav justify-content-end'>
                <li class='nav-item dropdown pe-2'>
                    <a href='javascript:;' class='nav-link text-body p-0 position-relative' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                        &nbsp;&nbsp;<i class='fa-solid fa-ellipsis-vertical' title=\"" . _('Create') . "\"></i>&nbsp;&nbsp;
                    </a>
                    <ul class='dropdown-menu dropdown-menu-end p-2 me-sm-n4' aria-labelledby='dropdownMenuButton'>
                        <li class='mb-2'>
                            <a class='dropdown-item border-radius-md' onclick=\"window.location.href=('itsm_tableview.php?id=$module')\">
                                <div class='d-flex align-items-center py-1'>
                                    <div class='ms-2'>
                                        <h6 class='text-sm font-weight-normal my-auto'>
                                            <i class='fa-solid fa-inbox'></i> " . _("View") . "
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>";

                        $navMenu01 .= "<li class='mb-2'>
                            <a class='dropdown-item border-radius-md' onclick=\"window.location.href=('./itsm_all_open.php?id=$module')\">
                                <div class='d-flex align-items-center py-1'>
                                    <div class='ms-2'>
                                        <h6 class='text-sm font-weight-normal my-auto'>
                                            <i class='fas fas-dark fa-box-open'></i> " . _("All open") . "
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class='mb-2'>
                            <a class='dropdown-item border-radius-md' onclick=\"window.location.href=('./itsm_all_closed.php?id=$module')\">
                                <div class='d-flex align-items-center py-1'>
                                    <div class='ms-2'>
                                        <h6 class='text-sm font-weight-normal my-auto'>
                                            <i class='fas fas-dark fa-door-closed'></i> " . _("All closed") . "
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class='mb-2'>
                            <a class='dropdown-item border-radius-md' onclick=\"window.location.href=('./itsm_search.php?id=$module')\">
                                <div class='d-flex align-items-center py-1'>
                                    <div class='ms-2'>
                                        <h6 class='text-sm font-weight-normal my-auto'>
                                            <i class='fas fas-dark fa-search'></i> " . _('Search') . "
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class='mb-2'>
                            <a class='dropdown-item border-radius-md' onclick=\"window.location.href=('./itsm_stats.php?id=$module')\">
                                <div class='d-flex align-items-center py-1'>
                                    <div class='ms-2'>
                                        <h6 class='text-sm font-weight-normal my-auto'>
                                            <i class='fa-solid fa-chart-pie'></i> " . _('Statistics') . "
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>";
    return $navMenu01;
}

function navMenu02($Href, $Name)
{
    $return = "<li class=\"nav-item\">
                <a class=\"nav-link text-white\" href=\"./$Href\">
                  <span class=\"sidenav-normal ms-2 ps-1\">" . _("$Name") . "</span>
                </a>
              </li>";
    return $return;
}

function navMenu03($module)
{
    $navMenu03 = "
        <div class='float-end'>
            <ul class='navbar-nav justify-content-end'>
                <li class='nav-item dropdown pe-2'>
                    <a href='javascript:;' class='nav-link text-body p-0 position-relative' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                        &nbsp;&nbsp;<i class='fa-solid fa-ellipsis-vertical' title=\"" . _('Create') . "\"></i>&nbsp;&nbsp;
                    </a>
                    <ul class='dropdown-menu dropdown-menu-end p-2 me-sm-n4' aria-labelledby='dropdownMenuButton'>
                        ".                        
                        "<li class='mb-2'>
                            <a class='dropdown-item border-radius-md' onclick=\"window.location.href=('itsm_simple.php?id=$module')\">
                                <div class='d-flex align-items-center py-1'>
                                    <div class='ms-2'>
                                        <h6 class='text-sm font-weight-normal my-auto'>
                                            <i class='fa-solid fa-inbox'></i> " . _("View") . "
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>                        
                        <li class='mb-2'>
                            <a class='dropdown-item border-radius-md' onclick=\"window.location.href=('./itsm_search.php?id=$module')\">
                                <div class='d-flex align-items-center py-1'>
                                    <div class='ms-2'>
                                        <h6 class='text-sm font-weight-normal my-auto'>
                                            <i class='fas fas-dark fa-search'></i> " . _('Search') . "
                                        </h6>
                                    </div>
                                </div>
                            </a>
                        </li>                        
                    </ul>
                </li>
            </ul>
        </div>";
    return $navMenu03;
}

function navMenu04($module)
{
    $navMenu03 = "
        <div class='float-end'>
            <ul class='navbar-nav justify-content-end'>
                <li class='nav-item dropdown pe-2'>
                    <a href='javascript:;' class='nav-link text-body p-0 position-relative' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                        &nbsp;&nbsp;<i class='fa-solid fa-ellipsis-vertical' title=\"" . _('Create') . "\"></i>&nbsp;&nbsp;
                    </a>
                </li>
            </ul>
        </div>";
    return $navMenu03;
}

function QuickSearch($SearchTerm)
{
    global $conn;
    global $functions;

    $db = getCurrentDB();
    $group_array = $_SESSION['memberofgroups'];
    $SessionUserID = $_SESSION['id'];
    $team_array = $_SESSION['teamid'];
    $SearchResultsArray[] = array();

    //Search modules
    //Get all Modules
    $ModuleRolesArray = getAllModuleRoles();

    $ModulesArray = array();

    foreach ($ModuleRolesArray as $moduleRole) {
        $ID = $moduleRole["ModuleID"];
        $RoleID = $moduleRole["RoleID"];
        $RoleGroups = getGroupsInRole($RoleID);
        $TableName = $moduleRole['TableName'];
        $Name = $moduleRole['ShortElementName'];

        // Check if the user belongs to any of the groups associated with the role
        if (in_array("100001", $group_array) || !empty(array_intersect($group_array, $RoleGroups))) {
            $ModulesArray[] = array('ID' => $ID, 'Name' => $Name, 'TableName' => $TableName);
        }
    }

    //Get Fields per ITSM
    foreach ($ModulesArray as $rowFields) {
        $ShowField = "";
        $ITSMID = $rowFields["ID"];
        $ModuleType = $functions->getITSMModuleType($ITSMID);        
        $Name = $rowFields["Name"];
        $TableName = $rowFields["TableName"];

        //getShowField
        $ShowField = $functions->getITSMFieldToWorkAsID($ITSMID);

        $sqlITSMFIelds = "select index_name, group_concat(column_name) as ColumnName
                    from information_Schema.STATISTICS
                    where table_schema = '$db'
                    and table_name = '$TableName' 
                    and index_type = 'FULLTEXT'
                    group by index_name;";

        $resultITSMFields = mysqli_query($conn, $sqlITSMFIelds) or die('Query fail: ' . mysqli_error($conn));

        while ($rowITSMFields = mysqli_fetch_array($resultITSMFields)) {
            $FieldName = $rowITSMFields['ColumnName'];

            $FieldLabel = getITSMFieldLabelFromFieldName($ITSMID, $FieldName);

            if ($FieldName != "") {
                if ($ModuleType == "3") {
                    $ITSMSearchSQL = "SELECT ID, $FieldName, MATCH($FieldName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) AS Score
                FROM $TableName
                WHERE MATCH($FieldName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) 
                AND Status = 4
                AND ((
                    (($TableName.RelatedGroupID IN('" . implode("','", $group_array) . "') OR $TableName.RelatedGroupID IS NULL OR $TableName.RelatedGroupID = '')
                        AND ($TableName.Team IN('$team_array') OR $TableName.Team IS NULL OR $TableName.Team = ''))
                    OR 
                    (($TableName.RelatedGroupID IN('" . implode("','", $group_array) . "') OR $TableName.RelatedGroupID IS NULL OR $TableName.RelatedGroupID = '')
                        AND ($TableName.Team = '' OR $TableName.Team IS NULL))
                    OR 
                    (($TableName.RelatedGroupID = '' OR $TableName.RelatedGroupID IS NULL)
                        AND ($TableName.Team IN('$team_array') OR $TableName.Team IS NULL OR $TableName.Team = ''))
                    OR 
                    ($TableName.RelatedGroupID = '' AND $TableName.Team = '')
                ) OR Public = 1)
                HAVING score > 0
                ORDER BY score DESC;";
                }
                elseif ($ModuleType == "4") {
                    $ITSMSearchSQL = "SELECT ID, $FieldName, MATCH($FieldName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) AS Score
                FROM $TableName
                WHERE MATCH($FieldName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) 
                AND Status = 2
                AND ((
                    (($TableName.RelatedGroup IN('" . implode("','", $group_array) . "') OR $TableName.RelatedGroup IS NULL OR $TableName.RelatedGroup = '')
                        AND ($TableName.RelatedTeam IN('$team_array') OR $TableName.RelatedTeam IS NULL OR $TableName.RelatedTeam = ''))
                    OR 
                    (($TableName.RelatedGroup IN('" . implode("','", $group_array) . "') OR $TableName.RelatedGroup IS NULL OR $TableName.RelatedGroup = '')
                        AND ($TableName.RelatedTeam = '' OR $TableName.RelatedTeam IS NULL))
                    OR 
                    (($TableName.RelatedGroup = '' OR $TableName.RelatedGroup IS NULL)
                        AND ($TableName.RelatedTeam IN('$team_array') OR $TableName.RelatedTeam IS NULL OR $TableName.RelatedTeam = ''))
                    OR 
                    ($TableName.RelatedGroup = '' AND $TableName.RelatedTeam = '')
                ) OR Responsible = $SessionUserID)
                HAVING score > 0
                ORDER BY score DESC;";
                }
                else {
                    $ITSMSearchSQL = "SELECT ID, $FieldName, MATCH($FieldName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) AS Score
                FROM $TableName
                WHERE MATCH($FieldName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE)
                HAVING score > 0
                ORDER BY score DESC;";
                }

                $result6 = mysqli_query($conn, $ITSMSearchSQL) or die('Query fail: ' . mysqli_error($conn));

                while ($row = mysqli_fetch_array($result6)) {
                    if ($ITSMID !== "6") {
                        $ID = $row['ID'];
                        $SearchHit = $row["$FieldName"];
                        $SearchHit = strip_tags($SearchHit);
                        $Subject = $functions->getFieldValueFromID($ID, $ShowField, $TableName);
                        $Subject = strip_tags($Subject);
                        $Subject2 = $Subject;

                        if (strlen($Subject) > 75) $Subject2 = substr($Subject, 0, 75) . '...';

                        // Highlight the search term in $SearchHit
                        $HighlightedSearchHit = str_ireplace($SearchTerm, "<span style='background-color: yellow;color: black;'>$SearchTerm</span>", $SearchHit);

                        $Module = _("$Name");
                        $ModuleID = "$ITSMID";
                        $URL = "<a href=\"javascript:viewITSM('$ID','$ITSMID','0','modal');collapseSearch();\">$Subject2<br><small>".$functions->translate("Found in field").": ".$functions->translate("$FieldLabel")."<br>$HighlightedSearchHit</small></a>";
                        $SearchResultsArray[] = array('ID' => $ID, 'Subject' => $Subject, 'Module' => $Module, 'ModuleID' => $ModuleID, 'URL' => $URL);
                    }
                }
            }
        }
    }

    //Search CIs
    //Get all CIs
    if (in_array("100001", $group_array) || in_array("100014", $group_array) || in_array("100015", $group_array)
    ) {
        $sqlCIs = "SELECT cmdb_cis.ID, cmdb_cis.Name, cmdb_cis.TableName
                    FROM cmdb_cis
                    WHERE cmdb_cis.Active = 1 AND cmdb_cis.GroupID IN (".implode(",", $group_array).");";

        $resultCIs = mysqli_query($conn, $sqlCIs) or die('Query fail: ' . mysqli_error($conn));
        
        while ($row = mysqli_fetch_array($resultCIs)) {
            $ID = $row['ID'];
            $Name = $row['Name'];
            $TableName = $row['TableName'];
            $CIArray[] = array('ID' => $ID, 'Name' => $Name, 'TableName' => $TableName);
        }
        
        //Get Fields per CI
        foreach ($CIArray as $rowFields){
            $Matches = "";
            $ShowField = "";
            $CITypeID = $rowFields["ID"];
            $Name = $rowFields["Name"];
            $TableName = $rowFields["TableName"];
            //getShowField
            $ShowField = getCIFieldToWorkAsID($CITypeID);

            $sqlCIFIelds = "select index_name, group_concat(column_name) as ColumnName
                            from information_Schema.STATISTICS
                            where table_schema = '$db'
                            and table_name = '$TableName' 
                            and index_type = 'FULLTEXT'
                            group by index_name;";
            
            $resultCIFields = mysqli_query($conn, $sqlCIFIelds) or die('Query fail: ' . mysqli_error($conn));

            while ($rowCIFields = mysqli_fetch_array($resultCIFields)) {
                $FieldName = $rowCIFields['ColumnName'];
                $FieldLabel = getCIFieldLabelFromFieldName($CITypeID, $FieldName);
                
                if ($FieldName != "") {
                    $CISearchSQL = "SELECT ID, $FieldName, MATCH($FieldName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) AS Score
                                    FROM $TableName
                                    WHERE Active = 1 AND MATCH($FieldName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE)
                                    HAVING score > 0
                                    ORDER BY score DESC;";

                    $result6 = mysqli_query($conn, $CISearchSQL) or die('Query fail: ' . mysqli_error($conn));
                    while ($row = mysqli_fetch_array($result6)) {
                        $ID = $row['ID'];
                        $SearchHit = $row["$FieldName"];
                        $SearchHit = strip_tags($SearchHit);
                        $Subject = $functions->getFieldValueFromID($ID, $ShowField, $TableName);
                        $Subject = strip_tags($Subject);
                        $Subject2 = $Subject;

                        if (strlen($Subject) > 75) $Subject2 = substr($Subject, 0, 75) . '...';

                        // Highlight the search term in $SearchHit
                        $HighlightedSearchHit = str_ireplace($SearchTerm, "<span style='background-color: yellow;color: black;'>$SearchTerm</span>", $SearchHit);
                        
                        $Module = _("$Name");
                        $ModuleID = "4";
                        $URL = "<a href=\"javascript:runModalViewCI('$ID','$CITypeID','0');collapseSearch();\">$Subject2<br><small>" . $functions->translate("Found in field") . ": " . $functions->translate("$FieldLabel") . "<br>$HighlightedSearchHit</small></a>";
                        $SearchResultsArray[] = array('ID' => $ID, 'Subject' => $Subject, 'Module' => $Module, 'ModuleID' => $ModuleID, 'URL' => $URL);
                    }
                }

            }
        }
    }

    //Search Projects
    if (in_array("100001", $group_array) || in_array("100007", $group_array) || in_array("100008", $group_array)
    ) {
        $sql10 = "SELECT ID, Name, MATCH(Name) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) AS score1, MATCH(Description) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) AS score2
                FROM projects
                WHERE (MATCH(Name) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE)
                OR MATCH(Description) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE))
                AND Status NOT IN ('7','8')
                HAVING score1 > 0 OR score2 > 0
                ORDER BY score1 DESC, score2 DESC
                LIMIT 20;";

        $result10 = mysqli_query($conn, $sql10) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_array($result10)) {
            $ID = $row['ID'];
            $SearchHit = $row["Name"];
            $SearchHit = strip_tags($SearchHit);
            $UserProjectID = isUserProjectParticipant($ID);
            //If you are not in project user group, you will get no result
            if($ID == $UserProjectID){
                $Subject = $row['Name'];
                $Subject = strip_tags($Subject);
                $Subject2 = $Subject;
                if (strlen($Subject) > 75) $Subject2 = substr($Subject, 0, 75) . '...';

                // Highlight the search term in $SearchHit
                $HighlightedSearchHit = str_ireplace($SearchTerm, "<span style='background-color: yellow;color: black;'>$SearchTerm</span>", $SearchHit);

                $Module = _("Project");
                $ModuleID = "6";
                $URL = "<a href=\"projects_view.php?projectid=$ID#FilesTab\" onblur=\"collapseSearch();\">$Subject2<br><small>" . $functions->translate("Found in field") . ": " . $functions->translate("$FieldLabel") . "<br>$HighlightedSearchHit</small></a>";
                $SearchResultsArray[] = array('ID' => $ID, 'Subject' => $Subject, 'Module' => $Module, 'ModuleID' => $ModuleID, 'URL' => $URL);
            }
        }
    }

    //Search Project Tasks
    if (in_array("100001", $group_array) || in_array("100007", $group_array) || in_array("100008", $group_array)
    ) {
        $sql11 = "SELECT ID, TaskName, MATCH(TaskName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) AS score1, MATCH(Description) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) AS score2
                FROM project_tasks
                WHERE (MATCH(TaskName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE)
                OR MATCH(Description) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE))
                AND Status NOT IN ('7','8')
                HAVING score1 > 0 OR score2 > 0
                ORDER BY score1 DESC, score2 DESC
                LIMIT 20;";

        $result11 = mysqli_query($conn, $sql11) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_array($result11)) {
            $ID = $row['ID'];
            $SearchHit = $row["TaskName"];
            $SearchHit = strip_tags($SearchHit);
            // Highlight the search term in $SearchHit
            $HighlightedSearchHit = str_ireplace($SearchTerm, "<span style='background-color: yellow;color: black;'>$SearchTerm</span>", $SearchHit);
            //If you are not in project task user group, you will get no result
            $UserProjectTaskID = isUserProjectTaskParticipant($ID);
            if ($ID == $UserProjectTaskID) {
                $Subject = $row['TaskName'];
                $Subject = strip_tags($Subject);
                $Subject2 = $Subject;
                if (strlen($Subject) > 75) $Subject2 = substr($Subject, 0, 75) . '...';
                $Module = _("Project Task");
                $ModuleID = "13";
                $URL = "<a href=\"projects_tasks_view.php?projecttaskid=$ID#FilesTab\" onblur=\"collapseSearch();\">$Subject2<br><small>" . $functions->translate("Found in field") . ": " . $functions->translate("$FieldLabel") . "<br>$HighlightedSearchHit</small></a>";
                $SearchResultsArray[] = array('ID' => $ID, 'Subject' => $Subject, 'Module' => $Module, 'ModuleID' => $ModuleID, 'URL' => $URL);
            }
        }
    }

    // Initialize a new array to hold unique values
    $uniqueArray = [];
    // Temporary array to hold keys combination of ID and ModuleID
    $keysArray = [];

    foreach ($SearchResultsArray as $item) {
        // Combine ID and ModuleID to create a unique key
        $uniqueKey = $item['ID'] . '-' . $item['ModuleID'];
        // Check if this combination exists in $keysArray
        if (!in_array($uniqueKey, $keysArray)) {
            // If it does not exist, add it to $keysArray and $uniqueArray
            $keysArray[] = $uniqueKey;
            $uniqueArray[] = $item;
        }
    }

    // Assign unique values back to $SearchResultsArray
    $SearchResultsArray = $uniqueArray;

    return $SearchResultsArray;

}

function SearchITSM($ITSMTypeID, $SearchTerm, $StatusArray)
{
    global $conn;
    global $functions;
    
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
    $statusCodesString = implode(',', $StatusArray);

    $group_array = $_SESSION['memberofgroups'];
    $team_array = $_SESSION['teamid'];
    
    // Get the list of full-text indexes for the table
    $fullTextIndexes = getFullTextIndexes($ITSMTableName);
 
    // Initialize arrays for search terms and where clauses
    $searchTerms = array();
    $whereClauses = array();

    // Construct the WHERE clause based on full-text indexes availability
    foreach ($fullTextIndexes as $index) {
        $whereClauses[] = "MATCH($index) AGAINST (? IN NATURAL LANGUAGE MODE)";
        $searchTerms[] = $SearchTerm;
    }

    // Check if any full-text indexes were found
    if (empty($whereClauses)) {
        // No full-text indexes found
        $functions->errorlog("No full-text indexes defined for table $ITSMTableName", "SearchITSM");
        return []; // Return an empty array or handle the error accordingly
    }

    // Construct the SQL query dynamically
    $whereClause = implode(" OR ", $whereClauses);

    // Construct additional conditions for $ITSMTypeID == 13
    if ($ITSMTypeID == "7") {
        $whereClause .= " AND (
            ($ITSMTableName.RelatedGroupID IN ('" . implode("','", $group_array) . "') OR $ITSMTableName.RelatedGroupID IS NULL OR $ITSMTableName.RelatedGroupID = '')
            AND ($ITSMTableName.Team IN ('$team_array') OR $ITSMTableName.Team IS NULL OR $ITSMTableName.Team = '')
        )";
    }

    $sql = "SELECT ID, Subject FROM $ITSMTableName WHERE $whereClause AND Status IN ($statusCodesString) ORDER BY ID DESC LIMIT 50";

    // Prepare the SQL statement
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        $functions->errorlog("Failed to prepare the query: " . mysqli_error($conn), "SearchITSM");
        return []; // Return an empty array or handle the error accordingly
    }

    // Bind search terms dynamically
    $bindParams = array_merge(array(str_repeat('s', count($searchTerms))), $searchTerms);
    call_user_func_array(array($stmt, 'bind_param'), $bindParams);

    // Execute the query
    if (!$stmt->execute()) {
        $functions->errorlog("Failed to execute the query: " . $stmt->error, "SearchITSM");
        return []; // Return an empty array or handle the error accordingly
    }

    // Get the result set
    $result = $stmt->get_result();
    if ($result === false) {
        $functions->errorlog("Failed to get the result set: " . $stmt->error, "SearchITSM");
        return []; // Return an empty array or handle the error accordingly
    }

    // Fetch and process the search results
    $searchResultsArray = [];
    while ($row = mysqli_fetch_array($result)) {
        $ITSMID = $row['ID'];
        $Subject = $row['Subject'];
        $Subject2 = (strlen($Subject) > 75) ? substr($Subject, 0, 75) . '...' : $Subject;
        $URL = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','0','modal');\" title=\"$Subject\">$Subject2</a>";
        $searchResultsArray[] = ['ID' => $ITSMID, 'Subject' => $URL];
    }

    // Close the statement and database connection
    $stmt->close();
    $conn->close();

    return $searchResultsArray;
}

function getFullTextIndexes($tableName)
{
    global $conn;
    global $functions;

    $dbName = getCurrentDB(); // Ensure $dbName contains the correct database name
    $fullTextIndexes = array();

    // Query to retrieve the list of full-text indexes for the specified table
    $sql = "SELECT COLUMN_NAME
            FROM information_schema.statistics
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_TYPE = 'FULLTEXT'";

    // Prepare the SQL statement
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        $functions->errorlog("Failed to prepare the query to retrieve full-text indexes: " . mysqli_error($conn), "getFullTextIndexes");
        return $fullTextIndexes; // Return an empty array or handle the error accordingly
    }

    // Bind parameters and execute the query
    mysqli_stmt_bind_param($stmt, "ss", $dbName, $tableName);
    if (!mysqli_stmt_execute($stmt)) {
        $functions->errorlog("Failed to execute the query to retrieve full-text indexes: " . mysqli_stmt_error($stmt), "getFullTextIndexes");
        mysqli_stmt_close($stmt);
        return $fullTextIndexes; // Return an empty array or handle the error accordingly
    }

    // Bind result variables
    mysqli_stmt_bind_result($stmt, $columnName);

    // Fetch results and populate the array of full-text indexes
    while (mysqli_stmt_fetch($stmt)) {
        $fullTextIndexes[] = $columnName;
    }

    // Close the statement
    mysqli_stmt_close($stmt);
    return $fullTextIndexes;
}


// Function to check if a full-text index exists for the specified columns in a table
function checkFullTextIndexExists($tableName, $column)
{
    global $conn;
    global $functions;
    $sql = "SHOW INDEX FROM $tableName WHERE Index_type = 'FULLTEXT' AND Column_name = '$column'";
    $result = mysqli_query($conn, $sql);
    return (mysqli_num_rows($result) > 0);
}

function HelpdeskSearch($SearchTerm)
{
    global $conn;
    global $functions;
    $db = getCurrentDB();
    $ModuleType = 3;
    $TableName = "itsm_knowledge";

    $sql = "select index_name, group_concat(column_name) as ColumnName
            from information_Schema.STATISTICS
            where table_schema = '$db'
            and table_name = '$TableName' 
            and index_type = 'FULLTEXT'
            group by index_name;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($rowITSMFields = mysqli_fetch_array($result)) {
        $FieldName = $rowITSMFields['ColumnName'];

        $FieldLabel = getITSMFieldLabelFromFieldName($ITSMID, $FieldName);

        if ($FieldName != "") {
            if ($ModuleType == "3") {
                $ITSMSearchSQL = "SELECT ID, $FieldName, MATCH($FieldName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) AS Score
                                FROM $TableName
                                WHERE MATCH($FieldName) AGAINST ('$SearchTerm' IN NATURAL LANGUAGE MODE) 
                                AND Status = 4
                                AND Public = 1
                                HAVING score > 0
                                ORDER BY score DESC;";
            }

            $result6 = mysqli_query($conn, $ITSMSearchSQL) or die('Query fail: ' . mysqli_error($conn));

            while ($row = mysqli_fetch_array($result6)) {
                if ($ITSMID !== "6") {
                    $ID = $row['ID'];
                    $SearchHit = $row["$FieldName"];
                    $SearchHit = strip_tags($SearchHit);
                    $Subject = $functions->getFieldValueFromID($ID, $ShowField, $TableName);
                    $Subject = strip_tags($Subject);
                    $Subject2 = $Subject;

                    if (strlen($Subject) > 75) $Subject2 = substr($Subject, 0, 75) . '...';

                    // Highlight the search term in $SearchHit
                    $HighlightedSearchHit = str_ireplace($SearchTerm, "<span style='background-color: yellow;color: black;'>$SearchTerm</span>", $SearchHit);

                    $Module = _("$Name");
                    $ModuleID = "$ITSMID";
                    $URL = "<a href=\"javascript:viewITSM('$ID','$ITSMID','0','modal');collapseSearch();\">$Subject2<br><small>" . $functions->translate("Found in field") . ": " . $functions->translate("$FieldLabel") . "<br>$HighlightedSearchHit</small></a>";
                    $SearchResultsArray[] = array('ID' => $ID, 'Subject' => $Subject, 'Module' => $Module, 'ModuleID' => $ModuleID, 'URL' => $URL);
                }
            }
        }
    }

    return $SearchResultsArray;
}

function isUserProjectParticipant($ProjectID)
{
    global $conn;
    global $functions;
    $SessionUserID = $_SESSION["id"];

    $sql = "SELECT ProjectID
            FROM project_users
            WHERE ProjectID = $ProjectID AND UserID = $SessionUserID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ProjectID = $row['ProjectID'];
    }

    return $ProjectID;
}

function isUserProjectTaskParticipant($ProjectTaskID)
{
    global $conn;
    global $functions;
    $SessionUserID = $_SESSION["id"];

    $sql = "SELECT ProjectTaskID
            FROM project_tasks_users
            WHERE ProjectTaskID = $ProjectTaskID AND UserID = $SessionUserID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ProjectTaskID = $row['ProjectTaskID'];
    }

    return $ProjectTaskID;
}

function getDocumentGroupID($DocumentID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedGroupID
            FROM knowledge_documents
            WHERE ID = $DocumentID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $RelatedGroupID = $row['RelatedGroupID'];
    }

    return $RelatedGroupID;
}

function createDatabaseBackup($ITSMTypeID, $UserID)
{
    global $conn;
    global $functions;
    $ExcludeListDontTouch = getTableExcludeList();
    $Backup_File_Name = "";
    $Date = "";
    $Description = "";

    if($ITSMTypeID !== ""){
        // Check for maximum number of backups allowed
        $countSql = "SELECT COUNT(*) as count 
                    FROM db_backups
                    WHERE RelatedModule = $ITSMTypeID";
        $countResult = mysqli_query($conn, $countSql);
        $countRow = mysqli_fetch_assoc($countResult);
        $numberallowed = 5;

        if ($countRow['count'] >= $numberallowed) {
            return "You have $numberallowed backups, which is the maximum allowed, please delete some before taking any more backups";
        }

        $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
        $ITSMName = getITSMModuleShotName($ITSMTypeID);
        $ITSMName = _("$ITSMName");
        // This is not an ITSM backup, creating full backup
        $tables = array();

        $sql = "SHOW TABLES LIKE '$ITSMTableName';";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }
        
        $sqlScript = "SET FOREIGN_KEY_CHECKS=0;";
        foreach ($tables as $table) {
            // itsm Main table
            // Prepare SQLscript for DROP Table

            $sqlScript .= "\n" . "DROP TABLE IF EXISTS " . $table . ";\n";

            // Prepare SQLscript for creating table structure
            $query = "SHOW CREATE TABLE $table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_row($result);

            $sqlScript .= "\n\n" . $row[1] . ";\n\n";

            $query = "SELECT * FROM $table";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = mysqli_real_escape_string($conn, $row[$j]);

                        if ($row[$j] !== "") {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";

            // itsm_comments
            $temp = "DELETE FROM itsm_comments WHERE ITSMType = $ITSMTypeID;";
            $sqlScript .= "$temp\n";

            $sqlScript .= "\n";

            $query = "SELECT * FROM itsm_comments WHERE ITSMType = $ITSMTypeID;";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO itsm_comments VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = mysqli_real_escape_string($conn, $row[$j]);

                        if ($row[$j] !== "") {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";

            // itsm_fieldslist
            $temp = "DELETE FROM itsm_fieldslist WHERE RelatedTypeID = $ITSMTypeID;";
            $sqlScript .= "$temp\n";

            $sqlScript .= "\n";

            $query = "SELECT * FROM itsm_fieldslist WHERE RelatedTypeID = $ITSMTypeID;";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO itsm_fieldslist VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = mysqli_real_escape_string($conn, $row[$j]);                        

                        if ($row[$j] !== "") {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";

            // itsm_log
            $temp = "DELETE FROM itsm_log WHERE RelatedType = $ITSMTypeID;";
            $sqlScript .= "$temp\n";

            $sqlScript .= "\n";

            $query = "SELECT * FROM itsm_log WHERE RelatedType = $ITSMTypeID;";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO itsm_log VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = mysqli_real_escape_string($conn, $row[$j]);

                        if ($row[$j] !== "") {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";

            // itsm_relations
            $temp = "DELETE FROM itsm_relations WHERE Table1 = '$ITSMTableName';";
            $sqlScript .= "$temp\n";

            $sqlScript .= "\n";

            $query = "SELECT * FROM itsm_relations WHERE Table1 = '$ITSMTableName';";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO itsm_relations VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = mysqli_real_escape_string($conn, $row[$j]);

                        if ($row[$j] !== "") {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";

            // itsm_relations
            $temp = "DELETE FROM itsm_relations WHERE Table2 = '$ITSMTableName';";
            $sqlScript .= "$temp\n";

            $sqlScript .= "\n";

            $query = "SELECT * FROM itsm_relations WHERE Table2 = '$ITSMTableName';";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO itsm_relations VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = mysqli_real_escape_string($conn, $row[$j]);

                        if ($row[$j] !== "") {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";

            // itsm_statuscodes
            $temp = "DELETE FROM itsm_statuscodes WHERE ModuleID = $ITSMTypeID;";
            $sqlScript .= "$temp\n";

            $sqlScript .= "\n";

            $query = "SELECT * FROM itsm_statuscodes WHERE ModuleID = $ITSMTypeID;";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO itsm_statuscodes VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = mysqli_real_escape_string($conn, $row[$j]);

                        if ($row[$j] !== "") {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";

            // itsm_templates
            $temp = "DELETE FROM itsm_templates WHERE RelatedModule = $ITSMTypeID;";
            $sqlScript .= "$temp\n";

            $sqlScript .= "\n";

            $query = "SELECT * FROM itsm_templates WHERE RelatedModule = $ITSMTypeID;";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO itsm_templates VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = mysqli_real_escape_string($conn, $row[$j]);

                        if ($row[$j] !== "") {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";

            // files_itsm
            $temp = "DELETE FROM files_itsm WHERE RelatedType = $ITSMTypeID;";
            $sqlScript .= "$temp\n";

            $sqlScript .= "\n";

            $query = "SELECT * FROM files_itsm WHERE RelatedType = $ITSMTypeID;";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO files_itsm VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = mysqli_real_escape_string($conn, $row[$j]);

                        if ($row[$j] !== "") {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }

            $sqlScript .= "\n";
        }

        if (!empty($sqlScript)) {
            // Save the SQL script to a backup file
            $Date = date('Y-m-d H:i:s');
            $ShortDate = date("YmdHis", strtotime($Date));
            $Backup_File_Dir = "./backups/db";
            $Backup_File_Name = "$Backup_File_Dir/".$ITSMName."_backup_".$ShortDate.".sql";

            if (!file_exists("$Backup_File_Dir")) {
                $cmd = "mkdir -p $Backup_File_Dir";
                exec($cmd);
            }

            $fileHandler = fopen($Backup_File_Name, 'w+');
            $number_of_lines = fwrite($fileHandler, $sqlScript);
            fclose($fileHandler);
        }
        $Description = _("$ITSMName")." backup";
    }
    else{
        // Check for maximum number of backups allowed
        $countSql = "SELECT COUNT(*) as count 
                    FROM db_backups
                    WHERE RelatedModule IS NULL;";
        $countResult = mysqli_query($conn, $countSql);
        $countRow = mysqli_fetch_assoc($countResult);
        $numberallowed = 5;

        if ($countRow['count'] >= $numberallowed) {
            return "You have $numberallowed backups, which is the maximum allowed, please delete some before taking any more backups";
        }

        // This is not an ITSM backup, creating full backup
        $tables = array();
        
        $sql = "SHOW TABLES";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_row($result)) {
            $TableName = $row[0];
            if (!in_array($TableName, $ExcludeListDontTouch)) {
                $tables[] = $row[0];
            }
        }

        $sqlScript = "SET FOREIGN_KEY_CHECKS=0;";
        foreach ($tables as $table) {

            if ($table == "db_backups") {
                continue;
            }

            // Prepare SQLscript for DROP Table
            $sqlScript .= "\n" . "DROP TABLE IF EXISTS ". $table . ";\n";

            // Prepare SQLscript for creating table structure
            $query = "SHOW CREATE TABLE $table";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_row($result);

            $sqlScript .= "\n\n" . $row[1] . ";\n\n";


            $query = "SELECT * FROM $table";
            $result = mysqli_query($conn, $query);

            $columnCount = mysqli_num_fields($result);

            // Prepare SQLscript for dumping data for each table
            for ($i = 0; $i < $columnCount; $i++) {
                while ($row = mysqli_fetch_row($result)) {
                    $sqlScript .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $columnCount; $j++) {
                        $row[$j] = mysqli_real_escape_string($conn, $row[$j]);

                        if ($row[$j] !== "") {
                            $sqlScript .= '"' . $row[$j] . '"';
                        } else {
                            $sqlScript .= "NULL";
                        }
                        if ($j < ($columnCount - 1)) {
                            $sqlScript .= ',';
                        }
                    }
                    $sqlScript .= ");\n";
                }
            }
            $sqlScript .= "\n";
        }
        $sqlScript .= "SET FOREIGN_KEY_CHECKS=1;";
        if (!empty($sqlScript)) {
            // Save the SQL script to a backup file
            $Date = date('Y-m-d H:i:s');
            $ShortDate = date("YmdHis", strtotime($Date));
            $RandomString = $functions->generateRandomString(22);

            $Backup_File_Dir = "./backups/db";
            $Backup_File_Name = "$Backup_File_Dir/fulldatabase_backup_".$RandomString."_$ShortDate.sql";

            if (!file_exists("$Backup_File_Dir")) {
                $cmd = "mkdir -p $Backup_File_Dir";
                exec($cmd);
            }

            $fileHandler = fopen($Backup_File_Name, 'w+');
            $number_of_lines = fwrite($fileHandler, $sqlScript);
            fclose($fileHandler);
        }
        $Description = "Database backup";
    }
    $result = registerDatabaseBackup($ITSMTypeID, $Backup_File_Name, $UserID, $Date, $Description);
    return $result;
}

function registerDatabaseBackup($ITSMTypeID, $Backup_File_Name, $UserID, $Date, $Description)
{
    global $functions;

    // SQL statement with placeholders
    $sql = "INSERT INTO db_backups(Date, Description, UserID, RelatedModule, BackupFile)
            VALUES (?, ?, ?, ?, ?);";

    // Check if $ITSMTypeID is empty and assign NULL if necessary
    if (empty($ITSMTypeID)) {
        $ITSMTypeID = NULL;
    }

    // Parameters
    $params = [$Date, $Description, $UserID, $ITSMTypeID, $Backup_File_Name];

    // Tables to lock for manipulation
    $tables = ["db_backups"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check the result and return appropriate response
    if ($result['LastID'] > 0) {
        return "Completed";
    } else {
        $functions->errorlog("Failed to register database backup.", "registerDatabaseBackup");
        return "Failed";
    }
}

function deleteDatabaseBackup($restorepointid)
{
    global $functions;

    if ($restorepointid !== "") {
        // Get physical backup file
        $File = getDatabaseBackupFile($restorepointid);
        deleteFile($File);

        // Check if the file was successfully deleted
        if (!file_exists($File)) {
            // SQL query to delete the backup record
            $sql = "DELETE FROM db_backups WHERE ID = ?";

            // Parameters
            $params = [$restorepointid];

            // Tables to lock for manipulation
            $tables = ["db_backups"];

            // Execute the query using dmlQuery
            $result = $functions->dmlQuery($sql, $params, $tables);

            // Check the result and return the response
            if ($result['LastID'] >= 0) { // Affected rows will also reflect as LastID
                return "Completed";
            } else {
                $functions->errorlog("Failed to delete database backup with ID: $restorepointid", "deleteDatabaseBackup");
                return "Fail";
            }
        } else {
            $functions->errorlog("Failed to delete physical backup file: $File", "deleteDatabaseBackup");
            return "Fail";
        }
    } else {
        $functions->errorlog("Invalid restore point ID provided", "deleteDatabaseBackup");
        return "Fail";
    }
}

function getDatabaseBackupFile($restorepointid)
{
    global $conn;
    global $functions;

    $sql = "SELECT BackupFile
            FROM db_backups
            WHERE ID = $restorepointid;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        $BackupFile = $row[0];
    }

    return $BackupFile;
}

function deleteFile($File)
{
    $cmd = "rm -dfr $File";
    exec($cmd);
}

function restoreDatabaseBackup($restorepointid)
{
    global $conn;
    global $functions;

    if ($restorepointid !== "") {
        // Get physical backup file
        $File = getDatabaseBackupFile($restorepointid);
        $sql = file_get_contents($File);

        if (file_exists("$File")) {

            // Execute multi query
            if ($conn->multi_query($sql)) {
                do {
                    // Store first result set
                    if ($result = $conn->store_result()) {
                        while ($row = $result->fetch_row()) {
                            //printf("%s\n", $row[0]);
                        }
                        $result->free_result();
                    }
                    // if there are more result-sets, the print a divider
                    if ($conn->more_results()) {
                        //printf("-------------\n");
                    }
                    //Prepare next result set
                } while ($conn->next_result());
            }

            return "Completed";
        } else {
            return "Fail";
        }
    } else {
        return "Fail";
    }
}

function optimizeDatabase()
{
    global $conn;
    global $functions;
    $ExcludeListDontTouch = getTableExcludeList();

    // This is not an ITSM backup, creating full backup
    $tables = array();

    $sql = "SHOW TABLES";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        $TableName = $row[0];
        if (!in_array($TableName, $ExcludeListDontTouch)) {
            $tables[] = $row[0];
        }
    }

    foreach ($tables as $table) {

        // Prepare SQLscript for optimization
        $query = "OPTIMIZE TABLE $table;";

        $result = mysqli_query($conn, $query) or die('Query fail: ' . mysqli_error($conn));
    }

    return "Completed";
}

function enrollChangeLogToAll()
{
    global $conn;
    global $functions;

    $TableName = "changelog";
    $DatabaseArray = array();
    $DatabaseExcludes = array("practicle_practicle", "practicle_practiclev3", "practicle_react");
    $sql = "SHOW DATABASES LIKE 'practicle_%';";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $DatabaseName = $row[0];
        if (!in_array($DatabaseName, $DatabaseExcludes)) {
            $DatabaseArray[] = $DatabaseName;
        }
    }

    foreach ($DatabaseArray as $DatabaseName) {
        truncateTable($DatabaseName, $TableName);
        copyChangeLogToDatabases($DatabaseName);
    }
}

function truncateTable($DatabaseName, $TableName)
{
    global $conn;
    global $functions;
    $sql = "TRUNCATE TABLE $DatabaseName.$TableName;";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
}

function copyChangeLogToDatabases($DatabaseName)
{
    global $conn;
    global $functions;
    $sql = "INSERT INTO $DatabaseName.changelog
            SELECT * FROM practicle_practicle.Changelog;";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
}

function getStringBetween($str, $start, $end)
{
    $r = explode($start, $str);
    if (isset($r[1])) {
        $r = explode($end, $r[1]);
        $returnvalue = $r[0];
        echo $returnvalue;
        return $returnvalue;
    }
}

function getElementTable($ModuleID)
{
    global $conn;
    global $functions;
    $sql = "SELECT MainTableName
            FROM modules
            WHERE ID = $ModuleID;";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $MainTableName = $row['MainTableName'];
    }

    return $MainTableName;
}

function getElementSubject2($ID,$TableName)
{
    global $conn;
    global $functions;
    $sql = "SELECT Subject
            FROM $TableName
            WHERE ID = $ID";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Subject = $row['Subject'];
    }

    return $Subject;
}

function getElementDate2($ID, $TableName, $DateField)
{
    global $conn;
    global $functions;
    $sql = "SELECT $DateField
            FROM $TableName
            WHERE ID = $ID;";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $DateCreated = $row["$DateField"];
    }

    return $DateCreated;
}

function getElementDefaultDoingStatus($ModuleID)
{
    global $conn;
    global $functions;
    $sql = "SELECT DoingStatusID
            FROM modules
            WHERE ID = $ModuleID;";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $DoingStatusID = $row['DoingStatusID'];
    }

    return $DoingStatusID;
}

function getElementDefaultDoneStatus($ModuleID)
{
    global $conn;
    global $functions;
    $sql = "SELECT DoneStatusID
            FROM modules
            WHERE ID = $ModuleID;";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $DoneStatusID = $row['DoneStatusID'];
    }

    return $DoneStatusID;
}

function updateKanBanTaskStatus($KanbanTaskID, $Status)
{
    global $conn;
    global $functions;
    $sql = "UPDATE taskslist
            SET Status = '$Status'
            WHERE ID = $KanbanTaskID;";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    $workflowstepsID = getWorkFlowStepID($KanbanTaskID);

    if (!empty($workflowstepsID)) {
        $StatusCodes = array(1, 2, 3);
        if (in_array($Status, $StatusCodes)) {
            updateWorkFlowStepStatus($workflowstepsID, $Status);
        }
    }
}

function getWorkFlowStepID($KanbanTaskID)
{
    global $conn;
    global $functions;
    $sql = "SELECT workflowsteps.ID
            FROM workflowsteps
            WHERE RelatedTaskID = $KanbanTaskID;";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
    }

    return $ID;
}

function getWorkFlowID($ITSMID,$ITSMTypeID)
{
    global $conn;
    global $functions;
    $sql = "SELECT workflows.ID
            FROM workflows
            WHERE RelatedElementID = $ITSMID AND RelatedElementTypeID = $ITSMTypeID;";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
    }

    return $ID;
}

function updateWorkFlowStepStatus($workflowstepsID, $Status)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE workflowsteps SET RelatedStatusID = ? WHERE ID = ?";

    // Parameters for the query
    $params = [$Status, $workflowstepsID];

    // Tables to lock for manipulation
    $tables = ["workflowsteps"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the update was successful
    if ($result['LastID'] >= 0) { // Affected rows will also reflect as LastID
        return true;
    } else {
        $functions->errorlog("Failed to update RelatedStatusID for WorkflowStepID: $workflowstepsID", "updateWorkFlowStepStatus");
        return false;
    }
}

function updateKanbanTaskFromElement($ElementID, $ModuleID, $ElementStatus)
{
    global $conn;
    global $functions;

    $DoneStatus = getElementDefaultDoneStatus($ModuleID);
    $DoingStatus = getElementDefaultDoingStatus($ModuleID);

    if ($ElementStatus == $DoneStatus) {
        closeKanBanTasks($ElementID, $ModuleID);
    }

    if ($ElementStatus == $DoingStatus) {
        startKanBanTasks($ElementID, $ModuleID);
    }
}

function updateKanbanTaskDeadline($Deadline, $ElementID, $ModuleID)
{
    global $functions;

    // SQL statement with placeholders
    $sql = "UPDATE taskslist 
            SET Deadline = ? 
            WHERE RelatedElementID = ? AND RelatedElementTypeID = ?";

    // Parameters
    $params = [$Deadline, $ElementID, $ModuleID];

    // Tables to lock for manipulation
    $tables = ["taskslist"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check for success (affected rows or no error)
    if ($result['LastID'] >= 0) {
        return "Completed";
    } else {
        $functions->errorlog("Failed to update task deadline for ElementID: $ElementID and ModuleID: $ModuleID", "updateKanbanTaskDeadline");
        return "Failed";
    }
}

function updateKanbanTaskResponsible($NewResponsible, $OldResponsible, $ElementID, $ModuleID)
{
    global $functions;

    // Check if the new responsible already has a task
    $ExistingTaskID = checkForExistingTaskNewResponsible($NewResponsible, $ElementID, $ModuleID);

    // If the new responsible does not have a task
    if (empty($ExistingTaskID)) {
        // SQL query to update RelatedUserID and Responsible
        $sql = "UPDATE taskslist 
                SET RelatedUserID = ?, Responsible = ? 
                WHERE RelatedElementID = ? AND RelatedElementTypeID = ? AND RelatedUserID = ? 
                AND Status != '4'";

        // Parameters for the query
        $params = [$NewResponsible, $NewResponsible, $ElementID, $ModuleID, $OldResponsible];

        // Tables to lock
        $tables = ["taskslist"];

        // Execute the query using dmlQuery
        $functions->dmlQuery($sql, $params, $tables);
    }

    // SQL query to update Responsible only
    $sql = "UPDATE taskslist 
            SET Responsible = ? 
            WHERE RelatedElementID = ? AND RelatedElementTypeID = ? AND Status != '4'";

    // Parameters for the second query
    $params = [$NewResponsible, $ElementID, $ModuleID];

    // Execute the query using dmlQuery
    $tables = ["taskslist"];
    $functions->dmlQuery($sql, $params, $tables);
}

function checkForExistingTaskNewResponsible($NewResponsible, $ElementID, $ModuleID)
{
    global $conn;
    global $functions;

    $sql = "SELECT taskslist.ID
            FROM taskslist
            WHERE RelatedElementID = '$ElementID' AND RelatedElementTypeID = $ModuleID AND RelatedUserID = $NewResponsible
            AND Status != '4'";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $TakslistID = $row['ID'];
    }

    return $TakslistID;
}

function getDeadlineForSLAElementID($ElementID, $ModuleID)
{
    $Deadline = "";

    $DoneStatus = getDoneStatusForModule($ModuleID);
    $Deadline = getITSMDeadlineForElement($ElementID, $DoneStatus, $ModuleID);
    return $Deadline;
}

function getDeadlineForElementID($ElementID, $ModuleID)
{
    $Deadline = "";

    switch ($ModuleID) {
        case "1":
            //Ticket
            $DoneStatus = getDoneStatusForModule($ModuleID);
            $SlatimelineTable = getSlaTimelineTableForModule($ModuleID);
            $Deadline = getDeadlineForElement($ElementID, $DoneStatus, $SlatimelineTable);
            break;
        case "2":
            //Request
            $DoneStatus = getDoneStatusForModule($ModuleID);
            $SlatimelineTable = getSlaTimelineTableForModule($ModuleID);
            $Deadline = getDeadlineForElement($ElementID, $DoneStatus, $SlatimelineTable);
            break;
        case "3":
            //Change
            $Deadline = getDeadlineForChange($ElementID);
            break;
        case "4":
            //Problem
            $DoneStatus = getDoneStatusForModule($ModuleID);
            $SlatimelineTable = getSlaTimelineTableForModule($ModuleID);
            $Deadline = getDeadlineForElement($ElementID, $DoneStatus, $SlatimelineTable);
            break;
        case "6":
            //Project
            $Deadline = getDeadlineForProject($ElementID);
            break;
        case "13":
            //Project Task
            //Get Deadline for Project Task
            $Deadline = getDeadlineForProjectTask($ElementID);
            break;
        default:
            //Default action
            $Deadline = date('Y-m-d H:i:s', time() + 604800); 
    }

    return $Deadline;
}

function getITSMDeadlineForElementID($ElementID, $ModuleID)
{
    $Deadline = "";

    if($ModuleID == "6"){
        $Deadline = getDeadlineForProject($ElementID);
    }
    elseif ($ModuleID == "13") {
        $Deadline = getDeadlineForProjectTask($ElementID);
    }
    else{
        $DoneStatus = getITSMDoneStatusForModule($ModuleID);
        $Deadline = getITSMDeadlineForElement($ElementID, $DoneStatus, $ModuleID);
    }
    if($Deadline == ""){
        $Deadline = date("Y-m-d H:i:s");
        $Deadline = date("Y-m-d H:i:s", strtotime("$Deadline +7 day"));
    }

    return $Deadline;
}

function getSlaTimelineTableForModule($ModuleID)
{
    global $conn;
    global $functions;
    $sql = "SELECT slatimelinetable
            FROM modules
            WHERE ID = $ModuleID";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $slatimelinetable = $row['slatimelinetable'];
    }

    return $slatimelinetable;
}

function getDeadlineForElement($ElementID, $DoneStatus, $SlatimelineTable)
{
    global $conn;
    global $functions;
    $sql = "SELECT SLAViolationDate
            FROM $SlatimelineTable
            WHERE RelatedElementID = $ElementID AND RelatedStatusCodeID = $DoneStatus";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Deadline = $row['SLAViolationDate'];
    }

    return $Deadline;
}

function getITSMDeadlineForElement($ElementID, $DoneStatus, $ModuleID)
{
    global $conn;
    global $functions;
    $sql = "SELECT SLAViolationDate
            FROM itsm_slatimelines
            WHERE RelatedElementID = $ElementID AND RelatedStatusCodeID = $DoneStatus AND RelatedElementTypeID = $ModuleID";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Deadline = $row['SLAViolationDate'];
    }
    
    return $Deadline;
}

function getMainTableNameForModule($ModuleID)
{
    global $conn;
    global $functions;
    $sql = "SELECT MainTableName
            FROM modules
            WHERE ID = $ModuleID";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $MainTableName = $row['MainTableName'];
    }

    return $MainTableName;
}

function getDoneStatusForModule($ModuleID)
{
    global $conn;
    global $functions;
    $sql = "SELECT DoneStatus
            FROM itsm_modules
            WHERE ID = $ModuleID";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $DoneStatus = $row['DoneStatus'];
    }

    return $DoneStatus;
}

function getITSMDoneStatusForModule($ModuleID)
{
    global $conn;
    global $functions;
    $sql = "SELECT DoneStatus
            FROM itsm_modules
            WHERE ID = $ModuleID";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $DoneStatus = $row['DoneStatus'];
    }

    return $DoneStatus;
}

function getDeadlineForProjectTask($ElementID)
{
    global $conn;
    global $functions;
    $sql = "SELECT Deadline
            FROM project_tasks
            WHERE ID = $ElementID";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Deadline = $row['Deadline'];
    }

    return $Deadline;
}

function getDeadlineForProject($ElementID)
{
    global $conn;
    global $functions;
    $sql = "SELECT Deadline
            FROM projects
            WHERE ID = $ElementID";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Deadline = $row['Deadline'];
    }

    return $Deadline;
}

function closeKanBanTasks($ElementID, $ModuleID)
{
    global $functions;

    // SQL query to update the status to '3' (closed)
    $sql = "UPDATE taskslist
            SET Status = '3'
            WHERE RelatedElementID = ? AND RelatedElementTypeID = ? AND taskslist.Status != 4";

    // Parameters for the query
    $params = [$ElementID, $ModuleID];

    // Tables to lock
    $tables = ["taskslist"];

    // Execute the query using dmlQuery
    $functions->dmlQuery($sql, $params, $tables);
}

function startKanBanTasks($ElementID, $ModuleID)
{
    global $functions;

    // SQL query to update the status to '2' (started)
    $sql = "UPDATE taskslist
            SET Status = '2'
            WHERE RelatedElementID = ? AND RelatedElementTypeID = ? AND taskslist.Status != 4";

    // Parameters for the query
    $params = [$ElementID, $ModuleID];

    // Tables to lock
    $tables = ["taskslist"];

    // Execute the query using dmlQuery
    $functions->dmlQuery($sql, $params, $tables);
}

function sanitizeTextAndBase64($Text, $ElementID, $ModuleID)
{
    global $conn;
    global $functions;

    $SystemURL = $functions->getSettingValue(17);
    $UserID = $_SESSION["id"];

    //Define regex filter for preg_match_all base64 string
    $Regex = '/src="(data:image\/[^;]+;base64[^"]+)"/';

    //Set percentage of large image - we will do original size
    $percent = 0.85;

    //If preg_match_all lets cycle through each picture and create jpg files
    if (preg_match_all($Regex, $Text, $Matches, PREG_SET_ORDER, 1)) {
        //We hit jackpot with preg_match and will now extract base64 string and create jpgs
        foreach ($Matches as $Picture) {
            //Lets generate jpg filenames for both large picture and thumbnail
            $PictureName = $functions->generateRandomString(10);
            $PictureNameNoExt = $PictureName;
            $PictureName = $PictureName . ".jpg";
            $Exists = doesDatabaseEntryForCommentImageExist($PictureName);

            while ($Exists != 0) {
                $PictureName = $functions->generateRandomString(10);
                $PictureNameNoExt = $PictureName;
                $PictureName = $PictureName . ".jpg";
                $Exists = doesDatabaseEntryForCommentImageExist($PictureName);
            }

            //Picture string
            $TempPicture = $Picture[1];

            //Extract clean base64 image string
            $TempString = str_replace("data:image/png;base64,", "", $TempPicture);

            //Create thumbnail picture
            //$Thumbnail = createThumbnailFromBase64($TempString);

            //Decode
            $data = base64_decode($TempString);

            //Create Image from string
            $im = imagecreatefromstring($data);

            $width = imagesx($im);
            $height = imagesy($im);
            $newwidth = $width * $percent;
            $newheight = $height * $percent;

            $TempImage = imagecreatetruecolor($newwidth, $newheight);

            imagecopyresampled($TempImage, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

            ob_start();
            imagejpeg($TempImage, "./uploads/comment_images/$PictureName", 90);
            createDatabaseEntryForCommentImages($PictureName, $TempString, $UserID, $ElementID, $ModuleID);
            ob_get_clean();
            ob_end_clean();
            imagedestroy($TempImage);

            $ReplaceImage = "<a href=\"javascript:openViewImageModal('$PictureNameNoExt');\"><img id=\"$PictureNameNoExt\" src=\"$SystemURL/uploads/comment_images/$PictureName\" style=\"width:100%;max-width:200px\"></a>";

            $Text = str_replace("$TempPicture", "$ReplaceImage", $Text);
            $Text = str_replace("<img src=\"<a", "<a", $Text);
            $Text = str_replace("</a>\" ", "</a>", $Text);
            $Text = str_replace("</a>\">", "</a>", $Text);
            $Text = str_replace("style=\"font-size: var(--bs-body-font-size); text-align: var(--bs-body-text-align);\">", "", $Text);
            $Text = str_replace("style=\"font-family: var(--bs-body-font-family); font-size: var(--bs-body-font-size); text-align: var(--bs-body-text-align);\">", "", $Text);
            $Text = str_replace("style=\"color: var(--bs-body-color); font-size: var(--bs-body-font-size); text-align: var(--bs-body-text-align);\">", "", $Text);
            $Text = mysqli_real_escape_string($conn, $Text);
        }
    }

    return $Text;
}

function createThumbnailFromBase64($Base64ImageString)
{
    $maxWidth = 200;
    $maxHeight = 200;
    $data = base64_decode($Base64ImageString);
    $im = imagecreatefromstring($data);

    $maxAspect = $maxWidth / $maxHeight; //Figure out the aspect ratio of the desired limits 
    $origWidth = imagesx($im); //Get the width of the uploaded image 
    $origHeight = imagesy($im); //Get the height of the uploaded image 
    $origAspect = $origWidth / $origHeight; //Get the aspect ratio of the uploaded image 
    if (($origWidth > $maxWidth) || ($origHeight > $maxHeight)) { //See if we actually need to do anything 
        if ($origAspect <= $maxAspect) { //If the aspect ratio of the uploaded image is less than or equal to the target size... 
            $newWidth = $maxHeight * $origAspect; //Resize the image based on the height 
            $newHeight = $maxHeight;
        } else { //If the ratio is greater... 
            $newWidth = $maxWidth; //Resize based on width 
            $newHeight = $maxWidth / $origAspect;
        }
        $om = imagecreatetruecolor($newWidth, $newHeight); //Create the target image 
        imagecopyresampled($om, $im, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight); //actually do the resizing 
        return ($om); //Return the result 
    } else {
        return ($im); //Or don't do anything and just return the input. 
    }
}

function createDatabaseEntryForCommentImages($PictureName, $Base64String, $UserID, $ElementID, $ModuleID)
{
    global $conn;
    global $functions;

    $sql = "INSERT INTO comment_pictures (PictureName,Location,CreatedBy,ElementID,ModuleID)
            VALUES ('$PictureName','./uploads/comment_images/$PictureName','$UserID','$ElementID','$ModuleID');";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));
}

function doesDatabaseEntryForCommentImageExist($PictureName)
{
    global $conn;
    global $functions;

    $exists = "1";

    $sql = "SELECT COUNT(PictureName) AS Antal
            FROM comment_pictures
            WHERE PictureName = '$PictureName'";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Antal = $row['Antal'];
    }

    if (empty($Antal)) {
        $exists = "0";
    }

    return $exists;
}

function doesUsernameExist($Username, $Email)
{
    global $conn;
    global $functions;

    $exists = false;

    $TempEmail = $functions->getUserIDFromEmail($Email);

    if (!empty($TempEmail)) {
        $exists = true;
        return $exists;
    } else {
        return $exists;
    }
}

function deleteAllServers()
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM cis WHERE RelatedClassID = 1;
            ALTER TABLE ci_servers AUTO_INCREMENT = 1";
    $result = mysqli_multi_query($conn, $sql) or die(mysqli_error($conn));
}

function getAuthenticatedFromLDAP($username, $password)
{
    global $functions;

    $returnvalue = false;
    $ldap_enabled = $functions->getSettingValue(56);

    if ($ldap_enabled == "1") {
        $ldap_password = $functions->getSettingValue(51);
        $ldap_username = $functions->getSettingValue(52);
        $ldap_hostname = $functions->getSettingValue(53);
        $ldap_domain = $functions->getSettingValue(57);
        $ldap_hostname = "ldap://" . $ldap_hostname . "." . $ldap_domain . ":389";
        $ldap_base_dn = $functions->getSettingValue(54);
        $ldap_version = $functions->getSettingValue(55);
        $ldap_connection = ldap_connect($ldap_hostname);
        
        if (!$ldap_connection) {
            return $returnvalue;
        }

        // We have to set this option for the version of Active Directory we are using.
        if (!ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, $ldap_version)) {
            return $returnvalue;
        }

        ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.

        // Check if the username is in email format
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $LDAPUsername = $username;
        } else {
            $LDAPUsername = $username . "@" . $ldap_domain;
        }

        $ldap_bind = @ldap_bind($ldap_connection, $LDAPUsername, $password);
        if (!$ldap_bind) {
           ldap_close($ldap_connection);
            return $returnvalue;
        } else {
            $returnvalue = true;
            ldap_close($ldap_connection);
            return $returnvalue;
        }
    } else {
        return $returnvalue;
    }

    return $returnvalue;
}

function addUserToRole($Username, $RoleID)
{
    global $functions;

    // Retrieve UserID from the username
    $RetrievedUserID = getUserIDFromUsername($Username);

    // SQL query to insert the user into the role
    $sql = "INSERT INTO usersroles (UserID, RoleID) VALUES (?, ?)";

    // Parameters for the query
    $params = [$RetrievedUserID, $RoleID];

    // Tables to lock
    $tables = ["usersroles"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the insertion was successful
    if ($result['LastID'] > 0) {
        return true;
    } else {
        $functions->errorlog("Failed to add user $Username to role $RoleID", "addUserToRole");
        return false;
    }
}

function addUserToGroup($UserID, $GroupID)
{
    global $functions;

    // Check if the row already exists
    $checkSql = "SELECT COUNT(*) as count FROM usersgroups WHERE UserID = ? AND GroupID = ?";
    $checkParams = [$UserID, $GroupID];
    $checkTables = ["usersgroups"];

    // Execute the check query using dmlQuery
    $checkResult = $functions->dmlQuery($checkSql, $checkParams, $checkTables);

    if ($checkResult['Result'] && $checkResult['Result'][0]['count'] > 0) {
        // Row already exists
        return false;
    }

    // If the row doesn't exist, insert it
    $insertSql = "INSERT INTO usersgroups (UserID, GroupID) VALUES (?, ?)";
    $insertParams = [$UserID, $GroupID];
    $insertTables = ["usersgroups"];

    $insertResult = $functions->dmlQuery($insertSql, $insertParams, $insertTables);

    // Check if the insertion was successful
    if ($insertResult['LastID'] > 0) {
        return true;
    } else {
        $functions->errorlog("Failed to add user $UserID to group $GroupID", "addUserToGroup");
        return false;
    }
}

function addUserIDToRole($UserID, $RoleID)
{
    global $functions;

    // Check if the row already exists
    $checkSql = "SELECT COUNT(*) as count FROM usersroles WHERE UserID = ? AND RoleID = ?";
    $checkParams = [$UserID, $RoleID];
    $checkTables = ["usersroles"];

    // Execute the check query using dmlQuery
    $checkResult = $functions->dmlQuery($checkSql, $checkParams, $checkTables);

    if ($checkResult['Result'] && $checkResult['Result'][0]['count'] > 0) {
        // Row already exists
        return false;
    }

    // If the row doesn't exist, insert it
    $insertSql = "INSERT INTO usersroles (UserID, RoleID) VALUES (?, ?)";
    $insertParams = [$UserID, $RoleID];
    $insertTables = ["usersroles"];

    $insertResult = $functions->dmlQuery($insertSql, $insertParams, $insertTables);

    // Check if the insertion was successful
    if ($insertResult['LastID'] > 0) {
        return true;
    } else {
        $functions->errorlog("Failed to add UserID $UserID to RoleID $RoleID", "addUserIDToRole");
        return false;
    }
}

function removeUsersFromRole($RoleID)
{
    global $functions;

    // SQL query to delete users from the role, except for UserID 1
    $sql = "DELETE FROM usersroles WHERE RoleID = ? AND UserID != 1";

    // Parameters for the query
    $params = [$RoleID];

    // Tables to lock
    $tables = ["usersroles"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the deletion was successful
    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to remove users from RoleID $RoleID", "removeUsersFromRole");
        return false;
    }
}

function doesTeamExist($TeamName)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID, Teamname FROM teams WHERE Teamname = '$TeamName'";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['ID'];
    }

    return $Value;
}

function createTeam($TeamName)
{
    global $functions;

    // SQL query to insert a new team
    $sql = "INSERT INTO teams (Teamname, Colour) VALUES (?, '1')";

    // Parameters for the query
    $params = [$TeamName];

    // Tables to lock
    $tables = ["teams"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Return the last inserted ID
    return $result['LastID'] > 0 ? $result['LastID'] : null;
}

function deleteTeamRelations($TeamID)
{
    global $functions;

    // SQL query to delete team-user relations
    $sql = "DELETE FROM usersteams WHERE TeamID = ?";

    // Parameters for the query
    $params = [$TeamID];

    // Tables to lock
    $tables = ["usersteams"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the deletion was successful
    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to delete team relations for TeamID $TeamID", "deleteTeamRelations");
        return false;
    }
}

function insertUserOnTeam($UserID, $TeamID)
{
    global $functions;

    // SQL query to insert a user into a team
    $sql = "INSERT INTO usersteams (UserID, TeamID) VALUES (?, ?)";

    // Parameters for the query
    $params = [$UserID, $TeamID];

    // Tables to lock
    $tables = ["usersteams"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the insertion was successful
    if ($result['LastID'] > 0) {
        return true;
    } else {
        $functions->errorlog("Failed to insert UserID $UserID into TeamID $TeamID", "insertUserOnTeam");
        return false;
    }
}

function getUsersAttachedGroupsAttachedInRole($Groups)
{
    global $conn;
    global $functions;

    $Array = [];

    $sql = "SELECT UserID
            FROM usersgroups
            LEFT JOIN users ON usersgroups.UserID = users.ID
            WHERE usersgroups.GroupID IN ($Groups) AND users.Active = 1 AND users.RelatedUserTypeID IN (1,3)
            UNION
            SELECT UserID
            FROM usersroles
            LEFT JOIN usergroupsroles ON usersroles.RoleID = usergroupsroles.RoleID
            LEFT JOIN users ON usersroles.UserID = users.ID
            WHERE usergroupsroles.GroupID IN ($Groups) AND users.Active = 1 AND users.RelatedUserTypeID IN (1,3)";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Array[] = $row['UserID'];
    }

    return $Array;
}

function getFormFieldNameFromFieldID($FieldID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldName 
            FROM forms_fieldslist
            WHERE forms_fieldslist.ID = $FieldID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $FieldName = $row['FieldName'];
    }
    return $FieldName;
}

function getCIFieldNameFromFieldID($FieldID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldName 
            FROM cmdb_ci_fieldslist
            WHERE cmdb_ci_fieldslist.ID = $FieldID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $FieldName = $row['FieldName'];
    }
    return $FieldName;
}

function getITSMFieldNameFromFieldID($FieldID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldName 
            FROM itsm_fieldslist
            WHERE itsm_fieldslist.ID = $FieldID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $FieldName = $row['FieldName'];
    }

    return $FieldName;
}

function updateITSMFieldValue($FieldID, $FieldValue, $FieldName, $ITSMTypeID)
{
    global $functions;

    // Get the ITSM table name based on the ITSM type
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

    // SQL query to update the field value dynamically
    $sql = "UPDATE $ITSMTableName SET $FieldName = ? WHERE ID = ?;";

    // Parameters for the query
    $params = [$FieldValue, $FieldID];

    // Tables to lock
    $tables = [$ITSMTableName];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the update was successful
    if ($result['LastID'] >= 0) { // Affected rows will also reflect as LastID
        return true;
    } else {
        $functions->errorlog("Failed to update field $FieldName in table $ITSMTableName with ID $FieldID", "updateITSMFieldValue");
        return false;
    }
}

function getFormsFieldDefaultValue($FieldName, $FormID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldDefaultValue
            FROM forms_fieldslist
            WHERE RelatedFormID = $FormID AND FieldName = '$FieldName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $FieldDefaultValue = $row["FieldDefaultValue"];
    }
    return $FieldDefaultValue;
}

function getCIFieldDefaultValue($FieldName, $CITypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldDefaultValue
            FROM cmdb_ci_fieldslist
            WHERE RelatedCITypeID = $CITypeID AND FieldName = '$FieldName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $FieldDefaultValue = $row["FieldDefaultValue"];
    }
    return $FieldDefaultValue;
}

function getITSMFieldDefaultValue($FieldName, $ITSMTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldDefaultValue
            FROM itsm_fieldslist
            WHERE RelatedTypeID = $ITSMTypeID AND FieldName = '$FieldName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $FieldDefaultValue = $row["FieldDefaultValue"];
    }
    return $FieldDefaultValue;
}

function getCITypeName($CITypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT cmdb_cis.Name
            FROM cmdb_cis
            WHERE ID = $CITypeID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Name = $row["Name"];
    }
    return $Name;
}

function getCIsForOverview()
{
    global $conn;
    global $functions;
    $group_array = $_SESSION['memberofgroups'];

    $ID = "";
    $Name = "";
    $Description = "";
    $LastSyncronized = "";
    
    $array[] = array();
    $sql = "SELECT ID, Name, Description, LastSyncronized
            FROM cmdb_cis
            WHERE Active = 1 AND cmdb_cis.GroupID IN (" . implode(",", $group_array) . ")";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ID = $row["ID"];
        $Name = _($row["Name"]);
        $Description = _($row["Description"]);
        $LastSyncronized = $row["LastSyncronized"];
        if($LastSyncronized == ""){
            $LastSyncronized = "never";
        }
        else{
            $LastSyncronized = convertToDanishTimeFormat($row["LastSyncronized"]);
        }
        $array[] = array("ID" => $ID, "Name" => $Name, "Description" => $Description, "LastSyncronized" => $LastSyncronized);
    }
    usort($array, fn ($a, $b) => $a["Name"] <=> $b["Name"]);
    return $array;
}

function getITSMsForOverview()
{
    global $conn;
    global $functions;
    $array[] = array();
    $sql = "SELECT itsm_modules.ID, itsm_modules.Name, itsm_modules.Description, itsm_modules.LastSyncronized, itsm_modules.Type, itsm_types.MenuPage
            FROM itsm_modules
            LEFT JOIN itsm_types ON itsm_modules.Type = itsm_types.ID
            WHERE Active = '1'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $ID = $row["ID"];
        $Name = $row["Name"];
        $Description = $row["Description"];
        $MenuPage = $row["MenuPage"];
        $LastSyncronized = $row["LastSyncronized"];
        if ($LastSyncronized == "") {
            $LastSyncronized = "never";
        } else {
            $LastSyncronized = convertToDanishTimeFormat($row["LastSyncronized"]);
        }
        $array[] = array("ID" => $ID, "Name" => _("$Name"), "MenuPage" => $MenuPage, "Description" => _("$Description"), "LastSyncronized" => $LastSyncronized);
    }
    usort($array, fn ($a, $b) => $a['Name'] <=> $b['Name']);
    return $array;
}

function generateRandomNumber($length)
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function checkIfFormsTableNameExists($TableName)
{
    global $conn;
    global $functions;

    $sql = "SELECT TableName FROM Forms WHERE TableName = '$TableName';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $returnvalue = $row['TableName'];
    }

    return $returnvalue;
}

function createDuplicateFormsFieldTable($OldTableName, $NewTableName)
{
    global $conn;
    global $functions;

    $sql = "CREATE TABLE $NewTableName
            LIKE $OldTableName;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql2 = "TRUNCATE TABLE $NewTableName;";
    $result2 = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));
}

function getRequestFormFieldValue($FormTableName, $FieldName, $RelatedFormTableRowID)
{
    global $conn;
    global $functions;

    $sql = "SELECT $FieldName FROM $FormTableName WHERE ID = '$RelatedFormTableRowID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $SettingValue = $row["$FieldName"];
    }

    if (empty($SettingValue)) {
        $SettingValue = "";
    }

    return $SettingValue;
}

function getRequestFormID($ITSMID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedFormID
            FROM itsm_requests
            WHERE ID = '$ITSMID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $RelatedFormID = $row["RelatedFormID"];
    }

    return $RelatedFormID;
}

function getFormTableName($FormID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TableName
            FROM forms
            WHERE ID = '$FormID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $TableName = $row["TableName"];
    }

    return $TableName;
}

function duplicateFormEntry($ITSMID, $NewITMSID, $FormTableName){
    global $conn;
    global $functions;
    $FieldArray = array();
    $Temp = "";

    $sql = "DESCRIBE `$FormTableName`;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        array_push($FieldArray, $row["Field"]);
    }

    foreach ($FieldArray as $Field){
        if(stripos($Field,"FormField") !== FALSE){
            $Temp .= "$Field,";
        }
    }

    $Temp = substr($Temp, 0, strlen($Temp) - 1);


    // Step 1: Insert a new form entry by duplicating an existing one
    $sqlInsert = "INSERT INTO $FormTableName($Temp)
                  SELECT $Temp
                  FROM $FormTableName
                  WHERE RelatedRequestID = ?";
    $paramsInsert = [$ITSMID];
    $tablesInsert = [$FormTableName];

    // Execute the insert query
    $insertResult = $functions->dmlQuery($sqlInsert, $paramsInsert, $tablesInsert);

    // Check if the insert was successful
    $NewFormEntryID = $insertResult['LastID'];
    if ($NewFormEntryID <= 0) {
        $functions->errorlog("Failed to duplicate form entry for RelatedRequestID: $ITSMID", "duplicateFormEntry");
        return false;
    }

    // Step 2: Update the new form entry's RelatedRequestID
    $sqlUpdate = "UPDATE $FormTableName
                  SET RelatedRequestID = ?
                  WHERE ID = ?";
    $paramsUpdate = [$NewITSMID, $NewFormEntryID];
    $tablesUpdate = [$FormTableName];

    // Execute the update query
    $updateResult = $functions->dmlQuery($sqlUpdate, $paramsUpdate, $tablesUpdate);

    // Check if the update was successful
    if ($updateResult['LastID'] >= 0) {
        return $NewFormEntryID; // Return the ID of the new form entry
    } else {
        $functions->errorlog("Failed to update RelatedRequestID for FormEntryID: $NewFormEntryID", "duplicateFormEntry");
        return false;
    }
}

function getCompanyIDFromUserID($UserID)
{
    global $conn;
    global $functions;

    $sql = "SELECT CompanyID 
            FROM users 
            WHERE ID = '$UserID';";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $value = $row['CompanyID'];
    }
    return $value;
}

function updateRelatedRequestOnFormFieldRecord($TableName, $RecordID, $RequestID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE $TableName SET RelatedRequestID = ? WHERE ID = ?";

    // Parameters for the query
    $params = [$RequestID, $RecordID];

    // Tables to lock for manipulation
    $tables = [$TableName];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the update was successful
    if ($result['LastID'] >= 0) { // Affected rows will also reflect as LastID
        return true;
    } else {
        $functions->errorlog("Failed to update RelatedRequestID to $RequestID for RecordID: $RecordID in Table: $TableName", "updateRelatedRequestOnFormFieldRecord");
        return false;
    }
}

function getDefaultCMDBCIFields()
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldName,
                DBFieldType,
                FieldLabel, 
                RelationShowField, 
                FieldType,
                FieldOrder, 
                FieldDefaultValue, 
                FieldWidth,
                LookupTable, 
                LookupField, 
                LookupFieldResultTable, 
                LookupFieldResultView, 
                SelectFieldOptions,
                ResultFields,
                UserFullName,
                HideForms,
                HideTables,
                Required,
                LockedCreate,
                LockedView,
                Addon,
                AddEmpty,
                FullHeight,
                RightColumn,
                LabelType
            FROM cmdb_ci_default_fields";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $Array = array(); // Initialize the array

    while ($row = mysqli_fetch_array($result)) {
        $Array[] = array(
            'FieldName' => $row['FieldName'],
            'DBFieldType' => $row['DBFieldType'],
            'FieldType' => $row['FieldType'],
            'FieldLabel' => $row['FieldLabel'],
            'RelationShowField' => $row['RelationShowField'],
            'FieldOrder' => $row['FieldOrder'],
            'FieldDefaultValue' => $row['FieldDefaultValue'],
            'FieldWidth' => $row['FieldWidth'],
            'LookupTable' => $row['LookupTable'],
            'LookupField' => $row['LookupField'],
            'LookupFieldResultTable' => $row['LookupFieldResultTable'],
            'LookupFieldResultView' => $row['LookupFieldResultView'],
            'SelectFieldOptions' => $row['SelectFieldOptions'],
            'ResultFields' => $row['ResultFields'],
            'UserFullName' => $row['UserFullName'],
            'HideForms' => $row['HideForms'],
            'HideTables' => $row['HideTables'],
            'Required' => $row['Required'],
            'LockedCreate' => $row['LockedCreate'],
            'LockedView' => $row['LockedView'],
            'Addon' => $row['Addon'],
            'AddEmpty' => $row['AddEmpty'],
            'FullHeight' => $row['FullHeight'],
            'RightColumn' => $row['RightColumn'],
            'LabelType' => $row['LabelType'],
            'DefaultField' => '1' // Assuming this is a static value you need to add
        );
    }

    return $Array;
}

function getDefaultITSMFields()
{
    global $conn;
    global $functions;

    $sql = "SELECT * FROM itsm_default_fields";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $Array = mysqli_fetch_all($result, MYSQLI_ASSOC);

    return $Array;
}

function getFormsLookupTableSelectOptions($LookupTable, $LookupField, $LookupFieldResult, $FormID, $FieldName, $AddEmpty)
{
    global $conn;
    global $functions;

    $SearchFilter = "";
    $Result = "";

    $DefaultValue = getFormsFieldDefaultValue($FieldName, $FormID);

    if ($FieldName == "Responsible") {
        $DefaultValue = $_SESSION["id"];
    }
    if ($FieldName == "Team") {
        $DefaultValue = $_SESSION["teamid"];
    }

    // Check if the LookupTable has a column named "Active"
    $columnExists = false;
    $result = $conn->query("DESCRIBE $LookupTable");
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'Active') {
            $columnExists = true;
            break;
        }
    }

    $options = [];

    if ($columnExists) {
        $sql = "SELECT $LookupField AS ID, $LookupFieldResult AS Result
                FROM $LookupTable
                WHERE Active = 1 $SearchFilter";
    } else {
        $sql = "SELECT $LookupField AS ID, $LookupFieldResult AS Result
                FROM $LookupTable";
    }

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $Value = $functions->translate($row['Result']);
        $options[] = ['ID' => $ID, 'Value' => $Value];
    }

    // Sort the options array by the 'Value' key
    usort($options, function ($a, $b) {
        return strcmp($a['Value'], $b['Value']);
    });

    // Generate the HTML options after sorting
    foreach ($options as $option) {
        $ID = $option['ID'];
        $Value = $option['Value'];

        if ($ID == $DefaultValue) {
            $Result .= "<option value=\"$ID\" selected>$Value</option>";
        } else {
            $Result .= "<option value=\"$ID\">$Value</option>";
        }
    }

    return $Result;
}

function getCILookupTableSelectOptions($LookupTable, $LookupField, $LookupFieldResult, $CITypeID, $FieldName, $AddEmpty)
{
    global $conn;
    global $functions;

    $Result = "";

    $DefaultValue = getCIFieldDefaultValue($FieldName, $CITypeID);
    if ($FieldName == "Responsible") {
        $DefaultValue = $_SESSION["id"];
    }
    if ($FieldName == "Team") {
        $DefaultValue = $_SESSION["teamid"];
    }

    if ($AddEmpty == "1") {
        $Result .= "<option></option>"; // Add an empty option
    }

    // Check if the 'Active' column exists in the table
    $hasActiveColumn = false;
    $checkColumnSql = "SHOW COLUMNS FROM $LookupTable LIKE 'Active'";
    $checkResult = mysqli_query($conn, $checkColumnSql) or die('Query fail: ' . mysqli_error($conn));

    if (mysqli_num_rows($checkResult) > 0) {
        $hasActiveColumn = true;
    }

    // Build the SQL query based on whether the 'Active' column exists
    $sql = "SELECT $LookupField AS ID, $LookupFieldResult AS Result
            FROM $LookupTable";
    if ($hasActiveColumn) {
        $sql .= " WHERE Active = 1";
    }

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $optionsArray = [];

    // Fetch the results into an array
    while ($row = mysqli_fetch_array($result)) {
        $TempValue = $functions->translate($row['Result']);
        $optionsArray[] = [
            'ID' => $row['ID'],
            'Value' => $TempValue
        ];
    }

    // Sort the array by 'Value'
    usort($optionsArray, function ($a, $b) {
        return strcmp($a['Value'], $b['Value']);
    });

    // Generate the options
    foreach ($optionsArray as $option) {
        $ID = $option['ID'];
        $Value = $option['Value'];

        if ($ID == $DefaultValue) {
            $Result .= "<option data-id=\"$ID\" value=\"$ID\" selected>$Value</option>";
        } else {
            $Result .= "<option data-id=\"$ID\" value=\"$ID\">$Value</option>";
        }
    }

    return $Result;
}

function getITSMLookupTableSelectOptions($LookupTable, $LookupField, $LookupFieldResult, $ITSMTypeID, $FieldName, $FieldType, $AddEmpty)
{
    global $conn;
    global $functions;

    $SearchFilter = "";
    $Result = "";

    if ($FieldType == "11") {
        $SearchFilter = " AND RelatedModuleID = '$ITSMTypeID'";
    }

    $DefaultValue = getITSMFieldDefaultValue($FieldName, $ITSMTypeID);
    if ($FieldName == "Responsible") {
        $DefaultValue = $_SESSION["id"];
    }
    if ($FieldName == "Team") {
        $DefaultValue = $_SESSION["teamid"];
    }

    if ($AddEmpty == "1") {
        $Result .= "<option></option>"; // Add an empty option
    }

    // Check if the LookupTable has a column named "Active"
    $columnExists = false;
    $result = $conn->query("DESCRIBE $LookupTable");
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'Active') {
            $columnExists = true;
            break;
        }
    }

    $optionsArray = [];

    if ($columnExists) {
        $sql = "SELECT $LookupField AS ID, $LookupFieldResult AS Result
                FROM $LookupTable
                WHERE Active = 1 $SearchFilter";
    } else {
        $sql = "SELECT $LookupField AS ID, $LookupFieldResult AS Result
                FROM $LookupTable";
    }

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $optionsArray[] = [
            'ID' => $row['ID'],
            'Value' => $functions->translate($row['Result'])
        ];
    }

    // Sort the array by 'Value'
    usort($optionsArray, function ($a, $b) {
        return strcmp($a['Value'], $b['Value']);
    });

    // Generate the datalist options
    foreach ($optionsArray as $option) {
        $ID = $option['ID'];
        $Value = $option['Value'];
        if ($ID == $DefaultValue) {
            $Result .= "<option data-id=\"$ID\" value=\"$ID\" selected>$Value</option>";
        } else {
            $Result .= "<option data-id=\"$ID\" value=\"$ID\">$Value</option>";
        }
    }

    return $Result;
}

function getITSMStatusOptions($ITSMTypeID, $AddEmpty)
{
    global $conn;
    global $functions;

    $Result = "";

    if ($AddEmpty == "1") {
        $Result = "<option value=\"\"></option>"; // Add an empty option
    }

    $sql = "SELECT StatusCode, StatusName
            FROM itsm_statuscodes
            WHERE ModuleID = '$ITSMTypeID'
            ORDER BY StatusCode ASC;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $StatusCode = $row['StatusCode'];
        $StatusName = $functions->translate($row['StatusName']);
        $DefaultValue = getITSMFieldDefaultValue("Status", $ITSMTypeID);
        if ($StatusCode == $DefaultValue) {
            $Result .= "<option data-id=\"$StatusCode\" value=\"$StatusCode\" selected=\"true\">$StatusName</option>";
        } else {
            $Result .= "<option data-id=\"$StatusCode\" value=\"$StatusCode\">$StatusName</option>";
        }
    }
    return $Result;
}

function getShowInRelationFields($CITypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldName
            FROM cmdb_ci_fieldslist
            WHERE RelationShowField = 1 AND RelatedCITypeID = $CITypeID";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldName = $row["FieldName"];
        $ReturnResult = array("FieldName" => "$FieldName");
    }
    return $ReturnResult;
}

function getITSMShowInRelationFields($ITSMTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldName
            FROM itsm_fieldslist
            WHERE RelationShowField = 1 AND RelatedTypeID = $ITSMTypeID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldName = $row["FieldName"];
        $ReturnResult = array("FieldName" => "$FieldName");
    }
    return $ReturnResult;
}

function updateCIRelationLookup($FieldID, $NewStringValue)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE cmdb_ci_fieldslist SET RelationsLookup = ? WHERE ID = ?";

    // Parameters for the query
    $params = [$NewStringValue, $FieldID];

    // Tables to lock
    $tables = ["cmdb_ci_fieldslist"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the update was successful
    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to update RelationsLookup for FieldID: $FieldID", "updateCIRelationLookup");
        return false;
    }
}

function updateSelectOptions($FieldID, $NewStringValue, $TableName)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE $TableName SET SelectFieldOptions = ? WHERE ID = ?";

    // Parameters for the query
    $params = [$NewStringValue, $FieldID];

    // Tables to lock
    $tables = [$TableName];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the update was successful
    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to update SelectFieldOptions for FieldID: $FieldID in Table: $TableName", "updateSelectOptions");
        return false;
    }
}

function updateGroupFilter($FieldID, $NewStringValue, $TableName)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE $TableName SET GroupFilterOptions = ? WHERE ID = ?";

    // Parameters for the query
    $params = [$NewStringValue, $FieldID];

    // Tables to lock
    $tables = [$TableName];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the update was successful
    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to update GroupFilterOptions for FieldID: $FieldID in Table: $TableName", "updateGroupFilter");
        return false;
    }
}

function getCITypeIDFromTableName($CITableName)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID
            FROM cmdb_cis
            WHERE TableName = '$CITableName'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $value = $row["ID"];
    }
    return $value;
}

function getCITypeIDFromFieldID($FieldID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedCITypeID
            FROM cmdb_ci_fieldslist
            WHERE ID = '$FieldID'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $value = $row["RelatedCITypeID"];
    }
    return $value;
}

function getITSMTypeIDFromFieldID($FieldID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedTypeID
            FROM itsm_fieldslist
            WHERE ID = '$FieldID'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $value = $row["RelatedTypeID"];
    }
    return $value;
}

function getFormTypeIDFromFieldID($FieldID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedFormID
            FROM forms_fieldslist
            WHERE ID = '$FieldID'";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $value = $row["RelatedFormID"];
    }
    return $value;
}

function alterTableAddColumn($TableName, $FieldName, $FieldType)
{
    global $conn;
    global $functions;

    $sql = "ALTER TABLE $TableName ADD COLUMN $FieldName $FieldType;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function alterTableColumnName($TableName, $FieldNamePre, $FieldNamePost)
{
    global $conn;
    global $functions;

    // Get the column definition for the existing column
    $columnDefinition = getColumnDefinition($TableName, $FieldNamePre);

    // Escape table and column names for use in SQL query
    $tableNameSafe = mysqli_real_escape_string($conn, $TableName);
    $fieldNamePreSafe = mysqli_real_escape_string($conn, $FieldNamePre);
    $fieldNamePostSafe = mysqli_real_escape_string($conn, $FieldNamePost);

    // Construct the SQL query to change the column name and type
    $sql = "ALTER TABLE `$tableNameSafe` CHANGE `$fieldNamePreSafe` `$fieldNamePostSafe` " .
           "{$columnDefinition['COLUMN_TYPE']} " .
           ($columnDefinition['IS_NULLABLE'] == 'YES' ? 'NULL' : 'NOT NULL') . " " .
           "COMMENT '{$columnDefinition['COLUMN_COMMENT']}'";

    // Execute the query
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die('Query fail: ' . mysqli_error($conn));
    }

    return true; // Return true if the query executes successfully
}

function getColumnDefinition($TableName, $ColumnName)
{
    global $conn;
    global $functions;
    $dbName = getCurrentDB();

    // Escape table and column names
    $tableNameSafe = mysqli_real_escape_string($conn, $TableName);
    $columnNameSafe = mysqli_real_escape_string($conn, $ColumnName);

    // Query to get column definition
    $sql = "SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '$dbName'
            AND TABLE_NAME = '$tableNameSafe'
            AND COLUMN_NAME = '$columnNameSafe'";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die('Query fail: ' . mysqli_error($conn));
    }

    $row = mysqli_fetch_assoc($result);

    // Free result set
    mysqli_free_result($result);

    return $row;
}

function alterTableAddFullTextToColumn($TableName, $FieldName)
{
    global $conn;
    global $functions;

    $dbName = getCurrentDB();

    $checkSql = "SELECT COUNT(*) AS index_count
                 FROM information_schema.statistics
                 WHERE table_schema = '$dbName'
                 AND table_name = '$TableName'
                 AND index_type = 'FULLTEXT'
                 AND column_name = '$FieldName'";

    $result = mysqli_query($conn, $checkSql) or die('Query fail: ' . mysqli_error($conn));
    $row = mysqli_fetch_assoc($result);

    if ($row['index_count'] > 0) {
        return;
    }

    $sql = "ALTER TABLE $TableName ADD FULLTEXT($FieldName);";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function alterTableRemoveFullTextOnColumn($TableName, $FieldName)
{
    global $conn;
    global $functions;

    $dbName = getCurrentDB();

    $checkSql = "SELECT COUNT(*) AS index_count
                 FROM information_schema.statistics
                 WHERE table_schema = '$dbName'
                 AND table_name = '$TableName'
                 AND index_type = 'FULLTEXT'
                 AND column_name = '$FieldName'";

    $result = mysqli_query($conn, $checkSql) or die('Query fail: ' . mysqli_error($conn));
    $row = mysqli_fetch_assoc($result);

    if ($row['index_count'] > 0) {
        $sql = "ALTER TABLE $TableName DROP INDEX $FieldName;";
        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }
}

function insertCMDBFieldToFieldListTable(
    $RelatedCI,
    $FieldName,
    $FieldLabel,
    $RelationShowField,
    $FieldType,
    $FieldOrder,
    $FieldDefaultValue,
    $FieldWidth,
    $DefaultField,
    $SelectFieldOptions,
    $LookupTable,
    $LookupField,
    $LookupFieldResultTable,
    $LookupFieldResultView,
    $ResultFields,
    $UserFullName,
    $HideForms,
    $HideTables,
    $Required,
    $LockedCreate,
    $LockedView,
    $Addon,
    $AddEmpty,
    $FullHeight,
    $RightColumn,
    $LabelType
) {
    global $conn;
    global $functions;

    $sql = "INSERT INTO cmdb_ci_fieldslist (
                RelatedCITypeID, 
                FieldName, 
                FieldLabel, 
                RelationShowField, 
                FieldType, 
                FieldOrder, 
                FieldDefaultValue, 
                FieldWidth, 
                DefaultField, 
                LookupTable, 
                LookupField, 
                LookupFieldResultTable, 
                LookupFieldResultView, 
                SelectFieldOptions,
                ResultFields,
                UserFullName,
                HideForms,
                HideTables,
                Required,
                LockedCreate,
                LockedView,
                Addon,
                AddEmpty,
                FullHeight,
                RightColumn,
                LabelType
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    try {
        // Prepare the statement
        if (!$stmt = $conn->prepare($sql)) {
            throw new Exception('Prepare failed: ' . htmlspecialchars($conn->error));
        }

        // Ensure the parameters are correctly typed for null values
        $params = [
            $RelatedCI,
            $FieldName,
            $FieldLabel,
            $RelationShowField,
            $FieldType,
            $FieldOrder,
            $FieldDefaultValue,
            $FieldWidth,
            $DefaultField,
            $LookupTable,
            $LookupField,
            $LookupFieldResultTable,
            $LookupFieldResultView,
            $SelectFieldOptions,
            $ResultFields,
            $UserFullName,
            $HideForms,
            $HideTables,
            $Required,
            $LockedCreate,
            $LockedView,
            $Addon,
            $AddEmpty,
            $FullHeight,
            $RightColumn,
            $LabelType
        ];

        // Bind the parameters to the statement, accounting for possible NULLs
        $null_type = [];
        foreach ($params as $i => &$param) {
            if (is_null($param)) {
                $param = null;
                $null_type[$i] = 's';  // Treat NULL as a string for binding
            } else {
                $null_type[$i] = (is_int($param) ? 'i' : (is_double($param) ? 'd' : 's'));
            }
        }

        $types = implode('', $null_type);

        // Bind the parameters
        if (!$stmt->bind_param($types, ...$params)) {
            throw new Exception('Bind_param failed: ' . htmlspecialchars($stmt->error));
        }

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . htmlspecialchars($stmt->error));
        }

        // Close the statement
        $stmt->close();
    } catch (Exception $e) {
        $functions->errorlog($e->getMessage(), "insertCMDBFieldToFieldListTable");
    }
}

function insertITSMFieldToFieldListTable(
    $RelatedITSM,
    $FieldName,
    $FieldLabel,
    $RelationShowField,
    $FieldType,
    $FieldOrder,
    $FieldDefaultValue,
    $FieldWidth,
    $DefaultField,
    $SelectFieldOptions,
    $LookupTable,
    $LookupField,
    $LookupFieldResultTable,
    $LookupFieldResultView,
    $ResultFields,
    $UserFullName,
    $HideForms,
    $HideTables,
    $Required,
    $LockedCreate,
    $LockedView,
    $Addon,
    $AddEmpty,
    $FullHeight,
    $RightColumn,
    $LabelType,
    $FieldTitle,
    $ImportSourceField,
    $SyncSourceField,
    $RelationsLookup,
    $Indexed
) {
    global $functions;

    // SQL query with placeholders
    $sql = "INSERT INTO itsm_fieldslist(
                RelatedTypeID,
                FieldName, 
                FieldLabel, 
                RelationShowField, 
                FieldType, 
                FieldOrder, 
                FieldDefaultValue, 
                FieldWidth, 
                DefaultField, 
                LookupTable, 
                LookupField, 
                LookupFieldResultTable, 
                LookupFieldResultView, 
                SelectFieldOptions,
                ResultFields,
                UserFullName,
                HideForms,
                HideTables,
                Required,
                LockedCreate,
                LockedView,
                Addon,
                AddEmpty,
                FullHeight,
                RightColumn,
                LabelType,
                FieldTitle,
                ImportSourceField,
                SyncSourceField,
                RelationsLookup,
                Indexed
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Parameters for the query
    $params = [
        $RelatedITSM,
        $FieldName,
        $FieldLabel,
        $RelationShowField,
        $FieldType,
        $FieldOrder,
        $FieldDefaultValue,
        $FieldWidth,
        $DefaultField,
        $LookupTable,
        $LookupField,
        $LookupFieldResultTable,
        $LookupFieldResultView,
        $SelectFieldOptions,
        $ResultFields,
        $UserFullName,
        $HideForms,
        $HideTables,
        $Required,
        $LockedCreate,
        $LockedView,
        $Addon,
        $AddEmpty,
        $FullHeight,
        $RightColumn,
        $LabelType,
        $FieldTitle,
        $ImportSourceField,
        $SyncSourceField,
        $RelationsLookup,
        $Indexed
    ];

    // Tables to lock
    $tables = ["itsm_fieldslist"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the insert was successful
    if ($result['LastID'] > 0) {
        return $result['LastID']; // Return the ID of the inserted row
    } else {
        $functions->errorlog("Failed to insert field into itsm_fieldslist", "insertITSMFieldToFieldListTable");
        return false;
    }
}

function dropCITable($CITableName)
{
    global $conn;
    global $functions;

    // Check if the table exists
    $checkSql = "SELECT COUNT(*) AS table_count
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE()
                 AND table_name = '$CITableName'";

    $checkResult = mysqli_query($conn, $checkSql) or die('Query fail: ' . mysqli_error($conn));
    $tableCount = mysqli_fetch_assoc($checkResult)['table_count'];

    if ($tableCount > 0) {
        // Drop the table
        $sql = "DROP TABLE $CITableName;";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }

    return true;
}

function dropITSMTable($ITSMTableName)
{
    global $conn;
    global $functions;

    // Check if the table exists
    $checkSql = "SELECT COUNT(*) AS table_count
                 FROM information_schema.tables
                 WHERE table_schema = DATABASE()
                 AND table_name = '$ITSMTableName'";

    $checkResult = mysqli_query($conn, $checkSql) or die('Query fail: ' . mysqli_error($conn));
    $tableCount = mysqli_fetch_assoc($checkResult)['table_count'];

    if ($tableCount > 0) {
        // Drop the table
        $sql = "DROP TABLE $ITSMTableName;";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    }

    return true;
}

function deleteCIFromCMDBCITable($CIID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM cmdb_cis WHERE ID = ?";

    // Parameters for the query
    $params = [$CIID];

    // Tables to lock
    $tables = ["cmdb_cis"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the deletion was successful
    if ($result['LastID'] >= 0) { // Affected rows will also reflect as LastID
        return true;
    } else {
        $functions->errorlog("Failed to delete CIID: $CIID from cmdb_cis", "deleteCIFromCMDBCITable");
        return false;
    }
}

function deleteITSMFromITSMTable($ITSMID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM itsm_modules WHERE ID = ?";

    // Parameters for the query
    $params = [$ITSMID];

    // Tables to lock
    $tables = ["itsm_modules"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Check if the deletion was successful
    if ($result['LastID'] >= 0) { // Affected rows will also reflect as LastID
        return true;
    } else {
        $functions->errorlog("Failed to delete ITSMID: $ITSMID from itsm_modules", "deleteITSMFromITSMTable");
        return false;
    }
}

function deleteITSMs($ITSMTableName)
{
    global $conn;
    global $functions;

    $db = getCurrentDB();

    $sql = "TRUNCATE TABLE $db.$ITSMTableName;";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deleteCIFields($CIID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM cmdb_ci_fieldslist WHERE RelatedCITypeID = ?";

    // Parameters for the query
    $params = [$CIID];

    // Tables to lock
    $tables = ["cmdb_ci_fieldslist"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to delete CI fields for RelatedCITypeID: $CIID", "deleteCIFields");
        return false;
    }
}

function deleteITSMFields($ITSMID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM itsm_fieldslist WHERE RelatedTypeID = ?";

    // Parameters for the query
    $params = [$ITSMID];

    // Tables to lock
    $tables = ["itsm_fieldslist"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to delete ITSM fields for RelatedTypeID: $ITSMID", "deleteITSMFields");
        return false;
    }
}

function deleteCILogs($CITypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM log_cis WHERE RelatedType = ?";

    // Parameters for the query
    $params = [$CITypeID];

    // Tables to lock
    $tables = ["log_cis"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to delete CI logs for RelatedType: $CITypeID", "deleteCILogs");
        return false;
    }
}

function deleteITSMLogs($ITSMTypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM itsm_log WHERE RelatedType = ?";

    // Parameters for the query
    $params = [$ITSMTypeID];

    // Tables to lock
    $tables = ["itsm_log"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to delete ITSM logs for RelatedType: $ITSMTypeID", "deleteITSMLogs");
        return false;
    }
}

function deleteITSMStatusCodes($ITSMTypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM itsm_statuscodes WHERE ModuleID = ?";

    // Parameters for the query
    $params = [$ITSMTypeID];

    // Tables to lock
    $tables = ["itsm_statuscodes"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to delete ITSM status codes for ModuleID: $ITSMTypeID", "deleteITSMStatusCodes");
        return false;
    }
}

function deleteITSMSLAMatrix($ITSMTypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM itsm_sla_matrix WHERE RelatedModuleID = ?";

    // Parameters
    $params = [$ITSMTypeID];

    // Tables to lock
    $tables = ["itsm_sla_matrix"];

    // Execute the query
    $result = $functions->dmlQuery($sql, $params, $tables);

    return $result['LastID'] >= 0;
}

function deleteITSMSLATimelines($ITSMTypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM itsm_slatimelines WHERE RelatedElementTypeID = ?";

    // Parameters
    $params = [$ITSMTypeID];

    // Tables to lock
    $tables = ["itsm_slatimelines"];

    // Execute the query
    $result = $functions->dmlQuery($sql, $params, $tables);

    return $result['LastID'] >= 0;
}

function deleteITSMParticipants($ITSMTypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM itsm_participants WHERE ModuleID = ?";

    // Parameters
    $params = [$ITSMTypeID];

    // Tables to lock
    $tables = ["itsm_participants"];

    // Execute the query
    $result = $functions->dmlQuery($sql, $params, $tables);

    return $result['LastID'] >= 0;
}

function deleteITSMTasks($ITSMTypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM taskslist WHERE RelatedElementTypeID = ?";

    // Parameters
    $params = [$ITSMTypeID];

    // Tables to lock
    $tables = ["taskslist"];

    // Execute the query
    $result = $functions->dmlQuery($sql, $params, $tables);

    return $result['LastID'] >= 0;
}

function deleteITSMWorkFlows($ITSMTypeID)
{
    global $functions;

    // Delete associated workflow steps
    $sqlDeleteSteps = "DELETE FROM workflowsteps WHERE RelatedWorkFlowID IN (SELECT ID FROM workflows WHERE RelatedElementTypeID = ?)";
    $paramsDeleteSteps = [$ITSMTypeID];
    $tablesDeleteSteps = ["workflowsteps", "workflows"];
    $functions->dmlQuery($sqlDeleteSteps, $paramsDeleteSteps, $tablesDeleteSteps);

    // Delete workflows
    $sqlDeleteWorkflows = "DELETE FROM workflows WHERE RelatedElementTypeID = ?";
    $paramsDeleteWorkflows = [$ITSMTypeID];
    $tablesDeleteWorkflows = ["workflows"];
    $functions->dmlQuery($sqlDeleteWorkflows, $paramsDeleteWorkflows, $tablesDeleteWorkflows);

    return true;
}

function deleteITSMComments($ITSMTypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM itsm_comments WHERE ITSMType = ?";

    // Parameters
    $params = [$ITSMTypeID];

    // Tables to lock
    $tables = ["itsm_comments"];

    // Execute the query
    $result = $functions->dmlQuery($sql, $params, $tables);

    return $result['LastID'] >= 0;
}

function deleteCIRelations($CITableName)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM cmdb_ci_relations WHERE CITable1 = ? OR CITable2 = ?";

    // Parameters for the query
    $params = [$CITableName, $CITableName];

    // Tables to lock
    $tables = ["cmdb_ci_relations"];

    // Execute the query
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to delete CI relations for CITableName: $CITableName", "deleteCIRelations");
        return false;
    }
}

function deleteITSMRelations($ITSMTableName)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM itsm_relations WHERE Table1 = ? OR Table2 = ?";

    // Parameters for the query
    $params = [$ITSMTableName, $ITSMTableName];

    // Tables to lock
    $tables = ["itsm_relations"];

    // Execute the query
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to delete ITSM relations for ITSMTableName: $ITSMTableName", "deleteITSMRelations");
        return false;
    }
}

function deleteCIFiles($CITypeID)
{
    global $functions;

    // SQL query to select files
    $selectSql = "SELECT FileName FROM files_cis WHERE RelatedType = ?";
    $selectParams = [$CITypeID];

    // Retrieve file names using selectQuery
    $files = $functions->selectQuery($selectSql, $selectParams);

    if (!empty($files)) {
        foreach ($files as $row) {
            $FileName = $row['FileName'];
            $filePointer = "../uploads/files_cis/$FileName";

            // Attempt to delete the file
            if (!unlink($filePointer)) {
                $functions->errorlog("File $filePointer cannot be deleted due to an error", "deleteCIFiles");
            } else {
                echo "$filePointer has been deleted\n";
            }
        }
    }

    // SQL query to delete records from the database
    $deleteSql = "DELETE FROM files_cis WHERE RelatedType = ?";
    $deleteParams = [$CITypeID];
    $deleteTables = ["files_cis"];

    // Execute the delete query using dmlQuery
    $deleteResult = $functions->dmlQuery($deleteSql, $deleteParams, $deleteTables);

    if ($deleteResult['LastID'] >= 0) {
        return true; // Deletion was successful
    } else {
        $functions->errorlog("Failed to delete files for RelatedType: $CITypeID", "deleteCIFiles");
        return false;
    }
}

function deleteITSMFiles($ITSMTypeID)
{
    global $functions;

    // SQL query to select files
    $selectSql = "SELECT FileName FROM files_itsm WHERE RelatedType = ?";
    $selectParams = [$ITSMTypeID];

    // Retrieve file names using selectQuery
    $files = $functions->selectQuery($selectSql, $selectParams);

    if (!empty($files)) {
        foreach ($files as $row) {
            $FileName = $row['FileName'];
            $filePointer = "../uploads/files_itsm/$FileName";

            // Attempt to delete the file
            if (!unlink($filePointer)) {
                $functions->errorlog("File $filePointer cannot be deleted due to an error", "deleteITSMFiles");
            } else {
                echo "$filePointer has been deleted\n";
            }
        }
    }

    // SQL query to delete records from the database
    $deleteSql = "DELETE FROM files_itsm WHERE RelatedType = ?";
    $deleteParams = [$ITSMTypeID];
    $deleteTables = ["files_itsm"];

    // Execute the delete query using dmlQuery
    $deleteResult = $functions->dmlQuery($deleteSql, $deleteParams, $deleteTables);

    if ($deleteResult['LastID'] >= 0) {
        return true; // Deletion was successful
    } else {
        $functions->errorlog("Failed to delete files for RelatedType: $ITSMTypeID", "deleteITSMFiles");
        return false;
    }
}

function getCITypeFromFieldID($FieldID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedCITypeID
            FROM cmdb_ci_fieldslist
            WHERE ID = $FieldID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $RelatedCITypeID = $row['RelatedCITypeID'];
    }
    return $RelatedCITypeID;
}

function getITSMTypeFromFieldID($FieldID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedTypeID
            FROM itsm_fieldslist
            WHERE ID = $FieldID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $RelatedTypeID = $row['RelatedTypeID'];
    }
    return $RelatedTypeID;
}

function getCITypeFromTableName($TableName)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID
            FROM cmdb_cis
            WHERE TableName = '$TableName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
    }
    return $ID;
}

function getCITableName($CITypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TableName
            FROM cmdb_cis
            WHERE ID = $CITypeID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $TableName = $row['TableName'];
    }
    return $TableName;
}

function removeAllCIRelations($TableName)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM cmdb_ci_relations WHERE CITable1 = ? OR CITable2 = ?";

    // Parameters for the query
    $params = [$TableName, $TableName];

    // Tables to lock
    $tables = ["cmdb_ci_relations"];

    try {
        // Execute the query using dmlQuery
        $result = $functions->dmlQuery($sql, $params, $tables);

        if ($result['LastID'] >= 0) {
            return true; // Deletion was successful
        } else {
            $functions->errorlog("Failed to remove CI relations for TableName: $TableName", "removeAllCIRelations");
            return false;
        }
    } catch (Exception $e) {
        // Log the exception
        $functions->errorlog('Error removing CI relations: ' . $e->getMessage(), "removeAllCIRelations");
        return false;
    }
}

function truncateTableCurrentDB($TableName)
{
    global $conn;
    global $functions;

    $result = mysqli_query($conn, "SELECT DATABASE() AS db") or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $db = $row['db'];
    }

    $sql2 = "TRUNCATE $db.$TableName";

    $result2 = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));
}

function getCIFieldToWorkAsID($CITypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldName
            FROM cmdb_ci_fieldslist
            WHERE RelatedCITypeID = $CITypeID AND RelationShowField  = '1'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldName = $row['FieldName'];
    }
    return $FieldName;
}

function getITSMFormsFieldTypeID($FormsID, $FieldName)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldType
            FROM forms_fieldslist
            WHERE RelatedFormID = '$FormsID' AND FieldName  = '$FieldName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldType = $row['FieldType'];
    }

    return $FieldType;
}

function getCMDBFieldTypeID($CITypeID, $FieldName)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldType
            FROM cmdb_ci_fieldslist
            WHERE RelatedCITypeID = '$CITypeID' AND FieldName  = '$FieldName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldType = $row['FieldType'];
    }

    return $FieldType;
}

function getCMDBFieldHideFormsState($CITypeID, $FieldName)
{
    global $conn;
    global $functions;

    $sql = "SELECT HideForms
            FROM cmdb_ci_fieldslist
            WHERE RelatedCITypeID = '$CITypeID' AND FieldName  = '$FieldName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $HideForms = $row['HideForms'];
    }

    return $HideForms;
}

function getCISourceImportFieldName($CITypeID, $CIIDFieldName)
{
    global $conn;
    global $functions;

    $sql = "SELECT ImportSourceField
            FROM cmdb_ci_fieldslist
            WHERE RelatedCITypeID = '$CITypeID' AND FieldName  = '$CIIDFieldName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ImportSourceField = $row['ImportSourceField'];
    }
    return $ImportSourceField;
}

function getCIFieldsToImport($CITypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID,RelatedCITypeID,FieldName, ImportSourceField, DefaultField
            FROM cmdb_ci_fieldslist
            WHERE (RelatedCITypeID = '$CITypeID' AND ImportSourceField != '') OR (RelatedCITypeID = '$CITypeID' AND DefaultField = '1' AND FieldDefaultValue != '');";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldName = $row['FieldName'];
        $ImportSourceField = $row['ImportSourceField'];
        $DefaultField = $row['DefaultField'];
        $FieldArray[] = array("FieldName" => $FieldName, "ImportSourceField" => $ImportSourceField, "DefaultField" => $DefaultField);
    }

    return $FieldArray;
}

function getCIFieldsToSync($CITypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ID,RelatedCITypeID,FieldName, ImportSourceField, DefaultField
            FROM cmdb_ci_fieldslist
            WHERE (RelatedCITypeID = '$CITypeID' AND SyncSourceField != '' AND DefaultField = '0');";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldName = $row['FieldName'];
        $ImportSourceField = $row['ImportSourceField'];
        $DefaultField = $row['DefaultField'];
        $FieldArray[] = array("FieldName" => $FieldName, "ImportSourceField" => $ImportSourceField, "DefaultField" => $DefaultField);
    }

    return $FieldArray;
}

function runSpecificQuery($sql)
{
    global $conn;
    global $functions;

    if (!mysqli_query($conn, $sql)) {
        $functions->errorlog("SQL Error: $conn->error", "runSpecificQuery");
        throw new Exception("Error description: " . $conn->error. " SQL: " . $sql);
    }
}

function getCIDefaultFieldDefaultValue($FieldName, $CITypeID)
{
    global $conn;
    global $functions;

    try {
        $sql = "SELECT FieldDefaultValue
                FROM cmdb_ci_fieldslist
                WHERE RelatedCITypeID = '$CITypeID' AND FieldName = '$FieldName';";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_array($result)) {
            $FieldDefaultValue = $row["FieldDefaultValue"];
        }
        return $FieldDefaultValue;
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
    }
}

function doesCIExist($TableName, $CIIDFieldName, $IDColumn, $CITypeID)
{
    global $functions;

    try {
        // Step 1: Get all relevant fields from cmdb_ci_fieldslist table
        $fieldQuery = "SELECT FieldName FROM cmdb_ci_fieldslist WHERE RelatedCITypeID = ? AND DefaultField = 0";
        $fieldParams = [$CITypeID];

        // Retrieve fields using selectQuery
        $fieldResults = $functions->selectQuery($fieldQuery, $fieldParams);

        // Collect the fields in an array
        $fields = array_map(fn($row) => $row['FieldName'], $fieldResults);

        // Step 2: Construct the dynamic SQL query to select the record from the target table
        $selectSql = "SELECT $CIIDFieldName, " . implode(", ", $fields) . " FROM $TableName WHERE $CIIDFieldName = ?";
        $selectParams = [$IDColumn];

        // Retrieve the record using selectQuery
        $recordResults = $functions->selectQuery($selectSql, $selectParams);

        // If no record found, return an empty string to indicate creation
        if (empty($recordResults)) {
            return "";
        }

        // Step 3: Compare the existing data with the new data
        $row = $recordResults[0];
        $differences = 0;

        foreach ($fields as $field) {
            if (isset($_POST[$field])) { // Assuming the new data comes from a POST request
                $newValue = $_POST[$field];
                $existingValue = $row[$field];

                if ($newValue != $existingValue) {
                    $differences++;
                }
            }
        }

        // Step 4: If 3 or more fields are different, treat it as a new record
        if ($differences >= 3) {
            return ""; // Indicate that a new record should be created
        }

        // Return the ID if the record exists and is similar enough
        return $row[$CIIDFieldName];
    } catch (Exception $e) {
        // Log any exceptions
        $functions->errorlog("Error in doesCIExist: " . $e->getMessage(), "doesCIExist");
        return ""; // Default to indicating a new record should be created
    }
}

function getStatusOfCI($TableName, $ID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Active
            FROM $TableName
            WHERE ID = $ID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Status = $row["Active"];
    }
    return $Status;
}

function deactivateCI($CITableName, $CITypeID, $CIID)
{
    global $conn;
    global $functions;

    try {
        $sql = "UPDATE $CITableName
                SET Active = 0
                WHERE ID = '$CIID';";

        mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        $LogActionText = "CI not found in import source, deactivated by sync job";
        createCILogEntry($CIID, $CITypeID, "1", $LogActionText);
        setEndDateOnCI($CITableName, $CITypeID, $CIID);
    } catch (Exception $e) {
        $functions->errorlog("Error: " . $e->getMessage(), "deactivateCI");
    }
}

function setEndDateOnCI($CITableName, $CITypeID, $CIID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE $CITableName SET EndDate = Now() WHERE ID = ?";

    // Parameters for the query
    $params = [$CIID];

    // Tables to lock
    $tables = [$CITableName];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        $LogActionText = "CI EndDate set to " . date("Y-m-d H:i:s") . " by sync job";
        createCILogEntry($CIID, $CITypeID, "1", $LogActionText);
        return true;
    } else {
        $functions->errorlog("Failed to set EndDate for CIID: $CIID in $CITableName", "setEndDateOnCI");
        return false;
    }
}

function removeEndDateOnCI($CITableName, $CITypeID, $CIID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE $CITableName SET EndDate = '' WHERE ID = ?";

    // Parameters for the query
    $params = [$CIID];

    // Tables to lock
    $tables = [$CITableName];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        $LogActionText = "CI EndDate removed by sync job";
        createCILogEntry($CIID, $CITypeID, "1", $LogActionText);
        return true;
    } else {
        $functions->errorlog("Failed to remove EndDate for CIID: $CIID in $CITableName", "removeEndDateOnCI");
        return false;
    }
}

function setStartDateOnCI($CITableName, $CITypeID, $CIID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE $CITableName SET StartDate = Now() WHERE ID = ?";

    // Parameters for the query
    $params = [$CIID];

    // Tables to lock
    $tables = [$CITableName];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        $LogActionText = "CI StartDate set to " . date("Y-m-d H:i:s") . " by sync job";
        createCILogEntry($CIID, $CITypeID, "1", $LogActionText);
        return true;
    } else {
        $functions->errorlog("Failed to set StartDate for CIID: $CIID in $CITableName", "setStartDateOnCI");
        return false;
    }
}

function getCIStatus($CITableName, $CITypeID, $CIID)
{
    global $conn;
    global $functions;

    $sql = "SELECT Active
            FROM $CITableName
            WHERE ID = '$CIID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $Value = $row['Active'];
    }

    return $Value;
}

function setCIStatusActive($CITableName, $CITypeID, $CIID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE $CITableName SET Active = 1 WHERE ID = ?";

    // Parameters for the query
    $params = [$CIID];

    // Tables to lock
    $tables = [$CITableName];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        $LogActionText = "CI was not active but found in import source, CI reactivated by sync job";
        createCILogEntry($CIID, $CITypeID, "1", $LogActionText);
        setStartDateOnCI($CITableName, $CITypeID, $CIID);
        return true;
    } else {
        $functions->errorlog("Failed to set CI status active for CIID: $CIID in $CITableName", "setCIStatusActive");
        return false;
    }
}

function getCILookupField($CITypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT FieldName FROM cmdb_ci_fieldslist WHERE RelatedCITypeID = ? AND RelationShowField = 1";

    // Parameters for the query
    $params = [$CITypeID];

    // Retrieve the field name using selectQuery
    $fields = $functions->selectQuery($sql, $params);

    if (!empty($fields)) {
        return $fields[0]['FieldName'];
    } else {
        $functions->errorlog("No lookup field found for CITypeID: $CITypeID", "getCILookupField");
        return null; // Return null if no field found
    }
}

function getITSMLookupField($ITSMTypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT FieldLabel FROM itsm_fieldslist WHERE RelatedTypeID = ? AND RelationShowField = 1";

    // Parameters for the query
    $params = [$ITSMTypeID];

    // Retrieve the field label using selectQuery
    $fields = $functions->selectQuery($sql, $params);

    if (!empty($fields)) {
        return $fields[0]['FieldLabel'];
    } else {
        $functions->errorlog("No lookup field found for ITSMTypeID: $ITSMTypeID", "getITSMLookupField");
        return null; // Return null if no field found
    }
}

function getCINameFromTableName($CITableName)
{
    global $functions;

    if ($CITableName === "businessservices") {
        return _("businessservices");
    } else {
        // SQL query with placeholders
        $sql = "SELECT Name FROM cmdb_cis WHERE TableName = ?";

        // Parameters for the query
        $params = [$CITableName];

        // Retrieve the name using selectQuery
        $records = $functions->selectQuery($sql, $params);

        if (!empty($records)) {
            return $records[0]['Name'];
        } else {
            $functions->errorlog("No CI name found for CITableName: $CITableName", "getCINameFromTableName");
            return null;
        }
    }
}

function getCINameFromTypeID($CITypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT Name FROM cmdb_cis WHERE ID = ?";

    // Parameters for the query
    $params = [$CITypeID];

    // Retrieve the name using selectQuery
    $records = $functions->selectQuery($sql, $params);

    if (!empty($records)) {
        return $records[0]['Name'];
    } else {
        $functions->errorlog("No CI name found for CITypeID: $CITypeID", "getCINameFromTypeID");
        return null;
    }
}

function getITSMNameFromTableName($ITSMTableName)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT Name FROM itsm_modules WHERE TableName = ?";

    // Parameters for the query
    $params = [$ITSMTableName];

    // Retrieve the name using selectQuery
    $records = $functions->selectQuery($sql, $params);

    if (!empty($records)) {
        return $records[0]['Name'];
    } else {
        $functions->errorlog("No ITSM name found for TableName: $ITSMTableName", "getITSMNameFromTableName");
        return null;
    }
}

function getITSMNameFromITSMType($ITSMTypeID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT Name FROM itsm_modules WHERE ID = ?";

    // Parameters for the query
    $params = [$ITSMTypeID];

    // Retrieve the name using selectQuery
    $records = $functions->selectQuery($sql, $params);

    if (!empty($records)) {
        return $functions->translate($records[0]['Name']);
    } else {
        $functions->errorlog("No ITSM name found for ITSMTypeID: $ITSMTypeID", "getITSMNameFromITSMType");
        return null;
    }
}

function getCIFields($CIID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT FieldName, FieldLabel 
            FROM cmdb_ci_fieldslist 
            WHERE RelatedCITypeID = ? AND DefaultField = 0 
            ORDER BY FieldOrder ASC";

    // Parameters for the query
    $params = [$CIID];

    // Retrieve the fields using selectQuery
    $fields = $functions->selectQuery($sql, $params);

    if (!empty($fields)) {
        return array_map(function ($row) {
            return [
                'FieldName' => $row['FieldName'],
                'Label' => $row['FieldLabel']
            ];
        }, $fields);
    } else {
        $functions->errorlog("No CI fields found for CIID: $CIID", "getCIFields");
        return [];
    }
}

function getITSMActiveStatusCodes($ITSMTypeID)
{
    global $conn;
    global $functions;

    $Array = array();
    if($ITSMTypeID == "3"){
        $sql = "SELECT StatusCode
                FROM itsm_statuscodes
                WHERE ModuleID = $ITSMTypeID AND StatusCode IN (1,2,3,4,5,6)
                ORDER BY StatusCode ASC";
    }
    else{
        $sql = "SELECT StatusCode
                FROM itsm_statuscodes
                WHERE ModuleID = $ITSMTypeID AND StatusCode IN (1,2,3,4,5)
                ORDER BY StatusCode ASC";
    }
    
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        array_push($Array,$row["StatusCode"]);
    }
    return $Array;
}

function getCIFileToImport($CITypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT ImportSource
            FROM cmdb_cis
            WHERE ID = $CITypeID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $file = $row["ImportSource"];
    }

    return $file;
}

function getCIIDFromIDColumnName($IDColumn, $CITableName, $CIIDFieldName){
    global $conn;
    global $functions;

    $sql = "SELECT ID
            FROM $CITableName
            WHERE $CIIDFieldName = '$IDColumn'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ID = $row["ID"];
    }

    return $ID;
}

function getCIValueFromRelation($ElementID, $CITableName, $ColumnName)
{
    global $conn;
    global $functions;

    $sql = "SELECT $ColumnName
            FROM $CITableName
            WHERE ID = '$ElementID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $value = $row["$ColumnName"];
    }

    return $value;
}

function getCIIDFromElementRelationID($RelationID)
{
    global $conn;
    global $functions;

    $sql = "SELECT CIID
            FROM cmdb_ci_itsm_relations
            WHERE ID = '$RelationID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $CIID = $row["CIID"];
    }

    return $CIID;
}

function getCITypeFromFileID($FileID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedType
            FROM files_cis
            WHERE ID = '$FileID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["RelatedType"];
    }

    return $Value;
}

function getCITypeFromDocName($DocName)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedType
            FROM files_cis
            WHERE FileName = '$DocName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["RelatedType"];
    }

    return $Value;
}

function getITSMTypeFromDocName($DocName)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedType
            FROM files_itsm
            WHERE FileName = '$DocName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["RelatedType"];
    }

    return $Value;
}

function getCIOriginalFileID($FileID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FileNameOriginal
            FROM files_cis
            WHERE ID = '$FileID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["FileNameOriginal"];
    }

    return $Value;
}

function getCIOriginalDocName($DocName)
{
    global $conn;
    global $functions;

    $sql = "SELECT FileNameOriginal
            FROM files_cis
            WHERE FileName = '$DocName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["FileNameOriginal"];
    }

    return $Value;
}

function getITSMOriginalDocName($DocName)
{
    global $conn;
    global $functions;

    $sql = "SELECT FileNameOriginal
            FROM files_itsm
            WHERE FileName = '$DocName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["FileNameOriginal"];
    }

    return $Value;
}

function getCIIDFromDocName($DocName)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedElementID
            FROM files_cis
            WHERE FileName = '$DocName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ID = $row["RelatedElementID"];
    }

    return $ID;
}

function getCIIDFromFileID($FileID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedElementID
            FROM files_cis
            WHERE ID = '$FileID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ID = $row["RelatedElementID"];
    }

    return $ID;
}

function getCIFileNameFromFileID($FileID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FileName
            FROM files_cis
            WHERE ID = '$FileID'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FileName = $row["FileName"];
    }

    return $FileName;
}

function getITSMIDFromDocName($DocName)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedElementID
            FROM files_itsm
            WHERE FileName = '$DocName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $value = $row["RelatedElementID"];
    }

    return $value;
}

function truncateCITable($CITableName){
    global $conn;
    global $functions;

    $sql = "TRUNCATE TABLE $CITableName;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function importCI($CITypeID)
{
    global $conn;
    global $functions;

    $fname = getCIFileToImport($CITypeID);

    if (!($fp = fopen($fname, 'r'))) {
        return "File $fname could not be opened";
    } else {
        $file_parts = pathinfo($fname);
    }

    switch ($file_parts['extension']) {
        case "csv":
            //read csv headers
            $key = fgetcsv($fp, "2048", ",");
            $keys = implode(",", $key);
            // parse csv rows into array
            $data = array();

            while ($row = fgetcsv($fp, "2048", ",")) {
                $rows = implode(",", $row);
                //$code = mb_detect_encoding($rows);
                $rows = mb_convert_encoding($rows, "UTF-8");
                $rows = mysqli_real_escape_string($conn, $rows);
                $row = explode(",", $rows);
                $data[] = array_combine($key, $row);
            }

            // release file handle
            fclose($fp);

            //$jsondata = json_encode($array);

            break;
        case "json":

            $jsondata = file_get_contents($fname);
            //$jsondata = $fp;
            $data = json_decode($jsondata, true);

            break;

        case "": // Handle file extension for files ending in '.'
            return "File needs correct file extension that is csv or json";

        case NULL: // Handle no file extension
            return "File has no file extension, ensure correct extension that is csv or json";
    }

    //Convert data to array

    //lets clean up existing data before new import
    $CITableName = getCITableName($CITypeID);
    truncateTableCurrentDB($CITableName);
    removeAllCIRelations($CITableName);

    //Declare important variables
    //First lets get ID column name
    $CIIDFieldName = getCIFieldToWorkAsID($CITypeID);
    $CISourceImportFieldName = getCISourceImportFieldName($CITypeID, $CIIDFieldName);
    $Created = date("Y-m-d H:i:s");
    $FieldsToImportArray = getCIFieldsToImport($CITypeID);
    foreach ($data as $key => $row) {
        $DestinationFields = [];
        $ValueFields = [];
        $CreateQuery = "";
        $rows = implode(",", $row);
        $IDColumn = $row[0];
        //$IDColumn = $row["$CISourceImportFieldName"];
        $exists = doesCIExist($CITableName, $CIIDFieldName, $IDColumn, $CITypeID);

        //If doesnt exist - create
        if ($exists == "") {
            $CreateQuery .= "INSERT INTO $CITableName(";

            foreach ($FieldsToImportArray as $row_FieldsToImportArray) {
                $FieldName = $row_FieldsToImportArray["FieldName"];

                $DestinationFields[] = $FieldName;
                if ($row_FieldsToImportArray["DefaultField"] == 1) {
                    $Value = getCIDefaultFieldDefaultValue($FieldName, $CITypeID);
                    $ValueFields[] = "'$Value'";
                } else {
                    $ImportSourceField = $row_FieldsToImportArray["ImportSourceField"];
                    $ValueField = $row[$ImportSourceField];                    
                    $ValueField = ltrim($ValueField);
                    $ValueField = "'$ValueField'";
                    $ValueFields[] = "$ValueField";
                }
            }

            $CreateQuery .= implode(",", $DestinationFields);
            $CreateQuery .= ")";

            $CreateQuery = str_replace(",)", "", $CreateQuery);
            $CreateQuery .= " VALUES (";

            $CreateQuery .= implode(",", $ValueFields);
            $CreateQuery .= ");";
            runSpecificQuery($CreateQuery);
        } else {
            $CreateQuery .= "UPDATE $CITableName SET ";
            $lastKey = array_key_last($FieldsToImportArray);
            foreach ($FieldsToImportArray as $key => $row_FieldsToImportArray) {
                if ($key == $lastKey) {
                    $FieldName = $row_FieldsToImportArray["FieldName"];
                    $ImportSourceField = $row_FieldsToImportArray["ImportSourceField"];
                    $ValueField = $row[$ImportSourceField];
                    $ValueField = ltrim($ValueField);
                    $CreateQuery .= "$FieldName=$ValueField";
                } else {
                    $FieldName = $row_FieldsToImportArray["FieldName"];
                    $ImportSourceField = $row_FieldsToImportArray["ImportSourceField"];
                    $ValueField = $row[$ImportSourceField];
                    $ValueField = ltrim($ValueField);
                    $CreateQuery .= "$FieldName=$ValueField,";
                }
            }
            $CreateQuery .= " WHERE $CIIDFieldName = '$exists'";
            runSpecificQuery($CreateQuery);
        }
    }
    return $data;
}

function syncCI($CITypeID)
{
    global $conn;
    global $functions;
    
    $success = "success";

    $fname = getCIFileToImport($CITypeID);

    // Check if $fname is a URL
    if (filter_var($fname, FILTER_VALIDATE_URL)) {
        $ext = "url";
    } else {
        // Get file extension for non-URL values
        $file_extension = strtolower(pathinfo($fname, PATHINFO_EXTENSION));

        if ($file_extension == 'json') {
            $ext = "json";
        } elseif ($file_extension == 'csv') {
            $ext = "csv";
        } else {
            // Handle other cases or unknown extensions if necessary
            $functions->errorlog("Unknown file type or extension: $file_extension", "syncCI");
            return;
        }
    }

    $array_encoding = [        
        "utf-8",
        "ASCII",
        "JIS",
        "EUC-JP",
        "Windows-1252",
        "ISO-8859-1",
        "ISO-8859-2",
        "ISO-8859-3",
        "ISO-8859-4",
        "ISO-8859-5",
        "ISO-8859-6",
        "ISO-8859-7",
        "ISO-8859-8",
        "ISO-8859-9",
        "ISO-8859-10",
        "ISO-8859-13",
        "ISO-8859-14",
        "ISO-8859-15",
        "ISO-8859-16"
    ];

    switch ($ext) {
        
        case "csv":
            try {
                // Check both possible paths
                $possiblePaths = ["../uploads/import/$fname", "./uploads/import/$fname"];

                $foundPath = null;
                foreach ($possiblePaths as $path) {
                    if (file_exists($path) && filesize($path) > 0) {
                        $foundPath = $path;
                        break;
                    }
                }

                // If no valid file is found in either path
                if (!$foundPath) {
                    $errorMessage = "Files not found or have empty content in paths: " . implode(", ", $possiblePaths);
                    $success = $errorMessage;
                    $functions->errorlog($errorMessage, "syncCI");
                    return $success;
                }


                $fname = $foundPath;

            } catch (Exception $e) {
                $success = $e->getMessage();
                $functions->errorlog($e->getMessage(), "syncCI");
                return $success;
            }

            try {
                $encoding = mb_detect_encoding(file_get_contents($fname), $array_encoding);

                if (!$encoding) {
                    $success = "Could not detect encoding on $fname.";
                    $functions->errorlog("Could not detect encoding on $fname.", "syncCI");
                    return $success;
                }

                if (!($fp = fopen($fname, 'r'))) {
                    $success = "Failed to open file $fname.";
                    $functions->errorlog("Failed to open file $fname.", "syncCI");
                    return $success;
                }

                $key = fgetcsv($fp, "2048", ",");
                
                // parse csv rows into array
                $data = array();

                while ($row = fgetcsv($fp, "2048", ",")) {
                    $row = array_map('trim', $row);
                    $row = implode(",", $row);
                    if ($encoding !== "UTF-8") {
                        $row = mb_convert_encoding($row, "UTF-8", $encoding);
                    }
                    $row = array_map('trim', explode(",", $row));
                    $data[] = array_combine($key, $row);
                }

                if(empty($data)){
                    $success = "Data read from file: $fname is empty";
                    $functions->errorlog("Data read from file: $fname is empty", "syncCI");
                    return $success;
                }

                // release file handle
                fclose($fp);

                //unlink($fname);
            } catch (Exception $e) {
                $success = $e->getMessage();
                $functions->errorlog($e->getMessage(), "syncCI");
                return $success;
            }
            break;
        case "json":
            try {
                // Get the file content
                $fileContent = file_get_contents($fname);

                // Detect the file's encoding
                $detectedEncoding = mb_detect_encoding($fileContent, $array_encoding, true);

                // If encoding isn't UTF-8, convert it
                if ($detectedEncoding !== 'UTF-8' && $detectedEncoding !== false) {
                    $fileContent = mb_convert_encoding($fileContent, 'UTF-8', $detectedEncoding);
                }

                // Now, try decoding the JSON
                $data = json_decode($fileContent, true);

                // Check if the JSON decoding was successful
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $success = "Error decoding JSON: " . json_last_error_msg()." detectedEncoding: $detectedEncoding";
                    $functions->errorlog($success, "syncCI");
                    return $success;
                }

                if (empty($data)) {
                    $success = "Data read from file: $fname is empty or not valid JSON detectedEncoding: $detectedEncoding";
                    $functions->errorlog("Data read from file: $fname is empty or not valid JSON detectedEncoding: $detectedEncoding", "syncCI");
                }

            } catch (Exception $e) {
                $success = $e->getMessage();
                $functions->errorlog($e->getMessage(), "syncCI");
                return $success;
            }
            break;
        case "url":
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $fname);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Return the result as a string
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // Enable SSL verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ]);

            $jsondata = curl_exec($ch);

            if ($jsondata === false) {
                $errorMessage = 'cURL Error: ' . curl_error($ch);
                curl_close($ch);
                $functions->errorlog($errorMessage, "syncCI");
                return $errorMessage;
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode !== 200) {
                curl_close($ch);
                $functions->errorlog("HTTP Error: " . $httpCode, "syncCI");
                return "HTTP Error: " . $httpCode;
            }

            curl_close($ch);

            try {
                $encoding = mb_detect_encoding($jsondata, $array_encoding);
                $jsondata = mb_convert_encoding($jsondata, "UTF-8", $encoding);
                $data = json_decode($jsondata, true);
                if (empty($data)) {
                    $success = "Data read from URL: $fname is empty";
                    $functions->errorlog("Data read from URL: $fname is empty", "syncCI");
                }
            } catch (Exception $e) {
                $success = $e->getMessage();
                $functions->errorlog($e->getMessage(), "syncCI");
                return $success;
            }
            break;
        case "": // Handle file extension for files ending in '.'
            $success = "File '$fname' needs correct file extension that is csv or json";
            $functions->errorlog("File '$fname' needs correct file extension that is csv or json", "syncCI");
            return $success;
            break;

        case NULL: // Handle no file extension
            $success = "File '$fname' has no file extension, ensure correct extension that is csv or json";
            $functions->errorlog("File '$fname' has no file extension, ensure correct extension that is csv or json", "syncCI");
            return $success;
            break;
    }

    //lets clean up existing data before new import
    try {
        $CITableName = getCITableName($CITypeID);
    } catch (Exception $e) {
        $success = $e->getMessage();
        $functions->errorlog($e->getMessage(), "getCITableName");
        return $success;
    }

    //Declare important variables
    //First lets get ID column name
    try {
        $CIIDFieldName = getCIFieldToWorkAsID($CITypeID);
    } catch (Exception $e) {
        $success = $e->getMessage();
        $functions->errorlog($e->getMessage(), "getCIFieldToWorkAsID");
        return $success;
    }
    
    try {
        $CISourceImportFieldName = getCISourceImportFieldName($CITypeID, $CIIDFieldName);
    } catch (Exception $e) {
        $success = $e->getMessage();
        $functions->errorlog($e->getMessage(), "getCISourceImportFieldName");
        return $success;
    }

    try {
        $Created = date("Y-m-d H:i:s");
    } catch (Exception $e) {
        $success = $e->getMessage();
        $functions->errorlog($e->getMessage(), "date");
        return $success;
    }

    try {
        $FieldsToImportArray = getCIFieldsToImport($CITypeID);
    } catch (Exception $e) {
        $success = $e->getMessage();
        $functions->errorlog($e->getMessage(), "getCIFieldsToImport");
        return $success;
    }

    try {
        $FieldsToSyncArray = getCIFieldsToSync($CITypeID);
    } catch (Exception $e) {
        $success = $e->getMessage();
        $functions->errorlog($e->getMessage(), "getCIFieldsToSync");
        return $success;
    }

    foreach ($data as $row) {
        $DestinationFields = [];
        $ValueFields = [];
        $InsertQuery = "";
        $UpdateQuery = "";

        $IDColumn = $row["$CISourceImportFieldName"];

        try {
            $exists = doesCIExist($CITableName, $CIIDFieldName, $IDColumn, $CITypeID);
        } catch (Exception $e) {
            $success = $e->getMessage();
            $functions->errorlog($e->getMessage(), "doesCIExist");
            return $success;
        }

        //If doesnt exist - create
        if ($exists == "") {
            $InsertQuery .= "INSERT INTO $CITableName(";

            foreach ($FieldsToImportArray as $row_FieldsToImportArray) {
                $FieldName = $row_FieldsToImportArray["FieldName"];

                $DestinationFields[] = $FieldName;
                if ($row_FieldsToImportArray["DefaultField"] == 1) {
                    try {
                        $Value = getCIDefaultFieldDefaultValue($FieldName, $CITypeID);
                    } catch (Exception $e) {
                        $success = "Did not get value from getCIDefaultFieldDefaultValue";
                        $functions->errorlog($e->getMessage(), "getCIDefaultFieldDefaultValue");
                        return $success;
                    }
                    $ValueFields[] = "'$Value'";
                } else {
                    $ImportSourceField = $row_FieldsToImportArray["ImportSourceField"];
                    $ValueField = $row[$ImportSourceField];                    
                    $ValueField = ltrim($ValueField);
                    $ValueField = "'$ValueField'";                    
                    $ValueFields[] = "$ValueField";                    
                }
            }
            $DestinationFields[] = "StartDate";
            $ValueFields[] = "Now()";
            $DestinationFields[] = "Created";
            $ValueFields[] = "Now()";
            try {
                $Value = getCIDefaultFieldDefaultValue("CreatedBy", $CITypeID);
            } catch (Exception $e) {
                $success = "Did not get value from getCIDefaultFieldDefaultValue";
                $functions->errorlog($e->getMessage(), "getCIDefaultFieldDefaultValue");
                return $success;
            }
            if($Value == ""){
                $Value = "1";
            }
            $DestinationFields[] = "CreatedBy";
            $ValueFields[] = "$Value";
            $InsertQuery .= implode(",", $DestinationFields);
            $InsertQuery .= ")";

            $InsertQuery = str_replace(",)", "", $InsertQuery);
            $InsertQuery .= " VALUES (";

            $InsertQuery .= implode(",", $ValueFields);
            $InsertQuery .= ");";

            try {
                runSpecificQuery($InsertQuery);
            } catch (Exception $e) {
                $success = "runSpecificQuery failed";
                $functions->errorlog($e->getMessage(), "runSpecificQuery");
                return $success;
            }
            $last_id = mysqli_insert_id($conn);
            $LogActionText = "Created by syncronization task";
            createCILogEntry($last_id, $CITypeID, "1", $LogActionText);
        } else {
            try {
                $CIID = getCIIDFromIDColumnName($IDColumn, $CITableName, $CIIDFieldName);
            } catch (Exception $e) {
                $success = "Did not get CIID from getCIIDFromIDColumnName";
                $functions->errorlog("Did not get CIID from getCIIDFromIDColumnName", "runSpecificQuery");
                return $success;
            }

            $UpdateQuery .= "UPDATE $CITableName SET ";
            $lastKey = array_key_last($FieldsToSyncArray);
            //Check if CI status is not active - then we activate it and log it
            $CIStatus = getCIStatus($CITableName, $CITypeID, $CIID);

            if($CIStatus == 0){                
                setCIStatusActive($CITableName, $CITypeID, $CIID);
            }
            //Continue
            foreach ($FieldsToSyncArray as $key => $row_FieldsToSyncArray) {
                if ($key == $lastKey) {
                    $FieldName = $row_FieldsToSyncArray["FieldName"];
                    $ImportSourceField = $row_FieldsToSyncArray["ImportSourceField"];
                    $ValueField = $row[$ImportSourceField];
                    $ValueField = ltrim($ValueField);
                    $ValueField = "$ValueField";
                    $UpdateQuery .= "$FieldName='$ValueField'";
                    $PreValue = getCIFieldPreValue($CIID, $CITableName, $FieldName);
                    $FieldLabel = getCIFieldLabelFromFieldName($CITypeID, $FieldName);
                    if ($ValueField == NULL) {
                        $ValueField = "";
                    }
                    if ($PreValue == NULL
                    ) {
                        $PreValue = "";
                    }

                    if ($PreValue !== $ValueField
                    ) {
                        $LogActionText = "$FieldLabel: changed from $PreValue to $ValueField by syncronization task";
                        createCILogEntry($CIID, $CITypeID, "1", $LogActionText);
                    }
                } else {
                    $FieldName = $row_FieldsToSyncArray["FieldName"];
                    $ImportSourceField = $row_FieldsToSyncArray["ImportSourceField"];
                    $ValueField = $row[$ImportSourceField];
                    $ValueField = ltrim($ValueField);
                    $UpdateQuery .= "$FieldName='$ValueField',";
                    $PreValue = getCIFieldPreValue($CIID, $CITableName, $FieldName);
                    $FieldLabel = getCIFieldLabelFromFieldName($CITypeID, $FieldName);

                    if ($ValueField == NULL) {
                        $ValueField = "";
                    }
                    if (
                        $PreValue == NULL
                    ) {
                        $PreValue = "";
                    }

                    if (
                        $PreValue !== $ValueField
                    ) {
                        $LogActionText = "$FieldLabel: changed from $PreValue to $ValueField by syncronization task";
                        createCILogEntry($CIID, $CITypeID, "1", $LogActionText);
                    }
                }
            }
            $UpdateQuery .= " WHERE $CIIDFieldName = '$exists'";

            try {
                runSpecificQuery($UpdateQuery);
            } catch (Exception $e) {
                $success = $e->getMessage();
                $functions->errorlog($e->getMessage(), "runSpecificQuery");
                return $success;
            }
        }
        $success = "success";
    }
    //lets deactivate CIs that are not in source
    //First get all active CIs from db table with ID and Name
    
    if(!empty($data)){
        //Build simple compare array from data source
        $TempArray = array();
        foreach ($data as $row) {
            $IDColumn = $row["$CISourceImportFieldName"];
            array_push($TempArray, $IDColumn);
        }
        //Lets retrieve all active CI's
        $ActiveCISArray = getAllActiveCIs($CITypeID);
        foreach ($ActiveCISArray as $key) {
            $InactiveCIID = $key["ID"];
            $FieldValue = $key["Value"];
            //If servername is not found in data array (import source) then set active 0
            if (!in_array($FieldValue, $TempArray)) {
                try {
                    deactivateCI($CITableName, $CITypeID, $InactiveCIID);
                } catch (Exception $e) {
                    $functions->errorlog($e->getMessage(), "Deactivating CI failed");
                    $success = $e->getMessage();
                    return $success;
                }
            }
        }

        $success = "success";
    }

    if($success == "success"){
        updateCISyncDateTime($CITypeID);
    }

    return $success;
}

function getAllActiveCIs($CITypeID)
{
    global $functions;

    // Retrieve CIID field name and table name
    $CIIDFieldName = getCIFieldToWorkAsID($CITypeID);
    $CITableName = getCITableName($CITypeID);

    // SQL query to fetch active CIs
    $sql = "SELECT ID, $CIIDFieldName FROM $CITableName WHERE Active IN (1, 2)";

    // Execute the query using selectQuery
    $records = $functions->selectQuery($sql, []);

    // Transform the results into the desired format
    $resultArray = [];
    if (!empty($records)) {
        foreach ($records as $row) {
            $resultArray[] = [
                "ID" => $row['ID'],
                "Value" => $row[$CIIDFieldName]
            ];
        }
    }

    return $resultArray;
}

function getRelationShowField($CITypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldName
            FROM cmdb_ci_fieldslist
            WHERE RelationShowField = '1' AND RelatedCITypeID = '$CITypeID'
            LIMIT 1;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldName = $row["FieldName"];
    }

    return $FieldName;
}

function CreateCIRelations(){
    global $conn;
    global $functions;

    $resultarray = array();
    $FinalArray = array();

    $sql = "SELECT cmdb_cis.TableName AS ParentTableName, FieldName AS ParentFieldName, cmdb_ci_fieldslist.RelationsLookup, cmdb_ci_fieldslist.FieldType
            FROM cmdb_ci_fieldslist
            LEFT JOIN cmdb_cis ON cmdb_ci_fieldslist.RelatedCITypeID = cmdb_cis.ID
            WHERE RelationsLookup != '';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        $FieldType = $row['FieldType'];
        $ParentTableName = $row['ParentTableName'];
        $ParentFieldName = $row['ParentFieldName'];
        $RelationsLookup = $row['RelationsLookup'];
        $temprelations = explode("#", $RelationsLookup);

        foreach($temprelations as $key => $temprow){
            list($ChildTableName, $ChildFieldName) = explode(",", $temprow);
            $resultarray[] = array("FieldType" => $FieldType, "ParentTableName" => $ParentTableName, "ParentFieldName" => $ParentFieldName, "ChildTableName" => $ChildTableName, "ChildFieldName" => $ChildFieldName);
        }
    }

    // Lets first check if any relations exists to child table on parent table and parent value
    foreach ($resultarray as $row) {
        $FieldType = $row["FieldType"];
        $ParentTableName = $row["ParentTableName"];
        $ParentFieldName = $row["ParentFieldName"];
        $ChildTableName = $row["ChildTableName"];
        $ChildFieldName = $row["ChildFieldName"];

        // Query to fetch all active records from the parent table
        $ParentSql = "SELECT ID, $ParentFieldName AS ParentValue
                        FROM $ParentTableName
                        WHERE Active = '1';";
                        

        $ParentResult = mysqli_query($conn, $ParentSql) or die('Query fail: ' . mysqli_error($conn));

        // Define the IP pattern outside the loop to avoid redefining it every time.
        $ip_pattern = '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/';

        // Iterate through all fetched parent records.
        while ($ParentRow = mysqli_fetch_array($ParentResult)) {
            $ParentID = $ParentRow["ID"];
            $ParentValue = strtolower($ParentRow["ParentValue"]);

            if ($FieldType == "10") {
                // Fetch all child records as the comparison will be done in PHP.
                $ChildSql = "SELECT ID, $ChildFieldName AS ChildValue
                     FROM $ChildTableName
                     WHERE Active = '1';";
            } else {
                // For other field types, fetch only records that exactly match the parent value.
                $ChildSql = "SELECT ID, $ChildFieldName AS ChildValue
                     FROM $ChildTableName
                     WHERE Active = '1';";
            }

            $ChildResult = mysqli_query($conn, $ChildSql) or die('Query fail: ' . mysqli_error($conn));

            while ($ChildRow = mysqli_fetch_array($ChildResult)) {
                $ChildID = $ChildRow["ID"];
                $ChildValue = strtolower($ChildRow["ChildValue"]);

                $isMatch = false;

                // Split the ParentValue by ';' to handle multiple values
                $ParentValues = explode(";", $ParentValue);

                foreach ($ParentValues as $ParentValueItem) {
                    $ParentValueItem = strtolower($ParentValueItem);
                    if ($FieldType == "10") {
                        // Extract all IPv4 addresses from the ParentValueItem.
                        preg_match_all($ip_pattern, $ParentValueItem, $parent_ip_matches);
                        $parent_ip_addresses = $parent_ip_matches[0];

                        // Extract all IPv4 addresses from the ChildValue.
                        preg_match_all($ip_pattern, $ChildValue, $child_ip_matches);
                        $child_ip_addresses = $child_ip_matches[0];

                        // Check if any of the extracted IP addresses from the parent field match any from the child field.
                        foreach ($parent_ip_addresses as $parent_ip) {
                            if (in_array($parent_ip, $child_ip_addresses)) { // Exact match comparison.
                                $isMatch = true;
                                break 2; // Breaks both the foreach loops since we found a match.
                            }
                        }
                    } else {
                        // For any other field type, check for an exact match.
                        if ($ParentValueItem == $ChildValue) {
                            
                            $isMatch = true;
                            break; // Breaks the foreach loop since we found a match.
                        }
                    }
                }

                // If a match is found, add the details to the final array.
                if ($isMatch) {
                    $FinalArray[] = array("CITable1" => $ParentTableName, "CITable2" => $ChildTableName, "CI1ID" => $ParentID, "CI2ID" => $ChildID);
                }
            }
        }
    }

    foreach ($FinalArray as $FinalRow) {
        $CITable1 = $FinalRow["CITable1"];
        $CITable2 = $FinalRow["CITable2"];
        $CI1ID = $FinalRow["CI1ID"];
        $CI2ID = $FinalRow["CI2ID"];
        $CITypeID1 = getCITypeIDFromTableName($CITable1);
        $CITypeID2 = getCITypeIDFromTableName($CITable2);
        $CIName1 = getCINameFromTypeID($CITypeID1);
        $CIName2 = getCINameFromTypeID($CITypeID2);
        $LookupField1 = getCILookupField($CITypeID1);
        $LookupField2 = getCILookupField($CITypeID2);
        $CIValue1 = getCIValueFromRelation($CI1ID, $CITable1, $LookupField1);
        $CIValue2 = getCIValueFromRelation($CI2ID, $CITable2, $LookupField2);

        if (!checkifCIRelationExists($CITable1, $CITable2, $CI1ID, $CI2ID)) {
            createOrUpdateCIRelation($CITable1, $CITable2, $CI1ID, $CI2ID, $CIName1, $CIName2, $CIValue1, $CIValue2, $CITypeID1, $CITypeID2);
        }
    }

    // Now let's validate existing relations and do cleanup
    $relationsSql = "SELECT * FROM cmdb_ci_relations
                    WHERE auto = 1;";
    $relationsResult = mysqli_query($conn, $relationsSql) or die('Query fail: ' . mysqli_error($conn));

    $ip_pattern = '/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/'; // Define IP pattern outside the loop

    while ($relation = mysqli_fetch_array($relationsResult)) {
        $isValid = false; // Assume the relation is not valid initially
        $RelationID = $relation["ID"];
        $CITable1 = $relation["CITable1"];
        $CITable2 = $relation["CITable2"];
        $CI1ID = $relation["CI1ID"];
        $CI2ID = $relation["CI2ID"];
        $CITypeID1 = getCITypeIDFromTableName($CITable1);
        $CITypeID2 = getCITypeIDFromTableName($CITable2);
        $CIName1 = getCINameFromTypeID($CITypeID1);
        $CIName2 = getCINameFromTypeID($CITypeID2);
        $LookupField1 = getCILookupField($CITypeID1);
        $LookupField2 = getCILookupField($CITypeID2);
        $CIValue1 = getCIValueFromRelation($CI1ID, $CITable1, $LookupField1);
        $CIValue2 = getCIValueFromRelation($CI2ID, $CITable2, $LookupField2);

        // Prepare and bind
        $stmt = $conn->prepare("SELECT cmdb_cis.TableName AS ParentTableName, FieldName AS ParentFieldName, RelationsLookup, cmdb_ci_fieldslist.FieldType
                    FROM cmdb_ci_fieldslist
                    LEFT JOIN cmdb_cis ON cmdb_ci_fieldslist.RelatedCITypeID = cmdb_cis.ID
                    WHERE cmdb_cis.TableName = ? AND RelationsLookup != ''");
        if ($stmt === false) {
            die('prepare() failed: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("s", $CITable1); // "s" indicates the type of the parameter is a string

        // Execute
        $execResult = $stmt->execute();
        if ($execResult === false) {
            die('execute() failed: ' . htmlspecialchars($stmt->error));
        }

        // Get result
        $fieldResult = $stmt->get_result();

        if ($fieldResult === false) {
            die('get_result() failed: ' . htmlspecialchars($stmt->error));
        }

        while ($fieldData = $fieldResult->fetch_assoc()) { // Loop through all field data

            $FieldType = $fieldData['FieldType'];
            $ParentFieldName = $fieldData['ParentFieldName'];
            $RelationsLookup = $fieldData['RelationsLookup'];

            // Parsing RelationsLookup for the correct child field name
            $relations = explode("#", $RelationsLookup);
            $ChildFieldName = null;
            foreach ($relations as $rel) {
                list($table, $field) = explode(",", $rel);
                if ($table === $CITable2) {
                    $ChildFieldName = $field;
                    break;
                }
            }

            if ($ChildFieldName === null) {
                // handle error here: no matching child field name found in RelationsLookup
                continue; // skip this iteration
            }

            // Based on your new logic, fetch the values to compare
            $ParentValueSql = "SELECT $ParentFieldName FROM $CITable1 WHERE ID = ?";
            $ChildValueSql = "SELECT $ChildFieldName FROM $CITable2 WHERE ID = ?";

            // Prepare and bind parameters
            $ParentValueStmt = mysqli_prepare($conn, $ParentValueSql);
            mysqli_stmt_bind_param($ParentValueStmt, "i",
                $CI1ID
            );

            $ChildValueStmt = mysqli_prepare($conn, $ChildValueSql);
            mysqli_stmt_bind_param($ChildValueStmt, "i", $CI2ID);

            // Execute queries
            mysqli_stmt_execute($ParentValueStmt);
            $ParentValueResult = mysqli_stmt_get_result($ParentValueStmt);

            mysqli_stmt_execute($ChildValueStmt);
            $ChildValueResult = mysqli_stmt_get_result($ChildValueStmt);

            // Fetch and process results
            if ($ParentValueRow = mysqli_fetch_assoc($ParentValueResult)) {
                $ParentValue = $ParentValueRow[$ParentFieldName];
            }

            if ($ChildValueRow = mysqli_fetch_assoc($ChildValueResult)) {
                $ChildValue = strtolower($ChildValueRow[$ChildFieldName]);
            }

            // Free prepared statements
            mysqli_stmt_close($ParentValueStmt);
            mysqli_stmt_close($ChildValueStmt);

            $isValid = false; // Assume the relation is not valid initially

            // Split the ParentValue by ';' to handle multiple values
            $ParentValues = explode(";", $ParentValue);

            foreach ($ParentValues as $ParentValueItem) {
                $ParentValueItem = strtolower($ParentValueItem);
                if ($FieldType == "10") {
                    preg_match_all($ip_pattern, $ParentValueItem, $parent_ip_matches);
                    $parent_ip_addresses = $parent_ip_matches[0];

                    preg_match_all($ip_pattern, $ChildValue, $child_ip_matches);
                    $child_ip_addresses = $child_ip_matches[0];

                    foreach ($parent_ip_addresses as $parent_ip) {
                        if (in_array($parent_ip, $child_ip_addresses)) { // Exact match comparison
                            $isValid = true;
                            break 2; // Breaks both the foreach loops since we found a match
                        }
                    }
                } else {
                    // For any other field type, check for an exact match
                    if ($ParentValueItem == $ChildValue) {
                        $isValid = true;
                        break; // Breaks the foreach loop since we found a match
                    }
                }
            }

            // If a valid match is found, break the loop as no need to check further
            if ($isValid) {
                break;
            }
        }

        // Close statement
        $stmt->close();

        // If no valid relation is found, delete the entry from the database
        if (!$isValid) {
            // Prepare the delete statement
            $deleteSql = "DELETE FROM cmdb_ci_relations WHERE CITable1 = ? AND CITable2 = ? AND CI1ID = ? AND CI2ID = ? AND auto = '1'";
            $deleteStmt = mysqli_prepare($conn, $deleteSql);

            // Bind parameters
            mysqli_stmt_bind_param($deleteStmt, "ssii", $CITable1, $CITable2, $CI1ID, $CI2ID);

            // Execute the delete statement
            if (!mysqli_stmt_execute($deleteStmt)) {
                die('Error deleting record: ' . mysqli_error($conn));
            } else {
                // Log the deletion action for the first CI
                $LogActionText1 = "Relation ID: $RelationID deleted automatically to $CIName2: $CIValue2 (CI ID: $CI2ID)";
                createCILogEntry($CI1ID, $CITypeID1, "1", $LogActionText1);

                // Log the deletion action for the second CI
                $LogActionText2 = "Relation ID: $RelationID deleted automatically to $CIName1: $CIValue1 (CI ID: $CI1ID)";
                createCILogEntry($CI2ID, $CITypeID2, "1", $LogActionText2);
            }

            // Close the prepared statement
            mysqli_stmt_close($deleteStmt);
        }
    }

    return "success";
}

function checkifCIRelationExists($CITable1, $CITable2, $CI1ID, $CI2ID)
{
    global $functions;

    // SQL query to check for the existence of the relationship
    $sql = "SELECT ID
            FROM cmdb_ci_relations
            WHERE (CITable1 = ? AND CITable2 = ? AND CI1ID = ? AND CI2ID = ?)
            OR (CITable1 = ? AND CITable2 = ? AND CI1ID = ? AND CI2ID = ?)";

    // Parameters for the query
    $params = [$CITable1, $CITable2, $CI1ID, $CI2ID, $CITable2, $CITable1, $CI2ID, $CI1ID];

    // Execute the query using selectQuery
    $records = $functions->selectQuery($sql, $params);

    // Return true if a record is found, otherwise false
    return !empty($records);
}

function createOrUpdateCIRelation($CITable1, $CITable2, $CI1ID, $CI2ID, $CIName1, $CIName2, $CIValue1, $CIValue2, $CITypeID1, $CITypeID2)
{
    global $functions;

    // SQL query with placeholders
    $sql = "INSERT INTO cmdb_ci_relations (CITable1, CITable2, CI1ID, CI2ID, auto)
            VALUES (?, ?, ?, ?, 1)";

    // Parameters for the query
    $params = [$CITable1, $CITable2, $CI1ID, $CI2ID];

    // Tables to lock
    $tables = ["cmdb_ci_relations"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        // Log the creation of the relation for both CIs
        $LogActionText1 = "Relation created automatically to $CIName2: $CIValue2 (CI ID: $CI2ID)";
        createCILogEntry($CI1ID, $CITypeID1, "1", $LogActionText1);

        $LogActionText2 = "Relation created automatically to $CIName1: $CIValue1 (CI ID: $CI1ID)";
        createCILogEntry($CI2ID, $CITypeID2, "1", $LogActionText2);

        return true;
    } else {
        // Log the error if the query fails
        $functions->errorlog("Failed to create CI relation between $CI1ID and $CI2ID", "createOrUpdateCIRelation");
        return false;
    }
}

function dailyCISync()
{
    global $conn;
    global $functions;

    $sql = "SELECT ID, SyncTime
            FROM cmdb_cis
            WHERE Synchronization = '1' AND Active = '1';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $CITypeID = $row["ID"];
        $SyncTime = $row["SyncTime"];

        $CurrentTime = date("H:i");
        $FromTime = date("H:i", strtotime("$SyncTime"));
        $ToTime = date('H:i:s', strtotime($SyncTime. ' +2 minutes'));

        if ($CurrentTime > $FromTime && $CurrentTime < $ToTime) {
            try {
                syncCI($CITypeID);
            } catch (Exception $e) {
                $CITypeName = getCITypeName($CITypeID);
                $EmailSubject = "Practicle Syncronization of $CITypeName failed";
                $Content = "Error: " . $e->getMessage();
                $AdminArray = getAllAdministratorsEmail();
                foreach($AdminArray as $Admin){
                    $Email = $Admin["Email"];
                    $Name = $Admin["Name"];
                    sendMailToSinglePerson($Email, $Name, $EmailSubject, $Content);
                }
            }
           
            CreateCIRelations();
        }
    }

    // Update Certificate timestamps
    $SyncTime = "02:00";

    $CurrentTime = date("H:i");
    $FromTime = $SyncTime;
    $ToTime = date('H:i:s', strtotime($SyncTime . ' +2 minutes'));

    if ($CurrentTime > $SyncTime && $CurrentTime < $ToTime) {
        checkAndUpdateCertificateExpireDate();
    }
}

function disableUser($Username)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE users SET Active = 0 WHERE Username = ? AND ID != 1";

    // Parameters for the query
    $params = [$Username];

    // Tables to lock
    $tables = ["users"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to disable user: $Username", "disableUser");
        return false;
    }
}

function enableUser($Username)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE users SET Active = 1 WHERE Username = ? AND ID != 1";

    // Parameters for the query
    $params = [$Username];

    // Tables to lock
    $tables = ["users"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to enable user: $Username", "enableUser");
        return false;
    }
}

function syncADTeams(){
    global $conn;
    global $functions;

    $ldap_password = $functions->getSettingValue(51);
    $ldap_username = $functions->getSettingValue(52);
    $ldap_hostname = $functions->getSettingValue(53);
    $ldap_domain = $functions->getSettingValue(57);
    $ldap_hostname = "ldap://" . $ldap_hostname . "." . $ldap_domain . ":389";
    $ldap_base_dn = $functions->getSettingValue(54);
    $ldap_version = $functions->getSettingValue(55);
    $ldap_syncteams = $functions->getSettingValue(59);
    $LDAPTeamsOU = $functions->getSettingValue(60);

    if ($LDAPTeamsOU == "0") {
        exit;
    }

    $LDAPUsername = $ldap_username . "@" . $ldap_domain;

    $ldap_connection = ldap_connect($ldap_hostname);
    // We have to set this option for the version of Active Directory we are using.
    ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, $ldap_version) or die('Unable to set LDAP protocol version');
    ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.

    if (TRUE === ldap_bind($ldap_connection, "$LDAPUsername", $ldap_password)) {

        $search_filter = "(&(objectClass=Group))";

        $attributes = array();
        $attributes[] = 'name';
        $attributes[] = 'distinguishedname';

        $result = ldap_search($ldap_connection, $LDAPTeamsOU, $search_filter, $attributes);

        if (FALSE !== $result) {
            $entries = ldap_get_entries($ldap_connection, $result);

            for ($teamcounter = 0; $teamcounter < $entries['count']; $teamcounter++) {
                if (
                    !empty($entries[$teamcounter]['name'][0]) &&
                    !empty($entries[$teamcounter]['distinguishedname'][0])
                ) {
                    $TeamName = trim($entries[$teamcounter]['name'][0]);
                    $TeamDN = trim($entries[$teamcounter]['distinguishedname'][0]);

                    $Array[] = array('TeamName' => $TeamName);

                    $TeamID = doesTeamExist($TeamName);
                    if (empty($TeamID)) {

                        // Team does not exist lets import            
                        $NewTeamID = createTeam($TeamName);

                        $Teammembers_search_filter = "(&(objectCategory=user)(memberOf=$TeamDN))";

                        $Members_attributes = array();
                        $Members_attributes[] = 'givenname';
                        $Members_attributes[] = 'mail';
                        $Members_attributes[] = 'samaccountname';
                        $Members_attributes[] = 'sn';
                        $Members_attributes[] = 'displayname';
                        $Members_attributes[] = 'useraccountcontrol';

                        $TeamUsers = ldap_search($ldap_connection, $ldap_base_dn, $Teammembers_search_filter, $Members_attributes);

                        if (FALSE !== $TeamUsers) {
                            $Teamentries = ldap_get_entries($ldap_connection, $TeamUsers);

                            for ($membercounter = 0; $membercounter < $Teamentries['count']; $membercounter++) {
                                if (
                                    !empty($Teamentries[$membercounter]['samaccountname'][0])
                                ) {
                                    $Username = strtolower(trim($Teamentries[$membercounter]['samaccountname'][0]));
                                    $Email = strtolower(trim($Teamentries[$membercounter]['mail'][0]));
                                    $userAccountControl = strtolower(trim($Teamentries[$membercounter]['useraccountcontrol'][0]));
                                    $Fullname = strtolower(trim($Teamentries[$membercounter]['displayname'][0]));
                                    $Firstname = strtolower(trim($Teamentries[$membercounter]['givenname'][0]));
                                    $Lastname = strtolower(trim($Teamentries[$membercounter]['sn'][0]));

                                    $resultfromcheck = doesUsernameExist($Username, $Email);
                                    $Status = "";
                                    if ($resultfromcheck == true) {
                                        // Account exists lets update
                                        if ($userAccountControl == "512") {
                                            // Account is enabled
                                            enableUser($Username);
                                            $Status = 1;
                                        }
                                        if ($userAccountControl == "514") {
                                            // Account is disabled
                                            // Lets disable account
                                            $Status = 0;
                                            disableUser($Username);
                                        }
                                    }
                                    if ($resultfromcheck == false && $Status == 1) {
                                        // Account exists lets import
                                        $password = $functions->generateRandomString(20);
                                        $hashed_password = $functions->SaltAndHashPasswordForCompare($password);
                                        $RelatedCompanyID = $functions->getSettingValue(48);
                                        $JobTitel = "";
                                        $RelatedUserTypeID = "1";
                                        $RelatedManagerID = "";
                                        $StartDate = date('Y-m-d H:i:s');
                                        $NewPin = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);

                                        createNewUser($Firstname, $Lastname, $Email, $Username, $hashed_password, $RelatedCompanyID, $JobTitel, $RelatedUserTypeID, $RelatedManagerID, $StartDate, $NewPin);
                                        $Action = "Imported";
                                    }
                                    $UserIDToBeAdded = getUserIDFromUsername($Username);
                                    insertUserOnTeam($UserIDToBeAdded, $NewTeamID);
                                }
                            }
                        }
                        $TeamUsers = "";
                        $Teamentries = "";
                        $NewTeamID = "";
                        $Action = "Imported";
                    } else {
                        deleteTeamRelations($TeamID);
                        $Teammembers_search_filter = "(&(objectCategory=user)(memberOf=$TeamDN))";
                        $Members_attributes = array();
                        $Members_attributes[] = 'givenname';
                        $Members_attributes[] = 'mail';
                        $Members_attributes[] = 'samaccountname';
                        $Members_attributes[] = 'sn';
                        $Members_attributes[] = 'displayname';
                        $Members_attributes[] = 'useraccountcontrol';

                        $TeamUsers = ldap_search($ldap_connection, "DC=gdev,DC=local", $Teammembers_search_filter, $Members_attributes);
                        if (FALSE !== $TeamUsers) {
                            $Teamentries = ldap_get_entries($ldap_connection, $TeamUsers);

                            for ($membercounter = 0; $membercounter < $Teamentries['count']; $membercounter++) {
                                $checkTemp = $Teamentries[$membercounter]['samaccountname'][0];
                                if (
                                    !empty($Teamentries[$membercounter]['samaccountname'][0])
                                ) {
                                    $Username = strtolower(trim($Teamentries[$membercounter]['samaccountname'][0]));

                                    $Email = strtolower(trim($Teamentries[$membercounter]['mail'][0]));
                                    $userAccountControl = strtolower(trim($Teamentries[$membercounter]['useraccountcontrol'][0]));
                                    $Fullname = strtolower(trim($Teamentries[$membercounter]['displayname'][0]));
                                    $Firstname = strtolower(trim($Teamentries[$membercounter]['givenname'][0]));
                                    $Lastname = strtolower(trim($Teamentries[$membercounter]['sn'][0]));

                                    $resultfromcheck = doesUsernameExist($Username, $Email);
                                    $Status = "";
                                    if ($resultfromcheck == true) {
                                        // Account exists lets update
                                        if ($userAccountControl == "512") {
                                            // Account is enabled
                                            enableUser($Username);
                                            $Status = 1;
                                        }
                                        if ($userAccountControl == "514") {
                                            // Account is disabled
                                            // Lets disable account
                                            $Status = 0;
                                            disableUser($Username);
                                        }
                                    }
                                    if ($resultfromcheck == false && $Status == 1) {
                                        // Account exists lets import
                                        $password = $functions->generateRandomString(20);
                                        $hashed_password = $functions->SaltAndHashPasswordForCompare($password);
                                        $RelatedCompanyID = $functions->getSettingValue(48);
                                        $JobTitel = "";
                                        $RelatedUserTypeID = "1";
                                        $RelatedManagerID = "";
                                        $StartDate = date('Y-m-d H:i:s');
                                        $NewPin = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);

                                        createNewUser($Firstname, $Lastname, $Email, $Username, $hashed_password, $RelatedCompanyID, $JobTitel, $RelatedUserTypeID, $RelatedManagerID, $StartDate, $NewPin);
                                        $Action = "Imported";
                                    }

                                    
                                    $UserIDToBeAdded = getUserIDFromUsername($Username);
                                    insertUserOnTeam($UserIDToBeAdded, $TeamID);
                                }
                            }
                        }
                        $TeamUsers = "";
                        $Teamentries = "";
                        $TeamID = "";
                        $Action = "Update";
                    }
                }
            }
            ldap_unbind($ldap_connection); // Clean up after ourselves.
        }
        $Antal = count($Array);
    }
    return $Antal;
}

function syncADUsers()
{
    global $conn;
    global $functions;
    $PracticleUsersArray = array();
    $PracticleUsersArray = getAllActiveUsersUsernames();
    $ADUsersArray = array();

    $ldap_password = $functions->getSettingValue(51);
    $ldap_username = $functions->getSettingValue(52);
    $ldap_hostname = $functions->getSettingValue(53);
    $ldap_domain = $functions->getSettingValue(57);
    $ldap_hostname = "ldap://" . $ldap_hostname . "." . $ldap_domain . ":389";
    $ldap_base_dn = $functions->getSettingValue(54);
    $ldap_version = $functions->getSettingValue(55);

    $LDAPUsername = $ldap_username . "@" . $ldap_domain;

    $ldap_connection = ldap_connect($ldap_hostname);

    // We have to set this option for the version of Active Directory we are using.
    ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, $ldap_version) or die('Unable to set LDAP protocol version');
    ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.

    if (TRUE === ldap_bind($ldap_connection, "$LDAPUsername", $ldap_password)) {

        $search_filter = "(&(objectCategory=person)(samaccountname=*))";

        $attributes = array();
        $attributes[] = 'givenname';
        $attributes[] = 'mail';
        $attributes[] = 'samaccountname';
        $attributes[] = 'sn';
        $attributes[] = 'displayname';
        $attributes[] = 'useraccountcontrol';

        $result = ldap_search($ldap_connection, $ldap_base_dn, $search_filter, $attributes);

        if (FALSE !== $result) {
            $entries = ldap_get_entries($ldap_connection, $result);

            for ($x = 0; $x < $entries['count']; $x++) {
                if (
                    !empty($entries[$x]['givenname'][0]) &&
                    !empty($entries[$x]['mail'][0]) &&
                    !empty($entries[$x]['samaccountname'][0]) &&
                    !empty($entries[$x]['sn'][0]) &&
                    !empty($entries[$x]['displayname'][0]) &&
                    !empty($entries[$x]['useraccountcontrol'][0])
                ) {
                    $Status = 1;
                    $resultfromcheck = false;

                    $Username = strtolower(trim($entries[$x]['samaccountname'][0]));
                    array_push($ADUsersArray, $Username);
                    $Email = strtolower(trim($entries[$x]['mail'][0]));
                    $Fullname = trim($entries[$x]['displayname'][0]);
                    $Firstname = trim($entries[$x]['givenname'][0]);
                    $Lastname = trim($entries[$x]['sn'][0]);
                    $userAccountControl = trim($entries[$x]['useraccountcontrol'][0]);

                    if ($userAccountControl == "512") {
                        // Account is enabled
                        $Status = 1;
                    }
                    if ($userAccountControl == "514") {
                        // Account is disabled
                        // Lets disable account
                        $Status = 0;
                        disableUser($Username);
                    }

                    $resultfromcheck = doesUsernameExist($Username, $Email);

                    if ($resultfromcheck == true) {
                        // Account exists lets update
                        $Action = "Update";
                    }

                    if ($resultfromcheck == false && $Status == 1) {
                        // Account exists lets import
                        $password = $functions->generateRandomString(20);
                        $hashed_password = $functions->SaltAndHashPasswordForCompare($password);
                        $RelatedCompanyID = $functions->getSettingValue(48);
                        $JobTitel = "";
                        $RelatedUserTypeID = "1";
                        $RelatedManagerID = "";
                        $StartDate = date('Y-m-d H:i:s');
                        $NewPin = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
                        createNewUser($Firstname, $Lastname, $Email, $Username, $hashed_password, $RelatedCompanyID, $JobTitel, $RelatedUserTypeID, $RelatedManagerID, $StartDate, $NewPin);
                        $Action = "Imported";
                    }
                    $Array[] = array('Username' => $Username, 'Email' => $Email, 'Firstname' => $Firstname, 'Lastname' => $Lastname, 'Fullname' => $Fullname, 'Action' => $Action, 'Status' => $Status);
                }
            }
            ldap_unbind($ldap_connection); // Clean up after ourselves.
        }
        $Antal = count($Array);
    }

    if(!empty($ADUsersArray)){
        foreach ($PracticleUsersArray as $TempUsernameToCheck) {
            if (!in_array($TempUsernameToCheck, $ADUsersArray)) {
                disableUser($TempUsernameToCheck);
            }
        }
    }

    return $Antal;
}

function syncAdministrators()
{
    global $conn;
    global $functions;
    $ldap_password = $functions->getSettingValue(51);
    $ldap_username = $functions->getSettingValue(52);
    $ldap_hostname = $functions->getSettingValue(53);
    $ldap_domain = $functions->getSettingValue(57);
    $ldap_hostname = "ldap://" . $ldap_hostname . "." . $ldap_domain . ":389";
    $ldap_base_dn = $functions->getSettingValue(54);
    $ldap_version = $functions->getSettingValue(55);
    $LDAPAdministratorGroup = $functions->getSettingValue(58);

    $LDAPUsername = $ldap_username . "@" . $ldap_domain;

    $ldap_connection = ldap_connect($ldap_hostname);

    // We have to set this option for the version of Active Directory we are using.
    ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, $ldap_version) or die('Unable to set LDAP protocol version');
    ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.

    if (TRUE === ldap_bind($ldap_connection, "$LDAPUsername", $ldap_password)) {

        $search_filter = "(&(memberof=$LDAPAdministratorGroup))";
        //$search_filter = "(&(objectClass=user)(objectCategory=person)(memberof=$LDAPAdministratorGroup))";
        $attributes = array();
        $attributes[] = 'givenname';
        $attributes[] = 'mail';
        $attributes[] = 'samaccountname';
        $attributes[] = 'sn';
        $attributes[] = 'displayname';
        $attributes[] = 'useraccountcontrol';
        $attributes[] = 'modifytimestamp';

        $result = ldap_search($ldap_connection, $ldap_base_dn, $search_filter, $attributes);

        if (FALSE !== $result) {
            $entries = ldap_get_entries($ldap_connection, $result);

            for ($x = 0; $x < $entries['count']; $x++) {
                if (
                    !empty($entries[$x]['samaccountname'][0])
                ) {
                    $Status = 1;
                    $resultfromcheck = false;

                    $Username = strtolower(trim($entries[$x]['samaccountname'][0]));
                    $Email = strtolower(trim($entries[$x]['mail'][0]));
                    $Fullname = trim($entries[$x]['displayname'][0]);
                    $Firstname = trim($entries[$x]['givenname'][0]);
                    $Lastname = trim($entries[$x]['sn'][0]);
                    $userAccountControl = trim($entries[$x]['useraccountcontrol'][0]);

                    if ($userAccountControl == "512") {
                        // Account is enabled
                        $Status = 1;
                    }
                    if ($userAccountControl == "514") {
                        // Account is disabled
                        // Lets disable account
                        $Status = 0;
                    }

                    $resultfromcheck = doesUsernameExist($Username, $Email);

                    if ($resultfromcheck == true) {
                        // Account exists lets update
                        $Action = "Update";
                        removeUsersFromRole("1");
                        addUserToRole($Username, "1");
                    }

                    if ($resultfromcheck == false && $Status == 1) {
                        // Account exists lets import
                        $Action = "Import";
                    }

                    $Array[] = array('Username' => $Username, 'Email' => $Email, 'Firstname' => $Firstname, 'Lastname' => $Lastname, 'Fullname' => $Fullname, 'Action' => $Action, 'Status' => $Status);
                }
            }
            ldap_unbind($ldap_connection); // Clean up after ourselves.
        }
        $Antal = count($Array);
    }

    return $Antal;
}

function updateCISyncDateTime($ID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE cmdb_cis SET LastSyncronized = NOW() WHERE ID = ?";

    // Parameters for the query
    $params = [$ID];

    // Tables to lock
    $tables = ["cmdb_cis"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        // Return the current date and time in the desired format
        return date("d-m-Y H:i");
    } else {
        $functions->errorlog("Failed to update LastSyncronized for CI ID: $ID", "updateCISyncDateTime");
        return false;
    }
}

function resetITSMModules(){

    global $conn;
    global $functions;

    $sql = "
        DROP TABLE IF EXISTS `itsm_incidents`;

        CREATE TABLE `itsm_incidents` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `RelatedCompanyID` mediumint DEFAULT NULL,
        `Customer` mediumint DEFAULT NULL,
        `Subject` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Description` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Status` smallint DEFAULT NULL,
        `Priority` smallint DEFAULT NULL,
        `Team` smallint DEFAULT NULL,
        `Responsible` smallint DEFAULT NULL,
        `BusinessService` smallint DEFAULT NULL,
        `SLA` smallint DEFAULT NULL,
        `Solution` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Created` datetime DEFAULT CURRENT_TIMESTAMP,
        `CreatedBy` mediumint DEFAULT NULL,
        `LastUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`ID`),
        FULLTEXT KEY `Subject` (`Subject`),
        FULLTEXT KEY `Description` (`Description`),
        FULLTEXT KEY `Solution` (`Solution`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci COMMENT='itsmid=1';
        
        DROP TABLE IF EXISTS `itsm_requests`;

        CREATE TABLE `itsm_requests` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `RelatedCompanyID` mediumint DEFAULT NULL,
        `Customer` mediumint DEFAULT NULL,
        `Subject` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Description` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Status` smallint DEFAULT NULL,
        `Priority` smallint DEFAULT NULL,
        `Team` smallint DEFAULT NULL,
        `Responsible` smallint DEFAULT NULL,
        `BusinessService` smallint DEFAULT NULL,
        `SLA` smallint DEFAULT NULL,
        `Solution` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Created` datetime DEFAULT CURRENT_TIMESTAMP,
        `CreatedBy` mediumint DEFAULT NULL,
        `LastUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        `RelatedFormID` int DEFAULT NULL,
        PRIMARY KEY (`ID`),
        FULLTEXT KEY `Subject` (`Subject`),
        FULLTEXT KEY `Description` (`Description`),
        FULLTEXT KEY `Solution` (`Solution`)
        ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci COMMENT='itsmid=2';
        
        DROP TABLE IF EXISTS `itsm_changes`;

        CREATE TABLE `itsm_changes` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `RelatedCompanyID` mediumint DEFAULT NULL,
        `Customer` mediumint DEFAULT NULL,
        `Subject` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Status` smallint DEFAULT NULL,
        `Priority` smallint DEFAULT NULL,
        `Team` smallint DEFAULT NULL,
        `Responsible` smallint DEFAULT NULL,
        `BusinessService` smallint DEFAULT NULL,
        `Authorizer` smallint DEFAULT NULL,
        `SLA` smallint DEFAULT NULL,
        `Solution` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Created` datetime DEFAULT CURRENT_TIMESTAMP,
        `CreatedBy` mediumint DEFAULT NULL,
        `LastUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        `CIField84325898` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `CIField25990292` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        PRIMARY KEY (`ID`),
        FULLTEXT KEY `Subject` (`Subject`),
        FULLTEXT KEY `Description` (`Description`),
        FULLTEXT KEY `Solution` (`Solution`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci COMMENT='itsmid=3';


        DROP TABLE IF EXISTS `itsm_problems`;

        CREATE TABLE `itsm_problems` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `RelatedCompanyID` mediumint DEFAULT NULL,
        `Customer` mediumint DEFAULT NULL,
        `Subject` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Description` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Status` smallint DEFAULT NULL,
        `Priority` smallint DEFAULT NULL,
        `Team` smallint DEFAULT NULL,
        `Responsible` smallint DEFAULT NULL,
        `BusinessService` smallint DEFAULT NULL,
        `SLA` smallint DEFAULT NULL,
        `Solution` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Created` datetime DEFAULT CURRENT_TIMESTAMP,
        `CreatedBy` mediumint DEFAULT NULL,
        `LastUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`ID`),
        FULLTEXT KEY `Subject` (`Subject`),
        FULLTEXT KEY `Description` (`Description`),
        FULLTEXT KEY `Solution` (`Solution`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci COMMENT='itsmid=4';


        DROP TABLE IF EXISTS `itsm_passwords`;

        CREATE TABLE `itsm_passwords` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `RelatedCompanyID` mediumint DEFAULT NULL,
        `Customer` mediumint DEFAULT NULL,
        `Subject` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Description` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Status` smallint DEFAULT NULL,
        `Priority` smallint DEFAULT NULL,
        `Team` smallint DEFAULT NULL,
        `Responsible` smallint DEFAULT NULL,
        `BusinessService` smallint DEFAULT NULL,
        `SLA` smallint DEFAULT NULL,
        `Solution` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Created` datetime DEFAULT CURRENT_TIMESTAMP,
        `CreatedBy` mediumint DEFAULT NULL,
        `LastUpdated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        `ITSMField01254234` text COLLATE utf8mb3_danish_ci,
        `ITSMField51933705` text COLLATE utf8mb3_danish_ci,
        `ITSMField09320574` text COLLATE utf8mb3_danish_ci,
        `ITSMField86001858` text COLLATE utf8mb3_danish_ci,
        `ITSMField27033131` text COLLATE utf8mb3_danish_ci,
        PRIMARY KEY (`ID`),
        FULLTEXT KEY `Subject` (`Subject`),
        FULLTEXT KEY `Description` (`Description`),
        FULLTEXT KEY `Solution` (`Solution`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci COMMENT='itsmid=8';

        DROP TABLE IF EXISTS `itsm_knowledge`;

        CREATE TABLE `itsm_knowledge` (
        `ID` int NOT NULL,
        `RelatedCompanyID` mediumint DEFAULT NULL,
        `Customer` mediumint DEFAULT NULL,
        `Responsible` mediumint NOT NULL,
        `Subject` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `Content` mediumtext COLLATE utf8mb4_danish_ci,
        `Status` smallint NOT NULL DEFAULT '1',
        `Version` decimal(10,0) NOT NULL DEFAULT '1',
        `CreatedBy` mediumint NOT NULL,
        `RelatedGroupID` mediumint DEFAULT NULL,
        `RelatedReviewerID` mediumint DEFAULT NULL,
        `RelatedApproverID` mediumint DEFAULT NULL,
        `RelatedOwnerID` mediumint DEFAULT NULL,
        `LastChanged` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `Created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `ExpirationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `Team` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

        DROP TABLE IF EXISTS `itsm_knowledge_archive`;

        CREATE TABLE `itsm_knowledge_archive` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `RelatedDocumentID` mediumint NOT NULL,
        `RelatedModuleID` int DEFAULT NULL,
        `DocumentVersion` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `RelatedUser` mediumint NOT NULL,
        `Content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `ContentFulltext` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `Date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`ID`),
        KEY `RelatedDocumentID` (`RelatedDocumentID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

        DROP TABLE IF EXISTS `itsm_changes_types`;

        CREATE TABLE `itsm_changes_types` (
        `ID` mediumint NOT NULL AUTO_INCREMENT,
        `TypeName` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `Active` tinyint(1) NOT NULL,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        INSERT INTO `itsm_changes_types` (`ID`, `TypeName`, `Active`) VALUES
        (1, 'Normal Change', 1),
        (2, 'Standard Change', 1),
        (3, 'Release', 1);

        DROP TABLE IF EXISTS `itsm_comments`;

        CREATE TABLE `itsm_comments` (
        `ID` bigint NOT NULL AUTO_INCREMENT,
        `RelatedElementID` bigint NOT NULL,
        `ITSMType` smallint NOT NULL,
        `UserID` int NOT NULL,
        `Text` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `Date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `Internal` tinyint NOT NULL DEFAULT '0',
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        DROP TABLE IF EXISTS `itsm_default_fields`;

        CREATE TABLE `itsm_default_fields` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `FieldName` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `DBFieldType` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `FieldType` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
        `Label` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
        `RelationShowField` tinyint DEFAULT '0',
        `FieldOrder` smallint DEFAULT NULL,
        `FieldDefaultValue` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `SelectFieldOptions` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `LookupTable` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
        `LookupField` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
        `LookupFieldResultTable` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `LookupFieldResultView` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        INSERT INTO `itsm_default_fields` (`ID`, `FieldName`, `DBFieldType`, `FieldType`, `Label`, `RelationShowField`, `FieldOrder`, `FieldDefaultValue`, `SelectFieldOptions`, `LookupTable`, `LookupField`, `LookupFieldResultTable`, `LookupFieldResultView`) VALUES
        (1, 'RelatedCompanyID', 'MEDIUMINT', '4', 'Related Company', 0, 1, NULL, NULL, 'companies', 'ID', 'Companyname', 'Companyname'),
        (2, 'Customer', 'MEDIUMINT', '4', 'Customer', 0, 2, NULL, NULL, 'users', 'ID', 'CONCAT(Customer.Firstname,\" \",Customer.Lastname,\" (\",Customer.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")'),
        (3, 'Subject', 'TEXT', '1', 'Subject', 1, 3, NULL, NULL, NULL, NULL, NULL, NULL),
        (4, 'Description', 'MEDIUMTEXT', '2', 'Description', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL),
        (5, 'Status', 'SMALLINT', '4', 'Status', 0, 5, '1', '', 'changes_statuscodes', 'ID', 'StatusName', 'StatusName'),
        (6, 'Priority', 'SMALLINT', '4', 'Priority', 0, 6, '1', NULL, 'itsm_priorities', 'ID', 'PriorityName', 'PriorityName'),
        (7, 'Team', 'SMALLINT', '4', 'Team', 0, 7, '1', NULL, 'teams', 'ID', 'Teamname', 'Teamname'),
        (8, 'Responsible', 'SMALLINT', '4', 'Responsible', 0, 8, '1', NULL, 'users', 'ID', 'CONCAT(Responsible.Firstname,\" \",Responsible.Lastname,\" (\",Responsible.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")'),
        (9, 'BusinessService', 'SMALLINT', '4', 'Business Service', 0, 9, '1', NULL, 'cmdb_ci_jsf03ynsyjuvoug', 'ID', 'CIField16831324', 'CIField16831324'),
        (10, 'SLA', 'SMALLINT', '4', 'SLA', 0, 10, '1', NULL, 'slaagreements', 'ID', 'Name', 'Name'),
        (11, 'Created', 'DATETIME', '5', 'Created', 0, 11, NULL, NULL, NULL, NULL, NULL, NULL),
        (12, 'CreatedBy', 'MEDIUMINT', '4', 'Created By', 0, 12, NULL, NULL, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")'),
        (13, 'LastUpdated', 'DATETIME', '5', 'Last Updated', 0, 13, NULL, NULL, NULL, NULL, NULL, NULL);

        DROP TABLE IF EXISTS `itsm_fieldslist`;

        CREATE TABLE `itsm_fieldslist` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `RelatedTypeID` int NOT NULL,
        `FieldName` varchar(55) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `FieldLabel` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `FieldType` int NOT NULL,
        `FieldOrder` int NOT NULL,
        `FieldDefaultValue` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `FieldTitle` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `SelectFieldOptions` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `FieldWidth` smallint NOT NULL DEFAULT '4',
        `DefaultField` tinyint NOT NULL DEFAULT '0',
        `LookupTable` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
        `LookupField` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
        `LookupFieldResultTable` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `LookupFieldResultView` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `RelationShowField` tinyint DEFAULT '0',
        `ImportSourceField` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
        `SyncSourceField` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
        `RelationsLookup` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `Indexed` tinyint DEFAULT '0',
        `HideTables` tinyint DEFAULT '0',
        `HideForms` tinyint DEFAULT '0',
        `Required` tinyint DEFAULT '0',
        `Locked` tinyint DEFAULT '0',
        `Addon` mediumint DEFAULT NULL,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        INSERT INTO `itsm_fieldslist` (`ID`, `RelatedTypeID`, `FieldName`, `FieldLabel`, `FieldType`, `FieldOrder`, `FieldDefaultValue`, `FieldTitle`, `SelectFieldOptions`, `FieldWidth`, `DefaultField`, `LookupTable`, `LookupField`, `LookupFieldResultTable`, `LookupFieldResultView`, `RelationShowField`, `ImportSourceField`, `SyncSourceField`, `RelationsLookup`, `Indexed`, `HideTables`, `HideForms`, `Required`, `Locked`, `Addon`) VALUES
        (29, 4, 'RelatedCompanyID', 'Company', 4, 1, '', '', '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (1, 3, 'RelatedCompanyID', 'Related Company', 4, 1, '', '', '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (13, 1, 'RelatedCompanyID', 'Company', 4, 1, '', '', '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, '', '', '', 0, 0, 0, 0, 0, NULL),
        (158, 8, 'ITSMField01254234', 'Username', 1, 1, '', 'Username', '', 4, 0, '', '', '', '', 1, '', '', '', 0, 0, 0, 1, 0, NULL),
        (42, 2, 'RelatedCompanyID', 'Company', 4, 1, '', '', '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (2, 3, 'Customer', 'Customer', 4, 2, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(Customer.Firstname,\" \",Customer.Lastname,\" (\",Customer.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 1, 1, 1, 0, NULL),
        (30, 4, 'Customer', 'Customer', 4, 2, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(Customer.Firstname,\" \",Customer.Lastname,\" (\",Customer.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (14, 1, 'Customer', 'Customer', 4, 2, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(Customer.Firstname,\" \",Customer.Lastname,\" (\",Customer.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (43, 2, 'Customer', 'Customer', 4, 2, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(Customer.Firstname,\" \",Customer.Lastname,\" (\",Customer.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (159, 8, 'ITSMField51933705', 'Password', 9, 2, '', 'Password', '', 4, 0, '', '', '', '', 0, '', '', '', 0, 1, 0, 0, 0, NULL),
        (44, 2, 'Subject', 'Subject', 1, 3, '', '', '', 12, 1, '', '', '', '', 1, '', '', '', 1, 0, 0, 1, 0, NULL),
        (163, 8, 'ITSMField09320574', 'End Date', 5, 3, '', 'End Date', '', 4, 0, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0),
        (15, 1, 'Subject', 'Subject', 1, 3, '', '', '', 8, 1, '', '', '', '', 1, '', '', '', 1, 0, 0, 1, 0, NULL),
        (31, 4, 'Subject', 'Subject', 1, 3, '', '', '', 12, 1, '', '', '', '', 1, '', '', '', 1, 0, 0, 1, 0, NULL),
        (3, 3, 'Subject', 'Subject', 1, 3, '', '', '', 12, 0, '', '', '', '', 1, '', '', '', 1, 0, 0, 1, 0, NULL),
        (45, 2, 'Description', 'Description', 2, 4, NULL, NULL, NULL, 12, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, 1, 1, 0, 0, NULL),
        (32, 4, 'Description', 'Description', 2, 4, '', '', '', 12, 1, '', '', '', '', 0, '', '', '', 1, 0, 0, 0, 0, NULL),
        (164, 8, 'ITSMField86001858', 'Type', 4, 4, '', 'Type', 'TVNTUUwNCk15U1FMDQpQb3N0R3Jlcw0KUkRQDQpTU0gNCldlYg==', 4, 0, '', '', '', '', 0, NULL, '', '', 0, 0, 0, 0, 0, 0),
        (28, 3, 'Description', 'Description', 2, 4, '', 'Description of the change (howto)', '', 12, 0, '', '', '', '', 0, '', '', '', 1, 1, 0, 0, 0, NULL),
        (16, 1, 'Description', 'Description', 2, 4, '', '', '', 12, 1, '', '', '', '', 0, '', '', '', 1, 1, 0, 0, 0, NULL),
        (46, 2, 'Status', 'Status', 4, 5, '2', 'Status', '', 4, 1, 'itsm_statuscodes', 'ID', 'StatusName', 'StatusName', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (160, 8, 'ITSMField27033131', 'Group', 4, 5, '', 'Group', '', 4, 1, 'usergroups', 'ID', 'GroupName', 'GroupName', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL),
        (33, 4, 'Status', 'Status', 4, 5, '1', 'Status', '', 4, 1, 'itsm_statuscodes', 'ID', 'StatusName', 'StatusName', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (26, 3, 'CIField84325898', 'Reason', 2, 5, '', 'Reason for this change', '', 12, 0, '', '', '', '', 0, '', '', '', 0, 1, 0, 0, 0, NULL),
        (17, 1, 'Status', 'Status', 4, 5, '2', 'Status', '', 4, 1, 'itsm_statuscodes', 'ID', 'StatusName', 'StatusName', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (129, 8, 'Subject', 'Subject', 1, 6, '', 'Subject', '', 12, 1, '', '', '', '', 0, '', '', '', 0, 1, 1, 0, 0, NULL),
        (47, 2, 'Priority', 'Priority', 4, 6, '3', 'Priority', '', 4, 1, 'itsm_priorities', 'ID', 'PriorityName', 'PriorityName', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (34, 4, 'Priority', 'Priority', 4, 6, '3', 'Priority', '', 4, 1, 'itsm_priorities', 'ID', 'PriorityName', 'PriorityName', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (18, 1, 'Priority', 'Priority', 4, 6, '3', 'Priority', '', 4, 1, 'itsm_priorities', 'ID', 'PriorityName', 'PriorityName', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (27, 3, 'CIField25990292', 'Backout Plan', 2, 6, '', 'Backout Plan', '', 12, 0, '', '', '', '', 0, '', '', '', 0, 1, 0, 0, 0, NULL),
        (19, 1, 'Team', 'Team', 4, 7, '1', NULL, NULL, 4, 1, 'teams', 'ID', 'Teamname', 'Teamname', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL),
        (35, 4, 'Team', 'Team', 4, 7, '1', NULL, '', 4, 1, 'teams', 'ID', 'Teamname', 'Teamname', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL),
        (127, 8, 'RelatedCompanyID', 'Company', 4, 7, '', '', '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, '', '', '', 0, 1, 1, 0, 0, NULL),
        (48, 2, 'Team', 'Team', 4, 7, '1', NULL, NULL, 4, 1, 'teams', 'ID', 'Teamname', 'Teamname', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL),
        (4, 3, 'Status', 'Status', 4, 7, '1', 'Status', '', 4, 1, 'itsm_statuscodes', 'ID', 'StatusName', 'StatusName', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (36, 4, 'Responsible', 'Responsible', 4, 8, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(Responsible.Firstname,\" \",Responsible.Lastname,\" (\",Responsible.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 0, 0, NULL),
        (128, 8, 'Customer', 'Customer', 4, 8, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(Customer.Firstname,\" \",Customer.Lastname,\" (\",Customer.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 1, 1, 0, 0, NULL),
        (20, 1, 'Responsible', 'Responsible', 4, 8, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(Responsible.Firstname,\" \",Responsible.Lastname,\" (\",Responsible.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 0, 0, NULL),
        (49, 2, 'Responsible', 'Responsible', 4, 8, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(Responsible.Firstname,\" \",Responsible.Lastname,\" (\",Responsible.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 0, 0, NULL),
        (5, 3, 'Priority', 'Priority', 4, 8, '2', 'Priority', '', 4, 1, 'itsm_priorities', 'ID', 'PriorityName', 'PriorityName', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (50, 2, 'BusinessService', 'Business Service', 4, 9, '', '', '', 4, 1, 'cmdb_ci_jsf03ynsyjuvoug', 'ID', 'CIField16831324', 'CIField16831324', 0, '', '', '', 0, 0, 0, 0, 0, NULL),
        (130, 8, 'Description', 'Description', 2, 9, '', '', '', 12, 1, '', '', '', '', 0, '', '', '', 1, 0, 0, 0, 0, NULL),
        (6, 3, 'Team', 'Team', 4, 9, '1', '', '', 4, 1, 'teams', 'ID', 'Teamname', 'Teamname', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (37, 4, 'BusinessService', 'Business Service', 4, 9, '', '', '', 4, 1, 'cmdb_ci_jsf03ynsyjuvoug', 'ID', 'CIField16831324', 'CIField16831324', 0, '', '', '', 0, 0, 0, 0, 0, NULL),
        (21, 1, 'BusinessService', 'Business Service', 4, 9, '', '', '', 4, 1, 'cmdb_ci_jsf03ynsyjuvoug', 'ID', 'CIField16831324', 'CIField16831324', 0, '', '', '', 0, 0, 0, 0, 0, NULL),
        (7, 3, 'Responsible', 'Responsible', 4, 10, '1', '', '', 4, 1, 'users', 'ID', 'CONCAT(Responsible.Firstname,\" \",Responsible.Lastname,\" (\",Responsible.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (131, 8, 'Status', 'Status', 4, 10, '1', 'Status', '', 4, 1, 'itsm_statuscodes', 'ID', 'StatusName', 'StatusName', 0, '', '', '', 0, 0, 0, 0, 0, NULL),
        (51, 2, 'SLA', 'SLA', 4, 10, '', '', '', 4, 1, 'slaagreements', 'ID', 'Name', 'Name', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (38, 4, 'SLA', 'SLA', 4, 10, '', '', '', 4, 1, 'slaagreements', 'ID', 'Name', 'Name', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (22, 1, 'SLA', 'SLA', 4, 10, '', '', '', 4, 1, 'slaagreements', 'ID', 'Name', 'Name', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (8, 3, 'BusinessService', 'Business Service', 4, 11, '', '', '', 4, 1, 'cmdb_ci_jsf03ynsyjuvoug', 'ID', 'CIField16831324', 'CIField16831324', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (132, 8, 'Priority', 'Priority', 4, 11, '3', 'Priority', '', 4, 1, 'itsm_priorities', 'ID', 'PriorityName', 'PriorityName', 0, '', '', '', 0, 1, 1, 0, 0, NULL),
        (23, 1, 'Created', 'Created', 5, 11, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (52, 2, 'Created', 'Created', 5, 11, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (39, 4, 'Created', 'Created', 5, 11, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (24, 1, 'CreatedBy', 'Created By', 4, 12, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (53, 2, 'CreatedBy', 'Created By', 4, 12, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (60, 3, 'Authorizer', 'Authorizer', 4, 12, '1', '', '', 4, 1, 'users', 'ID', 'CONCAT(Responsible.Firstname,\" \",Responsible.Lastname,\" (\",Responsible.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 1, 0, NULL),
        (40, 4, 'CreatedBy', 'Created By', 4, 12, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (133, 8, 'Team', 'Team', 4, 12, '1', '', '', 4, 1, 'teams', 'ID', 'Teamname', 'Teamname', 0, '', '', '', 0, 1, 1, 0, 0, NULL),
        (54, 2, 'LastUpdated', 'Last Updated', 5, 13, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (134, 8, 'Responsible', 'Responsible', 4, 13, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(Responsible.Firstname,\" \",Responsible.Lastname,\" (\",Responsible.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 0, 0, NULL),
        (9, 3, 'SLA', 'SLA', 4, 13, '', '', '', 4, 1, 'slaagreements', 'ID', 'Name', 'Name', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (25, 1, 'LastUpdated', 'Last Updated', 5, 13, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (41, 4, 'LastUpdated', 'Last Updated', 5, 13, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 0, 0, 1, NULL),
        (135, 8, 'BusinessService', 'Business Service', 4, 14, '', '', '', 4, 1, 'cmdb_ci_jsf03ynsyjuvoug', 'ID', 'CIField16831324', 'CIField16831324', 0, '', '', '', 0, 1, 1, 0, 0, NULL),
        (59, 4, 'Solution', 'Solution', 2, 14, NULL, NULL, NULL, 12, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, 1, 1, 0, 0, NULL),
        (56, 1, 'Solution', 'Solution', 2, 14, NULL, NULL, NULL, 12, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, 1, 1, 0, 0, NULL),
        (55, 2, 'RelatedFormID', 'Related Form', 1, 14, NULL, 'Related Form', NULL, 3, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 1, 1, 0, 0, NULL),
        (10, 3, 'Created', 'Created', 5, 14, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 1, 0, 0, 1, NULL),
        (11, 3, 'CreatedBy', 'Created By', 4, 15, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 1, 0, 0, 1, NULL),
        (136, 8, 'SLA', 'SLA', 4, 15, '', '', '', 4, 1, 'slaagreements', 'ID', 'Name', 'Name', 0, '', '', '', 0, 1, 1, 0, 0, NULL),
        (57, 2, 'Solution', 'Solution', 2, 15, NULL, NULL, NULL, 12, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, 1, 1, 0, 0, NULL),
        (137, 8, 'Created', 'Created', 5, 16, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 1, 0, 0, 0, NULL),
        (12, 3, 'LastUpdated', 'Last Updated', 5, 16, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 1, 0, 0, 1, NULL),
        (138, 8, 'CreatedBy', 'Created By', 4, 17, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 1, 0, 0, 0, NULL),
        (58, 3, 'Solution', 'Solution', 2, 17, NULL, NULL, NULL, 12, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, 1, 1, 0, 0, NULL),
        (139, 8, 'LastUpdated', 'Last Updated', 5, 18, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 1, 0, 0, 0, NULL),
        (140, 8, 'Solution', 'Solution', 2, 19, '', '', '', 12, 1, '', '', '', '', 0, '', '', '', 0, 1, 1, 0, 0, NULL),
        (165, 7, 'Subject', 'Subject', 1, 1, '', 'Subject', '', 12, 0, NULL, '', '', '', 1, NULL, '', '', 1, 0, 0, 1, 0, 0),
        (166, 7, 'Content', 'Content', 2, 2, '', 'Content', '', 12, 0, NULL, '', '', '', 0, NULL, '', '', 1, 1, 0, 0, 0, 0),
        (167, 7, 'Responsible', 'Owner', 4, 3, '', 'Owner', '', 4, 0, 'users', 'ID', 'CONCAT(Responsible.Firstname,\" \",Responsible.Lastname,\" (\",Responsible.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, NULL, '', '', 0, 0, 0, 1, 0, 0),
        (168, 7, 'Status', 'Status', 4, 4, '', 'Status', '', 4, 0, 'itsm_statuscodes', 'ID', 'StatusName', 'StatusName', 0, NULL, '', '', 0, 0, 0, 1, 0, 0),
        (169, 7, 'ExpirationDate', 'Expiration Date', 5, 5, '', 'Expiration Date', '', 4, 0, '', '', '', '', 0, NULL, '', '', 0, 0, 0, 1, 0, 0),
        (170, 7, 'RelatedCompanyID', 'Company', 4, 6, '', 'Company', '', 4, 0, 'companies', 'ID', 'companyname', 'companyname', 0, NULL, '', '', 0, 1, 1, 0, 0, 0),
        (171, 7, 'Version', 'Version', 1, 7, '1,2', 'Version', '', 4, 0, '', '', '', '', 0, NULL, '', '', 0, 1, 0, 0, 1, 0),
        (172, 7, 'Customer', 'Customer', 4, 8, '', 'Customer', '', 4, 0, 'users', 'ID', 'CONCAT(Customer.Firstname,\" \",Customer.Lastname,\" (\",Customer.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, NULL, '', '', 0, 1, 1, 0, 0, 0),
        (173, 7, 'RelatedGroupID', 'Group', 11, 9, '', 'Group', '', 4, 0, 'usergroups', 'ID', 'GroupName', 'GroupName', 0, NULL, '', '', 0, 1, 0, 0, 0, 2),
        (174, 7, 'RelatedApproverID', 'Approver', 4, 10, '', 'Approver', '', 4, 0, 'users', 'ID', 'CONCAT(Customer.Firstname,\" \",Customer.Lastname,\" (\",Customer.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, NULL, '', '', 0, 1, 0, 0, 0, 0),
        (175, 7, 'RelatedReviewerID', 'Reviewer', 4, 11, '', 'Reviewer', '', 4, 0, 'users', 'ID', 'CONCAT(Customer.Firstname,\" \",Customer.Lastname,\" (\",Customer.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, NULL, '', '', 0, 1, 0, 0, 0, 0),
        (176, 7, 'CreatedBy', 'Created By', 4, 12, '', 'Created By', '', 4, 0, 'users', 'ID', 'CONCAT(Customer.Firstname,\" \",Customer.Lastname,\" (\",Customer.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, NULL, '', '', 0, 1, 0, 0, 1, 0),
        (177, 7, 'LastChanged', 'Last changed', 5, 13, '', 'Last changed', '', 4, 0, '', '', '', '', 0, NULL, '', '', 0, 1, 0, 0, 1, 0),
        (178, 7, 'Created', 'Created', 5, 14, '', 'Created', '', 4, 0, '', '', '', '', 0, NULL, '', '', 0, 1, 0, 0, 1, 0);

        ALTER TABLE itsm_fieldslist AUTO_INCREMENT = 5000;

        DROP TABLE IF EXISTS `itsm_fieldslist_types`;

        CREATE TABLE `itsm_fieldslist_types` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `TypeName` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
        `DBFieldDef` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
        `Definition` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        INSERT INTO `itsm_fieldslist_types` (`ID`, `TypeName`, `DBFieldDef`, `Definition`) VALUES
        (1, 'Text', 'TINYTEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:> ondblclick=\"copytoclipboard(\'<:fieldid:>\');\">\r\n</div>\r\n</div>'),
        (2, 'Note', 'LONGTEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\"><label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label>&ensp;<a href=\"javascript:toggleTrumbowygEditor(\'<:fieldid:>\');\"><i class=\"fa-solid fa-pen fa-sm\" title=\"Double click on field to edit\"></i></a><div style=\"height: 200px; word-wrap: break-word; overflow-y: auto;\" class=\"resizable_textarea form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"Double click to edit\" rows=\"5\" autocomplete=\"off\" ondblclick=\"createTrumbowygeditor(\'<:fieldid:>\');\" <:required:> <:Locked:>><:fieldvalue:></div>\r\n</div>\r\n</div>'),
        (3, 'Check', 'TINYINT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\"><:label:><:requiredlabel:></label>&nbsp;\r\n<input type=\"checkbox\" name=\"<:fieldname:>\" id=\"<:fieldid:>\" <:required:> <:Locked:>>\r\n</input>\r\n</div>\r\n</div>'),
        (4, 'Select', 'TEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label>\r\n<select class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"<:fieldtitle:>\" <:required:> <:Locked:>>\r\n<:selectoptions:>\r\n</select>\r\n</div>\r\n</div>'),
        (5, 'Date', 'VARCHAR(50)', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" autocomplete=\"off\" onclick=\"runDateTimePicker(\'<:fieldid:>\');\"<:Locked:>>\r\n</div>\r\n</div>'),
        (6, 'Number', 'INT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:>>\r\n</div>\r\n</div>'),
        (7, 'Relation', 'MEDIUMINT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\">\r\n</div>\r\n</div>'),
        (8, 'Booelan', 'TINYINT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:></label>\r\n<select class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"<:fieldtitle:>\">\r\n<:selectoptions:>\r\n</select>\r\n</div>\r\n</div>'),
        (9, 'Password', 'VARCHAR(255)', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:><a href=\"javascript:generateRandomPassword(\'<:fieldid:>\');\"><i class=\"fa-solid fa-shuffle\"></i></a> <a href=\"javascript:void(0)\" onclick=\"togglePasswordVisibility(\'<:fieldid:>\')\"><i class=\"fa-regular fa-eye\"></i></a></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:> ondblclick=\"copytoclipboard(\'<:fieldid:>\');\">\r\n</div>\r\n</div>\r\n<script>\r\nsetTimeout(function() {\r\n    var passwordField = document.getElementById(\'<:fieldid:>\');\r\n    if (passwordField) {        passwordField.setAttribute(\'type\', \'password\');\r\n    }\r\n}, 100);\r\n</script>'),
        (10, 'IP', 'TEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:>>\r\n</div>\r\n</div>'),
        (11, 'Group Filtered', 'TEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label>\r\n<select class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"<:fieldtitle:>\" <:required:> <:Locked:>>\r\n<:selectoptions:>\r\n</select>\r\n</div>\r\n</div>'),
        (12, 'Team Filtered', 'TEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label>\r\n<select class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"<:fieldtitle:>\" <:required:> <:Locked:>>\r\n<:selectoptions:>\r\n</select>\r\n</div>\r\n</div>');

        DROP TABLE IF EXISTS `itsm_types`;

        CREATE TABLE `itsm_types` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `Name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `MenuPage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

        INSERT INTO `itsm_types` (`ID`, `Name`, `Description`, `MenuPage`) VALUES
        (1, 'Service', 'ITSM module with SLA support and advanced service level status codes.', 'itsm_tableview.php?id='),
        (2, 'Project', 'Project Module', 'projects.php'),
        (3, 'Knowledge base', 'Knowledge base', 'itsm_knowledge.php?id='),
        (4, 'Simple', 'Simple modules are mostly for registration uses. It does not involve SLA and service level status codes.', 'itsm_simple.php?id=');

        DROP TABLE IF EXISTS `itsm_log`;

        CREATE TABLE `itsm_log` (
        `ID` bigint NOT NULL AUTO_INCREMENT,
        `LogActionText` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
        `LogActionDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `RelatedElementID` mediumint DEFAULT NULL,
        `RelatedType` mediumint DEFAULT NULL,
        `RelatedUserID` mediumint NOT NULL,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        DROP TABLE IF EXISTS `itsm_modules`;

        CREATE TABLE `itsm_modules` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `ShortElementName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `TableName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
        `CreatedBy` mediumint NOT NULL,
        `Created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `LastEdited` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `LastEditedBy` mediumint NOT NULL,
        `Active` tinyint NOT NULL DEFAULT '1',
        `GroupID` mediumint DEFAULT NULL,
        `ImportSource` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
        `Synchronization` tinyint NOT NULL DEFAULT '0',
        `SyncTime` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
        `LastSyncronized` datetime DEFAULT NULL,
        `TypeIcon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `DoneStatus` tinyint NOT NULL DEFAULT '5',
        `Type` smallint DEFAULT NULL,
        `SLA` tinyint DEFAULT NULL,
        `MenuPage` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=5001 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

        INSERT INTO `itsm_modules` (`ID`, `Name`, `ShortElementName`, `TableName`, `Description`, `CreatedBy`, `Created`, `LastEdited`, `LastEditedBy`, `Active`, `GroupID`, `ImportSource`, `Synchronization`, `SyncTime`, `LastSyncronized`, `TypeIcon`, `DoneStatus`, `Type`, `SLA`, `MenuPage`) VALUES
        (1, 'Incidents', 'Incident', 'itsm_incidents', 'Incidents/Tickets/Inquiries', 1, '2023-03-09 15:05:25', '2023-03-09 15:05:25', 1, 1, 100002, NULL, 0, NULL, NULL, 'fa-solid fa-ticket', 6, 1, 1, 'itsm_tableview.php?id=1'),
        (2, 'Requests', 'Request', 'itsm_requests', 'Requests', 1, '2023-03-09 15:05:25', '2023-03-09 15:05:25', 1, 1, 100010, NULL, 0, NULL, NULL, 'fa-solid fa-store', 6, 1, 1, 'itsm_tableview.php?id=2'),
        (3, 'Changes', 'Change', 'itsm_changes', 'Changes', 1, '2023-03-09 15:05:25', '2023-03-09 15:05:25', 1, 1, 100006, NULL, 0, NULL, NULL, 'fa-brands fa-uncharted', 7, 1, 1, 'itsm_tableview.php?id=3'),
        (4, 'Problems', 'Problem', 'itsm_problems', 'Problems', 1, '2023-03-09 15:05:25', '2023-03-09 15:05:25', 1, 1, 100012, NULL, 0, NULL, NULL, 'fa-solid fa-magnifying-glass', 6, 1, 1, 'itsm_tableview.php?id=4'),
        (5, 'Assets', 'Asset', '', 'Service Assets Management (SACM) is an Information Technology Infrastructure Library (ITIL) version 2 and an IT Service Management (ITSM) process that tracks all of the individual Configuration Items (CI) in an IT system which may be as simple as a single server, or as complex as the entire IT department. In large organizations a configuration manager may be appointed to oversee and manage the CM process.', 1, '2023-11-03 11:44:16', '2023-11-03 11:44:16', 1, 2, 100014, NULL, 0, NULL, '2023-11-03 11:39:38', 'fa fa-laptop', 5, 1, 0, 'cmdb.php'),
        (6, 'Projects', 'Project', 'projects', 'Project management', 1, '2023-11-03 15:22:32', '2023-11-03 15:22:32', 1, 1, 100007, NULL, 0, NULL, '2023-11-03 15:20:51', 'fa-solid fa-list-check', 5, 2, 0, 'projects.php'),
        (7, 'Knowledge base', 'Dokument', 'itsm_knowledge', 'Knowledge base for creating and managing knowledge.', 1, '2023-11-03 15:24:41', '2023-11-03 15:24:41', 1, 1, 100004, NULL, 0, NULL, '2023-11-03 15:23:09', 'fas fa-file-word', 5, 3, 0, 'itsm_knowledge.php?id='),
        (8, 'Passwords', 'Password', 'itsm_passwords', 'Password database', 1, '2023-03-09 15:05:25', '2023-03-09 15:05:25', 1, 1, 100018, NULL, 0, NULL, NULL, 'fa fa-unlock-alt', 2, 4, 0, 'itsm_simple.php?id=8'),
        (13, 'Project Tasks', 'Project Task', 'projects_tasks', 'Project Management', 1, '2023-11-03 15:27:59', '2023-11-03 15:27:59', 1, 2, 100007, NULL, 0, NULL, '2023-11-03 15:26:21', 'fa-solid fa-thumbtack', 5, 1, 0, '');

        ALTER TABLE itsm_modules AUTO_INCREMENT = 5000;

        DROP TABLE IF EXISTS `itsm_participants`;

        CREATE TABLE `itsm_participants` (
        `ID` bigint NOT NULL AUTO_INCREMENT,
        `ModuleID` smallint NOT NULL,
        `ElementID` bigint NOT NULL,
        `UserID` int NOT NULL,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        DROP TABLE IF EXISTS `itsm_priorities`;

        CREATE TABLE `itsm_priorities` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `PriorityName` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `Active` tinyint NOT NULL DEFAULT '1',
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;
        
        INSERT INTO `itsm_priorities` (`ID`, `PriorityName`, `Active`) VALUES
        (1, 'Urgent', 1),
        (2, 'High', 1),
        (3, 'Normal', 1),
        (4, 'Low', 1);

        DROP TABLE IF EXISTS `itsm_relations`;

        CREATE TABLE `itsm_relations` (
        `ID` bigint NOT NULL AUTO_INCREMENT,
        `Table1` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `Table2` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `ID1` mediumint NOT NULL,
        `ID2` mediumint NOT NULL,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        DROP TABLE IF EXISTS `itsm_slatimelines`;

        CREATE TABLE `itsm_slatimelines` (
        `ID` bigint NOT NULL AUTO_INCREMENT,
        `RelatedElementID` mediumint DEFAULT NULL,
        `RelatedElementTypeID` mediumint DEFAULT NULL,
        `TimelineUpdatedDate` datetime DEFAULT NULL,
        `RelatedStatusCodeID` mediumint DEFAULT NULL,
        `SLAViolationDate` datetime DEFAULT NULL,
        `SLAViolated` tinyint NOT NULL DEFAULT '0',
        `SLAViolatedSoon` tinyint NOT NULL DEFAULT '0',
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        DROP TABLE IF EXISTS `itsm_sla_matrix`;

        CREATE TABLE `itsm_sla_matrix` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `RelatedModuleID` mediumint NOT NULL,
        `Status` mediumint NOT NULL,
        `SLA` mediumint NOT NULL,
        `P1` mediumint NOT NULL,
        `P2` mediumint NOT NULL,
        `P3` mediumint NOT NULL,
        `P4` mediumint NOT NULL,
        PRIMARY KEY (`ID`),
        UNIQUE KEY `RelatedModuleID` (`RelatedModuleID`,`Status`,`SLA`)
        ) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        INSERT INTO `itsm_sla_matrix` (`ID`, `RelatedModuleID`, `Status`, `SLA`, `P1`, `P2`, `P3`, `P4`) VALUES
        (1, 1, 2, 1, 15, 20, 30, 60),
        (2, 1, 2, 2, 15, 30, 60, 120),
        (3, 1, 2, 3, 30, 60, 120, 240),
        (4, 1, 3, 1, 60, 90, 120, 240),
        (5, 1, 3, 2, 60, 90, 120, 240),
        (6, 1, 3, 3, 60, 90, 120, 240),
        (7, 1, 4, 1, 60, 90, 120, 240),
        (8, 1, 4, 2, 60, 90, 120, 240),
        (9, 1, 4, 3, 60, 90, 120, 240),
        (10, 1, 5, 1, 240, 360, 360, 480),
        (11, 1, 5, 2, 240, 360, 360, 480),
        (12, 1, 5, 3, 240, 360, 360, 480),
        (13, 1, 6, 1, 480, 720, 960, 1440),
        (14, 1, 6, 2, 480, 720, 1440, 2880),
        (15, 1, 6, 3, 480, 720, 2880, 3360),
        (16, 2, 2, 1, 15, 20, 30, 60),
        (17, 2, 2, 2, 15, 30, 60, 120),
        (18, 2, 2, 3, 30, 60, 120, 240),
        (19, 2, 3, 1, 60, 90, 120, 240),
        (20, 2, 3, 2, 60, 90, 120, 240),
        (21, 2, 3, 3, 60, 90, 120, 240),
        (22, 2, 4, 1, 60, 90, 120, 240),
        (23, 2, 4, 2, 60, 90, 120, 240),
        (24, 2, 4, 3, 60, 90, 120, 240),
        (25, 2, 5, 1, 240, 360, 360, 480),
        (26, 2, 5, 2, 240, 360, 360, 480),
        (27, 2, 5, 3, 240, 360, 360, 480),
        (28, 2, 6, 1, 480, 720, 960, 1440),
        (29, 2, 6, 2, 480, 720, 1440, 2880),
        (30, 2, 6, 3, 480, 720, 960, 3360),
        (31, 3, 2, 1, 15, 20, 30, 60),
        (32, 3, 2, 2, 15, 30, 60, 120),
        (33, 3, 2, 3, 30, 60, 120, 240),
        (34, 3, 3, 1, 60, 90, 120, 240),
        (35, 3, 3, 2, 60, 90, 120, 240),
        (36, 3, 3, 3, 60, 90, 120, 240),
        (37, 3, 4, 1, 60, 90, 120, 240),
        (38, 3, 4, 2, 60, 90, 120, 240),
        (39, 3, 4, 3, 60, 90, 120, 240),
        (40, 3, 5, 1, 240, 360, 360, 480),
        (41, 3, 5, 2, 240, 360, 360, 480),
        (42, 3, 5, 3, 240, 360, 360, 480),
        (43, 3, 6, 1, 480, 720, 480, 960),
        (44, 3, 6, 2, 480, 720, 960, 1440),
        (45, 3, 6, 3, 480, 720, 1440, 3360),
        (46, 3, 7, 1, 480, 720, 960, 1440),
        (47, 3, 7, 2, 480, 720, 1440, 2400),
        (48, 3, 7, 3, 480, 720, 3360, 4800),
        (49, 4, 2, 1, 15, 20, 30, 60),
        (50, 4, 2, 2, 15, 30, 60, 120),
        (51, 4, 2, 3, 30, 60, 120, 240),
        (52, 4, 3, 1, 60, 90, 120, 240),
        (53, 4, 3, 2, 60, 90, 120, 240),
        (54, 4, 3, 3, 60, 90, 120, 240),
        (55, 4, 4, 1, 60, 90, 120, 240),
        (56, 4, 4, 2, 60, 90, 120, 240),
        (57, 4, 4, 3, 60, 90, 120, 240),
        (58, 4, 5, 1, 240, 360, 360, 480),
        (59, 4, 5, 2, 240, 360, 360, 480),
        (60, 4, 5, 3, 240, 360, 360, 480),
        (61, 4, 6, 1, 480, 720, 960, 1440),
        (62, 4, 6, 2, 480, 720, 1440, 2880),
        (63, 4, 6, 3, 480, 720, 960, 3360),
        (128, 8, 1, 1, 15, 20, 30, 60),
        (129, 8, 1, 2, 15, 30, 60, 120),
        (130, 8, 1, 3, 30, 60, 120, 240),
        (131, 8, 2, 1, 15, 20, 30, 60),
        (132, 8, 2, 2, 15, 30, 60, 120),
        (133, 8, 2, 3, 30, 60, 120, 240);

        ALTER TABLE itsm_sla_matrix AUTO_INCREMENT = 5000;

        DROP TABLE IF EXISTS `itsm_statuscodes`;

        CREATE TABLE `itsm_statuscodes` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `ModuleID` int NOT NULL,
        `StatusCode` tinyint NOT NULL,
        `StatusName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `SLA` tinyint NOT NULL DEFAULT '1',
        `ClosedStatus` tinyint NOT NULL DEFAULT '0',
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        INSERT INTO `itsm_statuscodes` (`ID`, `ModuleID`, `StatusCode`, `StatusName`, `SLA`, `ClosedStatus`) VALUES
        (1, 1, 1, 'Created', 0, 0),
        (2, 1, 2, 'Received', 1, 0),
        (3, 1, 3, 'Assigned to team', 1, 0),
        (4, 1, 4, 'Assigned to responsible', 1, 0),
        (5, 1, 5, 'In Resolution Process', 1, 0),
        (6, 1, 6, 'Resolved', 1, 1),
        (7, 1, 7, 'Closed', 0, 1),
        (8, 1, 8, 'Awaiting', 0, 0),
        (9, 2, 1, 'Created', 0, 0),
        (10, 2, 2, 'Received', 1, 0),
        (11, 2, 3, 'Assigned to team', 1, 0),
        (12, 2, 4, 'Assigned to responsible', 1, 0),
        (13, 2, 5, 'In Resolution Process', 1, 0),
        (14, 2, 6, 'Resolved', 1, 1),
        (15, 2, 7, 'Closed', 0, 1),
        (16, 2, 8, 'Awaiting', 0, 0),
        (17, 3, 1, 'Created', 0, 0),
        (18, 3, 2, 'In Assessment', 1, 0),
        (19, 3, 3, 'Authorized', 1, 0),
        (20, 3, 4, 'Scheduled', 1, 0),
        (21, 3, 5, 'Implementing', 1, 0),
        (22, 3, 6, 'Review', 1, 0),
        (24, 3, 8, 'Canceled', 0, 0),
        (25, 4, 1, 'Created', 0, 0),
        (26, 4, 2, 'Received', 1, 0),
        (27, 4, 3, 'Assigned to team', 1, 0),
        (28, 4, 4, 'Assigned to responsible', 1, 0),
        (29, 4, 5, 'In Resolution Process', 1, 0),
        (30, 4, 6, 'Resolved', 1, 1),
        (31, 4, 7, 'Closed', 0, 0),
        (32, 4, 8, 'Awaiting', 0, 0),
        (33, 3, 7, 'Closed', 1, 1),
        (79, 8, 1, 'Created', 0, 0),
        (80, 8, 2, 'Published', 0, 0),
        (5000, 5000, 1, 'Created', 0, 0),
        (5001, 5000, 2, 'Published', 0, 0),
        (5002, 7, 1, 'In construction', 0, 0),
        (5003, 7, 2, 'In review', 0, 0),
        (5004, 7, 3, 'In approval', 0, 0),
        (5005, 7, 4, 'Published', 0, 0),
        (5006, 7, 5, 'Decommissioned', 0, 1),
        (5007, 8, 3, 'Closed', 0, 1);

        ALTER TABLE itsm_statuscodes AUTO_INCREMENT = 5000;

        DROP TABLE IF EXISTS `itsm_templates`;

        CREATE TABLE `itsm_templates` (
        `ID` bigint NOT NULL AUTO_INCREMENT,
        `Description` varchar(350) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `Owner` int NOT NULL,
        `Public` int NOT NULL,
        `FieldsValues` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
        `RelatedModule` int NOT NULL,
        `RelatedFormID` int DEFAULT NULL,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

        INSERT INTO `itsm_templates` (`Description`, `Owner`, `Public`, `FieldsValues`, `RelatedModule`,`RelatedFormID`) VALUES
        ('Server bestilling plp-testserver02', 1, 1, 'CreateFormCustomer<;><#>CreateFormSubject<;>Server bestilling plp-testserver02<#>CreateFormStatus<;>2<#>CreateFormPriority<;>3<#>CreateFormTeam<;>21<#>CreateFormResponsible<;>1<#>CreateFormBusinessService<;><#>FormField59390349<;>plp-testserver02<#>FormField35230620<;>6<#>FormField25704512<;>12<#>FormField02878374<;>Windows Server 2019 Std<#>FormField17847591<;>80GB<#>FormField97089507<;>GDEV<#>FormField01898732<;>', 2, 1);

        DROP TABLE IF EXISTS `modules_addons`;

        CREATE TABLE `modules_addons` (
        `ID` int NOT NULL AUTO_INCREMENT,
        `Type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        `Button` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
        PRIMARY KEY (`ID`)
        ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

        INSERT INTO `modules_addons` (`ID`, `Type`, `Name`, `Button`) VALUES
        (1, 'CMDB', 'Certificate check expire date', '<a href=\"javascript:void(0);\"><i class=\"fa-solid fa-circle-chevron-down float-right\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseCertificate\" aria-expanded=\"false\" aria-controls=\"collapseCertificate\"></i></a>\r\n<div class=\"collapse\" id=\"collapseCertificate\">\r\n	<div class=\"row\">\r\n		<a href=\"javascript:getCertificateExpireDate(\'<:FieldName:>\');\" class=\"btn btn-sm btn-info\">get expire date</a>\r\n	</div>\r\n</div>'),
        (2, 'CMDB', 'View group members', '<a href=\"javascript:void(0);\"><i class=\"fa-solid fa-circle-chevron-down float-right\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseViewGroupMembers\" aria-expanded=\"false\" aria-controls=\"collapseViewGroupMembers\"></i></a>\r\n<div class=\"collapse\" id=\"collapseViewGroupMembers\">\r\n	<div class=\"row\">\r\n		<a href=\"javascript:viewGroupMembers(\'<:FieldName:>\');\" class=\"btn btn-sm btn-info\">Show</a>\r\n	</div>\r\n</div>'),
        (3, 'CMDB', 'View Team members', '<a href=\"javascript:void(0);\"><i class=\"fa-solid fa-circle-chevron-down float-right\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseViewTeam\" aria-expanded=\"false\" aria-controls=\"collapseViewTeam\"></i></a>\r\n<div class=\"collapse\" id=\"collapseViewTeam\">\r\n	<div class=\"row\">\r\n		<a href=\"javascript:viewTeam(\'<:FieldName:>\');\" class=\"btn btn-sm btn-info\">Show</a>\r\n	</div>\r\n</div>');

        DELETE FROM `workflows` WHERE RelatedElementTypeID IN (1,2,3,4,8);

        DELETE FROM `taskslist` WHERE RelatedElementTypeID IN (1,2,3,4,8);

        DELETE FROM `cmdb_ci_itsm_relations`;

        TRUNCATE `itsm_slatimelines`;

        TRUNCATE `activitystream`;
        ";
    //Delete user created itsm module tables
    deleteNotExistingModules("itsm");
    if ($conn->multi_query($sql)) {
        return "Completed";
    } else {
        return "error";
    }
}

function deleteNotExistingModules($module){
    global $conn;
    global $functions;

    $Excludes = array();
    $db = getCurrentDB();

    if($module == "itsm"){
        $Excludes = array("itsm_changes","itsm_changes_types","itsm_comments","itsm_default_fields","itsm_fieldslist","itsm_fieldslist_types","itsm_incidents","itsm_log","itsm_modules","itsm_participants","itsm_priorities","itsm_problems","itsm_relations","itsm_requests","itsm_slatimelines","itsm_sla_matrix","itsm_statuscodes","itsm_templates");
    }elseif ($module == "cmdb"){
        $Excludes = array("cmdb_cis", "cmdb_ci_default_fields", "cmdb_ci_fieldslist", "cmdb_ci_itsm_relations","cmdb_fieldslist_types");
    }

    $TableName = $module."_%";

    $sql = "SELECT TABLE_Name
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_NAME LIKE '$TableName' AND table_schema = '$db';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        $TableName = $row[0];
        if (!in_array($TableName, $Excludes)) {
            dropTable($TableName);
        }
    }
    mysqli_free_result($result);
}

function dropTable($TableName)
{
    global $conn;
    global $functions;

    $sql = "DROP TABLE $TableName;";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function updateDatabase(){
    global $conn;
    global $functions;
    $ThisDBTables = array();
    $NewDBTables = array();
    $ExcludeListThisDBDontTouch = array();

    // FIrst create excludelist for tables that must not be touched
    // CMDB Tables
    $sql = "SELECT TableName
            FROM cmdb_cis;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        array_push($ExcludeListThisDBDontTouch, $row[0]);
    }

    // Forms Tables
    $sql = "SELECT TableName
            FROM forms;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        array_push($ExcludeListThisDBDontTouch, $row[0]);
    }

    // ITSM Tables
    $sql = "SELECT TableName
            FROM itsm_modules;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        array_push($ExcludeListThisDBDontTouch, $row[0]);
    }

    $sql = "SHOW TABLES;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        $ThisDBTables[] = $row[0];
    }
    mysqli_free_result($result);

    if ($conn->multi_query($sql)) {
        return "Completed";
    } else {
        return "error";
    }
}

function createDBVersion(){
    global $conn;
    global $functions;

    $ExcludeListThisDBDontTouch = array();
    $ThisDBTables = array();
    $TableStructures = array();

    // FIrst create excludelist for tables that must not be touched
    // CMDB Tables
    $sql = "SELECT TableName
            FROM cmdb_cis;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        array_push($ExcludeListThisDBDontTouch, $row[0]);
    }

    // Forms Tables
    $sql = "SELECT TableName
            FROM forms;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        array_push($ExcludeListThisDBDontTouch, $row[0]);
    }

    // ITSM Tables
    $sql = "SELECT TableName
            FROM itsm_modules;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        array_push($ExcludeListThisDBDontTouch, $row[0]);
    }

    $sql = "SHOW TABLES;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        $Table = $row[0];
        if (!in_array($Table, $ExcludeListThisDBDontTouch)){
            array_push($ThisDBTables, $Table);
        }
    }
    foreach($ThisDBTables AS $value){
        $Structure = getTableColumnStructure($value);
        $TableStructures[] = array($value,$Structure);
    }

    $JSON = serialize($TableStructures);
    $JSON = mysqli_real_escape_string($conn, $JSON);

    $sql = "INSERT INTO db_versions (Version,phpArray) VALUES ('1','$JSON');";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    mysqli_free_result($result);
}

function getTableColumnStructure($Table){
    global $conn;
    global $functions;
    $TableStructures = array();

    $sql = "DESCRIBE $Table";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_row($result)) {
        $Field = $row["Field"];
        $Type = $row["Type"];
        $Null = $row["Null"];
        $Key = $row["Key"];
        $Default = $row["Default"];
        $Extra = $row["Extra"];

        if($Null == "NO"){
            $Null = "NOT NULL";
        }
        else{
            $Null = "DEFAULT NULL";
        }

        $ALTER_STMNT = "ALTER $Table MODIFY COLUMN $Field $Type $Null";

        $TableStructures = array("Field" => $Field, "Type" => $Type, "Null" => $Null, "Key" => $Key, "Default" => $Default, "Extra" => $Extra);
    }
    
    return $TableStructures;
}

function bind_params_by_reference($stmt, $types, $params)
{
    $refs = [];
    $refs[] = &$types; // Add the type string as the first argument
    foreach ($params as $key => $value) {
        $refs[] = &$params[$key]; // Add each parameter by reference
    }

    // Call bind_param with references
    return call_user_func_array([$stmt, "bind_param"], $refs);
}

function getUserGroupName($GroupID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT GroupName
            FROM usergroups
            WHERE ID = ?
            UNION
            SELECT GroupName
            FROM system_groups
            WHERE ID = ?";

    // Parameters for the query
    $params = [$GroupID, $GroupID];

    // Execute the query using selectQuery
    $records = $functions->selectQuery($sql, $params);

    if (!empty($records)) {
        // Return the first GroupName found
        return $records[0]['GroupName'];
    } else {
        $functions->errorlog("No group name found for GroupID: $GroupID", "getUserGroupName");
        return null; // Return null if no group name is found
    }
}

function getWFTElementID($WFTID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedElementID
            FROM taskslist
            WHERE ID = '$WFTID';";

    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_row($result)) {
        $Value = $row[0];
    }
    mysqli_free_result($result);
    return $Value;
}

function getWFTElementTypeID($WFTID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedElementTypeID
            FROM taskslist
            WHERE ID = '$WFTID';";

    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_row($result)) {
        $Value = $row[0];
    }
    mysqli_free_result($result);
    return $Value;
}

function getFilesPath($ModuleID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FilesPath
            FROM modules
            WHERE ID = '$ModuleID';";

    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_row($result)) {
        $Value = $row[0];
    }
    mysqli_free_result($result);
    return $Value;
}

function getITSMStatusCodeName($ModuleID,$StatusID)
{
    global $conn;
    global $functions;

    $sql = "SELECT StatusName
            FROM itsm_statuscodes
            WHERE ModuleID = '$ModuleID' AND StatusCode = '$StatusID';";

    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_row($result)) {
        $Value = $row[0];
    }
    mysqli_free_result($result);
    return $Value;
}

function createStatusSLAEntries($ModuleID, $StatusID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "INSERT INTO itsm_sla_matrix (RelatedModuleID, Status, SLA, P1, P2, P3, P4) 
            VALUES (?, ?, ?, 0, 0, 0, 0)";

    // Tables to lock
    $tables = ["itsm_sla_matrix"];

    // Insert entries for SLA 1, 2, and 3
    for ($sla = 1; $sla <= 3; $sla++) {
        $params = [$ModuleID, $StatusID, $sla];
        $functions->dmlQuery($sql, $params, $tables);
    }
}

function deleteStatusSLAEntries($ModuleID, $StatusID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "DELETE FROM itsm_sla_matrix WHERE RelatedModuleID = ? AND Status = ?";

    // Parameters for the query
    $params = [$ModuleID, $StatusID];

    // Tables to lock
    $tables = ["itsm_sla_matrix"];

    // Execute the query using dmlQuery
    $functions->dmlQuery($sql, $params, $tables);
}

function getSLAStatusCores($ModuleID, $SLA, $Priority)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT Status, P$Priority
            FROM itsm_sla_matrix
            WHERE RelatedModuleID = ? AND SLA = ?";

    // Parameters for the query
    $params = [$ModuleID, $SLA];

    // Execute the query using selectQuery
    $result = $functions->selectQuery($sql, $params);

    // Transform result into the desired format
    return array_map(function ($row) use ($Priority) {
        return [
            "Status" => $row["Status"],
            "Minutes" => $row["P$Priority"]
        ];
    }, $result);
}

function getPreSLA($StatusID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT SLA FROM itsm_statuscodes WHERE ID = ?";

    // Parameters for the query
    $params = [$StatusID];

    // Execute the query using selectQuery
    $result = $functions->selectQuery($sql, $params);

    // Return the SLA value if found
    if (!empty($result)) {
        return $result[0]['SLA'];
    }

    // Log if SLA not found and return null
    $functions->errorlog("No SLA found for StatusID: $StatusID", "getPreSLA");
    return null;
}

function removeOldTempFiles()
{
    global $functions;

    // SQL query to delete old records
    $sql = "DELETE FROM files_temp WHERE Date < (NOW() - INTERVAL 20 MINUTE)";

    // Tables to lock
    $tables = ["files_temp"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, [], $tables);

    if ($result['LastID'] >= 0) {
        // Proceed to delete files from the directory
        $folderName = "./uploads/files_temp";
        $expire = strtotime("-20 minutes");
        $files = glob($folderName . '/*');

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) <= $expire) {
                if (!unlink($file)) {
                    // Log file deletion failure
                    $functions->errorlog("Failed to delete file: $file", "removeOldTempFiles");
                }
            }
        }
    } else {
        // Log database deletion failure
        $functions->errorlog("Failed to delete old temp file records", "removeOldTempFiles");
    }
}

function createITSMFilesFromTemp($ITSMID, $ITSMTypeID, $UserID)
{
    global $functions;

    // SQL query to fetch files from the temp table
    $sql1 = "SELECT FileName, FileNameOriginal, TempPath FROM files_temp WHERE RelatedUserID = ?";
    $params1 = [$UserID];

    // Execute the query using selectQuery
    $files = $functions->selectQuery($sql1, $params1);

    // Process each file
    foreach ($files as $file) {
        $FileName = $file['FileName'];
        $FileNameOriginal = $file['FileNameOriginal'];
        $SourcePath = "./uploads/files_temp/$FileName";
        $DestinationPath = "./uploads/files_itsm/$FileName";

        // Move the file from temp to itsm directory
        if (!rename($SourcePath, $DestinationPath)) {
            $functions->errorlog("Could not move temp file: $SourcePath to $DestinationPath", "createITSMFilesFromTemp");
            continue; // Skip to the next file if moving fails
        }

        // Insert the file record into the files_itsm table
        $sql2 = "INSERT INTO files_itsm (FileName, FileNameOriginal, RelatedElementID, RelatedUserID, RelatedType)
                 VALUES (?, ?, ?, ?, ?)";
        $params2 = [$FileName, $FileNameOriginal, $ITSMID, $UserID, $ITSMTypeID];
        $tables2 = ["files_itsm"];
        $functions->dmlQuery($sql2, $params2, $tables2);
    }

    // Delete the processed files from the temp table
    $sql3 = "DELETE FROM files_temp WHERE RelatedUserID = ?";
    $params3 = [$UserID];
    $tables3 = ["files_temp"];
    $functions->dmlQuery($sql3, $params3, $tables3);
}

function removeClosedStatus($ITSMTypeID, $StatusID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE itsm_statuscodes 
            SET ClosedStatus = 0 
            WHERE ID != ? AND ModuleID = ?";

    // Parameters for the query
    $params = [$StatusID, $ITSMTypeID];

    // Tables to lock
    $tables = ["itsm_statuscodes"];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Handle errors if needed
    if ($result['LastID'] < 0) {
        $functions->errorlog("Failed to update ClosedStatus for ModuleID: $ITSMTypeID and StatusID: $StatusID", "removeClosedStatus");
    }
}

function filterStopWords($string, $UserSessionID){
    global $conn;
    global $functions;
    $LanguageID = $functions->getUserLanguage($UserSessionID);
    $languageshort = $functions->getLanguageCode($LanguageID);
    $stopWords = array();

    $sql = "SELECT StopWord 
            FROM stopwords;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        $StopWord = $row[0];
        array_push($stopWords, $StopWord);
    }
    $min_word_length = 3;
    $strip_arr = [",", ".", ";", ":", "\"", "'", "“", "”", "(", ")", "!", "?"];
    $str_clean = str_replace($strip_arr, "", $string);
    $str_arr = explode(' ', $str_clean);
    $clean_arr = [];
    foreach ($str_arr as $word) {
        if (strlen($word) > $min_word_length) {
            $word = strtolower($word);
            if (!in_array($word, $stopWords)) {
                $clean_arr[] = $word;
            }
        }
    }
    mysqli_free_result($result);
    return implode(' ', $clean_arr);
}

function countTeamMembers($TeamID){
    global $conn;
    global $functions;

    $sql = "SELECT COUNT(UserID) 
            FROM usersteams
            WHERE TeamID = $TeamID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_row($result)) {
        $Value = $row[0];
    }
    mysqli_free_result($result);
    return $Value;
}

function showFileDeleteLink($ModuleID, $FileID)
{
    global $conn;
    global $functions;
    $Value = "";
    if($ModuleID == "7"){
        $sql = "SELECT RelatedElementID
            FROM files_documents
            WHERE ID = $FileID;";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_array($result)) {
            $Temp = $row["RelatedElementID"];
        }
        $Status = getDocumentStatus($Temp);
        if($Status == "5"){
            $Value = "0";
        }
        else{
            $Value = "1";
        }
    }

    return $Value;
}

function getDocumentStatus($ID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedStatusID
            FROM knowledge_documents
            WHERE ID = $ID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["RelatedStatusID"];
    }
    mysqli_free_result($result);
    return $Value;
}

function getITSMOpenStatus($ITSMTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT StatusCode
            FROM itsm_statuscodes
            WHERE ModuleID = $ITSMTypeID AND ClosedStatus = 0;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $ValueArray = array();

    while ($row = mysqli_fetch_array($result)) {
        $ValueArray[] = $row["StatusCode"];
    }

    return $ValueArray;
}

function getITSMFinishedStatus($ITSMTypeID)
{
    global $conn;
    global $functions;

    $sql = "SELECT DoneStatus
            FROM itsm_modules
            WHERE itsm_modules.ID = $ITSMTypeID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $ValueArray = array();

    while ($row = mysqli_fetch_array($result)) {
        $ValueArray = $row["DoneStatus"];
    }

    mysqli_free_result($result);
    return $ValueArray;
}

function getMissingTimelineUpdates($ITSMTypeID,$ITSMID){
    global $conn;
    global $functions;

    $sql = "SELECT ID
            FROM itsm_slatimelines
            WHERE itsm_slatimelines.RelatedElementTypeID = ? AND RelatedElementID = ? AND TimelineUpdatedDate IS NULL
            ORDER BY RelatedStatusCodeID ASC;";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $ITSMTypeID, $ITSMID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $ValueArray = array();

    while ($row = mysqli_fetch_array($result)) {
        $ValueArray[] = ($row["ID"]);
    }

    return $ValueArray;
}

function updateMissingStatusCodeTimelines($ITSMTypeID, $ITSMID)
{
    global $functions;

    // Retrieve missing timelines to be updated
    $TimelinesToBeUpdated = getMissingTimelineUpdates($ITSMTypeID, $ITSMID);

    // Tables to lock
    $tables = ["itsm_slatimelines"];

    // Update each timeline
    foreach ($TimelinesToBeUpdated as $key) {
        $sql = "UPDATE itsm_slatimelines
                SET TimelineUpdatedDate = NOW()
                WHERE ID = ?";
        $params = [$key];
        $functions->dmlQuery($sql, $params, $tables);
    }

    return true;
}

function getITSMStatusCodesBelowFinishedStatus($ITSMTypeID, $FinishedStatus)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT StatusCode
            FROM itsm_statuscodes
            WHERE ModuleID = ? AND SLA = 1 AND StatusCode < ?
            ORDER BY StatusCode ASC";

    // Parameters for the query
    $params = [$ITSMTypeID, $FinishedStatus];

    // Execute the query using selectQuery
    $result = $functions->selectQuery($sql, $params);

    // Transform result into a simple array
    return array_map(function ($row) {
        return $row["StatusCode"];
    }, $result);
}

function getITSMStatusCodesAllreadyUpdated($ITSMTypeID, $ITSMID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT RelatedStatusCodeID
            FROM itsm_slatimelines
            WHERE TimelineUpdatedDate IS NOT NULL AND RelatedElementID = ? AND RelatedElementTypeID = ?
            ORDER BY RelatedStatusCodeID ASC";

    // Parameters for the query
    $params = [$ITSMID, $ITSMTypeID];

    // Execute the query using selectQuery
    $result = $functions->selectQuery($sql, $params);

    // Transform result into a simple array
    return array_map(function ($row) {
        return $row["RelatedStatusCodeID"];
    }, $result);
}

function getFormsTableName($FormID)
{
    global $conn;
    global $functions;

    $sql = "SELECT TableName
            FROM forms
            WHERE ID = '$FormID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["TableName"];
    }

    mysqli_free_result($result);

    return $Value;
    
}

function getFormsID($ITSMTableName, $ITSMID)
{
    global $conn;
    global $functions;

    $sql = "SELECT RelatedFormID
            FROM $ITSMTableName
            WHERE ID = '$ITSMID';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["RelatedFormID"];
    }

    mysqli_free_result($result);

    return $Value;
}

function getFormsName($FormID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FormsName
            FROM forms
            WHERE ID = $FormID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["FormsName"];
    }

    mysqli_free_result($result);

    return $Value;
}

function getModuleFieldAddonBtn($Addon)
{
    global $functions;

    try {
        // Validate input
        if (empty($Addon)) {
            throw new Exception("Addon ID is empty or invalid.");
        }

        // SQL query with placeholders
        $sql = "SELECT Button FROM modules_addons WHERE ID = ?";

        // Parameters for the query
        $params = [$Addon];

        // Execute the query using selectQuery
        $result = $functions->selectQuery($sql, $params);

        // Ensure a result is found
        if (!empty($result) && isset($result[0]["Button"])) {
            return $result[0]["Button"];
        } else {
            throw new Exception("No Button value found for Addon ID: $Addon.");
        }
    } catch (Exception $e) {
        // Log the error for debugging
        $functions->errorlog($e->getMessage(), "getModuleFieldAddonBtn");
        return null; // Return null on failure
    }
}

function getCertificateExpireDate($URL)
{
    global $functions;

    // Normalize URL to include https://
    $url = "https://$URL";

    // Check if the site is responsive
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode === 0) {
        return "Site did not respond";
    }

    // Extract host from URL
    $host = parse_url($url, PHP_URL_HOST);

    if (!$host) {
        return "Invalid URL";
    }

    // Establish SSL connection to retrieve certificate
    $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
    $socket = @stream_socket_client(
        "ssl://$host:443",
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if ($socket === false) {
        return "Failed to connect: $errstr ($errno)";
    }

    $params = stream_context_get_params($socket);
    fclose($socket);

    if (empty($params['options']['ssl']['peer_certificate'])) {
        return "Certificate not found";
    }

    // Parse the certificate and retrieve expiration date
    $certInfo = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
    if (!$certInfo || !isset($certInfo['validTo_time_t'])) {
        return "Invalid certificate data";
    }

    // Format the expiration date
    $validTo = date('Y-m-d H:i:s', $certInfo['validTo_time_t']);

    // Generate update link
    $updateUrl = "<a href=\"javascript:updateCertificateEndDate('$validTo');\">Click to update</a>";

    return "$validTo - $updateUrl";
}

function getCertificateExpireDateAuto($URL)
{
    // Normalize URL to include https://
    $url = "https://$URL";

    // Check if the site is responsive
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 0) {
        return "Site did not respond";
    }

    // Extract host from URL
    $host = parse_url($url, PHP_URL_HOST);

    if (!$host) {
        return "Invalid URL";
    }

    // Establish SSL connection to retrieve certificate
    $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
    $timeout = 5;

    $socket = @stream_socket_client(
        "ssl://$host:443",
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if ($socket === false) {
        return "Failed to connect: $errstr ($errno)";
    }

    $params = stream_context_get_params($socket);
    fclose($socket);

    if (empty($params['options']['ssl']['peer_certificate'])) {
        return "Certificate not found";
    }

    // Parse the certificate and retrieve expiration date
    $certInfo = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
    if (!$certInfo || !isset($certInfo['validTo_time_t'])) {
        return "Invalid certificate data";
    }

    // Format and return the expiration date
    $validTo = date('Y-m-d H:i:s', $certInfo['validTo_time_t']);
    return $validTo;
}

function updateCertificateEndDate($ciTypeId, $ciId, $endDate)
{
    global $functions;

    try {
        // Get the table name for the CI type
        $CITableName = getCITableName($ciTypeId);

        // SQL query with placeholders
        $sql = "UPDATE $CITableName SET EndDate = ? WHERE ID = ?";

        // Parameters for the query
        $params = [$endDate, $ciId];

        // Tables to lock for manipulation
        $tables = [$CITableName];

        // Execute the query using dmlQuery
        $functions->dmlQuery($sql, $params, $tables);
    } catch (Exception $e) {
        $functions->errorlog("Error updating certificate end date: " . $e->getMessage(), "updateCertificateEndDate");
    }
}

function getModuleTemplateSQL($TemplateID, $Type)
{
    global $functions;

    try {
        // SQL query with placeholders
        $sql = "SELECT $Type FROM module_templates WHERE ID = ?";

        // Parameters for the query
        $params = [$TemplateID];

        // Execute the query using selectQuery
        $result = $functions->selectQuery($sql, $params);

        // Return the retrieved value
        return $result[0][$Type] ?? null;

    } catch (Exception $e) {
        $functions->errorlog("Error retrieving module template SQL: " . $e->getMessage(), "getModuleTemplateSQL");
        return null;
    }
}

function compareDatabases($sourceDb, $destinationDb, $dbusername, $dbpassword)
{
    global $conn;
    global $functions;

    $returnValue = "";
    $systemTables = [
        "changelog",
        "changelog_types",
        "clipboardstemp",
        "cmdb_fieldslist_types",
        "colours",
        "corp_links",
        "countries",
        "db_versions",
        "debuglog",
        "emails",
        "events",
        "favorites",
        "forms_fieldslist_types",
        "itsm_default_fields",
        "itsm_fieldslist_types",
        "itsm_types",
        "languages",
        "mail_templates",
        "module_templates",
        "modules",
        "modules_addons",
        "notification_types",
        "passwordmanager_passwordtypes",
        "projects_statuscodes",
        "projects_tasks_categories",
        "releases",
        "settingstypes",
        "slaagreements",    
        "stopwords",
        "system_groups",
        "system_languages",
        "system_timezones",
        "taskslist_status",
        "users_quickmenu",
        "users_quickmenu_choices",
        "usertypes",
        "week",
        "widgets",
        "workdays"
    ];
    // Establish database connections
    $sourceConn = new PDO("mysql:host=localhost;dbname=$sourceDb", "$dbusername", "$dbpassword");
    $destinationConn = new PDO("mysql:host=localhost;dbname=$destinationDb", "$dbusername", "$dbpassword");

    // Get list of tables from source database
    $sourceTables = $sourceConn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    // Get list of tables from destination database
    $destinationTables = $destinationConn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    // Find tables that exist in the destination database but not in the source database
    $missingTables = array_diff($destinationTables, $sourceTables);

    // Loop through each missing table
    foreach ($missingTables as $table) {
        // Add information about missing table and a query to drop it
        $returnValue .= "<br><br>Table <strong>$table</strong> exists in the destination schema but not in the source schema.<br><br>";
        $returnValue .= '<details><summary>Drop table query:</summary>';
        $returnValue .= '<pre onclick="copyToClipboard(this)">' . "DROP TABLE IF EXISTS $destinationDb.$table;" . '</pre>';
        $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button><br><br>";
        $returnValue .= '</details>';
    }

    // Loop through each table in the source database
    foreach ($sourceTables as $table) {
        // Get table schema from source database
        $sourceSchema = $sourceConn->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_COLUMN, 1);
        if (strpos($sourceSchema, "cmdb_ci_") !== false || strpos($sourceSchema, "formstable_") !== false) {
            if (strpos($sourceSchema, "cmdb_ci_fieldslist") !== false) {
            } else {
                continue;
            }
        }

        $tableExists = false;
        $stmt = $destinationConn->query("SHOW TABLES LIKE '$table'");

        if ($stmt->rowCount() > 0) {
            $tableExists = true;
            $destinationSchema = $destinationConn->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_COLUMN, 1);
            // Get table schema from destination database

            if (!$destinationSchema) {
                $returnValue .= "<strong>$table</strong> not found in destination";
            } else {
                // Get list of fields from source database
                $sourceFields = $sourceConn->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN);

                // Loop through each field
                foreach ($sourceFields as $field) {
                    // Check if field exists in destination database
                    $fieldExists = $destinationConn->query("SHOW COLUMNS FROM $table LIKE '$field'")->rowCount();

                    if (!$fieldExists) {
                        $returnValue .= "Field '$field' does not exist in destination table: <strong>$table</strong>.<br>";
                        
                        // Get field details from source database
                        $sourceFieldDetails = $sourceConn->query("DESCRIBE $table $field")->fetchAll(PDO::FETCH_ASSOC)[0];

                        // Build the ALTER statement
                        $alterStatement = "ALTER TABLE $table ADD COLUMN $field " . $sourceFieldDetails['Type'];

                        // Check if the field allows NULL values
                        if ($sourceFieldDetails['Null'] === 'YES') {
                            $alterStatement .= " NULL";
                        } else {
                            $alterStatement .= " NOT NULL";
                        }
                        
                        // Check if there is a default value in the source field
                        if (!empty($sourceFieldDetails['Default']) || $sourceFieldDetails['Default'] != "") {
                            $alterStatement .= " DEFAULT '" . $sourceFieldDetails['Default'] . "'";
                        }

                        // Add Key and Extra to ALTER statement
                        if (!empty($sourceFieldDetails['Key'])) {
                            $alterStatement .= ", " . strtoupper($sourceFieldDetails['Key']);
                        }
                        if (!empty($sourceFieldDetails['Extra'])) {
                            $alterStatement .= " " . strtoupper($sourceFieldDetails['Extra']);
                        }

                        // Add the rest of the ALTER statement
                        $alterStatement .= ";";

                        // Add collapse section for the field
                        $returnValue .= '<details><summary>ALTER statement for field: ' . $field . '</summary>';
                        $returnValue .= '<pre onclick="copyToClipboard(this)">' . htmlspecialchars($alterStatement) . '</pre>';
                        $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button><br><br>";
                        $returnValue .= '</details>';
                    } else {
                        // Get field details from source database
                        $sourceFieldDetails = $sourceConn->query("DESCRIBE $table $field")->fetchAll(PDO::FETCH_ASSOC)[0];

                        // Get field details from destination database
                        $destinationFieldDetails = $destinationConn->query("DESCRIBE $table $field")->fetchAll(PDO::FETCH_ASSOC)[0];

                        // Compare field types
                        if ($sourceFieldDetails['Type'] != $destinationFieldDetails['Type']) {
                            $returnValue .= "Field '$field' type is different in destination table: <strong>$table</strong>.<br> ";
                            // Build the ALTER statement
                            $alterStatement = "ALTER TABLE $table MODIFY COLUMN $field " . $sourceFieldDetails['Type'] . ";";
                            
                            // Check if the field allows NULL values
                            if ($sourceFieldDetails['Null'] === 'YES') {
                                $alterStatement .= " NULL";
                            } else {
                                $alterStatement .= " NOT NULL";
                            }
                            
                            // Check if there is a default value in the source field
                            if (!empty($sourceFieldDetails['Default']) || $sourceFieldDetails['Default'] != "") {
                                $alterStatement .= " DEFAULT '" . $sourceFieldDetails['Default'] . "'";
                            }

                            // Add Key and Extra to ALTER statement
                            if (!empty($sourceFieldDetails['Key'])) {
                                $alterStatement .= ", " . strtoupper($sourceFieldDetails['Key']);
                            }
                            if (!empty($sourceFieldDetails['Extra'])) {
                                $alterStatement .= " " . strtoupper($sourceFieldDetails['Extra']);
                            }

                            // Add the rest of the ALTER statement
                            $alterStatement .= ";";
                            // Add collapse section for the field
                            $returnValue .= '<details><summary>ALTER statement for field: ' . $field . '</summary>';
                            $returnValue .= '<pre onclick="copyToClipboard(this)">' . htmlspecialchars($alterStatement) . '</pre>';
                            $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button><br><br>";
                            $returnValue .= '</details>';
                        } elseif ($sourceFieldDetails['Default'] != $destinationFieldDetails['Default']) {
                            $returnValue .= "Field '$field' default value is different in destination table: <strong>$table</strong>.<br> ";
                            // Build the ALTER statement to alter default value
                            $alterStatement = "ALTER TABLE $table ALTER COLUMN $field SET DEFAULT '" . $sourceFieldDetails['Default'] . "';";

                            // Add collapse section for the field
                            $returnValue .= '<details><summary>ALTER statement for field: ' . $field . '</summary>';
                            $returnValue .= '<pre onclick="copyToClipboard(this)">' . htmlspecialchars($alterStatement) . '</pre>';
                            $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button><br><br>";
                            $returnValue .= '</details>';
                        }
                    }
                }
            }
        } else {
            $result = $sourceConn->query("SHOW CREATE TABLE $table")->fetchAll();
            $sourceSchema = $result[0][1];  // Adjust as needed based on how the data is structured

            $returnValue .= "<br><br>Table <strong>$table</strong> does not exist in the $destinationDb.<br><br>";
            $returnValue .= '<details><summary>Create table query:</summary>';
            $returnValue .= '<pre onclick="copyToClipboard(this)">' . htmlspecialchars($sourceSchema) . '</pre>';
            $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button><br><br>";
            $returnValue .= '</details>';

            // Add collapse section for copying table from source to destination
            $returnValue .= "<details><summary>Copy $sourceDb.$table data to $destinationDb.$table:</summary>";
            $returnValue .= '<pre onclick="copyToClipboard(this)">' . "INSERT INTO $destinationDb.$table SELECT * FROM $sourceDb.$table;" . '</pre>';
            //$returnValue .= '<pre onclick="copyToClipboard(this)">' . "CREATE TABLE $destinationDb.$table AS SELECT * FROM $sourceDb.$table;" . '</pre>';
            $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button><br><br>";
            $returnValue .= '</details>';

            // Get data from the source table
            $sourceData = $sourceConn->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);

            if (count($sourceData) > 0) {
                // Generate INSERT statements for data migration
                $insertStatements = [];
                foreach ($sourceData as $row) {
                    $columns = implode(',', array_keys($row));
                    $values = implode(',', array_map(function ($value) {
                        return "'" . addslashes($value) . "'";
                    }, array_values($row)));
                    $insertStatements[] = "INSERT INTO $table ($columns) VALUES ($values);";
                }

                // Add collapse section for INSERT statements
                $returnValue .= '<details><summary>INSERT statements for table: ' . $table . '</summary>';
                foreach ($insertStatements as $insertStatement) {
                    $returnValue .= '<pre onclick="copyToClipboard(this)">' . htmlspecialchars($insertStatement) . '</pre>';
                    $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button><br><br>";
                }
                $returnValue .= '</details>';
            } else {
                $returnValue .= "No data found in source table: <strong>$table</strong>.<br>";
            }
        }
    }

    // Check if any differences found
    if (empty($returnValue)) {
        $returnValue = "No differences found.";
    }

    // Add collapse section for general database functions
    $returnValue .= "<br><hr><br>";
    $returnValue .= '<details><summary>General database functions</summary>';
    $returnValue .= '<br><details><summary>Copy forms from source to destination</summary>';
    $returnValue .= '<pre onclick="copyToClipboard(this)">';
    $returnValue .= "SET @dropTables = (
    SELECT GROUP_CONCAT(table_name) 
    FROM information_schema.tables 
    WHERE table_schema = '$destinationDb' 
        AND table_name LIKE '%forms%'
);

SET @dropQuery = IFNULL(CONCAT('DROP TABLE IF EXISTS ', @dropTables), 'SELECT * FROM Modules');

PREPARE stmt FROM @dropQuery;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

";

    // Loop through each source table and get forms tables
    foreach ($sourceTables as $table) {
        // Check if table name contains "forms"
        if (strpos($table, 'forms') !== false) {
            // Copy table from source to destination database
            // Get the CREATE TABLE statement for the table from source database
            $result = $conn->query("SHOW CREATE TABLE $sourceDb.$table;");
            $row = $result->fetch_assoc();  // Assuming you're using MySQLi. Adjust as needed.
            $createTableStatement = $row['Create Table'];

            // Create the table in destination database using the schema from source database
            $returnValue .= str_replace("`$table`", "`$destinationDb`.`$table`", $createTableStatement) . ';<br>';

            // Copy data from source table to destination table
            $returnValue .= "INSERT INTO $destinationDb.$table SELECT * FROM $sourceDb.$table;<br>";
        }
    }

    $returnValue .= '</pre>';
    $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button>";
    $returnValue .= '</details><br>';
    $returnValue .= '<details><summary>Copy CMDB from source to destination</summary>';
    $returnValue .= '<pre onclick="copyToClipboard(this)">';
    $returnValue .= "SET @dropTables = (
    SELECT GROUP_CONCAT(table_name) 
    FROM information_schema.tables 
    WHERE table_schema = '$destinationDb' 
        AND table_name LIKE '%cmdb_%'
);

SET @dropQuery = IFNULL(CONCAT('DROP TABLE IF EXISTS ', @dropTables), 'SELECT * FROM Modules');

PREPARE stmt FROM @dropQuery;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

";

    // Loop through each source table and get forms tables
    foreach ($sourceTables as $table) {
        // Check if table name contains "forms"
        if (strpos($table, 'cmdb_') !== false) {
            // Copy table from source to destination database
            // Get the CREATE TABLE statement for the table from source database
            $result = $conn->query("SHOW CREATE TABLE $sourceDb.$table;");
            $row = $result->fetch_assoc();  // Assuming you're using MySQLi. Adjust as needed.
            $createTableStatement = $row['Create Table'];

            // Create the table in destination database using the schema from source database
            $returnValue .= str_replace("`$table`", "`$destinationDb`.`$table`", $createTableStatement) . ';<br>';

            // Copy data from source table to destination table
            $returnValue .= "INSERT INTO $destinationDb.$table SELECT * FROM $sourceDb.$table;<br>";
        }
    }

    $returnValue .= '</pre>';
    $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button>";
    $returnValue .= '</details><br>';
    $returnValue .= '<details><summary>Copy ITSM from source to destination</summary>';
    $returnValue .= '<pre onclick="copyToClipboard(this)">';
    $returnValue .= "SET @dropTables = (
    SELECT GROUP_CONCAT(table_name) 
    FROM information_schema.tables 
    WHERE table_schema = '$destinationDb' 
        AND table_name LIKE '%itsm_%'
);

SET @dropQuery = IFNULL(CONCAT('DROP TABLE IF EXISTS ', @dropTables), 'SELECT * FROM Modules');

PREPARE stmt FROM @dropQuery;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

";

    // Loop through each source table and get forms tables
    foreach ($sourceTables as $table) {
        // Check if table name contains "forms"
        if (strpos($table, 'itsm_') !== false) {
            // Copy table from source to destination database
            // Get the CREATE TABLE statement for the table from source database
            $result = $conn->query("SHOW CREATE TABLE $sourceDb.$table;");
            $row = $result->fetch_assoc();  // Assuming you're using MySQLi. Adjust as needed.
            $createTableStatement = $row['Create Table'];

            // Create the table in destination database using the schema from source database
            $returnValue .= str_replace("`$table`", "`$destinationDb`.`$table`", $createTableStatement) . ';<br>';
            if($table == "itsm_incidents" || $table == "itsm_requests" || $table == "itsm_problems" || $table == "itsm_passwords"){
                // Copy data from source table to destination table
                //$returnValue .= "DELETE FROM $destinationDb.$table;<br>";
            }
            else{
                // Copy data from source table to destination table
                $returnValue .= "INSERT INTO $destinationDb.$table SELECT * FROM $sourceDb.$table;<br>";
            }
        }
    }

    $returnValue .= '</pre>';
    $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button>";
    $returnValue .= '</details><br>';
    $returnValue .= '<details><summary>Update All System Tables</summary>';
    $returnValue .= '<pre onclick="copyToClipboard(this)">';

    // Loop through each source table and if table exists in $systemTables we will drop it and recreate it from source
    foreach ($sourceTables as $table) {
        // Check if table name is in $systemTables
        if (in_array($table, $systemTables)) {

            // DROP the table in destination database if it exists
            $returnValue .= "SET FOREIGN_KEY_CHECKS=0;<br>";
            
            $returnValue .= "DROP TABLE IF EXISTS $destinationDb.$table;<br>";

            // Get the CREATE TABLE statement for the table from source database
            $result = $conn->query("SHOW CREATE TABLE $sourceDb.$table;");
            $row = $result->fetch_assoc();  // Assuming you're using MySQLi. Adjust as needed.
            $createTableStatement = $row['Create Table'];

            // Create the table in destination database using the schema from source database
            $returnValue .= str_replace("`$table`", "`$destinationDb`.`$table`", $createTableStatement) . ';<br>';

            // Copy data from source table to destination table
            $returnValue .= "INSERT INTO $destinationDb.$table SELECT * FROM $sourceDb.$table;<br>";
            $returnValue .= "SET FOREIGN_KEY_CHECKS=1;<br>";
        }
    }


    $returnValue .= '</pre>';
    $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button>";
    $returnValue .= '</details><br>';
    $returnValue .= '<details><summary>Create missing itsm modules</summary>';
    $returnValue .= '<pre onclick="copyToClipboard(this)">';

    // Copy data from source table to destination table
    $returnValue .= "INSERT IGNORE INTO $destinationDb.itsm_modules
                    SELECT * FROM $sourceDb.itsm_modules
                    WHERE ID < 5000;";

    $returnValue .= '</pre>';
    $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button>";
    $returnValue .= '</details><br>';
    $returnValue .= '<details><summary>Drop all missing tables query:</summary>';
    $returnValue .= '<pre onclick="copyToClipboard(this)">';

    // Loop through each missing table and create DROP TABLE for all tables
    if (!empty($missingTables)) {
        $returnValue .= "SET FOREIGN_KEY_CHECKS=0;<br>";

        foreach ($missingTables as $table) {
            $returnValue .= "DROP TABLE IF EXISTS $destinationDb.$table;" . '<br>';
        }
        $returnValue .= "SET FOREIGN_KEY_CHECKS=1;";
    }
    $returnValue .= '</pre>';
    $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button><br><br>";
    $returnValue .= '</details><br>';

    $returnValue .= '<details><summary>Update missing mail templates</summary>';
    $returnValue .= '<pre onclick="copyToClipboard(this)">';

    $returnValue .= "INSERT INTO $sourceDb.mail_templates (ID, Subject, Content, Updated, UpdatedBy)
                    SELECT $destinationDb.mail_templates.ID, $destinationDb.mail_templates.Subject, $destinationDb.mail_templates.Content, $destinationDb.mail_templates.Updated, $destinationDb.mail_templates.UpdatedBy
                    FROM $destinationDb.mail_templates
                    LEFT JOIN $sourceDb.mail_templates ON $destinationDb.mail_templates.ID = $sourceDb.mail_templates.ID
                    WHERE $sourceDb.mail_templates.ID IS NULL;";

    $returnValue .= '</pre>';
    $returnValue .= "<button onclick=\"executeQuery(this, '$destinationDb')\">Execute</button>";
    $returnValue .= '</details><br>';

    $returnValue .= '</details><br>';

    return $returnValue;
}

function closeAllTasksAssociatedWithITSM($ITSMTypeID, $ITSMID)
{
    global $functions;

    // Check if the task status is already 4 (skipping the update)
    $checkSql = "SELECT COUNT(*) AS count 
                 FROM taskslist 
                 WHERE RelatedElementTypeID = ? AND RelatedElementID = ? AND Status = ?";
    $checkParams = [$ITSMTypeID, $ITSMID, '4'];
    $checkResult = $functions->selectQuery($checkSql, $checkParams);

    if ($checkResult[0]['count'] > 0) {
        // Status is already 4, no need to update
        return;
    }

    // Update the tasks with status 3
    $updateSql = "UPDATE taskslist SET Status = ? 
                  WHERE RelatedElementTypeID = ? AND RelatedElementID = ?";
    $updateParams = ['3', $ITSMTypeID, $ITSMID];
    $tables = ['taskslist'];
    $functions->dmlQuery($updateSql, $updateParams, $tables);
}

function getFormFieldIDFromFieldOrder($TableName, $FormID, $OrderID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT ID
            FROM $TableName
            WHERE RelatedFormID = ? AND FieldOrder = ?";
    $params = [$FormID, $OrderID];

    // Execute the query using selectQuery
    $result = $functions->selectQuery($sql, $params);

    // Return the first matching ID or null if none
    return $result[0]['ID'] ?? null;
}

function getFieldIDFromFieldOrder($TableName, $ITSMTypeID, $OrderID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "SELECT ID
            FROM $TableName
            WHERE RelatedTypeID = ? AND FieldOrder = ?";
    $params = [$ITSMTypeID, $OrderID];

    // Execute the query using selectQuery
    $result = $functions->selectQuery($sql, $params);

    // Return the first matching ID or null if none
    return $result[0]['ID'] ?? null;
}

function getCIFieldIDFromFieldOrder($TableName, $CITypeID, $OrderID)
{
    global $conn;
    global $functions;

    // Check if the task status is already 4 (skipping the update)
    $sql = "SELECT ID
            FROM $TableName
            WHERE RelatedCITypeID = $CITypeID AND FieldOrder = $OrderID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["ID"];
    }

    mysqli_free_result($result);

    return $Value;
}

function getFieldOrderFromFieldID($TableName, $FieldID)
{
    global $conn;
    global $functions;

    $sql = "SELECT FieldOrder
            FROM $TableName
            WHERE ID = $FieldID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["FieldOrder"];
    }

    mysqli_free_result($result);

    return $Value;
}

function updateFieldOrder($TableName, $FieldID, $OrderID)
{
    global $functions;

    // Sanitize inputs to prevent SQL injection
    $TableName = mysqli_real_escape_string($functions->conn, $TableName);
    $FieldID = (int)$FieldID;
    $OrderID = (int)$OrderID;

    // Construct the query
    $sql = "UPDATE $TableName
            SET FieldOrder = ?
            WHERE ID = ?";

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, [$OrderID, $FieldID]);

    // Log an error if the query fails
    if (!$result) {
        $errorMessage = "Failed to update field order in table `$TableName`. SQL: $sql, Parameters: [OrderID: $OrderID, FieldID: $FieldID]";
        $functions->errorlog($errorMessage, "updateFieldOrder");
    }

    return $result;
}

function getParentTaskName($ParentTaskID)
{
    global $functions;

    // SQL query with placeholder
    $sql = "SELECT TaskName FROM project_tasks WHERE ID = ?";

    // Parameters for the query
    $params = [$ParentTaskID];

    // No tables to lock for a read-only operation
    try {
        $result = $functions->selectQuery($sql, $params);

        if ($result && count($result) > 0) {
            return $result[0]['TaskName']; // Return the TaskName from the first result
        }
        return ""; // Return an empty string if no rows found
    } catch (Exception $e) {
        $functions->errorlog("Error fetching parent task name for ParentTaskID: $ParentTaskID. " . $e->getMessage(), __FUNCTION__);
        return ""; // Return an empty string on error
    }
}

function updateParentTaskIDOnTask($TaskID, $ParentTaskID)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE project_tasks SET ParentTask = ? WHERE ID = ?";

    // Parameters for the query
    $params = [$ParentTaskID, $TaskID];

    // Tables to lock
    $tables = ['project_tasks'];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to update ParentTask for TaskID: $TaskID", __FUNCTION__);
        return false;
    }
}

function scanForCVR($tableName, $fieldName, $ResultName, $moduleID)
{
    // Connect to the database
    global $conn;
    global $functions;
    $ModuleName = getModuleNameFromModuleID($moduleID);

    // Query to select the records from the table
    $sql = "SELECT ID, $ResultName, $fieldName FROM $tableName";

    // Execute the query
    $result = mysqli_query($conn, $sql);
    
    // Check if the query executed successfully
    if ($result) {
        // Initialize an empty array to store the CVR numbers
        $cvrNumbers = array();
        $htmlLink = "<table class=\"table table-borderless dt-responsive\" cellspacing=\"0\">
                        <thead>
                            <tr>
                            <th>"._("ID"). "</th>
                            <th>"._("$ModuleName"). "</th>
                            <th>" . _("Result") . "</th>
                            </tr>
                        </thead>
                        <tbody>";

        // Loop through each row in the result set
        while ($row = mysqli_fetch_array($result)
        ) {
            $content = $row[$fieldName];

            // Extract CVR numbers using regular expression
            preg_match_all('/\d{6}-\d{4}/', $content, $matches);
            $cvrNumbers = array_merge($cvrNumbers, $matches[0]);

            // Loop through each extracted CVR number
            foreach ($matches[0] as $cvrNumber) {
                // Generate the link
                $ID = $row['ID'];
                $link = "./knowledge_view.php?docid=$ID";
                $Name = $row["$ResultName"];
                switch ($moduleID) {
                    case 1:
                        $IDLink = "<a href=\"javascript:viewITSM('$ID','$moduleID','1','modal');\">$ID</a>";
                        $NameLink = "<a href=\"javascript:viewITSM('$ID','$moduleID','1','modal');\">$Name</a>";
                        break;
                    case 2:
                        $IDLink = "<a href=\"javascript:viewITSM('$ID','$moduleID','1','modal');\">$ID</a>";
                        $NameLink = "<a href=\"javascript:viewITSM('$ID','$moduleID','1','modal');\">$Name</a>";
                        break;
                    case 3:
                        $IDLink = "<a href=\"javascript:viewITSM('$ID','$moduleID','1','modal');\">$ID</a>";
                        $NameLink = "<a href=\"javascript:viewITSM('$ID','$moduleID','1','modal');\">$Name</a>";
                        break;
                    case 4:
                        $IDLink = "<a href=\"javascript:viewITSM('$ID','$moduleID','1','modal');\">$ID</a>";
                        $NameLink = "<a href=\"javascript:viewITSM('$ID','$moduleID','1','modal');\">$Name</a>";
                        break;
                    case 5:
                        $IDLink = "<a href=\"javascript:runModalViewCI('$ID','1','1');\">$ID</a>";
                        $NameLink = "<a href=\"javascript:runModalViewCI('$ID','1','1');\">$Name</a>";
                        break;
                    case 6:
                        $IDLink = "<a href=\"./projects_view.php?projectid=$ID\">$ID</a>";
                        $NameLink = "<a href=\"./projects_view.php?projectid=$ID\">$Name</a>";
                        break;
                    case 7:
                        $IDLink = "<a href=\"./knowledge_view.php?docid=$ID\">$ID</a>";
                        $NameLink = "<a href=\"./knowledge_view.php?docid=$ID\">$Name</a>";
                        break;
                    case 8:
                        echo "Number is 8";
                        break;
                    case 9:
                        echo "Number is 9";
                        break;
                    case 10:
                        echo "Number is 9";
                        break;
                    case 11:
                        echo "Number is 9";
                        break;
                    case 12:
                        echo "Number is 9";
                        break;
                    case 13:
                        $IDLink = "<a href=\"./projects_tasks_view.php?projecttaskid=$ID\">$ID</a>";
                        $NameLink = "<a href=\"./projects_tasks_view.php?projecttaskid=$ID\">$Name</a>";
                        break;
                    case 14:
                        echo "Number is 9";
                        break;
                    case 15:
                        echo "Number is 9";
                        break;
                    case 16:
                        echo "Number is 9";
                        break;
                    
                    default:
                        $IDLink = "<a href=\"javascript:viewITSM('$ID','$moduleID','1','modal');\">$ID</a>";
                        $NameLink = "<a href=\"javascript:viewITSM('$ID','$moduleID','1','modal');\">$Name</a>";
                        break;
                }
                // Generate the HTML link with the CVR number as the link text
                $htmlLink .= "<tr><td>$IDLink</td><td>$NameLink</td><td>$cvrNumber</td></tr>";
            }
        }
        $htmlLink .= "  </tbody></table>";

        // Free the result set
        mysqli_free_result($result);

        // Close the database connection
        mysqli_close($conn);

        // Return the CVR numbers
        return $htmlLink;
    } else {
        // Query execution failed
        $functions->errorlog("Error executing query: " . mysqli_error($conn), "scanRecordsForCVR");

        // Close the database connection
        mysqli_close($conn);

        // Return an empty array if there was an error
        return array();
    }
}

function createTestCMDB(){
    global $conn;
    global $functions;

    $files = glob('./uploads/files_cis/*'); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file))
            unlink($file); // delete file
    }

    deleteNotExistingModules("cmdb");

    $sql = "

    -- IIS sites
    DROP TABLE IF EXISTS `cmdb_ci_6iyovf6jjka9a8b`;

    CREATE TABLE `cmdb_ci_6iyovf6jjka9a8b` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCompanyID` mediumint DEFAULT NULL,
    `RelatedUserID` mediumint DEFAULT NULL,
    `StartDate` datetime DEFAULT NULL,
    `EndDate` datetime DEFAULT NULL,
    `Active` tinyint DEFAULT NULL,
    `Created` datetime DEFAULT NULL,
    `CreatedBy` mediumint DEFAULT NULL,
    `CIField60246997` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField78141913` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField69973069` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField67447549` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField73322016` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

    INSERT INTO cmdb_ci_6iyovf6jjka9a8b (RelatedCompanyID, RelatedUserID, StartDate, EndDate, Active, Created, CreatedBy, CIField60246997, CIField78141913, CIField69973069, CIField67447549, CIField73322016)
            SELECT 
                t1.RelatedCompanyID,
                t1.RelatedUserID,
                t1.StartDate,
                t1.EndDate,
                t1.Active,
                t1.Created,
                t1.CreatedBy,
                t1.CIField60246997,
                'practicle.dk' AS CIField78141913,
                t1.CIField69973069,
                t1.CIField67447549,
                t1.CIField73322016
            FROM (
                SELECT 
                    FLOOR(RAND() * 2) + 1 AS RelatedCompanyID,
                    FLOOR(RAND() * 3) + 1 AS RelatedUserID,
                    NOW() AS StartDate,
                    DATE_ADD(NOW(), INTERVAL 365 DAY) AS EndDate,
                    1 AS Active,
                    0 AS Removed,
                    NOW() AS Created,
                    1 AS CreatedBy,
                    CONCAT(SUBSTRING(MD5(RAND()), FLOOR(RAND() * 26) + 1, 5), '.practicle.dk') AS CIField60246997,
                    CASE WHEN RAND() > 0.5 THEN 'Started' ELSE 'Stopped' END AS CIField69973069,
                    CONCAT(SUBSTRING(MD5(RAND()), FLOOR(RAND() * 26) + 1, 5), '.practicle.dk') AS CIField67447549,
                    CONCAT(CASE WHEN RAND() > 0.5 THEN 'webserver-' ELSE 'sqlserver-' END, FLOOR(RAND() * 100)) AS CIField73322016
                FROM
                    (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) AS numbers1,
                    (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) AS numbers2
                LIMIT 250
            ) AS t1
            WHERE NOT EXISTS (
                SELECT 1
                FROM cmdb_ci_6iyovf6jjka9a8b AS t2
                WHERE t2.CIField60246997 = t1.CIField60246997
            )
            LIMIT 100;

    -- Applikationer

    DROP TABLE IF EXISTS `cmdb_ci_7a6slcfmxjjldcm`;

    CREATE TABLE `cmdb_ci_7a6slcfmxjjldcm` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCompanyID` mediumint DEFAULT NULL,
    `RelatedUserID` mediumint DEFAULT NULL,
    `StartDate` datetime DEFAULT NULL,
    `EndDate` datetime DEFAULT NULL,
    `Active` tinyint(1) DEFAULT NULL,
    `Removed` tinyint(1) DEFAULT NULL,
    `Created` datetime DEFAULT NULL,
    `CreatedBy` mediumint DEFAULT NULL,
    `CIField04844561` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField08443731` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField34010167` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

    INSERT INTO cmdb_ci_7a6slcfmxjjldcm (RelatedCompanyID, RelatedUserID, StartDate, EndDate, Active, Removed, Created, CreatedBy, CIField04844561, CIField08443731, CIField34010167)
            SELECT 
                FLOOR(RAND() * 2) + 1 AS RelatedCompanyID,
                FLOOR(RAND() * 3) + 1 AS RelatedUserID,
                NOW() AS StartDate,
                DATE_ADD(NOW(), INTERVAL 365 DAY) AS EndDate,
                1 AS Active,
                0 AS Removed,
                NOW() AS Created,
                1 AS CreatedBy,
                CONCAT('applikation', FLOOR(RAND() * 100)) AS CIField04844561,
                CONCAT('1.', FLOOR(RAND() * 10), '.', LPAD(FLOOR(RAND() * 1000), 3, '0')) AS CIField08443731,
                CASE WHEN RAND() > 0.5 THEN '' ELSE 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.' END AS CIField34010167
            FROM
                (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) AS numbers1,
                (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) AS numbers2
            LEFT JOIN cmdb_ci_7a6slcfmxjjldcm ON cmdb_ci_7a6slcfmxjjldcm.CIField04844561 = CONCAT('applikation', FLOOR(RAND() * 100))
            WHERE cmdb_ci_7a6slcfmxjjldcm.CIField04844561 IS NULL
            LIMIT 20;

    -- Servere
 
    DROP TABLE IF EXISTS `cmdb_ci_8c9wm1w8xvy3bwp`;

    CREATE TABLE `cmdb_ci_8c9wm1w8xvy3bwp` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCompanyID` mediumint DEFAULT NULL,
    `RelatedUserID` mediumint DEFAULT NULL,
    `StartDate` datetime DEFAULT NULL,
    `EndDate` datetime DEFAULT NULL,
    `Active` tinyint DEFAULT NULL,
    `Removed` tinyint DEFAULT NULL,
    `Created` datetime DEFAULT CURRENT_TIMESTAMP,
    `CreatedBy` mediumint DEFAULT NULL,
    `CIField31134889` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField13430456` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField53503866` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField58145025` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField89303621` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField06513090` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField03414802` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField45370864` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField81991607` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField08487643` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField83802187` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField16949315` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField95487281` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField08978168` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField21209166` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

    INSERT INTO cmdb_ci_8c9wm1w8xvy3bwp (RelatedCompanyID, RelatedUserID, StartDate, EndDate, Active, Removed, CreatedBy, CIField31134889, CIField13430456, CIField53503866, CIField58145025, CIField89303621, CIField06513090, CIField03414802, CIField45370864, CIField81991607, CIField08487643, CIField83802187, CIField16949315, CIField95487281, CIField08978168, CIField21209166)
            SELECT 
                FLOOR(RAND() * 2) + 1 AS RelatedCompanyID,
                FLOOR(RAND() * 3) + 1 AS RelatedUserID,
                NOW() AS StartDate,
                DATE_ADD(NOW(), INTERVAL 365 DAY) AS EndDate,
                1 AS Active,
                0 AS Removed,
                1 AS CreatedBy,
                CONCAT(CASE WHEN RAND() > 0.5 THEN 'webserver-' ELSE 'sqlserver-' END, LPAD(FLOOR(RAND() * 10000), 4, '0')) AS CIField31134889,
                FLOOR(RAND() * 3) + 1 AS CIField13430456,
                FLOOR(RAND() * 9) + 4 AS CIField53503866,
                FLOOR(RAND() * 64) + 1 AS CIField58145025,
                FLOOR(RAND() * 121) + 80 AS CIField89303621,
                CASE WHEN RAND() > 0.5 THEN 'Ubuntu 22.04' ELSE 'Microsoft Windows Server 2019' END AS CIField06513090,
                CASE WHEN RAND() > 0.5 THEN '' ELSE 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.' END AS CIField03414802,
                'practicle.dk' AS CIField45370864,
                CASE WHEN RAND() > 0.5 THEN 'PoweredOff' ELSE 'PoweredOn' END AS CIField81991607,
                CONCAT(CASE WHEN RAND() > 0.5 THEN 'webserver-' ELSE 'sqlserver-' END, FLOOR(RAND() * 100), '.', 'practicle.dk') AS CIField08487643,
                CASE WHEN RAND() > 0.5 THEN 'dev01' ELSE 'prod01' END AS CIField83802187,
                CONCAT('12.1.', CASE WHEN RAND() > 0.5 THEN '1' ELSE '5' END) AS CIField16949315,
                CONCAT('7.0.', CASE WHEN RAND() > 0.5 THEN '1' ELSE '5' END) AS CIField95487281,
                CONCAT('10.0.112.', FLOOR(RAND() * 200) + 1) AS CIField08978168,
                FLOOR(RAND() * 15) + 1 AS CIField21209166
            FROM
                (SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) AS numbers1,
                (SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10) AS numbers2,
                (SELECT 1 UNION ALL SELECT 2) AS numbers3
            LIMIT 200;

    -- Business Services

    DROP TABLE IF EXISTS `cmdb_ci_jsf03ynsyjuvoug`;

    CREATE TABLE `cmdb_ci_jsf03ynsyjuvoug` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCompanyID` mediumint DEFAULT NULL,
    `RelatedUserID` mediumint DEFAULT NULL,
    `StartDate` datetime DEFAULT NULL,
    `EndDate` datetime DEFAULT NULL,
    `Active` tinyint(1) DEFAULT NULL,
    `Removed` tinyint(1) DEFAULT NULL,
    `Created` datetime DEFAULT NULL,
    `CreatedBy` mediumint DEFAULT NULL,
    `CIField16831324` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField22810882` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField51453526` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField22943447` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField57929675` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField00234828` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField80678679` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField24359083` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField05041701` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

    INSERT INTO `cmdb_ci_jsf03ynsyjuvoug` (`RelatedCompanyID`, `RelatedUserID`, `StartDate`, `EndDate`, `Active`, `Removed`, `Created`, `CreatedBy`, `CIField16831324`, `CIField22810882`, `CIField51453526`, `CIField22943447`, `CIField57929675`, `CIField00234828`, `CIField80678679`, `CIField24359083`, `CIField05041701`)
            SELECT
                1 AS RelatedCompanyID,
                FLOOR(RAND() * 2) + 1 AS RelatedUserID,
                NOW() AS StartDate,
                DATE_ADD(NOW(), INTERVAL 365 DAY) AS EndDate,
                1 AS Active,
                0 AS Removed,
                NOW() AS Created,
                FLOOR(RAND() * 3) + 1 AS CreatedBy,
                CONCAT('Product ', t.counter) AS CIField16831324,
                CASE WHEN RAND() > 0.5 THEN 1 ELSE 2 END AS CIField22810882,
                0 AS CIField51453526,
                FLOOR(RAND() * 3) + 1 AS CIField22943447,
                CONCAT('Service', FLOOR(RAND() * 10) + 1) AS CIField57929675,
                CASE WHEN RAND() < 0.3333 THEN 1 WHEN RAND() < 0.6666 THEN 2 ELSE 3 END AS CIField00234828,
                CONCAT('Service descript is ', FLOOR(RAND() * 10) + 1) AS CIField80678679,
                CASE WHEN RAND() > 0.5 THEN '' ELSE 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.' END AS CIField24359083,
                CASE WHEN RAND() > 0.5 THEN 1 ELSE 2 END AS CIField05041701
            FROM
                (SELECT DISTINCT FLOOR(RAND() * 99) + 1 AS counter
                FROM
                    (SELECT a.counter + (10 * b.counter) + 1 AS counter
                    FROM
                        (SELECT 0 AS counter
                        UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
                        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
                        UNION SELECT 9) a
                    CROSS JOIN
                        (SELECT 0 AS counter
                        UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
                        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
                        UNION SELECT 9) b
                    ) AS numbers
                LIMIT 25
                ) AS t
            LEFT JOIN
                `cmdb_ci_jsf03ynsyjuvoug` AS existing_records
                ON CONCAT('Product ', t.counter) = existing_records.CIField16831324
            WHERE
                existing_records.CIField16831324 IS NULL;

    -- DNS

    DROP TABLE IF EXISTS `cmdb_ci_jfgk2lcpcam0kdg`;

    CREATE TABLE `cmdb_ci_jfgk2lcpcam0kdg` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCompanyID` mediumint DEFAULT NULL,
    `RelatedUserID` mediumint DEFAULT NULL,
    `StartDate` datetime DEFAULT NULL,
    `EndDate` datetime DEFAULT NULL,
    `Active` tinyint(1) DEFAULT NULL,
    `Removed` tinyint(1) DEFAULT NULL,
    `Created` datetime DEFAULT NULL,
    `CreatedBy` mediumint DEFAULT NULL,
    `CIField84571059` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField85923326` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField64054212` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField53135041` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField81559787` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

    INSERT INTO `cmdb_ci_jfgk2lcpcam0kdg` (`RelatedCompanyID`, `RelatedUserID`, `StartDate`, `EndDate`, `Active`, `Removed`, `Created`, `CreatedBy`, `CIField84571059`, `CIField85923326`, `CIField64054212`, `CIField53135041`, `CIField81559787`)
            SELECT
                1 AS RelatedCompanyID,
                FLOOR(RAND() * 2) + 1 AS RelatedUserID,
                NOW() AS StartDate,
                DATE_ADD(NOW(), INTERVAL 365 DAY) AS EndDate,
                1 AS Active,
                0 AS Removed,
                NOW() AS Created,
                FLOOR(RAND() * 3) + 1 AS CreatedBy,
                CONCAT('practicle', LPAD(FLOOR(RAND() * 1000), 3, '0')) AS CIField84571059,
                'practicle.dk' AS CIField85923326,
                'CNAME' AS CIField64054212,
                CONCAT('10.0.112.', FLOOR(RAND() * 200) + 1) AS CIField53135041,
                CONCAT('Service', FLOOR(RAND() * 10) + 1) AS CIField81559787
            FROM
                (SELECT DISTINCT FLOOR(RAND() * 99) + 1 AS counter
                FROM
                    (SELECT a.counter + (10 * b.counter) + 1 AS counter
                    FROM
                        (SELECT 0 AS counter
                        UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
                        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
                        UNION SELECT 9) a
                    CROSS JOIN
                        (SELECT 0 AS counter
                        UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
                        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
                        UNION SELECT 9) b
                    ) AS numbers
                LIMIT 25
                ) AS t
            LEFT JOIN
                `cmdb_ci_jfgk2lcpcam0kdg` AS existing_records
                ON CONCAT('practicle', LPAD(FLOOR(RAND() * 1000), 3, '0')) = existing_records.CIField84571059
            WHERE
                existing_records.CIField84571059 IS NULL;

    -- MSSQL Databases

    DROP TABLE IF EXISTS `cmdb_ci_ryiioybl4u265vd`;

    CREATE TABLE `cmdb_ci_ryiioybl4u265vd` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCompanyID` mediumint DEFAULT NULL,
    `RelatedUserID` mediumint DEFAULT NULL,
    `StartDate` datetime DEFAULT NULL,
    `EndDate` datetime DEFAULT NULL,
    `Active` tinyint(1) DEFAULT NULL,
    `Removed` tinyint(1) DEFAULT NULL,
    `Created` datetime DEFAULT NULL,
    `CreatedBy` mediumint DEFAULT NULL,
    `CIField77502389` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField50154413` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField24483594` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField31244417` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField21246935` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

    INSERT INTO `cmdb_ci_ryiioybl4u265vd` (`RelatedCompanyID`, `RelatedUserID`, `StartDate`, `EndDate`, `Active`, `Removed`, `Created`, `CreatedBy`, `CIField77502389`, `CIField50154413`, `CIField24483594`, `CIField31244417`, `CIField21246935`)
            SELECT
                1 AS RelatedCompanyID,
                FLOOR(RAND() * 2) + 1 AS RelatedUserID,
                NOW() AS StartDate,
                DATE_ADD(NOW(), INTERVAL 365 DAY) AS EndDate,
                1 AS Active,
                0 AS Removed,
                NOW() AS Created,
                FLOOR(RAND() * 3) + 1 AS CreatedBy,
                CONCAT('dbpracticle', LPAD(FLOOR(RAND() * 1000), 3, '0')) AS CIField77502389,
                'practicle.dk' AS CIField50154413,
                FLOOR(RAND() * 25) + 1 AS CIField24483594,
                FLOOR(RAND() * 99) + 2 AS CIField31244417,
                CASE WHEN RAND() > 0.5 THEN 'mssql2019' ELSE 'mssql2022' END AS CIField21246935
            FROM
                (SELECT DISTINCT FLOOR(RAND() * 99) + 1 AS counter
                FROM
                    (SELECT a.counter + (10 * b.counter) + 1 AS counter
                    FROM
                        (SELECT 0 AS counter
                        UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
                        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
                        UNION SELECT 9) a
                    CROSS JOIN
                        (SELECT 0 AS counter
                        UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4
                        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8
                        UNION SELECT 9) b
                    ) AS numbers
                LIMIT 25
                ) AS t
            LEFT JOIN
                `cmdb_ci_ryiioybl4u265vd` AS existing_records
                ON CONCAT('dbpracticle', LPAD(FLOOR(RAND() * 1000), 3, '0')) = existing_records.CIField77502389
            WHERE
                existing_records.CIField77502389 IS NULL;

    -- mssql instances
    DROP TABLE IF EXISTS `cmdb_ci_f67j0cfdv39z89q`;
    
    CREATE TABLE `cmdb_ci_f67j0cfdv39z89q` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCompanyID` mediumint DEFAULT NULL,
    `RelatedUserID` mediumint DEFAULT NULL,
    `StartDate` datetime DEFAULT NULL,
    `EndDate` datetime DEFAULT NULL,
    `Active` tinyint(1) DEFAULT NULL,
    `Removed` tinyint(1) DEFAULT NULL,
    `Created` datetime DEFAULT NULL,
    `CreatedBy` mediumint DEFAULT NULL,
    `CIField22761230` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField87567547` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField54835793` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField82946239` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField19104153` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    PRIMARY KEY (`ID`),
    FULLTEXT KEY `CIField19104153` (`CIField19104153`)
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci COMMENT='ciid=14';

    INSERT INTO `cmdb_ci_f67j0cfdv39z89q` (`ID`, `RelatedCompanyID`, `RelatedUserID`, `StartDate`, `EndDate`, `Active`, `Removed`, `Created`, `CreatedBy`, `CIField22761230`, `CIField87567547`, `CIField54835793`, `CIField82946239`, `CIField19104153`) VALUES
    (1, 1, 1, '2023-07-06 20:43:17', '2024-07-06 21:00:26', 1, 0, '2023-07-06 20:43:17', 1, 'sqlserver-10', 'practicle.dk', 'running', 'mssql2019', 'mssql2019'),
    (2, 1, 1, '2023-07-06 20:43:17', '2024-07-06 21:00:26', 1, 0, '2023-07-06 20:43:17', 1, 'sqlserver-10', 'practicle.dk', 'running', 'mssql2022', 'mssql2022');

    -- Certificates
    DROP TABLE IF EXISTS `cmdb_ci_9iatqm9mcc42vl1`;
    
    CREATE TABLE `cmdb_ci_9iatqm9mcc42vl1` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCompanyID` mediumint DEFAULT NULL,
    `RelatedUserID` mediumint DEFAULT NULL,
    `StartDate` datetime DEFAULT NULL,
    `EndDate` datetime DEFAULT NULL,
    `Active` tinyint(1) DEFAULT NULL,
    `Removed` tinyint(1) DEFAULT NULL,
    `Created` datetime DEFAULT NULL,
    `CreatedBy` mediumint DEFAULT NULL,
    `CIField67895146` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField01018409` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField88104610` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField02200715` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `CIField81627337` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    PRIMARY KEY (`ID`),
    FULLTEXT KEY `CIField67895146` (`CIField67895146`),
    FULLTEXT KEY `CIField67895146_2` (`CIField67895146`),
    FULLTEXT KEY `CIField67895146_3` (`CIField67895146`),
    FULLTEXT KEY `CIField67895146_4` (`CIField67895146`),
    FULLTEXT KEY `CIField67895146_5` (`CIField67895146`),
    FULLTEXT KEY `CIField67895146_6` (`CIField67895146`),
    FULLTEXT KEY `CIField67895146_7` (`CIField67895146`)
    ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci COMMENT='ciid=13';

    INSERT INTO `cmdb_ci_9iatqm9mcc42vl1` (`ID`, `RelatedCompanyID`, `RelatedUserID`, `StartDate`, `EndDate`, `Active`, `Removed`, `Created`, `CreatedBy`, `CIField67895146`, `CIField01018409`, `CIField88104610`, `CIField02200715`, `CIField81627337`) VALUES
    (1, 1, 1, '2023-06-15 12:30:10', '2023-08-03 15:08:24', 1, 0, '2023-06-15 12:30:10', 1, 'support.practicle.dk', '1', NULL, 'Nej', NULL),
    (2, 1, 1, '2023-06-20 17:44:39', '2023-09-26 05:09:48', 1, 0, '2023-06-20 17:44:39', 1, 'test.practicle.dk', '1', NULL, 'Nej', NULL);

    -- cmdb_ci_fieldslist
    DROP TABLE IF EXISTS `cmdb_ci_fieldslist`;

    CREATE TABLE `cmdb_ci_fieldslist` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCITypeID` int NOT NULL,
    `FieldName` varchar(55) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `FieldLabel` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `FieldType` int NOT NULL,
    `FieldOrder` int NOT NULL,
    `FieldDefaultValue` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `FieldTitle` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `SelectFieldOptions` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `FieldWidth` smallint NOT NULL DEFAULT '4',
    `DefaultField` tinyint NOT NULL DEFAULT '0',
    `LookupTable` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `LookupField` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `LookupFieldResultTable` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `LookupFieldResultView` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `RelationShowField` tinyint DEFAULT '0',
    `ImportSourceField` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `SyncSourceField` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `RelationsLookup` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `Indexed` tinyint DEFAULT '0',
    `HideForms` tinyint DEFAULT '0',
    `HideTables` tinyint DEFAULT '0',
    `Required` tinyint DEFAULT '0',
    `Locked` tinyint DEFAULT '0',
    `Addon` mediumint DEFAULT NULL,
    `AddEmpty` tinyint(1) DEFAULT '0',
    `FullHeight` tinyint(1) DEFAULT '0',
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=196 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

    INSERT INTO `cmdb_ci_fieldslist` (`ID`, `RelatedCITypeID`, `FieldName`, `FieldLabel`, `FieldType`, `FieldOrder`, `FieldDefaultValue`, `FieldTitle`, `SelectFieldOptions`, `FieldWidth`, `DefaultField`, `LookupTable`, `LookupField`, `LookupFieldResultTable`, `LookupFieldResultView`, `RelationShowField`, `ImportSourceField`, `SyncSourceField`, `RelationsLookup`, `Indexed`, `HideForms`, `HideTables`, `Required`, `Locked`, `Addon`, `AddEmpty`, `FullHeight`) VALUES
    (1, 10, 'RelatedCompanyID', 'Related Company', 4, 1000, '2', '', '', 4, 1, 'companies', 'ID', 'Companyname ', 'Companyname ', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (2, 10, 'RelatedUserID', 'Related User', 4, 2000, '2', '', '', 4, 1, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (3, 10, 'StartDate', 'Start Date', 5, 3000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (4, 10, 'EndDate', 'End Date', 5, 4000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (5, 10, 'Active', 'Active', 4, 5000, '1', '', '<option value=\"0\">Inactive</option>#<option value=\"1\">Active</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (6, 10, 'Removed', 'Removed', 4, 6000, '0', '', '<option value=\"0\">Active</option>#<option value=\"1\">Removed</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (7, 10, 'Created', 'Created', 5, 7000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (8, 10, 'CreatedBy', 'Created By', 4, 8000, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (15, 10, 'CIField31134889', 'Hostname', 1, 100, '', 'Hostname', '', 4, 0, '', '', '', '', 1, 'VM', '', 'cmdb_ci_6iyovf6jjka9a8b,CIField73322016#cmdb_ci_jfgk2lcpcam0kdg,CIField84571059#cmdb_ci_f67j0cfdv39z89q,CIField22761230', 1, 0, 0, 0, 0, NULL, NULL, 0),
    (16, 10, 'CIField13430456', 'Responsible', 4, 175, '2', 'Responsible', '', 4, 0, 'users', 'ID', 'CONCAT(CIField13430456.Firstname,\" \",CIField13430456.Lastname,\" (\",CIField13430456.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (17, 10, 'CIField53503866', 'CPU', 1, 150, '2', 'CPU (number of cores)', '', 4, 0, '', '', '', '', 0, 'vCPU', 'vCPU', NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (18, 10, 'CIField58145025', 'Ram', 1, 125, '4', 'Ram (GB)', '', 4, 0, '', '', '', '', 0, 'RAM', 'RAM', NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (45, 10, 'CIField89303621', 'Disk Space', 1, 151, '', 'Disk space', '', 4, 0, '', '', '', '', 0, 'Tier2', 'Tier2', NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (46, 10, 'CIField06513090', 'OS', 1, 120, '', 'OS', '', 4, 0, '', '', '', '', 0, 'OS', 'OS', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (47, 10, 'CIField03414802', 'Notes', 2, 225, '', 'Notes', '', 12, 0, '', '', '', '', 0, 'Desc', '', '', 1, 0, 1, 0, 0, NULL, NULL, 0),
    (88, 10, 'CIField45370864', 'Domain', 1, 106, '', 'Domain', '', 4, 0, '', '', '', '', 0, 'Domain', 'Domain', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (89, 10, 'CIField81991607', 'Power', 1, 156, '', 'Power Status', '', 4, 0, '', '', '', '', 0, 'Power', 'Power', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (90, 10, 'CIField08487643', 'FQDN', 1, 153, '', 'Full Qualified Domain Name', '', 4, 0, '', '', '', '', 0, 'FQDN', 'FQDN', 'cmdb_ci_jfgk2lcpcam0kdg,CIField81559787', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (91, 10, 'CIField83802187', 'Network', 1, 155, '', 'Network', '', 4, 0, '', '', '', '', 0, 'Network', 'Network', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (92, 10, 'CIField16949315', 'VM Tools', 1, 152, '', 'VMWare Tools version', '', 4, 0, '', '', '', '', 0, 'ToolsVersion', 'ToolsVersion', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (93, 10, 'CIField95487281', 'FortiClient Version', 1, 152, '', 'FortiClient Version', '', 4, 0, '', '', '', '', 0, 'FortiVersion', 'FortiVersion', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (94, 10, 'CIField08978168', 'IP', 1, 160, '', 'IP Adresser', '', 4, 0, '', '', '', '', 0, 'IPs', 'IPs', '', 1, 0, 1, 0, 0, NULL, NULL, 0),
    (95, 11, 'RelatedCompanyID', 'Related Company', 4, 1073, '1', '', '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (96, 11, 'RelatedUserID', 'Related User', 4, 2073, '1', '', '', 4, 1, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (97, 11, 'StartDate', 'Start Date', 5, 3073, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (98, 11, 'EndDate', 'End Date', 5, 4073, NULL, NULL, '', 4, 1, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (99, 11, 'Active', 'Active', 4, 5073, '1', '', '<option value=\"1\">Active</option>#<option value=\"0\">Inactive</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (100, 11, 'Removed', 'Removed', 4, 6073, '0', '', '<option value=\"0\">Removed</option>#<option value=\"1\">Active</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (101, 11, 'Created', 'Created', 5, 7073, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (102, 11, 'CreatedBy', 'Created By', 4, 8073, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (103, 11, 'CIField60246997', 'Name', 1, 97, '', 'Name', '', 4, 0, '', '', '', '', 1, 'Name', 'Name', '', 1, 0, 0, 0, 0, NULL, NULL, 0),
    (104, 11, 'CIField78141913', 'Domain', 1, 123, '', 'Domain', '', 4, 0, '', '', '', '', 0, 'Domain', 'Domain', NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (105, 11, 'CIField69973069', 'State', 1, 148, '', 'State', '', 4, 0, '', '', '', '', 0, 'State', 'State', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (110, 11, 'CIField67447549', 'Application Pool', 1, 173, '', 'Application Pool', '', 4, 0, '', '', '', '', 0, 'Application Pool', 'Application Pool', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (113, 11, 'CIField73322016', 'On Server', 1, 248, '', 'On Server', '', 4, 0, '', '', '', '', 0, 'On Server', 'On Server', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (114, 12, 'RelatedCompanyID', 'Related Company', 4, 1000, '2', '', '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, '', '', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (115, 12, 'RelatedUserID', 'Related User', 4, 2000, '2', '', '', 4, 1, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (116, 12, 'StartDate', 'Start Date', 5, 3000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (117, 12, 'EndDate', 'End Date', 5, 4000, '', NULL, '', 4, 1, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (118, 12, 'Active', 'Active', 4, 5000, '1', '', '<option value=\"0\">Inactive</option>#<option value=\"1\">Active</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (119, 12, 'Removed', 'Removed', 4, 6000, '0', '', '<option value=\"0\">Active</option>#<option value=\"1\">Removed</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (120, 12, 'Created', 'Created', 5, 7000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (121, 12, 'CreatedBy', 'Created By', 4, 8000, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (122, 12, 'CIField84571059', 'Host Name', 1, 50, '', 'Host Name', '', 4, 0, '', '', '', '', 1, 'HostName', '', '', 1, 0, 0, 0, 0, NULL, NULL, 0),
    (123, 12, 'CIField85923326', 'Domain', 1, 75, '', 'Domain', '', 4, 0, '', '', '', '', 0, 'Domain', 'Domain', NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (124, 12, 'CIField64054212', 'Record Type', 1, 100, '', 'Record Type', '', 4, 0, '', '', '', '', 0, 'RecordType', 'RecordType', NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (125, 12, 'CIField53135041', 'IP', 1, 125, '', 'IP', '', 4, 0, '', '', '', '', 0, 'IP', 'IP', NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (126, 12, 'CIField81559787', 'CName', 1, 150, '', 'CName', '', 4, 0, '', '', '', '', 0, 'CName', 'CName', NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (127, 13, 'RelatedCompanyID', 'Related Company', 4, 1000, '1', '', '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, '', '', NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (128, 13, 'RelatedUserID', 'Related User', 4, 2000, '1', '', '', 4, 1, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (129, 13, 'StartDate', 'Start Date', 5, 3000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (130, 13, 'EndDate', 'End Date', 5, 4000, '', NULL, '', 4, 1, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (131, 13, 'Active', 'Active', 4, 5000, '1', '', '<option value=\"0\">Inactive</option>#<option value=\"1\">Active</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (132, 13, 'Removed', 'Removed', 4, 6000, '0', '', '<option value=\"0\">Active</option>#<option value=\"1\">Removed</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (133, 13, 'Created', 'Created', 5, 7000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (134, 13, 'CreatedBy', 'Created By', 4, 8000, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (135, 13, 'CIField67895146', 'Domain', 1, 25, '', 'Domain', '', 3, 0, '', '', '', '', 1, '', '', '', 1, 0, 0, 0, 0, 1, NULL, 0),
    (136, 13, 'CIField01018409', 'Responsible', 4, 50, '1', 'Responsible', '', 3, 0, 'users', 'ID', 'CONCAT(CIField01018409.Firstname,\" \",CIField01018409.Lastname,\" (\",CIField01018409.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (137, 13, 'CIField88104610', 'Notes', 2, 125, '', 'Notes', '', 12, 0, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (138, 10, 'CIField21209166', 'Project', 1, 105, '', 'Project', '', 4, 0, '', '', '', '', 0, 'Project', 'Project', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (139, 14, 'RelatedCompanyID', 'Related Company', 4, 1000, '2', '', '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, '', '', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (140, 14, 'RelatedUserID', 'Related User', 4, 2000, '2', '', '', 4, 1, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (141, 14, 'StartDate', 'Start Date', 5, 3000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (142, 14, 'EndDate', 'End Date', 5, 4000, '', NULL, '', 4, 1, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (143, 14, 'Active', 'Active', 4, 5000, '1', '', '<option value=\"0\">Inactive</option>#<option value=\"1\">Active</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (144, 14, 'Removed', 'Removed', 4, 6000, '0', '', '<option value=\"0\">Active</option>#<option value=\"1\">Removed</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (145, 14, 'Created', 'Created', 5, 7000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (146, 14, 'CreatedBy', 'Created By', 4, 8000, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (147, 14, 'CIField22761230', 'Server Name', 1, 150, '', 'Server Name', '', 4, 0, '', '', '', '', 0, 'ServerName', '', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (148, 14, 'CIField87567547', 'Domain', 1, 200, '', 'Domain', '', 4, 0, '', '', '', '', 0, 'Domain', 'Domain', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (149, 14, 'CIField54835793', 'Status', 1, 300, '', 'Status', '', 4, 0, '', '', '', '', 0, 'Status', 'Status', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (150, 14, 'CIField82946239', 'Name', 1, 400, '', 'Name', '', 4, 0, '', '', '', '', 0, 'Name', 'Name', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (151, 14, 'CIField19104153', 'Instance Name', 1, 100, '', 'Instance Name', '', 4, 0, '', '', '', '', 1, 'DisplayName', 'DisplayName', 'cmdb_ci_ryiioybl4u265vd,CIField21246935', 1, 0, 0, 0, 0, NULL, NULL, 0),
    (152, 15, 'RelatedCompanyID', 'Related Company', 4, 1000, '2', '', '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, '', '', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (153, 15, 'RelatedUserID', 'Related User', 4, 2000, '2', '', '', 4, 1, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (154, 15, 'StartDate', 'Start Date', 5, 3000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (155, 15, 'EndDate', 'End Date', 5, 4000, '', NULL, '', 4, 1, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (156, 15, 'Active', 'Active', 4, 5000, '1', '', '<option value=\"0\">Inactive</option>#<option value=\"1\">Active</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (157, 15, 'Removed', 'Removed', 4, 6000, '0', '', '<option value=\"0\">Active</option>#<option value=\"1\">Removed</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (158, 15, 'Created', 'Created', 5, 7000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (159, 15, 'CreatedBy', 'Created By', 4, 8000, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (160, 15, 'CIField77502389', 'Name', 1, 100, '', 'Name', '', 4, 0, '', '', '', '', 1, 'name', '', '', 1, 0, 0, 0, 0, NULL, NULL, 0),
    (161, 15, 'CIField50154413', 'Domain', 1, 200, '', 'Domain', '', 4, 0, '', '', '', '', 0, 'Domain', 'Domain', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (162, 15, 'CIField24483594', 'CostCenter', 1, 300, '', 'CostCenter', '', 4, 0, '', '', '', '', 0, 'CostCenter', 'CostCenter', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (163, 15, 'CIField31244417', 'Size In MB', 1, 400, '', 'Size In MB', '', 4, 0, '', '', '', '', 0, 'Size_MBs', 'Size_MBs', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (164, 15, 'CIField21246935', 'Instance', 1, 500, '', 'Instance', '', 4, 0, '', '', '', '', 0, 'InstanceName', 'InstanceName', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (165, 1, 'RelatedCompanyID', 'Related Company', 4, 1008, '', NULL, '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (166, 1, 'RelatedUserID', 'Related User', 4, 2008, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 1, 0, 0, 0, NULL, NULL, 0),
    (167, 1, 'StartDate', 'Start Date', 5, 3008, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (168, 1, 'EndDate', 'End Date', 5, 4008, '', NULL, '', 4, 1, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (169, 1, 'Active', 'Active', 4, 5008, '1', '', '<option value=\"0\">Inactive</option>#<option value=\"1\">Active</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (170, 1, 'Removed', 'Removed', 4, 6008, '0', '', '<option value=\"0\">Active</option>#<option value=\"1\">Removed</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 1, 1, 0, 0, NULL, NULL, 0),
    (171, 1, 'Created', 'Created', 5, 7008, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (172, 1, 'CreatedBy', 'Created By', 4, 8008, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (173, 1, 'CIField16831324', 'Navn', 1, 18, '', 'Navn', '', 4, 0, '', '', '', '', 1, '', '', '', 1, 0, 0, 0, 0, NULL, NULL, 0),
    (174, 1, 'CIField22810882', 'Type', 4, 28, '', 'Type', '<option value=\"Customer related Service\">Customer related Service</option>#<option value=\"Internal Service\">Internal Service</option>', 4, 0, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (175, 1, 'CIField51453526', 'Shared', 4, 38, '0', 'Shared', '<option value=\"Yes\">Yes</option>#<option value=\"No\">No</option>', 2, 0, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (176, 1, 'CIField22943447', 'SLA', 4, 48, '1', 'Related SLA', '<option value=\"\"></option>#<option value=\"1\">Gold</option>#<option value=\"2\">Silver</option>#<option value=\"3\">Bronze</option>\r\n', 2, 0, 'slaagreements', 'ID', 'Name', 'Name', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (178, 1, 'CIField57929675', 'Short Name', 1, 78, '', 'Short Name (used for relations to sub CIs)', '', 4, 0, '', '', '', '', 0, '', '', 'cmdb_ci_8c9wm1w8xvy3bwp,CIField21209166#cmdb_ci_ryiioybl4u265vd,CIField24483594', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (179, 19, 'RelatedCompanyID', 'Related Company', 4, 1000, '', NULL, '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (180, 19, 'RelatedUserID', 'Related User', 4, 2000, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (181, 19, 'StartDate', 'Start Date', 5, 3000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (182, 19, 'EndDate', 'End Date', 5, 4000, '', NULL, '', 4, 1, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, 0, 0, NULL, NULL, 0),
    (183, 19, 'Active', 'Active', 4, 5000, '1', '', '<option value=\"0\">Inactive</option>#<option value=\"1\">Active</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (184, 19, 'Removed', 'Removed', 4, 6000, '0', '', '<option value=\"0\">Active</option>#<option value=\"1\">Removed</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (185, 19, 'Created', 'Created', 5, 7000, '', '', '', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (186, 19, 'CreatedBy', 'Created By', 4, 8000, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (187, 19, 'CIField04844561', 'Name', 1, 10, '', 'Name', '', 6, 0, '', '', '', '', 1, '', '', '', 1, 0, 0, 0, 0, NULL, NULL, 0),
    (188, 19, 'CIField08443731', 'Version', 1, 20, '', 'Version', '', 6, 0, '', '', '', '', 0, '', '', '', 1, 0, 0, 0, 0, NULL, NULL, 0),
    (189, 19, 'CIField34010167', 'Notes', 2, 30, '', 'Notes', '', 12, 0, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (190, 1, 'CIField00234828', 'Product Owner', 4, 58, '', 'Product Owner', '', 4, 0, 'users', 'ID', 'CONCAT(CIField00234828.Firstname,\" \",CIField00234828.Lastname,\" (\",CIField00234828.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (191, 1, 'CIField80678679', 'Description', 2, 88, '', 'Description', '', 12, 0, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (192, 1, 'CIField24359083', 'Notes', 2, 98, '', 'Notes', '', 12, 0, '', '', '', '', 0, '', '', '', 0, 0, 1, 0, 0, NULL, NULL, 0),
    (193, 1, 'CIField05041701', 'Developer Team', 4, 68, '', 'Developer Team', '', 4, 0, 'teams', 'ID', 'Teamname', 'Teamname', 0, '', '', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (194, 13, 'CIField02200715', 'Pre Godkendt', 4, 100, '', 'Er pre godkendt af kunden - kan auto fornyes', '<option value=\"Nej\">Nej</option>#<option value=\"Ja\">Ja</option>', 3, 0, '', '', '', '', 0, '', '', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (195, 13, 'CIField81627337', 'Projekt nummer', 1, 85, '', 'Projekt nummer', '', 3, 0, '', '', '', '', 0, '', '', '', 0, 0, 0, 0, 0, NULL, NULL, 0),
    (196, 11, 'CIField39031921', 'Notes', 2, 122, '', 'Notes', '', 12, 0, '', NULL, '', '', 0, '', '', '', 1, 0, 1, 0, 0, NULL, NULL, 0);

    -- cmdb_cis
    DROP TABLE IF EXISTS `cmdb_cis`;

    CREATE TABLE `cmdb_cis` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `Name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
    `TableName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
    `Description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
    `CreatedBy` mediumint NOT NULL,
    `Created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastEdited` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastEditedBy` mediumint NOT NULL,
    `Active` tinyint NOT NULL DEFAULT '1',
    `GroupID` mediumint DEFAULT NULL,
    `ImportSource` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `Synchronization` tinyint NOT NULL DEFAULT '0',
    `SyncTime` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
    `LastSyncronized` datetime DEFAULT NULL,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

    INSERT INTO `cmdb_cis` (`ID`, `Name`, `TableName`, `Description`, `CreatedBy`, `Created`, `LastEdited`, `LastEditedBy`, `Active`, `GroupID`, `ImportSource`, `Synchronization`, `SyncTime`, `LastSyncronized`) VALUES
    (1, 'Forretningsservices', 'cmdb_ci_jsf03ynsyjuvoug', 'Forretningsservices', 2, '2023-02-03 13:38:57', '2023-02-03 13:38:57', 2, 1, 100014, NULL, 0, NULL, NULL),
    (10, 'Servere', 'cmdb_ci_8c9wm1w8xvy3bwp', 'Servere', 1, '2023-01-17 20:43:46', '2023-01-17 20:43:46', 1, 1, 100014, 'ci_servers.csv', 1, '06:00', '2023-05-24 06:02:43'),
    (11, 'IIS Sites', 'cmdb_ci_6iyovf6jjka9a8b', 'IIS Sites', 1, '2023-01-18 14:57:15', '2023-01-18 14:57:15', 1, 1, 100014, 'ci_iissites.csv', 1, '06:05', '2023-05-24 06:08:01'),
    (12, 'DNS', 'cmdb_ci_jfgk2lcpcam0kdg', 'DNS', 1, '2023-01-30 08:44:18', '2023-01-30 08:44:18', 1, 1, 100014, 'ci_dns.csv', 1, '06:15', '2023-05-24 06:17:56'),
    (13, 'Certifikater', 'cmdb_ci_9iatqm9mcc42vl1', 'Certifikater', 1, '2023-01-30 09:26:32', '2023-01-30 09:26:32', 1, 1, 100014, 'ci_certifikater.csv', 0, '', NULL),
    (14, 'MSSQL Server Instanser', 'cmdb_ci_f67j0cfdv39z89q', 'MSSQL Server Instanser', 2, '2023-02-03 13:05:43', '2023-02-03 13:05:43', 2, 1, 100014, 'ci_mssqlservers.csv', 1, '06:10', '2023-05-24 06:12:28'),
    (15, 'MSSQL Databaser', 'cmdb_ci_ryiioybl4u265vd', 'MSSQL Databaser', 2, '2023-02-03 13:22:13', '2023-02-03 13:22:13', 2, 1, 100014, 'ci_mssqldatabases.csv', 1, '06:10', '2023-05-24 06:12:45'),
    (19, 'Applikationer', 'cmdb_ci_7a6slcfmxjjldcm', 'Applikationer', 2, '2023-02-22 12:11:17', '2023-02-22 12:11:17', 2, 1, 100014, NULL, 0, NULL, NULL);

    -- cmdb_fieldslist_types
    DROP TABLE IF EXISTS `cmdb_fieldslist_types`;

    CREATE TABLE `cmdb_fieldslist_types` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `TypeName` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
    `DBFieldDef` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
    `Definition` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

    INSERT INTO `cmdb_fieldslist_types` (`ID`, `TypeName`, `DBFieldDef`, `Definition`) VALUES
    (1, 'Text', 'TINYTEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:>>\r\n</div>\r\n</div>'),
    (2, 'Note', 'LONGTEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div><div class=\"input-group input-group-static mb-4\"><label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:>&ensp;<a href=\"javascript:toggleCKEditor(\'<:fieldid:>\',\'<:height:>\');\"><i class=\"fa-solid fa-pen fa-sm\" title=\"Double click on field to edit\"></i></a></label></div><div style=\"height: <:height:>; word-wrap: break-word; overflow-y: auto; overflow-x: auto;\" class=\"resizable_textarea form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"Double click to edit\" rows=\"5\" autocomplete=\"off\" ondblclick=\"toggleCKEditor(\'<:fieldid:>\',\'<:height:>\');\" <:required:> <:Locked:> ><:fieldvalue:></div></div></div>'),
    (3, 'Check', 'TINYINT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\"><:label:><:requiredlabel:><:addonBtn:></label>&nbsp;\r\n<input type=\"checkbox\" name=\"<:fieldname:>\" id=\"<:fieldid:>\" <:required:> <:Locked:>>\r\n</input>\r\n</div>\r\n</div>'),
    (4, 'Select', 'TEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label>\r\n<select class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"<:fieldtitle:>\" <:required:> <:Locked:>>\r\n<:selectoptions:>\r\n</select>\r\n</div>\r\n</div>'),
    (5, 'Date', 'VARCHAR(50)', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" autocomplete=\"off\" onclick=\"runDateTimePicker(\'<:fieldid:>\');\"<:Locked:>>\r\n</div>\r\n</div>'),
    (6, 'Number', 'INT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:>>\r\n</div>\r\n</div>'),
    (7, 'Relation', 'MEDIUMINT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\">\r\n</div>\r\n</div>'),
    (8, 'Booelan', 'TINYINT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label>\r\n<select class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"<:fieldtitle:>\">\r\n<:selectoptions:>\r\n</select>\r\n</div>\r\n</div>'),
    (9, 'Password', 'VARCHAR(255)', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:><a href=\"javascript:generateRandomPassword(\'<:fieldid:>\');\"><i class=\"fa-solid fa-shuffle\"></i></a> <a href=\"javascript:void(0)\" onclick=\"togglePasswordVisibility(\'<:fieldid:>\')\"><i class=\"fa-regular fa-eye\"></i></a></label></p><input type=\"password\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"new-password\" <:required:> <:Locked:> ondblclick=\"copytoclipboard(\'<:fieldid:>\');\">\r\n</div>\r\n</div>'),
    (10, 'IP', 'TEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:>>\r\n</div>\r\n</div>');

    -- cmdb_ci_default_fields
    DROP TABLE IF EXISTS `cmdb_ci_default_fields`;

    CREATE TABLE `cmdb_ci_default_fields` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `FieldName` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `DBFieldType` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `FieldType` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `Label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `RelationShowField` tinyint DEFAULT '0',
    `FieldOrder` smallint DEFAULT NULL,
    `FieldDefaultValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `SelectFieldOptions` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `LookupTable` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `LookupField` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `LookupFieldResultTable` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `LookupFieldResultView` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

    INSERT INTO `cmdb_ci_default_fields` (`ID`, `FieldName`, `DBFieldType`, `FieldType`, `Label`, `RelationShowField`, `FieldOrder`, `FieldDefaultValue`, `SelectFieldOptions`, `LookupTable`, `LookupField`, `LookupFieldResultTable`, `LookupFieldResultView`) VALUES
    (1, 'Name', 'MEDIUMTEXT', '1', 'Name', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
    (2, 'RelatedCompanyID', 'MEDIUMINT', '4', 'Related Company', 0, 2, NULL, NULL, 'companies', 'ID', 'Companyname', 'Companyname'),
    (3, 'RelatedUserID', 'MEDIUMINT', '4', 'Related User', 0, 3, NULL, NULL, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")'),
    (4, 'StartDate', 'DATETIME', '5', 'Start Date', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL),
    (5, 'EndDate', 'DATETIME', '5', 'End Date', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL),
    (6, 'Active', 'BOOLEAN', '4', 'Active', 0, 6, '1', '<option value=\"0\">Inactive</option>\r\n<option value=\"1\">Active</option>', NULL, NULL, NULL, NULL),
    (7, 'Removed', 'BOOLEAN', '4', 'Removed', 0, 7, '0', '<option value=\"0\">Active</option>\r\n<option value=\"1\">Removed</option>', NULL, NULL, NULL, NULL),
    (8, 'Created', 'DATETIME', '5', 'Created', 0, 8, NULL, NULL, NULL, NULL, NULL, NULL),
    (9, 'CreatedBy', 'MEDIUMINT', '4', 'Created By', 0, 9, NULL, NULL, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")');

    -- cmdb_ci_relations
    DROP TABLE IF EXISTS `cmdb_ci_relations`;

    CREATE TABLE `cmdb_ci_relations` (
    `ID` bigint NOT NULL AUTO_INCREMENT,
    `CITable1` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `CITable2` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `CI1ID` mediumint NOT NULL,
    `CI2ID` mediumint NOT NULL,
    `auto` tinyint(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;
    
    DROP TABLE IF EXISTS `cmdb_logbook`;

    CREATE TABLE `cmdb_logbook` (
    `ID` bigint NOT NULL AUTO_INCREMENT,
    `RelatedCIType` int NOT NULL,
    `RelatedCI` int NOT NULL,
    `LogContent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `Relation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `Date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `RelatedUserID` int NOT NULL,
    `Status` tinyint(1) NOT NULL DEFAULT '1',
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

    TRUNCATE TABLE files_cis;";

    if ($conn->multi_query($sql)) {
        return "Completed";
    } else {
        return "error";
    }
}

function resetCMDB()
{
    global $conn;
    global $functions;

    deleteNotExistingModules("cmdb");

    $sql = "
    -- Create Business Service table

    CREATE TABLE `cmdb_ci_jsf03ynsyjuvoug` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCompanyID` mediumint DEFAULT NULL,
    `RelatedUserID` mediumint DEFAULT NULL,
    `StartDate` datetime DEFAULT NULL,
    `EndDate` datetime DEFAULT NULL,
    `Active` tinyint(1) DEFAULT NULL,
    `Removed` tinyint(1) DEFAULT NULL,
    `Created` datetime DEFAULT NULL,
    `CreatedBy` mediumint DEFAULT NULL,
    `CIField16831324` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField22810882` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField51453526` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField22943447` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField57929675` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField00234828` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField80678679` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField24359083` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `CIField05041701` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
    
    -- Create demo service
    INSERT INTO `cmdb_ci_jsf03ynsyjuvoug` (`ID`, `RelatedCompanyID`, `RelatedUserID`, `StartDate`, `EndDate`, `Active`, `Removed`, `Created`, `CreatedBy`, `CIField16831324`, `CIField22810882`, `CIField51453526`, `CIField22943447`, `CIField57929675`, `CIField00234828`, `CIField80678679`, `CIField24359083`, `CIField05041701`) VALUES
    (1, 1, 1, '2023-07-06 22:57:59', '2024-07-05 22:57:59', 1, 0, '2023-07-06 22:57:59', 1, 'Demo business service', '1', '0', '3', 'Demobusinessservice', '2', 'Service descript example text', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.', '2');

    -- cmdb_ci_fieldslist
    DROP TABLE IF EXISTS `cmdb_ci_fieldslist`;

    CREATE TABLE `cmdb_ci_fieldslist` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `RelatedCITypeID` int NOT NULL,
    `FieldName` varchar(55) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
    `FieldLabel` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
    `FieldType` int NOT NULL,
    `FieldOrder` int NOT NULL,
    `FieldDefaultValue` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `FieldTitle` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `SelectFieldOptions` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `FieldWidth` smallint NOT NULL,
    `DefaultField` tinyint NOT NULL DEFAULT '0',
    `LookupTable` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
    `LookupField` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
    `LookupFieldResultTable` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `LookupFieldResultView` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `RelationShowField` tinyint DEFAULT '0',
    `ImportSourceField` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
    `SyncSourceField` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
    `RelationsLookup` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `Indexed` tinyint NOT NULL DEFAULT '0',
    `HideForms` tinyint DEFAULT '0',
    `HideTables` tinyint DEFAULT '0',
    `Addon` int DEFAULT NULL,
    `Locked` tinyint NOT NULL DEFAULT '0',
    `Required` tinyint NOT NULL DEFAULT '0',
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=196 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

    INSERT INTO `cmdb_ci_fieldslist` (`ID`, `RelatedCITypeID`, `FieldName`, `FieldLabel`, `FieldType`, `FieldOrder`, `FieldDefaultValue`, `FieldTitle`, `SelectFieldOptions`, `FieldWidth`, `DefaultField`, `LookupTable`, `LookupField`, `LookupFieldResultTable`, `LookupFieldResultView`, `RelationShowField`, `ImportSourceField`, `SyncSourceField`, `RelationsLookup`, `Indexed`, `HideForms`, `HideTables`, `Addon`) VALUES
    (1, 1, 'RelatedCompanyID', 'Related Company', 4, 1000, '', NULL, '', 4, 1, 'companies', 'ID', 'Companyname', 'Companyname', 0, NULL, NULL, NULL, 0, 0, 0, NULL),
    (2, 1, 'RelatedUserID', 'Related User', 4, 2000, '', '', '', 4, 1, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, NULL),
    (3, 1, 'StartDate', 'Start Date', 5, 3000, '', NULL, '', 4, 1, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, NULL),
    (4, 1, 'EndDate', 'End Date', 5, 4000, '', NULL, '', 4, 1, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, NULL),
    (5, 1, 'Active', 'Active', 4, 5000, '1', NULL, '<option value=\"0\">Inactive</option>#<option value=\"1\">Active</option>', 4, 1, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, NULL),
    (6, 1, 'Removed', 'Removed', 4, 6000, '0', '', '<option value=\"0\">Active</option>#<option value=\"1\">Removed</option>', 4, 1, '', '', '', '', 0, '', '', '', 0, 0, 0, NULL),
    (7, 1, 'Created', 'Created', 5, 7000, '', NULL, '', 4, 1, '', '', '', '', 0, NULL, NULL, NULL, 0, 0, 0, NULL),
    (8, 1, 'CreatedBy', 'Created By', 4, 8000, '', NULL, '', 4, 1, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, NULL, NULL, NULL, 0, 0, 0, NULL),
    (9, 1, 'CIField16831324', 'Navn', 1, 10, '', 'Navn', '', 4, 0, '', '', '', '', 1, '', '', '', 0, 0, 0, NULL),
    (10, 1, 'CIField22810882', 'Type', 4, 20, '', 'Type', '<option value=\"Customer related Service\">Customer related Service</option>#<option value=\"Internal Service\">Internal Service</option>', 4, 0, '', '', '', '', 0, '', '', '', 0, 0, 0, NULL),
    (11, 1, 'CIField51453526', 'Shared', 4, 30, '0', 'Shared', '<option value=\"Yes\">Yes</option>#<option value=\"No\">No</option>', 2, 0, '', '', '', '', 0, '', '', '', 0, 0, 0, NULL),
    (12, 1, 'CIField22943447', 'SLA', 4, 40, '1', 'Related SLA', '', 2, 0, 'slaagreements', 'ID', 'Name', 'Name', 0, '', '', '', 0, 0, 0, NULL),
    (13, 1, 'CIField57929675', 'Short Name', 1, 70, '', 'Short Name (used for relations to sub CIs)', '', 4, 0, '', '', '', '', 0, '', '', 'cmdb_ci_8c9wm1w8xvy3bwp,CIField21209166#cmdb_ci_ryiioybl4u265vd,CIField24483594', 0, 0, 0, NULL),
    (14, 1, 'CIField00234828', 'Product Owner', 4, 50, '', 'Product Owner', '', 4, 0, 'users', 'ID', 'CONCAT(CIField00234828.Firstname,\" \",CIField00234828.Lastname,\" (\",CIField00234828.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")', 0, '', '', '', 0, 0, 0, NULL),
    (15, 1, 'CIField80678679', 'Description', 2, 80, '', 'Description', '', 12, 0, '', '', '', '', 0, '', '', '', 0, 0, 0, NULL),
    (16, 1, 'CIField24359083', 'Notes', 2, 90, '', 'Notes', '', 12, 0, '', '', '', '', 0, '', '', '', 0, 0, 0, NULL),
    (17, 1, 'CIField05041701', 'Developer Team', 4, 60, '', 'Developer Team', '', 4, 0, 'teams', 'ID', 'Teamname', 'Teamname', 0, '', '', '', 0, 0, 0, NULL);

    -- cmdb_cis
    DROP TABLE IF EXISTS `cmdb_cis`;

    CREATE TABLE `cmdb_cis` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `Name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
    `TableName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
    `Description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci NOT NULL,
    `CreatedBy` mediumint NOT NULL,
    `Created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastEdited` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `LastEditedBy` mediumint NOT NULL,
    `Active` tinyint NOT NULL DEFAULT '1',
    `GroupID` mediumint DEFAULT NULL,
    `ImportSource` tinytext CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    `Synchronization` tinyint NOT NULL DEFAULT '0',
    `SyncTime` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
    `LastSyncronized` datetime DEFAULT NULL,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

    INSERT INTO `cmdb_cis` (`ID`, `Name`, `TableName`, `Description`, `CreatedBy`, `Created`, `LastEdited`, `LastEditedBy`, `Active`, `GroupID`, `ImportSource`, `Synchronization`, `SyncTime`, `LastSyncronized`) VALUES
    (1, 'Forretningsservices', 'cmdb_ci_jsf03ynsyjuvoug', 'Forretningsservices', 2, '2023-02-03 13:38:57', '2023-02-03 13:38:57', 2, 1, 100014, NULL, 0, NULL, NULL);

    -- cmdb_fieldslist_types
    DROP TABLE IF EXISTS `cmdb_fieldslist_types`;

    CREATE TABLE `cmdb_fieldslist_types` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `TypeName` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
    `DBFieldDef` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL,
    `Definition` text CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_danish_ci;

    INSERT INTO `cmdb_fieldslist_types` (`ID`, `TypeName`, `DBFieldDef`, `Definition`) VALUES
    (1, 'Text', 'TINYTEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:>>\r\n</div>\r\n</div>'),
    (2, 'Note', 'LONGTEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\"><label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label>&ensp;<a href=\"javascript:toggleTrumbowygEditor(\'<:fieldid:>\');\"><i class=\"fa-solid fa-pen fa-sm\" title=\"Double click on field to edit\"></i></a><div class=\"resizable_textarea form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"Double click to edit\" rows=\"5\" autocomplete=\"off\" ondblclick=\"createTrumbowygeditor(\'<:fieldid:>\');\" <:required:> <:Locked:>><:fieldvalue:></div>\r\n</div>\r\n</div>'),
    (3, 'Check', 'TINYINT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\"><:label:><:requiredlabel:><:addonBtn:></label>&nbsp;\r\n<input type=\"checkbox\" name=\"<:fieldname:>\" id=\"<:fieldid:>\" <:required:> <:Locked:>>\r\n</input>\r\n</div>\r\n</div>'),
    (4, 'Select', 'TEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label>\r\n<select class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"<:fieldtitle:>\" <:required:> <:Locked:>>\r\n<:selectoptions:>\r\n</select>\r\n</div>\r\n</div>'),
    (5, 'Date', 'VARCHAR(50)', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" autocomplete=\"off\" onclick=\"runDateTimePicker(\'<:fieldid:>\');\"<:Locked:>>\r\n</div>\r\n</div>'),
    (6, 'Number', 'INT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:>>\r\n</div>\r\n</div>'),
    (7, 'Relation', 'MEDIUMINT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\">\r\n</div>\r\n</div>'),
    (8, 'Booelan', 'TINYINT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label>\r\n<select class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" title=\"<:fieldtitle:>\">\r\n<:selectoptions:>\r\n</select>\r\n</div>\r\n</div>'),
    (9, 'Password', 'VARCHAR(255)', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:><a href=\"javascript:generateRandomPassword(\'<:fieldid:>\');\"><i class=\"fa-solid fa-shuffle\"></i></a> <a href=\"javascript:void(0)\" onclick=\"togglePasswordVisibility(\'<:fieldid:>\')\"><i class=\"fa-regular fa-eye\"></i></a></label></p><input type=\"password\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:> ondblclick=\"copytoclipboard(\'<:fieldid:>\');\">\r\n</div>\r\n</div>'),
    (10, 'IP', 'TEXT', '<div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\"><div class=\"input-group input-group-static mb-4\">\r\n<label for=\"<:fieldname:>\" title=\"<:fieldtitle:>\"><:label:><:requiredlabel:><:addonBtn:></label></p><input type=\"text\" class=\"form-control\" id=\"<:fieldid:>\" name=\"<:fieldname:>\" value=\"<:fieldvalue:>\" title=\"<:fieldtitle:>\" autocomplete=\"off\" <:required:> <:Locked:>>\r\n</div>\r\n</div>');

    -- cmdb_ci_default_fields
    DROP TABLE IF EXISTS `cmdb_ci_default_fields`;

    CREATE TABLE `cmdb_ci_default_fields` (
    `ID` int NOT NULL AUTO_INCREMENT,
    `FieldName` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `DBFieldType` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `FieldType` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `Label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `RelationShowField` tinyint DEFAULT '0',
    `FieldOrder` smallint DEFAULT NULL,
    `FieldDefaultValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `SelectFieldOptions` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `LookupTable` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `LookupField` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci DEFAULT NULL,
    `LookupFieldResultTable` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    `LookupFieldResultView` text CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

    INSERT INTO `cmdb_ci_default_fields` (`ID`, `FieldName`, `DBFieldType`, `FieldType`, `Label`, `RelationShowField`, `FieldOrder`, `FieldDefaultValue`, `SelectFieldOptions`, `LookupTable`, `LookupField`, `LookupFieldResultTable`, `LookupFieldResultView`) VALUES
    (1, 'Name', 'MEDIUMTEXT', '1', 'Name', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL),
    (2, 'RelatedCompanyID', 'MEDIUMINT', '4', 'Related Company', 0, 2, NULL, NULL, 'companies', 'ID', 'Companyname', 'Companyname'),
    (3, 'RelatedUserID', 'MEDIUMINT', '4', 'Related User', 0, 3, NULL, NULL, 'users', 'ID', 'CONCAT(RelatedUserID.Firstname,\" \",RelatedUserID.Lastname,\" (\",RelatedUserID.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")'),
    (4, 'StartDate', 'DATETIME', '5', 'Start Date', 0, 4, NULL, NULL, NULL, NULL, NULL, NULL),
    (5, 'EndDate', 'DATETIME', '5', 'End Date', 0, 5, NULL, NULL, NULL, NULL, NULL, NULL),
    (6, 'Active', 'BOOLEAN', '4', 'Active', 0, 6, '1', '<option value=\"0\">Inactive</option>\r\n<option value=\"1\">Active</option>', NULL, NULL, NULL, NULL),
    (7, 'Removed', 'BOOLEAN', '4', 'Removed', 0, 7, '0', '<option value=\"0\">Active</option>\r\n<option value=\"1\">Removed</option>', NULL, NULL, NULL, NULL),
    (8, 'Created', 'DATETIME', '5', 'Created', 0, 8, NULL, NULL, NULL, NULL, NULL, NULL),
    (9, 'CreatedBy', 'MEDIUMINT', '4', 'Created By', 0, 9, NULL, NULL, 'users', 'ID', 'CONCAT(CreatedBy.Firstname,\" \",CreatedBy.Lastname,\" (\",CreatedBy.Username,\")\")', 'CONCAT(Firstname,\" \",Lastname,\" (\",Username,\")\")');

    -- cmdb_ci_relations
    DROP TABLE IF EXISTS `cmdb_ci_relations`;

    CREATE TABLE `cmdb_ci_relations` (
    `ID` bigint NOT NULL AUTO_INCREMENT,
    `CITable1` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `CITable2` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_danish_ci NOT NULL,
    `CI1ID` mediumint NOT NULL,
    `CI2ID` mediumint NOT NULL,
    PRIMARY KEY (`ID`)
    ) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;

    TRUNCATE TABLE files_cis;
    ";

    $files = glob('./uploads/files_cis/*'); // get all file names
    foreach($files as $file){ // iterate files
    if(is_file($file))
        unlink($file); // delete file
    }

    if ($conn->multi_query($sql)) {
        return "Completed";
    } else {
        return "error";
    }
}

function updateUserAPI($token, $userId, $userData) {
    global $functions;

    $SystemURL = $functions->getSettingValue(17);
    $url = "$SystemURL/api/index.php/$token/users/$userId";

    $jsonData = json_encode($userData);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode === 200) {
        return true; // Update successful
    } else {
        return false; // Update failed
    }
}

function getProfilePopover($UserSessionID,$UsersID){
    global $functions;

    $FullName = $functions->getUserFullName($UsersID);
    $FullNameWithUsername = $functions->getUserFullNameWithUsername($UsersID);
    $ProfilePicture = getUserProfilePicture($UsersID);
    $Email = getUserEmailFromID($UsersID);
    
    /*$ProfileString = "<div onclick=\"hidePopUp(this);\" data-bs-toggle=\"popover\" data-bs-html=\"true\" data-bs-trigger=\"hover focus click\" data-bs-content=\""
    . "<p class='text-center text-wrap'><b>$FullName</b><br>" . $Email . "<br><br>"
    . "<p><img class='rounded-circle img-fluid' style='width: 150px;' src='./uploads/images/profilepictures/" . $ProfilePicture . "'></p>"
    . "<p class='text-sm text-secondary mb-0 text-wrap'></p>"
    . "\" data-bs-original-title><a href='javascript:runModalViewUnit(\"User\",$UsersID);'>$FullName</a></div>";
    */
    $ProfileString = "<a href='javascript:runModalViewUnit(\"User\",$UsersID);'>$FullName</a>";

    return $ProfileString;

}

function getCMDBTypes()
{
    global $functions;

    $sql = "
        SELECT cmdb_cis.ID, cmdb_cis.Name, cmdb_cis.TableName, cmdb_ci_fieldslist.FieldName
        FROM cmdb_cis
        LEFT JOIN cmdb_ci_fieldslist
            ON cmdb_ci_fieldslist.RelatedCITypeID = cmdb_cis.ID AND cmdb_ci_fieldslist.RelationShowField = 1
        WHERE cmdb_cis.Active = 1
    ";

    // No tables to lock since this is a read-only query
    try {
        $result = $functions->selectQuery($sql,[]);

        if ($result && count($result) > 0) {
            return array_map(function ($row) {
                return [
                    "CMDBTypeID" => $row["ID"],
                    "Name" => $row["Name"],
                    "TableName" => $row["TableName"],
                    "FieldName" => $row["FieldName"]
                ];
            }, $result);
        }
        return []; // Return empty array if no rows are found
    } catch (Exception $e) {
        $functions->errorlog("Error fetching CMDB types: " . $e->getMessage(), __FUNCTION__);
        return [];
    }
}

function getExpiredForCMDBType($CMDBTypeID, $Name, $TableName, $FieldName, $UserSessionID)
{
    global $functions;

    // Check if the required columns exist
    $requiredColumns = ['RelatedUserID', 'Active', 'EndDate'];
    foreach ($requiredColumns as $column) {
        if (!columnExists($TableName, $column)) {
            $functions->errorlog("Column '$column' does not exist in table '$TableName'", __FUNCTION__);
            return [];
        }
    }

    $sql = "
        SELECT ID, $FieldName, EndDate
        FROM $TableName
        WHERE RelatedUserID = ?
          AND Active = 1
          AND EndDate <= DATE_ADD(NOW(), INTERVAL 10 DAY)
    ";

    $params = [$UserSessionID];

    // No table locks for read-only operations
    try {
        $result = $functions->selectQuery($sql, $params);

        if ($result && count($result) > 0) {
            return array_map(function ($row) use ($CMDBTypeID, $Name, $FieldName) {
                return [
                    "TypeName" => $Name,
                    "CIName" => $row[$FieldName],
                    "Link" => "javascript:runModalViewCI('{$row['ID']}','$CMDBTypeID','1');",
                    "EndDate" => $row["EndDate"]
                ];
            }, $result);
        }
        return []; // Return empty array if no rows match
    } catch (Exception $e) {
        $functions->errorlog("Error fetching expired CMDB items for type $CMDBTypeID: " . $e->getMessage(), __FUNCTION__);
        return [];
    }
}

function columnExists($table, $column)
{
    global $conn;
    global $functions;

    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return mysqli_num_rows($result) > 0;
}

function duplicateITSM($ITSMTableName, $ITSMTypeID)
{
    global $conn;
    global $functions;

    // Generate a unique name for the new table

    try {
        $GeneratedRandomName = $functions->generateRandomString(15);
    } catch (Exception $e) {
        // Handle the exception here
        $functions->errorlog($e->getMessage(), "duplicateITSM");
    }

    $DBTableNew = "itsm_$GeneratedRandomName";

    try {
        $exists = checkIfFormsTableNameExists($DBTableNew);
    } catch (Exception $e) {
        // Handle the exception here
        $functions->errorlog($e->getMessage(), "duplicateITSM");
    }
    
    try {
        $exists = checkIfFormsTableNameExists($DBTableNew);
        $i = 0;
        while (!empty($exists)) {
            $DBTableNew = "itsm_" . $GeneratedRandomName . $i;
            $exists = checkIfFormsTableNameExists($DBTableNew);
            $i = $i + 1;
        }
    } catch (Exception $e) {
        // Handle the exception here
        $functions->errorlog($e->getMessage(), "duplicateITSM");
    }

    try {
        if ($conn->connect_errno) {
            $functions->errorlog("Connection error: " . $conn->connect_error, "duplicateITSM");
        }
        $conn->autocommit(FALSE);
        if (!$conn->autocommit(FALSE)) {
            $functions->errorlog("Failed to set autocommit to false: " . $conn->error,"duplicateITSM");
        }
        if (!$conn->begin_transaction()) {
            $functions->errorlog("Failed to begin transaction: " . $conn->error,"duplicateITSM");
        } else {
        }
    } catch (mysqli_sql_exception $e) {
        $functions->errorlog($e->getMessage(), "duplicateITSM");
    } catch (Exception $e) {
        $functions->errorlog($e->getMessage(), "duplicateITSM");
    }

    try {
        // Create ITSM module
        $sql = "INSERT INTO itsm_modules (Name, ShortElementName, TableName, Description, Type, CreatedBy, Created, LastEditedBy, LastEdited, Active, RoleID, TypeIcon, MenuPage, DoneStatus)
                SELECT Name, ShortElementName, '$DBTableNew' AS TableName, Description, Type, CreatedBy, Created, LastEditedBy, LastEdited, Active, RoleID, TypeIcon, MenuPage, DoneStatus
                FROM itsm_modules
                WHERE ID = ?;";

        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            $functions->errorlog(mysqli_error($conn), "duplicateITSM");
            throw new Exception(mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $ITSMTypeID);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_error($stmt)) {
            $functions->errorlog(mysqli_stmt_error($stmt), "duplicateITSM");
            throw new Exception(mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        $last_id = mysqli_insert_id($conn);
        $Comment = "itsmid=$last_id";

        // Create Fields
        $sql = "INSERT INTO itsm_fieldslist (RelatedTypeID, FieldName, FieldLabel, FieldType, FieldOrder, FieldDefaultValue, FieldTitle, SelectFieldOptions, FieldWidth, DefaultField, LookupTable, LookupField, LookupFieldResultTable, LookupFieldResultView, RelationShowField, ImportSourceField, SyncSourceField, RelationsLookup, Indexed, HideTables, HideForms, Required, LockedView, LockedCreate, Addon, AddEmpty, FullHeight, UserFullName, ResultFields, RightColumn, LabelType)
                SELECT $last_id, FieldName, FieldLabel, FieldType, FieldOrder, FieldDefaultValue, FieldTitle, SelectFieldOptions, FieldWidth, DefaultField, LookupTable, LookupField, LookupFieldResultTable, LookupFieldResultView, RelationShowField, ImportSourceField, SyncSourceField, RelationsLookup, Indexed, HideTables, HideForms, Required, LockedView, LockedCreate, Addon, AddEmpty, FullHeight, UserFullName, ResultFields, RightColumn, LabelType
                FROM itsm_fieldslist WHERE RelatedTypeID = ?;";

        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            $functions->errorlog(mysqli_error($conn), "duplicateITSM");
            throw new Exception(mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'i', $ITSMTypeID);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_error($stmt)) {
            $functions->errorlog(mysqli_stmt_error($stmt), "duplicateITSM");
            throw new Exception(mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);

        // Create StatusCodes
        $sql = "INSERT INTO itsm_statuscodes (ModuleID, StatusCode, StatusName, SLA)
                SELECT ?, StatusCode, StatusName, SLA
                FROM itsm_statuscodes WHERE ModuleID = ?;";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            $functions->errorlog(mysqli_error($conn), "duplicateITSM");
            throw new Exception(mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'ii', $last_id, $ITSMTypeID);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_error($stmt)) {
            $functions->errorlog(mysqli_stmt_error($stmt), "duplicateITSM");
            throw new Exception(mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);

        // Create itsm_sla_matrix
        $sql = "INSERT INTO itsm_sla_matrix (RelatedModuleID, Status, SLA, P1, P2, P3, P4)
                SELECT ?, Status, SLA, P1, P2, P3, P4
                FROM itsm_sla_matrix WHERE RelatedModuleID = ?;";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            $functions->errorlog(mysqli_error($conn), "duplicateITSM");
            throw new Exception(mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'ii', $last_id, $ITSMTypeID);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_error($stmt)) {
            $functions->errorlog(mysqli_stmt_error($stmt), "duplicateITSM");
            throw new Exception(mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);

        // Duplicate the forms field table
        createDuplicateFormsFieldTable($ITSMTableName, $DBTableNew);

        // Update table comment
        $sql = "ALTER TABLE $DBTableNew COMMENT = '$Comment';";
        if (!mysqli_query($conn, $sql)) {
            $functions->errorlog(mysqli_error($conn), "duplicateITSM");
            throw new Exception(mysqli_error($conn));
        }

        // Commit the transaction
        mysqli_commit($conn);
    } catch (Exception $e) {
        // Rollback the transaction on error
        mysqli_rollback($conn);
        $functions->errorlog("Error: " . $e->getMessage(), "duplicateITSM");
        return;
    }
}

function incrementExistingFieldOrder($ITSMTypeID, $FieldOrder)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE itsm_fieldslist SET FieldOrder = FieldOrder + 1 WHERE RelatedTypeID = ? AND FieldOrder >= ?";

    // Parameters for the query
    $params = [$ITSMTypeID, $FieldOrder];

    // Tables to lock
    $tables = ['itsm_fieldslist'];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to increment field order for ITSMTypeID: $ITSMTypeID", "incrementExistingFieldOrder");
        return false;
    }
}

function incrementExistingCIFieldOrder($CITypeID, $FieldOrder)
{
    global $functions;

    // SQL query with placeholders
    $sql = "UPDATE cmdb_ci_fieldslist SET FieldOrder = FieldOrder + 1 WHERE RelatedCITypeID = ? AND FieldOrder >= ?";

    // Parameters for the query
    $params = [$CITypeID, $FieldOrder];

    // Tables to lock
    $tables = ['cmdb_ci_fieldslist'];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] >= 0) {
        return true;
    } else {
        $functions->errorlog("Failed to increment CI field order for CITypeID: $CITypeID", "incrementExistingCIFieldOrder");
        return false;
    }
}

function logActivity($ElementID, $ModuleID, $Headline, $Text, $Url)
{
    global $functions;

    $UserID = $_SESSION['id'] ?? 1; // Default to 1 if UserID is not set

    // SQL query with placeholders
    $sql = "INSERT INTO activitystream (ElementID, ModuleID, UserID, Headline, Text, Url) VALUES (?, ?, ?, ?, ?, ?)";

    // Parameters for the query
    $params = [$ElementID, $ModuleID, $UserID, $Headline, $Text, $Url];

    // Tables to lock
    $tables = ['activitystream'];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    if ($result['LastID'] > 0) {
        return true;
    } else {
        $functions->errorlog("Failed to log activity for ElementID: $ElementID, ModuleID: $ModuleID", "logActivity");
        return false;
    }
}

function getAllTimeRegistrations($week)
{
    global $functions;

    $LeaderOfArray = $_SESSION['LeaderOfArray'] ?? [];
    $UserSessionID = $_SESSION['id'] ?? null;

    if (!$UserSessionID) {
        $functions->errorlog("User session ID is missing.", __FUNCTION__);
        return [];
    }

    // Add current user to the leader array
    array_push($LeaderOfArray, $UserSessionID);
    $LeaderOf = implode(",", array_map('intval', $LeaderOfArray)); // Ensure IDs are integers

    // Base query
    $sql = "
        SELECT
            users.ID AS UserID,
            CONCAT(users.Firstname, ' ', users.Lastname, ' (', users.UserName, ')') AS FullName,
            users.UserName";

    // Add day-wise columns dynamically
    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    foreach ($daysOfWeek as $day) {
        $sql .= ",
            FORMAT(SUM(
                CASE WHEN DAYNAME(time_registrations.DateWorked) = '$day'
                THEN time_registrations.TimeRegistered / 60
                ELSE 0
            END), 2) AS $day";
    }

    // Append FROM, JOIN, and WHERE clauses
    $sql .= "
        FROM time_registrations
        JOIN users ON time_registrations.RelatedUserID = users.ID
        WHERE YEARWEEK(time_registrations.DateWorked, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL :week WEEK), 1)
          AND users.ID IN ($LeaderOf)
        GROUP BY users.ID, FullName
        ORDER BY users.Firstname";

    // Prepare parameters for the query
    $params = ['week' => $week];

    try {
        // Execute the query
        $result = $functions->selectQuery($sql, $params);

        // Process the result
        $resultArray = [];
        foreach ($result as $row) {
            $UserID = $row['UserID'];
            $FullName = $row['FullName'];
            $UserName = $row['UserName'];
            $ProfilePopOver = getProfilePopover($UserSessionID, $UserID);

            // Add user details and day-wise data
            $resultArray[] = array_merge(
                [
                    'UserID' => $UserID,
                    'UserName' => $UserName,
                    'ProfilePopOver' => $ProfilePopOver
                ],
                array_slice($row, 3) // Include dynamically generated day-wise columns
            );
        }

        return $resultArray;
    } catch (Exception $e) {
        $functions->errorlog("Error fetching time registrations: " . $e->getMessage(), __FUNCTION__);
        return []; // Return an empty array on error
    }
}

// Does not work properly
function insertRandomRows($table, $x)
{
    global $conn;
    global $functions;

    // Check connection
    if ($conn->connect_errno) {
        echo "Failed to connect to MySQL: " . $conn->connect_error;
        return;
    }

    // Retrieve table structure
    $result = $conn->query("DESCRIBE $table");
    $fields = array();
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] !== 'ID') {
            $fields[] = $row['Field'];
        }
    }

    // Get the start and end dates of the current month
    $currentMonthStart = date('Y-m-01');
    $currentMonthEnd = date('Y-m-t');

    for ($i = 1; $i <= $x; $i++) {
        $values = array();
        foreach ($fields as $field) {
            // Generate random values based on field type
            $typeResult = $conn->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table' AND COLUMN_NAME = '$field'");
            $typeRow = $typeResult->fetch_assoc();
            $dataType = $typeRow['DATA_TYPE'];

            if ($field === 'UserID') {
                $validUserIDs = array();
                $userIDsResult = $conn->query("SELECT ID FROM users WHERE Active = 1");
                while ($userIDRow = $userIDsResult->fetch_assoc()) {
                    $validUserIDs[] = $userIDRow['ID'];
                }
                $values[] = $validUserIDs[array_rand($validUserIDs)];

            } else {
                // Check if the field has a foreign key constraint
                $constraintResult = $conn->query("SELECT REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME = '$table' AND COLUMN_NAME = '$field' AND REFERENCED_TABLE_NAME IS NOT NULL");
                if ($constraintResult->num_rows > 0) {
                    $constraintRow = $constraintResult->fetch_assoc();
                    $referencedTable = $constraintRow['REFERENCED_TABLE_NAME'];
                    $referencedColumn = $constraintRow['REFERENCED_COLUMN_NAME'];

                    $validValues = array();
                    $tempquery = "SELECT $referencedColumn FROM $referencedTable";
                    $valuesResult = $conn->query("SELECT $referencedColumn FROM $referencedTable");

                    while ($valueRow = $valuesResult->fetch_assoc()) {
                        $validValues[] = $valueRow[$referencedColumn];
                    }

                    $values[] = $validValues[array_rand($validValues)];
                } else {
                    switch ($dataType) {
                        case 'int':
                            $values[] = rand(1, 100);
                            break;
                        case 'tinyint':
                            $values[] = 1;
                            break;
                        case 'smallint':
                        case 'mediumint':
                            $values[] = rand(1, 100);
                            break;
                        case 'bigint':
                            $values[] = rand(1, 100);
                            break;
                        case 'float':
                        case 'double':
                        case 'decimal':
                            $values[] = rand(1, 100) + (rand(0, 99) / 100);
                            break;
                        case 'date':
                            $values[] = date('Y-m-d', strtotime($currentMonthStart . "+ " . rand(0, 30) . " days"));
                            break;
                        case 'datetime':
                        case 'timestamp':
                            $values[] = date('Y-m-d H:i:s', strtotime($currentMonthStart . "+ " . rand(0, 30) . " days"));
                            break;
                        case 'varchar':
                        case 'text':
                            $values[] = $table . "_" . $functions->generateRandomString(6);
                            break;
                        default:
                            $values[] = null;
                    }
                }
            }
        }

        $query = "INSERT INTO $table (" . implode(", ", $fields) . ") VALUES ('" . implode("', '", array_pad($values, count($fields), '')) . "')";
        if (!$conn->query($query)) {
            echo "Error inserting row " . $i . ": " . $conn->error . "<br>";
        }
    }

    $conn->close();
}

function getCIFieldType($CITypeID, $Field)
{
    global $conn;
    global $functions;

    // Check if the task status is already 4 (skipping the update)
    $sql = "SELECT FieldType
            FROM cmdb_ci_fieldslist
            WHERE RelatedCITypeID = $CITypeID AND FieldName = '$Field';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["FieldType"];
    }

    mysqli_free_result($result);

    return $Value;
}

function normalizeValue($value)
{
    return ($value === NULL || $value === '') ? NULL : $value;
}

function getFormFieldType($formID, $Field)
{
    global $conn;
    global $functions;

    // Check if the task status is already 4 (skipping the update)
    $sql = "SELECT FieldType
            FROM forms_fieldslist
            WHERE RelatedFormID = $formID AND FieldName = '$Field';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Value = $row["FieldType"];
    }

    mysqli_free_result($result);

    return $Value;
}

function getAllModuleRoles()
{
    global $conn;
    global $functions;

    // Initialize an empty array to store the GroupID values
    $ModuleArray = array();

    $sql = "SELECT ID AS ModuleID, RoleID, TableName, ShortElementName
            FROM itsm_modules
            WHERE itsm_modules.Active = 1;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    // Loop through each row in the result set
    while ($row = mysqli_fetch_array($result)) {
        // Append the GroupID to the array
        $ModuleArray[] = array("ModuleID" => $row["ModuleID"], "RoleID" => $row["RoleID"], "TableName" => $row["TableName"], "ShortElementName" => $row["ShortElementName"]);
    }

    // Free the result set
    mysqli_free_result($result);

    // Return the array of GroupID values
    return $ModuleArray;
}

function resetCISortOrder($CITypeID)
{
    global $conn;
    global $functions;

    // The SQL queries
    $sql = "SET @new_order = 0;";
    $sql .= "UPDATE cmdb_ci_fieldslist SET FieldOrder = (@new_order := @new_order + 1) WHERE RelatedCITypeID = $CITypeID ORDER BY FieldOrder ASC;";

    // Execute multi queries
    if ($conn->multi_query($sql)) {
        do {
            /* store first result set */
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
    }

    // Close the connection
    $conn->close();

    return true;
}

function resetITSMSortOrder($ITSMID)
{
    global $conn;
    global $functions;

    // The SQL queries
    $sql = "SET @new_order = 0;";
    $sql .= "UPDATE itsm_fieldslist SET FieldOrder = (@new_order := @new_order + 1) WHERE RelatedTypeID = $ITSMID ORDER BY FieldOrder ASC;";

    // Execute multi queries
    if ($conn->multi_query($sql)) {
        do {
            /* store first result set */
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
    }

    // Close the connection
    $conn->close();

    return true;
}

function resetFormsSortOrder($FormID)
{
    global $conn;
    global $functions;

    // The SQL queries
    $sql = "SET @new_order = 0;";
    $sql .= "UPDATE forms_fieldslist SET FieldOrder = (@new_order := @new_order + 1) WHERE RelatedFormID = $FormID ORDER BY FieldOrder ASC;";

    // Execute multi queries
    if ($conn->multi_query($sql)) {
        do {
            /* store first result set */
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
    }

    // Close the connection
    $conn->close();

    return true;
}

function restoreBackup($backupToRestore)
{
    global $conn;
    global $functions;

    $sql = "SELECT BackupFile FROM db_backups WHERE ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $backupToRestore);

    if ($stmt->execute()) {
        $stmtResult = $stmt->get_result();  // Rename to $stmtResult
        while ($row = $stmtResult->fetch_assoc()) {  // Use $stmtResult here
            $BackupFile = $row["BackupFile"];
            $sqlToRestore = file_get_contents($BackupFile);

            // Check if the SQL file is empty
            if (empty($sqlToRestore)) {
                return "No file found";
            }

            if (mysqli_multi_query($conn, $sqlToRestore)) {
                do {
                    $multiQueryResult = mysqli_store_result($conn);  // Use a different variable here
                    if ($multiQueryResult) {
                        mysqli_free_result($multiQueryResult);  // Use the new variable
                    }
                } while (mysqli_more_results($conn) && mysqli_next_result($conn));
            } else {
                // Handle multi-query error
                return "Multi-query failed: " . mysqli_error($conn);
            }

        }

        $stmt->close();
        if (isset($stmtResult) && $stmtResult !== false) {
            mysqli_free_result($stmtResult);
        }
    } else {
        // Handle error - statement execution failed
        die("Statement failed: " . $stmt->error);
    }

    // Free the multiQueryResult set, if any
    if (isset($multiQueryResult) && $multiQueryResult !== false) {
        mysqli_free_result($multiQueryResult);
    }

    return true;
}

function deleteBackup($backupToDelete)
{
    global $conn;
    global $functions;

    // First, fetch the file path
    $sql = "SELECT BackupFile FROM db_backups WHERE ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $backupToDelete);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $BackupFile = $row["BackupFile"];

            // Check if the file exists
            if (!file_exists($BackupFile)) {
                return "File not found";
            }

            // Delete the file
            if (!unlink($BackupFile)) {
                return "Failed to delete file";
            }
        }
        $stmt->close();
    } else {
        // Handle error - statement execution failed
        die("Statement failed: " . $stmt->error);
    }

    // Delete the record from the database
    $deleteSql = "DELETE FROM db_backups WHERE ID = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param('i', $backupToDelete);

    if (!$deleteStmt->execute()) {
        // Handle error - statement execution failed
        return "Statement failed: " . $deleteStmt->error;
    }
    $deleteStmt->close();

    // Free the result set, if any
    if (isset($result) && $result !== false) {
        mysqli_free_result($result);
    }

    return true;
}

function getCIRelationTableName($RelationID, $Table)
{
    global $conn;
    global $functions;

    // First, fetch the file path
    $sql = "SELECT $Table FROM cmdb_ci_relations WHERE ID = ?";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        // Handle error - statement preparation failed
        $functions->errorlog("Statement preparation failed: " . $conn->error, "getCIRelationParentTable");
        return false;
    }

    $stmt->bind_param('i', $RelationID);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result === false) {
            // Handle error - execution failed
            $functions->errorlog("Execution failed: " . $stmt->error, "getCIRelationParentTable");
            $stmt->close();
            return false;
        }
        if ($row = $result->fetch_assoc()) {
            $Table = $row[$Table];
        }
        $stmt->close();
    } else {
        // Handle error - statement execution failed
        $functions->errorlog("Statement failed: " . $stmt->error, "getCIRelationParentTable");
    }

    // Free the result set, if any
    if (isset($result) && $result !== false) {
        mysqli_free_result($result);
    }

    return $Table;
}

function getCIRelationCIID($RelationID, $ID)
{
    global $conn;
    global $functions;

    // First, fetch the file path
    $sql = "SELECT $ID FROM cmdb_ci_relations WHERE ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $RelationID);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $CIID = $row["$ID"];
        }
        $stmt->close();
    } else {
        // Handle error - statement execution failed
        $functions->errorlog("Statement failed: " . $stmt->error, "getCIRelationParentTable");
    }

    // Free the result set, if any
    if (isset($result) && $result !== false) {
        mysqli_free_result($result);
    }

    return $CIID;
}

function createITSMEntry($UserSessionID, $ITSMForm, $FormID, $RequestForm, $ITSMTableName, $ElementCreatedDateVal, $ITSMTypeID, $ModuleType, $SLASupported, $ITSMTypeName1, $ITSMID2, $ITSMTypeID2)
{
    global $conn;
    global $functions;

    $createdByFound = false; // Initialize flag to detect 'CreatedBy' field

    if ($ITSMID2 !== "") {
        $ITSMTableName2 = $functions->getITSMTableName($ITSMTypeID2);
        $ITSMTypeName2 = $functions->getITSMTypeName($ITSMTypeID2);
    }

    // Check if 'CreatedBy' is present in the form data
    foreach ($ITSMForm as $item) {
        if ($item['name'] == 'CreateFormCreatedBy') {
            $createdByFound = true;
            break;
        }
    }

    // If 'CreatedBy' is not found, add it with a default value
    if (!$createdByFound) {
        $ITSMForm[] = ['name' => 'CreateFormCreatedBy', 'value' => $UserSessionID];
    }

    // Add 'Created' field if not present
    if (!$createdByFound) {
        $ITSMForm[] = ['name' => 'CreateFormCreated', 'value' => "Now()"];
    }

    // Initialize arrays for columns and values
    $columns = [];
    $placeholders = [];
    $params = [];

    foreach ($ITSMForm as $value) {
        $Field = trim(str_replace("CreateForm", "", $value['name']));
        $Value = $value['value'];
        $FieldType = $functions->getITSMFieldTypeID($ITSMTypeID, $Field);

        // Apply transformations based on field name or type
        switch ($Field) {
            case "Created":
                $Value = date("Y-m-d H:i:s");
                break;
            case "CreatedBy":
                $Value = $UserSessionID;
                break;
            case "Customer":
                $CustomerID = $Value;
                break;
            case "Priority":
                $Priority = $Value;
                break;
            case "Subject":
                $Subject = $Value;
                if (strpos($Subject, 'We received your email but...') !== false) {
                    return null;
                }
                break;
            case "Active":
                $Value = 1;
                break;
            case "LastUpdatedBy":
                $Value = $UserSessionID;
                break;
            case "LastUpdated":
                $Value = date("Y-m-d H:i:s");
                break;
            default:
                if ($FieldType == "5" && $Value !== "") {
                    $Value = date("Y-m-d H:i:s", strtotime($Value));
                }
        }

        // Add field and value to the arrays
        $columns[] = $Field;
        $placeholders[] = "?";
        $params[] = $Value === "" ? null : $Value;
    }

    // Construct the SQL query
    $sql = "INSERT INTO $ITSMTableName (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";

    // Tables to lock
    $tables = [$ITSMTableName];

    // Execute the query using dmlQuery
    $result = $functions->dmlQuery($sql, $params, $tables);

    // Get the last inserted ID
    if ($result['LastID'] > 0) {
        $ITSMID = $result['LastID'];
    } else {
        $functions->errorlog("Failed to create ITSM entry in table $ITSMTableName", "createITSMEntry");
        return null;
    }

    $LogActionText = "Created";
    createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);

    // Determine whether to email the customer
    $DontEmailCustomer = ($ModuleType == "4" || $UserSessionID == "0");

    // Handle cases where CustomerID is empty
    if (empty($CustomerID) && !empty($ResponsibleID)) {
        $CustomerID = $ResponsibleID;
        updateITSMFieldValue($ITSMID, $CustomerID, "Customer", $ITSMTypeID);
        $DontEmailCustomer = true;
    }

    // We got ourselves a create form - let's create relations
    if ($ITSMID2 !== "") {
        global $functions;

        // SQL query to insert the relation
        $sql = "INSERT INTO itsm_relations(Table1, Table2, ID1, ID2) VALUES (?, ?, ?, ?)";

        // Parameters for the query
        $params = [$ITSMTableName, $ITSMTableName2, $ITSMID, $ITSMID2];

        // Tables to lock
        $tables = ["itsm_relations"];

        // Execute the query using dmlQuery
        $result = $functions->dmlQuery($sql, $params, $tables);

        if ($result['LastID'] > 0) {
            // Log entries for the created relations
            $LogActionText1 = "Relation created to $ITSMTypeName2 $ITSMID2";
            createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText1);

            $LogActionText2 = "Relation created to $ITSMTypeName1 $ITSMID";
            createITSMLogEntry($ITSMID2, $ITSMTypeID2, $UserSessionID, $LogActionText2);
        } else {
            $functions->errorlog("Failed to create ITSM relation between $ITSMTableName ($ITSMID) and $ITSMTableName2 ($ITSMID2)", "createRelations");
        }
    }

    // Set CreatedBy
    updateITSMFieldValue($ITSMID, $UserSessionID, "CreatedBy", $ITSMTypeID);
    // Set CompanyID on newly created ITSM element
    $CompanyID = getUserRelatedCompanyID($CustomerID);
    updateITSMFieldValue($ITSMID, $CompanyID, "RelatedCompanyID", $ITSMTypeID);

    $LogActionText = "Company ID set to $CompanyID";
    createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
    $SLA = "";
    
    if ($SLASupported == 1) {
        // Set SLA - if no BusinessService then set to Company SLA
        if (empty($BusinessServiceID)) {
            $SLA = getRelatedSLAID($CompanyID);
            updateITSMFieldValue($ITSMID, $SLA, "SLA", $ITSMTypeID);
        } else {
            $SLA = getSLAFromBS($BusinessServiceID);
            updateITSMFieldValue($ITSMID, $SLA, "SLA", $ITSMTypeID);
        }
        $LogActionText = "SLA set to $SLA";
        createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
    }

    if ($ITSMTypeID == "2") {
        global $functions;

        // Get related table name and workflow ID
        $FormsTableName = getTableNameFromFormID($FormID);
        $WorkFlowID = getRelatedWorkFlowID($FormID);

        // Handle Workflow Creation
        if (!empty($WorkFlowID)) {
            $RedirectPage = "javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');";
            $WorkFlowID = createWorkFlow($ITSMID, $WorkFlowID, $UserSessionID, $RedirectPage, $ITSMTypeID);

            $LogActionText = "Added Workflow: $WorkFlowID";
            createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
        }

        // Insert or update records in the forms table
        $RecordID = null;
        foreach ($RequestForm as $key => $value) {
            $fieldname = $value['name'];
            $fieldvalue = $value['value'] ?? "";

            if (is_null($RecordID)) {
                // First field: Insert a new record and get the ID
                $sql = "INSERT INTO $FormsTableName ($fieldname) VALUES (?)";
                $params = [$fieldvalue];
                $tables = [$FormsTableName];
                $result = $functions->dmlQuery($sql, $params, $tables);
                $RecordID = $result['LastID'];
            } else {
                // Subsequent fields: Update the existing record
                $sql = "UPDATE $FormsTableName SET $fieldname = ? WHERE ID = ?";
                $params = [$fieldvalue, $RecordID];
                $tables = [$FormsTableName];
                $functions->dmlQuery($sql, $params, $tables);
            }
        }

        if ($RecordID) {
            // Set RelatedRequestID on forms table
            $sql = "UPDATE $FormsTableName SET RelatedRequestID = ? WHERE ID = ?";
            $params = [$ITSMID, $RecordID];
            $tables = [$FormsTableName];
            $functions->dmlQuery($sql, $params, $tables);

            // Set FormID on request table
            $sql = "UPDATE $ITSMTableName SET RelatedFormID = ? WHERE ID = ?";
            $params = [$FormID, $ITSMID];
            $tables = [$ITSMTableName];
            $functions->dmlQuery($sql, $params, $tables);
        }
    }

    if ($SLASupported == 1) {
        if ($Priority !== "" && $SLA !== "") {
            //Create ITSM SLA Reaction times
            //Get SLA reactiontimes for the SLA ID according to the priority selected
            $ReactionTimes = [];
            $ReactionTimes = getSLAStatusCores($ITSMTypeID, $SLA, $Priority);

            foreach ($ReactionTimes as $value) {
                $Status = $value["Status"];
                $Minutes = $value["Minutes"];
                $DateViolated = getDateTimeViolated($ElementCreatedDateVal, $Minutes);
                createTimelineSLAViolationDates($ITSMID, $ITSMTypeID, $Status, $DateViolated);
            }
        }
    }

    if (!empty($CustomerID) && $DontEmailCustomer == false && $ModuleType == "1") {

        $TemplateID = "3";
        $CustomerEmail = getUserEmailFromID($CustomerID);
        $CustomerFullName = $functions->getUserFullName($CustomerID);

        if (strpos($Subject, '[Practicle Problem report]') !== false) {
            sendITSMMailTemplate($ITSMTypeID, $ITSMID, $CustomerID, "", $TemplateID);
            $LogActionText = "Customer: $CustomerFullName emailed on: $CustomerEmail";
            createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
        }
    }
    if ($ModuleType == "4" || $ModuleType == "3") {
    } else {
        if (!empty($ResponsibleID)) {

            // Add to users tasklist
            $RedirectPage = "javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');";
            //addtotaskslist($ITSMID, $ResponsibleID, $ITSMTypeID, $RedirectPage);
            $TemplateID = "4";

            $ResponsibleEmail = getUserEmailFromID($ResponsibleID);
            $ResponsibleFullName = $functions->getUserFullName($ResponsibleID);
            
            //sendITSMMailTemplate($ITSMTypeID, $ITSMID, $ResponsibleID, "", $TemplateID);

            $LogActionText = "Responsible: $ResponsibleFullName emailed on: $ResponsibleEmail";
            //createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
        }
    }
    $ActivityText = "<b>" . $functions->translate("Subject") . "</b>" . "<br><br>" . $Subject;
    $Headline = $functions->translate("Created") . " " . strtolower($ITSMTypeName1) . " " . $ITSMID;
    logActivity($ITSMID, $ITSMTypeID, $Headline, $ActivityText, "javascript:javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');");

    createITSMFilesFromTemp($ITSMID, $ITSMTypeID, $UserSessionID);

    $ShortName = getElementShortName($ITSMTypeID);
    return array($ITSMID, $ITSMTableName, $ITSMTypeID, $ShortName, $ModuleType);
}

/* Before refactoring */
/*
function createITSMEntry($UserSessionID, $ITSMForm, $FormID, $RequestForm, $ITSMTableName, $ElementCreatedDateVal, $ITSMTypeID, $ModuleType, $SLASupported, $ITSMTypeName1, $ITSMID2, $ITSMTypeID2)
{
    global $conn;
    global $functions;

    $sql = "";
    $createdByFound = false; // Initialize flag to detect 'CreatedBy' field

    if ($ITSMID2 !== "") {
        $ITSMTableName2 = $functions->getITSMTableName($ITSMTypeID2);
        $ITSMTypeName2 = $functions->getITSMTypeName($ITSMTypeID2);
    }
    
    // Check if 'CreatedBy' is present in the form data
    foreach ($ITSMForm as $item) {
        if ($item['name'] == 'CreateFormCreatedBy') {
            $createdByFound = true;
            break;
        }
    }

    // If 'CreatedBy' is not found, add it with a default value
    if (!$createdByFound) {
        $ITSMForm[] = ['name' => 'CreateFormCreatedBy', 'value' => $UserSessionID];
    }

    // If 'CreatedBy' is not found, add it with a default value
    if (!$createdByFound) {
        $ITSMForm[] = ['name' => 'CreateFormCreated', 'value' => "Now()"];
    }

    $sql = "INSERT INTO $ITSMTableName (";
    foreach ($ITSMForm as $key => $value) {
        $Field = $value['name'];
        $Field = str_replace("CreateForm", "", $Field);
        $sql .= "$Field,";
    }
    
    $sql .= ") VALUES (";
    foreach ($ITSMForm as $key => $value) {

        $Name = $value['name'];
        $Name = trim(str_replace("CreateForm", "", $Name));
        $Value = mysqli_real_escape_string($conn, $value['value']);
        $FieldType = $functions->getITSMFieldTypeID($ITSMTypeID, $Name);
        
        switch ($Name) {
            case "Created":
                $Value = date("Y-m-d H:i:s");
                break;
            case "CreatedBy":
                $Value = $UserSessionID;
                break;
            case "Customer":
                $CustomerID = $Value;
                break;
            case "Subject":
                $Subject = $Value;
                if (strpos($Subject, 'We recieved your email but...') !== false) {
                    return;
                }
                break;
            case "Description":
                $Description = $Value;
                break;
            case "Responsible":
                $ResponsibleID = $Value;
                break;
            case "Active":
                $Value = 1;
                break;
            case "Priority":
                $Priority = $Value;
                break;
            case "SLA":
                $SLAID = $Value;
                break;
            case "LastUpdatedBy":
                $Value = $UserSessionID;
                break;
            case "LastUpdated":
                $Value = date("Y-m-d H:i:s");
                break;
            case "BusinessService":
                $BusinessServiceID = $Value;
                break;
            default:
                if ($FieldType == "5" && $Value !== "") {
                    $Value = date("Y-m-d H:i:s", strtotime($Value));
                } else {
                    $Value = mysqli_real_escape_string($conn, $value['value']);
                }
        }

        if ($Value == "") {
            $sql .= "NULL,";
        } else {
            $sql .= "'$Value',";
        }
    }
    $sql .= ");";
    $sql = str_replace(",)", ")", $sql);

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    $ITSMID = mysqli_insert_id($conn);
    if ($ModuleType == "4") {
        $LogActionText = "Created";
        createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
        $DontEmailCustomer = true;
    } else {
        $LogActionText = "Created";
        createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
        $DontEmailCustomer = false;
    }

    if($UserSessionID == "0"){
        $DontEmailCustomer = true;
    }
    // If Customer is empty, that is for ITSM modules that do not require customer, we can still make it - we can set Customer to Repsonsible
    if (empty($CustomerID) && !empty($ResponsibleID)) {
        $CustomerID = $ResponsibleID;
        updateITSMFieldValue($ITSMID, $CustomerID, "Customer", $ITSMTypeID);
        $DontEmailCustomer = true;
    }

    // We got ourselfs a create from - lets create relations
    if ($ITSMID2 !== "") {
        $LogActionText = "Created";
        $sql = "INSERT INTO itsm_relations(Table1, Table2, ID1, ID2) VALUES (?,?,?,?);";

        $stmt = mysqli_prepare($conn, $sql);
        $stmt->bind_param("ssss", $ITSMTableName, $ITSMTableName2, $ITSMID, $ITSMID2);
        $stmt->execute();
        $result = $stmt->get_result();

        $LogActionText1 = "Relation created to $ITSMTypeName2 $ITSMID2";
        createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText1);
        $LogActionText2 = "Relation created to $ITSMTypeName1 $ITSMID";
        createITSMLogEntry($ITSMID2, $ITSMTypeID2, $UserSessionID, $LogActionText2);
    }

    // Set CreatedBy
    updateITSMFieldValue($ITSMID, $UserSessionID, "CreatedBy", $ITSMTypeID);
    // Set CompanyID on newly created ITSM element
    $CompanyID = getUserRelatedCompanyID($CustomerID);
    updateITSMFieldValue($ITSMID, $CompanyID, "RelatedCompanyID", $ITSMTypeID);

    $LogActionText = "Company ID set to $CompanyID";
    createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
    $SLA = "";
    if ($SLASupported == 1) {
        // Set SLA - if no BusinessService then set to Company SLA
        if (empty($BusinessServiceID)) {
            $SLA = getRelatedSLAID($CompanyID);
            updateITSMFieldValue($ITSMID, $SLA, "SLA", $ITSMTypeID);
        } else {
            $SLA = getSLAFromBS($BusinessServiceID);
            updateITSMFieldValue($ITSMID, $SLA, "SLA", $ITSMTypeID);
        }
        $LogActionText = "SLA set to $SLA";
        createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
    }

    if ($ITSMTypeID == "2") {
        $FormsTableName = getTableNameFromFormID($FormID);
        $WorkFlowID = getRelatedWorkFlowID($FormID);

        if (!empty($WorkFlowID)) {
            $RedirectPage = "javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');";
            $WorkFlowID = createWorkFlow($ITSMID, $WorkFlowID, $UserSessionID, $RedirectPage, $ITSMTypeID);

            $LogActionText = "Added Workflow: $WorkFlowID";
            createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
        }

        $counter = 0;

        $RecordID = "";
        foreach ($RequestForm as $key => $value) {            
            $fieldname = $value['name'];
            $fieldvalue = $value['value'];

            if (empty($fieldvalue)) {
                $fieldvalue = "";
            }

            if ($counter == 0) {
                $sql = "INSERT INTO $FormsTableName ($fieldname) VALUES ('$fieldvalue');";
                mysqli_query($conn, $sql);
                $RecordID = mysqli_insert_id($conn);
                $counter = $counter + 1;
            } else {
                $sql = "UPDATE $FormsTableName SET $fieldname = '$fieldvalue' WHERE ID = $RecordID;";
                mysqli_query($conn, $sql);
            }
        }
        //Set RelatedRequestID on forms table
        $sql = "UPDATE $FormsTableName SET RelatedRequestID = '$ITSMID' WHERE ID = $RecordID;";
        mysqli_query($conn, $sql);
        //Set FormID on request
        $sql = "UPDATE $ITSMTableName SET RelatedFormID = '$FormID' WHERE ID = $ITSMID;";
        mysqli_query($conn, $sql);
    }

    if ($SLASupported == 1) {
        if ($Priority !== "" && $SLA !== "") {
            //Create ITSM SLA Reaction times
            //Get SLA reactiontimes for the SLA ID according to the priority selected
            $ReactionTimes[] = array();
            $ReactionTimes = getSLAStatusCores($ITSMTypeID, $SLA, $Priority);
            foreach ($ReactionTimes as $value) {
                $Status = $value["Status"];
                $Minutes = $value["Minutes"];
                $DateViolated = getDateTimeViolated($ElementCreatedDateVal, $Minutes);
                createTimelineSLAViolationDates($ITSMID, $ITSMTypeID, $Status, $DateViolated);
            }
        }
    }

    if (!empty($CustomerID) && $DontEmailCustomer == false && $ModuleType == "1") {

        $TemplateID = "3";
        $CustomerEmail = getUserEmailFromID($CustomerID);
        $CustomerFullName = $functions->getUserFullName($CustomerID);

        if (strpos($Subject, '[Practicle Problem report]') !== false) {
            sendITSMMailTemplate($ITSMTypeID, $ITSMID, $CustomerID, "", $TemplateID);
            $LogActionText = "Customer: $CustomerFullName emailed on: $CustomerEmail";
            createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
        }
    }
    if ($ModuleType == "4" || $ModuleType == "3") {
    } else {
        if (!empty($ResponsibleID)) {

            // Add to users tasklist
            $RedirectPage = "javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');";
            //addtotaskslist($ITSMID, $ResponsibleID, $ITSMTypeID, $RedirectPage);
            $TemplateID = "4";

            $ResponsibleEmail = getUserEmailFromID($ResponsibleID);
            $ResponsibleFullName = $functions->getUserFullName($ResponsibleID);
            
            //sendITSMMailTemplate($ITSMTypeID, $ITSMID, $ResponsibleID, "", $TemplateID);

            $LogActionText = "Responsible: $ResponsibleFullName emailed on: $ResponsibleEmail";
            //createITSMLogEntry($ITSMID, $ITSMTypeID, $UserSessionID, $LogActionText);
        }
    }
    $ActivityText = "<b>" . $functions->translate("Subject") . "</b>" . "<br><br>" . $Subject;
    $Headline = $functions->translate("Created") . " " . strtolower($ITSMTypeName1) . " " . $ITSMID;
    logActivity($ITSMID, $ITSMTypeID, $Headline, $ActivityText, "javascript:javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');");

    createITSMFilesFromTemp($ITSMID, $ITSMTypeID, $UserSessionID);

    $ShortName = getElementShortName($ITSMTypeID);
    return array($ITSMID, $ITSMTableName, $ITSMTypeID, $ShortName, $ModuleType);
}
*/
function updateManifestFile($settingvalue)
{
    global $conn;
    global $functions;

    // Read manifest.json into a string
    $content = file_get_contents('manifest.json');

    // Decode the JSON string into a PHP associative array
    $manifest = json_decode($content, true);

    // Replacement value
    $new_name = "$settingvalue"; // Set this to whatever name you want

    // Modify the name and short_name
    $manifest['name'] = $new_name;
    $manifest['short_name'] = $new_name;

    // Convert the modified PHP array back into a JSON string
    $updated_content = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // Write changes back to manifest.json
    file_put_contents('manifest.json', $updated_content);    
}

function fetchJsonData($url)
{
    // Fetch the JSON data from the URL
    $jsonContent = file_get_contents($url);

    // Decode the JSON data to a PHP array
    return json_decode($jsonContent, true);
}

function getCVEEntries($days)
{
    global $conn;
    global $functions;
    $days = strval($days);

    // Check if category is active
    $checkQuery = $conn->query("SELECT Active FROM news_categories WHERE ID = 2");
    if (!$checkQuery) {
        die("Category check query failed: " . $conn->error);
    }

    $category = $checkQuery->fetch_assoc();
    if (!$category || $category['Active'] != 1) {
        return;
    }

    // Fetch the list of products to search for
    $result = $conn->query("SELECT Product FROM news_cve_filters");
    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    while ($row = $result->fetch_assoc()) {
        $product = $row['Product'];

        // Fetch data from the URL for the product
        $product = strtolower($product);
        $productReplaced = str_replace(' ', '%20', $product);
        $data = fetchJsonData("https://services.nvd.nist.gov/rest/json/cves/2.0?keywordSearch={$productReplaced}&keywordExactMatch");

        if (isset($data['vulnerabilities'])) {
            $CVEArray = [];

            // Get the DateTime object for the threshold date (14 days ago)
            $thresholdDate = new DateTime();
            $thresholdDate->modify("-$days days");

            foreach ($data['vulnerabilities'] as $entry) {
                $cveData = $entry['cve'];

                // Convert the date to desired format
                $date = new DateTime($cveData['published']);
                $formattedDate = $date->format('Y-m-d H:i:s');

                // If the publication date of the vulnerability is earlier than the threshold date, skip processing it
                if ($date < $thresholdDate
                ) {
                    continue;
                }

                // Construct the Headline and Content as described in the provided SQL query
                $headline = "{$cveData['id']}: {$product} (Score: {$cveData['metrics']['cvssMetricV31'][0]['cvssData']['baseScore']} Severity: {$cveData['metrics']['cvssMetricV31'][0]['cvssData']['baseSeverity']})";
                $content = "{$cveData['descriptions'][0]['value']}<br><br><a href='https://nvd.nist.gov/vuln/detail/{$cveData['id']}' target='_new' onclick=\"event.stopPropagation(); window.open('https://nvd.nist.gov/vuln/detail/{$cveData['id']}', '_blank');\">https://nvd.nist.gov/vuln/detail/{$cveData['id']}</a><br><br><button id=\"createChangeFromCVE\" class=\"btn btn-sm btn-info\" onclick=\"(async function() { event.stopPropagation(); await runModalCreateITSM(3); fillCreateChangeFromCVE('{$cveData['id']}'); })();\">Create Change</button><br><br>";

                $cve = [
                        'headline' => $headline,
                        'content' => $content,
                        'cveid' => $cveData['id'],
                        'dateCreated' => $formattedDate
                    ];

                $CVEArray[] = $cve;
            }

            // Loop through CVEArray and insert each entry into the `news` table
            foreach ($CVEArray as $cve) {
                $stmt = $conn->prepare("INSERT INTO news (Headline, Content, CreatedByUserID, NewsWriter, DateCreated, RelatedCategory, CVEID, Active) 
                            VALUES (?, ?, 0, 0, ?, 2, ?, 1)
                            ON DUPLICATE KEY UPDATE
                            Headline = VALUES(Headline),
                            Content = VALUES(Content),
                            DateCreated = VALUES(DateCreated)");

                $stmt->bind_param('ssss', $cve['headline'], $cve['content'], $cve['dateCreated'], $cve['cveid']);

                if (!$stmt->execute()) {
                    echo "Error inserting CVE as news for product {$product}: " . $stmt->error . "<br>";
                }

                $stmt->close();
            }
        }
    }

    $conn->close();
}

function removeAllCVEEntries()
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM news WHERE RelatedCategory = 2";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        // Handle error - statement preparation failed
        $functions->errorlog("Statement preparation failed: " . $conn->error, "removeAllCVEEntries");
        return false;
    }

    if (!$stmt->execute()) {
        // Handle error - statement execution failed
        $functions->errorlog("Execution failed: " . $stmt->error, "removeAllCVEEntries");
        $stmt->close();
        return false;
    }

    $stmt->close();
    return true;
}

function fixDescriptionInChangelog()
{
    global $conn;
    global $functions;

    $sql = "UPDATE changelog SET Description = REPLACE(Description, '\\n\\n', '');";

    mysqli_query($conn, $sql) or die(mysqli_error($conn));
}


function updateAllTableComments()
{
    global $conn;
    global $functions;
    $db = getCurrentDB();
    $tableComment = '{"update":["schema"]}';

    // Fetch all table names along with their table comments
    $query = $conn->prepare("SELECT TABLE_NAME, TABLE_COMMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?");
    $query->bind_param('s', $db);
    $query->execute();
    $result = $query->get_result();

    if ($result === false) {
        // Handle error
        die('Error executing query: ' . htmlspecialchars($conn->error));
    }

    $tables = $result->fetch_all(MYSQLI_ASSOC);

    // Loop through each table and set the comment if there isn't one already
    foreach ($tables as $row) {
        $table = $row['TABLE_NAME'];
        $currentComment = $row['TABLE_COMMENT'];

        $alterSql = "ALTER TABLE `$table` COMMENT = '$tableComment'";
        mysqli_query($conn, $alterSql) or die('Query fail: ' . mysqli_error($conn));
    }

    return true;
}

function checkForExistingMainLanguage($LanguageEntry)
{
    global $conn;
    global $functions;

    // Fetch all table names along with their table comments
    $query = $conn->prepare("SELECT MainLanguage FROM languages WHERE MainLanguage = ?");
    $query->bind_param('s', $LanguageEntry);
    $query->execute();
    $result = $query->get_result();

    $tables = $result->fetch_all(MYSQLI_ASSOC);

    // Loop through each table and set the comment if there isn't one already
    foreach ($tables as $row) {
        $MainLanguage = $row['MainLanguage'];
    }

    return $MainLanguage;
}

function upgradeLatestPracticleBaseDb()
{
    // Name of the database to drop and restore
    $databaseName = 'practicle_base';

    // URL of the remote SQL file
    $sqlFileUrl = 'https://support.practicle.dk/backups/releases/hekx85klqcs5yhw7vfw5mq9sak0g/practicle_base.sql';

    // Use cURL to download the SQL file content
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sqlFileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // consider adjusting this and the next line in production for security
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $sqlContent = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    // Check if any error occurred during download
    if ($error) {
        die("Error downloading SQL file: $error");
    }

    // Get the PDO instance from your include file
    $pdo = getDbConnection();

    // Proceed with the database operations
    try {
        // Drop the database. BE VERY CAREFUL WITH THIS COMMAND.
        $pdo->exec("DROP DATABASE IF EXISTS `$databaseName`");

        // Create the database
        $pdo->exec("CREATE DATABASE `$databaseName`");

        // Select the database
        $pdo->exec("USE `$databaseName`");

        // Disable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

        // Execute the SQL dump to restore the database
        $pdo->exec($sqlContent);

        // Enable foreign key checks again
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");

        return true;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
        return false;
    }
}

function regenerateDatabaseCollation()
{
    global $conn;
    global $functions;

    // Set the database name
    $database = getCurrentDB();

    // Set target character set and collation
    $target_charset = 'utf8mb4';
    $target_collation = 'utf8mb4_unicode_ci';

    // Check the current charset of the database
    $current_db_charset_query = "SELECT DEFAULT_CHARACTER_SET_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'";
    $result_db_charset = mysqli_query($conn, $current_db_charset_query);
    $current_db_charset = mysqli_fetch_assoc($result_db_charset)['DEFAULT_CHARACTER_SET_NAME'];

    // Only change if the charset is not the target charset
    if ($current_db_charset != $target_charset) {
        // Change the database character set and collation
        $alter_db_sql = "ALTER DATABASE `$database` CHARACTER SET $target_charset COLLATE $target_collation";
        if (mysqli_query($conn, $alter_db_sql)) {
            echo "Converted database `$database` to $target_charset successfully.<br>";
        } else {
            echo "Error converting database `$database`: " . mysqli_error($conn) . "<br>";
        }
    }

    // Fetch all tables in the database
    $result = mysqli_query($conn, "SHOW TABLES");
    if ($result) {
        while ($row = mysqli_fetch_row($result)) {
            $table = $row[0];

            // Check the current charset of the table
            $current_table_charset_query = "SELECT CCSA.character_set_name 
            FROM information_schema.tables T 
            LEFT JOIN information_schema.collation_character_set_applicability CCSA 
            ON T.table_collation = CCSA.collation_name 
            WHERE T.table_schema = '$database' AND T.table_name = '$table'";

            $result_table_charset = mysqli_query($conn, $current_table_charset_query);
            $current_table_charset = mysqli_fetch_assoc($result_table_charset)['character_set_name'];

            // Only alter the table if its charset is not the target charset
            if ($current_table_charset != $target_charset) {
                $alter_table_sql = "ALTER TABLE `$table` CONVERT TO CHARACTER SET $target_charset COLLATE $target_collation";
                if (mysqli_query($conn, $alter_table_sql)) {
                    echo "Converted table `$table` to $target_charset successfully.<br>";
                } else {
                    echo "Error converting table `$table`: " . mysqli_error($conn) . "<br>";
                }
            } else {
                echo "Table `$table` already has the correct charset.<br>";
            }
        }
    } else {
        echo "Failed to fetch tables: " . mysqli_error($conn);
    }

    mysqli_close($conn);

}

function checkSubmissionRate($limit, $timeFrame, $waitTime)
{
    if (!isset($_SESSION['submit_count'])) {
        $_SESSION['submit_count'] = 0;
        $_SESSION['first_submit_time'] = time();
    }

    // If the limit has been exceeded
    if ($_SESSION['submit_count'] >= $limit) {
        if ((time() - $_SESSION['first_submit_time']) < $timeFrame) {
        // User must wait for $waitTime seconds
            echo "You've tried too fast too many times. Please wait for <span id=\"counter\">30</span> seconds.";
            ?>
            <script>
                var seconds = <?php echo $waitTime ?>;
                function countdown() {
                    seconds = seconds - 1;
                    if (seconds < 0) {
                        // Chose one of the following lines:
                        window.location = "register.php"; // if the file is in the same directory or you want to specify the path
                        // window.location.href = window.location.href; // if you want to refresh the same page
                    } else {
                        // Update remaining seconds
                        document.getElementById("counter").innerHTML = seconds;
                        // Count down using javascript
                        window.setTimeout("countdown()", 1000);
                    }
                }
                // Start countdown
                countdown();
            </script>
            <?php
            return false;
        } else {
            // Reset count and time
            $_SESSION['submit_count'] = 0;
            $_SESSION['first_submit_time'] = time();
        }
    } else {
        return true;
    }
}



function checkConnection($url) {
    global $conn;
    global $functions;

    // Get the current database name from the connection
    $currentDbResult = mysqli_query($conn, "SELECT DATABASE()");
    $currentDbRow = mysqli_fetch_row($currentDbResult);
    $currentDbName = $currentDbRow[0];

    // Check if the current database is 'practicle_practicle'
    if ($currentDbName !== 'practicle_practicle') {
        return "Database not matching. Skipping ping test.";
    }

    // Ping command parameters
    $count = 1; // Send only one echo request

    // Platform-specific ping command
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows-based ping command
        $command = "ping -n $count $url";
    } else {
        // Unix-based ping command
        $command = "ping -c $count $url";
    }

    // Execute ping command
    $output = [];
    $status = 0;
    exec($command, $output, $status);

    // Log to database only if the ping fails
    if ($status !== 0) {
        // Prepare and execute SQL query for failed ping
        $stmt = $conn->prepare("INSERT INTO connection_test (Url) VALUES (?)");
        $stmt->bind_param('s', $url);
        $stmt->execute();
        $stmt->close();

        return "Down";
    }

    return "Up";
}

function removeUserGroupsRoles($GroupID)
{
    global $conn;
    global $functions;

    // Prepare and execute SQL query for failed ping
    $stmt = $conn->prepare("DELETE FROM usergroupsroles WHERE GroupID = ?;");
    $stmt->bind_param('i', $GroupID);
    $stmt->execute();
    $stmt->close();

    // Prepare and execute SQL query for failed ping
    $stmt2 = $conn->prepare("DELETE FROM usersgroups WHERE GroupID = ?;");
    $stmt2->bind_param('i', $GroupID);
    $stmt2->execute();
    $stmt2->close();

}

function getCIFieldDefinitions($SessionUserID, $CITypeID, $CIID, $CITableName, $group_array, $FormType, $languageshort, $ModalType, $UserLanguageCode)
{
    global $conn;
    global $functions;

    $DefinitionLeft = "";
    $DefinitionRight = "";
    $Locked = "";
    $UsersGroups = $group_array;

    if(!$CIID){
        $CIID = false;
    }

    $sql = "SELECT cmdb_cis.Name, cmdb_ci_fieldslist.ID, cmdb_ci_fieldslist.FieldName, cmdb_ci_fieldslist.FieldLabel, cmdb_fieldslist_types.Definition, cmdb_ci_fieldslist.FieldDefaultValue, cmdb_ci_fieldslist.fieldtitle, cmdb_ci_fieldslist.SelectFieldOptions, cmdb_ci_fieldslist.FieldWidth,
          cmdb_ci_fieldslist.GroupFilterOptions, cmdb_ci_fieldslist.LookupTable, cmdb_ci_fieldslist.DefaultField, cmdb_ci_fieldslist.LookupFieldResultView, cmdb_ci_fieldslist.HideForms,cmdb_ci_fieldslist.LockedView,cmdb_ci_fieldslist.LockedCreate,cmdb_ci_fieldslist.Required,
          cmdb_ci_fieldslist.Addon, cmdb_ci_fieldslist.Hidden, cmdb_ci_fieldslist.SyncSourceField, cmdb_ci_fieldslist.AddEmpty, cmdb_ci_fieldslist.FullHeight, cmdb_ci_fieldslist.RightColumn, cmdb_ci_fieldslist.LabelType, cmdb_ci_fieldslist.FieldType
          FROM cmdb_cis
          LEFT JOIN cmdb_ci_fieldslist ON cmdb_cis.ID = cmdb_ci_fieldslist.RelatedCITypeID
          LEFT JOIN cmdb_fieldslist_types ON cmdb_ci_fieldslist.FieldType = cmdb_fieldslist_types.ID
          WHERE cmdb_cis.ID = ?
          ORDER BY cmdb_ci_fieldslist.FieldOrder ASC;";

    try {
        // Prepare the SQL statement
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement: $sql. Error: " . mysqli_error($conn));
        }

        // Bind parameters
        if (!$stmt->bind_param("i", $CITypeID)) {
            throw new Exception("Failed to bind parameters for SQL: $sql. Error: " . mysqli_error($conn));
        }

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute SQL: $sql. Error: " . mysqli_error($conn));
        }

        // Get the result
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception("Failed to get result for SQL: $sql. Error: " . mysqli_error($conn));
        }
    } catch (Exception $e) {
        // Log the full SQL query and error message
        $functions->errorlog("SQL Error: " . $e->getMessage(), "executeQuery");
        http_response_code(500); // Internal Server Error
        echo json_encode([
            "error" => true,
            "message" => "An error occurred while processing the request. Please check the logs for more details."
        ]);
        exit; // Stop further execution
    }
    
    while ($row = mysqli_fetch_array($result)) {
        $FieldID = $row["ID"];
        $CIName = $row["Name"];
        $FieldType = $row["FieldType"];
        $LookupTable = $row["LookupTable"];
        $LookupField = "ID";
        $LookupFieldResult = $row["LookupFieldResultView"];
        $DefaultField = $row["DefaultField"];
        $SyncSourceField = $row["SyncSourceField"];
        $FieldLabel = $row["FieldLabel"];
        $FieldLabel = $functions->translate($FieldLabel);
        $FieldName = $row["FieldName"];
        $FieldDefaultValue = $row["FieldDefaultValue"];        
        $FieldTitle = $row["fieldtitle"];
        $FieldTitle = $functions->translate($FieldTitle);
        $FieldWidth = $row["FieldWidth"];
        $FullHeight = $row["FullHeight"];
        $LockedView = $row["LockedView"];
        $LockedCreate = $row["LockedCreate"];
        $AddEmpty = $row["AddEmpty"];
        $RightColumn = $row["RightColumn"];
        $LabelType = $row["LabelType"];
        $Addon = $row["Addon"];
        $Hidden = $row["Hidden"];
        $GroupFilterOptions = $row["GroupFilterOptions"];

        if($GroupFilterOptions){
            // Split the string by '#' to get an array of group IDs
            $groupFilterArray = explode('#', $GroupFilterOptions);

            // Iterate through the group filter array and check if any group ID exists in the user groups array
            foreach ($groupFilterArray as $groupID) {
                if (in_array($groupID, $UsersGroups)) {
                    $Hidden = "0";
                } else {
                    $Hidden = "1";
                }
            }
        }

        if($Hidden == "1"){
            $Hidden = "hidden";
        }

        $SelectFieldOptions = $row["SelectFieldOptions"];
        $SelectFieldOptionsPre = "";

        if($SelectFieldOptions){
            if ($AddEmpty == "1") {
                $SelectFieldOptionsPre = "<option value=\"\"></option>";
            }
            $SelectFieldOptions = str_replace("#", "", $SelectFieldOptions);
            $SelectFieldOptions = translateOptions($SelectFieldOptions, $FieldDefaultValue);
            $SelectFieldOptions = $SelectFieldOptionsPre . $SelectFieldOptions;
        }

        $addonBtn = "";
        if ($Addon) {
            $addonBtn = getModuleFieldAddonBtn($Addon);
            $addonBtn = str_replace("<:FieldName:>", $FieldName, $addonBtn);
        } else {
            $addonBtn = "";
        }

        $HideForms = $row["HideForms"];
        if ($HideForms == "1") {
            continue;
        }

        if ($FullHeight == "1") {
            $Height = "100%";
        } else {
            $Height = "150px";
        }

        $Required = $row["Required"];
        if ($Required == "1") {
            $Required = "required";
            $RequiredLabel = "<code>*</code>";
        } else {
            $Required = "";
            $RequiredLabel = "";
        }

        if($ModalType == "Create"){
            $Locked = $LockedCreate;
        } else {
            $Locked = $LockedView;
        }

        if ($Locked == "1") {
            $Locked = "disabled";
        } else {
            $Locked = "";
        }

        if ($SyncSourceField !== "" && $DefaultField == "0") {
            $Locked = "disabled";
            $FieldTitle = $FieldTitle . " (" . $functions->translate("This field will be automatically updated by syncronization") . ")";
        }
        
        if($LabelType == "1"){
            if ($CIID) {
                $fieldValue = $functions->getFieldValueFromID($CIID, $FieldName, $CITableName);
            }

            if($FieldType == "5"){
                if($fieldValue){
                    $fieldValue = convertToDanishTimeFormat($fieldValue);
                }
            }
            if ($FieldType == "4") {
                if ($fieldValue) {
                    $fieldValue = getSelectOptionValue($LookupTable, $LookupField, $LookupFieldResult, $fieldValue, $AddEmpty);
                } else {
                    $fieldValue = "";
                }                
            }
            $Definition = "
            <div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\">
                <li id=\"<:fieldid:>\" class=\"form-control\" title=\"<:fieldtitle:>\">$FieldLabel: $fieldValue</li>
            </div>";
        } else {
            $Definition = $row["Definition"];
        }

        // Add condition for FieldType 3 to set the checkbox as checked
        if ($FieldType == "3") {
            $fieldValue = $functions->getFieldValueFromID($CIID, $FieldName, $CITableName);
            // Check if $fieldValue is set; if not, fall back to $FieldDefaultValue
            if (isset($fieldValue)) {
                // If $fieldValue is set, check if it's 1 for checked, 0 for unchecked
                $checked = ($fieldValue == 1) ? "checked" : "";
            } else {
                // If $fieldValue is not set, use $FieldDefaultValue to determine checked state
                $checked = ($FieldDefaultValue == 1) ? "checked" : "";
            }
            // Replace the <:checked:> placeholder in the HTML definition
            $Definition = str_replace("<:checked:>", $checked, $Definition);
        } else {
            // If it's not a checkbox, clear the checked attribute
            $Definition = str_replace("<:checked:>", "", $Definition);
        }

        $Definition = str_replace("<:fieldname:>", $FieldName, $Definition);
        $Definition = str_replace("<:fieldvalue:>", $FieldDefaultValue, $Definition);
        $Definition = str_replace("<:fieldid:>", $FieldName, $Definition);
        $Definition = str_replace("<:label:>", $FieldLabel, $Definition);
        $Definition = str_replace("<:fieldtitle:>", $FieldTitle, $Definition);
        $Definition = str_replace("<:required:>", $Required, $Definition);
        $Definition = str_replace("<:requiredlabel:>", $RequiredLabel, $Definition);
        $Definition = str_replace("<:Locked:>", $Locked, $Definition);
        $Definition = str_replace("<:addonBtn:>", " " . $addonBtn, $Definition);
        $Definition = str_replace("<:height:>", $Height, $Definition);
        $Definition = str_replace("<:hidden:>", $Hidden, $Definition);
        
        if (!empty($LookupTable)) {
            $SelectFieldOptions = "";
            $SelectFieldOptions = getCILookupTableSelectOptions($LookupTable, $LookupField, $LookupFieldResult, $CITypeID, $FieldName, $AddEmpty);
        }
        
        $Definition = str_replace("<:selectoptions:>", $SelectFieldOptions, $Definition);
        $Definition = str_replace("<:fieldwidth:>", $FieldWidth, $Definition);
        $Definition = str_replace("<:languagecode:>", $languageshort, $Definition);
        $Definition = str_replace("<:addonBtn:>", " " . $addonBtn, $Definition);

        if($RightColumn == "1"){
            $DefinitionRight .= $Definition;
        } else {
            $DefinitionLeft .= $Definition;
        }
    }

    if (in_array("100001", $group_array) || in_array("100015", $group_array)) {
        $UpdateButton = "<div class=\"col-md-12 col-sm-12 col-xs-12\"><button type=\"button\" class=\"btn btn-sm btn-success float-end\" id=\"btn_ci_update\" name=\"btn_ci_update\" onclick=\"updateCI('$CITypeID')\">" . $functions->translate("Update") . "</button></div>";
    } else {
        $UpdateButton = "";
    };

    $CITypeName = $CIName.": " .$functions->translate("Create");
    $FormFileAction = "<form action=\"../functions/cifileupload.php?userid=$SessionUserID&elementref=$CITypeID&elementid=$CIID&elementpath=cis\" class=\"dropzone\" id=\"dropzoneformCMDB\"></form>";

    $FieldsArray[] = array("CITypeName" => $CITypeName, "DefinitionLeft" => $DefinitionLeft, "DefinitionRight" => $DefinitionRight, "UpdateButton" => $UpdateButton, "FormFileAction" =>$FormFileAction, "UserLanguageCode" => $UserLanguageCode);
    return $FieldsArray;
}

function getITSMFieldDefinitions($SessionUserID, $ITSMTypeID, $ITSMID, $FormType, $UserGroups, $languageshort, $ModalType, $TeamID)
{
    global $conn;
    global $functions;

    $Allowed = 1;
    $UserType = $functions->getUserType($SessionUserID);
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
    $ModuleType = $functions->getITSMModuleType($ITSMTypeID);
    $DefinitionLeft = "";
    $DefinitionRight = "";
    $UpdateButton = "";

    if($ModuleType == 4){
        if($ITSMID){
            $Allowed = $functions->allowedToViewITSMType4($ITSMTypeID, $ITSMID, $SessionUserID, $UserGroups, $TeamID);
        }
    }

    if ($ModuleType == 3) {
        if ($ITSMID) {
            $Allowed = $functions->allowedToViewITSMModuleType3($ITSMTypeID, $ITSMID, $SessionUserID, $UserGroups, $TeamID);
        }
    }

    if($Allowed == 0){
        $FieldsArray[] = array("ITSMTypeName" => "", "DefinitionLeft" => "", "DefinitionRight" => "", "UpdateButton" => "", "FormFileAction" => "", "CreateRelationButton1" => "", "CreateRelationButton2" => "", "CreateITSMEntrynButton" => "", "rejectNow" => "1");
        return $FieldsArray;
        exit;
    }

    if (!$ITSMID) {
        $ITSMID = false;
    }

    $sql = "SELECT itsm_fieldslist.ID, itsm_fieldslist.FieldName, itsm_fieldslist.FieldLabel, itsm_fieldslist_types.Definition, itsm_fieldslist.FieldDefaultValue, itsm_fieldslist.FieldTitle, itsm_fieldslist.SelectFieldOptions, itsm_fieldslist.FieldWidth,
          itsm_fieldslist.GroupFilterOptions, itsm_fieldslist.UserFullName, itsm_fieldslist.LookupTable, itsm_fieldslist.LookupFieldResultView,itsm_fieldslist.HideForms,itsm_fieldslist.Required
          ,itsm_fieldslist.LockedCreate, itsm_fieldslist.LockedView, itsm_fieldslist.Hidden, itsm_fieldslist.Addon, itsm_fieldslist.FieldType, itsm_fieldslist.AddEmpty, itsm_fieldslist.FullHeight, itsm_fieldslist.RightColumn, itsm_fieldslist.LabelType, itsm_fieldslist.FieldType
          FROM itsm_modules
          LEFT JOIN itsm_fieldslist ON itsm_modules.ID = itsm_fieldslist.RelatedTypeID
          LEFT JOIN itsm_fieldslist_types ON itsm_fieldslist.FieldType = itsm_fieldslist_types.ID
          WHERE itsm_modules.ID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC";

    try {
        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt === false) {
            throw new Exception('mysqli_prepare failed: ' . mysqli_error($conn));
        }

        // Bind parameters
        if (!$stmt->bind_param("i", $ITSMTypeID)) {
            throw new Exception('bind_param failed: ' . $stmt->error);
        }

        // Execute the statement
        if (!$stmt->execute()) {
            throw new Exception('execute failed: ' . $stmt->error);
        }

        // Get the result
        $result = $stmt->get_result();
        if ($result === false) {
            throw new Exception('get_result failed: ' . $stmt->error);
        }

    } catch (Exception $e) {
        $functions->errorlog($e->getMessage(),"getITSMFieldDefinitions");
    }

    while ($row = mysqli_fetch_array($result)) {
        $FieldID = $row["ID"];
        $FieldType = $row["FieldType"];
        $LookupTable = $row["LookupTable"];
        $LookupField = "ID";
        $LookupFieldResult = $row["LookupFieldResultView"];
        $FieldLabel = $functions->translate($row["FieldLabel"]);
        $FieldName = $row["FieldName"];
        $FieldName2 = $FormType . $row["FieldName"];
        $FieldDefaultValue = $row["FieldDefaultValue"];
        $FieldTitle = $functions->translate($row["fieldtitle"]);
        $AddEmpty = $row["AddEmpty"];
        $RightColumn = $row["RightColumn"];
        $LabelType = $row["LabelType"];
        $FieldWidth = $row["FieldWidth"];
        $FullHeight = $row["FullHeight"];
        $LockedCreate = $row["LockedCreate"];
        $LockedView = $row["LockedView"];
        $UserFullName = $row["UserFullName"];
        $Hidden = $row["Hidden"];
        $GroupFilterOptions = $row["GroupFilterOptions"];

        if($GroupFilterOptions){
            // Split the string by '#' to get an array of group IDs
            $groupFilterArray = explode('#', $GroupFilterOptions);

            // Iterate through the group filter array and check if any group ID exists in the user groups array
            foreach ($groupFilterArray as $groupID) {
                if (in_array($groupID, $UserGroups)) {
                    $Hidden = "0";
                } else {
                    $Hidden = "1";
                }
            }
        }

        if($Hidden == "1"){
            $Hidden = "hidden";
        }

        if($UserType == "2"){
            $LabelType = "1";
        }

        $SelectFieldOptions = $row["SelectFieldOptions"];
        $SelectFieldOptionsPre = "";
        if ($SelectFieldOptions) {
            if ($AddEmpty == "1") {
                $SelectFieldOptionsPre = "<option></option>";
            }
            $SelectFieldOptions = str_replace("#", "", $SelectFieldOptions);
            $SelectFieldOptions = translateOptions($SelectFieldOptions, $FieldDefaultValue);
            $SelectFieldOptions = $SelectFieldOptionsPre . $SelectFieldOptions;
        }

        $HideForms = $row["HideForms"];
        
        $Addon = $row["Addon"];
        if ($Addon) {
            $addonBtn = getModuleFieldAddonBtn($Addon);
            if (str_contains($FormType, "CreateForm")) {
                $addonBtn = str_replace("<:FieldName:>", $FieldName2, $addonBtn);
            } else {
                $addonBtn = str_replace("<:FieldName:>", $FieldName, $addonBtn);
            }
        } else {
            $addonBtn = "";
        }
        if ($HideForms == "1") {
            continue;
        }
        if ($FullHeight == "1") {
            $Height = "100%";
        } else {
            $Height = "150px";
        }

        $Required = $row["Required"];
        if ($Required == "1") {
            $Required = "required";
            $RequiredLabel = "<code>*</code>";
        } else {
            $Required = "";
            $RequiredLabel = "";
        }

        if ($ModalType == "Create") {
            $Locked = $LockedCreate;
        } else {
            $Locked = $LockedView;
        }

        if ($Locked == "1") {
            $Locked = "disabled";
        } else {
            $Locked = "";
        }

        if ($LabelType == "1") {
            if($ITSMID){
                $fieldValue = $functions->getFieldValueFromID($ITSMID, $FieldName, $ITSMTableName);
            }
            
            if ($FieldType == "5") {
                if ($fieldValue) {
                    $fieldValue = convertToDanishTimeFormat($fieldValue);
                }
            }
            if ($FieldType == "4") {
                if($fieldValue){
                    $fieldValue = getSelectOptionValue($LookupTable, $LookupField, $LookupFieldResult, $fieldValue, $AddEmpty);
                } else {
                    $fieldValue = "";
                }                
            }
            $Definition = "
            <div class=\"col-lg-<:fieldwidth:> col-sm-12 col-xs-12\">
                <li id=\"<:fieldid:>\" class=\"form-control\" title=\"<:fieldtitle:>\">$FieldLabel: $fieldValue</li>
            </div>";
        } else {
            $Definition = $row["Definition"];
        }

        // Add condition for FieldType 3 to set the checkbox as checked
        if ($FieldType == "3") {
            if ($ITSMID) {
                $fieldValue = $functions->getFieldValueFromID($ITSMID, $FieldName, $ITSMTableName);
            }
            // Check if $fieldValue is set; if not, fall back to $FieldDefaultValue
            if (isset($fieldValue)) {
                // If $fieldValue is set, check if it's 1 for checked, 0 for unchecked
                $checked = ($fieldValue == 1) ? "checked" : "";
            } else {
                // If $fieldValue is not set, use $FieldDefaultValue to determine checked state
                $checked = ($FieldDefaultValue == 1) ? "checked" : "";
            }
            // Replace the <:checked:> placeholder in the HTML definition
            $Definition = str_replace("<:checked:>", $checked, $Definition);
        } else {
            // If it's not a checkbox, clear the checked attribute
            $Definition = str_replace("<:checked:>", "", $Definition);
        }

        $Definition = str_replace("<:fieldname:>", $FieldName2, $Definition);
        $Definition = str_replace("<:fieldvalue:>", "$FieldDefaultValue", $Definition);
        $Definition = str_replace("<:fieldid:>", $FieldName2, $Definition);
        $Definition = str_replace("<:label:>", $FieldLabel, $Definition);
        $Definition = str_replace("<:fieldtitle:>", $FieldTitle, $Definition);
        $Definition = str_replace("<:required:>", $Required, $Definition);
        $Definition = str_replace("<:requiredlabel:>", $RequiredLabel, $Definition);
        $Definition = str_replace("<:Locked:>", $Locked, $Definition);
        $Definition = str_replace("<:addonBtn:>", " " . $addonBtn, $Definition);
        $Definition = str_replace("<:height:>", $Height, $Definition);
        $Definition = str_replace("<:hidden:>", $Hidden, $Definition);

        if ($FieldName == "Status") {
            $SelectFieldOptions = getITSMStatusOptions($ITSMTypeID, $AddEmpty);
        }
        if (!empty($LookupTable) && $FieldName !== "Status") {
            $SelectFieldOptions = getITSMLookupTableSelectOptions($LookupTable, $LookupField, $LookupFieldResult, $ITSMTypeID, $FieldName, $FieldType, $AddEmpty);
        }
        $Definition = str_replace("<:selectoptions:>", $SelectFieldOptions, $Definition);
        $Definition = str_replace("<:fieldwidth:>", $FieldWidth, $Definition);
        $Definition = str_replace("<:languagecode:>", $languageshort, $Definition);

        if ($RightColumn == "1") {
            $DefinitionRight .= $Definition;
        } else {
            $DefinitionLeft .= $Definition;
        }
    }

    $CreateITSMEntrynButton = "<button type=\"button\" class=\"btn btn-sm btn-success ml-auto float-end\" onclick=\"createITSMEntry('$ITSMTableName','','','')\">" . _("Create") . "</button>";
    
    if (in_array("100001", $UserGroups) || in_array("100015", $UserGroups)) {
        $ITSMButtons = $UpdateButton;
        $CreateRelationButton1 = "<button type=\"button\" id=\"btn_itsm_createrelation1\" name=\"btn_itsm_createrelation1\" class=\"btn btn-sm btn-success ml-auto\" onclick=\"createCIRelationITSM('$ITSMTableName')\">" . _("Create") . "</button>";
        $CreateRelationButton2 = "<button type=\"button\" id=\"btn_itsm_createrelation2\" name=\"btn_itsm_createrelation2\" class=\"btn btn-sm btn-success ml-auto\" onclick=\"createITSMRelationITSM('$ITSMTableName')\">" . _("Create") . "</button>";
    } else {
        $UpdateButton = "";
    };

    $ITSMTypeName = $functions->translate("Create") . " " . mb_strtolower($functions->translate($functions->getITSMTypeName($ITSMTypeID)));

    $FormFileAction = "<form action=\"../functions/cifileupload.php?userid=$SessionUserID&elementref=$ITSMTypeID&elementid=$ITSMID&elementpath=itsm\" class=\"dropzone\" id=\"dropzoneformITSM\"></form>";

    $FieldsArray[] = array("ITSMTypeName" => "$ITSMTypeName","DefinitionLeft" => $DefinitionLeft, "DefinitionRight" => $DefinitionRight, "UpdateButton" => $ITSMButtons, "FormFileAction" => $FormFileAction, "CreateRelationButton1" => $CreateRelationButton1, "CreateRelationButton2" => $CreateRelationButton2, "CreateITSMEntrynButton" =>$CreateITSMEntrynButton, "rejectNow" => "");

    return $FieldsArray;
}

function testGetITSMFieldDefinitions()
{
    // Sample inputs to test the function
    $SessionUserID = 1;
    $ITSMTypeID = 1;
    $ITSMID = 1;
    $FormType = 'EditForm';
    $UserGroups = [100001, 100015];
    $languageshort = 'en';
    $ModalType = 'Create';
    $TeamID = 1;

    // Call the function
    $result = getITSMFieldDefinitions($SessionUserID, $ITSMTypeID, $ITSMID, $FormType, $UserGroups, $languageshort, $ModalType, $TeamID);

    // Define expected structure or keys for a valid response
    $expectedKeys = ["ITSMTypeName", "DefinitionLeft", "DefinitionRight", "UpdateButton", "FormFileAction", "CreateRelationButton1", "CreateRelationButton2", "CreateITSMEntrynButton", "rejectNow"];
    
    // Verify that the result is an array with the expected keys in the first element
    if (is_array($result) && isset($result[0])) {
        foreach ($expectedKeys as $key) {
            if (!array_key_exists($key, $result[0])) {
                return "Fail: Missing key '$key' in the result.";
            }
        }
        return "Pass";
    }
    return "Fail: Unexpected result format or structure.";
}

function getRequestFormView($SessionUserID, $ITSMTypeID, $ITSMID, $FormID, $languageshort, $ModalType, $UsersGroups)
{
    global $conn;
    global $functions;
   
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

    if (!$FormID) {
        $FormID = $functions->getITSMFormID($ITSMID, $ITSMTableName);
    }

    $FormName = $functions->translate("Request") . ": " . $functions->getFormNameFromFormID($FormID);

    $Definition1Final = "<div class=\"card\"><div class=\"card-body\"><div class=\"row\"><small>$FormName</small></div><br><div class=\"row\">";
    $sql = "SELECT forms_fieldslist.FieldName, forms_fieldslist.FieldLabel, forms_fieldslist_types.Definition, forms_fieldslist.FieldDefaultValue, forms_fieldslist.fieldtitle, forms_fieldslist.SelectFieldOptions, forms_fieldslist.FieldWidth,
          forms_fieldslist.GroupFilterOptions, forms_fieldslist.LookupTable, forms_fieldslist.Hidden, forms_fieldslist.FieldType, forms_fieldslist.LookupFieldResultView,forms_fieldslist.HideForms,forms_fieldslist.Required,forms_fieldslist.LockedCreate,forms_fieldslist.LockedView,forms_fieldslist.Addon, forms_fieldslist.AddEmpty, forms_fieldslist.FullHeight
          FROM forms
          LEFT JOIN forms_fieldslist ON forms.ID = forms_fieldslist.RelatedFormID
          LEFT JOIN forms_fieldslist_types ON forms_fieldslist.FieldType = forms_fieldslist_types.ID
          WHERE forms.ID = ?
          ORDER BY forms_fieldslist.FieldOrder ASC";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("s", $FormID);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = mysqli_fetch_array($result)) {
        $LookupTable = $row["LookupTable"];
        $LookupField = "ID";
        $FieldType = $row["FieldType"];
        $LookupFieldResult = $row["LookupFieldResultView"];
        $FieldLabel = $row["FieldLabel"];
        $FieldName = $row["FieldName"];
        $FieldDefaultValue = $row["FieldDefaultValue"];
        $FieldTitle = $row["fieldtitle"];
        $AddEmpty = $row["AddEmpty"];
        $FullHeight = $row["FullHeight"];
        $FieldWidth = $row["FieldWidth"];
        $HideForms = $row["HideForms"];
        $LockedCreate = $row["LockedCreate"];
        $LockedView = $row["LockedView"];
        $Hidden = $row["Hidden"];
        $GroupFilterOptions = $row["GroupFilterOptions"];

        if($GroupFilterOptions){
            // Split the string by '#' to get an array of group IDs
            $groupFilterArray = explode('#', $GroupFilterOptions);

            // Iterate through the group filter array and check if any group ID exists in the user groups array
            foreach ($groupFilterArray as $groupID) {
                if (in_array($groupID, $UsersGroups)) {
                    $Hidden = "0";
                } else {
                    $Hidden = "1";
                }
            }
        }

        if ($Hidden == "1") {
            $Hidden = "hidden";
        }

        $SelectFieldOptions = $row["SelectFieldOptions"];
        $SelectFieldOptionsPre = "";
        if ($SelectFieldOptions) {
            if ($AddEmpty == "1") {
                $SelectFieldOptionsPre = "<option></option>";
            }
            $SelectFieldOptions = str_replace("#", "", $SelectFieldOptions);
            $SelectFieldOptions = translateOptions($SelectFieldOptions, $FieldDefaultValue);
            $SelectFieldOptions = $SelectFieldOptionsPre . $SelectFieldOptions;
        }

        if ($HideForms == "1") {
            continue;
        }

        if ($FullHeight == "1") {
            $Height = "100%";
        } else {
            $Height = "150px";
        }

        $Required = $row["Required"];
        if ($Required == "1") {
            $Required = "required";
            $RequiredLabel = "<code>*</code>";
        } else {
            $Required = "";
            $RequiredLabel = "";
        }

        if ($ModalType == "Create") {
            $Locked = $LockedCreate;
        } else {
            $Locked = $LockedView;
        }
        
        if ($Locked == "1") {
            $Locked = "disabled";
        } else {
            $Locked = "";
        }

        $Addon = $row["Addon"];
        if ($Addon) {
            $addonBtn = getModuleFieldAddonBtn($Addon);
            $addonBtn = str_replace("<:FieldName:>", $FieldName, $addonBtn);
        } else {
            $addonBtn = "";
        }

        $Definition1 = $row["Definition"];

        // Add condition for FieldType 3 to set the checkbox as checked if FieldDefaultValue is true
        if ($FieldType == "3") {
            $checked = $FieldDefaultValue ? "checked" : "";
            $Definition1 = str_replace("<:checked:>", $checked, $Definition1);
        } else {
            $Definition1 = str_replace("<:checked:>", "", $Definition1);
        }
        
        $Definition1 = str_replace("<:fieldname:>", $FieldName, $Definition1);
        $Definition1 = str_replace("<:fieldvalue:>", $FieldDefaultValue, $Definition1);
        $Definition1 = str_replace("<:fieldid:>", $FieldName, $Definition1);
        $Definition1 = str_replace("<:label:>", $FieldLabel, $Definition1);
        $Definition1 = str_replace("<:fieldtitle:>", $FieldTitle, $Definition1);
        $Definition1 = str_replace("<:required:>", $Required, $Definition1);
        $Definition1 = str_replace("<:requiredlabel:>", $RequiredLabel, $Definition1);
        $Definition1 = str_replace("<:Locked:>", $Locked, $Definition1);
        $Definition1 = str_replace("<:addonBtn:>", " " . $addonBtn, $Definition1);
        $Definition1 = str_replace("<:fieldwidth:>", $FieldWidth, $Definition1);
        $Definition1 = str_replace("<:height:>", $Height, $Definition1);
        $Definition1 = str_replace("<:hidden:>", $Hidden, $Definition1);

        if (!empty($LookupTable)) {
            $SelectFieldOptions = "";
            $SelectFieldOptions = getFormsLookupTableSelectOptions($LookupTable, $LookupField, $LookupFieldResult, $FormID, $FieldName, $AddEmpty);
        }
        $Definition1 = str_replace("<:selectoptions:>", $SelectFieldOptions, $Definition1);
        $Definition1 = str_replace("<:languagecode:>", $languageshort, $Definition1);
        $Definition1Final .= $Definition1;
    }
    $Definition1Final .= "</div></div></div>";
    $FieldsArray[] = array("Definition" => $Definition1Final);

    return $FieldsArray;
}

function testGetRequestFormView()
{
    // Sample inputs to test the function
    $SessionUserID = 1;
    $ITSMTypeID = 2;
    $ITSMID = 1;
    $FormID = '123';
    $languageshort = 'en';
    $ModalType = 'Create';
    $UsersGroups = [100001, 100015];

    // Call the function
    $result = getRequestFormView($SessionUserID, $ITSMTypeID, $ITSMID, $FormID, $languageshort, $ModalType, $UsersGroups);

    // Check if result is an array with at least one entry
    if (is_array($result) && isset($result[0]) && isset($result[0]['Definition'])) {
        $definitionContent = $result[0]['Definition'];

        // Check for expected HTML structure elements
        if (
            strpos($definitionContent, "<div class=\"card\">") !== false &&
            strpos($definitionContent, "<div class=\"card-body\">") !== false &&
            strpos($definitionContent, "<div class=\"row\">") !== false &&
            strpos($definitionContent, "<small>") !== false
        ) {
            return "Pass";
        } else {
            return "Fail: Expected HTML structure not found.";
        }
    }

    return "Fail: Result is not in the expected array structure or missing 'Definition'.";
}

function getITSMAsMailWithPDFFields($ITSMTypeID, $ITSMID, $SessionUserID, $ModuleType, $UserLanguageCode, $Type, $group_array)
{
    global $functions;

    try {
        $getPDF = "1";
        $mergedArray = [];
        $htmlContent = "";

        $ITSMFormID = getITSMRelatedFormID($ITSMTypeID, $ITSMID);

        if ($ITSMFormID !== "") {
            $FieldsArray1 = getRequestFormFieldValues($ITSMTypeID, $ITSMID, $getPDF);
            if (!empty($FieldsArray1)) {
                $FieldsArray1[] = ["FieldLabel" => "/n", "FieldValue" => "/n"];
                foreach ($FieldsArray1 as $item) {
                    $mergedArray[] = extractITSMFormFieldsArray($item);
                }
            }
        }

        $FieldsArray2 = getITSMFieldsValues($ITSMTypeID, $ITSMID, $ModuleType, $UserLanguageCode, $Type, $group_array, $getPDF);

        foreach ($FieldsArray2 as $item) {
            $mergedArray[] = extractITSMFormFieldsArray($item);
        }

        $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
        $htmlContent .= "<br><br><br><br><h1>$ITSMTypeName: $ITSMID</h1>";

        foreach ($mergedArray as $field) {
            if (!empty($field["FieldLabel"])) {
                if ($field["FieldLabel"] == "/n") {
                    $htmlContent .= "<br>";
                } else {
                    $fieldValue = $functions->translate(convertOnClickToHref($field["FieldValue"]));
                    $fieldLabel = $functions->translate($field["FieldLabel"]);
                    $htmlContent .= '<b>' . htmlspecialchars($fieldLabel, ENT_QUOTES) . ': </b>' . $fieldValue . '<br>';
                }
            }
        }

        return ["htmlContent" => $htmlContent, "mergedArray" => $mergedArray];
    } catch (Exception $e) {
        $functions->errorlog("Error in getITSMAsMailWithPDFFields: " . $e->getMessage(), "getITSMAsMailWithPDFFields");
        return ["htmlContent" => "", "mergedArray" => []];
    }
}

function extractITSMFormFieldsArray($item)
{
    return array(
        "FieldLabel" => $item["FieldLabel"],
        "FieldValue" => $item["FieldValue"]
    );
}

function getITSMRelatedFormID($ITSMTypeID, $ITSMID)
{
    global $functions;

    // Retrieve the table name
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

    // Check if the `RelatedFormID` column exists in the table
    $testSql = "SHOW COLUMNS FROM `$ITSMTableName` LIKE 'RelatedFormID'";
    $testResult = $functions->selectQuery($testSql, []);

    if (empty($testResult)) {
        // Column does not exist, return a default value
        return "";
    }

    // Proceed with retrieving the RelatedFormID if the column exists
    $sql = "SELECT RelatedFormID FROM `$ITSMTableName` WHERE ID = ?";
    $result = $functions->selectQuery($sql, [$ITSMID]);

    // Return the RelatedFormID if found
    if (!empty($result)) {
        return $result[0]["RelatedFormID"] ?? "";
    }

    // Return default value if the query fails or RelatedFormID is empty
    return "";
}

function getITSMFieldSelectOptions($FieldID)
{
    global $functions;

    // SQL query to fetch field options
    $sql = "SELECT SelectFieldOptions, AddEmpty, FieldDefaultValue
            FROM itsm_fieldslist
            WHERE ID = ?";

    // Execute the query using selectQuery
    $result = $functions->selectQuery($sql, [$FieldID]);

    // Initialize the SelectFieldOptions variable
    $SelectFieldOptions = "";

    if (!empty($result)) {
        $row = $result[0]; // Extract the first row since only one row is expected
        $SelectFieldOptions = $row["SelectFieldOptions"];
        $AddEmpty = $row["AddEmpty"];
        $FieldDefaultValue = $row["FieldDefaultValue"];
        $SelectFieldOptionsPre = "";

        if ($SelectFieldOptions) {
            // Add an empty option if required
            if ($AddEmpty == "1") {
                $SelectFieldOptionsPre = "<option value=\"\"></option>";
            }

            // Clean and translate the options
            $SelectFieldOptions = str_replace("#", "", $SelectFieldOptions);
            $SelectFieldOptions = translateOptions($SelectFieldOptions, $FieldDefaultValue);

            // Prepend the empty option if applicable
            $SelectFieldOptions = $SelectFieldOptionsPre . $SelectFieldOptions;
        }
    }

    return $SelectFieldOptions;
}

function getFormFieldSelectOptions($FieldID)
{
    global $functions;

    // SQL query to fetch field options
    $sql = "SELECT SelectFieldOptions, AddEmpty, FieldDefaultValue
            FROM forms_fieldslist
            WHERE ID = ?";

    // Execute the query using selectQuery
    $result = $functions->selectQuery($sql, [$FieldID]);

    // Initialize the SelectFieldOptions variable
    $SelectFieldOptions = "";

    if (!empty($result)) {
        $row = $result[0]; // Extract the first row since only one row is expected
        $SelectFieldOptions = $row["SelectFieldOptions"];
        $AddEmpty = $row["AddEmpty"];
        $FieldDefaultValue = $row["FieldDefaultValue"];
        $SelectFieldOptionsPre = "";

        if ($SelectFieldOptions) {
            // Add an empty option if required
            if ($AddEmpty == "1") {
                $SelectFieldOptionsPre = "<option value=\"\"></option>";
            }

            // Clean and translate the options
            $SelectFieldOptions = str_replace("#", "", $SelectFieldOptions);
            $SelectFieldOptions = translateOptions($SelectFieldOptions, $FieldDefaultValue);

            // Prepend the empty option if applicable
            $SelectFieldOptions = $SelectFieldOptionsPre . $SelectFieldOptions;
        }
    }

    return $SelectFieldOptions;
}

function getITSMFieldsValues($ITSMTypeID, $ITSMID, $ModuleType, $UserLanguageCode, $Type, $group_array, $getPDF)
{
    global $functions;

    try {
        // Retrieve ITSM Table Information
        $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
        $RelationFieldName = $functions->getITSMRelationShowField($ITSMTypeID);
        $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);

        $FieldsArray = [];

        // Use selectQuery to get columns
        $columns = $functions->selectQuery("SHOW COLUMNS FROM $ITSMTableName", []);
        if (empty($columns)) {
            throw new Exception("No columns found for table: $ITSMTableName");
        }

        foreach ($columns as $column) {
            $FieldName = $column["Field"];
            $FieldLabel = $functions->getITSMFieldLabel($ITSMTypeID, $FieldName);
            $FieldValue = $functions->getITSMFieldValue($ITSMID, $FieldName, $ITSMTableName);
            $FieldType = $functions->getITSMFieldTypeID($ITSMTypeID, $FieldName);
            $FieldID = $functions->getITSMFieldID($FieldName, $ITSMTypeID);

            // Handle PDF-specific logic for FieldType 4
            if ($FieldType == "4" && $FieldValue !== "" && $getPDF == "1") {
                // Sanitize and clean up the field value
                $FieldValue = strip_tags(str_replace(
                    ['<b>', '</b>', '<i>', '</i>', '<br>', '<br/>', '<br />', '<p>', '</p>'],
                    ['**', '**', '*', '*', "\n", "\n", "\n", '', "\n\n"],
                    $FieldValue
                ));

                // Handle FieldSelectOptions or lookup table
                $FieldSelectOptions = getITSMFieldSelectOptions($FieldID);
                if (empty($FieldSelectOptions)) {
                    $FieldLookUpTable = $functions->getITSMFieldLookUpTable($FieldID);
                    $FieldLookupField = $functions->getITSMFieldLookUpField($FieldID);
                    $FieldResultView = $functions->getITSMFieldLookUpFieldResultView($FieldID);

                    if (!empty($FieldLookUpTable) && !empty($FieldLookupField) && !empty($FieldResultView)) {
                        $lookupSql = "SELECT $FieldResultView FROM $FieldLookUpTable WHERE $FieldLookupField = ?";
                        $lookupResult = $functions->selectQuery($lookupSql, [$FieldValue]);
                        if (!empty($lookupResult)) {
                            $FieldValue = $lookupResult[0][$FieldResultView] ?? $FieldValue;
                        }
                    }
                }
            }

            // Check if field should be hidden
            $HideField = $functions->getITSMFieldHideStatus($FieldName, $ITSMTypeID);
            if ($HideField == "1") {
                continue;
            }

            // Format field value for datetime fields
            if ($FieldType == "5" && !empty($FieldValue)) {
                $FieldValue = $functions->convertToDanishDateTimeFormat($FieldValue);
            }

            $RelationField = "0";
            // Handle RelationField            
            if ($FieldName === $RelationFieldName) {
                $RelationField = $functions->translate($ITSMTypeName) . " $ITSMID: " . $FieldValue;
            }

            // Add field to FieldsArray
            $FieldsArray[] = [
                "ModuleType" => $ModuleType,
                "LanguageCode" => $UserLanguageCode,
                "FieldType" => $FieldType,
                "FieldName" => $FieldName,
                "FieldValue" => $FieldValue,
                "RelationField" => $RelationField,
                "ITSMTypeName" => $ITSMTypeName,
                "FieldLabel" => $FieldLabel,
            ];
        }

        return $FieldsArray;
    } catch (Exception $e) {
        // Log the error and return an empty array
        $functions->errorlog("Error in getITSMFieldsValues: " . $e->getMessage(), "getITSMFieldsValues");
        return [];
    }
}

function sanitizeHtmlPurifier($html) {
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($html);
}

function getRequestFormFieldValues($ITSMTypeID, $ITSMID, $getPDF)
{
    global $functions;

    try {
        // Initialize variables
        $FieldsArray = [];
        $Excludes = ["ID", "RelatedRequestID"];

        // Fetch necessary details
        $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
        $ITSMFormID = $functions->getITSMFormID($ITSMID, $ITSMTableName);
        $ITSMFormsTableName = $functions->getITSMFormsTableName($ITSMFormID);
        $FormsRowID = $functions->getITSMFormsRowID($ITSMFormsTableName, $ITSMID);

        // Fetch columns from the form's table
        $columnsSql = "SHOW COLUMNS FROM $ITSMFormsTableName";
        $columns = $functions->selectQuery($columnsSql, []);

        foreach ($columns as $row) {
            $FieldName = $row["Field"];

            if (!in_array($FieldName, $Excludes)) {
                $FieldValue = $functions->getITSMFieldValue($FormsRowID, $FieldName, $ITSMFormsTableName);
                $FieldType = $functions->getITSMFormsFieldTypeID($ITSMFormID, $FieldName);
                $FieldLabel = $functions->getFormsFieldLabel($ITSMFormID, $FieldName);
                $FieldID = $functions->getFormFieldID($FieldName, $ITSMFormID);

                // Handle PDF-specific processing
                if ($FieldType == "4" && $FieldValue !== "" && $getPDF == "1") {
                    $FieldSelectOptions = getFormFieldSelectOptions($FieldID);

                    if (empty($FieldSelectOptions)) {
                        $FieldLookUpTable = $functions->getFormFieldLookUpTable($FieldID);
                        $FieldLookupField = $functions->getFormFieldLookUpField($FieldID);
                        $FieldResultView = $functions->getFormFieldLookUpFieldResultView($FieldID);

                        $lookupSql = "SELECT $FieldResultView
                                      FROM $FieldLookUpTable
                                      WHERE $FieldLookupField = ?";
                        $lookupResult = $functions->selectQuery($lookupSql, [$FieldValue]);

                        if (!empty($lookupResult)) {
                            $FieldValue = $lookupResult[0]["$FieldResultView"];
                        }
                    }
                }

                $FieldsArray[] = [
                    "FieldType" => $FieldType,
                    "FieldName" => $FieldName,
                    "FieldLabel" => $FieldLabel,
                    "FieldValue" => $FieldValue
                ];
            }
        }

        return $FieldsArray;
    } catch (Exception $e) {
        // Log the error and return an empty array
        $functions->errorlog("Error in getRequestFormFieldValues: " . $e->getMessage(), "getRequestFormFieldValues");
        return [];
    }
}

function getFirstActiveAPIKey()
{
    global $conn;
    global $functions;
    
    $api_key = "";

    $sql = "SELECT `api_key`
            FROM `api_keys`
            WHERE Status = 1
            LIMIT 1;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $api_key = $row["api_key"];
    }

    return $api_key;
}

function getRelatedITSMModuleFromStatusID($StatusID)
{
    global $conn;
    global $functions;

    $ModuleID = "";

    $sql = "SELECT ModuleID, StatusCode
            FROM itsm_statuscodes
            WHERE ID = $StatusID;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $ModuleID = $row["ModuleID"];
        $StatusCode = $row["StatusCode"];
        $ReturnArray[] = array("ModuleID" => $ModuleID, "StatusCode" => $StatusCode);
    }

    return $ReturnArray;
}

function deleteRelatedSLAMatrixEntries($StatusID)
{
    global $conn;
    global $functions;

    // Retrieve ModuleID and StatusCode using the getRelatedITSMModuleFromStatusID function
    $RelatedModule = getRelatedITSMModuleFromStatusID($StatusID);

    // Extract ModuleID and StatusCode from the returned array
    $ModuleID = $RelatedModule[0]["ModuleID"];
    $StatusCode = $RelatedModule[0]["StatusCode"];

    // Prepare the DELETE query
    $sql = "DELETE FROM itsm_sla_matrix
            WHERE RelatedModuleID = $ModuleID AND Status = $StatusCode";

    // Execute the DELETE query
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function deactivate2FA($UserID)
{
    global $conn;
    global $functions;

    $sql = "UPDATE users SET google_secret_code = '0Km#9kQyfI1CLkthWhDb#F', QRUrl = ''
            WHERE users.ID = $UserID";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getCMDBNameFromTableName($TableName)
{
    global $conn;
    global $functions;

    $Name = "";

    $sql = "SELECT Name
            FROM cmdb_cis
            WHERE TableName = '$TableName'";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $Name = $row["Name"];
    }

    return $Name;
}

function getCMDBFieldLabelFromFieldName($FieldName, $RelCITypeID)
{
    global $conn;
    global $functions;

    $Name = "";

    $sql = "SELECT FieldLabel
            FROM cmdb_ci_fieldslist
            WHERE FieldName = '$FieldName' AND RelatedCITypeID = $RelCITypeID";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldLabel = $row["FieldLabel"];
    }

    return $FieldLabel;
}

function processSelectFieldOptions($tableName)
{
    global $conn;
    global $functions;
    $count = 0; // Initialize count variable

    // Prepare the SQL query to select records with non-empty SelectFieldOptions
    $sql = "SELECT ID, SelectFieldOptions FROM $tableName WHERE SelectFieldOptions IS NOT NULL AND SelectFieldOptions <> ''";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        // Query failed
        echo "Error: " . mysqli_error($conn);
        return;
    }

    // Fetch each row from the result set
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row['ID'];
        $selectFieldOptions = $row['SelectFieldOptions'];

        // Check if SelectFieldOptions is base64 encoded
        if (base64_decode($selectFieldOptions, true) !== false) {
            // Decode the base64 encoded string
            $decodedOptions = base64_decode($selectFieldOptions);

            // Check if the decoding resulted in a valid string
            if ($decodedOptions !== false) {
                // Replace the SelectFieldOptions in the database with the decoded options
                $decodedOptions = explode("</option>", $decodedOptions);
                $decodedOptions = implode("</option>#", $decodedOptions);
                $decodedOptions = rtrim($decodedOptions, '#');
                
                $updateSql = "UPDATE $tableName SET SelectFieldOptions = '$decodedOptions' WHERE ID = $id";
                $updateResult = mysqli_query($conn, $updateSql);

                if ($updateResult) {
                    // Increment count if update was successful
                    $count++;
                } else {
                    // Update query failed
                    echo "Error updating SelectFieldOptions: " . mysqli_error($conn);
                    return;
                }
            } else {
                // Base64 decoding failed
                echo "Error decoding SelectFieldOptions from base64: ID $id in table $tableName";
                continue; // Move to the next iteration
            }
        }
    }

    // Return count along with success message
    return $count;
}

function getITSMRelationInfo($RelationID)
{
    global $conn;
    global $functions;
    $count = 0; // Initialize count variable

    // Prepare the SQL query to select records with non-empty SelectFieldOptions
    $sql = "SELECT `Table1`, `Table2`, `ID1`, `ID2`
            FROM `itsm_relations`
            WHERE ID = $RelationID";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        // Query failed
        echo "Error: " . mysqli_error($conn);
        return;
    }

    // Fetch each row from the result set
    while ($row = mysqli_fetch_assoc($result)) {
        $Table1 = $row['Table1'];
        $Table2 = $row['Table2'];
        $ID1 = $row['ID1'];
        $ID2 = $row['ID2'];
        $ResultArray[] = array("Table1" => $Table1, "Table2" => $Table2, "ID1" => $ID1, "ID2" => $ID2);
    }

    // Return count along with success message
    return $ResultArray;
}

function deleteITSMRelation($RelationID)
{
    global $conn;
    global $functions;

    $sql = "DELETE FROM `itsm_relations` WHERE ID = $RelationID";

    mysqli_query($conn, $sql);
}

function checkRelationShowField($TypeID, $Type)
{
    global $conn;
    global $functions;

    switch ($Type) {
        case "ci":
            $RelatedTypeID = "RelatedCITypeID";
            $Table = "cmdb_ci_fieldslist";
            break;
        case "form":
            $RelatedTypeID = "RelatedFormID";
            $Table = "forms_fieldslist";
            break;
        default:
            $RelatedTypeID = "RelatedTypeID";
            $Table = "itsm_fieldslist";
    }

    $sql = "SELECT COUNT(*) as count FROM $Table WHERE $RelatedTypeID = ? AND RelationShowField = 1";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $functions->errorlog("Prepare failed: " . $conn->error, "checkRelationShowField");
        return false;
    }

    $stmt->bind_param("i", $TypeID);
    if (!$stmt->execute()) {
        $functions->errorlog("Execute failed: " . $stmt->error, "checkRelationShowField");
        return false;
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['count'];
}

function getCurrentRelationShowField($fieldID,$Type) {
    global $conn;
    global $functions;

    switch ($Type) {
    case "ci":
        $Table = "cmdb_ci_fieldslist";
        break;
    case "form":
        $Table = "forms_fieldslist";
        break;
    default:
        $Table = "itsm_fieldslist";
    }

    $sql = "SELECT RelationShowField FROM $Table WHERE ID = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $functions->errorlog("Prepare failed: " . $conn->error, "getCurrentRelationShowField");
        return null;
    }

    $stmt->bind_param("i", $fieldID);
    if (!$stmt->execute()) {
        $functions->errorlog("Execute failed: " . $stmt->error, "getCurrentRelationShowField");
        return null;
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['RelationShowField'];
}

function removeOtherRelationShowField($Type,$TypeID) {
    global $conn;
    global $functions;

    switch ($Type) {
        case "ci":
            $RelatedTypeID = "RelatedCITypeID";
            $Table = "cmdb_ci_fieldslist";
            break;
        case "form":
            $RelatedTypeID = "RelatedFormID";
            $Table = "forms_fieldslist";
            break;
        default:
            $RelatedTypeID = "RelatedTypeID";
            $Table = "itsm_fieldslist";
    }

    $sql = "UPDATE $Table SET RelationShowField = '0' WHERE $RelatedTypeID = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $functions->errorlog("Prepare failed: " . $conn->error, "removeOtherRelationShowField");
        return null;
    }

    $stmt->bind_param("i", $TypeID);
    if (!$stmt->execute()) {
        $functions->errorlog("Execute failed: " . $stmt->error, "removeOtherRelationShowField");
        return null;
    }

    $stmt->close();
}

function getUserTransferObjectsCMDB($UserID)
{
    global $conn;
    global $functions;

    $ciTypes = array();

    $sql = "SELECT `ID`, `TableName`, `Description`
            FROM `cmdb_cis`
            WHERE `Active` = 1;";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $functions->errorlog("Prepare failed: " . $conn->error, "getUserTransferObjectsCMDB");
        return;
    }

    if (!$stmt->execute()) {
        $functions->errorlog("Execute failed: " . $stmt->error, "getUserTransferObjectsCMDB");
        return;
    }

    $result = $stmt->get_result();

    if (!$result) {
        $functions->errorlog("Getting result set failed: " . $stmt->error, "getUserTransferObjectsCMDB");
        return;
    }

    while ($row = $result->fetch_assoc()) {
        $ciTypes[] = $row;
    }

    $result->free();
    $stmt->close();

    return $ciTypes;
}

function getUserTransferObjectsITSM($UserID)
{
    global $conn;
    global $functions;

    $itsmTypes = array();

    $sql = "SELECT `ID`, `TableName`, `Name`
            FROM `itsm_modules`
            WHERE `Active` = 1 AND ID != 6 AND ID != 13;";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $functions->errorlog("Prepare failed: " . $conn->error, "getUserTransferObjectsCMDB");
        return;
    }

    if (!$stmt->execute()) {
        $functions->errorlog("Execute failed: " . $stmt->error, "getUserTransferObjectsCMDB");
        return;
    }

    $result = $stmt->get_result();

    if (!$result) {
        $functions->errorlog("Getting result set failed: " . $stmt->error, "getUserTransferObjectsCMDB");
        return;
    }

    while ($row = $result->fetch_assoc()) {
        // Translate the Name column
        $row['Name'] = $functions->translate($row['Name']);
        $itsmTypes[] = $row;
    }

    $result->free();
    $stmt->close();

    return $itsmTypes;
}

function getUserTransferObjectsOther($UserID)
{
    global $conn;
    global $functions;

    $otherTypes = array();

    $otherTypes[] = array("ID" => "1", "TableName" => "taskslist", "Name" => $functions->translate("KanBan Tasks"));
    $otherTypes[] = array("ID" => "2", "TableName" => "workflowsteps", "Name" => $functions->translate("Workflow Tasks"));
    $otherTypes[] = array("ID" => "3", "TableName" => "projects", "Name" => $functions->translate("Projects"));
    $otherTypes[] = array("ID" => "4", "TableName" => "project_tasks", "Name" => $functions->translate("Project Tasks"));


    return $otherTypes;
}

function getUserTransferObjectsMemberShips($UserID)
{
    global $conn;
    global $functions;

    $otherTypes = array();

    $otherTypes[] = array("ID" => "1", "TableName" => "usersroles", "Name" => $functions->translate("Roles"));
    $otherTypes[] = array("ID" => "2", "TableName" => "usersgroups", "Name" => $functions->translate("Groups"));

    return $otherTypes;
}

function getUsersArray()
{
    global $conn;
    global $functions;

    $users = array();

    $sql = "SELECT ID, CONCAT(Firstname,' ',Lastname,' (',Username,')') AS Name
            FROM users
            WHERE Active = 1 AND RelatedUserTypeID IN (1,3)
            ORDER BY Firstname ASC;";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $functions->errorlog("Prepare failed: " . $conn->error, "getUserTransferObjectsCMDB");
        return;
    }

    if (!$stmt->execute()) {
        $functions->errorlog("Execute failed: " . $stmt->error, "getUserTransferObjectsCMDB");
        return;
    }

    $result = $stmt->get_result();

    if (!$result) {
        $functions->errorlog("Getting result set failed: " . $stmt->error, "getUserTransferObjectsCMDB");
        return;
    }

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    $result->free();
    $stmt->close();

    return $users;
}

function transferCMDBItems($item, $UserIDToMoveFrom, $UserToMoveTo, $UserSessionID)
{
    global $conn;
    global $functions;

    $CITypeID = $item["fieldvalue"];
    $CITableName = getCITableName($CITypeID);
    $Name = getCINameFromTypeID($CITypeID);
    $FromName = $functions->getUserFullName($UserIDToMoveFrom);
    $ToName = $functions->getUserFullName($UserToMoveTo);

    // First, select the IDs of the rows that will be affected
    $selectSQL = "SELECT ID FROM $CITableName
                WHERE RelatedUserID = $UserIDToMoveFrom AND Active = 1;";
    $result = mysqli_query($conn, $selectSQL);

    if (!$result) {
        // Handle error if needed
        error_log("Error selecting IDs: " . mysqli_error($conn));
        return false;
    }

    // Collect the IDs
    $affectedIDs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $affectedIDs[] = $row['ID'];
    }

    // Perform the update
    $updateSQL = "UPDATE $CITableName SET RelatedUserID = $UserToMoveTo 
                WHERE RelatedUserID = $UserIDToMoveFrom AND Active = 1;";

    mysqli_query($conn, $updateSQL);

    if (mysqli_affected_rows($conn) > 0) {
        // Loop through the affected IDs and call createCILogEntry
        foreach ($affectedIDs as $ID) {
            $LogActionText = "$Name: Responsible changed from $FromName to $ToName;";
            createCILogEntry($ID, $CITypeID, $UserSessionID, $LogActionText);
        }
    }

    return true;
}

function transferITSMItems($item, $UserIDToMoveFrom, $UserToMoveTo, $UserSessionID)
{
    global $conn;
    global $functions;

    $ITSMTypeID = $item["fieldvalue"];
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
    $Name = getITSMNameFromITSMType($ITSMTypeID);
    
    $FromName = $functions->getUserFullName($UserIDToMoveFrom);
    $ToName = $functions->getUserFullName($UserToMoveTo);
    $ClosedStatus = $functions->getITSMClosedStatus($ITSMTypeID);
    // Convert the array to a comma-separated string
    $ClosedStatusString = "'" . implode("', '", $ClosedStatus) . "'";

    // First, select the IDs of the rows that will be affected
    $selectSQL = "SELECT ID FROM $ITSMTableName
                WHERE Responsible = $UserIDToMoveFrom AND Status NOT IN ($ClosedStatusString);";

    $result = mysqli_query($conn, $selectSQL);

    if (!$result) {
        // Handle error if needed
        error_log("Error selecting IDs: " . mysqli_error($conn));
        return false;
    }

    // Collect the IDs
    $affectedIDs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $affectedIDs[] = $row['ID'];
    }

    // Perform the update
    $updateSQL = "UPDATE $ITSMTableName SET Responsible = $UserToMoveTo
                WHERE Responsible = $UserIDToMoveFrom AND Status NOT IN ($ClosedStatusString);";

    mysqli_query($conn, $updateSQL);

    if (mysqli_affected_rows($conn) > 0) {
        // Loop through the affected IDs and call createCILogEntry
        foreach ($affectedIDs as $ID) {
            $LogActionText = "$Name: Responsible changed from $FromName to $ToName";
            createITSMLogEntry($ID, $ITSMTypeID, $UserSessionID, $LogActionText);
        }
    }

    return true;
}

function transferOthersItems($item, $UserIDToMoveFrom, $UserToMoveTo, $UserSessionID)
{
    global $conn;
    global $functions;
    
    $TypeID = $item["fieldvalue"];

    switch ($TypeID) {
        case "1":
            //KanBan tasks
            $sql = "UPDATE taskslist SET RelatedUserID = $UserToMoveTo WHERE RelatedUserID = $UserIDToMoveFrom AND Status != 4;";
            mysqli_query($conn, $sql);
            break;
        case "2":
            //Workflow tasks
            $sql = "UPDATE workflowsteps SET RelatedUserID = $UserToMoveTo WHERE RelatedUserID = $UserIDToMoveFrom AND RelatedStatusID != 3;";
            mysqli_query($conn, $sql);
            break;
        case "3":
            //Projects
            $sql = "UPDATE projects SET ProjectManager = $UserToMoveTo WHERE ProjectManager = $UserIDToMoveFrom AND Status NOT IN (7,8);";
            mysqli_query($conn, $sql);
            break;
        case "4":
            //Project Tasks
            $sql = "UPDATE project_tasks SET Responsible = $UserToMoveTo WHERE Responsible = $UserIDToMoveFrom AND Status != 7;";
            mysqli_query($conn, $sql);
            break;
        default:
            return true;
    }
    
    return true;
}

function transferMS($item, $UserIDToMoveFrom, $UserToMoveTo, $UserSessionID)
{
    global $conn;
    global $functions;
    $TypeID = $item["fieldvalue"];

    switch ($TypeID) {
        case "1":
            //Roles
            $sql = "SELECT RoleID FROM usersroles WHERE UserID = $UserIDToMoveFrom;";
            $result = mysqli_query($conn, $sql);

            if (!$result) {
                error_log("Query failed: " . mysqli_error($conn));
                return;
            }

            // Loop through each RoleID and run addUserIDToRole
            while ($row = mysqli_fetch_assoc($result)) {
                $RoleID = $row['RoleID'];                
                $RoleName = getUserRoleName($RoleID);
                $Status = addUserIDToRole($UserToMoveTo, $RoleID);
                if($Status){
                    $LogActionText = "User got added to Role: $RoleName";
                    $LogTypeID = 2;
                    createUserLogEntry($UserToMoveTo, $UserSessionID, $LogTypeID, $LogActionText);
                }
            }
            break;
        case "2":
            //Groups
            $sql = "SELECT GroupID FROM usersgroups WHERE UserID = $UserIDToMoveFrom;";
            $result = mysqli_query($conn, $sql);

            if (!$result) {
                error_log("Query failed: " . mysqli_error($conn));
                return;
            }

            // Loop through each RoleID and run addUserIDToRole
            while ($row = mysqli_fetch_assoc($result)) {
                $GroupID = $row['GroupID'];
                $GroupName = getUserGroupName($GroupID);
                $Status = addUserToGroup($UserToMoveTo, $GroupID);
                if ($Status) {
                    $LogActionText = "User got added to Group: $GroupName";
                    $LogTypeID = 2;
                    createUserLogEntry($UserToMoveTo, $UserSessionID, $LogTypeID, $LogActionText);
                }
            }
            break;
        default:
            return true;
    }
    
    return true;
}

function makeLookupFieldResultTable($FieldID, $Type){
    switch ($Type) {
        case "ci":
            $FieldName = getCIFieldNameFromFieldID($FieldID);
            $ConcatString = "CONCAT($FieldName.Firstname,' ',$FieldName.Lastname,' (',$FieldName.Username,')')";
            break;
        case "itsm":
            $FieldName = getITSMFieldNameFromFieldID($FieldID);
            $ConcatString = "CONCAT($FieldName.Firstname,' ',$FieldName.Lastname,' (',$FieldName.Username,')')";
            break;
        case "form":
            $FieldName = getFormFieldNameFromFieldID($FieldID);
            $ConcatString = "CONCAT($FieldName.Firstname,' ',$FieldName.Lastname,' (',$FieldName.Username,')')";
            break;
        default:
            return false;
    }

    return $ConcatString;
}

function makeLookupFieldResultView($FieldID, $Type)
{
    switch ($Type) {
        case "ci":
            $FieldName = getCIFieldNameFromFieldID($FieldID);
            $ConcatString = "CONCAT(Firstname,' ',Lastname,' (',Username,')')";
            break;
        case "itsm":
            $FieldName = getITSMFieldNameFromFieldID($FieldID);
            $ConcatString = "CONCAT(Firstname,' ',Lastname,' (',Username,')')";
            break;
        case "form":
            $FieldName = getFormFieldNameFromFieldID($FieldID);
            $ConcatString = "CONCAT(Firstname,' ',Lastname,' (',Username,')')";
            break;
        default:
            return false;
    }

    return $ConcatString;
}

function checkAndUpdateCertificateExpireDate() {
    global $conn;
    global $functions;

    $sql = "SELECT cmdb_ci_fieldslist.ID,cmdb_ci_fieldslist.FieldName
            FROM cmdb_ci_fieldslist
            LEFT JOIN cmdb_cis ON cmdb_ci_fieldslist.RelatedCITypeID = cmdb_cis.ID
            WHERE cmdb_cis.Active = '1' AND cmdb_ci_fieldslist.addon = '1';";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
        $FieldID = $row["ID"];
        $CITypeID = getCITypeIDFromFieldID($FieldID);
        $CITableName = getCITableName($CITypeID);
        $FieldName = $row["FieldName"];
        $FieldsArray[] = array("FieldID" => $FieldID, "FieldName" => $FieldName,"CITableName" => $CITableName,);
    }

    foreach ($FieldsArray as $key){
        $FieldID = $key["ID"];
        $CITableName = $key["CITableName"];
        $FieldName = $key["FieldName"];

        $sql2 = "SELECT ID, $FieldName AS Domain
                FROM $CITableName;";

        $result2 = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_array($result2)) {
            $Domain = $row["Domain"];
            $CIID = $row["ID"];
            $ExpireDate = getCertificateExpireDateAuto($Domain);

            if($ExpireDate){
                updateCIFieldValue($CITableName, $CIID, "EndDate", $ExpireDate);
            }
        }
    }
}

function updateCIFieldValue($CITableName, $CIID, $FieldName, $FieldValue)
{
    global $conn;
    global $functions;

    $sql = "UPDATE $CITableName
            SET $FieldName = '$FieldValue'
            WHERE ID = $CIID;";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

function getSelectOptionValue($LookupTable, $LookupField, $LookupFieldResult, $fieldValue, $AddEmpty)
{
    global $conn;
    global $functions;

    $Value = "";

    if ($AddEmpty == "1") {
        $Value .= "<option value=\"\"></option>"; // Add an empty option
    }

    $sql = "SELECT $LookupFieldResult AS Result
            FROM $LookupTable
            WHERE $LookupField = $fieldValue;";
    try {
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_array($result)) {
            $Value .= $row["Result"];
        }
    } catch (Exception $e) {
        // Log the error with the full SQL query for debugging
        $functions->errorlog($e->getMessage()." SQL: $sql", "getSelectOptionValue");
    }

    return $Value;
}

function updateITSMUpdatedDate($ITSMTypeID,$ITSMID){
    global $conn;
    global $functions;

    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

    $sql = "UPDATE $ITSMTableName SET LastUpdated = NOW() WHERE ID = $ITSMID;";

    mysqli_query($conn, $sql);
}

function checkReadStatus($commentID, $userID)
{
    global $conn;
    global $functions;

    $sql = "SELECT 1 FROM itsm_comments_readstatus WHERE CommentID = ? AND UserID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $commentID, $userID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $isRead = mysqli_stmt_num_rows($stmt) > 0 ? 1 : 0;

        mysqli_stmt_free_result($stmt);
        mysqli_stmt_close($stmt);
        return $isRead;
    } else {
        return 0; // Assuming unread if there's an error
    }
}

function getNumberOfUnreadITSMComments($ITSMTypeID, $ITSMID, $userID)
{
    global $conn;
    global $functions;

    $sql = "SELECT COUNT(*) AS unread_count
            FROM itsm_comments c
            LEFT JOIN itsm_comments_readstatus r ON c.ID = r.CommentID AND r.UserID = ?
            WHERE c.RelatedElementID = ? AND c.ITSMType = ? AND r.CommentID IS NULL";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iii", $userID, $ITSMID, $ITSMTypeID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $unreadCount);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return $unreadCount;
    } else {
        return 0; // Assuming 0 if there's an error
    }
}

function readITSMComments($ITSMTypeID, $ITSMID, $UserSessionID)
{
    global $conn;
    global $functions;

    $sql = "INSERT IGNORE INTO itsm_comments_readstatus (UserID, CommentID)
            SELECT ?, ID
            FROM itsm_comments
            WHERE RelatedElementID = ? AND ITSMType = ?;";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iii", $UserSessionID, $ITSMID, $ITSMTypeID);
        if (mysqli_stmt_execute($stmt)) {
        } else {
            $functions->errorlog("Error marking comments as read: " . mysqli_stmt_error($stmt), "readITSMComments");
        }
        mysqli_stmt_close($stmt);
    } else {
        $functions->errorlog("Statement preparation failed: " . mysqli_error($conn)," readITSMComments");
    }
}

function sendITSMMailTemplate($ITSMTypeID, $ITSMID, $CustomerID, $Comment, $TemplateID)
{
    global $functions;

    $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
    $ITSMTypeName = $functions->translate("$ITSMTypeName");
    $CustomerEmail = getUserEmailFromID($CustomerID);
    $CustomerFullName = $functions->getUserFullName($CustomerID);
    $CustomerFirstName = getUserFirstName($CustomerID);
    $CustomerLastName = getUserLastName($CustomerID);
    $SystemName = $functions->getSettingValue(13);
    $SystemURL = $functions->getSettingValue(17);
    $MailCode = getMailCodeForModule($ITSMTypeID);
    $MailTemplateSubject = getMailTemplateSubject($TemplateID);
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
    $ITSMSubject = $functions->getITSMFieldValue($ITSMID, "Subject", $ITSMTableName);

    $InternalViewLink = "<a href=\"$SystemURL/itsm-$ITSMTypeID-$ITSMID\">" . $functions->translate("Open") . " $ITSMTypeName: $ITSMID</a>";
    
    $MailTemplateSubject = str_replace("<:mailcode:>", $MailCode, $MailTemplateSubject);
    $MailTemplateSubject = str_replace("<:itsmnumber:>", $ITSMID, $MailTemplateSubject);
    $MailTemplateSubject = str_replace("<:systemname:>", $SystemName, $MailTemplateSubject);

    $MailTemplateContent = getMailTemplateContent($TemplateID);
    $MailTemplateContent = str_replace("<:firstname:>", $CustomerFirstName, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:lastname:>", $CustomerLastName, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:itsmnumber:>", $ITSMID, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:comment:>", $Comment, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:systemname:>", $SystemName, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:systemurl:>", $SystemURL, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:itsmtypename:>", $ITSMTypeName, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:internalviewlink:>", $InternalViewLink, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:subject:>", $ITSMSubject, $MailTemplateContent);

    sendMailToSinglePerson($CustomerEmail, $CustomerFullName, "$MailTemplateSubject", "$MailTemplateContent");
}

function sendSystemMailUserCreated($UserID,$TemplateID)
{
    global $functions;

    $CustomerEmail = getUserEmailFromID($UserID);
    $CustomerFullName = $functions->getUserFullName($UserID);
    $CustomerFirstName = getUserFirstName($UserID);
    $CustomerLastName = getUserLastName($UserID);
    $SystemName = $functions->getSettingValue(13);
    $SystemURL = $functions->getSettingValue(17);

    $MailTemplateSubject = getMailTemplateSubject($TemplateID);
    $MailTemplateSubject = str_replace("<:systemname:>", $SystemName, $MailTemplateSubject);

    $MailTemplateContent = getMailTemplateContent($TemplateID);
    $MailTemplateContent = str_replace("<:firstname:>", $CustomerFirstName, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:lastname:>", $CustomerLastName, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:systemname:>", $SystemName, $MailTemplateContent);
    $MailTemplateContent = str_replace("<:systemurl:>", $SystemURL, $MailTemplateContent);

    sendMailToSinglePerson($CustomerEmail, $CustomerFullName, "$MailTemplateSubject", "$MailTemplateContent");
}

function deleteITSMComment($commentID)
{

    global $conn;
    global $functions;

    $sql = "DELETE FROM itsm_comments
            WHERE ID = ?;";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $commentID);
        if (mysqli_stmt_execute($stmt)) {
        } else {
            $functions->errorlog("Error deleting comment: " . mysqli_stmt_error($stmt), "deleteITSMComment");
        }
        mysqli_stmt_close($stmt);
    } else {
        $functions->errorlog("Statement preparation failed: " . mysqli_error($conn), " deleteITSMComment");
    }
}

function updateITSMCOmment($commentID, $itsmComment)
{

    global $conn;
    global $functions;

    $sql = "UPDATE itsm_comments
            SET Text = ?
            WHERE ID = ?;";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $itsmComment,$commentID);
        if (mysqli_stmt_execute($stmt)) {
        } else {
            $functions->errorlog("Error deleting comment: " . mysqli_stmt_error($stmt), "deleteITSMComment");
        }
        mysqli_stmt_close($stmt);
    } else {
        $functions->errorlog("Statement preparation failed: " . mysqli_error($conn), " deleteITSMComment");
    }
}

// Function to process the options string
function translateOptions($optionsString, $FieldDefaultValue)
{
    global $functions;

    try {
        // Regular expression to match <option> elements
        $pattern = '/<option value="([^"]+)">([^<]+)<\/option>/';
        preg_match_all($pattern, $optionsString, $matches, PREG_SET_ORDER);

        // Array to hold the translated options
        $translatedOptions = [];

        foreach ($matches as $match) {
            $value = $match[1];
            $text = $match[2];
            
            // Translate the text
            $translatedText = $functions->translate($text);
            if ($translatedText === false) {
                throw new Exception("Translation failed for text: $text");
            }

            // Determine if this option should be selected
            $selected = ($value == $FieldDefaultValue) ? ' selected' : '';

            // Construct the new option element
            $translatedOptions[] = "<option value=\"$value\"$selected>$translatedText</option>";
        }

        // Combine the translated options back into a single string
        return implode('', $translatedOptions);

    } catch (Exception $e) {
        // Log the error with context
        $functions->errorlog($e->getMessage(), "translateOptions");
        return ''; // Return an empty string on failure
    }
}

function getGroupsInRole($RoleID)
{
    global $conn;
    global $functions;

    $GroupsArray = array();

    try {
        $sql = "SELECT GroupID FROM usergroupsroles WHERE RoleID = ?";
        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "i", $RoleID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $GroupsArray[] = $row['GroupID'];
        }

        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        $functions->errorlog("Database error: " . $e->getMessage(), "getGroupsInRole");
        return false;
    }

    return $GroupsArray;
}

function getGroupFilter($Type, $FieldID)
{
    global $conn;
    global $functions;

    switch ($Type) {
        case 'itsm':
            $sql = "SELECT GroupFilterOptions FROM itsm_fieldslist WHERE ID = ?";
            break;

        case 'ci':
            $sql = "SELECT GroupFilterOptions FROM cmdb_ci_fieldslist WHERE ID = ?";
            break;

        case 'form':
            $sql = "SELECT GroupFilterOptions FROM forms_fieldslist WHERE ID = ?";
            break;

        default:
            throw new Exception("Invalid Type: " . $Type);
    }
    try {

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param($stmt, "i", $FieldID);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $GroupFilterOptions = $row['GroupFilterOptions'];
        }

        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        $functions->errorlog("Database error: " . $e->getMessage(), "getGroupsInRole");
        return false;
    }

    return $GroupFilterOptions;
}

function addGroupFilter($Type, $FieldID, $GroupID)
{
    global $conn;
    global $functions;

    switch ($Type) {
        case 'itsm':
            $TableName = "itsm_fieldslist";
            break;

        case 'ci':
            $TableName = "cmdb_ci_fieldslist";
            break;

        case 'form':
            $TableName = "forms_fieldslist";
            break;

        default:
            throw new Exception("Invalid Type: " . $Type);
    }

    $EntryToAdd = "$GroupID";

    $GroupFilterOptions = getGroupFilter($Type, $FieldID);

    // If SelectFieldOptions is not empty, split the string by comma to get pairs of tablename and fieldname
    if (!empty($GroupFilterOptions)) {
        $GroupFilterOptions = trim($GroupFilterOptions, '#');
        $pairs = explode('#', $GroupFilterOptions);
    } else {
        // If SelectFieldOptions is empty, initialize $pairs as an empty array
        $pairs = [];
    }

    // Add new entry
    $pairs[] = $EntryToAdd;

    // Remove duplicates
    $pairs = array_unique($pairs);

    // Sort ASC
    sort($pairs);

    $NewStringValue = implode("#", $pairs);
    $NewStringValue = trim($NewStringValue, '#');

    updateGroupFilter($FieldID, $NewStringValue, $TableName);

    return true;
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

?>