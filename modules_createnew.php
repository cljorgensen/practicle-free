<!-- Modal for create new module -->
<div class="modal fade bs-example-modal-sm" id="createnewmoduleModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<!-- Modal Header -->
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">Create new module</h4>
			</div>

			<!-- Modal Body -->
			<div class="modal-body">
				<p class="statusMsg"></p>
				<form action="<?php echo $redirectpage; ?>" method="post">

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<input type="text" class="form-control" id="ProblemName" name="ProblemName" value="" placeholder="Problem Name">
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<input type="text" class="form-control" id="ProblemDescription" name="ProblemDescription" value="" placeholder="Problem Description">
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<button type="submit" name="submit_new" class="btn btn-success float-end">Submit new</button>
					</div>
				</form>
			</div>
			<!-- Modal Footer -->
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-default submitBtn" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal for edit module-->
<div class="modal fade bs-example-modal-sm" id="editModuleModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<!-- Modal Header -->
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">Edit Module</h4>
			</div>

			<!-- Modal Body -->
			<div class="modal-body">
				<p class="statusMsg"></p>
				<form action="<?php echo $redirectpage; ?>" method="post">

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<input type="text" class="form-control" id="editID" name="editID" value="" placeholder="ID" readonly>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<input type="text" class="form-control" id="editName" name="editName" value="" placeholder="Module Name">
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<input type="text" class="form-control" id="editLicenseKey" name="editLicenseKey" value="" placeholder="Licence Key">
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<input type="text" class="form-control" id="editModuleActive" name="editModuleActive" value="" placeholder="Active">
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<textarea id="editDescription" name="editDescription" rows="10" class="resizable_textarea form-control" placeholder="Description"></textarea>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<textarea id="editHelpText" name="editHelpText" rows="10" class="resizable_textarea form-control" placeholder="HelpText"></textarea>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<button type="submit" name="submit_changes" class="btn btn-success float-end">Submit</button>
					</div>
				</form>
			</div>
			<!-- Modal Footer -->
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-default submitBtn" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<!-- end of dispatch que modal -->