<?php
$StartDefaultDate = date("d-m-Y H:i");
$DeadlineDefaultDate = date("d-m-Y H:i", strtotime("+2 hours"));
?>

<!-- Modal New Project -->
<div class="modal fade" id="modalCreateNewProject" data-bs-focus="false" role="dialog" aria-labelledby="ModalCreateNewProjectLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title font-weight-normal" id="ModalCreateNewProjectLabel"></h6>
                <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
                    <i class="material-icons">clear</i>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' name='ModalProjectID' id='ModalProjectID' class='form-control' value=''>
                <div class="card-group">
                    <div class="card">
                        <div class="card-body">
                            <form id="createProject">
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                        <div class="input-group input-group-static mb-4">
                                            <label for="ProjectName"><?php echo _("Name"); ?> <code>*</code></label>
                                            <input type="text" class="form-control" id="ProjectName" name="ProjectName" required>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 col-sm-12 col-xs-12">
                                        <div>
                                            <div class="input-group input-group-static mb-4">
                                                <label for="ModalCreateProjectDescription"><?php echo _("Description") ?>&ensp;<a href="javascript:toggleCKEditor('ModalCreateProjectDescription','250');"><i class="fa-solid fa-pen fa-sm" title="Double click on field to edit"></i></a></label>
                                            </div>
                                            <div style="height: 150px; word-wrap: break-word; overflow-y: auto; overflow-x: auto;" class="resizable_textarea form-control" id="ModalCreateProjectDescription" name="ModalCreateProjectDescription" title="Double click to edit" autocomplete="off" ondblclick="toggleCKEditor('ModalCreateProjectDescription','250');">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-12 col-xs-12">
                                        <label for="ProjectManager" title="Test"><?php echo _("Project Manager"); ?> <code>*</code></label>
                                        <div class="input-group input-group-static mb-4">
                                            <select class="form-control select2" id="ProjectManager" name="ProjectManager" title="Project manager" required>
                                                <?php
                                                $Groups = "100007,100008";
                                                $UsersArray = [];
                                                $UsersArray = getUsersAttachedGroupsAttachedInRole($Groups);
                                                if (!empty($UsersArray)) {
                                                    $sql = "SELECT ID, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
                                                            FROM users
                                                            WHERE ID IN (" . implode(",", $UsersArray) . ")";
                                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        $SessionUserID = $_SESSION['id'];
                                                        $ID = $row['ID'];
                                                        $FullName = $row['FullName'];
                                                        if ($ID === $SessionUserID) {
                                                            echo "<option value=\"$ID\" selected>$FullName</option>";
                                                        } else {
                                                            echo "<option value=\"$ID\">$FullName</option>";
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-12 col-xs-12">
                                        <label for="ProjectResponsible"><?php echo _("Responsible"); ?> <code>*</code></label>
                                        <div class="input-group input-group-static mb-4">
                                            <select class="form-control select2" id="ProjectResponsible" name="ProjectResponsible" required>
                                                <option value='-1' label=''></option>
                                                <?php
                                                $Groups = "100007,100008";
                                                $UsersArray = [];
                                                $UsersArray = getUsersAttachedGroupsAttachedInRole($Groups);
                                                if (!empty($UsersArray)) {
                                                    $sql = "SELECT ID, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
                                                            FROM users
                                                            WHERE ID IN (" . implode(",", $UsersArray) . ")";
                                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        $SessionUserID = $_SESSION['id'];
                                                        $ID = $row['ID'];
                                                        $FullName = $row['FullName'];
                                                        if ($ID === $SessionUserID) {
                                                            echo "<option value=\"$ID\" selected>$FullName</option>";
                                                        } else {
                                                            echo "<option value=\"$ID\">$FullName</option>";
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-12 col-xs-12">
                                        <label for="ProjectRelCustomer" title="<?php echo _("Customer that this project is to be related to") ?>"><?php echo _("Related Customer"); ?></label>
                                        <div class="input-group input-group-static mb-4">
                                            <select id="ProjectRelCustomer" name="ProjectRelCustomer" class="form-control select2" required>
                                                <?php
                                                $sql = "SELECT ID, Companyname
                                                        FROM companies
                                                        WHERE Active = 1";
                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $UsersCompanyID = $_SESSION['companyid'];
                                                    $CompanyID = $row['ID'];
                                                    $Companyname = $row['Companyname'];
                                                    if ($UsersCompanyID == $CompanyID) {
                                                        echo "<option value='$CompanyID' selected='true'>$Companyname</option>";
                                                    } else {
                                                        echo "<option value='$CompanyID'>$Companyname</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-12 col-xs-12">
                                        <label for="ProjectStatus"><?php echo _("Status"); ?> <code>*</code></label>
                                        <div class="input-group input-group-static mb-4">
                                            <select class="form-control select2" id="ProjectStatus" name="ProjectStatus" required>
                                                <?php
                                                $sql = "SELECT ID, StatusName 
                                                        FROM projects_statuscodes
                                                        WHERE projects_statuscodes.Active = 1";
                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $Status = _($row['StatusName']);
                                                    echo "<option value='" . $row['ID'] . "'>" . $Status . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-12 col-xs-12">
                                        <div class="input-group input-group-static mb-4">
                                            <label for="ProjectStart"><?php echo _("Start"); ?></a> <code>*</code></label>
                                            <input type="text" id="ProjectStart" name="ProjectStart" class="form-control" autocomplete="off" value="<?php echo $StartDefaultDate; ?>" required>
                                        </div>
                                    </div>

                                    <div class="col-lg-3 col-sm-12 col-xs-12">
                                        <div class="input-group input-group-static mb-4">
                                            <label for="ProjectDeadline"><?php echo _("Deadline"); ?> <code>*</code></label>
                                            <input type="text" class="form-control" id="ProjectDeadline" name="ProjectDeadline" autocomplete="off" value="<?php echo $DeadlineDefaultDate; ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button name="createproject" id="createproject" class="btn btn-sm btn-success float-end" onclick="createNewProject();"><span class=""></span> <?php echo _("Create"); ?></button>
                <button name="updateproject" id="updateproject" class="btn btn-sm btn-success float-end" onclick="updateProject()"><span class=""></span> <?php echo _("Update"); ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {
        jQuery('#ProjectStart').datetimepicker({
            format: 'd-m-Y H:i',
            prevButton: false,
            nextButton: false,
            step: 60,
            dayOfWeekStart: 1
        });
        $.datetimepicker.setLocale('da');

        jQuery('#ProjectDeadline').datetimepicker({
            format: 'd-m-Y H:i',
            prevButton: false,
            nextButton: false,
            step: 60,
            dayOfWeekStart: 1
        });
        $.datetimepicker.setLocale('<?php echo $languageshort ?>');
    });
</script>
<!-- End Modal New Project -->