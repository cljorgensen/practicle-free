<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array) || in_array("100008", $group_array) || in_array("100007", $group_array)) {
} else {
  $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>

<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header card-header-projects"><i class="fas fa-project-diagram fa-lg"></i> <?php echo _("Finished projects"); ?>
          <div class="float-end">
            <ul class="navbar-nav justify-content-end">
              <li class="nav-item dropdown pe-2">
                <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                  &nbsp;&nbsp;<i class="fa-solid fa-circle-chevron-down" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Create") ?>"></i>&nbsp;&nbsp;
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href=('projects.php')">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Open projects") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href=('projects_finished.php')">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Closed projects") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href=('projects_search.php')">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Search projects") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
          <div class="float-end">
						<ul class="navbar-nav justify-content-end">
							<li class="nav-item dropdown pe-2">
								<a href="javascript:void(0);" title="<?php echo _("Create project") ?>" onclick="runModalCreateNewProject();" class="nav-link text-body p-0 position-relative" aria-expanded="false">
									&nbsp;&nbsp;<i class="far far-dark fa-plus-square"></i>&nbsp;&nbsp;
								</a>
							</li>
						</ul>
					</div>
        </div>
        <div class="card-body">
          <script>
            $(document).ready(function() {
              <?php initiateStandardSearchTable("TableProjects"); ?>
            });
          </script>
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <table id="TableProjects" class="table table-responsive table-borderless table-hover" cellspacing="0">
              <thead>
                <tr class='text-sm text-secondary mb-0'>
                  <th><?php echo _('ID'); ?></th>
                  <th></th>
                  <th><?php echo _("Name"); ?></th>
                  <th><?php echo _("% Progress"); ?></th>
                  <th><?php echo _("Project Manager"); ?></th>
                  <th><?php echo _("Project Responsible"); ?></th>
                  <th><?php echo _("Status"); ?></th>
                  <th><?php echo _("Deadline"); ?></th>
                  <th><?php echo _("Estimated Budget"); ?></th>
                  <th><?php echo _("Budget Spend"); ?></th>
                  <th><?php echo _("Estimated Hours"); ?></th>
                  <th><?php echo _("Hours spend"); ?></th>
                </tr>
              </thead>
              <?php

              $sql = "SELECT projects.ID, projects.Name, projects.Start, projects.Progress, projects_statuscodes.StatusName, projects.Deadline, projects.EstimatedBudget, 
										(SELECT CONCAT(users.firstname,' ', users.lastname) FROM users WHERE users.ID = projects.ProjectResponsible) AS ProjectResponsible, (SELECT CONCAT(users.firstname,' ', users.lastname) FROM users WHERE users.ID = projects.ProjectManager) AS ProjectManager,EstimatedHours, HoursSpend,
										(SELECT SUM(project_tasks.progress) FROM project_tasks WHERE project_tasks.RelatedProject = projects.ID)/(SELECT COUNT(*) FROM project_tasks WHERE project_tasks.RelatedProject = projects.ID) AS ProjectProgress 
										FROM projects
										LEFT JOIN projects_statuscodes ON projects.Status = projects_statuscodes.ID
										LEFT JOIN users ON projects.ProjectManager = users.ID
										WHERE (Status IN ('7') AND projects.ID IN (SELECT project_users.ProjectID
										FROM project_users
										WHERE project_users.UserID = $UserSessionID) OR (Status IN ('7') AND projects.ProjectResponsible = $UserSessionID) OR (Status IN ('7') AND projects.ProjectManager = $UserSessionID));";

              $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
              ?>
              <tbody>
                <?php while ($row = mysqli_fetch_array($result)) { ?>
                  <?php $ProjectProgress = $row['ProjectProgress']; ?>
                  <tr class='text-sm text-secondary mb-0'>
                    <td><?php echo $row['ID']; ?></td>
                    <td>
                      <?php echo "<a href='projects_view.php?projectid=" . $row['ID'] . "'><span class='badge badge-pill bg-gradient-secondary'><i class='fa fa-folder-open'></i></span></a>"; ?> </td>
                    </td>
                    <td><b><?php echo $row['Name']; ?></b></td>
                    <td data-bs-toggle="tooltip" data-bs-title="<?php echo ROUND($ProjectProgress, 0); ?>%">
                      <div class="progress">
                        <div class="progress-bar bg-success" role="progressbar" aria-valuenow="<?php echo $row['ProjectProgress']; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $ProjectProgress; ?>%">
                        </div>
                      </div>
                    </td>
                    <td><?php echo $row['ProjectManager']; ?></td>
                    <td><?php echo $row['ProjectResponsible']; ?></td>
                    <td><?php echo _($row['StatusName']); ?></td>
                    <td><?php echo convertToDanishTimeFormat($row['Deadline']); ?></td>
                    <td><?php echo $row['EstimatedBudget']; ?></td>
                    <td><?php echo $ProjectBudgetSpendVal = getProjectTotalAmountSpend($row['ID']); ?></td>
                    <td><?php echo $row['EstimatedHours']; ?></td>
                    <td><?php echo $ProjectHoursSpendVal = getProjectTotalHoursSpend($row['ID']); ?></td>
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
<?php include("./footer.php") ?>