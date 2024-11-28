<!-- Modal Master for general tasks -->
<div class="modal fade" id="modalMaster" data-bs-focus="false" role="dialog" aria-labelledby="ModalMasterLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
    <div class="modal-content">
      <div id="UnitType" hidden></div>
      <div id="UnitTypeID" hidden></div>
      <div class="modal-header">
        <div id="HeaderModalMaster"></div>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div id="ModalMasterInformation" hidden>
          <span id="collapseInfoMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseInfo" aria-expanded="false" aria-controls="collapseInfo"><i class="fa-solid fa-circle-info float-right" title="<?php echo _("Information") ?>"></i></a></span>
          <div id="collapseInfo" class="collapse width" data-bs-parent="#accordionMore">
            <div class="card-body" id="ModalMasterInformationText">
            </div>
          </div>
        </div>
        <div class="card">
          <div class="card-body">
            <div id="SubHeaderModalMaster"></div>
            <form id="FormModalMaster">
              <div class="row" id="FieldsContainerModalMaster"></div>
            </form>
            <div class="col-lg-12 col-md-12 col-sm-12">
              <div id="ModalMasterProgress" hidden>
                <div class="progress-wrapper">
                  <div class="progress-info">
                    <div class="progress-percentage">
                      <span class="text-sm font-weight-normal">0%</span>
                    </div>
                  </div>
                  <div class="progress">
                    <div class="progress-bar bg-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <div class="col-lg-12 col-md-12 col-sm-12">
              <div id="ModalMasterBtns"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal Master viewer -->