<?php
if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>
<script>
    $(document).ready(function() {
        <?php initiateStandardSearchTable("tableITSMSLA"); ?>
    });
</script>

<table id="tableITSMSLA" class="table table-responsive table-borderless table-hover" cellspacing="0">
    <thead>
        <tr>
            <th><?php echo _('Status'); ?></th>
            <th></th>
            <th><?php echo _("SLA"); ?></th>
            <th><?php echo _("Prio 1"); ?></th>
            <th><?php echo _("Prio 2"); ?></th>
            <th><?php echo _("Prio 3"); ?></th>
            <th><?php echo _("Prio 4"); ?></th>
        </tr>
    </thead>
    <?php
    $sql = "SELECT `ID`, `RelatedModuleID`, `Status`, `SLA`, `P1`, `P2`, `P3`, `P4` 
            FROM `itsm_sla_matrix`
            WHERE RelatedModuleID = $ITSMTypeID
			ORDER BY Status ASC";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    ?>
    <tbody>
        <?php while ($row = mysqli_fetch_array($result)) { ?>
            <?php $ID = $row['ID'] ?>
            <?php $RelatedModuleID = $row['RelatedModuleID'] ?>
            <?php $Status = $row['Status'] ?>
            <?php $StatusName = getITSMStatusCodeName($RelatedModuleID, $Status); ?>
            <?php $SLA = $row['SLA'] ?>
            <?php $SLAName = getITSMSLAName($SLA) ?>
            <?php $P1 = $row['P1'] ?>
            <?php $P2 = $row['P2'] ?>
            <?php $P3 = $row['P3'] ?>
            <?php $P4 = $row['P4'] ?>
            
            <tr>
                <td><?php echo $StatusName; ?></td>
                <td>
                    <?php
                    echo "<a href=\"javascript:editSLA('$ID');\"><span class=\"badge bg-gradient-info\"><i class=\"fa fa-pencil-alt\"></i></span></a>";
                    echo "<a href=\"javascript:deleteITSMSLA('$ID');\"><span class=\"badge bg-gradient-danger\"><i class=\"fa fa-trash\"></i></span></a>";
                    ?>
                </td>
                <td><?php echo $SLAName ?></td>
                <td><?php echo $P1 ?></td>
                <td><?php echo $P2 ?></td>
                <td><?php echo $P3 ?></td>
                <td><?php echo $P4 ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>