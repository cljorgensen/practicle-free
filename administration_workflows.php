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
		<?php initiateStandardSearchTable("searchresultsdocattcontents"); ?>
	});
</script>

<script>
	function openCreateWorkFlowModal(workflowid) {
		$("#createWorkFlowModal").modal('show');
	}

	function submitNewWorkFlow() {
		var workflowname = $('#modalCreateWorkFlowName').val();
		var workflowdescription = $('#modalCreateDescription').val();
		var workflowresponsible = $('#modalCreateResponsible').val();
		var workflowrelatedmodule = $('#modalCreateRelatedModule').val();

		var vData = {
			workflowname: workflowname,
			workflowdescription: workflowdescription,
			workflowresponsible: workflowresponsible,
			workflowrelatedmodule: workflowrelatedmodule,
		};

		$.ajax({
			type: "POST",
			url: "./getdata.php?createWorkFlow",
			data: vData,
			beforeSend: function() {
				$('.submitBtn').attr("disabled", "disabled");
				$('.modal-body').css('opacity', '.5');
			},
			success: function(data) {
				$("#createWorkFlowModal").modal('hide');
				var message = "Created";
				localStorage.setItem("pnotify", message);
				sleep(1).then((res) => {
					location.reload(true);
				});
				location.reload(true);
			}
		});
		location.href = "administration_workflows.php";
	}

	function EditWorkflow(workflowid) {
		$("#editWorkFlowModal").modal('show');

		$.ajax({
			url: "./getdata.php?editWorkFlowModal",
			data: {
				workflowid: workflowid
			},
			type: 'POST',
			success: function(data) {
				var obj = JSON.parse(data);
				for (var i = 0; i < obj.length; i++) {
					var WorkFlowName = obj[i].WorkFlowName;
					var Description = obj[i].Description;
					var RequestProblemText = obj[i].RequestProblemText;
					var Responsible = obj[i].Responsible;
					var RelatedModuleID = obj[i].RelatedModuleID;

					document.getElementById("WFID").innerHTML = workflowid;
					document.getElementById("modalEditWorkFlowName").value = WorkFlowName;
					document.getElementById("modalEditWorkFlowDescription").value = obj[i].Description;
					document.getElementById("modalEditWorkFlowResponsible").value = obj[i].Responsible;
					document.getElementById("modalEditWorkFlowRelatedModule").value = obj[i].RelatedModuleID;
					document.getElementById("modalEditWorkFlowActive").value = obj[i].Active;
				}
			}
		});
	}

	function submitWorkFlowChanges() {
		var workflowname = $('#modalEditWorkFlowName').val();
		var workflowdescription = $('#modalEditWorkFlowDescription').val();
		var workflowresponsible = $('#modalEditWorkFlowResponsible').val();
		var workflowrelatedmodule = $('#modalEditWorkFlowRelatedModule').val();
		var modalEditWorkFlowActive = $('#modalEditWorkFlowActive').val();
		var workflowid = document.getElementById("WFID").innerHTML;

		vData = {
			workflowname: workflowname,
			workflowdescription: workflowdescription,
			workflowresponsible: workflowresponsible,
			workflowrelatedmodule: workflowrelatedmodule,
			workflowid: workflowid,
			modalEditWorkFlowActive: modalEditWorkFlowActive,
		}

		$.ajax({
			type: 'POST',
			url: './getdata.php?updateWorkFlow',
			data: vData,
			success: function(data) {
				$("#editWorkFlowModal").modal('hide');
				var message = "Updated";
				localStorage.setItem("pnotify", message);
				sleep(1).then((res) => {
					location.reload(true);
				});
			}
		});
	}
