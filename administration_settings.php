<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card text-wrap">
				<div class="card-header card-header"> <i class="fa fa-cog fa-lg"></i> <?php echo _("System settings"); ?></div>
				<div class="card-body">
					<?php include("./administration_settings_edit.php"); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include("./footer.php"); ?>