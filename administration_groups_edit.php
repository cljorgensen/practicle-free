<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100033", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>
<?php
$GroupID = $_GET['groupid'];

$sql = "SELECT ID AS ID, GroupName AS GroupName, Description AS Description, RelatedModuleID AS RelatedModuleID, Active AS Active, 'Personal' AS Type
        FROM usergroups
        WHERE usergroups.ID = '$GroupID'
        UNION
        SELECT ID AS ID, GroupName AS GroupName, Description AS Description, RelatedModuleID AS RelatedModuleID, Active AS Active, 'System' AS Type
        FROM system_groups
        WHERE system_groups.ID = '$GroupID'";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
    $GroupIDVal = $row['ID'];
    $GroupNameVal = $functions->translate($row['GroupName']);
    $GroupDescriptionVal = $row['Description'];
    $RelatedModuleIDActiveVal = $row['RelatedModuleID'];
    $GroupActiveVal = $row['Active'];
    $GroupType = $row['Type'];
}
?>

<script>
    function removeUsersFromGroup(groupId) {
        activateSpinner();
        // Get the multi-select element
        const multiSelect = document.getElementById('UsersInGroup');

        // Get all the selected options
        const selectedOptions = Array.from(multiSelect.selectedOptions).map(option => option.value);

        // Send the selected values to an AJAX call
        const vData = {
            selectedValues: selectedOptions,
            groupId: groupId
        };

        // Retrieve the options from the UsersInGroup select box
        const usersInGroupOptions = Array.from(multiSelect.options).map(option => ({
            value: option.value,
            text: option.text
        }));

        $.ajax({
            url: "./getdata.php?removeUsersFromGroup",
            data: vData,
            type: "GET",
            success: function(data) {
                var obj = JSON.parse(data);
                for (var i = 0; i < obj.length; i++) {
                    var Result = obj[i].Result;
                    if (Result == "success") {
                        pnotify(Result, "success");
                    } else {
                        pnotify(Result, "danger");
                    }
                }
            },
            complete: function(data) {
                // Remove selected options from the multi-select element
                selectedOptions.forEach(optionValue => {
                    const optionToRemove = multiSelect.querySelector(`option[value="${optionValue}"]`);
                    if (optionToRemove) {
                        multiSelect.removeChild(optionToRemove);
                    }
                });

                // Add the removed options to the AvaliableUsers select box
                const avaliableUsersSelect = document.getElementById('AvaliableUsers');
                selectedOptions.forEach(optionValue => {
                    const optionToMove = document.createElement('option');

                    // Find the corresponding option from the usersInGroupOptions array
                    const matchingOption = usersInGroupOptions.find(option => option.value === optionValue);

                    if (matchingOption) {
                        // Set the value and text of the option
                        optionToMove.value = matchingOption.value;
                        optionToMove.text = matchingOption.text;
                        avaliableUsersSelect.appendChild(optionToMove);
                    }
                });
                deactivateSpinner();
            },
        });
    }


    function addUsersToGroup(groupId) {
        activateSpinner();
        // Get the multi-select element
        const multiSelect = document.getElementById('AvaliableUsers');

        // Get all the selected options
        const selectedOptions = Array.from(multiSelect.selectedOptions).map(option => option.value);

        // Send the selected values to an AJAX call
        const vData = {
            selectedValues: selectedOptions,
            groupId: groupId
        };

        // Retrieve the options from the AvaliableUsers select box
        const avaliableUsersOptions = Array.from(multiSelect.options).map(option => ({
            value: option.value,
            text: option.text
        }));

        $.ajax({
            url: "./getdata.php?addUsersToGroup",
            data: vData,
            type: "GET",
            success: function(data) {
                var obj = JSON.parse(data);
                for (var i = 0; i < obj.length; i++) {
                    var Result = obj[i].Result;
                    if (Result == "success") {
                        pnotify(Result, "success");
                    } else {
                        pnotify(Result, "danger");
                    }
                }
            },
            complete: function(data) {
                deactivateSpinner();

                // Remove selected options from the multi-select element
                selectedOptions.forEach(optionValue => {
                    const optionToRemove = multiSelect.querySelector(`option[value="${optionValue}"]`);
                    if (optionToRemove) {
                        multiSelect.removeChild(optionToRemove);
                    }
                });

                // Add the selected options to the UsersInGroup select box
                const usersInGroupSelect = document.getElementById('UsersInGroup');
                selectedOptions.forEach(optionValue => {
                    const optionToMove = document.createElement('option');

                    // Find the corresponding option from the avaliableUsersOptions array
                    const matchingOption = avaliableUsersOptions.find(option => option.value === optionValue);

                    if (matchingOption) {
                        // Set the value and text of the option
                        optionToMove.value = matchingOption.value;
                        optionToMove.text = matchingOption.text;
                        usersInGroupSelect.appendChild(optionToMove);
                    }
                });
            },
        });
    }
</script>

