<?php 
	include("./header.php");

    if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
	} else {
		$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	    notgranted($CurrentPage);
	}
?>

<?php
	$tableid = $_GET['tableid'];

	$sql = "SELECT forms_tables.ID, forms_tables.TableName
			FROM forms_tables
			WHERE forms_tables.ID = $tableid";

	$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

	while ($row = mysqli_fetch_array($result)) {
		$TableName = $row['TableName'];
	}
?>

<script>
function createtablefield() {
    var fieldlabel = document.getElementById('FieldLabel').value;
    var fieldname = document.getElementById('FieldName').value;
    var fieldtype = document.getElementById('FieldType').value;
    var fieldlength = document.getElementById('FieldLength').value;
    var fieldwidth = document.getElementById('FieldWidth').value;
    var tableid = <?php echo $tableid?>;

    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
    } else {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }

    xmlhttp.open("GET", "getdata.php?createFormsTableField=" + fieldlabel + "&fieldname=" + fieldname + "&fieldtype=" +
        fieldtype + "&tableid=" + tableid + "&fieldlength=" + fieldlength + "&fieldwidth=" + fieldwidth, true);
    xmlhttp.send();
    message = 'Form table field ' + fieldname + ' created';
    console.log(fieldname);
    pnotify(message, 'success');
    url = "administration_formsbuilder_edit_table.php?tableid=" + tableid;
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
                    <div class="card-header card-header-administration"><i class="fas fa-file-invoice"></i>
                        <?php echo _("Create new field for")." ".$TableName; ?>
                    </div>
                    <div class="card-body">
                        <div class="toolbar">
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <label for="FieldLabel"><?php echo _("Field Label") ?></label>
                                <input type="text" class="form-control" id="FieldLabel" name="FieldLabel">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <label for="FieldName"><?php echo _("Field Name") ?></label>
                                <input type="text" class="form-control" id="FieldName" name="FieldName">
                            </div>
                        </div>
                        <label for="FieldType"><?php echo _('Field Type'); ?></label>
                        <select id="FieldType" name="FieldType" class="custom-select">
                            <?php
							$sql = "SELECT ID, TypeName
									FROM forms_tables_fieldslist_types
									ORDER BY TypeName ASC;";
							$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
							while ($row = mysqli_fetch_array($result)) {
								echo "<option value=" . $row['ID'] . ">" . $row['TypeName'] . "</option>";
							}
							?>
                        </select>
                        <label for="FieldWidth"><?php echo _('Field Width'); ?></label>
                        <select id="FieldWidth" name="FieldWidth" class="custom-select">
                            <?php
							$sql = "SELECT WidthName, Fieldwidth
									FROM forms_tables_fieldswidth
									ORDER BY ID ASC";
							$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
							while ($row = mysqli_fetch_array($result)) {
								$Fieldwidth = $row['Fieldwidth'];
								$WidthName = $row['WidthName'];
								echo "<option value='$WidthName'>$WidthName</option>";
							}
							?>
                        </select>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <label for="FieldLength"><?php echo _("Field Length") ?></label>
                                <input type="text" class="form-control" id="FieldLength" name="FieldLength">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <button id="createfield" name="createfield" class="btn btn-sm btn-success float-end"
                                    onclick="createtablefield()"><?php echo _('Create'); ?></button>
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