<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>

<script>
	$(document).ready(function() {
		<?php initiateStandardSearchTable("TableCMDB"); ?>
	});

	function showCreateNewDiv() {
		element = document.getElementById('createNew');
		elementplus = document.getElementById('plus');
		elementminus = document.getElementById('minus');

		if ($('#createNew').css('display') === 'block') {
			element.style.display = 'none';
			let createEntry = document.getElementById('createEntry');
			createEntry.removeAttribute("hidden");
		} else {
			element.style.display = 'block';
			document.getElementById('FieldFormID').value = "";
			document.getElementById('CIName').value = "";
			document.getElementById('CIDescription').value = "";
			let FieldFormID = document.getElementById('FieldFormID');
			FieldFormID.setAttribute("hidden", true);
			let createEntry = document.getElementById('createEntry');
			createEntry.removeAttribute("hidden");
		}
	}
</script>
<!-- page content -->
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header"><i class="fa fa-desktop"></i> <?php echo _("Assets") . " " . _("(CMDB)"); ?>
					<div class="float-end">
						<div class="dropdown">
							<a href="#" class="btn btn-sm bg-gradient-secondary dropup dropdown-toggle" data-bs-toggle="dropdown" id="navbarDropdownMenuLink2">
								<?php echo _("Actions"); ?>
							</a>
							<ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink2">
								<li>
									<a class="dropdown-item" href="javascript:createRelations();">
										<?php echo _("Create Relations"); ?>
									</a>
								</li>
								<li>
									<a class="dropdown-item" href="javascript:showCreateNewDiv();">
										<?php echo _("Create"); ?>
									</a>
								</li>
							</ul>
						</div>
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
									<label for="CIName" data-bs-toggle='tooltip' data-bs-title="<?php echo _("CI Name") ?>"><?php echo _("Name"); ?></label>
									<input type="text" class="form-control" id="CIName">
								</div>
							</div>

							<div class="col-md-6 col-sm-6 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="CIDescription"><?php echo _("Description"); ?></label>
									<input type="text" class="form-control" id="CIDescription">
								</div>
							</div>

							<div class="col-md-12 col-sm-12 col-xs-12">
								<div id="createEntry" style="display: hidden;">
									<?php
									if (in_array("100015", $group_array) || in_array("100001", $group_array)) {
										echo "<a href=\"javascript:createCI();\"><button class='btn btn-sm btn-success float-end'>" . _("Create") . "</button></a>";
										echo "<a href=\"javascript:showCreateNewDiv();\"><button class='btn btn-sm btn-secondary float-end'>" . _("Cancel") . "</button></a>";
									} else {
									}
									?>
								</div>
							</div>
						</div>
						<br><br>
					</div>
					<table id="TableCMDB" class="table table-responsive table-borderless table-hover" cellspacing="0">
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
						$sql = "SELECT cmdb_cis.ID, cmdb_cis.Name, cmdb_cis.TableName, cmdb_cis.Description, cmdb_cis.CreatedBy, cmdb_cis.Created, users.Username, cmdb_cis.LastEditedBy, cmdb_cis.LastEdited, cmdb_cis.Active
								FROM cmdb_cis
								LEFT JOIN users ON cmdb_cis.CreatedBy = users.ID";
						$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
						?>
						<tbody>
							<?php while ($row = mysqli_fetch_array($result)) { ?>
								<tr>
									<?php $CIID = $row['ID']; ?>
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
									<td><?php echo $CIID ?></td>
									<td><?php
										if (in_array("100029", $group_array) || in_array("100001", $group_array)) {
											$ID = $row['ID'];
											$CIName = $row['Name'];
											$TableName = $row['TableName'];
											$Description = $row['Description'];

											echo "<a href='administration_cmdb_edit.php?ciid=$CIID'><span class='badge bg-gradient-success' ' data-bs-toggle='tooltip' data-bs-title='" . _("Open") . "'><i class='fa fa-folder-open'></i></span></a>";
											echo "<a href='administration_cmdb_form_view.php?id=$CIID'><span class='badge bg-gradient-info' data-bs-toggle='tooltip' data-bs-title='" . _("Preview") . "'><i class='fa fa-search-plus'></i></span></a>";
											if ($ID <> "1") {
												echo "<a href=\"javascript:;\"><span class=\"badge bg-gradient-danger\" onclick=\"deleteCI($CIID);\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Delete") . "\"><i class='fa fa-trash'></i></span></a>";
											}
											echo "<a href=\"javascript:;\"><span class=\"badge bg-gradient-info\" onclick=\"duplicateCI(" . $CIID . ");\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Duplicate") . "\"><i class='fas fa-clone'></i></span></a>";
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