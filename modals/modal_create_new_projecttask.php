<?php
$StartDefaultDate = date("d-m-Y H:i");
$DeadlineDefaultDate = date("d-m-Y H:i", strtotime("+2 hours"));
?>
<script>
  $(function() {

    jQuery('#ProjectTaskStart').datetimepicker({
      format: 'd-m-Y H:i',
      prevButton: false,
      nextButton: false,
      step: 60,
      dayOfWeekStart: 1
    });
    $.datetimepicker.setLocale('<?php echo $languageshort ?>');

    jQuery('#ProjectTaskDeadline').datetimepicker({
      format: 'd-m-Y H:i',
      prevButton: false,
      nextButton: false,
      step: 60,
      dayOfWeekStart: 1
    });
    $.datetimepicker.setLocale('<?php echo $languageshort ?>');
  });
</script>
<!-- Modal Create Project Task -->
<div class="modal fade" id="modalCreateNewProjectTask" data-bs-focus="false" role="dialog" aria-labelledby="ModalCreateNewProjectTaskLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="ModalCreateNewProjectTaskHeader"></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div id='ModalProjectID' hidden></div>
        <input type='hidden' name='ModalProjectTaskID' id='ModalProjectTaskID' class='form-control' value=''>
        <div class="row">
          <div class="card-group">
            <div class="card">
              <div class="card-body">
                <form id="createProjectTask">
                  <div class="row">
                    <div class="col-lg-8 col-sm-12 col-xs-12" id="ModalProjectSelector">
                      <label for="ModalProjectSelect"><?php echo _("Project"); ?> <code>*</code></label>
                      <div class="input-group input-group-static mb-4">
                        <select id="ModalProjectSelect" name="ModalProjectSelect" class="form-control select2" onchange="changeProjectID()" required>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-4 col-sm-12 col-xs-12">
                      <label for="ProjectRelatedCategory"><?php echo @$functions->translate("Category") ?></label>
                      <div class="input-group input-group-static mb-4">
                        <select id="ProjectRelatedCategory" name="ProjectRelatedCategory" class="form-control select2">
                          <option value="" label=""></option>
                          <?php
                          $sql = "SELECT projects_tasks_categories.ID, projects_tasks_categories.ShortName 
                                  FROM projects_tasks_categories
                                  ORDER BY projects_tasks_categories.ShortName ASC;";
                          $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                          while ($row = mysqli_fetch_array($result)) {
                            echo "<option value='" . $row['ID'] . "'>" . @$functions->translate($row['ShortName']) . "</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-12 col-sm-12 col-xs-12">
                      <div class="input-group input-group-static mb-4">
                        <label for="ProjectTaskName"><?php echo _("Name"); ?> <code>*</code></label>
                        <input type="text" id="ProjectTaskName" name="ProjectTaskName" class="form-control">
                      </div>
                    </div>

                    <div class="col-lg-12 col-sm-12 col-xs-12">
                      <div class="input-group input-group-static mb-4">
                        <label for="ProjectTaskDescription"><?php echo _("Description"); ?>&ensp;<a href="javascript:toggleCKEditor('ProjectTaskDescription','350');"><i class="fa-solid fa-pen fa-sm" title="Double click on field to edit"></i></a></label>
                      </div>
                      <div style="height: 150px; word-wrap: break-word; overflow-y: auto; overflow-x: auto;" class="resizable_textarea form-control" id="ProjectTaskDescription" name="ProjectTaskDescription" title="Double click to edit" autocomplete="off" ondblclick="toggleCKEditor('ProjectTaskDescription','350');">
                      </div><br>
                    </div>

                    <div class="col-lg-3 col-sm-12 col-xs-12">
                      <label for="TaskResponsible"><?php echo _("Task Responsible"); ?> <code>*</code></label>
                      <div class="input-group input-group-static mb-4">
                        <select class="form-control select2" id="TaskResponsible" name="TaskResponsible" required>
                          <?php
                            $Groups = "100007,100008";
                            $UsersArray = [];
                            $UsersArray = getUsersAttachedGroupsAttachedInRole($Groups);
                            if(!empty($UsersArray)){
                              $sql = "SELECT ID, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
                                      FROM users
                                      WHERE ID IN (" . implode(",", $UsersArray) . ")
                                      ORDER BY FullName ASC;";
                              $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                              while ($row = mysqli_fetch_array($result)) {
                                $SessionUserID = $_SESSION['id'];
                                $ID = $row['ID'];
                                $FullName = $row['FullName'];
                                if ($ID == $SessionUserID) {
                                  echo "<option value=\"$ID\" selected=\"true\">$FullName</option>";
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
                      <label for="ProjectTaskStatus"><?php echo _("Status"); ?> <code>*</code></label>
                      <div class="input-group input-group-static mb-4">
                        <select class="form-control select2" id="ProjectTaskStatus" name="ProjectTaskStatus" required>
                          <?php
                          $sql = "SELECT ID, StatusName 
                                  FROM projects_statuscodes
                                  WHERE projects_statuscodes.Active = 1";
                          $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                          while ($row = mysqli_fetch_array($result)) {
                            $ID = $row["ID"];
                            $StatusName = @$functions->translate($row['StatusName']);
                            echo "<option value=\"$ID\">$StatusName</option>";
                          }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-6 col-sm-12 col-xs-12">
                      <label for="ProjectTaskParent"><?php echo @$functions->translate("Parent") . " " . @$functions->translate("task"); ?></label>
                      <div class="input-group input-group-static mb-4">
                        <select class="form-control select2" id="ProjectTaskParent" name="ProjectTaskParent">
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-3 col-sm-12 col-xs-12">
                      <div class="input-group input-group-static mb-4">
                        <label for="ProjectTaskStart"><?php echo @$functions->translate("Start"); ?> <code>*</code></label>
                        <input type="text" id="ProjectTaskStart" name="ProjectTaskStart" class="form-control" autocomplete="off" value="<?php echo $StartDefaultDate; ?>">
                      </div>
                    </div>

                    <div class="col-lg-3 col-sm-12 col-xs-12">
                      <div class="input-group input-group-static mb-4">
                        <label for="ProjectTaskDeadline"><?php echo @$functions->translate("Deadline"); ?> <code>*</code></label>
                        <input type="text" id="ProjectTaskDeadline" name="ProjectTaskDeadline" class="form-control" autocomplete="off" value="<?php echo $DeadlineDefaultDate; ?>">
                      </div>
                    </div>

                    <div class="col-lg-3 col-sm-12 col-xs-12">
                      <div class="input-group input-group-static mb-4">
                        <label for="ProjectTaskProgress" title="<?php echo _("Insert number for progress, example 70 as in 70% complete"); ?>"><?php echo @$functions->translate("Progress %"); ?></label>
                        <input type="number" id="ProjectTaskProgress" name="ProjectTaskProgress" class="form-control" value="0">
                      </div>
                    </div>

                    <div class="col-lg-3 col-sm-12 col-xs-12">
                      <label for="ProjectPrivate" title="<?php echo _("If project task is private only task participants can view and access project task"); ?>"><?php echo @$functions->translate("Private"); ?></label>
                      <div class="input-group input-group-static mb-4">
                        <select id="ProjectPrivate" name="ProjectPrivate" class="form-control select2">
                          <?php
                          echo "<option value='0' selected='true'>" . _("Open") . "</option>";
                          echo "<option value='1'>" . _("Private") . "</option>";
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-lg-3 col-sm-12 col-xs-12">
                      <div class="input-group input-group-static mb-4">
                        <label for="ProjectTaskEstimatedBudget"><?php echo _("Estimated Budget"); ?></label>
                        <input type="number" id="ProjectTaskEstimatedBudget" name="ProjectTaskEstimatedBudget" class="form-control" value="0">
                      </div>
                    </div>

                    <div class="col-lg-3 col-sm-12 col-xs-12">
                      <div class="input-group input-group-static mb-4">
                        <label for="ProjectTaskBudgetSpend"><?php echo _("Budget spend"); ?></label>
                        <input type="number" id="ProjectTaskBudgetSpend" name="ProjectTaskBudgetSpend" class="form-control" value="0">
                      </div>
                    </div>

                    <div class="col-lg-3 col-sm-12 col-xs-12">
                      <div class="input-group input-group-static mb-4">
                        <label for="ProjectTaskEstimatedHours"><?php echo _("Estimated Hours"); ?></label>
                        <input type="number" id="ProjectTaskEstimatedHours" name="ProjectTaskEstimatedHours" class="form-control" value="0">
                      </div>
                    </div>

                    <div class="col-lg-3 col-sm-12 col-xs-12">
                      <div class="input-group input-group-static mb-4">
                        <label for="ProjectTaskHoursSpend"><?php echo @$functions->translate("Hours spend"); ?></label>
                        <input type="number" id="ProjectTaskHoursSpend" name="ProjectTaskHoursSpend" class="form-control" value="0">
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button name="btncreateprojecttask" id="btncreateprojecttask" class="btn btn-sm btn-success float-end" onclick="createProjectTask();"><span class=""></span> <?php echo _("Create"); ?></button>
        <button name="btnupdateprojecttask" id="btnupdateprojecttask" class="btn btn-sm btn-success float-end" onclick="updateProjectTask();"><span class=""></span> <?php echo _("Update"); ?></button>
        <button name="btncreateduplicateprojecttask" id="btncreateduplicateprojecttask" class="btn btn-sm btn-secondary float-end" onclick="createProjectTask();"><span class=""></span> <?php echo _("Create duplicate"); ?></button>
        <button name="btndeleteprojecttask" id="btndeleteprojecttask" class="btn btn-sm btn-danger float-end" onclick="deleteProjectTask();"><span class=""></span> <?php echo _("Delete"); ?></button>
      </div>
    </div>
  </div>
</div>
<!-- End Modal Create New Project Task -->