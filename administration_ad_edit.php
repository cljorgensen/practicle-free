<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<script>
	function importADTeams() {
		console.log("importADTeams called");

		var url = './getdata.php?importADTeams';
		$.ajax({
			url: url
		}).done(function(data) {
			console.log(data);
			pnotify("Teams imported","success");
		});
	}

	function updateAdministratorsFromLDAP() {
		$('#TableADUsers').DataTable().clear().destroy();

		var table = $('#TableADUsers').DataTable({
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
			"buttons": [{extend: 'copy', className: 'btn btn-sm btn-secondary'},{extend: 'excel', className: 'btn btn-sm btn-secondary'},{extend: 'csv', className: 'btn btn-sm btn-secondary'}],
			"bStateSave": true,
			"iCookieDuration": 120,
			"displayLength": 25,
			"searching": true,
			"ajax": {
				"url": "./getdata.php?updateAdministratorsFromLDAP",
				"type": "GET",
				"dataSrc": ""
			},
			"columnDefs": [{
				"targets": 0,
				"data": "Username"
			}, {
				"targets": 1,
				"data": "Fullname"
			}, {
				"targets": 2,
				"data": "Firstname"
			}, {
				"targets": 3,
				"data": "Lastname"
			}, {
				"targets": 4,
				"data": "Email"
			}, {
				"targets": 5,
				"data": "Status"
			}, {
				"targets": 6,
				"data": "Action"
			}]
		});

		$('#TableADUsers thead th').each(function() {
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

	function importADUsers() {

		var url = './getdata.php?importADUsers';
		var temp = "go";

		var vData = {
			temp: temp
		};

		$.ajax({
			url: url,
			data: vData,
			type: 'GET',
			success: function(data) {
				var obj = JSON.parse(data);
				for (var i = 0; i < obj.length; i++) {
					var Antal = obj[i].Antal;
					var message = Antal + " brugere blev behandlet";
					pnotify(message, 'success');
				}
			}
		});

		$('#TableADUsers').DataTable().clear().destroy();

		var table = $('#TableADUsers').DataTable({
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
			"buttons": [{extend: 'copy', className: 'btn btn-sm btn-secondary'},{extend: 'excel', className: 'btn btn-sm btn-secondary'},{extend: 'csv', className: 'btn btn-sm btn-secondary'}],
			"bStateSave": true,
			"iCookieDuration": 120,
			"displayLength": 25,
			"searching": true,
			"ajax": {
				"url": "./getdata.php?prepareImportADUsers",
				"type": "GET",
				"dataSrc": ""
			},
			"columnDefs": [{
				"targets": 0,
				"data": "Username"
			}, {
				"targets": 1,
				"data": "Fullname"
			}, {
				"targets": 2,
				"data": "Firstname"
			}, {
				"targets": 3,
				"data": "Lastname"
			}, {
				"targets": 4,
				"data": "Email"
			}, {
				"targets": 5,
				"data": "Status"
			}, {
				"targets": 6,
				"data": "Action"
			}]
		});

		$('#TableADUsers thead th').each(function() {
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

	function prepareImportADUsers() {

		$('#TableADUsers').DataTable().clear().destroy();

		var table = $('#TableADUsers').DataTable({
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
			"buttons": [{extend: 'copy', className: 'btn btn-sm btn-secondary'},{extend: 'excel', className: 'btn btn-sm btn-secondary'},{extend: 'csv', className: 'btn btn-sm btn-secondary'}],
			"bStateSave": true,
			"iCookieDuration": 120,
			"displayLength": 25,
			"searching": true,
			"ajax": {
				"url": "./getdata.php?prepareImportADUsers",
				"type": "GET",
				"dataSrc": ""
			},
			"columnDefs": [{
				"targets": 0,
				"data": "Username"
			}, {
				"targets": 1,
				"data": "Fullname"
			}, {
				"targets": 2,
				"data": "Firstname"
			}, {
				"targets": 3,
				"data": "Lastname"
			}, {
				"targets": 4,
				"data": "Email"
			}, {
				"targets": 5,
				"data": "Status"
			}, {
				"targets": 6,
				"data": "Action"
			}]
		});

		$('#TableADUsers thead th').each(function() {
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
	};
</script>
<button type="button" class="btn btn-sm btn-success" onclick="prepareImportADUsers()"><?php echo _("Retrieve users"); ?></button>
<button type="button" class="btn btn-sm btn-danger" onclick="importADUsers()"><?php echo _("Import users"); ?></button>
<button type="button" class="btn btn-sm btn-danger" onclick="updateAdministratorsFromLDAP()"><?php echo _("update Administrators"); ?></button>
<button type="button" class="btn btn-sm btn-danger" onclick="importADTeams()"><?php echo _("Import teams"); ?></button>

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<table id="TableADUsers" class="table table-borderless dt-responsive table-hover" cellspacing="0">
		<thead>
			<tr class='text-sm text-secondary mb-0 text-wrap'>
				<th><?php echo _("Username"); ?></th>
				<th><?php echo _("Name"); ?></th>
				<th><?php echo _("Firstname"); ?></th>
				<th><?php echo _("Lastname"); ?></th>
				<th><?php echo _("Email"); ?></th>
				<th><?php echo _("Status"); ?></th>
				<th><?php echo _("Action"); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr class='text-sm text-secondary mb-0 text-wrap'>
				<td class='text-sm text-secondary mb-0 text-wrap'></td>
				<td class='text-sm text-secondary mb-0 text-wrap'></td>
				<td class='text-sm text-secondary mb-0 text-wrap'></td>
				<td class='text-sm text-secondary mb-0 text-wrap'></td>
				<td class='text-sm text-secondary mb-0 text-wrap'></td>
				<td class='text-sm text-secondary mb-0 text-wrap'></td>
				<td class='text-sm text-secondary mb-0 text-wrap'></td>
			</tr>
		</tbody>
	</table>
</div>