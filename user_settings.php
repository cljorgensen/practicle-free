<?php include("./header.php") ?>

<?php

  //Set important variables to work with
  $UserID = $_SESSION["id"];
  $UserFullName = $_SESSION["userfullname"];
  $DisbaleButtonValue = isPasswordChangeSubmitted($UserID);

  //Get tickets pre values to be able to compare between pre and post
  $sql = "SELECT users.ID, Firstname, Lastname, Email, JobTitel, Phone, ProfilePicture, RelatedDesignID, users.Pin
          FROM users
          WHERE users.ID = $UserID";
  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

  while ($row = mysqli_fetch_array($result)) {
    $Firstname = $row["Firstname"];
    $Lastname = $row["Lastname"];
    $Email = $row['Email'];
    $JobTitel = $row['JobTitel'];
    $Phone = $row['Phone'];
    $Pin = $row['Pin'];
    $RelatedDesignID = $row['RelatedDesignID'];
    $ProfilePicture = $row['ProfilePicture'];
  }

?>
<script>
  function removeWidget(entryid, reloadvalue) {
    UserID = <?php echo $UserID ?>;
    if (UserID == "") {
      document.getElementById("txtHint").innerHTML = "";
      return;
    } else {
      if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
      } else {
        // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          //document.getElementById("txtHint").innerHTML = this.responseText;
        }
      };
      xmlhttp.open("GET", "getdata.php?removeWidget=" + entryid + "&userid=" + UserID, true);
      xmlhttp.send();
    }
    if (reloadvalue == 1) {
      reloadwindow('user_settings.php');
    }
  }

  function addWidget(entryid, reloadvalue) {
    UserID = <?php echo $UserID ?>;
    if (UserID == "") {
      document.getElementById("txtHint").innerHTML = "";
      return;
    } else {
      if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
      } else {
        // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          //document.getElementById("txtHint").innerHTML = this.responseText;
        }
      };
      xmlhttp.open("GET", "getdata.php?addWidget=" + entryid + "&userid=" + UserID, true);
      xmlhttp.send();
    }
    if (reloadvalue == 1) {
      reloadwindow('user_settings.php');
    }
  }

  function removeQuickLaunchEntry(entryid, reloadvalue) {
    UserID = <?php echo $UserID ?>;
    if (UserID == "") {
      document.getElementById("txtHint").innerHTML = "";
      return;
    } else {
      if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
      } else {
        // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          //document.getElementById("txtHint").innerHTML = this.responseText;
        }
      };
      xmlhttp.open("GET", "getdata.php?removeQuickLaunchEntry=" + entryid + "&userid=" + UserID, true);
      xmlhttp.send();
    }
    if (reloadvalue == 1) {
      reloadwindow('user_settings.php');
    }
  }

  function addQuickLaunchEntry(entryid, reloadvalue) {
    UserID = <?php echo $UserID ?>;
    if (UserID == "") {
      document.getElementById("txtHint").innerHTML = "";
      return;
    } else {
      if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
      } else {
        // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
          //document.getElementById("txtHint").innerHTML = this.responseText;
        }
      };
      xmlhttp.open("GET", "getdata.php?addQuickLaunchEntry=" + entryid + "&userid=" + UserID, true);
      xmlhttp.send();
    }
    if (reloadvalue == 1) {
      reloadwindow('user_settings.php');
    }
  }
</script>

<?php
// Check if form is submitted successfully 
if (isset($_POST["submit_removequickmenuentry"])) {
  // Check if any option is selected 
  if (isset($_POST["RemoveLinksAdded"])) {
    // Retrieving each selected option 
    foreach ($_POST['RemoveLinksAdded'] as $RemoveLinksAdded)
      echo "<script type='text/javascript'>removeQuickLaunchEntry('$RemoveLinksAdded');</script>";
  } else {
    echo "Select an option first!";
  }
  echo "<script>window.location.href = 'user_settings.php';</script>";
}

