<!-- Modal for viewing any element -->
<?php
$UserID = $_SESSION['id'];
?>
<div class="modal fade" id="modalElementViewer" data-bs-focus="false" role="dialog" aria-labelledby="ModalElementViewerLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="ModalElementViewerLabel"></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div id="ModalElementViewerContent"></div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal element viewer -->