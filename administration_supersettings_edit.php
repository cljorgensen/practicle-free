<!-- Start settings list -->
<?php
if (in_array("100000", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<script>
	$(document).ready(function() {
		var table = $('#editSettings').DataTable({
			paging: true,
			dom: 'Bfrtip',
			pageLength: 20,
			language: {
				searchPlaceholder: "<?php echo _("Search"); ?>",
				search: "",
			},
			"columnDefs": [{
				"targets": 1,
				"data": null,
				"defaultContent": "<button class='btn btn-sm btn-success'><i class='fa fa-pencil-alt fa-2x'></i></button>"
			}],
			buttons: ['excel']
		});

		$('#editSettings tbody').on('click', 'button', function() {
			var data = table.row($(this).parents('tr')).data();
			settingid = data[0];
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
		});
	});
</script>

<!-- the table view -->
<table id="editSettings" class="table table-responsive table-borderless table-hover" cellspacing="0">
	<thead>
		<tr>
			<th>Setting ID</th>
			<th></th>
			<th>Name</th>
			<th>Description</th>
			<th>Value</th>
			<th>Type</th>
			<th>Active</th>
		</tr>
	</thead>
	<?php
	$sql = "SELECT Settings.ID, SettingName, SettingDescription, SettingValue, SettingsTypes.TypeName, Settings.Active FROM Settings 
              INNER JOIN SettingsTypes ON Settings.SettingsTypeID = SettingsTypes.ID
              WHERE settings.SettingsTypeID ='6'";

	$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

	?>
	<tbody>
		<?php while ($row = mysqli_fetch_array($result)) { ?>
			<tr>
				<td><?php echo $row[0]; ?></td>
				<td><?php echo $row[0]; ?></td>
				<td><?php echo $row[1]; ?></td>
				<td><?php echo $row[2]; ?></td>
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
<div class="modal fade bs-example-modal-sm" id="editSettingsEditModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<!-- Modal Header -->
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">Change setting</h4>
			</div>

			<!-- Modal Body -->
			<div class="modal-body">
				<p class="statusMsg"></p>
				<form role="form">
					<div class="form-group">
						<div class="col-md-4 col-sm-4 col-xs-12">
							<label for="modalSettingID">Setting ID</label>
							<input type="text" class="form-control" id="modalSettingID" readonly>
						</div>
					</div>
					<div class="clearfix"></div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<label for="modalSettingName">Name</label>
						<input type="text" class="form-control" id="modalSettingName" readonly>
					</div>
					<div class="clearfix"></div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<label for="modalSettingDesciption">Description</label>
						<textarea type="text" class="form-control" id="modalSettingDesciption" rows="10" readonly></textarea>
					</div>
					<div class="clearfix"></div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<label for="modalSettingValue">Value</label>
						<input type="text" class="form-control" id="modalSettingValue">
					</div>
					<div class="clearfix"></div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<label for="modalSettingType">Type</label>
						<select id="modalSettingType" name="modalSettingType" class="form-control" disabled="true">
							<?php
							$sql = "SELECT ID, TypeName 
								FROM settingstypes";
							$result = $conn->query($sql) or die('Query fail: ' . mysqli_error());
							while ($row = mysqli_fetch_array($result)) {
								if ($_POST["modalSettingType"] == $row['ID']) {
									echo "<option value='" . $row['ID'] . "' selected='select'>" . $row['TypeName'] . "</option>";
								} else {
									echo "<option value='" . $row['ID'] . "'>" . $row['TypeName'] . "</option>";
								}
							}
							?>
						</select>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<label for="modalSettingActive">Active</label>
						<select id="modalSettingActive" name="modalSettingActive" class="form-control">
							<option value='1' selected='select'>Active</option>
							<option value='0'>Inactive</option>
						</select>
					</div>
				</form>
			</div>
			<!-- Modal Footer -->
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-sm btn-success" onclick="submitSettingChange()">Save</button>
			</div>
		</div>
	</div>
</div>
<div class="clearfix">
	<!-- end of edit settings modal -->