// Check if form is submitted successfully 
if (isset($_POST["submit_addquickmenuentry"])) {
  // Check if any option is selected 
  if (isset($_POST["AddAvaliableLinks"])) {
    // Retrieving each selected option 
    foreach ($_POST['AddAvaliableLinks'] as $AddAvaliableLinks)
      echo "<script type='text/javascript'>addQuickLaunchEntry('$AddAvaliableLinks');</script>";
  } else {
    echo "Select an option first!";
  }
  echo "<script>window.location.href = 'user_settings.php';</script>";
}

// Check if form is submitted successfully 
if (isset($_POST["submit_removeWidget"])) {
  // Check if any option is selected 
  if (isset($_POST["WidgetsChosen"])) {
    // Retrieving each selected option 
    foreach ($_POST['WidgetsChosen'] as $WidgetsChosen)
      echo "<script type='text/javascript'>removeWidget($WidgetsChosen);</script>";
  } else {
    echo "Select an option first!";
  }
  echo "<script>window.location.href = 'user_settings.php';</script>";
}

// Check if form is submitted successfully 
if (isset($_POST["submit_addWidget"])) {
  // Check if any option is selected 
  if (isset($_POST["WidgetsAvailable"])) {
    // Retrieving each selected option 
    foreach ($_POST['WidgetsAvailable'] as $WidgetsAvailable)
      echo "<script type='text/javascript'>addWidget($WidgetsAvailable);</script>";
  } else {
    echo "Select an option first!";
  }
  echo "<script>window.location.href = 'user_settings.php';</script>";
}

if (isset($_POST['changePasswordSubmit'])) {
  changePasswordSubmit($UserID);
  echo "<script>window.location.href = 'user_settings.php';</script>";
}

if (isset($_POST['removePasswordSubmit'])) {
  removePasswordSubmit($UserID);
  echo "<script>window.location.href = 'user_settings.php';</script>";
}

require_once 'classes/GoogleAuthenticator.php';

