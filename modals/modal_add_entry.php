<!-- Modal Add Entry -->
<div class="modal fade" id="modalNewEntry" data-bs-focus="false" role="dialog" aria-labelledby="modalNewEntryLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title font-weight-normal" id="modalNewEntryHeader"></h6>
				<button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
					<i class="material-icons">clear</i>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<form id="formNewEntry">
							<div id="entryFields">
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-sm btn-success" onclick="submitEntryCreate()"><?php echo _("Add"); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- End Modal Add Entry -->