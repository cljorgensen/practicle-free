<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<?php
	$formid = $_GET['elementid'];

	$sql = "SELECT forms.ID, forms.FormsName
			FROM forms
			WHERE forms.ID = $formid";

	$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

	while ($row = mysqli_fetch_array($result)) {
		$FormsName = $row['FormsName'];
	}
?>

<script>
function createformstable() {
    var TableName = "forms_table_" + document.getElementById("TableName").value;
    var formid = '<?php echo $formid?>';

    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
    } else {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.open("GET", "getdata.php?createFormsTable=" + TableName + "&formid=" + formid, true);
    xmlhttp.send();
    message = 'Form table ' + TableName + ' created';
    console.log(TableName);
    pnotify(message, 'success');
    url = "administration_formsbuilder_edit.php?formid=" + formid;
    setTimeout(function() {
        window.location.href = url;
    }, 1000);
}
</script>
<!-- page content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="card-header card-header-icon">
                        <div class="card-icon">
                            <i class="fas fa-file-invoice fa-2x"></i>
                        </div>
                        <h4 class="card-title"><?php echo _("Create new table for")." ".$FormsName; ?>

                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <label for="TableName"><?php echo _("Table Name") ?></label>
                                <input type="text" class="form-control" id="TableName" name="TableName">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <button id="createtable" name="createtable" class="btn btn-sm btn-success float-end"
                                    onclick="createformstable()"><?php echo _('Create'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page content -->
<?php include("./footer.php"); ?>