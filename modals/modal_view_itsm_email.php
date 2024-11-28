<div class="modal fade" id="modalViewITSMEmail" data-bs-focus="false" aria-labelledby="modalViewITSMEmailLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
					<i class="material-icons">clear</i>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-lg-6 col-sm-12 col-xs-12">
								<label for="modalITSMEmails" title="<?php echo _("State what email you want to send to") ?>"><?php echo _("Email to send to") ?></label>
								<div class="input-group input-group-static mb-4">
									<input type="text" id="modalITSMOtherEmail" name="modalITSMOtherEmail" class="form-control"><br>
								</div>
							</div>
							<div class="col-lg-6 col-sm-12 col-xs-12">
								<label for="modalITSMEmails" title="<?php echo _("Select email to send to") ?>"><?php echo _("Or choose prepared email") ?></label>
								<div class="input-group input-group-static mb-4">
									<select class="form-control select2" id="modalITSMEmails" name="modalITSMEmails" title="">
									</select>
								</div>
							</div>
							<div class="col-lg-12 col-sm-12 col-xs-12" title="<?php echo _("Creates a PDF with all information and attaches it to the email") ?>">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="PDFChoice" name="PDFChoice" checked="" value="1">
									<label class="form-check-label" for="PDFChoice"><?php echo _("Attach PDF") ?></label>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<div class="col-lg-12 col-sm-12 col-xs-12">
							<button class="btn btn-sm btn-success float-end" onclick="sendITSMAsMailWithPDF('modal');"><?php echo _("Send"); ?></button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>