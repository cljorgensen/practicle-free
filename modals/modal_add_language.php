<!-- Modal Add Language -->
<div class="modal fade" id="modalNewLanguageEntry" data-bs-focus="false" role="dialog" aria-labelledby="NewLanguageEntryLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title font-weight-normal" id="NewLanguageEntryLabel"><?php echo _('Add Language Entry'); ?></h6>
				<button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
					<i class="material-icons">clear</i>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<p class="statusMsg"></p>
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="input-group input-group-static mb-4">
								<label for="modalMainLanguageAdd">Main Language (english)</label>
								<input type="text" id="modalMainLanguageAdd" class="form-control">
							</div>
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="input-group input-group-static mb-4">
								<label for="modaldaDKAdd">da_DK</label>
								<input type="text" id="modaldaDKAdd" class="form-control">
							</div>
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="input-group input-group-static mb-4">
								<label for="modaldeDEAdd">de_DE</label>
								<input type="text" class="form-control" id="modaldeDEAdd">
							</div>
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="input-group input-group-static mb-4">
								<label for="modalesESAdd">es_ES</label>
								<input type="text" class="form-control" id="modalesESAdd">
							</div>
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="input-group input-group-static mb-4">
								<label for="modalfrFRAdd">fr_FR</label>
								<input type="text" class="form-control" id="modalfrFRAdd">
							</div>
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="input-group input-group-static mb-4">
								<label for="modalfiFIAdd">fi_FI</label>
								<input type="text" class="form-control" id="modalfiFIAdd">
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-sm btn-success" onclick="submitLanguageCreate()"><?php echo _("Add"); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Modal Add Language -->
<!-- Script for modal Create Language -->
<script>
	function submitLanguageCreate() {
		var MainLanguage = $('#modalMainLanguageAdd').val();
		var daDK = $('#modaldaDKAdd').val();
		var deDE = $('#modaldeDEAdd').val();
		var esES = $('#modalesESAdd').val();
		var frFR = $('#modalfrFRAdd').val();
		var fiFI = $('#modalfiFIAdd').val();

		$.ajax({
			type: 'POST',
			url: './getdata.php',
			data: 'addLanguageEntry=1&MainLanguage=' + MainLanguage + '&daDK=' + daDK + '&deDE=' + deDE + '&esES=' + esES + '&frFR=' + frFR + '&fiFI=' + fiFI,
			success: function(data) {
				$("#modalNewLanguageEntry").modal('hide');
			}
		});
	}
</script>