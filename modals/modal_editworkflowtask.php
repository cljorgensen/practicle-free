<!-- Modal WorkFlowStep-->
<div class="modal fade" id="modalEditWFT" data-bs-focus="false" role="dialog" aria-labelledby="modalEditWFTLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="exampleModalLabel"><?php echo _('Task'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class='modal-body'>
        <div class="card">
          <div class="card-body">
            <div class="input-group input-group-static mb-4">
              <div id="WFID" hidden></div>
              <div id="WFTID" hidden></div>
            </div>
            <form id="EditWFT">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="EditWFTStepName"><?php echo _('Name'); ?></label>
                  <input type='text' name='EditWFTStepName' id='EditWFTStepName' class='form-control' value=''>
                </div>
              </div>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for='EditWFTDescription'><?php echo _('Description'); ?></label>
                  <textarea name='EditWFTDescription' id='EditWFTDescription' rows='10' class='resizable_textarea form-control'></textarea>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="EditWFTRelatedStatusID"><?php echo _('Status'); ?></label>
                    <select class="form-control" id="EditWFTRelatedStatusID" name='EditWFTRelatedStatusID'>
                      <option value='1' selected><?php echo "ToDo" ?></option>
                      <option value='2' selected><?php echo "Doing" ?></option>
                      <option value='3' selected><?php echo "Done" ?></option>
                    </select>
                  </div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="EditWFTRelatedUserID"><?php echo _('Responsible'); ?></label>
                    <select id="EditWFTRelatedUserID" name="EditWFTRelatedUserID" class="form-control">
                      <?php
                      $sql = "SELECT users.ID AS UserID, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
                          FROM users 
                          WHERE users.Active=1 AND users.RelatedUserTypeID IN (1,3)";

                      $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                      while ($row = mysqli_fetch_array($result)) {
                        $UserID = $row['UserID'];
                        $Fullname = $row['FullName'];
                        echo "<option value='$UserID'>$Fullname</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>

                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for='EditWFTDeadline'><?php echo _('Deadline'); ?></label>
                    <input type='text' class='form-control' id='EditWFTDeadline' name='EditWFTDeadline' value='<?php echo date('d-m-Y H:i'); ?>'>
                    <script>
                      $(function() {
                        jQuery('#EditWFTDeadline').datetimepicker({
                          format: 'd-m-Y H:i',
                          prevButton: false,
                          nextButton: false,
                          step: 60,
                          dayOfWeekStart: 1
                        });
                        $.datetimepicker.setLocale('<?php echo $languageshort ?>');
                      });
                    </script>
                  </div>
                </div>
              </div>
            </form>
          </div>

          <div class='modal-footer'>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <button id="updateWFTBtn" name="updateWFTBtn" onclick='updateWorkFlowTask()' class='btn btn-sm btn-success float-end'> <?php echo _('Update'); ?></button>
              <button id="createFWTBtn" name="createFWTBtn" onclick='openNewWorkFlowTask()' class='btn btn-sm btn-info float-end'> <?php echo _('Create'); ?></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal WorkFlowStep-->