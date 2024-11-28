</div>
<?php
  echo @$functions->designGetFooter($Version);
?>
</div>
</main>
<div class="fixed-plugin">
  <div class="card shadow-lg">
    <div class="pb-0 pt-3">
      <div class="float-end mt-4">
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <!-- End Toggle Button -->
    </div>
    <div class="card-body pt-sm-1 pt-0">
      <hr class="horizontal dark my-3">
      <div class="mt-1 d-flex">
        <h6 class="mb-0"><?php echo _("Light") ?> / <?php echo _("Dark") ?></h6>
        <div class="form-check form-switch ps-0 ms-auto my-auto">
          <input class="form-check-input mt-1 ms-auto" type="checkbox" id="dark-version" onclick="darkmodeswitch(this)">
        </div>
      </div>
      <hr class="horizontal dark my-3">
      <div class="mt-1 d-flex">
        <?php
        $UserGroupsTemp = $_SESSION['memberofgroups'];
        if (in_array("100001", $UserGroups)) {
          echo "<a href=\"javascript:$('#modalReportProblem').modal('show');\">
                    <h6 class=\"mb-0\">" . _("Report a problem") . "</h6>
                  </a>";
        }
        ?>
      </div>
      <hr class="horizontal dark my-3">
      <script>
        function deleteFavoriteListEntry(FavoriteID) {
          var urlget = './getdata.php?deleteFavoriteEntry';
          $.ajax({
            url: urlget,
            data: {
              FavoriteID: FavoriteID
            },
            type: 'GET',
            success: function(data) {
              var TableFavorites = $('#TableFavorites').DataTable();
              TableFavorites.ajax.reload();
              pnotify('<?php echo _("Favorite deleted") ?>', 'success');
            }
          });
        }

        function deleteWatchlistEntry(WLID) {
          var urlget = './getdata.php?deleteWatchlistEntry';
          $.ajax({
            url: urlget,
            data: {
              WLID: WLID
            },
            type: 'GET',
            success: function(data) {
              var TableWatchlist = $('#TableWatchlist').DataTable();
              TableWatchlist.ajax.reload();
              pnotify('<?php echo _("Whatch list entry deleted") ?>', 'success');
            }
          });
        }

        $(document).ready(function() {

          var ajaxurl = "getdata.php?getFavorites";
          var TableFavorites = $('#TableFavorites').DataTable({
            "dom": 'Bfrtip',
            "searching": false,
            "bFilter": true,
            "paging": true,
            "info": false,
            "pagingType": 'numbers',
            "processing": true,
            "deferRender": true,
            "pageLength": 10,
            "orderCellsTop": true,
            "fixedHeader": false,
            "autoWidth": false,
            "aaSorting": [],
            "responsive": true,
            "language": {
              url: '../assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
            },
            "buttons": [],
            "bStateSave": false,
            "displayLength": 10,
            "ajax": {
              "url": ajaxurl,
              "type": "POST",
              "dataSrc": ""
            },
            "columnDefs": [{
              "targets": 0,
              "data": "ID",
              "width": "0%",
              "visible": false
            }, {
              "targets": 1,
              "data": "Link",
              "width": "100%",
              "visible": true
            }, {
              "targets": 2,
              "data": "DeleteLink",
              "width": "100%",
              "visible": true
            }]
          });


          var ajaxurl = "getdata.php?getWatchlist";
          var TableWatchlist = $('#TableWatchlist').DataTable({
            "dom": 'Bfrtip',
            "searching": false,
            "bFilter": true,
            "paging": true,
            "info": false,
            "pagingType": 'numbers',
            "processing": true,
            "deferRender": true,
            "pageLength": 10,
            "orderCellsTop": true,
            "fixedHeader": false,
            "autoWidth": false,
            "aaSorting": [],
            "responsive": true,
            "language": {
              url: '../assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
            },
            "buttons": [],
            "bStateSave": false,
            "displayLength": 10,
            "ajax": {
              "url": ajaxurl,
              "type": "POST",
              "dataSrc": ""
            },
            "columnDefs": [{
              "targets": 0,
              "data": "ID",
              "width": "0%",
              "visible": false
            }, {
              "targets": 1,
              "data": "Link",
              "width": "100%",
              "visible": true
            }, {
              "targets": 2,
              "data": "DeleteLink",
              "width": "100%",
              "visible": true
            }]
          });

          var ajaxurl = "getdata.php?getShortcuts";
          var TableShortCuts = $('#TableShortCuts').DataTable({
            "dom": 'Bfrtip',
            "searching": false,
            "bFilter": true,
            "paging": true,
            "info": false,
            "pagingType": 'numbers',
            "processing": true,
            "deferRender": true,
            "pageLength": 10,
            "orderCellsTop": true,
            "fixedHeader": false,
            "autoWidth": false,
            "aaSorting": [],
            "responsive": true,
            "language": {
              url: '../assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
            },
            "buttons": [],
            "bStateSave": false,
            "displayLength": 10,
            "ajax": {
              "url": ajaxurl,
              "type": "POST",
              "dataSrc": ""
            },
            "columnDefs": [{
              "targets": 0,
              "data": "ID",
              "width": "0%",
              "visible": false
            }, {
              "targets": 1,
              "data": "Link",
              "width": "100%",
              "visible": true
            }]
          });

          var ajaxurl = "getdata.php?getGenericForms";
          var TableGenericForms = $('#TableGenericForms').DataTable({
            "dom": 'Bfrtip',
            "searching": false,
            "bFilter": true,
            "paging": true,
            "info": false,
            "pagingType": 'numbers',
            "processing": true,
            "deferRender": true,
            "pageLength": 10,
            "orderCellsTop": true,
            "fixedHeader": false,
            "autoWidth": false,
            "aaSorting": [],
            "responsive": true,
            "language": {
              url: '../assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
            },
            "buttons": [],
            "bStateSave": false,
            "displayLength": 10,
            "ajax": {
              "url": ajaxurl,
              "type": "POST",
              "dataSrc": ""
            },
            "columnDefs": [{
              "targets": 0,
              "data": "ID",
              "width": "0%",
              "visible": false
            }, {
              "targets": 1,
              "data": "Link",
              "width": "100%",
              "visible": true
            }]
          });
        });
      </script>
      <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
          <div class="accordion" id="accordionQuickMenus">
            <div class="accordion-item mb-0">
              <h6 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                  <i class="material-icons py-1">shortcut</i> <?php echo " " . _("Shortcuts") ?>
                  <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                  <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                </button>
              </h6>
              <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionQuickMenus">
                <div class="accordion-body text-sm opacity-8">
                  <table id="TableShortCuts" class="table table-borderless">
                    <thead>
                      <tr>
                        <th><?php echo _("Name"); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="accordion-item mb-0">
              <h6 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                  <i class="material-icons py-1">bookmarks</i> <?php echo " " . _("Favorites") ?>
                  <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                  <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                </button>
              </h6>
              <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionQuickMenus">
                <div class="accordion-body text-sm opacity-8">
                  <table id="TableFavorites" class="table table-borderless">
                    <thead>
                      <tr>
                        <th><?php echo _("Name"); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="accordion-item mb-0">
              <h6 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                  <i class="material-icons py-1">visibility</i> <?php echo " " . _("Watch list") ?>
                  <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                  <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                </button>
              </h6>
              <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionQuickMenus">
                <div class="accordion-body text-sm opacity-8">
                  <table id="TableWatchlist" class="table table-borderless">
                    <thead>
                      <tr>
                        <th><?php echo _("Name"); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="accordion-item mb-0">
              <h6 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                  <i class="material-icons py-1">visibility</i> <?php echo " " . _("Generic forms") ?>
                  <i class="collapse-close fa fa-plus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                  <i class="collapse-open fa fa-minus text-xs pt-1 position-absolute end-0 me-3" aria-hidden="true"></i>
                </button>
              </h6>
              <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionQuickMenus">
                <div class="accordion-body text-sm opacity-8">
                  <table id="TableGenericForms" class="table table-borderless">
                    <thead>
                      <tr>
                        <th><?php echo _("Name"); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "./modals/modal_functions.php"; ?>
<!--   Core JS Files   -->
<script src="./assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="./assets/js/plugins/smooth-scrollbar.min.js"></script>
<!--  Plugin for the DateTimePicker https://xdsoft.net/jqplugins/datetimepicker/ -->
<link rel="stylesheet" type="text/css" href="./assets/css/jquery.datetimepicker.css" />
<script src="./assets/js/plugins/jquery.datetimepicker.full.js"></script>
<!-- jquery-validation -->
<script src="./assets/js/plugins/jquery-validation/jquery.validate.min.js"></script>
<!-- multistep-form -->
<script src="./assets/js/plugins/multistep-form.js"></script>

<!-- Kanban scripts -->
<!--<script src="./assets/js/plugins/dragula/dragula.min.js"></script>
<script src="./assets/js/plugins/jkanban/jkanban.js"></script>-->
<script>
  var win = navigator.platform.indexOf('Win') > -1;
  if (win && document.querySelector('#sidenav-scrollbar')) {
    var options = {
      damping: '0.5'
    }
    Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
  }
</script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="./assets/js/material-dashboard.js?v=3.0.4"></script>
</body>
</html>