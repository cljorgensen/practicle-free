<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array) || in_array("100024", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<script>
	function runmodalNewLanguageEntry() {
		$("#modalNewLanguageEntry").modal('show');
	};
</script>
<?php include("./modals/modal_edit_language.php"); ?>
<?php include("./modals/modal_add_language.php"); ?>

<script>
	$(document).ready(function() {
		getDataTableEntries('<?php echo $UserLanguageCode ?>', 'LanguageTable', 'getLanguageEntries');
	});
</script>

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card">
			<div class="card-header card-header"><i class='fas fa-language fa-lg'></i> <a href="javascript:location.reload();"><?php echo _("Languages"); ?></a>
				<div class="float-end">
					<button type="submit" name="submit_generatefiles" class="btn btn-sm btn-warning" onclick="generateLanguageFiles();"><?php echo _("Generate files") ?></button>
				</div>
				<div class="float-end">
					<button class="btn btn-sm btn-success" onclick="runmodalNewLanguageEntry()"><?php echo _("Add") ?></button>
				</div>
			</div>

			<div class="card-body">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<table id="LanguageTable" class="table-hover wrap" cellspacing="0">
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- /page content -->
<?php include("./footer.php"); ?>