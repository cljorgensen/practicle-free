<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array) || in_array("100008", $group_array) || in_array("100007", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<?php
$UserID = $_SESSION['id'];
$time = strtotime(date("Y-m-d H:i:s"));
$datenow = date("d-m-Y G:i", $time);
$ProjectID = $_GET["projectid"];
$ProjectNameVal = $_GET["projectname"];
?>
<!-- page content -->
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="card">
					<div class="card-header card-header-projects card-header-icon">
						<div class="card-icon">
							<i class="practicle-icons icon-board-task icon-lg"></i>
						</div>
						<h4 class="card-title"> <a href="projects_front.php">Projects</a> <i class="fa fa-angle-double-right"></i> <?php echo "<a href='projects_view.php?projectid=" . $ProjectID . "'>"; ?><?php echo $ProjectNameVal; ?></a> <i class="fa fa-angle-double-right"></i> <?php echo _('Create New Sprint'); ?></h4>
					</div>
					<div class="card-body">
						<div class="toolbar">
							<!--        Here you can write extra buttons/actions for the toolbar              -->
						</div>

						<?php

						if (isset($_POST['submit_createprojectsprint'])) {
							$Name = $_POST['Name'];
							$Deadline = $_POST['Deadline'];
							$Responsible = $_POST['Responsible'];
							$SprintEstimatedBudget = $_POST['SprintEstimatedBudget'];
							$SprintDescription = $_POST['SprintDescription'];

							createNewProjectSprint($Name, $Deadline, $Responsible, $SprintEstimatedBudget, $SprintDescription, $ProjectID);

							$redirectpage = "<meta http-equiv='refresh' content='1';url='projects_view.php?projectid=$ProjectID'><p><div class='alert alert-success'><span><b> Sprint created successfully</div></p>";
							echo $redirectpage;
						}
						?>
						<form role='form' method='POST'>
							<div class="row">
								<div class="col-md-4 col-sm-6 col-xs-12">
									<label for="Name">Sprint Shortname</label>
									<input type="text" id="Name" name="Name" class="form-control" value="">
								</div>
								<div class="col-md-3 col-sm-6 col-xs-12">
									<label for="Deadline">Deadline</label>
									<input type="text" id="Deadline" name="Deadline" class="form-control" value="<?php echo $datenow; ?>">
								</div>
								<div class="col-md-3 col-sm-6 col-xs-12">
									<label for="Responsible">Responsible</label>
									<select id="Responsible" name="Responsible" class="form-control" required>
										<option value='-1' label=''></option>
										<?php
										$sql = "SELECT ID, CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName, Username
												FROM users
												WHERE RelatedUserTypeID=1";
										$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
										while ($row = mysqli_fetch_array($result)) {
											echo "<option value='" . $row['ID'] . "'>" . $row['FullName'] . "</option>";
										}
										?>
									</select>
								</div>
								<div class="col-md-2 col-sm-6 col-xs-12">
									<label for="SprintEstimatedBudget">Estimated Budget</label>
									<input type="text" id="SprintEstimatedBudget" name="SprintEstimatedBudget" class="form-control" value="0">
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
									<label for="SprintDescription">Description</label>
									<input type="text" id="SprintDescription" name="SprintDescription" class="form-control" value="">
								</div>
							</div>

							<button type="submit" name="submit_createprojectsprint" id="submit_createprojectsprint" class="btn btn-sm btn-success float-end"><span class=""></span> <?php echo _("Create"); ?></button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
		$(function() {

			jQuery('#Deadline').datetimepicker({
				format: 'd-m-Y H:i',
				prevButton: false,
				nextButton: false,
				step: 60,
				dayOfWeekStart: 1
			});
			$.datetimepicker.setLocale('<?php echo $languageshort ?>');
		});
	</script>
</div>
<!-- /page content -->

<?php include("./footer.php") ?>