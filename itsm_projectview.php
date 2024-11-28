<?php include("./header.php"); ?>
<?php

$ITSMTypeID = "1";
if (!empty($_GET['id'])) {
	$ITSMTypeID = $_GET['id'];
}

$sql = "SELECT itsm_modules.ID, itsm_modules.Name
		FROM itsm_modules
		WHERE itsm_modules.ID = $ITSMTypeID";
$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
	$ITSMName = $row["Name"];
}

$ITSMTableName = $functions->getITSMTableName($ITSMTypeID);
?>

<script>
	let ITSMTypeID = <?php echo $ITSMTypeID ?>;
	$(document).ready(function() {
		vData = {
			ITSMTypeID: ITSMTypeID
		};
		var columnsMy = [];
		$.ajax({
			type: "POST",
			url: "./getdata.php?getITSMDatarowsMy",
			data: vData,
			dataType: 'JSON',
			success: function(data) {
				console.log(data);

				var rowData = data[0]; // Get first row data to build columns from.
				if (rowData) {
					// Itereate rowData's object keys to build column definition.
					Object.keys(rowData).forEach(function(key, index) {
						var newkey = key.replace('ImAPlaceHolder', ' ');
						columnsMy.push({
							data: newkey,
							title: newkey
						});
					});

					var tablemy = $('#itsmtablemy').DataTable({
						"dom": 'Bfrtip',
						"Filter": true,
						"paging": true,
						"info": false,
						"pagingType": 'numbers',
						"processing": true,
						"deferRender": true,
						"pageLength": 25,
						"orderCellsTop": true,
						"fixedHeader": false,
						"autoWidth": false,
						"aaSorting": [],
						"responsive": true,
						"Sort": true,
						"ordering": true,
						"language": {
							url: './assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
						},
						"bLengthChange": true,
						"displayLength": 25,
						"searching": true,
						"stateSave": false,
						"buttons": ['copy', 'excel', 'csv'],
						"data": data,
						"columns": columnsMy,
						initComplete: function() {

						},
					});
					tablemy.columns().every(function() {

						var that = this;

						$('input', this.header()).on('keyup change', function() {
							if (that.search() !== this.value) {
								that
									.search(this.value)
									.draw();
							}
						});
					});
				}
			}
		});
		columnsMyTeams = [];
		$.ajax({
			type: "POST",
			url: "./getdata.php?getITSMDatarowsMyTeams",
			data: vData,
			dataType: 'JSON',
			success: function(dataMyTeams) {
				var rowData = dataMyTeams[0]; // Get first row data to build columns from.
				if (rowData) {
					// Itereate rowData's object keys to build column definition.
					Object.keys(rowData).forEach(function(key, index) {
						var newkey = key.replace('ImAPlaceHolder', ' ');
						columnsMyTeams.push({
							data: newkey,
							title: newkey
						});
					});

					var tableteams = $('#itsmtablemyteams').DataTable({
						"dom": 'Bfrtip',
						"bFilter": true,
						"paging": true,
						"info": false,
						"pagingType": 'numbers',
						"processing": true,
						"deferRender": true,
						"pageLength": 25,
						"orderCellsTop": true,
						"fixedHeader": false,
						"autoWidth": false,
						"aaSorting": [],
						"responsive": true,
						"bSort": true,
						"ordering": true,
						"language": {
							url: './assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
						},
						"bLengthChange": true,
						"displayLength": 25,
						"searching": true,
						"stateSave": false,
						"buttons": ['copy', 'excel'],
						data: dataMyTeams,
						columns: columnsMyTeams,

						initComplete: function() {
							$('#itsmtablemyteams thead th').each(function() {
								var title = $(this).text();
								$(this).html('<h6>' + title + '</h6><input type=\"search\" class=\"form-control-dt\" placeholder=\"\"/>');
							});
						},
					});

					tableteams.columns().every(function() {

						var that = this;

						$('input', this.header()).on('keyup change', function() {
							if (that.search() !== this.value) {
								that
									.search(this.value)
									.draw();
							}
						});
					});
				}
			}
		});
		columnsMeParticipating = [];
		$.ajax({
			type: "POST",
			url: "./getdata.php?getITSMDatarowsMeParticipating",
			data: vData,
			dataType: 'JSON',
			success: function(dataMeParticipating) {
				var rowData = dataMeParticipating[0]; // Get first row data to build columns from.
				if (rowData) {
					// Itereate rowData's object keys to build column definition.
					Object.keys(rowData).forEach(function(key, index) {
						var newkey = key.replace('ImAPlaceHolder', ' ');
						columnsMeParticipating.push({
							data: newkey,
							title: newkey
						});
					});

					var tableparticipating = $('#itsmtablemeparticipating').DataTable({
						"dom": 'Bfrtip',
						"bFilter": true,
						"paging": true,
						"info": false,
						"pagingType": 'numbers',
						"processing": true,
						"deferRender": true,
						"pageLength": 25,
						"orderCellsTop": true,
						"fixedHeader": false,
						"autoWidth": false,
						"aaSorting": [],
						"responsive": true,
						"bSort": true,
						"ordering": true,
						"language": {
							url: './assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
						},
						"bLengthChange": true,
						"displayLength": 25,
						"searching": true,
						"stateSave": false,
						"buttons": ['copy', 'excel'],
						data: dataMeParticipating,
						columns: columnsMeParticipating,

						initComplete: function() {
							$('#itsmtablemeparticipating thead th').each(function() {
								var title = $(this).text();
								$(this).html('<h6>' + title + '</h6><input type=\"search\" class=\"form-control-dt\" placeholder=\"\"/>');
							});
						},
					});

					tableparticipating.columns().every(function() {

						var that = this;

						$('input', this.header()).on('keyup change', function() {
							if (that.search() !== this.value) {
								that
									.search(this.value)
									.draw();
							}
						});
					});
				}
			}
		});
	});
