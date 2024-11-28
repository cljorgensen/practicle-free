<!-- Modal Document Archive -->
<div class="modal fade" id="modalArchivedDoc" data-bs-focus="false" role="dialog" aria-labelledby="ModalArchivedDocLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="ModalArchivedDocLabel"></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-body">
            <form role='form' method='POST'>
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <input type="hidden" id="ModalArchiveDocID" name="ModalArchiveDocID" class="form-control">
                <div class="row">
                  <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                      <label for="ModalArchiveDocVersion"><?php echo _('Version'); ?></label>
                      <input type="text" class="form-control" id="ModalArchiveDocVersion" name="ModalArchiveDocVersion">
                    </div>
                  </div>
                  <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                      <label for="ModalArchiveDocUser"><?php echo _('Created by'); ?></label>
                      <input class="form-control" id="ModalArchiveDocUser" name="ModalArchiveDocUser" type="text">
                    </div>
                  </div>
                  <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                      <label for="ModalArchiveDocDate"><?php echo _('Date'); ?></label>
                      <input type="text" id="ModalArchiveDocDate" name="ModalArchiveDocDate" value="" class="form-control">
                    </div>
                  </div>
                  <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12" id="restoreBtn">
                  </div>
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class='form-group'>
                      <label for="ModalArchiveDocContent"><?php echo _('Content'); ?></label>
                      <div class="form-control" id="ModalArchiveDocContent" name="ModalArchiveDocContent" rows="20"></div>
                    </div>
                  </div>
                </div>
              </div>
          </div>
          <div class="modal-footer">
          </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal Document Archive -->