<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>

<?php
$ITSMID = $_GET['id'];

$sql = "SELECT itsm_modules.ID, itsm_modules.Name, itsm_modules.ShortElementName, itsm_modules.TableName, itsm_modules.DoneStatus, itsm_modules.Description, itsm_modules.CreatedBy, itsm_modules.Created, itsm_modules.TypeIcon,
		itsm_modules.LastEditedBy, itsm_modules.LastEdited, itsm_modules.Active, itsm_modules.GroupID, itsm_modules.ImportSource, itsm_modules.Synchronization, itsm_modules.SyncTime, itsm_modules.LastSyncronized
		FROM itsm_modules
		WHERE itsm_modules.ID = $ITSMID";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

while ($row = mysqli_fetch_array($result)) {
    $Name = $row['Name'];
    $ShortElementName = $row['ShortElementName'];
    $TableName = $row['TableName'];
    $Description = $row['Description'];
    $CreatedBy = $row['CreatedBy'];
    $LastEditedBy = $row['LastEditedBy'];
    $LastEdited = $row['LastEdited'];
    $LastEditedDanishDateTime = convertToDanishTimeFormat($row['LastEdited']);
    $Active = $row['Active'];
    $ModuleGroupID = $row['GroupID'];
    $Created = convertToDanishTimeFormat($row['Created']);
    $CreatedBy = $row['CreatedBy'];
    $TypeIcon = $row['TypeIcon'];
    $DoneStatus = $row['DoneStatus'];
    $ImportSource = $row['ImportSource'];
    $SyncTime = $row['SyncTime'];
    $Synchronization = $row['Synchronization'];
    $LastSynced = $row['LastSyncronized'];
}

?>

<script>
    function showCreateNewDiv() {
        element = document.getElementById('createNew');
        elementplus = document.getElementById('plus');
        elementminus = document.getElementById('minus');

        if ($('#createNew').css('display') === 'block') {
            element.style.display = 'none';
            elementplus.style.display = '';
            elementminus.style.display = 'none';
            let createEntry = document.getElementById('createEntry');
            createEntry.removeAttribute("hidden");
            let updateEntry = document.getElementById('updateEntry');
            updateEntry.setAttribute("hidden", true);
        } else {
            element.style.display = 'block';
            elementplus.style.display = 'none';
            elementminus.style.display = '';
            document.getElementById('FieldID').value = "";
            document.getElementById('FieldLabel').value = "";
            document.getElementById('FieldOrder').value = "";
            document.getElementById('FieldType').value = "";
            document.getElementById('FieldDefaultValue').value = "";
            document.getElementById('FieldTitle').value = "";
            $('#SelectFieldOptions').trumbowyg('html', '');

            let fieldid = document.getElementById('FieldID');
            fieldid.setAttribute("hidden", true);
            let createEntry = document.getElementById('createEntry');
            createEntry.removeAttribute("hidden");
            let updateEntry = document.getElementById('updateEntry');
            updateEntry.setAttribute("hidden", true);
        }
    }

    function showCreateNewStatusDiv() {
        element = document.getElementById('createNewStatus');
        elementplus = document.getElementById('plusStatus');
        elementminus = document.getElementById('minusStatus');

        if ($('#createNewStatus').css('display') === 'block') {
            element.style.display = 'none';
            elementplus.style.display = '';
            elementminus.style.display = 'none';
            let createStatusEntry = document.getElementById('createStatusEntry');
            createStatusEntry.removeAttribute("hidden");
            let updateStatusEntry = document.getElementById('updateStatusEntry');
            updateStatusEntry.setAttribute("hidden", true);
        } else {
            element.style.display = 'block';
            elementplus.style.display = 'none';
            elementminus.style.display = '';
            document.getElementById('StatusCode').value = "";
            document.getElementById('StatusName').value = "";
            let createStatusEntry = document.getElementById('createStatusEntry');
            createStatusEntry.removeAttribute("hidden");
            let updateStatusEntry = document.getElementById('updateStatusEntry');
            updateStatusEntry.setAttribute("hidden", true);
        }
    }

    function showCreateNewSLADiv() {
        element = document.getElementById('createNewSLA');
        elementplus = document.getElementById('plusSLA');
        elementminus = document.getElementById('minusSLA');

        if ($('#createNewSLA').css('display') === 'block') {
            element.style.display = 'none';
            elementplus.style.display = '';
            elementminus.style.display = 'none';
            let createStatusEntry = document.getElementById('createStatusEntry');
            createStatusEntry.removeAttribute("hidden");
            let updateStatusEntry = document.getElementById('updateStatusEntry');
            updateStatusEntry.setAttribute("hidden", true);
        } else {
            element.style.display = 'block';
            elementplus.style.display = 'none';
            elementminus.style.display = '';
            document.getElementById('StatusCode').value = "";
            document.getElementById('StatusName').value = "";
            let createStatusEntry = document.getElementById('createStatusEntry');
            createStatusEntry.removeAttribute("hidden");
            let updateStatusEntry = document.getElementById('updateStatusEntry');
            updateStatusEntry.setAttribute("hidden", true);
        }
    }

    function deleteITSMField(FieldID, FieldName) {
        TableName = '<?php echo $TableName; ?>';
        console.log(TableName);
        console.log(FieldID);
        console.log(FieldName);
        xmlhttp.open("GET", "getdata.php?deleteITSMField=" + "&FieldID=" + FieldID + "&TableName=" + TableName + "&FieldName=" + FieldName, true);
        xmlhttp.send();

        message = "Field deleted";
        localStorage.setItem('pnotify', message);
        location.reload(true);
    }

    function deleteITSMStatus(StatusID) {
        xmlhttp.open("GET", "getdata.php?deleteITSMStatus=" + "&StatusID=" + StatusID, true);
        xmlhttp.send();

        message = "Status deleted";
        localStorage.setItem('pnotify', message);
        location.reload(true);
    }

    $(document).ready(function() {
        getITSMFields(<?php echo $ITSMID ?>);
    });
