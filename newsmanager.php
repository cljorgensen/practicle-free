<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100020", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>
<script>
    $(document).ready(function() {
        getNewsCategories();
        getUserNewsUserGroups();
        getUserNewsCategoryRoles();
        getNewsCVEFilters();
        getNewsArticles('<?php echo $UserLanguageCode?>');
    });
</script>
<div class="row">
    <div class="col-md-8 col-sm-8 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header"> <i class="fa fa-newspaper fa-lg"></i> <a href="javascript:location.reload(true)"><?php echo _("News"); ?></a>
                    <div class="float-end">
						<ul class="navbar-nav justify-content-end">
							<li class="nav-item dropdown pe-2">
								<a href="javascript:void(0);" title="<?php echo _("Create News Article") ?>" onclick="viewCreateNews(<?php echo $_SESSION['id']; ?>);" class="nav-link text-body p-0 position-relative" aria-expanded="false">
									&nbsp;&nbsp;<i class="far far-dark fa-plus-square"></i>&nbsp;&nbsp;
								</a>
							</li>
						</ul>
					</div>
                </div>
                <div class="card-body">
                    <table id="TableNewsArticles" class="table table-responsive table-borderless table-hover" cellspacing="0">
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-sm-4 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header">
                    <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseNewsCategories" aria-expanded="false" aria-controls="collapseNewsCategories"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Expand category administration") ?>"></i> <?php echo _("Category administration") ?></a>
                </div>
                <div id="collapseNewsCategories" class="collapse width">
                    <div class="card-body">
                        <div id="newsAdmin">
                            <section id="newsCategories">
                                <h6><?php echo _("Categories") ?></h6>
                                <table id="TableNewsCategories"></table>
                                <br>
                                <div id="addCategory">
                                    <h6><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseAddNewsCategory" aria-expanded="false" aria-controls="collapseAddNewsCategory"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Expand category administration") ?>"></i>
                                            <?php echo _("Add new category") ?>
                                        </a>
                                    </h6>
                                    <div id="collapseAddNewsCategory" class="collapse width">
                                        <form id="addNewsCategory">
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <div class="input-group input-group-static mb-4">
                                                    <label for="newCategoryName"><?php echo _("Name") ?></label>
                                                    <input type="text" class="form-control" id="newCategoryName" name="newCategoryName" value="" title="" autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <label for="UserNewsUserGroup"><?php echo _("Usergroup") ?></label>
                                                <div class="input-group input-group-static mb-4">
                                                    <select id="UserNewsUserGroup" name="UserNewsUserGroup" class="form-control">
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <label for="UserNewsCategoryRole"><?php echo _("Role") ?></label>
                                                <div class="input-group input-group-static mb-4">
                                                    <select id="UserNewsCategoryRole" name="UserNewsCategoryRole" class="form-control">
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                        <button class="btn btn-sm btn-success float-end" onclick="createNewsCategory()"><?php echo _("Add") ?></button>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-group">
            <div class="card">
                <div class="card-header">
                    <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseNewsCVE" aria-expanded="false" aria-controls="collapseNewsCVE"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("If CVE news catagory is enabled - you can add keywords here for CVE product relevant pulls") ?>"></i> <?php echo _("CVE Filters") ?></a>
                </div>
                <div id="collapseNewsCVE" class="collapse width">
                    <div class="card-body">
                        <div id="newsAdmin">
                            <section id="newsCVEFilters">
                                <h6><?php echo _("Filters") ?></h6>
                                <table id="TableCVEFilters"></table>
                                <br>
                                <div id="addCVEFilter">
                                    <h6><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseAddNewsCVEFilter" aria-expanded="false" aria-controls="collapseAddNewsCVEFilter"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Expand category administration") ?>"></i>
                                            <?php echo _("Add filter") ?>
                                        </a>
                                    </h6>
                                    <div id="collapseAddNewsCVEFilter" class="collapse width">
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            <div class="input-group input-group-static mb-4">
                                                <label for="newCVEFilter"><?php echo _("Name") ?></label>
                                                <input type="text" class="form-control" id="newCVEFilter" name="newCVEFilter" value="" title="" autocomplete="off">
                                            </div>
                                        </div>
                                        <button class="btn btn-sm btn-success float-end" onclick="addNewsCVEFilter()"><?php echo _("Add") ?></button>
                                    </div>
                                </div>
                                <div id="Miscellaneous">
                                    <h6><a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseMiscellaneous" aria-expanded="false" aria-controls="collapseMiscellaneous"><i class="fa-solid fa-circle-chevron-down float-right" title="<?php echo _("Miscellaneous") ?>"></i>
                                            <?php echo _("Miscellaneous") ?>
                                        </a>
                                    </h6>
                                    <div id="collapseMiscellaneous" class="collapse width">
                                        <button name="getCVE" class="btn btn-sm btn-info float-left" onclick="getCVEEntries('30');" title="<?php echo _("Get latest CVE news entries") ?>"><?php echo _("Fetch") ?></button>
                                        <button name="getCVE" class="btn btn-sm btn-danger float-left" onclick="removeAllCVEEntries();" title="<?php echo _("Delete all CVE news entries") ?>"><?php echo _("Delete") ?></button>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("./footer.php"); ?>