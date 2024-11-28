<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<script>
    $(document).ready(function() {

        <?php initiateStandardSearchTable("tableFields"); ?>

    });
</script>
<script>
    function deletetablefield(fieldid, fieldname, formid) {
        if (window.confirm("Are you sure?")) {
            location.href = this.href;
            if (window.XMLHttpRequest) {
                // code for IE7+, Firefox, Chrome, Opera, Safari
                xmlhttp = new XMLHttpRequest();
            } else {
                // code for IE6, IE5
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            xmlhttp.open("GET", "getdata.php?deleteFormsTableField=" + fieldid + "&fieldname=" + fieldname + "&formid=" +
                formid, true);
            xmlhttp.send();
            location.reload(true);
        }
    }
</script>
<!-- the table view -->
<table id="tableFields" class="table table-responsive table-borderless table-hover" cellspacing="0">
    <thead>
        <tr>
            <th><?php echo _('Order'); ?></th>
            <th></th>
            <th><?php echo _('Field Name'); ?></th>
            <th><?php echo _('Label'); ?></th>
            <th><?php echo _('Type'); ?></th>
        </tr>
    </thead>
    <?php
    $sql = "SELECT forms_fieldslist.ID, forms_fieldslist.FieldName, forms_fieldslist.RelatedFormID, forms_fieldslist_types.TypeName, 
			forms_fieldslist.FieldOrder, forms_fieldslist.Label
			FROM forms_fieldslist
			LEFT JOIN forms_fieldslist_types ON forms_fieldslist.FieldType = forms_fieldslist_types.ID
			WHERE RelatedFormID = $FormID
			ORDER BY FieldOrder ASC";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    ?>
    <tbody>
        <?php while ($row = mysqli_fetch_array($result)) { ?>
            <?php $FieldID = $row['ID'] ?>
            <?php $FieldName = $row['FieldName'] ?>
            <?php $FieldType = $row['TypeName'] ?>
            <?php $RelatedFormID = $row['RelatedFormID'] ?>
            <?php $FieldOrder = $row['FieldOrder'] ?>
            <?php $Label = $row['Label'] ?>
            <tr>
                <td><?php echo $FieldOrder; ?></td>
                <td>
                    <a href="javascript:;"><span class="badge bg-gradient-info" onclick="editFieldEntry('<?php echo $FieldID ?>');"><i class='fa fa-pencil-alt'></i></span></a>
                    <?php echo "<a href='javascript:void(0);' onclick=\"deleteFormField($FieldID,'$FieldName')\"><span class='badge bg-gradient-danger'><i class='fa fa-trash'></i></span></a>"; ?>
                </td>
                <td><?php echo $FieldName ?></td>
                <td><?php echo $Label ?></td>
                <td><?php echo $FieldType ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>