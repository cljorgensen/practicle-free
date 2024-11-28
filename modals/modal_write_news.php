<!-- Modal Write News -->
<div class='modal fade' id='modalViewNews' data-bs-focus="false" role='dialog'>
  <div class='modal-dialog'>
    <!-- Modal content-->
    <div class='modal-content'>
      <div class='modal-header'>
        <h6 class="modal-title font-weight-normal" id="exampleModalLabel" id='NewsModal'><?php echo _('News'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class='modal-body'>
        <div class="card">
          <div class="card-body">
            <form role='form' method='POST'>
              <div class='form-group'>
                <label class='control-label col-md-12 col-sm-12 col-xs-12'><?php echo _('Description'); ?></label><br>
                <div class='autocomplete=off col-md-12 col-sm-12 col-xs-12'>
                  <textarea name='ModalNewsDescription' id='ModalNewsDescription' rows='10' class='resizable_textarea form-control'></textarea>
                </div>
              </div>
            </form>
          </div>
          <div class='modal-footer'>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal Write News-->