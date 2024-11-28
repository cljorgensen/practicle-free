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
            <th><?php echo _('FieldLabel'); ?></th>
            <th><?php echo _('Type'); ?></th>
            <th><?php echo _('Show in relations'); ?></th>
            <th><?php echo _('Addon'); ?></th>
        </tr>
    </thead>
    <?php
    $sql = "SELECT cmdb_ci_fieldslist.ID, cmdb_ci_fieldslist.FieldName, cmdb_ci_fieldslist.RelatedCITypeID, cmdb_fieldslist_types.TypeName, 
			cmdb_ci_fieldslist.FieldOrder, cmdb_ci_fieldslist.FieldLabel, cmdb_ci_fieldslist.DefaultField, cmdb_ci_fieldslist.RelationShowField, cmdb_ci_fieldslist.Addon
			FROM cmdb_ci_fieldslist
			LEFT JOIN cmdb_fieldslist_types ON cmdb_ci_fieldslist.FieldType = cmdb_fieldslist_types.ID
			WHERE RelatedCITypeID = $CITypeID
			ORDER BY FieldOrder ASC";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    ?>
    <tbody>
        <?php while ($row = mysqli_fetch_array($result)) { ?>
            <?php $FieldID = $row['ID'] ?>
            <?php $FieldName = $row['FieldName'] ?>
            <?php $FieldType = $row['TypeName'] ?>
            <?php $RelatedCITypeID = $row['RelatedCITypeID'] ?>
            <?php $FieldOrder = $row['FieldOrder'] ?>
            <?php $FieldLabel = $row['FieldLabel'] ?>
            <?php $DefaultField = $row['DefaultField'] ?>
            <?php $RelationShowField = $row['RelationShowField'] ?>
            <?php $Addon = $row['Addon'] ?>
            <tr>
                <td><?php echo $FieldOrder; ?></td>
                <td>
                    <?php
                    $Excludes = array();
                    array_push($Excludes, "CIField16831324", "CIField22810882", "CIField51453526", "CIField22943447", "CIField88022106", "CIField57929675", "CIField66095461", "CIField86044238");
                    echo "<a href=\"javascript:editCMDBFieldEntry('$FieldID','$CITypeID');\"><span class=\"badge bg-gradient-info\"><i class=\"fa fa-pencil-alt\"></i></span></a>";
                    if ($DefaultField == '0' && !in_array($FieldName, $Excludes)) {
                        echo "<a href=\"javascript:deleteCIField('$FieldID','$FieldName','$CITypeID');\"><span class=\"badge bg-gradient-danger\"><i class=\"fa fa-trash\"></i></span></a>";
                    }
                    ?>
                </td>
                <td><?php echo $FieldName; ?></td>
                <td><?php echo $FieldLabel ?></td>
                <td><?php echo $FieldType ?></td>
                <td><?php echo $RelationShowField ?></td>
                <td><?php echo $Addon ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>