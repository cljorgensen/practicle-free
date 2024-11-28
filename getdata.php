<?php
require_once('./inc/dbconnection.php');
require_once('./functions/functions.php');
include_once "./vendor/autoload.php";
// debug error level: low, medium, high
$functions->setDebugging("low");
// Check if the user is logged in
$functions->checkActiveSession();
// Set default timezone
date_default_timezone_set('Europe/Copenhagen');
?>

<?php
$UserGroups = $_SESSION['memberofgroups'];
$UserType = $_SESSION['usertype'];
$CompanyID = $_SESSION['companyid'];
$TeamID = $_SESSION['teamid'];
$UserSessionID = $_SESSION['id'];
$UserLanguageID = $functions->getUserLanguage($UserSessionID);
$UserLanguageCode = $functions->getLanguageCode($UserLanguageID);

if (isset($_SESSION['Active'])) {
  $Active = $_SESSION['Active'];
  if ($Active == "0") {
    $_SESSION["loggedin"] = false;
    header("location: login.php");
    exit;
  }
} else {
  $_SESSION["loggedin"] = false;
  header("location: login.php");
  exit;
}
?>

<?php

if (isset($_POST['taskid'])) {
  $TaskID = $_POST['taskid'];
  $DateTimeNow = date('Y-m-d H:i:s');

  $sql = "UPDATE taskslist
          SET todo = 'no', doing = 'no', done = 'no', Basket = 'yes', DateSolved = ?
          WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $DateTimeNow, $TaskID);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // Task update successful
  } else {
    // Handle the prepare statement error
    $functions->errorlog('Prepare statement error: ' . mysqli_error($conn), "taskid");
    // Task update failed
  }
}


if (isset($_GET['getCISInCITable'])) {
  if (in_array("100014", $UserGroups) || in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100014");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $CITableName = $_GET['CITableName'];
  $CITypeID = getCITypeIDFromTableName($CITableName);
  $FieldsToShow = getShowInRelationFields($CITypeID);
  $FieldsToSelect = implode(",", $FieldsToShow);

  $sql = "SELECT $CITableName.ID, $CITableName.$FieldsToSelect
          FROM $CITableName
          WHERE Active = 1";

  try {
    $result = mysqli_query($conn, $sql);

    if ($result === FALSE) {
      throw new Exception("The query for getting CIs did not succeed");
    } else {
      while ($row = mysqli_fetch_array($result)) {
        $array[] = array("ID" => $row[0], "FieldName" => "$row[1]");
      }
      echo json_encode($array, JSON_PRETTY_PRINT);
    }
  } catch (Exception $e) {
    $array[] = array("error" => "$e");
    echo json_encode($array, JSON_PRETTY_PRINT);
    return;
  }
}

if (isset($_GET['getElementsInITSMTable'])) {
  $ITSMTableName = $_GET['ITSMTableName'];
  $ITSMTypeID = $functions->getITSMTypeIDFromTableName($ITSMTableName);
  $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
  $FieldsToShow = getITSMShowInRelationFields($ITSMTypeID);
  $FieldsToSelect = implode(",", $FieldsToShow);

  $StatusCodes = getITSMActiveStatusCodes($ITSMTypeID);
  $StatusCodes = implode(",", $StatusCodes);

  $sql = "SELECT $ITSMTableName.ID, $ITSMTableName.$FieldsToSelect
          FROM $ITSMTableName
          WHERE Status IN ($StatusCodes)
          ORDER BY ID ASC";

  $result = mysqli_query($conn, $sql);
  if ($result) {
    $array = [];
    while ($row = mysqli_fetch_array($result)) {
      $FieldName = $row[0] . ": " . $row[1];
      $array[] = array("ID" => $row[0], "FieldName" => $FieldName);
    }
    mysqli_free_result($result);
    echo json_encode($array, JSON_PRETTY_PRINT);
  } else {
    // Handle the query error
    error_log('Query fail: ' . mysqli_error($conn));
    echo json_encode([], JSON_PRETTY_PRINT);
  }
}

if (isset($_GET['getITSMTemplates'])) {
  $UserSessionID = $_SESSION['id'];
  $ITSMTypeID = $_GET['itsmtypeid'];
  $array = [];
  $error = '';

  $sql = "SELECT itsm_templates.ID, itsm_templates.Description, itsm_templates.Public, itsm_templates.RelatedFormID
          FROM itsm_templates
          WHERE (Owner = ? OR Public = 1) AND itsm_templates.RelatedModule = ?
          ORDER BY Description ASC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $UserSessionID, $ITSMTypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      while ($row = mysqli_fetch_array($result)) {
        $Public = $row["Public"];
        $FormID = $row["RelatedFormID"];
        $Description = $row["Description"] . ($Public == "1" ? " (Public)" : " (Private)");
        $array[] = array("ID" => $row["ID"], "Description" => $Description, "FormID" => $FormID);
      }

      mysqli_free_result($result);

      if (empty($array)) {
        echo json_encode([]);
      } else {
        echo json_encode($array, JSON_PRETTY_PRINT);
      }
    } else {
      $error = "Query execution failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getITSMTemplates");
      echo json_encode(["error" => $error]);
    }

    mysqli_stmt_close($stmt);
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getITSMTemplates");
    echo json_encode(["error" => $error]);
  }
}


if (isset($_GET['getCIParentRelations'])) {
  if (in_array("100014", $UserGroups) || in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100014");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $CIID = $_POST['CIID'];
  $CITypeID = $_POST['CITypeID'];
  $CITableName = getCITableName($CITypeID);
  $AllowDelete = $_POST['AllowDelete'];

  if(in_array("100015", $UserGroups) || in_array("100001", $UserGroups)){
    $AllowDelete = "1";
  }

  //Lets first get an array with related child tables for this CI
  $sql = "SELECT ID, CITable1, CITable2, CI1ID, CI2ID
          FROM cmdb_ci_relations
          WHERE CITable2 = ? AND CI2ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("si", $CITableName, $CIID);
  $stmt->execute();
  $result = $stmt->get_result();

  $Counter = 1;

  while ($row = mysqli_fetch_array($result)) {
    $RelationID = $row["ID"];
    $CITable1 = $row["CITable1"];
    $CI1ID = $row["CI1ID"];
    $CITable2 = $row["CITable2"];
    $CI2ID = $row["CI2ID"];
    $Status1 = getStatusOfCI($CITable1, $CI1ID);
    $Status2 = getStatusOfCI($CITable2, $CI2ID);

    if ($Status1 == "0" || $Status2 == "0") {
    } else {
      $temparray[] = array("RelationID" => $RelationID, "CITable1" => $CITable1, "CI1ID" => $CI1ID, "CITable2" => $CITable2, "CI2ID" => $CI2ID);
    }
  }

  foreach ($temparray as $value) {
    $RelationID = $value["RelationID"];
    $Table1 = $value["CITable1"];
    $CI1ID = $value["CI1ID"];
    $Table2 = $value["CITable2"];
    $CIID2 = $value["CI2ID"];
    $CIName = getCINameFromTableName($Table1);
    $CITypeIDRelation = getCITypeIDFromTableName($Table1);
    $FieldName = getShowInRelationFields($CITypeIDRelation);
    $FieldNameImploded = implode("", $FieldName);
    $FieldValue = $functions->getFieldValueFromID($CI1ID, $FieldNameImploded, $Table1);

    if ($AllowDelete == "1") {
      $DeleteLink = "&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" title=\"" . $functions->translate("Delete relation") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteRelCIRel($RelationID);\"><i class=\"fa fa-trash\"></i></span></a>";
    } else {
      $DeleteLink = "";
    }

    $Link = "<a href=\"javascript:runModalViewCI('$CI1ID','$CITypeIDRelation','0');\">$FieldValue</a>$DeleteLink<br>";
    $resultarray[] = array("ID" => $Counter, "Type" => $CIName, "" => $Link);
    $Counter = ($Counter + 1);
  }

  if (!empty($resultarray)) {
    asort($resultarray);
    echo json_encode($resultarray, JSON_PRETTY_PRINT);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['getCIChildRelations'])) {

  if (in_array("100014", $UserGroups) || in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100014");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $CIID = $_POST['CIID'];
  $CITypeID = $_POST['CITypeID'];
  $CITableName = getCITableName($CITypeID);
  $AllowDelete = $_POST['AllowDelete'];

  if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
    $AllowDelete = "1";
  }

  //Lets first get an array with related child tables for this CI
  $sql = "SELECT ID, CITable1, CITable2, CI1ID, CI2ID
          FROM cmdb_ci_relations
          WHERE CITable1 = ? AND CI1ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("si", $CITableName, $CIID);
  $stmt->execute();
  $result = $stmt->get_result();

  $Counter = 1;

  while ($row = mysqli_fetch_array($result)) {
    $RelationID = $row["ID"];
    $CITable1 = $row["CITable1"];
    $CI1ID = $row["CI1ID"];
    $CITable2 = $row["CITable2"];
    $CI2ID = $row["CI2ID"];
    $Status1 = getStatusOfCI($CITable1, $CI1ID);
    $Status2 = getStatusOfCI($CITable2, $CI2ID);
    
    if ($Status1 == "0" || $Status2 == "0") {
    } else {
      $temparray[] = array("RelationID" => $RelationID, "CITable1" => $CITable1, "CI1ID" => $CI1ID, "CITable2" => $CITable2, "CI2ID" => $CI2ID);
    }
  }

  foreach ($temparray as $value) {
    $RelationID = $value["RelationID"];
    $Table1 = $value["CITable1"];
    $CI1ID = $value["CI1ID"];
    $Table2 = $value["CITable2"];
    $CI2ID = $value["CI2ID"];
    $CIName = getCINameFromTableName($Table2);
    $CITypeIDRelation = getCITypeIDFromTableName($Table2);
    $FieldName = getShowInRelationFields($CITypeIDRelation);
    $FieldNameImploded = implode("", $FieldName);
    $FieldValue = $functions->getFieldValueFromID($CI2ID, $FieldNameImploded, $Table2);

    if ($AllowDelete == "1") {
      $DeleteLink = "&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" title=\"" . $functions->translate("Delete relation") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteRelCIRel($RelationID);\"><i class=\"fa fa-trash\"></i></span></a>";
    } else {
      $DeleteLink = "";
    }

    $Link = "<a href=\"javascript:runModalViewCI('$CI2ID','$CITypeIDRelation','0');\">$FieldValue</a>$DeleteLink<br>";
    $resultarray[] = array("ID" => $Counter, "Type" => $CIName, "" => $Link);
    $Counter = ($Counter + 1);
  }

  if (!empty($resultarray)) {
    asort($resultarray);
    echo json_encode($resultarray, JSON_PRETTY_PRINT);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['getITSMRelationsITSM'])) {
    $UserType = $_SESSION['usertype'];
    if ($UserType == "2") {
        echo json_encode([]);
        return;
    }
    $ITSMID = $_POST['ITSMID'];
    $ITSMTypeID = $_POST['ITSMTypeID'];
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
    $AllowDelete = $_POST['AllowDelete'];
    
    //Lets first get an array with related child tables for this CI
    $StatusCodes = implode(",", getITSMActiveStatusCodes($ITSMTypeID));
    $sql = "SELECT itsm_relations.ID, itsm_relations.Table1, itsm_relations.Table2, itsm_relations.ID1, itsm_relations.ID2
            FROM itsm_relations
            LEFT JOIN $ITSMTableName AS passwords1 ON passwords1.ID = itsm_relations.ID1
            LEFT JOIN $ITSMTableName AS passwords2 ON passwords2.ID = itsm_relations.ID2
            WHERE (itsm_relations.Table1 = '$ITSMTableName' AND itsm_relations.ID1 = ?) 
              OR (itsm_relations.Table2 = '$ITSMTableName' AND itsm_relations.ID2 = ?) 
              AND (passwords1.Status IN ($StatusCodes) OR passwords2.Status IN ($StatusCodes));";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ii", $ITSMID, $ITSMID);
    $stmt->execute();
    $result = $stmt->get_result();

    $Counter = 1;
    while ($row = mysqli_fetch_array($result)) {
      $RelationID = $row["ID"];
      $Table1 = $row["Table1"];
      $ID1 = $row["ID1"];
      $Table2 = $row["Table2"];
      $ID2 = $row["ID2"];

      if ($AllowDelete == 1) {
        $DeleteLink = "&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" title=\"" . _("Delete relation") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteRelITSMRel('$RelationID');\"><i class=\"fa fa-trash\"></i></span></a>";
      } else {
        $DeleteLink = "";
      }
      $Link = "";

      // Determine the direction of the relation based on the current table being viewed
      if ($ITSMTableName == $Table1 && $ITSMID == $ID1) {
        $ITSMType2 = $functions->getITSMTypeIDFromTableName($Table2);
        $ITSMName2 = $functions->getITSMTypeName($ITSMType2);
        $FieldName2 = getITSMShowInRelationFields($ITSMType2);
        $FieldName2 = implode("", $FieldName2);
        $FieldValue2 = $functions->getITSMFieldValue($ID2, $FieldName2, $Table2);
        $Link = "<a href=\"javascript:viewITSM('$ID2','$ITSMType2','1','modal');\">$ID2: $FieldValue2</a>$DeleteLink<br>";
        $resultarray[] = array("ID" => $Counter, "Type" => $ITSMName2, "" => $Link);
        $Counter = ($Counter + 1);
      } else {
        $ITSMType1 = $functions->getITSMTypeIDFromTableName($Table1);
        $ITSMName1 = $functions->getITSMTypeName($ITSMType1);
        $FieldName1 = getITSMShowInRelationFields($ITSMType1);
        $FieldName1 = implode("", $FieldName1);
        $FieldValue1 = $functions->getITSMFieldValue($ID1, $FieldName1, $Table1);
        $Link = "<a href=\"javascript:viewITSM('$ID1','$ITSMType1','1','modal');\">$ID1: $FieldValue1</a>$DeleteLink<br>";
        $resultarray[] = array("ID" => $Counter, "Type" => $ITSMName1, "" => $Link);
        $Counter = ($Counter + 1);
      }
    }

    if ($resultarray) {
        asort($resultarray);
        echo json_encode($resultarray, JSON_PRETTY_PRINT);
    } else {
        echo json_encode([]);
    }
}

if (isset($_GET['getITSMRelationsCI'])) {
  $temparray = array();
  $result = "";
  $ITSMID = $_POST['ITSMID'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $AllowDelete = $_POST['AllowDelete'];

  //Lets first get an array with related child tables for this CI
  $StatusCodes = implode(",", getITSMActiveStatusCodes($ITSMTypeID));

  $sql = "SELECT cmdb_ci_itsm_relations.ID, ITSMTable, CITable, ITSMID, CIID
          FROM cmdb_ci_itsm_relations
          LEFT JOIN $ITSMTableName ON $ITSMTableName.ID = cmdb_ci_itsm_relations.ITSMID
          WHERE (ITSMTable = ? AND ITSMID = ?) AND $ITSMTableName.Status IN ($StatusCodes);";

  $stmtCIS = mysqli_prepare($conn, $sql);
  $stmtCIS->bind_param("si", $ITSMTableName, $ITSMID);
  $stmtCIS->execute();
  
  $result = $stmtCIS->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $RelationID = $row["ID"];
    $Table1 = $row["ITSMTable"];
    $ID1 = $row["ITSMID"];
    $Table2 = $row["CITable"];
    $ID2 = $row["CIID"];

    $temparray[] = array("RelationID" => $RelationID, "Table1" => $Table1, "ID1" => $ID1, "Table2" => $Table2, "ID2" => $ID2);
  }

  foreach ($temparray as $value) {
    $RelationID = $value["RelationID"];
    $Table1 = $value["Table1"];
    $ID1 = $value["ID1"];
    $Table2 = $value["Table2"];
    $ID2 = $value["ID2"];

    $ITSMTypeID2 = getCITypeIDFromTableName($Table2);
    $ITSMName2 = getCITypeName($ITSMTypeID2);
    $FieldName = getShowInRelationFields($ITSMTypeID2);
    $FieldName2 = implode("", $FieldName);
    $FieldValue = $functions->getFieldValueFromID($ID2, $FieldName2, $Table2);

    if ($AllowDelete == 1) {
      $DeleteLink = "&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" title=\"" . _("Delete relation") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteITSMToElementRelation($RelationID, $ITSMTypeID2, $ITSMTypeID, $ITSMID);\"><i class=\"fa fa-trash\"></i></span></a>";
    } else {
      $DeleteLink = "";
    }

    $Link = "<a href=\"javascript:runModalViewCI('$ID2','$ITSMTypeID2','0');\">$ID2: $FieldValue</a>$DeleteLink<br>";
    $resultarray[] = array("ID" => $Counter, "Type" => $ITSMName2, "" => $Link);
    $Counter = ($Counter + 1);
  }

  if ($resultarray) {
    asort($resultarray);
    echo json_encode($resultarray, JSON_PRETTY_PRINT);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['getCILog'])) {
  $CIID = $_POST['CIID'];
  $CITypeID = $_POST['CITypeID'];
  $CITableName = getCITableName($CITypeID);
  $AllowDelete = $_POST['AllowDelete'];

  $sql = "SELECT LogActionDate, LogActionText, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName, users.ID AS UserID
          FROM log_cis
          LEFT JOIN users ON log_cis.RelatedUserID = users.ID
          WHERE log_cis.RelatedElementID = ? AND log_cis.RelatedType = ?
          ORDER BY LogActionDate DESC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $CIID, $CITypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $myFormatForView = convertToDanishTimeFormat($row['LogActionDate']);
        $text = $row['LogActionText'];
        $user = $row['FullName'];
        $resultArray[] = array("Date" => $myFormatForView, "Action" => $text, "User" => $user);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getCILog");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getCILog");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getCILogBook'])) {
  $CIID = $_POST['CIID'];
  $CITypeID = $_POST['CITypeID'];
  $CITableName = getCITableName($CITypeID);
  $AllowDelete = $_POST['AllowDelete'];

  $sql = "SELECT cmdb_logbook.ID, cmdb_logbook.RelatedCIType, cmdb_logbook.RelatedCI, cmdb_logbook.LogContent, cmdb_logbook.Relation, cmdb_logbook.Date, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
          FROM cmdb_logbook
          LEFT JOIN users ON cmdb_logbook.RelatedUserID = users.ID
          WHERE RelatedCIType = ? AND RelatedCI = ? AND Status = 1
          ORDER BY cmdb_logbook.Date DESC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $CITypeID, $CIID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $myFormatForView = convertToDanishTimeFormat($row['Date']);
        $ID = $row['ID'];
        $Text = $row['LogContent'];
        $user = $row['FullName'];
        $DeleteLink = "&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" title=\"" . $functions->translate("Delete relation") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteCILogBookEntry($ID);\"><i class=\"fa fa-trash\"></i></span></a>";
        $Relation = $row['Relation'];
        $resultArray[] = array($functions->translate("Date") => $myFormatForView, $functions->translate("Description") => $Text, $functions->translate("Relation") => $Relation, $functions->translate("User") => $user, "" => $DeleteLink);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getCILogBook");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getCILogBook");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getCITableRelationDefinitions'])) {
  $CITypeID = $_POST['CITypeID'];
  $FieldID = $_POST['FieldID'];
  $newArray = [];
  $resultArray = [];

  $sql = "SELECT RelationsLookup FROM cmdb_ci_fieldslist WHERE ID = ? AND RelatedCITypeID = ?";

  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $FieldID, $CITypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if ($result) {
      $row = mysqli_fetch_array($result);
      if ($row) {
        $RelationsLookup = $row['RelationsLookup'];

        if (!empty($RelationsLookup)) {
          $pairs = explode('#', $RelationsLookup);

          foreach ($pairs as $pairString) {
            $pair = explode(',', $pairString);
            $resultArray[] = [
              'tablename' => $pair[0],
              'fieldname' => $pair[1]
            ];
          }

          foreach ($resultArray as $pair) {
            $TableName = $pair['tablename'];
            $name = getCMDBNameFromTableName($TableName);
            $RelCITypeID = getCITypeFromTableName($TableName);
            $FieldName = $pair['fieldname'];
            $field = getCMDBFieldLabelFromFieldName($FieldName, $RelCITypeID);
            $DeleteLink = "<a href=\"javascript:void(0);\" title=\"" . $functions->translate("Delete") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteCIFieldRelation('$TableName','$FieldName','$FieldID','$CITypeID');\"><i class=\"fa fa-trash\"></i></span></a>";

            $Relation = $name . " / " . "$field $DeleteLink";
            $newArray[] = array($functions->translate('Relations') => $Relation);
          }
        }
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getCITableRelationDefinitions");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
      exit;
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getCITableRelationDefinitions");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
    exit;
  }

  echo json_encode($newArray, JSON_PRETTY_PRINT);
}

if (isset($_GET['getTableSelectOptions'])) {
  $TypeID = $_POST['TypeID'];
  $FieldID = $_POST['FieldID'];
  $Type = $_POST['Type'];

  switch ($Type) {
    case "ci":
      $TableName = "cmdb_ci_fieldslist";
      $RelatedTypeIDField = "RelatedCITypeID";
      break;
    case "itsm":
      $TableName = "itsm_fieldslist";
      $RelatedTypeIDField = "RelatedTypeID";
      break;
    case "form":
      $TableName = "forms_fieldslist";
      $RelatedTypeIDField = "RelatedFormID";
      break;
    default:
      // Default case if $Type doesn't match any of the specified values
      // You can handle this case as needed
      break;
  }

  $sql = "SELECT SelectFieldOptions
          FROM $TableName
          WHERE ID = ? AND $RelatedTypeIDField = ?";

  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $FieldID,$TypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $SelectFieldOptions = $row['SelectFieldOptions'];
      }

      $resultArray = [];
      $resultArray = explode('#', $SelectFieldOptions);

      // Array to store the extracted pairs

      $optionValues = array();

      foreach ($resultArray as $option) {
          // Extract value attribute using regular expression
          preg_match('/value="([^"]+)"/', $option, $matches);
          if (isset($matches[1])) {
              $optionValues[] = $matches[1];
          }
      }


      foreach ($optionValues as $pair) {
        $SelectOption = $pair;

        $DeleteLink = "<a href=\"javascript:void(0);\" title=\"" . $functions->translate("Delete") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteFieldSelectOption('$pair','$FieldID','$TypeID','$Type');\"><i class=\"fa fa-trash\"></i></span></a>";
        
        $Relation = $SelectOption." "."$DeleteLink";

        $newArray[] = array($functions->translate('Options') => $Relation);
      }

      if (!empty($newArray)) {
        echo json_encode($newArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getTableSelectOptions");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getTableSelectOptions");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getTableGroupFilterOptions'])) {
  $TypeID = $_POST['TypeID'];
  $FieldID = $_POST['FieldID'];
  $Type = $_POST['Type'];

  switch ($Type) {
    case "ci":
      $TableName = "cmdb_ci_fieldslist";
      $RelatedTypeIDField = "RelatedCITypeID";
      break;
    case "itsm":
      $TableName = "itsm_fieldslist";
      $RelatedTypeIDField = "RelatedTypeID";
      break;
    case "form":
      $TableName = "forms_fieldslist";
      $RelatedTypeIDField = "RelatedFormID";
      break;
    default:
      // Default case if $Type doesn't match any of the specified values
      break;
  }

  $sql = "SELECT GroupFilterOptions
          FROM $TableName
          WHERE ID = ? AND $RelatedTypeIDField = ?";

  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $FieldID, $TypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      $newArray = [];

      while ($row = mysqli_fetch_array($result)) {
        $GroupFilterOptions = $row['GroupFilterOptions'];
      }

      // Split the string by '#' to get the individual group numbers
      $resultArray = explode('#', $GroupFilterOptions);

      foreach ($resultArray as $groupNumber) {
        $GroupFilterOption = $functions->translate($functions->getGroupName($groupNumber));

        $DeleteLink = "<a href=\"javascript:void(0);\" title=\"" . $functions->translate("Delete") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteGroupFilterOption('$groupNumber','$FieldID','$TypeID','$Type');\"><i class=\"fa fa-trash\"></i></span></a>";

        $Relation = $GroupFilterOption . " " . "$DeleteLink";

        $newArray[] = array($functions->translate("Groups added") => $Relation);
      }

      if (!empty($newArray)) {
        echo json_encode($newArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "tableGroupFilterOptions");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "tableGroupFilterOptions");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['deleteCIFieldRelation'])) {
  $TableName = $_POST['TableName'];
  $FieldName = $_POST['FieldName'];
  $FieldID = $_POST['FieldID'];
  $CITypeID = $_POST['CITypeID'];

  $EntryToDelete = "$TableName,$FieldName";
  $pairs = [];
  $result = "";

  $sql = "SELECT RelationsLookup
          FROM cmdb_ci_fieldslist
          WHERE ID = ? AND RelatedCITypeID = ?";

  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $FieldID,$CITypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if ($result) {
      while ($row = mysqli_fetch_array($result)) {
        $RelationsLookup = $row['RelationsLookup'];
      }

      // If SelectFieldOptions is not empty, split the string by comma to get pairs of tablename and fieldname
      if (!empty($RelationsLookup)) {
        $RelationsLookup = trim($RelationsLookup, '#');
        $pairs = explode('#', $RelationsLookup);
      } else {
        // If SelectFieldOptions is empty, initialize $pairs as an empty array
        $pairs = [];
      }

      // If the entry exists, delete it from the options array
      foreach ($pairs as $key => $pair) {

        if ($pair == $EntryToDelete) {
          // Remove the option from the array
          unset($pairs[$key]);
        }
      }
      
      $NewStringValue = implode("#", $pairs);
      $NewStringValue = trim($NewStringValue, '#');

      updateCIRelationLookup($FieldID, $NewStringValue);

    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "deleteCIFieldRelation");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }

    echo json_encode(["Result" => $functions->translate("success")]);

  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "deleteCIFieldRelation");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['deleteFieldSelectOption'])) {
  $OptionToDelete = $_POST['Option'];
  $FieldID = $_POST['FieldID'];
  $TypeID = $_POST['TypeID'];
  $Type = $_POST['Type'];

  switch ($Type) {
    case "ci":
      $TableName = "cmdb_ci_fieldslist";
      $RelatedTypeIDField = "RelatedCITypeID";
      break;
    case "itsm":
      $TableName = "itsm_fieldslist";
      $RelatedTypeIDField = "RelatedTypeID";
      break;
    case "form":
      $TableName = "forms_fieldslist";
      $RelatedTypeIDField = "RelatedFormID";
      break;
    default:
      // Default case if $Type doesn't match any of the specified values
      // You can handle this case as needed
      break;
  }

  $sql = "SELECT SelectFieldOptions
          FROM $TableName
          WHERE ID = ? AND $RelatedTypeIDField = ?";

  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $FieldID,$TypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
       while ($row = mysqli_fetch_array($result)) {
        $SelectFieldOptions = $row['SelectFieldOptions'];
      }

      // If SelectFieldOptions is not empty, split the string by # to get individual options
      if (!empty($SelectFieldOptions)) {
        $SelectFieldOptions = trim($SelectFieldOptions, '#');
        $options = explode('#', $SelectFieldOptions);
      } else {
        // If SelectFieldOptions is empty, initialize $options as an empty array
        $options = [];
      }
    
      // If the entry exists, delete it from the options array
      foreach ($options as $key => $option) {
        // Check if the option matches the one to delete
        // Parse the value attribute from the option tag
        preg_match('/value="(.*?)"/', $option, $matches);
        if (isset($matches[1]) && $matches[1] == $OptionToDelete) {
          // Remove the option from the array
          unset($options[$key]);
        }
      }

      sort($options);

      // Rebuild the string with the remaining options
      $NewStringValue = implode("#", $options);
      $NewStringValue = trim($NewStringValue, '#');

      // Update the database with the new options
      updateSelectOptions($FieldID, $NewStringValue, $TableName);

      echo json_encode(["Result" => $functions->translate("success")]);
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "deleteFieldSelectOption");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "deleteFieldSelectOption");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['deleteGroupFilterOption'])) {
  $GroupIDToDelete = $_POST['Option'];
  $FieldID = $_POST['FieldID'];
  $TypeID = $_POST['TypeID'];
  $Type = $_POST['Type'];

  switch ($Type) {
    case "ci":
      $TableName = "cmdb_ci_fieldslist";
      $RelatedTypeIDField = "RelatedCITypeID";
      break;
    case "itsm":
      $TableName = "itsm_fieldslist";
      $RelatedTypeIDField = "RelatedTypeID";
      break;
    case "form":
      $TableName = "forms_fieldslist";
      $RelatedTypeIDField = "RelatedFormID";
      break;
    default:
      // Handle unknown type
      echo json_encode(["error" => "Invalid type provided."]);
      exit;
  }

  $sql = "SELECT GroupFilterOptions FROM $TableName WHERE ID = ? AND $RelatedTypeIDField = ?";
  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $FieldID, $TypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $row = mysqli_fetch_assoc($result);
      $GroupFilterOptions = $row['GroupFilterOptions'];

      // If GroupFilterOptions is not empty, split the string by # to get individual group IDs
      if (!empty($GroupFilterOptions)) {
        $groupIDs = explode('#', $GroupFilterOptions);
      } else {
        // If GroupFilterOptions is empty, initialize $groupIDs as an empty array
        $groupIDs = [];
      }

      // Remove the group ID from the array if it exists
      $groupIDs = array_filter($groupIDs, function ($groupID) use ($GroupIDToDelete) {
        return $groupID != $GroupIDToDelete;
      });

      // Rebuild the string with the remaining group IDs
      $NewGroupFilterOptions = implode('#', $groupIDs);

      // Update the database with the new options
      $updateSql = "UPDATE $TableName SET GroupFilterOptions = ? WHERE ID = ? AND $RelatedTypeIDField = ?";
      $updateStmt = mysqli_prepare($conn, $updateSql);

      if ($updateStmt) {
        mysqli_stmt_bind_param($updateStmt, "sii", $NewGroupFilterOptions, $FieldID, $TypeID);
        mysqli_stmt_execute($updateStmt);

        echo json_encode(["Result" => "success"]);
      } else {
        $error = "Statement preparation failed: " . mysqli_error($conn);
        $functions->errorlog($error, "updateGroupFilterOptions");
        echo json_encode(["error" => "An error occurred. Please try again later."]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "fetchGroupFilterOptions");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }

    mysqli_stmt_close($stmt);
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "fetchGroupFilterOptions");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['createRelationLookup'])) {
  $TableName = $_POST['LookupTable'];
  $FieldName = $_POST['LookupField'];
  $FieldID = $_POST['FieldID'];
  $CITypeID = $_POST['CITypeID'];

  $EntryToAdd = "$TableName,$FieldName";

  $sql = "SELECT RelationsLookup
          FROM cmdb_ci_fieldslist
          WHERE ID = ? AND RelatedCITypeID = ?";

  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $FieldID,$CITypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $RelationsLookup = $row['RelationsLookup'];
      }

      // If RelationsLookup is not empty, split the string by comma to get pairs of tablename and fieldname
      if (!empty($RelationsLookup)) {
        $RelationsLookup = trim($RelationsLookup, '#');
        $pairs = explode('#', $RelationsLookup);
      } else {
        // If RelationsLookup is empty, initialize $pairs as an empty array
        $pairs = [];
      }

      // Add new entry
      $pairs[] = $EntryToAdd;

      $NewStringValue = implode("#", $pairs);
      $NewStringValue = trim($NewStringValue, '#');      
      
      updateCIRelationLookup($FieldID, $NewStringValue);

      echo json_encode(["Result" => $functions->translate("success")]);
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "createRelationLookup");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "createRelationLookup");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['createSelectOption'])) {
  $SelectFieldOption = $_POST['SelectFieldOption'];
  $FieldID = $_POST['FieldID'];
  $TypeID = $_POST['TypeID'];
  $Type = $_POST['Type'];

  switch ($Type) {
    case "ci":
      $TableName = "cmdb_ci_fieldslist";
      $RelatedTypeIDField = "RelatedCITypeID";
      break;
    case "itsm":
      $TableName = "itsm_fieldslist";
      $RelatedTypeIDField = "RelatedTypeID";
      break;
    case "form":
      $TableName = "forms_fieldslist";
      $RelatedTypeIDField = "RelatedFormID";
      break;
    default:
      // Default case if $Type doesn't match any of the specified values
      // You can handle this case as needed
      break;
  }

  $EntryToAdd = "<option value=\"$SelectFieldOption\">$SelectFieldOption</option>";

  $sql = "SELECT SelectFieldOptions
          FROM $TableName
          WHERE ID = ? AND $RelatedTypeIDField = ?";

  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $FieldID, $TypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $SelectFieldOptions = $row['SelectFieldOptions'];
      }

      // If SelectFieldOptions is not empty, split the string by comma to get pairs of tablename and fieldname
      if (!empty($SelectFieldOptions)) {
        $SelectFieldOptions = trim($SelectFieldOptions, '#');
        $pairs = explode('#', $SelectFieldOptions);
      } else {
        // If SelectFieldOptions is empty, initialize $pairs as an empty array
        $pairs = [];
      }

      // Add new entry
      $pairs[] = $EntryToAdd;
      // Sor ASC
      sort($pairs);

      $NewStringValue = implode("#", $pairs);
      $NewStringValue = trim($NewStringValue, '#');
      
      updateSelectOptions($FieldID, $NewStringValue, $TableName);

      echo json_encode(["Result" => $functions->translate("success")]);
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "createRelationLookup");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "createRelationLookup");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getITSMLog'])) {
  $userType = $_SESSION['usertype'];
  if ($userType == "2") {
    echo json_encode([]);
    return;
  }

  $ITSMID = $_POST['ITSMID'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $allowDelete = $_POST['AllowDelete'];

  $sql = "SELECT LogActionDate, LogActionText, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName, users.ID AS UserID, users.Username
          FROM itsm_log
          LEFT JOIN users ON itsm_log.RelatedUserID = users.ID
          WHERE itsm_log.RelatedElementID = ? AND itsm_log.RelatedType = ?
          ORDER BY LogActionDate DESC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $ITSMID, $ITSMTypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $myFormatForView = convertToDanishTimeFormat($row['LogActionDate']);
        $myFormatForView = "<p>$myFormatForView</p>";
        $text = $row['LogActionText'];
        $text = "<p>$text</p>";
        $user = $row['FullName'];
        $username = $row['Username'];
        $user = "<p title=\"$user\">$username</p>";
        $resultArray[] = array($functions->translate("Date") => $myFormatForView, $functions->translate("Actions") => $text, $functions->translate("User") => $user);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getITSMLog");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getITSMLog");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getNewsCategories'])) {
  if (in_array("100020", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100020");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $sql = "SELECT news_categories.ID, news_categories.Name, UserGroups.GroupName AS GroupName, roles.RoleName AS RoleName
          FROM news_categories
          LEFT JOIN roles ON news_categories.RelatedRole = roles.ID
          LEFT JOIN (
          SELECT ID AS ID, GroupName AS GroupName, Description AS Description, RelatedModuleID AS RelatedModuleID, Active AS Active, 'Standard' AS Type
          FROM usergroups
          UNION
          SELECT ID AS ID, GroupName AS GroupName, Description AS Description, RelatedModuleID AS RelatedModuleID, Active AS Active, 'System' AS Type
          FROM system_groups) AS UserGroups ON news_categories.RelatedGroupID = UserGroups.ID
          ORDER BY Name ASC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $ViewLink = "<a href=\"javascript:void(0);\"><span class=\"badge bg-gradient-success\" onclick=\"viewNewsCategory($ID);\" title=\"". $functions->translate("Edit")."\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
        $DeleteLink = "<a href=\"javascript:void(0);\" title=\"" . $functions->translate("Delete") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteNewsCategory($ID);\"><i class=\"fa fa-trash\"></i></span></a>";

        $Name = $row['Name'];
        $GroupName = $row['GroupName'];
        $RoleName = $row['RoleName'];
        $resultArray[] = array($functions->translate("Actions") => "$ViewLink $DeleteLink", $functions->translate("Name") => $Name, $functions->translate("Group") => $GroupName, $functions->translate("Role") => $RoleName);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getNewsCategories");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getNewsCategories");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getNewsFilters'])) {
  if (in_array("100020", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100020");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $sql = "SELECT news_cve_filters.ID, news_cve_filters.Product
          FROM news_cve_filters
          ORDER BY Product ASC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $DeleteLink = "<a href=\"javascript:void(0);\" title=\"" . $functions->translate("Delete") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteNewsCVEFilter($ID);\"><i class=\"fa fa-trash\"></i></span></a>";

        $Product = $row['Product'];
        $resultArray[] = array($functions->translate("Filter") => $Product,"" => "$DeleteLink");
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getNewsCategories");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getNewsCategories");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getUserNewsUserGroups'])) {

  $sql = "SELECT ID, GroupName AS GroupName
          FROM usergroups
          UNION
          SELECT ID, GroupName AS GroupName
          FROM system_groups
          ORDER BY GroupName ASC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $GroupName = $row['GroupName'];
        $resultArray[] = array("ID" => "$ID", "GroupName" => $GroupName);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getUserGroups");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getUserGroups");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getCVEDetails'])) {

  $CVEID = $_POST['CVEID'];
  $ID = "";
  $Headline = "";
  $Content = "";
  $resultArray = [];
  $result = "";

  $sql = "SELECT ID, Headline, Content, CVEID
          FROM news
          WHERE CVEID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $CVEID);

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $Headline = $row['Headline'];
        $Content = $row['Content'];

        // Remove button from content
        // The regular expression pattern to match the button element with the dynamic value
        $pattern = '/<br><br><button id="createChangeFromCVE" class="btn btn-sm btn-info" onclick="\(async function\(\) \{ event\.stopPropagation\(\); await runModalCreateITSM\(3\); fillCreateChangeFromCVE\(\'[^\']+\'\); \}\)\(\);">Create Change<\/button>/';

        $Content = preg_replace($pattern, '', $Content);
        $resultArray[] = array("ID" => "$ID", "Headline" => $Headline, "Content" => $Content);
      }
      
      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getCVEDetails");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getCVEDetails");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}


if (isset($_GET['getUserNewsCategoryRoles'])) {

  $sql = "SELECT `ID`, `RoleName`, `Description`, `Active`
          FROM `roles`
          WHERE Active = 1
          ORDER BY RoleName ASC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $RoleName = $row['RoleName'];
        $resultArray[] = array("ID" => "$ID", "RoleName" => $RoleName);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getUserNewsCategoryRoles");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getUserNewsCategoryRoles");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getNewsArticle'])) {

  $NewsID = $_POST['NewsID'];

  $sql = "SELECT `ID`, `Headline`, `Content`, `CreatedByUserID`, `NewsWriter`, `DateCreated`, `RelatedCategory`, `CommentsAllowed`, `Active` 
          FROM `news`
          WHERE ID = $NewsID;";

  $stmt = mysqli_prepare($conn, $sql);

  if (in_array("100001", $UserGroups) || in_array("100032", $UserGroups)) {
    $Disabled = "0";
  } else {
    $Disabled = "1";
  }

  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $Headline = $row['Headline'];
        $Content = $row['Content'];
        $CreatedByUserID = $row['CreatedByUserID'];
        $NewsWriter = $row['NewsWriter'];
        $DateCreated = $row['DateCreated'];  // This line was missing and is assumed based on your previous schema
        $RelatedCategory = $row['RelatedCategory'];
        $CommentsAllowed = $row['CommentsAllowed']; // Assumed based on your previous schema
        $Active = $row['Active']; // Assumed based on your previous schema

        $resultArray[] = array(
          "ID" => $ID,
          "Headline" => $Headline,
          "Content" => $Content,
          "CreatedByUserID" => $CreatedByUserID,
          "NewsWriter" => $NewsWriter,
          "DateCreated" => $DateCreated,
          "RelatedCategory" => $RelatedCategory,
          "CommentsAllowed" => $CommentsAllowed,
          "Active" => $Active,
          "Disabled" => $Disabled
        );
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getUserGroups");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getUserGroups");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getNewsCategory'])) {

  $CategoryID = $_POST['CategoryID'];

  // Modify SQL query to select from news_categories
  $sql = "SELECT `ID`, `Name`, `RelatedGroupID`, `RelatedRole`, `Active` 
          FROM `news_categories`
          WHERE ID = ?"; // Use a placeholder for security

  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $CategoryID); // Bind the CategoryID parameter
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_assoc($result)) { // Using fetch_assoc to get an associative array
        $resultArray[] = array(
          "ID" => $row['ID'],
          "Name" => $row['Name'],
          "RelatedGroupID" => $row['RelatedGroupID'],
          "RelatedRole" => $row['RelatedRole'],
          "Active" => $row['Active']
        );
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getUserGroups"); // assuming errorlog is a function you've created
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getUserGroups"); // assuming errorlog is a function you've created
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}


if (isset($_GET['getITSMWorkFlows'])) {
  $userType = $_SESSION['usertype'];

  if ($userType == "2") {
    echo json_encode([]);
    return;
  }

  $ITSMTypeID = $_GET['ITSMTypeID'];

  $sql = "SELECT ID, WorkFlowName
          FROM workflows_template
          WHERE RelatedModuleID = ? AND Active = 1";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $ITSMTypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $workFlowName = $row['WorkFlowName'];
        $resultArray[] = array("ID" => $ID, "WorkFlowName" => $workFlowName);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getITSMWorkFlows");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getITSMWorkFlows");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getActiveModuleWorkFlow'])) {
  $userType = $_SESSION['usertype'];

  if ($userType == "2") {
    echo json_encode([]);
    return;
  }

  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMID = $_POST['ITSMID'];

  $sql = "SELECT workflowsteps.ID AS StepID, StepOrder, StepName, workflowsteps.Description, workflowstatus.Name AS StatusName,workflowsteps.RelatedStatusID AS StatusID, workflowsteps.RelatedUserID, users.Username AS Responsible, taskslist.Deadline
          FROM workflowsteps
          LEFT JOIN workflows ON workflows.ID = workflowsteps.RelatedWorkFlowID
          LEFT JOIN workflowstatus ON workflowsteps.RelatedStatusID = workflowstatus.ID
          LEFT JOIN users ON workflowsteps.RelatedUserID = users.ID
          LEFT JOIN taskslist ON workflowsteps.RelatedTaskID = taskslist.ID
          WHERE workflows.ID = workflowsteps.RelatedWorkFlowID AND workflows.RelatedElementID = ? AND workflows.RelatedElementTypeID = ?
          ORDER BY workflows.ID, StepOrder ASC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $ITSMID, $ITSMTypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $StepID = $row['StepID'];
        $StepName = $row['StepName'];
        $Description = $row['Description'];
        $StatusName = $row['StatusName'];
        $WFStepName = "<small title='$Description'>$StepName</small>";
        $RelatedUserID = $row['RelatedUserID'];
        $Username = getUserName($RelatedUserID);
        $UserFullName = $functions->getUserFullName($RelatedUserID);
        $RelatedUser = "<small title='$UserFullName'>$Username</small>";
        $Deadline = $functions->convertToDanishDateTimeFormat($row['Deadline']);
        $Deadline = "<small>$Deadline</small>";
        $StatusName = "<small>$StatusName</small>";
        $LinkEdit = "<a href=\"javascript:editWorkFlowTask($StepID);\"><span class=\"badge bg-gradient-success\"  title=\"" . _("Edit") . "\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
        $LinkDelete = "<a href=\"javascript:deleteWorkFlowTask($StepID);\"><span class=\"badge bg-gradient-danger\"  title=\"" . _("Delete") . "\"><i class=\"fa fa-trash\"></i></span></a>";
        $Link = $LinkEdit . $LinkDelete;
        $resultArray[] = array("ID" => $StepID, "" => $Link, $functions->translate("Task") => "$WFStepName", "Status" => $StatusName, $functions->translate("User") => $RelatedUser, $functions->translate("Deadline") => "$Deadline");
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getActiveModuleWorkFlow");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getActiveModuleWorkFlow");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getITSMParticipants'])) {
  $userType = $_SESSION['usertype'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMID = $_POST['ITSMID'];
  $Type = $_POST['Type'];
  $tempArray = [];
  $finalParticipants = "<small>";

  if ($userType == "2") {
    echo json_encode([]);
    return;
  } else {
    $sql = "SELECT itsm_participants.UserID, users.Username, itsm_participants.ModuleID, itsm_participants.ElementID
            FROM itsm_participants
            LEFT JOIN users ON itsm_participants.UserID = users.ID
            WHERE itsm_participants.ModuleID = ? AND ElementID = ?";
  }

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $ITSMTypeID, $ITSMID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      while ($row = mysqli_fetch_array($result)) {
        $participantID = $row['UserID'];
        $username = $row['Username'];
        $moduleID = $row['ModuleID'];
        $elementID = $row['ElementID'];
        $participant = $functions->getUserFullName($participantID) . " (" . $username . ")";
        $deleteLink = "<a href=\"javascript:removeITSMParticipant('$participantID','$moduleID','$elementID','$Type');\"><i class=\"fas fa-trash\"></i></a>";
        $participant = "$participant $deleteLink";
        array_push($tempArray, $participant);
      }

      mysqli_free_result($result);
      $AddText = $functions->translate("Add");
      $btnAddParticipant = "<button class=\"btn btn-sm btn-success\" type=\"button\" onclick=\"addITSMParticipant('$ITSMTypeID','$ITSMID','$Type');\">$AddText</button>";
      $finalParticipants .= implode(", ", $tempArray);
      $finalParticipants .= "</small>";
      $resultArray[] = array("Participants" => $finalParticipants, "BtnAddParticipant" => $btnAddParticipant);

      if (!empty($resultArray)) {
        echo json_encode($resultArray);
      } else {
        $resultArray[] = array("Participants" => "none", "BtnAddParticipant" => $btnAddParticipant);
        echo json_encode($resultArray);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getITSMParticipants");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getITSMParticipants");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getITSMComments'])) {
  $UserID = $_SESSION['id'];
  $UserLanguageID = $functions->getUserLanguage($UserID);
  $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);

  $userType = $_SESSION['usertype'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMID = $_POST['ITSMID'];
  $currentUserID = $_SESSION['userID']; // Assuming you have the user ID stored in the session
  $counter = 0;
  $Type = "modal";
  $AddText = $functions->translate("Add");
  $btnAddComment = "<button class=\"btn btn-sm btn-success float-end\" type=\"button\" onclick=\"addITSMComment('$ITSMTypeID','$ITSMID','$Type');\">$AddText</button>";

  if ($userType == "2") {
    $sql = "SELECT itsm_comments.ID, itsm_comments.RelatedElementID, itsm_comments.ITSMType, itsm_comments.UserID, itsm_comments.Text, itsm_comments.Date, itsm_comments.Internal
                FROM itsm_comments
                WHERE itsm_comments.RelatedElementID = ? AND ITSMType = ? AND Internal != '1'
                ORDER BY itsm_comments.Date DESC";
  } else {
    $sql = "SELECT itsm_comments.ID, itsm_comments.RelatedElementID, itsm_comments.ITSMType, itsm_comments.UserID, itsm_comments.Text, itsm_comments.Date, itsm_comments.Internal
                FROM itsm_comments
                WHERE itsm_comments.RelatedElementID = ? AND ITSMType = ?
                ORDER BY itsm_comments.Date DESC";
  }

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $ITSMID, $ITSMTypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      while ($row = mysqli_fetch_array($result)) {
        $counter++;
        $expanded = ($counter <= 1) ? "true" : "false";
        $show = ($counter <= 0) ? "show" : "";
        $commentID = $row['ID'];
        $userID = $row['UserID'];
        $userFullName = $functions->getUserFullName($userID);
        $username = getUserName($userID);
        $userFullName = "$userFullName ($username)";
        $commentText = $row['Text'];
        $commentDate = convertToDanishTimeFormat($row['Date']);
        $commentType = $row['Internal'];
        $commentTypeName = ($commentType == "0") ? "<span class='badge bg-gradient-info'>" . _("External") . "</span>" : "<span class='badge bg-gradient-danger'>" . _("Internal") . "</span>";
        $deleteLink = "<a href=\"javascript:deleteITSMComment($commentID,$ITSMTypeID,$ITSMID,'modal','$UserLanguageCode');\"><i class=\"fas fa-trash\"></i></a>";

        // Check read status for the current user
        $readStatus = checkReadStatus($commentID, $currentUserID);
        $numberOfUnreadComments = getNumberOfUnreadITSMComments($ITSMTypeID, $ITSMID, $userID);

        if($readStatus === 1){
          $readStatus = "<span class=\"badge badge-pill bg-gradient-warning\" title=\"". $functions->translate("New")."\"><i class=\"fa-regular fa-envelope\"></i></span>";
        } else {
          $readStatus = "";
        }

        $entry = "<div class=\"accordion-item\">
                    <small class=\"accordion-header float-left\" id=\"heading$commentID\">
                      <a href=\"javascript:void(0);\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapse$commentID\" aria-expanded=\"$expanded\" aria-controls=\"collapse$commentID\">
                        $userFullName ($commentDate) $commentTypeName $readStatus
                      </a>
                    </small>
                    <div id=\"collapse$commentID\" class=\"accordion-collapse collapse $show\" aria-labelledby=\"heading$commentID\">
                        <div class=\"accordion-body text-wrap\">
                        <div class=\"col-lg-12 col-sm-12 col-xs-12\">
                          <div style=\"word-wrap: break-word; overflow-y: auto; overflow-x: auto;\" class=\"resizable_textarea form-control\" id=\"commentField$commentID\" name=\"commentField$commentID\" title=\"Double click to edit\" autocomplete=\"off\" ondblclick=\"toggleCKEditor('commentField$commentID','50');toggleShowSaveBtn('saveLink$commentID');\">$commentText
                          </div>
                          <div style=\"display: flex; align-items: center; gap: 10px;\">
                            <a href=\"javascript:toggleCKEditor('commentField$commentID','50');toggleShowSaveBtn('saveLink$commentID');\"><i class=\"fa-solid fa-pen fa-sm\" title=\"Double click on field to edit\"></i></a>
                            $deleteLink
                            <a href=\"javascript:saveITSMComment($commentID,'commentField$commentID',$ITSMTypeID,$ITSMID,'modal','$UserLanguageCode');\" id=\"saveLink$commentID\" class=\"save-link float-end\" style=\"display: none;\">Save</a>
                          </div>
                        </div>
                      </div>
                  </div>";

        $resultArray[] = array("Comments" => $entry);
      }

      mysqli_free_result($result);

      // Add the unread count to the result array
      $resultArray[] = array("Unread" => $numberOfUnreadComments);
      $resultArray[] = array("BtnAddComment" => $btnAddComment);

      if (!empty($resultArray)) {
        echo json_encode($resultArray);
      } else {
        $resultArray[] = array("Comments" => "", "Unread" =>0, "BtnAddComment" => $btnAddComment);
        echo json_encode($resultArray);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getITSMComments");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getITSMComments");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getITSMArchive'])) {
  try {
    $ITSMTypeID = (int) $_POST['ITSMTypeID'];
    $ITSMID = (int) $_POST['ITSMID'];

    $requiredGroups = ["100004", "100001"];
    $functions->checkUserGroups($requiredGroups, $UserGroups);

    $resultArray = $functions->getITSMArchive($ITSMTypeID, $ITSMID);

    echo json_encode($resultArray);
  } catch (Exception $e) {
    $functions->errorlog($e->getMessage(), "getITSMArchive");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['restoreITSMArchive'])) {
  $SessionUserID = $_SESSION["id"];
  $ArchiveID = $_POST['ArchiveID'];
  // Assuming $conn is your database connection
  $ModuleID = getITSMModuleIDFromArchive($ArchiveID);
  $ITSMID = getITSMIDFromArchive($ArchiveID);
  $Version = getITSMVersionFromArchive($ArchiveID);
  $ITSMTableName = $functions->getITSMTableName($ModuleID);

  // Ensure your SQL uses the correct variable for ID
  $sql = "UPDATE $ITSMTableName AS ik
          INNER JOIN itsm_knowledge_archive AS ika ON ik.ID = ika.RelatedDocumentID
          SET ik.Content = ika.Content
          WHERE ika.ID = ?"; // Use placeholder for prepared statement

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $ArchiveID); // Bind $ArchiveID as integer
    $executed = mysqli_stmt_execute($stmt);
    $affectedRows = mysqli_stmt_affected_rows($stmt);
    if ($executed && $affectedRows > 0) {
      $LogActionText = "User restored the document from version $Version";
      createITSMLogEntry($ITSMID, $ModuleID, $SessionUserID, $LogActionText);
      echo json_encode(['Result' => true]);
    } else {
      echo json_encode(['Result' => false]);
    }
  } else {
    echo json_encode(['Result' => false]);
  }
}

if (isset($_GET['searchITSM'])) {
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $SearchTerm = $_POST['SearchTerm'];
  $Inactive = $_POST['Inactive'];
  $ITSMModuleType = $functions->getITSMModuleType($ITSMTypeID);
  $StatusArray = array();

  switch ($ITSMModuleType) {
    case 1:
      if ($Inactive == "2") {
        $StatusArray = $functions->getITSMClosedStatus($ITSMTypeID);
      } else {
        $StatusArray = getITSMOpenStatus($ITSMTypeID);
      }
      break;
    case 2:
      if ($Inactive == "2") {
        $StatusArray[] = "7";
      } else {
        $StatusArray = array("1","2","3","4","5","6");
      }
      break;
    case 3:
      if ($Inactive == "2") {
        $StatusArray[] = "0";
      } else {
        $StatusArray[] = "4";
      }
      break;
    case 4:
      if ($Inactive == "2") {
        $StatusArray = $functions->getITSMClosedStatus($ITSMTypeID);
      } else {
        $StatusArray = getITSMOpenStatus($ITSMTypeID);
      }
      break;
    default:
      // Default case if module type is not within the specified range
      if ($Inactive == "2") {
        $StatusArray = $functions->getITSMClosedStatus($ITSMTypeID);
      } else {
        $StatusArray = getITSMOpenStatus($ITSMTypeID);
      }
      break;
  }

  $SearchResultArray = SearchITSM($ITSMTypeID, $SearchTerm, $StatusArray);

  if (!empty($SearchResultArray)) {
    echo json_encode($SearchResultArray);
  } else {
    $SearchResultArray[] = array("Result" => "none");
    echo json_encode($SearchResultArray);
  }
}

if (isset($_GET['quickSearch'])) {
  $SearchTerm = $_GET['SearchTerm'];

  $SearchResultArray = QuickSearch($SearchTerm);

  if (!empty($SearchResultArray)) {
    echo json_encode($SearchResultArray);
  } else {
    $SearchResultArray[] = array("Result" => "none");
    echo json_encode($SearchResultArray);
  }
}

if (isset($_GET['HelpdeskSearch'])) {
  $SearchValue = $_POST['SearchValue'];
  $counter = 0;

  $SearchResultArray = HelpdeskSearch($SearchValue);

  if (!empty($SearchResultArray)) {
    echo json_encode($SearchResultArray);
  } else {
    $SearchResultArray[] = array("Result" => "none");
    echo json_encode($SearchResultArray);
  }
}

if (isset($_GET['getITSMParticipantUsers'])) {
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMID = $_POST['ITSMID'];
  $UserType = $_SESSION['usertype'];
  if ($UserType == "2") {
    $resultarray[] = array([]);
    echo json_encode($resultarray);
    return;
  }

  $sql = "SELECT users.ID, users.Username
          FROM users
          WHERE users.ID NOT IN (SELECT itsm_participants.UserID
          FROM itsm_participants
          LEFT JOIN users ON itsm_participants.UserID = users.ID
          WHERE itsm_participants.ModuleID = ? AND ElementID = ?) AND Active = 1
          ORDER BY users.Firstname ASC, users.Lastname ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ITSMTypeID, $ITSMID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $UsersID = $row["ID"];
    $Username = $row["Username"];
    $UserFullName = $functions->getUserFullName($UsersID) . " (" . $Username . ")";
    $resultarray[] = array("UserID" => $UsersID, "FullName" => $UserFullName);
  }

  if (!empty($resultarray)) {
    echo json_encode($resultarray);
  } else {
    $resultarray[] = array([]);
    echo json_encode($resultarray);
  }
}

if (isset($_GET['addITSMParticipant'])) {
    try {
        $ITSMTypeID = (int)$_POST['ITSMTypeID'];
        $ITSMID = (int)$_POST['ITSMID'];
        $NewParticipant = (int)$_POST['NewParticipant'];

        $resultArray = $functions->addITSMParticipant($ITSMTypeID, $ITSMID, $NewParticipant);

        // Add translated text to the result
        if ($resultArray[0]["Result"] === "success") {
            $resultArray[0]["Message"] = $functions->translate("User added as participant");
        }

        echo json_encode($resultArray);
    } catch (Exception $e) {
        echo json_encode(["Result" => "error", "Message" => $functions->translate("An unexpected error occurred.")]);
    }
}

if (isset($_GET['addITSMComment'])) {
  $SessionUserID = $_SESSION["id"];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMID = $_POST['ITSMID'];
  $Comment = $_POST['Comment'];
  $Customer = $_POST['Customer'];
  $Internal = $_POST['Internal'];

  if ($Internal == "2") {
    $Internal = "0";
  }

  if ($Comment !== "") {
    
    $sql = "INSERT INTO itsm_comments (RelatedElementID, ITSMType, UserID, Text, Internal) VALUES (?,?,?,?,?);";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("sssss", $ITSMID, $ITSMTypeID, $SessionUserID, $Comment, $Internal);
    $stmt->execute();

    $resultarray[] = array("Result" => "success");
    if($Customer !== "0"){
      if ($Internal == "0") {
        $TemplateID = "1";
        sendITSMMailTemplate($ITSMTypeID, $ITSMID, $Customer, $Comment, $TemplateID);
      }
    }
    
    // Lets log the activity
    $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
    $Headline = $functions->translate("Commented") . " " . strtolower($ITSMTypeName) . " " . $ITSMID;
    $ActivityText = "<b>" . $functions->translate("Commented") . "<br><br>" . $CommentRaw;
    logActivity($ITSMID, $ITSMTypeID, $Headline, $ActivityText, "javascript:javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');");

  } else {
    $resultarray[] = array("Result" => "fail");
  }

  echo json_encode($resultarray);
}

if (isset($_GET['removeITSMParticipant'])) {
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMID = $_POST['ITSMID'];
  $Participant = $_POST['Participant'];

  $sql = "DELETE FROM itsm_participants WHERE ModuleID = ? AND ElementID = ? AND UserID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sss", $ITSMTypeID, $ITSMID, $Participant);
  $stmt->execute();

  $resultarray[] = array("Result" => "success");
  echo json_encode($resultarray);
}

if (isset($_GET['createITSMWorkFlow'])) {
  $UserID = ($_SESSION["id"]);
  $WorkFlowID = $_GET['WorkFlowID'];
  $ITSMTypeID = $_GET['ITSMTypeID'];

  $ITSMID = $_GET['ITSMID'];
  if ($ITSMTypeID == "6") {
    $RedirectPage = "projects_view.php?projectid=$ITSMID";
  } elseif ($ITSMTypeID == "13") {
    $RedirectPage = "projects_tasks_view.php?projecttaskid=$ITSMID";
  } else {
    $RedirectPage = "javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');";
  }

  try {
    $WorkFlowID = createWorkFlow($ITSMID, $WorkFlowID, $UserID, $RedirectPage, $ITSMTypeID);
  } catch (Exception $e) {
    $errorMessage = $e->getMessage();
    $functions->errorlog($errorMessage, "createWorkFlow");
    // Handle the error in some way, e.g., display an error message to the user
    $resultarray[] = array("Result" => "$errorMessage");
    echo json_encode($resultarray);
    return;
  }

  $LogActionText = "Added Workflow: $WorkFlowID";
  createITSMLogEntry($ITSMID, $ITSMTypeID, $UserID, $LogActionText);

  $resultarray[] = array("Result" => "success");
  echo json_encode($resultarray);
}

if (isset($_GET['removeITSMWorkFlow'])) {
  $UserID = $_SESSION["id"];
  $Username = getUserName($UserID);

  $ITSMTypeID = $_GET['ITSMTypeID'];
  $ITSMID = $_GET['ITSMID'];
  $WorkFlowID = getWorkFlowID($ITSMID, $ITSMTypeID);

  $resultarray = array();

  // Delete workflows
  $sql = "DELETE FROM workflows WHERE RelatedElementID = ? AND RelatedElementTypeID = ?";
  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $ITSMID, $ITSMTypeID);
    $stmt->execute();
    $resultarray[] = array("Result" => "success");
  } else {
    // Handle the prepare statement error
    $resultarray[] = array("Result" => "error", "Message" => "Failed to prepare statement for deleting workflows.");
    $functions->errorlog('Prepare statement error: ' . mysqli_error($conn), "removeITSMWorkFlow");
  }

  // Delete workflow steps
  $sql = "DELETE FROM workflowsteps WHERE RelatedWorkFlowID = ?";
  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $WorkFlowID);
    $stmt->execute();
  } else {
    // Handle the prepare statement error
    $resultarray[] = array("Result" => "error", "Message" => "Failed to prepare statement for deleting workflow steps.");
    $functions->errorlog('Prepare statement error: ' . mysqli_error($conn), "removeITSMWorkFlow");
  }

  // Delete taskslist
  $sql = "DELETE FROM taskslist WHERE RelatedElementTypeID = ? AND RelatedElementID = ?";
  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $ITSMTypeID, $ITSMID);
    $stmt->execute();
  } else {
    // Handle the prepare statement error
    $resultarray[] = array("Result" => "error", "Message" => "Failed to prepare statement for deleting taskslist.");
    $functions->errorlog('Prepare statement error: ' . mysqli_error($conn), "removeITSMWorkFlow");
  }

  // Create ITSM log entry
  $LogActionText = "Removed Workflow: $WorkFlowID";
  if (!createITSMLogEntry($ITSMID, $ITSMTypeID, $UserID, $LogActionText)) {
    $resultarray[] = array("Result" => "error", "Message" => "Failed to create ITSM log entry.");
  }

  echo json_encode($resultarray);
}

if (isset($_GET['getCIFiles'])) {
  $group_array = $_SESSION['memberofgroups'];
  $CIID = $_POST['CIID'];
  $CITypeID = $_POST['CITypeID'];
  $CITableName = getCITableName($CITypeID);
  $AllowDelete = $_POST['AllowDelete'];

  $sql = "SELECT files_cis.ID, FileName, FileNameOriginal, Date, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
            FROM files_cis
            LEFT JOIN users ON files_cis.RelatedUserID = users.ID
            WHERE RelatedElementID = ? AND RelatedType = ?
            ORDER BY Date DESC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $CIID, $CITypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultarrayFiles = array();

      while ($row = mysqli_fetch_array($result)) {
        $FileID = $row['ID'];
        $FileNameOrig = $row['FileNameOriginal'];
        $FileName = $row['FileName'];
        $Ext = strtolower(pathinfo($FileName, PATHINFO_EXTENSION));
        $FileNameOnly = strtolower(pathinfo($FileName, PATHINFO_FILENAME));
        $PictureArray = array("bmp", "gif", "jpg", "png");

        if (in_array("100001", $group_array) || in_array("100015", $group_array)) {
          $DeleteLink = "<a href=\"javascript:deleteCMDBFile($FileID,$CIID,'$CITableName','$CITypeID');\"><i class=\"fas fa-trash\"></i></a>";
        } else {
          $DeleteLink = "";
        }

        if (in_array($Ext, $PictureArray)) {
          $FileNameLink = "<a class=\"spotlight\" href=\"./uploads/files_cis/$FileName\"><img src=\"./uploads/files_cis/$FileName\" style=\"width:100%;max-width:150px\"></a> $DeleteLink";
        } elseif ($Ext == "pdf") {
          $FileNameLink = "<a href=\"./uploads/files_cis/$FileName\" target='_blank'>$FileNameOrig</a> $DeleteLink";
        } else {
          $FileNameLink = "<a href='./uploads/files_cis/$FileName' download='$FileNameOrig' target='_blank'>$FileNameOrig</a> $DeleteLink";
        }

        $Date = convertToDanishTimeFormat($row['Date']);
        $resultarrayFiles[] = array("Date" => $Date, "File" => $FileNameLink);
      }

      if ($resultarrayFiles) {
        echo json_encode($resultarrayFiles, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      // Handle the query execution error
      $functions->errorlog('Query execution error: ' . mysqli_error($conn), "getCIFiles");
      echo json_encode([]);
    }
  } else {
    // Handle the prepare statement error
    $functions->errorlog('Prepare statement error: ' . mysqli_error($conn), "getCIFiles");
    echo json_encode([]);
  }
}


if (isset($_GET['getCIFilesSum'])) {
  $group_array = $_SESSION['memberofgroups'];
  $CITableName = $_POST['CITableName'];
  $CIID = $_POST['CIID'];
  $CITypeID = $_POST['CITypeID'];
  $AllowDelete = $_POST['AllowDelete'];

  $sql = "SELECT COUNT(files_cis.ID) AS Antal
            FROM files_cis
            LEFT JOIN users ON files_cis.RelatedUserID = users.ID
            WHERE RelatedElementID = ? AND RelatedType = ?
            ORDER BY Date DESC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $CIID, $CITypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultarrayFiles = array();

      while ($row = mysqli_fetch_array($result)) {
        $Antal = $row['Antal'];
        $resultarrayFiles[] = array("Antal" => $Antal);
      }

      if ($resultarrayFiles) {
        echo json_encode($resultarrayFiles, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      // Handle the query execution error
      $functions->errorlog('Query execution error: ' . mysqli_error($conn), "getCIFilesSum");
      echo json_encode([]);
    }
  } else {
    // Handle the prepare statement error
    $functions->errorlog('Prepare statement error: ' . mysqli_error($conn), "getCIFilesSum");
    echo json_encode([]);
  }
}

if (isset($_GET['getITSMFiles'])) {
  $group_array = $_SESSION['memberofgroups'];
  $ITSMID = $_POST['ITSMID'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $AllowDelete = $_POST['AllowDelete'];

  $sql = "SELECT files_itsm.ID, FileName, FileNameOriginal, Date, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
          FROM files_itsm
          LEFT JOIN users ON files_itsm.RelatedUserID = users.ID
          WHERE RelatedElementID = ? AND RelatedType = ?
          ORDER BY Date DESC";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $ITSMID, $ITSMTypeID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $PictureHeader = "<div class=\"spotlight-group\">";
      $resultarrayFiles = array();

      while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $FileNameOrig = $row['FileNameOriginal'];
        $FileName = $row['FileName'];
        $Ext = strtolower(pathinfo($FileName, PATHINFO_EXTENSION));
        $FileNameOnly = strtolower(pathinfo($FileName, PATHINFO_FILENAME));
        $PictureArray = array("bmp", "gif", "jpg", "png");

        if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
          $DeleteLink = "<a href=\"javascript:deleteDocument('$FileName','itsm');\"><i class=\"fas fa-trash\"></i></a>";
        } else {
          $DeleteLink = "";
        }

        if (in_array($Ext, $PictureArray)) {
          $FileNameLink = "<a class=\"spotlight\" href=\"./uploads/files_itsm/$FileName\"><img src=\"./uploads/files_itsm/$FileName\" style=\"width:100%;max-width:150px\"></a> $DeleteLink";
        } elseif ($Ext == "pdf") {
          $FileNameLink = "<a href=\"./uploads/files_itsm/$FileName\" target='_blank'>$FileNameOrig</a> $DeleteLink";
        } else {
          $FileNameLink = "<a href='./uploads/files_itsm/$FileName' download='$FileNameOrig' target='_blank'>$FileNameOrig</a> $DeleteLink";
        }

        $Date = convertToDanishTimeFormat($row['Date']);
        $resultarrayFiles[] = array("Date" => $Date, "File" => $FileNameLink);
      }

      $PictureFooter = "</div>";

      echo json_encode($resultarrayFiles, JSON_PRETTY_PRINT);
    } else {
      // Handle the query execution error
      $functions->errorlog('Query execution error: ' . mysqli_error($conn), "gd:getITSMFiles");
      echo json_encode([]);
    }
  } else {
    // Handle the prepare statement error
    $functions->errorlog('Query execution error: ' . mysqli_error($conn), "gd:getITSMFiles");
    echo json_encode([]);
  }
}

if (isset($_GET['getFiles'])) {
  $SessionUserID = $_SESSION['id'];
  $ElementID = $_POST['ElementID'];
  $ModuleID = $_POST['ModuleID'];

  switch ($ModuleID) {
    case "users":
      $FilesPath = "files_users";
      break;
    case "companies":
      $FilesPath = "files_companies";
      break;
    default:
      $FilesPath = getFilesPath($ModuleID);
      break;
  }

  $sql = "SELECT $FilesPath.ID, FileName, FileNameOriginal, Date, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
          FROM $FilesPath
          LEFT JOIN users ON $FilesPath.RelatedUserID = users.ID
          WHERE RelatedElementID = ?
          ORDER BY Date DESC";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ElementID);
  $stmt->execute();
  $result = $stmt->get_result();

  $resultArray = []; // Initialize an empty array to store the results

  while ($row = mysqli_fetch_assoc($result)) {
    $ID = $row['ID'];
    $FileNameOrig = $row['FileNameOriginal'];
    $FileName = $row['FileName'];
    $FullName = $row['FullName'];
    $Ext = strtolower(pathinfo($FileName, PATHINFO_EXTENSION));
    $FileNameOnly = strtolower(pathinfo($FileName, PATHINFO_FILENAME));
    $pictureArray = array("bmp", "gif", "jpg", "png");
    $DeleteLink = "<a href=\"javascript:deleteDocument('$FileName','$ModuleID');\"><i class=\"fas fa-trash\"></i></a>";

    if ($ModuleID == "7") {
      $returnValue = showFileDeleteLink($ModuleID, $ID);

      if ($returnValue == 0) {
        $DeleteLink = "";
      }
    }

    if (in_array($Ext, $pictureArray)) {
      $FileNameLink = "<a class=\"spotlight\" href=\"./uploads/$FilesPath/$FileName\"><img src=\"./uploads/$FilesPath/$FileName\" style=\"width:100%;max-width:150px\"></a> $DeleteLink";
    } elseif ($Ext == "pdf") {
      $FileNameLink = "<a href=\"./uploads/$FilesPath/$FileName\" target='_blank'>$FileNameOrig</a> $DeleteLink";
    } else {
      $FileNameLink = "<a href='./uploads/$FilesPath/$FileName' download='$FileNameOrig' target='_blank'>$FileNameOrig</a> $DeleteLink";
    }

    $Date = convertToDanishTimeFormat($row['Date']);

    $resultArrayFile = array(
      $functions->translate("Date") => $Date,
      $functions->translate("User") => $FullName,
      $functions->translate("File") => $FileNameLink
    );

    $resultArray[] = $resultArrayFile; // Add the result to the array
  }

  // If no records found, output an empty JSON array
  if (empty($resultArray)) {
    echo json_encode([]);
  } else {
    echo json_encode($resultArray);
  }
}



if (isset($_GET['getITSMSums'])) {
  $group_array = $_SESSION['memberofgroups'];
  $ITSMID = $_POST['ITSMID'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $AllowDelete = $_POST['AllowDelete'];

  $sql = "SELECT COUNT(files_itsm.ID) AS Antal
          FROM files_itsm
          LEFT JOIN users ON files_itsm.RelatedUserID = users.ID
          WHERE RelatedElementID = ? AND RelatedType = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ITSMID, $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $AntalFiles = $row['Antal'];
  }

  $sql = "SELECT COUNT(workflowsteps.ID) AS Antal
          FROM workflows
          LEFT JOIN workflowsteps ON workflows.ID = workflowsteps.RelatedWorkFlowID
          WHERE RelatedElementID = $ITSMID AND RelatedElementTypeID = '$ITSMTypeID' AND RelatedStatusID IN(1,2)";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
  while ($row = mysqli_fetch_array($result)) {
    $AntalWFT = $row['Antal'];
  }

  $resultarrayFiles[] = array("SumFiles" => $AntalFiles, "SumWFT" => $AntalWFT);

  if ($resultarrayFiles) {
    echo json_encode($resultarrayFiles, JSON_PRETTY_PRINT);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['getCIITSMRelations'])) {
  $CIID = $_POST['CIID'];
  $CITypeID = $_POST['CITypeID'];
  $CITableName = getCITableName($CITypeID);
  $AllowDelete = $_POST['AllowDelete'];

  $sql = "SELECT ID, ITSMTable, ITSMID
          FROM cmdb_ci_itsm_relations
          WHERE CITable = ? AND CIID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $CITableName, $CIID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $ID = $row['ID'];
    $ITSMID = $row['ITSMID'];
    $ITSMTableName = $row['ITSMTable'];
    $ITSMTypeID = $functions->getITSMTypeIDFromTableName($ITSMTableName);
    $Subject = getElementSubject2($ITSMID, $ITSMTableName);
    $Date = getElementDate2($ITSMID, $ITSMTableName, "Created");

    if ($AllowDelete == 1) {
      $DeleteLink = "&nbsp;&nbsp;&nbsp;<a href=\"javascript:void(0);\" title=\"" . _("Delete relation") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteITSMToElementRelation($ID, $CITypeID, $ITSMTypeID, $ITSMID);\"><i class=\"fa fa-trash\"></i></span></a>";
    } else {
      $DeleteLink = "";
    }
    $Link = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');\">$Subject</a>$DeleteLink";
    $resultarray[] = array("Type" => _("$ITSMTableName"), _("Elements") => $Link, _("Date") => $Date);
  }

  if (!empty($resultarray)) {
    echo json_encode($resultarray);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['createICSFromProject'])) {
  $ProjectID = $_GET['ProjectID'];
  $WebPage = $functions->getSettingValue(17);

  $ICSFileContent = "BEGIN:VCALENDAR
PRODID:-//Google Inc//Google Calendar 70.9054//EN
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-TIMEZONE:Europe/Copenhagen
";

  $sql = "SELECT project_tasks.ID AS ID, project_tasks.TaskName, project_tasks.Description AS Description, project_tasks.Start AS StartDate, project_tasks.Deadline AS Deadline, 'PT' AS Type
          FROM project_tasks
          WHERE project_tasks.RelatedProject = ?
          UNION 
          SELECT projects_sprints.ID AS ID, projects_sprints.ShortName AS TaskName, projects_sprints.Description AS Description, projects_sprints.Start AS StartDate, projects_sprints.Deadline AS Deadline, 'Sprint' AS Type
          FROM projects_sprints
          WHERE projects_sprints.RelatedProjectID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ProjectID, $ProjectID);
  $stmt->execute();
  $result = $stmt->get_result();

  $Counter = "1";
  while ($row = mysqli_fetch_array($result)) {
    $TaskID = $row['ID'];
    $Type = $row['Type'];
    if ($Type == 'PT') {
      $URL = $WebPage . "/projects_tasks_view.php?projecttaskid=$TaskID";
      $Summary = _("$Type: ") . $TaskID . " - " . $row['TaskName'];
      $Summary = mb_convert_encoding($Summary, 'UTF-8', 'UTF-8');
      $Summary = strip_tags(htmlspecialchars_decode($Summary));
    } else {
      $URL = $WebPage . "/projects_view.php?projectid=$ProjectID";
      $Summary = _("$Type: ") . $row['TaskName'];
      $Summary = mb_convert_encoding($Summary, 'UTF-8', 'UTF-8');
      $Summary = strip_tags(htmlspecialchars_decode($Summary));
    }

    $Description = $row['Description'];
    $Description = mb_convert_encoding($Description, 'UTF-8', 'UTF-8');
    $Description = strip_tags(htmlspecialchars_decode($Description));
    //$Description = str_replace('#', '%23', $Description);
    $Description = $Description . "\\nCreated from <a href=\"$URL\">$WebPage</a>";

    $StartDate = $row['StartDate'];
    $StartDate = strtotime($StartDate);
    $StartDate = date('Ymd\THis', $StartDate);

    $Deadline = $row['Deadline'];
    $Deadline = strtotime($Deadline);
    $Deadline = date('Ymd\THis', $Deadline);

    $today = strtotime(date("D M d, Y G:i"));
    $DateNow = date('Ymd\THis', $today);
    $Event .= "BEGIN:VEVENT
DTSTART:$StartDate
DTEND:$Deadline
DTSTAMP:$DateNow
UID:$Type-$TaskID@$WebPage
CLASS:PUBLIC
CREATED:$DateNow
DESCRIPTION:$Description
URL:$URL
LAST-MODIFIED:$DateNow
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:$Summary
TRANSP:OPAQUE
END:VEVENT
";
  }

  $ICSFileContent .= $Event;
  $ICSFileContent .= "END:VCALENDAR";
  $array[] = array('filecontent' => $ICSFileContent);

  mysqli_free_result($result);
  echo json_encode($array, JSON_PRETTY_PRINT);
}

if (isset($_POST['message'])) {
  $ReadMessageID = $_POST['message'];
  $DateTimeNow = date('Y-m-d H:i:s');

  $sql = "SELECT ReadDate 
          FROM messages 
          WHERE messages.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ReadMessageID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $read = $row['ReadDate'];

    if (empty($read)) {
      $sql = "UPDATE messages 
              SET ReadDate = ?
              WHERE messages.ID = ?;";

      $stmt = mysqli_prepare($conn, $sql);
      $stmt->bind_param("ss", $DateTimeNow, $ReadMessageID);
      $stmt->execute();
    }
  }
}

if (isset($_POST['markmessagesreadforuserid'])) {
  $UserID = ($_SESSION["id"]);
  $DateTimeNow = date('Y-m-d H:i:s');
  $sql = "";
  $sql = "UPDATE messages SET messages.ReadDate = ? WHERE ToUserID = ? AND ReadDate IS NULL;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $DateTimeNow, $UserID);
  $stmt->execute();
}

if (isset($_GET['popModalEditUsers'])) {
  $UserID = $_GET['popModalEditUsers'];

  $sql = "SELECT Firstname, Lastname, Email, Username, RelatedUserTypeID, CompanyID, JobTitel, Phone, Active, RelatedManager, StartDate
          FROM Users
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  $users_array = mysqli_fetch_all($result, MYSQLI_ASSOC);

  foreach ($users_array as &$user) {
    $user['StartDate'] = convertToDanishDateFormat($user['StartDate']);
  }

  mysqli_free_result($result);
  echo json_encode($users_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['popModalEditPasswords'])) {
  $passwordid = $_GET['popModalEditPasswords'];

  $sql = "SELECT ID, RelatedCompanyID, ServerDestination, Domain, Username, Password, DestinationTypeID, Description, RelatedGroupID
          FROM passwordmanager_passwords
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $passwordid);
  $stmt->execute();
  $result = $stmt->get_result();

  $users_array = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);
  echo json_encode($users_array, JSON_PRETTY_PRINT);
}


if (isset($_GET['popModalEditSettings'])) {
  $settingid = $_GET['popModalEditSettings'];

  $sql = "SELECT ID, SettingsTypeID, SettingName, SettingDescription, SettingValue, Active
          FROM settings
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $settingid);
  $stmt->execute();
  $result = $stmt->get_result();

  $users_array = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);
  echo json_encode($users_array, JSON_PRETTY_PRINT);
}


if (isset($_GET['popModaleditMailTemplates'])) {
  $ID = $_GET['popModaleditMailTemplates'];

  $sql = "SELECT ID, Subject, Content, Updated, UpdatedBy
          FROM mail_templates
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ID);
  $stmt->execute();
  $result = $stmt->get_result();

  $templates_array = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);
  echo json_encode($templates_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['popModaleditLanguages'])) {
  $LanguageID = $_GET['popModaleditLanguages'];

  $sql = "SELECT ID, MainLanguage, da_DK, de_DE, es_ES, fr_FR, fi_FI, it_IT, tr_TR, zh_CN, ru_RU, ja_JP, pt_PT
          FROM languages
          WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $LanguageID);
  $stmt->execute();
  $result = $stmt->get_result();

  $languages_array = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);
  echo json_encode($languages_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['deleteLanguageEntry'])) {
  $LanguageID = $_POST['LanguageID'];

  $sql = "DELETE FROM languages
          WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $LanguageID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['editModulesModal'])) {
  $moduleid = $_GET['editModulesModal'];

  $sql = "SELECT ID, ModuleName, LicenseKey, ModuleActive, Description, HelpText 
          FROM modules
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $moduleid);
  $stmt->execute();
  $result = $stmt->get_result();

  $modules_array = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);
  echo json_encode($modules_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['getdocumentinfo'])) {
  $docid = $_GET['getdocumentinfo'];

  $sql = "SELECT knowledge_documents.ID AS DocID, knowledge_documents.RelatedStatusID AS StatusID, knowledge_documents.Name AS DocName, Version AS DocVersion, RelatedGroupID, RelatedApproverID, RelatedOwnerID, Content, RelatedCategory, Public, RelatedPublicCategoryID
          FROM knowledge_documents 
          WHERE knowledge_documents.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $docid);
  $stmt->execute();
  $result = $stmt->get_result();

  $docs_array = mysqli_fetch_all($result, MYSQLI_ASSOC);

  mysqli_free_result($result);
  echo json_encode($docs_array, JSON_PRETTY_PRINT);
}

if (isset($_POST['changeSettingDetails']) && !empty($_POST['settingid'])  && !empty($_POST['settingname'])) {

  // Submitted form data
  $settingid = $_POST['settingid'];
  $settingname = $_POST['settingname'];
  $settingdescription = $_POST['settingdescription'];
  $settingvalue = $_POST['settingvalue'];
  $settingtype = $_POST['settingtype'];
  $active = $_POST['active'];

  $sql = "UPDATE settings 
          SET SettingName = ?, SettingDescription = ?, SettingValue = ?, SettingsTypeID = ?, Active = ? 
          WHERE ID=$settingid";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sssss", $settingname, $settingdescription, $settingvalue, $settingtype, $active);
  $stmt->execute();

  if($settingid == "13"){
    updateManifestFile($settingvalue);
  }
}

if (isset($_GET['changeMailTemplate'])) {
  $UserID = $_SESSION['id'];
  $mailtemplateid = $_POST['mailtemplateid'];
  $mailtemplatesubject = $_POST['mailtemplatesubject'];
  $mailtemplateContent = $_POST['mailtemplateContent'];
  $mailtemplateUpdatedBy = $_POST['mailtemplateUpdatedBy'];

  $sql = "UPDATE mail_templates 
          SET mail_templates.Subject = ?, mail_templates.Content = ?, mail_templates.Updated = NOW(), mail_templates.UpdatedBy = ?
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssss", $mailtemplatesubject, $mailtemplateContent, $UserID, $mailtemplateid);
  $stmt->execute();
}

if (isset($_POST['updateSLA'])) {

  // Submitted form data
  $MatrixID = $_POST['SLAid'];
  $SLAName = $_POST['SLAName'];
  $SLAPriority = $_POST['SLAPriority'];
  $ReceivedMinutes = $_POST['ReceivedMinutes'];
  $AssignedToTeamMinutes = $_POST['AssignedToTeamMinutes'];
  $AssignedToTechnicianMinutes = $_POST['AssignedToTechnicianMinutes'];
  $InResolutionProcessMinutes = $_POST['InResolutionProcessMinutes'];
  $ResolvedMinutes = $_POST['ResolvedMinutes'];

  $sql = "UPDATE sla_reaction_time_matrix SET ReceivedMinutes = ?, AssignedToTeamMinutes = ?, AssignedToTechnicianMinutes = ?, InResolutionProcessMinutes = ?, ResolvedMinutes = ?
          WHERE sla_reaction_time_matrix.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sssssss", $ReceivedMinutes, $AssignedToTeamMinutes, $AssignedToTechnicianMinutes, $mailtemplateid, $InResolutionProcessMinutes, $ResolvedMinutes, $MatrixID);
  $stmt->execute();
}

if (isset($_GET['updateLanguageEntry'])) {
  if (in_array("100024", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100024");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $LanguageID = $_POST['modalLanguageID'];
  $MainLanguage = trim($_POST['MainLanguage']);
  $daDK = trim($_POST['daDK']);
  $deDE = trim($_POST['deDE']);
  $esES = trim($_POST['esES']);
  $frFR = trim($_POST['frFR']);
  $fiFI = trim($_POST['fiFI']);
  $itIT = trim($_POST['itIT']);
  $trTR = trim($_POST['trTR']);
  $zhCN = trim($_POST['zhCN']);
  $ruRU = trim($_POST['ruRU']);
  $jaJP = trim($_POST['jaJP']);
  $ptPT = trim($_POST['ptPT']);

  $sql = "UPDATE languages SET MainLanguage = ?, da_DK = ?, de_DE = ?, es_ES = ?, fr_FR = ?, fi_FI = ?, it_IT = ?, tr_TR = ?, zh_CN = ?, ru_RU = ?, ja_JP = ?, pt_PT = ?
          WHERE languages.ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param(
    "sssssssssssss",
    $MainLanguage,
    $daDK,
    $deDE,
    $esES,
    $frFR,
    $fiFI,
    $itIT,
    $trTR,
    $zhCN,
    $ruRU,
    $jaJP,
    $ptPT,
    $LanguageID
  );
  $stmt->execute();
}

if (isset($_POST['addLanguageEntry'])) {

  $MainLanguage = trim($_POST['MainLanguage']);

  $CheckMainLanguage = checkForExistingMainLanguage($LanguageEntry);

  if($MainLanguage == $CheckMainLanguage){
    return;
  }
  $daDK = trim($_POST['daDK']);
  $deDE = trim($_POST['deDE']);
  $esES = trim($_POST['esES']);
  $frFR = trim($_POST['frFR']);
  $fiFI = trim($_POST['fiFI']);

  $sql = "INSERT INTO languages(MainLanguage, da_DK, de_DE, es_ES, fr_FR, fi_FI) VALUES (?,?,?,?,?,?)";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssssss", $MainLanguage, $daDK, $deDE, $esES, $frFR, $fiFI);
  $stmt->execute();
}

if (isset($_POST['changeDocumentDetails']) && !empty($_POST['docid'])  && !empty($_POST['docname'])) {

  // Submitted form data
  $docid = $_POST['docid'];
  $status = $_POST['status'];
  $docname = mysqli_real_escape_string($conn, $_POST['docname']);
  $version = $_POST['version'];
  $group = $_POST['group'];
  $approver = $_POST['approver'];
  $owner = $_POST['owner'];
  $content = mysqli_real_escape_string($conn, $_POST['content']);
  $category = $_POST['category'];
  $public = $_POST['public'];
  $publicCategory = $_POST['publicCategory'];
  $UserID = $_SESSION["id"];
  $DateTimeNow = date('Y-m-d H:i:s');

  if (empty($approver)) {
    $approver = "NULL";
  }

  if (empty($group)) {
    $group = "NULL";
  }

  if (empty($publicCategory)) {
    $publicCategory = "0";
  }

  $sql = "UPDATE knowledge_documents SET Name = ?, Version = ?, RelatedGroupID = ?, RelatedApproverID = ?, RelatedOwnerID = ?, Content = ?, RelatedStatusID = ?, RelatedCategory = ?, 
          LastChanged = ?, LastChangedBy = ?, Public = ?, RelatedPublicCategoryID = ?
          WHERE ID='" . $docid . "';";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sssssssssssss", $docname, $version, $group, $approver, $owner, $content, $status, $category, $DateTimeNow, $UserID, $public, $publicCategory, $docid);
  $stmt->execute();
  $result = $stmt->get_result();

  // Create log entry for document, increment doc version
  $LogTypeID = 2;
  $LogActionText = "Document changed by " . $UserID;
  createDocLogEntry($docid, $UserID, $LogTypeID, $LogActionText);
  incrementDocVersion($docid, $version);
}

if (isset($_GET['createDocument'])) {
  // Submitted form data
  $userSessionId = $_SESSION['id'];
  $status = $_POST['status'];
  $docName = mysqli_real_escape_string($conn, $_POST["docname"]);
  $group = $_POST['group'];
  $approver = $_POST['approver'];
  $reviewer = $_POST['reviewer'];
  $owner = $_POST['owner'];
  $category = $_POST['category'];
  $content = trim(htmlspecialchars_decode($_POST['content']));
  $content = stripStringFromScrollbarTags($content);

  $contentFullText = strip_tags(str_replace('<', ' <', $contentFullText));
  $contentFullText = filterStopWords($contentFullText, $userSessionId);

  if (empty($approver)) {
    $approver = null;
  }

  if (empty($reviewer)) {
    $reviewer = null;
  }

  if (empty($group)) {
    $group = null;
  }

  $sql = "INSERT INTO knowledge_documents (RelatedCategory, CreatedBy, Name, Version, RelatedGroupID, RelatedReviewerID, RelatedApproverID, RelatedOwnerID, Content, ContentFullText, RelatedStatusID, LastChanged, LastChangedBy) 
          VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt === false) {
    // Handle prepare statement error
    $functions->errorlog("Error: Failed to prepare statement.", "createDocument");
  }

  $stmt->bind_param(
    "sssssssssss",
    $category,
    $userSessionId,
    $docName,
    $group,
    $reviewer,
    $approver,
    $owner,
    $content,
    $contentFullText,
    $status,
    $userSessionId
  );

  if (!$stmt->execute()) {
    // Handle execute statement error
    $functions->errorlog("Error: Failed to execute statement.", "createDocument");
  }

  $result = $stmt->get_result();
  $lastId = $conn->insert_id;

  if ($lastId === 0) {
    // Handle insert ID error
    $functions->errorlog("Error: Failed to retrieve insert ID.", "createDocument");
  }

  $logTypeId = 2;
  $logActionText = "Document Created";
  createDocLogEntry($lastId, $userSessionId, $logTypeId, $logActionText);

  $array[] = array('documentId' => $lastId);

  echo json_encode($array, JSON_PRETTY_PRINT);
}


if (isset($_POST['deletecategory'])) {

  $categorytobedeleted = $_POST['deletecategory'];

  $sql = "DELETE FROM knowledge_categories WHERE Fixed ! = 1 AND ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $categorytobedeleted);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['updateGroupInformation'])) {
  if (in_array("100033", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100033");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $formData = [];
  foreach ($_POST['data'] as $field) {
    $formData[$field['name']] = $field['value'];
  }

  $SessionUserID = $_SESSION["id"];

  $GroupID = $formData['GroupID'];
  $GroupType = $formData['GroupType'];
  $GroupName = $formData['GroupName'];
  $Description = $formData['Description'];
  $RelatedModuleID = $formData['RelatedModuleID'];
  $Active = $formData['Active'];

  if($GroupType === "Personal"){
    $sql = "UPDATE usergroups 
            SET GroupName = ?, Description = ?, RelatedModuleID = ?, Active = ?
            WHERE ID = ?;";
  } 
  else {
    if (in_array("100000", $UserGroups)) {
      $sql = "UPDATE system_groups 
              SET GroupName = ?, Description = ?, RelatedModuleID = ?, Active = ?
              WHERE ID = ?;";
    } else {
      return;
    }
  }

  $updateStmt = mysqli_prepare($conn, $sql);
  $updateStmt->bind_param("ssiii", $GroupName, $Description, $RelatedModuleID, $Active, $GroupID);
  $updateStmt->execute(); 

  $LogTypeID = "2";
  $LogActionText = "Group updated to: $FieldValue";
  createGroupLogEntry($GroupID, $SessionUserID, $LogTypeID, $LogActionText);
  $Message = $functions->translate("Group updated");
  $Array[] = array("Result" => $Message);
  echo json_encode($Array);
}

if (isset($_GET['updateTeamInformation'])) {
  if (in_array("100033", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100033");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $formData = [];
  foreach ($_POST['data'] as $field) {
    $formData[$field['name']] = $field['value'];
  }

  $SessionUserID = $_SESSION["id"];

  $TeamID = $formData['TeamID'];
  $TeamName = $formData['TeamName'];
  $TeamLeader = $formData['TeamLeader'];
  $TeamStatus = $formData['TeamStatus'];
  $TeamDescription = $formData['TeamDescription'];

  $sql = "UPDATE teams 
          SET Teamname = ?, TeamLeader = ?, Active = ?, Description = ?
          WHERE ID = ?;";
 
  $updateStmt = mysqli_prepare($conn, $sql);
  $updateStmt->bind_param("siisi", $TeamName, $TeamLeader, $TeamStatus, $TeamDescription, $TeamID);
  $updateStmt->execute();

  $Message = $functions->translate("Team updated");
  $Array[] = array("Result" => $Message);
  echo json_encode($Array);
}

if (isset($_GET['updateRoleInformation'])) {
  if (in_array("100033", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100033");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $formData = [];
  foreach ($_POST['data'] as $field) {
    $formData[$field['name']] = $field['value'];
  }

  $SessionUserID = $_SESSION["id"];

  $RoleID = $formData['RoleID'];
  $RoleName = $formData['RoleName'];
  $RoleStatus = $formData['RoleStatus'];
  $RoleDescription = $formData['RoleDescription'];

  $sql = "UPDATE roles 
          SET RoleName = ?, Description = ?, Active = ?
          WHERE ID = ?;";

  $updateStmt = mysqli_prepare($conn, $sql);
  $updateStmt->bind_param("ssii", $RoleName, $RoleDescription, $RoleStatus, $RoleID);
  $updateStmt->execute();

  $Message = $functions->translate("Role updated");
  $Array[] = array("Result" => $Message);
  echo json_encode($Array);
}

if (isset($_GET['deleteGroup'])) {
  if (in_array("100033", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100033");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $GroupID = $_POST['GroupID'];

  $SessionUserID = $_SESSION["id"];

  $sql = "DELETE FROM usergroups
          WHERE ID = ?;";

  $updateStmt = mysqli_prepare($conn, $sql);
  $updateStmt->bind_param("i", $GroupID);
  $updateStmt->execute();

  // Remove existing group associations
  removeUserGroupsRoles($GroupID);

  $LogTypeID = "2";
  $SessionUserFullName = $functions->getUserFullName($SessionUserID);
  $LogActionText = "Group deleted by $SessionUserFullName";
  createGroupLogEntry($GroupID, $SessionUserID, $LogTypeID, $LogActionText);
  $Message = $functions->translate("Group deleted");
  $Array[] = array("Result" => $Message);
  echo json_encode($Array);
}

if (isset($_GET['deleteRole'])) {
  if (in_array("100033", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100033");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $RoleID = $_POST['RoleID'];

  $SessionUserID = $_SESSION["id"];

  $sql = "DELETE FROM roles
          WHERE ID = ?;";

  $deleteStmt = mysqli_prepare($conn, $sql);
  $deleteStmt->bind_param("i", $RoleID);
  $deleteStmt->execute();

  $sql = "DELETE FROM usersroles
          WHERE RoleID = ?;";

  $delete2Stmt = mysqli_prepare($conn, $sql);
  $delete2Stmt->bind_param("i", $RoleID);
  $delete2Stmt->execute();

  $Message = $functions->translate("Role deleted");
  $Array[] = array("Result" => $Message);
  echo json_encode($Array);
}

if (isset($_GET['deleteTeam'])) {
  if (in_array("100033", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100033");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $TeamID = $_POST['TeamID'];

  $SessionUserID = $_SESSION["id"];

  $sql = "DELETE FROM teams
          WHERE ID = ?;";

  $deleteStmt = mysqli_prepare($conn, $sql);
  $deleteStmt->bind_param("i", $TeamID);
  $deleteStmt->execute();

  $sql = "DELETE FROM usersteams
          WHERE TeamID = ?;";

  $delete2Stmt = mysqli_prepare($conn, $sql);
  $delete2Stmt->bind_param("i", $TeamID);
  $delete2Stmt->execute();

  $Message = $functions->translate("Team deleted");
  $Array[] = array("Result" => $Message);
  echo json_encode($Array);
}

if (isset($_GET['createNewUserGroup'])) {
  if (in_array("100033", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100033");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $formData = [];
  foreach ($_POST['data'] as $field) {
    $formData[$field['name']] = $field['value'];
  }

  $SessionUserID = $_SESSION["id"];
  $GroupName = $formData["GroupName"];
  $GroupDescription = $formData["GroupDescription"];
  $RelatedModule = $formData["RelatedModule"];

  $sql = "INSERT INTO usergroups (GroupName, RelatedModuleID, Active, Description) VALUES (?,?,1,?);";

  $updateStmt = mysqli_prepare($conn, $sql);
  $updateStmt->bind_param("sis", $GroupName, $RelatedModule, $GroupDescription);
  $updateStmt->execute();

  $Array[] = array("Result" => $functions->translate("Group created"));
  echo json_encode($Array);
}

if (isset($_GET['createNewTeam'])) {
  if (in_array("100033", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100033");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $formData = [];
  foreach ($_POST['data'] as $field) {
    $formData[$field['name']] = $field['value'];
  }

  $SessionUserID = $_SESSION["id"];
  $TeamName = $formData["TeamName"];
  $TeamLeader = $formData["TeamLeader"];
  $Description = $formData["TeamDescription"];

  $sql = "INSERT INTO teams (Teamname, Colour, Active, TeamLeader, Description) VALUES (?,1,1,?,?);";

  $updateStmt = mysqli_prepare($conn, $sql);
  $updateStmt->bind_param("sis", $TeamName, $TeamLeader, $Description);
  $updateStmt->execute();

  $Array[] = array("Result" => $functions->translate("Group created"));
  echo json_encode($Array);
}

if (isset($_GET['createNewRole'])) {
  if (in_array("100033", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100033");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $formData = [];
  foreach ($_POST['data'] as $field) {
    $formData[$field['name']] = $field['value'];
  }

  $SessionUserID = $_SESSION["id"];
  $RoleName = $formData["RoleName"];
  $RoleDescription = $formData["RoleDescription"];

  $sql = "INSERT INTO roles (RoleName, Description, Active) VALUES (?,?,1);";

  $updateStmt = mysqli_prepare($conn, $sql);
  $updateStmt->bind_param("ss", $RoleName, $RoleDescription);
  $updateStmt->execute();

  $Array[] = array("Result" => $functions->translate("Role created"));
  echo json_encode($Array);
}

if (isset($_POST['createcategory'])) {

  $newcategory = $_POST['createcategory'];

  $sql = "INSERT INTO knowledge_categories (Name) VALUES(?)";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $newcategory);
  $stmt->execute();
}

if (isset($_POST['updatedoccatusergroup'])) {

  // Submitted form data
  $newusergroup = $_POST['usergroup'];
  $category = $_POST['modalcategories'];

  if ($category != 4) {
    $sql = "UPDATE knowledge_categories SET RelatedGroupAccessOnly = ? 
            WHERE knowledge_categories.ID = ?";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $newusergroup, $category);
    $stmt->execute();
  }
}

if (isset($_POST['addDocAsFavorite']) && !empty($_POST['docid']) && !empty($_POST['docname'])) {

  // Submitted form data
  $docid = $_POST['docid'];
  $docname = $_POST['docname'];
  $docname = mysqli_real_escape_string($conn, $docname);
  $UserID = $_SESSION["id"];

  $ModuleID = "3";

  $sql = "INSERT INTO favorites (RelatedModuleID, Name, URL, RelatedUserID) VALUES (?,?,'knowledge_view.php?docid=$docid',?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sss", $ModuleID, $docname, $UserID);
  $stmt->execute();
}

if (isset($_POST['addDocAsTask']) && !empty($_POST['docid']) && !empty($_POST['docname'])) {

  // Submitted form data
  $DocumentID = $_POST['docid'];
  $DocName = $_POST['docname'];
  $RelatedStatusID = $_POST['status'];
  $Version = $_POST['version'];
  $RelatedOwnerID = $_POST['owner'];
  $RelatedCategory = $_POST['category'];
  $UserID = ($_SESSION["id"]);
  $DateTimeNow = date('Y-m-d H:i:s');
  $LastChanged = convertFromDanishTimeFormat($LastChanged);
  $ModuleID = 3;
  $ModuleIconName = getModuleTypeIconName($ModuleID);
  $url = "<a href='knowledge_view.php?docid=" . $DocumentID . "' class='btn btn-sm btn-dark' role='button'>Go To Document</a>";
  $url = mysqli_real_escape_string($conn, $url);

  $Note = $url . " <br><br><b>Document:</b> " . $DocumentID . "<br><b>Name:</b> " . $DocName . "<br><b>Version:</b> " . $Version .
    "<br><b>Category:</b> " . $RelatedCategory . "<br><b>Owner:</b> " . $RelatedOwnerID . "<br><b>Status:</b> " . $RelatedStatusID;

  $Subject = "<i class=\"fa $ModuleIconName\"></i> Document " . $DocumentID;

  $sql = "INSERT INTO taskslist (Note, Subject, RelatedUserID, DateAdded, RelatedTicketID, Deadline, todo) VALUES ('" . $Note . "','" . $Subject . "','" . $UserID . "','"
    . $DateTimeNow . "','" . $DocumentID . "','" . $DateTimeNow . "','yes');";

  mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  echo $status;
  die;
}

if (isset($_GET['TeamID'])) {
  $selectedTeamID = $_GET['TeamID'];

  $sql = "SELECT users.ID AS UserID, CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName
          FROM users 
          LEFT JOIN usersteams ON usersteams.UserID = users.ID
          LEFT JOIN teams ON usersteams.TeamID = teams.ID
          WHERE users.Active=TRUE AND users.RelatedUserTypeID = 1 AND teams.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $selectedTeamID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $teams_array[] = array('UserID' => $row['UserID'], 'FullName' => $row['FullName']);
  }
  mysqli_free_result($result);
  echo json_encode($teams_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['deleteFavoriteEntry'])) {
  $FavoriteID = $_GET['FavoriteID'];

  $sql = "DELETE FROM favorites WHERE favorites.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $FavoriteID);
  $stmt->execute();
}

if (isset($_GET['deleteWatchlistEntry'])) {
  $WLID = $_GET['WLID'];

  $sql = "DELETE FROM watchlist WHERE watchlist.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $WLID);
  $stmt->execute();
}

if (isset($_GET['addtimeregistration'])) {
  $UserID = $_SESSION['id'];
  $relatedtask = $_GET["relatedtask"];
  $description = $_GET["description"];
  $timespend = $_GET["timespend"];
  $billable = $_GET["billable"];
  $dateperformed = convertFromDanishTimeFormat($_GET["dateperformed"]);

  $sql = "INSERT INTO time_registrations (RelatedTaskID, RelatedUserID, Description, TimeRegistered, DateWorked, DateRegistered,Billable)
          VALUES (?,?,?,?,?, NOW(),?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssssss", $relatedtask, $UserID, $description, $timespend, $dateperformed, $billable);
  $stmt->execute();
}

if (isset($_GET['changetimeregistration'])) {
  $UserID = $_SESSION['id'];
  $registrationid = $_POST["registrationid"];
  $relatedtask = $_POST["relatedtask"];
  $description = $_POST["description"];
  $billable = $_POST["billable"];
  $timespend = $_POST["timespend"];
  $dateperformed = convertFromDanishTimeFormat($_POST["dateperformed"]);

  $sql = "UPDATE time_registrations SET RelatedTaskID = ?, Description = ?, Billable = ?, TimeRegistered = ?, DateWorked = ?
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("isiisi", $relatedtask, $description, $billable, $timespend, $dateperformed, $registrationid);
  $stmt->execute();
}

if (isset($_GET['updatetaskfrommodal'])) {

  $TaskID = $_GET["taskid"];
  $Deadline = convertFromDanishTimeFormat($_GET["deadline"]);

  $sql = "UPDATE taskslist SET Deadline = ?
          WHERE taskslist.ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Deadline, $TaskID);
  $stmt->execute();
}

if (isset($_GET['getUsersInTeam'])) {
  $TeamID = $_POST['TeamID'];

  $sql = "SELECT users.ID AS UserID, CONCAT(users.Firstname,' ',users.Lastname,' (',Username,')') AS UserFullName
          FROM usersteams
          LEFT JOIN users ON usersteams.UserID = users.ID 
          LEFT JOIN teams ON usersteams.TeamID = teams.ID
          WHERE usersteams.TeamID = ? AND users.Active IN (0,1,2);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $TeamID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Users_array[] = array('UserID' => $row['UserID'], 'UserFullName' => $row['UserFullName']);
  }
  if (empty($Users_array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Users_array, JSON_PRETTY_PRINT);
  }
}

if (isset($_GET['getusersinCAB'])) {
  $CABID = $_GET['getusersinCAB'];

  $sql = "SELECT users.ID AS UserID, CONCAT(users.Firstname,' ',users.Lastname,' (',Username,')') AS UserFullName
          FROM cab_users
          LEFT JOIN users ON cab_users.UserID = users.ID 
          WHERE cab_users.CABID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $CABID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Users_array[] = array('UserID' => $row['UserID'], 'UserFullName' => $row['UserFullName']);
  }
  if (empty($Users_array)) {
    $Users_array[] = array('UserID' => '0', 'UserFullName' => 'empty');
  }
  mysqli_free_result($result);
  echo json_encode($Users_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['getFavorites'])) {
  $UserID = $_SESSION['id'];

  $sql = "SELECT favorites.ID, favorites.Name, favorites.URL
          FROM favorites
          WHERE RelatedUserID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $ID = $row['ID'];
    $Name = $row['Name'];
    $URL = $row['URL'];
    $Link = "<a href='$URL'>$Name</a>";
    $DeleteLink = "<a href='javascript:(deleteFavoriteListEntry($ID));' title='" . _("Delete") . "'><i class=\"fa-solid fa-trash\"></i></a>";

    $Array[] = array('ID' => $ID, 'Link' => $Link, 'DeleteLink' => $DeleteLink);
  }

  mysqli_free_result($result);
  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['getWatchlist'])) {
  $UserID = $_SESSION['id'];

  $sql = "SELECT watchlist.ID, watchlist.ElementName, watchlist.URL, watchlist.ElementID
          FROM watchlist
          WHERE RelatedUserID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $ID = $row['ID'];
    $ElementID = $row['ElementID'];
    $ElementName = $row['ElementName'];
    $URL = $row['URL'];
    $Link = "<a href='$URL'>" . $ElementName . " " . $ElementID . "</a>";
    $DeleteLink = "<a href='javascript:(deleteWatchlistEntry($ID));' title='" . _("Delete") . "'><i class=\"fa-solid fa-trash\"></i></a>";

    $Array[] = array('ID' => $ID, 'Link' => $Link, 'DeleteLink' => $DeleteLink);
  }

  mysqli_free_result($result);
  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['getShortcuts'])) {
  $UserID = $_SESSION['id'];

  $sql = "SELECT users_quickmenu.ID, users_quickmenu_choices.Name, users_quickmenu_choices.URL
          FROM users_quickmenu_choices
          LEFT JOIN users_quickmenu ON users_quickmenu_choices.ID = users_quickmenu.RelatedChoiceID
          WHERE users_quickmenu.RelatedUserID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  $choices = [];

  while ($row = mysqli_fetch_array($result)) {
    $ID = $row['ID'];
    $Name = $functions->translate($row['Name']);
    $URL = $row['URL'];
    $LINK = "<a href='$URL'>$Name</a>";
    $choices[] = [
      'ID' => $ID,
      'Name' => $Name,
      'URL' => $URL,
      'Link' => $LINK
    ];
  }
  // Sort the choices array by the 'Name' key
    usort($choices, function($a, $b) {
        return strcmp($a['Name'], $b['Name']);
    });

    // Now echo out the sorted choices
    foreach ($choices as $choice) {
      $Array[] = array('ID' => $choice['ID'], 'Link' => $choice['Link']);
    }

  if (empty($Array)) {
    echo json_encode([], JSON_PRETTY_PRINT);
  } else {
    echo json_encode($Array, JSON_PRETTY_PRINT);
  }
  mysqli_free_result($result);
}

if (isset($_GET['getGenericForms'])) {
  $UserID = $_SESSION['id'];
  $RelatedModule = "16";

  $sql = "SELECT ID, FormsName
          FROM forms
          WHERE forms.Active = '1' AND RelatedModuleID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $RelatedModule);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $ID = $row['ID'];
    $Name = $row['FormsName'];
    $URL = "./administration_formview.php?formid=$ID";
    $Link = "<a href='$URL'>$Name</a>";

    $Array[] = array('ID' => $ID, 'Link' => $Link);
  }

  if (empty($Array)) {
    echo json_encode([], JSON_PRETTY_PRINT);
  } else {
    echo json_encode($Array, JSON_PRETTY_PRINT);
  }
  mysqli_free_result($result);
}

if (isset($_GET['getTextTemplateCodes'])) {
  $SessionUserID = $_SESSION['id'];

  $sql = "SELECT Code
          FROM text_templates
          WHERE UserID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SessionUserID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Code = $row['Code'];
    $Array[] = array('Code' => $Code);
  }

  mysqli_free_result($result);
  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['getTextTemplateContentFromCode'])) {
  $SessionUserID = $_SESSION['id'];
  $Code = $_GET['Code'];

  $sql = "SELECT Content
          FROM text_templates
          WHERE UserID = ? AND Code = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $SessionUserID, $Code);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Content = $row['Content'];
    $Array[] = array('Content' => $Content);
  }

  mysqli_free_result($result);
  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['getTextTemplateContent'])) {
  $templateid = $_GET['templateid'];
  if (empty($templateid)) {
    echo json_encode([]);
    exit;
  }
  $sql = "SELECT ID, Name, Code, Content
          FROM text_templates
          WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $templateid);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $ID = $row['ID'];
    $Name = $row['Name'];
    $Code = $row['Code'];
    $Content = $row['Content'];

    $Array[] = array('ID' => $ID, 'Name' => $Name, 'Code' => $Code, 'Content' => $Content);
  }

  mysqli_free_result($result);
  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['updateTextTemplate'])) {
  $TemplateID = $_GET['TemplateID'];
  $TemplateName = $_GET['TemplateName'];
  $TemplateCode = $_GET['TemplateCode'];
  $TemplateText = $_GET['TemplateText'];
  if (empty($TemplateID)) {
    exit;
  }
  $sql = "UPDATE text_templates SET Name = ?, Code = ?, Content = ?
          WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssss", $TemplateName, $TemplateCode, $TemplateText, $TemplateID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['deleteTextTemplate'])) {
  $templateid = $_GET['templateid'];

  $sql = "DELETE FROM text_templates WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $templateid);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['createTextTemplate'])) {
  $SessionUserID = $_SESSION['id'];
  $TemplateName = $_GET['TemplateName'];
  $TemplateCode = $_GET['TemplateCode'];
  $TemplateText = $_GET['TemplateText'];

  $sql = "INSERT INTO text_templates(Name,Code,Content,UserID) VALUES (?,?,?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssss", $TemplateName, $TemplateCode, $TemplateText, $SessionUserID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['getusersinproject'])) {
  $ProjectID = $_GET['getusersinproject'];

  $sql = "SELECT project_users.UserID, CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName
          FROM projects 
          LEFT JOIN project_users ON projects.ID = project_users.ProjectID
          LEFT JOIN users ON project_users.UserID = users.ID
          WHERE ProjectID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Users_array[] = array('UserID' => $row['UserID'], 'FullName' => $row['FullName']);
  }
  mysqli_free_result($result);
  echo json_encode($Users_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['getusersinprojecttask'])) {
  $ProjectTaskID = $_GET['getusersinprojecttask'];

  $sql = "SELECT project_tasks_users.UserID AS UserID, CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName
          FROM project_tasks
          LEFT JOIN project_tasks_users ON project_tasks.ID = project_tasks_users.ProjectTaskID
          LEFT JOIN users ON project_tasks_users.UserID = users.ID
          WHERE project_tasks.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectTaskID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {

    $Users_array[] = array('UserID' => $row['UserID'], 'FullName' => $row['FullName']);
  }
  mysqli_free_result($result);
  echo json_encode($Users_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['removeuserfromteam'])) {
  $UserID = $_GET['userid'];
  $TeamID = $_GET['removeuserfromteam'];

  $sql = "DELETE FROM usersteams WHERE UserID = ? AND TeamID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $UserID, $TeamID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['removeuserfromcompany'])) {
  $UserID = $_GET['userid'];
  $CompanyID = $_GET['removeuserfromcompany'];

  $sql = "UPDATE users SET CompanyID = NULL WHERE users.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['removeuserfromproject'])) {
  $ProjectUserID = $_GET['userid'];
  $ProjectID = $_GET['removeuserfromproject'];
  $UserID = $_SESSION['id'];

  $sql = "DELETE FROM project_users WHERE UserID = ? AND ProjectID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ProjectUserID, $ProjectID);
  $stmt->execute();
  $result = $stmt->get_result();

  $LogTypeID = 2;
  $LogActionText = "User: " . $ProjectUserID . " removed from project";
  createProjectLogEntry($ProjectID, "", $UserID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateProjectTaskProgress'])) {
  $ProjectTaskProgress = $_GET['updateProjectTaskProgress'];
  $ProjectTaskID = $_GET['projecttaskid'];
  $ProjectID = getProjectIDFromTaskID($ProjectTaskID);
  $UserID = $_SESSION['id'];

  $sql = "UPDATE project_tasks SET Progress = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ProjectTaskProgress, $ProjectTaskID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Project Progress changed to: " . $ProjectTaskProgress;
  createProjectTaskLogEntry($ProjectID, $ProjectTaskID, $UserID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateProjectTask'])) {
  $UserID = $_SESSION["id"];
  if ($_SESSION["id"] == "") {
    return;
  }
  $ModuleID = "13";
  $ProjectID = $_POST['ProjectID'];
  $ProjectTaskID = $_POST['ProjectTaskID'];
  if(!$ProjectID){
    $ProjectID = getRelatedProjectFromProjectTask($ProjectTaskID);
  }

  $TaskResponsible = $_POST['TaskResponsible'];
  $ProjectTaskStatus = $_POST['ProjectTaskStatus'];
  $ProjectTaskParent = $_POST['ProjectTaskParent'];
  $ProjectTaskStart = $_POST['ProjectTaskStart'];
  $ProjectTaskDeadline = $_POST['ProjectTaskDeadline'];

  // Convert startdate and enddate strings to the appropriate format
  $TaskStart = convertFromDanishTimeFormat($ProjectTaskStart);
  $TaskDeadline = convertFromDanishTimeFormat($ProjectTaskDeadline);

  // Convert the date strings to Unix timestamps for comparison
  $startTimestamp = strtotime($TaskStart);
  $endTimestamp = strtotime($TaskDeadline);

  // Check if enddate is before or same as startdate
  if ($endTimestamp <= $startTimestamp) {
    $message = $functions->translate("End date cannot be before or the same as the start date");
    $resultarray[] = array("Result" => $message);
    echo json_encode($resultarray);
    return;
  }

  $ProjectTaskEstimatedBudget = $_POST['ProjectTaskEstimatedBudget'];
  $ProjectTaskBudgetSpend = $_POST['ProjectTaskBudgetSpend'];
  $ProjectTaskEstimatedHours = $_POST['ProjectTaskEstimatedHours'];
  $ProjectTaskHoursSpend = $_POST['ProjectTaskHoursSpend'];
  $ProjectRelatedCategory = $_POST['ProjectRelatedCategory'];
  $ProjectTaskProgress = $_POST['ProjectTaskProgress'];
  $ProjectTaskName = $_POST['ProjectTaskName'];
  $ProjectPrivate = $_POST['ProjectPrivate'];
  $ProjectTaskDescription = $_POST['ProjectTaskDescription'];
  $ProjectTaskDescription = sanitizeTextAndBase64($ProjectTaskDescription, $ProjectTaskID, $ModuleID);

  if ($ProjectTaskStatus == "7") {
    $ProjectTaskProgress = 100;
  }

  updateProjectTask($ProjectID, $ProjectTaskID, $TaskResponsible, $ProjectTaskStatus, $ProjectTaskParent, $ProjectTaskStart, $ProjectTaskDeadline, $ProjectTaskEstimatedBudget, $ProjectTaskBudgetSpend, $ProjectTaskEstimatedHours, $ProjectTaskHoursSpend, $ProjectRelatedCategory, $ProjectTaskProgress, $ProjectTaskName, $ProjectTaskDescription, $ProjectPrivate);
  updateKanbanTaskFromElement($ProjectTaskID, '13', $ProjectTaskStatus);

  $LogTypeID = 2;
  $LogActionText = "Projekt task: $ProjectTaskID updated";
  createProjectTaskLogEntry($ProjectID, $ProjectTaskID, $UserID, $LogTypeID, $LogActionText);
  $resultarray[] = array("Result" => "success");
  echo json_encode($resultarray);
}

if (isset($_GET['sendPinToUser'])) {
  $UsersID = $_GET['sendPinToUser'];

  $sql = "SELECT Pin, Email, CONCAT(firstname,' ',lastname) AS FullName FROM users WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UsersID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Pin = $row['Pin'];
    $Email = $row['Email'];
    $FullName = $row['FullName'];
  }

  $Subject = "Your Pin";
  $Content = "Your Pin is: $Pin<br><br>You can change you pin in user settings, we invite you to change it as it now has been emailed to your account and now has been exposed.";
  sendMailToSinglePerson($Email, $ToName, $Subject, $Content);
}

if (isset($_GET['createProjectTask'])) {
  $UserID = $_SESSION['id'];
  $ProjectID = $_POST['ProjectID'];
  $TaskResponsible = $_POST['TaskResponsible'];
  $ProjectTaskStatus = $_POST['ProjectTaskStatus'];
  $ProjectTaskParent = $_POST['ProjectTaskParent'];
  $ProjectTaskStart = $_POST['ProjectTaskStart'];
  $ProjectTaskDeadline = $_POST['ProjectTaskDeadline'];
  $ProjectTaskEstimatedBudget = $_POST['ProjectTaskEstimatedBudget'];
  $ProjectTaskEstimatedHours = $_POST['ProjectTaskEstimatedHours'];
  $ProjectTaskBudgetSpend = $_POST['ProjectTaskBudgetSpend'];
  $ProjectRelatedCategory = $_POST['ProjectRelatedCategory'];
  $ProjectTaskProgress = $_POST['ProjectTaskProgress'];
  $ProjectTaskName = $_POST['ProjectTaskName'];
  $ProjectPrivate = $_POST['ProjectPrivate'];
  $ProjectTaskDescription = $_POST['ProjectTaskDescription'];

  if ($ProjectTaskProgress == "") {
    $ProjectTaskProgress = "0";
  }

  if ($ProjectTaskEstimatedBudget == "") {
    $ProjectTaskEstimatedBudget = "0";
  }

  if ($ProjectTaskBudgetSpend == "") {
    $ProjectTaskBudgetSpend = "0";
  }

  if ($ProjectTaskStatus == "1") {
    $ProjectTaskProgress = "0";
  }

  $ProjectTaskID = createNewProjectTask($ProjectTaskEstimatedHours, $TaskResponsible, $ProjectTaskStatus, $ProjectTaskParent, $ProjectTaskStart, $ProjectTaskDeadline, $ProjectTaskEstimatedBudget, $ProjectTaskBudgetSpend, $ProjectTaskName, $ProjectTaskDescription, $ProjectID, $ProjectRelatedCategory, $ProjectTaskProgress, $ProjectPrivate);

  $LogTypeID = 2;
  $LogActionText = "Projekt task: $ProjectTaskID created";
  createProjectLogEntry($ProjectID, $ProjectTaskID, $UserID, $LogTypeID, $LogActionText);

  echo json_encode($ProjectTaskArray, JSON_PRETTY_PRINT);
}

if (isset($_GET['createNewProject'])) {
  $UserID = $_SESSION['id'];
  $ProjectManager = $_GET['ProjectManager'];
  $ProjectResponsible = $_GET['ProjectResponsible'];
  $ProjectStatus = $_GET['ProjectStatus'];
  $ProjectRelCustomer = $_GET['ProjectRelCustomer'];
  $ProjectStart = $_GET['ProjectStart'];
  $ProjectDeadline = $_GET['ProjectDeadline'];
  $ProjectName = $_GET['ProjectName'];
  $ProjectDescription = $_GET['ProjectDescription'];

  $ProjectID = createNewProject($ProjectManager, $ProjectStatus, $ProjectStart, $ProjectDeadline, $ProjectName, $ProjectDescription, $ProjectRelCustomer, $ProjectResponsible);
  $ProjectArray[] = array('ProjectIDCreated' => $ProjectID);

  $LogTypeID = 2;
  $LogActionText = "Projekt: $ProjectID created";
  createProjectLogEntry($ProjectID, $ProjectTaskID, $UserID, $LogTypeID, $LogActionText);

  echo json_encode($ProjectArray, JSON_PRETTY_PRINT);
}

if (isset($_GET['createNewProjectSprint'])) {
  $UserID = $_SESSION['id'];
  $ProjectID = $_GET['ProjectID'];
  $StartDate = $_GET['StartDate'];
  $Deadline = $_GET['Deadline'];
  $Responsible = $_GET['Responsible'];
  $SprintEstimatedBudget = $_GET['SprintEstimatedBudget'];
  $SprintName = $_GET['SprintName'];
  $SprintDescription = $_GET['SprintDescription'];
  $Link = $_GET['Link'];
  $Version = $_GET['Version'];
  $ProjectSprintArray[] = array();
  $Result = "";
  $Message = "";

  $ReturnedSprintName = checkProjectSprintDateIntervals($StartDate, $Deadline, $ProjectID);

  if (empty($ReturnedSprintName)) {
    $ProjectIDSprintID = createNewProjectSprint($SprintName, $StartDate, $Deadline, $Responsible, $SprintEstimatedBudget, $SprintDescription, $ProjectID, $Link, $Version);
    $Result = "success";
    $Message = $ProjectIDSprintID;
  }

  if (!empty($ReturnedSprintName)) {
    $Result = "fail";
    $Message = "The sprint overlaps sprint: $ReturnedSprintName";
  }

  $ProjectSprintArray[] = array('Result' => $Result, 'Message' => $Message);

  echo json_encode($ProjectSprintArray, JSON_PRETTY_PRINT);
}

if (isset($_GET['createNewProjectTaskCategory'])) {
  $UserSessionID = $_SESSION['id'];
  $CategoryName = $_GET['CategoryName'];
  $CategoryDescription = $_GET['CategoryDescription'];

  $sql = "INSERT INTO projects_tasks_categories(ShortName,Description) VALUES (?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $CategoryName, $CategoryDescription);
  $stmt->execute();
}

if (isset($_GET['editProjectSprint'])) {
  $UserID = $_SESSION['id'];
  $SprintID = $_GET['SprintID'];
  $ProjectTaskID = "";

  $sql = "SELECT * FROM projects_sprints WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SprintID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    if (!empty($row['ID'])) {
      $ProjectDetails[] = array(
        'Link' => $row['Link'], 'Version' => $row['Version'], 'ShortName' => $row['ShortName'], 'StartDate' => $functions->convertToDanishDateTimeFormat($row['Start']), 'Deadline' => $functions->convertToDanishDateTimeFormat($row['Deadline']), 'EstimatedBudget' => $row['EstimatedBudget'], 'Responsible' => $row['Responsible'], 'Description' => $row['Description']
      );
    }
  }

  echo json_encode($ProjectDetails, JSON_PRETTY_PRINT);
}

if (isset($_GET['editProjectTaskCategory'])) {
  $UserID = $_SESSION['id'];
  $CategoryID = $_GET['CategoryID'];

  $sql = "SELECT * FROM projects_tasks_categories WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $CategoryID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    if (!empty($row['ID'])) {
      $Details[] = array(
        'ShortName' => $row['ShortName'], 'Description' => $row['Description']
      );
    }
  }

  echo json_encode($Details, JSON_PRETTY_PRINT);
}

if (isset($_GET['deleteProjectSprint'])) {

  $SprintID = $_GET['SprintID'];

  $sql = "DELETE FROM projects_sprints WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SprintID);
  $stmt->execute();
}

if (isset($_GET['deleteProjectTaskCategory'])) {

  $CategoryID = $_GET['CategoryID'];

  $sql = "DELETE FROM projects_tasks_categories WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $CategoryID);
  $stmt->execute();
}

if (isset($_GET['updateProjectSprint'])) {

  $ProjectSprintArray = array();
  $SprintID = $_GET['SprintID'];
  $StartDate = $_GET['StartDate'];
  $Deadline = $_GET['Deadline'];
  $Responsible = $_GET['Responsible'];
  $SprintEstimatedBudget = $_GET['SprintEstimatedBudget'];
  $SprintName = $_GET['SprintName'];
  $SprintDescription = $_GET['SprintDescription'];
  $Link = $_GET['Link'];
  $Version = $_GET['Version'];

  $ProjectTaskID = "";
  $ProjectID = getProjectIDFromSprint($SprintID);

  $StartDate = convertFromDanishTimeFormat($StartDate);
  $Deadline = convertFromDanishTimeFormat($Deadline);

  $ReturnedSprintName = checkProjectSprintDateIntervalsForUpdateSprint($StartDate, $Deadline, $ProjectID, $SprintID);

  if (empty($ReturnedSprintName)) {
    updateProjectSprint($SprintName, $StartDate, $Deadline, $Responsible, $SprintEstimatedBudget, $SprintDescription, $SprintID, $Link, $Version);
    $Result = "success";
    $Message = "$SprintName";
  }

  if (!empty($ReturnedSprintName)) {
    $Result = "fail";
    $Message = "The sprint overlaps sprint: $ReturnedSprintName";
  }

  $ProjectSprintArray[] = array('Result' => $Result, 'Message' => $Message);

  echo json_encode($ProjectSprintArray, JSON_PRETTY_PRINT);
}

if (isset($_GET['updateProjectTaskCategory'])) {

  $CategoryID = $_GET['CategoryID'];
  $CategoryName = $_GET['CategoryName'];
  $CategoryDescription = $_GET['CategoryDescription'];

  $sql = "UPDATE projects_tasks_categories SET ShortName = ?, Description = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sss", $CategoryName, $CategoryDescription, $CategoryID);
  $stmt->execute();
}

if (isset($_GET['updateProject'])) {

  $UserID = $_SESSION['id'];
  $ProjectID = $_GET['ProjectID'];
  $ProjectManager = $_GET['ProjectManager'];
  $ProjectResponsible = $_GET['ProjectResponsible'];
  $ProjectStatus = $_GET['ProjectStatus'];
  $ProjectRelCustomer = $_GET['ProjectRelCustomer'];
  $ProjectStart = $_GET['ProjectStart'];
  $ProjectDeadline = $_GET['ProjectDeadline'];
  $ProjectName = $_GET['ProjectName'];
  $ProjectDescription = $_GET['ProjectDescription'];
  $ProjectTaskID = "";

  updateProject($ProjectName, $ProjectStatus, $ProjectRelCustomer, $ProjectDescription, $ProjectStart, $ProjectDeadline, $ProjectManager, $ProjectResponsible, $ProjectID);
  updateKanbanTaskFromElement($ProjectID, '6', $ProjectStatus);
}

if (isset($_GET['editProject'])) {
  $UserID = $_SESSION['id'];
  $ProjectID = $_GET['ProjectID'];

  $sql = "SELECT * FROM Projects WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    if (!empty($row['ID'])) {
      $ProjectDetails[] = array(
        'Name' => $row['Name'], 'Start' => $functions->convertToDanishDateTimeFormat($row['Start']), 'Status' => $row['Status'], 'Deadline' => $functions->convertToDanishDateTimeFormat($row['Deadline']), 'Description' => $row['Description'], 'EstimatedBudget' => $row['EstimatedBudget'],
        'EstimatedHours' => $row['EstimatedHours'], 'ProjectManager' => $row['ProjectManager'], 'RelatedCompanyID' => $row['RelatedCompanyID'], 'RelatedGroupAccessID' => $row['RelatedGroupAccessID'],
        'RelatedGroupManageID' => $row['RelatedGroupManageID'], 'ProjectResponsible' => $row['ProjectResponsible']
      );
    }
  }

  mysqli_free_result($result);
  echo json_encode($ProjectDetails, JSON_PRETTY_PRINT);
}

if (isset($_GET['editProjectTask'])) {

  $UserID = $_SESSION['id'];
  $ProjectTaskID = $_GET['ProjectTaskID'];

  $sql = "SELECT * FROM project_tasks WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectTaskID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    if (!empty($row['ID'])) {
      $ProjectTaskDetails[] = array(
        'ParentTask' => $row['ParentTask'], 'TaskName' => $row['TaskName'], 'Start' => $functions->convertToDanishDateTimeFormat($row['Start']), 'Status' => $row['Status'], 'Deadline' => $functions->convertToDanishDateTimeFormat($row['Deadline']), 'Description' => $row['Description'],
        'Responsible' => $row['Responsible'], 'EstimatedBudget' => $row['EstimatedBudget'], 'BudgetSpend' => $row['BudgetSpend'], 'EstimatedHours' => $row['EstimatedHours'], 'HoursSpend' => $row['HoursSpend'],
        'Progress' => $row['Progress'], 'CompletedDate' => $row['CompletedDate'], 'RelatedCategory' => $row['RelatedCategory'], 'Private' => $row['Private']
      );
    }
  }

  mysqli_free_result($result);
  echo json_encode($ProjectTaskDetails, JSON_PRETTY_PRINT);
}

if (isset($_GET['updateProjectTaskDescription'])) {
  $ProjectTaskDescription = $_GET['updateProjectTaskDescription'];
  $ProjectTaskID = $_GET['projecttaskid'];
  $ProjectID = $_GET['projectid'];
  $UserID = $_SESSION['id'];

  $sql = "UPDATE project_tasks SET Description = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ProjectTaskDescription, $ProjectTaskID);
  $stmt->execute();
  $result = $stmt->get_result();

  $LogTypeID = 2;
  $LogActionText = "Project Task Description changed to: " . $ProjectTaskDescription;
  createProjectTaskLogEntry($ProjectID, $ProjectTaskID, $UserID, $LogTypeID, $LogActionText);
}

if (isset($_GET['deleteProjectTask'])) {
  $ProjectTaskID = $_GET['ProjectTaskID'];
  $UserID = $_SESSION['id'];
  $ProjectID = getRelatedProjectFromProjectTask($ProjectTaskID);

  $sql = "DELETE FROM project_tasks WHERE ID = ?";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectTaskID);
  $stmt->execute();

  //Get all tasks from related project task and add to TempArray
  $TasksArray = getRelatedTaskToProjectTask("13", $ProjectTaskID);

  //Now lets delete all related tasks and timegistrations, project tasks are linked with cascade delete in DB so this will cleanup automatically
  foreach ($TasksArray as $Task) {
    deleteRelatedTimeRegEntry($Task);
    deleteRelatedTasks($Task);
  }

  $LogTypeID = 2;
  $LogActionText = "Projekt task: $ProjectTaskID deleted";
  createProjectLogEntry($ProjectID, $ProjectTaskID, $UserID, $LogTypeID, $LogActionText);
  echo json_encode(["Result" => "success"]);
}

if (isset($_GET['removeUserFromProjectTask'])) {
  $ProjectUserID = $_GET['UserID'];
  $ProjectTaskID = $_GET['ProjectTaskID'];

  $sql = "DELETE FROM project_tasks_users WHERE UserID = ? AND ProjectTaskID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ProjectUserID, $ProjectTaskID);
  $stmt->execute();
}

if (isset($_GET['addusertoteam'])) {
  $UserID = $_GET['userid'];
  $TeamID = $_GET['addusertoteam'];

  //Lets check if it exists allready
  $sql = "SELECT UserID, TeamID
          FROM usersteams
          WHERE UserID = $UserID AND TeamID = $TeamID;";

  $result = mysqli_query($conn, $sql);
  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {
    // no results
  } else {

    $sql = "INSERT INTO usersteams(UserID, TeamID) VALUES (?,?);";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $UserID, $TeamID);
    $stmt->execute();
  }
}

if (isset($_GET['addUserToCompany'])) {
  if (!in_array("100026", $UserGroups) || !in_array("100001", $UserGroups)) {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_GET['userid'];
  $CompanyID = $_GET['addUserToCompany'];

  //Lets check if it exists allready
  $sql = "UPDATE users SET CompanyID = ? WHERE users.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $CompanyID, $UserID);
  $stmt->execute();
}

// Add groups to user with user group membership check
if (isset($_GET['addGroupsToUser'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $userId = $_GET['userId'];

    // Prepare the SQL statement
    $sql = "INSERT INTO usersgroups (UserID, GroupID) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    foreach ($selectedValues as $groupId) {
      // Bind the parameters
      mysqli_stmt_bind_param($stmt, "ss", $userId, $groupId);
      // Execute the SQL statement
      mysqli_stmt_execute($stmt);
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}

// Remove groups from user with user group membership check
if (isset($_GET['removeGroupsFromUser'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $userId = $_GET['userId'];

    // Prepare the SQL statement
    $sql = "DELETE FROM usersgroups WHERE UserID = ? AND GroupID = ?";
    $stmt = mysqli_prepare($conn, $sql);

    foreach ($selectedValues as $groupId) {
      // Bind the parameters
      mysqli_stmt_bind_param($stmt, "ss", $userId, $groupId);
      // Execute the SQL statement
      mysqli_stmt_execute($stmt);
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}

// Add roles to user with user group membership check
if (isset($_GET['addRolesToUser'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $userId = $_GET['userId'];

    // Prepare the SQL statement
    $sql = "INSERT INTO usersroles (UserID, RoleID) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    foreach ($selectedValues as $roleId) {
      // Bind the parameters
      mysqli_stmt_bind_param($stmt, "ii", $userId, $roleId);
      // Execute the SQL statement
      mysqli_stmt_execute($stmt);
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}

// Remove roles from user with user group membership check
if (isset($_GET['removeRolesFromUser'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $userId = $_GET['userId'];

    // Prepare the SQL statement
    $sql = "DELETE FROM usersroles WHERE UserID = ? AND RoleID = ?";
    $stmt = mysqli_prepare($conn, $sql);

    foreach ($selectedValues as $roleId) {
      // Bind the parameters
      mysqli_stmt_bind_param($stmt, "ii", $userId, $roleId);
      // Execute the SQL statement
      mysqli_stmt_execute($stmt);
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}

// Add teams to user with user group membership check
if (isset($_GET['addTeamsToUser'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $userId = $_GET['userId'];

    // Prepare the SQL statement
    $sql = "INSERT INTO usersteams (UserID, TeamID) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    foreach ($selectedValues as $teamId) {
      // Bind the parameters
      mysqli_stmt_bind_param($stmt, "ii", $userId, $teamId);
      // Execute the SQL statement
      mysqli_stmt_execute($stmt);
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}


// Remove teams from user with user group membership check
if (isset($_GET['removeTeamsFromUser'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $userId = $_GET['userId'];

    // Prepare the SQL statement
    $sql = "DELETE FROM usersteams WHERE UserID = ? AND TeamID = ?";
    $stmt = mysqli_prepare($conn, $sql);

    foreach ($selectedValues as $teamId) {
      // Bind the parameters
      mysqli_stmt_bind_param($stmt, "ss", $userId, $teamId);
      // Execute the SQL statement
      mysqli_stmt_execute($stmt);
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}


// Add users to role with user group membership check
if (isset($_GET['addUsersToRole'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $roleId = $_GET['roleId'];

    // Prepare the SQL statement
    $sql = "INSERT INTO usersroles (RoleID, UserID) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    foreach ($selectedValues as $userId) {
      // Bind the parameters
      mysqli_stmt_bind_param($stmt, "ii", $roleId, $userId);
      // Execute the SQL statement
      mysqli_stmt_execute($stmt);
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}

// Remove users from role with user group membership check
if (isset($_GET['removeUsersFromRole'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $roleId = $_GET['roleId'];

    // Prepare the SQL statement
    $sql = "DELETE FROM usersroles WHERE RoleID = ? AND UserID = ?";
    $stmt = mysqli_prepare($conn, $sql);

    foreach ($selectedValues as $userId) {
      // Bind the parameters
      mysqli_stmt_bind_param($stmt, "ii", $roleId, $userId);
      // Execute the SQL statement
      mysqli_stmt_execute($stmt);
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}

// Add groups to role with user group membership check
if (isset($_GET['addGroupsToRole'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $roleId = $_GET['roleId'];

    // Prepare the SQL statement
    $sql = "INSERT INTO usergroupsroles (GroupID, RoleID) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    foreach ($selectedValues as $groupId) {
      // Bind the parameters
      mysqli_stmt_bind_param($stmt, "ii", $groupId, $roleId);
      // Execute the SQL statement
      mysqli_stmt_execute($stmt);
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}

// Remove groups from role with user group membership check
if (isset($_GET['removeGroupsFromRole'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $roleId = $_GET['roleId'];

    // Prepare the SQL statement
    $sql = "DELETE FROM usergroupsroles WHERE GroupID = ? AND RoleID = ?";
    $stmt = mysqli_prepare($conn, $sql);

    foreach ($selectedValues as $groupId) {
      // Bind the parameters
      mysqli_stmt_bind_param($stmt, "ii", $groupId, $roleId);
      // Execute the SQL statement
      mysqli_stmt_execute($stmt);
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}


if (isset($_GET['getMessageInformations'])) {
  $MessageID = $_GET['messageid'];
  $sql = "SELECT ID, ToUserID, FromUserID, Message, SendDate, ReadDate
          FROM messages
          WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $MessageID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $FromUser = $functions->getUserFullName($row['FromUserID']);
    $SendDate = $functions->convertToDanishDateTimeFormat($row['SendDate']);
    $Array[] = array('MessageID' => $MessageID, 'FromUserID' => $row['FromUserID'], 'FromUserFullName' => $FromUser, 'Message' => $row['Message'], 'SendDate' => $SendDate);
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['sendMessageFromModal'])) {
  $from = $_POST['from'];
  $to = $_POST['to'];
  $message = $_POST['message'];
  $message = mysqli_real_escape_string($conn, $message);

  $sql = "INSERT INTO messages(ToUserID, FromUserID, Message, SendDate) 
          VALUES (?,?,?,NOW())";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sss", $to, $from, $message);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['sendGroupMessageFromModal'])) {
  $Group = $_POST['Group'];
  $Message = $_POST['Message'];
  $From = $_POST['From'];
  $Message = mysqli_real_escape_string($conn, $Message);

  $sql = "SELECT UserID
          FROM usersgroups
          WHERE GroupID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $Group);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $To = $row['UserID'];
    sendInternalMessage($To, $From, $Message);
  }
}

if (isset($_GET['deleteMessage'])) {
  $modalMessageID = $_GET['modalMessageID'];

  $sql = "DELETE FROM messages WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $modalMessageID);
  $stmt->execute();
}

if (isset($_GET['addBSToCompany'])) {
  $BSID = $_GET['BSID'];
  $CompanyID = $_GET['addBSToCompany'];

  $sql = "INSERT INTO businessservices_companies (BusinessServiceID, 	CompanyID) VALUES (?,?)";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $BSID, $CompanyID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['adduserstocab'])) {
  $UserID = $_GET['userid'];
  $CABID = $_GET['adduserstocab'];

  $sql = "INSERT INTO cab_users (CABID, UserID) VALUES (?,?);";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $CABID, $UserID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['removeusersfromcab'])) {
  $UserID = $_GET['userid'];
  $CABID = $_GET['removeusersfromcab'];

  $sql = "DELETE FROM cab_users WHERE CABID = ? AND UserID = ?;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $CABID, $UserID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['addUserToProject'])) {
  $UserID = $_GET['UserID'];
  $ProjectID = $_GET['ProjectID'];

  $sql = "INSERT INTO project_users(ProjectID, UserID) VALUES (?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ProjectID, $UserID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['removeUserFromProject'])) {
  $UserID = $_GET['UserID'];
  $ProjectID = $_GET['ProjectID'];

  $sql = "DELETE FROM project_users WHERE ProjectID = ? AND UserID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ProjectID, $UserID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['addUserToProjectTask'])) {
  $UserID = $_GET['UserID'];
  $ProjectTaskID = $_GET['ProjectTaskID'];

  $sql = "INSERT INTO project_tasks_users(UserID, ProjectTaskID) VALUES (?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $UserID, $ProjectTaskID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['removeUsersFromTeam'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $teamId = $_GET['teamId'];

    // Prepare the SQL statement
    $sql = "DELETE FROM usersteams WHERE UserID = ? AND TeamID = ?;";
    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bind_param("ss", $userId, $teamId);

    // Iterate over the selected values and execute the statement for each
    foreach ($selectedValues as $userId) {
      // Execute the SQL statement
      $stmt->execute();
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}

if (isset($_GET['addUsersToTeam'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $teamId = $_GET['teamId'];

    // Prepare the SQL statement
    $sql = "INSERT INTO usersteams (UserID, TeamID) VALUES (?, ?);";
    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bind_param("ss", $userId, $teamId);

    // Iterate over the selected values and execute the statement for each
    foreach ($selectedValues as $userId) {
      // Execute the SQL statement
      $stmt->execute();
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be a member of $groupName");
    echo json_encode($array);
    return;
  }
}

if (isset($_GET['addUsersToGroup'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups) || in_array("100033", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $groupId = $_GET['groupId'];

    // Prepare the SQL statement
    $sql = "INSERT INTO usersgroups (GroupID, UserID) VALUES (?, ?);";
    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bind_param("ss", $groupId, $userId);

    // Iterate over the selected values and execute the statement for each
    foreach ($selectedValues as $userId) {
      // Execute the SQL statement
      $stmt->execute();
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName1 = getUserGroupName("100026");
    $groupName2 = getUserGroupName("100033");
    $array[] = array("error" => "You need to be a member of $groupName1 or $groupName2");
    echo json_encode($array);
    return;
  }
}

if (isset($_GET['removeUsersFromGroup'])) {
  if (in_array("100001", $UserGroups) || in_array("100026", $UserGroups)|| in_array("100033", $UserGroups)) {
    $selectedValues = $_GET['selectedValues'];
    $groupId = $_GET['groupId'];

    // Prepare the SQL statement
    $sql = "DELETE FROM usersgroups WHERE GroupID = ? AND UserID = ?;";
    $stmt = $conn->prepare($sql);

    // Bind the parameters
    $stmt->bind_param("ss", $groupId, $userId);

    // Iterate over the selected values and execute the statement for each
    foreach ($selectedValues as $userId) {
      // Execute the SQL statement
      $stmt->execute();
    }

    echo json_encode(["Result" => "success"]);
  } else {
    $groupName1 = getUserGroupName("100026");
    $groupName2 = getUserGroupName("100033");
    $array[] = array("error" => "You need to be a member of $groupName1 or $groupName2");
    echo json_encode($array);
    return;
  }
}

if (isset($_GET['getusersingroup'])) {
  $GroupID = $_GET['getusersingroup'];

  $sql = "SELECT users.ID AS UserID, CONCAT(users.Firstname,' ',users.Lastname,' (',Username,')') AS UserFullName
          FROM usersgroups
          LEFT JOIN users ON usersgroups.UserID = users.ID 
          LEFT JOIN usergroups ON usersgroups.GroupID = usergroups.ID
          WHERE usersgroups.GroupID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("i", $GroupID);
  $stmt->execute();
  $result = $stmt->get_result();

  $Users_array = [];
  while ($row = mysqli_fetch_array($result)) {
    $Users_array[] = array('UserID' => $row['UserID'], 'UserFullName' => $row['UserFullName']);
  }

  if (empty($Users_array)) {
    echo json_encode(['empty' => true]);
  } else {
    echo json_encode($Users_array);
  }
}

if (isset($_GET['getGroupsInRole'])) {
  $RoleID = $_POST['RoleID'];

  $sql = "
    SELECT 
        usergroups.ID AS ID, 
        usergroups.GroupName AS GroupName, 
        modules.Name AS ModuleName, 
        usergroups.Active AS Active, 
        'Personal' AS Type
    FROM 
        usergroups
    JOIN 
        usergroupsroles ON usergroups.ID = usergroupsroles.GroupID
    LEFT JOIN 
        modules ON usergroups.RelatedModuleID = modules.ID
    WHERE 
        modules.Active='1' AND usergroupsroles.RoleID = ?

    UNION

    SELECT 
        system_groups.ID AS ID, 
        system_groups.GroupName AS GroupName, 
        modules.Name AS ModuleName, 
        system_groups.Active AS Active, 
        'System' AS Type
    FROM 
        system_groups
    JOIN 
        usergroupsroles ON system_groups.ID = usergroupsroles.GroupID
    LEFT JOIN 
        modules ON system_groups.RelatedModuleID = modules.ID
    WHERE 
        system_groups.ID !='100000' AND modules.Active='1' AND usergroupsroles.RoleID = ?
    ORDER BY 
        Active DESC, GroupName ASC;
";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ii", $RoleID, $RoleID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Type = $row['Type'];
    $GroupName = $row['GroupName'];
    if($Type == "System"){
      $GroupName = $functions->translate($GroupName);
    }

    $Array[] = array("ID" => $row['ID'], 'GroupName' => $GroupName);
  }

  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['getusersgroups'])) {
  $UserID = $_GET['getusersgroups'];

  $sql = "SELECT usersgroups.GroupID AS GroupID, usergroups.GroupName AS GroupName
          FROM usersgroups
          LEFT JOIN (SELECT usergroups.ID AS ID, usergroups.GroupName AS GroupName
          FROM usergroups
          UNION
          SELECT system_groups.ID AS ID, system_groups.GroupName AS GroupName
          FROM system_groups) AS usergroups ON usersgroups.GroupID = usergroups.ID
          WHERE usersgroups.UserID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == 0) {
    $Groups_array[] = "iamjustsoempty";
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Groups_array[] = array('GroupID' => $row['GroupID'], 'GroupName' => $row['GroupName']);
    }
  }
  mysqli_free_result($result);
  echo json_encode($Groups_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['getusersroles'])) {
  $UserID = $_GET['getusersroles'];

  $sql = "SELECT roles.ID AS RoleID, roles.RoleName
          FROM usersroles
          INNER JOIN roles ON roles.ID = usersroles.RoleID
          WHERE usersroles.UserID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == 0) {
    $Roles_array[] = "iamjustsoempty";
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Roles_array[] = array('RoleID' => $row['RoleID'], 'RoleName' => $row['RoleName']);
    }
  }

  mysqli_free_result($result);
  echo json_encode($Roles_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['getusersteams'])) {
  $UserID = $_GET['getusersteams'];

  $sql = "SELECT teams.ID AS TeamID, teams.Teamname
          FROM usersteams
          INNER JOIN teams ON teams.ID = usersteams.TeamID
          WHERE usersteams.UserID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == 0) {
    $Roles_array[] = "iamjustsoempty";
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Roles_array[] = array('TeamID' => $row['TeamID'], 'Teamname' => $row['Teamname']);
    }
  }

  mysqli_free_result($result);
  echo json_encode($Roles_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['getusersincompany'])) {
  $CompanyID = $_GET['getusersincompany'];

  $sql = "SELECT users.ID AS UserID, CONCAT(users.Firstname,' ',users.Lastname,' (',Username,')') AS UserFullName
          FROM users
          WHERE users.CompanyID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $CompanyID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Users_array[] = array('UserID' => $row['UserID'], 'UserFullName' => $row['UserFullName']);
  }
  mysqli_free_result($result);
  echo json_encode($Users_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['removeuserfromgroup'])) {
  $UserID = $_GET['userid'];
  $GroupID = $_GET['removeuserfromgroup'];

  $sql = "DELETE FROM usersgroups WHERE UserID = ? AND GroupID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $UserID, $GroupID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['pushProjectTasksForwardXDays'])) {
  $UserID = $_SESSION['id'];
  $ProjectTaskID = $_GET['ProjectTaskID'];
  $DaysToPush = $_GET['DaysToPush'];
  $ProjectID = getProjectIDFromTaskID($ProjectTaskID);
  $CurrentStartDate = getProjectTaskStartDate($ProjectTaskID);

  if (preg_match("/[a-z]/i", $DaysToPush)) {
    echo "<script>alert('Your days can only contain a number')</script>";
    exit;
  } else {
    $sql = "UPDATE project_tasks SET Start = DATE_ADD(`Start` , INTERVAL $DaysToPush DAY), Deadline = DATE_ADD(`Deadline` , INTERVAL $DaysToPush DAY), updated_by = ? WHERE project_tasks.Start >= ? AND Status NOT IN (6,7) AND RelatedProject = ?;";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("sss", $UserID, $CurrentStartDate, $ProjectID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
}

if (isset($_GET['removeuserfromteam'])) {
  $UserID = $_GET['userid'];
  $TeamID = $_GET['removeuserfromteam'];

  $sql = "DELETE FROM usersteams WHERE UserID = ? AND TeamID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $UserID, $TeamID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['addusertogroup'])) {
  $UserID = $_GET['userid'];
  $GroupID = $_GET['addusertogroup'];

  //Lets check if it exists allready
  $sql = "SELECT UserID, GroupID
          FROM usersgroups
          WHERE UserID = ? AND GroupID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $UserID, $GroupID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {
    // no results
  } else {

    $sql = "INSERT INTO usersgroups(UserID, GroupID) VALUES (?,?);";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $UserID, $GroupID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
}

if (isset($_GET['addusertoteam'])) {
  $UserID = $_GET['userid'];
  $TeamID = $_GET['addusertogroup'];

  //Lets check if it exists allready
  $sql = "SELECT UserID, TeamID
          FROM usersteams
          WHERE UserID = ? AND GroupID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $UserID, $TeamID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {
    // no results
  } else {

    $sql = "INSERT INTO usersteams(UserID, TeamID) VALUES (?,?);";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $UserID, $TeamID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
}

if (isset($_GET['removeWidget'])) {
  $UserID = $_GET['userid'];
  $WidgetID = $_GET['removeWidget'];

  $sql = "DELETE FROM widgets_users WHERE WidgetID = ? AND UserID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $WidgetID, $UserID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['addWidget'])) {
  $WidgetID = $_GET['addWidget'];
  $UserID = $_GET['userid'];

  $sql = "INSERT INTO widgets_users(UserID, WidgetID) VALUES (?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $UserID, $WidgetID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['removeQuickLaunchEntry'])) {
  $UserID = $_GET['userid'];
  $Entryid = $_GET['removeQuickLaunchEntry'];

  $sql = "DELETE FROM users_quickmenu WHERE RelatedChoiceID = ? AND RelatedUserID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Entryid, $UserID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['addQuickLaunchEntry'])) {
  $UserID = $_GET['userid'];
  $Entryid = $_GET['addQuickLaunchEntry'];

  $sql = "INSERT INTO users_quickmenu(RelatedChoiceID, RelatedUserID) VALUES (?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Entryid, $UserID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['getusersinroles'])) {
  $RoleID = $_GET['getusersinroles'];
  $ResultArray[] = array();

  $sql = "SELECT usersroles.UserID AS UserID, CONCAT(Users.FirstName,' ', Users.LastName,' (', Users.Username,')') AS FullName
          FROM roles
          INNER JOIN usersroles ON roles.ID = usersroles.RoleID
          INNER JOIN users ON usersroles.UserID = users.ID
          WHERE usersroles.RoleID = ? AND roles.Active = 1;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $RoleID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $ResultArray[] = array('UserID' => $row['UserID'], 'FullName' => $row['FullName']);
  }

  echo json_encode($ResultArray, JSON_PRETTY_PRINT);
}

if (isset($_GET['getgroupsinroles'])) {
  $RoleID = $_GET['getgroupsinroles'];

  $sql = "SELECT usergroups.ID AS GroupID, usergroups.GroupName AS GroupName
          FROM roles
          INNER JOIN usergroupsroles ON roles.ID = usergroupsroles.RoleID
          INNER JOIN usergroups ON usergroupsroles.GroupID = usergroups.ID
          WHERE roles.ID = ? AND roles.Active = 1
          UNION
          SELECT system_groups.ID AS GroupID, system_groups.GroupName AS GroupName
          FROM roles
          INNER JOIN usergroupsroles ON roles.ID = usergroupsroles.RoleID
          INNER JOIN system_groups ON usergroupsroles.GroupID = system_groups.ID
          WHERE roles.ID = ? AND roles.Active = 1 AND system_groups.ID != '100000';";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $RoleID, $RoleID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Groups_Array[] = array('GroupID' => $row['GroupID'], 'GroupName' => $row['GroupName']);
  }
  mysqli_free_result($result);
  echo json_encode($Groups_Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['popModaleditWorkdays'])) {
  $WorkdayID = $_GET['popModaleditWorkdays'];

  $sql = "SELECT id, dates, day, timestart, timeend, reason, locked 
          FROM workdays
          WHERE workdays.id = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $WorkdayID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array(
      'id' => $row['id'], 'dates' => $row['dates'], 'day' => $row['day'], 'timestart' => $row['timestart'], 'timeend' => $row['timeend'], 'reason' => $row['reason'], 'locked' => $row['locked']
    );
  }

  mysqli_free_result($result);
  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_POST['updateWorkday'])) {

  // Submitted form data
  $id = $_POST['id'];
  $dates = $_POST['dates'];
  $day = $_POST['day'];
  $timestart = $_POST['timestart'];
  $timeend = $_POST['timeend'];
  $reason = $_POST['reason'];

  $sql = "UPDATE workdays SET dates=?,day=?,timestart=?,timeend=?,reason=?
          WHERE workdays.id = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssssss", $dates, $day, $timestart, $timeend, $reason, $id);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['getworkflows'])) {
  $WorkflowID = $_GET['getworkflows'];

  //Lets check if it exists allready
  $sql = "SELECT ID, RelatedWorkFlowID, StepOrder, StepName, Description 
          FROM workflowsteps_template 
          WHERE RelatedWorkFlowID = $WorkflowID;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $WorkflowID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array(
      'ID' => $row['ID'], 'RelatedWorkFlowID' => $row['RelatedWorkFlowID'], 'StepOrder' => $row['StepOrder'], 'StepName' => $row['StepName'], 'Description' => $row['timDescriptioneend']
    );
  }

  mysqli_free_result($result);
  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['getworkflowsTable'])) {
  $WorkflowID = $_GET['getworkflows'];

  $sql = "SELECT workflowsteps_template.ID, RelatedWorkFlowID, StepOrder, StepName, Description, users.ID AS UserID
          FROM workflowsteps_template
          LEFT JOIN users ON workflowsteps_template.RelatedUserID = users.ID
          WHERE RelatedWorkFlowID = $WorkflowID
          ORDER BY StepOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $WorkflowID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array(
      'ID' => $row['ID'], 'RelatedWorkFlowID' => $row['RelatedWorkFlowID'], 'StepOrder' => $row['StepOrder'], 'StepName' => $row['StepName'], 'Description' => $row['timDescriptioneend']
    );
  }

  mysqli_free_result($result);
  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['popModaleditWorkflowstep'])) {
  $StepID = $_GET['popModaleditWorkflowstep'];

  $sql = "SELECT id, steporder, stepname, description, relateduserid
          FROM workflowsteps_template 
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $StepID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array('id' => $row['id'], 'steporder' => $row['steporder'], 'stepname' => $row['stepname'], 'description' => $row['description'], 'relateduserid' => $row['relateduserid']);
  }

  mysqli_free_result($result);
  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['popModaleditStansdardAnswer'])) {
  $AnswerID = $_GET['popModaleditStansdardAnswer'];

  $sql = "SELECT standardanswers.ID, Name, Answer, RelatedModuleID
          FROM standardanswers
          WHERE standardanswers.ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $AnswerID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array('ID' => $row['ID'], 'Name' => $row['Name'], 'Answer' => $row['Answer'], 'RelatedModuleID' => $row['RelatedModuleID']);
  }

  mysqli_free_result($result);
  echo json_encode($Array, JSON_PRETTY_PRINT);
}


if (isset($_GET['editWorkFlowModal'])) {
  $WorkFlowID = $_POST['workflowid'];

  $sql = "SELECT ID, WorkflowName, Description, Responsible, RelatedModuleID, Active
          FROM workflows_template
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $WorkFlowID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array('ID' => $row['ID'], 'WorkFlowName' => $row['WorkflowName'], 'Description' => $row['Description'], 'Responsible' => $row['Responsible'], 'Active' => $row['Active'], 'RelatedModuleID' => $row['RelatedModuleID']);
  }

  echo json_encode($Array);
}

if (isset($_POST['updateWorkFlowStep'])) {
  if (in_array("100023", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100023");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  // Submitted form data
  $stepid = $_POST['stepid'];
  $steporder = $_POST['steporder'];
  $stepname = $_POST['stepname'];
  $stepname = mysqli_real_escape_string($conn, $stepname);
  $description = $_POST['description'];
  $description = mysqli_real_escape_string($conn, $description);
  $responsible = $_POST['responsible'];

  if ($responsible == -1) {
    $responsible = "NULL";
  }

  $sql = "UPDATE workflowsteps_template SET StepOrder=?,StepName=?,Description=?,RelatedUserID=?
          WHERE workflowsteps_template.ID=?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sssss", $steporder, $stepname, $description, $responsible, $stepid);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['updateWorkFlow'])) {
  if (in_array("100023", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100023");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  // Submitted form data
  $workflowid = $_POST['workflowid'];
  $workflowname = $_POST['workflowname'];
  $workflowname = mysqli_real_escape_string($conn, $workflowname);
  $workflowdescription = $_POST['workflowdescription'];
  $workflowdescription = mysqli_real_escape_string($conn, $workflowdescription);
  $workflowresponsible = $_POST['workflowresponsible'];
  $relatedmodule = $_POST['workflowrelatedmodule'];
  $modalEditWorkFlowActive = $_POST['modalEditWorkFlowActive'];

  if ($workflowresponsible == -1) {
    $workflowresponsible = "NULL";
  }

  $sql = "UPDATE workflows_template 
          SET WorkflowName=?, Description=?, Responsible=?, RelatedModuleID=?, Active=?
          WHERE workflows_template.ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssssss", $workflowname, $workflowdescription, $workflowresponsible, $relatedmodule, $modalEditWorkFlowActive, $workflowid);
  $stmt->execute();
}

if (isset($_POST['createWorkFlowStep'])) {

  // Submitted form data
  $workflowid = $_POST['workflowid'];
  $steporder = $_POST['steporder'];
  $stepname = $_POST['stepname'];
  $stepname = mysqli_real_escape_string($conn, $stepname);
  $description = $_POST['description'];
  $description = mysqli_real_escape_string($conn, $description);
  $responsible = $_POST['responsible'];

  if ($responsible == -1) {
    $responsible = "NULL";
  }

  $sql = "INSERT INTO workflowsteps_template(RelatedWorkFlowID, StepOrder, StepName, Description, RelatedUserID) 
          VALUES (?,?,?,?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sssss", $workflowid, $steporder, $stepname, $description, $responsible);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_POST['updateStandardAnswer'])) {

  // Submitted form data
  $id = $_POST['id'];
  $name = $_POST['name'];
  $answer = mysqli_real_escape_string($conn, urldecode($_POST['answer']));
  $moduleid = $_POST['moduleid'];

  $sql = "UPDATE standardanswers 
          SET Name=?,RelatedModuleID=?,Answer=?
          WHERE standardanswers.ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssss", $name, $moduleid, $answer, $id);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['createWorkFlow'])) {
  // Submitted form data
  $workflowname = $_POST['workflowname'];
  $workflowname = mysqli_real_escape_string($conn, $workflowname);
  $workflowdescription = $_POST['workflowdescription'];
  $workflowdescription = mysqli_real_escape_string($conn, $workflowdescription);
  $workflowresponsible = $_POST['workflowresponsible'];
  $relatedmodule = $_POST['workflowrelatedmodule'];

  if ($workflowresponsible == -1) {
    $workflowresponsible = "NULL";
  }

  $sql = "INSERT INTO workflows_template(WorkflowName, Description, Responsible, RelatedModuleID) 
          VALUES (?,?,?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssss", $workflowname, $workflowdescription, $workflowresponsible, $relatedmodule);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['deleteWorkFlowTemplateTask'])) {

  // Submitted form data
  $WFTASKID = $_POST['WFTASKID'];

  $sql = "DELETE FROM workflowsteps_template
          WHERE workflowsteps_template.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $WFTASKID);
  $stmt->execute();
}

if (isset($_GET['deleteWorkFlow'])) {
  if (in_array("100023", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100023");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  // Submitted form data
  $WFID = $_GET['WFID'];

  $sql = "DELETE FROM workflows_template
          WHERE workflows_template.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $WFID);
  $stmt->execute();

  $array[] = array("Result" => "success");
  echo json_encode($array);
}

if (isset($_GET['getOnlineUsers'])) {

  $sql = "SELECT userid, sessioncreated, sessionrenewed, CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName
          FROM sessions
          LEFT JOIN users ON sessions.userid = users.ID 
          WHERE sessionrenewed > (NOW() - INTERVAL 15 MINUTE)
          ORDER BY sessioncreated DESC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows === 0) exit('No rows');
  while ($row = $result->fetch_assoc()) {
    $Array[] = array('userid' => $row['userid'], 'FullName' => $row['FullName']);
  }

  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['prepareImportADUsers'])) {
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

    $search_filter = "(&(objectCategory=person)(samaccountname=*))";
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
          }

          if ($resultfromcheck == false && $Status == 1) {
            // Account exists lets import
            $Action = "Import";
          }

          //$ad_users[strtoupper(trim($entries[$x]['samaccountname'][0]))] = array('email' => $Email, 'first_name' => $Firstname, 'last_name' => $Lastname);
          $Array[] = array('Username' => $Username, 'Email' => $Email, 'Firstname' => $Firstname, 'Lastname' => $Lastname, 'Fullname' => $Fullname, 'Action' => $Action, 'Status' => $Status);
        }
      }
      ldap_unbind($ldap_connection); // Clean up after ourselves.
    }
    $Antal = count($Array);
    $message .= "Retrieved $Antal Active Directory users\n";
  }

  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['updateAdministratorsFromLDAP'])) {

  $Antal = syncAdministrators();

  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['importADUsers'])) {
  $Antal = syncADUsers();
  $ArrayResult[] = array('Antal' => $Antal);

  if (empty($ArrayResult)) {
    echo json_encode([]);
  } else {
    echo json_encode($ArrayResult);
  }
}


if (isset($_GET['importADTeams'])) {

  $Antal = syncADTeams();
  $ArrayResult[] = array('Antal' => $Antal);

  if (empty($ArrayResult)) {
    echo json_encode([]);
  } else {
    echo json_encode($ArrayResult);
  }
}

if (isset($_GET['getProjectTasks'])) {
  $ProjectID = $_GET['projectid'];
  $UserSessionID = $_SESSION['id'];
  $ShortName = "";
  $Array = [];

  $sql = "SELECT project_tasks.ID AS TaskID, TaskName, project_tasks.Description, project_tasks.Start, project_tasks.Deadline, CONCAT(users.firstname,' ', users.lastname) AS FullName, project_tasks.Status, projects_statuscodes.StatusName, 
                project_tasks.ParentTask AS ParentTask,project_tasks.Progress AS TaskProgress, projects_sprints.Start AS SprintStartDate, projects_sprints.Deadline AS SprintEndDate, projects_sprints.ShortName, projects_sprints.ID AS SprintID, 
                project_tasks.Private AS TaskPrivate, projects.ProjectManager,projects_tasks_categories.ShortName AS RelatedTaskCategoryName, projects_tasks_categories.Description AS RelatedTaskCategoryDescription
          FROM project_tasks
          LEFT JOIN projects ON project_tasks.RelatedProject = projects.ID
          LEFT JOIN users ON project_tasks.Responsible = users.ID
          LEFT JOIN projects_statuscodes ON project_tasks.Status = projects_statuscodes.ID
          LEFT JOIN projects_tasks_categories ON project_tasks.RelatedCategory = projects_tasks_categories.ID
          LEFT OUTER JOIN projects_sprints ON projects_sprints.RelatedProjectID = ? AND project_tasks.Start >= projects_sprints.Start AND project_tasks.Deadline <= projects_sprints.Deadline
          WHERE projects.ID = ?
          ORDER BY projects_sprints.Deadline ASC, project_tasks.Deadline ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ii", $ProjectID, $ProjectID);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows === 0) exit('No rows');
  while ($row = $result->fetch_assoc()) {
    if (empty($row['ShortName'])) {
      $ShortName = $functions->translate("No Sprint");
    } else {
      $ShortName = $row['ShortName'];
    }

    $TaskDelayed = "0";
    $Description = $row['Description'];
    $StatusName = $functions->translate($row['StatusName']);
    $Status = $row['Status'];
    $ParentTask = $row['ParentTask'];
    if($ParentTask == "-1"){
      $ParentTask = "";
    }
    $TaskProgress = $row['TaskProgress'];
    $TaskPrivateStatus = $row['TaskPrivate'];
    $StatusName = sprintf(_("$StatusName"));
    $ProjectTaskID = $row['TaskID'];
    $SprintID = $row['SprintID'];
    $SprintEndDate = $row['SprintEndDate'];
    $ProjectManager = $row['ProjectManager'];
    $RelatedTaskCategoryName = $row['RelatedTaskCategoryName'];
    $RelatedTaskCategoryDescription = $row['RelatedTaskCategoryDescription'];
    $ClosedStatus = "0";
    if ($TaskPrivateStatus == "1" && $ProjectManager <> $UserSessionID) {
      $IsTaskParticipant = getProjectTaskParticipantsForPrivate($ProjectTaskID, $UserSessionID);
      if (empty($IsTaskParticipant)) {
        $ClosedStatus = "1";
      }
    }

    if ($Status <> "7") {
      $DeadlineCheck = date('Y-m-d H:m', strtotime($row['Deadline']));
      $Now = date('Y-m-d H:m');

      if ($DeadlineCheck < $Now) {
        $TaskDelayed = "1";
      }
      if ($DeadlineCheck < $Now) {
        $TaskDelayed = "1";
      }
    }

    //$ParentTask = isset($row['ParentTask']) && !empty($row['ParentTask']) ? $row['ParentTask'] : null;

    if (!empty($ParentTask)) {
      $ParentTaskName = getParentTaskName($ParentTask);
    } else {
      $ParentTaskName = "";
    }

    $Array[] = array('Progress' => $TaskProgress, 'ParentTask' => $ParentTask, 'ParentTaskName' => $ParentTaskName, 'ProjectID' => $ProjectID, 'TaskID' => $ProjectTaskID, 'CalStart' => $row['Start'], 'Start' => convertToDanishTimeFormat($row['Start']), 'CalEnd' => $row['Deadline'], 'Deadline' => convertToDanishTimeFormat($row['Deadline']), 'TaskName' => $row['TaskName'], 'Description' => $row['Description'], 'FullName' => $row['FullName'], 'StatusName' => $StatusName, 'TaskProgress' => $row['TaskProgress'], 'SprintName' => $ShortName, 'SprintID' => $row['SprintID'], 'SprintDeadline' => $SprintEndDate, 'RelatedTaskCategoryName' => $RelatedTaskCategoryName, 'RelatedTaskCategoryDescription' => $RelatedTaskCategoryDescription, 'Private' => $TaskPrivateStatus, 'ClosedStatus' => $ClosedStatus, 'TaskDelayed' => $TaskDelayed, 'SprintID' => $SprintID);
  }

  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['getProjectTasksSprint'])) {
  $ProjectID = $_GET['projectid'];
  $SprintID = $_GET['sprintid'];

  $sql = "SELECT project_tasks.ID AS ID, TaskName, project_tasks.Description, project_tasks.Deadline, CONCAT(users.firstname,' ', users.lastname) AS FullName, projects_statuscodes.StatusName, project_tasks.Progress AS TaskProgress, projects_sprints.ShortName
          FROM project_tasks
          LEFT JOIN projects ON project_tasks.RelatedProject = projects.ID
          LEFT JOIN users ON project_tasks.Responsible = users.ID
          LEFT JOIN projects_statuscodes ON project_tasks.Status = projects_statuscodes.ID
          LEFT JOIN projects_sprints ON projects_sprints.ID = project_tasks.RelatedCategory
          WHERE projects.ID = ? AND project_tasks.RelatedCategory = ?
          ORDER BY project_tasks.Deadline ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ProjectID, $SprintID);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows === 0) exit('No rows');
  while ($row = $result->fetch_assoc()) {
    $Array[] = array('ID' => $row['ID'], 'Deadline' => convertToDanishTimeFormat($row['Deadline']), 'TaskName' => $row['TaskName'], 'Description' => $row['Description'], 'FullName' => $row['FullName'], 'StatusName' => _($row['StatusName']), 'TaskProgress' => $row['TaskProgress'], 'SprintName' => $row['ShortName']);
  }
  mysqli_free_result($result);
  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['updateProjectTaskDate'])) {
  $UserID = $_SESSION['id'];
  $taskid = $_GET['taskid'];
  $startdate = $_GET['startdate'];
  $enddate = $_GET['enddate'];
  $ModuleID = "13";

  $sql = "UPDATE project_tasks SET Start = ?, Deadline = ?, updated_by = ? WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssss", $startdate, $enddate, $UserID, $taskid);
  $stmt->execute();
  $result = $stmt->get_result();

  updateKanbanTaskDeadline($enddate, $taskid, $ModuleID);
  echo json_encode([]);
}


if (isset($_GET['getSprintIDFromName'])) {
  $sprintname = $_GET['sprintname'];

  $sql = "SELECT ID
          FROM projects_sprints
          WHERE ShortName = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $sprintname);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array('SprintID' => $row['ID']);
  }

  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET['getProjectTasksBudgetView'])) {
  $ProjectID = $_GET['projectid'];

  $sql = "SELECT project_tasks.ID AS ID, TaskName, project_tasks.Progress AS TaskProgress, project_tasks.EstimatedBudget, project_tasks.BudgetSpend, project_tasks.EstimatedHours
          FROM project_tasks
          LEFT JOIN projects ON project_tasks.RelatedProject = projects.ID
          LEFT JOIN users ON project_tasks.Responsible = users.ID
          LEFT JOIN projects_statuscodes ON project_tasks.Status = projects_statuscodes.ID 
          WHERE projects.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array('ID' => $row['ID'], 'TaskName' => $row['TaskName'], 'TaskProgress' => $row['TaskProgress'], 'EstimatedBudget' => $row['EstimatedBudget'], 'BudgetSpend' => $row['BudgetSpend'], 'EstimatedHours' => $row['EstimatedHours'], 'HoursSpend' => getProjectTaskTotalHoursSpend($row['ID']));
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['getProjectTeamMembers'])) {
  $ProjectID = $_GET['projectid'];

  $sql = "SELECT project_users.ProjectID, project_users.UserID, CONCAT(users.Firstname,' ',users.Lastname) AS FullName, users.ProfilePicture,users.Email
          FROM project_users
          LEFT JOIN users ON project_users.UserID = users.ID
          WHERE project_users.ProjectID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array('ProjectID' => $row['ProjectID'], 'UserID' => $row['UserID'], 'FullName' => $row['FullName'], 'Email' => $row['Email'], 'ProfilePicture' => $row['ProfilePicture']);
  }
  if (empty($Array)) {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array();
    }
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['getProjectTaskTeamMembers'])) {
  $ProjectTaskID = $_GET['projecttaskid'];

  $sql = "SELECT project_tasks_users.ProjectTaskID , project_tasks_users.UserID, CONCAT(users.Firstname,' ',users.Lastname) AS FullName, users.ProfilePicture,users.Email
          FROM project_tasks_users
          LEFT JOIN users ON project_tasks_users.UserID = users.ID
          WHERE project_tasks_users.ProjectTaskID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectTaskID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array('ProjectTaskID' => $row['ProjectTaskID'], 'UserID' => $row['UserID'], 'FullName' => $row['FullName'], 'Email' => $row['Email'], 'ProfilePicture' => $row['ProfilePicture']);
  }
  if (empty($Array)) {
    $Array[] = array();
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['getLanguages'])) {

  $sql = "SELECT ID, MainLanguage, da_DK, de_DE, es_ES, fr_FR, fi_FI 
          FROM languages";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
  while ($row = mysqli_fetch_array($result)) {
    $Array[] = array('ID' => $row['ID'], 'MainLanguage' => $row['MainLanguage'], 'da_DK' => $row['da_DK'], 'de_DE' => $row['de_DE'], 'es_ES' => $row['es_ES'], 'fr_FR' => $row['fr_FR'], 'fi_FI' => $row['fi_FI']);
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['addusertoteam'])) {
  $UserID = $_GET['userid'];
  $TeamID = $_GET['addusertoteam'];

  //Lets check if it exists allready
  $sql = "SELECT UserID, TeamID
          FROM usersteams
          WHERE UserID = ? AND TeamID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $UserID, $TeamID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {
    // no results
  } else {

    $sql = "INSERT INTO usersteams(UserID, TeamID) VALUES (?,?);";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $UserID, $TeamID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
}

if (isset($_GET['togglesidebar'])) {
  $UserID = $_SESSION['id'];
  $SidebarValue = $_GET['togglesidebar'];

  if ($SidebarValue == 1) {
    $SidebarValue = "sidebar-mini";
  } else {
    $SidebarValue = "";
  }

  //Lets check if it exists allready
  $sql = "SELECT SettingValue
          FROM user_settings
          WHERE RelatedUserID = ? AND RelatedSettingID = 12";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {
    $sql = "UPDATE user_settings SET SettingValue = ? WHERE RelatedUserID = ? AND RelatedSettingID = 12";
    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $SidebarValue, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {

    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES (12,?,?)";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $SidebarValue, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
}

if (isset($_GET['getsidebarvalue'])) {
  $UserID = $_SESSION['id'];

  $sql = "SELECT SettingValue FROM user_settings 
          WHERE RelatedUserID = ? AND RelatedSettingID = 12";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == 0) {
    $Array = array("sidebarvalue" => "");
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array('sidebarvalue' => $row['SettingValue']);
    }
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['togglesidebarcolor'])) {
  $UserID = $_SESSION['id'];
  $SidebarColorValue = $_GET['togglesidebarcolor'];

  //Lets check if it exists allready
  $sql = "SELECT SettingValue
          FROM user_settings
          WHERE RelatedUserID = ? AND RelatedSettingID = 18";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {
    $sql = "UPDATE user_settings SET SettingValue = ? WHERE RelatedUserID = ? AND RelatedSettingID = 18";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $SidebarColorValue, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {

    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES (18,?,?)";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $SidebarColorValue, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
}

if (isset($_GET['getbackgroundvalue'])) {
  $UserID = $_SESSION['id'];

  $sql = "SELECT SettingValue FROM user_settings 
          WHERE RelatedUserID = ? AND RelatedSettingID = 22";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == 0) {
    $Array = array("backgroundvalue" => "");
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array('backgroundvalue' => $row['SettingValue']);
    }
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['togglebackground'])) {
  $UserID = $_SESSION['id'];
  $BackgroundImage = $_GET['togglebackground'];

  //Lets check if it exists allready
  $sql = "SELECT SettingValue
          FROM user_settings
          WHERE RelatedUserID = ?AND RelatedSettingID = 22";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {
    $sql = "UPDATE user_settings SET SettingValue = ? WHERE RelatedUserID = ? AND RelatedSettingID = 22";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $BackgroundImage, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {

    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES (22,?,?)";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $BackgroundImage, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
}

if (isset($_GET['getThemeStyle'])) {
  $UserID = $_SESSION['id'];

  $sql = "SELECT SettingValue FROM user_settings 
          WHERE RelatedUserID = ? AND RelatedSettingID = 20";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == "0") {
    $Array = array("themestylevaluevalue" => "light");
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array('themestylevaluevalue' => $row['SettingValue']);
    }
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['getNavbarstyle'])) {
  $UserID = $_SESSION['id'];

  $sql = "SELECT SettingValue FROM user_settings 
          WHERE RelatedUserID = ? AND RelatedSettingID = 12";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == "0") {
    $Array = array("themestylevaluevalue" => "hidden");
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array('themestylevaluevalue' => $row['SettingValue']);
    }
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['getsidebarimagevalue'])) {
  $UserID = $_SESSION['id'];

  $sql = "SELECT SettingValue FROM user_settings 
          WHERE RelatedUserID = ? AND RelatedSettingID = 19";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == 0) {
    $Array = array("sidebarimagevalue" => "");
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array('sidebarimagevalue' => $row['SettingValue']);
    }
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['togglethemestylevalue'])) {
  $UserID = $_SESSION['id'];
  $ToggleThemeStyle = $_GET['togglethemestylevalue'];
  $Array = array("togglethemestylevalue" => $ToggleThemeStyle);

  //Lets check if it exists allready
  $sql = "SELECT SettingValue
          FROM user_settings
          WHERE RelatedUserID = ? AND RelatedSettingID = 20";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {
    $sql = "UPDATE user_settings SET SettingValue = ? WHERE RelatedUserID = ? AND RelatedSettingID = 20";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $ToggleThemeStyle, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {
    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES (20,?,?)";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $ToggleThemeStyle, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
  echo json_encode($Array);
}

if (isset($_GET['togglenavbarstylevalue'])) {
  $UserID = $_SESSION['id'];
  $ToggleNavBarStyle = $_GET['togglenavbarstylevalue'];
  $Array = array("togglenavbarstylevalue" => $ToggleNavBarStyle);

  //Lets check if it exists allready
  $sql = "SELECT SettingValue
          FROM user_settings
          WHERE RelatedUserID = ? AND RelatedSettingID = 12";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {
    $sql = "UPDATE user_settings SET SettingValue = ? WHERE RelatedUserID = ? AND RelatedSettingID = 12";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $ToggleNavBarStyle, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {

    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES (12,?,?)";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $ToggleNavBarStyle, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
  echo json_encode($Array);
}

if (isset($_GET['togglesidebarimagevalue'])) {
  $UserID = $_SESSION['id'];
  $SidebarImageToggleValue = $_GET['togglesidebarimagevalue'];

  if ($SidebarImageToggleValue == true) {
    $SidebarImageToggleValue = "1";
  } else {
    $SidebarImageToggleValue = "0";
  }

  //Lets check if it exists allready
  $sql = "SELECT SettingValue
          FROM user_settings
          WHERE RelatedUserID = $UserID AND RelatedSettingID = 19";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {

    $sql = "UPDATE user_settings SET SettingValue = ? WHERE RelatedUserID = ? AND RelatedSettingID = 19";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $SidebarImageToggleValue, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {

    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES (19,?,?)";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $SidebarImageToggleValue, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
}

if (isset($_GET['togglebackground'])) {
  $UserID = $_SESSION['id'];
  $BackgroundImageValue = $_GET['togglebackground'];

  if ($BackgroundImageValue == "1") {
    $BackgroundImageValue = "1";
  } else {
    $BackgroundImageValue = "0";
  }

  //Lets check if it exists allready
  $sql = "SELECT SettingValue
          FROM user_settings
          WHERE RelatedUserID = ? AND RelatedSettingID = 22";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {

    $sql = "UPDATE user_settings SET SettingValue = '$BackgroundImageValue' WHERE RelatedUserID = $UserID AND RelatedSettingID = 22";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $BackgroundImageValue, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {

    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES (22,?,?)";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $BackgroundImageValue, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
}

if (isset($_GET['getlatestnofication'])) {
  $UserID = $_SESSION['id'];

  $sql = "SELECT MAX(ID) AS ID
          FROM notifications
          WHERE RelatedUserID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('You do not have any notifications yet')</script>";
    return;
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array('ID' => $row['ID']);
    }
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['getlatestmessage'])) {
  $UserID = $_SESSION['id'];

  $sql = "SELECT MAX(ID) AS ID
          FROM messages
          WHERE ToUserID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('You do not have any messages yet')</script>";
    return;
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array('ID' => $row['ID']);
    }
  }
  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_POST['searchfunction'])) {
  $searchtype = $_POST['searchtype'];
  $searchstring = $_POST['searchstring'];

  $sql = "SELECT SettingValue
          FROM user_settings
          WHERE RelatedUserID = ? AND RelatedSettingID = 19";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  $numrows = mysqli_num_rows($result);

  if ($numrows > 0) {

    $sql = "UPDATE user_settings SET SettingValue = ? WHERE RelatedUserID = ? AND RelatedSettingID = 19";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $SidebarImageToggleValue, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {

    $sql = "INSERT INTO user_settings(RelatedSettingID, SettingValue, RelatedUserID) VALUES (19,?,?)";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ss", $SidebarImageToggleValue, $UserID);
    $stmt->execute();
    $result = $stmt->get_result();
  }
}

if (isset($_GET['deleteFormsTable'])) {
  $TableID = $_GET['deleteFormsTable'];
  $TableName = $_GET['tablename'];

  deleteFormsTable($TableID);
  deleteDatabaseTable($TableName);
}

if (isset($_GET['createFormsTable'])) {
  $TableName = $_GET['createFormsTable'];
  $FormID = $_GET['formid'];

  createFormsTable($FormID, $TableName);
  createDatabaseTable($TableName);
}

if (isset($_GET['createFormsTableField'])) {
  $fieldlabel = $_GET['createFormsTableField'];
  $fieldname = $_GET['fieldname'];
  $fieldtype = $_GET['fieldtype'];
  $fieldlength = $_GET['fieldlength'];
  $fieldwidth = $_GET['fieldwidth'];
  $tableid = $_GET['tableid'];

  createFormsTableField($fieldlabel, $fieldname, $fieldtype, $tableid, $fieldlength, $fieldwidth);
  createDatabaseTableField($fieldlabel, $fieldname, $fieldtype, $tableid, $fieldlength, $fieldwidth);
}

if (isset($_GET['deleteFormsTableField'])) {
  $FieldID = $_GET['deleteFormsTableField'];
  $FieldName = $_GET['fieldname'];
  $TableID = $_GET['formtableid'];

  deleteFormsTableField($FieldID);
  deleteDatabaseTableField($TableID, $FieldName);
}

if (isset($_GET['deleteRelCompany'])) {
  $RelID = $_GET['deleteRelCompany'];

  $sql = "DELETE FROM knowledge_companies WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $RelID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['deleteRelUser'])) {
  $RelID = $_GET['deleteRelUser'];

  $sql = "DELETE FROM knowledge_users WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $RelID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['deletenewsitem'])) {
  $NewsID = $_GET['deletenewsitem'];

  $sql = "DELETE FROM News WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $NewsID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['deleteDocument'])) {
  $UserID = $_SESSION['id'];
  $UserLanguageID = $functions->getUserLanguage($UserID);
  $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);
  $DocName = $_GET['deleteDocument'];
  $ModuleID = $_GET['moduleid'];
  $CIID = getCIIDFromDocName($DocName);
  $ITSMID = getITSMIDFromDocName($DocName);

  switch ($ModuleID) {
    case 1:
      $TableName = "files_tickets";
      break;
    case 2:
      $TableName = "files_changes";
      break;
    case 3:
      $TableName = "files_documents";
      break;
    case 4:
      $TableName = "files_cis";
      $CITypeID = getCITypeFromDocName($DocName);
      $FileNameOriginal = getCIOriginalDocName($DocName);
      $LogActionText = "Deleted file $FileNameOriginal";
      createCILogEntry($CIID, $CITypeID, $UserID, $LogActionText);
      break;
    case 5:
      $TableName = "files_problems";
      break;
    case 6:
      $TableName = "files_projects";
      break;
    case 7:
      $TableName = "files_documents";
      break;
    case 13:
      $TableName = "files_projecttasks";
      break;
    case "companies":
      $TableName = "files_companies";
      break;
    case "businessServicefile":
      $TableName = "files_businessservices";
      break;
    case "users":
      $TableName = "files_users";
      break;
    case "itsm":
      $TableName = "files_itsm";
      $ITSMTypeID = getITSMTypeFromDocName($DocName);
      $FileNameOriginal = getITSMOriginalDocName($DocName);
      $LogActionText = "Deleted file $FileNameOriginal";
      createITSMLogEntry($ITSMID, $ITSMTypeID, $UserID, $LogActionText);
      $resultarray[] = array("ITSMTypeID" => $ITSMTypeID, "ITSMID" => $ITSMID, "UserLanguageCode" => $UserLanguageCode);
      break;
  }
  $Filepath = "./uploads/$TableName/$DocName";
  unlink($Filepath);
  $sql = "DELETE FROM $TableName WHERE FileName = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $DocName);
  $stmt->execute();
  
  if($resultarray){
    echo json_encode($resultarray, JSON_PRETTY_PRINT);
  }  
}

if (isset($_GET['deleteCMDBFile'])) {
  $UserID = $_SESSION['id'];
  $FileID = $_POST['FileID'];

  $TableName = "files_cis";
  $CITypeID = getCITypeFromFileID($FileID);
  $FileName = getCIFileNameFromFileID($FileID);
  $FileNameOriginal = getCIOriginalFileID($FileID);
  $CIID = getCIIDFromFileID($FileID);
  $LogActionText = "Deleted file $FileNameOriginal";
  createCILogEntry($CIID, $CITypeID, $UserID, $LogActionText);

  $Filepath = "./uploads/$TableName/$FileName";
  if (!unlink($Filepath)) {
    $functions->errorlog("Could not delete $Filepath", "deleteCMDBFile");
  }

  $sql = "DELETE FROM $TableName WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $FileID);
  $stmt->execute();
  $result = $stmt->get_result();
}


if (isset($_GET['updatesysteminformation'])) {
  $SystemName = $_GET['updatesysteminformation'];
  $DefaultLanguage = $_GET['defaultlanguage'];
  $DefaultTimeZone = $_GET['defaulttimezone'];
  $SystemURL = $_GET['systemurl'];
  $DefaultDesign = $_GET['defaultdesign'];

  $sql = "UPDATE settings SET settingvalue = ? WHERE ID = 13;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SystemName);
  $stmt->execute();

  $sql = "UPDATE settings SET settingvalue = ? WHERE ID = 10;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $DefaultLanguage);
  $stmt->execute();

  $sql = "UPDATE settings SET settingvalue = ? WHERE ID = 11;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $DefaultTimeZone);
  $stmt->execute();

  $sql = "UPDATE settings SET settingvalue = ? WHERE ID = 17;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SystemURL);
  $stmt->execute();

  $sql = "UPDATE settings SET settingvalue = ? WHERE ID = 20;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $DefaultDesign);
  $stmt->execute();
}

if (isset($_GET['updateMailSettings'])) {
  $SMTPPort = $_GET['updateMailSettings'];
  $SMTPSecure = $_GET['SMTPSecure'];
  $SMTPHost = $_GET['SMTPHost'];
  $SMTPUsername = $_GET['SMTPUsername'];
  $SMTPPassword = $_GET['SMTPPassword'];

  $sql = "UPDATE settings SET settingvalue = ? WHERE ID = 26;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SMTPPort);
  $stmt->execute();

  $sql = "UPDATE settings SET settingvalue = ? WHERE ID = 27;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SMTPSecure);
  $stmt->execute();

  $sql = "UPDATE settings SET settingvalue = ? WHERE ID = 28;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SMTPHost);
  $stmt->execute();

  $sql = "UPDATE settings SET settingvalue = ? WHERE ID = 29;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SMTPUsername);
  $stmt->execute();

  $sql = "UPDATE settings SET settingvalue = ? WHERE ID = 30;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SMTPPassword);
  $stmt->execute();
}

if (isset($_GET['updateAndLogUserUsername'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $Username = $_POST['username'];
  $Username = mysqli_real_escape_string($conn, $Username);

  $sql = "UPDATE users SET Username = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Username, $UsersID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Username changed to: " . $Username;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserBirthday'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $Birthday = $functions->convertFromDanishDateFormat($_POST['birthday']);

  $sql = "UPDATE users SET Birthday = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Birthday, $UsersID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Birthday changed to: " . $Birthday;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserPhone'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $phone = $_POST['phone'];
  $phone = mysqli_real_escape_string($conn, $phone);

  $sql = "UPDATE users SET Phone = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $phone, $UsersID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Phone changed to: " . $phone;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserEmail'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $email = $_POST['email'];
  $email = mysqli_real_escape_string($conn, $email);

  $sql = "UPDATE users SET Email = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $email, $UsersID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Email changed to: " . $email;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserFirstname'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $firstname = $_POST['firstname'];
  $firstname = mysqli_real_escape_string($conn, $firstname);

  $sql = "UPDATE users SET Firstname = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $firstname, $UsersID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Firstname changed to: " . $firstname;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserLastname'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $lastname = $_POST['lastname'];
  $lastname = mysqli_real_escape_string($conn, $lastname);

  $sql = "UPDATE users SET Lastname = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $lastname, $UsersID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Lastname changed to: " . $lastname;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserJobTitel'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $JobTitel = $_POST['jobtitel'];
  $JobTitel = mysqli_real_escape_string($conn, $JobTitel);

  $sql = "UPDATE users SET JobTitel = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $JobTitel, $UsersID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "JobTitel changed to: " . $JobTitel;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserLinkedIn'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $LinkedIn = $_POST['linkedin'];
  $LinkedIn = mysqli_real_escape_string($conn, $LinkedIn);


  $sql = "UPDATE users SET LinkedIn = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $LinkedIn, $UsersID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "LinkedIn changed to: " . $LinkedIn;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserZoomPersRoom'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $ZoomPersRoom = $_POST['updateAndLogUserZoomPersRoom'];
  $ZoomPersRoom = mysqli_real_escape_string($conn, $ZoomPersRoom);

  $sql = "UPDATE users SET ZoomPersRoom = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ZoomPersRoom, $UsersID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Zoom Personal Meeting Room changed to: " . $ZoomPersRoom;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserCompany'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $Company = $_POST['company'];
  $Company = mysqli_real_escape_string($conn, $Company);

  $sql = "UPDATE users SET CompanyID = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Company, $UsersID);
  $stmt->execute();

  $CompanyName = getCompanyName($Company);

  $LogTypeID = 2;
  $LogActionText = "Company changed to: " . $CompanyName;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserUserType'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $UserType = $_POST['usertype'];
  $UserType = mysqli_real_escape_string($conn, $UserType);

  $sql = "UPDATE users SET RelatedUserTypeID = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $UserType, $UsersID);
  $stmt->execute();

  $UserTypeName = getUserTypeName($UserType);

  $LogTypeID = 2;
  $LogActionText = "UserType changed to: " . $UserTypeName;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserManager'])) {
  $UsersID = $_POST['usersid'];
  $UserManager = $_POST['UserManager'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $sql = "UPDATE users SET RelatedManager = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ii", $UserManager, $UsersID);
  $stmt->execute();

  $LogActionText = "";

  if($UserManager){
    $ManagerFullName = $functions->getUserFullNameWithUsername($UserManager);
    $LogActionText = "Manager changed to: $ManagerFullName";
  } else{
    $ManagerFullName = "ingen";
    $LogActionText = "Manager removed";
  }
  
  $LogTypeID = 2;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserActive'])) {
  $UsersID = $_POST['usersid'];
  $UserID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($UserID == $UsersID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $active = $_POST['active'];
  $active = mysqli_real_escape_string($conn, $active);

  $sql = "UPDATE users SET Active = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $active, $UsersID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Active status changed to: " . $active;
  createUserLogEntry($UserID, $UsersID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogUserNotes'])) {
  $$UserIDTarget = $_POST['UserID'];
  $UserSessionID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || ($$UserIDTarget == $UserSessionID) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $usernotes = $_POST['usernotes'];

  $sql = "UPDATE users SET Notes = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("si", $usernotes, $$UserIDTarget);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Notes changed to: " . $usernotes;
  createUserLogEntry($$UserIDTarget, $UserSessionID, $LogTypeID, $LogActionText);
}

if (isset($_GET['updateAndLogCompanyNotes'])) {
  $$CompanyID = $_POST['CompanyID'];
  $UserSessionID = $_SESSION['id'];

  if (in_array("100026", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $companynotes = $_POST['companynotes'];

  $sql = "UPDATE companies SET Notes = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("si", $companynotes, $$CompanyID);
  $stmt->execute();

  $LogTypeID = 2;
  $LogActionText = "Notes changed";
  createCompanyLogEntry($CompanyID, $UserSessionID, $LogTypeID, $LogActionText);
}

if (isset($_GET['getCompanyIDFromName'])) {
  $CompanyName = $_GET['Companyname'];

  $sql = "SELECT ID AS CompanyID
          FROM companies
          WHERE Companyname = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $CompanyName);
  $stmt->execute();

  while ($row = mysqli_fetch_array($result)) {
    $fields_array[] = array('CompanyID' => $row['CompanyID']);
  }
  mysqli_free_result($result);
  echo json_encode($fields_array, JSON_PRETTY_PRINT);
}

// Kontroller om brugerens session er indstillet og om den er udløbet
if (isset($_GET['getSessionActiveStatus'])) {
  $session_minutes = $functions->getSettingValue(63);
  $timeout_duration = (($_SESSION['timeout_duration'] - 1) * $session_minutes); // setting i sekunder og trukket 1 minut fra så man bliver logget af inden session reelt udløber
  if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout_duration)) {
    // Hvis sessionen er udløbet, ødelæg den
    session_unset();
    session_destroy();
    echo json_encode(["status" => "timeout"]);
  } else {
    // Calculate the remaining time in seconds
    $remaining_time = $timeout_duration - (time() - $_SESSION['LAST_ACTIVITY']);
    $remaining_minutes = floor($remaining_time / 60);
    echo json_encode(["status" => "active", "time_left" => $remaining_minutes, "last_activity" => $_SESSION['LAST_ACTIVITY'], "session_minutes" => $session_minutes]);
  }
  exit();
}

if (isset($_GET['updateSessionActive'])) {
  $UserID = $_GET['updateSessionActive'];
  $Status = $_GET['status'];

  $sql = "UPDATE Sessions SET active = ? WHERE userid = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Status, $UserID);
  $stmt->execute();
}

if (isset($_GET['updateClientSessionIP'])) {
  $UserID = $_GET['updateClientSessionIP'];
  $IP = $_GET['ip'];

  $sql = "UPDATE Sessions SET ip = ? WHERE userid = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $IP, $UserID);
  $stmt->execute();
}

if (isset($_GET['updateSettingActive'])) {
  $UsersID = $_GET["userid"];
  $Active = "Logout";
  $time = $functions->getSettingValue(63);

  $sql = "SELECT Active 
          FROM Sessions
          WHERE userid = ? AND (sessionrenewed > DATE_SUB(NOW(), INTERVAL $time MINUTE))";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UsersID);
  $stmt->execute();

  while ($row = mysqli_fetch_array($result)) {
    $Active = $row["Active"];
  }

  $array[] = array("Active" => $Active);

  echo json_encode($array);
}

if (isset($_GET['getSessionActive'])) {
  $userid = $_GET['getSessionActive'];

  $sql = "SELECT Active 
          FROM Sessions
          WHERE userid = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $userid);
  $stmt->execute();

  while ($row = mysqli_fetch_array($result)) {
    $fields_array[] = array('Active' => $row['Active']);
  }
  mysqli_free_result($result);
  echo json_encode($fields_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['checkUserSession'])) {
  $UsersID = $_GET["userid"];
  $Active = "Logout";
  $time = $functions->getSettingValue(63);

  $sql = "SELECT Active 
          FROM Sessions
          WHERE userid = ? AND (sessionrenewed > DATE_SUB(NOW(), INTERVAL $time MINUTE))";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UsersID);
  $stmt->execute();

  while ($row = mysqli_fetch_array($result)) {
    $Active = $row["Active"];
  }

  $array[] = array("Active" => $Active);

  echo json_encode($array);
}

if (isset($_GET['lockSession'])) {
  $UserID = $_GET['lockSession'];

  $sql = "UPDATE Sessions SET locked=1 WHERE userid = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
}

if (isset($_GET['changetimeregistration'])) {
  $UserID = $_SESSION['id'];
  $registrationid = $_GET["registrationid"];
  $relatedtask = $_GET["relatedtask"];
  $description = $_GET["description"];
  $billable = $_GET["billable"];
  $timespend = $_GET["timespend"];
  $dateperformed = convertFromDanishTimeFormat($_GET["dateperformed"]);

  $sql = "UPDATE time_registrations SET RelatedTaskID=?, Description=?, Billable=?, TimeRegistered=?, DateWorked=?
          WHERE ID=?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssssss", $relatedtask, $description, $billable, $timespend, $dateperformed, $registrationid);
  $stmt->execute();
}

if (isset($_GET['changeWorkFlowStep'])) {
  $ModalStepID = $_GET["modalstepid"];
  $ModalStepName = $_GET["modalstepname"];
  $ModalDescription = $_GET["modaldescription"];
  $ModalStatus = $_GET["modalstatus"];
  $ModalResponsible = $_GET["modalresponsible"];
  $ModalDeadline = convertFromDanishTimeFormat($_GET["modaldeadline"]);

  $sql = "UPDATE workflowsteps SET StepName='$ModalStepName',Description='$ModalDescription',RelatedStatusID='$ModalStatus',RelatedUserID='$ModalResponsible'
          WHERE workflowsteps.ID = $ModalStepID";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sssss", $ModalStepName, $ModalDescription, $ModalStatus, $ModalResponsible, $ModalStepID);
  $stmt->execute();

  //Get RelatedTaskID
  $RelatedTaskID = getRelatedTaskIDFromWorkFlowStep($ModalStepID);
  //Update Related task
  updateTaskFromWorkflowStep($RelatedTaskID, $ModalResponsible, $ModalDeadline, $ModalStatus);
}

if (isset($_GET['addWorkFlowStep'])) {
  $ModalStepID = $_GET["modalstepid"];
  $ModalStepName = $_GET["modalstepname"];
  $ModalDescription = $_GET["modaldescription"];
  $ModalStatus = $_GET["modalstatus"];
  $ModalResponsible = $_GET["modalresponsible"];
  $ModalDeadline = convertFromDanishTimeFormat($_GET["modaldeadline"]);
  $ElementID = $_GET["elementid"];
  $ElementType = $_GET["elementtype"];

  $ElementSubject = getElementSubject($ElementID, $ElementType);
  $RedirectPage = getElementRedirectPage($ElementType) . $ElementID;
  $CompanyID = getElementCompanyID($ElementID, $ElementType);
  $CustomerName = $functions->getUserFullName(getElementCustomerID($ElementID, $ElementType));
  $CreatedDate = getElementCreatedDate($ElementID, $ElementType);
  $Deadline = getElementDeadlineDate($ElementID, $ElementType);
  $ModuleShortName = getElementShortName($ElementType);
  $TicketSubject = getElementSubject($ElementID, $ElementType);

  $RelatedWorkFlowIDID = getRelatedWorkFlowIDFromWorkFlowStep($ModalStepID);
  $StepOrder = getNextStepOrder($RelatedWorkFlowIDID);

  $sql = "INSERT INTO workflowsteps (RelatedWorkFlowID, StepOrder, StepName, Description, RelatedStatusID, RelatedUserID) VALUES (?,?,?,?,'1',?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sssss", $RelatedWorkFlowIDID, $StepOrder, $ModalStepName, $ModalDescription, $ModalResponsible);
  $stmt->execute();

  $NewlyCreatedStepID = getNewlyCreatedStepID($RelatedWorkFlowIDID, $ModalResponsible);
  addworkflowtotaskslist($ElementID, $ElementType, $ModalResponsible, $ModalStepName, $RedirectPage, $CompanyID, $CustomerName, $CreatedDate, $Deadline, "wft", $StepOrder, $TicketSubject);
  $NewlyCreatedTaskID = getNewlyCreatedTaskID($ModalResponsible);
  updateWorkFlowStepWithTaskID($NewlyCreatedStepID, $NewlyCreatedTaskID);

  //Get RelatedTaskID
  $RelatedTaskID = getRelatedTaskIDFromWorkFlowStep($ModalStepID);
  //Update Related task
  updateTaskFromWorkflowStep($RelatedTaskID, $ModalResponsible, $ModalDeadline, $ModalStatus);
}

if (isset($_GET['deleteWorkFlowStep'])) {
  $ModalStepID = $_GET["modalstepid"];
  $RelatedTaskID = getRelatedTaskIDFromWorkFlowStep($ModalStepID);
  deleteWorkFlowStepID($ModalStepID);
  deleteTask($RelatedTaskID);
}

if (isset($_GET['deletimapemail'])) {
  $UID = $_GET["uid"];
  removeIMAPEmail($UID);
}

if (isset($_GET['getImapFolders'])) {

  $IMAPServer = "";
  $IMAPMailAddress = "";
  $IMAPPassword = "";

  $sql = "SELECT ID, SettingValue
          FROM settings
          WHERE ID IN (38,39,40)";
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
  }

  $UID = $_GET["uid"];
  $mbox = imap_open("$IMAPServer", "$IMAPMailAddress", "$IMAPPassword");

  $list = imap_list($mbox, "{imap.one.com:993/imap/ssl/novalidate-cert}", "*");
  if (is_array($list)) {
    foreach ($list as $val) {
      $folder = imap_utf7_decode($val) . "\n";
    }
  } else {
    echo "imap_list failed: " . imap_last_error() . "\n";
  }

  imap_close($mbox);
}

if (isset($_GET['popModalDemoBooking'])) {
  $DemoBookingID = $_GET['popModalDemoBooking'];
  $sql = "SELECT ID AS BookingID, InstanceName, InstancePath, InstanceURL, CustomerName, CustomerEmail, Description, DateStart, DateEnd, Status 
          FROM bpage.demo_bookinger_instances
          WHERE demo_bookinger_instances.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $DemoBookingID);
  $stmt->execute();

  while ($row = mysqli_fetch_array($result)) {
    $DateStart = $row['DateStart'];
    if (!empty($DateStart)) {
      $DateStart = $functions->convertToDanishDateTimeFormat($DateStart);
    } else {
      $DateStart = date("d-m-Y h:i");
    }

    $DateEnd = $row['DateEnd'];
    if (!empty($DateEnd)) {
      $DateEnd = $functions->convertToDanishDateTimeFormat($DateEnd);
    } else {
      $DateEnd = date('d-m-Y h:i', strtotime("+30 days"));
    }

    $users_array[] = array(
      'BookingID' => $row['BookingID'], 'InstanceName' => $row['InstanceName'], 'CustomerName' => $row['CustomerName'], 'CustomerEmail' => $row['CustomerEmail'], 'Description' => $row['Description'], 'DateStart' => $DateStart, 'DateEnd' => $DateEnd
    );
  }

  mysqli_free_result($result);
  echo json_encode($users_array, JSON_PRETTY_PRINT);
}

if (isset($_GET['submitDeleteDemoBooking'])) {
  $DemoBookingID = $_GET['submitDeleteDemoBooking'];

  $sql = "UPDATE bpage.demo_bookinger_instances SET CustomerName = null, CustomerEmail = null, Description = null, DateStart = null, DateEnd = null, Status = '2'
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $DemoBookingID);
  $stmt->execute();
}

if (isset($_GET['submitCreateDemoBooking'])) {

  $DemoBookingID = $_GET['DemoBookingID'];
  $CustomerName = $_GET['CustomerName'];
  $CustomerEmail = $_GET['CustomerEmail'];
  $Description = $_GET['Description'];
  $DateStart = convertFromDanishTimeFormat($_GET['Start']);
  $DateEnd = convertFromDanishTimeFormat($_GET['End']);

  $sql = "UPDATE bpage.demo_bookinger_instances SET CustomerName = '$CustomerName', CustomerEmail = '$CustomerEmail', Description = '$Description', DateStart = '$DateStart', DateEnd = '$DateEnd', Status = '3'
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $DemoBookingID);
  $stmt->execute();
}

if (isset($_GET['submitUpdateDemoBooking'])) {

  $DemoBookingID = $_GET['DemoBookingID'];
  $CustomerName = $_GET['CustomerName'];
  $CustomerEmail = $_GET['CustomerEmail'];
  $Description = $_GET['Description'];
  $DateStart = convertFromDanishTimeFormat($_GET['Start']);
  $DateEnd = convertFromDanishTimeFormat($_GET['End']);

  $sql = "UPDATE bpage.demo_bookinger_instances SET CustomerName = '$CustomerName', CustomerEmail = '$CustomerEmail', Description = '$Description', DateStart = '$DateStart', DateEnd = '$DateEnd', Status = '1'
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $DemoBookingID);
  $stmt->execute();
}

if (isset($_GET['orderNewSiteInstall'])) {
  $SiteName = strtolower($_GET['SiteName']);
  $SiteName = str_replace(' ', '', $SiteName);
  $SiteName = preg_replace('/[^A-Za-z0-9\-]/', '', $SiteName);

  $ExistingSiteName = doesSiteExist($SiteName);
  $ExistingSiteNameInstall = doesSiteInstallExist($SiteName);

  if (empty($ExistingSiteNameInstall) && empty($ExistingSiteName)) {
    $sql = "INSERT INTO bpage.site_installation (SiteName, Status) VALUES (?,'1')";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("s", $SiteName);
    $stmt->execute();
  } else {
    echo "<script>alert('Site eksisterer allerede!');</script>";
  }
}


if (isset($_GET['orderSiteToBeDeleted'])) {
  $SiteName = $_GET['SiteName'];

  $ExistingSiteName = doesSiteInstallExist($SiteName);
  if (empty($ExistingSiteName)) {
    $sql = "INSERT INTO bpage.site_installation (SiteName, Status) VALUES (?,'2')";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("s", $SiteName);
    $stmt->execute();
  }
}

if (isset($_GET['approveSiteDrop'])) {
  $ID = $_GET['ID'];

  $sql = "UPDATE bpage.site_installation SET Status = 3 WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ID);
  $stmt->execute();
}

if (isset($_GET['denySiteDrop'])) {
  $ID = $_GET['ID'];

  $sql = "DELETE FROM bpage.site_installation WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ID);
  $stmt->execute();
}

if (isset($_GET['archiveProject'])) {
  $ProjectID = $_GET['archiveProject'];

  $sql = "UPDATE Projects SET status = '8' WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectID);
  $stmt->execute();
}

if (isset($_GET['deleteProject'])) {

  $ProjectID = $_GET['deleteProject'];

  $TasksArray = array();

  regenerateUUIDOnProject($ProjectID);

  $ProjectTaskArray = getRelatedProjectTaskFromProject($ProjectID);

  // For alle project tasks in project create temp array and add to TaskArray in order to delete all tasks and related timeregistrations
  foreach ($ProjectTaskArray as $ProjectTask) {
    //Clear TempArray
    $TempArray = array();
    //Get all tasks from related project task and add to TempArray
    $TempArray = getRelatedTaskToProjectTask("13", $ProjectTask);
    //Push each task to resulting TaskArray
    foreach ($TempArray as $Task) {
      array_push($TasksArray, $Task);
    }
  }
  //Now lets delete all related tasks and timegistrations, project tasks are linked with cascade delete in DB so this will cleanup automatically
  foreach ($TasksArray as $Task) {
    deleteRelatedTimeRegEntry($Task);
    deleteRelatedTasks($Task);
  }

  //And now lets delete the project and its cascade related project tasks
  $sql = "DELETE FROM projects WHERE ID = ?;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectID);
  $stmt->execute();

  //And lastly lets delete all tasks related to project
  deleteRelatedTasksToProject("6", $ProjectID);
}

if (isset($_GET['restoreProjectBaseline'])) {

  $ProjectID = $_GET['restoreProjectBaseline'];
  $UUID = $_GET['UUID'];

  //Delete existing project
  $sql = "DELETE FROM projects WHERE ID = ?;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectID);
  $stmt->execute();

  //Restore Project on baseline UUID
  $sql = "INSERT INTO projects SELECT * FROM projects_baselines WHERE UUID = ?;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UUID);
  $stmt->execute();

  //Delete existing project
  $sql = "DELETE FROM project_tasks WHERE RelatedProject = ?;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectID);
  $stmt->execute();

  //Restore Project on baseline UUID
  $sql = "INSERT INTO project_tasks SELECT * FROM project_tasks_baselines WHERE UUID = ?;";
  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UUID);
  $stmt->execute();
}

if (isset($_GET['createProjetBaseline'])) {

  $ProjectID = $_GET['createProjetBaseline'];
  regenerateUUIDOnProject($ProjectID);
  createProjectBaseline($ProjectID);
}

if (isset($_GET['getKanBanTasks'])) {
  $UserSessionID = $_GET['userid'];
  $TaskStatus = $_GET['taskstatus'];
  $TableName = $_GET['tablename'];
  $TaskStatusNext = $TaskStatus + 1;

  $sql = "SELECT taskslist.ID AS TaskID, taskslist.ModuleName, Headline, DateAdded, Responsible, Deadline, CONCAT(users.FirstName,' ', users.LastName) AS UsersName, 
          RelatedElementID, RelatedElementTypeID, GoToLink, Status, wftid, modules.ShortElementName
          FROM taskslist 
          INNER JOIN users ON taskslist.RelatedUserID = users.id
          INNER JOIN modules ON taskslist.RelatedElementTypeID = modules.ID
          WHERE taskslist.RelatedUserID = ? AND taskslist.Status = ?
          ORDER BY Deadline ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ii", $UserSessionID, $TaskStatus);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $ModuleID = $row['RelatedElementTypeID'];
    /* Temp deactivation of non working modules
    if($ModuleID > 5 && $ModuleID < 4999){
      continue;
    }
    */
    $ModuleIcon = getModuleIcon($ModuleID);
    $TaskID = $row['TaskID'];

    $RelatedElementID = $row['RelatedElementID'];
    $DateAdded = convertToDanishTimeFormat($row['DateAdded']);
    $ModuleName = $row['ModuleName'];
    $GoToLink = $row['GoToLink'];
    $Status = $row['Status'];
    $DeadlineForTask = convertToDanishTimeFormat($row['Deadline']);
    $Responsible = $row['Responsible'];
    $wft = $row['wftid'];
    $Headline = $row['Headline'];

    $ResponsibleName = $functions->getUserFullName($Responsible);
    $ResponsibleUsername = getUserName($Responsible);    
    $SubjectColumnName = getSubjectColumnFromModuleID($ModuleID);
    $SubjectColumnName = "Subject";
    $MainTableName = getMainTableNameFromModuleID($ModuleID);
    $MainTableName = $functions->getITSMTableName($ModuleID);
    $ModuleType = $functions->getITSMModuleType($ModuleID);

    $LinkToParent = "";
    if (!empty($wft)) {
      $ElementSubjectHeadline = $Headline;
      $ElementTitle = getSubjectFromModuleElementID($RelatedElementID, $SubjectColumnName, $MainTableName);
      $ElementTitleFWT = getSubjectFromModuleElementID($RelatedElementID, $SubjectColumnName, $MainTableName);
      $LinkToElement = "<a href=\"javascript:editWorkFlowTask($wft);\" class=\"text-sm text-secondary mb-0 text-wrap\" title=\"$ElementTitle\">" . _("$ModuleName") . " $RelatedElementID</a>";
      $TaskButton = "<a href=\"javascript:editWorkFlowTask($wft);\" id=\"$TaskID\" class=\"badge badge-pill bg-gradient-success\" title=\"" . $functions->translate("Edit") . "\"><i class=\"fa fa-pen-to-square\"></i></a>";
      $LinkToParent = "<p class=\"text-sm text-secondary mb-0\"><b>" . $functions->translate("Parent Task") . ": <a href=\"$GoToLink\">$ElementTitleFWT</a></p>";
    } else {
      $TaskButton = "<a href=\"$GoToLink\" id=\"$TaskID\" class=\"badge badge-pill bg-gradient-success\" title=\"" . $functions->translate("Edit") . "\"><i class=\"fa fa-pen-to-square\"></i></a>";
      //$TaskButton = "<a href=\"$GoToLink\" class=\"text-sm text-secondary mb-0 text-wrap\" title=\"$ElementTitle\"><i class=\"fa fa-pen-to-square\"></i></a>";
      if($ModuleID == "6"){
        $ElementSubjectHeadline = getProjectName($RelatedElementID);
        $ElementTitle = $ElementSubjectHeadline;
      } 
      else if($ModuleID == "13"){
        $ElementSubjectHeadline = getProjectTaskName($RelatedElementID);
        $ElementTitle = $ElementSubjectHeadline;
      } else {
        $SubjectColumnName = getSubjectColumnFromModuleID($ModuleID);
        $MainTableName = getMainTableNameFromModuleID($ModuleID);
        $ElementSubjectHeadline = getSubjectFromModuleElementID($RelatedElementID, $SubjectColumnName, $MainTableName);
        $ElementTitle = $ElementSubjectHeadline;
      }

      $ElementTitleFWT = $ElementTitle;
      $LinkToElement = "<a href=\"$GoToLink\" class=\"text-sm text-secondary mb-0 text-wrap\" title=\"$ElementTitle\">" . _("$ModuleName") . " $RelatedElementID</a>";
    }

    if (empty($ElementSubjectHeadline)) {
      $ElementSubjectHeadline = "Element is removed - deleting task";
      updateKanBanTaskStatus($TaskID, "4");
      continue;
    }
    if (strtotime($row['Deadline']) < strtotime('-7 days') && $Status == "3") {
      updateKanBanTaskStatus($TaskID, "4");
      continue;
    }

    $deadline = strtotime($DeadlineForTask);
    $currentTimestamp = time();

    $diffInSeconds = $deadline - $currentTimestamp;
    $hoursRemaining = floor($diffInSeconds / 3600);
    $minutesRemaining = floor(($diffInSeconds % 3600) / 60);

    $hoursDisplay = ($hoursRemaining < 0) ? '-' . abs($hoursRemaining) : $hoursRemaining;
    $minutesDisplay = ($minutesRemaining < 0) ? '-' . abs($minutesRemaining) : $minutesRemaining;

    $timeRemaining = '';
    if ($hoursRemaining !== 0) {
      $timeRemaining .= $hoursDisplay . ' hour' . (($hoursDisplay != 1) ? 's' : '') . ' ';
    }
    if ($minutesRemaining !== 0) {
      $timeRemaining .= $minutesDisplay . ' minute' . (($minutesDisplay != 1) ? 's' : '');
    }

    $ModulesToExclude = [];
    if($ModuleType != "1"){
      $ModulesToExclude[] = "$ModuleID";
    }

    $ModulesToExclude[] = "6";
    $ModulesToExclude[] = "13";
    
    if (in_array($ModuleID, $ModulesToExclude)) {
        if ($ModuleID == "6") {
          $DeadlineForElement = $functions->convertToDanishDateTimeFormat(getProjectDeadlineDate($RelatedElementID));
        } else if ($ModuleID == "13") {
          $DeadlineForElement = $functions->convertToDanishDateTimeFormat(getProjectTaskDeadlineDate($RelatedElementID));
        } else {
          $DeadlineForElement = $DeadlineForTask;
        }
    } else {        
        if($SLASupported == "1"){
          $DeadlineForElement = $functions->convertToDanishDateTimeFormat(getDeadlineForSLAElementID($RelatedElementID, $ModuleID));
        } else {
          $DeadlineForElement = $DeadlineForTask;
        }
    }
    
    $ProfilePicture = getUserProfilePicture($Responsible);
    $Email = getUserEmailFromID($Responsible);
    $TodoTaskBtn = "<a href=\"javascript:void(0);\" id=\"1\" name=\"$TaskID\" class=\"badge badge-pill bg-gradient-secondary float-end\" title=\"" . $functions->translate("Todo") . "\" onclick=\"updateTaskStatus(this.name, this.id,'$TableName')\"><i class=\"fa fa-circle-left\"></i></a>";
    $DoingTaskBtn = "<a href=\"javascript:void(0);\" id=\"2\" name=\"$TaskID\" class=\"badge badge-pill bg-gradient-secondary float-end\" title=\"" . $functions->translate("Doing") . "\" onclick=\"updateTaskStatus(this.name, this.id,'$TableName')\"><i class=\"fa fa-circle-up\"></i></a>";
    $DoneTaskBtn = "<a href=\"javascript:void(0);\" id=\"3\" name=\"$TaskID\" class=\"badge badge-pill bg-gradient-secondary float-end\" title=\"" . $functions->translate("Done") . "\" onclick=\"updateTaskStatus(this.name, this.id,'$TableName')\"><i class=\"fa fa-circle-right\"></i></a>";
    $ClosedTaskBtn = "<a href=\"javascript:void(0);\" id=\"4\" name=\"$TaskID\" class=\"badge badge-pill bg-gradient-secondary float-end\" title=\"" . $functions->translate("Close") . "\" onclick=\"updateTaskStatus(this.name, this.id,'$TableName')\"><i class=\"fa fa-trash\"></i></a>";
    
    if ($TaskStatus == "3") {
      $DeleteTaskBtnDoneState = $ClosedTaskBtn;
    } else {
      $DeleteTaskBtnDoneState = "";
    }

    // Update the code snippet
    $dotIcon = '';

    // Check if the deadline has surpassed the current date
    if (strtotime($DeadlineForElement) < time()) {
      $dotIcon = "<i class=\"fa-solid fa-circle dot-icon dot-icon-red\" title=\"" . _("Deadline has passed") . "\"></i>";
    }
    // Check if the deadline is within 5 hours
    elseif (strtotime($DeadlineForElement) - time() <= 5 * 3600) {
      $dotIcon = "<i class=\"fa-solid fa-circle dot-icon dot-icon-orange\" title=\"" . _("Deadline is within 5 hours!") . "\"></i>";
    }

    $Title = $functions->translate("You can drag tasks to board header to change its state");
    $TaskContent = "<div class=\"card card-body-dropdown text-wrap\" title=\"$Title\" id=\"$TaskID\" draggable=\"true\" ondragstart=\"drag(event)\">
      <a class=\"collapsed\" data-bs-toggle=\"collapse\" href=\"#collapse$TaskID\" aria-expanded=\"false\" aria-controls=\"collapse$TaskID\" id=\"$TaskID\" draggable=\"true\" ondragstart=\"drag(event)\">
        <div class=\"d-flex justify-content-between align-items-center\">
          <p class=\"text-sm text-secondary mb-0 text-wrap\">$dotIcon <i class=\"$ModuleIcon\"></i> $ModuleName: $ElementSubjectHeadline</p>
          <div class=\"text-right\">
            $DeleteTaskBtnDoneState
          </div>
        </div>
      </a>
      <div id=\"collapse$TaskID\" class=\"accordion-collapse collapse\" role=\"tabpanel\" aria-labelledby=\"heading$TaskID\" data-parent=\"#accordionTabToDo\">
        <br>
        <p class=\"text-sm text-secondary mb-0\"><b>" . $functions->translate("Task") . ": $LinkToElement</p>
$LinkToParent
        <p class=\"text-sm text-secondary mb-0\"><b>" . $functions->translate("Responsible") . ":</b> <a href=\"javascript:runModalViewUnit('User',$Responsible);\">$ResponsibleName ($ResponsibleUsername)</a></p>
        <p class=\"text-sm text-secondary mb-0\"><b>" . $functions->translate("Deadline") . ":</b> $DeadlineForElement</p>
        <p class=\"text-sm text-secondary mb-0\"><b>" . $functions->translate("Remaining") . ":</b> $timeRemaining</p>
        <p class=\"text-sm text-secondary mb-0\"><b>" . $functions->translate("Task created") . ":</b> $DateAdded</p>
        <br>
        <div class=\"row\">
          <div class=\"col-md-12 col-sm-12 col-xs-12\">
            $TaskButton
            <a href=\"javascript:void(0);\" class=\"badge badge-pill bg-gradient-info\" title=\"" . $functions->translate("Timeregistration") . "\" onclick=\"javascript:runModalCreateTimeRegistration($TaskID);\"><i class=\"fa fa-clock\"></i></a>
            $DeleteTaskBtn
            $ClosedTaskBtn
            $DoneTaskBtn
            $DoingTaskBtn
            $TodoTaskBtn
            
          </div>          
        </div>
      </div>
    </div>";

    $Array[] = array("TaskID" => $TaskID, "TaskContent" => $TaskContent);
  }
  mysqli_free_result($result);
  if (empty($Array)) {
    echo json_encode([]);
  } else {
    echo json_encode($Array);
  }
}

if (isset($_GET["sendPasswordChangeRequest"])) {
  $Email = $_GET["emailaddress"];
  $UsersID = $functions->getUserIDFromEmail($Email);
  changePasswordSubmit($UsersID);
}

if (isset($_GET['updateKanbanTask'])) {
  $KanbanTaskID = $_GET["taskid"];
  $KanBanTaskStatus = $_GET["status"];
  updateKanBanTaskStatus($KanbanTaskID, $KanBanTaskStatus);
}

if (isset($_GET['getWorkflowInformation'])) {
  $workflowstepid = $_GET['workflowstepid'];

  $sql = "SELECT workflowsteps.ID, workflowsteps.RelatedWorkFlowID, workflowsteps.StepOrder, workflowsteps.StepName, workflowsteps.Description, workflowsteps.RelatedStatusID, workflowsteps.RelatedUserID, workflowsteps.RelatedTaskID, taskslist.Deadline
          FROM workflowsteps
          INNER JOIN taskslist ON workflowsteps.RelatedTaskID = taskslist.ID
          WHERE workflowsteps.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $workflowstepid);
  $stmt->execute();

  while ($row = mysqli_fetch_array($result)) {
    $ID = $row['ID'];
    $RelatedWorkFlowID = $row['RelatedWorkFlowID'];
    $StepOrder = $row['StepOrder'];
    $StepName = $row['StepName'];
    $Description = $row['Description'];
    $RelatedStatusID = $row['RelatedStatusID'];
    $RelatedUserID = $row['RelatedUserID'];
    $RelatedTaskID = $row['RelatedTaskID'];
    $Deadline = $row['Deadline'];
    $Deadline = convertToDanishTimeFormat($Deadline);

    $wftarray[] = array('ID' => $ID, 'RelatedWorkFlowID' => $RelatedWorkFlowID, 'StepOrder' => $StepOrder, 'StepName' => $StepName, 'Description' => $Description, 'RelatedStatusID' => $RelatedStatusID, 'RelatedUserID' => $RelatedUserID, 'RelatedTaskID' => $RelatedTaskID, 'Deadline' => $Deadline);
  }

  mysqli_free_result($result);
  echo json_encode($wftarray, JSON_PRETTY_PRINT);
}

if (isset($_GET['getArchivedDocument'])) {
  $ArchiveDocID = $_GET['ArchiveDocID'];

  $sql = "SELECT itsm_knowledge_archive.Content, itsm_knowledge_archive.DocumentVersion,itsm_knowledge_archive.Date,CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
          FROM itsm_knowledge_archive
          LEFT JOIN users ON itsm_knowledge_archive.RelatedUser = users.ID
          WHERE itsm_knowledge_archive.ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("i", $ArchiveDocID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Content = $row['Content'];
    $DocumentVersion = $row['DocumentVersion'];
    $FullName = $row['FullName'];
    $Date = $row['Date'];
    $Date = $functions->convertToDanishDateTimeFormat($Date);

    $ContentArray[] = array('Content' => $Content, 'DocumentVersion' => $DocumentVersion, 'FullName' => $FullName, 'Date' => $Date);
  }

  mysqli_free_result($result);
  echo json_encode($ContentArray, JSON_PRETTY_PRINT);
}

if (isset($_GET['testCreateNewTicket'])) {

  $UserSessionID = $_SESSION['id'];
  $ModuleID = "1";
  $ElementID = "1";
  $ProblemText = $_POST['ProblemText'];
  $SanitizedString = sanitizeTextAndBase64($ProblemText, $ElementID, $ModuleID);
}

if (isset($_GET['getFormDatarows'])) {

  $FormID = $_POST['FormID'];
  $FormTableName = getTableNameFromFormID($FormID);

  $sql = "SHOW COLUMNS FROM $FormTableName WHERE Field != 'ID' AND Field != 'RelatedRequestID'";
  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {
    $FieldName = $row["Field"];
    $Label = getFormColumnNameForTableView($FormID, $FieldName);
    $FieldArray[] = "$FieldName AS `$Label`";
  }

  $Fields = implode(",", $FieldArray);

  $sql = "SELECT $Fields
          FROM $FormTableName;";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $FormRows[] = $row;
  }
  if (empty($FormRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($FormRows);
  }
}

if (isset($_GET['getSiteCreation'])) {

  $sql = "SELECT SiteName, Status, Created, Progress
          FROM bpage.site_installation
          WHERE Status IN (1,4);";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }
  if (empty($TempArray)) {
    echo json_encode([]);
  } else {
    echo json_encode($TempArray);
  }
}

if (isset($_GET['getCIDatarows'])) {
  $CITypeID = $_POST['CITypeID'];
  $CIName = getCINameFromTypeID($CITypeID);
  $Active = $_POST['Active'];
  $CITableName = getCITableName($CITypeID);
  $FieldsArray = array();
  $TempArray = array();
  $group_array = $_SESSION['memberofgroups'];
  $CIRows = array();
  $ActiveArray = array();

  if (in_array("100001", $group_array) || in_array("100015", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  };

  if (!(in_array("100001", $group_array) || in_array("100014", $group_array) || in_array("100015", $group_array))) {
    exit;
  }
  
  $ActiveStatusFilter = "";

  // Build the query based on the value of $Active
  if ($Active == 1) {
      // When $Active is 1, select records with status codes 1 (active) and 2 (to be decommissioned)
      $ActiveStatusFilter = " != 0";
  } else {
      // When $Active is 0, select only inactive records
      $ActiveStatusFilter = " = 0";
  }

  $sql = "SELECT cmdb_ci_fieldslist.DefaultField,FieldType,FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,cmdb_cis.TableName,cmdb_ci_fieldslist.HideTables,cmdb_ci_fieldslist.HideForms
          FROM cmdb_ci_fieldslist
          LEFT JOIN cmdb_cis ON cmdb_ci_fieldslist.RelatedCITypeID = cmdb_cis.ID
          WHERE RelatedCITypeID = ? AND cmdb_cis.Active = 1
          ORDER BY cmdb_ci_fieldslist.FieldOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  if (!$stmt) {
    $functions->errorlog("Failed to prepare statement: " . mysqli_error($conn), "getCIDatarows");
    exit;
  }

  $stmt->bind_param("i", $CITypeID);

  if (!$stmt->execute()) {
    $functions->errorlog("Failed to execute statement: " . $stmt->error, "getCIDatarows");
    exit;
  }

  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }

  $SelectHeader = "SELECT $CITableName.ID,";
  $SelectButtom = "";
  $FieldsToShow = getCIFieldToWorkAsID($CITypeID);

  if (!$FieldsToShow) {
    $Text = $functions->translate("We are missing a primary field for this type");
    $CIRows[] = array("" => "Error: $Text");
    echo json_encode(array("CIName" => $CIName, "CIRows" => $CIRows));
    return;
  }

  foreach ($TempArray as $key => $value) {
    $FieldType = $value["FieldType"];
    $FieldName = $value["FieldName"];
    $FieldLabel = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $DefaultField = $value["DefaultField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];

    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      if ($FieldName == "Status") {
        $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $FieldLabel . "`,";
        $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.StatusCode AND $LookupTable.ModuleID = '$ITSMTypeID'";
      } else {
        if (strpos($LookupFieldResult, "CONCAT") !== false) {
          $NewFieldName = $LookupFieldResult . " AS `" . $FieldLabel . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        } else {
          $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $FieldLabel . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.ID";
        }
      }
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $FieldLabel . "`,";
    }

    $SelectHeader .= $NewFieldName;
  }
  $SelectHeader .= "FROM $CITableName ";
  $SelectHeader .= $SelectButtom;
  $SelectHeader .= " WHERE $CITableName.Active $ActiveStatusFilter ORDER BY $FieldsToShow ASC;";

  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $CIID = $row['ID'];
    //$viewBtn = "<a href=\"javascript:runModalViewCI('$CIID','$CITypeID','$AllowDelete');\"><i class=\"fa fa-pen-to-square\"></i></span></a>";

    $translatedRow = array();
    foreach ($row as $key => $value) {
      $translatedColumnName = $key;
      $translatedValue = $value;
      if($key == "ID"){
        continue;
      }

      foreach ($TempArray as $fieldInfo) {
        if ($fieldInfo['FieldLabel'] == $key) {
          if ($fieldInfo['FieldType'] == 5) {
            if ($translatedValue == "") {
              continue;
            } else {
              $timestamp = strtotime($translatedValue);
              $translatedValue = $functions->convertToDanishDateTimeFormat($translatedValue);
              $translatedValue = "<span style=\"display:none;\">$timestamp</span>$translatedValue";
            }
          }
          if ($fieldInfo['FieldName'] == 'Status') {
            $translatedValue = $functions->translate($translatedValue);
          }
          if ($fieldInfo['FieldName'] == $FieldsToShow) {
            $translatedValue = "<a href=\"javascript:runModalViewCI('$CIID','$CITypeID','$AllowDelete');\">$translatedValue</a>";
          }
        }
      }

      $translatedRow[$translatedColumnName] = $translatedValue;
    }
    $CIRows[] = $translatedRow;
  }

  if (empty($CIRows)) {
    echo json_encode(array("CIName" => $CIName, "CIRows" => []));
  } else {
    echo json_encode(array("CIName" => $CIName, "CIRows" => $CIRows));
  }
}

if (isset($_GET['getPasswords'])) {
  $SessionUserID = $_SESSION['id'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $FieldsArray = array();
  $TempArray = array();
  $group_array = $_SESSION['memberofgroups'];
  $GroupsImplode = implode("','", $group_array); // Adjusted for query
  $UserGroups = $group_array; // Array of user's groups
  $TeamID = $_SESSION['teamid']; // Assuming team ID is stored in session
  if($TeamID == ""){
    $TeamID = "0";
  }
  if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  }
  $ClosedStatusArray = $functions->getITSMClosedStatus($ITSMTypeID);

  $sql = "SELECT GroupFilterOptions,FieldType,FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,itsm_modules.TableName,itsm_fieldslist.HideTables,itsm_fieldslist.HideForms
          FROM itsm_fieldslist
          LEFT JOIN itsm_modules ON itsm_fieldslist.RelatedTypeID = itsm_modules.ID
          WHERE RelatedTypeID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("i", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }

  $SelectHeader = "SELECT $ITSMTableName.ID,";
  $SelectButtom = "";
  $FieldsToShow = $functions->getITSMFieldToWorkAsID($ITSMTypeID);
  $FieldPrimary = $FieldsToShow;

  if (!empty($FieldsToShow)) {
    $FieldsToShow = "ORDER BY $ITSMTableName.$FieldsToShow ASC";
  }

  foreach ($TempArray as $value) {
    $FieldType = $value["FieldType"];
    $FieldName = $value["FieldName"];
    $Label = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];
    $GroupFilterOptions = $value["GroupFilterOptions"];

    if ($GroupFilterOptions) {
      // Split the string by '#' to get an array of group IDs
      $groupFilterArray = explode('#', $GroupFilterOptions);

      // Iterate through the group filter array and check if any group ID exists in the user groups array
      foreach ($groupFilterArray as $groupID) {
        if (in_array($groupID, $group_array)) {
          $HideTables = "0";
        } else {
          $HideTables = "1";
        }
      }
    }

    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      if ($FieldName == "Status") {
        $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $Label . "`,";
        $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.StatusCode AND $LookupTable.ModuleID = '$ITSMTypeID'";
      } else {
        if (strpos($LookupFieldResult, "CONCAT") !== false) {
          $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        } else {
          $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.ID";
        }
      }
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $Label . "`,";
    }
    $SelectHeader .= $NewFieldName;
  }

  $SelectHeader .= "FROM $ITSMTableName ";
  $SelectHeader .= $SelectButtom;
  $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') ";
  $SelectHeader .= " AND (";
  $SelectHeader .= " ($ITSMTableName.Private = 0 AND (";
  $SelectHeader .= " $ITSMTableName.RelatedGroup IN ('$GroupsImplode') OR ";
  $SelectHeader .= " (($ITSMTableName.RelatedGroup = '' OR $ITSMTableName.RelatedGroup IS NULL) AND ($ITSMTableName.RelatedTeam = '' OR $ITSMTableName.RelatedTeam IS NULL)) OR ";
  $SelectHeader .= " $ITSMTableName.RelatedTeam = '$TeamID'";
  $SelectHeader .= " ))";
  $SelectHeader .= " OR ";
  $SelectHeader .= " ($ITSMTableName.Private = 1 AND $ITSMTableName.Responsible = '$SessionUserID')";
  $SelectHeader .= " OR $ITSMTableName.Responsible = '$SessionUserID'";
  $SelectHeader .= " )";

  if (!empty($FieldsToShow)) {
    $SelectHeader .= " $FieldsToShow";
  }

  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  $ITSMRows = [];
  while ($row = mysqli_fetch_assoc($result)) {
    $ITSMID = $row['ID'];

    $translatedRow = [];
    foreach ($row as $key => $value) {
      if ($key !== "") {
        $translatedColumnName = $functions->translate($key);
        $translatedValue = $value;
      } else {
        $translatedColumnName = $key;
        $translatedValue = $value;
      }

      foreach ($TempArray as $fieldInfo) {
        if ($fieldInfo['FieldLabel'] == $key) {
          if ($fieldInfo['FieldType'] == 5) {
            if ($translatedValue == "") {
              $translatedValue = "";
            } else {
              $translatedValue = $functions->convertToDanishDateTimeFormat($translatedValue);
            }
          }
          if ($fieldInfo['FieldName'] == 'Status') {
            $translatedValue = $functions->translate($translatedValue);
          }

          if ($fieldInfo['FieldName'] == $FieldPrimary) {
            $translatedValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\">$translatedValue</a>";
          }
        }
      }
      $translatedRow[$translatedColumnName] = $translatedValue;
    }
    $ITSMRows[] = $translatedRow;
  }

  if (empty($ITSMRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($ITSMRows);
  }
}

if (isset($_GET['getDocumentDatarows'])) {
  $SessionUserID = $_SESSION['id'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $FieldsArray = array();
  $TempArray = array();
  $group_array = $_SESSION['memberofgroups'];
  $GroupsImplode = implode(",", $group_array);
  if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  };
  $ClosedStatus = $functions->getITSMClosedStatus($ITSMTypeID);
  $ClosedStatus = implode(', ', $ClosedStatus);

  $sql = "SELECT FieldType,FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,itsm_modules.TableName,itsm_fieldslist.HideTables,itsm_fieldslist.HideForms
          FROM itsm_fieldslist
          LEFT JOIN itsm_modules ON itsm_fieldslist.RelatedTypeID = itsm_modules.ID
          WHERE RelatedTypeID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }
  $tmp = _("Actions");
  $SelectHeader = "SELECT $ITSMTableName.ID,'' AS $tmp,";
  $SelectButtom = "";
  $FieldsToShow = $functions->getITSMFieldToWorkAsID($ITSMTypeID);
  if (!empty($FieldsToShow)) {
    $FieldsToShow = "ORDER BY $ITSMTableName.$FieldsToShow ASC";
  }

  foreach ($TempArray as $key => $value) {
    $FieldType = $value["FieldType"];
    $FieldName = $value["FieldName"];
    $Label = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];
    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      if ($FieldName == "Status") {
        $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $Label . "`,";
        $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.StatusCode AND $LookupTable.ModuleID = '$ITSMTypeID'";
      } else {
        if (strpos($LookupFieldResult, "CONCAT") !== false) {
          $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        } else {
          $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.ID";
        }
      }
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $Label . "`,";
    }
    $SelectHeader .= $NewFieldName;
  }
  $SelectHeader .= "FROM $ITSMTableName ";
  $SelectHeader .= $SelectButtom;

  $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ($ClosedStatus) $FieldsToShow;";

  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $ITSMID = $row['ID'];
    $NewValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\"><span class=\"badge bg-gradient-success\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
    $row['Actions'] = $NewValue;

    $translatedRow = array();
    foreach ($row as $key => $value) {
      if ($key !== "") {
        $translatedColumnName = $functions->translate($key);
        $translatedValue = $value;
      } else {
        $translatedColumnName = $key;
        $translatedValue = $value;
      }
      // Process values based on FieldType and FieldName
      foreach ($TempArray as $fieldInfo) {

        if ($fieldInfo['FieldLabel'] == $key) {
          if ($fieldInfo['FieldType'] == 5) {
            if ($translatedValue == "") {
              $translatedValue = "";
            } else {
              $translatedValue = $functions->convertToDanishDateTimeFormat($translatedValue);
            }
          }
          if ($fieldInfo['FieldName'] == 'Status') {
            $translatedValue = $functions->translate($translatedValue);
          }
        }
      }

      $translatedRow[$translatedColumnName] = $translatedValue;
    }
    $ITSMRows[] = $translatedRow;
  }

  if (empty($ITSMRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($ITSMRows);
  }
}

if (isset($_GET['getITSMDatarowsKnowledge'])) {
  $SessionUserID = $_SESSION['id'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $Type = $_POST['Type'];
  $screensize = $_POST['screensize'];

  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $ClosedStatusArray = $functions->getITSMClosedStatus($ITSMTypeID);

  $FieldsArray = array();
  $TempArray = array();
  $group_array = $_SESSION['memberofgroups'];
  $UsersGroups = $group_array;

  if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  };

  $sql = "SELECT GroupFilterOptions,FieldType,FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,itsm_modules.TableName,itsm_fieldslist.HideTables,itsm_fieldslist.HideForms
          FROM itsm_fieldslist
          LEFT JOIN itsm_modules ON itsm_fieldslist.RelatedTypeID = itsm_modules.ID
          WHERE RelatedTypeID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }
  $tmp = _("Actions");
  $SelectHeader = "SELECT $ITSMTableName.ID,'' AS $tmp,";
  $SelectButtom = "";
  $SelectFilter = "";
  $FieldsToShow = $functions->getITSMFieldToWorkAsID($ITSMTypeID);
  $FieldPrimary = $FieldsToShow;
  
  if (!empty($FieldsToShow)) {
    $FieldsToShow = "ORDER BY $ITSMTableName.$FieldsToShow ASC";
  } else {
    $Text = $functions->translate("We are missing a primary field for this type");
    $ITSMRows[] = array("" => "Error: $Text");
    echo json_encode($ITSMRows);
    return;
  }

  foreach ($TempArray as $key => $value) {
    $FieldType = $value["FieldType"];
    $FieldName = $value["FieldName"];
    $Label = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];
    $GroupFilterOptions = $value["GroupFilterOptions"];

    if ($GroupFilterOptions) {
      // Split the string by '#' to get an array of group IDs
      $groupFilterArray = explode('#', $GroupFilterOptions);

      // Iterate through the group filter array and check if any group ID exists in the user groups array
      foreach ($groupFilterArray as $groupID) {
        if (in_array($groupID, $UsersGroups)) {
          $HideTables = "0";
        } else {
          $HideTables = "1";
        }
      }
    }

    $SelectFilter = "";
    if ($FieldType == "11") {
      $GroupFilter = " AND $ITSMTableName.$FieldName IN ('" . implode("','", $UserGroups) . "')";
      $SelectFilter .= $GroupFilter;
    } else {
      $GroupFilter = "";
    }
    if ($FieldType == "12") {
      $TeamFilter = " OR $ITSMTableName.$FieldName = '$TeamID'";
      $SelectFilter .= $TeamFilter;
    } else {
      $TeamFilter = "";
    }
    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      if ($FieldName == "Status") {
        $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $Label . "`,";
        $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.StatusCode AND $LookupTable.ModuleID = '$ITSMTypeID'";
      } else {
        if (strpos($LookupFieldResult, "CONCAT") !== false) {
          $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        } else {
          $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.ID";
        }
      }
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $Label . "`,";
    }
    $SelectHeader .= $NewFieldName;
  }
  switch ($Type) {
    case "all":
      $SelectHeader .= "FROM $ITSMTableName ";
      $SelectHeader .= $SelectButtom;
      $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') $SelectFilter $FieldsToShow;";
      break;
    case "my":
      $SelectHeader .= "FROM $ITSMTableName ";
      $SelectHeader .= $SelectButtom;
      $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') AND $ITSMTableName.Responsible = '$SessionUserID' $SelectFilter $FieldsToShow;";
      break;
    case "myteams":
      $SelectHeader .= "FROM $ITSMTableName ";
      $SelectHeader .= $SelectButtom;
      $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') AND $ITSMTableName.Team = $TeamID AND (($ITSMTableName.Responsible != $SessionUserID) OR ($ITSMTableName.Responsible IS NULL)) $SelectFilter $FieldsToShow;";
      break;
    case "meparticipating":
      $SelectButtom .= " LEFT JOIN itsm_participants ON $TableName.ID = itsm_participants.ElementID";
      $SelectHeader .= "FROM $ITSMTableName ";
      $SelectHeader .= $SelectButtom;
      $SelectHeader .= " WHERE itsm_participants.ModuleID = $ITSMTypeID AND $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') AND itsm_participants.UserID = $SessionUserID AND (($ITSMTableName.Responsible != $SessionUserID) OR ($ITSMTableName.Responsible IS NULL)) $SelectFilter $FieldsToShow;";
      break;
    default:
      $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') AND $ITSMTableName.Responsible = $SessionUserID $SelectFilter $FieldsToShow;";
  }

  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $ITSMID = $row['ID'];
    $NewValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\"><span class=\"badge bg-gradient-success\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
    $row['Actions'] = $NewValue;

    if ($screensize == "sm") {
      $Temp = $row["$FieldPrimary"];
      if (strlen($Temp) > 20) {
        $Temp = substr($Temp, 0, 20) . '...';
      }
      $NewValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\">$Temp</a>";
      $row["$FieldPrimary"] = $NewValue;
    } else {
      $Temp = $row["$FieldPrimary"];
      $NewValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\">$Temp</a>";
      $row["$FieldPrimary"] = $NewValue;
    }

    $translatedRow = array();

    foreach ($row as $key => $value) {

      if ($key == "ID") {
        continue; // Skip to the next iteration of the loop
      }
      if ($key == "Actions") {
        continue; // Skip to the next iteration of the loop
      }
      if ($key !== "") {
        $translatedColumnName = $functions->translate($key);
        $translatedValue = $value;
      } else {
        $translatedColumnName = $key;
        $translatedValue = $value;
      }

      if ($key == "Actions") {
        $translatedColumnName = "";
        $translatedValue = $value;
      }

      // Process values based on FieldType and FieldName
      foreach ($TempArray as $fieldInfo) {

        if ($fieldInfo['FieldLabel'] == $key) {
          if ($fieldInfo['FieldType'] == 5) {
            if ($translatedValue == "") {
              $translatedV43alue = "";
            } else {
              $timestamp = strtotime($translatedValue);
              $translatedValue = $functions->convertToDanishDateTimeFormat($translatedValue);
              $translatedValue = "<span style=\"display:none;\">$timestamp</span>$translatedValue";
            }
          }
          if ($fieldInfo['FieldName'] == 'Status') {
            $translatedValue = $functions->translate($translatedValue);
          }
        }
      }

      $translatedRow[$translatedColumnName] = $translatedValue;
    }
    $ITSMRows[] = $translatedRow;
  }

  if (empty($ITSMRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($ITSMRows);
  }
}

if (isset($_GET['getITSMDatarowsMy'])) {
  $SessionUserID = $_SESSION['id'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $Type = $_POST['Type'];
  $screensize = $_POST['screensize'];
  
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $ClosedStatusArray = $functions->getITSMClosedStatus($ITSMTypeID);

  $FieldsArray = array();  
  $TempArray = array();
  $group_array = $_SESSION['memberofgroups'];
  $UsersGroups = $group_array;

  if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  };

  $sql = "SELECT GroupFilterOptions,FieldType,FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,itsm_modules.TableName,itsm_fieldslist.HideTables,itsm_fieldslist.HideForms
          FROM itsm_fieldslist
          LEFT JOIN itsm_modules ON itsm_fieldslist.RelatedTypeID = itsm_modules.ID
          WHERE RelatedTypeID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC;";

  try {
    // Prepare the statement
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
      throw new Exception('Prepare failed: ' . htmlspecialchars(mysqli_error($conn)));
    }

    // Bind parameters
    if (!$stmt->bind_param("i", $ITSMTypeID)) {
      throw new Exception('Bind_param failed: ' . htmlspecialchars($stmt->error));
    }

    // Execute the statement
    if (!$stmt->execute()) {
      throw new Exception('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    // Get the result
    $result = $stmt->get_result();
    if (!$result) {
      throw new Exception('Get_result failed: ' . htmlspecialchars($stmt->error));
    }

    // Close the statement
    $stmt->close();
  } catch (Exception $e) {
    $functions->errorlog($e->getMessage(), "getITSMDatarowsMy");
  }

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }

  $SelectHeader = "SELECT $ITSMTableName.ID,";
  $SelectButtom = "";
  $SelectFilter = "";
  $FieldsToShow = $functions->getITSMFieldToWorkAsID($ITSMTypeID);
  $FieldPrimary = $FieldsToShow;

  if (!empty($FieldsToShow)) {
    $FieldsToShow = "ORDER BY $ITSMTableName.$FieldsToShow ASC";
  } else {
    $Text = $functions->translate("We are missing a primary field for this type");
    $ITSMRows[] = array("" => "Error: $Text");
    echo json_encode($ITSMRows);
    return;
  }

  foreach ($TempArray as $key => $value) {
    $FieldType = $value["FieldType"];    
    $FieldName = $value["FieldName"];
    $Label = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];
    $GroupFilterOptions = $value["GroupFilterOptions"];

    if ($GroupFilterOptions) {
      // Split the string by '#' to get an array of group IDs
      $groupFilterArray = explode('#', $GroupFilterOptions);

      // Iterate through the group filter array and check if any group ID exists in the user groups array
      foreach ($groupFilterArray as $groupID) {
        if (in_array($groupID, $UsersGroups)) {
          $HideTables = "0";
        } else {
          $HideTables = "1";
        }
      }
    }

    $SelectFilter = "";
    if ($FieldType == "11") {
      $GroupFilter = " AND $ITSMTableName.$FieldName IN ('" . implode("','", $UserGroups) . "')";
      $SelectFilter .= $GroupFilter;
    } else {
      $GroupFilter = "";
    }
    if ($FieldType == "12") {
      $TeamFilter = " OR $ITSMTableName.$FieldName = '$TeamID'";
      $SelectFilter .= $TeamFilter;
    } else {
      $TeamFilter = "";
    }
    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      if ($FieldName == "Status") {
        $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $Label . "`,";
        $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.StatusCode AND $LookupTable.ModuleID = '$ITSMTypeID'";
      } else {
        if (strpos($LookupFieldResult, "CONCAT") !== false) {
          $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        } else {
          $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.ID";
        }
      }
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $Label . "`,";
    }
    $SelectHeader .= $NewFieldName;
  }
  
  switch ($Type) {
    case "my":
      $SelectHeader .= "FROM $ITSMTableName ";
      $SelectHeader .= $SelectButtom;
      $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') AND $ITSMTableName.Responsible = $SessionUserID $SelectFilter $FieldsToShow;";
      break;
    case "myteams":
      $SelectHeader .= "FROM $ITSMTableName ";
      $SelectHeader .= $SelectButtom;
      $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') AND $ITSMTableName.Team = $TeamID AND (($ITSMTableName.Responsible != $SessionUserID) OR ($ITSMTableName.Responsible IS NULL)) $SelectFilter $FieldsToShow;";
      break;
    case "meparticipating":
      $SelectButtom .= " LEFT JOIN itsm_participants ON $TableName.ID = itsm_participants.ElementID";
      $SelectHeader .= "FROM $ITSMTableName ";
      $SelectHeader .= $SelectButtom;
      $SelectHeader .= " WHERE itsm_participants.ModuleID = $ITSMTypeID AND $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') AND itsm_participants.UserID = $SessionUserID AND (($ITSMTableName.Responsible != $SessionUserID) OR ($ITSMTableName.Responsible IS NULL)) $SelectFilter $FieldsToShow;";
      break;
    default:
      $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') AND $ITSMTableName.Responsible = $SessionUserID $SelectFilter $FieldsToShow;";
  }

  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $ITSMID = $row['ID'];
    $numberOfUnreadComments = getNumberOfUnreadITSMComments($ITSMTypeID, $ITSMID, $SessionUserID);
    $unreadCommentsElement = "";

    if($numberOfUnreadComments > 0){
      $unreadCommentsElement = "<span class=\"badge badge-circle badge-info\" title=\"" . $functions->translate("Unread comments") . "\">$numberOfUnreadComments</span>";
    }

    $viewBtn = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\"><span class=\"badge bg-gradient-success\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
    
    $translatedRow = [];
    foreach ($row as $key => $value) {      
      if ($key !== "") {
        $translatedColumnName = $functions->translate($key);
        $translatedValue = $value;
      } else {
        $translatedColumnName = $key;
        $translatedValue = $value;
      }

      // Process values based on FieldType and FieldName
      foreach ($TempArray as $fieldInfo) {

        if ($fieldInfo['FieldLabel'] == $key) {
          if ($fieldInfo['FieldType'] == 5) {
            if ($translatedValue == "") {
              $translatedValue = "";
            } else {
              $timestamp = strtotime($translatedValue);
              $translatedValue = $functions->convertToDanishDateTimeFormat($translatedValue);
              $translatedValue = "<span style=\"display:none;\">$timestamp</span>$translatedValue";
            }
          }
          if ($fieldInfo['FieldName'] == 'Status') {
            $translatedValue = $functions->translate($translatedValue);
          }

          if ($fieldInfo['FieldName'] == $FieldPrimary) {
            $translatedValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\">$translatedValue</a>";
          }
        }
      }

      $translatedRow[$translatedColumnName] = $translatedValue;
    }
    $ITSMRows[] = $translatedRow;
  }

  if (empty($ITSMRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($ITSMRows);
  }
}

if (isset($_GET['getITSMDatarowsQuickView'])) {
  $ITSMTypeID = $_GET["ITSMTypeID"];
  $FilterValue = $_GET["FilterValue"];
  $SessionUserID = $_SESSION['id'];
  $SessionTeamId = $_SESSION['teamid'];

  $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $FieldsArray = array();
  $TempArray[] = array("FieldName" => "ID", "Label" => "ID", "LookupTable" => "", "LookupField" => "", "LookupFieldResultTable" => "", "TableName" => "$ITSMTableName");
  $group_array = $_SESSION['memberofgroups'];
  if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  };
  $ClosedStatus = $functions->getITSMClosedStatus($ITSMTypeID);

  $sql = "SELECT FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,itsm_modules.TableName,itsm_fieldslist.HideTables,itsm_fieldslist.HideForms
          FROM itsm_fieldslist
          LEFT JOIN itsm_modules ON itsm_fieldslist.RelatedTypeID = itsm_modules.ID
          WHERE RelatedTypeID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }
  $tmp = _("Actions");
  $SelectHeader = "SELECT $ITSMTableName.ID,'' AS $tmp,";
  $SelectButtom = "";
  $FieldsToShow = $functions->getITSMFieldToWorkAsID($ITSMTypeID);
  if (!empty($FieldsToShow)) {
    $FieldsToShow = "ORDER BY $ITSMTableName.$FieldsToShow ASC";
  }

  foreach ($TempArray as $key => $value) {
    $FieldName = $value["FieldName"];
    $Label = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];
    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      if ($FieldName == "Status") {
        $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
        $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID AND $FieldName.ModuleID = $ITSMTypeID";
      } else {
        if (strpos($LookupFieldResult, "CONCAT") !== false) {
          $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        } else {
          $NewFieldName = $FieldName . "." . $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        }
      }
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $Label . "`,";
    }
    $SelectHeader .= $NewFieldName;
  }
  $SelectHeader .= "FROM $ITSMTableName ";
  $SelectHeader .= $SelectButtom;

  switch ($FilterValue) {
    case "1":
      $SelectHeader .= " WHERE $ITSMTableName.Status != '$ClosedStatus' AND $ITSMTableName.Responsible = $SessionUserID $FieldsToShow;";
      break;
    case "2":
      $SelectHeader .= " WHERE $ITSMTableName.Status != '$ClosedStatus' AND $ITSMTableName.Responsible != $SessionUserID AND $ITSMTableName.Team = '$SessionTeamId' $FieldsToShow;";
      break;
    case "3":
      $SelectHeader .= " LEFT JOIN itsm_participants ON $ITSMTableName.ID = itsm_participants.ElementID AND itsm_participants.ModuleID = '$ITSMTypeID' AND itsm_participants.UserID = '$SessionUserID'";
      $SelectHeader .= " WHERE $ITSMTableName.Status != '$ClosedStatus' AND $ITSMTableName.Responsible != $SessionUserID $FieldsToShow;";
      break;
    case "4":
      $SelectHeader .= " WHERE $ITSMTableName.Status != '$ClosedStatus' $FieldsToShow;";
      break;
    case "5":
      $SelectHeader .= " WHERE $ITSMTableName.Status = '$ClosedStatus' $FieldsToShow;";
      break;
    default:
      $SelectHeader .= " WHERE $ITSMTableName.Status != '$ClosedStatus' AND $ITSMTableName.Responsible = $SessionUserID $FieldsToShow;";
  }

  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  $ITSMRows = [];

  while ($row = mysqli_fetch_array($result)) {
    $ITSMID = $row['ID'];
    $Subject = $row['Subject'];
    $ITSMRows[] = array($functions->translate("ID") => "$ITSMID", $functions->translate("Subject") => "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','quickview');\">$Subject</a>");
  }

  if (empty($ITSMRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($ITSMRows);
  }
}

if (isset($_GET['getITSMDatarowsMyTeams'])) {
  $SessionUserID = $_SESSION['id'];
  $TeamID = $_SESSION['teamid'];
  if ($TeamID == "") {
    echo json_encode([]);
    return;
  }
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $FieldsArray = array();
  //$TempArray[] = array("FieldName" => "ID", "Label" => "ID", "LookupTable" => "", "LookupField" => "", "LookupFieldResultTable" => "", "TableName" => "$ITSMTableName");
  $TempArray = array();
  $group_array = $_SESSION['memberofgroups'];
  if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  };

  $ClosedStatusArray = $functions->getITSMClosedStatus($ITSMTypeID);

  $sql = "SELECT FieldType,FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,itsm_modules.TableName,itsm_fieldslist.HideTables,itsm_fieldslist.HideForms
          FROM itsm_fieldslist
          LEFT JOIN itsm_modules ON itsm_fieldslist.RelatedTypeID = itsm_modules.ID
          WHERE RelatedTypeID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }
  $tmp = _("Actions");
  $SelectHeader = "SELECT $ITSMTableName.ID,'' AS $tmp,";
  $SelectButtom = "";
  $SelectFilter = "";
  $FieldsToShow = $functions->getITSMFieldToWorkAsID($ITSMTypeID);
  if (!empty($FieldsToShow)) {
    $FieldsToShow = "ORDER BY $ITSMTableName.$FieldsToShow ASC";
  }

  foreach ($TempArray as $key => $value) {
    $FieldType = $value["FieldType"];
    $FieldName = $value["FieldName"];
    $Label = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];

    if ($FieldType == "11") {
      $GroupFilter = " AND $ITSMTableName.$FieldName IN ('" . implode("','", $UserGroups) . "')";
      $SelectFilter .= $GroupFilter;
    } else {
      $GroupFilter = "";
    }
    if ($FieldType == "12") {
      $TeamFilter = " OR $ITSMTableName.$FieldName = '$TeamID'";
      $SelectFilter .= $TeamFilter;
    } else {
      $TeamFilter = "";
    }

    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      if ($FieldName == "Status") {
        $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $Label . "`,";
        $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.StatusCode AND $LookupTable.ModuleID = '$ITSMTypeID'";
      } else {
        if (strpos($LookupFieldResult, "CONCAT") !== false) {
          $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        } else {
          $NewFieldName = $FieldName . "." . $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        }
      }
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $Label . "`,";
    }
    $SelectHeader .= $NewFieldName;
  }
  $SelectHeader .= "FROM $ITSMTableName ";
  $SelectHeader .= $SelectButtom;
  $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') AND $ITSMTableName.Team = $TeamID AND (($ITSMTableName.Responsible != $SessionUserID) OR ($ITSMTableName.Responsible IS NULL)) $SelectFilter $FieldsToShow;";

  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $ITSMID = $row['ID'];
    $NewValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\"><span class=\"badge bg-gradient-success\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
    $row['Actions'] = $NewValue;

    $translatedRow = array();
    foreach ($row as $key => $value) {
      if ($key !== "") {
        $translatedColumnName = $functions->translate($key);
        $translatedValue = $value;
      } else {
        $translatedColumnName = $key;
        $translatedValue = $value;
      }
      // Process values based on FieldType and FieldName
      foreach ($TempArray as $fieldInfo) {

        if ($fieldInfo['FieldLabel'] == $key) {
          if ($fieldInfo['FieldType'] == 5) {
            if ($translatedValue == "") {
              $translatedValue = "";
            } else {
              $timestamp = strtotime($translatedValue);
              $translatedValue = $functions->convertToDanishDateTimeFormat($translatedValue);
              $translatedValue = "<span style=\"display:none;\">$timestamp</span>$translatedValue";
            }
          }
          if ($fieldInfo['FieldName'] == 'Status') {
            $translatedValue = $functions->translate($translatedValue);
          }
        }
      }

      $translatedRow[$translatedColumnName] = $translatedValue;
    }
    $ITSMRows[] = $translatedRow;
  }

  if (empty($ITSMRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($ITSMRows);
  }
}

if (isset($_GET['getITSMDatarowsMeParticipating'])) {
  $SessionUserID = $_SESSION['id'];
  $TeamID = $_SESSION['teamid'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $FieldsArray = array();
  //$TempArray[] = array("FieldName" => "ID", "Label" => "ID", "LookupTable" => "", "LookupField" => "", "LookupFieldResultTable" => "", "TableName" => "$ITSMTableName");
  $TempArray = array();
  $group_array = $_SESSION['memberofgroups'];
  if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  };
  $ClosedStatusArray = $functions->getITSMClosedStatus($ITSMTypeID);

  $sql = "SELECT FieldType,FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,itsm_modules.TableName,itsm_fieldslist.HideTables,itsm_fieldslist.HideForms
          FROM itsm_fieldslist
          LEFT JOIN itsm_modules ON itsm_fieldslist.RelatedTypeID = itsm_modules.ID
          WHERE RelatedTypeID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }
  $tmp = _("Actions");
  $SelectHeader = "SELECT $ITSMTableName.ID,'' AS $tmp,";
  $SelectButtom = "";
  $SelectFilter = "";
  $FieldsToShow = $functions->getITSMFieldToWorkAsID($ITSMTypeID);
  if (!empty($FieldsToShow)) {
    $FieldsToShow = "ORDER BY $ITSMTableName.$FieldsToShow ASC";
  }

  foreach ($TempArray as $key => $value) {
    $FieldType = $value["FieldType"];
    $FieldName = $value["FieldName"];
    $Label = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];

    if ($FieldType == "11") {
      $GroupFilter = " AND $ITSMTableName.$FieldName IN ('" . implode("','", $UserGroups) . "')";
      $SelectFilter .= $GroupFilter;
    } else {
      $GroupFilter = "";
    }
    if ($FieldType == "12") {
      $TeamFilter = " OR $ITSMTableName.$FieldName = '$TeamID'";
      $SelectFilter .= $TeamFilter;
    } else {
      $TeamFilter = "";
    }

    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      if ($FieldName == "Status") {
        $NewFieldName = $LookupTable . "." . $LookupFieldResult . " AS `" . $Label . "`,";
        $SelectButtom .= " LEFT JOIN $LookupTable ON $TableName.$FieldName = $LookupTable.StatusCode AND $LookupTable.ModuleID = '$ITSMTypeID'";
      } else {
        if (strpos($LookupFieldResult, "CONCAT") !== false) {
          $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        } else {
          $NewFieldName = $FieldName . "." . $LookupFieldResult . " AS `" . $Label . "`,";
          $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
        }
      }
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $Label . "`,";
    }
    $SelectHeader .= $NewFieldName;
  }
  $SelectButtom .= " LEFT JOIN itsm_participants ON $TableName.ID = itsm_participants.ElementID";
  $SelectHeader .= "FROM $ITSMTableName ";
  $SelectHeader .= $SelectButtom;
  $SelectHeader .= " WHERE itsm_participants.ModuleID = $ITSMTypeID AND $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "') AND itsm_participants.UserID = $SessionUserID AND (($ITSMTableName.Responsible != $SessionUserID) OR ($ITSMTableName.Responsible IS NULL)) $SelectFilter $FieldsToShow;";

  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $ITSMID = $row['ID'];
    $NewValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\"><span class=\"badge bg-gradient-success\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
    $row['Actions'] = $NewValue;

    $translatedRow = array();
    foreach ($row as $key => $value) {
      if ($key !== "") {
        $translatedColumnName = $functions->translate($key);
        $translatedValue = $value;
      } else {
        $translatedColumnName = $key;
        $translatedValue = $value;
      }
      // Process values based on FieldType and FieldName
      foreach ($TempArray as $fieldInfo) {

        if ($fieldInfo['FieldLabel'] == $key) {
          if ($fieldInfo['FieldType'] == 5) {
            if ($translatedValue == "") {
              $translatedValue = "";
            } else {
              $timestamp = strtotime($translatedValue);
              $translatedValue = $functions->convertToDanishDateTimeFormat($translatedValue);
              $translatedValue = "<span style=\"display:none;\">$timestamp</span>$translatedValue";
            }
          }
          if ($fieldInfo['FieldName'] == 'Status') {
            $translatedValue = $functions->translate($translatedValue);
          }
        }
      }

      $translatedRow[$translatedColumnName] = $translatedValue;
    }
    $ITSMRows[] = $translatedRow;
  }

  if (empty($ITSMRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($ITSMRows);
  }
}

if (isset($_GET['getITSMDatarowsAllOpen'])) {
  $SessionUserID = $_SESSION['id'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $FieldsArray = array();
  $TempArray[] = array("FieldName" => "ID", "Label" => "ID", "LookupTable" => "", "LookupField" => "", "LookupFieldResultTable" => "", "TableName" => "$ITSMTableName");
  $group_array = $_SESSION['memberofgroups'];
  if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  };

  $ClosedStatusArray = $functions->getITSMClosedStatus($ITSMTypeID);

  $sql = "SELECT FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,itsm_modules.TableName,itsm_fieldslist.HideTables,itsm_fieldslist.HideForms
          FROM itsm_fieldslist
          LEFT JOIN itsm_modules ON itsm_fieldslist.RelatedTypeID = itsm_modules.ID
          WHERE RelatedTypeID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }
  $tmp = _("Actions");
  $SelectHeader = "SELECT $ITSMTableName.ID,'' AS $tmp,";
  $SelectButtom = "";
  $FieldsToShow = $functions->getITSMFieldToWorkAsID($ITSMTypeID);
  if (!empty($FieldsToShow)) {
    $FieldsToShow = "ORDER BY $FieldsToShow ASC";
  }

  foreach ($TempArray as $key => $value) {
    $FieldName = $value["FieldName"];
    $Label = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];
    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      if ($FieldName == "Status") {
        $NewFieldName = "ITSMStatus.StatusName,";
        $SelectButtom .= " LEFT JOIN itsm_statuscodes AS ITSMStatus ON $ITSMTableName.Status = ITSMStatus.StatusCode AND ITSMStatus.ModuleID = $ITSMTypeID";
      } else {
        $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
        $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
      }
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $Label . "`,";
    }

    $SelectHeader .= $NewFieldName;
  }
  $SelectHeader .= "FROM $ITSMTableName ";
  $SelectHeader .= $SelectButtom;
  $SelectHeader .= " WHERE $ITSMTableName.Status NOT IN ('" . implode("','", $ClosedStatusArray) . "');";

  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $ITSMID = $row['ID'];
    $NewValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\"><span class=\"badge bg-gradient-success\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
    $row['Actions'] = $NewValue;
    $ITSMRows[] = $row;
  }

  if (empty($ITSMRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($ITSMRows);
  }
}

if (isset($_GET['getITSMDatarowsAllClosed'])) {
  $SessionUserID = $_SESSION['id'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $FieldsArray = array();
  $TempArray[] = array("FieldName" => "ID", "Label" => "ID", "LookupTable" => "", "LookupField" => "", "LookupFieldResultTable" => "", "TableName" => "$ITSMTableName");
  $group_array = $_SESSION['memberofgroups'];
  if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  };

  $ClosedStatusArray = $functions->getITSMClosedStatus($ITSMTypeID);

  $sql = "SELECT FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,itsm_modules.TableName,itsm_fieldslist.HideTables,itsm_fieldslist.HideForms
          FROM itsm_fieldslist
          LEFT JOIN itsm_modules ON itsm_fieldslist.RelatedTypeID = itsm_modules.ID
          WHERE RelatedTypeID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }
  $tmp = _("Actions");
  $SelectHeader = "SELECT $ITSMTableName.ID,'' AS $tmp,";
  $SelectButtom = "";
  $FieldsToShow = $functions->getITSMFieldToWorkAsID($ITSMTypeID);
  if (!empty($FieldsToShow)) {
    $FieldsToShow = "ORDER BY $FieldsToShow ASC";
  }

  foreach ($TempArray as $key => $value) {
    $FieldName = $value["FieldName"];
    $Label = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];
    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      if ($FieldName == "Status") {
        $NewFieldName = "ITSMStatus.StatusName,";
        $SelectButtom .= " LEFT JOIN itsm_statuscodes AS ITSMStatus ON $ITSMTableName.Status = ITSMStatus.StatusCode AND ITSMStatus.ModuleID = $ITSMTypeID";
      } else {
        $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
        $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
      }
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $Label . "`,";
    }

    $SelectHeader .= $NewFieldName;
  }
  $SelectHeader .= "FROM $ITSMTableName ";
  $SelectHeader .= $SelectButtom;
  $SelectHeader .= " WHERE $ITSMTableName.Status IN ('" . implode("','", $ClosedStatusArray) . "');";
  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $ITSMID = $row['ID'];
    $NewValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\"><span class=\"badge bg-gradient-success\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
    $row['Actions'] = $NewValue;
    $ITSMRows[] = $row;
  }

  if (empty($ITSMRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($ITSMRows);
  }
}

if (isset($_GET['getTempFiles'])) {

  $ElementType = $_POST['ElementType'];
  $UserID = $_POST['UserID'];
  $Content = "";

  $sql = "SELECT files_temp.FileName,files_temp.FileNameOriginal
          FROM files_temp
          WHERE RelatedUserID = ?
          ORDER BY files_temp.Date DESC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $UserID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $FileName = $row['FileName'];
    $FileNameOriginal = $row['FileNameOriginal'];
    $DeleteLink = "<a href=\"javascript:deleteTempPicture('$FileName',$UserID);\"><i class=\"fa-solid fa-trash\"></i></a>";
    $Content .= "$FileNameOriginal $DeleteLink<br>";
  }

  $TempArray[] = array("Content" => $Content);
  if (empty($Content)) {
    echo json_encode(["Content" => ""]);
  } else {
    echo json_encode($TempArray);
  }
}

if (isset($_GET['getITSMDataRows'])) {

  $CompanyID = $_POST['CompanyID'];
  $StatusCodes = $_POST['StatusCodes'];
  $USERID = $_POST['USERID'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $FieldsArray = array();
  $TempArray[] = array("FieldName" => "ID", "Label" => "ID", "LookupTable" => "", "LookupField" => "", "LookupFieldResultTable" => "", "TableName" => "$ITSMTableName");
  $group_array = $_SESSION['memberofgroups'];
  if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
    $AllowDelete = 1;
  } else {
    $AllowDelete = 0;
  };

  $sql = "SELECT FieldName,FieldLabel,LookupTable,LookupField,LookupFieldResultTable,itsm_modules.TableName,itsm_fieldslist.HideTables,itsm_fieldslist.HideForms
          FROM itsm_fieldslist
          LEFT JOIN itsm_modules ON itsm_fieldslist.RelatedTypeID = itsm_modules.ID
          WHERE RelatedTypeID = ?
          ORDER BY itsm_fieldslist.FieldOrder ASC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_assoc($result)) {
    $TempArray[] = $row;
  }
  $tmp = _("Actions");
  $SelectHeader = "SELECT $ITSMTableName.ID,'' AS $tmp,";
  $SelectButtom = "";
  $FieldsToShow = $functions->getITSMFieldToWorkAsID($ITSMTypeID);
  if (!empty($FieldsToShow)) {
    $FieldsToShow = "ORDER BY $FieldsToShow ASC";
  }

  foreach ($TempArray as $key => $value) {
    $FieldName = $value["FieldName"];
    $Label = $value["FieldLabel"];
    $LookupTable = $value["LookupTable"];
    $LookupField = $value["LookupField"];
    $LookupFieldResult = $value["LookupFieldResultTable"];
    $TableName = $value["TableName"];
    $HideTables = $value["HideTables"];
    if ($HideTables == "1") {
      continue;
    }

    if (!empty($LookupTable)) {
      $NewFieldName = $LookupFieldResult . " AS `" . $Label . "`,";
      $SelectButtom .= " LEFT JOIN $LookupTable AS $FieldName ON $TableName.$FieldName = $FieldName.ID";
    } else {
      $NewFieldName = $TableName . "." . $FieldName . " AS `" . $Label . "`,";
    }
    $SelectHeader .= $NewFieldName;
  }
  $SelectHeader .= "FROM $ITSMTableName ";
  $SelectHeader .= $SelectButtom;
  $SelectHeader .= " WHERE $ITSMTableName.Status IN ($StatusCodes) AND $ITSMTableName.RelatedCompanyID = $CompanyID";

  if ($USERID !== "none") {
    $SelectHeader .= " AND Customer = $USERID;";
  } else {
    $SelectHeader .= ";";
  }

  $Query = str_replace(",FROM", " FROM", $SelectHeader);

  $result = mysqli_query($conn, $Query) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_assoc($result)) {
    $ITSMID = $row['ID'];
    $NewValue = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\"><span class=\"badge bg-gradient-success\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
    $row['Actions'] = $NewValue;
    $ITSMRows[] = $row;
  }

  if (empty($ITSMRows)) {
    echo json_encode([]);
  } else {
    echo json_encode($ITSMRows);
  }
}

if (isset($_GET['getCIFieldsValues'])) {
  $CIID = $_GET['CIID'];
  $CITypeID = $_GET['CITypeID'];
  $CITableName = getCITableName($CITypeID);
  $RelationFieldName = getRelationShowField($CITypeID);
  $CITypeName = getCITypeName($CITypeID);

  $FieldsArray = (array) null;
  $sql = "SHOW COLUMNS FROM $CITableName";
  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {
    $FieldName = $row["Field"];
    $FieldValue = $functions->getFieldValueFromID($CIID, $FieldName, $CITableName);
    $FieldType = getCMDBFieldTypeID($CITypeID, $FieldName);
    if($FieldType == "5" && !empty($FieldValue)){
      $FieldValue = $functions->convertToDanishDateTimeFormat($FieldValue);
    }

    $HideFormsState = getCMDBFieldHideFormsState($CITypeID, $FieldName);
    if ($HideFormsState == "1") {
      continue;
    }
    if ($FieldName == $RelationFieldName) {
      $RelationFIeld = "1";
    } else {
      $RelationFIeld = "0";
    }
    $FieldsArray[] = array("FieldType" => $FieldType,  "FieldName" => $FieldName, "FieldValue" => $FieldValue, "RelationFIeld" => $RelationFIeld, "CITypeName" => $CITypeName);
  }

  if (empty($FieldsArray)) {
    echo json_encode([]);
  } else {
    echo json_encode($FieldsArray);
  }
}

if (isset($_GET['getITSMFieldsValues'])) {
  $SessionUserID = $_SESSION['id'];
  $UserLanguageID = $functions->getUserLanguage($SessionUserID);
  $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);
  $UsersGroups = $_SESSION['memberofgroups'];
  $ITSMID = $_GET['ITSMID'];
  $ITSMTypeID = $_GET['ITSMTypeID'];
  $Type = $_GET['Type'];
  $CompanyID = $functions->getUserCompany($SessionUserID);

  if ($UserType == "2") {
    $ITSMCompanyID = $functions->getITSMCompanyID($ITSMTypeID, $ITSMID);
    if ($CompanyID != $ITSMCompanyID) {
      return;
    }
  }

  $GroupID = $functions->getITSMTypeUserRole($ITSMTypeID);
  $ModuleType = $functions->getModuleType($ITSMTypeID);
  $exit = false;

  $ITSMRoleID = $functions->getITSMTypeUserRole($ITSMTypeID);
  $RoleGroups = $functions->getRoleGroups($ITSMRoleID);
  $errorArray = array();
  $exit = false;

  // Check if any user group is in the role groups
  $commonGroups = array_intersect($UsersGroups, $RoleGroups);
  $RoleName = $functions->getRoleName($ITSMRoleID);
  
  // Check if $commonGroups is empty
  if (empty($commonGroups)) {
    // Check if "100001" is in $UsersGroups
    if (!in_array("100001", $UsersGroups)) {
      $RoleName = $functions->getRoleName($ITSMRoleID);
      $Message = _("You need to be a member of the role: ") . $RoleName;
      $errorArray[] = array("error" => $Message);
      $exit = true;
    }
  }

  if ($exit == true) {
    echo json_encode($errorArray);
    return;
  }

  $getPDF = "0";

  $FieldsArray = getITSMFieldsValues($ITSMTypeID, $ITSMID, $ModuleType, $UserLanguageCode, $Type, $UsersGroups, $getPDF);

  if (empty($FieldsArray)) {
    echo json_encode([]);
  } else {
    echo json_encode($FieldsArray);
  }
}

if (isset($_GET['getRequestFormFieldValues'])) {
  $ITSMID = $_POST['ITSMID'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $getPDF = "0";

  $FieldsArray = getRequestFormFieldValues($ITSMTypeID, $ITSMID, $getPDF);

  if (empty($FieldsArray)) {
    echo json_encode([]);
  } else {
    echo json_encode($FieldsArray);
  }
}

if (isset($_GET['getCIFieldDefinitions'])) {
  $SessionUserID = $_SESSION["id"];
  $CITypeID = $_POST['CITypeID'];
  $CIID = $_POST['CIID'];
  $ModalType = $_POST['ModalType'];
  $group_array = $_SESSION['memberofgroups'];

  try {
    $UserLanguageID = $functions->getUserLanguage($SessionUserID);
    $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);
    $CITableName = getCITableName($CITypeID);

    $FieldsArray = getCIFieldDefinitions($SessionUserID, $CITypeID, $CIID, $CITableName, $group_array, $FormType, $languageshort, $ModalType, $UserLanguageCode);
    // Respond with the resulting array or an empty array if no fields are found
    echo json_encode(empty($FieldsArray) ? [] : $FieldsArray);

  } catch (Exception $e) {
    // Log the error and return an appropriate response
    $functions->errorlog($e->getMessage(), "getCIFieldDefinitions");
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
  }
}

if (isset($_GET['getITSMFieldDefinitions'])) {
  $SessionUserID = $_SESSION["id"];
  $SessionTeamID = $_SESSION["teamid"];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMID = $_POST['ITSMID'];
  $ModalType = $_POST['ModalType'];
  $FormType = $_POST['FormType'];
  $UsersGroups = $_SESSION['memberofgroups'];

  $ModuleType = $functions->getITSMModuleType($ITSMTypeID);
  $CompanyID = $functions->getUserCompany($SessionUserID);


  if ($UserType == "2") {
    $ITSMCompanyID = $functions->getITSMCompanyID($ITSMTypeID, $ITSMID);
    if ($CompanyID != $ITSMCompanyID) {
      return;
    }
  }

  $ITSMRoleID = $functions->getITSMTypeUserRole($ITSMTypeID);
  $RoleGroups = $functions->getRoleGroups($ITSMRoleID);
  $errorArray = array();
  $exit = false;

  // Check if any user group is in the role groups
  $commonGroups = array_intersect($UsersGroups, $RoleGroups);

  // Check if $commonGroups is empty
  if (empty($commonGroups)) {
    // Check if "100001" is in $UsersGroups
    if (!in_array("100001", $UsersGroups)) {
      $RoleName = $functions->getRoleName($ITSMRoleID);
      $Message = _("You need to be a member of the role: ") . $RoleName;
      $errorArray[] = array("error" => $Message);
      $exit = true;
    }
  }

  if ($exit == true) {
    echo json_encode($errorArray);
    return;
  }

  $FieldsArray = getITSMFieldDefinitions($SessionUserID, $ITSMTypeID, $ITSMID, $FormType, $UsersGroups, $languageshort, $ModalType, $SessionTeamID);

  if (empty($FieldsArray)) {
    echo json_encode([]);
  } else {
    echo json_encode($FieldsArray);
  }
}

if (isset($_GET['getRequestFormView'])) {
  $SessionUserID = $_SESSION["id"];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMID = $_POST['ITSMID'];
  $FormID = $_POST['FormID'];
  $ModalType = "View";
  $UsersGroups = $_SESSION['memberofgroups'];

  $FieldsArray = getRequestFormView($SessionUserID, $ITSMTypeID, $ITSMID, $FormID, $languageshort, $ModalType, $UsersGroups);

  if (empty($FieldsArray)) {
    echo json_encode([]);
  } else {
    echo json_encode($FieldsArray);
  }
}

if (isset($_GET['addToKanBanTaskList'])) {
  $array[] = array();
  $UserID = $_SESSION['id'];
  $ElementID = $_POST['elementid'];
  $ModuleID = $_POST['moduleid'];
  $RedirectPage = $_POST['redirectpage'];

  if ($RedirectPage == "none") {
    $ITSMTableName = $functions->getITSMTableName($ModuleID);
    $RedirectPage = "javascript:viewITSM('$ElementID','$ModuleID','1','modal');";
  }

  $Exists = addtotaskslist($ElementID, $UserID, $ModuleID, $RedirectPage);
  $array[] = array('Exists' => $Exists);
  echo json_encode($array, JSON_PRETTY_PRINT);
}

if (isset($_GET['cloneITSM'])) {
  $UserID = $_SESSION['id'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $ITSMID = $_POST['ITSMID'];
  $ITSMModuleType = $functions->getITSMModuleType($ITSMTypeID);
  $ITSMModuleSLA = getITSMModuleSLA($ITSMTypeID);

  $Fields = array();

  $sql = "SELECT COLUMN_NAME
          FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ?
          AND TABLE_SCHEMA = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ITSMTableName, $dbname);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    array_push($Fields, $row[0]);
  }
  $sql = "INSERT INTO $ITSMTableName (";

  $lastElement = end($Fields);
  foreach ($Fields as $Field) {
    if ($Field == $lastElement) {
      $sql .= $Field;
    } else {
      $sql .= $Field . ",";
    }
  }

  $sql .= ") VALUES (";

  $lastElement = end($Fields);
  foreach ($Fields as $Field) {
    $PreValue = getITSMFieldPreValue($ITSMID, $ITSMTableName, $Field);
    if ($Field == $lastElement) {
      if ($Field == "ID" || $PreValue == "") {
        $sql .= "NULL";
      } elseif ($Field == "CreatedBy") {
        $sql .= "'$UserID'";
      } elseif ($Field == "LastUpdated" || $Field == "Created") {
        $sql .= "NOW()";
      } elseif ($Field == "RelatedCompanyID") {
        $CompanyID = $PreValue;
        $sql .= "'$PreValue',";
      } elseif ($Field == "BusinessService") {
        $BusinessServiceID = $PreValue;
        $sql .= "'$PreValue',";
      } elseif ($Field == "Priority") {
        $Priority = $PreValue;
        $sql .= "'$PreValue',";
      } else {
        $sql .= "'$PreValue'";
      }
    } else {
      if ($Field == "ID" || $PreValue == "") {
        $sql .= "NULL,";
      } elseif ($Field == "CreatedBy") {
        $sql .= "'$UserID',";
      } elseif ($Field == "LastUpdated" || $Field == "Created") {
        $sql .= "NOW(),";
      } elseif ($Field == "RelatedCompanyID") {
        $CompanyID = $PreValue;
        $sql .= "'$PreValue',";
      } elseif ($Field == "BusinessService") {
        $BusinessServiceID = $PreValue;
        $sql .= "'$PreValue',";
      } elseif ($Field == "Priority") {
        $Priority = $PreValue;
        $sql .= "'$PreValue',";
      } else {
        $sql .= "'$PreValue',";
      }
    }
  }
  $sql .= ")";

  $result = mysqli_query($conn, $sql);

  $NewITMSID = $conn->insert_id;
  $Text = "Cloned from $ITSMID";
  createITSMLogEntry($NewITMSID, $ITSMTypeID, $UserID, $Text);

  //Lets clone the request form
  if ($ITSMTypeID == "2") {
    $FormID = getRequestFormID($ITSMID);
    $FormTableName = getFormTableName($FormID);
    duplicateFormEntry($ITSMID, $NewITMSID, $FormTableName);
  }

  //Lets create SLA
  if ($ITSMModuleSLA == "1" && $ITSMModuleType == "1"){
    if (empty($BusinessServiceID)) {
      $SLA = getRelatedSLAID($CompanyID);
      updateITSMFieldValue($NewITMSID, $SLA, "SLA", $ITSMTypeID);
    } else {
      $SLA = getSLAFromBS($BusinessServiceID);
      updateITSMFieldValue($NewITMSID, $SLA, "SLA", $ITSMTypeID);
    }

    //Create ITSM SLA Reaction times
    //Get SLA reactiontimes for the SLA ID according to the priority selected
    if ($Priority !== "" && $SLA !== "") {

      $ReactionTimes[] = array();
      $ReactionTimes = getSLAStatusCores($ITSMTypeID, $SLA, $Priority);
      $ElementCreatedDateVal = date("Y-m-d H:i:s");
      foreach ($ReactionTimes as $ReactionTime) {
        $Status = $ReactionTime["Status"];
        $Minutes = $ReactionTime["Minutes"];
        $DateViolated = getDateTimeViolated($ElementCreatedDateVal, $Minutes);
        createTimelineSLAViolationDates($NewITMSID, $ITSMTypeID, $Status, $DateViolated);
      }
    }
  }  

  if ($NewITMSID !== "") {
    //success
    $Array[] = array("Result" => "success", "ITSMID" => $NewITMSID, "ITSMTypeID" => $ITSMTypeID);
    echo json_encode($Array);
  } else {
    $Array[] = array("Result" => "Fail");
    echo json_encode($Array);
  }
}

if (isset($_GET['sendITSMAsMailWithPDF'])) {
    $SessionUserID = $_SESSION['id'];
    $group_array = $_SESSION['memberofgroups'];
    $ITSMTypeID = $_POST['ITSMTypeID'];
    $ITSMID = $_POST['ITSMID'];
    $Type = $_POST['Type'];
    $PDFChoice = $_POST['PDFChoice'];
    $selectedEmail = $_POST['selectedEmail'];

    $ModuleType = $functions->getITSMModuleType($ITSMTypeID);
    $UserLanguageID = $functions->getUserLanguage($SessionUserID);
    $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);

    // Get merged fields and HTML content
    $Result = getITSMAsMailWithPDFFields($ITSMTypeID, $ITSMID, $SessionUserID, $ModuleType, $UserLanguageCode, $Type, $group_array);

    $htmlContent = $Result["htmlContent"];
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->SetTitle("Form: $ITSMTypeID");
    $mpdf->WriteHTML($htmlContent);

    $pdfDir = __DIR__ . '/uploads/files_itsm';
    if (!file_exists($pdfDir) && !mkdir($pdfDir, 0777, true)) {
        $functions->errorlog("Failed to create directory.", "sendITSMAsMailWithPDF");
    }

    $pdfName = "form_$ITSMTypeID" . "_" . $ITSMID . time() . '.pdf';
    $pdfPath = "$pdfDir/$pdfName";

    try {
        $mpdf->Output($pdfPath, 'F');
    } catch (\Mpdf\MpdfException $e) {
        $functions->errorlog($e->getMessage(), "sendITSMAsMailWithPDF");
        echo json_encode([["Result" => "error", "Message" => "Failed to create PDF."]]);
        exit;
    }

    if ($selectedEmail === "") {
        $sql = "INSERT INTO files_itsm (FileName, FileNameOriginal, RelatedElementID, Date, RelatedUserID, RelatedType, FileContent)
                VALUES (?, ?, ?, NOW(), ?, ?, NULL)";
        $functions->dmlQuery($sql, [$pdfName, $pdfName, $ITSMID, $SessionUserID, $ITSMTypeID], ["files_itsm"]);
    } else {
        try {
            $EmailSubject = "ITSM Form $ITSMTypeID";
            if ($PDFChoice === "1") {
                sendMailToSinglePerson($selectedEmail, $selectedEmail, $EmailSubject, $htmlContent, $pdfPath, $pdfName);
            } else {
                sendMailToSinglePerson($selectedEmail, $selectedEmail, $EmailSubject, $htmlContent);
            }
            if (file_exists($pdfPath)) unlink($pdfPath);
        } catch (Exception $e) {
            $functions->errorlog($e->getMessage(), "sendITSMAsMailWithPDF");
            echo json_encode([["Result" => "error", "Message" => "Failed to send email."]]);
            exit;
        }
    }

    echo json_encode([["Result" => "success", "ITSMID" => $ITSMID, "ITSMTypeID" => $ITSMTypeID]]);
}

if (isset($_GET['createITSMFromITSM'])) {
  $UserID = $_SESSION['id'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $ITSMID = $_POST['ITSMID'];

  $Fields = array();

  $sql = "SELECT FieldName
          FROM itsm_fieldslist
          WHERE FieldName != 'ID' AND RelatedTypeID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    array_push($Fields, $row[0]);
  }

  foreach ($Fields as $value) {
    $PreValue = getITSMFieldPreValue($ITSMID, $ITSMTableName, $value);
    $FinalArray[] = array("Field" => $value, "Value" => $PreValue);
  }

  if (!empty($FinalArray)) {
    echo json_encode($FinalArray);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['addallprojecttaskstotaskslists'])) {
  $UserID = $_SESSION['id'];
  $ProjectID = $_POST['ProjectID'];
  $ModuleID = "13";

  $sql = "SELECT project_tasks.ID AS ProjectTaskID
          FROM project_tasks
          INNER JOIN projects ON project_tasks.RelatedProject = projects.ID
          WHERE project_tasks.RelatedProject = ? AND project_tasks.Status NOT IN (6,7)";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ProjectID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $ProjectTaskID = $row["ProjectTaskID"];
    $RedirectPage = "projects_tasks_view.php?projecttaskid=$ProjectTaskID";
    $Exists = addtotaskslist($ProjectTaskID, $UserID, $ModuleID, $RedirectPage);
    $array[] = array('ProjectTaskID' => $ProjectTaskID, 'Exists' => $Exists);
  }

  echo json_encode($array, JSON_PRETTY_PRINT);
}

if (isset($_GET['deleteFormField'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100029");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $FieldID = $_POST['FieldID'];
  $FieldName = getRelatedFormFieldName($FieldID);
  $FormID = getRelatedFormID($FieldID);
  $TableName = getRelatedFormTableName($FormID);

  $sql1 = "DELETE FROM forms_fieldslist WHERE ID = $FieldID;";

  mysqli_query($conn, $sql1);

  $sql2 = "ALTER TABLE $TableName DROP COLUMN $FieldName;";

  mysqli_query($conn, $sql2);
}

if (isset($_GET['deleteCIField'])) {
  if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100015");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $FieldID = $_POST['FieldID'];
  $FieldName = $_POST['FieldName'];
  $CITypeID = getCITypeFromFieldID($FieldID);
  $TableName = getCITableName($CITypeID);

  $sql = "DELETE FROM cmdb_ci_fieldslist WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $FieldID);
  $stmt->execute();

  $sql2 = "ALTER TABLE $TableName DROP COLUMN $FieldName;";
  $result2 = mysqli_query($conn, $sql2);

  $Array = array("Result" => "success", "CITypeID" => $CITypeID);
  echo json_encode($Array);
}

if (isset($_GET['deleteITSMField'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $FieldID = $_POST['FieldID'];
  $FieldName = getITSMFieldNameFromFieldID($FieldID);
  $ITSMTypeID = getITSMTypeFromFieldID($FieldID);
  $TableName = $functions->getITSMTableName($ITSMTypeID);

  $sql = "DELETE FROM itsm_fieldslist WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $FieldID);
  $stmt->execute();

  $sql2 = "ALTER TABLE $TableName DROP COLUMN $FieldName;";

  $result2 = mysqli_query($conn, $sql2);

  $Array = array("Result" => "success", "ITSMTypeID" => $ITSMTypeID);
  echo json_encode($Array);
}

if (isset($_GET['deleteITSMStatus'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $StatusID = $_GET['StatusID'];

  deleteRelatedSLAMatrixEntries($StatusID);

  $sql = "DELETE FROM itsm_statuscodes WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $StatusID);
  $stmt->execute();
  $result = $stmt->get_result();
  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['deleteITSMSLA'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $SLAID = $_GET['SLAID'];

  $sql = "DELETE FROM itsm_sla_matrix WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SLAID);
  $stmt->execute();
  $result = $stmt->get_result();

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['deleteITSMEmail'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $EmailID = $_GET['EmailID'];

  $sql = "DELETE FROM itsm_emails WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $EmailID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['getFormFieldValue'])) {

  $UserID = $_SESSION['id'];
  $FieldID = $_POST['FieldID'];
  $FormID = getFormTypeIDFromFieldID($FieldID);

  $sql = "SELECT *
          FROM forms_fieldslist
          WHERE ID = ?";

  // Prepare the statement
  $stmt = $conn->prepare($sql);
  // Bind the parameter
  $stmt->bind_param("i", $FieldID);
  // Execute the query
  $stmt->execute();
  // Get the result set
  $result = $stmt->get_result();
  // Fetch the row
  $row = $result->fetch_assoc();

  // Create an associative array for the JSON response
  $response = array();

  // Iterate over the row and build the response array
  foreach ($row as $key => $value) {
    $field = array(
      "fieldName" => $key,
      "fieldValue" => $value,
      "FormID" => $FormID,
    );
    $response[] = $field;
  }

  if (isset($row['DefaultField'])) {
    $response[] = array(
      "fieldName" => "DefaultField",
      "fieldValue" => $row['DefaultField'],
    );
  }

  mysqli_free_result($result);
  echo json_encode($response);
}

if (isset($_GET['getCIFieldValue'])) {

  $UserID = $_SESSION['id'];
  $FieldID = $_POST['FieldID'];
  $CITypeID = getCITypeIDFromFieldID($FieldID);

  $sql = "SELECT *
          FROM cmdb_ci_fieldslist
          WHERE ID = ?";

  // Prepare the statement
  $stmt = $conn->prepare($sql);
  // Bind the parameter
  $stmt->bind_param("i", $FieldID);
  // Execute the query
  $stmt->execute();
  // Get the result set
  $result = $stmt->get_result();
  // Fetch the row
  $row = $result->fetch_assoc();

  // Create an associative array for the JSON response
  $response = array();

  // Iterate over the row and build the response array
  foreach ($row as $key => $value) {
    $field = array(
      "fieldName" => $key,
      "fieldValue" => $value,
    );
    $response[] = $field;
  }

  if (isset($row['DefaultField'])) {
    $response[] = array(
      "fieldName" => "DefaultField",
      "fieldValue" => $row['DefaultField'],
      "CITypeID" => $CITypeID,
    );
  }

  mysqli_free_result($result);
  echo json_encode($response);
}


if (isset($_GET['getITSMFieldValue'])) {

  $UserID = $_SESSION['id'];
  $FieldID = $_POST['FieldID'];
  $ITSMTypeID = getITSMTypeIDFromFieldID($FieldID);

  $sql = "SELECT *
          FROM itsm_fieldslist
          WHERE ID = ?";

  // Prepare the statement
  $stmt = $conn->prepare($sql);
  // Bind the parameter
  $stmt->bind_param("i", $FieldID);
  // Execute the query
  $stmt->execute();
  // Get the result set
  $result = $stmt->get_result();
  // Fetch the row
  $row = $result->fetch_assoc();

  // Create an associative array for the JSON response
  $response = array();

  // Iterate over the row and build the response array
  foreach ($row as $key => $value) {
    $field = array(
      "fieldName" => $key,
      "fieldValue" => $value,
    );
    $response[] = $field;
  }
  
  if (isset($row['DefaultField'])) {
    $response[] = array(
      "fieldName" => "DefaultField",
      "fieldValue" => $row['DefaultField'],
      "ITSMTypeID" => $ITSMTypeID,
    );
  }

  mysqli_free_result($result);
  echo json_encode($response);
}

if (isset($_GET['getITSMStatusValue'])) {

  $UserID = $_SESSION['id'];
  $StatusID = $_POST['StatusID'];

  $sql = "SELECT ID, StatusCode, StatusName, SLA, ClosedStatus
          FROM itsm_statuscodes
          WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $StatusID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == "0") {
    $Array = array('StatusCode' => "", 'StatusName' => "");
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array('ClosedStatus' => $row['ClosedStatus'], 'StatusID' => $row['ID'],  'StatusCode' => $row['StatusCode'], 'StatusName' => $row['StatusName'], 'SLA' => $row['SLA']);
    }
  }

  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['getSLAValues'])) {

  $UserID = $_SESSION['id'];
  $SLAID = $_POST['SLAID'];

  $sql = "SELECT ID,P1,P2,P3,P4
          FROM itsm_sla_matrix
          WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $SLAID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == "0") {
    $Array = array([]);
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array('ID' => $row['ID'], 'P1' => $row['P1'],  'P2' => $row['P2'], 'P3' => $row['P3'], 'P4' => $row['P4']);
    }
  }

  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['getEmailValues'])) {

  $UserID = $_SESSION['id'];
  $EmailID = $_POST['EmailID'];

  $sql = "SELECT ID, Email, DefaultEmail
          FROM itsm_emails
          WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("i", $EmailID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (mysqli_num_rows($result) == "0") {
    $Array = array([]);
  } else {
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array('ID' => $row['ID'], 'Email' => $row['Email'],  'DefaultEmail' => $row['DefaultEmail']);
    }
  }

  mysqli_free_result($result);
  echo json_encode($Array);
}

if (isset($_GET['syncCI'])) {
  $ResultArray = array();
  $ReturnValue = "";
  $CITypeID = $_POST["CITypeID"];
  $ReturnValue = syncCI($CITypeID);

  if($ReturnValue == "success"){
    //success
    $ResultArray[] = array("Result" => "success");
    echo json_encode($ResultArray);
  } else {
    $ResultArray[] = array("Result" => "$ReturnValue");
    echo json_encode($ResultArray);
    $CITypeName = getCITypeName($CITypeID);
    $EmailSubject = "Practicle Syncronization of $CITypeName failed";
    $Content = "Error: $ReturnValue";
    $AdminArray = getAllAdministratorsEmail();
    foreach ($AdminArray as $Admin) {
      $Email = $Admin["Email"];
      $Name = $Admin["Name"];
      sendMailToSinglePerson($Email, $Name, $EmailSubject, $Content);
    }
  }
}

if (isset($_GET['resetCISortOrder'])) {
  $CITypeID = $_POST["CITypeID"];

  $result = resetCISortOrder($CITypeID);

  if ($result == false) {
    //No result - error
    $Success[] = array("Result" => "fail");
    echo json_encode($Success);
  } else {
    //success
    $Success[] = array("Result" => "success");
    echo json_encode($Success);
  }
}

if (isset($_GET['resetITSMSortOrder'])) {
  $ITSMID = $_POST["ITSMID"];

  $result = resetITSMSortOrder($ITSMID);

  if ($result == false) {
    //No result - error
    $Success[] = array("Result" => "fail");
    echo json_encode($Success);
  } else {
    //success
    $Success[] = array("Result" => "success");
    echo json_encode($Success);
  }
}

if (isset($_GET['resetFormsSortOrder'])) {
  $FormID = $_POST["FormID"];

  $result = resetFormsSortOrder($FormID);

  if ($result == false) {
    //No result - error
    $Success[] = array("Result" => "fail");
    echo json_encode($Success);
  } else {
    //success
    $Success[] = array("Result" => "success");
    echo json_encode($Success);
  }
}

if (isset($_GET['CreateCIRelations'])) {
  $SessionUserID = $_SESSION['id'];
  $UserLanguageID = $functions->getUserLanguage($SessionUserID);
  $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);

  $ReturnArray = CreateCIRelations();

  if (empty($ReturnArray)) {
    //No result - error
    $Success[] = array("Result" => "fail");
    echo json_encode($Success);
  } else {
    //success
    $Success[] = array("Result" => "success", "UserLanguageCode" => $UserLanguageCode);
    echo json_encode($Success);
  }
}

if (isset($_GET['updateFormfield'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $response = array();
  $TableName = $_POST['TableName'];
  $FieldID = $_POST['FieldID'];
  $FieldSpecs = $_POST['FieldSpecs'];
  $FieldName = getFormFieldNameFromFieldID($FieldID);
  // Prepare the update statement
  $sql = "UPDATE forms_fieldslist SET ";
  $sql2 = "UPDATE forms_fieldslist SET "; // SQL statement for debugging
  $params = array();
  $types = "";

  // Create an array to keep track of the field names
  $fieldNames = array();

  $requiredFields = array("Indexed", "HideTables", "HideForms", "LockedCreate", "LockedView", "Required", "AddEmpty", "FullHeight","UserFullName", "Hidden");
  
  foreach ($FieldSpecs as $fieldSpec) {
    if ($fieldSpec['name']  === "ResultFields") {
      $ResultFieldsValue = $fieldSpec['value'];
    }
  }

  $UserFullName = false;

  foreach ($FieldSpecs as $fieldSpec) {
    if ($fieldSpec['name']  === "UserFullName") {
      if ($fieldSpec['value'] == "on") {
        
        $LookupFieldResultTable = makeLookupFieldResultTable($FieldID, "form");
        $LookupFieldResultView = makeLookupFieldResultView($FieldID, "form");
        $FieldSpecs[] = array('name' => "LookupFieldResultTable", 'value' => $LookupFieldResultTable);
        $FieldSpecs[] = array('name' => "LookupFieldResultView", 'value' => $LookupFieldResultView);
        $UserFullName = true;
      }
    }
  }
  
  if(!$UserFullName && $ResultFieldsValue){
    $FieldSpecs[] = array("name" => "LookupFieldResultTable", "value" => "$ResultFieldsValue");
    $FieldSpecs[] = array("name" => "LookupFieldResultView", "value" => "$ResultFieldsValue");
  }

  foreach ($requiredFields as $requiredField) {
    // Check if the required field exists in the FieldSpecs array
    $fieldExists = false;
    foreach ($FieldSpecs as $fieldSpec) {
      if ($fieldSpec['name'] === $requiredField) {
        $fieldExists = true;
        break;
      }
    }

    // If the required field doesn't exist, set the field value to 0
    if (!$fieldExists) {
      $FieldSpecs[] = array('name' => $requiredField, 'value' => 0);
    }
  }

  foreach ($FieldSpecs as $fieldSpec) {
    $fieldName = $fieldSpec['name'];
    $fieldValue = $fieldSpec['value'];

    // Check if fieldName is "LookupTable" and fieldValue is not empty
    if ($fieldName === 'LookupTable' && !empty($fieldValue)) {
        // Add new element to the array
        $FieldSpecs[] = array('name' => 'LookupField', 'value' => 'ID');
    }

    if ($fieldSpec['name'] === "SelectFieldOption" || $fieldSpec['name'] === "LookupTable2" || $fieldSpec['name'] === "LookupField2") {
      continue;
    }

    // Handle special fields (Indexed, HideTables, HideForms, SelectFieldOptions)
    if (in_array($fieldName, $requiredFields)) {
      if ($fieldValue === "on") {
        $fieldValue = 1;
        if ($fieldName === "Indexed") {
          alterTableAddFullTextToColumn($TableName, $FieldName);
        }
      } elseif ($fieldValue === 0) {
        if ($fieldName === "Indexed") {
          alterTableRemoveFullTextOnColumn($TableName, $FieldName);
        }
      }
    } elseif ($fieldName === "Addon") {
      // Skip the field if it is "off"
      if ($fieldValue === "") {
        $fieldValue = NULL;
      }
    }

    // Add the field name and value to the update statement
    $sql .= "$fieldName = ?, ";

    if ($fieldValue === "") {
      $sql2 .= "$fieldName = NULL, ";
    } else {
      $sql2 .= "$fieldName = '$fieldValue', ";
    }

    $params[] = $fieldValue;
    $types .= "s";

    // Add the field name to the fieldNames array
    $fieldNames[] = $fieldName;
  }

  // Remove the trailing comma and space
  $sql = rtrim($sql, ", ");
  $sql2 = rtrim($sql2, ", ");

  // Add the WHERE clause to update the specific field
  $sql .= " WHERE ID = ?";
  $sql2 .= " WHERE ID = $FieldID";

  $params[] = $FieldID;
  $types .= "s";

  // Prepare the statement
  $stmt = $conn->prepare($sql);

  // Check for errors during preparation
  if (!$stmt) {
    // Display the error message
    $functions->errorlog("Prepare failed: " . $conn->error, "updateITSMField");
    $response[] = array("Result" => "Prepare failed: " . $conn->error, "updateITSMField");
    echo json_encode($response);
    exit;
  }

  // Bind the parameters
  if (!$stmt->bind_param($types, ...$params)) {
    // Display the error message
    $functions->errorlog("Binding parameters failed: " . $stmt->error, "updateITSMField");
    $response[] = array("Result" => "Binding parameters failed: " . $stmt->error, "updateITSMField");
    echo json_encode($response);
    exit;
  }

  // Get the parameter count
  $paramCount = $stmt->param_count;

  // Build the debug information string
  $queryString = $sql . " [Params: ";

  for ($i = 0; $i < $paramCount; $i++) {
    $queryString .= $types[$i] . ": " . $params[$i] . ", ";
  }

  $queryString = rtrim($queryString, ", ");
  $queryString .= "]";

  // Execute the query
  if (!$stmt->execute()) {
    // Display the error message
    $functions->errorlog("Execute failed: " . $stmt->error, "updateFormField");
    $response[] = array("Result" => "Execute failed: " . $stmt->error, "updateITSMField");
    echo json_encode($response);
    exit;
  } else{
    $response[] = array("Result" => "success");
    echo json_encode($response); // Output the JSON string to the client
  }
}

if (isset($_GET['updateCIField'])) {

  if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100015");
    $array[] = array("Result" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $TableName = $_POST['TableName'];
  $CITypeID = getCITypeIDFromTableName($TableName);
  $FieldID = $_POST['FieldID'];
  $FieldSpecs = $_POST['FieldSpecs'];
  $FieldName = getCIFieldNameFromFieldID($FieldID);

  // Prepare the update statement
  $sql = "UPDATE cmdb_ci_fieldslist SET ";
  $sql2 = "UPDATE cmdb_ci_fieldslist SET "; // SQL statement for debugging
  $params = array();
  $types = "";

  // Create an array to keep track of the field names
  $fieldNames = array();

  $requiredFields = array("Indexed", "HideTables", "HideForms", "LockedCreate", "LockedView", "Required", "AddEmpty", "FullHeight", "RelationShowField", "UserFullName", "RightColumn","LabelType", "Hidden");

  foreach ($FieldSpecs as $fieldSpec) {
    if ($fieldSpec['name']  === "ResultFields") {
      $ResultFieldsValue = $fieldSpec['value'];
    }
  }

  $UserFullName = false;

  foreach ($FieldSpecs as $fieldSpec) {    
    if ($fieldSpec['name']  === "UserFullName") {     
      $LookupFieldResultTable = makeLookupFieldResultTable($FieldID, "ci");
      $LookupFieldResultView = makeLookupFieldResultView($FieldID, "ci");
      $FieldSpecs[] = array("name" => "LookupFieldResultTable", "value" => $LookupFieldResultTable);
      $FieldSpecs[] = array("name" => "LookupFieldResultView", "value" => $LookupFieldResultView);
      $UserFullName = true;
    }
  }

  if(!$UserFullName && $ResultFieldsValue){
    $FieldSpecs[] = array("name" => "LookupFieldResultTable", "value" => "$ResultFieldsValue");
    $FieldSpecs[] = array("name" => "LookupFieldResultView", "value" => "$ResultFieldsValue");
  }

  foreach ($requiredFields as $requiredField) {
    // Check if the required field exists in the FieldSpecs array
    $fieldExists = false;
    foreach ($FieldSpecs as $fieldSpec) {
      if ($fieldSpec['name'] === $requiredField) {
        $fieldExists = true;
        break;
      }
    }

    // If the required field doesn't exist, set the field value to 0
    if (!$fieldExists) {
      $FieldSpecs[] = array('name' => $requiredField, 'value' => 0);
    }
  }

  foreach ($FieldSpecs as $fieldSpec) {
    $fieldName = $fieldSpec['name'];
    $fieldValue = $fieldSpec['value'];

    // Check if fieldName is "LookupTable" and fieldValue is not empty
    if ($fieldName === 'LookupTable' && !empty($fieldValue)) {
        // Add new element to the array
        $FieldSpecs[] = array('name' => 'LookupField', 'value' => 'ID');
    }

    if ($fieldSpec['name'] === "SelectFieldOption" || $fieldSpec['name'] === "LookupTable2" || $fieldSpec['name'] === "LookupField2") {
      continue;
    }

    // Handle special fields (Indexed, HideTables, HideForms, SelectFieldOptions)
    if (in_array($fieldName, $requiredFields)) {
      if ($fieldValue === "on") {
        $fieldValue = 1;
        if ($fieldName === "Indexed") {
          alterTableAddFullTextToColumn($TableName, $FieldName);
        }
        
      } elseif ($fieldValue === 0) {
        if ($fieldName === "Indexed") {
          alterTableRemoveFullTextOnColumn($TableName, $FieldName);
        }
      }
      if ($fieldName == "RelationShowField") {
        if ($fieldValue == 0) {
          $currentField = getCurrentRelationShowField($FieldID,"ci");
          $NumberOfRelationShowFields = checkRelationShowField($CITypeID,"ci");
          if ($fieldValue == 0 && $currentField == 1) {
            if ($NumberOfRelationShowFields <= "1") {
              $response[] = array("Result" => $functions->translate("There must be a primary field"));
              echo json_encode($response);
              return;
            }
          }
        } else {
          removeOtherRelationShowField("ci", $CITypeID);
        }
      }
    } elseif ($fieldName === "Addon") {
      // Skip the field if it is "off"
      if ($fieldValue === "") {
        $fieldValue = NULL;
      }
    }

    // Add the field name and value to the update statement
    $sql .= "$fieldName = ?, ";
    if ($fieldValue === "") {
      $sql2 .= "$fieldName = NULL, ";
    } else {
      $sql2 .= "$fieldName = '$fieldValue', ";
    }

    $params[] = $fieldValue;
    $types .= "s";

    // Add the field name to the fieldNames array
    $fieldNames[] = $fieldName;
  }

  // Remove the trailing comma and space
  $sql = rtrim($sql, ", ");
  $sql2 = rtrim($sql2, ", ");

  // Add the WHERE clause to update the specific field
  $sql .= " WHERE ID = ?";
  $sql2 .= " WHERE ID = $FieldID";

  $params[] = $FieldID;
  $types .= "s";

  // Prepare the statement
  $stmt = $conn->prepare($sql);

  // Check for errors during preparation
  if (!$stmt) {
    // Display the error message
    $functions->errorlog("Prepare failed: " . $conn->error, "updateCIField");
    $response[] = array("Result" => "Prepare failed: " . $conn->error, "updateCIField");
    echo json_encode($response);
    exit;
  }

  // Bind the parameters
  if (!$stmt->bind_param($types, ...$params)) {
    // Display the error message
    $functions->errorlog("Binding parameters failed: " . $stmt->error, "updateCIField");
    $response[] = array("Result" => "Binding parameters failed: " . $stmt->error, "updateCIField");
    echo json_encode($response);
    exit;
  }

  // Get the parameter count
  $paramCount = $stmt->param_count;

  // Build the debug information string
  $queryString = $sql . " [Params: ";

  for ($i = 0; $i < $paramCount; $i++) {
    $queryString .= $types[$i] . ": " . $params[$i] . ", ";
  }

  $queryString = rtrim($queryString, ", ");
  $queryString .= "]";

  // Execute the query
  if (!$stmt->execute()) {
    // Display the error message
    $functions->errorlog("Execute failed: " . $stmt->error, "updateCIField");
    $response[] = array("Result" => "Execute failed: " . $stmt->error, "updateCIField");
    echo json_encode($response);
    exit;
  }

  $response[] = array("Result" => "success");

  // Convert the response array to JSON
  echo json_encode($response);
}

if (isset($_GET['updateITSMField'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $response = array();
  $TableName = $_POST['TableName'];
  $FieldID = $_POST['FieldID'];
  $ITSMTypeID = getITSMTypeFromFieldID($FieldID);
  $FieldSpecs = $_POST['FieldSpecs'];

  $FieldName = getITSMFieldNameFromFieldID($FieldID);

  // Prepare the update statement
  $sql = "UPDATE itsm_fieldslist SET ";
  $sql2 = "UPDATE itsm_fieldslist SET "; // SQL statement for debugging
  $params = array();
  $types = "";

  // Create an array to keep track of the field names
  $fieldNames = array();

  $requiredFields = array("Indexed", "HideTables", "HideForms", "LockedCreate", "LockedView", "Required", "AddEmpty", "FullHeight", "RelationShowField", "UserFullName", "RightColumn", "LabelType", "DefaultField", "Hidden");

  foreach ($FieldSpecs as $fieldSpec) {
    if ($fieldSpec['name']  === "ResultFields") {
      $ResultFieldsValue = $fieldSpec['value'];
    }
  }

  $UserFullName = false;

  foreach ($FieldSpecs as $fieldSpec) {
    if ($fieldSpec['name']  === "UserFullName") {
      $LookupFieldResultTable = makeLookupFieldResultTable($FieldID, "itsm");
      $LookupFieldResultView = makeLookupFieldResultView($FieldID, "itsm");
      $FieldSpecs[] = array("name" => "LookupFieldResultTable", "value" => $LookupFieldResultTable);
      $FieldSpecs[] = array("name" => "LookupFieldResultView", "value" => $LookupFieldResultView);
      $UserFullName = true;
    }
  }

  if (!$UserFullName && $ResultFieldsValue) {
    $FieldSpecs[] = array("name" => "LookupFieldResultTable", "value" => "$ResultFieldsValue");
    $FieldSpecs[] = array("name" => "LookupFieldResultView", "value" => "$ResultFieldsValue");
  }

  foreach ($requiredFields as $requiredField) {
    // Check if the required field exists in the FieldSpecs array
    $fieldExists = false;
    foreach ($FieldSpecs as $fieldSpec) {
      if ($fieldSpec['name'] === $requiredField) {
        $fieldExists = true;
        break;
      }
    }

    // If the required field doesn't exist, set the field value to 0
    if (!$fieldExists) {
      $FieldSpecs[] = array('name' => $requiredField, 'value' => 0);
    }
  }

  foreach ($FieldSpecs as $fieldSpec) {
    $fieldName = $fieldSpec['name'];
    $fieldValue = $fieldSpec['value'];

    // Check if fieldName is "LookupTable" and fieldValue is not empty
    if ($fieldName === 'LookupTable' && !empty($fieldValue)) {
      // Add new element to the array
      $FieldSpecs[] = array('name' => 'LookupField', 'value' => 'ID');
    }

    if ($fieldSpec['name'] === "SelectFieldOption" || $fieldSpec['name'] === "LookupTable2" || $fieldSpec['name'] === "LookupField2") {
      continue;
    }

    // Handle special fields (Indexed, HideTables, HideForms, SelectFieldOptions)
    if (in_array($fieldName, $requiredFields)) {
      if ($fieldValue === "on") {
        $fieldValue = 1;
        if ($fieldName === "Indexed") {
          alterTableAddFullTextToColumn($TableName, $FieldName);
        }
      } elseif ($fieldValue === 0) {
        if ($fieldName === "Indexed") {
          alterTableRemoveFullTextOnColumn($TableName, $FieldName);
        }
      }
    }
    if ($fieldName == "RelationShowField") {
      if ($fieldValue == 0) {
        $currentField = getCurrentRelationShowField($FieldID, "itsm");
        $NumberOfRelationShowFields = checkRelationShowField($ITSMTypeID, "itsm");
        if ($fieldValue == 0 && $currentField == 1) {
          if ($NumberOfRelationShowFields <= "1") {
            $response[] = array("Result" => $functions->translate("There must be a primary field"));
            echo json_encode($response);
            return;
          }
        }
      } else {
        removeOtherRelationShowField("itsm", $ITSMTypeID);
      }
    } elseif ($fieldName === "Addon") {
      // Skip the field if it is "off"
      if ($fieldValue === "") {
        $fieldValue = NULL;
      }
    }

    // Add the field name and value to the update statement
    $sql .= "$fieldName = ?, ";
    if ($fieldValue === "") {
      $sql2 .= "$fieldName = NULL, ";
    } else {
      $sql2 .= "$fieldName = '$fieldValue', ";
    }

    $params[] = $fieldValue;
    $types .= "s";

    // Add the field name to the fieldNames array
    $fieldNames[] = $fieldName;
  }

  // Remove the trailing comma and space
  $sql = rtrim($sql, ", ");
  $sql2 = rtrim($sql2, ", ");

  // Add the WHERE clause to update the specific field
  $sql .= " WHERE ID = ?";
  $sql2 .= " WHERE ID = $FieldID";

  $params[] = $FieldID;
  $types .= "s";

  // Prepare the statement
  $stmt = $conn->prepare($sql);

  // Check for errors during preparation
  if (!$stmt) {
    // Display the error message
    $functions->errorlog("Prepare failed: " . $conn->error, "updateITSMField");
    $response[] = array("Result" => "Prepare failed: " . $conn->error, "updateITSMField");
    echo json_encode($response);
    exit;
  }

  // Bind the parameters
  if (!$stmt->bind_param($types, ...$params)) {
    // Display the error message
    $functions->errorlog("Binding parameters failed: " . $stmt->error, "updateITSMField");
    $response[] = array("Result" => "Binding parameters failed: " . $stmt->error, "updateITSMField");
    echo json_encode($response);
    exit;
  }

  // Get the parameter count
  $paramCount = $stmt->param_count;

  // Build the debug information string
  $queryString = $sql . " [Params: ";

  for ($i = 0; $i < $paramCount; $i++) {
    $queryString .= $types[$i] . ": " . $params[$i] . ", ";
  }

  $queryString = rtrim($queryString, ", ");
  $queryString .= "]";

  // Execute the query
  if (!$stmt->execute()) {
    // Display the error message
    $functions->errorlog("Execute failed: " . $stmt->error, "updateITSMField");
    $response[] = array("Result" => "Execute failed: " . $stmt->error, "updateITSMField");
    echo json_encode($response);
    exit;
  } else{
    // Fieldname changed - so lets change field name in table
    if ($FieldNamePre != $FieldNamePost) {
      alterTableColumnName($TableName, $FieldNamePre, $FieldNamePost);
    }
    $response[] = array("Result" => "success");
    echo json_encode($response); // Output the JSON string to the client
  }
}

if (isset($_GET['updateITSMStatus'])) {

  $StatusID = $_GET['StatusID'];
  $StatusCode = $_GET['StatusCode'];
  $StatusName = $_GET['StatusName'];
  $SLA = $_GET['SLA'];
  $ClosedStatus = $_GET['ClosedStat'];
  $ITSMTypeID = $_GET['ITSMTypeID'];
  $PreSLA = getPreSLA($StatusID);

  if ($SLA == "2") {
    $SLA = "0";
  }

  if ($PreSLA !== $SLA) {
    if ($SLA == "0") {
      deleteStatusSLAEntries($ITSMTypeID, $StatusCode);
    } else {
      createStatusSLAEntries($ITSMTypeID, $StatusCode);
    }
  }

  if ($ClosedStatus == "2") {
    $ClosedStatus = "0";
  }

  $sql = "UPDATE itsm_statuscodes SET StatusCode = ?, StatusName = ?, SLA = ?, ClosedStatus = ?
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sssss", $StatusCode, $StatusName, $SLA, $ClosedStatus, $StatusID);
  $stmt->execute();

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['updateSLAValues'])) {

  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $SLAID = $_POST['SLAID'];
  $P1 = $_POST['P1'];
  $P2 = $_POST['P2'];
  $P3 = $_POST['P3'];
  $P4 = $_POST['P4'];

  $sql = "UPDATE itsm_sla_matrix SET P1 = ?, P2 = ?, P3 = ?, P4 = ? 
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sssss", $P1, $P2, $P3, $P4, $SLAID);
  $stmt->execute();
  $result = $stmt->get_result();

  $Array[] = array("ResultIs" => "succes");
  echo json_encode($Array);
}

if (isset($_GET['updateITSMEmail'])) {

  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $EmailID = $_POST['EmailID'];
  $ITSMEmail = $_POST['ITSMEmail'];
  $DefaultEmail = $_POST['DefaultEmail'];
  if ($DefaultEmail == "2") {
    $DefaultEmail = "0";
  }

  $sql = "UPDATE itsm_emails SET Email = ?, DefaultEmail = ? 
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssi", $ITSMEmail, $DefaultEmail, $EmailID);
  $stmt->execute();
  $result = $stmt->get_result();

  $Array[] = array("ResultIs" => "succes");
  echo json_encode($Array);
}

if (isset($_GET['deleteForm'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100029");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $FormID = $_GET['FormID'];

  $sql = "UPDATE forms SET Status = 0, Active = 0 WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $FormID);
  $stmt->execute();
  $stmt->get_result();
  mysqli_stmt_close($stmt);

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['deleteCI'])) {
  if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100015");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $CIID = $_POST['CIID'];

  $CITableName = getCITableName($CIID);
  $CITypeID = getCITypeIDFromTableName($CITableName);
  dropCITable($CITableName);
  deleteCIFromCMDBCITable($CIID);
  deleteCIFields($CIID);
  deleteCIRelations($CITableName);
  deleteCILogs($CITypeID);
  deleteCIFiles($CITypeID);
}

if (isset($_GET['deleteITSM'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

  dropITSMTable($ITSMTableName);
  deleteITSMFromITSMTable($ITSMTypeID);
  deleteITSMFields($ITSMTypeID);
  deleteITSMRelations($ITSMTableName);
  deleteITSMFiles($ITSMTypeID);
  deleteITSMLogs($ITSMTypeID);
  deleteITSMStatusCodes($ITSMTypeID);
  deleteITSMSLAMatrix($ITSMTypeID);
  deleteITSMSLATimelines($ITSMTypeID);
  deleteITSMParticipants($ITSMTypeID);
  deleteITSMTasks($ITSMTypeID);
}

if (isset($_GET['resetITSMModuleData'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

  deleteITSMs($ITSMTableName);
  deleteITSMRelations($ITSMTableName);
  deleteITSMFiles($ITSMTypeID);
  deleteITSMLogs($ITSMTypeID);
  deleteITSMSLATimelines($ITSMTypeID);
  deleteITSMParticipants($ITSMTypeID);
  deleteITSMTasks($ITSMTypeID);
  deleteITSMWorkFlows($ITSMTypeID);
  deleteITSMComments($ITSMTypeID);

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['resetAllITSMModulesData'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $ActiveITSMModules = $functions->getActiveITSMModules();

  foreach($ActiveITSMModules as $row){
    $ITSMTypeID = $row["ID"];
    $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

    deleteITSMs($ITSMTableName);
    deleteITSMRelations($ITSMTableName);
    deleteITSMFiles($ITSMTypeID);
    deleteITSMLogs($ITSMTypeID);
    deleteITSMSLATimelines($ITSMTypeID);
    deleteITSMParticipants($ITSMTypeID);
    deleteITSMTasks($ITSMTypeID);
    deleteITSMWorkFlows($ITSMTypeID);
    deleteITSMComments($ITSMTypeID);
  }

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['resetCI'])) {
  if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100015");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $CITypeID = $_GET['CITypeID'];

  $CITableName = getCITableName($CITypeID);
  truncateCITable($CITableName);
  deleteCIRelations($CITableName);
  deleteCILogs($CITypeID);
  deleteCIFiles($CITypeID);
  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['createForm'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
    $UserID = $_SESSION['id'];
    $GenerateRandomName = $functions->generateRandomString(15);
    $TableName = "formstable_$GenerateRandomName";
    $FormsName = $_GET['FormsName'];
    $FormDescription = $_GET['FormDescription'];

    $sql = "INSERT INTO forms (FormsName, TableName, Description, CreatedBy) VALUES (?,?,?,?)";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
      $error = mysqli_error($conn);
      $array[] = array("error" => "Failed to prepare the SQL statement: $error");
      echo json_encode($array);
      return;
    }
    $stmt->bind_param("ssss", $FormsName, $TableName, $FormDescription, $UserID);
    if (!$stmt->execute()) {
      $error = $stmt->error;
      $array[] = array("error" => "Failed to execute the SQL statement: $error");
      echo json_encode($array);
      return;
    }

    $last_id = mysqli_insert_id($conn);
    $FormComment = "formid=$last_id";

    $sql2 = "CREATE TABLE IF NOT EXISTS $TableName (
              ID INT AUTO_INCREMENT,
              RelatedRequestID INT NULL,
              PRIMARY KEY (ID))";
    if (!mysqli_query($conn, $sql2)) {
      $error = mysqli_error($conn);
      $array[] = array("error" => "Failed to create the table: $error");
      echo json_encode($array);
      return;
    }

    $sql3 = "ALTER TABLE $TableName COMMENT = '$FormComment'";
    if (!mysqli_query($conn, $sql3)) {
      $error = mysqli_error($conn);
      $array[] = array("error" => "Failed to set table comment: $error");
      echo json_encode($array);
      return;
    }
  } else {
    $GroupName = getUserGroupName("100029");
    $array[] = array("error" => "You need to be a member of $GroupName");
    echo json_encode($array);
    return;
  }
}

if (isset($_GET['createCIRelation'])) {
  $UserID = $_SESSION['id'];
  if ($UserID == "") {
    $UserID = "1";
  }
  $CITypeID1 = $_GET['CITypeID'];
  $CITableName1 = getCITableName($CITypeID1);
  $CIID1 = $_GET['CIID'];
  $CITableName2 = $_GET['CITableName2'];
  $CITypeID2 = getCITypeIDFromTableName($CITableName2);
  $CIID2 = $_GET['CIID2'];
  $Exists = checkifCIRelationExists($CITableName1, $CITableName2, $CIID1, $CIID2);

  if($Exists == "1"){
    $Array[] = array("Result" => "Relation exists!","CITypeID" => $CITypeID);
    echo json_encode($Array);
    return;
  }
  $RelationType = $_GET['RelationType'];

  if($RelationType === "parent"){
    $sql = "INSERT INTO cmdb_ci_relations(CITable1, CITable2, CI1ID, CI2ID, auto) VALUES (?,?,?,?,'0');";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ssii", $CITableName2, $CITableName1, $CIID2, $CIID1);
    $stmt->execute();
    $result = $stmt->get_result();

    $CITypeName2 = getCINameFromTableName($CITableName2);
    $CILookupFieldName2 = getCILookupField($CITypeID2);
    $CILookupFieldNameValue2 = $functions->getFieldValueFromID($CIID2, $CILookupFieldName2, $CITableName2);
    $LogActionText = "Relation created to child $CITypeName2 $CILookupFieldNameValue2";
    createCILogEntry($CIID1, $CITypeID1, $UserID, $LogActionText);
  }

  if ($RelationType === "child") {
    $sql = "INSERT INTO cmdb_ci_relations(CITable1, CITable2, CI1ID, CI2ID) VALUES (?,?,?,?);";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ssii", $CITableName1, $CITableName2, $CIID1, $CIID2);
    $stmt->execute();
    $result = $stmt->get_result();

    $CITypeName1 = getCINameFromTableName($CITableName1);
    $CILookupFieldName1 = getCILookupField($CITypeID1);
    $CILookupFieldNameValue1 = $functions->getFieldValueFromID($CIID1, $CILookupFieldName1, $CITableName1);
    $LogActionText = "Relation created to parent $CITypeName1 $CILookupFieldNameValue1";
    createCILogEntry($CIID2, $CITypeID2, $UserID, $LogActionText);
  }

  $Array[] = array("Result" => "success","CITypeID" => $CITypeID);
  echo json_encode($Array);
}

if (isset($_GET['createCIRelationITSM'])) {
  $UserID = $_SESSION['id'];
  $UserLanguageID = $functions->getUserLanguage($UserID);
  $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);
  if ($UserID == "") {
    $UserID = "1";
  }
  $ITSMTableName = $_GET['ITSMTableName'];
  $ITSMTypeID = $functions->getITSMTypeIDFromTableName($ITSMTableName);
  $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
  $ITSMID = $_GET['ITSMID'];
  $CITable = $_GET['CITable'];
  $CIID = $_GET['CIID'];

  $CITypeName = getCINameFromTableName($CITable);
  $CITypeID = getCITypeIDFromTableName($CITable);
  $ShowField = getCIFieldToWorkAsID($CITypeID);
  $CIValue = $functions->getFieldValueFromID($CIID, $ShowField, $CITable);

  if ($CIID !== "") {
    $sql = "INSERT INTO cmdb_ci_itsm_relations(ITSMTable, CITable, ITSMID, CIID) VALUES (?,?,?,?);";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ssss", $ITSMTableName, $CITable, $ITSMID, $CIID);
    $stmt->execute();
    $result = $stmt->get_result();

    $LogActionText1 = "Relation created to $ITSMTypeName: $ITSMID";
    createCILogEntry($CIID, $CITypeID, $UserID, $LogActionText1);
    $LogActionText2 = "Relation created to $CITypeName $CIValue";
    createITSMLogEntry($ITSMID, $ITSMTypeID, $UserID, $LogActionText2);
  }

  $Array[] = array("Result" => "success", "ITSMTypeID" => $ITSMTypeID, "CITypeID" => $CITypeID, "UserLanguageCode" => $UserLanguageCode);
  echo json_encode($Array);

}

if (isset($_GET['createITSMRelationCI'])) {
  $UserID = $_SESSION['id'];
  $UserLanguageID = $functions->getUserLanguage($UserID);
  $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);
  if ($UserID == "") {
    $UserID = "1";
  }
  $ITSMTableName = $_GET['ITSMTableName'];
  $ITSMTypeID = $functions->getITSMTypeIDFromTableName($ITSMTableName);
  $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
  $ITSMID = $_GET['ITSMID'];
  $CITypeID = $_GET['CITypeID'];
  $CIID = $_GET['CIID'];

  $CITypeName = getCINameFromTypeID($CITypeID);
  $CITableName = getCITableName($CITypeID);
  $ShowField = getCIFieldToWorkAsID($CITypeID);
  $CIValue = $functions->getFieldValueFromID($CIID, $ShowField, $CITableName);

  if ($CIID !== "") {
    $sql = "INSERT INTO cmdb_ci_itsm_relations(ITSMTable, CITable, ITSMID, CIID) VALUES (?,?,?,?);";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ssss", $ITSMTableName, $CITableName, $ITSMID, $CIID);
    $stmt->execute();
    $result = $stmt->get_result();

    $LogActionText1 = "Relation created to $ITSMTypeName: $ITSMID";
    createCILogEntry($CIID, $CITypeID, $UserID, $LogActionText1);
    $LogActionText2 = "Relation created to $CITypeName $CIValue";
    createITSMLogEntry($ITSMID, $ITSMTypeID, $UserID, $LogActionText2);
  }

  $Array[] = array("Result" => "success", "ITSMTypeID" => $ITSMTypeID, "UserLanguageCode" => $UserLanguageCode, "Message" => $functions->translate("Relation created"));
  echo json_encode($Array);
}

if (isset($_GET['createITSMRelationITSM'])) {
  $UserID = $_SESSION['id'];
  $UserLanguageID = $functions->getUserLanguage($UserID);
  $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);
  if ($UserID == "") {
    $UserID = "1";
  }
  $ITSMTableName1 = $_GET['ITSMTableName1'];
  $ITSMID1 = $_GET['ITSMID1'];
  $ITSMTableName2 = $_GET['ITSMTableName2'];
  $ITSMID2 = $_GET['ITSMID2'];

  $ITSMTypeID1 = $functions->getITSMTypeIDFromTableName($ITSMTableName1);
  $ITSMTypeName1 = $functions->getITSMTypeName($ITSMTypeID1);
  $ITSMTypeID2 = $functions->getITSMTypeIDFromTableName($ITSMTableName2);
  $ITSMTypeName2 = $functions->getITSMTypeName($ITSMTypeID2);
  $ShowField1 = $functions->getITSMFieldToWorkAsID($ITSMTypeID1);
  $ShowField2 = $functions->getITSMFieldToWorkAsID($ITSMTypeID2);
  $ITSMValue1 = $functions->getITSMFieldValue($ITSMID1, $ShowField1, $ITSMTableName1);
  $ITSMValue2 = $functions->getITSMFieldValue($ITSMID2, $ShowField2, $ITSMTableName2);

  if ($ITSMID1 !== "") {
    $sql = "INSERT INTO itsm_relations(Table1, Table2, ID1, ID2) VALUES (?,?,?,?);";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("ssss", $ITSMTableName1, $ITSMTableName2, $ITSMID1, $ITSMID2);
    $stmt->execute();
    $result = $stmt->get_result();

    $LogActionText1 = "Relation created to $ITSMTypeName2 $ITSMID2: $ITSMValue2";
    createITSMLogEntry($ITSMID1, $ITSMTypeID1, $UserID, $LogActionText1);
    $LogActionText2 = "Relation created to $ITSMTypeName1 $ITSMID1: $ITSMValue1";
    createITSMLogEntry($ITSMID2, $ITSMTypeID2, $UserID, $LogActionText2);;
  }

  $Array[] = array("Result" => "success","ITSMTypeID" => $ITSMTypeID1, "UserLanguageCode" => $UserLanguageCode);
  echo json_encode($Array);

}

if (isset($_GET['deleteRelCIRel'])) {
  $RelationID = $_GET["RelationID"];
  $UserID = $_SESSION["id"];
  $ParentTableName = getCIRelationTableName($RelationID,"CITable1");
  $ChildTableName = getCIRelationTableName($RelationID, "CITable2");

  $ParentCIID = getCIRelationCIID($RelationID, "CI1ID");
  $ChildCIID = getCIRelationCIID($RelationID, "CI2ID");

  $ParentCITypeID = getCITypeIDFromTableName($ParentTableName);
  $ChildCITypeID = getCITypeIDFromTableName($ChildTableName);

  $ParentCITypeName = getCINameFromTableName($ParentTableName);
  $ChildCITypeName = getCINameFromTableName($ChildTableName);

  $ParentCILookupFieldName = getCILookupField($ParentCITypeID);
  $ChildCILookupFieldName = getCILookupField($ChildCITypeID);

  $ParentCILookupFieldNameValue = $functions->getFieldValueFromID($ParentCIID, $ParentCILookupFieldName, $ParentTableName);
  $ChildCILookupFieldNameValue = $functions->getFieldValueFromID($ChildCIID, $ChildCILookupFieldName, $ChildTableName);

  $ParentLogActionText = "Relation deleted to child $ChildCITypeName $ChildCILookupFieldNameValue";
  createCILogEntry($ParentCIID, $ParentCITypeID, $UserID, $ParentLogActionText);
  $ChildLogActionText = "Relation deleted to parent $ParentCITypeName $ParentCILookupFieldNameValue";
  createCILogEntry($ChildCIID, $ChildCITypeID, $UserID, $ChildLogActionText);

  $sql = "DELETE FROM cmdb_ci_relations WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("i", $RelationID);
  $stmt->execute();

  $Array[] = array("Result" => "success");
  echo json_encode($Array);

}

if (isset($_GET['deleteRelITSMRel'])) {
  $UserID = $_SESSION['id'];
  $UserLanguageID = $functions->getUserLanguage($UserID);
  $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);

  $RelationID = $_GET['RelationID'];
  $ResultArray = getITSMRelationInfo($RelationID);

  foreach($ResultArray as $row){
    $Table1 = $row["Table1"];
    $Table2 = $row["Table2"];
    $ID1 = $row["ID1"];
    $ID2 = $row["ID2"];

    $ITSMTypeID1 = $functions->getITSMTypeIDFromTableName($Table1);
    $ITSMTypeID2 = $functions->getITSMTypeIDFromTableName($Table2);

    $ITSMTypeName1 = getITSMNameFromTableName($Table1);
    $ITSMTypeName2 = getITSMNameFromTableName($Table2);

    $ITSMLookupFieldName1 = $functions->getITSMFieldToWorkAsID($ITSMTypeID1);
    $ITSMLookupFieldName2 = $functions->getITSMFieldToWorkAsID($ITSMTypeID2);

    $FieldValue1 = $functions->getITSMFieldValue($ID1, $ITSMLookupFieldName1, $Table1);
    $FieldValue2 = $functions->getITSMFieldValue($ID2, $ITSMLookupFieldName2, $Table2);

    $LogActionText1 = "Relation deleted to $ITSMTypeName1 $ID1: $FieldValue1";
    $LogActionText2 = "Relation deleted to $ITSMTypeName2 $ID2: $FieldValue2";

    createITSMLogEntry($ID1, $ITSMTypeID1, $UserID, $LogActionText2);
    createITSMLogEntry($ID2, $ITSMTypeID2, $UserID, $LogActionText1);
  }

  deleteITSMRelation($RelationID);

  $Array[] = array("Result" => "success", "ITSMTypeID" => $ITSMTypeID1, "UserLanguageCode" => $UserLanguageCode);
  echo json_encode($Array);

}

if (isset($_GET['deleteCIToElementRelation'])) {
  $UserID = $_SESSION['id'];
	$UserLanguageID = $functions->getUserLanguage($UserID);
	$UserLanguageCode = $functions->getLanguageCode($UserLanguageID);

  $RelationID = $_GET['RelationID'];
  $CITypeID = $_GET['CITypeID'];
  $ITSMTypeID = $_GET['ITSMTypeID'];
  $ITSMID = $_GET['ITSMID'];

  $CIID = getCIIDFromElementRelationID($RelationID);
  $CIName = getCINameFromTypeID($CITypeID);
  $ITSMName = getITSMNameFromITSMType($ITSMTypeID);
  
  $sql = "DELETE
          FROM cmdb_ci_itsm_relations
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("i", $RelationID);
  $stmt->execute();

  $LogActionText = "Relation deleted for $ITSMName: $ITSMID";
  createCILogEntry($CIID, $CITypeID, $UserID, $LogActionText);
  $LogActionText1 = "Relation deleted for $CIName: $CIID";
  createITSMLogEntry($ITSMID, $ITSMTypeID, $UserID, $LogActionText1);
  
  //success
  $Array[] = array("Result" => "success", "ITSMTypeID" => $ITSMTypeID, "ITSMID" =>$ITSMID, "CIID" => $CIID, "UserLanguageCode" => $UserLanguageCode);
  echo json_encode($Array);

}

if (isset($_GET['createDatabaseBackup'])) {
  if (in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100001");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];
  $ITSMTypeID = $_GET['itsmtypeid'];

  $result = createDatabaseBackup($ITSMTypeID, $UserID);

  if ($result == "Completed") {
    //success
    $Array[] = array("Result" => "success");
    echo json_encode($Array);
  } else {
    //No result - error
    $Array[] = array("Result" => "$result");
    echo json_encode($Array);
  }
}

if (isset($_GET['deleteDatabaseBackup'])) {
  if (in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100001");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];
  $restorepointid = $_GET['restorepointid'];

  $result = deleteDatabaseBackup($restorepointid);

  if ($result == "Completed") {
    //success
    $Array[] = array("Result" => "success");
    echo json_encode($Array);
  } else {
    //No result - error
    $Array[] = array("Result" => "Error");
    echo json_encode($Array);
  }
}

if (isset($_GET['restoreDatabaseBackup'])) {
  if (in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100001");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];
  $restorepointid = $_GET['restorepointid'];

  $result = restoreDatabaseBackup($restorepointid);

  if ($result == "Completed") {
    //success
    $Array[] = array("Result" => "success");
    echo json_encode($Array);
  } else {
    //No result - error
    $Array[] = array("Result" => "Error");
    echo json_encode($Array);
  }
}

if (isset($_GET['optimizeDatabase'])) {
  if (in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100001");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];

  $result = optimizeDatabase($UserID);

  if ($result == "Completed") {
    //success
    $Array[] = array("Result" => "success");
    echo json_encode($Array);
  } else {
    //No result - error
    $Array[] = array("Result" => "Error");
    echo json_encode($Array);
  }
}

if (isset($_GET['scanForCVRInWiki'])) {
  if (in_array("100001", $UserGroups)) {
    $UserID = $_SESSION['id'];

    $result = scanForCVR("knowledge_documents", "ContentFullText", "Name", "7");

    if (!empty($result)) {
      // Success - Return the scan result
      $Array[] = array("Result" => "success", "ResultFromQuery" => $result);
      echo json_encode($Array);
    } else {
      // No result - error
      $Array[] = array("Result" => "Error", "ResultFromQuery" => "No CVR numbers found");
      echo json_encode($Array);
    }
  } else {
    $GroupName = getUserGroupName("100001");
    $Array[] = array("Result" => "Error", "ResultFromQuery" => "You need to be a member of $GroupName");
    echo json_encode($Array);
  }
}


if (isset($_GET['resetITSMModules'])) {
  if (in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100001");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];

  $result = resetITSMModules();

  if ($result == "Completed") {
    //success
    $Array[] = array("Result" => "success");
    echo json_encode($Array);
  } else {
    //No result - error
    $Array[] = array("Result" => "Error");
    echo json_encode($Array);
  }
}

if (isset($_GET['createCINew'])) {
  if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100015");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];
  $FieldsArray = $_GET['result'];
  $CITypeID = $_GET['CITypeID'];

  $CITableName = getCITableName($CITypeID);

  $sql = "INSERT INTO $CITableName (";
  foreach ($FieldsArray as $key => $value) {
    $Field = $value['name'];
    $sql .= "$Field,";
  }
  $sql .= ") VALUES (";
  foreach ($FieldsArray as $key => $value) {
    $Name = $value['name'];

    $FieldType = getCIFieldType($CITypeID, $Name);

    switch ($Name) {
      case "Created":
        $Value = date("Y-m-d H:i:s");
        break;
      case "StartDate":
        $Value = date("Y-m-d H:i:s");
        break;
      case "CreatedBy":
        $Value = $UserID;
        break;
      case "Active":
        $Value = 1;
        break;
      case "Removed":
        $Value = 0;
        break;
      default:
        $Value = $value['value'];
    }

    // If $FieldType is 5, change the value
    if ($FieldType == 5) {
      $Value = convertFromDanishTimeFormat($Value);
    }
    
    if ($Value == "") {
      $sql .= "NULL,";
    } else {
      $sql .= "'" . $Value . "',";
    }
  }
  $sql .= ");";
  $sql = str_replace(",)", ")", $sql);

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  $last_id = mysqli_insert_id($conn);
  $LogActionText = "CI Created";
  createCILogEntry($last_id, $CITypeID, $UserID, $LogActionText);

  if (!$result) {
    //No result - error
    $Array[] = array("Result" => mysqli_error($conn));
    echo json_encode($Array);
  } else {
    //success
    $Array[] = array("Result" => "success");
    echo json_encode($Array);
  }
}

if (isset($_GET['createITSMEntry'])) {
  $UserSessionID = $_SESSION['id'];

  $ITSMForm = $_POST['ITSMForm'];
  $FormID = $_POST['FormID'];

  $UserLanguageID = $functions->getUserLanguage($UserSessionID);
  $UserLanguageCode = $functions->getLanguageCode($UserLanguageID);

  $RequestForm = $_POST['RequestForm'];

  $ITSMTableName = $_POST['ITSMTableName'];
  $ElementCreatedDateVal = date("Y-m-d H:i:s");
  $ITSMTypeID = $functions->getITSMTypeIDFromTableName($ITSMTableName);
  $ModuleType = $functions->getModuleType($ITSMTypeID);
  $SLASupported = $functions->getITSMSLASupport($ITSMTypeID);
  $ITSMTypeName1 = $functions->getITSMTypeName($ITSMTypeID);
  $ITSMID2 = $_POST['ITSMCreatedFromITSMID'];
  $ITSMTypeID2 = $_POST['ITSMCreatedFromITSMTypeID'];

  // Call the function and store the result in an array
  $resultArray = createITSMEntry($UserSessionID, $ITSMForm, $FormID, $RequestForm, $ITSMTableName, $ElementCreatedDateVal, $ITSMTypeID, $ModuleType, $SLASupported, $ITSMTypeName1, $ITSMID2, $ITSMTypeID2);

  // Check if the result array is not empty
  if (!empty($resultArray)) {
    // If the array is not empty, use list() to assign values to variables
    list($ITSMID, $ITSMTableName, $ITSMTypeID, $ShortName, $ModuleType) = $resultArray;
    $URL = $functions->getSettingValue(25);
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

  $Array[] = array("LanguageCode" => $UserLanguageCode, "ITSMID" => $ITSMID, "ITSMTableName" => $ITSMTableName, "ITSMTypeID" => $ITSMTypeID, "AllowDelete" => "0", "ShortName" => $ShortName, "ModuleType" => $ModuleType);
  echo json_encode($Array);
}

if (isset($_GET['createCILogBookEntry'])) {
  $UserSessionID = $_SESSION['id'];

  $CITypeID = $_GET['CITypeID'];
  $CIID = $_GET['CIID'];
  $Content = $_GET['Content'];
  $LogBookRelation = $_GET['LogBookRelation'];

  $result = createCILogBookEntry($CIID, $CITypeID, $UserSessionID, $Content, $LogBookRelation);

  if($result = true){
    $Array[] = array("Result" => "success");
    $LogActionText = "Log Book entry created: $Content ($LogBookRelation)";
    createCILogEntry($CIID, $CITypeID, $UserSessionID, $LogActionText);
  }
  else {
    $Array[] = array("Result" => "failed");
  }
  echo json_encode($Array);
}

if (isset($_GET['deleteCILogBookEntry'])) {
  $UserSessionID = $_SESSION['id'];

  $CITypeID = $_GET['CITypeID'];
  $CIID = $_GET['CIID'];
  $LogBookID = $_GET['LogBookID'];

  $result = deleteCILogBookEntry($LogBookID);

  if ($result = true) {
    $Array[] = array("Result" => "success");
    $LogActionText = "Log Book entry deleted: $LogBookID";
    createCILogEntry($CIID, $CITypeID, $UserSessionID, $LogActionText);
  } else {
    $Array[] = array("Result" => "failed");
  }
  echo json_encode($Array);
}

if (isset($_GET['createITSMTemplate'])) {
  $Public = $_GET['public'];
  $FieldValueArray = [];
  $UserID = $_SESSION['id'];
  $ITSMForm = $_GET['ITSMForm'];
  $FormID = $_GET['FormID'];
  if (empty($FormID)) {
    $FormID = 0;
  }
  $RequestForm = $_GET['RequestForm'];
  $ITSMTypeID = $_GET['ITSMTypeID'];
  if ($Public == "") {
    $Public = "0";
  }
  $Description = "";
  $Value = "";
  $FieldValue = "";  

  if (!in_array("100031", $UserGroups) && ($Public == "1")) {
    if (!in_array("100001", $UserGroups) && ($Public == "1")) {
      $GroupName = getUserGroupName("100031");
      $array[] = array("error" => "You need to be member of $GroupName");
      echo json_encode($array);
      return;
    }
  }

  foreach ($ITSMForm as $key => $value) {
    $Field = $value['name'];
    if ($Field == "CreateFormSubject") {
      $Description = $value['value'];
    }
    $Value = $value['value'];
    $FieldValue = $Field . "<;>" . $Value;
    array_push($FieldValueArray, $FieldValue);
  }

  foreach ($RequestForm as $key => $value) {
    $Field = $value['name'];
    if ($Field == "CreateFormSubject") {
      $Description = $value['value'];
    }
    $Value = $value['value'];
    $FieldValue = $Field . "<;>" . $Value;
    array_push($FieldValueArray, $FieldValue);
  }

  $FieldsValues = implode("<#>", $FieldValueArray);
  $sql = "INSERT INTO itsm_templates (Description,Owner,Public,FieldsValues,RelatedModule,RelatedFormID) 
          VALUES (?,?,?,?,?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssssss", $Description, $UserID, $Public, $FieldsValues, $ITSMTypeID, $FormID);
  $stmt->execute();
  $result = $stmt->get_result();

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['loadITSMTemplates'])) {
  $UserID = $_SESSION['id'];
  $ITSMTypeID = $_GET['ITSMTypeID'];
  $TemplateID = $_GET['TemplateID'];

  $sql = "SELECT itsm_templates.FieldsValues, RelatedFormID
          FROM itsm_templates
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $TemplateID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $FieldsValues = $row['FieldsValues'];
    $FormID = $row['RelatedFormID'];
  }

  $FieldsValuesArray = explode("<#>", $FieldsValues);
  $FieldsValuesArrayImploded = implode($FieldsValuesArray);
  $TempArray = explode("<;>", $FieldsValuesArrayImploded);

  foreach ($FieldsValuesArray as $key => $value) {
    $TempArray = explode("<;>", $value);
    $FieldName = $TempArray[0];
    // Strip CreateForm from fieldname in order to allow to get FieldType
    $TempFieldName = str_replace("CreateForm", "", $FieldName);

    if (strpos($TempFieldName, "FormField") !== false) {
      $FieldType = getElementFieldTypeForms($TempFieldName);
    } else {
      $FieldType = getElementFieldType($ITSMTypeID, $TempFieldName);
    }

    $TempArrayFinal[] = array("FieldName" => $TempArray[0], "FieldValue" => $TempArray[1], "FieldType" => $FieldType, "FormID" => $FormID);
  }

  echo json_encode($TempArrayFinal);
}

if (isset($_GET['getITSMSLA'])) {
  $UserID = $_SESSION['id'];
  $ITSMID = $_POST['ITSMID'];
  $ITSMTypeID = $_POST['ITSMTypeID'];

  $sql = "SELECT itsm_slatimelines.SLAViolationDate, itsm_slatimelines.RelatedStatusCodeID, itsm_slatimelines.TimelineUpdatedDate
          FROM itsm_slatimelines
          WHERE RelatedElementID = ? AND RelatedElementTypeID = ?
          ORDER BY SLAViolationDate DESC;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $ITSMID, $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();
  $timelineItems = [];

  while ($row = mysqli_fetch_array($result)) {
    $SLAViolationDate = $row['SLAViolationDate'];
    $TimelineUpdatedDate = $row['TimelineUpdatedDate'];
    $RelatedStatusCodeID = $row['RelatedStatusCodeID'];
    $StatusName = getITSMStatusName($RelatedStatusCodeID, $ITSMTypeID);
    $StatusName = _($StatusName);

    $timelineItems[] = [
      'status' => $StatusName,
      'date' => $SLAViolationDate,
      'updatedDate' => $TimelineUpdatedDate,
    ];
  }

  if (!empty($timelineItems)) {
    $timelineHTML = '';
    $title = "";

    foreach ($timelineItems as $item) {
      $violatedStatus = "";
      $status = htmlspecialchars($item['status']);
      $date = htmlspecialchars($item['date']);
      $updatedDate = htmlspecialchars($item['updatedDate']);
      $updatedDateDanish = convertToDanishTimeFormat($updatedDate);

      // Update the code snippet
      $dotIcon = "<i class=\"fa-solid fa-circle dot-icon dot-icon-green\" title=\"" . _("Deadline not surpassed") . "\"></i>";

      // Check if the deadline has surpassed the current date
      if (strtotime($date) < time()) {
        $dotIcon = "<i class=\"fa-solid fa-circle dot-icon dot-icon-red\" title=\"" . _("Deadline has passed") . "\"></i>";
      }
      // Check if the deadline is within 5 hours
      elseif (strtotime($date) - time() <= 5 * 3600) {
        $dotIcon = "<i class=\"fa-solid fa-circle dot-icon dot-icon-orange\" title=\"" . _("Deadline is within 5 hours") . "\"></i>";
      }

      // Check if the updated date is after the original date
      if (!empty($updatedDate) && strtotime($updatedDate) > strtotime($date)) {
        $violatedStatus = "&nbsp;<span class=\"badge bg-gradient-danger\">" . _("violated") . "</span>";
        $dotIcon = "";
        $title = " title=\"" . _("completed: $updatedDateDanish") . "\"";
      } elseif (!empty($updatedDate) && strtotime($updatedDate) <= strtotime($date)) {
        $violatedStatus = "";
        $dotIcon = "";
        $title = " title=\"" . _("completed: $updatedDateDanish") . "\"";
      }

      $timelineHTML .= "<div class=\"container\"$title>
                          <div class=\"content\">$dotIcon
                            <small><b>$status</b></small>:&nbsp;
                            <small>" . convertToDanishTimeFormat($date) . "</small>
                            $violatedStatus
                          </div>
                        </div>";
    }

    $timelineHTML .= '';

    $ArrayFinal[] = ['SLA' => $timelineHTML];
  } else {
    $ArrayFinal[] = ['SLA' => ''];
  }

  echo json_encode($ArrayFinal);
}

if (isset($_GET['getITSMSolution'])) {
  $UserID = $_SESSION['id'];
  $ITSMID = $_POST['ITSMID'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $Type = $_POST['Type'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

  $sql = "SELECT $ITSMTableName.Solution
          FROM $ITSMTableName
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $Solution = $row['Solution'];
    if($Type == "modal"){

      $Btn = '<div class="input-group input-group-static mb-4"><label for="ITSMSolution" title="' . $functions->translate("Solution to the problem") . '">';
      $Btn .= $functions->translate("Solution") . '&ensp;<a href="javascript:toggleCKEditor(\'ITSMSolution\',\'250px\');"><i class="fa-solid fa-pen fa-sm" title="Double click on field to edit"></i></a>';
      $Btn .= '</label></div>';
      $Btn .= '<div style="height: 150px; word-wrap: break-word; overflow-y: auto; overflow-x: auto;" class="resizable_textarea form-control" id="ITSMSolution" name="ITSMSolution" title="Double click to edit" rows="5" autocomplete="off" ondblclick="toggleCKEditor(\'ITSMSolution\',\'250px\');">';
      $Btn .= '</div>';
    }
    else {
      $Btn = '<div class="input-group input-group-static mb-4"><label for="ITSMQWSolution" title="' . $functions->translate("Solution to the problem") . '">';
      $Btn .= $functions->translate("Solution") . '&ensp;<a href="javascript:toggleCKEditor(\'ITSMQWSolution\',\'250px\');"><i class="fa-solid fa-pen fa-sm" title="Double click on field to edit"></i></a>';
      $Btn .= '</label></div>';
      $Btn .= '<div style="height: 150px; word-wrap: break-word; overflow-y: auto; overflow-x: auto;" class="resizable_textarea form-control" id="ITSMQWSolution" name="ITSMQWSolution" title="Double click to edit" rows="5" autocomplete="off" ondblclick="toggleCKEditor(\'ITSMQWSolution\',\'250px\');">';
      $Btn .= '</div>';
    }
  }

  if ($Solution !== "") {
    $Array[] = array("Btn" => $Btn, "Solution" => $Solution);
    echo json_encode($Array);
  } else {
    $Array[] = array("Btn" => $Btn,"Solution" => "");
    echo json_encode($Array);
  }
}

if (isset($_GET['getTemplateFormID'])) {
  $UserID = $_SESSION['id'];
  $TemplateID = $_GET['TemplateID'];

  $sql = "SELECT RelatedFormID
          FROM itsm_templates
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $TemplateID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $FormID = $row['RelatedFormID'];
  }

  $ArrayFinal[] = array("FormID" => $FormID);

  echo json_encode($ArrayFinal);
}

if (isset($_GET['deleteTSMTemplate'])) {
  $group_array = $_SESSION['memberofgroups'];
  $UserID = $_SESSION['id'];
  $TemplateID = $_GET['TemplateID'];
  $ITSMTypeID = $_GET['ITSMTypeID'];
  $DeleteAllowed = true;
  $PublicStatus = getTemplatePublicStatus($TemplateID);

  if ($PublicStatus == "1") {
    if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
      $DeleteAllowed = true;
    } else {
      $DeleteAllowed = false;
    }
  }

  if ($DeleteAllowed == true) {
    $sql = "DELETE FROM itsm_templates WHERE ID = ?";

    $stmt = mysqli_prepare($conn, $sql);
    $stmt->bind_param("s", $TemplateID);
    $stmt->execute();
    $result = $stmt->get_result();
    $Array[] = array("Result" => "success");
  } else {
    $Array[] = array("Result" => "fail");
  }

  echo json_encode($Array);
}

if (isset($_GET['updateCIValues'])) {
  if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100015");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];
  $FieldName = $_GET['FieldName'];
  $FieldValue = $_GET['FieldValue'];
  $CITypeID = $_GET['CITypeID'];

  $sql = "UPDATE cmdb_cis SET $FieldName = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $FieldValue, $CITypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (!$result) {
    //No result - error
    $Array[] = array("Result" => mysqli_error($conn));
    echo json_encode($Array);
  } else {
    //success
    $Array[] = array("Result" => "success");
    echo json_encode($Array);
  }
}

if (isset($_GET['updateITSMValues'])) {
  $UserID = $_SESSION['id'];
  $FieldName = $_GET['FieldName'];
  $FieldValue = $_GET['FieldValue'];
  $ITSMTypeID = $_GET['ITSMTypeID'];

  $sql = "UPDATE itsm_modules SET $FieldName = ? WHERE ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $FieldValue, $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  if (!$result) {
    //No result - error
    $Array[] = array("Result" => mysqli_error($conn));
    echo json_encode($Array);
  } else {
    //success
    $Array[] = array("Result" => "success");
    echo json_encode($Array);
  }
}

if (isset($_GET['updateCI'])) {
  if (!in_array("100015", $UserGroups) && !in_array("100001", $UserGroups)) {
    $GroupName = getUserGroupName("100015");
    echo json_encode([["error" => "You need to be member of $GroupName"]]);
    return;
  }

  $UserID = $_SESSION['id'];
  $FieldsArray = $_POST['Result'];
  $CITypeID = $_POST['CITypeID'];
  $CITableName = getCITableName($CITypeID);
  $CIID = $_POST['CIID'];

  $LogArray = [];
  $sql = "UPDATE $CITableName SET ";
  $sql_debug = "UPDATE $CITableName SET ";
  $types = "";
  $values = [];

  foreach ($FieldsArray as $key => $value) {
    $Field = $value['name'];
    $Value = $value['value'];

    $PreValue = getCIFieldPreValue($CIID, $CITableName, $Field);
    $FieldLabel = getCIFieldLabelFromFieldName($CITypeID, $Field);
    $FieldType = getCIFieldType($CITypeID, $Field);

    if ($Value == $PreValue) {
      continue;
    }

    if ($FieldType == "5" && $Value !== "") {
      $Value = convertFromDanishTimeFormat($Value);
    }

    // Check if the field is a date field and the value is empty, then set it to NULL
    if ($FieldType == "5" && $Value === "") {
      $Value = NULL;
    }

    $PreValue = $PreValue === NULL ? "" : $PreValue;

    $PreValueForLog = normalizeValue($PreValue);
    $ValueForLog = normalizeValue($Value);

    // If value is not pre value then add to log array
    if ($PreValueForLog !== $ValueForLog) {
      array_push($LogArray, "$FieldLabel: changed from $PreValue to $Value");
    }

    $sql .= "$Field = ?,";
    $sql_debug .= "$Field = '" . ($Value !== NULL ? $Value : "NULL") . "',";

    $types .= $Value !== NULL ? "s" : "s"; // Modify as needed if your database schema requires different types
    $values[] = $Value;
  }

  $sql = rtrim($sql, ',') . " WHERE ID = ?";
  $sql_debug = rtrim($sql_debug, ',') . " WHERE ID = $CIID";

  $types .= "i"; // Assuming ID is an integer
  $values[] = $CIID;

  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, $types, ...$values);

  if (mysqli_stmt_execute($stmt)) {
    $UpdateValues = implode(" ", $LogArray);
    if ($UpdateValues != "") {
      createCILogEntry($CIID, $CITypeID, $UserID, $UpdateValues);
    }
    echo json_encode([["Result" => $functions->translate("success"),"Message" => $functions->translate("Updated")]]);
  } else {
    echo json_encode([["Result" => $functions->translate("Error"), "Message" => mysqli_error($conn)]]);
  }

  mysqli_stmt_close($stmt);
}

if (isset($_GET['updateITSM'])) {
  $UserID = $_SESSION['id'];
  $ITSMForm = $_POST['ITSMForm'];
  $RequestForm = $_POST['RequestForm'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMModuleType = $functions->getITSMModuleType($ITSMTypeID);
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $ITSMID = $_POST['ITSMID'];
  
  $LogArray = [];
  $RequestEmptyForm = 0;
  $SomethingChanged = false;

  // First we update Request form if it is there
  if (!empty($RequestForm)) {
    $formID = getFormsID($ITSMTableName, $ITSMID);
    $formsTableName = getFormsTableName($formID);
    $formsEntryID = getFormTableEntryID($formsTableName, $ITSMID);
    $sql = "UPDATE $formsTableName SET ";
    $types = '';
    $values = [];

    foreach ($RequestForm as $key => $value) {
      $Field = $value['name'];
      $Value = $value['value'];
      $PreValue = getFormsFieldPreValue($ITSMID, $formsTableName, $Field);

      $FieldLabel = getFormFieldLabelFromFieldName($formID, $Field);

      $Value = $Value === NULL ? "" : $Value;
      $PreValue = $PreValue === NULL ? "" : $PreValue;

      $FieldType = getFormFieldType($formID, $Field);

      if ($FieldType == "5" && !empty($Value)) {
        $Value = convertFromDanishTimeFormat($Value);
      }

      if ($PreValue !== $Value) {
        $SomethingChanged = true;
        array_push($LogArray, "$FieldLabel: changed from $PreValue to $Value");
      }

      if ($Value == "") {
        $sql .= "$Field = NULL, ";
      } else {
        $sql .= "$Field = ?, ";
        $types .= "s";
        $values[] = $Value;
      }
    }

    $sql = rtrim($sql, ', ') . " WHERE ID = ?";
    $types .= "i"; // Assuming ID is an integer
    $values[] = $formsEntryID;

    // Prepare and execute the statement
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$values);

    if (mysqli_stmt_execute($stmt)) {
      // Handle success, e.g., logging changes or returning a success message
    } else {
      // Handle error, e.g., logging the error or returning an error message
    }

    mysqli_stmt_close($stmt);
  } else {
    $RequestEmptyForm = 1;
  }
  
  //  Next we update ITSM form
  $sql = "UPDATE $ITSMTableName SET ";
  $types = '';
  $values = [];

  $DontLogPasswords = 0;
  //error_reporting(E_ALL);
  //ini_set('display_errors', 1);

  try {
    foreach ($ITSMForm as $key => $value) {
      $Field = $value['name'];
      $Value = $value['value'];

      $PreValue = getITSMFieldPreValue($ITSMID, $ITSMTableName, $Field);
      $FieldLabel = getITSMFieldLabelFromFieldName($ITSMTypeID, $Field);
      $FieldType = $functions->getITSMFieldType($ITSMTypeID, $Field);
      
      if($FieldType == "9"){
        $DontLogPasswords = 1;
      }

      // If date field then convert date from danish format to mysql format
      if ($FieldType == "5" && !empty($Value)) {
        //$Value = convertFromDanishTimeFormat($Value);
        $Value = convertFromDanishTimeFormat($Value);
        $PreValue = $PreValue;
      }

      // If date field then convert date from danish format to mysql format
      if ($FieldType == "2" && !empty($Value)) {
        //$Value = mysqli_real_escape_string($conn, $Value);
      }

      $PreValueForLog = normalizeValue($PreValue);
      $ValueForLog = normalizeValue($Value);

      // If value is not pre value then add to log array - except for password fields
      if ($PreValueForLog !== $ValueForLog && $DontLogPasswords == 0) {
        $FieldLabelName = $functions->translate($FieldLabel);
        if($FieldType == "2"){
          array_push($LogArray, "$FieldLabelName: changed");
        } else {
          array_push($LogArray, "$FieldLabelName: changed from $PreValue to $Value");
        }
      }

      // SQL Statement builder - use original values
      if ($Value === "" || $Value === NULL) {
        if ($PreValue !== NULL && $PreValue !== "") {
          $sql .= "$Field = NULL, ";
        }
      } elseif ($PreValue !== $Value) {
        $sql .= "$Field = ?, ";
        $types .= "s";
        $values[] = $Value;
      }

      // Exclude email for these modules
      $ExclueModuleTypes = array("2","4");
      // Check fields and do actions accordingly
      
      if($PreValue != $Value){
        switch ($Field) {
          case "Customer":
            if ($Value != "") {
              $MailTemplateID = "3";
              $Description = "";
              // Set CompanyID on newly created ITSM element
              $CompanyID = getUserRelatedCompanyID($Value);
              updateITSMFieldValue($ITSMID, $CompanyID, "RelatedCompanyID", $ITSMTypeID);
              // Set Company SLA
              $SLA = getRelatedSLAID($CompanyID);
              updateITSMFieldValue(
                $ITSMID,
                $SLA,
                "SLA",
                $ITSMTypeID
              );

              if (!in_array($ITSMModuleType, $ExclueModuleTypes)) {
                sendITSMNotificationMailToUsers($MailTemplateID, $ITSMTypeID, $ITSMID, $Description, $Field, $PreValue, $Value);
              }
            }
            break;
          case "Responsible":
            if ($Value != "") {
              $MailTemplateID = "4";
              $Description = "";
              if (!in_array($ITSMModuleType, $ExclueModuleTypes)) {
                if($PreValue != $Value){
                  sendITSMNotificationMailToUsers($MailTemplateID, $ITSMTypeID, $ITSMID, $Description, $Field, $PreValue, $Value);
                }
              }
            }
            break;
          case "BusinessService":
            if ($Value != "") {
              $SLA = getSLAFromBS($Value);
              $BSID = $Value;
              updateITSMFieldValue(
                $ITSMID,
                $SLA,
                "SLA",
                $ITSMTypeID
              );
            } else {
              $CompanyID = $functions->getITSMFieldValue($ITSMID, "RelatedCompanyID", $ITSMTableName);
              $SLA = getRelatedSLAID($CompanyID);
              updateITSMFieldValue(
                $ITSMID,
                $SLA,
                "SLA",
                $ITSMTypeID
              );
            }
            break;
          case "Priority":
            $NewPriorityID = $Value;
            $NewPriorityName = getITSMPriorityName($NewPriorityID);
            $PrePriorityID = $PreValue;
            $PrePriorityName = getITSMPriorityName($PrePriorityID);
            $MailTemplateID = "5";
            $Description = "";

            if (!in_array($ITSMModuleType, $ExclueModuleTypes)) {
              sendITSMNotificationMailToUsers($MailTemplateID, $ITSMTypeID, $ITSMID, $Description, $Field, $PrePriorityName, $NewPriorityName);
            }

            break;
          case "Status":
            $NewStatusID = $Value;
            $NewStatusName = getITSMStatusName($NewStatusID, $ITSMTypeID);
            $PreStatusID = $PreValue;
            $PreStatusName = getITSMStatusName($PreStatusID, $ITSMTypeID);

            $Description = "";

            if (in_array($NewStatusID, $functions->getITSMClosedStatus($ITSMTypeID))) {
              $MailTemplateID = "2";
              $Description = "";
              if (!in_array($ITSMModuleType, $ExclueModuleTypes)) {
                sendITSMNotificationMailToUsers($MailTemplateID, $ITSMTypeID, $ITSMID, $Description, $Field, $PreStatusName, $NewStatusName);
              }
              updateTimelineUpdatedDate($ITSMID, $ITSMTypeID, $NewStatusID);
              updateMissingStatusCodeTimelines($ITSMTypeID, $ITSMID);
              closeAllTasksAssociatedWithITSM($ITSMTypeID, $ITSMID);
            } else {
              $MailTemplateID = "7";
              $Description = "";
              if (!in_array($ITSMModuleType, $ExclueModuleTypes)) {
                sendITSMNotificationMailToUsers($MailTemplateID, $ITSMTypeID, $ITSMID, $Description, $Field, $PreStatusName, $NewStatusName);
              }
            }
            
            break;
          case "SLA":
            $NewSLAID = $Value;
            $NewSLAName = getITSMSLAName($NewSLAID);
            $PreSLAID = $PreValue;
            $PreSLAName = getITSMSLAName($PreSLAID);
            break;
          default:
            break;
        }
      }
    }
  } catch (Exception $e) {
    // Catch any exceptions thrown
    $functions->errorlog("Exception caught: " . $e->getMessage(),"updateITSM");
  }

  $sql = rtrim($sql, ', ') . " WHERE ID = ?";

  $ControlSql = "UPDATE $ITSMTableName SET WHERE ID = ?";

  if($sql == $ControlSql){
    //success
    if($SomethingChanged == true){
      $Array[] = array("Result" => "formupdated");
      $UpdateValues = implode(" ", $LogArray);
      if ($UpdateValues != "") {
        createITSMLogEntry($ITSMID, $ITSMTypeID, $UserID, $UpdateValues);
      }
    } else {
      $Array[] = array("Result" => "nothing");
    }
    echo json_encode($Array);
    exit;
  } else {
    $types .= "i"; // Assuming ID is an integer
    $values[] = $ITSMID;

    // Prepare and execute the statement
    $stmt2 = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt2, $types, ...$values);

    if (mysqli_stmt_execute($stmt2)) {
      if ($ITSMModuleType == "3") {
        if ($NewStatusID !== $PreStatusID && $NewStatusID == "4") {
          $Version = incrementDocumentVersion($ITSMTypeID, $ITSMID);
          CreateDocArchive($ITSMID, $ITSMTypeID, $Version, $UserID);
        }
      }
    } else {
      // Handle error, e.g., logging the error or returning an error message
    }

    mysqli_stmt_close($stmt2);
  }

  $CompanyID = $functions->getITSMFieldValue($ITSMID, "RelatedCompanyID", $ITSMTableName);
  $ElementCreatedDateVal = $functions->getITSMFieldValue($ITSMID, "Created", $ITSMTableName);

  if ($PrePriorityID != $NewPriorityID) {
    //Get SLA reactiontimes for the SLA ID according to the priority selected
    $ReactionTimes[] = array();
    $ReactionTimes = getSLAStatusCores($ITSMTypeID, $NewSLAID, $NewPriorityID);
    foreach ($ReactionTimes as $value) {
      $Status = $value["Status"];
      $Minutes = $value["Minutes"];
      $DateViolated = getDateTimeViolated($ElementCreatedDateVal, $Minutes);
      updateTimelineSLAViolationDate($ITSMID, $ITSMTypeID, $Status, $DateViolated);
    }
  }

  if ($PreSLAID != $NewSLAID) {
    $ReactionTimes[] = array();
    $ReactionTimes = getSLAStatusCores($ITSMTypeID, $NewSLAID, $NewPriorityID);

    foreach ($ReactionTimes as $value) {
      $Status = $value["Status"];
      $Minutes = $value["Minutes"];
      $DateViolated = getDateTimeViolated($ElementCreatedDateVal, $Minutes);
      updateTimelineSLAViolationDate($ITSMID, $ITSMTypeID, $Status, $DateViolated);
    }
  }

  if ($NewStatusID != "") {
    updateTimelineUpdatedDate($ITSMID, $ITSMTypeID, $NewStatusID);
  }

  $UpdateValues = implode(" ", $LogArray);
  if ($UpdateValues != "") {
     //Update updated date time stamp
    updateITSMUpdatedDate($ITSMTypeID,$ITSMID);
    createITSMLogEntry($ITSMID, $ITSMTypeID, $UserID, $UpdateValues);
    // Lets log the activity
    $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
    $Headline = $functions->translate("Updated") . " " . strtolower($ITSMTypeName) . " " . $ITSMID;
    $ActivityText = "<b>" . $functions->translate("Updates") . "<br><br>" . $UpdateValues;
    logActivity($ITSMID, $ITSMTypeID, $Headline, $ActivityText, "javascript:javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');");
  }
  
  //success
  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['createCI'])) {
  try {
    if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
      // Proceed with operation if user is in required group
    } else {
      $GroupName = getUserGroupName("100015");
      $array[] = array("error" => "You need to be a member of $GroupName");
      echo json_encode($array);
      return;
    }

    $UserID = $_SESSION['id'];
    $GenerateRandomName = $functions->generateRandomString(15);
    $TableName = "cmdb_ci_$GenerateRandomName";
    $CIName = $_GET['CIName'];
    $CIDescription = $_GET['CIDescription'];

    // Prepare and execute the insert statement
    $sql = "INSERT INTO cmdb_cis (Name, TableName, Description, GroupID, CreatedBy, LastEditedBy) VALUES (?,?,?,'100014',?,?);";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
      throw new Exception("Failed to prepare SQL statement: " . mysqli_error($conn));
    }

    $stmt->bind_param("sssii", $CIName, $TableName, $CIDescription, $UserID, $UserID);
    if (!$stmt->execute()) {
      throw new Exception("Failed to execute SQL statement: " . $stmt->error);
    }

    $last_id = mysqli_insert_id($conn);
    $CIComment = "ciid=$last_id";

    // Create table query
    $sql2 = "CREATE TABLE IF NOT EXISTS $TableName (
            ID INT AUTO_INCREMENT,
            PRIMARY KEY (ID));";
    if (!mysqli_query($conn, $sql2)) {
      throw new Exception("Failed to create table $TableName: " . mysqli_error($conn));
    }

    // Alter table comment
    $sql3 = "ALTER TABLE $TableName COMMENT = '$CIComment';";
    if (!mysqli_query($conn, $sql3)) {
      throw new Exception("Failed to alter table comment: " . mysqli_error($conn));
    }

    // Get default fields and add them
    $DefaultFieldsArray = getDefaultCMDBCIFields();
    foreach ($DefaultFieldsArray as $key => $value) {
      $FieldName = $value['FieldName'];
      $DBFieldType = $value['DBFieldType'];
      $FieldType = $value['FieldType'];
      $FieldLabel = $value['FieldLabel'];
      $RelationShowField = $value['RelationShowField'];
      $FieldOrder = $value['FieldOrder'];
      $FieldDefaultValue = $value['FieldDefaultValue'];
      $DefaultField = $value['DefaultField'];
      $FieldWidth = $value['FieldWidth'];
      $LookupTable = $value['LookupTable'];
      $LookupField = $value['LookupField'];
      $LookupFieldResultTable = $value['LookupFieldResultTable'];
      $LookupFieldResultView = $value['LookupFieldResultView'];
      $SelectFieldOptions = $value['SelectFieldOptions'];
      $ResultFields = $value['ResultFields'];
      $UserFullName = $value['UserFullName'];
      $HideForms = $value['HideForms'];
      $HideTables = $value['HideTables'];
      $Required = $value['Required'];
      $LockedCreate = $value['LockedCreate'];
      $LockedView = $value['LockedView'];
      $Addon = $value['Addon'];
      $AddEmpty = $value['AddEmpty'];
      $FullHeight = $value['FullHeight'];
      $RightColumn = $value['RightColumn'];
      $LabelType = $value['LabelType'];

      if ($SelectFieldOptions == "false") {
        $SelectFieldOptions = "";
      }

      if ($FieldName == "Name") {
        $DefaultField = "0";
      }

      alterTableAddColumn($TableName, $FieldName, $DBFieldType);

      insertCMDBFieldToFieldListTable(
        $last_id,
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
      );
    }

    $Array[] = array("Result" => "success");
    echo json_encode($Array);

  } catch (Exception $e) {
    $errormessage = $e->getMessage();
    $functions->errorlog($errormessage, "getdata createCI");

    $errorArray[] = array("error" => $errormessage);
    echo json_encode($errorArray);
  }
}

if (isset($_GET['createITSM'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];
  $GenerateRandomName = $functions->generateRandomString(15);
  $TableName = "itsm_$GenerateRandomName";
  $ITSMName = $_POST['ITSMName'];
  $ITSMDescription = $_POST['ITSMDescription'];

  $sql = "INSERT INTO itsm_modules (Name, ShortElementName, TableName, Description, CreatedBy, LastEditedBy, TypeIcon, DoneStatus, Type, SLA, Active, RoleID, MenuPage) VALUES (?,?,?,?,?,?,'fa fa-retweet','5','1','0','1','1','itsm_tableview.php?id=')";

  try {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
      throw new Exception('Prepare failed: ' . htmlspecialchars(mysqli_error($conn)));
    }

    if (!$stmt->bind_param("ssssii", $ITSMName, $ITSMName, $TableName, $ITSMDescription, $UserID, $UserID)) {
      throw new Exception('Bind_param failed: ' . htmlspecialchars($stmt->error));
    }

    if (!$stmt->execute()) {
      throw new Exception('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    $result = $stmt->get_result();
    if ($stmt->error) {
      throw new Exception('Get_result failed: ' . htmlspecialchars($stmt->error));
    }
  } catch (Exception $e) {
    $functions->errorlog($e->getMessage(),"createITSM");
  }
  
  $last_id = mysqli_insert_id($conn);

  $sql2 = "CREATE TABLE IF NOT EXISTS $TableName (
          ID INT AUTO_INCREMENT,
          PRIMARY KEY (ID));";
  $result2 = mysqli_query($conn, $sql2);

  $sql3 = "ALTER TABLE $TableName COMMENT = '$Comment';";
  $result3 = mysqli_query($conn, $sql3);

  $DefaultFieldsArray = getDefaultITSMFields();

  foreach ($DefaultFieldsArray as $key => $value) {
    $FieldName = $value['FieldName'];
    $DBFieldType = $value['DBFieldType'];
    $FieldType = $value['FieldType'];
    $FieldLabel = $value['FieldLabel'];
    $RelationShowField = $value['RelationShowField'];
    $FieldOrder = $value['FieldOrder'];
    $FieldTitle = $value['FieldTitle'];
    $FieldDefaultValue = $value['FieldDefaultValue'];
    $FieldWidth = $value['FieldWidth'];
    $DefaultField = "1";
    $LookupTable = $value['LookupTable'];
    $LookupField = $value['LookupField'];
    $LookupFieldResultTable = $value['LookupFieldResultTable'];
    $LookupFieldResultView = $value['LookupFieldResultView'];
    $SelectFieldOptions = $value['SelectFieldOptions'];
    $ResultFields = $value['ResultFields'];
    $UserFullName = $value['UserFullName'];
    $HideForms = $value['HideForms'];
    $HideTables = $value['HideTables'];
    $Required = $value['Required'];
    $LockedCreate = $value['LockedCreate'];
    $LockedView = $value['LockedView'];
    $Addon = $value['Addon'];
    $AddEmpty = $value['AddEmpty'];
    $FullHeight = $value['FullHeight'];
    $RightColumn = $value['RightColumn'];
    $LabelType = $value['LabelType'];
    $ImportSourceField = $value['ImportSourceField'];
    $SyncSourceField = $value['SyncSourceField'];
    $RelationsLookup = $value['RelationsLookup'];
    $Indexed = $value['Indexed'];

    alterTableAddColumn($TableName, $FieldName, $DBFieldType);
    insertITSMFieldToFieldListTable(
      $last_id,
      $FieldName,
      $FieldLabel,
      $RelationShowField,
      $FieldType,
      $FieldOrder,
      $FieldTitle,
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
      $ImportSourceField,
      $SyncSourceField,
      $RelationsLookup,
      $Indexed);
  }
}

if (isset($_GET['createFormsField'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100029");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];
  $FormID = $_POST['FormID'];
  $FieldOrder = $_POST['FieldOrder'];
  $TableName = getFormTableName($FormID);
  $FieldSpecs = $_POST['FieldSpecs'];
  $FieldID = generateRandomNumber(8);
  $FieldName = "FormField" . $FieldID;
  array_push($FieldSpecs, ["name" => "RelatedFormID", "value" => "$FormID"]);
  array_push($FieldSpecs, ["name" => "FieldName", "value" => "$FieldName"]);

  foreach ($FieldSpecs as $fieldSpec) {
    if ($fieldSpec['name']  === "ResultFields") {
      $ResultFieldsValue = $fieldSpec['value'];
      if ($ResultFieldsValue) {
        array_push($FieldSpecs, ["name" => "LookupFieldResultTable", "value" => "$ResultFieldsValue"]);
        array_push($FieldSpecs, ["name" => "LookupFieldResultView", "value" => "$ResultFieldsValue"]);
        array_push($FieldSpecs, ["name" => "LookUpField", "value" => "ID"]);
      }
    }
  }

  if ($FieldOrder == "") {
    $FieldOrder = 1;
  }

  incrementExistingFormFieldOrder($FormID, $FieldOrder);

  $sql = "INSERT INTO forms_fieldslist (";
  foreach ($FieldSpecs as $key => $value) {
    $Field = $value['name'];
    if ($Field === "SelectFieldOption" || $Field === "LookupTable2" || $Field === "LookupField2") {
      continue;
    }
    $sql .= "$Field,";
  }

  $sql = rtrim($sql, ',');
  $sql .= ") VALUES (";

  $requiredFields = array("Indexed", "HideTables", "HideForms", "LockedCreate", "LockedView", "Required", "AddEmpty", "FullHeight", "RelationShowField", "UserFullName", "RightColumn", "LabelType", "DefaultField", "Hidden");

  foreach ($FieldSpecs as $key => $value) {
    $Name = $value['name'];
    $Value = $value['value'];

    if ($Name === "SelectFieldOption" || $Name === "LookupTable2" || $Name === "LookupField2") {
      continue;
    }

    switch ($Name) {
      case "FieldTitle":
        $Value = strip_tags($Value);
        break;
      default:
        $Value = mysqli_real_escape_string($conn, strip_tags($Value));
    }

    // Handle special fields (Indexed, HideTables, HideForms, SelectFieldOptions)
    if (in_array($Name, $requiredFields)) {
      if ($Value === "on") {
        $Value = 1;
        if ($Name === "Indexed") {
          $Indexed = 1;
        }
      } elseif ($Value === 0) {
        if ($Name === "Indexed") {
          $Indexed = 0;
        }
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

  $sql3 = "ALTER TABLE $TableName ADD $FieldName TEXT;";
  $result3 = mysqli_query($conn, $sql3);

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['createCIField'])) {
  if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100015");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $UserID = $_SESSION['id'];
  $CITypeID = $_POST['CITypeID'];
  $FieldOrder = $_POST['FieldOrder'];
  $TableName = getCITableName($CITypeID);
  $FieldSpecs = $_POST['FieldSpecs'];
  $FieldID = generateRandomNumber(8);
  $FieldName = "CIField" . $FieldID;
  array_push($FieldSpecs, ["name" => "RelatedCITypeID", "value" => "$CITypeID"]);
  array_push($FieldSpecs, ["name" => "FieldName", "value" => "$FieldName"]);

  foreach ($FieldSpecs as $fieldSpec) {
    if ($fieldSpec['name']  === "ResultFields") {
      $ResultFieldsValue = $fieldSpec['value'];
      if ($ResultFieldsValue) {
        array_push($FieldSpecs, ["name" => "LookupFieldResultTable", "value" => "$ResultFieldsValue"]);
        array_push($FieldSpecs, ["name" => "LookupFieldResultView", "value" => "$ResultFieldsValue"]);
        array_push($FieldSpecs, ["name" => "LookUpField", "value" => "ID"]);
      }
    }
  }
  
  if ($FieldOrder == "") {
    $FieldOrder = 1;
  }

  incrementExistingCIFieldOrder($CITypeID, $FieldOrder);

  $sql = "INSERT INTO cmdb_ci_fieldslist (";
  foreach ($FieldSpecs as $key => $value) {
    $Field = $value['name'];
    if($Field === "SelectFieldOption" || $Field === "LookupTable2" || $Field ==="LookupField2"){
      continue;
    }
    $sql .= "$Field,";
  }
  $sql .= ") VALUES (";

  $requiredFields = array("Indexed", "HideTables", "HideForms", "LockedCreate", "LockedView", "Required", "AddEmpty", "FullHeight", "RelationShowField", "UserFullName", "RightColumn", "LabelType", "DefaultField", "Hidden");

  foreach ($FieldSpecs as $key => $value) {

    $Name = $value['name'];
    $Value = $value['value'];

    if($Name === "SelectFieldOption" || $Name === "LookupTable2" || $Name ==="LookupField2"){
      continue;
    }

    switch ($Name) {
      case "FieldTitle":
        $Value = strip_tags($Value);
        break;
      default:
        $Value = mysqli_real_escape_string($conn, strip_tags($Value));
    }

    // Handle special fields (Indexed, HideTables, HideForms, SelectFieldOptions)
    if (in_array($Name, $requiredFields)) {
      if ($Value === "on") {
        $Value = 1;
        if ($Name === "Indexed") {
          $Indexed = 1;
        }
      } elseif ($Value === 0) {
        if ($Name === "Indexed") {
          $Indexed = 0;
        }
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

  $sql3 = "ALTER TABLE $TableName ADD $FieldName TEXT;";
  $result3 = mysqli_query($conn, $sql3);

  if($Indexed === 1){
    alterTableAddFullTextToColumn($TableName, $FieldName);
  }

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['createITSMField'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $UserID = $_SESSION['id'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $FieldOrder = $_POST['FieldOrder'];
  $FieldSpecs = $_POST['FieldSpecs'];

  $TableName = $functions->getITSMTableName($ITSMTypeID);
  $FieldID = generateRandomNumber(8);
  $FieldName = "ITSMField" . $FieldID;
  array_push($FieldSpecs, ["name" => "RelatedTypeID", "value" => "$ITSMTypeID"]);
  array_push($FieldSpecs, ["name" => "FieldName", "value" => "$FieldName"]);

  foreach ($FieldSpecs as $fieldSpec) {
    if ($fieldSpec['name']  === "ResultFields") {
      $ResultFieldsValue = $fieldSpec['value'];
      if ($ResultFieldsValue) {
        array_push($FieldSpecs, ["name" => "LookupFieldResultTable", "value" => "$ResultFieldsValue"]);
        array_push($FieldSpecs, ["name" => "LookupFieldResultView", "value" => "$ResultFieldsValue"]);
        array_push($FieldSpecs, ["name" => "LookUpField", "value" => "ID"]);
      }
    }
  }

  if ($FieldOrder == "") {
    $FieldOrder = 1;
  }

  incrementExistingFieldOrder($ITSMTypeID, $FieldOrder);

  $sql = "INSERT INTO itsm_fieldslist (";
  
  foreach ($FieldSpecs as $key => $value) {
    $Field = $value['name'];
    if ($Field === "SelectFieldOption" || $Field === "LookupTable2" || $Field === "LookupField2") {
      continue;
    } else {
      $sql .= "$Field,";
    }    
  }

  $sql = rtrim($sql, ',');
  $sql .= ") VALUES (";

  $requiredFields = array("Indexed", "HideTables", "HideForms", "LockedCreate", "LockedView", "Required", "AddEmpty", "FullHeight", "RelationShowField", "UserFullName", "RightColumn", "LabelType", "DefaultField", "Hidden");

  foreach ($FieldSpecs as $key => $value) {
    $Name = $value['name'];
    $Value = $value['value'];
    
    if ($Name === "SelectFieldOption" || $Name === "LookupTable2" || $Name === "LookupField2") {
      continue;
    }

    switch ($Name) {
      case "FieldTitle":
        $Value = strip_tags($Value);
        break;
      default:
        $Value = mysqli_real_escape_string($conn, strip_tags($Value));
    }

    // Handle special fields (Indexed, HideTables, HideForms, SelectFieldOptions)
    if (in_array($Name, $requiredFields)) {
      if ($Value === "on") {
        $Value = 1;
        if ($Name === "Indexed") {
          $Indexed = 1;
        }
      } elseif ($Value === 0) {
        if ($Name === "Indexed") {
          $Indexed = 0;
        }
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

  $sql3 = "ALTER TABLE $TableName ADD $FieldName TEXT;";
  $result3 = mysqli_query($conn, $sql3);

  if($Indexed === 1){
    alterTableAddFullTextToColumn($TableName, $FieldName);
  }

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['createITSMStatus'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    // No return statement here
  }
  $SLASupported = "0";
  $UserID = $_SESSION['id'];
  $ITSMID = $_GET['ITSMID'];
  $StatusCode = $_GET['StatusCode'];
  $StatusName = $_GET['StatusName'];
  $SLASupported = $_GET['SLASupported'];
  $ClosedStatus = $_GET['ClosedStatus'];

  if ($SLASupported == "on") {
    $SLASupported = "1";
  }
  if ($ClosedStatus == "on") {
    $ClosedStatus = "1";
  }

  $sql = "INSERT INTO itsm_statuscodes (ModuleID, StatusCode,	StatusName, SLA) 
          VALUES (?,?,?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ssss", $ITSMID, $StatusCode, $StatusName, $SLASupported);
  $stmt->execute();
  $result = $stmt->get_result();

  // If this StatusCode needs to be SLA supported, so the value is 1 - we will now add sla supported reaction times for this statuscode to the itsm_sla_matrix table
  // and with some default values defined in the array

  if($SLASupported == "1"){
    $relatedModuleID = $ITSMID;
    $status = $StatusCode;
    // Define an array containing different SLA values and their corresponding minute values
    $sla_minutes = array(
      "1" => array("minutes" => array(15, 20, 30, 60)),
      "2" => array("minutes" => array(15, 30, 60, 120)),
      "3" => array("minutes" => array(30, 60, 120, 240))
    );

    // Prepare the SQL statement
    $sql_sla = "INSERT INTO itsm_sla_matrix (RelatedModuleID, Status, SLA, P1, P2, P3, P4) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_sla = mysqli_prepare($conn, $sql_sla);

    // Bind parameters
    mysqli_stmt_bind_param($stmt_sla, 'iiiiiii', $relatedModuleID, $status, $sla, $p1, $p2, $p3, $p4);

    // Iterate through the array
    foreach ($sla_minutes as $sla => $values) {
      // Extract the minutes array
      $minutes = $values['minutes'];

      // Bind minutes parameters and execute the statement
      list($p1, $p2, $p3, $p4) = $minutes;
      $sla = intval($sla);
      mysqli_stmt_execute($stmt_sla);
    }
  }
  $stmt_sla->close();

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['createITSMSLA'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];
  $ITSMID = $_GET['ITSMID'];
  $Priority1 = $_GET['Priority1'];
  $Priority2 = $_GET['Priority2'];
  $Priority3 = $_GET['Priority3'];
  $Priority4 = $_GET['Priority4'];

  $sql = "INSERT INTO itsm_sla_matrix (RelatedModuleID, Priority1, Priority2, Priority3, Priority4) 
          VALUES (?,?,?,?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sssss", $ITSMID, $Priority1, $Priority2, $Priority3, $Priority4);
  $stmt->execute();
  $result = $stmt->get_result();

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['createITSMEmail'])) {
  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];
  $ITSMID = $_GET['ITSMID'];
  $ITSMEmail = $_GET['ITSMEmail'];
  $DefaultEmail = $_GET['DefaultEmail'];

  if ($DefaultEmail == "2") {
    $DefaultEmail = "0";
  }

  $sql = "INSERT INTO itsm_emails (Email, DefaultEmail, RelatedITSMTypeID) 
          VALUES (?,?,?);";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sss", $ITSMEmail, $DefaultEmail, $ITSMID);
  $stmt->execute();
  $result = $stmt->get_result();

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['getITSMEmails'])) {
  // Check user groups
  if (!in_array("100031", $UserGroups) && !in_array("100001", $UserGroups)) {
    $GroupName = getUserGroupName("100031");
    $response = array("error" => "You need to be a member of $GroupName");
    echo json_encode($response);
    return;
  }

  $ITSMID = $_POST['ITSMID'];

  $sql = "SELECT Email
          FROM itsm_emails
          WHERE RelatedITSMTypeID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    $stmt->bind_param("i", $ITSMID);
    $stmt->execute();
    $result = $stmt->get_result();

    $emails = array();
    while ($row = $result->fetch_assoc()) {
      $emails[] = array(
        "Email" => $row['Email']
      );
    }
  
    $response = array("Result" => "success", "Emails" => $emails);
    echo json_encode($response);
  } else {
    $response = array("error" => "Error in preparing SQL statement");
    echo json_encode($response);
  }
}

if (isset($_GET['updateFormsName'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100029");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $UserID = $_SESSION['id'];
  $FormsName = mysqli_real_escape_string($conn, $_GET['FormsName']);
  $FormID = $_GET['formid'];
  $prevalue = $_GET['prevalue'];

  if (!empty($exists)) {
    echo "<script>alert('Navn eksisterer allerede');</script>";
    exit;
  }

  $sql = "UPDATE forms SET FormsName = ?, LastEditedBy = ?, LastEdited = Now() WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("sss", $FormsName, $UserID, $FormID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['updateFormsDescription'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100029");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $FormID = $_GET['formid'];
  $Description = mysqli_real_escape_string($conn, $_GET['Description']);

  $sql = "UPDATE forms SET Description = ? WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Description, $FormID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['updateFormsRelatedWorkFlow'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100029");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $FormID = $_GET['formid'];
  $WorkFlowID = $_GET['WorkFlowID'];

  $sql = "UPDATE forms SET RelatedWorkFlow = ? WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $WorkFlowID, $FormID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['updateCIDescription'])) {
  if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100015");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $CIID = $_GET['ciid'];
  $Description = mysqli_real_escape_string($conn, $_GET['Description']);

  $sql = "UPDATE cmdb_cis SET Description = ? WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Description, $CIID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['updateFormsModule'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100029");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $FormID = $_GET['formid'];
  $RelatedModuleID = $_GET['RelatedModule'];

  $sql = "UPDATE forms SET RelatedModuleID = ? WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $RelatedModuleID, $FormID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['updateFormStatus'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100029");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $FormID = $_GET['formid'];
  $Active = $_GET['Active'];

  $sql = "UPDATE forms SET Active = ? WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Active, $FormID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['updateCIStatus'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100029");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $CIID = $_GET['ciid'];
  $Active = $_GET['Active'];

  $sql = "UPDATE cmdb_cis SET Active = '$Active' WHERE ID = $CIID;";
  mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
}

if (isset($_GET['updateCIName'])) {
  if (in_array("100015", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100015");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $CIID = $_GET['ciid'];
  $Name = $_GET['Name'];

  $sql = "UPDATE cmdb_cis SET Name = ? WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $Name, $CIID);
  $stmt->execute();
  $result = $stmt->get_result();
}

if (isset($_GET['duplicateForm'])) {
  if (in_array("100029", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100029");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $i = 0;
  $FormID = $_GET['FormID'];
  $DBTable = getFormsTableName($FormID);
  $GenerateRandomName = $functions->generateRandomString(15);
  $DBTableNew = "formstable_$GenerateRandomName";
  $exists = checkIfFormsTableNameExists($DBTableNew);

  while (!empty($exists)) {
    $DBTableNew = $DBTableNew . $i;
    $exists = checkIfFormsTableNameExists($DBTableNew);
    $i = $i + 1;
  }

  //Create Form
  $sql = "INSERT INTO Forms(FormsName, TableName, Description, CreatedBy, Created, LastEditedBy, LastEdited, Active, Forms.Status)
          SELECT FormsName, '$DBTableNew' AS TableName, Description, CreatedBy, Created, LastEditedBy, LastEdited, Active, Forms.Status
          FROM Forms
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("i", $FormID);
  $stmt->execute();
  $result = $stmt->get_result();

  $last_id = mysqli_insert_id($conn);
  $FormComment = "formid=$last_id";

  //Create Fields
  //First select all fields from old checklist
  $sql = "INSERT INTO forms_fieldslist(RelatedFormID, FieldName, FieldLabel, FieldType, FieldOrder, FieldDefaultValue, FieldTitle, SelectFieldOptions)
          SELECT $last_id, FieldName, FieldLabel, FieldType, FieldOrder, FieldDefaultValue, FieldTitle, SelectFieldOptions
          FROM forms_fieldslist
          WHERE RelatedFormID = ?;";

  $stmt2 = mysqli_prepare($conn, $sql);
  $stmt2->bind_param("i", $FormID);
  $stmt2->execute();
  $result = $stmt2->get_result();
  mysqli_stmt_close($stmt2);

  createDuplicateFormsFieldTable($DBTable, $DBTableNew);

  $sql3 = "ALTER TABLE $DBTableNew COMMENT = '$FormComment';";
  $result3 = mysqli_query($conn, $sql3) or die('Query fail: ' . mysqli_error($conn));
  
  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['duplicateWorkFlow'])) {
  if (in_array("100023", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100023");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $i = 0;
  $WFID = $_GET['WFID'];

  //Create Form
  $sql = "INSERT INTO workflows_template(WorkflowName, Description, Responsible, RelatedModuleID, Active)
          SELECT WorkflowName, Description, Responsible, RelatedModuleID, Active
          FROM workflows_template
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $WFID);
  $stmt->execute();

  $last_id = mysqli_insert_id($conn);

  //Create Fields
  //First select all fields from old checklist
  $sql = "INSERT INTO workflowsteps_template(RelatedWorkFlowID, StepOrder, StepName, Description, RelatedUserID)
          SELECT $last_id, StepOrder, StepName, Description, RelatedUserID
          FROM workflowsteps_template
          WHERE RelatedWorkFlowID = ?;";

  $stmt2 = mysqli_prepare($conn, $sql);
  $stmt2->bind_param("s", $WFID);
  $stmt2->execute();

  $array[] = array("Result" => "success");
  echo json_encode($array);
}

if (isset($_GET['duplicateWorkFlowTask'])) {
  if (in_array("100023", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100023");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
  $i = 0;
  $WFTASKID = $_GET['WFTASKID'];

  //Create Form
  $sql = "INSERT INTO workflowsteps_template(RelatedWorkFlowID, StepOrder, StepName, Description, RelatedUserID)
          SELECT RelatedWorkFlowID, StepOrder, StepName, Description, RelatedUserID
          FROM workflowsteps_template
          WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $WFTASKID);
  $stmt->execute();

  $array[] = array("Result" => "success");
  echo json_encode($array);
}

if (isset($_GET['duplicateCI'])) {
  
  // Initialize array
  $array = [];

  if (!in_array("100015", $UserGroups) && !in_array("100001", $UserGroups)) {
    $GroupName = getUserGroupName("100015");
    $array[] = ["error" => "You need to be a member of $GroupName"];
    echo json_encode($array);
    return;
  }

  $i = 0;
  $CITypeID = $_POST['CITypeID'];
  $CITableName = getCITableName($CITypeID);
  $GenerateRandomName = $functions->generateRandomString(15);
  $DBTableNew = "cmdb_ci_$GenerateRandomName";

  // Check if the table name exists
  $exists = checkIfFormsTableNameExists($DBTableNew);

  while (!empty($exists)) {
    $DBTableNew = $DBTableNew . $i;
    $exists = checkIfFormsTableNameExists($DBTableNew);
    $i++;
  }

  // Prepare and execute SQL to create CI
  $sql1 = "INSERT INTO cmdb_cis(Name, TableName, Description, CreatedBy, Created, LastEditedBy, LastEdited, Active, GroupID)
          SELECT Name, '$DBTableNew' AS TableName, Description, CreatedBy, Created, LastEditedBy, LastEdited, Active, GroupID
          FROM cmdb_cis
          WHERE ID = ?";
  if ($stmt1 = mysqli_prepare($conn, $sql1)) {
    $stmt1->bind_param("i", $CITypeID);
    $stmt1->execute();
  } else {
    // Handle preparation failure
    die("Failed to prepare statement: " . mysqli_error($conn));
  }

  // Get the last inserted ID
  $last_id = mysqli_insert_id($conn);
  $CIComment = "ciid=$last_id";

  // Prepare and execute SQL to create Fields
  $sql2 = "INSERT INTO cmdb_ci_fieldslist(RelatedCITypeID, FieldName, FieldLabel, FieldType, FieldOrder, FieldDefaultValue, FieldTitle, SelectFieldOptions, FieldWidth, DefaultField, LookupTable, LookupField, ResultFields, UserFullName, LookupFieldResultTable, LookupFieldResultView, RelationShowField, ImportSourceField, SyncSourceField, RelationsLookup, Indexed, HideForms, HideTables, Required, LockedView, LockedCreate, Addon, AddEmpty, FullHeight, RightColumn, LabelType)
          SELECT $last_id, FieldName, FieldLabel, FieldType, FieldOrder, FieldDefaultValue, FieldTitle, SelectFieldOptions, FieldWidth, DefaultField, LookupTable, LookupField, ResultFields, UserFullName, LookupFieldResultTable, LookupFieldResultView, RelationShowField, ImportSourceField, SyncSourceField, RelationsLookup, Indexed, HideForms, HideTables, Required, LockedView, LockedCreate, Addon, AddEmpty, FullHeight, RightColumn, LabelType
          FROM cmdb_ci_fieldslist
          WHERE RelatedCITypeID = ?";

  if ($stmt2 = mysqli_prepare($conn, $sql2)) {
    $stmt2->bind_param("i", $CITypeID);
    $stmt2->execute();
  } else {
    // Handle preparation failure
    die("Failed to prepare statement: " . mysqli_error($conn));
  }

  // Note: This function should be safe from SQL Injection if implemented correctly
  createDuplicateFormsFieldTable($CITableName, $DBTableNew);

  // Note: Direct query used due to dynamic table name
  $sql3 = "ALTER TABLE $DBTableNew COMMENT = '$CIComment'";
  if (!mysqli_query($conn, $sql3)) {
    die("Query failed: " . mysqli_error($conn));
  }
}

if (isset($_GET['duplicateITSM'])) {

  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

  if (in_array("100031", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100031");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  duplicateITSM($ITSMTableName, $ITSMTypeID);
}

if (isset($_GET['getRequests'])) {
  $ITSMTypeID = $_GET['getRequests'];

  $sql = "SELECT forms.ID AS FormID, forms.FormsName, forms.Description
          FROM forms
          WHERE Active = 1 AND Status = 1 AND forms.RelatedModuleID = ?
          ORDER BY forms.FormsName ASC";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $ITSMTypeID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $FormID = $row['FormID'];
    $FormsName = $row['FormsName'];
    $Description = $row['Description'];

    $RequestLink = "<a href=\"javascript:getRequestDefinition($FormID,'');\" title=\"$Description\">$FormsName</a>";

    $array[] = array('RequestLink' => $RequestLink);
  }

  echo json_encode($array);
}

if (isset($_GET['getRequestDefinition'])) {
  $FormID = $_GET['FormID'];
  $ModalType = $_GET['ModalType'];
  $Definition = "<div class=\"card\"><div class=\"card-body\"><div class=\"row\"><h6 id=\"createFormsName\"></h6>";
  $FormsName = getFormsName($FormID);
  $UsersGroups = $_SESSION['memberofgroups'];

  $sql = "SELECT Forms_fieldslist.FieldName, Forms_fieldslist.FieldLabel, Forms_fieldslist_types.Definition, Forms_fieldslist.FieldDefaultValue, Forms_fieldslist.fieldtitle, 
          Forms_fieldslist.GroupFilterOptions, Forms_fieldslist.SelectFieldOptions, Forms_fieldslist.Hidden, Forms_fieldslist.FieldWidth, forms_fieldslist.LookupTable, forms_fieldslist.LookupField, forms_fieldslist.LookupFieldResultView,forms_fieldslist.HideForms,forms_fieldslist.Required,forms_fieldslist.LockedCreate, forms_fieldslist.LockedView, forms_fieldslist.Addon, forms_fieldslist.AddEmpty, forms_fieldslist.FullHeight
          FROM Forms
          LEFT JOIN Forms_fieldslist ON Forms.ID = Forms_fieldslist.RelatedFormID
          LEFT JOIN Forms_fieldslist_types ON Forms_fieldslist.FieldType = Forms_fieldslist_types.ID
          WHERE Forms.ID = ?
          ORDER BY Forms_fieldslist.FieldOrder ASC";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("i", $FormID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $LookupTable = $row["LookupTable"];
    $LookupField = "ID";
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

    if ($GroupFilterOptions) {
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
      $Height = "250px";
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

    $Definition1 .= $row["Definition"];

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
  }
  $Definition1 .= "</div></div></div><div class=\"row\"><br></div>";
  $array[] = array('Definition' => $Definition1, "FormID" => $FormID, "FormsName" => $FormsName);

  mysqli_free_result($result);
  echo json_encode($array);
}

if (isset($_GET['updateFormFieldValue'])) {
  $UserID = $_SESSION["id"];
  $FormID = $_GET['formid'];
  $FormRelatedModule = getFormModuleID($FormID);

  $Array = json_decode($_GET['jsonString'], true);
  $RequestCustomer = $_GET['Requester'];
  $BusinessService = $_GET['BusinessService'];
  $Priority = $_GET['Priority'];
  $RequestRespTeam = $_GET['ResponsibleTeam'];
  $RequestRespSup = $_GET['Responsible'];
  $RequestID = $_GET['RequestID'];
  $imgData = $_GET['imgData'];

  $counter = 0;
  $TableName = getTableNameFromFormID($FormID);
  foreach ($Array as $row) {
    $fieldname = $row['fieldname'];
    $fieldvalue = $row['value'];

    if (empty($fieldvalue)) {
      $fieldvalue = "";
    }

    if ($counter == 0) {
      $sql = "INSERT INTO $TableName ($fieldname) VALUES ('$fieldvalue');";
      $result = mysqli_query($conn, $sql);
      $RecordID = mysqli_insert_id($conn);
      $counter = $counter + 1;
    } else {
      $sql = "UPDATE $TableName SET $fieldname = '$fieldvalue' WHERE ID = $RecordID;";
      $result = mysqli_query($conn, $sql);
    }
  }
  if ($FormRelatedModule == "16") {
    $ResultArray[] = array("Result" => "notarequest");
    echo json_encode($ResultArray);
    exit;
  }
  $UserSessionID = $UserID;
  if (empty($RequestCustomer)) {
    $RequestCustomer = $UserID;
  }
  if (empty($BusinessService)) {
    $BusinessService = "-1";
  }
  if (empty($Priority)) {
    $Priority = "2";
  }
  if (empty($RequestRespTeam)) {
    $RequestRespTeam = "-1";
  }
  if (empty($RequestRespSup)) {
    $RequestRespSup = "-1";
  }
  if (empty($RequestRespTeam)) {
    $RequestRespTeam = "-1";
  }
  if (empty($RequestID)) {
    $RequestID = "0";
  }
  $RequestSubject = $functions->getFormNameFromFormID($FormID);
  $RequestProblemText = "";

  $Base64Paste = "";

  $RequestID = createNewRequestFromFormView($RequestID, $FormID, $RecordID, $UserSessionID, $RequestCustomer, $BusinessService, $RequestSubject, $RequestProblemText, $Priority, $RequestRespTeam, $RequestRespSup, $Base64Paste);
  updateRelatedRequestOnFormFieldRecord($TableName, $RecordID, $RequestID);

  if (empty($RequestID)) {
    //No result - error
    $ResultArray[] = array("Result" => "error");
    echo json_encode($ResultArray);
    exit;
  } else {
    //success
    $ResultArray[] = array("Result" => "success");
    echo json_encode($ResultArray);
    exit;
  }
}

if (isset($_GET['getRequestInformation'])) {
  $UserID = $_SESSION["id"];
  $RequestID = $_GET['RequestID'];

  //Get Requests pre values to be able to compare between pre and post
  $sql = "SELECT Requests.ID AS RequestID, DateCreated, Subject, RelatedCompanyID, companies.Companyname AS Companyname, Requests.Type, ProblemText, ResponsibleTeam, Requests.SolutionText, Requests.RelatedFormID, Requests.RelatedFormTableRowID,
        Responsible, Priority, Status, CONCAT(users.Firstname,' ',users.Lastname) AS CustomerName, Requests.RelatedCustomerID as CustomerID, 
        users.CompanyID as CustomerCompanyID, users.Phone AS CustomerPhone, Requeststatuscodes.StatusName AS StatusName, Companies.Phone AS CompanyPhone, Requests.RelatedBusinessService
        FROM Requests 
        LEFT JOIN Companies ON Requests.RelatedCompanyID = Companies.ID
        LEFT JOIN users ON Requests.RelatedCustomerID = users.ID
        LEFT JOIN Requeststatuscodes ON Requests.Status = Requeststatuscodes.ID
        WHERE Requests.ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $RequestID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $RequestCreatedDateVal = $row["DateCreated"];
    $RequestSubjectVal = $row["Subject"];
    $RequestCompanyIDVal = $row['RelatedCompanyID'];
    $RequestCompanyName = $row['Companyname'];
    $RequestTypeVal = $row['Type'];
    $RequestProblemTextVal = $row['ProblemText'];
    $RequestRespTeamVal = $row['ResponsibleTeam'];
    $RequestResponsibleVal = $row['Responsible'];
    $ResponsibleVal = $row['Responsible'];
    $RequestPriorityVal = $row['Priority'];
    $RequestStatusVal = $row['Status'];
    $RequestStatusName = $row['StatusName'];
    $RequestCustomerNameVal = $row['CustomerName'];
    $RequestCustomerIDVal = $row['CustomerID'];
    $RequestCustomerPhoneVal = $row['CustomerPhone'];
    $RequestCustomerCompanyIDVal = $row['CustomerCompanyID'];
    $RequestCompanyPhoneVal = $row['CompanyPhone'];
    $RequestSolutionTextVal = $row['SolutionText'];
    $RelatedBusinessService = $row['RelatedBusinessService'];
    $RelatedFormID = $row['RelatedFormID'];
    $RelatedFormTableRowID = $row['RelatedFormTableRowID'];
  }

  $Definition .= "<div class=\"col-lg-6 col-md-6 col-sm-12 col-xs-12\">
                    <div class=\"input-group input-group-static mb-4\">
                      <label for=\"RequestCompany\">" . _("Company") . "</label>
                      <input type=\"text\" class=\"form-control\" id=\"RequestCompany\" value=\"$RequestCompanyName\" readonly=\"readonly\">
                    </div>
                  </div>";
  $Definition .= "<div class=\"col-lg-6 col-md-6 col-sm-12 col-xs-12\">
                    <div class=\"input-group input-group-static mb-4\">
                      <label for=\"RequestCustomer\">" . _("Customer") . "</label>
                      <input type=\"text\" class=\"form-control\" id=\"RequestCustomer\" value=\"$RequestCustomerNameVal\" readonly=\"readonly\">
                    </div>
                  </div>";

  $FormTableName = getTableNameFromFormID($RelatedFormID);

  $sql = "SELECT forms_fieldslist.FieldName, forms_fieldslist.Label, forms_fieldslist_types.Definition, forms_fieldslist.FieldType, Forms_fieldslist.FieldDefaultValue, Forms_fieldslist.fieldtitle, Forms_fieldslist.SelectFieldOptions, Forms_fieldslist.FieldWidth
          FROM forms
          LEFT JOIN forms_fieldslist ON forms.ID = forms_fieldslist.RelatedFormID
          LEFT JOIN forms_fieldslist_types ON forms_fieldslist.FieldType = forms_fieldslist_types.ID
          WHERE Forms.ID = ?
          ORDER BY FieldOrder ASC";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("s", $RelatedFormID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $FieldType = $row["FieldType"];
    $FieldNameShort = $row["FieldName"];
    //$FieldName = "#1" . $row["FieldName"] . "#2" . "#3" . $RelatedFormID . "#4" . "#5" . $FormTableName . "#6#7$RelatedFormTableRowID#8";
    $FormFieldValue = getRequestFormFieldValue($FormTableName, $FieldNameShort, $RelatedFormTableRowID);
    if ($FormFieldValue == "NULL") {
      $FormFieldValue = "";
    }
    $FieldDefaultValue = $row["FieldDefaultValue"];
    $FieldTitle = $row["fieldtitle"];
    $SelectFieldOptions = $row["SelectFieldOptions"];
    $FieldWidth = $row["FieldWidth"];
    $Label = $row["Label"];

    switch ($FieldType) {
      case 2:
        $Definition .= "<div class=\"col-lg-$FieldWidth col-md-$FieldWidth col-sm-12 col-xs-12\">
                            <div class=\"input-group input-group-static mb-4\"><label for=\"$FieldNameShort\" title=\"$FieldTitle\">$Label</label>
                              <textarea class=\"resizable_textarea form-control\" id=\"$FieldNameShort\" name=\"$FieldNameShort\" title=\"$FieldTitle\" rows=\"5\" autocomplete=\"off\" readonly=\"readonly\">$FormFieldValue</textarea>
                            </div>
                          </div>";
        break;
      case 3:
        $Definition .= "<div class=\"col-lg-$FieldWidth col-md-$FieldWidth col-sm-12 col-xs-12\">
                            <div class=\"input-group input-group-static mb-4\">
                              <label for=\"$FieldNameShort\">$Label</label>
                                &nbsp;<input type=\"checkbox\" name=\"$FieldNameShort\" id=\"$FieldNameShort\" onclick=\"return false;\"></input>
                            </div>
                          </div>";
        break;
      case 5:
        $Definition .= "<div class=\"col-lg-$FieldWidth col-md-$FieldWidth col-sm-12 col-xs-12\">
                            <div class=\"input-group input-group-static mb-4\">
                              <label for=\"$FieldNameShort\" title=\"$FieldTitle\">$Label</label>
                              <input type=\"datetime-local\" class=\"form-control\" id=\"$FieldNameShort\" value=\"$FormFieldValue\" readonly=\"readonly\">
                            </div>
                          </div>";
        break;
      default:
        $Definition .= "<div class=\"col-lg-$FieldWidth col-md-$FieldWidth col-sm-12 col-xs-12\">
                            <div class=\"input-group input-group-static mb-4\">
                              <label for=\"$FieldNameShort\" title=\"$FieldTitle\">$Label</label>
                              <input type=\"text\" class=\"form-control\" id=\"$FieldNameShort\" value=\"$FormFieldValue\" readonly=\"readonly\">
                            </div>
                          </div>";
    }
  }

  if (empty($RequestID)) {
    //No result - error
    $ResultArray[] = array("Result" => "error");
  } else {
    //success
    $ResultArray[] = array("Result" => "success", 'Definition' => $Definition, 'RequestID' => $RequestID);
  }
  echo json_encode($ResultArray);
}

if (isset($_GET['updateBaseDBVersion'])) {
  if (!in_array("100000", $UserGroups)) {
    echo "You are not allowed!";
    return;
  }

  $FileToRestore = $_GET["file"];
  $sql1 = "DROP DATABASE IF EXISTS practicle_base;";
  $result = mysqli_query($conn, $sql1);
  $sql2 = "CREATE DATABASE IF NOT EXISTS practicle_base COLLATE utf8mb3_danish_ci;";
  $result = mysqli_query($conn, $sql2);

  $base_dbname = "practicle_base";

  // Connect & select the database
  $conn_base = mysqli_connect($dbservername, $dbusername, $dbpassword, $base_dbname);
  // Temporary variable, used to store current query
  $templine = '';

  // Read the dump file
  $dump = file_get_contents($FileToRestore);

  // Split the dump file into individual queries
  $queries = preg_split('/;\s*\r?\n/', $dump);

  // Flag to indicate whether to process queries or skip
  $skipQueries = true;

  try {
    // Loop through each query
    foreach ($queries as $sql) {
      // Remove any leading/trailing white space and semicolon
      $sql = trim($sql, ";\r\n");

      // Skip empty queries
      if ($sql === '') {
        continue;
      }

      // Check if the header section ends
      if (strpos($sql, 'CREATE TABLE') !== false) {
        $skipQueries = false;
      }

      // Skip queries if in the header section
      if ($skipQueries) {
        continue;
      }

      // Check if the query is a commit statement or sets collation_connection/character_set_client to NULL
      if (strpos($sql, 'COMMIT') !== false || strpos($sql, 'SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION') !== false || strpos($sql, 'SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT') !== false) {
        continue;
      }

      // Execute the query
      $result = mysqli_query($conn_base, $sql);

      // Check for errors
      if (!$result) {
        $error = mysqli_error($conn_base);
        throw new Exception($error);
      }
    }

    // Close the connection
    mysqli_close($conn_base);

    $ResultArray[] = array("result" => "success");
    echo json_encode($ResultArray);
  } catch (Exception $e) {
    // Handle the exception/error
    $error = $e->getMessage();
    $query = isset($sql) ? $sql : '';
    // Log the error message and the query
    $functions->errorlog("Error: " . $error . " | Query: " . $query, "updateBaseDBVersion");
    // Add error information to the response
    $ResultArray[] = array("result" => "error", "message" => $error, "query" => $query);
    echo json_encode($ResultArray);
  }
}

if (isset($_GET['editWorkFlowTask'])) {

  $WFTID = $_GET["WFTID"];

  $sql = "SELECT workflowsteps.`ID`, workflowsteps.`RelatedWorkFlowID`, workflowsteps.`StepOrder`, workflowsteps.`StepName`, workflowsteps.`Description`, workflowsteps.`RelatedStatusID`, workflowsteps.
                `RelatedUserID`, taskslist.Deadline
          FROM workflowsteps
          LEFT JOIN taskslist ON workflowsteps.RelatedTaskID = taskslist.ID
          WHERE workflowsteps.ID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("i", $WFTID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = mysqli_fetch_array($result)) {
    $ID = $row["ID"];
    $RelatedWorkFlowID = $row["RelatedWorkFlowID"];
    $StepOrder = $row["StepOrder"];
    $StepName = $row["StepName"];
    $Description = $row["Description"];
    $RelatedStatusID = $row["RelatedStatusID"];
    $RelatedUserID = $row["RelatedUserID"];
    $Deadline = $functions->convertToDanishDateTimeFormat($row["Deadline"]);

    $FieldsString = $StepNameTemp . $DescriptionTemp . $RelatedStatusIDTemp . $RelatedUserIDTemp . $DeadlineTemp;
    $ResultArray[] = array("ID" => $ID, "RelatedWorkFlowID" => $RelatedWorkFlowID, "StepOrder" => $StepOrder, "StepName" => $StepName, "Description" => $Description, "RelatedStatusID" => $RelatedStatusID, "RelatedUserID" => $RelatedUserID, "Deadline" => $Deadline);
  }

  echo json_encode($ResultArray);
}

if (isset($_GET['updateWorkFlowTask'])) {
  $WFTID = $_POST["WFTID"];
  $queryString = $_POST["queryString"];
  $ITSMID = getWFTElementID($WFTID);
  $ITSMTypeID = getWFTElementTypeID($WFTID);

  foreach ($queryString as $value) {

    $Field = trim($value['name']);
    $Field = str_replace("EditWFT", "", $Field);
    $Value = mysqli_real_escape_string($conn, $value['value']);

    if ($Field == "Deadline") {
      $Value = convertFromDanishTimeFormat($Value);
      $sql = "UPDATE taskslist SET $Field = '$Value'
              WHERE wftid = $WFTID";
      mysqli_query($conn, $sql);
    } else if ($Field == "RelatedStatusID") {
      $sql = "UPDATE taskslist SET Status = '$Value'
              WHERE wftid = $WFTID";
      mysqli_query($conn, $sql);
      $sql = "UPDATE workflowsteps SET $Field = '$Value'
              WHERE ID = $WFTID";
      mysqli_query($conn, $sql);
    } else {
      $sql = "UPDATE workflowsteps SET $Field = '$Value'
              WHERE ID = $WFTID";

      mysqli_query($conn, $sql);
    }
  }

  $ResultArray[] = array("ITSMTypeID" => $ITSMTypeID, "ITSMID" => $ITSMID, "Result" => "success");
  echo json_encode($ResultArray);
}

if (isset($_GET['deleteWorkFlowTask'])) {
  $WFTID = $_POST["WFTID"];

  $sql = "DELETE FROM taskslist
          WHERE wftid = $WFTID";
  mysqli_query($conn, $sql);

  $sql = "DELETE FROM workflowsteps
          WHERE ID = $WFTID";

  mysqli_query($conn, $sql);

  $ResultArray[] = array("Result" => "success");
  echo json_encode($ResultArray);
}

if (isset($_GET['deleteTempPicture'])) {
  $FileName = $_POST["FileName"];
  $UserID = $_POST["UserID"];

  $sql = "DELETE FROM files_temp
          WHERE FileName = ? AND RelatedUserID = ?";

  $stmt = mysqli_prepare($conn, $sql);
  $stmt->bind_param("ss", $FileName, $UserID);
  $stmt->execute();

  $ResultArray[] = array("Result" => "success");
  echo json_encode($ResultArray);
}

if (isset($_GET['getTeamsITSM'])) {
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

  $sql = "SELECT $ITSMTableName.ID, Customer, Subject, $ITSMTableName.Description, itsm_statuscodes.StatusName, itsm_priorities.PriorityName, Responsible, BusinessService, $ITSMTableName.SLA, Created, CreatedBy, LastUpdated, teams.Teamname, companies.Companyname
          FROM $ITSMTableName
          LEFT JOIN teams ON $ITSMTableName.Team = teams.ID
          LEFT JOIN companies ON $ITSMTableName.RelatedCompanyID = companies.ID
          LEFT JOIN itsm_statuscodes ON $ITSMTableName.Status = itsm_statuscodes.StatusCode
          LEFT JOIN itsm_priorities ON $ITSMTableName.Priority = itsm_priorities.ID
          WHERE itsm_statuscodes.ModuleID = $ITSMTypeID;";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {
    $ID = $row["ID"];
    $StatusName = $row["StatusName"];
    $PriorityName = $row["PriorityName"];
    $Teamname = $row["Teamname"];
    if ($Teamname == "") {
      $Teamname = "none";
    }
    $Companyname = $row["Companyname"];
    $temparray[] = array("Status" => "$StatusName", "Priority" => "$PriorityName", "Team" => $Teamname, "Company" => $Companyname);
  }

  echo json_encode($temparray);
}

if (isset($_GET['getTeamsOpenITSM'])) {
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $temparray[] = array();

  $sql = "SELECT teams.Teamname, COUNT($ITSMTableName.ID) AS Antal
          FROM $ITSMTableName
          LEFT JOIN teams ON $ITSMTableName.Team = teams.ID
          WHERE $ITSMTableName.Team IS NOT NULL AND $ITSMTableName.Status NOT IN (6,7)
          GROUP BY teams.Teamname;";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {
    $Antal = $row[1];
    $Teamname = $row[0];
    if ($Teamname == "") {
      $Teamname = "Not assigned to Team";
    }

    $temparray[] = array("Antal" => $Antal, "Team" => $Teamname);
  }

  echo json_encode($temparray);
}

if (isset($_GET['getTeamsClosedITSM'])) {
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $temparray[] = array();

  $sql = "SELECT teams.Teamname, COUNT($ITSMTableName.ID) AS Antal
          FROM $ITSMTableName
          LEFT JOIN teams ON $ITSMTableName.Team = teams.ID
          WHERE $ITSMTableName.Team IS NOT NULL AND $ITSMTableName.Status IN (6,7)
          GROUP BY teams.Teamname;";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {
    $Antal = $row[1];
    $Teamname = $row[0];

    $temparray[] = array("Antal" => $Antal, "Team" => $Teamname);
  }

  echo json_encode($temparray);
}

if (isset($_GET['getCompaniesOpenITSM'])) {
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $temparray[] = array();

  $sql = "SELECT companies.Companyname, COUNT($ITSMTableName.ID) AS Antal
          FROM $ITSMTableName
          LEFT JOIN companies ON $ITSMTableName.RelatedCompanyID = companies.ID
          WHERE $ITSMTableName.Status NOT IN (6,7)
          GROUP BY companies.Companyname;";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {
    $Antal = $row["Antal"];
    $Companyname = $row["Companyname"];
    if ($Companyname == "") {
      $Companyname = "none";
    }
    $temparray[] = array("Antal" => $Antal, "Company" => $Companyname);
  }

  if (empty($temparray)) {
    echo json_encode([]);
  } else {
    echo json_encode($temparray);
  }
}

if (isset($_GET['getCompaniesClosedITSM'])) {
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  $temparray[] = array();

  $sql = "SELECT companies.Companyname, COUNT($ITSMTableName.ID) AS Antal
          FROM $ITSMTableName
          LEFT JOIN companies ON $ITSMTableName.RelatedCompanyID = companies.ID
          WHERE $ITSMTableName.Status IN (6,7)
          GROUP BY companies.Companyname;";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {
    $Antal = $row[1];
    $Companyname = $row[0];
    if ($Companyname == "") {
      $Companyname = "none";
    }

    $temparray[] = array("Antal" => $Antal, "Company" => $Companyname);
  }

  echo json_encode($temparray);
}

if (isset($_GET['getResponsiblesOpenITSM'])) {
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

  $sql = "SELECT $ITSMTableName.Responsible, COUNT($ITSMTableName.ID) AS Antal
          FROM $ITSMTableName
          WHERE $ITSMTableName.Responsible IS NOT NULL AND $ITSMTableName.Status NOT IN (6,7)
          GROUP BY $ITSMTableName.Responsible;";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {
    $Antal = $row["Antal"];
    $Responsible = $row["Responsible"];

    $ResponsibleName = $functions->getUserFullName($Responsible);

    $temparray[] = array("Antal" => $Antal, "Responsible" => "$ResponsibleName");
  }

  echo json_encode($temparray);
}

if (isset($_GET['getResponsiblesClosedITSM'])) {
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

  $sql = "SELECT $ITSMTableName.Responsible, COUNT($ITSMTableName.ID) AS Antal
          FROM $ITSMTableName
          WHERE $ITSMTableName.Responsible IS NOT NULL AND $ITSMTableName.Status IN (6,7)
          GROUP BY $ITSMTableName.Responsible;";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {
    $Antal = $row["Antal"];
    $Responsible = $row["Responsible"];

    $ResponsibleName = $functions->getUserFullName($Responsible);

    $temparray[] = array("Antal" => $Antal, "Responsible" => $ResponsibleName);
  }

  echo json_encode($temparray);
}

if (isset($_GET['updateCompany'])) {
  $requiredGroups = ["100025","100001"];
  $functions->checkUserGroups($requiredGroups, $UserGroups);

  $UserSessionID = $_SESSION["id"];
  $temp = $functions->translate("Company");
  $fieldSpecsArray = $_POST['FieldSpecs'];
  $CompanyID = $_POST['CompanyID'];  // The ID of the company you wish to update

  $updatePairs = [];
  $differences = [];

  foreach ($fieldSpecsArray as $item) {
    $fieldName = $item['name'];
    $fieldValue = $item['value'];

    $oldValue = getOldValueOfCompany($CompanyID, $fieldName);

    if ($oldValue !== $fieldValue) {
        $differences[] = ["field" => $fieldName, "old" => $oldValue, "new" => $fieldValue];
    }

    // Check for email field and if it's empty or if it already exists
    if ($fieldName === 'Email') {
        if (empty($fieldValue)) {
            echo json_encode(["Result" => "error", "Message" => $functions->translate("Email cannot be empty")]);
            return;
        } elseif (doesCompanyEmailExists($fieldValue, $CompanyID)) {
            echo json_encode(["Result" => "error", "Message" => $functions->translate("This email already exists")]);
            return;
        }
    }

    if ($fieldName === 'RelatedSLAID') {
      if (empty($fieldValue)) {
        echo json_encode(["Result" => "error", "Message" => $functions->translate("SLA is required")]);
        return;
      }
    }

    if ($fieldName === 'CompanyName') {
      if (empty($fieldValue)) {
        echo json_encode(["Result" => "error", "Message" => $functions->translate("Company name is required")]);
        return;
      }
    }

    if (empty($fieldValue)) {
        $updatePairs[] = "$fieldName = NULL";
    } else {
        $updatePairs[] = "$fieldName = '" . $fieldValue . "'";
    }
  }

  if (!empty($updatePairs)) {

    // Split pairs into field names and values
    $fieldNames = [];
    $fieldValues = [];
    $fieldPlaceholders = [];

    foreach ($fieldSpecsArray as $item) {
      $fieldName = $item['name'];
      $fieldValue = $item['value'];

      $fieldNames[] = $fieldName;
      $fieldValues[] = $fieldValue;

      if (is_null($fieldValue)) {
        $fieldPlaceholders[] = "$fieldName = NULL";
      } else {
        $fieldPlaceholders[] = "$fieldName = ?";
      }
    }

    $updateStr = implode(", ", $fieldPlaceholders);

    $sql = "UPDATE Companies SET $updateStr WHERE ID = ?";

    $stmt = mysqli_prepare($conn, $sql);

    // Build the type string (like 'sssi') for bind_param based on the number of fields
    $types = str_repeat('s', count($fieldValues));

    // Add CompanyID to the values array for binding
    $fieldValues[] = $CompanyID;

    // Use the unpacking operator to bind all values at once
    $stmt->bind_param($types . 'i', ...$fieldValues);

    $result = $stmt->execute();

    if ($result) {
      foreach ($differences as $difference) {
        $LogActionText = "{$difference['field']} changed from {$difference['old']} to {$difference['new']}";
        createCompanyLogEntry($CompanyID, $UserSessionID, "1", $LogActionText);
      }
      $unitTypeTranslated = $functions->translate($temp);
      $msg = $unitTypeTranslated . " " . $functions->translate("updated");
      echo json_encode(["Result" => "success", "Message" => $msg]);
    } else {
      $errorMsg = mysqli_error($conn);
      $functions->errorlog("$unitTypeTranslated could not be updated. Error: $errorMsg", "updateCompany");
      echo json_encode(["Result" => "error", "Message" => $errorMsg]);
    }
  }
}

if (isset($_GET['updateUser'])) {
  $requiredGroups = ["100026","100001"];
  $functions->checkUserGroups($requiredGroups, $UserGroups);

  $UserSessionID = $_SESSION["id"];
  $temp = $functions->translate("User");
  $fieldSpecsArray = $_POST['FieldSpecs'];
  $UserID = $_POST['UserID'];

  $updatePairs = [];
  $differences = [];

  foreach ($fieldSpecsArray as $item) {
    $fieldName = $item['name'];
    $fieldValue = $item['value'];    
    $oldValue = getOldValueOfUser($UserID, $fieldName);

    // Skip logging and updating if old value is NULL and new value is empty
    if ($oldValue === NULL && $fieldValue === "") {
        continue;
    }

    if ($oldValue !== $fieldValue) {
      $differences[] = ["field" => $fieldName, "old" => $oldValue, "new" => $fieldValue];
    }

    // Check for email field and if it's empty or if it already exists
    if ($fieldName === 'Email') {
      if (empty($fieldValue)) {
        echo json_encode(["Result" => "error", "Message" => $functions->translate("Email cannot be empty")]);
        return;
      } elseif ($Name = doesEmailExists($fieldValue, $UserID)) {
        echo json_encode(["Result" => "error", "Message" => $functions->translate("This email already exists for")."<br>".$Name]);
        return;
      }
    }

    if ($fieldName === 'Firstname') {
      if (empty($fieldValue)) {
        echo json_encode(["Result" => "error", "Message" => $functions->translate("Firstname is required")]);
        return;
      }
    }

    if ($fieldName === 'Username') {
      if (empty($fieldValue)) {
        echo json_encode(["Result" => "error", "Message" => $functions->translate("Username cannot be empty")]);
        return;
      } elseif ($Name = doesUsernameExists($fieldValue, $UserID)) {
        echo json_encode(["Result" => "error", "Message" => $functions->translate("This username already exists for") . "<br>" . $Name]);
        return;
      }
    }
    
    if (empty($fieldValue)) {
      $updatePairs[] = "$fieldName = NULL";
    } else {
      $updatePairs[] = "$fieldName = '" . $fieldValue . "'";
    }
  }
  
  if (!empty($updatePairs)) {
    $fieldNames = [];
    $fieldValues = [];
    $fieldPlaceholders = [];
    
    foreach ($fieldSpecsArray as $item) {
        $fieldName = $item['name'];
        $fieldValue = $item['value'];
        
        $fieldNames[] = $fieldName;
        
        if (empty($fieldValue)) {
            $fieldPlaceholders[] = "$fieldName = NULL";
        } else {
            $fieldPlaceholders[] = "$fieldName = ?";
            $fieldValues[] = $fieldValue; // Only add non-empty values to be bound
        }
    }

    $updateStr = implode(", ", $fieldPlaceholders);

    $sql = "UPDATE Users SET $updateStr WHERE ID = ?"; // We use a placeholder for the ID too

    $stmt = mysqli_prepare($conn, $sql);

    // Append the UserID to the fieldValues for binding
    $fieldValues[] = $UserID;

    // Generate the types string for bind_param
    $types = str_repeat('s', count($fieldValues) - 1) . 'i'; // -1 because the last one is an integer (UserID)
    
    $stmt->bind_param($types, ...$fieldValues);

    $result = $stmt->execute();
    if (!$result) {
      die("Statement execution failed: " . mysqli_stmt_error($stmt));
    }

    if (!mysqli_stmt_error($stmt)) {
      foreach ($differences as $difference) {
        $LogActionText = "{$difference['field']} changed from {$difference['old']} to {$difference['new']}";
        createUserLogEntry($UserID, $UserSessionID, "1", $LogActionText);
      }
      $unitTypeTranslated = $functions->translate($temp);
      $msg = $unitTypeTranslated . " " . $functions->translate("updated");
      echo json_encode(["Result" => "success", "Message" => $msg]);
    } else {
      $errorMsg = mysqli_stmt_error($conn);
      $functions->errorlog("$unitTypeTranslated could not be updated. Error: $errorMsg", "updateUser");
      echo json_encode(["Result" => "error", "Message" => $errorMsg]);
    }
  }
}

if (isset($_GET['removeCompany'])) {
  if (in_array("100025", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100025");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $UserSessionID = $_SESSION["id"];
  $CompanyID = $_POST['CompanyID'];
  $AnonymizedEmail = $functions->generateRandomString(12) . "@anonymized";
  $CompanyNameString = "Anonymized".$functions->generateRandomString(6);

  $sql = "UPDATE companies
          SET CompanyName = '$CompanyNameString', WebPage = 'Anonymized', Phone = 'Anonymized', CustomerAccountNumber = 'Anonymized', Address = 'Anonymized', Email = '$AnonymizedEmail', CBR = 'Anonymized', Notes = 'Anonymized', Active = 3
          WHERE ID = $CompanyID";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  deleteCompanyLogs($CompanyID);
  deleteCompanyFiles($CompanyID);

  $LogActionText = "Removed";
  createCompanyLogEntry($CompanyID, $UserSessionID, "2", $LogActionText);

  $Array[] = array("Result" => "success");

  echo json_encode($Array);
}

if (isset($_GET['removeUser'])) {
  if (in_array("100026", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100026");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $UserSessionID = $_SESSION["id"];
  $UsersID = $_POST['UserID'];
  $AnonymizedEmail = $functions->generateRandomString(12) . "@anonymized";
  $AnonymizedUsername = $functions->generateRandomString(10) . "@anonymized";
  $AnonymizedSaltedPassword = $functions->SaltAndHashPasswordForCompare($functions->generateRandomString(26));
  $AnonymizedLastname = $functions->generateRandomString(6);
  $PicturePath = $functions->getProfilePicture($UsersID);
  if ($PicturePath !== "./uploads/images/profilepictures/default_user.png") {
    unlink($PicturePath);
  }

  $sql = "UPDATE users
          SET Firstname = 'Anonymized', Lastname = '$AnonymizedLastname', google_secret_code = '0Km#9kQyfI1CLkthWhDb#F', QRUrl = NULL, ZoomPersRoom = NULL, Username = '$AnonymizedUsername', Password = '$AnonymizedSaltedPassword', Birthday = NULL, ADUUID = NULL, Email = '$AnonymizedEmail', ProfilePicture = 'default_user.png', Phone = NULL, JobTitel = NULL, LinkedIn = NULL, Notes = NULL, Active = 3
          WHERE ID = $UsersID";
  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  deleteUserLogs($UsersID);
  deleteUserFiles($UsersID);

  $LogActionText = "Removed";
  createUserLogEntry($UsersID, $UserSessionID, "2", $LogActionText);

  $Array[] = array("Result" => "success");

  echo json_encode($Array);
}

if (isset($_GET['resolveITSM'])) {

  $UserSessionID = $_SESSION["id"];
  
  $ITSMID = $_POST['ITSMID'];
  
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $Solution = $_POST['Solution'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
  
  if(empty($FinishedStatus)){
    $ResolveStatus = $functions->getITSMClosedStatus($ITSMTypeID);
    $FinishedStatus = $ResolveStatus[0];
  }

  $sql = "UPDATE $ITSMTableName SET Status = ?, Solution = ? WHERE ID = ?";
  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    // Bind the parameters
    mysqli_stmt_bind_param($stmt, 'isi', $FinishedStatus, $Solution, $ITSMID);

    // Execute the statement
    mysqli_stmt_execute($stmt);

    // Check for errors during execution
    if (mysqli_stmt_errno($stmt)) {
      die('Query execution failed: ' . mysqli_stmt_error($stmt));
    }

    // Close the statement
    mysqli_stmt_close($stmt);
  } else {
    die('Prepared statement failed: ' . mysqli_error($conn));
  }

  // Update SLA Timeline on all missing statuscodes

  $FinishedStatus = getITSMFinishedStatus($ITSMTypeID);
  $SLAStatusCodesToCheck = getITSMStatusCodesBelowFinishedStatus($ITSMTypeID, $FinishedStatus);
  $SLAStatusCodesUpdated = getITSMStatusCodesAllreadyUpdated($ITSMTypeID, $ITSMID);

  // Remove the status codes from $SLAStatusCodesToCheck that exist in $SLAStatusCodesUpdated
  $SLAStatusCodesToCheck = array_diff($SLAStatusCodesToCheck, $SLAStatusCodesUpdated);

  foreach($SLAStatusCodesToCheck as $item){
    updateTimelineUpdatedDate($ITSMID, $ITSMTypeID, $item);
  }

  updateTimelineUpdatedDate($ITSMID, $ITSMTypeID, $FinishedStatus);

  $MailTemplateID = "2";
  $Description = "";
  $Value = $Solution;
  $Field = "";
  $PreValue = "";

  closeAllTasksAssociatedWithITSM($ITSMTypeID, $ITSMID);
  sendITSMNotificationMailToUsers($MailTemplateID, $ITSMTypeID, $ITSMID, $Description, $Field, $PreValue, $Value);

  // Lets log the activity
  $ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
  $Headline = $functions->translate("Solved") . " " . strtolower($ITSMTypeName) . " " . $ITSMID;
  $ActivityText = "<b>" . $functions->translate("Solved with solution") . "<br><br>" . $Solution;
  logActivity($ITSMID, $ITSMTypeID, $Headline, $ActivityText, "javascript:javascript:viewITSM('$ITSMID','$ITSMTypeID','1','modal');");
  
  $Array[] = array("Result" => "success");

  echo json_encode($Array);
}

if (isset($_GET['resetFormsData'])) {

  $UserSessionID = $_SESSION["id"];
  $FormID = $_GET['FormID'];
  $Array[] = array("Result" => "success");

  $FormsTableName = getFormsTableName($FormID);

  $sql = "DELETE FROM $FormsTableName;";
  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  $sql = "ALTER TABLE $FormsTableName AUTO_INCREMENT = 1";
  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  echo json_encode($Array);
}

if (isset($_GET['createFormFromTemplate'])) {

  $UserSessionID = $_SESSION["id"];
  $TemplateID = $_GET['TemplateID'];
  $FormsTableName = "formstable_" . $functions->generateRandomString(15);
  $ModuleQuery = getModuleTemplateSQL($TemplateID, "Module");
  $ModuleQuery = str_replace("<:TableName:>", $FormsTableName, $ModuleQuery);

  $result = mysqli_query($conn, $ModuleQuery) or die('Query fail: ' . mysqli_error($conn));

  $last_id = $conn->insert_id;

  $TableQuery = getModuleTemplateSQL($TemplateID, "Table");
  $TableQuery = str_replace("<:TableName:>", $FormsTableName, $TableQuery);
  $result = mysqli_query($conn, $TableQuery) or die('Query fail: ' . mysqli_error($conn));

  $FieldsQuery = getModuleTemplateSQL($TemplateID, "Fields");
  $FieldsQuery = str_replace("<:FormID:>", "$last_id", $FieldsQuery);

  $result = mysqli_query($conn, $FieldsQuery) or die('Query fail: ' . mysqli_error($conn));

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['getCertificateExpireDate'])) {
  $URL = $_GET['URL'];

  $ExpireDate = getCertificateExpireDate($URL);

  $Array[] = array("Result" => "$ExpireDate");
  echo json_encode($Array);
}

if (isset($_GET['updateCertificateEndDate'])) {
  $ciTypeId = $_GET['ciTypeId'];
  $ciId = $_GET['ciId'];
  $endDate = $_GET['endDate'];

  updateCertificateEndDate($ciTypeId, $ciId, $endDate);

  $Array[] = array("Result" => "End Date updated to: $endDate");
  echo json_encode($Array);
}

if (isset($_GET['executeQuery'])) {
  if (!in_array("100000", $UserGroups)) {
    echo "You are not allowed!";
    return;
  }

  $query = $_POST['query'];
  $DestinationDatabase = $_POST['DestinationDatabase'];

  // Establish database connection
  $conntemp = new mysqli("$dbservername", "$dbusername", "$dbpassword", "$DestinationDatabase");

  // Enable multi-query
  $conntemp->multi_query($query);

  // Check for errors during execution
  if ($conntemp->errno) {
    die('Query fail: ' . $conntemp->error);
  }

  $Array[] = array("Result" => "success");
  echo json_encode($Array);
}

if (isset($_GET['closeAllTasksAssociatedWithITSM'])) {
  if (!in_array("100000", $UserGroups)) {
    echo "You are not allowed!";
    return;
  }

  $ITSMTypeID = $_POST['ITSMTypeID'];
  $ITSMID = $_POST['ITSMID'];

  // Close all tasks by setting the status to 4
  $sql = "UPDATE taskslist SET Status = '4'
        WHERE RelatedElementTypeID = ? AND RelatedElementID = ?;";

  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "ss", $ITSMTypeID, $ITSMID);
  mysqli_stmt_execute($stmt);

  if (mysqli_stmt_affected_rows($stmt) > 0) {
    // Tasks were successfully updated
    $response = "Tasks closed successfully.";
  } else {
    // No tasks were updated
    $response = "No tasks found or error occurred.";
  }

  mysqli_stmt_close($stmt);

  echo $response;
}

if (isset($_GET['translate'])) {
  $text = $_GET['text']; // Get the individual text to be translated
  $translatedText = $functions->translate($text); // Call the 'translate' function for the individual text
  echo json_encode($translatedText); // Send the translated text as a JSON response
}

if (isset($_GET['checkFormsTableConsistency'])) {
  if (!in_array("100001", $UserGroups) || !in_array("100029", $UserGroups)) {
    $Array[] = array("Result" => "You are not allowed!");
    echo json_encode($Array);
    return;
  }
  $returnValue = "";

  $formID = $_GET['FormID'];
  $formsTableName = getFormsTableName($formID);

  $sql = "SELECT FieldName
          FROM forms_fieldslist
          WHERE RelatedFormID = '$formID';";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {

    $FieldName = $row['FieldName'];

    $tempSQL = "SELECT COUNT(*)
                FROM information_schema.columns
                WHERE table_schema = '$dbname'
                  AND table_name = '$formsTableName'
                  AND column_name = '$FieldName';";

    $resultTemp = mysqli_query($conn, $tempSQL) or die('Query fail: ' . mysqli_error($conn));

    if (mysqli_num_rows($resultTemp) == 0) {
      $returnValue .= "<br><details><summary>$FieldName was not found in $formsTableName</summary>";
      $returnValue .= '<pre onclick="copyToClipboard(this)">';
      $returnValue .= "ALTER TABLE $formsTableName
                      ADD $FieldName TEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL;";
      $returnValue .= '</pre>';
      $returnValue .= "<button onclick=\"executeQuery(this, '$dbname')\">Execute</button>";
      $returnValue .= '</details><br>';
    } else {
      $returnValue .= "$FieldName was found in $formsTableName<br>";
    }
  }

  $returnValue .= "<br>";

  $sql = "SHOW COLUMNS FROM $formsTableName WHERE Field LIKE 'FormField%';";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {

    $FieldName = $row[0];

    $tempSQL = "SELECT FieldName
                FROM forms_fieldslist
                WHERE RelatedFormID = $formID AND FieldName = '$FieldName';";

    $resultTemp = mysqli_query($conn, $tempSQL) or die('Query fail: ' . mysqli_error($conn));

    if (mysqli_num_rows($resultTemp) == 0) {
      $returnValue .= "<br><details><summary>$FieldName is not relevant</summary>";
      $returnValue .= '<pre onclick="copyToClipboard(this)">';
      $returnValue .= "ALTER TABLE $formsTableName
                      DROP COLUMN $FieldName;";
      $returnValue .= '</pre>';
      $returnValue .= "<button onclick=\"executeQuery(this, '$dbname')\">Execute</button>";
      $returnValue .= '</details><br>';
    } else {
      $returnValue .= "$FieldName in $formsTableName is relevant<br>";
    }
  }

  mysqli_free_result($result);

  $Array[] = array("Result" => "$returnValue");
  echo json_encode($Array);
}

if (isset($_GET['checkITSMTableConsistency'])) {
  if (!in_array("100001", $UserGroups) || !in_array("100031", $UserGroups)) {
    $Array[] = array("Result" => "You are not allowed!");
    echo json_encode($Array);
    return;
  }
  $returnValue = "";

  $ITSMTypeID = $_GET['ITSMTypeID'];
  $ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

  $sql = "SELECT FieldName
          FROM itsm_fieldslist
          WHERE RelatedTypeID = '$ITSMTypeID';";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {

    $FieldName = $row['FieldName'];

    $tempSQL = "SELECT COUNT(*)
                FROM information_schema.columns
                WHERE table_schema = '$dbname'
                  AND table_name = '$ITSMTableName'
                  AND column_name = '$FieldName';";

    $resultTemp = mysqli_query($conn, $tempSQL) or die('Query fail: ' . mysqli_error($conn));

    if (mysqli_num_rows($resultTemp) == 0) {
      $returnValue .= "<br><details><summary>$FieldName was not found in $ITSMTableName</summary>";
      $returnValue .= '<pre onclick="copyToClipboard(this)">';
      $returnValue .= "ALTER TABLE $ITSMTableName
                      ADD $FieldName TEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL;";
      $returnValue .= '</pre>';
      $returnValue .= "<button onclick=\"executeQuery(this, '$dbname')\">Execute</button>";
      $returnValue .= '</details><br>';
    } else {
      $returnValue .= "<small>$FieldName was found in $ITSMTableName</small><br>";
    }
  }

  $returnValue .= "<br>";

  $sql = "SHOW COLUMNS FROM $ITSMTableName WHERE Field NOT LIKE 'ID';";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {

    $FieldName = $row[0];

    $tempSQL = "SELECT FieldName
                FROM itsm_fieldslist
                WHERE RelatedTypeID = $ITSMTypeID AND FieldName = '$FieldName';";

    $resultTemp = mysqli_query($conn, $tempSQL) or die('Query fail: ' . mysqli_error($conn));

    if (mysqli_num_rows($resultTemp) == 0) {
      $returnValue .= "<br><details><summary>$FieldName is not relevant</summary>";
      $returnValue .= '<pre onclick="copyToClipboard(this)">';
      $returnValue .= "ALTER TABLE $ITSMTableName
                      DROP COLUMN $FieldName;";
      $returnValue .= '</pre>';
      $returnValue .= "<button onclick=\"executeQuery(this, '$dbname')\">Execute</button>";
      $returnValue .= '</details><br>';
    } else {
      $returnValue .= "<small>$FieldName: relevant</small><br>";
    }
  }

  mysqli_free_result($result);

  $Array[] = array("Result" => "$returnValue");
  echo json_encode($Array);
}

if (isset($_GET['checkCMDBTableConsistency'])) {
    // Check if user is authorized
  $requiredGroups = ["100001", "100031"];
  $functions->checkUserGroups($requiredGroups, $UserGroups);
  
  $returnValue = "";

  $CITypeID = $_GET["CITypeID"];
  $CITableName = getCITableName($CITypeID);

  $sql = "SELECT FieldName
          FROM cmdb_ci_fieldslist
          WHERE RelatedCITypeID = '$CITypeID';";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {

    $FieldName = $row['FieldName'];

    $tempSQL = "SELECT COUNT(*)
                FROM information_schema.columns
                WHERE table_schema = '$dbname'
                  AND table_name = '$CITableName'
                  AND column_name = '$FieldName';";
 
    $resultTemp = mysqli_query($conn, $tempSQL) or die('Query fail: ' . mysqli_error($conn));

    if (mysqli_num_rows($resultTemp) == 0) {
      $returnValue .= "<br><details><summary>$FieldName was not found in $CITableName</summary>";
      $returnValue .= '<pre onclick="copyToClipboard(this)">';
      $returnValue .= "ALTER TABLE $CITableName
                      ADD $FieldName TEXT CHARACTER SET utf8mb3 COLLATE utf8mb3_danish_ci DEFAULT NULL;";
      $returnValue .= '</pre>';
      $returnValue .= "<button onclick=\"executeQuery(this, '$dbname')\">Execute</button>";
      $returnValue .= '</details><br>';
    } else {
      $returnValue .= "<small>$FieldName was found in $CITableName</small><br>";
    }
  }

  $returnValue .= "<br>";

  $sql = "SHOW COLUMNS FROM $CITableName WHERE Field NOT LIKE 'ID';";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {

    $FieldName = $row[0];

    $tempSQL = "SELECT FieldName
                FROM cmdb_ci_fieldslist
                WHERE RelatedCITypeID = $CITypeID AND FieldName = '$FieldName';";

    $resultTemp = mysqli_query($conn, $tempSQL) or die('Query fail: ' . mysqli_error($conn));

    if (mysqli_num_rows($resultTemp) == 0) {
      $returnValue .= "<br><details><summary>$FieldName is not relevant</summary>";
      $returnValue .= '<pre onclick="copyToClipboard(this)">';
      $returnValue .= "ALTER TABLE $CITableName
                      DROP COLUMN $FieldName;";
      $returnValue .= '</pre>';
      $returnValue .= "<button onclick=\"executeQuery(this, '$dbname')\">Execute</button>";
      $returnValue .= '</details><br>';
    } else {
      $returnValue .= "<small>$FieldName: relevant</small><br>";
    }
  }

  mysqli_free_result($result);

  $Array[] = array("Result" => "$returnValue");
  echo json_encode($Array);
}

if (isset($_GET['getLanguageEntries'])) {
  $UserType = $_SESSION['usertype'];
  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $sql = "SELECT ID, MainLanguage, da_DK, es_ES, de_DE, fr_FR, fi_FI, it_IT, tr_TR, zh_CN, ru_RU, ja_JP, pt_PT
        FROM languages;";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
  while ($row = mysqli_fetch_array($result)) {
    $ID = $row['ID'];
    $BtnEdit = "<a href=\"javascript:editLanguageEntry($ID);\"><span class='badge badge-pill bg-gradient-secondary'><i class='fas fa-pencil-alt'></i></span></a>";
    $BtnDelete = "<a href=\"javascript:deleteLanguageEntry($ID);\"><span class='badge badge-pill bg-gradient-danger'><i class=\"fa-solid fa-trash\"></i></span></a>";
    $MainLanguage = $row['MainLanguage'];
    $da_DK = $row['da_DK'];

    $Array[] = array(
      "ID" => $ID,
      "Edit" => $BtnEdit,
      "Delete" => $BtnDelete,
      "MainLanguage" => $MainLanguage,
      "danish" => $da_DK
    );
  }

  if ($Array) {
    echo json_encode($Array, JSON_PRETTY_PRINT);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['getLicenceEntries'])) {
  $UserType = $_SESSION['usertype'];
  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $sql = "SELECT `ID`, `Firstname`, `Lastname`, `Email`, `Country`, `Company`, `LicenceKey`, `LicenceInfo`, `LicenceStats`, `LicenceLastVerified`, `LicenceRegistered` FROM `licences`;";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
  while ($row = mysqli_fetch_array($result)) {
    $ID = $row['ID'];
    $BtnEdit = "<a href=\"javascript:editLicenceEntry($ID);\"><span class='badge badge-pill bg-gradient-secondary'><i class='fas fa-pencil-alt'></i></span></a>";
    $BtnDelete = "<a href=\"javascript:deleteLicenceEntry($ID);\"><span class='badge badge-pill bg-gradient-danger'><i class=\"fa-solid fa-trash\"></i></span></a>";
    $Company = $row['Company'];
    $LicenceKey = $row['LicenceKey'];
    $LicenceRegistered = $row['LicenceRegistered'];
    $Person = $row['Firstname'] . " " . $row['Lastname'] . " (" . $row['Email'] . ")";

    $Array[] = array(
      "ID" => $ID,
      "Edit" => $BtnEdit,
      "Delete" => $BtnDelete,
      "Company" => $Company,
      "LicenceKey" => $LicenceKey,
      "LicenceRegistered" => $LicenceRegistered,
      "Person" => $Person
    );
  }

  if ($Array) {
    echo json_encode($Array, JSON_PRETTY_PRINT);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['getLicenceFields'])) {
    $UserType = $_SESSION['usertype'];
    if ($UserType == "2") {
        echo json_encode([]);
        return;
    }

    // Run query to fetch one row to determine the table structure
    $sql = "SELECT * FROM licences LIMIT 1;";
    $result = mysqli_query($conn, $sql) or die('Query failed: ' . mysqli_error($conn));

    // Check if the table has rows
    if ($result && mysqli_num_rows($result) > 0) {
        // Fetch field metadata from the result set
        $fields = [];
        $fieldInfo = mysqli_fetch_fields($result);

        foreach ($fieldInfo as $field) {
            // Dynamically determine input type based on the field type
            $inputType = 'text'; // Default input type

            switch ($field->type) {
                case MYSQLI_TYPE_DECIMAL:
                case MYSQLI_TYPE_NEWDECIMAL:
                case MYSQLI_TYPE_FLOAT:
                case MYSQLI_TYPE_DOUBLE:
                case MYSQLI_TYPE_LONG:
                case MYSQLI_TYPE_INT24:
                case MYSQLI_TYPE_SHORT:
                case MYSQLI_TYPE_TINY:
                    $inputType = 'number';
                    break;

                case MYSQLI_TYPE_DATE:
                case MYSQLI_TYPE_DATETIME:
                case MYSQLI_TYPE_TIMESTAMP:
                    $inputType = 'date';
                    break;

                case MYSQLI_TYPE_STRING:
                case MYSQLI_TYPE_VAR_STRING:
                case MYSQLI_TYPE_BLOB:
                    $inputType = 'text';
                    break;

                case MYSQLI_TYPE_TIME:
                case MYSQLI_TYPE_YEAR:
                    $inputType = 'text'; // Adjust as needed
                    break;

                default:
                    $inputType = 'text';
            }

            $fieldDisabled = "";
            $fieldHidden = "";
            if ($field->name == "ID") {
              continue;
            }

            $fields[] = [
                "id" => $field->name,
                "name" => $field->name,
                "type" => $inputType,
                "fieldWidth" => "6",
                "fieldValue" => "",
                "fieldTitle" => "",
                "fieldDisabled" => "$fieldDisabled",
                "fieldHidden" => "$fieldHidden"
            ];
        }

        // Return field definitions as JSON
        echo json_encode($fields, JSON_PRETTY_PRINT);
    } else {
        echo json_encode([]);
    }
}

if (isset($_GET['generateLanguageFiles'])) {

  $languagearray = ["da_DK", "de_DE", "es_ES", "fr_FR", "fi_FI", "it_IT", "tr_TR", "zh_CN", "ru_RU", "ja_JP", "pt_PT"];

  foreach ($languagearray as $countrycode) {

    $sql = "SELECT MainLanguage, $countrycode
				    FROM languages";

    $header = "msgid \"\"
msgstr \"\"" . "\n" .
      '"' . "Project-Id-Version: Practicle" . '\n' . '"' . "\n" .
      '"' . "POT-Creation-Date: 2019-05-16 11:25+0200" . '\n' . '"' . "\n" .
      '"' . "PO-Revision-Date: 2019-07-11 13:00+0200" . '\n' . '"' . "\n" .
      '"' . "Last-Translator: " . '\n' . '"' . "\n" .
      '"' . "Language-Team: " . '\n' . '"' . "\n" .
      '"' . "MIME-Version: 1.0" . '\n' . '"' . "\n" .
      '"' . "Content-Type: text/plain; charset=UTF-8" . '\n' . '"' . "\n" .
      '"' . "Content-Transfer-Encoding: 8bit" . '\n' . '"' . "\n" .
      '"' . "X-Generator: Poedit 2.2.3" . '\n' . '"' . "\n" .
      '"' . "X-Poedit-Basepath: ." . '\n' . '"' . "\n" .
      '"' . "Plural-Forms: nplurals=2; plural=(n != 1);" . '\n' . '"' . "\n" .
      '"' . "Language: $countrycode" . '\n' . '"' . "\n" .
      '"' . "X-Poedit-SourceCharset: UTF-8" . '\n' . '"';
    $String = "";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    while ($row = mysqli_fetch_array($result)) {
      $Main = trim($row['MainLanguage']);
      $Country = trim($row[$countrycode]);
      $Temp = "msgid " . '"' . $Main . '"' . "\n" . "msgstr " . '"' . $Country . '"' . "\n\n";
      $String .= "msgid " . '"' . $Main . '"' . "\n" . "msgstr " . '"' . $Country . '"' . "\n\n";
    }

    $filecontent = $header . "\n\n" . $String;
    $dir = dirname(__FILE__);
    $dir = $dir . "/locales/" . $countrycode . "/LC_MESSAGES/";
    $file = "main.po";

    // Ensure directory exists or create it
    $dir = dirname(__FILE__) . "/locales/" . $countrycode . "/LC_MESSAGES/";
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true); // Recursive directory creation
    }
    
    // Write the contents back to the file
    unlink($dir . $file);
    unlink($dir . "main.mo");
    file_put_contents($dir . $file, $filecontent);
    $commandGenerateLanguageFile = "msgfmt " . $dir . "main.po --output-file=" . $dir . "main.mo";
    exec("$commandGenerateLanguageFile");
  }
  echo "<script type='text/javascript'>
	$(document).ready(function(e) {
		pnotify('Language files generated','success');
	});
	</script>";
  if ($dbname == "practicle_practicle") {
    $sql = "TRUNCATE TABLE practicle_base.languages;";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "INSERT INTO practicle_base.languages
          SELECT *
          FROM languages;";

    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
  }

  sleep(2);
  $commandApacheRestart = "systemctl restart php8.3-fpm.service";
  exec("$commandApacheRestart");
  $functions->errorlog("LanguageFiles did not generate, tried $commandGenerateLanguageFile", "generateLanguageFiles");
}

if (isset($_GET['getFormFields'])) {
  $UserType = $_SESSION['usertype'];
  $FormID = $_POST['FormID'];
  $TableName = "forms_fieldslist";

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $sql = "SELECT forms_fieldslist.ID, forms_fieldslist.FieldName, forms_fieldslist.RelatedFormID, forms_fieldslist_types.TypeName, 
			forms_fieldslist.FieldOrder, forms_fieldslist.FieldLabel
			FROM forms_fieldslist
			LEFT JOIN forms_fieldslist_types ON forms_fieldslist.FieldType = forms_fieldslist_types.ID
			WHERE RelatedFormID = $FormID
			ORDER BY FieldOrder ASC";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
  while ($row = mysqli_fetch_array($result)) {
    $FieldID = $row['ID'];
    $FieldName = $row['FieldName'];
    $FieldType = $row['TypeName'];
    $RelatedFormID = $row['RelatedFormID'];
    $FieldOrder = $row['FieldOrder'];
    $FieldLabel = $row['FieldLabel'];

    $EditField =  "<a href=\"javascript:setFormFieldOrderUp('$TableName','$FormID','$FieldOrder','$UserLanguageCode');\"><i class=\"fa-solid fa-arrow-down\"></i></i></a>
    <a href=\"javascript:setFormFieldOrderDown('$TableName','$FormID','$FieldOrder','$UserLanguageCode');\"><i class=\"fa-solid fa-arrow-up\"></i></a>&nbsp&nbsp<a href=\"javascript:editFormFieldEntry('$FieldID');\"><span class=\"badge bg-gradient-info\"><i class=\"fa fa-pencil-alt\"></i></span></a>";

    $DeleteField = "<a href=\"javascript:deleteFormField('$FieldID','$FormID','$UserLanguageCode');\"><span class=\"badge bg-gradient-danger\"><i class=\"fa fa-trash\"></i></span></a>";

    $EditField = $EditField . $DeleteField;

    $Array[] = array("" => $EditField, $functions->translate("Field Name") => "$FieldName", $functions->translate("Field Label") => $FieldLabel, $functions->translate("Type") => $FieldType);
  }

  if ($Array) {
    echo json_encode($Array, JSON_PRETTY_PRINT);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['getITSMFields'])) {
  $UserType = $_SESSION['usertype'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $TableName = "itsm_fieldslist";

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $sql = "SELECT itsm_fieldslist.ID, itsm_fieldslist.FieldName, itsm_fieldslist.RelatedTypeID, itsm_fieldslist_types.TypeName, 
			itsm_fieldslist.FieldOrder, itsm_fieldslist.FieldLabel, itsm_fieldslist.DefaultField, itsm_fieldslist.RelationShowField
			FROM itsm_fieldslist
			LEFT JOIN itsm_fieldslist_types ON itsm_fieldslist.FieldType = itsm_fieldslist_types.ID
			WHERE RelatedTypeID = $ITSMTypeID
			ORDER BY FieldOrder ASC";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
  while ($row = mysqli_fetch_array($result)) {
    $FieldID = $row['ID'];
    $FieldName = $row['FieldName'];
    $FieldType = $row['TypeName'];
    $RelatedITSMTypeID = $row['RelatedTypeID'];
    $FieldOrder = $row['FieldOrder'];
    $FieldLabel = $row['FieldLabel'];
    $DefaultField = $row['DefaultField'];
    $RelationShowField = $row['RelationShowField'];

    $EditField =  "<a href=\"javascript:setITSMieldOrderUp('$TableName','$ITSMTypeID','$FieldOrder');\"><i class=\"fa-solid fa-arrow-down\"></i></i></a>
    <a href=\"javascript:setITSMieldOrderDown('$TableName','$ITSMTypeID','$FieldOrder');\"><i class=\"fa-solid fa-arrow-up\"></i></a>&nbsp&nbsp<a href=\"javascript:editITSMFieldEntry('$FieldID');\"><span class=\"badge bg-gradient-info\"><i class=\"fa fa-pencil-alt\"></i></span></a>";
    if ($DefaultField == '0') {
      $DeleteField = "<a href=\"javascript:deleteITSMField('$FieldID','$UserLanguageCode');\"><span class=\"badge bg-gradient-danger\"><i class=\"fa fa-trash\"></i></span></a>";
    } else {
      $DeleteField = "";
    }
    $EditField = $EditField . $DeleteField;

    $Array[] = array("" => $EditField, $functions->translate("Field Name") => "$FieldName", $functions->translate("Field Label") => $FieldLabel, $functions->translate("Type") => $FieldType, $functions->translate('Primary') => $RelationShowField);
  }

  if ($Array) {
    echo json_encode($Array, JSON_PRETTY_PRINT);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['getCIFields'])) {
  $UserType = $_SESSION['usertype'];
  $CITypeID = $_POST['CITypeID'];
  $TableName = "cmdb_ci_fieldslist";

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $sql = "SELECT cmdb_ci_fieldslist.ID, cmdb_ci_fieldslist.FieldName, cmdb_ci_fieldslist.RelatedCITypeID, cmdb_fieldslist_types.TypeName, 
			cmdb_ci_fieldslist.FieldOrder, cmdb_ci_fieldslist.FieldLabel, cmdb_ci_fieldslist.DefaultField, cmdb_ci_fieldslist.RelationShowField, cmdb_ci_fieldslist.Addon
			FROM cmdb_ci_fieldslist
			LEFT JOIN cmdb_fieldslist_types ON cmdb_ci_fieldslist.FieldType = cmdb_fieldslist_types.ID
			WHERE RelatedCITypeID = $CITypeID
			ORDER BY FieldOrder ASC";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
  while ($row = mysqli_fetch_array($result)) {
    $FieldID = $row['ID'];
    $FieldName = $row['FieldName'];
    $FieldType = $row['TypeName'];
    $RelatedCITypeID = $row['RelatedTypeID'];
    $FieldOrder = $row['FieldOrder'];
    $FieldLabel = $row['FieldLabel'];
    $DefaultField = $row['DefaultField'];
    $RelationShowField = $row['RelationShowField'];

    $EditField =  "<a href=\"javascript:setCMDBFieldOrderUp('$TableName','$CITypeID','$FieldOrder');\"><i class=\"fa-solid fa-arrow-down\"></i></i></a>
    <a href=\"javascript:setCMDBFieldOrderDown('$TableName','$CITypeID','$FieldOrder');\"><i class=\"fa-solid fa-arrow-up\"></i></a>&nbsp&nbsp<a href=\"javascript:editCMDBFieldEntry('$FieldID');\"><span class=\"badge bg-gradient-info\"><i class=\"fa fa-pencil-alt\"></i></span></a>";
    if ($DefaultField == '0') {
      $DeleteField = "<a href=\"javascript:deleteCIField('$FieldID','$FieldName','$UserLanguageCode');\"><span class=\"badge bg-gradient-danger\"><i class=\"fa fa-trash\"></i></span></a>";
    } else {
      $DeleteField = "";
    }
    $EditField = $EditField . $DeleteField;

    $Array[] = array("" => $EditField, $functions->translate("Field Name") => "$FieldName", $functions->translate("Field Label") => $FieldLabel, $functions->translate("Type") => $FieldType, $functions->translate('Primary') => $RelationShowField);
  }

  if ($Array) {
    echo json_encode($Array, JSON_PRETTY_PRINT);
  } else {
    echo json_encode([]);
  }
}

if (isset($_GET['setITSMieldOrderUp'])) {
  $UserType = $_SESSION['usertype'];

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $TableName = $_POST['TableName'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $FieldOrder = intval($_POST['FieldOrder']);

  // Get FieldID from recieved FieldOrder
  $FieldID = getFieldIDFromFieldOrder($TableName, $ITSMTypeID, $FieldOrder);

  // Get Next Field Order by incrementing by 1
  $NextFieldOrder = intval($FieldOrder + 1);

  // Get FieldID from recieved NextFieldOrder
  $NextFieldID = getFieldIDFromFieldOrder($TableName, $ITSMTypeID, $NextFieldOrder);

  updateFieldOrder($TableName, $FieldID, $NextFieldOrder);
  updateFieldOrder($TableName, $NextFieldID, $FieldOrder);

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['setITSMieldOrderDown'])) {
  $UserType = $_SESSION['usertype'];

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $TableName = $_POST['TableName'];
  $ITSMTypeID = $_POST['ITSMTypeID'];
  $FieldOrder = intval($_POST['FieldOrder']);
  if ($FieldOrder == 1) {
    return;
  }

  // Get FieldID from recieved FieldOrder
  $FieldID = getFieldIDFromFieldOrder($TableName, $ITSMTypeID, $FieldOrder);

  // Get Next Field Order by incrementing by 1
  $NextFieldOrder = intval($FieldOrder - 1);

  // Get FieldID from recieved NextFieldOrder
  $NextFieldID = getFieldIDFromFieldOrder($TableName, $ITSMTypeID, $NextFieldOrder);

  updateFieldOrder($TableName, $FieldID, $NextFieldOrder);
  updateFieldOrder($TableName, $NextFieldID, $FieldOrder);

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['setFormFieldOrderUp'])) {
  $UserType = $_SESSION['usertype'];

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $TableName = $_POST['TableName'];
  $FormID = $_POST['FormID'];
  $FieldOrder = intval($_POST['FieldOrder']);

  // Get FieldID from recieved FieldOrder
  $FieldID = getFormFieldIDFromFieldOrder($TableName, $FormID, $FieldOrder);

  // Get Next Field Order by incrementing by 1
  $NextFieldOrder = intval($FieldOrder + 1);

  // Get FieldID from recieved NextFieldOrder
  $NextFieldID = getFormFieldIDFromFieldOrder($TableName, $FormID, $NextFieldOrder);

  updateFieldOrder($TableName, $FieldID, $NextFieldOrder);
  updateFieldOrder($TableName, $NextFieldID, $FieldOrder);

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['setFormFieldOrderDown'])) {
  $UserType = $_SESSION['usertype'];

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $TableName = $_POST['TableName'];
  $FormID = $_POST['FormID'];
  $FieldOrder = intval($_POST['FieldOrder']);
  if ($FieldOrder == 1) {
    return;
  }

  // Get FieldID from recieved FieldOrder
  $FieldID = getFormFieldIDFromFieldOrder($TableName, $FormID, $FieldOrder);

  // Get Next Field Order by incrementing by 1
  $NextFieldOrder = intval($FieldOrder - 1);

  // Get FieldID from recieved NextFieldOrder
  $NextFieldID = getFormFieldIDFromFieldOrder($TableName, $FormID, $NextFieldOrder);

  updateFieldOrder($TableName, $FieldID, $NextFieldOrder);
  updateFieldOrder($TableName, $NextFieldID, $FieldOrder);

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['setCMDBFieldOrderUp'])) {
  $UserType = $_SESSION['usertype'];

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $TableName = $_POST['TableName'];
  $CITypeID = $_POST['CITypeID'];
  $FieldOrder = intval($_POST['FieldOrder']);

  // Get FieldID from recieved FieldOrder
  $FieldID = getCIFieldIDFromFieldOrder($TableName, $CITypeID, $FieldOrder);

  // Get Next Field Order by incrementing by 1
  $NextFieldOrder = intval($FieldOrder + 1);

  // Get FieldID from recieved NextFieldOrder
  $NextFieldID = getCIFieldIDFromFieldOrder($TableName, $CITypeID, $NextFieldOrder);
  updateFieldOrder($TableName, $FieldID, $NextFieldOrder);
  updateFieldOrder($TableName, $NextFieldID, $FieldOrder);

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['setCMDBFieldOrderDown'])) {
  $UserType = $_SESSION['usertype'];

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $TableName = $_POST['TableName'];
  $CITypeID = $_POST['CITypeID'];
  $FieldOrder = intval($_POST['FieldOrder']);
  if ($FieldOrder == 1) {
    return;
  }

  // Get FieldID from recieved FieldOrder
  $FieldID = getCIFieldIDFromFieldOrder($TableName, $CITypeID, $FieldOrder);
 
  // Get Next Field Order by incrementing by 1
  $NextFieldOrder = intval($FieldOrder - 1);

  // Get FieldID from recieved NextFieldOrder
  $NextFieldID = getCIFieldIDFromFieldOrder($TableName, $CITypeID, $NextFieldOrder);

  updateFieldOrder($TableName, $FieldID, $NextFieldOrder);
  updateFieldOrder($TableName, $NextFieldID, $FieldOrder);

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['setParentTaskIdOnTask'])) {
  $UserType = $_SESSION['usertype'];

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $taskId = $_POST['taskId'];
  $parentTaskId = $_POST['parentTaskId'];

  updateParentTaskIDOnTask($taskId, $parentTaskId);

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['createTestCMDB'])) {
  if (!in_array("100000", $UserGroups)) {
    $Array[] = array("Result" => "You are not allowed!");
    echo json_encode($Array);
    return;
  }

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  createTestCMDB();

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['getCVEEntries'])) {
  if (in_array("100020", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100020");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("Result" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $days = isset($_POST["days"]) ? (string) $_POST["days"] : "";

  getCVEEntries($days);

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['removeAllCVEEntries'])) {
  if (in_array("100020", $UserGroups) || in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100020");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("Result" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  removeAllCVEEntries();

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['resetCMDB'])) {
  if (!in_array("100000", $UserGroups)) {
    $Array[] = array("Result" => "You are not allowed!");
    echo json_encode($Array);
    return;
  }

  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $Result = resetCMDB();
  if($Result == "Completed"){
    $Array[] = array("Result" => "success");
  } else{
    $Array[] = array("Result" => "failed");
  }

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['submitWizzard'])) {
  $SettingArray[] = array();

  if (!in_array("100001", $UserGroups)) {
    $Array[] = array("Result" => "You are not allowed!");
    echo json_encode($Array);
    return;
  }
  $wizzardData = $_POST['WizzardForm'];

  // Process the form data and update the database
  foreach ($wizzardData as $field) {
    $fieldName = $field['name'];
    $fieldValue = $field['value'];

    switch ($fieldName) {
      case 'SystemName':
        $SettingArray[] = array("ID" => "13", "Value" => "$fieldValue");
        break;

      case 'SystemURL':
        $SettingArray[] = array("ID" => "17", "Value" => "$fieldValue");
        $SettingArray[] = array("ID" => "25", "Value" => "$fieldValue");
        break;

      case 'DefaultDesign':
        $SettingArray[] = array("ID" => "20", "Value" => "$fieldValue");
        break;

      case 'GoogleAuth':
        $SettingArray[] = array("ID" => "31", "Value" => "$fieldValue");
        break;

      case 'smtpPort':
        $SettingArray[] = array("ID" => "26", "Value" => "$fieldValue");
        break;

      case 'SMTPHost':
        $SettingArray[] = array("ID" => "28", "Value" => "$fieldValue");
        break;

      case 'SMTPUsername':
        $SettingArray[] = array("ID" => "29", "Value" => "$fieldValue");
        break;

      case 'SMTPPassword':
        $SettingArray[] = array("ID" => "30", "Value" => "$fieldValue");
        break;

      case 'IMAPHost':
        $SettingArray[] = array("ID" => "38", "Value" => "$fieldValue");
        break;

      case 'IMAPUsername':
        $SettingArray[] = array("ID" => "39", "Value" => "$fieldValue");
        break;

      case 'IMAPPassword':
        $SettingArray[] = array("ID" => "40", "Value" => "$fieldValue");
        break;

      case 'APIToken':
        $SettingArray[] = array("ID" => "36", "Value" => "$fieldValue");
        break;

      case 'FBLink':
        $SettingArray[] = array("ID" => "45", "Value" => "$fieldValue");
        break;

      case 'CPLink':
        $SettingArray[] = array("ID" => "46", "Value" => "$fieldValue");
        break;

      case 'LinkedInLink':
        $SettingArray[] = array("ID" => "47", "Value" => "$fieldValue");
        break;

      case 'UserViaEmail':
        $SettingArray[] = array("ID" => "49", "Value" => "$fieldValue");
        break;

      case 'defaultCompany':
        $SettingArray[] = array("ID" => "48", "Value" => "$fieldValue");
        break;

      default:
        break;
    }
  }

  $updateQuery = "UPDATE settings SET SettingValue = ? WHERE ID = ?";
  $stmt = $conn->prepare($updateQuery);

  foreach ($SettingArray as $setting) {
    $settingID = $setting['ID'];
    $settingValue = $setting['Value'];

    // Prepare the statement
    $stmt = $conn->prepare($updateQuery);

    // Bind the parameters
    $stmt->bind_param("si", $settingValue, $settingID);

    // Execute the statement
    $stmt->execute();

    // Close the statement
    $stmt->close();
  }

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['generateRandomRows'])) {
  if (!in_array("100000", $UserGroups)) {
    $Array[] = array("Result" => "You are not allowed!");
    echo json_encode($Array);
    return;
  }

  $tableName = $_GET['tableName'];
  $numberOfRows = $_GET['numberOfRows'];
  insertRandomRows($tableName, $numberOfRows);

  $Array[] = array("Result" => "success");

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['getActivities'])) {
  // Retrieve the necessary parameters from the DataTables request
  $start = $_POST['start'] ?? 0;
  $length = $_POST['length'] ?? 25;
  $draw = $_POST['draw'] ?? 1;

  // Construct the SQL query to fetch the total number of records
  $countSql = "SELECT COUNT(*) AS total FROM activitystream";
  $countResult = mysqli_query($conn, $countSql) or die('Count query fail: ' . mysqli_error($conn));
  $countRow = mysqli_fetch_assoc($countResult);
  $totalRecords = $countRow['total'];

  // Construct the SQL query to fetch the data with pagination
  $sql = "SELECT activitystream.ID, Headline, Text, Date, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName, Url
            FROM activitystream
            LEFT JOIN users ON activitystream.UserID = users.ID
            ORDER BY Date DESC
            LIMIT $start, $length";

  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  // Prepare the response
  $response = array(
    'draw' => $draw, // Pass the draw parameter back to the client
    'recordsTotal' => $totalRecords, // Total number of records in the database
    'recordsFiltered' => $totalRecords, // Total number of records after applying filters (same as total in this case)
    'data' => array() // Array to hold the data for the current page
  );

  // Fetch rows and populate the response data
  while ($row = mysqli_fetch_assoc($result)) {
    $response['data'][] = $row;
  }

  // Send the response as JSON
  echo json_encode($response);
}

if (isset($_GET['getAllTimeRegistrations'])) {
  $UserID = $_SESSION['id'];
  // Get the week offset from the URL parameter (default to 0 if not provided)
  $weekOffset = isset($_GET['weekOffset']) ? intval($_GET['weekOffset']) : 0;

  // Calculate the start date of the requested week based on the week offset
  $today = new DateTime();
  $startOfWeek = new DateTime();
  $startOfWeek->setISODate($today->format('Y'), $today->format('W'));
  $startOfWeek->modify("+$weekOffset week");

  $timeRegistrations = getAllTimeRegistrations($weekOffset);
  $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
  $data = array();

  foreach ($timeRegistrations as $row) {
    $UserName = $row["UserName"];
    $ProfilePopOver = $row["ProfilePopOver"];
    $rowData = array($ProfilePopOver . ' (' . $UserName . ')');

    foreach ($daysOfWeek as $day) {
      $TotalHours = $row[$day] ?? '';
      $rowData[] = $TotalHours;
    }

    $data[] = $rowData;
  }

  echo json_encode($data);
}

if (isset($_GET['getWeekNumber'])) {
  $weekOffset = $_GET['weekOffset'];
  $today = new DateTime();
  $startOfWeek = new DateTime();
  $startOfWeek->setISODate($today->format('Y'), $today->format('W'));
  $startOfWeek->modify("-$weekOffset week");
  $weekNumber = $startOfWeek->format('W');
  echo $weekNumber;
  exit;
}

if (isset($_GET['restoreSelectedBackup'])) {
  $backupToRestore = $_GET['backupToRestore'];

  $Result = restoreBackup($backupToRestore);

  if($Result == true){
    $resultArray[] = array("Result" => "success");
    echo json_encode($resultArray);
  } else {
    $resultArray[] = array("Result" => $Result);
    echo json_encode($resultArray);
  }
}

if (isset($_GET['deleteSelectedBackup'])) {
  $backupToDelete = $_GET['backupToDelete'];

  $Result = deleteBackup($backupToDelete);

  if ($Result == true) {
    $resultArray[] = array("Result" => "success");
    echo json_encode($resultArray);
  } else {
    $resultArray[] = array("Result" => $Result);
    echo json_encode($resultArray);
  }
}

if (isset($_GET['submitProblemReportForm'])) {
  if (in_array("100001", $UserGroups)) {
  } else {
    $GroupName = getUserGroupName("100014");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  $emailContent = $_POST['emailContent'];
  $SystemName = $functions->getSettingValue("13");
  $SystemURL = $functions->getSettingValue("17");
  $UserID = $_SESSION['id'];
  $UserEmail = $_SESSION['email'];
  $UserFullName = $_SESSION['fullname'];
  $Subject = "[Practicle Problem report] from $SystemName";
  $Content = "($SystemURL).<br><br>Reporter:<br>$UserFullName ($UserEmail)<br><br>Problem:<br>$emailContent";
  $Content2 = "This is a problem sent via system:<br>$SystemName ($SystemURL).<br><br>Reporter:<br>$UserFullName ($UserEmail)<br><br>Problem:<br>$emailContent";
  sendMailToSinglePerson("support@practicle.dk", "Practicle Support", $Subject, $Content);
  sendMailToSinglePerson($UserEmail, $UserFullName, $Subject, $Content2);

  $resultArray[] = array("Result" => "success");
  echo json_encode($resultArray);

}

if (isset($_GET['createNewsArticle'])) {
  // Check if user is authorized
  if (!in_array("100014", $UserGroups) && !in_array("100001", $UserGroups)) {
    $GroupName = getUserGroupName("100014");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  // Get data from POST request
  $ModalNewsCategory = $_POST['ModalNewsCategory'];
  $ModalNewsWriter = $_POST['ModalNewsWriter'];
  $ModalDateCreated = date('Y-m-d H:i:s', strtotime($_POST['ModalDateCreated'])); // Convert to SQL datetime format
  $ModalNewsHeadline = $_POST['ModalNewsHeadline'];
  $ModalNewsContent = $_POST['ModalNewsContent'];
  $ModalActive = $_POST['ModalActive'];
  $CreatedByUserID = $_SESSION['id'];

  $sql = "INSERT INTO news (Headline, Content, CreatedByUserID, NewsWriter, DateCreated, RelatedCategory, Active) VALUES (?, ?, ?, ?, ?, ?, ?)";
  // Prepare an insert statement
  $stmt = $conn->prepare($sql);

  // Bind parameters
  $stmt->bind_param("ssiissi", $ModalNewsHeadline, $ModalNewsContent, $CreatedByUserID, $ModalNewsWriter, $ModalDateCreated, $ModalNewsCategory, $ModalActive);

  // Execute the statement
  if ($stmt->execute()) {
    $resultArray[] = array("Result" => "success");
    echo json_encode($resultArray);
  } else {
    // Handle errors
    $array[] = array("error" => "An error occurred while inserting the news article.");
    echo json_encode($array);
  }
}

if (isset($_GET['createNewsCategory'])) {
  // Check if user is authorized
  if (!in_array("100014", $UserGroups) && !in_array("100001", $UserGroups)) {
    $GroupName = getUserGroupName("100014");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  // Get data from POST request
  $newCategoryName = $_POST['newCategoryName'];
  $UserNewsUserGroup = $_POST['UserNewsUserGroup'];
  $UserNewsCategoryRole = $_POST['UserNewsCategoryRole'];
  $CreatedByUserID = $_SESSION['id'];

  $sql = "INSERT INTO `news_categories`(`Name`, `RelatedGroupID`, `RelatedRole`, `Active`) VALUES (?,?,?,1)";
  // Prepare an insert statement
  $stmt = $conn->prepare($sql);

  // Bind parameters
  $stmt->bind_param("sii", $newCategoryName, $UserNewsUserGroup, $UserNewsCategoryRole);

  // Execute the statement
  if ($stmt->execute()) {
    $resultArray[] = array("Result" => "success");
    echo json_encode($resultArray);
  } else {
    // Handle errors
    $array[] = array("error" => "An error occurred while inserting the news article.");
    echo json_encode($array);
  }
}

if (isset($_GET['addNewsCVEFilter'])) {
  // Check if user is authorized
  if (!in_array("100014", $UserGroups) && !in_array("100001", $UserGroups)) {
    $GroupName = getUserGroupName("100014");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  // Get data from POST request
  $newCVEFilter = $_POST['newCVEFilter'];

  $sql = "INSERT INTO `news_cve_filters`(`Product`) VALUES (?)";
  // Prepare an insert statement
  $stmt = $conn->prepare($sql);

  // Bind parameters
  $stmt->bind_param("s", $newCVEFilter);

  // Execute the statement
  if ($stmt->execute()) {
    $resultArray[] = array("Result" => "success");
    echo json_encode($resultArray);
  } else {
    // Handle errors
    $array[] = array("error" => "An error occurred while inserting the news article.");
    echo json_encode($array);
  }
}

if (isset($_GET['updateNewsArticle'])) {
  // Check if user is authorized
  if (!in_array("100014", $UserGroups) && !in_array("100001", $UserGroups)) {
    $GroupName = getUserGroupName("100014");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  // Get data from POST request
  $NewsID = $_POST['NewsID']; // Assuming NewsID is being passed
  $ModalNewsCategory = $_POST['ModalNewsCategory'];
  $ModalNewsWriter = $_POST['ModalNewsWriter'];
  $ModalDateCreated = date('Y-m-d H:i:s', strtotime($_POST['ModalDateCreated'])); // Convert to SQL datetime format
  $ModalNewsHeadline = $_POST['ModalNewsHeadline'];
  $ModalNewsContent = $_POST['ModalNewsContent'];
  $ModalActive = $_POST['ModalActive'];
  $CreatedByUserID = $_SESSION['id'];

  // SQL UPDATE statement to modify the existing record
  $sql = "UPDATE news SET Headline = ?, Content = ?, CreatedByUserID = ?, NewsWriter = ?, DateCreated = ?, RelatedCategory = ?, Active = ? WHERE ID = ?";

  // Prepare the update statement
  $stmt = $conn->prepare($sql);

  // Bind parameters
  $stmt->bind_param("ssiissii", $ModalNewsHeadline, $ModalNewsContent, $CreatedByUserID, $ModalNewsWriter, $ModalDateCreated, $ModalNewsCategory, $ModalActive, $NewsID);

  // Execute the statement
  if ($stmt->execute()) {
    $resultArray[] = array("Result" => "success");
    echo json_encode($resultArray);
  } else {
    // Handle errors
    $array[] = array("error" => "An error occurred while updating the news article.");
    echo json_encode($array);
  }
}

if (isset($_GET['updateNewsCategory'])) {
  // Check if user is authorized
  $requiredGroups = ["100014", "100001"];
  $functions->checkUserGroups($requiredGroups, $UserGroups);
  
  /*
  if (!in_array("100014", $UserGroups) && !in_array("100001", $UserGroups)) {
    $GroupName = getUserGroupName("100014");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }
    */

  // Get data from POST request
  $NewsCatID = $_POST['NewsCatID'];
  $ModalNewsCategoryGroups = $_POST['ModalNewsCategoryGroups'];
  $ModalNewsCategoryRole = $_POST['ModalNewsCategoryRole'];
  $ModalNewsCategoryActive = $_POST['ModalNewsCategoryActive'];
  $ModalNewsCategoryName = $_POST['ModalNewsCategoryName'];
  $CreatedByUserID = $_SESSION['id'];

  $sql = "UPDATE `news_categories` SET `Name`=?,`RelatedGroupID`=?,`RelatedRole`=?,`Active`=? WHERE ID = ?";

  // Prepare the update statement
  $stmt = $conn->prepare($sql);

  // Bind parameters
  $stmt->bind_param("siiii", $ModalNewsCategoryName, $ModalNewsCategoryGroups, $ModalNewsCategoryRole, $ModalNewsCategoryActive, $NewsCatID);

  // Execute the statement
  if ($stmt->execute()) {
    $resultArray[] = array("Result" => "success");
    echo json_encode($resultArray);
  } else {
    // Handle errors
    $array[] = array("error" => "An error occurred while updating the news article.");
    echo json_encode($array);
  }
}

if (isset($_GET['getNewsArticles'])) {

  $sql = "SELECT news.ID, news.Headline, news.Content, CONCAT(users.Firstname,' ',users.Lastname) AS CreatedBy, news.DateCreated, news_categories.Name AS CategoryName, news.CommentsAllowed, news.Active 
          FROM news 
          LEFT JOIN news_categories ON news.RelatedCategory = news_categories.ID
          LEFT JOIN users ON news.CreatedByUserID = users.ID
          ORDER BY DateCreated DESC;";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $ViewLink = "<a href=\"javascript:void(0);\"><span class=\"badge bg-gradient-success\" onclick=\"viewNews($ID);\" title=\"" . $functions->translate("Edit") . "\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
        if (in_array("100001", $UserGroups) || in_array("100032", $UserGroups)) {
          $DeleteLink = "<a href=\"javascript:void(0);\" title=\"" . $functions->translate("Delete") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteNewsArticle($ID);\"><i class=\"fa fa-trash\"></i></span></a>";
        } else {
          $DeleteLink = "";
        }
        
        $Headline = $row['Headline'];
        $CreatedBy = $row['CreatedBy'];
        $DateCreated = $functions->convertToDanishDateTimeFormat($row['DateCreated']);
        $CategoryName = $row['CategoryName'];
        $resultArray[] = array($functions->translate("Headline") => $Headline, $functions->translate("Created by") =>$CreatedBy, $functions->translate("Created") => $DateCreated, $functions->translate("Category") => $CategoryName, "" => "$ViewLink $DeleteLink");
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode([]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getNewsArticles");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getNewsArticles");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getTeamMembers'])) {

  $teamId = $_POST['teamId'];

  $sql = "SELECT usersteams.UserID
          FROM usersteams
          LEFT JOIN users ON usersteams.UserID = users.ID
          WHERE usersteams.TeamID = $teamId AND users.Active = 1;";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $TempUserID = $row['UserID'];
        $Email = getUserEmailFromID($TempUserID);
        $SessionUserID = $_SESSION['id'];
        $UserFullName = $functions->getUserFullNameWithUsername($TempUserID);
        $ProfilePicture = getUserProfilePicture($TempUserID);

        $NewUsername = "<a href=\"javascript:runModalViewUnit('User',$TempUserID);\" data-bs-toggle='popover' data-bs-html='true' data-bs-trigger='hover' data-bs-content='
                        <p class=\"text-center\"><b>$UserFullName</b><br>$Email<br><br>
                        <img class=\"rounded-circle img-fluid\" style=\"width: 100px;\" src=\"$ProfilePicture\">
                        <p class=\"text-sm text-secondary mb-0 text-wrap\">$UserFullName</p>
                        '>$UserFullName</a>";

        $resultArray[] = array($functions->translate("Name") => $NewUsername);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode(["message" => "Team has no members"]);  // <-- Custom message when no members
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getTeamMembers");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getTeamMembers");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getActiveApiKeys'])) {

  $sql = "SELECT `id`, `api_key`, `description`, `company_id`, `expiry_date`, `status`, `created`, created_by
          FROM `api_keys`
          WHERE Status = 1;";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $id = $row['id'];
        $api_key = $row['api_key'];
        $description = $row['description'];
        $company_id = $row['company_id'];
        $company_name = getCompanyName($company_id);
        $created_by = $row['created_by'];
        $CreatedByUserName = $functions->getUserFullNameWithUsername($created_by);
        $expiry_date = $functions->convertToDanishDateTimeFormat($row['expiry_date']);
        $status = $row['status'];
        $created = $functions->convertToDanishDateTimeFormat($row['created']);

        $deleteLink = "<a href=\"javascript:void(0);\" title=\"" . $functions->translate("Delete") . "\"><span class=\"badge badge-pill bg-gradient-danger\" onclick=\"deleteApiKey($id);\"><i class=\"fa fa-trash\"></i></span></a>";

        $resultArray[] = array("" => $deleteLink, $functions->translate("Description") => $description, $functions->translate("Key") => $api_key, $functions->translate("Company") => $company_name, $functions->translate("Expires") =>$expiry_date, $functions->translate("Created") => $created, $functions->translate("Created By") => $CreatedByUserName);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode(["message" => "No active API keys"]);  // <-- Custom message when no members
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getActiveApiKeys");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getActiveApiKeys");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['deleteApiKey'])) {
  $key = $_POST['key'];

  $sql = "DELETE FROM `api_keys` WHERE ID = ?;";

  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $key); // Binding parameter; "i" indicates integer
    if (mysqli_stmt_execute($stmt)) {
      if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(["message" => "API key deleted successfully."]);
      } else {
        echo json_encode(["message" => "No API key found with the specified ID."]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "deleteApiKey");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
    mysqli_stmt_close($stmt);
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "deleteApiKey");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getActiveCompanies'])) {

  $sql = "SELECT `id`, `CompanyName`
          FROM `companies`
          WHERE Active = 1;";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $id = $row['id'];
        $CompanyName = $row['CompanyName'];

        $resultArray[] = array("id" => $id, "Company" => $CompanyName);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode(["message" => "No active Companies"]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getActiveCompanies");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getActiveCompanies");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['deleteNewsArticle'])) {

  $ID = $_POST['newsArticleID'];

  $sql = "DELETE FROM news
          WHERE ID = $ID;";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
      $resultArray[] = array("Result" => "success");
      echo json_encode($resultArray);
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "deleteNewsArticle");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $functions->errorlog($error, "deleteNewsArticle");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['deleteNewsCategory'])) {

  $ID = $_POST['CategoryID'];

  $sql = "DELETE FROM news_categories
          WHERE ID = $ID;";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
      $resultArray[] = array("Result" => "success");
      echo json_encode($resultArray);
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "deleteNewsCategory");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $functions->errorlog($error, "deleteNewsCategory");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['deleteNewsCVEFilter'])) {

  $ID = $_POST['FilterID'];

  $sql = "DELETE FROM news_cve_filters
          WHERE ID = $ID;";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
      $resultArray[] = array("Result" => "success");
      echo json_encode($resultArray);
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "deleteNewsCVEFilter");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $functions->errorlog($error, "deleteNewsCVEFilter");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['updateNewsReadStatus'])) {
  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $newsId = $_POST['newsId'];
  $userSessionId = $_SESSION['id'];

  $userId = $_SESSION['id'];
  $newsId = $_POST['newsId'];

  $stmt = $conn->prepare("INSERT INTO news_reads (NewsID, UserID) VALUES (?, ?) ON DUPLICATE KEY UPDATE NewsID=NewsID");
  $stmt->bind_param("ii", $newsId, $userId);  // "ii" means two integers
  $stmt->execute();

  $Array[] = array();

  if ($stmt->error) {
    $Array[] = array("Result" => $stmt->error);
    $functions->errorlog("Error: " . $stmt->error, "updateNewsReadStatus");
  } else {
    $Array[] = array("Result" => "success");
  }

  $stmt->close();
  $conn->close();

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['createAPIKey'])) {
  if ($UserType == "2") {
    echo json_encode([]);
    return;
  }

  $apiKeyDescription = $_POST['apiKeyDescription'];
  $APIKeyCompanies = $_POST['APIKeyCompanies'];
  $apiExpireDate = convertFromDanishTimeFormat($_POST['apiExpireDate']);
  $API_key = $functions->generateRandomString(64);
  $userId = $_SESSION['id'];

  $stmt = $conn->prepare("INSERT INTO `api_keys`(`api_key`, `company_id`, `description`, `expiry_date`,`created_by`) 
                          VALUES (?,?,?,?,?)");
  $stmt->bind_param("sissi", $API_key, $APIKeyCompanies, $apiKeyDescription, $apiExpireDate, $userId);  // "ii" means two integers
  $stmt->execute();

  $Array[] = array();

  if ($stmt->error) {
    $Array[] = array("Result" => $stmt->error);
    $functions->errorlog("Error: " . $stmt->error, "createAPIKey");
  } else {
    $Array[] = array("Result" => "success");
  }

  $stmt->close();
  $conn->close();

  echo json_encode($Array, JSON_PRETTY_PRINT);
}

if (isset($_GET['getChangelogInfo'])) {

  $ChangeLogID = $_GET['ChangeLogID'];

  $sql = "SELECT `ID`, `Date`, `Version`, `Description`, `Type` 
          FROM `changelog`
          WHERE ID = '$ChangeLogID';";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
      $resultArray = [];
      while ($row = mysqli_fetch_array($result)) {
        $ID = $row['ID'];
        $Date = $row['Date'];
        $Version = $row['Version'];
        $Description = $row['Description'];
        $Type = $row['Type'];

        $resultArray[] = array("ID" => $ID, "Date" =>$Date, "Version" => $Version, "Description" => $Description, "Type" => $Type);
      }

      mysqli_free_result($result);

      if (!empty($resultArray)) {
        echo json_encode($resultArray, JSON_PRETTY_PRINT);
      } else {
        echo json_encode(["message" => "Error"]);
      }
    } else {
      $error = "Query failed: " . mysqli_error($conn);
      $functions->errorlog($error, "getChangelogInfo");
      echo json_encode(["error" => "An error occurred. Please try again later."]);
    }
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "getChangelogInfo");
    echo json_encode(["error" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['updateChangelog'])) {
  $ChangeLogID = $_GET['ChangeLogID'];
  $Version = $_GET['Version'];
  $Description = $_GET['Description'];
  $ChangelogDate = $_GET['ChangelogDate'];
  $Type = $_GET['Type'];  

  // Prepare the update statement
  $sql = "UPDATE `changelog` 
          SET `Version` = ?, `Description` = ?, `Type` = ?, `Date` = ?
          WHERE `ID` = ?";

  $stmt = mysqli_prepare($conn, $sql);
  if ($stmt) {
    // Bind the parameters to the statement
    mysqli_stmt_bind_param($stmt, 'ssiss', $Version, $Description, $Type, $ChangelogDate, $ChangeLogID);

    // Execute the prepared statement
    if (mysqli_stmt_execute($stmt)) {
      echo json_encode(["Result" => "Update successful"]);
    } else {
      $error = "Update failed: " . mysqli_stmt_error($stmt);
      // You can use $functions->errorlog() if you have it defined somewhere, or just log it in another way
      $functions->errorlog($error, "updateChangelog");
      echo json_encode(["Result" => "An error occurred. Please try again later."]);
    }
    mysqli_stmt_close($stmt);
  } else {
    $error = "Statement preparation failed: " . mysqli_error($conn);
    $functions->errorlog($error, "updateChangelog");
    echo json_encode(["Result" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getTimeRegistrationInformation'])) {
  $UserID = $_SESSION['id'];
  $timeregid = $_POST['timeregid'];

  $sql = "SELECT time_registrations.ID, time_registrations.DateWorked, TimeRegistered, time_registrations.Description, RelatedTaskID, DateRegistered, taskslist.Headline, taskslist.UserNote, taskslist.Deadline, taskslist.GoToLink, 
          taskslist.RelatedElementID, taskslist.RelatedElementTypeID, time_registrations.Billable
          FROM time_registrations
          LEFT JOIN taskslist ON time_registrations.RelatedTaskID = taskslist.ID
          WHERE time_registrations.ID = ?
          ORDER BY DateWorked DESC, DateRegistered DESC;";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, 'i', $timeregid);

  if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
      $data[] = $row;
    }
    echo json_encode($data);
  } else {
    $error = "Update failed: " . mysqli_stmt_error($stmt);
    // You can use $functions->errorlog() if you have it defined somewhere, or just log it in another way
    $functions->errorlog($error, "getTimeRegistrationInformation");
    echo json_encode(["Result" => "An error occurred. Please try again later."]);
  }
  mysqli_stmt_close($stmt);
}

if (isset($_GET['resetInstallation'])) {
  $requiredGroups = ["100000","100001"];
  $functions->checkUserGroups($requiredGroups, $UserGroups);
  
  resetInstallation();

  $ResultArray[] = array("Result" => "success");

  echo json_encode($ResultArray);

}

if (isset($_GET['updateAllTableComments'])) {
  $requiredGroups = ["100000","100001"];
  $functions->checkUserGroups($requiredGroups, $UserGroups);

  updateAllTableComments();

  $ResultArray[] = array("Result" => "success");

  echo json_encode($ResultArray);

}

if (isset($_GET['upgradeLatestPracticleBaseDb'])) {
  $requiredGroups = ["100000","100001"];
  $functions->checkUserGroups($requiredGroups, $UserGroups);

  $Result = upgradeLatestPracticleBaseDb();

  if($Result = true){
    $ResultArray[] = array("Result" => "success");
  } else{
    $ResultArray[] = array("Result" => "fail");
  }  

  echo json_encode($ResultArray);
}

if (isset($_GET['regenerateDatabaseCollation'])) {
  $requiredGroups = ["100000","100001"];
  $functions->checkUserGroups($requiredGroups, $UserGroups);

  $Result = regenerateDatabaseCollation();

  if($Result = true){
    $ResultArray[] = array("Result" => "success");
  } else{
    $ResultArray[] = array("Result" => "fail");
  }  

  echo json_encode($ResultArray);
}

if (isset($_GET['getUnitCreateFieldDefinitions'])) {
  $UnitType = $_POST['UnitType'];
  $CreateText = $functions->translate("Create");
  $CreateBtn = "<button class=\"btn btn-sm btn-success float-end\" onclick=\"createUnit('$UnitType')\">$CreateText</button>";
  $Fields = array();

  if ($UnitType == "User"){
    $requiredGroups = ["100026","100001"];
    $functions->checkUserGroups($requiredGroups, $UserGroups);
    $Fields = $functions->getUnitCreateFieldsUsers();
  } else if ($UnitType == "Group") {
    $requiredGroups = ["100026","100001"];
    $functions->checkUserGroups($requiredGroups, $UserGroups);
    $Fields = $functions->getUnitFieldsGroups();
  } else if ($UnitType == "Role") {
    $requiredGroups = ["100026","100001"];
    $functions->checkUserGroups($requiredGroups, $UserGroups);
    $Fields = $functions->getUnitFieldsRoles();
  } else if ($UnitType == "Team") {
    $requiredGroups = ["100026","100001"];
    $functions->checkUserGroups($requiredGroups, $UserGroups);
    $Fields = $functions->getUnitFieldsTeams();
  } else if ($UnitType == "Company") {
    $requiredGroups = ["100025","100001"];
    $functions->checkUserGroups($requiredGroups, $UserGroups);
    $Fields = $functions->getUnitFieldsCompanies();
  }

  $ResultArray[] = array("FieldsArray" => $Fields, "CreateBtn" => $CreateBtn);

  echo json_encode($ResultArray);
}

if (isset($_GET['updateLookupField'])) {
  $requiredGroups = ["100001"];
  $functions->checkUserGroups($requiredGroups, $UserGroups);

  $tableName = $_POST['tableName'];

  $sql = "DESCRIBE $tableName;";

  // Now you can execute this query.
  $result = mysqli_query($conn, $sql);
  while ($row = mysqli_fetch_array($result)) {
    $ResultArray[] = array("FieldName" => $row["Field"]);
  }

  echo json_encode($ResultArray);
}

if (isset($_GET['getUnitViewFieldDefinitions'])) {
  $UnitType = $_POST['UnitType'];
  $CreateText = $functions->translate("Create");
  $Fields = array();

  if ($UnitType == "User") {
    $Fields = $functions->getUnitViewFieldsUsers();
  } else if ($UnitType == "Group") {
    $Fields = $functions->getUnitFieldsGroups();
  } else if ($UnitType == "Role") {
    $Fields = $functions->getUnitFieldsRoles();
  } else if ($UnitType == "Team") {
    $Fields = $functions->getUnitFieldsTeams();
  } else if ($UnitType == "Company") {
    $Fields = $functions->getUnitFieldsCompanies();
  }

  $ResultArray[] = array("FieldsArray" => $Fields);

  echo json_encode($ResultArray);
}

if (isset($_GET['getUnitEditFieldDefinitions'])) {
  $UnitType = $_POST['UnitType'];
  $CreateText = $functions->translate("Create");
  $Fields = array();

  if ($UnitType == "User"){
    $Fields = $functions->getUnitFieldsUsers();
  } else if ($UnitType == "Group") {
    $Fields = $functions->getUnitFieldsGroups();
  } else if ($UnitType == "Role") {
    $Fields = $functions->getUnitFieldsRoles();
  } else if ($UnitType == "Team") {
    $Fields = $functions->getUnitFieldsTeams();
  } else if ($UnitType == "Company") {
    $Fields = $functions->getUnitFieldsCompanies();
  }

  $ResultArray[] = array("FieldsArray" => $Fields);

  echo json_encode($ResultArray);
}

if (isset($_GET['createUnit'])) {
    $requiredGroups = ["100026","100001"];
    $functions->checkUserGroups($requiredGroups, $UserGroups);
    $temp = $_POST['UnitType'];
    $fieldSpecsArray = $_POST['FieldSpecs'];
    
    // Call createUnit and get the result
    $result = $functions->createUnit($temp, $fieldSpecsArray);
    // Return JSON-encoded response
    echo json_encode($result);
}

if (isset($_GET['getGroupsTable'])) {
    $UserID = $_SESSION['id'];

    try {
        $sql = "SELECT usergroups.ID AS ID, usergroups.GroupName AS GroupName, usergroups.Active AS Active, 'Personal' AS Type
                FROM usergroups
                UNION
                SELECT system_groups.ID AS ID, system_groups.GroupName AS GroupName, system_groups.Active AS Active, 'System' AS Type
                FROM system_groups
                WHERE system_groups.ID !='100000'
                ORDER BY Active DESC, GroupName ASC;";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        $Array = array();

        while ($row = mysqli_fetch_array($result)) {
            $GroupID = $row['ID'];
            $Type = $row['Type'];
            $GroupName = $row['GroupName'];
            $ModuleName = $row['ModuleName'];
            $Active = $row['Active'] == "1" ? _("Active") : _("Inactive");

            $ViewBtn = "<a href='administration_groups_edit.php?groupid=$GroupID' title=\"" . $functions->translate("Edit") . "\"><span class='badge badge-pill bg-gradient-info'><i class='fa fa-folder-open'></i></span></a>";
            $PreviewBtn = "<a href=\"javascript:getUsersInGroup('$GroupID');\" title=\"". $functions->translate("Preview group members") . "\"><span class='badge badge-pill bg-gradient-secondary'><i class='far fa-eye'></i></span></a>";
            $DeleteBtn = ($Type == "Personal") ? "<a href=\"javascript:deleteGroup($GroupID);\" title=\"" . $functions->translate("Delete") . "\"><span class='badge badge-pill bg-gradient-danger'><i class='fa-solid fa-trash'></i></span></a>" : "";

            $Array[] = array(
                'Name' => $functions->translate($GroupName),
                'Actions' => $ViewBtn . $PreviewBtn . $DeleteBtn,
                'Module' => $functions->translate($ModuleName),
                'Type' => $functions->translate($Type),
                'Status' => $functions->translate($Active)
            );
        }

        // Sort using the fixed 'Name' key without re-translating
        usort($Array, function ($a, $b) {
            return strcmp($a['Name'], $b['Name']);
        });

        echo json_encode($Array ?: [], JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        echo json_encode(["Result" => "An error occurred. Please try again later."]);
    }
}

if (isset($_GET['getUsersTable'])) {
    $UserID = $_SESSION['id'];

    try {
        $sql = "SELECT DISTINCT users.ID AS UserID, CONCAT(users.Firstname, ' ', users.Lastname) AS fullName, users.Username, usertypes.TypeName AS UserType, 
                       users.Email, users.Created_Date AS CreatedDate, companies.Companyname AS Companyname, 
                       users.RelatedUserTypeID, users.Active AS Active, users.RelatedManager, users.LastLogon, users.Phone
                FROM Users 
                LEFT JOIN companies ON users.CompanyID = companies.ID
                LEFT JOIN usertypes ON users.RelatedUserTypeID = usertypes.ID
                WHERE Users.Active IN (0,1)
                ORDER BY fullName ASC, Active DESC;";

        $result = mysqli_query($conn, $sql) or die('Query failed: ' . mysqli_error($conn));

        $Array = array();

        // Define translation keys once
        $keyName = $functions->translate('Name');
        $keyUsername = $functions->translate('Username');
        $keyType = $functions->translate('Type');
        $keyEmail = $functions->translate('Email');
        $keyPhone = $functions->translate('Phone');
        $keyCompany = $functions->translate('Company');
        $keyLastLogon = $functions->translate('Last logon');
        $keyTeamLeader = $functions->translate('Team Leader');
        $keyStatus = $functions->translate('Status');
        $keyEdit = $functions->translate('Edit');
        $keyView = $functions->translate('View');

        while ($row = mysqli_fetch_array($result)) {
            $UsersID = $row['UserID'];
            $fullName = $row['fullName'];
            $Username = $row['Username'];
            $Phone = $row['Phone'];
            $TypeName = $row['UserType'];
            $Companyname = $row['Companyname'];
            $Email = $row['Email'];
            $RelatedManager = !empty($row['RelatedManager']) ? $functions->getUserFullNameWithUsername($row['RelatedManager']) : "";
            $LastLogon = !empty($row['LastLogon']) ? convertToDanishTimeFormat($row['LastLogon']) : "";

            $Status = $row['Active'] == "1" ? $functions->translate("Active") : $functions->translate("Inactive");

            // Prepare action buttons
            $OpenBtn = "<a href='administration_users_edit.php?usersid=$UsersID' title=\"" . $keyEdit . "\"><span class='badge badge-pill bg-gradient-info'><i class='fa fa-folder-open'></i></span></a>";
            $ViewBtn = "<a href=\"javascript:runModalViewUnit('User', $UsersID);\" title=\"" . $keyView . "\"><span class='badge badge-pill bg-gradient-success'><i class='fa fa-pen-to-square'></i></span></a>";

            // Append the user data to the array
            $Array[] = array(
                $keyName => $fullName,
                "" => $OpenBtn . $ViewBtn,
                $keyUsername => $Username,
                $keyType => $functions->translate($TypeName),
                $keyEmail => $Email,
                $keyPhone => $Phone,
                $keyCompany => $Companyname,
                $keyLastLogon => $LastLogon,
                $keyTeamLeader => $RelatedManager,
                $keyStatus => $Status,
            );
        }

        // Sort by the translated 'Name' key
        if ($Array) {
            usort($Array, function ($a, $b) use ($keyName) {
                return strcmp($a[$keyName], $b[$keyName]);
            });

            echo json_encode($Array, JSON_PRETTY_PRINT);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        echo json_encode(["Result" => "An error occurred. Please try again later."]);
    }
}

if (isset($_GET['getRolesTable'])) {
  $UserID = $_SESSION['id'];

  try {
    $sql = "SELECT roles.ID AS RoleID, roles.RoleName, roles.Description, roles.Active
            FROM roles
            WHERE roles.ID > 0
            ORDER BY RoleName ASC;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $Array = array();

    $keyName = $functions->translate('Name');
    $keyDescription = $functions->translate('Description');
    $keyStatus = $functions->translate('Status');
    $keyEdit = $functions->translate('Edit');
    $keyPreview = $functions->translate('Preview Groups in the role');
    $keyDelete = $functions->translate('Delete');

    while ($row = mysqli_fetch_array($result)) {
      $RoleID = $row['RoleID'];
      $RoleName = $functions->translate($row['RoleName']);
      $Description = $row['Description'];
      $Status = $row['Active'];

      if ($Status == "1") {
        $Status = $functions->translate("Active");
      } else {
        $Status = $functions->translate("Inactive");
      }

      $ViewBtn = "<a href='administration_roles_edit.php?roleid=$RoleID' title='" . $keyEdit . "'><span class='badge badge-pill bg-gradient-info'><i class='fa fa-folder-open'></i></span></a>";
      $PreviewBtn = "<a href=\"javascript:getGroupsInRole('$RoleID');\" title='" . $keyPreview . "'><span class='badge badge-pill bg-gradient-secondary'><i class='far fa-eye'></i></span></a>";
      $DeleteBtn = "<a href=\"javascript:deleteRole($RoleID);\" title='" . $keyDelete . "'><span class='badge badge-pill bg-gradient-danger'><i class='fa-solid fa-trash'></i></span></a>";

      $Array[] = array(
        $keyName => $RoleName,
        "" => $ViewBtn . $PreviewBtn . $DeleteBtn,
        $keyDescription => $Description,
        $keyStatus => $Status,
      );
    }

    if ($Array) {
      usort($Array, function ($a, $b) use ($keyName) {
        return strcmp($a[$keyName], $b[$keyName]);
      });
      echo json_encode($Array, JSON_PRETTY_PRINT);
    } else {
      echo json_encode([]);
    }
  } catch (Exception $e) {
    $functions->errorlog($e->getMessage(), "getRolesTable");
    echo json_encode(["Result" => "An error occurred. Please try again later."]);
  }

}

if (isset($_GET['getTeamsTable'])) {
  $UserID = $_SESSION['id'];

  try {
    $sql = "SELECT teams.ID, teams.Teamname, teams.Description, teams.Active 
            FROM teams
            ORDER BY teams.Active DESC, teams.Teamname ASC;";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $Array = array();

    $keyName = $functions->translate('Name');
    $keyDescription = $functions->translate('Description');
    $keyStatus = $functions->translate('Status');
    $keyEdit = $functions->translate('Edit');
    $keyPreview = $functions->translate('Preview of team members');
    $keyDelete = $functions->translate('Delete');

    while ($row = mysqli_fetch_array($result)) {
      $ID = $row['ID'];
      $Teamname = $row['Teamname'];
      $Description = $row['Description'];
      $Status = $row['Active'];

      if ($Status == "1") {
        $Status = $functions->translate("Active");
      } else {
        $Status = $functions->translate("Inactive");
      }

      $ViewBtn = "<a href='administration_teams_edit.php?teamid=$ID' title='" . $keyEdit . "'><span class='badge badge-pill bg-gradient-info'><i class='fa fa-folder-open'></i></span></a>";
      $PreviewBtn = "<a href=\"javascript:getUsersInTeam('$ID');\" title='" . $keyPreview . "'><span class='badge badge-pill bg-gradient-secondary'><i class='far fa-eye'></i></span></a>";
      $DeleteBtn = "<a href=\"javascript:deleteTeam($ID);\" title='" . $keyDelete . "'><span class='badge badge-pill bg-gradient-danger'><i class='fa-solid fa-trash'></i></span></a>";

      $Array[] = array(
        $keyName => $Teamname,
        '' => $ViewBtn . $PreviewBtn . $DeleteBtn,
        $keyDescription => $Description,
        $keyStatus => $Status,
      );
    }

    if ($Array) {
      usort($Array, function ($a, $b) use ($keyName) {
        return strcmp($a[$keyName], $b[$keyName]);
      });
      echo json_encode($Array, JSON_PRETTY_PRINT);
    } else {
      echo json_encode([]);
    }
  } catch (Exception $e) {
    $functions->errorlog($e->getMessage(), "getTeamsTable");
    echo json_encode(["Result" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getCompaniesTable'])) {
    $UserID = $_SESSION['id'];

    try {
        $sql = "SELECT ID, Companyname, Active, WebPage, Phone, RelatedSLAID, CustomerAccountNumber, Address, ZipCode, City, countries.name AS Country, Email, CBR 
                FROM companies
                LEFT JOIN countries ON companies.Country = countries.abv
                WHERE Active IN (0,1)
                ORDER BY Companyname ASC;";
        $result = mysqli_query($conn, $sql) or die('Query failed: ' . mysqli_error($conn));

        $Array = array();

        // Define translation keys once
        $keyName = $functions->translate('Name');
        $keyPhone = $functions->translate('Phone');
        $keyWebPage = $functions->translate('WebPage');
        $keyCountry = $functions->translate('Country');
        $keyCity = $functions->translate('City');
        $keyEmail = $functions->translate('Email');
        $keyCBR = $functions->translate('CBR');
        $keyStatus = $functions->translate('Status');
        $keyInactive = $functions->translate('Inactive');
        $keyEdit = $functions->translate('Edit');

        while ($row = mysqli_fetch_array($result)) {
            $ID = $row['ID'];
            $Companyname = $row['Companyname'];
            $Phone = $row['Phone'];
            $WebPage = $row['WebPage'];
            $Country = $row['Country'];
            $City = $row['City'];
            $Email = $row['Email'];
            $CBR = $row['CBR'];
            
            // Use ternary operator for status
            $Status = $row['Active'] == "1" ? $functions->translate("Active") : $keyInactive;

            // Prepare action button
            $ViewBtn = "<a href='administration_companies_edit.php?companyid=$ID' title='" . $keyEdit . "'><span class='badge badge-pill bg-gradient-info'><i class='fa fa-folder-open'></i></span></a>";

            // Append the company data to the array
            $Array[] = array(
                $keyName => $Companyname,
                '' => $ViewBtn,
                $keyPhone => $Phone,
                $keyWebPage => $WebPage,
                $keyCountry => $Country,
                $keyCity => $City,
                $keyEmail => $Email,
                $keyCBR => $CBR,
                $keyStatus => $Status,
            );
        }

        // Sort by the translated 'Name' key
        if ($Array) {
            usort($Array, function ($a, $b) use ($keyName) {
                return strcmp($a[$keyName], $b[$keyName]);
            });
            echo json_encode($Array, JSON_PRETTY_PRINT);
        } else {
            echo json_encode([]);
        }
    } catch (Exception $e) {
        $functions->errorlog($e->getMessage(), "getCompaniesTable");
        echo json_encode(["Result" => "An error occurred. Please try again later."]);
    }
}

if (isset($_GET['getUserProjects'])) {
  $UserID = $_SESSION['id'];
  $Array = array();

  try {
    $sql = "SELECT projects.ID, projects.Name, projects.Start, projects.Progress, projects_statuscodes.StatusName, projects.Deadline, projects.EstimatedBudget, projects.RelatedCompanyID, 
            (SELECT CONCAT(users.firstname,' ', users.lastname) FROM users WHERE users.ID = projects.ProjectResponsible) AS ProjectResponsible, (SELECT CONCAT(users.firstname,' ', users.lastname) FROM users WHERE users.ID = projects.ProjectManager) AS ProjectManager,EstimatedHours, HoursSpend,
            (SELECT SUM(project_tasks.progress) FROM project_tasks WHERE project_tasks.RelatedProject = projects.ID)/(SELECT COUNT(*) FROM project_tasks WHERE project_tasks.RelatedProject = projects.ID) AS ProjectProgress 
            FROM projects
            LEFT JOIN projects_statuscodes ON projects.Status = projects_statuscodes.ID
            LEFT JOIN users ON projects.ProjectManager = users.ID
            WHERE (Status NOT IN ('7','8') AND projects.ID IN (SELECT project_users.ProjectID
            FROM project_users
            WHERE project_users.UserID = $UserID) OR (Status NOT IN ('7','8') AND projects.ProjectResponsible = $UserID) OR (Status NOT IN ('7','8') AND projects.ProjectManager = $UserID))
            ORDER BY projects.Deadline ASC;";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
      $Array[] = array(
        'ID' => $row['ID'],        
        'Project' => $row['Name'],
        'UserID' => $UserID
      );
    }    

    if ($Array) {      
      echo json_encode($Array, JSON_PRETTY_PRINT);
    } else {
      echo json_encode([]);
    }
  } catch (Exception $e) {
    $functions->errorlog($e->getMessage(), "getUserProjects");
    echo json_encode(["Result" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['getUnitViewFieldValues'])) {
  $UserID = $_SESSION['id'];
  $UnitType = $_POST["UnitType"];
  $UnitID = $_POST["UnitID"];
  $responseData = $_POST["responseData"];
  try {
    $UnitTableName = $functions->getUnitTableName($UnitType);
   } catch (Exception $e) {
    $functions->errorlog("Error: " . $e->getMessage(), "practicleFunctions");
  }
  
  try {

    $fieldsArray = $responseData[0]['FieldsArray'];
    $response = [];
    
    foreach ($fieldsArray as $fieldName => $field) {

      $FieldType = $field['FieldType'];

      if ($FieldType === 'Select') {
        if ($fieldName == "CompanyID") {
          $FieldValue = $functions->getITSMFieldValue($UnitID, $fieldName, $UnitTableName);
          if ($FieldValue) {
            $FieldValue = getCompanyIDFromUserID($UnitID);
            $FieldValue = getCompanyName($CompanyID);
          }
        } else if ($fieldName == "RelatedUserTypeID") {
          $FieldValue = $functions->getITSMFieldValue($UnitID, $fieldName, $UnitTableName);
          if ($FieldValue) {
            $FieldValue = getUserTypeName($FieldValue);
          }
        } else if ($fieldName == "RelatedManager") {
          $FieldValue = $functions->getITSMFieldValue($UnitID, $fieldName, $UnitTableName);
          if($FieldValue){
            $FieldValue = $functions->getUserFullName($FieldValue);
          }          
        } else if ($fieldName == "TeamLeader") {
          $FieldValue = $functions->getITSMFieldValue($UnitID, $fieldName, $UnitTableName);
          if ($FieldValue) {
            $FieldValue = $functions->getUserFullName($FieldValue);
          }
        } else if ($fieldName == "RelatedSLAID") {
          $FieldValue = $functions->getITSMFieldValue($UnitID, $fieldName, $UnitTableName);
          if ($FieldValue) {
            $FieldValue = getSLANameFromID($FieldValue);
          }
        } else {
          $FieldValue = $functions->getITSMFieldValue($UnitID, $fieldName, $UnitTableName);
        }
      } else if ($FieldType === 'Date'){
        $FieldValue = $functions->getITSMFieldValue($UnitID, $fieldName, $UnitTableName);
      }
      else {
        $FieldValue = $functions->getITSMFieldValue($UnitID, $fieldName, $UnitTableName);
      }
      $response[$fieldName] = isset($FieldValue) ? $FieldValue : null;
    }

    if($response){
      echo json_encode($response, JSON_PRETTY_PRINT);
    } else {
      echo json_encode([]);
    }
  } catch (Exception $e) {
    $functions->errorlog($e->getMessage(), "getUnitViewFieldValues");
    echo json_encode(["Result" => "An error occurred. Please try again later."]);
  }
}

if (isset($_GET['fetchQueryTimes'])) {
  $UserID = $_SESSION['id'];
  // Create an array to hold the query times and messages
  $queryTimes = [];

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Define the expected query time
  $expectedTime = 0.010; // 1 millisecond

  // Execute 5 queries and record their execution times
  for ($i = 1; $i <= 5; $i++) {
    $startTime = microtime(true); // Start time
    $sql = "SELECT * FROM stopwords;";
    // Replace with your actual query
    $result = $conn->query($sql);

    $endTime = microtime(true); // End time
    $executionTime = $endTime - $startTime; // Calculate execution time

    // Check if the query was slower or faster than expected
    if ($executionTime > $expectedTime) {
      $message = "slow";
    } else {
      $message = "fast";
    }

    // Store the execution time and the message in the array
    $queryTimes[] = [
      'time' => $executionTime,
      'message' => $message
    ];
  }

  $conn->close(); // Close the database connection

  // Return the query times and messages as a JSON response
  echo json_encode($queryTimes);
}

if (isset($_GET['createProjectActivity'])) {
  $Content = $_POST['Content'];
  $ProjectTaskID = $_POST['ProjectTaskID'];

  createNewProjectActivity($Content, $ProjectTaskID, $UserSessionID);
  echo json_encode(["Result" => "success"]);
}

if (isset($_GET['deactivate2FA'])) {
  $userId = $_POST['userId'];

  deactivate2FA($userId);
  echo json_encode(["Result" => "success"]);
}

if (isset($_GET['fixBase64SelectField'])) {
  $counts = [
    "cmdb_ci_fieldslist" => processSelectFieldOptions("cmdb_ci_fieldslist"),
    "forms_fieldslist" => processSelectFieldOptions("forms_fieldslist"),
    "itsm_fieldslist" => processSelectFieldOptions("itsm_fieldslist")
  ];

  echo json_encode(["Result" => "success", "Counts" => $counts]);
}

if (isset($_GET['fetchCMDBTimelineData'])) {
  $UserSessionID = $_SESSION['id'];
  $CITypeID = $_POST['CITypeID'];
  $CIName = getCINameFromTypeID($CITypeID);
  $CITableName = getCITableName($CITypeID);
  $Field = getRelationShowField($CITypeID);
  $SiteUrl = $functions->getSettingValue(17);
  $currentYear = date("Y");

  if ($CITableName && $Field) { // Ensure valid table name and field
    $sql = "SELECT `ID`, `EndDate`, `$Field`
            FROM `$CITableName`
            WHERE YEAR(`EndDate`) = ? AND Active = 1";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $currentYear);

    if (mysqli_stmt_execute($stmt)) {
      $result = mysqli_stmt_get_result($stmt);
      $events = [];
      while ($row = mysqli_fetch_assoc($result)) {
        $endDate = new DateTime($row['EndDate']);
        $ID = $row['ID'];
        $Name = $row[$Field];
        $Link = "<a href=\"javascript:runModalViewCI('$ID','$CITypeID','0');\">$Name</a>";
        $events[] = [
          'id' => $ID,
          'endDate' => $row['EndDate'],
          'endDateShort' => $endDate->format('d-m-Y'),
          'CITypeID' => $CITypeID,
          'url' => $SiteUrl,
          'name' => $Name // Use the variable directly here
        ];
      }
      echo json_encode([
        "CIName" => $CIName,
        "events" => $events
      ]);
    } else {
      $error = "Query execution failed: " . mysqli_stmt_error($stmt);
      // Log the error if you have a logging mechanism
      $functions->errorlog($error, "fetchCMDBTimelineData");
      echo json_encode(["Result" => "An error occurred. Please try again later."]);
    }
    mysqli_stmt_close($stmt);
  } else {
    echo json_encode(["Result" => "Invalid CITypeID or field."]);
  }
}

if (isset($_GET['getTranserUserObjectsCMDB'])) {
  $UserSessionID = $_SESSION['id'];
  $UserID = $_POST['UserID'];

  $ciTypes = getUserTransferObjectsCMDB($UserID);

  echo json_encode($ciTypes); // Directly encoding the array of objects
}

if (isset($_GET['getTranserUserObjectsITSM'])) {
  $UserSessionID = $_SESSION['id'];
  $UserID = $_POST['UserID'];

  $itsmTypes = getUserTransferObjectsITSM($UserID);

  echo json_encode($itsmTypes); // Directly encoding the array of objects
}

if (isset($_GET['getUserTransferObjectsOther'])) {
  $UserSessionID = $_SESSION['id'];
  $UserID = $_POST['UserID'];

  $otherTypes = getUserTransferObjectsOther($UserID);

  echo json_encode($otherTypes); // Directly encoding the array of objects
}

if (isset($_GET['getUserTransferObjectsMemberShips'])) {
  $UserSessionID = $_SESSION['id'];
  $UserID = $_POST['UserID'];

  $otherTypes = getUserTransferObjectsMemberShips($UserID);

  echo json_encode($otherTypes); // Directly encoding the array of objects
}

if (isset($_GET['getUsersArray'])) {
  $users = getUsersArray();
  echo json_encode($users);
}

if (isset($_GET['transferObjectsToUser'])) {
  $UserSessionID = $_SESSION['id'];
  ob_implicit_flush(true);
  ob_end_clean(); // Clean any previous output buffers

  $CMDBArray = array();
  $ITSMArray = array();
  $OthersArray = array();
  $MSArray = array();
  $UserIDToMoveFrom = $_POST['UserIDToMoveFrom'];
  $UserToMoveTo = $_POST['UserToMoveTo'];
  $ObjectsForm = $_POST['ObjectsForm'];
  
  if (in_array("100026", $UserGroups) || in_array("100001", $UserGroups) || ($UserIDToMoveFrom == $UserSessionID)) {
  } else {
    $GroupName = getUserGroupName("100026");
    //$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
    $array[] = array("error" => "You need to be member of $GroupName");
    echo json_encode($array);
    return;
  }

  foreach ($ObjectsForm as $key => $value) {
    $fieldname = $value['name'];
    $fieldvalue = $value['value'];

    if (strpos($fieldname, "ciType") !== false) {
      $CMDBArray[] = array("name" => $fieldname, "fieldvalue" => $fieldvalue);
    }
    if (strpos($fieldname, "itsmType") !== false) {
      $ITSMArray[] = array("name" => $fieldname, "fieldvalue" => $fieldvalue);
    }
    if (strpos($fieldname, "otherType") !== false) {
      $OthersArray[] = array("name" => $fieldname, "fieldvalue" => $fieldvalue);
    }
    if (strpos($fieldname, "msType") !== false) {
      $MSArray[] = array("name" => $fieldname, "fieldvalue" => $fieldvalue);
    }
  }
  
  $totalItems = count($CMDBArray) + count($ITSMArray) + count($OthersArray) + count($MSArray);
  $processedItems = 0;

  function sendProgress($processedItems, $totalItems)
  {
    $progress = round(($processedItems / $totalItems) * 100);
    echo json_encode(["progress" => $progress]) . PHP_EOL;
  }

  foreach ($CMDBArray as $item) {
    transferCMDBItems($item, $UserIDToMoveFrom, $UserToMoveTo, $UserSessionID);
    $processedItems++;
    sendProgress($processedItems, $totalItems);
  }

  foreach ($ITSMArray as $item) {
    transferITSMItems($item, $UserIDToMoveFrom, $UserToMoveTo, $UserSessionID);
    $processedItems++;
    sendProgress($processedItems, $totalItems);
  }

  foreach ($OthersArray as $item) {
    transferOthersItems($item, $UserIDToMoveFrom, $UserToMoveTo, $UserSessionID);
    $processedItems++;
    sendProgress($processedItems, $totalItems);
  }

  foreach ($MSArray as $item) {
    transferMS($item, $UserIDToMoveFrom, $UserToMoveTo, $UserSessionID);
    $processedItems++;
    sendProgress($processedItems, $totalItems);
  }

  echo json_encode(["Result" => "success"]) . PHP_EOL;
}

if (isset($_GET['readITSMComments'])) {
  $UserSessionID = $_SESSION['id'];

  $commentID = $_POST['commentID'];

  readITSMComments($ITSMTypeID, $ITSMID, $UserSessionID);
}

if (isset($_GET['deleteITSMComment'])) {
  $UserSessionID = $_SESSION['id'];

  $commentID = $_POST['commentID'];

  deleteITSMComment($commentID);
}

if (isset($_GET['saveITSMComment'])) {
  $UserSessionID = $_SESSION['id'];

  $commentID = $_POST['commentID'];
  $itsmComment = $_POST['itsmComment'];

  updateITSMCOmment($commentID, $itsmComment);
}

if (isset($_GET['addGroupFilter'])) {
  $UserSessionID = $_SESSION['id'];

  $Type = $_POST["Type"];
  $FieldID = $_POST["FieldID"];
  $GroupID = $_POST["GroupID"];

  $result = addGroupFilter($Type, $FieldID, $GroupID);

  if($result){
    $Array[] = array("Result" => "success");
    echo json_encode($Array);
  } else {
    $Array[] = array("Result" => "fail");
    echo json_encode($Array);
  }  
}

?>