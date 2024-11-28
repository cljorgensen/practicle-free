<div class="modal fade" id="modalNewsArticle" data-bs-focus="false" tabindex="-1" aria-labelledby="modalNewsArticleLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<?php echo _("News Article"); ?>
				<div id="NewsHeader"></div>
				<div id="NewsID" hidden></div>
				<button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
					<i class="material-icons">clear</i>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<div id="NewsContent">
							<form id="formNewsContent">
								<div class="row">
									<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
										<label for="ModalNewsCategory" class="control-label"><?php echo _("Category"); ?></label>
										<div class="input-group input-group-static mb-4">
											<select id="ModalNewsCategory" name="ModalNewsCategory" class="form-control" required>
												<?php
												$sql = "SELECT News_categories.ID, News_categories.Name
														FROM News_categories
														WHERE News_categories.Active=1";
												$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
												while ($row = mysqli_fetch_array($result)) {
													echo "<option value='" . $row['ID'] . "'>" . $row['Name'] . "</option>";
												}
												?>
											</select>
										</div>
									</div>

									<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
										<label for="ModalNewsWriter" class="control-label"><?php echo _("Writer"); ?></label>
										<div class="input-group input-group-static mb-4">
											<select id="ModalNewsWriter" name="ModalNewsWriter" class="form-control" required>
												<?php
												$sql = "SELECT users.ID, CONCAT(users.Firstname,' ',users.Lastname) AS Name
												FROM users
												WHERE users.RelatedUserTypeID = 1
												AND Active = 1";
												$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
												while ($row = mysqli_fetch_array($result)) {
													if ($_SESSION['id'] == $row['ID']) {
														echo "<option value='" . $row['ID'] . "' selected='select'>" . _($row['Name']) . "</option>";
													} else {
														echo "<option value='" . $row['ID'] . "'>" . $row['Name'] . "</option>";
													}
												}
												?>
											</select>
										</div>
									</div>

									<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
										<label for="ModalDateCreated" class="control-label"><?php echo _("Date to be published"); ?></label>
										<div class="input-group input-group-static mb-4">
											<input type="text" class="form-control" id="ModalDateCreated" name="ModalDateCreated" value="<?php echo date('d-m-Y H:i'); ?>">
										</div>
									</div>

									<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
										<label for="ModalActive" class="control-label"><?php echo _("Active"); ?></label>
										<div class="input-group input-group-static mb-4">
											<select id="ModalActive" name="ModalActive" class="form-control" required>
												<option value="1"><?php echo _("Active") ?></option>
												<option value="0"><?php echo _("Inactive") ?></option>
											</select>
										</div>
									</div>

									<label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _("Headline"); ?></label>
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
										<div class="input-group input-group-static mb-4">
											<input type="text" class="form-control" id="ModalNewsHeadline" name="ModalNewsHeadline" value="">
										</div>
									</div>

									<div class="col-lg-<:fieldwidth:> col-sm-12 col-xs-12">
										<div class="input-group input-group-static mb-4"><label for="ModalNewsContent"><?php echo _("Content"); ?>
												&ensp;<a href="javascript:toggleCKEditor('ModalNewsContent','250');"><i class="fa-solid fa-pen fa-sm" title="Double click on field to edit"></i></a>
											</label>
										</div>
										<div style="height: 250px; word-wrap: break-word; overflow-y: auto; overflow-x: auto;" class="resizable_textarea form-control" id="ModalNewsContent" name="ModalNewsContent" title="Double click to edit" rows="10" autocomplete="off" ondblclick="toggleCKEditor('ModalNewsContent','250');">
										</div><br>
									</div>
								</div>
							</form>
						</div>
					</div>
					<div class="modal-footer">
						<?php
						if (in_array("100001", $group_array) || in_array("100032", $group_array)) {
						?>
							<button class="btn btn-sm btn-success float-end" id="btnUpdateNewsArticle" name="btnUpdateNewsArticle" onclick="updateNewsArticle()"><?php echo _("Update") ?></button>
							<button class="btn btn-sm btn-success float-end" id="btnCreateNewsArticle" name="btnCreateNewsArticle" onclick="createNewsArticle()"><?php echo _("Create") ?></button>
						<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>