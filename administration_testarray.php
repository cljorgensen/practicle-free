<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>

<?php

	function fetchJsonData($url)
	{
		// Fetch the JSON data from the URL
		$jsonContent = file_get_contents($url);

		// Decode the JSON data to a PHP array
		$dataArray = json_decode($jsonContent, true);

		return $dataArray;
	}

	// Use the function to fetch data from the URL
	$data = fetchJsonData("https://services.nvd.nist.gov/rest/json/cves/2.0?keywordSearch=geoserver&keywordExactMatch");

	$CVEArray = [];

	foreach ($data['vulnerabilities'] as $entry) {
		$cveData = $entry['cve'];

		// Convert the date to desired format
		$date = new DateTime($cveData['published']);
		$formattedDate = $date->format('Y-m-d H:i:s');

		$cve = [
			'link'  => "<a href='https://nvd.nist.gov/vuln/detail/".$cveData['id']."' target='_new'>https://nvd.nist.gov/vuln/detail/".$cveData['id']."</a>",
			'baseScore' => $cveData['metrics']['cvssMetricV31'][0]['cvssData']['baseScore'],
			'baseSeverity' => $cveData['metrics']['cvssMetricV31'][0]['cvssData']['baseSeverity'],
			'vulnStatus' => $cveData['vulnStatus'],
			'id' => $cveData['id'],
			'published' => $formattedDate,
			'description' => $cveData['descriptions'][0]['value'],
		];
		$CVEArray[] = $cve;
	}

	// Loop through CVEArray and insert each entry into the database
	foreach ($CVEArray as $cve) {
		$stmt = $conn->prepare("INSERT INTO news_cve_entries (CVEID, Link, baseScore, baseSeverity, vulnStatus, published, description) 
								VALUES (?, ?, ?, ?, ?, ?, ?)
								ON DUPLICATE KEY UPDATE
								Link = VALUES(Link),
								baseScore = VALUES(baseScore),
								baseSeverity = VALUES(baseSeverity),
								vulnStatus = VALUES(vulnStatus),
								published = VALUES(published),
								description = VALUES(description)");

		$stmt->bind_param('sssssss', $cve['id'], $cve['link'], $cve['baseScore'], $cve['baseSeverity'], $cve['vulnStatus'], $cve['published'], $cve['description']);

		if (!$stmt->execute()) {
			echo "Error inserting CVE: " . $stmt->error . "<br>";
		}

		$stmt->close();
	}

	$conn->close();

	echo "test start<br><br>";
	$UserID = $_SESSION['id'];
	$ITSMTypeID = "2";
	$TemplateID = "3";

	$sql = "SELECT itsm_templates.FieldsValues
			FROM itsm_templates
			WHERE ID = $TemplateID;";

	$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

	while ($row = mysqli_fetch_array($result)) {
		$FieldsValues = $row['FieldsValues'];
	}

	$FieldsValuesArray = explode("<#>", $FieldsValues);
	$FieldsValuesArrayImploded = implode($FieldsValuesArray);
	$TempArray = explode("<;>", $FieldsValuesArrayImploded);

	foreach ($FieldsValuesArray as $key => $value) {
		$TempArray = explode("<;>", $value);
		//$TempArray = implode(":",$TempArray);
		$TempArrayFinal[] = array("FieldName" => $TempArray[0],"FieldValue" => $TempArray[1]);
	}

	$Array[] = array("Result" => "success");
	echo json_encode($Array);

	echo "<br><br>test end";
?>

<!-- /page content -->
<?php include("./footer.php"); ?>