<div class="row">
    <div class="col-md-8 col-sm-8 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header card-header"><i class="fas fa-user-friends"></i> <a href="administration_groups.php"><?php echo _("Groups"); ?></a> <i class="fa fa-angle-right fa-sm"></i> <a href="javascript:location.reload();"><?php echo $GroupNameVal; ?></a></div>
                <div class="card-body">
                    <form id="FormUserGroup">
                        <div id="GroupType" name="GroupType" hidden><?php echo $GroupType; ?></div>
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _("Name"); ?></label>
                                    <input type="text" class="form-control" id="GroupName" name="GroupName" value="<?php echo $GroupNameVal; ?>">
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _("Related module"); ?></label>
                                    <select id="RelatedModuleID" name="RelatedModuleID" class="form-control" required>
                                        <?php
                                        $TempArray = [];
                                        $sql = "SELECT itsm_modules.ID, itsm_modules.Name
                                                FROM itsm_modules
                                                WHERE itsm_modules.Active=1
                                                ORDER BY itsm_modules.Name ASC;";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            $TempArray[] = array("ID" => $row["ID"], "Name" => _($row["Name"]));
                                        }
                                        $TempArray[] = array("ID" => 12, "Name" => "System");
                                        usort($TempArray, fn ($a, $b) => $a['Name'] <=> $b['Name']);

                                        foreach ($TempArray as $Key => $Value) {
                                            $ID = $Value["ID"];
                                            $Name = $Value["Name"];
                                            if ($RelatedModuleIDActiveVal == $ID) {
                                                echo "<option value=\"$ID\" selected=\"true\">$Name</option>";
                                            } else {
                                                echo "<option value=\"$ID\">$Name</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class=" control-label col-md-12 col-sm-12 col-xs-12"><?php echo _("Status"); ?></label>
                                    <select class="form-control" id="Active" name="Active">
                                        <?php
                                        $sql = "SELECT ID, GroupName, Active
                                        FROM usergroups
                                        WHERE ID = $GroupID
                                        UNION
                                        SELECT ID, GroupName, Active
                                        FROM system_groups
                                        WHERE ID = $GroupID;";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            $Status = $row['Active'];
                                            if ($Status == 1) {
                                                echo "<option value='1' selected=true>" . _("Active") . "</option>";
                                                echo "<option value='0'>" . _("Inactive") . "</option>";
                                            } else {
                                                echo "<option value='1'>" . _("Active") . "</option>";
                                                echo "<option value='0' selected=true>" . _("Inactive") . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 form-group">
                            <div class="input-group input-group-static mb-4">
                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _("Description"); ?></label>
                                <textarea id="Description" name="Description" rows="10" cols="30" class="resizable_textarea form-control"><?php echo _($GroupDescriptionVal); ?></textarea>
                            </div>
                        </div>

                    </form>
                    <?php
                    if ($GroupType == "Standard" || in_array("100000", $UserGroups)) {
                        echo "<div class=\"col-lg-12 col-md-12 col-sm-12 col-xs-12\">
                        <button name=\"submit_updateGroupInformation\" class=\"btn btn-sm btn-success float-end\" onclick=\"updateGroupInformation($GroupID);\">" . _("Update") . "</button>
                    </div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-4 col-xs-12">
        <div class="card">
            <div class="card-header card-header"><a href="javascript:location.reload(true);"><?php echo _("Users in group"); ?></a></div>
            <div class="card-body">
                <div class="input-group input-group-static mb-4">
                    <select class="form-control" id="UsersInGroup" name="UsersInGroup" ondblclick='removeUsersFromGroup(<?php echo $GroupID ?>)' size="15" multiple>
                        <?php
                        $sql = "SELECT users.ID AS UserID, CONCAT(users.Firstname,' ',users.Lastname,' (',Username,')') AS FullName
                            FROM usersgroups
                            LEFT JOIN users ON usersgroups.UserID = users.ID 
                            LEFT JOIN usergroups ON usersgroups.GroupID = usergroups.ID
                            WHERE usersgroups.GroupID = $GroupID;
                            ";
                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                        while ($row = mysqli_fetch_array($result)) {
                            echo "<option value='" . $row['UserID'] . "'>" . $row['FullName'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button class="btn btn-sm btn-danger float-end" onclick="removeUsersFromGroup(<?php echo $GroupID ?>);"><?php echo _("Remove"); ?></button>
            </div>
        </div>
        <div class="card">
            <div class="card-header card-header"><a href="javascript:location.reload(true);"><?php echo _("Users Available"); ?></a></div>
            <div class="card-body">
                <div class="input-group input-group-static mb-4">
                    <select class="form-control" id="AvaliableUsers" name="AvaliableUsers" ondblclick='addUsersToGroup(<?php echo $GroupID ?>)' size="15" multiple>
                        <?php
                        $sql = "SELECT users.ID, CONCAT(users.Firstname,' ',users.Lastname,' (',Username,')') AS FullName
                                FROM users
                                WHERE ID NOT IN(
                                    SELECT users.ID AS UserID
                                    FROM usersgroups
                                    LEFT JOIN users ON usersgroups.UserID = users.ID 
                                    LEFT JOIN usergroups ON usersgroups.GroupID = usergroups.ID
                                    WHERE usersgroups.GroupID = $GroupID)
                                AND users.RelatedUserTypeID != 2
                                AND users.Active = 1
                                ORDER BY FullName ASC";
                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                        while ($row = mysqli_fetch_array($result)) {
                            echo "<option value='" . $row['ID'] . "'>" . $row['FullName'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button class="btn btn-sm btn-success float-end" onclick="addUsersToGroup(<?php echo $GroupID ?>);"><?php echo _("Add"); ?></button>
            </div>
        </div>
    </div>

    <?php include("./footer.php"); ?>