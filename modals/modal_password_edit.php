<?php
if (in_array("100001", $group_array) || in_array("100018", $group_array) || in_array("100019", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<script>
	$(document).ready(function() {
		
	});
</script>
