<div class="modal fade" id="modalCreateITSM" data-bs-focus="false" role="dialog" aria-labelledby="modalCreateITSMLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-wide" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6><div id="modalCreateITSMHeaderHeadline"></div></h6>
				<button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
					<i class="material-icons">clear</i>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<input type='text' class='form-control' id="ModalCreateITSMTypeID" name="ModalCreateITSMTypeID" hidden>
							<p><a href="javascript:void(0);"><i class="fa-solid fa-circle-chevron-down float-right" title="more" data-bs-toggle="collapse" data-bs-target="#collapseITSMCreateMore" aria-expanded="false" aria-controls="collapseITSMMore"></i></a></p>
							<div class="collapse" id="collapseITSMCreateMore">
								<div class="card">
									<div class="card-body">
										<div class="row">
											<div class="col-md-6 col-sm-6 col-xs-12">
												<h6><?php echo _("Choose template") ?></h6>
												<div class="input-group input-group-static mb-4">
													<label class="form-label"></label>
													<select id="ITSMTemplates" name="ITSMTemplates" class="form-control" onchange="loadITSMTemplateCreate(this.value);">
													</select>
												</div>
											</div>
											<div class="col-md-4 col-sm-4 col-xs-12">
												<div class="row">
													<div class="col-md-12 col-sm-12 col-xs-12">
														<button class="btn btn-sm btn-success float-end" onclick="createITSMTemplate()" title="<?php echo _("Create a template based on the completed form below"); ?>"><?php echo _("Create") ?></button>
														<button class="btn btn-sm btn-danger float-end" onclick="deleteTSMTemplate()" title="<?php echo _("Delete the current selected template"); ?>"><?php echo _("Delete") ?></button>
													<?php
													if (in_array("100001", $group_array) || in_array("100031", $group_array)) {
														echo "<div class=\"form-check  float-end\" title=\"" . _("Create public template that all users can choose") . "\">
												<input class=\"form-check-input\" type=\"checkbox\" value=\"\" id=\"public_template\" checked=false>
												<label class=\"custom-control-label\" for=\"public_template\">Public</label>
											</div></div>";
													} else {
														echo "</div>";
													}
													?>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<table id="TableRequestsView" class="table table-borderless dt-responsive table-hover" cellspacing="0">
						</table>
						<div class="row">
							<form id="RequestDefinition"></form>
						</div>
						<div class="row">
							<div id="FormID" hidden></div>
							<div id="ITSMCreatedFrom" hidden></div>
							<div id="ITSMCreatedFromITSMID" hidden></div>
							<div id="ITSMCreatedFromITSMTypeID" hidden></div>
							<div class="row">
								<form id="ITSMFormCreate">
									<div class="row" id="modalContent">
										<div id="modalLeftContent" class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
											<div class="row" id="ITSMCreateDefinitionLeft"></div>
										</div>
										<div id="modalRightContent" class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
											<div class="card">
												<div class="card-body">
													<div class="row" id="ITSMCreateDefinitionRight"></div>
													<div class="row">
														<div class="col-md-12 col-sm-12 col-xs-12">
															<div id="ITSMCreateButton"></div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
					<div class="modal-footer">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>