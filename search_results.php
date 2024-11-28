<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100014", $group_array) || in_array("5", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}

?>

<script>
	$(document).ready(function() {
		<?php initiateStandardSearchTable("TableSearchResults"); ?>
	});
</script>
<?php
$SearchTerm = $_GET["SearchTerm"];
$Result = QuickSearch($SearchTerm);
?>
<script>
	$(document).ready(function() {
		document.getElementById("searchfield").value = '<?php echo $SearchTerm ?>';
		var searchlabel = document.getElementById("searchlabel");
		searchlabel.style.display = "none";
	});
</script>
<div class="row">
	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header card-header"><i class="fab fa-searchengin fa-lg"></i> <?php echo _("Search results"); ?></div>
				<div class="card-body">
					<table id="TableSearchResults" class="table table-responsive table-borderless table-hover" cellspacing="0">
						<thead>
							<tr>
								<th><?php echo _('ID'); ?></th>
								<th><?php echo _('Subject'); ?></th>
								<th><?php echo _('Type'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($Result as $item) {
								$ID = $item['ID'];
								$Subject = $item['Subject'];
								$Module = $item['Module'];
								$ModuleID = $item['ModuleID'];
								$URL = $item['URL'];

								echo "<tr class='text-sm text-secondary mb-0'>
										<td>$ID</td>
										<td>$URL</td>
										<td>$Module</td>										
									</tr>";
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header card-header"><i class="fab fa-searchengin fa-lg"></i> <?php echo _("Preview"); ?></div>
				<div class="card-body">

				</div>
			</div>
		</div>
	</div>
</div>

<?php include("./footer.php"); ?>