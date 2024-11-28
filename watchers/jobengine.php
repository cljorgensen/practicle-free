#!/usr/bin/php -q
<?php
chdir(dirname(__FILE__));

include("../inc/dbconnection.php");
include("../vendor/autoload.php");
include("../functions/functions.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use benhall14\phpImapReader\Email as Email;
use benhall14\phpImapReader\EmailAttachment as EmailAttachment;
use benhall14\phpImapReader\Reader as Reader;

$SystemName = $functions->getSettingValue(13);
$SystemURL = $functions->getSettingValue(17);
global $SystemName, $SystemURL;

$IMAPServer = $functions->getSettingValue(38);
$IMAPMailAddress = $functions->getSettingValue(39);
$IMAPPassword = $functions->getSettingValue(40);

$AnswerSubject = "";
$AnswerContent = "";
$EmailFrom = "";
$FromName = "";

//Set default date timezone to Europe/Copenhagen
date_default_timezone_set('Europe/Copenhagen');

define('IMAP_USERNAME', "$IMAPMailAddress");
define('IMAP_PASSWORD', "$IMAPPassword");
define('IMAP_MAILBOX', "$IMAPServer");
define('ATTACHMENT_PATH', __DIR__ . '/attachments');

try {
    // Main function to handle incoming emails
    @handleIncomingEmails();
} catch (Exception $e) {
    echo $e->getMessage();
}

// Function to handle incoming emails
function handleIncomingEmails()
{
  global $conn, $functions, $SystemName, $SystemURL, $IMAPMailAddress;

  $mark_as_read = true;
  $encoding = 'UTF-8';

  // Determine if we're using IMAP or POP3 based on the mailbox string
  $isImap = strpos(IMAP_MAILBOX, '/imap') !== false;

  // Initialize the Reader object
  $imap = new Reader(IMAP_MAILBOX, IMAP_USERNAME, IMAP_PASSWORD, ATTACHMENT_PATH, $mark_as_read, $encoding);

  // Fetch unseen emails (only for IMAP)
  if ($isImap) {
    $imap->unseen()->get();
  }

  foreach ($imap->emails() as $email) {
    $EmailID = $email->id();
    $EmailFrom = $email->fromEmail();
    $FromName = $email->fromName();
    $Subject = $email->subject();
    $Message = $email->plain();
    $Comment = $functions->formatMessage($Message);
    $EmailDate = date("Y-m-d H:i:s");

    echo "Handling email for: $SystemName\n";
    echo "$EmailFrom retrieved\n";

    // Log incoming mail
    $functions->logIncomingMail($EmailFrom, $IMAPMailAddress, $EmailDate, $Subject, $Message);

    // Check for spam
    $spam = $functions->checkForMailSpam($EmailFrom, $IMAPMailAddress, $EmailDate, $Subject);
    if ($spam === 1) {
      continue; // Skip if spam detected
    }

    // Handle customer validation
    $RelatedCustomerID = handleCustomerValidation($EmailFrom, $FromName, $Subject, $Comment);
    if (!$RelatedCustomerID) {
      continue; // Skip further processing if customer is not valid
    }

    // Get the company ID
    $CompanyID = getUserRelatedCompanyID($RelatedCustomerID);
    if (!$CompanyID) {
      sendCustomerDenyEmail($EmailFrom, $FromName, "You are not associated with any company.");
      continue;
    }

    // If using IMAP, mark the email as read
    if ($isImap) {
      $imap->markAsRead($EmailID); // Mark email as read using the Reader class
    } else {
      // If using POP3, delete the email after processing
      $imap->deleteEmail($EmailID);
    }

    // Process the email based on its subject
    processEmailSubject($email, $Subject, $Comment, $RelatedCustomerID, $CompanyID, $EmailFrom, $FromName);
  }
}

// Function to format the email message
function formatMessage($message)
{
    $comment = nl2br($message);
    return str_replace("<br />", "<br>", $comment);
}

// Function to handle customer validation
function handleCustomerValidation($emailFrom, $fromName, $subject, $comment)
{
    global $functions, $SystemURL;

    $RelatedCustomerID = $functions->getUserIDFromEmail($emailFrom);

    if (empty($RelatedCustomerID)) {
        $Allowed = $functions->getSettingValue(49);
        if ($Allowed == '1') {
            createUserFromEmail($emailFrom, $fromName);
        } else {
            sendCustomerDenyEmail($emailFrom, $fromName, "Please register first at: $SystemURL/register.php");
            return false;
        }
    }
    return $RelatedCustomerID;
}

// Function to send denial email
function sendCustomerDenyEmail($emailFrom, $fromName, $message)
{
    $subject = "We received your email but...";
    $content = "We did nothing. $message";
    sendMailToSinglePerson($emailFrom, $fromName, $subject, $content);
}

// Function to process the email subject
function processEmailSubject($email, $subject, $comment, $RelatedCustomerID, $CompanyID, $EmailFrom, $FromName)
{
  global $functions, $conn, $SystemURL, $SystemName;

  $ITSMTypeID = "1";
  $existing = false;

  if (str_contains($subject, "[INC#")) {
    $ITSMTypeID = "1";
    $ITSMID = extractITSMID($subject, "[INC#");
    $existing = true;
  } elseif (str_contains($subject, "[REQ#")) {
    $ITSMTypeID = "2";
    $ITSMID = extractITSMID($subject, "[REQ#");
    $existing = true;
  } elseif (str_contains($subject, "[CHA#")) {
    $ITSMTypeID = "3";
    $ITSMID = extractITSMID($subject, "[CHA#");
    $existing = true;
  } elseif (str_contains($subject, "[PRO#")) {
    $ITSMTypeID = "4";
    $ITSMID = extractITSMID($subject, "[PRO#");
    $existing = true;
  } else {
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
    $ElementCreatedDateVal = date("Y-m-d H:i:s");
    $ITSMTypeID = $functions->getITSMTypeIDFromTableName($ITSMTableName);
    $ModuleType = $functions->getModuleType($ITSMTypeID);
    $SLASupported = $functions->getITSMSLASupport($ITSMTypeID);
    $ITSMTypeName1 = $functions->getITSMTypeName($ITSMTypeID);
    $ITSMID2 = "";
    $ITSMTypeID2 = "";
    $ITSMForm = [];
    // Build ITSMForm array
    $ITSMForm[] = ['name' => 'CreateFormSubject', 'value' => $subject];
    $ITSMForm[] = ['name' => 'CreateFormDescription', 'value' => $comment];
    $ITSMForm[] = ['name' => 'CreateFormCustomer', 'value' => $RelatedCustomerID];
    $ITSMForm[] = ['name' => 'CreateFormPriority', 'value' => '3'];
    $ITSMForm[] = ['name' => 'CreateFormStatus', 'value' => '1'];
    $ITSMForm[] = ['name' => 'CreateFormCreated', 'value' => date("Y-m-d H:i:s")];
    $ITSMForm[] = ['name' => 'CreateFormCreatedBy', 'value' => $RelatedCustomerID];
    $ITSMForm[] = ['name' => 'CreateFormLastUpdated', 'value' => date("Y-m-d H:i:s")];
    
    $FormID = "";
    $RequestForm = [];

    $resultArray = createITSMEntry($RelatedCustomerID, $ITSMForm, $FormID, $RequestForm, $ITSMTableName, $ElementCreatedDateVal, $ITSMTypeID, $ModuleType, $SLASupported, $ITSMTypeName1, $ITSMID2, $ITSMTypeID2);

    // Check if the result array is not empty
    if (!empty($resultArray)) {
      // If the array is not empty, use list() to assign values to variables
      list($ITSMID, $ITSMTableName, $ITSMTypeID, $ShortName, $ModuleType) = $resultArray;
      $URL = $functions->getSettingValue(17);
      $Link = "$URL/itsm-$ITSMTypeID-$ITSMID";
      $CreatedBy = $functions->getITSMCreatedByName($ITSMID, $ITSMTypeID);
      $CreatedWord = $functions->translate("created");
      $byWord = $functions->translate("by");
      $Message = "$ITSMTypeName1: $ITSMID $CreatedWord $byWord $CreatedBy: $Link";
      $functions->sendMessageToSlack($Message);
    } else {
      // Handle the case where the result is empty
      echo "The result is empty.";
    }

    echo "New ticket created with ID: $ITSMID\n";
    // Send a confirmation email to the sender that the ticket has been created
    $TemplateID = "3";
    sendITSMMailTemplate($ITSMTypeID, $ITSMID, $RelatedCustomerID, $comment, $TemplateID);
  }

  handleAttachments($email, $ITSMID, $ITSMTypeID, $RelatedCustomerID, $existing, $comment);

  if ($existing) {
    $TemplateID = "1";
    sendITSMMailTemplate($ITSMTypeID, $ITSMID, $RelatedCustomerID, $comment, $TemplateID);
    echo "Reply recieved for existing itsm: $ITSMID\n";
    createITSMComment($ITSMID, $ITSMTypeID, $comment, $RelatedCustomerID);
  }
}

// Function to extract ITSM ID from subject
function extractITSMID($subject, $prefix)
{
    $from = $prefix;
    $to = "]";
    return getStringBetween($subject, $from, $to);
}

// Function to handle email attachments
function handleAttachments($email, $ITSMID, $ITSMTypeID, $RelatedCustomerID, $existing, &$comment)
{
    global $conn;
    global $functions;
    $FilesArray = [];
    
    if ($email->hasAttachments()) {
        $attachments = $email->attachments();
        $i = 0;
        foreach ($attachments as $attachment) {
            $i++;
            $SourceFileName = cleanAttachmentName($attachment->name());
            $SourcePath = $attachment->filePath();

            $UploadFolder = "../uploads/files_itsm";
            $ModuleID = "1";
            $FileExtension = pathinfo($SourceFileName, PATHINFO_EXTENSION);
            $FileName = "$ModuleID-$ITSMID--$RelatedCustomerID-$i.$FileExtension";

            if (file_exists($SourcePath)) {
                $DestinationFile = "$UploadFolder/$FileName";
                $sql = "INSERT INTO files_itsm(FileName, FileNameOriginal, RelatedElementID, Date, RelatedUserID, RelatedType) 
                        VALUES ('$FileName','$SourceFileName',$ITSMID,Now(),$RelatedCustomerID,'$ITSMTypeID')";
                mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                rename($SourcePath, $DestinationFile);
                $FilesArray[] = $SourceFileName;
            }
        }
    }

    if ($existing && !empty($FilesArray)) {
        $FileNameText = implode("<br>", $FilesArray);
        $comment .= "<br><br>Files attached this message: <br>$FileNameText";
    }
}

// Function to clean attachment names
function cleanAttachmentName($filename)
{
    $filename = str_replace([
        "?=", "=?utf-8?Q?", "=C3=A6", "=C3=86", "=C3=B8", "=C3=98", "=C3=A5", "=C3=85", "'", "="
    ], ["", "", "æ", "Æ", "ø", "Ø", "å", "Å", "", ""], $filename);
    return utf8_encode($filename);
}

// clear old sessions
$functions->clearOldSessions();
// update SLA violations
$functions->updateSLAViolations();

//Run CI Sync Jobs
dailyCISync();

//Run daily AD import
//Sync AD users
$value = $functions->getSettingValue(56);
if($value == "1"){
  $SyncTime = "05:00";

  $CurrentTime = date("H:i");
  $FromTime = date("H:i", strtotime("$SyncTime"));
  $ToTime = date('H:i:s', strtotime($SyncTime . ' +2 minutes'));

  if ($CurrentTime > $FromTime && $CurrentTime < $ToTime) {
    syncADUsers();
  }
}
//Sync AD teams and included users
//Sync also administrators
$value = $functions->getSettingValue(59);
if ($value == "1") {
  $SyncTime = "05:02";

  $CurrentTime = date("H:i");
  $FromTime = date("H:i", strtotime("$SyncTime"));
  $ToTime = date('H:i:s', strtotime($SyncTime . ' +2 minutes'));

  if ($CurrentTime > $FromTime && $CurrentTime < $ToTime) {
    syncADTeams();
    syncAdministrators();
  }
}

// Remove temp files
removeOldTempFiles();

// Run CVE Import
$currentHour = date('H');
$currentMinute = date('i');

$hoursToCheck = ['05', '15'];

if (in_array($currentHour, $hoursToCheck) && ($currentMinute >= '00' && $currentMinute <= '02')) {
  getCVEEntries('7');
}

// Check connection
checkConnection("google.dk");

echo "Finished: $SystemName\n";
?>