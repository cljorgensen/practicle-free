<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>

<?php
$FormID = $_GET['formid'];

$sql = "SELECT forms.ID, forms.FormsName, forms.TableName, forms.Description, forms.CreatedBy, forms.Created,
		forms.LastEditedBy, forms.LastEdited, forms.RelatedModuleID, modules.Name, forms.Active, forms.RelatedWorkFlow
		FROM forms
		LEFT JOIN forms_fieldslist ON forms.ID = forms_fieldslist.RelatedFormID
        LEFT JOIN modules ON forms.RelatedModuleID = modules.ID
		WHERE forms.ID = $FormID";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

while ($row = mysqli_fetch_array($result)) {
    $FormsName = $row['FormsName'];
    $TableName = $row['TableName'];
    $Description = $row['Description'];
    $CreatedBy = $row['CreatedBy'];
    $LastEditedBy = $row['LastEditedBy'];
    $LastEdited = $row['LastEdited'];
    $LastEditedDanishDateTime = convertToDanishTimeFormat($row['LastEdited']);
    $RelatedModuleID = $row['RelatedModuleID'];
    $ModuleName = _($row['Name']);
    $Active = $row['Active'];
    $Created = convertToDanishTimeFormat($row['Created']);
    $CreatedBy = $row['CreatedBy'];
    $RelatedWorkFlow = $row['RelatedWorkFlow'];
}

?>

<script>
    function checkFormsTableConsistency(FormID) {
        document.getElementById("formsConsistencyCheck").hidden = false;
        document.getElementById("formsConsistencyCheckBody").innerHTML = "";

        $.ajax({
            url: "getdata.php?checkFormsTableConsistency",
            data: {
                FormID: FormID
            },
            type: 'GET',
            cache: false,
            success: function(data) {
                var obj = JSON.parse(data);
                for (var i = 0; i < obj.length; i++) {
                    document.getElementById("formsConsistencyCheckBody").innerHTML = obj[i].Result;
                }
            }
        });
    }

    function showCreateNewDiv() {
        element = document.getElementById("createNew");
        elementplus = document.getElementById("plus");
        elementminus = document.getElementById("minus");

        if ($("#createNew").css("display") === "block") {
            element.style.display = "none";
            elementplus.style.display = "";
            elementminus.style.display = "none";
            let createEntry = document.getElementById("createEntry");
            createEntry.removeAttribute("hidden");
            let updateEntry = document.getElementById("updateEntry");
            updateEntry.setAttribute("hidden", true);
            $("#FieldSpecs input[name!='FieldOrder'][name!='FieldType'], #myForm select[name!='FieldOrder'][name!='FieldType']").each(function() {
                // Reset the value of each field
                $(this).val('');
            });
            $("#FieldSpecs input[type='checkbox']").prop("checked", false);
            let collapseSelectOptionsMenu = document.getElementById("collapseSelectOptionsMenu");
            collapseSelectOptionsMenu.hidden = false;
            let collapseLookupMenu = document.getElementById("collapseLookupMenu");
            collapseLookupMenu.hidden = false;
        } else {
            element.style.display = "block";
            elementplus.style.display = "none";
            elementminus.style.display = "";
            document.getElementById("FormFieldID").value = "";
            document.getElementById("FieldLabel").value = "";
            document.getElementById("FieldDefaultValue").value = "";
            document.getElementById("FieldTitle").value = "";

            let fieldid = document.getElementById("FormFieldID");
            fieldid.setAttribute("hidden", true);
            let createEntry = document.getElementById("createEntry");
            createEntry.removeAttribute("hidden");
            let updateEntry = document.getElementById("updateEntry");
            updateEntry.setAttribute("hidden", true);
        }
    }

    $(document).ready(function() {
        getFormFields(<?php echo $FormID ?>, '<?php echo $UserLanguageCode ?>');
    });
