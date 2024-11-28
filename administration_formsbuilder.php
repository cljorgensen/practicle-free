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
		<?php initiateStandardSearchTable("TableForms"); ?>
	});

	function toggleVisibility(divId) {
		var element = document.getElementById(divId);
		if (element) {
			element.hidden = !element.hidden;
		}
	}

	function createForm() {
		FormsName = document.getElementById('FormsName').value;
		FormDescription = document.getElementById('FormDescription').value;

		url = "getdata.php?createForm=" + "&FormsName=" +
			FormsName + "&FormDescription=" + FormDescription;

		var xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				pnotify('Opdateret', 'success');
				window.location.reload(true);
			}
		};
		xhttp.open("GET", url, true);
		xhttp.send();
	}

	function updateForm() {
		FormID = document.getElementById('FieldFormID').value;
		FormDescription = document.getElementById('FormDescription').value;
		FormsName = document.getElementById('FormsName').value;
		DBTableName = FormsName.replace(/\s+/g, '');
		DBTableName = FormsName.replace(/[^a-zA-Z0-9 ]/g, "");
		var length = 50;
		var trimmedDBTableName = DBTableName.substring(0, length);

		xmlhttp.open("GET", "getdata.php?updateForm=" + "&FormsName=" + FormsName + "&trimmedDBTableName=" +
			trimmedDBTableName + "&FormDescription=" + FormDescription + "&FormID=" + FormID, true);
		xmlhttp.send();
		//location.reload(true);
		pnotify('Forme opdateret', 'success');
	}

	function editForm(FormID, FormsName, FormDescription) {
		showCreateNewDiv();
		let FieldFormID = document.getElementById('FieldFormID');
		FieldFormID.removeAttribute("hidden");
		let updateEntry = document.getElementById('updateEntry');
		updateEntry.removeAttribute("hidden");
		let createEntry = document.getElementById('createEntry');
		createEntry.setAttribute("hidden", true);

		document.getElementById('FieldFormID').value = FormID;
		document.getElementById('FormsName').value = FormsName;
		document.getElementById('FormDescription').value = FormDescription;

		element = document.getElementById('createNew');
		elementplus = document.getElementById('plus');
		elementminus = document.getElementById('minus');

		element.style.display = 'block';
		elementplus.style.display = 'none';
		elementminus.style.display = '';
	}
	
