<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array)) {
} else {
	$CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}

$SystemURL = @$functions->getSettingValue(17);
$token = $functions->getFirstActiveAPIKey();
?>
<script>
	$(document).ready(function() {
		// Load the API keys
		getActiveApiKeys('<?php echo $UserLanguageCode; ?>');
		getActiveCompanies();
	});

	function getUsers() {
		var url = "<?php echo $SystemURL; ?>/api/index.php/<?php echo $token; ?>/users/";
		var resultDiv = document.getElementById("apiTestResult");

		$.ajax({
			url: url,
			type: "GET",
			success: function(data) {},
			complete: function(data) {
				// Convert the data object to a JSON string
				var jsonData = JSON.stringify(data.responseJSON, null, 2);

				// Inject the JSON output into the div
				resultDiv.innerHTML = jsonData;
			},
			error: function(xhr, status, error) {
				// Handle the error case
				console.error("Error: " + error);
			}
		});
	}

	function updateUser() {
		var userId = document.getElementById("UserID").value;
		var url = "<?php echo $SystemURL; ?>/api/index.php/<?php echo $token; ?>/users/" + userId;
		var resultDiv = document.getElementById("apiTestResult");

		// Retrieve the new Lastname from the input field
		var lastname = document.getElementById("Lastname").value;

		// Create the user data object with the updated Lastname
		var userData = {
			Lastname: lastname
		};

		var jsonData = JSON.stringify(userData);

		$.ajax({
			url: url,
			type: "PATCH",
			data: jsonData,
			contentType: "application/json",
			headers: {
				"Authorization": "Bearer <?php echo $token; ?>"
			},
			success: function(response) {
				resultDiv.innerHTML = "<pre>" + JSON.stringify(response, null, 2) + "</pre>";
			},
			error: function(xhr, status, error) {
				console.error("Error: " + error);
			}
		});
	}

	function createUser() {
		var Firstname = document.getElementById("Firstname").value;
		var Lastname = document.getElementById("CreateLastname").value;
		var Email = document.getElementById("Email").value;
		var Username = document.getElementById("Username").value;
		var currentDate = new Date().toISOString().slice(0, 16).replace("T", " ").toString();
		console.log(currentDate);

		var url = "<?php echo $SystemURL; ?>/api/index.php/<?php echo $token; ?>/users";
		var resultDiv = document.getElementById("apiTestResult");

		// Create the user data object
		var userData = {
			Firstname: Firstname,
			Lastname: Lastname,
			Email: Email,
			Username: Username,
			Created_Date: currentDate,
			CompanyID: 1,
			RelatedUserTypeID: 2,
			ADUUID: null,
			JobTitel: null,
			LinkedIn: null,
			Phone: null,
			Active: 1,
			InactiveDate: null,
			LastLogon: null,
			ProfilePicture: "default_user.png",
			RelatedDesignID: 3,
			Birthday: null,
			StartDate: null,
			RelatedManager: null,
			QRUrl: null,
			Notes: "This is a test user Created from API",
			Pin: null,
			ZoomPersRoom: null
		};

		var jsonData = JSON.stringify(userData);

		$.ajax({
			url: url,
			type: "POST",
			data: jsonData,
			contentType: "application/json",
			headers: {
				"Authorization": "Bearer <?php echo $token; ?>"
			},
			success: function(response) {
				resultDiv.innerHTML = "<pre>" + JSON.stringify(response, null, 2) + "</pre>";
			},
			error: function(xhr, status, error) {
				console.error("Error: " + error);
			}
		});
	}

	function deleteUser() {
		var userId = document.getElementById("DeleteUserID").value;
		var url = "<?php echo $SystemURL; ?>/api/index.php/<?php echo $token; ?>/users/" + userId;
		var resultDiv = document.getElementById("apiTestResult");

		$.ajax({
			url: url,
			type: "DELETE",
			contentType: "application/json",
			headers: {
				"Authorization": "Bearer <?php echo $token; ?>"
			},
			success: function(response) {
				resultDiv.innerHTML = "<pre>" + JSON.stringify(response, null, 2) + "</pre>";
			},
			error: function(xhr, status, error) {
				console.error("Error: " + error);
			}
		});
	}
