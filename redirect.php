<?php

$type = $_GET['type'];
$id = $_GET['id'];
$elementid = $_GET['elementid'];

if ($type === 'cmdb') {
    $redirect_url = "./cmdb.php?id=$id&ciid=$id&elementid=$elementid";
    header("Location: $redirect_url");
} elseif ($type === 'itsm') {
    $redirect_url = "./itsm_tableview.php?id=$id&itsmid=$id&elementid=$elementid";
    header("Location: $redirect_url");
}
exit;
?>