</script>

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header card-header"><i class="fas fa-stream"></i> <a href="javascript:location.reload(true);"><?php echo _("Workflows"); ?></a>
					<div class="float-end">
						<ul class='navbar-nav justify-content-end'>
							<li class='nav-item dropdown pe-2'>
								<a href='javascript:;' class='nav-link text-body p-0 position-relative' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
									&nbsp;&nbsp;<i class="fa-solid fa-ellipsis-vertical" title="<?php echo _('Actions') ?>"></i>&nbsp;&nbsp;
								</a>
								<ul class='dropdown-menu dropdown-menu-end p-2 me-sm-n4' aria-labelledby='dropdownMenuButton'>
									<li class='mb-2'>
										<a class='dropdown-item border-radius-md' onclick="openCreateWorkFlowModal();">
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
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<table id="searchresultsdocattcontents" class="table table-responsive table-borderless table-hover" cellspacing="0">
							<thead>
								<tr>
									<th><?php echo _("Name"); ?></th>
									<th></th>
									<th><?php echo _("Responsible"); ?></th>
									<th><?php echo _("Related Module"); ?></th>
									<th><?php echo _("Active"); ?></th>
								</tr>
							</thead>
							<?php

							$sql = "SELECT workflows_template.ID, workflows_template.WorkflowName, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName, workflows_template.Description, modules.Name AS ModuleName, workflows_template.Active
									FROM workflows_template
									LEFT JOIN users ON workflows_template.Responsible = users.ID
									LEFT JOIN modules ON workflows_template.RelatedModuleID = modules.ID
									ORDER BY WorkflowName ASC";

							$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
							?>
							<tbody>
								<?php while ($row = mysqli_fetch_array($result)) { ?>
									<?php
									$ID = $row['ID'];
									$ModuleName = $row['ModuleName'];
									$ModuleName = _($ModuleName);
									$Description = $row['Description'];
									$Active = $row['Active'];
									if ($Active == "1") {
										$Active = _("Yes");
									} else {
										$Active = _("No");
									}
									?>
									<tr class='text-sm text-secondary mb-0'>
										<td data-bs-toggle="tooltip" data-bs-title="<?php echo $Description; ?>"><?php echo $row['WorkflowName']; ?></td>
										<td><?php echo "
											<a href='administration_workflows_edit.php?workflowid=" . $row['ID'] . "'><span class='badge bg-gradient-success' data-bs-toggle='tooltip' data-bs-title=\"" . _("Open") . "\"><i class='fa fa-folder-open'></i></span></a>
											<a href=\"javascript:EditWorkflow($ID);\"><span class='badge bg-gradient-info' data-bs-toggle='tooltip' data-bs-title=\"" . _("Edit") . "\"><i class='fa fa-pen-to-square'></i></span></a>
											<a href=\"javascript:;\"><span class=\"badge bg-gradient-danger\" onclick=\"deleteWorkFlow($ID);\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Delete") . "\"><i class='fa fa-trash'></i></span></a>
											<a href=\"javascript:;\"><span class=\"badge bg-gradient-info\" onclick=\"duplicateWorkFlow($ID);\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Duplicate") . "\"><i class='fas fa-clone'></i></span></a>"; ?>
										</td>
										<td><?php echo $row['FullName']; ?></td>
										<td><?php echo $ModuleName; ?></td>
										<td><?php echo $Active; ?></td>
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
<!-- Modal for adding new WorkFlowStep -->
<div class="modal fade" id="createWorkFlowModal" tabindex="-1" role="dialog" aria-labelledby="createWorkFlowModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title font-weight-normal" id="createWorkFlowModalLabel"><?php echo _("Create") ?></h6>
				<button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p class="statusMsg"></p>
				<form method='post'>
					<div class="form-group">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="input-group input-group-static mb-4">
								<label for="modalCreateWorkFlowName"><?php echo _("Name") ?></label>
								<input type="text" class="form-control" id="modalCreateWorkFlowName">
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
							<label for="modalCreateResponsible" data-bs-toggle="tooltip" data-bs-title="This is the responsible for the workflow, remember to add relevant people to group: workflow administrators"><?php echo _("Responsible") ?></label>
							<select id="modalCreateResponsible" name="modalCreateResponsible" class="form-control" required>
								<?php
								$sql = "SELECT ID, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
									FROM users
									WHERE RelatedUserTypeID !=2 AND users.ID IN (SELECT usersgroups.UserID FROM usersgroups WHERE usersgroups.GroupID = 100023)
									AND Active = 1";
								$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
								while ($row = mysqli_fetch_array($result)) {
									$ID = $row['ID'];
									$FullName = $row['FullName'];
									if ($ResponsibleUserIDVal == $row['ID']) {
										echo "<option value='$ID' selected='select'>$FullName</option>";
									} else {
										echo "<option value='$ID'>$FullName</option>";
									}
								}
								?>
							</select>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalCreateRelatedModule"><?php echo _("Related Module") ?></label>
							<select id="modalCreateRelatedModule" name="modalCreateRelatedModule" class="form-control" required>
								<?php
								$sql = "SELECT ID, Name
									FROM itsm_modules
									WHERE Active = 1
									UNION
									SELECT ID, ShortElementName AS Name
									FROM modules
									WHERE ID IN (6,13)										
									ORDER BY Name ASC";
								$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
								while ($row = mysqli_fetch_array($result)) {
									$ID = _($row['ID']);
									$ModuleName = _($row['Name']);
									if ($row['ID'] == 1) {
										echo "<option value='$ID' selected='select'>$ModuleName</option>";
									} else {
										echo "<option value='$ID'>$ModuleName</option>";
									}
								}
								?>
							</select>
						</div>
					</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-success float-end" onclick="submitNewWorkFlow()"><?php echo _("Create"); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End Modal for adding new WorkFlowStep -->

