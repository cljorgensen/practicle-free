<?php include("./header.php"); ?>
<?php

$ITSMTypeID = "1";
if (!empty($_GET['id'])) {
	$ITSMTypeID = $_GET['id'];
}

$url = @$functions->getSettingValue(17) . "/itsm_simple.php?id=$ITSMTypeID";

$sql = "SELECT itsm_modules.ID, itsm_modules.Name
		FROM itsm_modules
		WHERE itsm_modules.ID = $ITSMTypeID";
$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
	$ITSMName = $row["Name"];
}

$ITSMTableName = @$functions->getITSMTableName($ITSMTypeID);
?>

<div id="ITSMTypeIDSimpleView" data-value="<?php echo $ITSMTypeID; ?>" hidden></div>
<div id="CurrentPage" data-value="<?php echo $CurrentPage; ?>" hidden></div>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header">
					<?php $ModuleIconName = getITSMModuleTypeIcon($ITSMTypeID); ?>
					<i class="<?php echo $ModuleIconName ?>"></i> <a href="<?php echo _($url); ?>"><?php echo _($ITSMName); ?></a>
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
						echo $navMenu03 = navMenu03($ITSMTypeID);
					} ?>
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
						<div class="tab-content" id="ITSMTabContent">
							<div class="tab-pane fade show active" id="My" role="tabpanel" aria-labelledby="My">
								<table id="itsmsimple" class="table table-borderless table-responsive table-hover" cellspacing="0">
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