</script>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="card-group">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-gear"></i>
                        <a href="administration_modules.php"><?php echo "Modules" ?></a> <i class="fa fa-angle-double-right"></i> <a href="javascript:location.reload(true);"><?php echo _("$Name"); ?></a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="Name"><?php echo _("Name"); ?></label>
                                    <input type="text" class="form-control" id="Name" value="<?php echo $Name ?>" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMID; ?>);">
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="ShortElementName"><?php echo _("Shortname"); ?></label>
                                    <input type="text" class="form-control" id="ShortElementName" value="<?php echo $ShortElementName ?>" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMID; ?>);">
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="TableName"><?php echo _("Tablename"); ?></label>
                                    <input type="text" class="form-control" id="TableName" value="<?php echo $TableName ?>" disabled>
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="TypeIcon" data-bs-toggle="tooltip" data-bs-title="Fontawesome code"><?php echo _("Icon"); ?></label>
                                    <input type="text" class="form-control" id="TypeIcon" value="<?php echo $TypeIcon ?>" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMID; ?>);">
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="Description"><?php echo _("Description"); ?></label>
                                    <input type="text" class="form-control" id="Description" value="<?php echo $Description ?>" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMID; ?>);">
                                </div>
                            </div>

                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="DoneStatus"><?php echo _("Done Status"); ?></label>
                                    <input type="text" class="form-control" id="DoneStatus" value="<?php echo $DoneStatus ?>" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMID; ?>);">
                                </div>
                            </div>

                            <div class=" col-md-6 col-sm-6 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="Active"><?php echo _("Active"); ?></label>

                                    <select class='form-control' id="Active" name="Active" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMID; ?>);">
                                        <?php
                                        $sql = "SELECT ID, itsm_modules.Active
                                                FROM itsm_modules
                                                WHERE ID = $ITSMID";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            if ($row['Active'] == 1) {
                                                echo "<option value='1' selected='select'>" . _("Active") . "</option>";
                                                echo "<option value='0'>" . _("Inactive") . "</option>";
                                            } else {
                                                echo "<option value='1'>" . _("Active") . "</option>";
                                                echo "<option value='0' selected='select'>" . _("Inactive") . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class=" col-md-6 col-sm-6 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="GroupID" data-bs-toggle="tooltip" data-bs-title="Is available on main view page"><?php echo _("User Group"); ?></label>

                                    <select class='form-control' id="GroupID" name="GroupID" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMID; ?>);">
                                        <?php
                                        $sql = "SELECT usergroups.ID AS ID, usergroups.GroupName AS GroupName, modules.Name AS ModuleName, usergroups.Active AS Active, 'Standard' AS Type
                                                FROM usergroups
                                                LEFT JOIN modules ON usergroups.RelatedModuleID = modules.ID
                                                WHERE modules.Active='1'
                                                UNION
                                                SELECT system_groups.ID AS ID, system_groups.GroupName AS GroupName, modules.Name AS ModuleName, system_groups.Active AS Active, 'System' AS Type
                                                FROM system_groups
                                                LEFT JOIN modules ON system_groups.RelatedModuleID = modules.ID
                                                WHERE system_groups.ID !='100000' AND modules.Active='1'
                                                ORDER BY Active DESC, GroupName ASC";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            $GroupID = $row['ID'];
                                            $GroupName = $row['GroupName'];
                                            if ($ModuleGroupID == $GroupID) {
                                                echo "<option value='$GroupID' selected='select'>" . _($GroupName) . "</option>";
                                            } else {
                                                echo "<option value='$GroupID'>" . _($GroupName) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="RestorePoints" class="ms-0"><?php echo _("Restore Points"); ?></label>
                                    <select id="RestorePoints" name="RestorePoints" class="form-control" required>
                                        <option value=''></option>
                                        <?php
                                        $sql = "SELECT ID, Date, Description
                                                FROM db_backups
                                                WHERE RelatedModule = $ITSMID
                                                ORDER BY Date DESC";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            echo "<option value='" . $row['ID'] . "'>" . convertToDanishTimeFormat($row['Date']) . " (" . $row['Description'] . ")</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class=" col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <button class="btn btn-sm btn-danger" id="restoreITSM" name="restoreITSM" onclick="restoreDatabaseBackup();" data-bs-toggle="tooltip" data-bs-title="This will restore this ITSM module to chosen restore point"><?php echo _("Restore"); ?></button>
                                <button class="btn btn-sm btn-danger" id="backupITSM" name="backupITSM" onclick="deleteDatabaseBackup();" data-bs-toggle="tooltip" data-bs-title="This will delete restore point"><?php echo _("Delete"); ?></button>
                                <button class="btn btn-sm btn-success" id="backupITSM" name="backupITSM" onclick="createDatabaseBackup(<?php echo $ITSMID ?>);" data-bs-toggle="tooltip" data-bs-title="This will create restore point"><?php echo _("Create"); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8 col-sm-8 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseFields" aria-expanded="true" aria-controls="collapseFields"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="tooltip" data-bs-title="more" data-bs-toggle="collapse" data-bs-target="#collapseFields" aria-expanded="true" aria-controls="collapseFields"></i> <?php echo _("Fields") ?></a>
                        <div class="collapse show" id="collapseFields">
                            <div class="float-end">
                                <a href="javascript:;" onclick="showCreateNewDiv();"><button class='btn btn-sm btn-success' id="plus"><?php echo _("Create new"); ?></button></a>
                                <a href="javascript:;" onclick="showCreateNewDiv();"><button class='btn btn-sm btn-success' id="minus" style="display: none;"><?php echo _("Cancel"); ?></button></a>
                            </div>
                            <div class="card-body">
                                <div id="createNew" style="display: none;">
                                    <div class="row">
                                        <div id="ITSMFieldID" style="display: none;"></div>

                                        <form id="FieldSpecs">
                                            <div class="row">

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="FieldOrder"><?php echo _("Order"); ?></label>
                                                        <select class='form-control' id="FieldOrder" name="FieldOrder">
                                                            <?php
                                                            $sql = "SELECT FieldOrder, Label
                                                                    FROM `itsm_fieldslist`
                                                                    WHERE RelatedTypeID = '$ITSMID'
                                                                    ORDER BY FieldOrder ASC";
                                                            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                            while ($row = mysqli_fetch_array($result)) {
                                                                $FieldOrder = $row['FieldOrder'];
                                                                $Label = $row['Label'];
                                                                echo "<option value='$FieldOrder'>$Label</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="FieldLabel" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Field description") ?>"><?php echo _("Label"); ?></label>
                                                        <input type="text" class="form-control" id="FieldLabel" name="FieldLabel">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="FieldDefaultValue" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Field default value") ?>"><?php echo _("Default value"); ?></label>
                                                        <input type="text" class="form-control" id="FieldDefaultValue" name="FieldDefaultValue">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="FieldType"><?php echo _("Type"); ?></label>
                                                        <select class='form-control' id="FieldType" name="FieldType">
                                                            <?php
                                                            $sql = "SELECT ID, TypeName
                                                                    FROM forms_fieldslist_types";
                                                            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                            while ($row = mysqli_fetch_array($result)) {
                                                                $ID = $row['ID'];
                                                                $TypeName = $row['TypeName'];
                                                                echo "<option value='$ID'>$TypeName</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="FieldTitle" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Field Title") ?>"><?php echo _("Field title"); ?></label>
                                                        <input type="text" class="form-control" id="FieldTitle" name="FieldTitle">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="FieldWidth"><?php echo _("Field Width"); ?></label>
                                                        <select class='form-control' id="FieldWidth" name="FieldWidth">
                                                            <option value='1'>1</option>
                                                            <option value='2'>2</option>
                                                            <option value='3'>3</option>
                                                            <option value='4' selected='selected'>4</option>
                                                            <option value='5'>5</option>
                                                            <option value='6'>6</option>
                                                            <option value='7'>7</option>
                                                            <option value='8'>8</option>
                                                            <option value='9'>9</option>
                                                            <option value='10'>10</option>
                                                            <option value='11'>11</option>
                                                            <option value='12'>12</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="LookupTable" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Lookup Table") ?>"><?php echo _("Lookup Table"); ?></label>
                                                        <select class='form-control' id="LookupTable" name="LookupTable">
                                                            <option value=''></option>
                                                            <?php
                                                            $sql = "SHOW TABLES;";
                                                            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                            while ($row = mysqli_fetch_array($result)) {
                                                                $ResultTableName = $row[0];
                                                                echo "<option value='$ResultTableName'>$ResultTableName</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="LookupField" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Lookup Field - example: ID") ?>"><?php echo _("Lookup Field"); ?></label>
                                                        <input type="text" class="form-control" id="LookupField" name="LookupField">
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="LookupFieldResultTable" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Lookup Field Result - example: Firstname;Lastname") ?>"><?php echo _("Lookup Field Result (Table View)"); ?></label>
                                                        <input type="text" class="form-control" id="LookupFieldResultTable" name="LookupFieldResultTable">
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="LookupFieldResultView" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Lookup Field Result - example: Firstname;Lastname") ?>"><?php echo _("Lookup Field Result (Modal View)"); ?></label>
                                                        <input type="text" class="form-control" id="LookupFieldResultView" name="LookupFieldResultView">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="RelationShowField" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Only one field can be set as Primary relation field, but at least one must be set per CI, this field is used for relations generation") ?>"><?php echo _("Primary Relation Field"); ?></label>
                                                        <select class='form-control' id="RelationShowField" name="RelationShowField">
                                                            <option value='0'>0</option>
                                                            <option value='1'>1</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="ImportSourceField" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Import file source field that this field is to get data from") ?>"><?php echo _("Import Source Field"); ?></label>
                                                        <input type="text" class="form-control" id="ImportSourceField">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="SyncSourceField" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Set field if this field is to be automatically updated via sync task - source field that this field is to get data from") ?>"><?php echo _("Sync Source Field"); ?></label>
                                                        <input type="text" class="form-control" id="SyncSourceField" name="SyncSourceField">
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="Addon" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Addon feature for this field") ?>"><?php echo _("Addon"); ?></label>
                                                        <select class='form-control' id="Addon" name="Addon">
                                                            <option value=''></option>
                                                            <?php
                                                            $sql = "SELECT ID, Name
                                                    FROM modules_addons;";
                                                            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                            while ($row = mysqli_fetch_array($result)) {
                                                                $AddonID = $row["ID"];
                                                                $AddonName = $row["Name"];
                                                                echo "<option value='$AddonID'>$AddonName</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="FieldTitle" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Only Select fields - Example: <option value=car1>car1</option><option value=car2>car2</option>") ?>"><?php echo _("Select Field Options"); ?></label>
                                                        <textarea type="text" class="form-control" id="SelectFieldOptions" name="SelectFieldOptions" rows="5"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <div class="input-group input-group-static mb-4">
                                                        <label for="RelationsLookup" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Automatically create relations with this field as relations source - Example: <target table>,<targetField>") ?>"><?php echo _("Child relations lookup"); ?></label>
                                                        <textarea type="text" class="form-control" id="RelationsLookup" name="RelationsLookup" rows="5"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-3 col-sm-3 col-xs-12">

                                                    <div class="form-check form-switch" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Show field but disabled for edit") ?>">
                                                        <input class="form-check-input" type="checkbox" id="Locked">
                                                        <label class="form-check-label" for="Locked"><?php echo _("Locked"); ?></label>
                                                    </div>
                                                    <div class="form-check form-switch" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Is this field required") ?>">
                                                        <input class="form-check-input" type="checkbox" id="Required">
                                                        <label class="form-check-label" for="Required"><?php echo _("Required"); ?></label>
                                                    </div>
                                                    <div class="form-check form-switch" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Make this field searchable from search bar") ?>">
                                                        <label class="form-check-label" for="Indexed"><?php echo _("Searchable"); ?></label>
                                                        <input class="form-check-input" type="checkbox" id="Indexed" name="Indexed">
                                                    </div>
                                                </div>
                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                    <div class="form-check form-switch" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Hide this field from Table views") ?>">
                                                        <label class="form-check-label" for="HideTables"><?php echo _("Hide Tables"); ?></label>
                                                        <input class="form-check-input" type="checkbox" id="HideTables" name="HideTables">
                                                    </div>
                                                    <div class="form-check form-switch" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Hide this field from Form views") ?>">
                                                        <label class="form-check-label" for="HideForms"><?php echo _("Hide Forms"); ?></label>
                                                        <input class="form-check-input" type="checkbox" id="HideForms" name="HideForms">
                                                    </div>
                                                </div>

                                            </div>
                                        </form>

                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div id="updateEntry" style="display: hidden;">
                                                <a href="javascript:updateITSMField('<?php echo $TableName ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Update"); ?></button></a>
                                            </div>
                                            <div id="createEntry" style="display: hidden;">
                                                <a href="javascript:createITSMField('<?php echo $TableName ?>','<?php echo $ITSMID ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Create"); ?></button></a>
                                            </div>
                                        </div>
                                    </div>
                                    <br><br>
                                </div>
                                <table id="itsm_fieldslist" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                </table>

                                <?php //include("./administration_modules_fields_incl.php"); 
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseStatusCodes" aria-expanded="false" aria-controls="collapseStatusCodes"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="tooltip" data-bs-title="more" data-bs-toggle="collapse" data-bs-target="#collapseStatusCodes" aria-expanded="false" aria-controls="collapseStatusCodes"></i> <?php echo _("Status Codes") ?></a>
                        <div class="collapse" id="collapseStatusCodes">
                            <div class="float-end">
                                <a href="javascript:;" onclick="showCreateNewStatusDiv();"><button class='btn btn-sm btn-success' id="plusStatus"><?php echo _("Create new"); ?></button></a>
                                <a href="javascript:;" onclick="showCreateNewStatusDiv();"><button class='btn btn-sm btn-success' id="minusStatus" style="display: none;"><?php echo _("Cancel"); ?></button></a>
                            </div>
                            <div class="card-body">
                                <div id="createNewStatus" style="display: none;">
                                    <div class="row">
                                        <input type="hidden" class="form-control" id="StatusRowID" disabled="true">

                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="StatusCode"><?php echo _("Status ID"); ?></label>
                                                <input type="text" class="form-control" id="StatusCode">
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="StatusName" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Status Name") ?>"><?php echo _("Label"); ?></label>
                                                <input type="text" class="form-control" id="StatusName">
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="form-check form-switch" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Activate if this status must be SLA Supported") ?>">
                                                <input class="form-check-input" type="checkbox" id="SLASupported" value="true">
                                                <label class="form-check-label" for="SLASupported"><?php echo _("SLA Supported"); ?></label>
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="form-check form-switch" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Activate if this status is a closed status") ?>">
                                                <input class="form-check-input" type="checkbox" id="ClosedStatus" value="true">
                                                <label class="form-check-label" for="ClosedStatus"><?php echo _("Closed Status"); ?></label>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div id="updateStatusEntry" style="display: hidden;">
                                                <button id="updateStatusEntry" class='btn btn-sm btn-success float-end' onclick="updateITSMStatus(<?php echo $ITSMID ?>);"><?php echo _("Update"); ?></button>
                                            </div>
                                            <div id="createStatusEntry" style="display: hidden;">
                                                <a href="javascript:createITSMStatus('<?php echo $ITSMID ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Create"); ?></button></a>
                                            </div>
                                        </div>
                                    </div>
                                    <br><br>
                                </div>
                                <?php include("./administration_modules_statuscodes_incl.php"); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseSLA" aria-expanded="false" aria-controls="collapseSLA"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="tooltip" data-bs-title="more" data-bs-toggle="collapse" data-bs-target="#collapseSLA" aria-expanded="false" aria-controls="collapseSLA"></i> <?php echo _("SLA") ?></a>
                        <div class="collapse" id="collapseSLA">
                            <div class="float-end">
                                <a href="javascript:;" onclick="showCreateNewSLADiv();"><button class='btn btn-sm btn-success' id="plusSLA" hidden><?php echo _("Hide"); ?></button></a>
                                <a href="javascript:;" onclick="showCreateNewSLADiv();"><button class='btn btn-sm btn-success' id="minusSLA" style="display: none;"><?php echo _("Cancel"); ?></button></a>
                            </div>
                            <div class="card-body">
                                <div id="createNewSLA" style="display: none;">
                                    <div class="row">
                                        <div id="SLAID" hidden></div>

                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="Priority1" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Reaction Time in minutes - example: 30 as in 30 minutes") ?>"><?php echo _("Priority 1"); ?></label>
                                                <input type="text" class="form-control" id="Priority1">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="Priority2" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Reaction Time in minutes - example: 30 as in 30 minutes") ?>"><?php echo _("Priority 2"); ?></label>
                                                <input type="text" class="form-control" id="Priority2">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="Priority3" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Reaction Time in minutes - example: 30 as in 30 minutes") ?>"><?php echo _("Priority 3"); ?></label>
                                                <input type="text" class="form-control" id="Priority3">
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="Priority4" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Reaction Time in minutes - example: 30 as in 30 minutes") ?>"><?php echo _("Priority 4"); ?></label>
                                                <input type="text" class="form-control" id="Priority4">
                                            </div>
                                        </div>
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div id="updateSLAEntry" style="display: hidden;">
                                                <button id="updateSLAEntry" class='btn btn-sm btn-success float-end' onclick="updateSLA();"><?php echo _("Update"); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                    <br><br>
                                </div>
                                <?php include("./administration_modules_sla_incl.php"); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- /page content -->
<?php include("./footer.php"); ?>