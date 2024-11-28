<?php
if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
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

<table id="tableFields" class="table table-responsive table-borderless table-hover" cellspacing="0">
    <thead>
        <tr>
            <th><?php echo _('Order'); ?></th>
            <th></th>
            <th><?php echo _('Field Name'); ?></th>
            <th><?php echo _('Label'); ?></th>
            <th><?php echo _('Type'); ?></th>
            <th><?php echo _('Show in relations'); ?></th>
        </tr>
    </thead>
    <?php
    $sql = "SELECT itsm_fieldslist.ID, itsm_fieldslist.FieldName, itsm_fieldslist.RelatedTypeID, itsm_fieldslist_types.TypeName, 
			itsm_fieldslist.FieldOrder, itsm_fieldslist.FieldLabel, itsm_fieldslist.DefaultField, itsm_fieldslist.RelationShowField
			FROM itsm_fieldslist
			LEFT JOIN itsm_fieldslist_types ON itsm_fieldslist.FieldType = itsm_fieldslist_types.ID
			WHERE RelatedTypeID = $ITSMID
			ORDER BY FieldOrder ASC";
    
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    ?>
    <tbody>
        <?php while ($row = mysqli_fetch_array($result)) { ?>
            <?php $FieldID = $row['ID'] ?>
            <?php $FieldName = $row['FieldName'] ?>
            <?php $FieldType = $row['TypeName'] ?>
            <?php $RelatedITSMTypeID = $row['RelatedTypeID'] ?>
            <?php $FieldOrder = $row['FieldOrder'] ?>
            <?php $FieldLabel = $row['FieldLabel'] ?>
            <?php $DefaultField = $row['DefaultField'] ?>
            <?php $RelationShowField = $row['RelationShowField'] ?>
            <tr>
                <td><?php echo $FieldOrder; ?></td>
                <td>
                    <?php
                    $Excludes = array();
                    array_push($Excludes,"CIField16831324", "CIField22810882", "CIField51453526", "CIField22943447", "CIField88022106", "CIField57929675", "CIField66095461", "CIField86044238");
                    echo "<a href=\"javascript:editITSMFieldEntry('$FieldID');\"><span class=\"badge bg-gradient-info\"><i class=\"fa fa-pencil-alt\"></i></span></a>";
                    if ($DefaultField == '0' && !in_array($FieldName, $Excludes)) {
                        echo "<a href=\"javascript:deleteITSMField('$FieldID','$FieldName');\"><span class=\"badge bg-gradient-danger\"><i class=\"fa fa-trash\"></i></span></a>";
                    }
                    ?>
                </td>
                <td><?php echo $FieldName; ?></td>
                <td><?php echo $FieldLabel ?></td>
                <td><?php echo $FieldType ?></td>
                <td><?php echo $RelationShowField ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>