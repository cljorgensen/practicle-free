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
<link rel="stylesheet" href="./assets/js/pivot.css" />
<script src="./assets/js/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js" integrity="sha512-XgJh9jgd6gAHu9PcRBBAp0Hda8Tg87zi09Q2639t0tQpFFQhGpeCgaiEFji36Ozijjx9agZxB0w53edOFGCQ0g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/export_renderers.min.js" integrity="sha512-p5LbrvUKLNYfB4NnF9AUhdzcr2VaLfWxZ65rU8/P1VM06XvwEGNfU9gaXPiJGQh1NCHzzbhpcjIRLiFE8GSnCA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.da.min.js" integrity="sha512-2apOoGq/uB/lQY543BbNLnNkpSeIBrvbHMAE82Vua/19lBT+fNhK2ZaEFw/RVBdbYsvFeNnwFiCyBMTXw2K1EA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="./assets/js/jquery-ui-1.13.1/jquery-ui.min.js"></script>

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header">
					<?php $ModuleIconName = getITSMModuleTypeIcon($ITSMTypeID); ?>
					<i class="<?php echo $ModuleIconName ?>"></i> <a href="javascript:location.reload(true);"><?php echo _($ITSMName) . " " . _("Statistics"); ?></a>
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
					<div class="row">
						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
							<canvas id="CompaniesOpen" style="width:100%;max-width:300px"></canvas>
						</div>
						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
							<canvas id="CompaniesClosed" style="width:100%;max-width:300px"></canvas>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
							<canvas id="TeamsOpen" style="width:100%;max-width:300px"></canvas>
						</div>
						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
							<canvas id="TeamsClosed" style="width:100%;max-width:300px"></canvas>
						</div>
					</div>
					<div class="row">
						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
							<canvas id="ResponsiblesOpen" style="width:100%;max-width:300px"></canvas>
						</div>

						<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
							<canvas id="ResponsiblesClosed" style="width:100%;max-width:300px"></canvas>
						</div>
					</div>
					<br>
					<br>
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div id="TeamsPivot"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
		$(document).ready(function() {

			var ITSMTypeID = <?php echo $ITSMTypeID; ?>

			vData = {
				ITSMTypeID: ITSMTypeID
			};

			$.ajax({
				type: "POST",
				url: "./getdata.php?getCompaniesOpenITSM",
				data: vData,
				success: function(data) {
					obj = JSON.parse(data);
					if (obj) {
						let xValues = new Array(0);
						let yValues = new Array(0);
						for (var i = 0; i < obj.length; i++) {
							xValues.push(obj[i].Company);
							yValues.push(obj[i].Antal);
						}
						xValues = xValues.filter(Boolean);
						yValues = yValues.filter(Boolean);

						new Chart("CompaniesOpen", {
							type: "doughnut",
							data: {
								labels: xValues,
								datasets: [{
									data: yValues
								}]
							},
							options: {
								responsive: false,
								plugins: {
									legend: {
										position: 'top',
									},
									title: {
										display: true,
										text: 'Active per Company'
									}
								},
								colors: {
									forceOverride: true
								}
							},
						});
					}
				},
			});

			$.ajax({
				type: "POST",
				url: "./getdata.php?getCompaniesClosedITSM",
				data: vData,
				success: function(data) {
					obj = JSON.parse(data);
					if (obj) {
						let xValues = new Array(0);
						let yValues = new Array(0);
						for (var i = 0; i < obj.length; i++) {
							let Company = obj[i].Company;
							if (Company) {
								xValues.push(obj[i].Company);
								yValues.push(obj[i].Antal);
							} else {

							}

						}
						if (xValues) {
							xValues = xValues.filter(Boolean);
							yValues = yValues.filter(Boolean);

							new Chart("CompaniesClosed", {
								type: "doughnut",
								data: {
									labels: xValues,
									datasets: [{
										data: yValues
									}]
								},
								options: {
									responsive: false,
									plugins: {
										legend: {
											position: 'top',
										},
										title: {
											display: true,
											text: 'Closed per Company'
										}
									},
									colors: {
										forceOverride: true
									}
								},
							});
						}
					}
				},
			});

			$.ajax({
				type: "POST",
				url: "./getdata.php?getTeamsOpenITSM",
				data: vData,
				success: function(data) {
					obj = JSON.parse(data);
					if (obj) {
						let xValues = new Array(0);
						let yValues = new Array(0);
						for (var i = 0; i < obj.length; i++) {
							xValues.push(obj[i].Team);
							yValues.push(obj[i].Antal);
						}
						xValues = xValues.filter(Boolean);
						yValues = yValues.filter(Boolean);

						new Chart("TeamsOpen", {
							type: "doughnut",
							data: {
								labels: xValues,
								datasets: [{
									data: yValues
								}]
							},
							options: {
								responsive: false,
								plugins: {
									legend: {
										position: 'top',
									},
									title: {
										display: true,
										text: 'Active per Team'
									}
								},
								colors: {
									forceOverride: true
								}
							},
						});
					}
				},
			});

			$.ajax({
				type: "POST",
				url: "./getdata.php?getTeamsClosedITSM",
				data: vData,
				success: function(data) {
					obj = JSON.parse(data);
					if (obj) {
						let xValues = new Array(0);
						let yValues = new Array(0);
						for (var i = 0; i < obj.length; i++) {
							xValues.push(obj[i].Team);
							yValues.push(obj[i].Antal);
						}
						xValues = xValues.filter(Boolean);
						yValues = yValues.filter(Boolean);

						new Chart("TeamsClosed", {
							type: "doughnut",
							data: {
								labels: xValues,
								datasets: [{
									data: yValues
								}]
							},
							options: {
								responsive: false,
								plugins: {
									legend: {
										position: 'top',
									},
									title: {
										display: true,
										text: 'Closed per Team'
									}
								},
								colors: {
									forceOverride: true
								}
							},
						});
					}
				},
			});

			$.ajax({
				type: "POST",
				url: "./getdata.php?getResponsiblesOpenITSM",
				data: vData,
				success: function(data) {
					obj = JSON.parse(data);
					if (obj) {
						let xValues = new Array(0);
						let yValues = new Array(0);
						for (var i = 0; i < obj.length; i++) {
							xValues.push(obj[i].Responsible);
							yValues.push(obj[i].Antal);
						}
						xValues = xValues.filter(Boolean);
						yValues = yValues.filter(Boolean);

						new Chart("ResponsiblesOpen", {
							type: "doughnut",
							data: {
								labels: xValues,
								datasets: [{
									data: yValues
								}]
							},
							options: {
								responsive: false,
								plugins: {
									legend: {
										position: 'top',
									},
									title: {
										display: true,
										text: 'Active per Responsible'
									}
								},
								colors: {
									forceOverride: true
								}
							},
						});
					}
				},
			});

			$.ajax({
				type: "POST",
				url: "./getdata.php?getResponsiblesClosedITSM",
				data: vData,
				success: function(data) {
					obj = JSON.parse(data);
					if (obj) {

						let xValues = new Array(0);
						let yValues = new Array(0);
						for (var i = 0; i < obj.length; i++) {
							xValues.push(obj[i].Responsible);
							yValues.push(obj[i].Antal);
						}
						xValues = xValues.filter(Boolean);
						yValues = yValues.filter(Boolean);

						new Chart("ResponsiblesClosed", {
							type: "doughnut",
							data: {
								labels: xValues,
								datasets: [{
									data: yValues
								}]
							},
							options: {
								responsive: false,
								plugins: {
									legend: {
										position: 'top',
									},
									title: {
										display: true,
										text: 'Closed per Responsible'
									}
								},
								colors: {
									forceOverride: true
								}
							},
						});
					}
				},
			});

			$.ajax({
				type: "POST",
				url: "./getdata.php?getTeamsITSM",
				data: vData,
				success: function(data) {
					obj = JSON.parse(data);
					if (obj) {

						$("#TeamsPivot").pivotUI(obj, {
							cols: [],
							rows: ["Team", "Status", "Priority", "Company"],
							rendererName: "Table Barchart"
						});
					}
				},
			});
		});
	</script>
	<?php include("./footer.php"); ?>