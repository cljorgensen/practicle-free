<?php
include("./header.php");
?>
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<script>
	$(document).ready(function() {
		<?php initiateStandardSearchTable("workflowstepstable"); ?>
	});
</script>
<script>
	function openModalEditWorkFlow(stepid) {
		var url = './getdata.php?popModaleditWorkflowstep=' + stepid;
		$("#editWorkFlowStepModal").modal('show');
		$.ajax({
			url: url,
			data: {
				data: stepid
			},
			type: 'POST',
			success: function(data) {
				var obj = JSON.parse(data);
				for (var i = 0; i < obj.length; i++) {
					document.getElementById('modalStepID').value = obj[i].id;
					document.getElementById('modalStepOrder').value = obj[i].steporder;
					document.getElementById('modalStepName').value = obj[i].stepname;
					document.getElementById('modalDescription').value = obj[i].description;
					document.getElementById("modalResponsible").value = obj[i].relateduserid;
				}
			}
		});
	}
</script>
<?php

$WorkFlowID = $_GET["workflowid"];

$sql = "SELECT workflows_template.ID, workflows_template.WorkflowName, workflowsteps_template.RelatedUserID, workflows_template.RelatedModuleID
        FROM workflows_template
        LEFT JOIN workflowsteps_template ON workflows_template.ID = workflowsteps_template.RelatedWorkFlowID
        WHERE workflows_template.ID = $WorkFlowID;";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

while ($row = mysqli_fetch_array($result)) {
	$WorkflowName = $row['WorkflowName'];
	$WorkFlowIDVal = $row['ID'];
	$ResponsibleUserIDVal = $row['RelatedUserID'];
	$RelatedModuleID = $row['RelatedModuleID'];
}
?>
<script>
	function submitWorkFlowStepChange() {
		var stepid = $('#modalStepID').val();
		var steporder = $('#modalStepOrder').val();
		var stepname = $('#modalStepName').val();
		var description = $('#modalDescription').val();
		var responsible = $('#modalResponsible').val();

		$.ajax({
			type: 'POST',
			url: './getdata.php',
			data: 'updateWorkFlowStep=1&stepid=' + stepid + '&steporder=' + steporder + '&stepname=' + stepname + '&description=' + description + '&responsible=' + responsible,
			beforeSend: function() {
				$('.submitBtn').attr("disabled", "disabled");
				$('.modal-body').css('opacity', '.5');
			},
			success: function(data) {
				$("#editWorkFlowStepModal").modal('hide');
				var message = "Updated";
				localStorage.setItem("pnotify", message);
				sleep(1).then((res) => {
					location.reload(true);
				});
				location.reload(true);
			}
		});
		location.href = "administration_workflows_edit.php?workflowid=<?php echo $WorkFlowIDVal; ?>";
	}

	function submitNewWorkFlowStep(workflowid) {
		var stepid = $('#modalCreateStepID').val();
		var steporder = $('#modalCreateStepOrder').val();
		var stepname = $('#modalCreateStepName').val();
		var description = $('#modalCreateDescription').val();
		var responsible = $('#modalCreateResponsible').val();

		$.ajax({
			type: 'POST',
			url: './getdata.php',
			data: 'createWorkFlowStep=1&workflowid=' + workflowid + '&steporder=' + steporder + '&stepname=' + stepname + '&description=' + description + '&responsible=' + responsible,
			beforeSend: function() {
				$('.submitBtn').attr("disabled", "disabled");
				$('.modal-body').css('opacity', '.5');
			},
			success: function(data) {
				$("#createWorkFlowStepModal").modal('hide');
				var message = "Updated";
				localStorage.setItem("pnotify", message);
				sleep(1).then((res) => {
					location.reload(true);
				});
				location.reload(true);
			}
		});
		location.href = "administration_workflows_edit.php?workflowid=<?php echo $WorkFlowIDVal; ?>";
	}

	function openCreateWorkflowstepModal(workflowid) {
		$("#createWorkFlowStepModal").modal('show');
	}
