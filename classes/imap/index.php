<?php

    include_once('class.imap.php');

    $email = new Imap();
    $inbox = null;
 

    if($email->connect(
        "{imap.one.com:993/imap/ssl/novalidate-cert}INBOX",
        "support@practicle.dk",
        "mqvFX7XhHrAVdvk"
    )){
        $inbox = $email->getMessages('html');
    }
    echo '<pre>';
    foreach ($inbox['data'] as $emails) {
            echo "Email from: ".$emails['from']['address']."<br>";
            echo "Subject is: ".$emails['subject'] . "<br>";
            echo "This email will now result in a new ticket...<br><br>";
    }

    print_r($inbox['data']);
    echo '</pre>';
?>