<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>

<?php
$formtableid = $_GET['tableid'];
$formtablename = getTableNameFromID($formtableid);
$formtablefieldid = $_GET['fieldid'];

$sql = "SELECT ID, FieldName, FieldType 
		FROM forms_tables_fieldslist 
		WHERE RelatedTableID = $formtableid";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

while ($row = mysqli_fetch_array($result)) {
	$FieldID = $row['ID'];
	$FieldName = $row['FieldName'];
	$FieldType = $row['FieldType'];
}

?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-sm-8 col-xs-12">
                <div class="card">
                    <div class="card-header card-header-administration"><i class="fas fa-file-invoice"></i>
                        <?php echo $formtablename; ?>
                        <div class="float-end">
                            <button class="btn btn-sm btn-success dropdown-toggle" type="button"
                                data-toggle="dropdown"><?php echo _("Menu") ?>
                                <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li><?php echo "<a href='administration_formsbuilder_create_field.php?tableid=$formtableid'><b> " . _("Create new field") . "</b></a>"; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <h4 class="card-title"><?php echo _("Fields"); ?></h4>
                        <?php include("./administration_formsbuilder_fields_incl.php	"); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- /page content -->
<?php include("./footer.php"); ?>