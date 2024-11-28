<?php include("./header.php") ?>
<!-- page content -->
<div class="content">
  <div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
      <div class="card">
        <div class="card-header"><i class="fas fa-link fa-lg"></i> <?php echo _("Favorites"); ?>
        </div>
        <div class="card-body">
          <script>
            $(document).ready(function() {
              var table = $('#myFavorites').DataTable({
                dom: "Bfrtip",
                bFilter: true,
                paging: true,
                info: false,
                pagingType: "numbers",
                processing: true,
                deferRender: true,
                pageLength: 15,
                orderCellsTop: true,
                fixedHeader: false,
                autoWidth: false,
                aaSorting: [],
                responsive: true,
                bSort: true,
                ordering: true,
                bLengthChange: false,
                buttons: [],
                displayLength: 15,
                searching: true,
                language: {
                  url: "./assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json",
                },
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

              $('#myFavorites tbody').on('click', 'button', function() {
                var data = table.row($(this).parents('tr')).data();
                favoriteid = data[0];

                $.ajax({
                  type: 'POST',
                  url: './getdata.php',
                  data: 'deleteFavoriteEntry=1&favoriteid=' + favoriteid,
                  beforeSend: function() {
                    $('.button').attr("disabled", "disabled");
                  },
                  success: function(data) {
                    $('.button').removeAttr("disabled");
                    window.location.href = "favorites.php";
                  }
                });
              });
            });
          </script>

          <table id="myFavorites" class="table table-responsive table-borderless table-hover" cellspacing="0">
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
            $sql = "SELECT favorites.ID, favorites.Name, modules.TypeIcon, favorites.URL, favorites.ElementName 
              FROM favorites
              LEFT JOIN modules On modules.ID = favorites.RelatedModuleID
              WHERE RelatedUserID ='" . $_SESSION["id"] . "'";
            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            ?>
            <tbody>
              <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tr>
                  <td><?php echo $row['ID']; ?></td>
                  <td></td>
                  <td><?php echo "<i class=\"fa " . $row['TypeIconName'] . " fa-lg\">"; ?></td>
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

<!-- /page content -->

<?php include("./footer.php") ?>