<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("35", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<?php

$FormID = $_GET['formid'];

$sql = "SELECT Forms.ID, Forms.FormsName
		FROM Forms
		WHERE Forms.ID = $FormID";
$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
	$FormsName = $row["FormsName"];
}

$FormTableName = getTableNameFromFormID($FormID);
?>

<script>
	$(document).ready(function() {
		const data = "";
		let FormID = <?php echo $FormID ?>;

		vData = {
			FormID: FormID
		};

		$.ajax({
			type: "POST",
			url: "./getdata.php?getFormDatarows",
			data: vData,
			dataType: 'JSON',
			success: function(data) {
				var rowData = data[0]; // Get first row data to build columns from.
				// Itereate rowData's object keys to build column definition.
				Object.keys(rowData).forEach(function(key, index) {
					var newkey = key.replace('ImAPlaceHolder', ' ');
					columns.push({
						data: newkey,
						title: newkey
					});
				});

				var table = $('#formstable').DataTable({
					"dom": 'Bfrtip',
					"searching": true,
					"bFilter": true,
					"paging": true,
					"info": false,
					"pagingType": 'numbers',
					"processing": true,
					"deferRender": true,
					"pageLength": 25,
					"orderCellsTop": true,
					"fixedHeader": false,
					"autoWidth": false,
					"aaSorting": [],
					"responsive": true,
					"bSort": true,
					"ordering": false,
					"language": {
						"info": '_START_ <?php echo _("to"); ?> _END_ <?php echo _("of"); ?> _TOTAL_ <?php echo _("Total"); ?>',
						"searchPlaceholder": '<?php echo _("Search"); ?>',
						"search": '',
					},
					"bLengthChange": false,
					"buttons": ['copy', 'excel'],
					"displayLength": 15,
					"searching": true,
					data: data,
					columns: columns
				});
				$('#formstable thead th').each(function() {
					var title = $(this).text();
					$(this).html('<input type=\"search\" class=\"form-control form-control-sm\" placeholder=\"' + title + '\"/>');
				});

				table.columns().every(function() {
					var that = this;

					$('input', this.header()).on('keyup change', function() {
						if (that.search() !== this.value) {
							that
								.search(this.value)
								.draw();
						}
					});
				});

			}
		});
		columns = [];
	});
</script>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header">
					<i class="fas fa-check-square"></i> <a href="administration_formsbuilder.php"><?php echo "Generic Forms" ?></a> <i class="fa fa-angle-double-right"></i> <a href="javascript:location.reload(true);"><?php echo _("$FormsName"); ?></a>
				</div>

				<div class="card-body">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<table id="formstable" class="table table-borderless table-responsive table-hover" cellspacing="0">

							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include("./footer.php"); ?>