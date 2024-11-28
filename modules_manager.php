<?php include("./header.php"); ?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="card-header card-header-icon">
                        <div class="card-icon">
                            <i class="fa fa-gears fa-2x"></i>
                        </div>
                        <h4 class="card-title"><?php echo _("Modules"); ?>
                            <div class="dropdown float-end">
                                <button class="btn btn-sm btn-info dropdown-toggle" type="button" data-toggle="dropdown">Menu
                                    <span class="caret"></span></button>
                                <ul class="dropdown-menu">
                                    <li><a onclick="runmodalcreatenew()" href="javascript:void(0);">Create</a></li>
                                </ul>
                            </div>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">
                            <!--        Here you can write extra buttons/actions for the toolbar              -->
                        </div>
                        <?php
                        if (in_array("100000", $group_array)) {
                        } else {
                            $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	                        notgranted($CurrentPage);
                        }
                        ?>

                        <?php $redirectpage = "modules_manager.php"; ?>
                        <?php
                        $UserID = $_SESSION["id"];
                        if (isset($_POST['submit_new'])) {
                            $ModuleName = $_POST["ID"];
                            $LicenseKey = $_POST["LicenseKey"];
                            $ModuleActive = $_POST["ModuleActive"];
                            $Description = $_POST["Description"];

                            createNewProblemEntry($UserID, $ModuleName, $LicenseKey, $ModuleActive, $Description);

                            $redirectpagego = "<meta http-equiv='refresh' content='1';url=" . $redirectpage . "><p><b><div class='alert alert-success'><strong>Module created</strong></div></b></p>";
                            echo $redirectpagego;
                        }

                        if (isset($_POST['submit_changes'])) {
                            $ModuleId = $_POST["editID"];
                            $ModuleName = $_POST["editName"];
                            $LicenseKey = $_POST["editLicenseKey"];
                            $ModuleActive = $_POST["editModuleActive"];
                            $Description = $_POST["editDescription"];
                            $HelpText = $_POST["editHelpText"];

                            changeModuleEntry($ModuleId, $ModuleName, $LicenseKey, $ModuleActive, $Description, $HelpText);

                            $redirectpagego = "<meta http-equiv='refresh' content='1';url=" . $redirectpage . "><p><b><div class='alert alert-success'><strong>Module changes saved</strong></div></b></p>";
                            echo $redirectpagego;
                        }

                        if (isset($_POST['submit_remove'])) {
                            $ModuleId = $_POST["ID"];

                            deletePasswordEntry($PasswordID);

                            $redirectpagego = "<meta http-equiv='refresh' content='1';url=" . $redirectpage . "><p><b><div class='alert alert-success'><strong>Password entry deleted</strong></div></b></p>";
                            echo $redirectpagego;
                        }
                        ?>

                        <script>
                            $(document).ready(function() {

                                var table = $('#tablemodules').DataTable({
                                    paging: true,
                                    dom: 'Bfrtip',
                                    pageLength: 20,
                                    language: {
                                        searchPlaceholder: "Search",
                                        search: "",
                                    },
                                    buttons: ['excel'],
                                    searching: true,
                                    info: true,
                                    "columnDefs": [{
                                        "targets": 1,
                                        "data": null,
                                        "defaultContent": "<button class='btn btn-sm btn-success'><i class='fa fa-pencil-alt fa-2x'></i></button>"
                                    }],
                                });

                                $('#tablemodules tbody').on('click', 'button', function() {
                                    var data = table.row($(this).parents('tr')).data();
                                    moduleid = data[0];
                                    var url = './getdata.php?editModulesModal=' + moduleid
                                    $("#editModuleModal").modal('show')
                                    $.ajax({
                                        url: url,
                                        data: {
                                            data: moduleid
                                        },
                                        type: 'POST',
                                        success: function(data) {
                                            var obj = JSON.parse(data);
                                            for (var i = 0; i < obj.length; i++) {
                                                document.getElementById('editID').value = obj[i].ID;
                                                document.getElementById('editName').value = obj[i].ModuleName;
                                                document.getElementById('editLicenseKey').value = obj[i].LicenseKey;
                                                document.getElementById('editModuleActive').value = obj[i].ModuleActive;
                                                document.getElementById('editDescription').value = obj[i].Description;
                                                document.getElementById('editHelpText').value = obj[i].HelpText;
                                            }
                                        }
                                    });
                                });
                            });
                        </script>
                        <script>
                            function runmodalcreatenew() {
                                $("#createnewmoduleModal").modal('show');
                            };
                        </script>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

                            <table id="tablemodules" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th></th>
                                        <th>Name</th>
                                        <th>LicenseKey</th>
                                        <th>ModuleActive</th>
                                        <th>Description</th>
                                        <th>HelpText</th>
                                    </tr>
                                </thead>
                                <?php

                                $sql = "SELECT ID, ModuleName, LicenseKey, ModuleActive, Description, HelpText FROM modules";

                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                ?>
                                <tbody>
                                    <?php while ($row = mysqli_fetch_array($result)) { ?>
                                        <tr>

                                            <td><?php echo $row['ID']; ?> </td>
                                            <td></td>
                                            <td><?php echo $row['ModuleName']; ?></td>
                                            <td><?php echo $row['LicenseKey']; ?></td>
                                            <td><?php echo $row['ModuleActive']; ?></td>
                                            <td><?php echo $row['Description']; ?></td>
                                            <td><?php echo $row['HelpText']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <!-- Start no responsible assigned to ticket -->
            <?php include("./modules_createnew.php"); ?>
            <!-- End no team assigned to ticket -->
        </div>
    </div>
</div>

<!-- End unanswered ticket list -->

<!-- /page content -->
<?php include("./footer.php"); ?>