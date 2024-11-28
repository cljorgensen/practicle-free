<!-- Start settings list -->
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<div class="row">
	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
		<h6><?php echo _("Database performance") ?></h6>
		<button class="btn btn-sm btn-info" id="btndeleteSelectedBackup" onclick="fetchQueryTimes();"><?php echo _("Test"); ?></button>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
		<table id="performanceResult" class="table table-borderless table-responsive table-hover">
			<thead id="perfTH" hidden>
				<tr>
					<th>Query</th>
					<th>Execution Time (seconds)</th>
					<th>Performance</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
<div class="row">
	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
		<h6><?php echo _("Database backup") ?></h6>
		<div class="input-group input-group-static mb-4">
			<label for="backups" class="ms-0"><?php echo _("Backups"); ?></label>
			<select id="backups" name="backups" class="form-control" required>
				<?php
				$sql = "SELECT ID, Date, Description
						FROM db_backups
						WHERE RelatedModule IS NULL
						ORDER BY Date DESC";
				$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
				while ($row = mysqli_fetch_array($result)) {
					echo "<option value='" . $row['ID'] . "'>" . convertToDanishTimeFormat($row['Date']) . " (" . $row['Description'] . ")</option>";
				}
				?>
			</select>
		</div>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
		<br>
		<button class="btn btn-sm btn-info" id="btncreateDatabaseBackup" onclick="createDatabaseBackup('');"><?php echo _("Create"); ?></button>
		<button class="btn btn-sm btn-info" id="btnrestoreSelectedBackup" onclick="restoreSelectedBackup('');"><?php echo _("Restore"); ?></button>
		<button class="btn btn-sm btn-danger" id="btndeleteSelectedBackup" onclick="deleteSelectedBackup('');"><?php echo _("Delete"); ?></button>
	</div>
</div>
<br>
<div class="row">
	<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
		<h6><?php echo _("Optimize database") ?></h6>
		<button class="btn btn-sm btn-info" id="btnoptimizeDatabase" onclick="optimizeDatabase('');"><?php echo _("Optimize database"); ?></button>
	</div>
</div>