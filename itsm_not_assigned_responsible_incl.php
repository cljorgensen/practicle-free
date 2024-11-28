<?php
if (in_array("100001", $group_array) || in_array("100002", $group_array) || in_array("100003", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<script>
	$(document).ready(function() {

		<?php initiateStandardSearchTable("dispatchqueticketsnoresponsible"); ?>

	});
</script>
<!-- the table view -->
<table id="dispatchqueticketsnoresponsible" class="table table-responsive table-borderless table-hover" cellspacing="0">
	<thead>
		<tr>
			<th><?php echo _('ID'); ?></th>
			<th></th>
			<th><?php echo _('Subject'); ?></th>
			<th><?php echo _('Description'); ?></th>
			<th><?php echo _('Priority'); ?></th>
			<th><?php echo _('Status'); ?></th>
			<th><?php echo _('Company'); ?></th>
			<th><?php echo _('Customer'); ?></th>
			<th><?php echo _('Created'); ?></th>
		</tr>
	</thead>
	<?php
	$sql = "SELECT $ITSMTableName.ID AS ITSMID, $ITSMTableName.Subject, $ITSMTableName.Description, $ITSMTableName.Status, $ITSMTableName.Priority, $ITSMTableName.Created, $ITSMTableName.Customer, $ITSMTableName.Responsible, $ITSMTableName.Team, companies.Companyname, teams.Teamname
			FROM $ITSMTableName
			LEFT JOIN companies ON $ITSMTableName.RelatedCompanyID = companies.ID
			LEFT JOIN teams ON $ITSMTableName.Team = teams.ID
			WHERE Responsible IS NULL AND Team IS NOT NULL AND Status NOT IN (6,7,8)
			ORDER BY $ITSMTableName.ID ASC";

	$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
	?>
	<tbody>
		<?php while ($row = mysqli_fetch_array($result)) { ?>
			<?php
			$ITSMID = $row['ITSMID'];
			$Subject = mb_strimwidth(strip_tags(html_entity_decode($row['Subject'])), 0, 50, "...");
			$DescriptionText = mb_strimwidth(strip_tags(html_entity_decode($row['Description'])), 0, 50, "...");
			$CustomerID = $row['Customer'];
			$ResponsibleID = $row['Responsible'];
			$CustomerName = $functions->getUserFullName($CustomerID);
			$ResponsibleName = $functions->getUserFullName($ResponsibleID);
			$AllowDelete = "0";
			$View = "<a href=\"javascript:viewITSM('$ITSMID','$ITSMTypeID','$AllowDelete','modal');\"><span class=\"badge bg-gradient-success\"><i class=\"fa fa-pen-to-square\"></i></span></a>";
			?>
			<tr class='text-sm text-secondary mb-0'>
				<td><?php echo $ITSMID; ?> </td>
				<td><?php echo $View; ?> </td>
				<td><a href="javascript:void(0);" title="<?php echo strip_tags(html_entity_decode($row['Subject'])) ?>"><?php echo $Subject ?></a></td>
				<td><a href="javascript:void(0);" title="<?php echo strip_tags(html_entity_decode($row['Description'])) ?>"><?php echo $DescriptionText ?></a></td>
				<td><?php echo $row['Priority']; ?></td>
				<td><?php echo $row['Status']; ?></td>
				<td><?php echo $row['Companyname']; ?></td>
				<td><?php echo $CustomerName; ?></td>
				<td><?php echo $myFormatForView = convertToDanishTimeFormat($row['Created']); ?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>