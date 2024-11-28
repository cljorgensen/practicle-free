<?php
if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<script>
    $(document).ready(function() {
        <?php initiateStandardSearchTable("tableStatusCodes"); ?>
    });
</script>

<table id="tableStatusCodes" class="table table-responsive table-borderless table-hover" cellspacing="0">
    <thead>
        <tr>
            <th><?php echo _('ID'); ?></th>
            <th></th>
            <th><?php echo _('Name'); ?></th>
            <th><?php echo _('SLA Supported'); ?></th>
            <th><?php echo _('Closed Status'); ?></th>
        </tr>
    </thead>
    <?php
    $sql = "SELECT itsm_statuscodes.ID, itsm_statuscodes.StatusCode, itsm_statuscodes.StatusName, itsm_statuscodes.SLA, itsm_statuscodes.ClosedStatus
			FROM itsm_statuscodes			
			WHERE ModuleID = $ITSMTypeID
			ORDER BY StatusCode ASC";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    ?>
    <tbody>
        <?php while ($row = mysqli_fetch_array($result)) { ?>
            <?php $StatusID = $row['ID'] ?>
            <?php $StatusCode = $row['StatusCode'] ?>
            <?php $StatusName = $row['StatusName'] ?>
            <?php $SLA = $row['SLA'] ?>
            <?php $ClosedStatus = $row['ClosedStatus'] ?>
            <tr>
                <td><?php echo $StatusCode; ?></td>
                <td>
                    <?php
                    echo "<a href=\"javascript:editITSMStatus('$StatusID');\"><span class=\"badge bg-gradient-info\"><i class=\"fa fa-pencil-alt\"></i></span></a>";
                    echo "<a href=\"javascript:deleteITSMStatus('$StatusID');\"><span class=\"badge bg-gradient-danger\"><i class=\"fa fa-trash\"></i></span></a>";
                    ?>
                </td>
                <td><?php echo $StatusName ?></td>
                <td><?php echo $SLA ?></td>
                <td><?php echo $ClosedStatus ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>