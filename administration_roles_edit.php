<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100033", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>
<?php
$RoleID = $_GET['roleid'];

$sql = "SELECT ID, RoleName, Description, Active 
        FROM roles
        WHERE ID = $RoleID";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
    $RoleIDVal = $row['ID'];
    $RoleNameVal = $row['RoleName'];
    $RoleDescriptionVal = $row['Description'];
    $RoleActiveVal = $row['Active'];
}
?>

<script>
    function addUsersToRole(roleId) {
        const multiSelect = document.getElementById('AvaliableUsers');
        const selectedOptions = Array.from(multiSelect.selectedOptions).map(option => option.value);

        const vData = {
            selectedValues: selectedOptions,
            roleId: roleId
        };

        const avaliableUsersOptions = Array.from(multiSelect.options).map(option => ({
            value: option.value,
            text: option.text
        }));

        $.ajax({
            url: "./getdata.php?addUsersToRole",
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
                selectedOptions.forEach(optionValue => {
                    const optionToRemove = multiSelect.querySelector(`option[value="${optionValue}"]`);
                    if (optionToRemove) {
                        multiSelect.removeChild(optionToRemove);
                    }
                });

                const usersInRoleSelect = document.getElementById('UsersInRole');
                selectedOptions.forEach(optionValue => {
                    const optionToMove = document.createElement('option');
                    const matchingOption = avaliableUsersOptions.find(option => option.value === optionValue);

                    if (matchingOption) {
                        optionToMove.value = matchingOption.value;
                        optionToMove.text = matchingOption.text;
                        usersInRoleSelect.appendChild(optionToMove);
                    }
                });
            },
        });
    }

    function removeUsersFromRole(roleId) {
        const multiSelect = document.getElementById('UsersInRole');
        const selectedOptions = Array.from(multiSelect.selectedOptions).map(option => option.value);

        const vData = {
            selectedValues: selectedOptions,
            roleId: roleId
        };

        const usersInRoleOptions = Array.from(multiSelect.options).map(option => ({
            value: option.value,
            text: option.text
        }));

        $.ajax({
            url: "./getdata.php?removeUsersFromRole",
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
                selectedOptions.forEach(optionValue => {
                    const optionToRemove = multiSelect.querySelector(`option[value="${optionValue}"]`);
                    if (optionToRemove) {
                        multiSelect.removeChild(optionToRemove);
                    }
                });

                const avaliableUsersSelect = document.getElementById('AvaliableUsers');
                selectedOptions.forEach(optionValue => {
                    const optionToMove = document.createElement('option');
                    const matchingOption = usersInRoleOptions.find(option => option.value === optionValue);

                    if (matchingOption) {
                        optionToMove.value = matchingOption.value;
                        optionToMove.text = matchingOption.text;
                        avaliableUsersSelect.appendChild(optionToMove);
                    }
                });
            },
        });
    }

    // Add groups to role
    function addGroupsToRole(roleId) {
        activateSpinner();
        // Get the multi-select element
        const multiSelect = document.getElementById('AvaliableGroups');

        // Get all the selected options
        const selectedOptions = Array.from(multiSelect.selectedOptions).map(option => option.value);

        // Send the selected values to an AJAX call
        const vData = {
            selectedValues: selectedOptions,
            roleId: roleId
        };

        // Retrieve the options from the AvaliableGroups select box
        const avaliableGroupsOptions = Array.from(multiSelect.options).map(option => ({
            value: option.value,
            text: option.text
        }));

        $.ajax({
            url: "./getdata.php?addGroupsToRole",
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

                // Add the selected options to the GroupsInRole select box
                const groupsInRoleSelect = document.getElementById('GroupsInRole');
                selectedOptions.forEach(optionValue => {
                    const optionToMove = document.createElement('option');

                    // Find the corresponding option from the avaliableGroupsOptions array
                    const matchingOption = avaliableGroupsOptions.find(option => option.value === optionValue);

                    if (matchingOption) {
                        // Set the value and text of the option
                        optionToMove.value = matchingOption.value;
                        optionToMove.text = matchingOption.text;
                        groupsInRoleSelect.appendChild(optionToMove);
                    }
                });
            },
        });
    }

    // Remove groups from role
    function removeGroupsFromRole(roleId) {
        activateSpinner();
        // Get the multi-select element
        const multiSelect = document.getElementById('GroupsInRole');

        // Get all the selected options
        const selectedOptions = Array.from(multiSelect.selectedOptions).map(option => option.value);

        // Send the selected values to an AJAX call
        const vData = {
            selectedValues: selectedOptions,
            roleId: roleId
        };

        // Retrieve the options from the GroupsInRole select box
        const groupsInRoleOptions = Array.from(multiSelect.options).map(option => ({
            value: option.value,
            text: option.text
        }));

        $.ajax({
            url: "./getdata.php?removeGroupsFromRole",
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

                // Add the removed options to the AvaliableGroups select box
                const avaliableGroupsSelect = document.getElementById('AvaliableGroups');
                selectedOptions.forEach(optionValue => {
                    const optionToMove = document.createElement('option');

                    // Find the corresponding option from the groupsInRoleOptions array
                    const matchingOption = groupsInRoleOptions.find(option => option.value === optionValue);

                    if (matchingOption) {
                        // Set the value and text of the option
                        optionToMove.value = matchingOption.value;
                        optionToMove.text = matchingOption.text;
                        avaliableGroupsSelect.appendChild(optionToMove);
                    }
                });
            },
        });
    }
