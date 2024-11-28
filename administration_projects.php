<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>

<script>
	$(document).ready(function() {
		<?php initiateStandardSearchTable("TableITSM"); ?>
	});

	function showCreateNewDiv() {
		element = document.getElementById('createNew');
		elementplus = document.getElementById('plus');
		elementminus = document.getElementById('minus');

		if ($('#createNew').css('display') === 'block') {
			element.style.display = 'none';
			elementplus.style.display = '';
			elementminus.style.display = 'none';
			let createEntry = document.getElementById('createEntry');
			createEntry.removeAttribute("hidden");
			let updateEntry = document.getElementById('updateEntry');
			updateEntry.setAttribute("hidden", true);
		} else {
			element.style.display = 'block';
			elementplus.style.display = 'none';
			elementminus.style.display = '';
			document.getElementById('FieldFormID').value = "";
			document.getElementById('ITSMName').value = "";
			document.getElementById('ITSMDescription').value = "";
			let FieldFormID = document.getElementById('FieldFormID');
			FieldFormID.setAttribute("hidden", true);
			let createEntry = document.getElementById('createEntry');
			createEntry.removeAttribute("hidden");
			let updateEntry = document.getElementById('updateEntry');
			updateEntry.setAttribute("hidden", true);
		}
	}
</script>
<!-- page content -->
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header"><i class="fa-solid fa-gear"></i> <a href="javascript:location.reload(true);"><?php echo _("Modules"); ?></a>

						<div class="float-end">
							<div class="dropdown">
								<a href="#" class="btn bg-gradient-dark dropdown-toggle " data-bs-toggle="dropdown" id="navbarDropdownMenuLink2">
									<?php echo _("Actions"); ?>
								</a>
								<ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink2">
									<li>
										<a class="dropdown-item" href="javascript:showCreateNewDiv();">
											<?php echo _("Create"); ?>
										</a>
									</li>
									<li>
										<a class="dropdown-item" href="javascript:resetITSMModules();">
											<?php echo _("Reset"); ?>
										</a>
									</li>
								</ul>
							</div>

							<a href="javascript:;" onclick="showCreateNewDiv();"><button class='btn btn-sm btn-success' id="minus" style="display: none;"><?php echo _("Cancel"); ?></button></a>
						</div>
				</div>

				<div class="card-body">
					<div id="createNew" style="display: none;">
						<div class="row">

							<div class="input-group input-group-static mb-4">
								<input type="text" class="form-control" id="FieldFormID" disabled="true" hidden>
							</div>

							<div class="col-md-6 col-sm-6 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="ITSMName" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Name") ?>"><?php echo _("Name"); ?></label>
									<input type="text" class="form-control" id="ITSMName">
								</div>
							</div>

							<div class="col-md-6 col-sm-6 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="ITSMDescription"><?php echo _("Description"); ?></label>
									<input type="text" class="form-control" id="ITSMDescription">
								</div>
							</div>

							<div class="col-md-12 col-sm-12 col-xs-12">
								<div id="createEntry" style="display: hidden;">
									<?php
									if (in_array("100031", $group_array) || in_array("100001", $group_array)) {
										echo "<a href=\"javascript:createITSM();\"><button class='btn btn-sm btn-success float-end'>" . _("Create") . "</button></a>";
									} else {
									}
									?>
								</div>
							</div>
						</div>
						<br><br>
					</div>
					<table id="TableITSM" class="table table-responsive table-borderless table-hover" cellspacing="0">
						<thead>
							<tr>
								<th><?php echo _('ID'); ?></th>
								<th></th>
								<th><?php echo _("Name"); ?></th>
								<th><?php echo _("Description"); ?></th>
								<th><?php echo _("Created By"); ?></th>
								<th><?php echo _("Created"); ?></th>
								<th><?php echo _("Last Edited By"); ?></th>
								<th><?php echo _("Last Edited"); ?></th>
								<th><?php echo _("Active"); ?></th>
							</tr>
						</thead>
						<?php
						$sql = "SELECT itsm_modules.ID, itsm_modules.Name, itsm_modules.TableName, itsm_modules.Description, itsm_modules.CreatedBy, itsm_modules.Created, users.Username, itsm_modules.LastEditedBy, itsm_modules.LastEdited, itsm_modules.Active
								FROM itsm_modules
								LEFT JOIN users ON itsm_modules.CreatedBy = users.ID";
						$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
						?>
						<tbody>
							<?php while ($row = mysqli_fetch_array($result)) { ?>
								<tr>
									<?php $ITSMTypeID = $row['ID']; ?>
									<?php $CreatedBy = $functions->getUserFullName($row['CreatedBy']); ?>
									<?php $LastEditedBy = $functions->getUserFullName($row['LastEditedBy']); ?>
									<?php
									if (empty($row['LastEdited'])) {
										$LastEdited = NULL;
									} else {
										$LastEdited = convertToDanishTimeFormat($row['LastEdited']);
									}
									if (empty($row['Created'])) {
										$Created = NULL;
									} else {
										$Created = convertToDanishTimeFormat($row['Created']);
									}
									?>
									<td><?php echo $ITSMTypeID ?></td>
									<td><?php
										if (in_array("100031", $group_array) || in_array("100001", $group_array)) {
											$ID = $row['ID'];
											$ITSMName = $row['Name'];
											$TableName = $row['TableName'];
											$Description = $row['Description'];

											echo "<a href='administration_modules_edit.php?id=$ITSMTypeID'><span class='badge bg-gradient-success' data-bs-toggle='tooltip' data-bs-title='" . _("Open") . "'><i class='fa fa-folder-open'></i></span></a>";
											//echo "<a href='administration_modules_form_view.php?id=$ITSMTypeID'><span class='badge bg-gradient-info' data-bs-toggle='tooltip' data-bs-title='" . _("Preview") . "'><i class='fa fa-search-plus'></i></span></a>";
											echo "<a href=\"javascript:;\"><span class=\"badge bg-gradient-danger\" onclick=\"deleteITSM($ITSMTypeID);\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Delete") . "\"><i class='fa fa-trash'></i></span></a>";
											echo "<a href=\"javascript:;\"><span class=\"badge bg-gradient-info\" onclick=\"duplicateITSM($ITSMTypeID);\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Duplicate") . "\"><i class='fas fa-clone'></i></span></a>";
										} else {
										}
										?>
									</td>
									<td><?php echo $row['Name']; ?></td>
									<td><?php echo $row['Description']; ?></td>
									<td><?php echo $CreatedBy ?></td>
									<td><?php echo $Created ?></td>
									<td><?php echo $LastEditedBy ?></td>
									<td><?php echo $LastEdited ?></td>
									<td><?php echo $row['Active']; ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>

			</div>
		</div>
	</div>

	<!-- /page content -->
	<?php include("./footer.php"); ?>