</script>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card text-wrap">
				<div class="card-header card-header"> <i class="fa fa-cog fa-lg"></i> <a href="javascript:location.reload(true);"><?php echo _("API configuration"); ?></a>
				</div>
				<div class="card-body">
					<div class="scrolling-menu-wrapper">
						<div class="arrow arrow-left">&#9664;</div>
						<div class="scrolling-menu">
							<ul class="nav nav-tabs" id="myTab" role="tablist">
								<li class="nav-item">
									<a class="nav-link active" data-bs-toggle="tab" href="#Keyleasings">
										<?php echo _("Active Keys"); ?>
									</a>
								</li>

								<li class="nav-item">
									<a class="nav-link" data-bs-toggle="tab" href="#Documentation">
										<?php echo _("Documentation"); ?>
									</a>
								</li>

								<li class="nav-item">
									<a class="nav-link" data-bs-toggle="tab" href="#Tests">
										<?php echo _("Tests"); ?>
									</a>
								</li>
							</ul>
						</div>
						<div class="arrow arrow-right">&#9654;</div>
					</div>
					<br>
					<div class="tab-content">
						<!-- Tab panes -->
						<div class="tab-pane active" id="Keyleasings">
							<?php
							echo "<h6>" . _("API keys") . "</h6>";
							?>
							<a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseCreateKey" aria-expanded="false" aria-controls="collapseCreateKey"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Create") ?>"></i> <?php echo _("Create") ?></a>
							<div id="collapseCreateKey" class="collapse width" data-bs-parent="#accordionCreateKey">
								<div class="card-body">
									<div class="row">
										<div class="col-lg-6 col-sm-6 col-xs-12">
											<div class="row">
												<div class="col-lg-12 col-sm-12 col-xs-12">
													<div class="input-group input-group-static mb-4">
														<label for="apiKeyDescription" title="Please give this api key a Description so you know what it is used for"><?php echo _("Description"); ?>
														</label></p><input type="text" class="form-control" id="apiKeyDescription" name="apiKeyDescription" title="" autocomplete="off" required>
													</div>
												</div>
												<div class="col-lg-6 col-sm-6 col-xs-12">
													<div class="col-lg-<:fieldwidth:> col-sm-12 col-xs-12">
														<div class="input-group input-group-static mb-4">
															<label for="APIKeyCompanies" title="Choose what company this API key is associated for"><?php echo _("Company"); ?>
															</label>
															<select class="form-control" id="APIKeyCompanies" name="APIKeyCompanies" title="">
															</select>
														</div>
													</div>
												</div>
												<div class="col-lg-6 col-sm-6 col-xs-12">
													<div class="input-group input-group-static mb-4" title="Double Click on field to use date time picker">
														<label for="apiExpireDate" title="<?php echo _("This is the date when this key will expire"); ?>"><?php echo _("Expires"); ?>
														</label></p><input type="text" class="form-control" id="apiExpireDate" name="apiExpireDate" value="<?php echo convertToDanishDateTimeFormat(date('Y-m-d H:i:s', strtotime('+1 year'))); ?>" autocomplete="off" ondblclick="runDateTimePicker('apiExpireDate');">
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-6 col-sm-6 col-xs-12">
										</div>
										<div class="col-lg-6 col-sm-6 col-xs-12">
											<button class="btn btn-sm btn-success float-end" onclick="createAPIKey();"><?php echo _("Create"); ?></button>
										</div>
										<div class="col-lg-6 col-sm-6 col-xs-12">
										</div>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12 col-sm-12 col-xs-12">
									<table id="apiKeys" class="table table-responsive table-borderless" cellspacing="0">
									</table>
								</div>
							</div>
						</div>
						<div class="tab-pane" id="Documentation">
							<?php
							echo "<h6>" . _("API path") . "</h6>";
							$URL = @$functions->getSettingValue(17);
							echo '<code>' . $URL . '/api/index.php/</code><small>' . '<:' . 'Token' . ':>' . '</small><code>/</code><small>' . '<:' . 'ElementType' . ':>' . '</small><code>/</code><small>' . '<:' . 'SpecificID' . ':>' . '</small><br>';
							echo "<h6>" . _("API available element types") . "</h6>";
							echo '<code>
									<ul>
										<li><a href="' . $URL . '/api/index.php/' . $token . '/itsm/3/" target="_new">Changes</a></li>
										<li><a href="' . $URL . '/api/index.php/' . $token . '/cmdb/1/" target="_new">CMDB</a></li>
										<li><a href="' . $URL . '/api/index.php/' . $token . '/companies/" target="_new">Companies</a></li>
										<li><a href="' . $URL . '/api/index.php/' . $token . '/itsm/7/" target="_new">Documents</a></li>
										<li><a href="' . $URL . '/api/index.php/' . $token . '/groups/" target="_new">Groups</a></li>
										<li><a href="' . $URL . '/api/index.php/' . $token . '/itsm/1/" target="_new">Incidents</a></li>
										<li><a href="' . $URL . '/api/index.php/' . $token . '/itsm/8/" target="_new">Passwords</a></li>
										<li><a href="' . $URL . '/api/index.php/' . $token . '/itsm/4/" target="_new">Problems</a></li>
										<li>ProjectTasks</li>
										<li>Projects</li>
										<li><a href="' . $URL . '/api/index.php/' . $token . '/itsm/2/" target="_new">Requests</a></li>
										<li>Roles</li>
										<li>Tasks</li>
										<li><a href="' . $URL . '/api/index.php/' . $token . '/users/" target="_new">Users</a></li>	
									</ul>
								</code>';
							echo "<h6>" . _("CMDB") . "</h6>";
							echo '<code>' . $URL . '/api/index.php/<b>$Token</b>/<b>cmdb</b>/<b>$CMDBType</b>/<b>$SpecificID</b></code><br>';
							echo '<code><a href="' . $URL . '/api/index.php/' . $token . '/cmdb/1/" target="_new">example: Business Services</a></code><br>';
							echo '<code>$CMDBType: is the id of specific CMDB module id<br>';
							echo '$SpecificID: is the id of specific CMDB module item to retrieve<br></code><br>';
							echo "<h6>" . _("ITSM") . "</h6>";
							echo '<code>' . $URL . '/api/index.php/<b>$Token</b>/<b>itsm</b>/<b>$ITSMType</b>/<b>$SpecificID</b></code><br>';
							echo '<code><a href="' . $URL . '/api/index.php/' . $token . '/itsm/2/" target="_new">example: requests</a></code><br>';
							echo '<code>$ITSMType: is the id of specific ITSM module id<br>';
							echo '$SpecificID: is the id of specific ITSM module item to retrieve<br></code>';
							?>
						</div>

						<div class="tab-pane" id="Tests">
							<div class="row">
								<div class="col-md-4 col-sm-4 col-xs-12">
									<div class="row">
										<div class="col-md-12 col-sm-12 col-xs-12">
											<button name="createTestCMDB" class="btn btn-sm btn-info float-left" onclick="getUsers();"><?php echo _("Get users") ?></button>
										</div>
									</div>
									<div class="row">
										<h6><?php echo _("Test update user") ?></h6>
									</div>
									<div class="row">
										<div class="col-md-4 col-sm-4 col-xs-12">

											<div class="input-group input-group-static mb-4">
												<label for="UserID" data-bs-toggle="tooltip" data-bs-title="select user to update"><?php echo _("Select user") ?></label>
												<select class="form-control" id="UserID" name="UserID" data-bs-toggle="tooltip" data-bs-title="select user to update">
													<?php
													$sql = "SELECT ID, CONCAT(firstname,' ',lastname,'(',username,')') AS FullName
												FROM users;";

													$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

													while ($row = mysqli_fetch_array($result)) {
														$ID = $row["ID"];
														$FullName = $row["FullName"];
														echo "<option value=\"$ID\">($ID) $FullName</option>";
													}

													mysqli_free_result($result);
													?>
												</select>
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-12">
											<div class="input-group input-group-static mb-4">
												<label for="Lastname" data-bs-toggle="tooltip" data-bs-title="set lastname"><?php echo _("New lastname") ?></label>
												<input type="text" id="Lastname" name="Lastname" class="form-control" placeholder="New lastname">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-12">
											<button name="updateUser" class="btn btn-sm btn-info float-left" onclick="updateUser();"><?php echo _("Update") ?></button>
										</div>
									</div>
									<div class="row">
										<h6>Test Create user</h6>
									</div>
									<div class="row">
										<div class="col-md-4 col-sm-4 col-xs-12">
											<div class="input-group input-group-static mb-4">
												<label for="Firstname" data-bs-toggle="tooltip" data-bs-title="set lastname"><?php echo _("Firstname") ?></label>
												<input type="text" id="Firstname" name="Firstname" class="form-control" value="John" placeholder="Firstname">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-12">
											<div class="input-group input-group-static mb-4">
												<label for="CreateLastname" data-bs-toggle="tooltip" data-bs-title="set lastname"><?php echo _("Lastname") ?></label>
												<input type="text" id="CreateLastname" name="CreateLastname" class="form-control" value="Doe" placeholder="Lastname">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-12">
											<div class="input-group input-group-static mb-4">
												<label for="Email" data-bs-toggle="tooltip" data-bs-title="set lastname"><?php echo _("Email") ?></label>
												<input type="text" id="Email" name="Email" class="form-control" value="johndoe@example.com" placeholder="Email">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-12">
											<div class="input-group input-group-static mb-4">
												<label for="Username" data-bs-toggle="tooltip" data-bs-title="set lastname"><?php echo _("Username") ?></label>
												<input type="text" id="Username" name="Username" class="form-control" value="johndoe" placeholder="Username">
											</div>
										</div>
										<div class="col-md-4 col-sm-4 col-xs-12">
											<button name="updateUser" class="btn btn-sm btn-info float-left" onclick="createUser();"><?php echo _("Create") ?></button>
										</div>
									</div>
									<div class="row">
										<h6><?php echo _("Delete user (careful it will delete the user)") ?></h6>
									</div>
									<div class="row">
										<div class="col-md-4 col-sm-4 col-xs-12">

											<div class="input-group input-group-static mb-4">
												<label for="DeleteUserID" data-bs-toggle="tooltip" data-bs-title="select user to update"><?php echo _("Select user") ?></label>
												<select class="form-control" id="DeleteUserID" name="DeleteUserID" data-bs-toggle="tooltip" data-bs-title="select user to delete">
													<?php
													$sql = "SELECT ID, CONCAT(firstname,' ',lastname,'(',username,')') AS FullName
												FROM users;";

													$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

													while ($row = mysqli_fetch_array($result)) {
														$ID = $row["ID"];
														$FullName = $row["FullName"];
														echo "<option value=\"$ID\">($ID) $FullName</option>";
													}

													mysqli_free_result($result);
													?>
												</select>
											</div>
										</div>

										<div class="col-md-4 col-sm-4 col-xs-12">
											<button name="deleteUser" class="btn btn-sm btn-info float-left" onclick="deleteUser();"><?php echo _("Delete") ?></button>
										</div>
									</div>
								</div>
								<div class="col-md-8 col-sm-8 col-xs-12">
									<div id="apiTestResult">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include("./footer.php"); ?>