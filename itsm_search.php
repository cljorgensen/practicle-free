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

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header">
					<?php $ModuleIconName = getITSMModuleTypeIcon($ITSMTypeID); ?>
					<i class="<?php echo $ModuleIconName ?>"></i> <a href="javascript:location.reload(true);"><?php echo _("Search") . " " . _("$ITSMName"); ?></a>
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
						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="tab-pane fade show active" id="My" role="tabpanel" aria-labelledby="My">
								<div class="input-group input-group-static mb-4">
									<label for="SearchField" class="ms-0"><?php echo _("Subject") . " / " . _("Description") . " / " . _("Solution"); ?></label>
									<input type="text" class="form-control" id="SearchField" name="SearchField">
								</div>
								<button type="submit" class="btn btn-sm btn-secondary" onclick="searchITSM(<?php echo htmlspecialchars($ITSMTypeID); ?>);"><?php echo _("Search"); ?></button>
							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="form-check form-switch" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Search in inactive") ?>">
								<input class="form-check-input" type="checkbox" id="Status">
								<label class="form-check-label" for="Status"><?php echo _("Closed"); ?></label>
							</div>
						</div>
					</div>
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
						<table id="itsmtablesearch" class="table table-borderless table-responsive table-hover" cellspacing="0">
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include("./footer.php"); ?>