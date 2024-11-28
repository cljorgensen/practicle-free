<!-- Modal TimeRegistration-->
<?php
$UserID = $_SESSION['id'];
?>
<div class="modal fade" id="modalTimeRegistration" data-bs-focus="false" role="dialog" aria-labelledby="ModalTimeRegistrationLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="ModalTimeRegistrationLabel"><?php echo _('New Timeregistration'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-body">
            <form role='form' method='POST'>
              <input type='hidden' name='ModalRegID' id='ModalRegID' class='form-control' value=''>
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="ModalRelatedTask"><?php echo _('Related task'); ?></label>
                  <select class="form-control" id="ModalRelatedTask" name='ModalRelatedTask'>
                    <?php
                    $sql = "SELECT ID, Headline, RelatedElementTypeID
                          FROM taskslist 
                          WHERE RelatedUserID = $UserID AND DateAdded BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW() AND Status != 4
                          ORDER BY Deadline ASC, Headline ASC";
                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    while ($row = mysqli_fetch_array($result)) {
                      $TaskID = $row['ID'];
                      $Headline = $row['Headline'];
                      $RelatedElementTypeID = $row['RelatedElementTypeID'];
                      $ModuleName = getModuleNameFromModuleID($RelatedElementTypeID);
                      echo "<option value='$TaskID'>$ModuleName: $Headline</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="ModalTimeSpend" title="<?php echo _("Example: 15 as in 15 minutes") ?>"><?php echo _('Time spend') . " (" . _("minutes") . ")"; ?></label>
                  <input type='number' name='ModalTimeSpend' id='ModalTimeSpend' class='form-control' value='<?php echo date('15'); ?>'>
                </div>
              </div>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for='ModalDescription'><?php echo _('Description'); ?></label>
                  <textarea name='ModalDescription' id='ModalDescription' rows='10' class='resizable_textarea form-control'></textarea>
                </div>
              </div>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="ModalBillable"><?php echo _('Billable'); ?></label>
                  <select class="form-control" id="ModalBillable" name='ModalBillable'>
                    <option value='1' selected><?php echo _("Yes") ?></option>
                    <option value='0'><?php echo _("No") ?></option>
                  </select>
                </div>
              </div>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for='ModalDatePerformed'><?php echo _('Date performed'); ?></label>
                  <input type='text' class='form-control' id='ModalDatePerformed' name='ModalDatePerformed' value='<?php echo date('d-m-Y H:i'); ?>'>
                  <script>
                    $(function() {

                      jQuery('#ModalDatePerformed').datetimepicker({
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
              <input type='hidden' name='Taskid' id='Taskid' class='form-control' value='' readonly>
          </div>
          <div class="modal-footer">
            <button id="timeregbtnCreate" name="timeregbtnCreate" onclick='addTimeRegistration()' class='btn btn-sm btn-success float-end'> <?php echo _('Create'); ?></button>
            <button id="timeregbtnUpdate" name="timeregbtnUpdate" onclick='changeTimeRegistration()' class='btn btn-sm btn-success float-end'> <?php echo _('Update'); ?></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal TimeRegistration-->