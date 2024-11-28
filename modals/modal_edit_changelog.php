<div class="modal fade" id="modalEditChangelog" data-bs-focus="false" aria-labelledby="modalEditChangelog" aria-hidden="true">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<div id="ChangeID" hidden></div>
				<button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
					<i class="material-icons">clear</i>
				</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class=" col-lg-4 col-md-4 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalChangelogVersion"><?php echo _('Version'); ?></label>
									<input type="text" name="modalChangelogVersion" id="modalChangelogVersion" class="form-control" value="">
								</div>
							</div>
							<div class=" col-lg-4 col-md-4 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalChangelogDate"><?php echo _('Date'); ?></label>
									<input type="text" name="modalChangelogDate" id="modalChangelogDate" class="form-control" value="">
								</div>
							</div>
							<div class=" col-lg-4 col-md-4 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalChangelogType"><?php echo _('Type'); ?></label>
									<select class="form-control" id="modalChangelogType" name="modalChangelogType">
										<?php
										$sql = "SELECT changelog_types.ID, changelog_types.Name
												FROM changelog_types
												ORDER BY changelog_types.Name ASC;";
										$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
										while ($row = mysqli_fetch_array($result)) {
											$Name = $row['Name'];
											echo "<option value='" . $row['ID'] . "'>$Name</option>";
										}

										mysqli_free_result($result); // Free the result set
										?>
									</select>
								</div>
							</div>
							<div class=" col-lg-12 col-md-12 col-sm-12 col-xs-12">
								<div class="input-group input-group-static mb-4">
									<label for="modalChangelogDescription"><?php echo _('Description'); ?></label>
									<textarea type="text" name="modalChangelogDescription" id="modalChangelogDescription" class="form-control" rows="10" value=""></textarea>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button class="btn btn-sm btn-success" onclick="updateChangelog();"><?php echo _('Update'); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>