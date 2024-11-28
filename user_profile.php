<?php include("./header.php") ?>
<?php
$UsersID = $_GET["userid"];
$RelatedManagerID = getRelatedManager($UsersID);

$sql = "SELECT DISTINCT users.ID AS UsersID, users.Firstname, users.Lastname, users.Username, usertypes.TypeName AS UserType, users.Email, users.Created_Date AS CreatedDate, companies.Companyname AS Companyname, 
          users.RelatedUserTypeID, users.JobTitel, users.LinkedIn, users.Birthday, users.Phone, users.LastLogon, users.StartDate, users.Active AS Active, ZoomPersRoom,
          users.RelatedManager AS RelatedManager,
          (SELECT teams.Teamname FROM users LEFT JOIN usersteams ON Users.ID = usersteams.UserID LEFT JOIN teams ON usersteams.TeamID = teams.ID WHERE usersteams.UserID = $UsersID) AS Team
          FROM Users 
          LEFT JOIN companies ON users.CompanyID = companies.ID
          LEFT JOIN usertypes ON users.RelatedUserTypeID = usertypes.ID
          LEFT JOIN usersteams ON Users.ID = usersteams.UserID
          LEFT JOIN teams ON usersteams.TeamID = teams.ID
          WHERE Users.ID = $UsersID";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

while ($row = mysqli_fetch_array($result)) {
  $UserFullName = $row['Firstname'] . " " . $row['Lastname'];
  $JobTitel = $row['JobTitel'];
  $Username = $row['Username'];
  $Email = $row['Email'];
  $Companyname = $row['Companyname'];
  $Firstname = $row['Firstname'];
  $Lastname = $row['Lastname'];
  $UserType = $row['UserType'];
  $Firstname = $row['Firstname'];
  $LinkedIn = $row['LinkedIn'];
  $StartDate = $row['StartDate'];
  $RelatedManager = $row['RelatedManager'];
  if ($RelatedManager == "") {
  } else {
    $RelatedManager = $functions->getUserFullNameWithUsername($row['RelatedManager']);
  }

  $Phone = $row['Phone'];
  $LastLogon = $row['LastLogon'];
  $Birthday = $row['Birthday'];
  $Team = $row['Team'];
  $ZoomPersRoom = $row['ZoomPersRoom'];
}

if ($UsersID == $_SESSION["id"]) {
  $Disabled = "";
} else {
  $Disabled = "disabled";
}
?>
<script>
  function editProfilePicture() {
    $("#editProfileImageModal").modal('show')
  }

  function updateAndLogUserEmailAddress(email) {

    usersid = "<?php echo $UsersID ?>";
    vData = {
      email: email,
      usersid: usersid
    };

    $.ajax({
      type: "POST",
      url: "./getdata.php?updateAndLogUserEmail",
      data: vData,
      success: function(data) {
        pnotify('Email updated', 'success');
      },
    });
  }

  function updateAndLogUserFirstname(firstname) {

    usersid = "<?php echo $UsersID ?>";
    vData = {
      firstname: firstname,
      usersid: usersid
    };

    $.ajax({
      type: "POST",
      url: "./getdata.php?updateAndLogUserFirstname",
      data: vData,
      success: function(data) {
        pnotify('Firstname updated', 'success');
      },
    });
  }

  function updateAndLogUserLastname(lastname) {
    usersid = "<?php echo $UsersID ?>";
    vData = {
      lastname: lastname,
      usersid: usersid
    };

    $.ajax({
      type: "POST",
      url: "./getdata.php?updateAndLogUserLastname",
      data: vData,
      success: function(data) {
        pnotify('Lastname updated', 'success');
      },
    });
  }

  function updateAndLogUserPhone(phone) {
    usersid = "<?php echo $UsersID ?>";
    vData = {
      phone: phone,
      usersid: usersid
    };

    $.ajax({
      type: "POST",
      url: "./getdata.php?updateAndLogUserPhone",
      data: vData,
      success: function(data) {
        pnotify('Phone updated', 'success');
      },
    });
  }

  function updateAndLogUserBirthday(birthday) {
    usersid = "<?php echo $UsersID ?>";
    vData = {
      birthday: birthday,
      usersid: usersid
    };

    $.ajax({
      type: "POST",
      url: "./getdata.php?updateAndLogUserBirthday",
      data: vData,
      success: function(data) {
        pnotify('Birthday updated', 'success');
      },
    });
  }

  function updateAndLogUserJobTitel(jobtitel) {
    usersid = "<?php echo $UsersID ?>";
    vData = {
      jobtitel: jobtitel,
      usersid: usersid
    };

    $.ajax({
      type: "POST",
      url: "./getdata.php?updateAndLogUserJobTitel",
      data: vData,
      success: function(data) {
        pnotify('Jobtitel updated', 'success');
      },
    });
  }

  function updateAndLogUserLinkedIn(linkedin) {
    usersid = "<?php echo $UsersID ?>";

    vData = {
      linkedin: linkedin,
      usersid: usersid
    };

    $.ajax({
      type: "POST",
      url: "./getdata.php?updateAndLogUserLinkedIn",
      data: vData,
      success: function(data) {
        pnotify('LinkedIn updated', 'success');
      },
    });
  }

  function updateAndLogUserZoomPersRoom(zoompersroom) {

    usersid = "<?php echo $UsersID ?>";

    vData = {
      zoompersroom: zoompersroom,
      usersid: usersid
    };

    $.ajax({
      type: "POST",
      url: "./getdata.php?updateAndLogUserZoomPersRoom",
      data: vData,
      success: function(data) {
        pnotify('Zoon Room updated', 'success');
      },
    });
  }
