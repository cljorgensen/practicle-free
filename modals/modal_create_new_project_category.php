<script>
  async function createNewProjectTaskCategory() {
    const CategoryName = document.getElementById('CategoryName').value;
    const CategoryDescription = document.getElementById('CategoryDescription').value;

    await $.ajax({
      type: 'GET',
      url: './getdata.php?createNewProjectTaskCategory',
      data: {
        CategoryName: CategoryName,
        CategoryDescription: CategoryDescription
      },
      success: function(data) {
        $("#ModalcreateNewProjectTaskCategory").modal("hide");
        pnotify('<?php echo _("Project Category created"); ?>', 'success');
        location.reload();
      }
    });
  }

  async function editProjectCategory(CategoryID) {

    if (CategoryID == -1) {
      document.getElementById("updateprojectcategory").style.display = "none";
      document.getElementById("deleteprojectcategory").style.display = "none";
      document.getElementById("createprojectcategory").style.display = "block";
    } else {
      document.getElementById("updateprojectcategory").style.display = "block";
      document.getElementById("deleteprojectcategory").style.display = "block";
      document.getElementById("createprojectcategory").style.display = "none";

      await $.ajax({
        type: 'GET',
        url: './getdata.php?editProjectTaskCategory',
        data: {
          CategoryID: CategoryID
        },
        success: function(data) {
          var obj = JSON.parse(data);
          for (var i = 0; i < obj.length; i++) {
            document.getElementById('CategoryName').value = obj[i].ShortName;
            CategoryDescription = obj[i].Description;
            $("#CategoryDescription").trumbowyg('html', CategoryDescription);
          }
        }
      });
      $('#TableProjectTasks').DataTable().ajax.reload();
    }
  }

  async function deleteProjectCategory() {

    CategoryID = document.getElementById('Categories').value

    await $.ajax({
      type: 'GET',
      url: './getdata.php?deleteProjectTaskCategory',
      data: {
        CategoryID: CategoryID
      },
      success: function(data) {
        pnotify('<?php echo _("Category deleted") ?>', 'success');
      }
    });
    $("#Categorys option[value='" + CategoryID + "']").remove();
    $("#ModalcreateNewProjectTaskCategory").modal("hide");
    $('#TableProjectTasks').DataTable().ajax.reload();
  }

  async function updateProjectCategory() {

    CategoryID = document.getElementById('Categories').value
    CategoryName = document.getElementById('CategoryName').value
    CategoryDescription = document.getElementById('CategoryDescription').value

    await $.ajax({
      type: 'GET',
      url: './getdata.php?updateProjectTaskCategory',
      data: {
        CategoryID: CategoryID,
        CategoryName: CategoryName,
        CategoryDescription: CategoryDescription
      },
      success: function(data) {
        pnotify('<?php echo _("Category updated") ?>', 'success');
      }
    });
    $("#modalcreateNewProjectTaskCategory").modal("hide");
    location.reload();
  }
</script>

<!-- Modal New Project -->
<div class="modal fade" id="modalcreateNewProjectTaskCategory" data-bs-focus="false" role="dialog" aria-labelledby="ModalcreateNewProjectTaskCategoryLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="ModalcreateNewProjectTaskCategoryLabel"><?php echo _('Project Task Category Administration'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-body">
            <div class="container-fluid">
              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="Categories"><?php echo _('Existing Categorys'); ?></label>
                    <select id="Categories" name="Categories" class="form-control" onchange="editProjectCategory(this.value)">
                      <option value='-1' label=''></option>
                      <?php
                      $sql = "SELECT ID, ShortName
                          FROM projects_tasks_categories
                          ORDER BY projects_tasks_categories.ShortName ASC";
                      $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                      while ($row = mysqli_fetch_array($result)) {
                        $ProjectCategoryID = $row['ID'];
                        $ProjectCategoryShortName = $row['ShortName'];

                        echo "<option value='$ProjectCategoryID'>$ProjectCategoryShortName</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="CategoryName"><?php echo _('Name'); ?></label>
                    <input type="text" id="CategoryName" name="CategoryName" class="form-control" value="">
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="input-group input-group-static mb-4">
                    <label for="CategoryDescription"><?php echo _('Description'); ?></label>
                    <textarea id="CategoryDescription" name="CategoryDescription" class="form-control" value=""></textarea>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                  <button name="createprojectcategory" id="createprojectcategory" class="btn btn-sm btn-success float-end" onclick="createNewProjectTaskCategory();"><span class=""></span> <?php echo _("Create"); ?></button>
                  <button name="updateprojectcategory" id="updateprojectcategory" class="btn btn-sm btn-success float-end" onclick="updateProjectCategory()"><span class=""></span> <?php echo _("Update"); ?></button>
                  <button name="deleteprojectcategory" id="deleteprojectcategory" class="btn btn-sm btn-danger float-end" onclick="deleteProjectCategory()"><span class=""></span> <?php echo _("Delete"); ?></button>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  $(function() {
    jQuery('#StartDate').datetimepicker({
      format: 'd-m-Y H:i',
      prevButton: false,
      nextButton: false,
      step: 60,
      dayOfWeekStart: 1
    });
    $.datetimepicker.setLocale('<?php echo $languageshort ?>');

    jQuery('#Deadline').datetimepicker({
      format: 'd-m-Y H:i',
      prevButton: false,
      nextButton: false,
      step: 60,
      dayOfWeekStart: 1
    });
    $.datetimepicker.setLocale('<?php echo $languageshort ?>');
  });
</script>
<!-- End Modal New Project -->