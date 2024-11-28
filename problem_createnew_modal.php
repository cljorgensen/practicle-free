<?php
if (in_array("100001", $group_array) || in_array("100012", $group_array) || in_array("100013", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<!-- Modal for create new problem -->
<div class="modal fade bs-example-modal-sm" id="createnewproblemModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<!-- Modal Header -->
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">Create new problem</h4>
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

<!-- Modal for edit problem-->
<div class="modal fade bs-example-modal-sm" id="editProblemModal" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<!-- Modal Header -->
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">Edit problem</h4>
			</div>

			<!-- Modal Body -->
			<div class="modal-body">
				<p class="statusMsg"></p>
				<form action="<?php echo $redirectpage; ?>" method="post">

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<input type="text" class="form-control" id="editProblemID" name="editProblemID" value="" placeholder="Problem ID" readonly>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<input type="text" class="form-control" id="editProblemName" name="editProblemName" value="" placeholder="Problem Name">
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<input type="text" class="form-control" id="editProblemDescription" name="editProblemDescription" value="" placeholder="Problem Description">
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<select id="editProblemResp" name="editProblemResp" class="form-control" required>
							<?php
							$sql = "SELECT ticketusers.ID, ticketusers.FullName 
								FROM ticketusers
								WHERE RelatedUserTypeID = 1";
							$result = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));
							while ($row = mysqli_fetch_array($result)) {
								echo "<option value='" . $row['ID'] . "'>" . $row['FullName'] . "</option>";
							}
							?>
						</select>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<select id="editProblemStatus" name="editProblemStatus" class="form-control" required>
							<?php
							$sql = "SELECT problemstatuscodes.ID, problemstatuscodes.StatusName 
								FROM problemstatuscodes";
							$result = mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));
							while ($row = mysqli_fetch_array($result)) {
								echo "<option value='" . $row['ID'] . "'>" . $row['StatusName'] . "</option>";
							}
							?>
						</select>
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