<?php

include("./header.php");

$GroupsAllowedArray = ["2", "34"];

if (count(array_intersect($GroupsAllowedArray, $group_array)) > 0) {
} else {
  $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
  notgranted($CurrentPage);
}

?>
<script>
  $(document).ready(function() {
    <?php initiateStandardSearchTable("TableTimeRegistrations"); ?>
  });
</script>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header"> <i class="fa fa-clock fa-lg"></i> <?php echo _("Time registrations"); ?>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <?php echo _("last 3 months") ?>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <table id="TableTimeRegistrations" class="table table-responsive table-borderless table-hover" cellspacing="0">
                    <thead>
                      <tr>
                        <th data-s-type="date"><?php echo _('Date worked'); ?></th>
                        <th></th>
                        <th><?php echo _('Name'); ?></th>
                        <th><?php echo _('Username'); ?></th>
                        <th><?php echo _('Time worked'); ?></th>
                        <th><?php echo _('Description'); ?></th>
                        <th><?php echo _('Billable'); ?></th>
                        <th><?php echo _('Task Subject'); ?></th>
                        <th><?php echo _('Date for registration'); ?></th>
                      </tr>
                    </thead>
                    <?php
                    $sql = "SELECT time_registrations.ID, DateWorked, TimeRegistered, time_registrations.Description, RelatedTaskID, DateRegistered, taskslist.Headline, taskslist.UserNote AS Note, taskslist.Deadline, taskslist.GoToLink, 
                            taskslist.RelatedElementID, time_registrations.Billable, CONCAT(users.Firstname,' ',users.Lastname) AS FullName, users.Username
                            FROM time_registrations
                            LEFT JOIN taskslist ON time_registrations.RelatedTaskID = taskslist.ID
                            LEFT JOIN users ON time_registrations.RelatedUserID = users.ID
                            WHERE DateAdded BETWEEN DATE_SUB(NOW(), INTERVAL 90 DAY) AND NOW()
                            ORDER BY DateWorked DESC, DateRegistered DESC";
                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    ?>
                    <tbody>
                      <?php while ($row = mysqli_fetch_array($result)) { ?>
                        <tr class='text-sm text-secondary mb-0'>
                          <?php
                          $RelatedTaskID = $row['RelatedTaskID'];
                          $RegistrationID = $row['ID'];
                          $Description = $row['Description'];
                          $FullName = $row['FullName'];
                          $Username = $row['Username'];
                          $DescriptionUncapped = $row['Description'];
                          if (strlen($Description) > 140) $Description = substr($Description, 0, 140) . '...';
                          $TimeRegistered = $row['TimeRegistered'];
                          $Headline = $row['Headline'];
                          $TaskNote = $row['Note'];
                          $DateWorked = convertToDanishDateTimeFormat($row['DateWorked']);
                          $DeadlineForChange = $row['Deadline'];
                          $Billable = $row['Billable'];
                          if ($Billable == "1") {
                            $BillableText = _("Yes");
                          } else {
                            $BillableText = _("No");
                          }
                          $DescriptionWithTitle = "<a href='javascript:void(0);' title='$DescriptionUncapped'>" . $Description . "</a>";
                          ?>
                          <td><?php echo convertToDanishDateTimeFormat($row['DateWorked']); ?></td>
                          <td><?php echo "<a href=\"javascript:void(0);\"><span class='badge badge-pill bg-gradient-success' onclick=\"runModalChangeTimeRegistration($RegistrationID)\"><i class='fa fa-pencil-alt fa-lg'></i></span></a></td>" ?>
                          <td><?php echo $FullName; ?></td>
                          <td><?php echo $Username; ?></td>
                          <td><?php echo $row['TimeRegistered']; ?></td>
                          <td>
                            <p class='text-sm text-secondary mb-0'><?php echo $DescriptionWithTitle; ?></p>
                          </td>
                          <td><?php echo $BillableText; ?></td>
                          <td><a href="<?php echo $row['GoToLink']; ?>" title="<?php echo _('Open') ?>"><?php echo $Headline . " " . $row['RelatedElementID']; ?></a></td>
                          <td><?php echo convertToDanishDateTimeFormat($row['DateRegistered']); ?></td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("./modals/modal_timeregistration.php") ?>

<?php include("./footer.php"); ?>