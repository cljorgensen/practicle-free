<div class="modal fade" id="modalCreateCi" data-bs-focus="false" role="dialog" aria-FieldLabelledby="modalCreateCiFieldLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-wide" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6><div id="modalCreateCIHeaderHeadline"></div></h6>
				<button class="btn btn-link text-dark p-0 fixed-plugin-close-button float-end" data-bs-dismiss="modal" aria-label="Close">
					<i class="material-icons">clear</i>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<form id="CIFormCreate">
							<div class="row" id="modalCICreateContent">
								<div id="modalCreateLeftContent" class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
									<div class="row" id="CICreateDefinitionLeft"></div>
								</div>
								<div id="modalCreateRightContent" class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
									<div class="card">
										<div class="card-body">
											<div class="row" id="CICreateDefinitionRight"></div>
										</div>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<?php
						if (in_array("100001", $group_array) || in_array("100015", $group_array)) {
							echo "<button type=\"button\" class=\"btn btn-sm btn-success\" onclick=\"createCIEntry()\">" . _("Create") . "</button>";
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>