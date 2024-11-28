<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100014", $group_array) || in_array("100015", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}

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

$ITSMTypeName = $functions->getITSMTypeName($ITSMTypeID);
$ITSMTableName = $functions->getITSMTableName($ITSMTypeID);

?>
<script>
    const LanguageCode = "<?php echo $UserLanguageCode ?>";
    var FilterValue = "1";
    var ITSMTypeID = "<?php echo $ITSMTypeID ?>";
    var FirstITSMID = "1";

    columnsMy = [];
    $.ajax({
        type: "GET",
        url: "./getdata.php?getITSMDatarowsQuickView=1&FilterValue=" + FilterValue + "&ITSMTypeID=" + ITSMTypeID,
        dataType: "json",
        success: function(dataMy) {
            var rowData = dataMy[0]; // Get first row data to build columns from.
            if (rowData) {
                FirstITSMID = rowData["ID"];
                // Itereate rowData's object keys to build column definition.
                Object.keys(rowData).forEach(function(key, index) {
                    var newkey = key.replace("ImAPlaceHolder", " ");
                    columnsMy.push({
                        data: newkey,
                        title: newkey,
                    });
                });

                var itsmEntries = $("#itsmEntries").DataTable({
                    dom: "Brtip",
                    paging: true,
                    pagingType: "numbers",
                    info: false,
                    buttons: [],
                    processing: true,
                    ordering: true,
                    pageLength: 20,
                    displayLength: 20,
                    bSort: true,
                    searching: true, // Disable the default search field
                    language: {
                        url: "./assets/js/DataTables/Languages/" + LanguageCode + ".json",
                    },
                    columnDefs: [{
                            width: "20%",
                            targets: 0
                        },
                        {
                            targets: "_all",
                            width: "auto"
                        },
                    ],
                    data: dataMy,
                    columns: columnsMy,
                });

                // Bind the custom search field to the DataTables search event
                $("#itsmEntries_filter input").on("keyup", function() {
                    itsmEntries.search(this.value).draw();
                });

                itsmEntries.each(function() {
                    var title = $(this).text();
                    $(this).html(
                        '<input type="search" class="form-control form-control-sm" placeholder="' +
                        title +
                        '"/>'
                    );
                });

                itsmEntries.columns().every(function() {
                    var that = this;

                    $("input", this.header()).on("keyup change", function() {
                        if (that.search() !== this.value) {
                            that.search(this.value).draw();
                        }
                    });
                });
            } else {
                document.getElementById("ITSMQWFieldsDiv").hidden = true;
                document.getElementById("ITSMQWHeaderHeadline").innerHTML = "";
                document.getElementById("itsmqwid").innerHTML = "";
                document.getElementById("itsmqwtypeid").innerHTML = "";
            }
        },
        complete: function(dataMy) {
            if (FirstITSMID) {
                viewITSM(FirstITSMID, ITSMTypeID, 1, "quickview");
            }
        },
        error: function(err) {
            deactivateSpinner();
            console.log(err);
        },
    });
</script>