</script>

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header">
					<?php $ModuleIconName = getITSMModuleTypeIcon($ITSMTypeID); ?>
					<i class="<?php echo $ModuleIconName ?>"></i> <a href="javascript:location.reload(true);"><?php echo _($ITSMName); ?></a>
					<div class="float-end">
						<ul class="navbar-nav justify-content-end">
							<li class="nav-item dropdown pe-2">
								<a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
									&nbsp;&nbsp;<i class="fa fa-circle-chevron-down"></i>&nbsp;&nbsp;
								</a>
								<ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
									<?php
									$BSArray = getITSMsForOverview();

									foreach ($BSArray as $key => $value) {
										$Name = $value["Name"];
										$MenuPage = $value["MenuPage"];
										echo "<li class=\"mb-2\">
												<a class=\"dropdown-item border-radius-md\" onclick=\"window.location.href='$MenuPage" . $value['ID'] . "'\">
													<div class=\"d-flex align-items-center py-1\">
														<div class=\"ms-2\">
															<h6 class=\"text-sm font-weight-normal my-auto\">
																$Name
															</h6>
														</div>
													</div>
												</a>
											</li>";
									}
									?>
								</ul>
							</li>
						</ul>
					</div>
					<?php
					if (in_array("100001", $group_array) || in_array("100015", $group_array)) {
						echo $NavMenu01 = navMenu01($ITSMTypeID);
					} ?>
				</div>

				<div class="card-body">

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<ul class="nav nav-tabs nav-fill mb-3" role="tablist" id="activetablist">
							<li class="nav-item">
								<a class="nav-link active" data-bs-toggle="tab" data-bs-target="#My" href="#My">
									<?php echo _("My"); ?>
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-bs-toggle="tab" data-bs-target="#MyTeams" href="#MyTeams">
									<?php echo _("My teams"); ?>
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-bs-toggle="tab" data-bs-target="#MeParticipating" href="#MeParticipating">
									<?php echo _("Me participating"); ?>
								</a>
							</li>
						</ul>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="tab-content" id="ITSMTabContent">
							<div class="tab-pane fade show active" id="My" role="tabpanel" aria-labelledby="My">
								<table id="itsmtablemy" class="table table-borderless table-responsive table-hover" cellspacing="0">
								</table>
							</div>
							<div class="tab-pane fade" id="MyTeams" role="tabpanel" aria-labelledby="MyTeams">
								<table id="itsmtablemyteams" class="table table-borderless table-responsive table-hover" cellspacing="0">
								</table>
							</div>
							<div class="tab-pane fade" id="MeParticipating" role="tabpanel" aria-labelledby="MeParticipating">
								<table id="itsmtablemeparticipating" class="table table-borderless table-responsive table-hover" cellspacing="0">
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include("./footer.php"); ?>