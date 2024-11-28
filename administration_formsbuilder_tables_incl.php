<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<script>
$(document).ready(function() {

    <?php initiateStandardSearchTable("forms_tables"); ?>

});
</script>
<script>
function deletetable(tableid, tablename) {
    if (window.confirm("Are you sure?")) {
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        console.log(tablename);
        xmlhttp.open("GET", "getdata.php?deleteFormsTable=" + tableid + "&tablename=" + tablename, true);
        xmlhttp.send();
        location.reload(true);
    }
}
</script>
<!-- the table view -->
<table id="forms_tables" class="table dt-responsive table-bordered table-hover" cellspacing="0">
    <thead>
        <tr>
            <th><?php echo _('ID'); ?></th>
            <th></th>
            <th></th>
            <th><?php echo _('Table Name'); ?></th>
        </tr>
    </thead>
    <?php
	$sql = "SELECT ID, TableName
			FROM forms
			WHERE forms_tables.RelatedformsID = $formid";
	$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
	?>
    <tbody>
        <?php while ($row = mysqli_fetch_array($result)) { ?>
        <?php $TableID = $row['ID']?>
        <?php $TableName = $row['TableName']?>
        <tr>
            <td><?php echo $row['ID']; ?></td>
            <td><?php echo "<a href='administration_formsbuilder_edit_table.php?tableid=$TableID'><span class='badge badge-dark'><i class='fa fa-folder-open fa-lg'></i></span></a>"; ?>
            </td>
            <td><?php echo "<a href='javascript:void(0);' onclick=\"deletetable('$TableID','$TableName')\"><span class='badge badge-danger'><i class='fa fa-trash fa-lg'></i></span></a>"; ?>
            </td>
            <td><?php echo $row['TableName']; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>