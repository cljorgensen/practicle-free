<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array) || in_array("100008", $group_array) || in_array("100007", $group_array)) {
} else {
  $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="card">
					<div class="card-header card-header-projects"><i class="fas fa-project-diagram fa-lg"></i> <?php echo _("Overdue projects"); ?>
						<div class="button float-end">
							<a href="projects_create.php" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Create new")?>"> <i class="far far-dark fa-plus-square"></i></a>
						</div>
					</div>
          <div class="card-body">
            <div class="toolbar">
              <!--        Here you can write extra buttons/actions for the toolbar              -->
            </div>

            <script>
              $(document).ready(function() {
                <?php initiateStandardSearchTable("TableProjects"); ?>
              });
            </script>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <table id="TableProjects" class="table table-responsive table-borderless table-hover" cellspacing="0">
                <thead>
                  <tr>
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

                $sql = "SELECT projects.ID, projects.Name, projects.Start, projects.Progress, projects_statuscodes.StatusName, projects.Deadline, CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName, projects.EstimatedBudget, 
                          (SELECT CONCAT(users.firstname,' ', users.lastname) FROM users WHERE users.ID = projects.ProjectResponsible) AS ProjectResponsible, EstimatedHours, HoursSpend,
                          (SELECT SUM(project_tasks.progress) FROM project_tasks WHERE project_tasks.RelatedProject = projects.ID)/(SELECT COUNT(*) FROM project_tasks WHERE project_tasks.RelatedProject = projects.ID) AS ProjectProgress 
                          FROM projects
                          LEFT JOIN projects_statuscodes ON projects.Status = projects_statuscodes.ID
                          LEFT JOIN users ON projects.ProjectManager = users.ID
											    WHERE Status != '7' AND Projects.Deadline < Now();";

                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                ?>
                <tbody>
                  <?php while ($row = mysqli_fetch_array($result)) { ?>
                    <tr>
                      <td><?php echo $row['ID']; ?></td>
                      <td>
                        <?php echo "<a href='projects_view.php?projectid=" . $row['ID'] . "'><span class='badge badge-pill bg-gradient-secondary'><i class='fa fa-folder-open'></i></span></a>"; ?> </td>
                      </td>
                      <td><b><?php echo $row['Name']; ?></b></td>
                      <td>
                        <div class="progress">
                          <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row['ProjectProgress']; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $row['ProjectProgress']; ?>%">
                            <?php echo ROUND($row['ProjectProgress'], 0); ?>
                          </div>
                        </div>
                      </td>
                      <td><?php echo $row['FullName']; ?></td>
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
</div>
</div>
<!-- /page content -->

<?php include("./footer.php") ?>