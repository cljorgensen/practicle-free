<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>

<?php

$ITSMTypeID = $_GET['id'];

$sql = "SELECT itsm_modules.ID, itsm_modules.Name, itsm_modules.ShortElementName, itsm_modules.TableName, itsm_modules.Type, itsm_modules.DoneStatus, itsm_modules.Description, itsm_modules.CreatedBy, itsm_modules.Created, itsm_modules.TypeIcon,
		itsm_modules.SLA,itsm_modules.LastEditedBy, itsm_modules.LastEdited, itsm_modules.Active, itsm_modules.GroupID, itsm_modules.ImportSource, itsm_modules.Synchronization, itsm_modules.SyncTime, itsm_modules.LastSyncronized
		FROM itsm_modules
		WHERE itsm_modules.ID = $ITSMTypeID";

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
    $SLA = $row['SLA'];
    $Type = $row['Type'];
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

<div id="ITSMTypeIDEdit" data-value="<?php echo $ITSMTypeID; ?>" style="display: none;"></div>
<div id="UserLanguageCode" data-value="<?php echo $UserLanguageCode; ?>" style="display: none;"></div>
<div id="CurrentPage" data-value="<?php echo $CurrentPage; ?>" style="display: none;"></div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="card-group">
            <div class="col-md-5 col-sm-5 col-xs-12">
                <div class="card">
                    <div class="card-header"><i class="fa-solid fa-gear"></i>
                        <a href="administration_modules.php"><?php echo _("Modules") ?></a> <i class="fa fa-angle-double-right"></i> <a href="javascript:location.reload(true);"><?php echo _("$Name"); ?></a>
                        <div class="float-end">
                            <ul class='navbar-nav justify-content-end'>
                                <li class='nav-item dropdown pe-2'>
                                    <a href='javascript:;' class='nav-link text-body p-0 position-relative' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                                        &nbsp;&nbsp;<i class="fa-solid fa-ellipsis-vertical" title="<?php echo _('Actions') ?>"></i>&nbsp;&nbsp;
                                    </a>
                                    <ul class='dropdown-menu dropdown-menu-end p-2 me-sm-n4' aria-labelledby='dropdownMenuButton'>
                                        <li class='mb-2'>
                                            <a class='dropdown-item' onclick="resetITSMSortOrder(<?php echo $ITSMTypeID ?>);">
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
                                            <a class='dropdown-item' onclick="resetITSMModuleData(<?php echo $ITSMTypeID ?>);">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto' title='<?php echo $functions->translate("This deletes all data records for this module including logs, files, relations, workflows and more"); ?>'>
                                                            <?php echo _('Reset module data') ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='mb-2'>
                                            <a class='dropdown-item' onclick="checkITSMTableConsistency(<?php echo $ITSMTypeID ?>);">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto' title='<?php echo $functions->translate("This checks fields registered vs fields existing in database table"); ?>'>
                                                            <?php echo _('Check Table Consistency') ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <?php
                                        if (in_array("100000", $group_array)) {
                                        ?><li class='mb-2'>
                                                <a class='dropdown-item border-radius-md' onclick="activateSuperAdminMode();">
                                                    <div class='d-flex align-items-center py-1'>
                                                        <div class='ms-2'>
                                                            <h6 class='text-sm font-weight-normal my-auto'>
                                                                <?php echo _('Activate Super Admin mode') ?>
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        <?php
                                        }
                                        ?>

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
                                    <span id="collapseStatusCodesMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseStatusCodes" aria-expanded="false" aria-controls="collapseStatusCodes"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Status Codes") ?>"></i> <?php echo _("Status Codes") ?></a></span><span class="badge badge-circle badge-info" id="unreadITSMComments"></span>
                                    <span id="collapseSLAMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseSLA" aria-expanded="false" aria-controls="collapseSLA"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("SLA") ?>"></i> <?php echo _("SLA") ?></a></span>
                                    <span id="collapseEmailsMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseEmails" aria-expanded="false" aria-controls="collapseEmails"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Emails") ?>"></i> <?php echo _("Emails") ?></a></span>
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
                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="Name"><?php echo _("Name"); ?></label>
                                                            <input type="text" class="form-control" id="Name" value="<?php echo $Name ?>" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMTypeID; ?>);">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="ShortElementName"><?php echo _("Shortname"); ?></label>
                                                            <input type="text" class="form-control" id="ShortElementName" value="<?php echo $ShortElementName ?>" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMTypeID; ?>);">
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
                                                            <label for="TypeIcon" title="Fontawesome code"><?php echo _("Icon"); ?></label>
                                                            <input type="text" class="form-control" id="TypeIcon" value="<?php echo $TypeIcon ?>" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMTypeID; ?>);">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="Description"><?php echo _("Description"); ?></label>
                                                            <input type="text" class="form-control" id="Description" value="<?php echo $Description ?>" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMTypeID; ?>);">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="DoneStatus"><?php echo _("Done Status"); ?></label>
                                                            <input type="text" class="form-control" id="DoneStatus" value="<?php echo $DoneStatus ?>" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMTypeID; ?>);">
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="Active" title=""><?php echo _("Active"); ?></label>
                                                            <select class="form-control" id="Active" name="Active" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMTypeID; ?>);">
                                                                <?php
                                                                $sql = "SELECT ID, itsm_modules.Active
                                                                        FROM itsm_modules
                                                                        WHERE ID = $ITSMTypeID";
                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                while ($row = mysqli_fetch_array($result)) {
                                                                    if ($row['Active'] == 1) {
                                                                        echo "<option value=\"1\" selected>" . _("Active") . "</option>";
                                                                        echo "<option value=\"0\">" . _("Inactive") . "</option>";
                                                                    } else {
                                                                        echo "<option value=\"1\">" . _("Active") . "</option>";
                                                                        echo "<option value=\"0\" selected>" . _("Inactive") . "</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                                        <div class="input-group input-group-static mb-4" title="User in this role has the module available on main their menu">
                                                            <label for="GroupID"><?php echo _("Group"); ?></label>
                                                            <select class="form-control" id="GroupID" name="GroupID" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMTypeID; ?>);">
                                                                <option value=''></option>
                                                                <?php
                                                                $sql = "SELECT usergroups.ID AS GroupID, usergroups.GroupName AS GroupName, usergroups.Active AS Active
                                                                        FROM usergroups
                                                                        UNION
                                                                        SELECT system_groups.ID AS ID, system_groups.GroupName AS GroupName, system_groups.Active AS Active
                                                                        FROM system_groups
                                                                        WHERE Active = 1;";
                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                                                                // Temporary array to hold rows
                                                                $groups = [];

                                                                // Fetch rows into the array
                                                                while ($row = mysqli_fetch_array($result)) {
                                                                    $groups[] = [
                                                                        'GroupID' => $row['GroupID'],
                                                                        'GroupName' => $functions->translate($row['GroupName']),
                                                                        'Active' => $row['Active']
                                                                    ];
                                                                }

                                                                // Sort the array by GroupName ASC
                                                                usort($groups, function ($a, $b) {
                                                                    return strcmp($a['GroupName'], $b['GroupName']);
                                                                });

                                                                // Output the sorted options
                                                                foreach ($groups as $group) {
                                                                    $GroupID = $group['GroupID'];
                                                                    $GroupName = $group['GroupName'];
                                                                    if ($ModuleGroupID == $GroupID) {
                                                                        echo "<option value='$GroupID' selected='selected'>$GroupName</option>";
                                                                    } else {
                                                                        echo "<option value='$GroupID'>$GroupName</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>

                                                        </div>
                                                    </div>

                                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="Type" class="ms-0"><?php echo _("Type"); ?></label>
                                                            <select id="Type" name="Type" class="form-control" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMTypeID; ?>);" required>
                                                                <?php
                                                                $sql = "SELECT ID, Name
                                                                        FROM itsm_Types
                                                                        ORDER BY Name ASC";

                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                                                                // Create a temporary array to store the data
                                                                $tempArray = array();

                                                                while ($row = mysqli_fetch_array($result)) {
                                                                    $ID = $row['ID'];
                                                                    $Name = $functions->translate($row['Name']);
                                                                    $tempArray[] = array('ID' => $ID, 'Name' => $Name);
                                                                }

                                                                // Sort the array by column name (Name) in ascending order
                                                                usort($tempArray, function ($a, $b) {
                                                                    return $a['Name'] <=> $b['Name'];
                                                                });

                                                                // Output the options from the sorted array
                                                                foreach ($tempArray as $row) {
                                                                    $ID = $row['ID'];
                                                                    $Name = $row['Name'];

                                                                    if ($Type == $ID) {
                                                                        echo "<option value='$ID' selected='select'>$Name</option>";
                                                                    } else {
                                                                        echo "<option value='$ID'>$Name</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class=" col-md-6 col-sm-6 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="SLA"><?php echo _("SLA"); ?></label>

                                                            <select class='form-control' id="SLA" name="SLA" onchange="updateITSMValues(this.getAttribute('id'),<?php echo $ITSMTypeID; ?>);">
                                                                <?php
                                                                $sql = "SELECT ID, itsm_modules.SLA
                                                                        FROM itsm_modules
                                                                        WHERE ID = $ITSMTypeID";
                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                while ($row = mysqli_fetch_array($result)) {
                                                                    if ($row['SLA'] == 1) {
                                                                        echo "<option value='1' selected='select'>" . _("Yes") . "</option>";
                                                                        echo "<option value='0'>" . _("No") . "</option>";
                                                                    } else {
                                                                        echo "<option value='1'>" . _("Yes") . "</option>";
                                                                        echo "<option value='0' selected='select'>" . _("No") . "</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="RestorePoints" class="ms-0"><?php echo _("Restore Points"); ?></label>
                                                            <select id="RestorePoints" name="RestorePoints" class="form-control">
                                                                <option value=''></option>
                                                                <?php
                                                                $sql = "SELECT ID, Date, Description
                                                                        FROM db_backups
                                                                        WHERE RelatedModule = $ITSMTypeID
                                                                        ORDER BY Date DESC";
                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                while ($row = mysqli_fetch_array($result)) {
                                                                    echo "<option value='" . $row['ID'] . "'>" . convertToDanishTimeFormat($row['Date']) . " (" . $row['Description'] . ")</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                    </div>
                                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                                        <button class="btn btn-sm btn-danger float-end" id="btnrestoreDatabaseBackup" name="btnrestoreDatabaseBackup" onclick="restoreDatabaseBackup();" title="This will restore this ITSM module to chosen restore point"><?php echo _("Restore"); ?></button>
                                                        <button class="btn btn-sm btn-danger float-end" id="btndeleteDatabaseBackup" name="btndeleteDatabaseBackup" onclick="deleteDatabaseBackup();" title="This will delete restore point"><?php echo _("Delete"); ?></button>
                                                        <button class="btn btn-sm btn-success float-end" id="btncreateDatabaseBackup" name="btncreateDatabaseBackup" onclick="createDatabaseBackup(<?php echo $ITSMTypeID ?>);" title="This will create restore point"><?php echo _("Create"); ?></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div id="collapseStatusCodes" class="collapse width" data-bs-parent="#accordionITSMAdministration">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="float-end">
                                                        <a href="javascript:;" onclick="showCreateNewStatusDiv();"><button class='btn btn-sm btn-success' id="plusStatus"><?php echo _("Create"); ?></button></a>
                                                        <a href="javascript:;" onclick="showCreateNewStatusDiv();"><button class='btn btn-sm btn-success' id="minusStatus" style="display: none;"><?php echo _("Cancel"); ?></button></a>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="createNewStatus" style="display: none;">
                                                            <div class="row">
                                                                <input type="hidden" class="form-control" id="StatusRowID" disabled="true">

                                                                <div class="col-md-4 col-sm-4 col-xs-12">
                                                                    <div class="input-group input-group-static mb-4">
                                                                        <label for="StatusCode"><?php echo _("Status ID"); ?></label>
                                                                        <input type="text" class="form-control" id="StatusCode">
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-4 col-sm-4 col-xs-12">
                                                                    <div class="input-group input-group-static mb-4">
                                                                        <label for="StatusName" title="<?php echo _("Status Name") ?>"><?php echo _("Label"); ?></label>
                                                                        <input type="text" class="form-control" id="StatusName">
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-4 col-sm-4 col-xs-12">
                                                                    <div class="form-check" title="<?php echo _("Activate if this status must be SLA Supported") ?>">
                                                                        <input class="form-check-input" type="checkbox" id="SLASupported" name="SLASupported">
                                                                        <label class="form-check-label" for="SLASupported"><?php echo _("SLA Supported"); ?></label>
                                                                    </div>
                                                                    <div class="form-check" title="<?php echo _("Activate if this status is a closed status") ?>">
                                                                        <input class="form-check-input" type="checkbox" id="ClosedStatus" name="ClosedStatus">
                                                                        <label class="form-check-label" for="ClosedStatus"><?php echo _("Closed Status"); ?></label>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                                    <div id="updateStatusEntry" style="display: hidden;">
                                                                        <button id="updateStatusEntry" class='btn btn-sm btn-success float-end' onclick="updateITSMStatus(<?php echo $ITSMTypeID ?>);"><?php echo _("Update"); ?></button>
                                                                    </div>
                                                                    <div id="createStatusEntry" style="display: hidden;">
                                                                        <a href="javascript:createITSMStatus('<?php echo $ITSMTypeID ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Create"); ?></button></a>
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
                                    </div>
                                    <div class="card">
                                        <div id="collapseSLA" class="collapse width" data-bs-parent="#accordionITSMAdministration">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="float-end">
                                                        <a href="javascript:;" onclick="showCreateNewSLADiv();"><button class='btn btn-sm btn-success' id="plusSLA"><?php echo _("Create"); ?></button></a>
                                                        <a href="javascript:;" onclick="showCreateNewSLADiv();"><button class='btn btn-sm btn-success' id="minusSLA" style="display: none;"><?php echo _("Cancel"); ?></button></a>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="createNewSLA" style="display: none;">
                                                            <div class="row">
                                                                <div id="SLAID" hidden></div>

                                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                                    <div class="input-group input-group-static mb-4">
                                                                        <label for="Priority1" title="<?php echo _("Reaction Time in minutes - example: 30 as in 30 minutes") ?>"><?php echo _("Priority 1"); ?></label>
                                                                        <input type="text" class="form-control" id="Priority1">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                                    <div class="input-group input-group-static mb-4">
                                                                        <label for="Priority2" title="<?php echo _("Reaction Time in minutes - example: 30 as in 30 minutes") ?>"><?php echo _("Priority 2"); ?></label>
                                                                        <input type="text" class="form-control" id="Priority2">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                                    <div class="input-group input-group-static mb-4">
                                                                        <label for="Priority3" title="<?php echo _("Reaction Time in minutes - example: 30 as in 30 minutes") ?>"><?php echo _("Priority 3"); ?></label>
                                                                        <input type="text" class="form-control" id="Priority3">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                                    <div class="input-group input-group-static mb-4">
                                                                        <label for="Priority4" title="<?php echo _("Reaction Time in minutes - example: 30 as in 30 minutes") ?>"><?php echo _("Priority 4"); ?></label>
                                                                        <input type="text" class="form-control" id="Priority4">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                                    <div id="updateSLABtn" style="display: hidden;">
                                                                        <button class='btn btn-sm btn-success float-end' onclick="updateSLA();"><?php echo _("Update"); ?></button>
                                                                    </div>
                                                                    <div id="createSLABtn" style="display: hidden;">
                                                                        <a href="javascript:createITSMSLA('<?php echo $ITSMTypeID ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Create"); ?></button></a>
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
                                    <div class="card">
                                        <div id="collapseEmails" class="collapse width" data-bs-parent="#accordionITSMAdministration">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="float-end">
                                                        <a href="javascript:;" onclick="showCreateNewEmailDiv();"><button class='btn btn-sm btn-success' id="plusEmail"><?php echo _("Create"); ?></button></a>
                                                        <a href="javascript:;" onclick="showCreateNewEmailDiv();"><button class='btn btn-sm btn-success' id="minusEmail" style="display: none;"><?php echo _("Cancel"); ?></button></a>
                                                    </div>
                                                    <div class="card-body">
                                                        <div id="createNewEmail" style="display: none;">
                                                            <div class="row">
                                                                <div id="EmailID" hidden></div>

                                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                                    <div class="input-group input-group-static mb-4">
                                                                        <label for="ITSMEmail" title="<?php echo _("Add email addres") ?>"><?php echo _("Email"); ?></label>
                                                                        <input type="text" class="form-control" id="ITSMEmail">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3 col-sm-3 col-xs-12">
                                                                    <div class="form-check form-switch" title="<?php echo _("Activate if this email should be chosen as default") ?>">
                                                                        <input class="form-check-input" type="checkbox" id="ITSMDefaultEmail" value="true">
                                                                        <label class="form-check-label" for="ITSMDefaultEmail"><?php echo _("Default"); ?></label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                                    <div id="updateEmailEntry" style="display: hidden;">
                                                                        <button class='btn btn-sm btn-success float-end' onclick="updateITSMEmail('<?php echo $ITSMTypeID ?>');"><?php echo _("Update"); ?></button>
                                                                    </div>
                                                                    <div id="createEmailEntry" style="display: hidden;">
                                                                        <button class='btn btn-sm btn-success float-end' onclick="createITSMEmail('<?php echo $ITSMTypeID ?>');"><?php echo _("Create"); ?></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <br><br>
                                                        </div>
                                                        <?php include("./administration_modules_email_incl.php"); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div id="collapseFields" class="collapse width show" data-bs-parent="#accordionITSMAdministration">
                                            <div class="card-body">
                                                <div class="row">
                                                    <table id="itsm_fieldslist" class="table table-responsive table-borderless table-hover" cellspacing="0">
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
            <div class="col-md-7 col-sm-7 col-xs-12">
                <div class="card">
                    <div class="card-header">
                        <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseField" aria-expanded="true" aria-controls="collapseField">
                            <i class="fa-solid fa-circle-chevron-down" title="more"></i> <?php echo _("Field design") ?>
                        </a>
                        <div class="float-end">
                            <a href="javascript:;" onclick="showITSMCreateNewDiv();">
                                <button class="badge bg-gradient-success" id="plus"><?php echo _("Create"); ?></button>
                            </a>
                            <a href="javascript:;" onclick="showITSMCreateNewDiv();">
                                <button class="badge bg-gradient-info" id="minus" style="display: none;"><?php echo _("Cancel"); ?></button>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
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
                                <div id="ITSMFieldID" hidden></div>
                                <form id="FieldSpecs">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="FieldOrder"><?php echo _("Order"); ?></label><code>*</code>
                                                <select class='form-control' id="FieldOrder" name="FieldOrder" required>
                                                    <?php
                                                    $sql = "SELECT FieldOrder, FieldLabel
                                                                FROM itsm_fieldslist
                                                                WHERE RelatedTypeID = '$ITSMTypeID'
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

                                        <div class="col-md-3 col-sm-3 col-xs-12" id="FieldNameSuperAdmin" hidden>
                                            <div class="input-group input-group-static mb-4">
                                                <label for="FieldName" title="<?php echo _("Field Name") ?>"><?php echo _("Field Name"); ?></label><code>*</code>
                                                <input type="text" class="form-control" id="FieldName" name="FieldName">
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="FieldLabel" title="<?php echo _("Field description") ?>"><?php echo _("Label"); ?></label><code>*</code>
                                                <input type="text" class="form-control" id="FieldLabel" name="FieldLabel" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="FieldTitle" title="<?php echo _("Field Title") ?>"><?php echo _("Field title"); ?></label>
                                                <input type="text" class="form-control" id="FieldTitle" name="FieldTitle">
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="FieldType"><?php echo _("Type"); ?></label><code>*</code>
                                                <select class='form-control' id="FieldType" name="FieldType" required>
                                                    <?php
                                                    $sql = "SELECT ID, TypeName
                                                                FROM itsm_fieldslist_types";
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
                                                <label for="FieldDefaultValue" title="<?php echo _("Field default value") ?>"><?php echo _("Default value"); ?></label>
                                                <input type="text" class="form-control" id="FieldDefaultValue" name="FieldDefaultValue">
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
                                                <label for="Addon" title="<?php echo _("Addon feature for this field") ?>"><?php echo _("Addon"); ?></label>
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

                                        <div class="row">
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <div id="itsm_menu">
                                                        <span id="collapseLookupMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseLookup" aria-expanded="false" aria-controls="collapseLookup"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Lookup configuration") ?>"></i> <?php echo _("Lookup") ?></a></span>
                                                        <span id="collapseSelectOptionsMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseSelectOptions" aria-expanded="false" aria-controls="collapseSelectOptions"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Select Options") ?>"></i> <?php echo _("Select Options") ?></a></span><span class="badge badge-circle badge-info" id="SelectOptionsMenu"></span>
                                                        <span id="collapseGroupFilterMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseGroupFilter" aria-expanded="false" aria-controls="collapseGroupFilter"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Group filter") ?>"></i> <?php echo _("Group filter") ?></a></span><span class="badge badge-circle badge-info" id="GroupsFilteredMenu"></span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="accordion width" id="accordionMore">
                                                        <div class="card">
                                                            <div id="collapseLookup" class="collapse width" data-bs-parent="#accordionMore">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                                            <div class="input-group input-group-static mb-4">
                                                                                <label for="LookupTable" title="<?php echo _("Lookup Table") ?>"><?php echo _("Lookup Table"); ?></label>
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
                                                                                <label for="ResultFields" title="<?php echo _("Choose wich field(s) should be returned as options") ?>"><?php echo _("Result fields"); ?></label>
                                                                                <select class='form-control' id="ResultFields" name="ResultFields">
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                                            <div class="form-check pull-left" title="<?php echo _("Select this if you want a result with users fullname and username") ?>">
                                                                                <input class="form-check-input" type="checkbox" id="UserFullName" name="UserFullName">
                                                                                <label class="form-check-label" for="UserFullName"><?php echo _("User fullname and username"); ?></label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card">
                                                            <div id="collapseSelectOptions" class="collapse width" data-bs-parent="#accordionMore">
                                                                <div class="card-body">
                                                                    <div class="input-group input-group-static mb-4">
                                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                                            <div class="input-group input-group-static mb-4">
                                                                                <input type="text" class="form-control" id="SelectFieldOption" name="SelectFieldOption" data-exclude="true">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                                            <a href="javascript:createSelectOption('<?php echo $ITSMTypeID ?>','itsm');" class="btn btn-sm btn-success float-end"><?php echo _("Create"); ?></a>
                                                                        </div>
                                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                                            <table id="tableSelectOptions" class="table table-responsive table-borderless table-hover" cellspacing="0"></table>
                                                                        </div>
                                                                    </div>
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
                                                                                    <option value=''></option>
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
                                                                                    <a href="javascript:addGroupFilter('itsm');" class="btn btn-sm btn-success float-end"><?php echo _("Add"); ?></a>
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
                                                    <div class="form-check" title="<?php echo _("Primary Relation Field") ?>">
                                                        <input class="form-check-input" type="checkbox" id="RelationShowField" name="RelationShowField">
                                                        <label class="form-check-label" for="RelationShowField"><?php echo _("Primary"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("Default Field") ?>" id="DefaultFieldSuperAdmin" hidden>
                                                        <input class="form-check-input" type="checkbox" id="DefaultField" name="DefaultField">
                                                        <label class="form-check-label" for="DefaultField"><?php echo _("Default Field"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("Make field disabled on create") ?>">
                                                        <input class="form-check-input" type="checkbox" id="LockedCreate" name="LockedCreate">
                                                        <label class="form-check-label" for="LockedCreate"><?php echo _("Locked on create"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("Make field disabled on view/edit") ?>">
                                                        <input class="form-check-input" type="checkbox" id="LockedView" name="LockedView">
                                                        <label class="form-check-label" for="LockedView"><?php echo _("Locked on view"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("Make field required") ?>">
                                                        <input class="form-check-input" type="checkbox" id="Required" name="Required">
                                                        <label class="form-check-label" for="Required"><?php echo _("Required"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("Make searchable from search bar on content of this field") ?>">
                                                        <input class="form-check-input" type="checkbox" id="Indexed" name="Indexed">
                                                        <label class="form-check-label" for="Indexed"><?php echo _("Searchable"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("Notes fields can get full height so all content is shown on view page") ?>">
                                                        <input class="form-check-input" type="checkbox" id="FullHeight" name="FullHeight">
                                                        <label class="form-check-label" for="FullHeight"><?php echo _("Full view height"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-xs-12">
                                                <div class="card">
                                                    <div class="form-check" title="<?php echo _("Deactivate this field from Table views") ?>">
                                                        <input class="form-check-input" type="checkbox" id="HideTables" name="HideTables">
                                                        <label class="form-check-label" for="HideTables"><?php echo _("Deactivate Tables"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("Deactivate this field from Form views") ?>">
                                                        <input class="form-check-input" type="checkbox" id="HideForms" name="HideForms">
                                                        <label class="form-check-label" for="HideForms"><?php echo _("Deactivate Forms"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("Retrieve field but hide it") ?>">
                                                        <input class="form-check-input" type="checkbox" id="Hidden" name="Hidden">
                                                        <label class="form-check-label" for="Hidden"><?php echo _("Hidden"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("This adds an empty value to a select box and only for that") ?>">
                                                        <input class="form-check-input" type="checkbox" id="AddEmpty" name="AddEmpty">
                                                        <label class="form-check-label" for="AddEmpty"><?php echo _("Add empty row"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("This will make the field a normal and simple text result") ?>">
                                                        <input class="form-check-input" type="checkbox" id="LabelType" name="LabelType">
                                                        <label class="form-check-label" for="LabelType"><?php echo _("Simple text"); ?></label>
                                                    </div>
                                                    <div class="form-check" title="<?php echo _("Place field on the right side of form") ?>">
                                                        <input class="form-check-input" type="checkbox" id="RightColumn" name="RightColumn">
                                                        <label class="form-check-label" for="RightColumn"><?php echo _("Place on right side"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                            </form>
                            <div class="row">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div id="updateEntry" style="display: hidden;">
                                        <a href="javascript:updateITSMField('<?php echo $TableName ?>','<?php echo $ITSMTypeID ?>','<?php echo $UserLanguageCode ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Update"); ?></button></a>
                                    </div>
                                    <div id="createEntry" style="display: hidden;">
                                        <a href="javascript:createITSMField('<?php echo $ITSMTypeID ?>','<?php echo $UserLanguageCode ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Create"); ?></button></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card" id="ITSMConsistencyCheck" hidden>
                    <div class="card-header"><?php echo _("Consistency") ?></div>
                    <div class="card-body">
                        <div id="ITSMConsistencyCheckBody"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- /page content -->
<?php include("./footer.php"); ?>