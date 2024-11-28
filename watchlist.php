<?php include("./header.php") ?>
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="card">
          <div class="card-header card-header-<?php echo $CardheadersColor ?>"><i class="fas fa-eye fa-lg"></i> <?php echo _("Watchlist"); ?>
          </div>
          <div class="card-body">
            <script>
              $(document).ready(function() {
                var table = $('#myWatchlist').DataTable({
                  paging: true,
                  pagingType: 'numbers',
                  processing: true,
                  bInfo: false,
                  dom: 'Bfrtip',
                  pageLength: 30,
                  orderCellsTop: true,
                  fixedHeader: true,
                  language: {
                    info: "<?php echo _("Showing"); ?> _START_ <?php echo _("to"); ?> _END_ <?php echo _("of"); ?> _TOTAL_ <?php echo _("records"); ?>",
                    searchPlaceholder: "<?php echo _("Search"); ?>",
                    search: "",
                  },
                  buttons: [],
                  searching: true,
                  className: 'dt-body-left',
                  info: true,
                  autoWidth: false,
                  columnDefs: [{
                      "targets": [0],
                      "visible": false
                    },
                    {
                      "targets": [1],
                      "data": null,
                      "defaultContent": "<button class='btn btn-sm btn-danger'><?php echo _("Delete"); ?></button>"
                    }
                  ]
                });

                $('#myWatchlist tbody').on('click', 'button', function() {
                  var data = table.row($(this).parents('tr')).data();
                  watchlistid = data[0];

                  $.ajax({
                    type: 'GET',
                    url: './getdata.php',
                    data: 'deleteWatchlistEntry=1&watchlistid=' + watchlistid,
                    beforeSend: function() {
                      $('.button').attr("disabled", "disabled");
                    },
                    success: function(data) {
                      $('.button').removeAttr("disabled");
                      window.location.href = "watchlist.php";
                    }
                  });
                });
              });
            </script>

            <table id="myWatchlist" class="table dt-responsive table-bordered order-column" cellspacing="0">
              <thead>
                <tr>
                  <th><?php echo _("ID"); ?></th>
                  <th></th>
                  <th></th>
                  <th><?php echo _("Type"); ?></th>
                  <th><?php echo _("Name"); ?></th>
                </tr>
              </thead>
              <?php
              $sql = "SELECT watchlist.ID, watchlist.Name, modules.TypeIcon, watchlist.URL, watchlist.ElementName 
              FROM watchlist
              LEFT JOIN modules On modules.ID = watchlist.RelatedModuleID
              WHERE RelatedUserID ='" . $_SESSION["id"] . "'";
              $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
              ?>
              <tbody>
                <?php while ($row = mysqli_fetch_array($result)) { ?>
                  <tr>
                    <td><?php echo $row['ID']; ?></td>
                    <td></td>
                    <td><?php echo "<i class=\"fa " . $row['TypeIcon'] . " fa-lg\">"; ?></td>
                    <td><?php echo _($row['ElementName']); ?></td>
                    <td><?php echo "<p><a href='" . $row['URL'] . "'><b>" . $row['Name'] . "</b></a></p>"; ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- /page content -->

<?php include("./footer.php") ?>