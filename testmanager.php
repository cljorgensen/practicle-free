<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<div class="content">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8 col-sm-8 col-xs-12">
				<div class="card">
					<div class="card-header card-header-icon">
						<div class="card-icon">
							<i class="fa fa-book fa-2x"></i>
						</div>
						<h4 class="card-title"><?php echo _("Validation Test"); ?>
							<form method="post">
								<button type="submit" name="submit_run_validation_test" class="btn btn-sm btn-info float-end">Run test now</button>
							</form>
						</h4>
					</div>
					<div class="card-body">
						<div class="toolbar">
							<!--        Here you can write extra buttons/actions for the toolbar              -->
						</div>
						<?php include("./test/result.html") ?>
						<?php
						if (isset($_POST['submit_run_validation_test'])) {
							chdir(dirname(__FILE__));
							exec("./test/runtest");
							echo "<script type='text/javascript'>
								$(document).ready(function(e) {
								pnotify('Validation test completed - reload page','success');
								location.href = 'testmanager.php';
								});
								</script>";
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /page content -->
<?php include("./footer.php"); ?>