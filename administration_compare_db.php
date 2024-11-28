<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}

?>
<form action="administration_compare_db.php?compareDatabases" method="get">
	<div class="row">
		<div class="col-lg-3 col-sm-12 col-xs-12">
			<div class="input-group input-group-static mb-4">
				<label for="SourceDatabase" data-bs-toggle="tooltip" data-bs-title="select source database">SourceDatabase</label>
				<select class="form-control" id="SourceDatabase" name="SourceDatabase" data-bs-toggle="tooltip" data-bs-title="select source database">
					<?php
					$sql = "SHOW Databases;";

					$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

					while ($row = mysqli_fetch_array($result)) {
						$dbtemp = $row[0];
						if (strpos($dbtemp, "practicle_") !== false) {
							if ($dbtemp === $dbname) {
								echo "<option value=\"$dbtemp\" selected>$dbtemp</option>";
							} else {
								echo "<option value=\"$dbtemp\">$dbtemp</option>";
							}
						}
					}

					mysqli_free_result($result);
					?>
				</select>
			</div>
		</div>
		<div class="col-lg-3 col-sm-12 col-xs-12">
			<div class="input-group input-group-static mb-4">
				<label for="DestinationDatabase" data-bs-toggle="tooltip" data-bs-title="select source database">DestinationDatabase</label>
				<select class="form-control" id="DestinationDatabase" name="DestinationDatabase" data-bs-toggle="tooltip" data-bs-title="select source database">
					<?php
					$sql = "SHOW Databases;";

					$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

					while ($row = mysqli_fetch_array($result)) {
						$dbtemp = $row[0];
						if (strpos($dbtemp, "practicle_") !== false) {
							if ($dbtemp === "practicle_base") {
								echo "<option value=\"$dbtemp\" selected>$dbtemp</option>";
							} else {
								echo "<option value=\"$dbtemp\">$dbtemp</option>";
							}
						}
					}

					mysqli_free_result($result);
					?>
				</select>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-3 col-sm-12 col-xs-12">
			<button class="submut btn btn-sm btn-success float-end">Compare</button>
		</div>
	</div>
</form>
<?php
if (isset($_GET['SourceDatabase']) && isset($_GET['DestinationDatabase'])) {
	$Array = [];
	if (in_array("100001", $UserGroups)) {
	} else {
		$GroupName = getUserGroupName("100001");
		//$array[] = array("ID" => "1", "FieldName" => "You need to be member of $GroupName");
		$array[] = array("CompareResult" => "You need to be member of $GroupName");
		echo json_encode($array);
		return;
	}
	$SourceDatabase = $_GET["SourceDatabase"];
	$DestinationDatabase = $_GET["DestinationDatabase"];
	$CompareResult = compareDatabases($SourceDatabase, $DestinationDatabase, $dbusername, $dbpassword);
	echo $CompareResult;
}
?>
<?php include("./footer.php"); ?>