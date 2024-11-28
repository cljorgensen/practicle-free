<div class="modal fade" id="modalReportProblem" data-bs-focus="false" aria-labelledby="modalReportProblemLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h6><?php echo _("Problem report"); ?></h6>
				<button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
					<i class="material-icons">clear</i>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<form id="emailForm">
							<div class="row">
								<div class="col-lg-12 col-sm-12 col-xs-12">
									<div class="form-group">
										<label for="ProblemReportMessageText"><?php echo _("Please explain the problem") ?></label>
										<a href="javascript:toggleCKEditor('ProblemReportMessageText');"><i class="fa-solid fa-pen fa-sm" title="Click to edit"></i></a>
										<div class="resizable_textarea form-control" id="ProblemReportMessageText" name="ProblemReportMessageText" title="Double click to edit" rows="5" autocomplete="off" ondblclick="toggleCKEditor('ProblemReportMessageText');">
										</div>
										<?php echo "<br>..." . _("this will create a ticket at Practicle Support so please dont abuse this!"); ?>
									</div>
								</div>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close"><?php echo _("Close"); ?></button>
						<button type="button" class="btn btn-primary" onclick="submitProblemReportForm()"><?php echo _("Send"); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>