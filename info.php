<?php
include "./header.php";

if (in_array("100001", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-7 col-sm-7 col-xs-12">
                <div class="card">
                    <div class="card-header card-header-icon">
                        <div class="card-icon">
                            <i class="fa fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        phpinfo();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include "./footer.php";
        ?>