$authenticator = new PHPGangsta_GoogleAuthenticator();
if (isset($_POST['updateusersettings'])) {
  $LanguageID = $_POST["LanguageID"];
  $timeZone = $_POST["TimeZone"];
  $UserGoogleAuthChosen = $_POST["GoogleAuth"];
  $Pin = $_POST["Pin"];

  //Update language for user
  $functions->updateUserSetting($UserID, $LanguageID, 10);

  //Update timezone for user
  $functions->updateUserSetting($UserID, $timeZone, 11);

  //Check if Pin is empty - pin is required
  if (!$Pin) {
    echo "<script>alert('Pin is required');</script>";
    echo "<script>window.location.href = \"user_settings.php\";</script>";
  }
  //If Pin is not empty, update
  else {
    updatePinForUser($UserID, $Pin);
  }

  $functions->updateUserSetting($UserID, $UserGoogleAuthChosen, 31);

  if ($UserGoogleAuthChosen == '1') {
    $username = $_SESSION["username"];

    $secret = $authenticator->createSecret();

    $sql = "UPDATE users SET google_secret_code = '$secret' WHERE users.ID = $UserID;";
    mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

    $sql = "SELECT SettingValue FROM settings WHERE ID = 25";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
      $website = $row['SettingValue'];
    }

    $title = 'Practicle MS';
    $qrCodeUrl = $authenticator->getQRCodeGoogleUrl($title, $secret, $website);

    $sql2 = "UPDATE users SET QRUrl = '$qrCodeUrl' WHERE users.ID = $UserID;";
    mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));
  } else {
    $sql2 = "UPDATE users SET QRUrl = '', google_secret_code = '0Km#9kQyfI1CLkthWhDb#F' WHERE users.ID = $UserID;";
    mysqli_query($conn, $sql2) or die('Query fail: ' . mysqli_error($conn));
  }

  echo "<script>pnotify('Settings updated','success');</script>";
}
?>
<div class="row">
  <div class="col-md-4 col-sm-4 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header card-header">
          <h5 class="card-title"><?php echo _("Settings"); ?></h5>
        </div>
        <div class="card-body">
          <form action=user_settings.php method="POST">
            <div id="userfields" name="userfields">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="GoogleAuth" class="ms-0"><?php echo _("Enable Google Authenticator"); ?></label>
                  <select id="GoogleAuth" name="GoogleAuth" class="form-control">
                    <?php

                    $UserGoogleAuth = getUserGoogleAuth($UserID);
                    $Enabled = $functions->translate("Enabled");
                    $Disabled = $functions->translate("Disabled");
                    if ($UserGoogleAuth == "none") {
                      echo "<option value='0' selected='select'>$Disabled</option>";
                      echo "<option value='1'>$Enabled</option>";
                    } elseif ($UserGoogleAuth == "0") {
                      echo "<option value='0' selected='select'>$Disabled</option>";
                      echo "<option value='1'>$Enabled</option>";
                    } else {
                      echo "<option value='0'>$Disabled</option>";
                      echo "<option value='1' selected='select'>$Enabled</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>

              <?php
              $UserQRUrl = getUserQRUrl($UserID);

              if ($UserGoogleAuth == "1" && $UserQRUrl !== "none") {
                echo "<br><div class='col-md-12 col-sm-12 col-xs-12'><p>" . _('Now download Google Authenticator App on your smartphone and scan this QR code - Do not Log Out before you have scanned this QR code successfully in the Google Authenticator App!') . "<br><br>" . _('When you have scanned the QR code with success - log off and login again. You will from now on be required to use Google Authenticator to finish your logins') . "</p></div>";
                echo "<br><div class='col-md-12 col-sm-12 col-xs-12'><img id='barcode' src='$UserQRUrl' width='150' height='150'></div><br>";
              }
              ?>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="LanguageID" class="ms-0"><?php echo _("Language"); ?></label>
                  <select id="LanguageID" name="LanguageID" class="form-control">
                    <?php
                    $UserLanguageID = $functions->getUserLanguage($UserID);

                    if (!$UserLanguageID) {
                      $UserLanguageID = $functions->getDefaultUserLanguage();
                    }

                    $sql = "SELECT ID, LanguageName, LanguageCode 
                            FROM system_languages
                            ORDER BY LanguageName ASC";
                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    while ($row = mysqli_fetch_array($result)) {
                      if ($UserLanguageID == $row['ID']) {
                        echo "<option value='" . $row['ID'] . "' selected='select'>" . $row['LanguageName'] . "</option>";
                      } else {
                        echo "<option value='" . $row['ID'] . "'>" . $row['LanguageName'] . "</option>";
                      }
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="TimeZone" class="ms-0"><?php echo _("Time Zone"); ?></label>
                  <select id="TimeZone" name="TimeZone" class="form-control" required>
                    <?php
                    $getUserTimeZoneID = $functions->getUserSettingValue($UserID, 11);

                    if (!$getUserTimeZoneID) {
                      $getUserTimeZoneID = $functions->getSettingValue(11);
                    }

                    $sql = "SELECT ID, TimezoneName FROM system_timezones";
                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    while ($row = mysqli_fetch_array($result)) {
                      if ($getUserTimeZoneID == $row['ID']) {
                        echo "<option value='" . $row['ID'] . "' selected='select'>" . $row['TimezoneName'] . "</option>";
                      } else {
                        echo "<option value='" . $row['ID'] . "'>" . $row['TimezoneName'] . "</option>";
                      }
                    }
                    ?>
                  </select>
                </div>
              </div>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label data-bs-toggle="tooltip" data-bs-title="<?php echo _("Use at least 4 digits -> example: 2468")?>"><?php echo _("Pin")?></label>
                  <input type="number" class="form-control" id="Pin" name="Pin" value="<?php echo $Pin; ?>" autocomplete="off">
                </div>
              </div>

              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <button type="submit" name="updateusersettings" class="btn btn-sm btn-success float-end"><?php echo _("Update"); ?></button>
                <?php
                if($DisbaleButtonValue == 'disabled'){
                  echo "<button type='submit' name='removePasswordSubmit' class='btn btn-sm btn-info float-left' data-bs-toggle=\"tooltip\" data-bs-title=\""._("Cancel password change")."\">" . _("Cancel") . "</button>";
                } else{
                  echo "<button type='submit' name='changePasswordSubmit' class='btn btn-sm btn-info float-left'>" . _("Change Password") . "</button>";
                }
                ?>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-sm-4 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header card-header">
          <h6 class="card-title"><?php echo _("Quick Actions"); ?></h6>
        </div>
        <div class="card-body">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <label class="form-label"><?php echo _("Added"); ?></label>
            <form method='post'>
              <div class="input-group input-group-static mb-4">
                <select multiple="" class="form-control pb-4" id="LinksAdded" name="RemoveLinksAdded[]" ondblclick='removeQuickLaunchEntry(this.value,1)' size="10" multiple>
                  <?php
                  $sql = "SELECT users_quickmenu.RelatedChoiceID, users_quickmenu_choices.Name 
                            FROM users_quickmenu
                            LEFT JOIN users_quickmenu_choices ON users_quickmenu.RelatedChoiceID = users_quickmenu_choices.ID
                          WHERE users_quickmenu.RelatedUserID = $UserID
                          ORDER BY Name ASC";
                  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                  // An array to hold the results
                  $choices = [];

                  while ($row = mysqli_fetch_array($result)) {
                      $choices[] = [
                          'ID' => $row['RelatedChoiceID'],
                          'Name' => $functions->translate($row['Name'])  // assuming _() is your translation function
                      ];
                  }

                  // Sort the choices array by the 'Name' key
                  usort($choices, function($a, $b) {
                      return strcmp($a['Name'], $b['Name']);
                  });

                  // Now echo out the sorted choices
                  foreach ($choices as $choice) {
                      echo "<option value='" . $choice['ID'] . "'>" . $choice['Name'] . "</option>";
                  }
                  ?>
                </select>
              </div>
              <input type='submit' name='submit_removequickmenuentry' value='<?php echo _("Remove") ?>' class='btn btn-sm btn-danger float-end'>
          </div>
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <label class="form-label"><?php echo _("Available"); ?></label>
            <div class="input-group input-group-static mb-4">
              <select multiple="" class="form-control pb-4" id="AvaliableLinks" name="AddAvaliableLinks[]" ondblclick='addQuickLaunchEntry(this.value,1)' size="10" multiple>
                <?php
                  $sql = "SELECT users_quickmenu_choices.ID, users_quickmenu_choices.Name 
                          FROM users_quickmenu_choices
                          WHERE users_quickmenu_choices.ID NOT IN (SELECT users_quickmenu.RelatedChoiceID
                            FROM users_quickmenu
                            WHERE users_quickmenu.RelatedUserID = $UserID)
                          ORDER BY Name ASC";
                  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                  // An array to hold the results
                  $choices = [];

                  while ($row = mysqli_fetch_array($result)) {
                      $choices[] = [
                          'ID' => $row['ID'],
                          'Name' => $functions->translate($row['Name'])  // assuming _() is your translation function
                      ];
                  }

                  // Sort the choices array by the 'Name' key
                  usort($choices, function($a, $b) {
                      return strcmp($a['Name'], $b['Name']);
                  });

                  // Now echo out the sorted choices
                  foreach ($choices as $choice) {
                      echo "<option value='" . $choice['ID'] . "'>" . $choice['Name'] . "</option>";
                  }
                ?>
              </select>
            </div>
            <input type='submit' name='submit_addquickmenuentry' value='<?php echo _("Add") ?>' class='btn btn-sm btn-success float-end'>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-sm-4 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header card-header">
          <h6 class="card-title"><?php echo _("Widgets on frontpage"); ?></h6>
        </div>
        <div class="card-body">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <label class="form-label"><?php echo _("Added"); ?></label>
            <form method='post'>
              <div class="input-group input-group-static mb-4">
                <select multiple="" class="form-control pb-4" id="WidgetsChosen" name="WidgetsChosen[]" ondblclick='removeWidget(this.value,1)' size="10" multiple>
                  <?php
                  $sql = "SELECT widgets.ID, widgets.WidgetName
                          FROM widgets
                          INNER JOIN widgets_users ON widgets.ID = widgets_users.WidgetID
                          WHERE widgets_users.UserID = $UserID
                          ORDER BY WidgetName ASC";
                  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                  $choices = [];

                  while ($row = mysqli_fetch_array($result)) {
                      $choices[] = [
                          'ID' => $row['ID'],
                          'Name' => $functions->translate($row['WidgetName'])  // assuming _() is your translation function
                      ];
                  }

                  // Sort the choices array by the 'Name' key
                  usort($choices, function($a, $b) {
                      return strcmp($a['Name'], $b['Name']);
                  });

                  // Now echo out the sorted choices
                  foreach ($choices as $choice) {
                      echo "<option value='" . $choice['ID'] . "'>" . $choice['Name'] . "</option>";
                  }

                  ?>
                </select>
              </div>
              <input type='submit' name='submit_removeWidget' value='<?php echo _("Remove") ?>' class='btn btn-sm btn-danger float-end'>
            </form>
          </div>
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <label class="form-label"><?php echo _("Available"); ?></label>
            <form method='post'>
              <div class="input-group input-group-static mb-4">
                <select multiple="" class="form-control pb-4" id="WidgetsAvailable" name="WidgetsAvailable[]" ondblclick='addWidget(this.value,1)' size="10" multiple>
                  <?php
                    $sql = "SELECT widgets.ID, widgets.WidgetName
                    FROM widgets
                      WHERE widgets.ID NOT IN 
                        (SELECT widgets.ID
                        FROM widgets
                        INNER JOIN widgets_users ON widgets.ID = widgets_users.WidgetID
                        WHERE widgets_users.UserID = $UserID)
                      AND widgets.Active = 1
                    ORDER BY WidgetName ASC";
                    
                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    $choices = [];

                    while ($row = mysqli_fetch_array($result)) {
                        $choices[] = [
                            'ID' => $row['ID'],
                            'Name' => $functions->translate($row['WidgetName'])  // assuming _() is your translation function
                        ];
                    }

                    // Sort the choices array by the 'Name' key
                    usort($choices, function($a, $b) {
                        return strcmp($a['Name'], $b['Name']);
                    });

                    // Now echo out the sorted choices
                    foreach ($choices as $choice) {
                        echo "<option value='" . $choice['ID'] . "'>" . $choice['Name'] . "</option>";
                    }
                  ?>
                </select>
              </div>
              <input type='submit' name='submit_addWidget' value='<?php echo _("Add") ?>' class='btn btn-sm btn-success float-end'>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-3 col-xs-12" hidden>
    <div class="card-group">
      <div class="card">
        <div class="card-header card-header">
          <h6 class="card-title" data-bs-toggle="tooltip" data-bs-title="<?php echo _("These are text shortcuts you can define a text code and a text template that is inserted when you type the text code in text boxes");?>"><?php echo $functions->translate("Text templates"); ?></h6>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="input-group input-group-static mb-4">
                <label for="templates"><?php echo _("Templates"); ?></label>
                <select id="templates" name="templates" class="form-control" onchange='showtemplatetext(this.value)'>
                <option value=''></option>
                  <?php
                    $sql = "SELECT ID,Name
                            FROM text_templates
                            WHERE UserID = $UserID";
                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    while ($row = mysqli_fetch_array($result)) {
                      $ID = $row['ID'];
                      $Name = $row['Name'];
                      echo "<option value='$ID'>$Name</option>";
                    }
                  ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="input-group input-group-static mb-4">
                <label for="TemplateName"><?php echo _("Name"); ?></label>
                <input type="text" class="form-control" id="TemplateName" name="TemplateName" placeholder="">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="input-group input-group-static mb-4">
                <label for="TemplateCode"><?php echo _("Code"); ?></label>
                <input type="text" class="form-control" id="TemplateCode" name="TemplateCode" placeholder="/codetext">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="input-group input-group-static mb-4">
                <label for="TemplateText" class="ms-0"><?php echo _("Text"); ?></label>
                <textarea class="form-control" id="TemplateText" name="TemplateText"></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <button id="createTemplate" name="createTemplate" class="btn btn-success btn-sm float-end" style="display:block;" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Create") ?>" onclick="createTemplate();"><?php echo _("Create") ?></button>
              <button id="updateTemplate" name="updateTemplate" class="btn btn-success btn-sm float-end" style="display:none;" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Update") ?>" onclick="updateTemplate();"><?php echo _("Update") ?></button>
              <button id="deleteTemplate" name="deleteTemplate" class="btn btn-danger btn-sm float-end" style="display:none;" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Delete") ?>" onclick="deleteTemplate()"><?php echo _("Delete") ?></button>
            </div>
          </div>
          <div class="row" id="templatetexttest" style="display:none;">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="input-group input-group-static mb-4">
                <label for="TestTemplateText" class="ms-0"><?php echo _("Test your text code shortcut here"); ?></label>
                <textarea class="form-control" id="TestTemplateText" name="TestTemplateText"></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>

  function updateTemplate(){
    var TemplateID = document.getElementById("templates").value;
    var TemplateName = document.getElementById("TemplateName").value;
    var TemplateCode = document.getElementById("TemplateCode").value;
    var TemplateText = $('#TemplateText').trumbowyg('html');
    url = "getdata.php?updateTextTemplate"

    $.ajax({
      url: url,
      data: {
        TemplateID: TemplateID,
        TemplateName: TemplateName,
        TemplateCode: TemplateCode,
        TemplateText: TemplateText
      },
      type: 'GET',
      success: function(data) {
        pnotify("Template updated","success");
      }
    });
  }

  function deleteTemplate(){
    var templateid = document.getElementById("templates").value;

    url = "getdata.php?deleteTextTemplate"

    $.ajax({
      url: url,
      data: {
        templateid: templateid
      },
      type: 'GET',
      success: function(data) {
        pnotify("Template deleted","success");
      }
    });
  }

  function createTemplate(){
    var TemplateCode = document.getElementById("TemplateCode").value;
    var TemplateName = document.getElementById("TemplateName").value;
    var TemplateText = $('#TemplateText').trumbowyg('html');

    url = "getdata.php?createTextTemplate"

    $.ajax({
      url: url,
      data: {
        TemplateCode: TemplateCode,
        TemplateName: TemplateName,
        TemplateText: TemplateText
      },
      type: 'GET',
      success: function(data) {
        location.reload(true);
      }
    });
  }

  function showtemplatetext(templateid){
    if(templateid){
      url = "getdata.php?getTextTemplateContent"

      $.ajax({
        url: url,
        data: {
          templateid: templateid
        },
        type: 'GET',
        success: function(data) {
          var obj = JSON.parse(data);
          for (var i = 0; i < obj.length; i++) {
            Code = obj[i].Code;
            Content = obj[i].Content;
            Name = obj[i].Name;
            if(Code){
              document.getElementById('TemplateCode').value = Code;
              document.getElementById('TemplateName').value = Name;
              $('#TemplateText').trumbowyg('html', Content);              
              document.getElementById("createTemplate").style.display = "none";
              document.getElementById("updateTemplate").style.display = "block";
              document.getElementById("deleteTemplate").style.display = "block";
              document.getElementById("templatetexttest").style.display = "block";
            }
            else{

            }
          }
        }
      });
    }
    else{
      document.getElementById('TemplateCode').value = "";
      document.getElementById('TemplateName').value = "";
      $('#TemplateText').trumbowyg('html', "");
      document.getElementById("createTemplate").style.display = "block";
      document.getElementById("updateTemplate").style.display = "none";
      document.getElementById("deleteTemplate").style.display = "none";
      document.getElementById("templatetexttest").style.display = "none";
    }    
  }
</script>
<?php include("./footer.php") ?>