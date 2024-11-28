<div class="modal fade" data-bs-focus="false" id="modalViewITSM" role="dialog" aria-labelledby="modalViewITSMLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-wide" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div id="modalITSMHeaderHeadline"></div>
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
				<div id="itsmid" hidden></div>
				<div id="itsmtypeid" hidden></div>
				<div class="row">
					<div class="scrolling-menu-wrapper">
						<div class="arrow arrow-left">&#9664;</div>
						<div class="scrolling-menu">
							<ul class="nav nav-tabs" id="myTab" role="tablist">
								<li class="nav-item">
									<a class="nav-link active" data-bs-toggle="tab" href="#ModalITSMDetailsTab" id="LinkModalITSMDetailsTab" role="tab">
										<?php echo _("Details"); ?>
									</a>
								</li>
								<?php
								$UserType = $_SESSION['usertype'];
								if ($UserType != "2") { ?>
									<li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#TabITSMRelations" id="LinkTabITSMRelations" role="tab">
											<?php echo _("Relations"); ?>
										</a>
									</li>
								<?php }
								?>
								<li class="nav-item">
									<a class="nav-link" data-bs-toggle="tab" href="#ModalITSMFilesTab" id="LinkModalITSMFilesTab" role="tab">
										<?php echo _("Files"); ?>
										<span class="badge rounded-pill" id="SumFiles"></span>
									</a>
								</li>
								<?php
								if ($UserType != "2") { ?>
									<li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#ModalITSMWorkFlowTab" id="LinkModalITSMWorkFlowTab" role="tab">
											<?php echo _("Workflows"); ?>
											<span class="badge rounded-pill" id="SumWFT"></span>
										</a>
									</li>
								<?php }
								?>
								<?php
								if ($UserType != "2") { ?>
									<li class="nav-item">
										<a class="nav-link" data-bs-toggle="tab" href="#ModalITSMLogTab" id="LinkModalITSMLogTab" role="tab">
											<?php echo _("History"); ?>
										</a>
									</li>
								<?php }
								?>
							</ul>
						</div>
						<div class="arrow arrow-right">&#9654;</div>
					</div>
				</div>
				<div class="card-group">
					<div class="card">
						<div class="card-body">
							<div class="tab-content">
								<div class="tab-pane active" id="ModalITSMDetailsTab">
									<div class="col-md-12 col-sm-12 col-xs-12">
										<div class="row">
											<div id="itsm_menu">
												<?php
												$UserType = $_SESSION['usertype'];
												if ($UserType != "2") { ?>
													<span id="collapseITSMParticipantsMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseITSMParticipants" aria-expanded="false" aria-controls="collapseITSMParticipants"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Participants") ?>"></i> <?php echo _("Participants") ?></a></span>
												<?php }
												?>
												<span id="collapseITSMCommentsMenu"><a href="javascript:void(0);" id="readCommentsLink" data-bs-toggle="collapse" data-bs-target="#collapseITSMComments" aria-expanded="false" aria-controls="collapseITSMComments"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Comment section") ?>"></i> <?php echo _("Comments") ?></a></span><span class="badge badge-circle badge-info" id="unreadITSMComments"></span>
												<span id="collapseITSMSLAMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseITSMSLA" aria-expanded="false" aria-controls="collapseITSMSLA"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("SLA") ?>"></i> <?php echo _("SLA") ?></a></span>
												<span id="collapseITSMSolutionMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseITSMSolution" aria-expanded="false" aria-controls="collapseITSMSolution"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Solution") ?>"></i> <?php echo _("Solution") ?></a></span>
												<span id="collapseITSMArchiveMenu"><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseITSMArchive" aria-expanded="false" aria-controls="collapseITSMArchive"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Archive") ?>"></i> <?php echo _("Archive") ?></a></span>
											</div>
											<?php
											$UserType = $_SESSION['usertype'];
											if ($UserType != "2") { ?>
												<div class="dropdown float-end">
													<button class="btn btn-sm bg-gradient-secondary dropup dropdown-toggle float-end" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
														<?php
														echo _("More")
														?>
													</button>
													<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
														<li><a class="dropdown-item" href="javascript:getDirectLink('itsm');"><?php echo _("Get direct link") ?></a></li>
														<li><a class="dropdown-item" href="javascript:addITSMToKanban();"><?php echo _("Add to taskboard") ?></a></li>
														<li><a class="dropdown-item" href="javascript:sendITSMAsMailWithPDF('modal');"><?php echo _("Create PDF") ?></a></li>
														<li><a class="dropdown-item" href="javascript:prepateITSMasEmailWithPDF();"><?php echo _("E-mail") ?></a></li>
														<li><a class="dropdown-item" href="javascript:cloneITSM('modal');"><?php echo _("Clone") ?></a></li>
														<li><a class="dropdown-item" href="javascript:createITSMFromITSM(1);"><?php echo _("Create incident from this") ?></a></li>
														<li><a class="dropdown-item" href="javascript:createITSMFromITSM(2);"><?php echo _("Create request from this") ?></a></li>
														<li><a class="dropdown-item" href="javascript:createITSMFromITSM(3);"><?php echo _("Create change from this") ?></a></li>
														<li><a class="dropdown-item" href="javascript:createITSMFromITSM(4);"><?php echo _("Create problem from this") ?></a></li>
														<li><a class="dropdown-item" href="javascript:runModalCreateProjectTask('');"><?php echo _("Create project task from this") ?></a></li>
													</ul>
												</div>
											<?php }
											?>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<div class="accordion width" id="accordionITSMMore">
												<div class="card">
													<div id="collapseITSMArchive" class="collapse width" data-bs-parent="#accordionITSMMore">
														<div class="card-body">
															<table id="TableITSMArchive" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
															</table>
														</div>
													</div>
												</div>
												<div class="card">
													<div id="collapseITSMParticipants" class="collapse width" data-bs-parent="#accordionITSMMore">
														<div class="card-body">
															<div class="row">
																<div class="col-md-4 col-sm-4 col-xs-12">
																	<h6><?php echo _("Add") ?></h6>
																	<label for="participants" class="form-label"></label>
																	<div class="input-group input-group-static mb-4">
																		<select id="participants" name="participants" class="form-control select2" style="width: 100% !important;">
																		</select>
																	</div>
																	<div id="BtnAddParticipant">
																	</div>
																</div>

																<div class="col-md-8 col-sm-8 col-xs-12">
																	<div id="ITSMParticipants"></div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="card">
													<div id="collapseITSMComments" class="collapse width" data-bs-parent="#accordionITSMMore">
														<div class="card-body">
															<div class="row">
																<div class="col-md-7 col-sm-7 col-xs-12">
																	<table id="TableITSMComments" class="table table-borderless table-responsive" cellspacing="0"></table>
																</div>
																<div class="col-md-1 col-sm-1col-xs-12">
																</div>
																<div class="col-lg-4 col-sm-4 col-xs-12">

																	<div class="input-group input-group-static mb-4"><label for="ITSMComment" title="<?php echo _("Create comment to user"); ?>">
																			<?php echo _("Comment"); ?>&ensp;<a href="javascript:toggleCKEditor('ITSMComment','250px');"><i class="fa-solid fa-pen fa-sm" title="Double click on field to edit"></i></a>
																		</label></div>
																	<div style="max-height: 150px; word-wrap: break-word; overflow-y: auto; overflow-x: auto;" class="resizable_textarea form-control" id="ITSMComment" name="ITSMComment" title="Double click to edit" autocomplete="off" ondblclick="toggleCKEditor('ITSMComment','250px');">
																	</div>

																	<br>
																	<div class="row">
																		<div id="BtnAddComment"></div>
																		<div class="form-check form-switch float-end" title="<?php echo _("If internal, reporter (customer) does not get email notified and cannot see the comment") ?>">
																			<label class="form-check-label float-end" for="InternalComment"><?php echo _("Internal"); ?></label>
																			<input class="form-check-input float-end" type="checkbox" id="InternalComment">
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
												<div class="card">
													<div id="collapseITSMSLA" class="collapse width" data-bs-parent="#accordionITSMMore">
														<div class="card-body">
															<div class="row">
																<div id="ITSMSLA"></div>
															</div>
														</div>
													</div>
												</div>
												<div class="card">
													<div id="collapseITSMSolution" class="collapse width" data-bs-parent="#accordionITSMMore">
														<div class="card-body">
															<div class="row">
																<div class="col-lg-12 col-sm-12 col-xs-12">
																	<div id="ITSMSolutionBtn"></div>
																</div>
															</div>
															<div class="row">
																<div class="col-lg-12 col-sm-12 col-xs-12">
																	<button class="btn btn-sm btn-success float-end" onclick="resolveITSM('modal');"><?php echo _("Resolve"); ?></button>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="row" id="modalContent">
										<div id="modalLeftContent" class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
											<form id="ITSMViewFormLeft">
												<div class="row" id="ITSMDefinitionLeft"></div>
											</form>
											<form id="RequestDefinitionView">
											</form>
										</div>
										<div id="modalRightContent" class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
											<div class="card">
												<div class="card-body">
													<form id="ITSMViewFormRight">
														<div class="row" id="ITSMDefinitionRight"></div>
													</form>
													<div class="row">
														<div class="col-md-12 col-sm-12 col-xs-12">
															<button type="button" class="btn btn-sm btn-success float-end" id="btn_itsm_update" name="btn_itsm_update" onclick="updateITSM('modal')"><?php echo _("Update"); ?></button>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>

									<div class="col-md-7 col-sm-7 col-xs-12 float-left">

									</div>
								</div>

								<div class="tab-pane" id="TabITSMRelations">
									<?php
									if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
										echo "<p><a href=\"javascript:void(0);\"><i class=\"fa-solid fa-circle-chevron-down float-right\" title=\"" . _("Admin") . "\" data-bs-toggle=\"collapse\" data-bs-target=\"#collapseCreate\" aria-expanded=\"false\" aria-controls=\"collapseCreate\"></i></a></p>";
									}
									?>

									<div class="collapse" id="collapseCreate">
										<div class="card">
											<div class="card-body">
												<div class="row">
													<div class="col-md-4 col-sm-4 col-xs-12">
														<div class="input-group input-group-static mb-4">
															<label for="ITSMRelationTypes" class="ms-0"><?php echo _("ITSM Types"); ?></label>
															<select class="form-control select2" id="ITSMRelationTypes" name="ITSMRelationTypes" onchange="getITSMs(this.value);" autocomplete="on" style="width: 100% !important;">
																<option></option>
																<?php
																// Initialize an empty array to store translated names
																$translatedNames = [];

																$sql = "SELECT itsm_modules.TableName, itsm_modules.Name
																		FROM itsm_modules 
																		WHERE itsm_modules.Active = 1
																		ORDER BY itsm_modules.Name ASC";

																$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

																// Loop through the query result and store translated names in the array
																while ($row = mysqli_fetch_array($result)) {
																	$translatedName = @$functions->translate($row['Name']);
																	$translatedNames[] = ['name' => $translatedName, 'tableName' => $row['TableName']];
																}

																// Sort the array by translated names
																usort($translatedNames, function ($a, $b) {
																	return strcmp($a['name'], $b['name']);
																});

																// Output the options with sorted translated names
																foreach ($translatedNames as $translated) {
																	$TableName = $translated['tableName'];
																	$Name = $translated['name'];
																	echo "<option value=\"$TableName\">$Name</option>";
																}
																?>
															</select>
														</div>
													</div>

													<div class="col-md-7 col-sm-7 col-xs-12">
														<div class="input-group input-group-static mb-4" title="<?php echo _("Relate to other ITSM element"); ?>">
															<label for="ITSMResultsElement" class="ms-0"><?php echo _("ITSM's"); ?></label>
															<select class="form-control select2" id="ITSMResultsElement" name="ITSMResultsElement" style="width: 100% !important;" onchange="this.blur();" autocomplete="off">
															</select>
														</div>
													</div>

													<div class="col-md-1 col-sm-1 col-xs-12">
														<br>
														<div id="ITSMCreateRelationButton2">
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
														<div class="input-group input-group-static mb-4">
															<label for="ElementCIS" class="ms-0"> <?php echo _("CI Types"); ?></label>
															<select class="form-control select2" id="ElementCIS" name="ElementCIS" onchange="getCIs(this.value);" autocomplete="on" placeholder="<?php echo _("Select") . "..." ?>" style="width: 100% !important;">
																<option></option>
																<?php
																$sql = "SELECT cmdb_cis.TableName, cmdb_cis.Name
																FROM cmdb_cis 
																WHERE cmdb_cis.Active=1
																ORDER BY cmdb_cis.Name ASC;";
																$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
																while ($row = mysqli_fetch_array($result)) {
																	$Name = $row['Name'];
																	$TableName = $row['TableName'];
																	echo "<option value='$TableName'>$Name</option>";
																}
																?>
															</select>
														</div>
													</div>
													<div class="col-lg-7 col-md-7 col-sm-12 col-xs-12">
														<div class="input-group input-group-static mb-4" title="<?php echo _("This will relate this to chosen CI"); ?>">
															<label for="CIResultsElement" class="ms-0"><?php echo _("CI's"); ?></label>
															<select class="form-control select2" id="CIResultsElement" name="CIResultsElement" style="width: 100% !important;" onchange="this.blur();" autocomplete="off">
															</select>
														</div>
													</div>
													<div class="col-md-1 col-sm-1 col-xs-12">
														<br>
														<div id="ITSMCreateRelationButton1">
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
													<h6><?php echo _("ITSM Relations") ?></h6>
													<table id="TableITSMRelations" class="table table-borderless table-responsive table-hover" cellspacing="0">
													</table>
												</div>
											</div>
										</div>

										<div class="col-md-6 col-sm-6 col-xs-12">
											<div class="card">
												<div class="card-body">
													<h6><?php echo _("CI Relations") ?></h6>
													<table id="TableITSMCIRelations" class="table table-borderless table-responsive table-hover" cellspacing="0">
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="tab-pane" id="ModalITSMFilesTab">
									<div id="ITSMFormFileAction"></div>
									<table id="TableITSMFiles" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
									</table>
								</div>

								<div class="tab-pane" id="ModalITSMWorkFlowTab">
									<div class="row">
										<a href="javascript:void(0);"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="collapse" data-bs-target="#collapseWFAdmin" aria-expanded="false" aria-controls="collapseWFAdmin"></i></a>
										<div class="collapse" id="collapseWFAdmin">
											<div class="card">
												<div class="card-body">
													<div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
														<div class="input-group input-group-static mb-4">
															<input class="form-control" autocomplete="off" list="ITSMWorkFlows" id="WorkflowInput" name="WorkflowInput" class="form-control" placeholder="<?php echo _("Select Workflow") ?>">
															<datalist id="ITSMWorkFlows">
															</datalist>
														</div>
													</div>
													<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
														<div id="btnCreateWorkFlow"></div>
													</div>
													<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
														<div id="btnRemoveWorkFlow"></div>
													</div>
												</div>
											</div>
											<br>
										</div>
									</div>

									<table id="TableITSMWorkFlow" class="col-md-12 col-sm-12 col-xs-12" class="table table-borderless table-responsive table-hover" cellspacing="0">
									</table>

								</div>

								<div class="tab-pane" id="ModalITSMLogTab">
									<table id="TableITSMLog" class="col-md-12 col-sm-12 col-xs-12" class="table table-borderless table-responsive table-hover" cellspacing="0">
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class=" modal-footer">
			</div>
		</div>
	</div>
</div>

<script>
	$('#modalViewITSM').on('hidden.bs.modal', function() {
		if ($.fn.DataTable.isDataTable("#TableParentRelations")) {
			$("#TableParentRelations").DataTable().clear().destroy();
		}

		if ($.fn.DataTable.isDataTable("#TableChildRelations")) {
			$("#TableChildRelations").DataTable().clear().destroy();
		}

		if ($.fn.DataTable.isDataTable("#TableITSMLogs")) {
			$("#TableITSMLogs").DataTable().clear().destroy();
		}

		if ($.fn.DataTable.isDataTable("#TableITSMFiles")) {
			$("#TableITSMFiles").DataTable().clear().destroy();
		}
	})
</script>