<div class="row">
    <div class="card-group">
        <div class="col-md-3 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-header">
                    <?php $ModuleIconName = getITSMModuleTypeIcon($ITSMTypeID); ?>
                    <i class="<?php echo $ModuleIconName ?>"></i> <a href="javascript:location.reload(true);"><?php echo _($ITSMName); ?></a>
                    <div class="float-end">
                        <?php
                        if (in_array("100001", $group_array) || in_array("100015", $group_array)) {
                            echo $NavMenu01 = navMenu01($ITSMTypeID);
                        } ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 col-sm-8 col-xs-8">
                            <div class="col-sm-12">
                                <div id="itsmEntries_filter" class="dataTables_filter">
                                    <label>
                                        <input type="search" class="form-control form-control-sm" placeholder="<?php echo _("Search") ?>" aria-controls="itsmEntries">
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-4">
                            <div class="input-group input-group-static mb-4">
                                <select class="form-control" id="FilterITSM" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Filter") ?>" onchange="loadITSMQWEntries('<?php echo $ITSMTypeID ?>',this.value, '<?php echo $UserLanguageCode ?>')">
                                    <option value="1"><?php echo _("My") ?></option>
                                    <option value="2"><?php echo _("My teams") ?></option>
                                    <option value="3"><?php echo _("Me participating") ?></option>
                                    <option value="4"><?php echo _("All open") ?></option>
                                    <option value="5"><?php echo _("All closed") ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <table id="itsmEntries" class="table-responsive table-striped" width="100%">
                        <thead>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-9 col-sm-12 col-xs-12">
            <div class="card">
                <div class="card-header">
                    <div id="ITSMQWHeaderHeadline"></div>
                    <div id="itsmqwid" hidden></div>
                    <div id="itsmqwtypeid" hidden></div>
                </div>
                <div class="card-body">
                    <div id="ITSMQWFieldsDiv" hidden>
                        <ul class="nav nav-tabs nav-fill mb-3">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#ITSMQWDetailsTab" id="LinkITSMQWDetailsTab">
                                    <?php echo _("Details"); ?>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ITSMQWRelations" id="LinkITSMQWRelations">
                                    <?php echo _("Relations"); ?>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ITSMQWFilesTab" id="LinkITSMQWFilesTab">
                                    <?php echo _("Files"); ?>
                                    <span class="badge rounded-pill" id="SumQWFiles"></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ITSMQWWorkFlow" id="LinkITSMQWWorkFlow">
                                    <?php echo _("Workflows"); ?>
                                    <span class="badge rounded-pill" id="SumQWWFT"></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ITSMQWLog" id="LinkITSMQWLog">
                                    <?php echo _("History"); ?>
                                </a>
                            </li>
                        </ul>
                        <div class="card-group">
                            <div class="card">
                                <div class="card-body">
                                    <div class="tab-content">
                                        <div class="tab-pane active" id="ITSMQWDetailsTab">
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseITSMQWParticipants" aria-expanded="false" aria-controls="collapseITSMQWParticipants"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Participants") ?>"></i> <?php echo _("Participants") ?></a>
                                                    <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseITSMQWComments" aria-expanded="false" aria-controls="collapseITSMQWComments"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Communication") ?>"></i> <?php echo _("Comments") ?></a>
                                                    <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseITSMQWSLA" aria-expanded="false" aria-controls="collapseITSMQWSLA"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="tooltip" data-bs-title="<?php echo _("SLA") ?>"></i> <?php echo _("SLA") ?></a>
                                                    <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseITSMQWSolution" aria-expanded="false" aria-controls="collapseITSMQWSLA"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Solution") ?>"></i> <?php echo _("Solution") ?></a>
                                                    <button class="btn btn-sm bg-gradient-secondary dropup dropdown-toggle float-end" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <?php echo _("More") ?>
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                        <li><a class="dropdown-item" href="javascript:addITSMToKanban();"><?php echo _("Add to taskboard") ?></a></li>
                                                        <li><a class="dropdown-item" href="javascript:cloneITSM('quickview');"><?php echo _("Clone") ?></a></li>
                                                        <li><a class="dropdown-item" href="javascript:createITSMFromITSM(1);"><?php echo _("Create incident from this") ?></a></li>
                                                        <li><a class="dropdown-item" href="javascript:createITSMFromITSM(2);"><?php echo _("Create request from this") ?></a></li>
                                                        <li><a class="dropdown-item" href="javascript:createITSMFromITSM(3);"><?php echo _("Create change from this") ?></a></li>
                                                        <li><a class="dropdown-item" href="javascript:createITSMFromITSM(4);"><?php echo _("Create problem from this") ?></a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <div class="accordion width" id="accordionITSMQWMore">
                                                        <div class="card">
                                                            <div id="collapseITSMQWParticipants" class="collapse width" data-bs-parent="#accordionITSMQWMore">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col-md-4 col-sm-4 col-xs-12">
                                                                            <h6><?php echo _("Add") ?></h6>
                                                                            <div class="input-group input-group-static mb-4">
                                                                                <label class="form-label"></label>
                                                                                <select id="QWparticipants" name="QWparticipants" class="form-control">
                                                                                </select>
                                                                            </div>
                                                                            <div id="BtnQWAddParticipant">
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-8 col-sm-8 col-xs-12">
                                                                            <div id="ITSMQWParticipants"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card">
                                                            <div id="collapseITSMQWComments" class="collapse width" data-bs-parent="#accordionITSMQWMore">
                                                                <div class="card-body">
                                                                    <div class="input-group input-group-static mb-4">
                                                                        <label for="ITSMQWComment" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Create comment to user"); ?>"><?php echo _("Comment"); ?></label>&ensp;<a href="javascript:toggleTrumbowygEditor('ITSMQWComment');"><i class="fa-solid fa-pen fa-sm" title="Click to edit"></i></a>
                                                                        <div class="resizable_textarea form-control" id="ITSMQWComment" name="ITSMQWComment" data-bs-toggle="tooltip" data-bs-title="Double click to edit" rows="2" autocomplete="off" ondblclick="toggleTrumbowygEditor('ITSMQWComment');">
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div id="BtnQWAddComment"></div>
                                                                        <div class="form-check form-switch float-end" data-bs-toggle="tooltip" data-bs-title="<?php echo _("If internal, reporter (customer) does not get email notified and cannot see the comment") ?>">
                                                                            <label class="form-check-label float-end" for="QWInternalComment"><?php echo _("Internal"); ?></label>
                                                                            <input class="form-check-input float-end" type="checkbox" id="QWInternalComment">
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <table id="TableITSMQWComments" class="table table-borderless table-responsive" cellspacing="0"></table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card">
                                                            <div id="collapseITSMQWSLA" class="collapse width" data-bs-parent="#accordionITSMQWMore">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div id="ITSMQWSLA"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card">
                                                            <div id="collapseITSMQWSolution" class="collapse width" data-bs-parent="#accordionITSMQWMore">
                                                                <div class="card-body">
                                                                    <div class="row">
                                                                        <div class="col-lg-12 col-sm-12 col-xs-12">
                                                                            <div id="ITSMQWSolutionBtn"></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-lg-12 col-sm-12 col-xs-12">
                                                                            <button class="btn btn-sm btn-success float-end" onclick="resolveITSM('quickview');"><?php echo _("Resolve"); ?></button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>

                                            <form id="RequestQWDefinitionView">
                                            </form>
                                            <br>
                                            <form id="ITSMQWViewForm">
                                                <div class="row" id="ITSMQWDefinition1">
                                                </div>

                                                <div class="row" id="ITSMQWDefinition2">
                                                </div>
                                            </form>
                                            <div class="row">
                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                    <button type="button" class="btn btn-sm btn-success float-end" id="btn_itsm_update" name="btn_itsm_update" onclick="updateITSM('quickview')"><?php echo _("Update"); ?></button>
                                                </div>
                                                <div class="col-md-12 col-sm-12 col-xs-12" id="ITSMQWUpdateButton">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="ITSMQWRelations">
                                            <?php
                                            if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
                                                echo "<p><a href=\"javascript:void(0);\"><i class=\"fa-solid fa-circle-chevron-down float-right\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Admin") . "\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseCreate\" aria-expanded=\"false\" aria-controls=\"collapseCreate\"></i></a></p>";
                                            }
                                            ?>

                                            <div class="collapse" id="collapseCreate">
                                                <div class="row">
                                                    <div class="col-md-4 col-sm-4 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="ITSMQWTypes" class="ms-0"><?php echo _("ITSM Types"); ?></label>
                                                            <select class="form-control" id="ITSMQWTypes" name="ITSMQWTypes" onchange="getITSMs(this.value);" autocomplete="on">
                                                                <option></option>
                                                                <?php
                                                                $sql = "SELECT itsm_modules.TableName, itsm_modules.Name
                                                                        FROM itsm_modules 
                                                                        WHERE itsm_modules.Active=1
                                                                        ORDER BY itsm_modules.Name ASC;";
                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                while ($row = mysqli_fetch_array($result)) {
                                                                    $Name = $row['Name'];
                                                                    echo "<option value='" . $row['TableName'] . "'>$Name</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-7 col-sm-7 col-xs-12">
                                                        <div class="input-group input-group-static mb-4" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Relate to other ITSM element"); ?>">
                                                            <label for="ITSMQW" class="ms-0"><?php echo _("ITSM's"); ?></label>
                                                            <input class="form-control" list="ITSMSQW" id="ITSMQW" name="ITSMQW" onfocus="this.value=''" onchange="this.blur();" autocomplete="off">
                                                            <datalist id="ITSMSQW">
                                                            </datalist>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-1 col-sm-1 col-xs-12">
                                                        <div id="ITSMQWCreateRelationButton2">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <label for="ElementQWCIS" class="ms-0"> <?php echo _("CI Types"); ?></label>
                                                            <select class="form-control" id="ElementQWCIS" name="ElementQWCIS" onchange="getCIs(this.value);" autocomplete="on" placeholder="<?php echo _("Select") . "..." ?>">
                                                                <option></option>
                                                                <?php
                                                                $sql = "SELECT cmdb_cis.TableName, cmdb_cis.Name
                                                                        FROM cmdb_cis 
                                                                        WHERE cmdb_cis.Active=1
                                                                        ORDER BY cmdb_cis.Name ASC;";
                                                                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                while ($row = mysqli_fetch_array($result)) {
                                                                    $Name = $row['Name'];
                                                                    $TableName = $row['TableName'];
                                                                    echo "<option value='$TableName'>$Name</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4" data-bs-toggle="tooltip" data-bs-title="<?php echo _("This will relate this to chosen CI"); ?>">
                                                            <label for="CIResultsElement" class="ms-0"><?php echo _("CI's"); ?></label>
                                                            <input class="form-control" list="CIResultsElement" id="CIResultElement" name="CIResultElement" autocomplete="off">
                                                            <datalist id="CIResultsElement">
                                                            </datalist>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1 col-sm-1 col-xs-12">
                                                        <div id="ITSMQWCreateRelationButton1">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class=" row">
                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <h6><?php echo _("ITSM Relations") ?></h6>
                                                    <table id="TableITSMQWRelations" class="table table-borderless table-responsive table-hover" cellspacing="0">
                                                    </table>
                                                </div>

                                                <div class="col-md-6 col-sm-6 col-xs-12">
                                                    <h6><?php echo _("CI Relations") ?></h6>
                                                    <table id="TableITSMQWCIRelations" class="table table-borderless table-responsive table-hover" cellspacing="0">
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="ITSMQWFilesTab">
                                            <div id="ITSMQWFormFileAction"></div>
                                            <table id="TableITSMQWFiles" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                            </table>
                                        </div>

                                        <div class="tab-pane" id="ITSMQWWorkFlow">
                                            <div class="row">
                                                <a href="javascript:void(0);"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="collapse" data-bs-target="#collapseWFAdmin" aria-expanded="false" aria-controls="collapseWFAdmin"></i></a>
                                                <div class="collapse" id="collapseWFAdmin">
                                                    <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                                                        <div class="input-group input-group-static mb-4">
                                                            <input class="form-control" autocomplete="off" list="ITSMQWWorkFlows" id="QWWorkflowInput" name="QWWorkflowInput" class="form-control" placeholder="<?php echo _("Select Workflow") ?>">
                                                            <datalist id="ITSMQWWorkFlows">
                                                            </datalist>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                                        <div id="btnQWCreateWorkFlow"></div>
                                                    </div>
                                                    <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                                        <div id="btnQWRemoveWorkFlow"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <table id="TableITSMQWWorkFlow" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                            </table>
                                        </div>

                                        <div class="tab-pane" id="ITSMQWLog">
                                            <table id="TableITSMQWLog" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
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
    </div>
</div>

<?php include("./footer.php"); ?>