<!-- Start settings list -->
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>

<script>
	$(document).ready(function() {
		<?php initiateSimpleViewTable("TableSettings", 25, []); ?>
	});
</script>

<script>
	function editSetting(settingid) {

		var url = './getdata.php?popModalEditSettings=' + settingid;
		$("#editSettingsEditModal").modal('show')
		$.ajax({
			url: url,
			data: {
				data: settingid
			},
			type: 'POST',
			success: function(data) {
				var obj = JSON.parse(data);
				for (var i = 0; i < obj.length; i++) {

					document.getElementById('modalSettingID').value = obj[i].ID;
					document.getElementById('modalSettingName').value = obj[i].SettingName;
					document.getElementById('modalSettingDesciption').value = obj[i].SettingDescription;
					document.getElementById('modalSettingValue').value = obj[i].SettingValue;
					document.getElementById('modalSettingType').value = obj[i].SettingsTypeID;
					document.getElementById('modalSettingActive').value = obj[i].Active;
				}
			}
		});
	}
</script>

<!-- the table view -->
<table id="TableSettings" class="table table-borderless table-responsive table-hover wrap" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo _("ID"); ?></th>
			<th></th>
			<th><?php echo _("Name"); ?></th>
			<th><?php echo _("Value"); ?></th>
			<th><?php echo _("Type"); ?></th>
			<th><?php echo _("Active"); ?></th>
		</tr>
	</thead>
	<?php
	$sql = "SELECT Settings.ID, SettingName, SettingDescription, SettingValue, SettingsTypes.TypeName, Settings.Active FROM Settings 
			INNER JOIN SettingsTypes ON Settings.SettingsTypeID = SettingsTypes.ID
			WHERE settings.SettingsTypeID !='6'
			ORDER BY settings.ID ASC";

	$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

	?>
	<tbody>
		<?php while ($row = mysqli_fetch_array($result)) { ?>
			<?php $SettingID = $row['ID'] ?>
			<tr class='text-sm text-secondary mb-0'>
				<td><?php echo $SettingID; ?></td>
				<td><?php echo "<a href='javascript:void(0);" . $row['ID'] . "'><span class='badge badge-pill bg-gradient-success' onclick='editSetting($SettingID);'><i class='fa fa-pen-to-square'></i></span></a>"; ?>
				</td>
				<td data-bs-toggle="tooltip" data-bs-title="<?php echo $row[2] ?>"><?php echo $row[1]; ?></td>
				<td><?php echo $row[3]; ?></td>
				<td><?php echo $row[4]; ?></td>
				<td><?php echo $row[5]; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>

<!-- Script for modal popup change settings -->
<script>
	function submitSettingChange() {
		var settingid = $('#modalSettingID').val();
		var settingname = $('#modalSettingName').val();
		var settingdescription = $('#modalSettingDesciption').val();
		var settingvalue = $('#modalSettingValue').val();
		var settingtype = $('#modalSettingType').val();
		var active = $('#modalSettingActive').val();

		$.ajax({
			type: 'POST',
			url: './getdata.php',
			data: 'changeSettingDetails=1&settingid=' + settingid + '&settingname=' + settingname + '&settingdescription=' + settingdescription + '&settingvalue=' + settingvalue + '&settingtype=' + settingtype + '&active=' + active,
			beforeSend: function() {
				$('.submitBtn').attr("disabled", "disabled");
				$('.modal-body').css('opacity', '.5');
			},
			success: function(data) {
				$("#editSettingsEditModal").modal('hide');
				$('.submitBtn').removeAttr("disabled");
				$('.modal-body').css('opacity', '');
			}
		});
	}
</script>
<!-- Modal for edit settings -->
<div class="modal fade" id="editSettingsEditModal" tabindex="-1" role="dialog" aria-labelledby="editSettingsEditModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title font-weight-normal" id="editSettingsEditModalLabel"><?php echo _('Edit'); ?></h6>
				<button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p class="statusMsg"></p>
				<form role="form">
					<div class="form-group">
						<div class="col-md-4 col-sm-4 col-xs-12">
							<div class="input-group input-group-static mb-4">
								<label for="modalSettingID"><?php echo _("ID"); ?></label>
								<input type="text" class="form-control" id="modalSettingID" disabled>
							</div>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalSettingName"><?php echo _("Name"); ?></label>
							<input type="text" class="form-control" id="modalSettingName" disabled>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalSettingDesciption"><?php echo _("Description"); ?></label>
							<textarea type="text" class="form-control" id="modalSettingDesciption" rows="10" disabled></textarea>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalSettingValue"><?php echo _("Value"); ?></label>
							<input type="text" class="form-control" id="modalSettingValue">
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalSettingType"><?php echo _("Type"); ?></label>
							<select id="modalSettingType" name="modalSettingType" class="form-control" disabled>
								<?php
								try {
									// Define the query
									$sql = "SELECT ID, TypeName FROM settingstypes";

									// Use selectQuery to execute the query
									$result = $functions->selectQuery($sql, []);

									// Check if result is not empty
									if (!empty($result)) {
										foreach ($result as $row) {
											$SettingsTypeID = $row['ID'];
											$TypeName = htmlspecialchars($row['TypeName'], ENT_QUOTES, 'UTF-8'); // Sanitize output
											echo "<option value='$SettingsTypeID'>$TypeName</option>";
										}
									} else {
										echo "<option value=''>No settings types found</option>";
									}
								} catch (Exception $e) {
									// Log the error
									$functions->errorlog("Error in fetching settings types: " . $e->getMessage(), "Settings Types Dropdown");
									echo "<option value=''>Error loading settings types</option>";
								}
								?>

							</select>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalSettingActive"><?php echo _("Active"); ?></label>
							<select id="modalSettingActive" name="modalSettingActive" class="form-control">
								<option value='1' selected='select'><?php echo _("Active"); ?></option>
								<option value='0'><?php echo _("Inactive"); ?></option>
							</select>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-success" onclick="submitSettingChange()"><?php echo _("Save"); ?></button>
			</div>
		</div>
	</div>
</div>
<!-- End Modal for edit settings -->