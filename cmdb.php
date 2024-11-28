<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100014", $group_array) || in_array("100015", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}

?>

<link rel="stylesheet" type="text/css" href="./assets/js/timeline3/css/timeline<?php echo $ThemeForTimeline ?>.css">
<script src="./assets/js/timeline3/js/timeline-min.js"></script>

<div class="hidden" id="elementtypeid"></div>
<div class="hidden" id="elementid"></div>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 id="ciheader" class="mb-0"></h5>
                    <div class="d-flex">
                        <div class="me-2">
                            <ul class="navbar-nav">
                                <li class="nav-item dropdown">
                                    <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="collapse" data-bs-target="#collapseViewCIS" aria-expanded="false" aria-controls="collapseViewCIS">
                                        &nbsp;&nbsp;<i class="fa fa-circle-chevron-down" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Assets") ?>"></i>&nbsp;&nbsp;
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="me-2">
                            <ul class='navbar-nav'>
                                <li class='nav-item dropdown'>
                                    <a href='javascript:;' class='nav-link text-body p-0 position-relative' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                                        &nbsp;&nbsp;<i class="fa-solid fa-ellipsis-vertical" title="<?php echo _("Actions") ?>"></i>&nbsp;&nbsp;
                                    </a>
                                    <ul class='dropdown-menu dropdown-menu-end p-2 me-sm-n4' aria-labelledby='dropdownMenuButton'>
                                        <li class='mb-2'>
                                            <a class='dropdown-item border-radius-md' onclick="fetchCMDBDataAndBuildTableArchive('', '', '<?php echo $UserLanguageCode ?>', '0');">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto'>
                                                            <?php echo _("Archive") ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='mb-2'>
                                            <a class='dropdown-item border-radius-md' onclick="fetchCMDBDataAndBuildTableArchive('', '', '<?php echo $UserLanguageCode ?>', '1');">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto'>
                                                            <?php echo _("Table view") ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class='mb-2'>
                                            <a class='dropdown-item border-radius-md' onclick="fetchCMDBTimelineData('', 1);">
                                                <div class='d-flex align-items-center py-1'>
                                                    <div class='ms-2'>
                                                        <h6 class='text-sm font-weight-normal my-auto'>
                                                            <?php echo _("Timeline view") ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <ul class=" navbar-nav">
                                <li class="nav-item dropdown" title="<?php echo @$functions->translate("Create"); ?>">
                                    <a href="javascript:void(0);" onclick="runModalCreateCI();" class="nav-link text-body p-0 position-relative" aria-expanded="false">
                                        &nbsp;&nbsp;<i class="far far-dark fa-plus-square"></i>&nbsp;&nbsp;
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="collapse" id="collapseViewCIS">
                            <div class="row">
                                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                    <div class="card-group">
                                        <div class="card">
                                            <div class="card-header card-header"><i class="fa fa-desktop"></i> <?php echo _("Assets"); ?>
                                            </div>
                                            <div class="card-body">
                                                <script>
                                                    $(document).ready(function() {
                                                        <?php initiateSimpleViewTable("tableCIs", 20, []); ?>
                                                    });
                                                </script>

                                                <table id="tableCIs" class="table-borderless" cellspacing="0">
                                                    <thead style="display: none;">
                                                        <tr>
                                                            <th></th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $BSArray = getCIsForOverview();

                                                        foreach ($BSArray as $key => $value) {
                                                            $Element = $value['ID'];
                                                            $Name = _($value["Name"]);
                                                            $Description = $value["Description"];
                                                            $LastSyncronized = $value["LastSyncronized"];

                                                            echo "<tr class='text-secondary mb-0' title='Synced: $LastSyncronized''>";
                                                            //echo "<td><a href='cmdb_tableview_cis.php?id=" . $value['ID'] . "'>" . $value["Name"] . "</a></td>";
                                                            echo "<td><a href=\"javascript:void(0);\" onclick=\"fetchAndCollapse('$Element');\">" . $value["Name"] . "</a></td>";
                                                            echo "<td>&nbsp;$Description</td>";
                                                            echo "</tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="citablerow">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <table id="cistable" class="table table-borderless table-responsive table-hover" cellspacing="0">
                            </table>
                        </div>
                    </div>
                    <div class="row" id="citimelinerow" hidden>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div id="timeline-embed" style="width: 100%; height: 600px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        let ElementID = "";
        let CITypeID = "";
        let UserLanguageCode = "<?php echo $UserLanguageCode ?>";
        let Active = "1";

        if (!CITypeID) {
            CITypeID = localStorage.getItem("standardCIToOpen");
            if (CITypeID == null) {
                CITypeID = 1;
            }
        }
        fetchCMDBDataAndBuildTable(ElementID, CITypeID, UserLanguageCode, Active);
    });
</script>
<?php include("./footer.php"); ?>