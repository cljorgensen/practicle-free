<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100026", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>
<?php
$UserID = $_GET['usersid'];

$RedirectPage = "administration_users_edit.php?UserID=" . $UserID;
$RedirectPageUpload = "../administration_users_edit.php?UserID=" . $UserID;
$ElementPath = "users";
$ElementRef = "UserID";
$ElementGetValue = "UserID";
$UserSessionID = $_SESSION["id"];

$sql = "SELECT ID, Firstname, Lastname, Email, Username, Password, Created_Date, CompanyID, RelatedUserTypeID, ADUUID, JobTitel, LinkedIn, Phone, 
            Active, InactiveDate, LastLogon, ProfilePicture, RelatedDesignID, Birthday, StartDate, RelatedManager, google_secret_code, QRUrl, Notes
            FROM users
            WHERE ID = $UserID";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
    $Firstname = $row['Firstname'];
    $Lastname = $row['Lastname'];
    $Email = $row['Email'];
    $Username = $row['Username'];
    $Created_Date = $row['Created_Date'];
    $CompanyID = $row['CompanyID'];
    $RelatedUserTypeID = $row['RelatedUserTypeID'];
    $ADUUID = $row['ADUUID'];
    $JobTitel = $row['JobTitel'];
    $LinkedIn = $row['LinkedIn'];
    $Phone = $row['Phone'];
    $Active = $row['Active'];
    $InactiveDate = $row['InactiveDate'];
    $RelatedDesignID = $row['RelatedDesignID'];
    $Birthday = $row['Birthday'];
    $StartDate = $row['StartDate'];
    $RelatedManager = $row['RelatedManager'];
    $Notes = $row['Notes'];
}
?>

<script>
    $(document).ready(function() {
        getFiles(<?php echo $UserID ?>, 'users', 'TableUserFiles', '<?php echo $UserLanguageCode ?>');
    });
</script>

