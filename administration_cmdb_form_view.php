<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100029", $group_array) || in_array("100030", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<?php

$CIID = $_GET['id'];

$sql = "SELECT cmdb_cis.ID, cmdb_cis.Name
		FROM cmdb_cis
		WHERE cmdb_cis.ID = $CIID";
$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
	$CIName = $row["Name"];
}
?>

<script>
	function createRequest() {
		FormID = "<?php echo $CIID ?>";
		const obj = Array.from(document.querySelectorAll('[id^=FormField]'));

		const Temp = [];

		for (var i = 0; i < obj.length; i++) {
			let Temp2;
			Temp2 = [{
				fieldname: obj[i].id,
				name: obj[i].parentNode.firstElementChild.innerHTML,
				value: obj[i].value
			}];
			Temp.push(...Temp2);
		}

		var jsonString = JSON.stringify(Temp);

		vData = {
			formid: FormID,
			jsonString: jsonString
		};

		$.ajax({
			type: "GET",
			url: "./getdata.php?updateFormFieldValue",
			data: vData,
			success: function(data) {
				var obj = JSON.parse(data);
				console.log(obj);
				for (var i = 0; i < obj.length; i++) {
					var Result = obj[i].Result;
					if (Result == "success") {
						message = "Request created";
						localStorage.setItem('pnotify', message);
						location.reload(true);
					}
					if (Result == "notarequest") {
						message = "Success";
						localStorage.setItem('pnotify', message);
						location.reload(true);
					}
					if (Result == "error") {
						message = "An error occured";
						pnotify(message, "danger");
					}
				}
			}
		});

	}
</script>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header">
					<i class="fa fa-desktop"></i> <a href="javascript:location.reload(true);"><?php echo _("$CIName"); ?></a>
					<?php
						if (in_array("100001", $group_array) || in_array("100029", $group_array)) {
							echo "<button type=\"button\" class=\"btn btn-sm btn-info float-end\" onclick=\"location.href='./cmdb_tableview_cis.php?id=$CIID'\">" . _("Vis registreringer") . "</button>";
						}
					?>
				</div>

				<div class="card-body">
					<div class="row">
						<?php
							$sql = "SELECT cmdb_ci_fieldslist.FieldName, cmdb_ci_fieldslist.FieldLabel, cmdb_fieldslist_types.Definition, cmdb_ci_fieldslist.FieldDefaultValue, cmdb_ci_fieldslist.fieldtitle, cmdb_ci_fieldslist.SelectFieldOptions, cmdb_ci_fieldslist.FieldWidth,
									cmdb_ci_fieldslist.LookupTable, cmdb_ci_fieldslist.LookupField, cmdb_ci_fieldslist.LookupFieldResultView
									FROM cmdb_cis
									LEFT JOIN cmdb_ci_fieldslist ON cmdb_cis.ID = cmdb_ci_fieldslist.RelatedCITypeID
									LEFT JOIN cmdb_fieldslist_types ON cmdb_ci_fieldslist.FieldType = cmdb_fieldslist_types.ID
									WHERE cmdb_cis.ID = $CIID
									ORDER BY cmdb_ci_fieldslist.FieldOrder ASC;";
							$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
							while ($row = mysqli_fetch_array($result)) {
								$LookupTable = $row["LookupTable"];
								$LookupField = $row["LookupField"];
								$LookupFieldResult = $row["LookupFieldResultView"];
								$Label = $row["FieldLabel"];
								$FieldName = $row["FieldName"];
								$FieldDefaultValue = $row["FieldDefaultValue"];
								$FieldTitle = $row["fieldtitle"];
								$SelectFieldOptions = base64_decode($row["SelectFieldOptions"]);
								$FieldWidth = $row["FieldWidth"];
								$Definition = $row["Definition"];
								$Definition = str_replace("<:fieldname:>", $FieldName, $Definition);
								$Definition = str_replace("<:fieldvalue:>", $FieldDefaultValue, $Definition);
								$Definition = str_replace("<:fieldid:>", $FieldName, $Definition);
								$Definition = str_replace("<:label:>", $Label, $Definition);
								$Definition = str_replace("<:fieldtitle:>", $FieldTitle, $Definition);
								$Definition = str_replace("<:required:>", $Required, $Definition);
								$Definition = str_replace("<:requiredlabel:>", $RequiredLabel, $Definition);
								$Definition = str_replace("<:Locked:>", $Locked, $Definition);
								if(!empty($LookupTable)){
									$SelectFieldOptions = "";
									$SelectFieldOptions = getCILookupTableSelectOptions($LookupTable, $LookupField, $LookupFieldResult);
								}
								$Definition = str_replace("<:selectoptions:>", $SelectFieldOptions, $Definition);
								$Definition = str_replace("<:fieldwidth:>", $FieldWidth, $Definition);
								$Definition = str_replace("<:languagecode:>", $languageshort, $Definition);
								$Definition = str_replace("<:addonBtn:>", " " . $addonBtn, $Definition);
								echo $Definition;
							}
						?>
					</div>					
				</div>
			</div>
		</div>
	</div>
</div>

<?php include("./footer.php"); ?>