<!-- Modal New ProjectActivity -->
<div class="modal fade" id="modalNewProjectActivity" data-bs-focus="false" role="dialog" aria-labelledby="NewProjectActivityLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="NewProjectActivityLabel"><?php echo _('New activity'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div id="projecttaskid" hidden></div>
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-lg-12 col-sm-12 col-xs-12">
                <div>
                  <div class="input-group input-group-static mb-4">
                    <label for="ModalProjectActivityDescription"><?php echo _("Description"); ?>&ensp;<a href="javascript:toggleCKEditor('ModalProjectActivityDescription');"><i class="fa-solid fa-pen fa-sm" title="Double click on field to edit"></i></a></label>
                  </div>
                  <div style="height: 150px; word-wrap: break-word; overflow-y: auto; overflow-x: auto;" class="resizable_textarea form-control" id="ModalProjectActivityDescription" name="ModalProjectActivityDescription" title="Double click to edit" autocomplete="off" ondblclick="toggleCKEditor('ModalProjectActivityDescription');">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button name='createProjectActivity' id='createProjectActivity' class='btn btn-sm btn-success float-end' onclick="createProjectActivity();"> <?php echo _('Create'); ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal New ProjectActivity-->