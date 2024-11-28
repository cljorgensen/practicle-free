<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100033", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>
<?php
$TeamID = $_GET['teamid'];

$sql = "SELECT ID, Teamname, TeamLeader, Description, Active 
        FROM Teams
        WHERE ID = $TeamID";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
    $TeamIDVal = $row['ID'];
    $TeamnameVal = $row['Teamname'];
    $TeamLeaderVal = $row['TeamLeader'];
    $TeamDescriptionVal = $row['Description'];
    $TeamActiveVal = $row['Active'];
}
?>

<script>
    $(document).ready(function() {
        TeamID = <?php echo $TeamID ?>;
        var urlget = './getdata.php?getUsersInTeam=' + TeamID;
        $.ajax({
            url: urlget,
            data: {
                TeamID: TeamID
            },
            type: 'POST',
            success: function(data) {
                document.getElementById("UsersInTeam").innerHTML = "";
                obj = JSON.parse(data);

                for (var i = 0; i < obj.length; i++) {
                    $('#UsersInTeam').append('<option value="' + obj[i].UserID + '">' + obj[i].UserFullName + '</option>');
                }
            }
        });
    });
</script>

<script>
    function removeUsersFromTeam(teamId) {
        activateSpinner();
        // Get the multi-select element
        const multiSelect = document.getElementById('UsersInTeam');

        // Get all the selected options
        const selectedOptions = Array.from(multiSelect.selectedOptions).map(option => option.value);

        // Send the selected values to an AJAX call
        const vData = {
            selectedValues: selectedOptions,
            teamId: teamId
        };

        // Retrieve the options from the UsersInTeam select box
        const usersInTeamOptions = Array.from(multiSelect.options).map(option => ({
            value: option.value,
            text: option.text
        }));

        $.ajax({
            url: "./getdata.php?removeUsersFromTeam",
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

                    // Find the corresponding option from the usersInTeamOptions array
                    const matchingOption = usersInTeamOptions.find(option => option.value === optionValue);

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


    function addUsersToTeam(teamId) {
        activateSpinner();
        // Get the multi-select element
        const multiSelect = document.getElementById('AvaliableUsers');

        // Get all the selected options
        const selectedOptions = Array.from(multiSelect.selectedOptions).map(option => option.value);

        // Send the selected values to an AJAX call
        const vData = {
            selectedValues: selectedOptions,
            teamId: teamId
        };

        // Retrieve the options from the AvaliableUsers select box
        const avaliableUsersOptions = Array.from(multiSelect.options).map(option => ({
            value: option.value,
            text: option.text
        }));

        $.ajax({
            url: "./getdata.php?addUsersToTeam",
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

                // Add the selected options to the UsersInTeam select box
                const usersInTeamSelect = document.getElementById('UsersInTeam');
                selectedOptions.forEach(optionValue => {
                    const optionToMove = document.createElement('option');

                    // Find the corresponding option in the avaliableUsersOptions array
                    const correspondingOption = avaliableUsersOptions.find(option => option.value === optionValue);
                    if (correspondingOption) {
                        optionToMove.value = correspondingOption.value;
                        optionToMove.text = correspondingOption.text;
                        usersInTeamSelect.appendChild(optionToMove);
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
                <div class="card-header card-header"><i class="fa fa-users fa-lg"></i> <a href="administration_teams.php"><?php echo _("Teams"); ?></a> <i class="fa fa-angle-right fa-sm"></i> <a href="javascript:location.reload(true);"><?php echo $TeamnameVal; ?></a></div>
                <div class="card-body">
                    <form id="FormTeamInformation">
                        <div class="col-lg-12 col-md-12 col-xs-12" hidden>
                            <div class="input-group input-group-static mb-4">
                                <label for="TeamID"><?php echo _("ID"); ?></label>
                                <input type="text" class="form-control" id="TeamID" name="TeamID" value="<?php echo $TeamIDVal; ?>" disabled>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="TeamName"><?php echo _("Name"); ?></label>
                                    <input type="text" class="form-control" id="TeamName" name="TeamName" value="<?php echo $TeamnameVal; ?>">
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="TeamLeader"><?php echo _("Team Leader"); ?></label>
                                    <select class="form-control" id="TeamLeader" name="TeamLeader">
                                        <option value='-1' label=''></option>
                                        <?php
                                        $sql = "SELECT users.ID, CONCAT(users.Firstname,' ',users.Lastname,' (',Username,')') AS FullName
                                                FROM users
                                                WHERE users.RelatedUserTypeID = '1' AND users.Active = 1
                                                ORDER BY FullName ASC";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            if ($TeamLeaderVal == $row['ID']) {
                                                echo "<option value='" . $row['ID'] . "' selected='select'>" . $row['FullName'] . "</option>";
                                            } else {
                                                echo "<option value='" . $row['ID'] . "'>" . $row['FullName'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="TeamStatus"><?php echo _("Status"); ?></label>
                                    <select class="form-control" id="TeamStatus" name="TeamStatus">
                                        <?php
                                        $sql = "SELECT ID, Active
                                                FROM teams WHERE ID = $TeamIDVal";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            if ($TeamActiveVal == True) {
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
                        </div>

                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="input-group input-group-static mb-4">
                                <label for="TeamDescription"><?php echo _("Description"); ?></label>
                                <textarea class="form-control" id="TeamDescription" name="TeamDescription" rows="10" autocomplete="off"><?php echo $TeamDescriptionVal; ?></textarea>
                            </div>
                        </div>
                    </form>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <button name="submit_updateteam" class="btn btn-sm btn-success float-end" onclick="updateTeamInformation(<?php echo $TeamID ?>);"><?php echo _("Update"); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-4 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-body">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h6><?php echo _("Users in team") ?></h6>
                        <div class="input-group input-group-static mb-4">
                            <select class="form-control" name="UsersInTeam" id="UsersInTeam" id="TeamUsers" size="15" multiple ondblclick="removeUsersFromTeam(<?php echo $TeamID ?>);">
                                <option value=''></option>
                            </select>
                        </div>
                        <button class="btn btn-sm btn-danger float-end" onclick="removeUsersFromTeam(<?php echo $TeamID ?>);"><?php echo _("Remove"); ?></button>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h6><?php echo _("Users Available") ?></h6>
                        <div class="input-group input-group-static mb-4">
                            <select class="form-control" name='AvaliableUsers' id="AvaliableUsers" size="15" multiple ondblclick="addUsersToTeam(<?php echo $TeamID ?>);">
                                <?php
                                $sql = "SELECT users.ID, CONCAT(users.Firstname,' ',users.Lastname,' (',Username,')') AS UserFullName
                                        FROM users
                                        WHERE ID NOT IN (
                                            SELECT users.ID AS UserID
                                            FROM usersteams
                                            LEFT JOIN users ON usersteams.UserID = users.ID 
                                            LEFT JOIN teams ON usersteams.TeamID = teams.ID
                                        )
                                        AND users.RelatedUserTypeID != 2
                                        AND users.Active = 1
                                        ORDER BY UserFullName ASC";

                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                while ($row = mysqli_fetch_array($result)) {
                                    echo "<option value='" . $row['ID'] . "'>" . $row['UserFullName'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <button class="btn btn-sm btn-success float-end" onclick="addUsersToTeam(<?php echo $TeamID ?>);"><?php echo _("Add"); ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<?php include("./footer.php"); ?>