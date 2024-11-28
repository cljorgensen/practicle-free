<!-- Modal for creating unit elements (users, groups, roles, companies etc.) -->
<?php
$UserID = $_SESSION['id'];
?>
<div class="modal fade" data-bs-focus="false" id="modalUnitCreate" role="dialog" aria-labelledby="ModalUnitCreateLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
    <div class="modal-content">
      <div id="UnitType" hidden></div>
      <div class="modal-header"><i class="fa-solid fa-sitemap"></i>
        <button class="btn btn-link text-dark p-0" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-body">
            <div id="modalUnitHeader">
              <h6></h6>
            </div>
            <br>
            <form id="FormModalUnitCreate">
              <div class="row" id="unitFieldsContainer"></div>
            </form>
          </div>
          <div class="modal-footer">
            <div id="createUnitBtn"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal unit viewer -->