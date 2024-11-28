<?php include("./header.php"); ?>
<?php
if (!empty($group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>

<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header card-header"><img class="practicle_logo" src="./images/practicle_logo_only.png" width="25" alt="Practicle"> <a href="changelog.php"><?php echo _("Changelog"); ?></a></div>
                <div class="card-body">

                    <script>
                        $(document).ready(function() {
                            <?php initiateStandardSearchTable("TableChangelog"); ?>
                        });
                    </script>

                    <table id="TableChangelog" class="table table-responsive table-borderless table-hover" cellspacing="0">
                        <thead>
                            <tr class='text-sm text-secondary mb-0'>
                                <th><?php echo _('Version'); ?></th>
                                <th><?php echo _('Date'); ?></th>
                                <th><?php echo _('Description'); ?></th>
                                <th><?php echo _('Type'); ?></th>
                            </tr>
                        </thead>
                        <?php

                        $sql = "SELECT changelog.ID, changelog.Date AS ChangelogDate, changelog.Version, changelog.Description, changelog_types.Name AS ChangelogTypeName
                                FROM changelog
                                INNER JOIN changelog_types ON changelog.Type = changelog_types.ID
                                WHERE Type != 4
                                ORDER BY changelog.Date DESC";

                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                        ?>
                        <tbody>
                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                <?php
                                // Check if $_SESSION['memberofgroups'] is set and is an array
                                $GroupsArray = array();
                                $ChangeLogID = $row['ID'];
                                $GroupsArray = $_SESSION['memberofgroups'];
                                $changeLogDate = $row['ChangelogDate'];
                                $version = $row['Version'];
                                $timestamp = strtotime($changeLogDate);
                                $changeLogDate = convertToDanishDateFormat($changeLogDate);
                                $changeLogDate = "<span style=\"display:none;\">$timestamp</span>$changeLogDate";

                                if (in_array("100000", $_SESSION['memberofgroups'])) {
                                    $editLink = "<a href=\"#\" data-bs-toggle=\"modal\" data-bs-target=\"#modalEditChangelog\" onclick=\"runModalEditChangelog('$ChangeLogID')\"><i class=\"fa-solid fa-pen-to-square\"></i> $version</a>";
                                } else {
                                    $editLink = "$version";
                                }
                                ?>
                                <tr class='text-sm text-secondary mb-0 text-wrap'>
                                    <td><?php echo $editLink ?></td>
                                    <td><?php echo $changeLogDate ?></td>
                                    <td class='text-sm text-secondary mb-0 text-wrap'><?php echo ($row['Description']); ?></td>
                                    <td><?php echo ($row['ChangelogTypeName']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("./modals/modal_edit_changelog.php") ?>
<?php include("./footer.php"); ?>