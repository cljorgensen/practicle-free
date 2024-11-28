<?php
if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>
<script>
    $(document).ready(function() {
        <?php initiateStandardSearchTable("tableITSMEmail"); ?>
    });
</script>

<table id="tableITSMEmail" class="table table-responsive table-borderless table-hover" cellspacing="0">
    <thead>
        <tr>
            <th></th>
            <th><?php echo _('Email'); ?></th>
            <th><?php echo _('Default'); ?></th>
        </tr>
    </thead>
    <?php
    $sql = "SELECT `ID`, `Email`, `DefaultEmail`
                FROM `itsm_emails`
                WHERE `RelatedITSMTypeID` = $ITSMTypeID
                ORDER BY `Email` ASC";

    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    ?>
    <tbody>
        <?php while ($row = mysqli_fetch_array($result)) { ?>
            <?php $ID = $row['ID'] ?>
            <?php $Email = $row['Email'] ?>
            <?php
            $DefaultEmail = $row['DefaultEmail'];
            if ($DefaultEmail == "1") {
                $DefaultEmail = $functions->translate("Yes");
            } else {
                $DefaultEmail = $functions->translate("No");
            }
            ?>
            <tr>
                <td>
                    <?php
                    echo "<a href=\"javascript:editITSMEmail('$ID');\"><span class=\"badge bg-gradient-info\"><i class=\"fa fa-pencil-alt\"></i></span></a>";
                    echo "<a href=\"javascript:deleteITSMEmail('$ID');\"><span class=\"badge bg-gradient-danger\"><i class=\"fa fa-trash\"></i></span></a>";
                    ?>
                </td>
                <td><?php echo $Email ?></td>
                <td><?php echo $DefaultEmail ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>