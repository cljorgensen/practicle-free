<?php

class EmailObject {
  
  var $saved_files        = array();
  var $filename_aliases   = array();
  var $inline_image_types = array("png","gif","jpg","jpeg","bmp","doc","docx","xls","pdf","xlsx","zip");
  
  function __construct($mysql,$uniqid,$source,$file_store) {
    
    $this->mysql      = $mysql;
    $this->uniqid     = $uniqid;
    $this->source     = $source;
    $this->file_store = $file_store;
  }
  
  function readEmail(){
    // Decode email message into parts
    $decoder = new Mail_mimeDecode($this->source);
    //echo $this->source."<br><br>";
    $this->decoded = $decoder->decode(
      Array(
        "decode_headers" => TRUE,
        "include_bodies" => TRUE,
        "decode_bodies"  => TRUE,
      )
    );
    
    // Get from name and email
    $this->from = $this->decoded->headers["from"];
    
    //Get Charset
    $charset = strstr($this->source, 'Content-Type: text/plain; charset=');
    $charset = str_replace('Content-Type: text/plain; charset=',"",$charset);
    $charset = strstr($charset, 'Content-Transfer-Encoding:', true);
    $charset = trim(str_replace('"',"",$charset));
    echo "<br>Charset is: ".$charset."<br>";
    //Get Encoding
    if (strpos($this->source, 'base64') !== false) {
        $encoding =  'base64';
    }
    elseif (strpos($this->source, 'quoted-printable') !== false) {
      $encoding =  'quoted-printable';
    }
    echo "Encoding is: ".$encoding."<br>";

    if (preg_match("/.* <.*@.*\..*>/i",$this->from,$matches)) {
      $this->name  = preg_replace("/ <(.*)>$/", "", $this->from);
      $this->email = preg_replace("/.*<(.*)>.*/","$1",$this->from);
    } else {
      $this->email = $this->from;
    }
      
    // Get subject
    $this->subject = trim($this->decoded->headers["subject"]);
    // Get body & attachments (if available)
    if (is_array($this->decoded->parts)) {
      foreach($this->decoded->parts as $arItem => $body_part){
        $this->decodePart($body_part);
      }
    } else {
      $this->bodyText = $this->decoded->body;
    }

	// Save Message to MySQL
    $this->saveToDb();
  }

  // Decode body part
  private function decodePart($body_part){
    
    // Get file and file name
    if (isset($body_part->d_parameters["filename"])) {
     
      // Set file name
      $filename = $body_part->d_parameters["filename"];

      // Save the file
      $this->saveFile($filename,$body_part->body);
      
      // Get content ID for image (as some mailers use CID instead of file name)
      if (isset($body_part->headers["content-id"])) {
        $cid = $body_part->headers["content-id"];
        $cid = str_replace("<","",$cid);
        $cid = str_replace(">","",$cid);

        // Replace the image src reference with the path to saved file in HTML
        $this->bodyHtml = preg_replace("/src=\"(cid:)?".$cid."\"/i", "src=\"[filePath]/".$this->uniqid."/".$filename."\"", $this->bodyHtml);
        
        // Replace the image CID reference in plain text
        $this->bodyText = preg_replace("/\[cid:".$cid."\]/i", "", $this->bodyText);
      }
      
      // Replace the image name reference in plain text
      $this->bodyText = preg_replace("/\[".$filename."\]/i", "", $this->bodyText);
      
    }
    
    $mimeType = "{$body_part->ctype_primary}/{$body_part->ctype_secondary}";

    // Decode sub-parts
    if ($body_part->ctype_primary == "multipart") {
      if (is_array($body_part->parts)) {
        foreach($body_part->parts as $arItem => $sub_part) {
          $this->decodePart($sub_part);
        }
      }
    }
    
    // Get plain text version
    if ($mimeType == "text/plain") {
      if (!isset($body_part->disposition)) {
        $this->bodyText .= $body_part->body;
      }
    }
    
    // Get HTML version
    if ($mimeType == "text/html") {
      if (!isset($body_part->disposition)) {
        $this->bodyHtml .= $body_part->body;
      }
    }

    if ($body_part->ctype_primary == "body");
  }
  
  // Save file
  private function saveFile($filename,$contents) {
    
    // Check if uniqid folder exists
    if (!file_exists($this->file_store."/".$this->uniqid))
      mkdir($this->file_store."/".$this->uniqid);
    
    // Save file
    file_put_contents($this->file_store."/".$this->uniqid."/".$filename, $contents);
    $this->saved_files[] = $filename;
  }
  
