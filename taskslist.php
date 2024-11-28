<?php include("./header.php"); ?>

<div class="right_col" role="main">
    <div>
        <div class="">
            <div class="page-title">
                <div class="title_left">
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h4><i class="fa fa-history"></i> Task history</h4>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">

                <?php

                $redirectpage = "administration_users.php";

                ?>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <script>
                        $(document).ready(function() {
                            var table = $('#todolist').DataTable({
                                paging: true,
                                dom: 'Bfrtip',
                                buttons: ['excel']
                            });

                            $('a.toggle-vis').on('click', function(e) {
                                e.preventDefault();

                                // Get the column API object
                                var column = table.column($(this).attr('data-column'));

                                // Toggle the visibility
                                column.visible(!column.visible());
                            });

                            $(document).on('shown.bs.tab', 'a[data-toggle="tab_content2"]', function(e) {
                                $.fn.dataTable.tables({
                                    visible: true,
                                    api: true
                                }).columns.adjust();
                            });

                        });
                    </script>


                    <table id="todolist" class="table table-responsive table-borderless table-hover" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Note</th>
                                <th>DateAdded</th>
                                <th>Deadline</th>
                                <th>Name</th>
                                <th>RelatedTicketID</th>
                                <th>DateSolved</th>
                            </tr>
                        </thead>
                        <?php
                        $UserID = $_SESSION["id"];
                        $sql = "SELECT taskslist.ID AS ID, Subject, Note, DateAdded, Deadline, CONCAT(users.FirstName,' ', users.LastName) AS UsersName, RelatedElementID, DateSolved
                FROM taskslist INNER JOIN users ON taskslist.RelatedUserID = users.id
                WHERE taskslist.RelatedUserID = $UserID AND taskslist.Status=4";

                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                        ?>
                        <tbody>
                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                <tr>
                                    <td><?php echo $row['ID']; ?> </td>
                                    <td><?php echo $row['Subject']; ?></td>
                                    <td><?php echo $row['Note']; ?></td>
                                    <td><?php echo $row['DateAdded']; ?></td>
                                    <td><?php echo $row['Deadline']; ?></td>
                                    <td><?php echo $row['UsersName']; ?></td>
                                    <td><?php echo $row['RelatedElementID']; ?></td>
                                    <td><?php echo $row['DateSolved']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>


</div>
</div>
<div class="clearfix"></div>
<!-- End unanswered ticket list -->

<!-- /page content -->
<?php include("./footer.php"); ?>