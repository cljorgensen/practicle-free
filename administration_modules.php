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

	function toggleVisibility(divId) {
		var element = document.getElementById(divId);
		if (element) {
			element.hidden = !element.hidden;
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
							<a href="#" class="btn btn-sm bg-gradient-secondary dropup dropdown-toggle" data-bs-toggle="dropdown" id="navbarDropdownMenuLink2">
								<?php echo _("Actions"); ?>
							</a>
							<ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink2">
								<li>
									<a class="dropdown-item" href="javascript:toggleVisibility('createNew');">
										<?php echo _("Create"); ?>
									</a>
								</li>
								<?php
									if (in_array("100000", $group_array)) {?>
									<li>
										<a class='dropdown-item' onclick="resetAllITSMModulesData();">
											<?php echo _('Reset all modules data') ?>
										</a>
									</li>
								<?php
								}
								?>
								<li>
									<a class="dropdown-item" href="javascript:resetITSMModules();">
										<?php echo _("Reset all modules to standard"); ?>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>

				<div class="card-body">
					<div id="createNew" hidden>
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
								<?php
								if (in_array("100031", $group_array) || in_array("100001", $group_array)) {
									echo "<a href=\"javascript:createITSM();\"><button class='btn btn-sm btn-success float-end'>" . _("Create") . "</button></a>";
									echo "<a href=\"javascript:;\" onclick=\"toggleVisibility('createNew');\"><button class='btn btn-sm btn-secondary float-end' id=\"minus\">" . _("Cancel") . "</button></a>";
								} else {
								}

								?>

							</div>
						</div>
						<br><br>
					</div>
					<table id="TableITSM" class="table table-responsive table-borderless table-hover" cellspacing="0">
						<thead>
							<tr>
								<th><?php echo _("Name"); ?></th>
								<th></th>
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
									LEFT JOIN users ON itsm_modules.CreatedBy = users.ID
									WHERE itsm_modules.Active IN (0,1)
									ORDER BY itsm_modules.Name ASC";
						$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

						while ($row = mysqli_fetch_assoc($result)) {
							$row['Name'] = $functions->translate($row['Name']);
							$row['CreatedByFullName'] = $functions->getUserFullName($row['CreatedBy']);
							$row['LastEditedByFullName'] = $functions->getUserFullName($row['LastEditedBy']);
							$row['LastEditedFormatted'] = empty($row['LastEdited']) ? NULL : convertToDanishTimeFormat($row['LastEdited']);
							$row['CreatedFormatted'] = empty($row['Created']) ? NULL : convertToDanishTimeFormat($row['Created']);
							$tempArray[] = $row;
						}

						// Sort the array by the 'Name' column
						usort($tempArray, function ($a, $b) {
							return strcmp($a['Name'], $b['Name']);
						});
						?>
						<tbody>
							<?php foreach ($tempArray as $row) { ?>
								<tr>
									<?php
									$ITSMTypeID = $row['ID'];
									$ITSMName = $row['Name'];
									$TableName = $row['TableName'];
									$Description = $row['Description'];
									?>
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
									<td><?php echo "<div title='$ITSMTypeID'>" . _($ITSMName) . "</div>"; ?></td>
									<td><?php
										if (in_array("100031", $group_array) || in_array("100001", $group_array)) {


											echo "<a href='administration_modules_edit.php?id=$ITSMTypeID'><span class='badge bg-gradient-success' data-bs-toggle='tooltip' data-bs-title='" . _("Open") . "'><i class='fa fa-folder-open'></i></span></a>";
											//echo "<a href='administration_modules_form_view.php?id=$ITSMTypeID'><span class='badge bg-gradient-info' data-bs-toggle="tooltip" data-bs-title='" . _("Preview") . "'><i class='fa fa-search-plus'></i></span></a>";
											echo "<a href=\"javascript:;\"><span class=\"badge bg-gradient-danger\" onclick=\"deleteITSM($ITSMTypeID);\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Delete") . "\"><i class='fa fa-trash'></i></span></a>";
											echo "<a href=\"javascript:;\"><span class=\"badge bg-gradient-info\" onclick=\"duplicateITSM($ITSMTypeID);\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Duplicate") . "\"><i class='fas fa-clone'></i></span></a>";
										} else {
										}
										?>
									</td>
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