</script>
<!-- page content -->
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header"><i class="fas fa-check-square"></i> <a href="javascript:location.reload(true);"><?php echo _("Forms"); ?></a>
					<div class="float-end">
						<div class="dropdown">
							<a href="#" class="btn btn-sm bg-gradient-secondary dropup dropdown-toggle" data-bs-toggle="dropdown" id="navbarDropdownMenuLink2">
								<?php echo _("Actions"); ?>
							</a>
							<ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink2">
								<li><a class="dropdown-item" href="javascript:toggleVisibility('createNew');"><?php echo _("Create") ?></a></li>
								<li><a class="dropdown-item" href="javascript:toggleVisibility('formTemplatesDiv');"><?php echo _("Load Templates") ?></a></li>
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
									<label for="FormsName" data-bs-toggle='tooltip' data-bs-title="<?php echo _("Form name") ?>"><?php echo _("Name"); ?></label>
									<input type="text" class="form-control" id="FormsName">
								</div>
							</div>

							<div class="col-md-6 col-sm-6 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="FormDescription"><?php echo _("Description"); ?></label>
									<input type="text" class="form-control" id="FormDescription">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<div id="updateEntry" style="display: hidden;">
									<?php
									if (in_array("100029", $group_array) || in_array("100001", $group_array)) {
										echo "<a href=\"javascript:createForm();\"><button class='btn btn-sm btn-success float-end'>" . _("Create") . "</button></a>";
									}
									?>
									<?php
									if (in_array("100029", $group_array) || in_array("100001", $group_array)) {
										echo "<a href=\"javascript:toggleVisibility('createNew');\"><button class='btn btn-sm btn-secondary float-end'>" . _("Cancel") . "</button></a>";
									}
									?>
								</div>
							</div>
						</div>
						<br><br>
					</div>
					<div id="formTemplatesDiv" hidden>
						<div class="row">
							<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="formTemplates"><?php echo _("Templates"); ?></label>
									<select class="form-control" id="formTemplates" name="formTemplates">
										<?php
										$sql = "SELECT ID, module_templates.Name
												FROM module_templates
												WHERE module_templates.Type = 'Form'";

										$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

										while ($row = mysqli_fetch_array($result)) {
											$TemplateID = $row["ID"];
											$TemplateName = $row["Name"];
											echo "<option value='$TemplateID'>$TemplateName</option>";
										}
										?>
									</select>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
								<button class='btn btn-sm btn-success float-end' onclick="createFormsTemplate();"><?php echo _("Create") ?></button>
								<button class='btn btn-sm btn-secondary float-end' onclick="toggleVisibility('formTemplatesDiv');"><?php echo _("Cancel") ?></button>
							</div>
						</div>
						<br><br>
					</div>
					<table id="TableForms" class="table table-responsive table-borderless table-hover" cellspacing="0">
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
								<th><?php echo _("Module"); ?></th>
								<th><?php echo _("Active"); ?></th>
							</tr>
						</thead>
						<?php
						$sql = "SELECT forms.ID, forms.FormsName, forms.TableName, forms.Description, forms.CreatedBy, forms.Created, users.Username, forms.LastEditedBy, forms.LastEdited, modules.Name, forms.Active
								FROM forms
								INNER JOIN users ON forms.CreatedBy = users.ID
								LEFT JOIN modules ON forms.RelatedModuleID = modules.ID
								WHERE forms.Status = 1";
						$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
						?>
						<tbody>
							<?php while ($row = mysqli_fetch_array($result)) { ?>
								<tr>
									<?php $FormID = $row['ID']; ?>
									<?php $CreatedBy = $functions->getUserFullName($row['CreatedBy']); ?>
									<?php $LastEditedBy = $functions->getUserFullName($row['LastEditedBy']); ?>
									<?php $ModuleName = _($row['Name']); ?>
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
									<td><?php echo $FormID ?></td>
									<td><?php
										if (in_array("100029", $group_array) || in_array("100001", $group_array)) {
											$FormsName = $row['FormsName'];
											$TableName = $row['TableName'];
											$Description = $row['Description'];

											echo "<a href='administration_formsbuilder_edit.php?formid=$FormID'><span class='badge bg-gradient-success' data-bs-toggle='tooltip' data-bs-title='" . _("Open") . "'><i class='fa fa-folder-open'></i></span></a>";
											echo "<a href='administration_formview.php?formid=$FormID'><span class='badge bg-gradient-info' data-bs-toggle='tooltip' data-bs-title='" . _("Preview") . "'><i class='fa fa-search-plus'></i></span></a>";
											echo "<a href=\"javascript:;\"><span class=\"badge bg-gradient-danger\" onclick=\"deleteForm($FormID);\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Delete") . "\"><i class='fa fa-trash'></i></span></a>";
											echo "<a href=\"javascript:;\"><span class=\"badge bg-gradient-info\" onclick=\"duplicateForm(" . $FormID . ");\" data-bs-toggle='tooltip' data-bs-title=\"" . _("Duplicate") . "\"><i class='fas fa-clone'></i></span></a>";
										} else {
										}
										?>
									</td>
									<td><?php echo $row['FormsName']; ?></td>
									<td><?php echo $row['Description']; ?></td>
									<td><?php echo $CreatedBy ?></td>
									<td><?php echo $Created ?></td>
									<td><?php echo $LastEditedBy ?></td>
									<td><?php echo $LastEdited ?></td>
									<td><?php echo $ModuleName ?></td>
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