<div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header"><i class="fa fa-user fa-lg"></i> <a href="administration_users.php"><?php echo _("Users"); ?></a> <i class="fa fa-angle-double-right"></i> <a href="javascript:location.reload(true);"><?php echo $Firstname . " " . $Lastname; ?></a>
                    <?php
                    if (in_array("100001", $group_array) || in_array("100026", $group_array)) {
                    ?>
                        <div class="dropdown float-end">
                            <button class="btn btn-sm bg-gradient-secondary dropup dropdown-toggle float-end" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo _("More") ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="javascript:deactivate2FA(<?php echo $UserID ?>);" title="<?php echo _("If user has lost 2FA informations"); ?>"><?php echo _('Deactivate') . " " . _('2FA'); ?></a></li>
                                <li><a class="dropdown-item" href="javascript:removeUser(<?php echo $UserID ?>);" title="<?php echo _("Anonimizes the user completely and deactivates the user"); ?>"><?php echo _('Remove'); ?></a></li>
                                <li><a class="dropdown-item" href="javascript:openTransferUserObjects(<?php echo $UserID ?>);" title="<?php echo _("Transfer users documents, tasks etc. to other user"); ?>"><?php echo _('Transfer'); ?></a></li>
                            </ul>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <div class="card-body">
                    <form id="updateUserForm">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label"><?php echo _("Firstname") ?> <code>*</code></label>
                                    <input type="text" class="form-control" id="Firstname" name="Firstname" value="<?php echo $Firstname; ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label"><?php echo _("Lastname") ?></label>
                                    <input type="text" class="form-control" id="Lastname" name="Lastname" value="<?php echo $Lastname; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label"><?php echo _("Email") ?> <code>*</code></label>
                                    <input type="Email" class="form-control" id="Email" name="Email" value="<?php echo $Email; ?>" required>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label"><?php echo _("Username") ?> <code>*</code></label>
                                    <input type="text" class="form-control" id="Username" name="Username" value="<?php echo $Username; ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label"><?php echo _("Company") ?> <code>*</code></label>

                                    <select class="form-control" id="CompanyID" name="CompanyID" required>
                                        <?php
                                        $sql = "SELECT Companies.ID, Companies.Companyname
                                            FROM Companies
                                            WHERE Companies.Active = 1";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            if ($CompanyID == $row['ID']) {
                                                echo "<option value='" . $row['ID'] . "' selected='select'>" . $row['Companyname'] . "</option>";
                                            } else {
                                                echo "<option value='" . $row['ID'] . "'>" . $row['Companyname'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label"><?php echo _("Usertype") ?> <code>*</code></label>

                                    <select class="form-control" id="RelatedUserTypeID" name="RelatedUserTypeID" required>
                                        <?php
                                        $sql = "SELECT usertypes.ID, usertypes.typename
                                            FROM usertypes";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            if ($RelatedUserTypeID == $row['ID']) {
                                                echo "<option value='" . $row['ID'] . "' selected='select'>" . _($row['typename']) . "</option>";
                                            } else {
                                                echo "<option value='" . $row['ID'] . "'>" . _($row['typename']) . "</option>";
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
                                    <label class="control-label"><?php echo _("Job Titel") ?></label>
                                    <input type="text" class="form-control" id="JobTitel" name="JobTitel" value="<?php echo $JobTitel; ?>">
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label"><?php echo _("LinkedIn") ?></label>
                                    <input type="text" class="form-control" id="LinkedIn" name="LinkedIn" value="<?php echo $LinkedIn; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label"><?php echo _("Team Leader") ?></label>

                                    <select class="form-control" id="RelatedManager" name="RelatedManager">
                                        <option value=''></option>
                                        <?php
                                        $sql = "SELECT users.ID
                                                FROM users
                                                WHERE Active = 1
                                                ORDER BY users.Firstname ASC, users.Lastname ASC;";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            $ManagerID = $row['ID'];
                                            $UserFullName = $functions->getUserFullNameWithUsername($ManagerID);
                                            if ($RelatedManager == $ManagerID) {
                                                echo "<option value='$ManagerID' selected='select'>$UserFullName</option>";
                                            } else {
                                                echo "<option value='$ManagerID'>$UserFullName</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class=" col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label class="control-label"> <?php echo _("Status") ?></label>

                                    <select class="form-control" id="Active" name="Active">
                                        <?php
                                        $sql = "SELECT ID, Active
                                            FROM users WHERE ID = $UserID";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            if ($Active == True) {
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
                    </form>
                    <?php
                    if (in_array("100001", $group_array) || in_array("100026", $group_array)) {
                        echo "<button name='updateUser' id='removeUser' class='btn btn-sm btn-success float-end' onclick='updateUser($UserID);'>" ?><?php echo _('Update'); ?><?php echo "</button>";
                                                                                                                                                                            }
                                                                                                                                                                                ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="card-group">
            <div id="TabSection" class='card'>
                <div class="card-header"><i class="fas fa-info-circle fa-lg"></i> <?php echo _("Details"); ?></div>
                <div class="scrolling-menu-wrapper">
                    <div class="arrow arrow-left">&#9664;</div>
                    <div class="scrolling-menu">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#NotesTab">
                                    <?php echo _("Notes"); ?>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#MembershipsTab">
                                    <?php echo _("Memberships"); ?>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#LogTab">
                                    <?php echo _("History"); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="arrow arrow-right">&#9654;</div>
                </div>
                <div class="card-body">
                    <br>
                    <div class="tab-content">
                        <!-- Tab panes -->
                        <div class="tab-pane active" id="NotesTab">
                            <label for="UserNotes" title="edit"><?php echo _("Notes"); ?></label>
                            <a href="javascript:toggleCKEditor('UserNotes');">&ensp;<i class="fa-solid fa-pen fa-sm" title="Double click on field to edit"></i></a>
                            <div style="height: 250px; word-wrap: break-word; overflow-y: auto; overflow-x: auto;" class="resizable_textarea form-control" id="UserNotes" name="UserNotes" title="Double click to edit" rows="10" autocomplete="off" ondblclick="toggleCKEditor('UserNotes');">
                                <?php echo $Notes; ?>
                            </div>
                            <button class="btn btn-sm btn-success float-end" onclick="updateAndLogUserNotes(<?php echo $UserID ?>)"><?php echo _('Update') ?></button>
                        </div>

                        <div class="tab-pane" id="LogTab">
                            <script>
                                $(document).ready(function() {
                                    var table = $('#userlog').DataTable({
                                        aaSorting: [],
                                        paging: true,
                                        pagingType: 'numbers',
                                        processing: true,
                                        dom: 'Bfrtip',
                                        language: {
                                            info: "<?php echo _("Showing"); ?> _START_ <?php echo _("to"); ?> _END_ <?php echo _("of"); ?> _TOTAL_ <?php echo _("records"); ?>",
                                            searchPlaceholder: "<?php echo _("Search"); ?>",
                                            search: "",
                                        },
                                        buttons: [{
                                            extend: 'copy',
                                            className: 'btn btn-sm btn-secondary'
                                        }, {
                                            extend: 'excel',
                                            className: 'btn btn-sm btn-secondary'
                                        }, {
                                            extend: 'csv',
                                            className: 'btn btn-sm btn-secondary'
                                        }],
                                        pageLength: 40
                                    });
                                });
                            </script>
                            <table id="userlog" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                <thead>
                                    <tr class='text-sm text-secondary mb-0'>
                                        <th><?php echo _("Date"); ?></th>
                                        <th><?php echo _("Text"); ?></th>
                                        <th><?php echo _("User"); ?></th>
                                    </tr>
                                </thead>
                                <?php
                                $sql = "SELECT LogActionDate, LogActionText, Users.Username AS Username 
                                        FROM log_users
                                        INNER JOIN Users ON log_users.UserID = Users.ID
                                        WHERE log_users.RelatedUserID = $UserID
                                        ORDER BY LogActionDate DESC";
                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                ?>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php $myFormatForView = convertToDanishTimeFormat($row['LogActionDate']);
                                                echo $myFormatForView; ?> </td>
                                            <td><?php echo $row['LogActionText']; ?></td>
                                            <td><?php echo $row['Username']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="tab-pane" id="MembershipsTab">
                            <div class="row justify-content-center">
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6><?php echo _("Group memberships"); ?></h6>
                                            <div class="input-group input-group-static mb-4">
                                                <select class="form-control" id="UserInGroups" ondblclick="removeGroupFromUser('<?php echo $UserID ?>');" size="15" multiple>
                                                    <?php
                                                    $sql = "SELECT usersgroups.GroupID AS GroupID, usergroups.GroupName AS GroupName
                                                        FROM usersgroups
                                                        LEFT JOIN (SELECT usergroups.ID AS ID, usergroups.GroupName AS GroupName
                                                        FROM usergroups
                                                        UNION
                                                        SELECT system_groups.ID AS ID, system_groups.GroupName AS GroupName
                                                        FROM system_groups) AS usergroups ON usersgroups.GroupID = usergroups.ID
                                                        WHERE usersgroups.UserID = $UserID
                                                        ORDER BY GroupName ASC;";
                                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        echo "<option value='" . $row['GroupID'] . "'>" . $row['GroupName'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <button class='btn btn-sm btn-danger float-end' onclick="removeGroupFromUser('<?php echo $UserID ?>');"><?php echo _("Remove"); ?></button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6><?php echo _("Groups Available"); ?></h6>
                                            <div class="input-group input-group-static mb-4">
                                                <select class="form-control" id="AvaliableGroups" ondblclick="addGroupToUser('<?php echo $UserID ?>');" size="15" multiple="multiple">
                                                    <?php
                                                    $sql = "SELECT usergroups.ID AS ID, usergroups.GroupName AS GroupName
                                                        FROM usergroups
                                                        WHERE ID NOT IN(
                                                            SELECT usersgroups.GroupID AS GroupID
                                                            FROM usersgroups
                                                            INNER JOIN usergroups ON usersgroups.GroupID = usergroups.ID
                                                            WHERE usersgroups.UserID = $UserID AND usergroups.Active = 1)
                                                        UNION
                                                        SELECT system_groups.ID AS ID, system_groups.GroupName AS GroupName
                                                        FROM system_groups
                                                        WHERE ID NOT IN(
                                                            SELECT usersgroups.GroupID AS GroupID
                                                            FROM usersgroups
                                                            INNER JOIN system_groups ON usersgroups.GroupID = system_groups.ID
                                                            WHERE usersgroups.UserID = $UserID AND system_groups.Active = 1) AND system_groups.ID !=100000
                                                        ORDER BY GroupName ASC";
                                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        echo "<option value='" . $row['ID'] . "'>" . $row['GroupName'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <button class='btn btn-sm btn-success float-end' onclick="addGroupToUser('<?php echo $UserID ?>');"><?php echo _("Add"); ?></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6><?php echo _("Role memberships"); ?></h6>
                                            <div class="input-group input-group-static mb-4">
                                                <select class="form-control" id="UserInRoles" ondblclick="removeRoleFromUser('<?php echo $UserID ?>');" size="15" multiple>
                                                    <?php
                                                    $sql = "SELECT roles.ID AS RoleID, roles.RoleName
                                                FROM usersroles
                                                INNER JOIN roles ON roles.ID = usersroles.RoleID
                                                WHERE usersroles.UserID = $UserID
                                                ORDER BY RoleName ASC;";
                                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        echo "<option value='" . $row['RoleID'] . "'>" . $row['RoleName'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <button class='btn btn-sm btn-danger float-end' onclick="removeRoleFromUser('<?php echo $UserID ?>');"><?php echo _("Remove"); ?></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6><?php echo _("Role Available"); ?></h6>
                                            <div class="input-group input-group-static mb-4">
                                                <select class="form-control" id="AvaliableRoles" ondblclick="addRoleToUser('<?php echo $UserID ?>');" size="15" multiple>
                                                    <?php
                                                    $sql = "SELECT roles.ID, roles.RoleName
                                                    FROM roles
                                                    WHERE ID NOT IN(
                                                        SELECT usersroles.RoleID
                                                        FROM usersroles
                                                        INNER JOIN roles ON usersroles.RoleID = roles.ID
                                                        WHERE usersroles.UserID = $UserID AND roles.Active = 1) AND roles.ID !=0
                                                    ORDER BY RoleName ASC";
                                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        echo "<option value='" . $row['ID'] . "'>" . $row['RoleName'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <button class='btn btn-sm btn-success float-end' onclick="addRoleToUser('<?php echo $UserID ?>');"><?php echo _("Add"); ?></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6><?php echo _("Team memberships"); ?></h6>
                                            <div class="input-group input-group-static mb-4">
                                                <select class="form-control" id="UserInTeams" ondblclick="removeTeamFromUser('<?php echo $UserID ?>');" size="15" multiple>
                                                    <?php
                                                    $sql = "SELECT teams.ID AS TeamID, teams.Teamname
                                                FROM usersteams
                                                INNER JOIN teams ON teams.ID = usersteams.TeamID
                                                WHERE usersteams.UserID = $UserID
                                                ORDER BY Teamname ASC;";
                                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        echo "<option value='" . $row['TeamID'] . "'>" . $row['Teamname'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <button class='btn btn-sm btn-danger float-end' onclick="removeTeamFromUser('<?php echo $UserID ?>');"><?php echo _("Remove"); ?></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6><?php echo _("Teams Available"); ?></h6>
                                            <div class="input-group input-group-static mb-4">
                                                <select class="form-control" id="AvaliableTeams" ondblclick="addTeamToUser('<?php echo $UserID ?>');" size="15">
                                                    <?php
                                                    $sql = "SELECT teams.ID, teams.Teamname
                                                    FROM teams
                                                    WHERE ID NOT IN(
                                                        SELECT usersteams.TeamID AS TeamID
                                                        FROM usersteams
                                                        INNER JOIN teams ON usersteams.TeamID = teams.ID
                                                        WHERE usersteams.UserID = $UserID AND teams.Active = 1)
                                                    ORDER BY Teamname ASC";
                                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        echo "<option value='" . $row['ID'] . "'>" . $row['Teamname'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <button class='btn btn-sm btn-success float-end' onclick="addTeamToUser('<?php echo $UserID ?>');"><?php echo _("Add"); ?></button>
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

<?php include("./footer.php"); ?>