<!-- Modal for edit WorkFlowStep -->
<div class="modal fade" id="editWorkFlowModal" tabindex="-1" role="dialog" aria-labelledby="editWorkFlowModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title font-weight-normal" id="editWorkFlowModalLabel"><?php echo _("Edit WorkFlow") ?></h6>
				<button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p class="statusMsg"></p>
				<div id="WFID" hidden></div>
				<form method='post'>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalEditWorkFlowName"><?php echo _("WorkFlow Name") ?></label>
							<input type="text" class="form-control" id="modalEditWorkFlowName">
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalEditWorkFlowDescription"><?php echo _("Description") ?></label>
							<textarea type="text" class="form-control" id="modalEditWorkFlowDescription" rows="10"></textarea>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalEditWorkFlowResponsible"><?php echo _("Responsible") ?></label>
							<select id="modalEditWorkFlowResponsible" name="modalEditWorkFlowResponsible" class="form-control" required>
								<?php
								$sql = "SELECT ID, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
										FROM users
										WHERE RelatedUserTypeID !=2 AND users.ID IN (SELECT usersgroups.UserID FROM usersgroups WHERE usersgroups.GroupID = 100023 OR usersgroups.GroupID = 100001)
										AND Active = 1";

								$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
								while ($row = mysqli_fetch_array($result)) {
									$ID = $row['ID'];
									$FullName = $row['FullName'];
									if ($ResponsibleUserIDVal == $row['ID']) {
										echo "<option value='$ID' selected='select'>$FullName</option>";
									} else {
										echo "<option value='$ID'>$FullName</option>";
									}
								}
								?>
							</select>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalEditWorkFlowRelatedModule"><?php echo _("Module") ?></label>
							<select id="modalEditWorkFlowRelatedModule" name="modalEditWorkFlowRelatedModule" class="form-control" required>
								<?php
								$sql = "SELECT ID, Name
										FROM itsm_modules
										WHERE Active = 1
										UNION
										SELECT ID, ShortElementName AS Name
										FROM modules
										WHERE ID IN (6,13)										
										ORDER BY Name ASC";
								$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
								while ($row = mysqli_fetch_array($result)) {
									$ID = $row['ID'];
									$Name = _($row['Name']);
									if ($row['ID'] == $RelatedModuleID) {
										echo "<option value='$ID' selected='select'>$Name</option>";
									} else {
										echo "<option value='$ID'>$Name</option>";
									}
								}
								?>
							</select>
						</div>
					</div>

					<div class="col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalEditWorkFlowActive"><?php echo _("Active"); ?></label>

							<select class='form-control' id="modalEditWorkFlowActive" name="modalEditWorkFlowActive">
								<option value='0'><?php echo _("Not active") ?></option>
								<option value='1'><?php echo _("Active") ?></option>
							</select>
						</div>
					</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success float-end" onclick="submitWorkFlowChanges()"><?php echo _("Submit"); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- End Modal for edit new WorkFlowStep -->
<?php include("./footer.php") ?>