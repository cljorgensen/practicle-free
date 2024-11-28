<!-- Start settings list -->
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>

<script>
	$(document).ready(function() {
		<?php initiateStandardSearchTable("TableMailTemplates"); ?>
	});
</script>

<script>
	function editMailTemplate(settingid) {

		var url = './getdata.php?popModaleditMailTemplates=' + settingid;
		$("#editMailTemplatesEditModal").modal('show')
		$.ajax({
			url: url,
			data: {
				data: settingid
			},
			type: 'POST',
			success: function(data) {
				var obj = JSON.parse(data);
				for (var i = 0; i < obj.length; i++) {

					document.getElementById('modalMailTemplateID').value = obj[i].ID;
					document.getElementById('modalMailTemplateSubject').value = obj[i].Subject;
					document.getElementById('modalMailTemplateContent').value = obj[i].Content;
					document.getElementById('modalMailTemplateUpdated').value = obj[i].Updated;
					document.getElementById('modalMailTemplateUpdatedBy').value = obj[i].UpdatedBy;
				}
			}
		});
	}
</script>

<!-- the table view -->
<table id="TableMailTemplates" class="table table-responsive table-borderless table-hover" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo _("ID"); ?></th>
			<th></th>
			<th><?php echo _("Description"); ?></th>
			<th><?php echo _("Updated"); ?></th>
			<th><?php echo _("Updated by"); ?></th>
			<th><?php echo _("Image"); ?></th>
		</tr>
	</thead>
	<?php
	$sql = "SELECT mail_templates.ID, mail_templates.Title, mail_templates.Content, mail_templates.Updated, mail_templates.UpdatedBy, mail_templates.RelatedTo 
			FROM mail_templates";

	$result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

	?>
	<tbody>
		<?php while ($row = mysqli_fetch_array($result)) { ?>
			<?php
			$ID = $row['ID'];
			$Updated = $row['Updated'];
			$Updated = convertToDanishTimeFormat($Updated);
			$UpdatedBy = $row['UpdatedBy'];
			$UpdatedByUsername = getUserName($row['UpdatedBy']);
			$UpdatedBy = @$functions->getUserFullName($UpdatedBy);
			$RelatedTo = $row['RelatedTo'];
			if (empty($RelatedTo)) {
				$Url = "";
			} else {
				$Url = "<a class=\"spotlight\" href=\"./images/misc/$RelatedTo\">" . @$functions->translate("Click to see") . "</a>";
			}

			?>
			<tr class='text-sm text-secondary mb-0'>
				<td><?php echo $ID; ?></td>
				<td><?php echo "<a href='javascript:void(0);" . $row['ID'] . "'><span class='badge badge-pill bg-gradient-success' onclick='editMailTemplate($ID);'><i class='fa fa-pen-to-square'></i></span></a>"; ?>
				</td>
				<td><?php echo $row['Title']; ?></td>
				<td><?php echo $Updated; ?></td>
				<td><?php echo $UpdatedBy . " ($UpdatedByUsername)"; ?></td>
				<td><?php echo $Url; ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>

<!-- Script for modal popup change settings -->
<script>
	function submitMailTemplateChange() {
		var mailtemplateid = $('#modalMailTemplateID').val();
		var mailtemplatesubject = $('#modalMailTemplateSubject').val();
		var mailtemplateContent = $('#modalMailTemplateContent').val();
		var mailtemplateUpdated = $('#modalMailTemplateUpdated').val();
		var mailtemplateUpdatedBy = $('#modalMailTemplateUpdatedBy').val();

		var urlget = './getdata.php?changeMailTemplate';

		var vData = {
			mailtemplateid: mailtemplateid,
			mailtemplatesubject: mailtemplatesubject,
			mailtemplateContent: mailtemplateContent,
			mailtemplateUpdatedBy: mailtemplateUpdatedBy
		};

		$.ajax({
			url: urlget,
			data: vData,
			type: 'POST',
			success: function(data) {
				$("#editMailTemplatesEditModal").modal('hide');
				location.reload(true);
			}
		});
	}
</script>
<!-- Modal for edit settings -->
<div class="modal fade" id="editMailTemplatesEditModal" tabindex="-1" role="dialog" aria-labelledby="editMailTemplatesEditModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h6 class="modal-title font-weight-normal" id="editMailTemplatesEditModalLabel"><?php echo _('Edit'); ?></h6>
				<button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p class="statusMsg"></p>
				<a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseTags" aria-expanded="false" aria-controls="collapseTags"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Placeholders") ?>"></i> <?php echo _("Placeholders") ?></a>
				<div id="collapseTags" class="collapse width">
					<div class="card-body">
						<div class="row">
							<div class="col-md-12 col-sm-12 col-xs-12">
								<small>
									[<:mailcode:>#<:itsmnumber:>] - <?php echo _("this is essential in the subject for the mail reply by the customer to land on specific type and number"); ?>
								</small>
								<br>
								<small>
									<:mailcode:> - <?php echo _("this is essential in the subject for the mail reply by the customer to land on specific type"); ?>
								</small>
								<br>
								<small>
									<:itsmnumber:> - <?php echo _("placeholder for current incident/change/request/problem number"); ?>
								</small>
								<br>
								<small>
									<:firstname:> - <?php echo _("placeholder for customers firstname"); ?>
								</small>
								<br>
								<small>
									<:lastname:> - <?php echo _("placeholder for customers lastname"); ?>
								</small>
								<br>
								<small>
									<:comment:> - <?php echo _("if action is a comment - this is the placeholder for the comment"); ?>
								</small>
								<br>
								<small>
									<:systemname:> - <?php echo _("placeholder for the system name"); ?>
								</small>
								<br>
								<small>
									<:systemurl:> - <?php echo _("placeholder for the system url"); ?>
								</small>
								<br>
								<small>
									<:itsmtypename:> - <?php echo _("placeholder for the name of the type"); ?>
								</small>
								<br>
								<small>
									<:internalviewlink:> - <?php echo _("placeholder for direct link to the specific incident/change/request/problem (can not be accessed by customers)"); ?>
								</small>
								<br>
								<small>
									<:subject:> - <?php echo _("placeholder for subject on the specific item)"); ?>
								</small>
							</div>
						</div>
					</div>
				</div>
				<br>
				<br>
				<form role="form">
					<div class="form-group">
						<div class="col-md-4 col-sm-4 col-xs-12" hidden>
							<div class="input-group input-group-static mb-4">
								<label for="modalMailTemplateID"><?php echo _("ID"); ?></label>
								<input type="text" class="form-control" id="modalMailTemplateID" disabled>
							</div>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalMailTemplateSubject"><?php echo _("Subject"); ?></label>
							<input type="text" class="form-control" id="modalMailTemplateSubject">
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group input-group-static mb-4">
							<label for="modalMailTemplateContent"><?php echo _("Content"); ?></label>
							<textarea type="text" class="form-control" id="modalMailTemplateContent" rows="10"></textarea>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" hidden>
						<div class="input-group input-group-static mb-4">
							<label for="modalMailTemplateUpdated"><?php echo _("Updated"); ?></label>
							<input type="text" class="form-control" id="modalMailTemplateUpdated" disabled>
						</div>
					</div>

					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" hidden>
						<div class="input-group input-group-static mb-4">
							<label for="modalMailTemplateUpdatedBy"><?php echo _("Updated by"); ?></label>
							<input type="text" class="form-control" id="modalMailTemplateUpdatedBy" disabled>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-success" onclick="submitMailTemplateChange()"><?php echo _("Save"); ?></button>
			</div>
		</div>
	</div>
</div>
<!-- End Modal for edit settings -->