</script>

<div class="row">
  <div class="col-md-4 col-sm-4 col-xs-12">
    <div class="card-group">
      <div class="card">
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
            $sql = "UPDATE users SET ProfilePicture = '" . $file_name . "' WHERE ID = '" . $UsersID . "';";
            mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            //Move file to the profile pictures folder
            move_uploaded_file($file_tmp, "./uploads/images/profilepictures/" . $file_name);
            echo "<div class='alert alert-success'><span><b> Success </b></span></div>";
            $url = "user_profile.php?userid=$UsersID";
            echo '<META HTTP-EQUIV="refresh" content="1;URL=' . $url . '">';
          } else {
            print_r($errors);
          }
        }

        if ($UsersID == $_SESSION["id"]) {
        ?>

        <?php } ?>
        <div class="card-body">
          <div class="d-flex justify-content-center">
            <?php
            $profilepicture = @$functions->getProfilePicture($UsersID);
            echo "<p><img class='rounded-circle img-fluid' style='width: 150px;' src='" . $profilepicture . "' alt='profile_image'></p>";
            ?>
          </div>
          <div class="d-flex justify-content-center">
            <form action="" method="POST" enctype="multipart/form-data">
              <label class="btn btn-sm btn-success"><input type="file" name="image"><?php echo _("Choose file") ?></label>
              <button type='submit' class='btn btn-sm btn-warning'><i class="fa fa-upload m-right-xs"></i> <?php echo _("Upload") ?></button>
            </form>
          </div>
          <div class="d-flex justify-content-center">
            <h4><?php echo $UserFullName ?></h4>
          </div>
          <div class="d-flex justify-content-center">
            <h6><?php echo $JobTitel ?></h6>
          </div>
          <div class="d-flex justify-content-center">
            <?php echo $Companyname; ?>
          </div>
          <br>
          <div class="d-flex justify-content-center">
            <a href="<?php echo $LinkedIn; ?>" target="_new"><img src="./assets/img/LI-Logo.png" style="width:60px;height:15px;border:0;"></a>
          </div>
          <br>
          <div class="d-flex justify-content-center">
            <a href="<?php echo $ZoomPersRoom; ?>" target="_new"><img src="./assets/img/zoom_logo.png" style="width:60px;height:15px;border:0;"></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-8 col-sm-8 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header card-header-icon card-header">
          <h6 class="card-title"> <?php echo _("Info"); ?></h6>
        </div>
        <div class="card-body">
          <form action="user_profile.php?userid=<?php echo $UsersID ?>" method="post">
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="Company"><?php echo _('Company'); ?></label>
                  <input type="text" name="Company" id="Company" class="form-control" value="<?php echo $Companyname; ?>" onfocusout="defocused(this)" disabled>
                </div>
              </div>

              <div class=" col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="Username"><?php echo _('Username'); ?></label>
                  <input type="text" name="Username" id="Username" class="form-control" value="<?php echo $Username; ?>" onfocusout="defocused(this)" disabled>
                </div>
              </div>

              <div class=" col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="email"><?php echo _('Email'); ?></label>
                  <input type="email" name="email" id="email" class="form-control" value="<?php echo $Email; ?>" onfocusout="defocused(this)" onchange="updateAndLogUserEmailAddress(this.value)" <?php echo $Disabled; ?>>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="firstname"><?php echo _('First Name'); ?></label>
                  <input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo $Firstname; ?>" onchange="updateAndLogUserFirstname(this.value)" <?php echo $Disabled; ?>>
                </div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="lastname"><?php echo _('Last Name'); ?></label>
                  <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo $Lastname; ?>" onchange="updateAndLogUserLastname(this.value)" <?php echo $Disabled; ?>>
                </div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="Usertype"><?php echo _('Usertype'); ?></label>
                  <input type="text" class="form-control" value="<?php echo $UserType; ?>" disabled>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label><?php echo _("Nearest Manager"); ?></label>
                  <input type="text" class="form-control" value="<?php echo $RelatedManager; ?>" disabled>
                </div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label><?php echo _("Job Titel"); ?></label>
                  <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo $JobTitel; ?>" onchange="updateAndLogUserJobTitel(this.value)" <?php echo $Disabled; ?>>
                </div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label><?php echo _("Team"); ?></label>
                  <input type="text" class="form-control" value="<?php echo $Team; ?>" disabled>
                </div>
              </div>

            </div>
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Example: 15-02-1985") ?>">
                  <label><?php echo _("Birthday"); ?></label>
                  <input type="text" name="birthday" id="birthday" class="form-control" value="<?php echo convertToDanishDateFormat($Birthday); ?>" onchange="updateAndLogUserBirthday(this.value)" <?php echo $Disabled; ?>>
                </div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label><?php echo _("Start Date"); ?></label>
                  <input type="text" class="form-control" value="<?php echo convertToDanishDateFormat($StartDate); ?>" disabled>
                </div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label><?php echo _("Last Logon"); ?></label>
                  <input type="text" class="form-control" value="<?php echo convertToDanishDateTimeFormat($LastLogon); ?>" disabled>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label><?php echo _("Phone"); ?></label>
                  <input type="text" name="phone" id="phone" class="form-control" value="<?php echo $Phone; ?>" onchange="updateAndLogUserPhone(this.value)" <?php echo $Disabled; ?>>
                </div>
              </div>

              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label><?php echo _("LinkedIn"); ?></label>
                  <input type="text" name="linkedin" id="linkedin" class="form-control" value="<?php echo $LinkedIn; ?>" onchange="updateAndLogUserLinkedIn(this.value)" <?php echo $Disabled; ?>>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label data-bs-toggle="tooltip" data-bs-title="<?php echo _("Personal Meeting Room"); ?>"><?php echo _("Zoom"); ?></label>
                  <input type="text" name="ZoomPersRoom" id="ZoomPersRoom" class="form-control" value="<?php echo $ZoomPersRoom; ?>" onchange="updateAndLogUserZoomPersRoom(this.value)" <?php echo $Disabled; ?>>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="card-group">
      <div class="card">
        <div class="card-header">
          <h5><?php echo _("Memberships"); ?></h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
              <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseGroups" aria-expanded="true" aria-controls="collapseGroups"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="tooltip" data-bs-title="more" data-bs-toggle="collapse" data-bs-target="#collapseFields" aria-expanded="true" aria-controls="collapseFields"></i> <?php echo _("Group memberships") ?></a>
              <div class="collapse" id="collapseGroups">
                <div class="card-body">
                  <form action="user_profile.php?userid=<?php echo $UsersID ?>" method="post">
                    <?php
                    $UserGroups = [];
                    $groupDetails = [];
                    $UserGroups = $_SESSION['memberofgroups'];
                    if ($UserGroups) {
                      // Step 1: Collect translated names and IDs
                      foreach ($UserGroups as $GroupID) {
                        $originalName = getUserGroupName($GroupID); // Assuming getUserGroupName() returns the original group name
                        $translatedName = @$functions->translate($originalName); // Assuming @$functions->translate() translates the group name
                        // Store both the Group ID and the translated name in the array
                        $groupDetails[] = ['id' => $GroupID, 'translatedName' => $translatedName];
                      }

                      // Step 2: Sort the array by the translated names in ascending order
                      usort($groupDetails, function ($a, $b) {
                        return strcmp($a['translatedName'], $b['translatedName']);
                      });

                      // Step 3: Output the sorted, translated names along with their Group IDs
                      foreach ($groupDetails as $details) {
                        echo htmlspecialchars($details['translatedName']) . " (" . htmlspecialchars($details['id']) . ")<br>";
                      }
                    }
                    ?>
                  </form>
                </div>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
              <a href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapseRoles" aria-expanded="true" aria-controls="collapseRoles"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="tooltip" data-bs-title="more" data-bs-toggle="collapse" data-bs-target="#collapseFields" aria-expanded="true" aria-controls="collapseFields"></i> <?php echo _("Role memberships") ?></a>
              <div class="collapse" id="collapseRoles">
                <div class="card-body">
                  <form action="user_profile.php?userid=<?php echo $UsersID ?>" method="post">
                    <?php
                    $UserRoles = [];
                    $roleDetails = [];
                    $UserRoles = $_SESSION['memberofroles'];
                    if($UserRoles){
                      // Step 1: Collect translated names and IDs
                      foreach ($UserRoles as $RoleID) {
                        $originalName = @$functions->getRoleName($RoleID); // Assuming getUserGroupName() returns the original group name
                        $translatedName = @$functions->translate($originalName); // Assuming @$functions->translate() translates the group name
                        // Store both the Group ID and the translated name in the array
                        $roleDetails[] = ['id' => $RoleID, 'translatedName' => $translatedName];
                      }

                      // Step 2: Sort the array by the translated names in ascending order
                      usort($roleDetails, function ($a, $b) {
                        return strcmp($a['translatedName'], $b['translatedName']);
                      });

                      // Step 3: Output the sorted, translated names along with their Group IDs
                      foreach ($roleDetails as $details) {
                        echo htmlspecialchars($details['translatedName']) . " (" . htmlspecialchars($details['id']) . ")<br>";
                      }
                    }                    
                    ?>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
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

<?php include("./footer.php") ?>