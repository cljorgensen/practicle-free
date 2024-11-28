<?php include("./header.php"); ?>
<script>
	$(document).ready(function() {
		<?php initiateStandardSearchTable("testResults"); ?>

		$(window).resize(function() {
			$("#testResults").DataTable().columns.adjust().draw();
		});
	});
</script>
<?php
$testResults = [
    ["Test generateUUID format", "Valid UUIDv4 format", generateUUID(), generateUUID()],
    ["Test getITSMFieldDefinitions structure", "Expected structure for ITSM field definitions", "Sample data", testGetITSMFieldDefinitions()],
    ["Test getRequestFormView structure", "Expected HTML structure for form view", "Sample data", testGetRequestFormView()]
];
?>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header card-header"><i class="fas fa-info-circle fa-lg"></i> <?php echo _("Practicle automated test"); ?></div>
				<div class="card-body">
					<p class="text-sm text-secondary mb-0">
					<table id="testResults" class="table table-responsive table-borderless table-hover" cellspacing="0">
						<thead>
							<tr>
								<th><?php echo _("Test Case"); ?></th>
								<th><?php echo _("Expected Result"); ?></th>
								<th><?php echo _("Actual Result"); ?></th>
								<th><?php echo _("Status"); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($testResults as $result): ?>
								<tr>
									<td><?php echo $result[0]; ?></td>
									<td><?php echo $result[1]; ?></td>
									<td><?php echo $result[2]; ?></td>
									<td class="<?php echo strtolower($result[3]); ?>"><?php echo $result[3]; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include("./footer.php"); ?>