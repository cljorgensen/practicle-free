<?php

include("./header.php");
$UserID = $_GET["userid"];
$SessionUserID = $_SESSION['id'];

$GroupsAllowedArray = ["2", "34"];

if (count(array_intersect($GroupsAllowedArray, $group_array)) > 0 || $UserID == $SessionUserID) {
  // User is allowed
} else {
  $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
  notgranted($CurrentPage);
}

?>
<script>
  $(document).ready(function() {
    <?php initiateStandardSearchTable("TableTimeRegistrations"); ?>
    <?php initiateStandardSearchTable("TableTimeRegistrationsDaily"); ?>
  });
</script>
<?php
$RecieverInformationArray = getNotificationRecieverDetails($UserID);
$FullName = $RecieverInformationArray['FullName'];
?>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header card-header"> <i class="fa fa-clock fa-lg"></i> <?php echo _("Time registrations") . ": "; ?><?php echo $FullName ?>
        </div>
        <div class="scrolling-menu-wrapper">
          <div class="arrow arrow-left">&#9664;</div>
          <div class="scrolling-menu">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#All">
                  <?php echo _("All"); ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#Daily">
                  <?php echo _("Daily basis"); ?>
                </a>
              </li>
            </ul>
          </div>
          <div class="arrow arrow-right">&#9654;</div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="tab-content">
                <div class="tab-pane active" id="All">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <?php echo _("last 2 months") ?>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <table id="TableTimeRegistrations" class="table table-responsive table-borderless table-hover" cellspacing="0">
                        <thead>
                          <tr>
                            <th data-s-type="date"><?php echo _('Date worked'); ?></th>
                            <th></th>
                            <th><?php echo _('Duration'); ?></th>
                            <th><?php echo _('Description'); ?></th>
                            <th><?php echo _('Billable'); ?></th>
                            <th><?php echo _('Subject'); ?></th>
                            <th><?php echo _('Date for registration'); ?></th>
                          </tr>
                        </thead>
                        <?php
                        $sql = "SELECT time_registrations.ID, time_registrations.DateWorked, TimeRegistered, time_registrations.Description, RelatedTaskID, DateRegistered, taskslist.Headline, taskslist.UserNote, taskslist.Deadline, taskslist.GoToLink, 
                                taskslist.RelatedElementID, taskslist.RelatedElementTypeID, time_registrations.Billable
                                FROM time_registrations
                                LEFT JOIN taskslist ON time_registrations.RelatedTaskID = taskslist.ID
                                WHERE time_registrations.RelatedUserID = $UserID AND DateAdded BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                                ORDER BY DateWorked DESC, DateRegistered DESC;";
                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                        ?>
                        <tbody>
                          <?php while ($row = mysqli_fetch_array($result)) { ?>
                            <tr class='text-sm text-secondary mb-0'>
                              <?php
                              $RelatedTaskID = $row['RelatedTaskID'];
                              $RegistrationID = $row['ID'];
                              $Description = $row['Description'];
                              $DescriptionUncapped = $row['Description'];
                              if (strlen($Description) > 140) $Description = substr($Description, 0, 140) . '...';
                              $TimeRegistered = $row['TimeRegistered'];
                              $TaskNote = $row['UserNote'];
                              $DeadlineForChange = $row['Deadline'];
                              $Headline = $row['Headline'];
                              $DateWorked = convertToDanishDateTimeFormat($row['DateWorked']);
                              $Billable = $row['Billable'];
                              if ($Billable == "1") {
                                $BillableText = _("Yes");
                              } else {
                                $BillableText = _("No");
                              }
                              $DescriptionWithTitle = "<a href='javascript:void(0);' title='$DescriptionUncapped'>" . $Description . "</a>";
                              ?>
                              <td><?php echo $DateWorked; ?></td>
                              <td><?php echo "<a href=\"javascript:void(0);\"><span class='badge badge-pill bg-gradient-success' onclick=\"runModalChangeTimeRegistration($RegistrationID)\"><i class='fa fa-pencil-alt fa-lg'></i></span></a></td>" ?>
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
                <div class="tab-pane" id="Daily">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <table id="TableTimeRegistrationsDaily" class="table table-responsive table-borderless table-hover" cellspacing="0">
                        <thead>
                          <tr>
                            <th data-s-type="date"><?php echo _('Date worked'); ?></th>
                            <th><?php echo _('Duration minutes'); ?></th>
                            <th><?php echo _('Duration hours'); ?></th>
                            <th><?php echo _('Number of registrations'); ?></th>
                          </tr>
                        </thead>
                        <?php
                        $sql = "SELECT sub.SubDateWorked, SUM(sub.TimeRegistered) AS TimeWorked, COUNT(sub.TimeRegistered) AS NumberOfRegs
                                FROM (
                                SELECT DATE_FORMAT(DateWorked, '%d-%m-%Y') AS SubDateWorked, TimeRegistered
                                FROM time_registrations
                                LEFT JOIN taskslist ON time_registrations.RelatedTaskID = taskslist.ID
                                WHERE time_registrations.RelatedUserID = $UserID AND DateAdded BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                                ORDER BY SubDateWorked DESC) AS sub
                                GROUP BY sub.SubDateWorked";
                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                        ?>
                        <tbody>
                          <?php while ($row = mysqli_fetch_array($result)) { ?>
                            <tr class='text-sm text-secondary mb-0'>
                              <?php
                              $DateWorked = convertToDanishDateFormat($row['SubDateWorked']);
                              $TimeWorked = $row['TimeWorked'];
                              $NumberOfRegs = $row['NumberOfRegs'];
                              ?>
                              <td><?php echo $DateWorked; ?></td>
                              <td><?php echo $TimeWorked; ?></td>
                              <td><?php echo round(($TimeWorked) / 60, 2); ?></td>
                              <td><?php echo $NumberOfRegs; ?></td>
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
  </div>
</div>

<?php include("./modals/modal_timeregistration.php") ?>

<?php include("./footer.php"); ?>