</script>

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header card-header"><i class="fas fa-stream"></i> <a href="administration_workflows.php"><?php echo _("Workflows"); ?></a> <i class="fa fa-angle-right fa-sm"></i> <a href="javascript:location.reload(true);"><?php echo $WorkflowName; ?></a>
					<div class="float-end">
						<ul class='navbar-nav justify-content-end'>
							<li class='nav-item dropdown pe-2'>
								<a href='javascript:;' class='nav-link text-body p-0 position-relative' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
									&nbsp;&nbsp;<i class="fa-solid fa-ellipsis-vertical" title="<?php echo _('Actions') ?>"></i>&nbsp;&nbsp;
								</a>
								<ul class='dropdown-menu dropdown-menu-end p-2 me-sm-n4' aria-labelledby='dropdownMenuButton'>
									<li class='mb-2'>
										<a class='dropdown-item border-radius-md' onclick="openCreateWorkflowstepModal('<?php echo $WorkFlowIDVal; ?>');">
											<div class='d-flex align-items-center py-1'>
												<div class='ms-2'>
													<h6 class='text-sm font-weight-normal my-auto'>
														<?php echo _('Create') ?>
													</h6>
												</div>
											</div>
										</a>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
				<div class="card-body">
					<div class="col-md-12 col-sm-12 col-xs-12">
						<table id="workflowstepstable" class="table table-responsive table-borderless table-hover" cellspacing="0">
							<thead>
								<tr>
									<th><?php echo _("Name") ?></th>
									<th></th>
									<th><?php echo _("Order") ?></th>
									<th><?php echo _("Description") ?></th>
									<th><?php echo _("Responsible") ?></th>
								</tr>
							</thead>
							<?php

							$sql = "SELECT workflowsteps_template.ID, RelatedWorkFlowID, StepOrder, StepName, Description, users.ID AS UserID
									FROM workflowsteps_template
									LEFT JOIN users ON workflowsteps_template.RelatedUserID = users.ID
									WHERE RelatedWorkFlowID = $WorkFlowIDVal
									ORDER BY StepOrder ASC;";

							$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
							?>
							<tbody>
								<?php while ($row = mysqli_fetch_array($result)) { ?>
									<?php
									$UsersID = $row['UserID'];
									if ($UsersID !== "") {
										$FullName = $functions->getUserFullNameWithUsername($UsersID);
									} else {
										$FullName = "";
									}
									?>
									<tr class='text-sm text-secondary mb-0'>
										<?php $ID = $row['ID']; ?>
										<td><?php echo $row['StepName']; ?></td>
										<td><?php echo "
											<a href='javascript:openModalEditWorkFlow($ID);'><span class='badge bg-gradient-info' data-bs-toggle='tooltip' data-bs-title=\"" . _("Edit") . "\"><i class='fa fa-pen-to-square'></i></span></a>
											<a href=\"javascript:;\"><span class=\"badge bg-gradient-danger\" onclick=\"deleteWorkFlowTemplateTask($ID);\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Delete") . "\"><i class='fa fa-trash'></i></span></a>
											<a href=\"javascript:;\"><span class=\"badge bg-gradient-info\" onclick=\"duplicateWorkFlowTask($ID);\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Duplicate") . "\"><i class='fas fa-clone'></i></span></a>"; ?></td>
										<td><?php echo $row['StepOrder']; ?></td>
										<td><?php echo substr($row['Description'], 0, 75) . "..."; ?></td>
										<td><?php echo $FullName; ?></td>
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

<!-- Modal for edit WorkFlowStep -->
<div class="modal fade" id="editWorkFlowStepModal" tabindex="-1" role="dialog" aria-labelledby="editWorkFlowStepModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title font-weight-normal" id="editWorkFlowStepModalLabel"><?php echo _("Edit") ?></h6>
				<button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p class="statusMsg"></p>
				<form method='post'>
					<div class="col-md-4 col-sm-4 col-xs-12" hidden>
						<div class="input-group input-group-static mb-4">
							<label for="modalStepID"><?php echo _("ID") ?></label>
							<input type="text" class="form-control" id="modalStepID" readonly>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalStepOrder"><?php echo _("Order") ?></label>
							<input type="text" class="form-control" id="modalStepOrder">
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalStepName"><?php echo _("Name") ?></label>
							<input type="text" class="form-control" id="modalStepName">
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalDescription"><?php echo _("Description") ?></label>
							<textarea type="text" class="form-control" id="modalDescription" rows="10"></textarea>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalResponsible"><?php echo _("Responsible") ?></label>
							<select id="modalResponsible" name="modalResponsible" class="form-control" required>
								<option value='-1' label=''></option>
								<?php
								$sql = "SELECT ID, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName, Username
										FROM users
										WHERE RelatedUserTypeID !=2
										AND Active = 1";
								$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
								while ($row = mysqli_fetch_array($result)) {
									if ($ResponsibleUserIDVal == $row['ID']) {
										echo "<option value='" . $row['ID'] . "' selected='select'>" . $row['FullName'] . "</option>";
									} else {
										echo "<option value='" . $row['ID'] . "'>" . $row['FullName'] . "</option>";
									}
								}
								?>
							</select>
						</div>
					</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-success float-end" onclick="submitWorkFlowStepChange()"><?php echo _("Update"); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End Modal for edit WorkFlowStep -->
<!-- Modal for adding new WorkFlowStep -->
<div class="modal fade" id="createWorkFlowStepModal" tabindex="-1" role="dialog" aria-labelledby="createWorkFlowStepModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title font-weight-normal" id="createWorkFlowStepModalLabel"><?php echo _("New Task") ?></h6>
				<button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p class="statusMsg"></p>
				<form method='post'>
					<div class="form-group">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="input-group input-group-static mb-4" data-bs-toggle="tooltip" data-bs-title="<?php echo _("A number to sort by, example: 100"); ?>">
								<label for="modalCreateStepOrder"><?php echo _("Order") ?></label>
								<input type="text" class="form-control" id="modalCreateStepOrder">
							</div>
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="input-group input-group-static mb-4">
								<label for="modalCreateStepName"><?php echo _("Name") ?></label>
								<input type="text" class="form-control" id="modalCreateStepName">
							</div>
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="input-group input-group-static mb-4">
								<label for="modalCreateDescription"><?php echo _("Description") ?></label>
								<textarea type="text" class="form-control" id="modalCreateDescription" rows="10"></textarea>
							</div>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalCreateResponsible"><?php echo _("Responsible") ?></label>
							<select id="modalCreateResponsible" name="modalCreateResponsible" class="form-control" required>
								<option value='-1' label=''></option>
								<?php
								$sql = "SELECT ID, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName, Username
										FROM users
										WHERE RelatedUserTypeID !=2
										AND Active = 1";
								$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
								while ($row = mysqli_fetch_array($result)) {
									if ($ResponsibleUserIDVal == $row['ID']) {
										echo "<option value='" . $row['ID'] . "' selected='select'>" . $row['FullName'] . "</option>";
									} else {
										echo "<option value='" . $row['ID'] . "'>" . $row['FullName'] . "</option>";
									}
								}
								?>
							</select>
						</div>
					</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success float-end" onclick="submitNewWorkFlowStep('<?php echo $WorkFlowID; ?>')"><?php echo _("Submit"); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End Modal for adding new WorkFlowStep -->

<?php include("./footer.php") ?>