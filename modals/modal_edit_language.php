<!-- Modal Edit Language -->
<div class="modal fade" id="EditLanguageEntry" data-bs-focus="false" role="dialog" aria-labelledby="EditLanguageEntryLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title font-weight-normal" id="EditLanguageEntryLabel"><?php echo _('Edit'); ?></h6>
				<button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
					<i class="material-icons">clear</i>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalLanguageID"><?php echo _('ID'); ?></label>
									<input type="text" id="modalLanguageID" class="form-control" disabled>
								</div>
							</div>

							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalMainLanguage"><?php echo _('Main Language (english)'); ?></label>
									<input type="text" id="modalMainLanguage" class="form-control" onclick="copytoclipboard('modalMainLanguage')">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modaldaDK"><?php echo _("Danish") ?></label>
									<input type="text" id="modaldaDK" class="form-control">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalesES"><?php echo _("Spanish") ?></label>
									<input type="text" class="form-control" id="modalesES">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modaldeDE"><?php echo _("German") ?></label>
									<input type="text" class="form-control" id="modaldeDE">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalfrFR"><?php echo _("French") ?></label>
									<input type="text" class="form-control" id="modalfrFR">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalfiFI"><?php echo _("Finnish") ?></label>
									<input type="text" class="form-control" id="modalfiFI">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalitIT"><?php echo _("Italian") ?></label>
									<input type="text" class="form-control" id="modalitIT">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modaltrTR"><?php echo _("Turkish") ?></label>
									<input type="text" class="form-control" id="modaltrTR">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalzhCN"><?php echo _("Mandarin") ?></label>
									<input type="text" class="form-control" id="modalzhCN">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalruRU"><?php echo _("Russian") ?></label>
									<input type="text" class="form-control" id="modalruRU">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modaljaJP"><?php echo _("Japanese") ?></label>
									<input type="text" class="form-control" id="modaljaJP">
								</div>
							</div>

							<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalptPT"><?php echo _("Portuguese") ?></label>
									<input type="text" class="form-control" id="modalptPT">
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-sm btn-danger" onclick="deleteLanguageEntry()"><?php echo _("Delete"); ?></button>
						<button type="button" class="btn btn-sm btn-success" onclick="updateLanguageEntry()"><?php echo _("Update"); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>