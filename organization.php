<?php include("./header.php") ?>

<script>
	$(document).ready(function() {
		<?php initiateStandardSearchTable("TableTeam"); ?>
		<?php initiateStandardSearchTable("TableTeams"); ?>
		<?php initiateStandardSearchTable("TableEmployees"); ?>
		<?php initiateStandardSearchTable("TableCompanies"); ?>
		<?php initiateStandardSearchTable("TableContacts"); ?>

		$(window).resize(function() {
			$("#TableTeam").DataTable().columns.adjust().draw();
			$("#TableTeams").DataTable().columns.adjust().draw();
			$("#TableEmployees").DataTable().columns.adjust().draw();
			$("#TableCompanies").DataTable().columns.adjust().draw();
			$("#TableContacts").DataTable().columns.adjust().draw();
		});
	});
</script>

<?php include("./modals/modal_message_user.php") ?>
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
		<div class="card-group">
			<div class="card">
				<div class="card-header"><a href="javascript:void(0);" onclick="location.reload(true);"><i class="fas fa-sitemap"></i> <?php echo _("Organization"); ?></a>
				</div>
				<div class="scrolling-menu-wrapper">
					<div class="arrow arrow-left">&#9664;</div>
					<div class="scrolling-menu">
						<ul class="nav nav-tabs" id="myTab" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" data-bs-toggle="tab" href="#Team" role="tablist" onclick="redrawTable('TableTeam');">
									<?php echo _('My Team'); ?>
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-bs-toggle="tab" href="#Teams" role="tablist" onclick="redrawTable('TableTeams');">
									<?php echo _('Teams'); ?>
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-bs-toggle="tab" href="#Employees" role="tablist" onclick="redrawTable('TableEmployees');">
									<?php echo _('Employees'); ?>
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-bs-toggle="tab" href="#Companies" role="tablist" onclick="redrawTable('TableCompanies');">
									<?php echo _('Customer Companies'); ?>
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" data-bs-toggle="tab" href="#Contacts" role="tablist" onclick="redrawTable('TableContacts');">
									<?php echo _('Customer Contacts'); ?>
								</a>
							</li>
						</ul>
					</div>
					<div class="arrow arrow-right">&#9654;</div>
				</div>
				<div class="card-body">
					<div class="tab-content tab-space">
						<div class="tab-pane active" id="Team">

							<?php
							// Prepare and execute the first SQL query using a prepared statement
							$sql1 = "SELECT TeamID FROM usersteams WHERE UserID = ?";
							$stmt1 = $conn->prepare($sql1);
							$stmt1->bind_param("i", $UserID);
							$stmt1->execute();
							$result1 = $stmt1->get_result();
							$TeamID = null;

							while ($row = $result1->fetch_assoc()) {
								$TeamID = $row['TeamID'];
							}

							if (empty($TeamID)) {
								echo _("You are not part of a team, you must be member of a team in order to see this view");
							} else {
							?>
								<table id="TableTeam" class="table table-responsive table-borderless table-hover" cellspacing="0">
									<thead>
										<tr>
											<th><?php echo _("Name"); ?></th>
											<th><?php echo _("Boards"); ?></th>
											<th><?php echo _("Username"); ?></th>
											<th><?php echo _("Online"); ?></th>
										</tr>
									</thead>
									<?php
									// Prepare and execute the second SQL query using a prepared statement
									$sql2 = "SELECT Users.ID, CONCAT(Users.Firstname,' ',Users.Lastname) AS FullName, Users.Username, Users.Email, sessions.userid AS SessionUserID
											FROM Users
											LEFT JOIN (SELECT userid FROM sessions WHERE sessions.sessionrenewed > (NOW() - INTERVAL 15 MINUTE)) AS sessions ON users.ID = sessions.userid
											INNER JOIN usersteams ON usersteams.UserID = users.ID
											INNER JOIN teams ON usersteams.TeamID = teams.ID
											WHERE usersteams.TeamID = ? AND Users.Active = 1
											ORDER BY Users.Firstname ASC, Users.Lastname ASC";
									$stmt2 = $conn->prepare($sql2);
									$stmt2->bind_param("i", $TeamID);
									$stmt2->execute();
									$result2 = $stmt2->get_result();
									?>
									<tbody>
										<?php while ($row = $result2->fetch_assoc()) {
											$Online = !empty($row['SessionUserID']) ? "Yes" : "No";
											$UserID = $row['ID'];
											$FullName = $row['FullName'];
											$Email = $row['Email'];
										?>
											<tr>
												<td><?php echo $FullName; ?></td>
												<td>
													<a href="javascript:runModalViewUnit('User', <?php echo $UserID; ?>);" title="<?php echo _("View") ?>"><span class='badge bg-gradient-success'><i class='fa-solid fa-up-right-from-square'></span></i></a>
													<a href="javascript:poptastic('timeregistrations_all.php?userid=<?php echo $row['ID']; ?>');" title="<?php echo _("Timeregistration") ?>"><span class='badge bg-gradient-secondary'><i class='fa fa-clock'></span></i></a>
													<a href="javascript:void(0);"><span class='badge bg-gradient-secondary' onclick="NewMessage('<?php echo $row['ID']; ?>')" title="<?php echo _("Message") ?>"><i class='fa fa-envelope'></i></span></a>
												</td>
												<td><?php echo $row['Username']; ?></td>
												<td><?php echo _($Online); ?></td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							<?php } ?>
						</div>

						<div class="tab-pane" id="Teams">
							<table id="TableTeams" class="table table-responsive table-borderless table-hover" cellspacing="0">
								<thead>
									<tr>
										<th><?php echo _("Team"); ?></th>
										<th></th>
										<th><?php echo _("Team Leader"); ?></th>
									</tr>
								</thead>
								<?php
								// Prepare and execute the SQL query using a prepared statement
								$sql = "SELECT teams.ID, teams.Teamname, teams.TeamLeader
										FROM teams 
										WHERE teams.Active = 1
										ORDER BY teams.Teamname ASC";
								$stmt = $conn->prepare($sql);
								$stmt->execute();
								$result = $stmt->get_result();
								?>
								<tbody>
									<?php while ($row = $result->fetch_assoc()) { ?>
										<?php $ID = $row['ID']; ?>
										<?php $Teamname = $row['Teamname']; ?>
										<?php
										$TeamLeader = $row['TeamLeader'];
										if (!empty($TeamLeader)) {
											$TeamLeader = $functions->getUserFullNameWithUsername($TeamLeader);
										} else {
											$TeamLeader = "";
										}
										?>
										<tr>
											<td><?php echo $Teamname; ?></td>
											<td>
												<a href="javascript:runModalViewUnit('Team', <?php echo $row['ID']; ?>);" title="<?php echo _("View"); ?>">
													<span class='badge bg-gradient-success'><i class='fa-solid fa-up-right-from-square'></span></i>
												</a>
												<a href="javascript:viewTeam('', <?php echo $ID; ?>, '<?php echo $Teamname; ?>');" title="<?php echo _("Show members"); ?>">
													<span class="badge bg-gradient-secondary"><i class='fa-regular fa-eye'></span></i>
												</a>
											</td>
											<td><?php echo $TeamLeader; ?></td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>

						<div class="tab-pane" id="Employees">
							<table id="TableEmployees" class="table table-responsive table-borderless table-hover" cellspacing="0">
								<thead>
									<tr>
										<th><?php echo _("Name"); ?></th>
										<th></th>
										<th><?php echo _("Username"); ?></th>
										<th><?php echo _("Email"); ?></th>
										<th><?php echo _("Phone"); ?></th>
										<th><?php echo _("Job Titel"); ?></th>
										<th><?php echo _("Team Leader"); ?></th>
										<th><?php echo _("Type"); ?></th>
										<th><?php echo _("Last login"); ?></th>
									</tr>
								</thead>
								<?php
								// Prepare and execute the SQL query using a prepared statement
								$sql = "SELECT DISTINCT Users.ID, Users.Firstname, Users.Lastname, CONCAT(Users.Firstname,' ',Users.Lastname) AS FullName, Users.Username, Users.Email, Users.Phone, Users.LinkedIn, Users.JobTitel, users.ProfilePicture, usertypes.TypeName, Users.LastLogon, users.RelatedManager
										FROM Users 
										LEFT JOIN usertypes ON Users.RelatedUserTypeID = usertypes.ID
										WHERE Users.RelatedUserTypeID != 2 AND Users.Active = 1
										ORDER BY Users.Firstname ASC, Users.Lastname ASC";
								$stmt = $conn->prepare($sql);
								$stmt->execute();
								$result = $stmt->get_result();
								?>
								<tbody>
									<?php while ($row = $result->fetch_assoc()) { ?>
										<tr>
											<?php
											$UserID = $row['ID'];
											$FullName = $row['FullName'];
											$Email = $row['Email'];
											$ProfilePicture = $row['ProfilePicture'];
											$LastLogin = $row["LastLogon"];
											?>
											<?php
											$Manager = $row['RelatedManager'];
											if (!empty($Manager)) {
												$Manager = $functions->getUserFullNameWithUsername($Manager);
											} else {
												$Manager = "";
											}
											?>
											<td><?php echo $FullName; ?></td>
											<td>
												<a href="javascript:runModalViewUnit('User', <?php echo $UserID; ?>);" title="<?php echo _("View"); ?>">
													<span class='badge bg-gradient-success'><i class='fa-solid fa-up-right-from-square'></span></i>
												</a>
												<a href="javascript:void(0);"><span class='badge bg-gradient-secondary' onclick="NewMessage('<?php echo $row['ID']; ?>')" title="<?php echo _("Message"); ?>"><i class='fa fa-envelope'></i></span></a>
											</td>
											<td><?php echo $row['Username']; ?></td>
											<td><?php echo "<a href='mailto:" . $row["Email"] . "'>" . $row["Email"] . "</a>"; ?></td>
											<td><?php echo $row["Phone"]; ?></td>
											<td><?php echo $row["JobTitel"]; ?></td>
											<td><?php echo $Manager; ?></td>
											<td><?php echo @$functions->translate($row["TypeName"]); ?></td>
											<td>
												<?php
												if (!empty($LastLogin)) {
													echo convertToDanishTimeFormat($LastLogin);
												} else {
													echo "";
												}
												?>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>

						<div class="tab-pane" id="Companies">
							<table id="TableCompanies" class="table table-responsive table-borderless table-hover" cellspacing="0">
								<thead>
									<tr>
										<th><?php echo _("Name"); ?></th>
										<th></th>
										<th><?php echo _("Webpage"); ?></th>
										<th><?php echo _("Phone"); ?></th>
										<th><?php echo _("SLA"); ?></th>
										<th><?php echo _("Email"); ?></th>
										<th><?php echo _("CBR"); ?></th>
										<th><?php echo _("Country"); ?></th>
										<th><?php echo _("Address"); ?></th>
										<th><?php echo _("Zip Code"); ?></th>
										<th><?php echo _("City"); ?></th>
									</tr>
								</thead>
								<?php
								$stmt = $conn->prepare("SELECT companies.ID, companies.Companyname, companies.Active, companies.WebPage, companies.Phone, slaagreements.Name AS SLAName, companies.CustomerAccountNumber, companies.Address, companies.ZipCode, companies.City, companies.Email, 
														companies.CBR, companies.Country
														FROM companies
														INNER JOIN slaagreements ON companies.RelatedSLAID = slaagreements.ID
														WHERE companies.Active = 1
														ORDER BY Companyname ASC");
								$stmt->execute();
								$result = $stmt->get_result();
								?>
								<tbody>
									<?php while ($row = $result->fetch_assoc()) { ?>
										<tr>
											<td><?php echo $row['Companyname']; ?></td>
											<td><a href="javascript:runModalViewUnit('Company', <?php echo $row['ID'] ?>);" title="<?php echo _("View") ?>"><span class='badge bg-gradient-success'><i class='fa-solid fa-up-right-from-square'></span></i></a></td>
											<td><?php echo "<a target=new href='" . $row["WebPage"] . "'>" . $row["WebPage"] . "</a>"; ?></td>
											<td><?php echo $row["Phone"]; ?></td>
											<td><?php echo $row["SLAName"]; ?></td>
											<td><?php echo "<a href='mailto:" . $row["Email"] . "'>" . $row["Email"] . "</a>"; ?></td>
											<td><?php echo $row["CBR"]; ?></td>
											<td><?php echo $row["Country"]; ?></td>
											<td><?php echo $row["Address"]; ?></td>
											<td><?php echo $row["ZipCode"]; ?></td>
											<td><?php echo $row["City"]; ?></td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
						<div class="tab-pane" id="Contacts">
							<table id="TableContacts" class="table table-responsive table-borderless table-hover" cellspacing="0">
								<thead>
									<tr>
										<th><?php echo _("Name"); ?></th>
										<th></th>
										<th><?php echo _("Email"); ?></th>
										<th><?php echo _("Phone"); ?></th>
										<th><?php echo _("LinkedIn"); ?></th>
										<th><?php echo _("Job Titel"); ?></th>
										<th><?php echo _("Company"); ?></th>
									</tr>
								</thead>
								<?php
								$stmt = $conn->prepare("SELECT DISTINCT Users.ID, Users.Firstname, Users.Lastname, Users.Username, Users.Email, Users.Phone, Users.LinkedIn, Users.JobTitel, users.ProfilePicture, usertypes.TypeName, companies.Companyname
														FROM Users
														LEFT JOIN usertypes ON Users.RelatedUserTypeID = usertypes.ID
														LEFT JOIN companies ON Users.CompanyID = companies.ID
														WHERE Users.RelatedUserTypeID = 2 AND Users.Active = 1
														ORDER BY Users.Firstname ASC, Users.Lastname ASC");
								$stmt->execute();
								$result = $stmt->get_result();
								?>
								<tbody>
									<?php while ($row = $result->fetch_assoc()) { ?>
										<tr>
											<?php $FullName = $row['Firstname'] . " " . $row['Lastname']; ?>
											<?php $UserID = $row['ID'] ?>
											<?php $Email = $row['Email'] ?>
											<?php $LinkedIn = $row['LinkedIn'] ?>
											<td><?php echo $FullName ?></a></td>
											<td><a href="javascript:runModalViewUnit('User', <?php echo $UserID ?>);" title="<?php echo _("View") ?>"><span class='badge bg-gradient-success'><i class='fa-solid fa-up-right-from-square'></span></i></a></td>
											<td><?php echo "<a href='mailto:" . $row["Email"] . "'>" . $row["Email"] . "</a>"; ?></td>
											<td><?php echo $row["Phone"]; ?></td>
											<td><?php echo "<a target=new href='$LinkedIn'>$LinkedIn</a>"; ?></td>
											<td><?php echo $row["JobTitel"]; ?></td>
											<td><?php echo $row["Companyname"]; ?></td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	function redrawTable(TableName) {
		$("#" + TableName).DataTable().columns.adjust().draw();
	}
</script>
<!-- /page content -->

<?php include("./footer.php") ?>