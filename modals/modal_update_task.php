<!-- Modal Edit Task -->

<div class="modal fade" id="modalEditKanBanTaskModal" data-bs-focus="false" role="dialog" aria-labelledby="EditKanBanTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="EditKanBanTaskModalLabel"><?php echo _('Task'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <input type="text" class="form-control" id="ModalEditTableName" name="ModalEditTableName" hidden>
              <input type="text" class="form-control" id="ModalEditTaskID" name="ModalEditTaskID" hidden>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="ModalEditTaskSubject"><?php echo _('Subject'); ?></label>
                  <input type="text" class="form-control" id="ModalEditTaskSubject" name="ModalEditTaskSubject" disabled>
                </div>
              </div>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for='ModalEditTaskDeadline'><?php echo _('Deadline'); ?></label>
                  <input type='text' class='form-control' id="ModalEditTaskDeadline" name="ModalEditTaskDeadline">
                  <script>
                    $(function() {
                      jQuery('#ModalEditTaskDeadline').datetimepicker({
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
          </div>
          <div class="modal-footer">
            <button id="taskbtnUpdate" name="taskbtnUpdate" onclick="updateTask()" class="btn btn-sm btn-success float-end"> <?php echo _('Update'); ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal Update Task-->