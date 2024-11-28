<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array) || in_array("100030", $group_array)) {
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
?>

<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header">
					<i class="fas fa-check-square"></i> <a href="javascript:location.reload(true);"><?php echo _("$FormsName"); ?></a>
					<?php
						if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
							echo "<button type=\"button\" class=\"btn btn-sm btn-info float-end\" onclick=\"location.href='./administration_tableview.php?formid=$FormID'\">" . _("Vis registreringer") . "</button>";
						}
					?>
					
				</div>

				<div class="card-body">
					<div class="row">
						<?php
						$sql = "SELECT Forms_fieldslist.FieldName, Forms_fieldslist.FieldLabel, Forms_fieldslist_types.Definition, Forms_fieldslist.FieldDefaultValue, Forms_fieldslist.fieldtitle, 
								Forms_fieldslist.SelectFieldOptions, Forms_fieldslist.FieldWidth, forms_fieldslist.LookupTable, forms_fieldslist.LookupField, forms_fieldslist.LookupFieldResultView,forms_fieldslist.HideForms,forms_fieldslist.Required,forms_fieldslist.Locked, forms_fieldslist.Addon
								FROM Forms
								LEFT JOIN Forms_fieldslist ON Forms.ID = Forms_fieldslist.RelatedFormID
								LEFT JOIN Forms_fieldslist_types ON Forms_fieldslist.FieldType = Forms_fieldslist_types.ID
								WHERE Forms.ID = $FormID
								ORDER BY Forms_fieldslist.FieldOrder ASC";
						$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
						while ($row = mysqli_fetch_array($result)) {
							$LookupTable = $row["LookupTable"];
							$LookupField = "ID";
							$LookupFieldResult = $row["LookupFieldResultView"];
							$Label = $row["FieldLabel"];
							$FieldName = $row["FieldName"];
							$FieldDefaultValue = $row["FieldDefaultValue"];
							$FieldTitle = $row["fieldtitle"];
							$SelectFieldOptions = $row["SelectFieldOptions"];
							$FieldWidth = $row["FieldWidth"];
							$HideForms = $row["HideForms"];

							$Addon = $row["Addon"];
							if ($Addon) {
								$addonBtn = getModuleFieldAddonBtn($Addon);
								$addonBtn = str_replace("<:FieldName:>", $FieldName, $addonBtn);
							} else {
								$addonBtn = "";
							}
							if ($HideForms == "1") {
								continue;
							}
							$Required = $row["Required"];
							if ($Required == "1") {
								$Required = "required";
								$RequiredLabel = "<code>*</code>";
							} else {
								$Required = "";
								$RequiredLabel = "";
							}

							$Definition = $row["Definition"];
							$Definition = str_replace("<:fieldname:>", $FieldName, $Definition);
							$Definition = str_replace("<:fieldvalue:>", $FieldDefaultValue, $Definition);
							$Definition = str_replace("<:fieldid:>", $FieldName, $Definition);
							$Definition = str_replace("<:label:>", $Label, $Definition);
							$Definition = str_replace("<:fieldtitle:>", $FieldTitle, $Definition);
							$Definition = str_replace("<:required:>", $Required, $Definition);
							$Definition = str_replace("<:requiredlabel:>", $RequiredLabel, $Definition);
							$Definition = str_replace("<:Locked:>", $Locked, $Definition);
							$Definition = str_replace("<:addonBtn:>", " " . $addonBtn, $Definition);
							$Definition = str_replace("<:fieldwidth:>", $FieldWidth, $Definition);
							if (!empty($LookupTable)) {
								$SelectFieldOptions = "";
								$SelectFieldOptions = getCILookupTableSelectOptions($LookupTable, $LookupField, $LookupFieldResult);
							}
							$Definition = str_replace("<:selectoptions:>", $SelectFieldOptions, $Definition);
							echo $Definition;
						}
						?>
					</div>
					<button type="button" class="btn btn-sm btn-success float-end" onclick="createRequest('<?php echo $FormID ?>')"><?php echo _("Create") ?></button>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include("./footer.php"); ?>