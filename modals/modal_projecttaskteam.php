<script>
  async function addUserToProject() {
    var UserID = document.getElementById('teamusersforadd').value;
    var ProjectTaskID = '<?php echo $ProjectTaskID ?>';

    await $.ajax({
      type: 'GET',
      url: './getdata.php?addUserToProjectTask',
      data: {
        UserID: UserID,
        ProjectTaskID: ProjectTaskID
      },
      success: function(data) {
        pnotify('<?php echo _("User added") ?>', 'success');
        $("#modalProjectTaskTeam").modal("hide");
        location.reload();
      }
    });
  }

  async function removeUserFromProject() {
    var UserID = document.getElementById('teamusersforremoval').value;
    var ProjectTaskID = '<?php echo $ProjectTaskID ?>';

    await $.ajax({
      type: 'GET',
      url: './getdata.php?removeUserFromProjectTask',
      data: {
        UserID: UserID,
        ProjectTaskID: ProjectTaskID
      },
      success: function(data) {
        pnotify('<?php echo _("User removed") ?>', 'success');
        $("#modalProjectTaskTeam").modal("hide");
        location.reload();
      }
    });
  }
</script>
<!-- Modal ModalProjectTeam -->
<div class="modal fade" id="modalProjectTaskTeam" data-bs-focus="false" role="dialog" aria-labelledby="ModalProjectTaskTeamLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="ModalProjectTaskTeamLabel"><?php echo $functions->translate('Task participants'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-body">
            <input type='hidden' name='ModalProjectID' id='ModalProjectID' class='form-control' value=''>
            <input type='hidden' name='ModalProjectTaskID' id='ModalProjectTaskID' class='form-control' value=''>
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="teamusersforadd"><?php echo $functions->translate('Select user for add'); ?></label>
                  <select class="form-control select2" id="teamusersforadd" name="teamusersforadd">
                    <?php

                    $UsersArray = getUsersAttachedGroupsAttachedInRole("100007,100008");

                    $sql = "SELECT ID, CONCAT(users.Firstname,' ',users.Lastname) AS FullName, Username
                          FROM users
                          WHERE ID IN (" . implode(",", $UsersArray) . ") AND ID NOT IN (SELECT UserID FROM project_tasks_users WHERE ProjectTaskID = $ProjectTaskID)";

                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    while ($row = mysqli_fetch_array($result)) {
                      $UsersID = $row['ID'];
                      $FullName = $row['FullName'];
                      echo "<option value=\"$UsersID\">$FullName</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="teamusersforremoval"><?php echo $functions->translate('Select user for removal'); ?></label>
                  <select class="form-control select2" id="teamusersforremoval" name="teamusersforremoval">
                    <?php

                    $sql = "SELECT ID, CONCAT(users.Firstname,' ',users.Lastname,' ','(',users.username,')') AS FullName
                      FROM users 
                      WHERE ID IN (SELECT UserID FROM project_tasks_users WHERE ProjectTaskID = $ProjectTaskID)
                      ORDER BY users.Firstname ASC";

                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    while ($row = mysqli_fetch_array($result)) {
                      $UsersID = $row['ID'];
                      $FullName = $row['FullName'];
                      echo "<option value=\"$UsersID\">$FullName</option>";
                    }

                    ?>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button name="btnremovefromproject" id="btnremovefromproject" class="btn btn-sm btn-danger float-end" onclick="removeUserFromProject();"><span class=""></span> <?php echo _("Remove"); ?></button>
              <button name="btnaddtoproject" id="btnaddtoproject" class="btn btn-sm btn-success float-end" onclick="addUserToProject();"><span class=""></span> <?php echo _("Add"); ?></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal ModalProjectTeam-->