<div class="modal fade" data-bs-focus="false" id="modalViewCi" role="dialog" aria-labelledby="modalViewCiLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-wide" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div id="modalHeaderHeadline"></div>
				<div id="ciid" hidden></div>
				<div id="citypeid" hidden></div>
				<div class="modal-header-buttons d-flex">
					<button class="btn btn-link text-dark p-0 restore-btn">
						<i class="material-icons">maximize</i>
					</button>
					&nbsp;&nbsp;
					<button class="btn btn-link text-dark p-0 minimize-btn">
						<i class="material-icons">minimize</i>
					</button>
					&nbsp;&nbsp;
					<button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
						<i class="material-icons">clear</i>
					</button>
				</div>
			</div>
			<div class="modal-body">
				<div id="CIFieldsDiv">
					<div class="row">
						<div class="scrolling-menu-wrapper">
							<div class="arrow arrow-left">&#9664;</div>
							<div class="scrolling-menu">
								<ul class="nav nav-tabs" id="myTab" role="tablist">
									<li class="nav-item">
										<a class="nav-link active" data-bs-toggle="tab" href="#ModalCIDetailsTab" id="LinkModalCIDetailsTab" role="tab">
											<?php echo _("Details"); ?>
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#TabCIRelations" id="LinkTabCIRelations" role="tab">
											<?php echo _("Relations"); ?>
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#ModalCIFilesTab" id="LinkModalCIFilesTab" role="tab">
											<?php echo _("Files"); ?>
											<span class="badge rounded-pill" id="SumCIFiles"></span>
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#ModalCILogBookTab" id="LinkModalCILogBookTab" role="tab">
											<?php echo _("Log Book"); ?>
										</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#ModalCILogTab" id="LinkModalCILogTab" role="tab">
											<?php echo _("Log"); ?>
										</a>
									</li>
								</ul>
							</div>
							<div class="arrow arrow-right">&#9654;</div>
						</div>
						<div class="card-group">
							<div class="card">
								<div class="card-body">
									<div class="tab-content">
										<div class="tab-pane active" id="ModalCIDetailsTab">
											<div class="row">
												<div class="dropdown">
													<button class="btn btn-sm bg-gradient-secondary dropup dropdown-toggle float-end" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
														<?php echo _("More") ?>
													</button>
													<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
														<li><a class="dropdown-item" href="javascript:getDirectLink('cmdb');"><?php echo _("Get direct link") ?></a></li>
													</ul>
												</div>
											</div>
											<form id="CIViewForm">
												<div class="row" id="modalContent">
													<div id="modalLeftContent" class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
														<div class="row" id="DefinitionLeft"></div>
													</div>
													<div id="modalRightContent" class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
														<div class="card">
															<div class="card-body">
																<div class="row" id="DefinitionRight"></div>
																<div class="row" id="UpdateButton"></div>
															</div>
														</div>
													</div>
												</div>
											</form>
										</div>

										<div class="tab-pane" id="TabCIRelations">
											<div class="scrolling-menu-wrapper">
												<div class="arrow arrow-left">&#9664;</div>
												<div class="scrolling-menu">
													<ul class="nav nav-tabs" id="myTab" role="tablist">
														<li class="nav-item">
															<a class="nav-link active" data-bs-toggle="tab" href="#ModalCIRelationsDetailsTab" id="LinkModalCIRelationsDetailsTab" role="tab">
																<?php echo _("CI Relations"); ?>
															</a>
														</li>
														<li class="nav-item">
															<a class="nav-link" data-bs-toggle="tab" href="#ModalITSMRelationsDetailsTab" id="LinkModalITSMRelationsDetailsTab" role="tab">
																<?php echo _("ITSM Relations"); ?>
															</a>
														</li>
													</ul>
												</div>
												<div class="arrow arrow-right">&#9654;</div>
											</div>
											<div class="tab-content">
												<div class="tab-pane active" id="ModalCIRelationsDetailsTab">
													<?php
													if (in_array("100001", $group_array) || in_array("100015", $group_array)) {
														echo "<p><a href=\"javascript:void(0);\"><i class=\"fa-solid fa-circle-chevron-down float-right\" title=\"" . _("Admin") . "\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseCreateCI\" aria-expanded=\"false\" aria-controls=\"collapseCreateCI\"></i></a></p>";
													}
													?>
													<div class="collapse" id="collapseCreateCI">
														<div class="card">
															<div class="card-body">
																<div class="row">
																	<h5><?php echo _("Create relation") ?></h5>
																</div>
																<div class="row">
																	<div class="col-md-5 col-sm-12 col-xs-12">
																		<label for="CIS"><?php echo _("CI Types"); ?></label>
																		<div class="input-group input-group-static mb-4">
																			<select class="form-control select2" id="CIS" name="CIS" onchange="getCIs(this.value);" autocomplete="on" style="width: 100% !important;">
																				<option></option>
																				<?php
																				$sql = "SELECT cmdb_cis.TableName, cmdb_cis.Name
																						FROM cmdb_cis
																						WHERE cmdb_cis.Active=1
																						ORDER BY cmdb_cis.Name ASC;";
																				$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
																				while ($row = mysqli_fetch_array($result)) {
																					$Name = $row['Name'];
																					echo "<option value='" . $row['TableName'] . "'>$Name</option>";
																				}

																				mysqli_free_result($result); // Free the result set
																				?>
																			</select>
																		</div>
																	</div>

																	<div class="col-md-5 col-sm-5 col-xs-12">
																		<div class="input-group input-group-static mb-4" title="<?php echo _("This relation will make current assets child"); ?>">
																			<label for="CIResultsParent" class="ms-0"><?php echo _("Overall"); ?><?php echo " " . _("CI's"); ?></label>
																			<select class="form-control select2" id="CIResultsParent" name="CIResultsParent" style="width: 100% !important;" onchange="this.blur();" autocomplete="off">
																			</select>
																		</div>
																	</div>
																	<div class="col-md-2 col-sm-2 col-xs-12">
																		<div class="col-md-12 col-sm-12 col-xs-12" id="CreateRelationButton1">
																			<br>
																			<button type="button" id="btn_ci_createrelation1" name="btn_ci_createrelation1" class="btn btn-sm btn-success ml-auto" onclick="createCIRelation('parent')"><?php echo @$functions->translate("Create") ?></button>
																		</div>
																	</div>

																	<div class="col-md-5 col-sm-5 col-xs-12">
																	</div>
																	<div class="col-md-5 col-sm-5 col-xs-12">
																		<label for="CIResultsChilds" class="ms-0"><?php echo _("Subordinate"); ?><?php echo " " . _("CI's"); ?></label>
																		<div class="input-group input-group-static mb-4" title="<?php echo _("This relation will make current asset parent"); ?>">
																			<select class="form-control select2" id="CIResultsChilds" name="CIResultsChilds" style="width: 100% !important;" onchange="this.blur();" autocomplete="off">
																			</select>
																		</div>
																	</div>
																	<div class="col-md-2 col-sm-2 col-xs-12">
																		<div class="col-md-12 col-sm-12 col-xs-12" id="CreateRelationButton2">
																			<br>
																			<button type="button" id="btn_ci_createrelation2" name="btn_ci_createrelation2" class="btn btn-sm btn-success ml-auto" onclick="createCIRelation('child')"><?php echo @$functions->translate("Create") ?></button>
																		</div>
																	</div>
																</div>
															</div>
														</div>
														<br>
													</div>
													<div class="row">
														<div class="col-md-6 col-sm-6 col-xs-12">
															<div class="card">
																<div class="card-body">
																	<h6><?php echo _("Parent Assets") ?></h6>
																	<table id="TableParentRelations" class="table table-borderless table-responsive table-hover" cellspacing="0">
																	</table>
																</div>
															</div>
														</div>
														<div class="col-md-6 col-sm-6 col-xs-12">
															<div class="card">
																<div class="card-body">
																	<h6><?php echo _("Child Assets") ?></h6>
																	<table id="TableChildRelations" class="table table-borderless table-responsive table-hover" cellspacing="0">
																	</table>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="tab-pane" id="ModalITSMRelationsDetailsTab">
													<?php
													if (in_array("100001", $group_array) || in_array("100015", $group_array)) {
														echo "<p><a href=\"javascript:void(0);\"><i class=\"fa-solid fa-circle-chevron-down float-right\" title=\"" . _("Create relation") . "\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseCreateITSM\" aria-expanded=\"false\" aria-controls=\"collapseCreateITSM\"></i></a></p>";
													}
													?>
													<div class="collapse" id="collapseCreateITSM">
														<div class="card">
															<div class="card-body">
																<div class="row">
																	<h5><?php echo _("Create relation") ?></h5>
																</div>
																<div class="row">
																	<div class="col-md-5 col-sm-5 col-xs-12">
																		<div class="input-group input-group-static mb-4">
																			<label for="ITSMTypes" class="ms-0"><?php echo _("ITSM Types"); ?></label>
																			<select class="form-control select2" id="ITSMTypes" name="ITSMTypes" onchange="getITSMs(this.value,'ci');" autocomplete="on" style="width: 100% !important;">
																				<option></option>
																				<?php
																				$sql = "SELECT itsm_modules.TableName, itsm_modules.Name
																						FROM itsm_modules 
																						WHERE itsm_modules.Active=1
																						ORDER BY itsm_modules.Name ASC;";
																				$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
																				while ($row = mysqli_fetch_array($result)) {
																					$Name = @$functions->translate($row['Name']);
																					echo "<option value='" . $row['TableName'] . "'>$Name</option>";
																				}
																				?>
																			</select>
																		</div>
																	</div>

																	<div class="col-md-5 col-sm-5 col-xs-12">
																		<div class="input-group input-group-static mb-4" title="<?php echo _("Relate to other ITSM element"); ?>">
																			<label for="CIITSMElements" class="ms-0"><?php echo _("ITSM's"); ?></label>
																			<select class="form-control select2" id="CIITSMElements" name="CIITSMElements" style="width: 100% !important;" onchange="this.blur();" autocomplete="off">
																			</select>
																		</div>
																	</div>
																	<div class="col-md-2 col-sm-2 col-xs-12">
																		<br>
																		<button type="button" id="btn_ci_createrelation1" name="btn_ci_createrelation1" class="btn btn-sm btn-success ml-auto" onclick="createITSMRelationCI()"><?php echo @$functions->translate("Create") ?></button>
																	</div>
																</div>
															</div>
														</div>
														<br>
													</div>
													<div class="card">
														<div class="card-body">
															<h6><?php echo _("Relations") ?></h6>
															<table id="TableCIITSMRelations" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="tab-pane" id="ModalCIFilesTab">
											<div id="FormFileAction"></div>
											<table id="TableCIFiles" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
											</table>
										</div>

										<div class="tab-pane" id="ModalCILogBookTab">
											<?php
											if (in_array("100001", $group_array) || in_array("100015", $group_array)) {
												echo "<p><a href=\"javascript:void(0);\"><i class=\"fa-solid fa-circle-chevron-down float-right\" title=\"" . _("Admin") . "\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseCreateLogBookEntry\" aria-expanded=\"false\" aria-controls=\"collapseCreateLogBookEntry\"></i></a></p>";
											}
											?>
											<div class="collapse" id="collapseCreateLogBookEntry">
												<div class="card">
													<div class="card-body">
														<div class="row">
															<h5><?php echo _("Create") ?></h5>
														</div>
														<div class="row">
															<div class="col-md-7 col-sm-7 col-xs-12">
																<div class="input-group input-group-static mb-4" title="<?php echo _("Description"); ?>">
																	<label for="LogBookDescription" class="ms-0"><?php echo _("Description"); ?></label>
																	<textarea rows="10" class="form-control" list="LogBookDescription" id="LogBookDescription" name="LogBookDescription" autocomplete="off"></textarea>
																</div>
															</div>

															<div class="col-md-3 col-sm-3 col-xs-12">
																<div class="input-group input-group-static mb-4" title="<?php echo _("Related number"); ?>">
																	<label for="LogBookRelation" class="ms-0"><?php echo _("Related number"); ?></label>
																	<input class="form-control" list="LogBookRelation" id="LogBookRelation" name="LogBookRelation" autocomplete="off">
																</div>
															</div>
															<div class="col-md-2 col-sm-2 col-xs-12">
																<div class="col-md-12 col-sm-12 col-xs-12" id="CreateLogBookEntryButton">
																	<br>
																	<button type="button" id="btn_ci_createlogbookentry" name="btn_ci_createlogbookentry" class="btn btn-sm btn-success ml-auto" onclick="createCILogBookEntry()"><?php echo @$functions->translate("Create") ?></button>
																</div>
															</div>
														</div>
													</div>
												</div>
												<br>
											</div>
											<table id="TableCILogBook" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
											</table>
										</div>

										<div class="tab-pane" id="ModalCILogTab">
											<table id="TableCILog" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
											</table>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$('#modalViewCi').on('hidden.bs.modal', function() {
		if ($.fn.DataTable.isDataTable("#TableParentRelations")) {
			$("#TableParentRelations").DataTable().clear().destroy();
		}

		if ($.fn.DataTable.isDataTable("#TableChildRelations")) {
			$("#TableChildRelations").DataTable().clear().destroy();
		}

		if ($.fn.DataTable.isDataTable("#TableCILog")) {
			$("#TableCILog").DataTable().clear().destroy();
		}

		if ($.fn.DataTable.isDataTable("#TableCIFiles")) {
			$("#TableCIFiles").DataTable().clear().destroy();
		}
	})
</script>