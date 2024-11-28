<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100002", $group_array) || in_array("100003", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>

<?php

$ITSMTypeID = "1";
if (!empty($_GET['id'])) {
    $ITSMTypeID = $_GET['id'];
}

$sql = "SELECT itsm_modules.ID, itsm_modules.Name
		FROM itsm_modules
		WHERE itsm_modules.ID = $ITSMTypeID";
$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
    $ITSMName = $row["Name"];
}

$ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
?>

<script>
    $(document).ready(function() {
        <?php initiateStandardSearchTable("TableMyTickets"); ?>
        <?php initiateStandardSearchTable("TeamTickets"); ?>
        <?php initiateStandardSearchTable("TableOverdueTickets"); ?>
        <?php initiateStandardSearchTable("TableSoonOverdueTickets"); ?>
    });
</script>

<div class="row">
    <div class="col-md-7 col-sm-7 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header">
                    <div class="float-end">
                        <ul class="navbar-nav justify-content-end">
                            <li class="nav-item dropdown pe-2">
                                <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    &nbsp;&nbsp;<i class="fa fa-circle-chevron-down"></i>&nbsp;&nbsp;
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                                    <?php
                                    $BSArray = getITSMsForOverview();

                                    foreach ($BSArray as $key => $value) {
                                        $Name = $value["Name"];
                                        $MenuPage = $value["MenuPage"];
                                        echo "<li class=\"mb-2\">
												<a class=\"dropdown-item border-radius-md\" onclick=\"window.location.href='$MenuPage" . $value['ID'] . "'\">
													<div class=\"d-flex align-items-center py-1\">
														<div class=\"ms-2\">
															<h6 class=\"text-sm font-weight-normal my-auto\">
																$Name
															</h6>
														</div>
													</div>
												</a>
											</li>";
                                    }
                                    ?>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <?php
                    echo $NavMenu01 = navMenu01($ITSMTypeID);
                    ?>
                    <div class="float-end">
                        <ul class="navbar-nav justify-content-end" title="<?php echo _("Dispatch"); ?>">
                            <li class="nav-item dropdown pe-2">
                                <a href="./itsm_dispatch.php?id=<?php echo $ITSMTypeID ?>" class="nav-link text-body p-0 position-relative" aria-expanded="false">
                                    &nbsp;&nbsp;<i class="fa fa-route fas-dark"></i>&nbsp;&nbsp;
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="float-end">
                        <ul class="navbar-nav justify-content-end">
                            <li class="nav-item dropdown pe-2">
                                <a href="javascript:void(0);" onclick="runModalCreateITSM('<?php echo $ITSMTypeID ?>','<?php echo $UserLanguageCode ?>');" class="nav-link text-body p-0 position-relative" aria-expanded="false">
                                    &nbsp;&nbsp;<i class="far far-dark fa-plus-square"></i>&nbsp;&nbsp;
                                </a>
                            </li>
                        </ul>
                    </div>
                    <i class="<?php echo $ModuleIconName ?>"></i> <a href="javascript:location.reload(true);"><?php echo _("New"); ?></a>
                </div>
                <div class="card-body">
                    <?php include("./itsm_dispatch_que_incl.php"); ?>
                </div>
            </div>
        </div>
        <br>
        <div class="card-group">
            <div class="card">
                <div class="card-header">
                    <?php $ModuleIconName = getITSMModuleTypeIcon($ITSMTypeID); ?>
                    <i class="<?php echo $ModuleIconName ?>"></i> <a href="javascript:location.reload(true);"><?php echo _("not assigned team"); ?></a>
                </div>
                <div class="card-body">
                    <?php include("./itsm_not_assigned_team_incl.php"); ?>
                </div>
            </div>
        </div>
        <br>
        <div class="card-group">
            <div class="card">
                <div class="card-header">
                    <?php $ModuleIconName = getITSMModuleTypeIcon($ITSMTypeID); ?>
                    <i class="<?php echo $ModuleIconName ?>"></i> <a href="javascript:location.reload(true);"><?php echo _("not assigned responsible"); ?></a>
                </div>
                <div class="card-body">
                    <?php include("./itsm_not_assigned_responsible_incl.php"); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header">
                    <?php $ModuleIconName = getITSMModuleTypeIcon($ITSMTypeID); ?>
                    <i class="<?php echo $ModuleIconName ?>"></i> <a href="javascript:location.reload(true);"><?php echo _("Overdue"); ?></a>
                </div>
                <ul class="nav nav-tabs nav-fill mb-3" role="tablist" id="activetablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#SoonOverdueTickets" role="tablist" title="<?php echo _("Now within 15% time left") ?>">
                            <?php echo _('Soon Overdue') . " " . "(15%)"; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#OverdueTickets" role="tablist">
                            <?php echo _('Overdue'); ?>
                        </a>
                    </li>
                </ul>
                <div class="card-body">
                    <div class="tab-content tab-space">
                        <div class="tab-pane active" id="SoonOverdueTickets">
                        </div>
                        <div class="tab-pane" id="OverdueTickets">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- /page content -->
    <?php include("./footer.php"); ?>