</script>

<div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header card-header"><i class="fa fa-user-tag fa-lg"></i> <a href="administration_roles.php"><?php echo _("Roles"); ?></a> <i class="fa fa-angle-right fa-sm"></i> <a href="javascript:location.reload(true);"><?php echo $RoleNameVal; ?></a></div>
                <div class="card-body">
                    <form id="FormRoleInformation">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" hidden>
                            <div class="input-group input-group-static mb-4">
                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _("ID"); ?></label>
                                <input type="text" class="form-control" id="RoleID" name="RoleID" value="<?php echo $RoleIDVal; ?>" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _("Name"); ?></label>
                                    <input type="text" class="form-control" id="RoleName" name="RoleName" value="<?php echo $RoleNameVal; ?>">
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _("Status"); ?></label>
                                    <select class="form-control" id="RoleStatus" name="RoleStatus">
                                        <?php
                                        if ($RoleActiveVal == "1") {
                                            echo "<option value='1' selected=true>" . _("Active") . "</option>";
                                            echo "<option value='0'>" . _("Inactive") . "</option>";
                                        } else {
                                            echo "<option value='1'>" . _("Active") . "</option>";
                                            echo "<option value='0' selected=true>" . _("Inactive") . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12 form-group">
                            <div class="input-group input-group-static mb-4">
                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _("Description"); ?></label>
                                <textarea id="RoleDescription" name="RoleDescription" rows="10" cols="30" class="resizable_textarea form-control"><?php echo $RoleDescriptionVal; ?></textarea>
                            </div>
                        </div>
                    </form>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <button name="submit_updaterole" class="btn btn-sm btn-success float-end" onclick="updateRoleInformation(<?php echo $RoleIDVal ?>);"><?php echo _("Update"); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <h6><?php echo _("Users in role"); ?></h6>
                            <div class="input-group input-group-static mb-4">
                                <select class="form-control" id="UsersInRole" name="UsersInRole" ondblclick='removeUsersFromRole(<?php echo $RoleID ?>);' size="15" multiple>
                                    <?php
                                    $sql = "SELECT usersroles.UserID AS UserID, CONCAT(Users.FirstName,' ', Users.LastName,' (', Users.Username,')') AS UserFullName
                                            FROM roles
                                            INNER JOIN usersroles ON roles.ID = usersroles.RoleID
                                            INNER JOIN users ON usersroles.UserID = users.ID
                                            WHERE usersroles.RoleID = $RoleID AND roles.Active = 1;";

                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                    $users = array();

                                    // Fetch all results into the array
                                    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                        $users[] = array("ID" => $row['UserID'], "UsersName" => $row['UserFullName']);
                                    }

                                    usort($users, function ($a, $b) {
                                        return strcmp($a['UsersName'], $b['UsersName']);
                                    });

                                    foreach ($users as $row) {
                                        echo "<option value='" . $row['ID'] . "'>" . $row['UsersName'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button class='btn btn-sm btn-danger float-end' onclick='removeUsersFromRole(<?php echo $RoleID ?>);'><?php echo _("Remove"); ?></button>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <h6><?php echo _("Users Available"); ?></h6>
                            <div class="input-group input-group-static mb-4">
                                <select class="form-control" id="AvaliableUsers" name="AvaliableUsers" ondblclick='addUsersToRole(<?php echo $RoleID ?>);' size="15" multiple>
                                    <?php
                                    $sql = "SELECT users.ID, CONCAT(users.Firstname,' ',users.Lastname,' (',Username,')') AS UserFullName
                                            FROM users
                                            WHERE ID NOT IN(
                                                SELECT users.ID AS UserID
                                                FROM usersroles
                                                LEFT JOIN users ON usersroles.UserID = users.ID 
                                                LEFT JOIN roles ON usersroles.RoleID = roles.ID
                                                WHERE usersroles.RoleID = $RoleIDVal)
                                            AND users.RelatedUserTypeID != 2
                                            AND users.Active = 1
                                            ORDER BY UserFullName ASC";

                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                    $users = array();

                                    // Fetch all results into the array
                                    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                        $users[] = array("ID" => $row['ID'], "UsersName" => $row['UserFullName']);
                                    }

                                    usort($users, function ($a, $b) {
                                        return strcmp($a['UsersName'], $b['UsersName']);
                                    });

                                    foreach ($users as $row) {
                                        echo "<option value='" . $row['ID'] . "'>" . $row['UsersName'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button class='btn btn-sm btn-success float-end' onclick='addUsersToRole(<?php echo $RoleID ?>);'><?php echo _("Add"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-group">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <h6><?php echo _("Groups in role"); ?></h6>
                            <div class="input-group input-group-static mb-4">
                                <select class="form-control" id="GroupsInRole" name="GroupsInRole" ondblclick='removeGroupsFromRole(<?php echo $RoleID ?>)' size="15" multiple>
                                    <?php
                                    $sql = "SELECT usergroups.ID AS GroupID, usergroups.GroupName AS GroupName
                                        FROM roles
                                        INNER JOIN usergroupsroles ON roles.ID = usergroupsroles.RoleID
                                        INNER JOIN usergroups ON usergroupsroles.GroupID = usergroups.ID
                                        WHERE roles.ID = $RoleID AND roles.Active = 1
                                        UNION
                                        SELECT system_groups.ID AS GroupID, system_groups.GroupName AS GroupName
                                        FROM roles
                                        INNER JOIN usergroupsroles ON roles.ID = usergroupsroles.RoleID
                                        INNER JOIN system_groups ON usergroupsroles.GroupID = system_groups.ID
                                        WHERE roles.ID = $RoleID AND roles.Active = 1 AND system_groups.ID != '100000';";

                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                    $groups = array();
                                    $keyGroupName = "GroupName";

                                    // Fetch all results into the array
                                    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                        $groups[] = array("ID" => $row['ID'], "GroupName" => $functions->translate($row['GroupName']));
                                    }

                                    $functions->debuglog($groups);
                                    
                                    usort($groups, function ($a, $b) use ($keyGroupName) {
                                        return strcmp($a[$keyGroupName], $b[$keyGroupName]);
                                    });

                                    foreach ($groups as $row) {
                                        echo "<option value='" . $row['ID'] . "'>" . $row['GroupName'] . "</option>";
                                    }
                                    

                                    ?>
                                </select>
                            </div>
                            <button class='btn btn-sm btn-danger float-end' onclick='removeGroupsFromRole(<?php echo $RoleID ?>);'><?php echo _("Remove"); ?></button>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <h6><?php echo _("Groups Available"); ?></h6>
                            <div class="input-group input-group-static mb-4">
                                <select class="form-control" id="AvaliableGroups" name="AvaliableGroups" ondblclick='addGroupsToRole(<?php echo $RoleID ?>)' size="15" multiple>
                                    <?php
                                    $sql = "SELECT usergroups.ID, usergroups.GroupName
                                            FROM usergroups
                                            WHERE ID NOT IN(
                                                SELECT usergroupsroles.GroupID AS GroupID
                                                FROM usergroupsroles
                                                LEFT JOIN usergroups ON usergroupsroles.GroupID = usergroups.ID 
                                                LEFT JOIN roles ON usergroupsroles.RoleID = roles.ID
                                                WHERE usergroupsroles.RoleID = $RoleID)
                                            AND usergroups.Active = 1
                                            UNION
                                            SELECT system_groups.ID, system_groups.GroupName
                                            FROM system_groups
                                            WHERE ID NOT IN(
                                                SELECT usergroupsroles.GroupID AS GroupID
                                                FROM usergroupsroles
                                                LEFT JOIN system_groups ON usergroupsroles.GroupID = system_groups.ID 
                                                LEFT JOIN roles ON usergroupsroles.RoleID = roles.ID
                                                WHERE usergroupsroles.RoleID = $RoleID) AND ID !=100000
                                            AND system_groups.Active = 1
                                            ORDER BY GroupName ASC";

                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                    // Temporary array to hold results
                                    $groups = array();

                                    $keyGroupName = "GroupName";

                                    // Fetch all results into the array
                                    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                        $groups[] = array("ID" => $row['ID'], "GroupName" => $functions->translate($row['GroupName']));
                                    }

                                    usort($groups, function ($a, $b) use ($keyGroupName) {
                                        return strcmp($a[$keyGroupName], $b[$keyGroupName]);
                                    });

                                    foreach ($groups as $row) {
                                        echo "<option value='" . $row['ID'] . "'>" . $row['GroupName'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button class='btn btn-sm btn-success float-end' onclick='addGroupsToRole(<?php echo $RoleID ?>);'><?php echo _("Add"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("./footer.php"); ?>