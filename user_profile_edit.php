<?php include("./header.php") ?>
<!-- page content -->
<div class="right_col" role="main">
  <div class="page-title">
    <div class="title_left"></div>
  </div>
  <div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12">
      <div class="x_panel">
        <div class="x_title">
          <h3><?php echo _("Edit Userprofile") ?></h3>
          <div class="clearfix"></div>
          <div class="x_content">

            <?php

            //Set important variables to work with
            $UserID = $_SESSION["id"];
            $UserFullName = $_SESSION["userfullname"];

            //Get tickets pre values to be able to compare between pre and post
            $sql = "SELECT users.ID, Firstname, Lastname, Email, JobTitel, Phone, ProfilePicture, RelatedDesignID, LinkedIn, Birthday
          FROM users LEFT JOIN
          designs ON users.RelatedDesignID = designs.ID
          WHERE users.ID = '" . $UserID . "';";
            if ($result = $conn->query($sql)) {
              while ($row = $result->fetch_assoc()) {
                $Firstname = $row["Firstname"];
                $Lastname = $row["Lastname"];
                $Email = $row['Email'];
                $JobTitel = $row['JobTitel'];
                $Phone = $row['Phone'];
                $LinkedIn = $row['LinkedIn'];
                $Birthday = $row['Birthday'];
                $RelatedDesignID = $row['RelatedDesignID'];
                $ProfilePicture = $row['ProfilePicture'];
              }
            }

            if (isset($_POST['updateuserprofile'])) {
              $Firstname = $_POST["Firstname"];
              $Lastname = $_POST["Lastname"];
              $Email = $_POST["Email"];
              $JobTitel = $_POST["JobTitel"];
              $Phone = $_POST["Phone"];
              $LinkedIn = $_POST["LinkedIn"];
              $Birthday = convertFromDanishDateFormat($_POST["Birthday"]);

              $sql = "UPDATE users SET Firstname='$Firstname', Lastname='$Lastname', Email='$Email', JobTitel='$JobTitel', Phone='$Phone', LinkedIn='$LinkedIn', Birthday='$Birthday' WHERE users.id = $UserID;";
              mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

              $redirectpage = "user_profile_edit.php?userid=$UserID.php";
              $redirectpagego = "<meta http-equiv='refresh' content='1';url='$redirectpage'><p><b><div class='alert alert-success'><strong>" . _("Updated") . "</strong></div></b></p>";
              echo $redirectpagego;
            }

            ?>
            <form action=<?php echo "user_profile_edit.php?userid=$UserID.php" ?> method="POST">
              <div id="userfields" name="userfields">
                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Firstname") ?></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="Firstname" name="Firstname" value="<?php echo $Firstname; ?>">
                </div>

                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Lastname") ?></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="Lastname" name="Lastname" value="<?php echo $Lastname; ?>">
                </div>

                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Email") ?></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="Email" name="Email" value="<?php echo $Email; ?>">
                </div>

                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Job Titel") ?></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="JobTitel" name="JobTitel" value="<?php echo $JobTitel; ?>">
                </div>

                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Phone") ?></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="Phone" name="Phone" value="<?php echo $Phone; ?>">
                </div>

                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Linked In") ?></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="LinkedIn" name="LinkedIn" value="<?php echo $LinkedIn; ?>">
                </div>

                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Birthday") ?></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="Birthday" name="Birthday" value="<?php echo convertToDanishDateFormat($Birthday); ?>">
                </div>

                <div class="clearfix"></div>
                <br>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <button type="submit" name="updateuserprofile" class="btn btn-sm btn-dark float-end"><i class="fa fa-edit m-right-xs"></i> <?php echo _("Update") ?></button>
                </div>
                <div class="clearfix"></div>
            </form>
            <br>
            <br>
            <script>
              $(function() {

                jQuery('#Birthday').datetimepicker({
                  format: 'd-m-Y H:i',
                  prevButton: false,
                  nextButton: false,
                  step: 60,
                  dayOfWeekStart: 1
                });
                $.datetimepicker.setLocale('<?php echo $languageshort ?>');
              });
            </script>
            <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Profile picture") ?></label>
            <div class="col-md-9 col-sm-9 col-xs-12">
              <?php
              $profilepicture = $functions->getProfilePicture($UserID);
              echo "<p><img class='profileimage' src='" . $profilepicture . "' alt='' float-end></p>";
              ?>
            </div>
            <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
              <!--<button type='submit_profilepicture' class='btn btn-sm btn-success'><i class="fa fa-upload m-right-xs"></i> Upload new picture</button>-->
              <div class="clearfix"></div>

              <?php
              if (isset($_FILES['image'])) {
                $errors = array();
                $file_name = $_FILES['image']['name'];
                $file_size = $_FILES['image']['size'];
                $file_tmp = $_FILES['image']['tmp_name'];
                $file_type = $_FILES['image']['type'];
                $tmp = explode('.', $file_name);
                $file_ext = end($tmp);

                $extensions = array("jpeg", "jpg", "png");

                if (in_array($file_ext, $extensions) === false) {
                  $errors[] = "extension not allowed, please choose a jpg, jpg or png file.";
                }

                if ($file_size > 500000) {
                  $errors[] = 'File size must be less then 500 Kb';
                }

                if (empty($errors) == true) {
                  //Randomize filename
                  $tmp = explode('.', $file_name);
                  $file_ext = end($tmp);
                  //Call randomizer
                  $file_name = generateRandomString(20);
                  //Put filename together with extension
                  $file_name = $file_name . "." . $file_ext;

                  //Update ProfilPicture reference in database
                  $sql = "UPDATE users SET ProfilePicture = '" . $file_name . "' WHERE ID = '" . $UserID . "';";
                  $conn->query($sql);
                  //Move file to the profile pictures folder
                  move_uploaded_file($file_tmp, "./uploads/images/profilepictures/" . $file_name);
                  echo "Success";
                } else {
                  print_r($errors);
                }
              }

              ?>
              <html>

              <body>
                <form action="" method="POST" enctype="multipart/form-data">
                  <label class="btn btn-sm btn-success"><input type="file" name="image"><?php echo _("Choose file") ?></label>
                  <!--<input type = "file" name = "image" >-->
                  <button type='submit' class='btn btn-sm btn-warning'><i class="fa fa-upload m-right-xs"></i> <?php echo _("Upload") ?></button>
                  <!--<input type="submit">-->
                </form>
              </body>

              </html>
              <br><br>
              <br>
              <div class="clearfix"></div>
              <?php include("./user_changepassword.php") ?>
            </div>
            <div>
            </div>
          </div>
        </div>
        <!-- /page content -->
        <?php include("./footer.php") ?>