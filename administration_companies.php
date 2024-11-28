<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100025", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header card-header"><i class="fa fa-building"></i> <a href="javascript:location.reload(true);"><?php echo _("Companies"); ?></a>
                    <div class="float-end">
                        <ul class="navbar-nav justify-content-end">
                            <li class="nav-item dropdown pe-2">
                                <a href="javascript:void(0);" onclick="runModalCreateUnit('Company');" class="nav-link text-body p-0 position-relative" aria-expanded="false" title="<?php echo _("Create"); ?>">
                                    &nbsp;&nbsp;<i class="far far-dark fa-plus-square"></i>&nbsp;&nbsp;
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <table id="TableCompanies" class="table table-responsive table-borderless table-hover" cellspacing="0">
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("./footer.php"); ?>