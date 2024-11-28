<div class="modal fade" id="modalNewsCategory" data-bs-focus="false" aria-labelledby="modalNewsCategoryLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header"><?php echo _("Category"); ?>
				<div id="NewsCategoryHeader"></div>
				<div id="NewsCatID" hidden></div>
				<button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
					<i class="material-icons">clear</i>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<div id="CatContent">
							<form id="formCatContent">
								<div class="row">
									<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
										<label for="ModalNewsCategoryGroups" class="control-label"><?php echo _("Group"); ?></label>
										<div class="input-group input-group-static mb-4">
											<select id="ModalNewsCategoryGroups" name="ModalNewsCategoryGroups" class="form-control" required>
												<option value=''></option>
												<?php
												$sql = "SELECT ID AS ID, GroupName AS GroupName, Description AS Description, RelatedModuleID AS RelatedModuleID, Active AS Active, 'Standard' AS Type
												FROM usergroups
												UNION
												SELECT ID AS ID, GroupName AS GroupName, Description AS Description, RelatedModuleID AS RelatedModuleID, Active AS Active, 'System' AS Type
												FROM system_groups AS UserGroups
												ORDER BY GroupName ASC";
												$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
												while ($row = mysqli_fetch_array($result)) {
													if ($_SESSION['id'] == $row['ID']) {
														echo "<option value='" . $row['ID'] . "' selected='select'>" . _($row['GroupName']) . "</option>";
													} else {
														echo "<option value='" . $row['ID'] . "'>" . $row['GroupName'] . "</option>";
													}
												}
												?>
											</select>
										</div>
									</div>

									<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
										<label for="ModalNewsCategoryRole" class="control-label"><?php echo _("Role"); ?></label>
										<div class="input-group input-group-static mb-4">
											<select id="ModalNewsCategoryRole" name="ModalNewsCategoryRole" class="form-control" required>
												<option value=''></option>
												<?php
												$sql = "SELECT `ID`, `RoleName`, `Description`, `Active`
												FROM `roles`
												WHERE ID != 0";
												$result = $conn->query($sql);
												while ($row = mysqli_fetch_array($result)) {
													if ($_SESSION['id'] == $row['ID']) {
														echo "<option value='" . $row['ID'] . "' selected='select'>" . _($row['RoleName']) . "</option>";
													} else {
														echo "<option value='" . $row['ID'] . "'>" . $row['RoleName'] . "</option>";
													}
												}
												?>
											</select>
										</div>
									</div>

									<div class=" col-lg-4 col-md-4 col-sm-12 col-xs-12">
										<label for="ModalNewsCategoryActive" class="control-label"><?php echo _("Active"); ?></label>
										<div class="input-group input-group-static mb-4">
											<select id="ModalNewsCategoryActive" name="ModalNewsCategoryActive" class="form-control" required>
												<option value="1"><?php echo _("Active") ?></option>
												<option value="0"><?php echo _("Inactive") ?></option>
											</select>
										</div>
									</div>

									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										<label for="ModalNewsCategoryName" class="control-label"><?php echo _("Name"); ?></label>
										<div class="input-group input-group-static mb-4">
											<input type="text" class="form-control" id="ModalNewsCategoryName" name="ModalNewsCategoryName" value="">
										</div>
									</div>
								</div>
							</form>
						</div>
					</div>
					<div class=" modal-footer">
						<button class="btn btn-sm btn-success float-end" id="btnUpdateNewsCategory" name="btnUpdateNewsCategory" onclick="updateNewsCategory()"><?php echo _("Update") ?></button>
						<button class="btn btn-sm btn-success float-end" id="btnCreateNewsCategory" name="btnCreateNewsCategory" onclick="createNewsCategory()"><?php echo _("Create") ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>