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
	$(document).ready(function() {
		const data = "";
		let ITSMTypeID = <?php echo $ITSMTypeID ?>;

		vData = {
			ITSMTypeID: ITSMTypeID
		};

		$.ajax({
			type: "POST",
			url: "./getdata.php?getITSMDatarowsAllClosed",
			data: vData,
			dataType: 'JSON',
			success: function(data) {
				var rowData = data[0]; // Get first row data to build columns from.
				if (rowData) {
					// Itereate rowData's object keys to build column definition.
					Object.keys(rowData).forEach(function(key, index) {
						var newkey = key.replace('ImAPlaceHolder', ' ');
						columns.push({
							data: newkey,
							title: newkey
						});
					});

					var table = $('#itsmtableallclosed').DataTable({
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
							"info": '_START_ <?php echo _("to"); ?> _END_ <?php echo _("of"); ?> _TOTAL_ <?php echo _("Total"); ?>',
							"searchPlaceholder": '<?php echo _("Search"); ?>',
							"search": '',
						},
						"bLengthChange": true,
						"displayLength": 25,
						"searching": true,
						"stateSave": false,

						"buttons": [{
								extend: 'colvis',
								columns: ':not(.noVis)'
							},
							['copy', 'excel'],
						],
						data: data,
						columns: columns,

						initComplete: function() {
							$('#itsmtableallclosed thead th').each(function() {
								var title = $(this).text();
								$(this).html('<h6>' + title + '</h6><input type=\"search\" class=\"form-control-dt\" placeholder=\"\"/>');
							});
						},
					});

					table.columns().every(function() {

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
		columns = [];
	});
</script>

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header">
					<?php $ModuleIconName = getITSMModuleTypeIcon($ITSMTypeID); ?>
					<i class="<?php echo $ModuleIconName ?>"></i> <a href="javascript:location.reload(true);"><?php echo _("$ITSMName"); ?></a>
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
					echo $NavMenu01 = navMenu01($ITSMTypeID);
					?>
					<div class="float-end">
						<ul class="navbar-nav justify-content-end" title="<?php echo _("Search"); ?>">
							<li class="nav-item dropdown pe-2">
								<a href="./itsm_search.php?id=<?php echo $ITSMTypeID ?>" class="nav-link text-body p-0 position-relative" aria-expanded="false">
									&nbsp;&nbsp;<i class="fa fa-magnifying-glass fas-dark"></i>&nbsp;&nbsp;
								</a>
							</li>
						</ul>
					</div>
					<div class="float-end">
						<ul class="navbar-nav justify-content-end" title="<?php echo _("Dispatch"); ?>">
							<li class="nav-item dropdown pe-2">
								<a href="./itsm_dispatch.php?id=<?php echo $ITSMTypeID ?>" class="nav-link text-body p-0 position-relative" aria-expanded="false">
									&nbsp;&nbsp;<i class="fa fa-route fas-dark"></i>&nbsp;&nbsp;
								</a>
							</li>
						</ul>
					</div>
					<div class="float-end">
						<ul class="navbar-nav justify-content-end">
							<li class="nav-item dropdown pe-2">
								<a href="javascript:void(0);" onclick="runModalCreateITSM('<?php echo $ITSMTypeID ?>','<?php echo $UserLanguageCode ?>');" class="nav-link text-body p-0 position-relative" aria-expanded="false">
									&nbsp;&nbsp;<i class="far far-dark fa-plus-square"></i>&nbsp;&nbsp;
								</a>
							</li>
						</ul>
					</div>
				</div>

				<div class="card-body">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="tab-pane fade show active" id="My" role="tabpanel" aria-labelledby="My">
							<table id="itsmtableallclosed" class="table table-borderless table-responsive table-hover" cellspacing="0">
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php include("./footer.php"); ?>