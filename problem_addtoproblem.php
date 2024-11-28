<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100012", $group_array) || in_array("100013", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<?php
//Set important variables to work with
$TicketID = $_GET["elementid"];
$NewProblemID = "";
$UserID = $_SESSION["id"];
$UserFullName = $_SESSION["userfullname"];
?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="card">
                    <div class="card-header card-header-problems card-header-icon">
                        <div class="card-icon">
                            <i class='fa fa-bug fa-2x'></i>
                        </div>
                        <h4 class="card-title"><?php echo _("Add") . " " . _("incident"); ?> <?php echo $TicketID; ?> <?php echo " " . _("to existing problem"); ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">
                            <!--        Here you can write extra buttons/actions for the toolbar              -->
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <script>
                                $(document).ready(function() {
                                    var table = $('#tableproblems').DataTable({
                                        paging: true,
                                        pagingType: 'numbers',
                                        processing: true,
                                        dom: 'Bfrtip',
                                        pageLength: 20,
                                        language: {
                                            info: "<?php echo _("Showing"); ?> _START_ <?php echo _("to"); ?> _END_ <?php echo _("of"); ?> _TOTAL_ <?php echo _("records"); ?>",
                                            searchPlaceholder: "<?php echo _("Search"); ?>",
                                            search: "",
                                        },
                                        "columnDefs": [{
                                            "targets": 1,
                                            "data": null,
                                            "defaultContent": "<button class='btn btn-sm btn-success'>Add</button>"
                                        }],
                                        buttons: ['excel']

                                    });

                                    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {

                                        $($.fn.dataTable.tables(true)).css('width', '100%');
                                        $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
                                    });

                                    $('a.toggle-vis').on('click', function(e) {
                                        e.preventDefault();

                                        // Get the column API object
                                        var column = table.column($(this).attr('data-column'));

                                        // Toggle the visibility
                                        column.visible(!column.visible());
                                    });

                                    $('#tableproblems tbody').on('click', 'button', function() {
                                        var data = table.row($(this).parents('tr')).data();
                                        problemid = data[0];
                                        ticketid = <?php echo $TicketID; ?>;
                                        var url = './getdata.php?addtickettoexistingproblem=' + problemid + '&ticketid=' + ticketid;
                                        $.ajax({
                                            url: url,
                                            data: {
                                                data: ticketid,
                                                problemid
                                            },
                                            type: 'POST',
                                            success: OnSuccessCall,
                                            error: OnErrorCall
                                        });
                                    });
                                });
                            </script>
                            <script>
                                function OnSuccessCall(response) {
                                    alert("Problem " + problemid + " added Successfully");
                                }

                                function OnErrorCall(response, status, error) {
                                    var err = eval("(" + response.responseText + ")"); //alert(response.status + " " + response.statusText);
                                    alert(err.Message);
                                }
                            </script>
                            <table id="tableproblems" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th></th>
                                        <th></th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th>Responsible</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <?php

                                $sql = "SELECT problems.ID AS ID, problems.Name, problems.Description, problems.RelatedRespID, problems.Created_Date,
            ticketusers.FullName AS FullName, problemstatuscodes.StatusName
            FROM problems
            LEFT JOIN ticketusers ON problems.RelatedRespID = ticketusers.ID
            LEFT JOIN problemstatuscodes ON problems.RelatedStatusID = problemstatuscodes.ID
            WHERE problems.RelatedStatusID !=6";

                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                ?>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_array($result)) { ?>
                                        <tr>

                                            <td><?php echo $row['ID']; ?> </td>
                                            <td></td>
                                            <td><a href="javascript:poptastic('problems_view.php?elementid=<?php echo $row['ID']; ?>');" class='btn btn-sm btn-info'>Preview</button></a></td>
                                            <td><?php echo $row['Name']; ?></td>
                                            <td><?php echo $row['Description']; ?></td>
                                            <td><?php echo $row['StatusName']; ?></td>
                                            <td><?php echo $row['FullName']; ?></td>
                                            <?php $myFormatForView = convertToDanishTimeFormat($row['Created_Date']); ?>
                                            <td><?php echo $myFormatForView; ?></td>
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
</div>
<!-- /page content -->
<?php include("./footer.php"); ?>