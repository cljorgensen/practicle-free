<?php
include("./header.php");
?>
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<?php
$DefaultDesign = @$functions->getSettingValue(20);
$GoogleAuth = "0";
$SMTPPort = @$functions->getSettingValue(26);
$SMTPHost = @$functions->getSettingValue(28);
$SMTPUsername = "smtp_username@companyname.dk";
$SMTPPassword = generateRandomString(16);
$SystemURL = "https://".$_SERVER['HTTP_HOST'];

$IMAPHost = @$functions->getSettingValue(38);
$IMAPUsername = "imap_username@companyname.dk";
$IMAPPassword = generateRandomString(16);

$APIToken = generateRandomString(50);

$FBLink = @$functions->getSettingValue(45);
$CPLink = @$functions->getSettingValue(46);
$LinkedInLink = @$functions->getSettingValue(47);

$UserViaEmail = "0";
$defaultCompany = @$functions->getSettingValue(48);
?>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card">
			<div class="card-header card-header"><a href="javascript:location.reload();"><?php echo _("System Configurator") ?></a>
			</div>
			<div class="card-body">
				<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
					<form id="regForm" action="">
						<div class="tab wizzard">
							<h6><?php echo _("System") ?></h6>
							<div class="row">
								<div class="col-md-4 col-sm-4 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="SystemName" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Max 25 characters") ?>"><?php echo _("Name of the system") ?><code>*</code></label>
										<input type="text" class="form-control" id="SystemName" name="SystemName" maxlength="25" autocomplete="off" required value="<?php echo $SystemName ?>">
									</div>
								</div>
								<div class="col-md-4 col-sm-4 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="SystemURL" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Please give the main url for the system") ?>"><?php echo _("System URL") ?><code>*</code></label>
										<input type="text" class="form-control" id="SystemURL" name="SystemURL" maxlength="25" autocomplete="off" required value="<?php echo $SystemURL ?>">
									</div>
								</div>
								<div class="col-md-2 col-sm-2 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="DefaultDesign"><?php echo _("Default Design") ?><code>*</code></label>
										<select id="DefaultDesign" name="DefaultDesign" class="form-control" required>
											<?php
											if ($DefaultDesign == "dark") {
												echo "<option value='dark' selected='true'>dark</option>";
												echo "<option value='light'>light</option>";
											} else {
												echo "<option value='dark'>dark</option>";
												echo "<option value='light' selected='true'>light</option>";
											}
											?>
										</select>
									</div>
								</div>
								<div class="col-md-2 col-sm-2 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="DefaultDesign" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Allow people to activate google authenticater for 2 faktor validation?") ?>"><?php echo _("Google Authenticate") ?><code>*</code></label>
										<select id="GoogleAuth" name="GoogleAuth" class="form-control" required>
											<?php
											if ($GoogleAuth == "1") {
												echo "<option value='0'>" . _("No") . "</option>";
												echo "<option value='1' selected='true'>" . _("Yes") . "</option>";
											} else {
												echo "<option value='0' selected='true'>" . _("No") . "</option>";
												echo "<option value='1'>" . _("Yes") . "</option>";
											}
											?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="tab wizzard">
							<h6><?php echo _("Email account for sending emails from") ?></h6>
							<div class="row">
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="smtpPort"><?php echo _("SMTP Port") ?><code>*</code></label>
										<input type="text" class="form-control" id="smtpPort" name="smtpPort" autocomplete="off" value="<?php echo $SMTPPort ?>" required>
									</div>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="col-md-12 col-sm-12 col-xs-12">
										<div class="input-group input-group-static mb-4">
											<label for="SMTPHost"><?php echo _("SMTP Host") ?><code>*</code></label>
											<input type="text" class="form-control" id="SMTPHost" name="SMTPHost" autocomplete="off" value="<?php echo $SMTPHost ?>" required>
										</div>
									</div>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="col-md-12 col-sm-12 col-xs-12">
										<div class="input-group input-group-static mb-4">
											<label for="SMTPUsername"><?php echo _("SMTP Username") ?><code>*</code></label>
											<input type="text" class="form-control" id="SMTPUsername" name="SMTPUsername" autocomplete="off" value="<?php echo $SMTPUsername ?>" required>
										</div>
									</div>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="col-md-12 col-sm-12 col-xs-12">
										<div class="input-group input-group-static mb-4">
											<label for="SMTPPassword"><?php echo _("SMTP Password") ?><code>*</code></label>
											<input type="text" class="form-control" id="SMTPPassword" name="SMTPPassword" autocomplete="off" value="<?php echo $SMTPPassword ?>" required>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="tab wizzard">
							<h6><?php echo _("Email account for recieving emails to Practicle") ?></h6>
							<div class="row">
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="col-md-12 col-sm-12 col-xs-12">
										<div class="input-group input-group-static mb-4">
											<label for="IMAPHost"><?php echo _("IMAP Host") ?><code>*</code></label>
											<input type="text" class="form-control" id="IMAPHost" name="IMAPHost" autocomplete="off" value="<?php echo $IMAPHost ?>" required>
										</div>
									</div>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="col-md-12 col-sm-12 col-xs-12">
										<div class="input-group input-group-static mb-4">
											<label for="IMAPUsername"><?php echo _("IMAP Username") ?><code>*</code></label>
											<input type="text" class="form-control" id="IMAPUsername" name="IMAPUsername" autocomplete="off" value="<?php echo $IMAPUsername ?>" required>
										</div>
									</div>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="col-md-12 col-sm-12 col-xs-12">
										<div class="input-group input-group-static mb-4">
											<label for="IMAPPassword"><?php echo _("IMAP Password") ?><code>*</code></label>
											<input type="text" class="form-control" id="IMAPPassword" name="IMAPPassword" autocomplete="off" value="<?php echo $IMAPPassword ?>" required>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="tab wizzard">
							<h6><?php echo _("API") ?></h6>
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="APIToken" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Please make a long token") ?>"><?php echo _("API Token") ?><code>*</code></label>
										<input type="text" class="form-control" id="APIToken" name="APIToken" minlength="50" autocomplete="off" value="<?php echo $APIToken ?>" required>
									</div>
								</div>
							</div>
						</div>
						<div class="tab wizzard">
							<h6 data-bs-toggle="tooltip" data-bs-title="<?php echo _("These are the links on the login page") ?>"><?php echo _("Frontpage links") ?></h6>
							<div class="row">
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="FBLink" data-bs-toggle="tooltip" data-bs-title="<?php echo _("You company Facebook page") ?>"><?php echo _("Facebook") ?><code>*</code></label>
										<input type="text" class="form-control" id="FBLink" name="FBLink" autocomplete="off" value="<?php echo $FBLink ?>" required>
									</div>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="CPLink" data-bs-toggle="tooltip" data-bs-title="<?php echo _("You company page") ?>"><?php echo _("Company Website") ?><code>*</code></label>
										<input type="text" class="form-control" id="CPLink" name="CPLink" autocomplete="off" value="<?php echo $CPLink ?>" required>
									</div>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="LinkedInLink" data-bs-toggle="tooltip" data-bs-title="<?php echo _("You company LinkedIn page") ?>"><?php echo _("LinkedIn") ?><code>*</code></label>
										<input type="text" class="form-control" id="LinkedInLink" name="LinkedInLink" autocomplete="off" value="<?php echo $LinkedInLink ?>" required>
									</div>
								</div>
							</div>
						</div>
						<div class="tab wizzard">
							<h6><?php echo _("Registration options") ?></h6>
							<div class="row">
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="UserViaEmail" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Allow user registrations via email?") ?>"><?php echo _("Allow user registrations via email?") ?><code>*</code></label>
										<select id="UserViaEmail" name="UserViaEmail" class="form-control" required>
											<?php
											if ($UserViaEmail == "1") {
												echo "<option value='0'>" . _("No") . "</option>";
												echo "<option value='1' selected='true'>" . _("Yes") . "</option>";
											} else {
												echo "<option value='0' selected='true'>" . _("No") . "</option>";
												echo "<option value='1'>" . _("Yes") . "</option>";
											}
											?>
										</select>
									</div>
								</div>
								<div class="col-md-6 col-sm-6 col-xs-12">
									<div class="input-group input-group-static mb-4">
										<label for="defaultCompany" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Default company assignment for new user registrations?") ?>"><?php echo _("Default Company?") ?><code>*</code></label>
										<select id="defaultCompany" name="defaultCompany" class="form-control" required>
											<?php
											$sql = "SELECT ID, CompanyName
													FROM Companies
													WHERE Active = '1'";
											$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
											while ($row = mysqli_fetch_array($result)) {
												$ID = $row["ID"];
												$CompanyName = $row["CompanyName"];
												if ($defaultCompany == $ID) {
													echo "<option value='$ID' selected='true'>$CompanyName</option>";
												} else {
													echo "<option value='$ID'>$CompanyName</option>";
												}
											}
											?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="tab wizzard">
							<h6><?php echo _("You are done") ?></h6>
							<div class="row">
								<p><?php echo _("Submit to save your settings") ?></p>
							</div>
						</div>
						<div style="overflow:auto;">
							<div style="float:right;">
								<button type="button" class="btn-btn-xs btn-primary" id="prevBtn" onclick="nextPrev(-1)"><?php echo _("Previous") ?></button>
								<button type="button" class="btn-btn-xs btn-primary" id="nextBtn" onclick="nextPrev(1)"><?php echo _("Next") ?></button>
							</div>
						</div>
						<!-- Circles which indicates the steps of the form: -->
						<div style="text-align:center;margin-top:40px;">
							<span class="step"></span>
							<span class="step"></span>
							<span class="step"></span>
							<span class="step"></span>
							<span class="step"></span>
							<span class="step"></span>
							<span class="step"></span>
						</div>

					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var currentTab = 0; // Current tab is set to be the first tab (0)
	showTab(currentTab); // Display the current tab

	function showTab(n) {
		// This function will display the specified tab of the form ...
		var x = document.getElementsByClassName("tab wizzard");
		x[n].style.display = "block";
		// ... and fix the Previous/Next buttons:
		if (n == 0) {
			document.getElementById("prevBtn").style.display = "none";
		} else {
			document.getElementById("prevBtn").style.display = "inline";
		}
		if (n == x.length - 1) {
			document.getElementById("nextBtn").innerHTML = "<?php echo _("Submit") ?>";
		} else {
			document.getElementById("nextBtn").innerHTML = "<?php echo _("Next") ?>";
		}
		// ... and run a function that displays the correct step indicator:
		fixStepIndicator(n);
	}

	function nextPrev(n) {
		// This function will figure out which tab to display
		var x = document.getElementsByClassName("tab wizzard");
		// Exit the function if any field in the current tab is invalid:
		if (n == 1 && !validateForm()) return false;
		// Hide the current tab:
		x[currentTab].style.display = "none";
		// Increase or decrease the current tab by 1:
		currentTab = currentTab + n;
		// if you have reached the end of the form... :
		if (currentTab >= x.length) {
			var WizzardForm = $("#regForm").serializeArray();

			// Call your custom function here, passing the serialized form data
			submitForm(WizzardForm);
			document.getElementById("regForm").submit();
			return false;
		}
		// Otherwise, display the correct tab:
		showTab(currentTab);
	}

	function validateForm() {
		// This function deals with validation of the form fields
		var x, y, i, valid = true;
		x = document.getElementsByClassName("tab wizzard");
		y = x[currentTab].getElementsByTagName("input");
		// A loop that checks every input field in the current tab:
		for (i = 0; i < y.length; i++) {
			// If a field is empty...
			if (y[i].value == "") {
				// add an "invalid" class to the field:
				y[i].className += " invalid";
				// and set the current valid status to false:
				valid = false;
			}
		}
		// If the valid status is true, mark the step as finished and valid:
		if (valid) {
			document.getElementsByClassName("step")[currentTab].className += " finish";
		}
		return valid; // return the valid status
	}

	function fixStepIndicator(n) {
		// This function removes the "active" class of all steps...
		var i, x = document.getElementsByClassName("step");
		for (i = 0; i < x.length; i++) {
			x[i].className = x[i].className.replace(" active", "");
		}
		//... and adds the "active" class to the current step:
		x[n].className += " active";
	}

	function submitForm(WizzardForm) {
		$.ajax({
			type: "POST",
			url: "./getdata.php?submitWizzard",
			data: {
				WizzardForm: WizzardForm
			},
			success: function(data) {
				var obj = JSON.parse(data);
				for (var i = 0; i < obj.length; i++) {
					var Result = obj[i].Result;

					if (Result === "success") {
						message = "<?php echo _("Settings updated") ?>";
						pnotify(message, "success");
					} else {
						message = "<?php echo _("Settings did not get updated") ?>";
						pnotify(message, "danger");
					}
				}
			},
			error: function(xhr, status, error) {
				// Handle any errors that occurred during the AJAX request
				console.error("Error: " + error);
			}
		});
	}
</script>
<?php include("./footer.php") ?>