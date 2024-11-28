<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
} else {
    notgranted($CurrentPage);
}
?>

<?php
$CITypeID = $_GET['ciid'];

$sql = "SELECT cmdb_cis.ID, cmdb_cis.Name, cmdb_cis.TableName, cmdb_cis.Description, cmdb_cis.CreatedBy, cmdb_cis.Created, cmdb_cis.GroupID,
		cmdb_cis.LastEditedBy, cmdb_cis.LastEdited, cmdb_cis.Active, cmdb_cis.ImportSource, cmdb_cis.Synchronization, cmdb_cis.SyncTime, cmdb_cis.LastSyncronized
		FROM cmdb_cis
		WHERE cmdb_cis.ID = $CITypeID";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

while ($row = mysqli_fetch_array($result)) {
    $Name = $row['Name'];
    $TableName = $row['TableName'];
    $Description = $row['Description'];
    $CreatedBy = $row['CreatedBy'];
    $LastEditedBy = $row['LastEditedBy'];
    $LastEdited = $row['LastEdited'];
    $LastEditedDanishDateTime = convertToDanishTimeFormat($row['LastEdited']);
    $CMDBGroupID = $row['GroupID'];
    $Active = $row['Active'];
    $Created = convertToDanishTimeFormat($row['Created']);
    $CreatedBy = $row['CreatedBy'];
    $ImportSource = $row['ImportSource'];
    $SyncTime = $row['SyncTime'];
    $Synchronization = $row['Synchronization'];
    $LastSynced = $row['LastSyncronized'];
}

?>

