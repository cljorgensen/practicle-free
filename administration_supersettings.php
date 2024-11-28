<?php include("./header.php") ?>
<?php
if (in_array("100000", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<!-- Script for adding button to datatable, format datatable, and define button press rutine -->
<!-- page content -->
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="card">
					<div class="card-header card-header-icon">
						<div class="card-icon">
							<i class="fa fa-cog fa-2x"></i>
						</div>
						<h4 class="card-title"><?php echo _("System settings"); ?></h4>
					</div>
					<div class="card-body">
						<div class="toolbar">
							<!--        Here you can write extra buttons/actions for the toolbar              -->
						</div>
						<?php include("./administration_supersettings_edit.php"); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
<!-- /page content -->
<?php include("./footer.php"); ?>