</script>
<div id="FormIDEdit" data-value="<?php echo $FormID; ?>" style="display: none;"></div>
<div id="UserLanguageCode" data-value="<?php echo $UserLanguageCode; ?>" style="display: none;"></div>
<div id="CurrentPage" data-value="<?php echo $CurrentPage; ?>" style="display: none;"></div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="card-group">
            <div class="col-md-5 col-sm-5 col-xs-12">
                <div class="card">
                    <div class="card-header"><i class="fas fa-check-square"></i>
                        <a href="administration_formsbuilder.php"><?php echo _("Forms") ?></a> <i class="fa fa-angle-double-right"></i> <a href="javascript:location.reload(true);"><?php echo _("$FormsName"); ?></a>
                        <div class="float-end">
                            <ul class='navbar-nav justify-content-end'>
                                <li class='nav-item dropdown pe-2'>
                                    <a href='javascript:;' class='nav-link text-body p-0 position-relative' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                                        &nbsp;&nbsp;<i class="fa-solid fa-ellipsis-vertical" title="<?php echo _('Actions') ?>"></i>&nbsp;&nbsp;
                                    </a>
                                    <ul class='dropdown-menu dropdown-menu-end p-2 me-sm-n4' aria-labelledby='dropdownMenuButton'>
                                        <li class='mb-2'>
                                            <a class='dropdown-item border-radius-md' onclick="resetFormsData(<?php echo $FormID; ?>);">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto'>
                                                            <?php echo _('Reset forms data') ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='mb-2'>
                                            <a class='dropdown-item border-radius-md' onclick="checkFormsTableConsistency(<?php echo $FormID; ?>);">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto'>
                                                            <?php echo _('Check Table Consistency') ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='mb-2'>
                                            <a class='dropdown-item border-radius-md' onclick="resetFormsSortOrder(<?php echo $FormID; ?>);">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto'>
                                                            <?php echo _('Reset field sort order') ?>
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
                                <div class="accordion width" id="accordionFormsAdministration">
                                    <div class="card">
                                        <div id="collapseGeneral" class="collapse width" data-bs-parent="#accordionFormsAdministration">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="FormsName"><?php echo _("Name"); ?></label>
                                                            <input type="text" class="form-control" id="FormsName" value="<?php echo $FormsName ?>" onchange="updateFormsName(<?php echo $FormID; ?>,'<?php echo $TableName ?>');">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-sm-12 col-xs-12" hidden=true>
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="TableName"><?php echo _("Table Name"); ?></label>
                                                            <input type="text" class="form-control" id="TableName" value="<?php echo $TableName ?>">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="Description"><?php echo _("Description"); ?></label>
                                                            <input type="text" class="form-control" id="Description" value="<?php echo $Description ?>" onchange="updateFormsDescription(<?php echo $FormID; ?>);">
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="RelatedWorkFlow"><?php echo _("Related workflow"); ?></label>

                                                            <select class='form-control' id="RelatedWorkFlow" name="RelatedWorkFlow" onchange="updateFormsRelatedWorkFlow(<?php echo $FormID; ?>,this.value);">
                                                                <option value='0'></option>
                                                                <?php
                                                                $sql = "SELECT workflows_template.ID, workflows_template.WorkflowName
                                                                        FROM workflows_template
                                                                        WHERE Active = 1";
                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                while ($row = mysqli_fetch_array($result)) {

                                                                    $ID = $row['ID'];
                                                                    $WorkflowName = $row['WorkflowName'];

                                                                    if ($RelatedWorkFlow == $ID) {
                                                                        echo "<option value='$ID' selected='select'>" . _("$WorkflowName") . "</option>";
                                                                    } else {
                                                                        echo "<option value='$ID'>" . _("$WorkflowName") . "</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="RelatedModule"><?php echo _("Related Module"); ?></label>

                                                            <select class='form-control' id="RelatedModule" name="RelatedModule" onchange="updateFormsModule(<?php echo $FormID; ?>);">
                                                                <option value='16'><?php echo _("Generic forms") ?></option>
                                                                <?php
                                                                $sql = "SELECT itsm_modules.ID, itsm_modules.Name
                                                                        FROM itsm_modules
                                                                        WHERE active = 1";
                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                while ($row = mysqli_fetch_array($result)) {

                                                                    $TempModuleID = $row['ID'];
                                                                    $ModuleName = $row['Name'];

                                                                    if ($RelatedModuleID == $TempModuleID) {
                                                                        echo "<option value='$TempModuleID' selected='select'>" . _("$ModuleName") . "</option>";
                                                                    } else {
                                                                        echo "<option value='$TempModuleID'>" . _("$ModuleName") . "</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="ActiveStatus"><?php echo _("Active"); ?></label>

                                                            <select class='form-control' id="ActiveStatus" name="ActiveStatus" onchange="updateFormStatus(<?php echo $FormID; ?>);">
                                                                <?php
                                                                $sql = "SELECT ID, forms.Active
                                                                        FROM forms
                                                                        WHERE ID = $FormID";
                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                while ($row = mysqli_fetch_array($result)) {

                                                                    if ($FormID == $DefaultChecklistAlarms || $FormID == $DefaultChecklistOversights) {
                                                                        echo "<option value=''>" . _("Dette er en default checkliste og kan derfor ikke deaktiveres") . "</option>";
                                                                    } else {
                                                                        if ($row['Active'] == 1) {
                                                                            echo "<option value='1' selected='select'>" . _("Active") . "</option>";
                                                                            echo "<option value='0'>" . _("Not Active") . "</option>";
                                                                        } else {
                                                                            echo "<option value='1'>" . _("Active") . "</option>";
                                                                            echo "<option value='0' selected='select'>" . _("Not Active") . "</option>";
                                                                        }
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div id="collapseFields" class="collapse width show" data-bs-parent="#accordionFormsAdministration">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <table id="form_fieldslist" class="table table-responsive table-borderless table-hover" cellspacing="0">
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
                        <div class="float-end">
                            <a href="javascript:;" onclick="showCreateNewDiv();">
                                <button class="badge bg-gradient-success" id="plus"><?php echo _("Create new"); ?></button>
                            </a>
                            <a href="javascript:;" onclick="showCreateNewDiv();">
                                <button class="badge bg-gradient-info" id="minus" style="display: none;"><?php echo _("Cancel"); ?></button>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="FormFieldID" hidden></div>
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
                                <form id="FieldSpecs">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="FieldOrder"><?php echo _("Order"); ?></label><code>*</code>
                                                <select class='form-control' id="FieldOrder" name="FieldOrder" required>
                                                    <?php
                                                    $sql = "SELECT FieldOrder, FieldLabel
                                                        FROM forms_fieldslist
                                                        WHERE RelatedFormID = '$FormID'
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
                                                <label for="FieldDefaultValue" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Field default value") ?>"><?php echo _("Default value"); ?></label>
                                                <input type="text" class="form-control" id="FieldDefaultValue" name="FieldDefaultValue">
                                            </div>
                                        </div>

                                        <div class="col-md-3 col-sm-3 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="FieldType"><?php echo _("Type"); ?></label><code>*</code>
                                                <select class='form-control' id="FieldType" name="FieldType" required>
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
                                                    <option value='4'>4</option>
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
                                                <div id="itsm_menu">
                                                    <span id="collapseLookupMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseLookup" aria-expanded="false" aria-controls="collapseLookup"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Lookup configuration") ?>"></i> <?php echo _("Lookup") ?></a></span>
                                                    <span id="collapseSelectOptionsMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseSelectOptions" aria-expanded="false" aria-controls="collapseSelectOptions"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Select Options") ?>"></i> <?php echo _("Select Options") ?></a></span><span class="badge badge-circle badge-info" id="unreadITSMComments"></span>
                                                    <span id="collapseGroupFilterMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseGroupFilter" aria-expanded="false" aria-controls="collapseGroupFilter"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Group filter") ?>"></i> <?php echo _("Group filter") ?></a></span><span class="badge badge-circle badge-info" id="GroupsFilteredMenu"></span>
                                                </div>
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
                                                                            <label for="ResultFields" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Choose wich field(s) should be returned as options") ?>"><?php echo _("Result fields"); ?></label>
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
                                                                                <a href="javascript:createSelectOption('<?php echo $FormID ?>','form');" class="btn btn-sm btn-success float-end"><?php echo _("Create"); ?></a>
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
                                                                                <a href="javascript:addGroupFilter('form');" class="btn btn-sm btn-success float-end"><?php echo _("Add"); ?></a>
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
                                                <div class="form-check" title="<?php echo _("Make field disabled on create") ?>">
                                                    <input class="form-check-input" type="checkbox" id="LockedCreate" name="LockedCreate">
                                                    <label class="form-check-label" for="LockedCreate"><?php echo _("Locked on create"); ?></label>
                                                </div>
                                                <div class="form-check" title="<?php echo _("Make field disabled on view/edit") ?>">
                                                    <input class="form-check-input" type="checkbox" id="LockedView" name="LockedView">
                                                    <label class="form-check-label" for="LockedView"><?php echo _("Locked on view"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Is this field required") ?>">
                                                    <input class="form-check-input" type="checkbox" id="Required" name="Required">
                                                    <label class="form-check-label" for="Required"><?php echo _("Required"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Make this field searchable from search bar") ?>">
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
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Hide this field from Table views") ?>">
                                                    <input class="form-check-input" type="checkbox" id="HideTables" name="HideTables">
                                                    <label class="form-check-label" for="HideTables"><?php echo _("Hide Tables"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Hide this field from view Form") ?>">
                                                    <input class="form-check-input" type="checkbox" id="HideForms" name="HideForms">
                                                    <label class="form-check-label" for="HideForms"><?php echo _("Hide Forms"); ?></label>
                                                </div>
                                                <div class="form-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("This adds an empty value to a select box and only for that") ?>">
                                                    <input class="form-check-input" type="checkbox" id="AddEmpty" name="AddEmpty">
                                                    <label class="form-check-label" for="AddEmpty"><?php echo _("Add empty row"); ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div id="updateEntry" style="display: hidden;">
                                    <a href="javascript:updateFormfield('<?php echo $TableName ?>','<?php echo $FormID ?>','<?php echo $UserLanguageCode ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Update"); ?></button></a>
                                </div>
                                <div id="createEntry" style="display: hidden;">
                                    <a href="javascript:createFormsField('<?php echo $TableName ?>','<?php echo $FormID ?>','<?php echo $UserLanguageCode ?>');"><button class='btn btn-sm btn-success float-end'><?php echo _("Create"); ?></button></a>
                                </div>
                            </div>
                            <br><br>
                        </div>
                        <?php //include("./administration_formsbuilder_fields_incl.php"); 
                        ?>
                    </div>
                </div>
                <div class="card" id="formsConsistencyCheck" hidden>
                    <div class="card-header"><?php echo _("Consistency") ?></div>
                    <div class="card-body">
                        <div id="formsConsistencyCheckBody"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- /page content -->
<?php include("./footer.php"); ?>