<div id="CITypeID" data-value="<?php echo $CITypeID; ?>" style="display: none;"></div>
<div id="UserLanguageCode" data-value="<?php echo $UserLanguageCode; ?>" style="display: none;"></div>
<div id="CurrentPage" data-value="<?php echo $CurrentPage; ?>" style="display: none;"></div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="card-group">
            <div class="col-md-5 col-sm-5 col-xs-12">
                <div class="card">
                    <div class="card-header"><i class="fa fa-desktop"></i>
                        <a href="administration_cmdb.php"><?php echo _("Assets") . " " . _("(CMDB)"); ?></a> <i class="fa fa-angle-double-right"></i> <a href="javascript:location.reload(true);"><?php echo _("$Name"); ?></a>
                        <div class="float-end">
                            <ul class='navbar-nav justify-content-end'>
                                <li class='nav-item dropdown pe-2'>
                                    <a href='javascript:;' class='nav-link text-body p-0 position-relative' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                                        &nbsp;&nbsp;<i class="fa-solid fa-ellipsis-vertical" title="<?php echo _('Actions') ?>"></i>&nbsp;&nbsp;
                                    </a>
                                    <ul class='dropdown-menu dropdown-menu-end p-2 me-sm-n4' aria-labelledby='dropdownMenuButton'>
                                        <li class='mb-2'>
                                            <a class='dropdown-item border-radius-md' onclick="resetCI(<?php echo $CITypeID; ?>);">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto'>
                                                            <?php echo _('Reset') ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='mb-2'>
                                            <a class='dropdown-item border-radius-md' onclick="syncCI(<?php echo $CITypeID ?>);">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto'>
                                                            <?php echo _('Syncronize') ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='mb-2'>
                                            <a class='dropdown-item border-radius-md' onclick="resetCISortOrder(<?php echo $CITypeID ?>);">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto'>
                                                            <?php echo _('Reset field sort order') ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='mb-2'>
                                            <a class='dropdown-item' onclick="checkCMDBTableConsistency(<?php echo $CITypeID ?>);">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto' title='<?php echo $functions->translate("This checks fields registered vs fields existing in database table"); ?>'>
                                                            <?php echo _('Check Table Consistency') ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div id="itsm_menu">
                                    <span id="collapseGeneralMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseGeneral" aria-expanded="false" aria-controls="collapseGeneral"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Geenral") ?>"></i> <?php echo _("General") ?></a></span>
                                    <span id="collapseFieldsMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseFields" aria-expanded="false" aria-controls="collapseFields"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Fields") ?>"></i> <?php echo _("Fields") ?></a></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="accordion width" id="accordionITSMAdministration">
                                    <div class="card">
                                        <div id="collapseGeneral" class="collapse width" data-bs-parent="#accordionITSMAdministration">
                                            <div class="card-body">
                                                <div class="row">

                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <div class="input-group input-group-static mb-4">
                                                                <label for="Name"><?php echo _("Name"); ?></label>
                                                                <input type="text" class="form-control" id="Name" value="<?php echo $Name ?>" onchange="updateCIValues(this.getAttribute('id'),<?php echo $CITypeID; ?>);">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <div class="input-group input-group-static mb-4">
                                                                <label for="TableName"><?php echo _("Table Name"); ?></label>
                                                                <input type="text" class="form-control" id="TableName" value="<?php echo $TableName ?>" disabled>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <div class="input-group input-group-static mb-4">
                                                                <label for="Description"><?php echo _("Description"); ?></label>
                                                                <input type="text" class="form-control" id="Description" value="<?php echo $Description ?>" onchange="updateCIValues(this.getAttribute('id'),<?php echo $CITypeID; ?>);">
                                                            </div>
                                                        </div>

                                                        <div class=" col-md-6 col-sm-6 col-xs-12">
                                                            <div class="input-group input-group-static mb-4">
                                                                <label for="Active"><?php echo _("Active"); ?></label>

                                                                <select class='form-control' id="Active" name="Active" onchange="updateCIValues(this.getAttribute('id'),<?php echo $CITypeID; ?>);">
                                                                    <?php
                                                                    $sql = "SELECT ID, cmdb_cis.Active
                                                                            FROM cmdb_cis
                                                                            WHERE ID = $CITypeID";
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
                                                                <label for="GroupID" data-bs-toggle="tooltip" data-bs-title="Is available on main view page"><?php echo _("Usergroup"); ?></label>
                                                                <select class='form-control' id="GroupID" name="GroupID" onchange="updateCIValues(this.getAttribute('id'),<?php echo $CITypeID; ?>);">
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
                                                                        if ($CMDBGroupID == $GroupID) {
                                                                            echo "<option value='$GroupID' selected='select'>" . _($GroupName) . "</option>";
                                                                        } else {
                                                                            echo "<option value='$GroupID'>" . _($GroupName) . "</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <div class="input-group input-group-static mb-4" data-bs-toggle="tooltip" data-bs-data-bs-toggle="tooltip" data-bs-title="<?php echo _("Path to data import source fil"); ?>">
                                                                <label for="ImportSource"><?php echo _("Import Source"); ?></label>
                                                                <input type="text" class="form-control" id="ImportSource" onchange="updateCIValues(this.getAttribute('id'),<?php echo $CITypeID; ?>);" value="<?php echo $ImportSource ?>">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                                            <div class="input-group input-group-static mb-4" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Activate synchronization"); ?>">
                                                                <label for="Synchronization"><?php echo _("Synchronization"); ?></label>

                                                                <select class='form-control' id="Synchronization" name="Synchronization" onchange="updateCIValues(this.getAttribute('id'),'<?php echo $CITypeID; ?>');">
                                                                    <?php
                                                                    $sql = "SELECT ID, cmdb_cis.Synchronization
                                                                            FROM cmdb_cis
                                                                            WHERE ID = $CITypeID";
                                                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                    while ($row = mysqli_fetch_array($result)) {
                                                                        if ($row['Synchronization'] == 1) {
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

                                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                                            <div class="input-group input-group-static mb-4" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Daily Sync Time - example: 6:00"); ?>">
                                                                <label for="SyncTime"><?php echo _("Sync Time"); ?></label>
                                                                <input type="text" class="form-control" id="SyncTime" onchange="updateCIValues(this.getAttribute('id'),<?php echo $CITypeID; ?>);" value="<?php echo $SyncTime ?>">
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                                            <div class="input-group input-group-static mb-4" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Data last syncronized"); ?>">
                                                                <label for="LastSynced"><?php echo _("Last Synced"); ?></label>
                                                                <input type="text" class="form-control" id="LastSynced" onchange="updateCIValues(this.getAttribute('id'),<?php echo $CITypeID; ?>);" value="<?php echo $LastSynced ?>">
                                                            </div>
                                                        </div>
                                                        <form action="upload.php" method="post" enctype="multipart/form-data">
                                                            <label for="file">Choose a file:</label>
                                                            <input type="file" class="form-control" id="file" name="file">
                                                            <button type="submit" class="btn btn-sm btn-success" title="<?php echo _("Here you manually can upload a json or csv file that you can import and syncronize from") ?>"><?php echo _("Upload") ?></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div id="collapseFields" class="collapse width show" data-bs-parent="#accordionITSMAdministration">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <table id="cmdb_ci_fieldslist" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 col-sm-7 col-xs-12">
                <div class="card">
                    <div class="card-header"><?php echo _("Field design") ?>
                        <div id="ciid_edit" hidden><?php echo $CITypeID ?></div>
                        <div class="float-end">
                            <div class="btn-group">
                                <a href="javascript:;" onclick="toggleCMDBCreateNewDiv();">
                                    <button class='badge bg-gradient-success' id="plus"><?php echo _("Create new"); ?></button>
                                </a>
                                <a href="javascript:;" onclick="toggleCMDBCreateNewDiv();">
                                    <button class='badge bg-gradient-info' id="minus" style="display: none;"><?php echo _("Cancel"); ?></button>
                                </a>
                            </div>
                            <div class="btn-group" style="margin-left: 10px;"> <!-- Adjust the margin as needed -->
                                <form id="uploadForm" enctype="multipart/form-data">
                                    <input type="file" id="fileInput" name="file" style="display: none;">
                                    <button class="badge bg-gradient-info" type="button" onclick="chooseFile()" title="<?php echo _("You can upload json or csv file and create fields from them") ?>"><?php echo _("Upload") ?></button>
                                </form>
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <div id="columnHeaders" hidden></div>
                        <div id="columnHeadersBtn" hidden>
                            <button class="badge bg-gradient-info" onclick="cancelCollectCheckedHeaders();"><?php echo _("Cancel") ?></button>
                            <button class="badge bg-gradient-success" onclick="collectCheckedHeaders('cmdb','<?php echo $CITypeID ?>');"><?php echo _("Create") ?></button>
                        </div>
                        <div id="createNew" style="display: none;">
                            <div class="row">
                                <div class="collapse show" id="collapseField">
                                    <div class="row">
                                        <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseTags" aria-expanded="false" aria-controls="collapseTags">
                                            <i class="fa-solid fa-circle-info float-end" title="<?php echo _("Help") ?>"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div id="collapseTags" class="collapse">
                                    <div class="card-body">
                                        <div class="col-12">
                                            <small>
                                                <b><?php echo _("Standard values") ?></b>: <?php echo _("If you want check fields to be default checked you must add the text checked to the default value"); ?>
                                            </small>
                                            <br>
                                            <small>
                                                <b><?php echo _("Remove fields") ?></b>: <?php echo _("Do not delete standard fields. If you feel they are not necessary - disable them in table view and modal view"); ?>
                                            </small>
                                            <br>
                                            <small>
                                                <b><?php echo _("Field widths") ?></b>: <?php echo _("Field widths are determined using Bootstrap's grid system. A row is 12 blocks wide, so you can set a field's width by specifying how many blocks it should occupy. For example, setting the width to 6 will make the field half the row's width"); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div id="CMDBFieldID" hidden></div>
                                <form id="FieldSpecs">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="FieldOrder"><?php echo _("Order"); ?></label><code>*</code>
                                                <select class='form-control' id="FieldOrder" name="FieldOrder" required>
                                                    <?php
                                                    $sql = "SELECT FieldOrder, FieldLabel
                                                            FROM cmdb_ci_fieldslist
                                                            WHERE RelatedCITypeID = '$CITypeID'
                                                            ORDER BY FieldOrder ASC";
                                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        $FieldOrder = $row['FieldOrder'];
                                                        $Label = $row['FieldLabel'];
                                                        echo "<option value='$FieldOrder'>$Label</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="FieldLabel" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Field description") ?>"><?php echo _("Label"); ?></label><code>*</code>
                                                <input type="text" class="form-control" id="FieldLabel" name="FieldLabel" required>
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
                                                <label for="FieldType"><?php echo _("Type"); ?></label><code>*</code>
                                                <select class='form-control' id="FieldType" name="FieldType" required>
                                                    <?php
                                                    $sql = "SELECT ID, TypeName
                                                            FROM cmdb_fieldslist_types";
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
                                                <label for="FieldDefaultValue" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Field default value") ?>"><?php echo _("Default value"); ?></label>
                                                <input type="text" class="form-control" id="FieldDefaultValue" name="FieldDefaultValue">
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="FieldWidth"><?php echo _("Field Width"); ?></label>
                                                <select class='form-control' id="FieldWidth" name="FieldWidth" required>
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
                                            <div class="row">
                                                <span id="collapseSyncMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseSync" aria-expanded="false" aria-controls="collapseSync"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Syncronization from import source") ?>"></i> <?php echo _("Syncronization") ?></a></span>
                                                <span id="collapseSelectOptionsMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseSelectOptions" aria-expanded="false" aria-controls="collapseSelectOptions"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Select Options") ?>"></i> <?php echo _("Select Options") ?></a></span>
                                                <span id="collapseRelationsMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseRelations" aria-expanded="false" aria-controls="collapseRelations"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Make relation associations to other assets and its fields, choose table and field that should automatically get child related to this asset on this field in case of equal values") ?>"></i> <?php echo _("Subordinate") . " " . _("relations") ?></a></span>
                                                <span id="collapseLookupMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseLookup" aria-expanded="false" aria-controls="collapseLookup"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Lookup configuration") ?>"></i> <?php echo _("Lookup") ?></a></span>
                                                <span id="collapseGroupFilterMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseGroupFilter" aria-expanded="false" aria-controls="collapseGroupFilter"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Group filter") ?>"></i> <?php echo _("Group filter") ?></a></span><span class="badge badge-circle badge-info" id="GroupsFilteredMenu"></span>
                                            </div>

                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class="accordion width" id="accordionMore">
                                                    <div class="card">
                                                        <div id="collapseLookup" class="collapse width" data-bs-parent="#accordionMore">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                        <div class="input-group input-group-static mb-4">
                                                                            <label for="LookupTable" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Lookup Table") ?>"><?php echo _("Lookup Table"); ?></label>
                                                                            <select class='form-control' id="LookupTable" name="LookupTable" onchange="updateLookupField(this.value);">
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
                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                        <div class="input-group input-group-static mb-4">
                                                                            <label for="ResultFields" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Choose wich field should be returned as result in the select box") ?>"><?php echo _("Result field"); ?></label>
                                                                            <select class='form-control' id="ResultFields" name="ResultFields">
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                        <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Select this if you want a result with users fullname and username") ?>">
                                                                            <input class="form-check-input" type="checkbox" id="UserFullName" name="UserFullName">
                                                                            <label class="form-check-label" for="UserFullName"><?php echo _("User fullname and username"); ?></label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card">
                                                        <div id="collapseSync" class="collapse width" data-bs-parent="#accordionMore">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                        <div class="input-group input-group-static mb-4">
                                                                            <label for="ImportSourceField" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Import file source field that this field is to get data from") ?>"><?php echo _("Import Source Field"); ?></label>
                                                                            <input type="text" class="form-control" id="ImportSourceField" name="ImportSourceField">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                        <div class="input-group input-group-static mb-4">
                                                                            <label for="SyncSourceField" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Set field if this field is to be automatically updated via sync task - source field that this field is to get data from") ?>"><?php echo _("Sync Source Field"); ?></label>
                                                                            <input type="text" class="form-control" id="SyncSourceField" name="SyncSourceField">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card">
                                                        <div id="collapseRelations" class="collapse width" data-bs-parent="#accordionMore">
                                                            <div class="card-body">
                                                                <div class="row">
                                                                    <div class="col-md-12 col-sm-12 col-xs-12" id="RelationDefinitions">
                                                                        <div class="input-group input-group-static mb-4">
                                                                            <label for="RelationsLookup" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Automatically create relations with this field as relations source - Example: <target table>,<targetField>") ?>"><?php echo _("Add"); ?>&ensp;<a href="javascript:createNewTableRelationDefinitions('<?php echo $CITypeID ?>');"><i class="fa-solid fa-plus fa-sm" title="<?php echo _("Insert Concat User fullname example") ?>"></i></a></label>
                                                                            <div class="col-md-12 col-sm-12 col-xs-12" id="divRelationDefinitions" hidden>
                                                                                <br>
                                                                                <div class="row">
                                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                                        <div class="input-group input-group-static mb-4">
                                                                                            <label for="LookupTable2" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Lookup Table") ?>"><?php echo _("Lookup Table"); ?></label>
                                                                                            <select class='form-control' id="LookupTable2" name="LookupTable2" onchange="updateLookupField2(this.value);">
                                                                                                <option value=''></option>
                                                                                                <?php
                                                                                                $sql = "SELECT TableName, Name
                                                                                                        FROM cmdb_cis
                                                                                                        WHERE Active = '1'";
                                                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                                                while ($row = mysqli_fetch_array($result)) {
                                                                                                    $ResultTableName = $row['TableName'];
                                                                                                    $CIName = $row['Name'];
                                                                                                    echo "<option value='$ResultTableName'>$CIName</option>";
                                                                                                }
                                                                                                ?>
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                                        <div class="input-group input-group-static mb-4">
                                                                                            <label for="LookupField2" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Lookup Field - example: ID") ?>"><?php echo _("Lookup Field"); ?></label>
                                                                                            <select class='form-control' id="LookupField2" name="LookupField2">
                                                                                            </select>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="row">
                                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                                        <a href="javascript:createRelationLookup('<?php echo $CITypeID ?>');" class="btn btn-sm btn-success float-end"><?php echo _("Create"); ?></a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <table id="tableRelationDefinitions" class="table table-responsive table-borderless table-hover" cellspacing="0"></table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card">
                                                        <div id="collapseSelectOptions" class="collapse width" data-bs-parent="#accordionMore">
                                                            <div class="card-body">
                                                                <div class="input-group input-group-static mb-4">
                                                                    <label for="SelectFieldOption" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Add new option value") ?>"><?php echo _("Add"); ?>&ensp;<a href="javascript:divCreateSelectFieldOptions();"><i class="fa-solid fa-plus fa-sm" title="<?php echo _("Click to insert some example select options") ?>"></i></a></label>
                                                                    <div class="col-md-12 col-sm-12 col-xs-12" id="divCreateSelectFieldOptions" hidden>
                                                                        <br>
                                                                        <div class="row">
                                                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                                                <div class="input-group input-group-static mb-4">
                                                                                    <input type="text" class="form-control" id="SelectFieldOption" name="SelectFieldOption" data-exclude="true">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                                                <a href="javascript:createSelectOption('<?php echo $CITypeID ?>','ci');" class="btn btn-sm btn-success float-end"><?php echo _("Create"); ?></a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <table id="tableSelectOptions" class="table table-responsive table-borderless table-hover" cellspacing="0"></table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="card">
                                                        <div id="collapseGroupFilter" class="collapse width" data-bs-parent="#accordionMore">
                                                            <div class="card-body">
                                                                <div>
                                                                    <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseGF" aria-expanded="false" aria-controls="collapseGF">
                                                                        <i class="fa-solid fa-circle-info" title="<?php echo _("Help") ?>"></i>
                                                                    </a>
                                                                </div>
                                                                <div id="collapseGF" class="collapse">
                                                                    <div class="card-body">
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                                <small>
                                                                                    <?php echo _("Gruppe filter er mulighed for at filtrere på hvilke grupper der kan se pågældende felt. Du kan tilføje flere grupper. Hvis der ikke er tilføjet nogle grupper så kan alle se feltet."); ?>
                                                                                </small>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="input-group input-group-static mb-4">
                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                        <div class="input-group input-group-static mb-4">
                                                                            <select class="form-control" id="GroupIDGroupFilter" name="GroupIDGroupFilter" data-exclude="true">
                                                                                <?php
                                                                                $sql = "SELECT * FROM (
                                                                                            SELECT usergroups.ID AS ID, usergroups.GroupName AS GroupName, usergroups.Active AS Active, 'Standard' AS Type
                                                                                            FROM usergroups
                                                                                            WHERE usergroups.Active = 1
                                                                                            UNION
                                                                                            SELECT system_groups.ID AS ID, system_groups.GroupName AS GroupName, system_groups.Active AS Active, 'System' AS Type
                                                                                            FROM system_groups
                                                                                            WHERE system_groups.ID != '100000'
                                                                                        ) AS combined_results
                                                                                        ORDER BY Active DESC, GroupName ASC;";

                                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                                $options = array();

                                                                                while ($row = mysqli_fetch_array($result)) {
                                                                                    $row['TranslatedGroupName'] = $functions->translate($row['GroupName']);
                                                                                    $options[] = $row;
                                                                                }

                                                                                // Sort the array by the translated group name
                                                                                usort($options, function ($a, $b) {
                                                                                    return strcmp($a['TranslatedGroupName'], $b['TranslatedGroupName']);
                                                                                });

                                                                                foreach ($options as $option) {
                                                                                    $GroupID = $option['ID'];
                                                                                    $TranslatedGroupName = $option['TranslatedGroupName'];
                                                                                    echo "<option value='$GroupID'>" . $TranslatedGroupName . "</option>";
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                                                <a href="javascript:addGroupFilter('ci');" class="btn btn-sm btn-success float-end"><?php echo _("Add"); ?></a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                                        <table id="tableGroupFilterOptions" class="table table-responsive table-borderless table-hover" cellspacing="0"></table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="card">
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Primary Relation Field") ?>">
                                                    <input class="form-check-input" type="checkbox" id="RelationShowField" name="RelationShowField">
                                                    <label class="form-check-label" for="RelationShowField"><?php echo _("Primary"); ?></label>
                                                </div>
                                                <div class="form-check" title="<?php echo _("Make field disabled on create") ?>">
                                                    <input class="form-check-input" type="checkbox" id="LockedCreate" name="LockedCreate">
                                                    <label class="form-check-label" for="LockedCreate"><?php echo _("Locked on create"); ?></label>
                                                </div>
                                                <div class="form-check" title="<?php echo _("Make field disabled on view/edit") ?>">
                                                    <input class="form-check-input" type="checkbox" id="LockedView" name="LockedView">
                                                    <label class="form-check-label" for="LockedView"><?php echo _("Locked on view"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Make field required") ?>">
                                                    <input class="form-check-input" type="checkbox" id="Required" name="Required">
                                                    <label class="form-check-label" for="Required"><?php echo _("Required"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Make searchable from search bar on content of this field") ?>">
                                                    <input class="form-check-input" type="checkbox" id="Indexed" name="Indexed">
                                                    <label class="form-check-label" for="Indexed"><?php echo _("Searchable"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Notes fields can get full height so all content is shown on view page") ?>">
                                                    <input class="form-check-input" type="checkbox" id="FullHeight" name="FullHeight">
                                                    <label class="form-check-label" for="FullHeight"><?php echo _("Full view height"); ?></label>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="card">
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Deactivate this field from Table views") ?>">
                                                    <input class="form-check-input" type="checkbox" id="HideTables" name="HideTables">
                                                    <label class="form-check-label" for="HideTables"><?php echo _("Deactivate Tables"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Deactivate this field from Form views") ?>">
                                                    <input class="form-check-input" type="checkbox" id="HideForms" name="HideForms">
                                                    <label class="form-check-label" for="HideForms"><?php echo _("Deactivate Forms"); ?></label>
                                                </div>
                                                <div class="form-check" title="<?php echo _("Retrieve field but hide it") ?>">
                                                    <input class="form-check-input" type="checkbox" id="Hidden" name="Hidden">
                                                    <label class="form-check-label" for="Hidden"><?php echo _("Hidden"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("This adds an empty value to a select box and only for that") ?>">
                                                    <input class="form-check-input" type="checkbox" id="AddEmpty" name="AddEmpty">
                                                    <label class="form-check-label" for="AddEmpty"><?php echo _("Add empty row"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("This will make the field a normal and simple text result") ?>">
                                                    <input class="form-check-input" type="checkbox" id="LabelType" name="LabelType">
                                                    <label class="form-check-label" for="LabelType"><?php echo _("Simple text"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Place field on the right side of form") ?>">
                                                    <input class="form-check-input" type="checkbox" id="RightColumn" name="RightColumn">
                                                    <label class="form-check-label" for="RightColumn"><?php echo _("Place on right side"); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div id="updateEntry" style="display: hidden;">
                                    <a href="javascript:updateCIField('<?php echo $TableName ?>', '<?php echo $CITypeID ?>','<?php echo $UserLanguageCode ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Update"); ?></button></a>
                                </div>
                                <div id="createEntry" style="display: hidden;">
                                    <a href="javascript:createCIField('<?php echo $CITypeID ?>', '<?php echo $UserLanguageCode ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Create"); ?></button></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card" id="CMDBConsistencyCheck" hidden>
                    <div class="card-header"><?php echo _("Consistency") ?></div>
                    <div class="card-body">
                        <div id="CMDBConsistencyCheckBody"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- /page content -->
<?php include("./footer.php"); ?>