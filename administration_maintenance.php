<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>

<div class="row">
	<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
		<div class="card-group">
			<div class="card text-wrap">
				<div class="card-header card-header"> <i class="fa fa-cog fa-lg"></i> <?php echo _("Maintenance"); ?></div>
				<div class="card-body">
					<?php include("./administration_maintenance_edit.php"); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
		<div class="card-group">
			<div class="card text-wrap">
				<div class="card-header card-header"> <i class="fa fa-cog fa-lg"></i> <?php echo _("Diverse"); ?></div>
					<div class="card-body">
						<button id="toggleTable" class="btn btn-sm btn-info">Connection Tests</button>

						<div id="connectionTableContainer" style="display: none;">
							<table id="connectionTestTable" class="table table-borderless table-responsive table-hover" cellspacing="0">
								<thead>
									<tr>
										<th>Date</th>
										<th>Url</th>
									</tr>
								</thead>
								<tbody>
									<?php
										$sql = "SELECT ID, Date, Url FROM connection_test";
										$result = $conn->query($sql);

										while ($row = $result->fetch_assoc()) {
											echo "<tr>
													<td>".$row['Date']."</td>
													<td>".$row['Url']."</td>
												</tr>";
										}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<script>
			$(document).ready(function() {
				// Initialize DataTable
				<?php
					echo initiateStandardSearchTable("connectionTestTable");
				?>

				// Toggle visibility of the DataTable
				$('#toggleTable').click(function() {
					$('#connectionTableContainer').toggle();
				});
			});
			</script>
		</div>
	</div>
</div>

<?php include("./footer.php"); ?>