  // Save message & files to MySQL
  private function saveToDb() {
    
    $mysql  = $this->mysql;
    $uniqid = $this->uniqid;
    
    if (isset($this->bodyText)) {
      $body_text = $this->bodyText;
      if ($charset !== "UTF-8"){
        $body_text = mb_convert_encoding($body_text, "UTF-8", mb_detect_encoding($body_text, "UTF-8, ISO-8859-1, ISO-8859-1", true));
        }
        else{
          $body_text = mysqli_real_escape_string($mysql, mb_convert_encoding(trim($body_text),'UTF-8','UTF-8'));
        }
      
      //$body_text = mysqli_real_escape_string($mysql, imap_utf8($body_text));
      echo "Body_Text: ".$body_text."<br>";
    } else {
      $body_text = "";
    }
    
    if (isset($this->bodyHtml)) {
      
      $body_html = $this->bodyHtml;
    
      // Strip header tag (some email clients)
      $body_html = preg_replace("/<!DOCTYPE(.*?)>(\\r\\n)?/i","",$body_html);
      // Strip HTML tags (Yahoo, Mozilla)
      $body_html = preg_replace("/<\/?html(.*?)>(\\r\\n)?/i","",$body_html);
      $body_html = preg_replace("/<\/?head(.*?)>(\\r\\n)?/i","",$body_html);
      $body_html = preg_replace("/<\/?body(.*?)>(\\r\\n)?/i","",$body_html);
      $body_html = preg_replace("/<meta(.*?)>(\\r\\n)?/i","",$body_html);
      $body_html = preg_replace("/<style(.*?)<\/style>(\\r\\n)?/i","",$body_html);
      // Replace superfluous inline image meta
      $body_html = preg_replace("/ id=\"(.*?)\"/i","",$body_html);
      $body_html = preg_replace("/ alt=\"(.*?)\"/i","",$body_html);
      $body_html = preg_replace("/ title=\"(.*?)\"/i","",$body_html);
      $body_html = preg_replace("/ class=\"(.*?)\"/i","",$body_html);
      $body_html = preg_replace("/ data-id=\"(.*?)\"/i","",$body_html);
      $body_html = preg_replace("/ apple-inline=\"yes\"/i","",$body_html);
      if ($charset !== "UTF-8"){
        $body_html = mb_convert_encoding($body_html, "UTF-8", mb_detect_encoding($body_html, "UTF-8, ISO-8859-1, ISO-8859-1", true));
      }
      else{
        $body_html = mysqli_real_escape_string($mysql, utf8_encode($body_html));
      }
      //$body_html = mysqli_real_escape_string($mysql, mb_convert_encoding(trim($body_html),'UTF-8',$charset));
      //$body_html = mysqli_real_escape_string($mysql, utf8_decode(imap_utf8($row['htmlmsg'])));
      echo "<br>body_html: ".$body_html."<br>";
    } else {
      $body_html = "";
    }
        
    // Prepare data for MySql
    if (isset($this->name)){
      if ($charset !== "UTF-8"){
        $name = $this->name;
        $name = mb_convert_encoding($name, "UTF-8", mb_detect_encoding($name, "UTF-8, ISO-8859-1, ISO-8859-1", true));
        }
        else{
          $name = mysqli_real_escape_string($mysql, imap_utf8($name));
        }
      //$name = mysqli_real_escape_string($mysql, imap_utf8($this->name));
      echo $name."<br>";
    }
    else
      $name = "";
    if (isset($this->email)){
      //$email = mysqli_real_escape_string($mysql, mb_convert_encoding(trim($this->email),'UTF-8','UTF-8'));
      $email = mysqli_real_escape_string($mysql, imap_utf8($this->email));
      echo $email."<br>";
    }
    else
      $email = "";
    if (isset($this->subject)){
      if ($charset !== "UTF-8"){
        $subject = $this->subject;
        $subject = mb_convert_encoding($subject, "UTF-8", mb_detect_encoding($subject, "UTF-8, ISO-8859-1, ISO-8859-1", true));
        }
        else{
          $subject = mysqli_real_escape_string($mysql, imap_utf8($subject));
        }
      //$subject = mysqli_real_escape_string($mysql, imap_utf8($this->subject));
      //$subject = mysqli_real_escape_string($mysql,mb_convert_encoding($this->subject, 'UTF-8', 'auto'));
      echo $subject."<br>";
    }
    else
      $subject = "";
    // Insert message to MySQL
    if (!empty($body_html)){
      $sql = "INSERT INTO emails (uniqid,time,name,email,subject,body_text,body_html) VALUES ('".$uniqid."',now(),'".$name."','".$email."','".$subject."','".$body_text."','".$body_html."')";
      echo "body_html not empty: ".$sql;
      mysqli_query($mysql,$sql);
    }
    else{
      $sql = "INSERT INTO emails (uniqid,time,name,email,subject,body_text) VALUES ('".$uniqid."',now(),'".$name."','".$email."','".$subject."','".$body_text."')";
      echo "body_html empty: ".$sql;
      mysqli_query($mysql,$sql);
    }
    
    // Get the AI ID from MySQL
    $result = mysqli_query ($mysql,"SELECT id FROM emails WHERE uniqid='".$uniqid."'");
    $row = mysqli_fetch_array($result);
    $email_id = mysqli_real_escape_string($mysql,$row["id"]);
    
    // Insert all the attached file names to MySQL
    if (sizeof($this->saved_files) > 0) {
      foreach($this->saved_files as $filename){
        $filename = mysqli_real_escape_string($mysql,mb_convert_encoding($filename,'UTF-8','UTF-8'));
        mysqli_query($mysql,"INSERT INTO files (email_id,filename) VALUES ('".$email_id."','".$filename."')");
      }
    }
  }
}
