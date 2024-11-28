<script>
  async function createNewProjectSprint() {
    const ProjectID = '<?php echo $ProjectID ?>';
    const StartDate = document.getElementById('StartDate').value;
    const Deadline = document.getElementById('Deadline').value;
    const Responsible = document.getElementById('Responsible').value;
    const SprintEstimatedBudget = document.getElementById('SprintEstimatedBudget').value;
    const SprintName = document.getElementById('Name').value;
    const SprintDescription = document.getElementById('SprintDescription').value;
    const Link = document.getElementById('Link').value;
    const Version = document.getElementById('Version').value;

    await $.ajax({
      type: 'GET',
      url: './getdata.php?createNewProjectSprint',
      data: {
        ProjectID: ProjectID,
        StartDate: StartDate,
        Deadline: Deadline,
        Responsible: Responsible,
        SprintEstimatedBudget: SprintEstimatedBudget,
        SprintName: SprintName,
        SprintDescription: SprintDescription,
        Link: Link,
        Version: Version
      },
      success: function(data) {
        var obj = JSON.parse(data);
        for (var i = 0; i < obj.length; i++) {
          var Result = obj[i].Result;
          var Message = obj[i].Message;
          if (Result === "fail") {
            pnotify(Message, "danger");
          }
          if (Result === "success") {
            messagetext = 'Sprint: ' + Message + ' <?php echo _("created") ?>';
            localStorage.setItem('pnotify', messagetext);
            location.reload();
          }
        }
      }
    });
    $("#modalCreateNewProjectSprint").modal("hide");
  }



  async function updateProjectSprint() {

    SprintID = document.getElementById('Sprints').value
    StartDate = document.getElementById('StartDate').value
    Deadline = document.getElementById('Deadline').value
    Responsible = document.getElementById('Responsible').value
    SprintEstimatedBudget = document.getElementById('SprintEstimatedBudget').value
    SprintName = document.getElementById('Name').value
    SprintDescription = document.getElementById('SprintDescription').value
    Link = document.getElementById('Link').value
    Version = document.getElementById('Version').value

    await $.ajax({
      type: 'GET',
      url: './getdata.php?updateProjectSprint',
      data: {
        SprintID: SprintID,
        StartDate: StartDate,
        Deadline: Deadline,
        Responsible: Responsible,
        SprintEstimatedBudget: SprintEstimatedBudget,
        SprintName: SprintName,
        SprintDescription: SprintDescription,
        Link: Link,
        Version: Version
      },
      success: function(data) {
        var obj = JSON.parse(data);
        for (var i = 0; i < obj.length; i++) {
          var Result = obj[i].Result;
          var Message = obj[i].Message;
          if (Result === "fail") {
            pnotify(Message, "danger");
          }
          if (Result === "success") {
            let messagetext = Message + ": " + "<?php echo _("updated") ?>";
            localStorage.setItem('pnotify', messagetext);
            location.reload();
          }
        }
      }
    });
  }
</script>

<!-- Modal New Project -->
<div class="modal fade" id="modalCreateNewProjectSprint" data-bs-focus="false" role="dialog" aria-labelledby="ModalCreateNewProjectSprintLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="ModalCreateNewProjectSprintLabel"><?php echo _('Sprint administration'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-body">
            <input type='hidden' name='ModalProjectID' id='ModalProjectID' class='form-control' value=''>
            <div class="container-fluid">
              <div class="row">
                <div class="col-md-8 col-sm-8 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="Sprints"><?php echo _('Existing sprints'); ?></label>
                    <select id="Sprints" name="Sprints" class="form-control" onchange="editProjectSprint(this.value)">
                      <option value='-1' label=''></option>
                      <?php
                      $sql = "SELECT ID, ShortName
                          FROM projects_sprints
                          WHERE RelatedProjectID = $ProjectID
                          ORDER BY projects_sprints.Deadline ASC";
                      $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                      while ($row = mysqli_fetch_array($result)) {
                        $ProjectSprintID = $row['ID'];
                        $ProjectSprintShortName = $row['ShortName'];

                        echo "<option value='$ProjectSprintID'>$ProjectSprintShortName</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <div class="col-md-4 col-sm-4 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="SprintEstimatedBudget"><?php echo _('Estimated Budget'); ?></label>
                    <input type="number" id="SprintEstimatedBudget" name="SprintEstimatedBudget" class="form-control" value="0">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-3 col-sm-3 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="StartDate"><?php echo _('Start'); ?></label>
                    <input type="text" id="StartDate" name="StartDate" class="form-control" autocomplete="off" value="<?php echo $datenow; ?>">
                  </div>
                </div>

                <div class="col-md-3 col-sm-3 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="Deadline"><?php echo _('Deadline'); ?></label>
                    <input type="text" id="Deadline" name="Deadline" class="form-control" autocomplete="off" value="<?php echo $datenow; ?>">
                  </div>
                </div>

                <div class="col-md-6 col-sm-6 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="Responsible"><?php echo _('Responsible'); ?></label>
                    <select id="Responsible" name="Responsible" class="form-control" required>
                      <option value='-1' label=''></option>
                      <?php
                      $sql = "SELECT ID, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
                          FROM users
                          WHERE RelatedUserTypeID=1";
                      $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                      while ($row = mysqli_fetch_array($result)) {
                        echo "<option value='" . $row['ID'] . "'>" . $row['FullName'] . "</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="Link" title="<?php echo _("Doubleclick to visit") ?>"><?php echo _('Link'); ?></label>
                    <input type="text" id="Link" name="Link" class="form-control" autocomplete="off" ondblclick="window.open(this.value, '_blank');">
                  </div>
                </div>

                <div class="col-md-6 col-sm-6 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="Version"><?php echo _('Version'); ?></label>
                    <input type="text" id="Version" name="Version" class="form-control" autocomplete="off">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="Name"><?php echo _('Sprint Shortname'); ?></label>
                    <input type="text" id="Name" name="Name" class="form-control" value="">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="SprintDescription"><?php echo _('Description'); ?></label>
                    <textarea id="SprintDescription" name="SprintDescription" class="form-control" value=""></textarea>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <button name="createprojectsprint" id="createprojectsprint" class="btn btn-sm btn-success float-end" onclick="createNewProjectSprint();"><span class=""></span> <?php echo _("Create"); ?></button>
                  <button name="updateprojectsprint" id="updateprojectsprint" class="btn btn-sm btn-success float-end" onclick="updateProjectSprint()"><span class=""></span> <?php echo _("Update"); ?></button>
                  <button name="deleteprojectsprint" id="deleteprojectsprint" class="btn btn-sm btn-danger float-end" onclick="deleteProjectSprint()"><span class=""></span> <?php echo _("Delete"); ?></button>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  $(function() {
    jQuery('#StartDate').datetimepicker({
      format: 'd-m-Y H:i',
      prevButton: false,
      nextButton: false,
      step: 60,
      dayOfWeekStart: 1
    });
    $.datetimepicker.setLocale('<?php echo $languageshort ?>');

    jQuery('#Deadline').datetimepicker({
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