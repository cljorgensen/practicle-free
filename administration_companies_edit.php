<?php include("./header.php"); ?>
<?php
$CompanyID = $_GET['companyid'];
$RedirectPage = "administration_companies_edit.php?companyid=" . $CompanyID;
$RedirectPageUpload = "../administration_companies_edit.php?companyid=" . $CompanyID;
$ElementPath = "companies";
$ElementRef = "CompanyID";
$ElementGetValue = "companyid";
$UserID = $_SESSION["id"];
$CompanyNotes = "";

$sql = "SELECT ID, Companyname, Active, WebPage, Phone, RelatedSLAID, CustomerAccountNumber, Address, ZipCode, City, Country, Email, CBR, Notes 
            FROM companies
            WHERE ID = $CompanyID";

$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
    $CompanyIDVal = $row['ID'];
    $CompanyNameVal = $row['Companyname'];
    $CompanyActiveVal = $row['Active'];
    $WebPageVal = $row['WebPage'];
    $PhoneVal = $row['Phone'];
    $RelatedSLAIDVal = $row['RelatedSLAID'];
    $CustomerAccountNumberVal = $row['CustomerAccountNumber'];
    $Address = $row['Address'];
    $ZipCode = $row['ZipCode'];
    $City = $row['City'];
    $Country = $row['Country'];
    $Email = $row['Email'];
    $CBRVal = $row['CBR'];
    $NotesVal = $row['Notes'];
}
?>

<script>
    $(document).ready(function() {
        <?php initiateStandardSearchTable("customerlog"); ?>
        <?php initiateStandardSearchTable("TableDocuments") ?>
        <?php initiateStandardSearchTable("TableInvoices") ?>
        <?php initiateSimpleViewTable("fileuploads", 25, []); ?>
        <?php initiateSimpleViewTable("tableusersincompany", 25, []); ?>
        <?php initiateSimpleViewTable("tablebsincompany", 25, []); ?>
        CompanyID = <?php echo $CompanyID ?>;
    });
</script>

<script>
    function updateAndLogCompanyNotes() {
        CompanyID = "<?php echo $CompanyID ?>";
        let companynotes = getCKEditorContent('CompanyNotes');

        vData = {
            companynotes: companynotes,
            CompanyID: CompanyID
        };

        $.ajax({
            type: "POST",
            url: "./getdata.php?updateAndLogCompanyNotes",
            data: vData,
            success: function(data) {
                pnotify('Company notes updated', 'success');
            },
        });
    }
</script>

<script>
    function removeUserFromCompany(UserID) {
        CompanyID = <?php echo $CompanyID ?>;
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
                    document.getElementById("txtHint").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "getdata.php?removeuserfromcompany=" + CompanyID + "&userid=" + UserID, true);
            xmlhttp.send();
            location.reload(true);
        }
    }

    function addUserToCompany(UserID) {
        CompanyID = <?php echo $CompanyID ?>;
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
                    document.getElementById("txtHint").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "getdata.php?addUserToCompany=" + CompanyID + "&userid=" + UserID, true);
            xmlhttp.send();
            location.reload(true);
        }
    }

    function addBSToCompany(BSID) {
        CompanyID = <?php echo $CompanyID ?>;
        if (BSID == "") {
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
                    document.getElementById("txtHint").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "getdata.php?addBSToCompany=" + CompanyID + "&BSID=" + BSID, true);
            xmlhttp.send();
            location.reload(true);
        }
    }
    $(document).ready(function() {
        getFiles(<?php echo $CompanyID ?>, 'companies', 'TableCompanyFiles', '<?php echo $UserLanguageCode ?>');
    });
</script>
<?php
// Check if form is submitted successfully 
if (isset($_POST["submit_removeUserFromCompany"])) {
    // Check if any option is selected 
    if (isset($_POST["RemoveCompanyUsers"])) {
        // Retrieving each selected option 
        foreach ($_POST['RemoveCompanyUsers'] as $RemoveCompanyUsers)
            echo "<script type='text/javascript'>removeUserFromCompany('$RemoveCompanyUsers');</script>";
    } else {
        echo "Select an option first!";
    }
}

// Check if form is submitted successfully 
if (isset($_POST["submit_addUserToCompany"])) {
    // Check if any option is selected 
    if (isset($_POST["AddAvaliableUsers"])) {
        // Retrieving each selected option 
        foreach ($_POST['AddAvaliableUsers'] as $AddAvaliableUsers)
            echo "<script type='text/javascript'>addUserToCompany('$AddAvaliableUsers');</script>";
    } else {
        echo "Select an option first!";
    }
}

?>

<div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="card-group">
            <div class="card">
                <div class="card-header card-header"><i class="fa fa-building"></i> <a href="administration_companies.php"><?php echo _("Companies"); ?> </a> <i class="fa fa-angle-right fa-sm"></i> <a href="administration_companies_edit.php?companyid=<?php echo $CompanyIDVal; ?>"><?php echo $CompanyNameVal; ?></a></div>
                <div class="card-body">
                    <form id="updateCompanyForm">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _("Name"); ?> <code>*</code></label>
                                <div class="input-group input-group-static mb-4">
                                    <input type="text" class="form-control" id="CompanyName" name="CompanyName" value="<?php echo $CompanyNameVal; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Address"); ?></label>
                                <div class="input-group input-group-static mb-4">
                                    <input type="text" class="form-control" id="Address" name="Address" value="<?php echo $Address; ?>">
                                </div>
                            </div>

                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Zip"); ?></label>
                                <div class="input-group input-group-static mb-4">
                                    <input type="text" class="form-control" id="ZipCode" name="ZipCode" value="<?php echo $ZipCode; ?>">
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("City"); ?></label>
                                <div class="input-group input-group-static mb-4">
                                    <input type="text" class="form-control" id="City" name="City" value="<?php echo $City; ?>">
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Country"); ?></label>
                                <div class="input-group input-group-static mb-4">
                                    <select id="Country" name="Country" class="form-control">
                                        <option value='' label=''></option>
                                        <?php
                                        $sql = "SELECT abv, name
                                    FROM countries
                                    ORDER BY abv ASC;";

                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            if ($Country == $row['abv']) {
                                                echo "<option value='" . $row['abv'] . "' selected='select'>" . $row['name'] . "</option>";
                                            } else {
                                                echo "<option value='" . $row['abv'] . "'>" . $row['name'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="Email" class="ms-0"><?php echo _("Email"); ?> <code>*</code></label>
                                    <input type="text" class="form-control" id="Email" name="Email" value="<?php echo $Email; ?>" required>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="Phone" class="ms-0"><?php echo _("Phone"); ?></label>
                                    <input type="text" class="form-control" id="Phone" name="Phone" value="<?php echo $PhoneVal; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="CBR" class="ms-0"><?php echo _("CBR"); ?></label>
                                    <input type="text" class="form-control" id="CBR" name="CBR" value="<?php echo $CBRVal; ?>">
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="WebPage" class="ms-0"><?php echo _("Webpage"); ?></label>
                                    <input type="text" class="form-control" id="WebPage" name="WebPage" value="<?php echo $WebPageVal; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="CustomerAccountNumber" class="ms-0"><?php echo _("Account number"); ?></label>
                                    <input type="text" class="form-control" id="CustomerAccountNumber" name="CustomerAccountNumber" value="<?php echo $CustomerAccountNumberVal; ?>">
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="RelatedSLAID" class="ms-0"><?php echo _("SLA"); ?> <code>*</code></label>
                                    <select id="RelatedSLAID" name="RelatedSLAID" class="form-control" required>
                                        <option value='' label=''></option>
                                        <?php
                                        $sql = "SELECT slaagreements.ID, slaagreements.Name
                                                FROM slaagreements";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            if ($RelatedSLAIDVal == $row['ID']) {
                                                echo "<option value='" . $row['ID'] . "' selected='select'>" . _($row['Name']) . "</option>";
                                            } else {
                                                echo "<option value='" . $row['ID'] . "'>" . _($row['Name']) . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                <div class="input-group input-group-static mb-4">
                                    <label for="Active" class="ms-0"><?php echo _("Status"); ?></label>
                                    <select class="form-control" id="Active" name="Active">
                                        <?php
                                        if ($CompanyActiveVal == True) {
                                            echo "<option value='1' selected='select'>Active</option>";
                                            echo "<option value='0'>Not Active</option>";
                                        } else {
                                            echo "<option value='1'>Active</option>";
                                            echo "<option value='0' selected='select'>Not Active</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                    <?php
                    if (in_array("100001", $group_array) || in_array("100025", $group_array)) {
                        echo "<div class='col-md-12 col-sm-12 col-xs-12'><button name='updateCompany' id='updateCompany' class='btn btn-sm btn-success float-end' onclick='updateCompany($CompanyID);'>" ?><?php echo _('Update'); ?><?php echo "</button></div>";
                                                                                                                                                                                                                                        echo "<div class='col-md-12 col-sm-12 col-xs-12'><button name='removeCompany' id='removeCompany' class='btn btn-sm btn-danger float-end' onclick='removeCompany($CompanyID);'>" ?><?php echo _('Remove'); ?><?php echo "</button></div>";
                                                                                                                                                                                                                                                                                                                                                                                                                                                } else {
                                                                                                                                                                                                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                                                                                                                                                                                                    ?>
                </div>
            </div>
        </div>
    </div>


    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="card-group">
            <div id="TabSection" class='card'>
                <div class="card-header card-header"><i class="fas fa-info-circle"> </i> <?php echo _("Details"); ?></div>
                <div class="card-body">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="scrolling-menu-wrapper">
                            <div class="arrow arrow-left">&#9664;</div>
                            <div class="scrolling-menu">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#NotesTab">
                                            <?php echo _("Notes"); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#RelationsTab">
                                            <?php echo _("Relations"); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#DocumentsTab">
                                            <?php echo _("Documents"); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#FilesTab">
                                            <?php echo _("Files"); ?>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" data-bs-toggle="tab" href="#LogTab">
                                            <?php echo _("History"); ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="arrow arrow-right">&#9654;</div>
                        </div>
                    </div>
                    <br>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <!-- Tab panes -->
                        <div class="tab-content">
                            <?php
                            if (isset($_POST['submit_updateCompanyNotes'])) {
                                $RedirectPage = "administration_companies_edit.php?companyid=$CompanyIDVal";
                                $UserID = $_SESSION["id"];
                                updateCompanyNotes($UserID, $CompanyIDVal, htmlspecialchars($_POST['CompanyNotes']));

                                $LogTypeID = "2";
                                $LogActionText = "Company Notes updated by: " . $_SESSION["username"];
                                createCompanyLogEntry($CompanyIDVal, $UserID, $LogTypeID, $LogActionText);

                                $RedirectPagego = "<meta http-equiv='refresh' content='0';url=$RedirectPage><p><b><div class='alert alert-success'><strong>Company Notes updated</strong></div></b></p>";
                                echo $RedirectPagego;
                            }

                            if (isset($_POST['submit_updateCompany'])) {
                                $RedirectPage = "administration_companies_edit.php?companyid=$CompanyIDVal";
                                $UserID = $_SESSION["id"];
                                $CompanyNameVal = htmlspecialchars($_POST['CompanyName']);

                                $Address = htmlspecialchars($_POST['Address']);
                                $ZipCode = htmlspecialchars($_POST['ZipCode']);
                                $City = htmlspecialchars($_POST['City']);
                                $Country = htmlspecialchars($_POST['Country']);
                                $Email = htmlspecialchars($_POST['Email']);
                                $CBRVal = htmlspecialchars($_POST['CBRVal']);
                                $WebPageVal = htmlspecialchars($_POST['WebPageVal']);
                                $PhoneVal = htmlspecialchars($_POST['PhoneVal']);
                                $CompanyStatusVal = $_POST['CompanyStatus'];
                                $CustomerAccountNumberVal = htmlspecialchars($_POST['CustomerAccountNumberVal']);
                                $RelatedSLAIDVal = $_POST['RelatedSLAIDVal'];

                                updateCompanyInformation($UserID, $CompanyIDVal, $CompanyNameVal, $Address, $ZipCode, $City, $Country, $Email, $CBRVal, $PhoneVal, $CompanyStatusVal, $CustomerAccountNumberVal, $RelatedSLAIDVal, $WebPageVal);

                                $LogTypeID = "2";
                                $LogActionText = "Company updated by: " . $_SESSION["username"];
                                createCompanyLogEntry($CompanyIDVal, $UserID, $LogTypeID, $LogActionText);

                                $RedirectPagego = "<meta http-equiv='refresh' content='0';url=$RedirectPage><p><b><div class='alert alert-success'><strong>Company updated</strong></div></b></p>";
                                echo $RedirectPagego;
                            }
                            ?>
                            <div class="tab-pane active" id="NotesTab">
                                <label for="CompanyNotes" title="edit"><?php echo _("Notes"); ?></label>
                                <a href="javascript:toggleCKEditor('CompanyNotes');">&ensp;<i class="fa-solid fa-pen fa-sm" title="Double click on field to edit"></i></a>
                                <div style="height: 250px; word-wrap: break-word; overflow-y: auto; overflow-x: auto;" class="resizable_textarea form-control" id="CompanyNotes" name="CompanyNotes" title="Double click to edit" rows="10" autocomplete="off" ondblclick="toggleCKEditor('UserNotes');">
                                    <?php echo $NotesVal; ?>
                                </div>
                                <button class="btn btn-sm btn-success float-end" onclick="updateAndLogCompanyNotes()"><?php echo _('Update') ?></button>
                            </div>

                            <div class="tab-pane" id="LogTab">
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <table id="customerlog" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th><?php echo _("Date"); ?></th>
                                                <th><?php echo _("Text"); ?></th>
                                                <th><?php echo _("User"); ?></th>
                                            </tr>
                                        </thead>
                                        <?php
                                        $sql = "SELECT LogActionDate, LogActionText, Users.Username AS Username 
                                                FROM log_companies
                                                INNER JOIN Users ON log_companies.UserID = Users.ID
                                                WHERE log_companies.RelatedCompanyID = $CompanyIDVal
                                                ORDER BY LogActionDate DESC";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        ?>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                                <tr class='text-sm text-secondary mb-0'>
                                                    <td><?php $myFormatForView = convertToDanishTimeFormat($row['LogActionDate']);
                                                        echo $myFormatForView; ?> </td>
                                                    <td><?php echo $row['LogActionText']; ?></td>
                                                    <td><?php echo $row['Username']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane" id="RelationsTab">
                                <?php
                                if (in_array("100001", $group_array) || in_array("29", $group_array)) {
                                ?>
                                    <div class="row">
                                        <div class='col-md-6 col-sm-6 col-xs-12'>
                                            <h6><?php echo _("Members") ?></h6>
                                            <div class="row">
                                                <div class='col-md-12 col-sm-12 col-xs-12' style="min-height:300px;">
                                                    <table id="tableusersincompany" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <?php
                                                        $sql =    "SELECT users.ID AS UserID, CONCAT(Firstname,' ',Lastname) AS FullName, Username
                                                                FROM users
                                                                WHERE users.CompanyID = $CompanyIDVal AND users.Active = 1
                                                                ORDER BY FullName ASC";

                                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                        ?>
                                                        <tbody>
                                                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                                                <?php $UsersID = $row['UserID']; ?>
                                                                <tr class='text-sm text-secondary mb-0'>
                                                                    <td width="80%"><a href="administration_users_edit.php?usersid=<?php echo $UsersID ?>"><?php echo $row['FullName']; ?></a></td>
                                                                    <td width="20%">
                                                                        <div data-bs-toggle="tooltip" data-bs-title="<?php echo _("Username") ?>"><?php echo strtolower($row['Username']); ?>
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <?php $ID = "tableContactsAvailable" ?>
                                            <a class="collapsed" data-bs-toggle="collapse" href="#collapse<?php echo $ID; ?>" aria-expanded="false" aria-controls="collapse<?php echo $ID; ?>">
                                                <h6><?php echo _("Members Available") ?></h6>
                                            </a>
                                            <div class="row">
                                                <div class='col-md-12 col-sm-12 col-xs-12'>
                                                    <div id="collapse<?php echo $ID; ?>" class="collapse" role="tabpanel" data-parent="#selecttables">
                                                        <div class="card-body">
                                                            <form method='post'>
                                                                <div class="input-group input-group-static mb-4">
                                                                    <select class='form-control' id='AvaliableUsers' name='AddAvaliableUsers[]' ondblclick='addUserToCompany(this.value)' size='15' multiple>
                                                                        <?php
                                                                        $sql = "SELECT users.ID AS UserID, CONCAT(users.Firstname,' ',users.Lastname,' (',Username,')') AS FullName
                                                                            FROM users
                                                                            WHERE users.CompanyID IS NULL AND users.Active IN (1,2)
                                                                            ORDER BY FullName ASC";
                                                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                                                        while ($row = mysqli_fetch_array($result)) {
                                                                            echo "<option value='" . $row['UserID'] . "'>" . $row['FullName'] . "</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                                <input type='submit' name='submit_addUserToCompany' value='<?php echo _("Add") ?>' class='btn btn-sm btn-success float-end'>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='col-md-6 col-sm-6 col-xs-12'>
                                            <h6><?php echo _("Business Services") ?></h6>
                                            <div class="row">
                                                <div class='col-md-12 col-sm-12 col-xs-12' style="min-height:300px;">
                                                    <table id="tablebsincompany" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <?php
                                                        $sql = "SELECT cmdb_ci_jsf03ynsyjuvoug.ID, cmdb_ci_jsf03ynsyjuvoug.CIField16831324 AS Name
                                                                FROM cmdb_ci_jsf03ynsyjuvoug
                                                                WHERE cmdb_ci_jsf03ynsyjuvoug.Active = 1 AND cmdb_ci_jsf03ynsyjuvoug.RelatedCompanyID = $CompanyIDVal";

                                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                                                        ?>
                                                        <tbody>
                                                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                                                <?php $BusinessServiceID = $row['ID']; ?>
                                                                <?php $BusinessServiceName = $row['Name']; ?>
                                                                <tr class='text-sm text-secondary mb-0'>
                                                                    <td width="80%"><a href="javascript:runModalViewCI('<?php echo $BusinessServiceID ?>','1','1');"><?php echo $BusinessServiceName ?></a></td>
                                                                </tr>
                                                            <?php } ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                } else {
                                }
                                ?>
                            </div>

                            <div class="tab-pane" id="DocumentsTab">
                                <table id="TableDocuments" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th><?php echo _("Name"); ?></th>
                                            <th><?php echo _("Status"); ?></th>
                                            <th><?php echo _("Expiration"); ?></th>
                                        </tr>
                                    </thead>
                                    <?php
                                    $sql = "SELECT knowledge_documents.ID AS DocID, RelatedCategory, knowledge_documents.Name, knowledge_documents.Version, RelatedGroupID, RelatedReviewerID, RelatedApproverID, RelatedOwnerID, Content, ContentFullText, LastChanged, 
                                            LastChangedBy, ExpirationDate, knowledge_status.Name AS StatusName, knowledge_documents.RelatedStatusID
                                            FROM knowledge_documents
                                            INNER JOIN knowledge_companies ON knowledge_documents.ID = knowledge_companies.DocID
                                            INNER JOIN knowledge_status ON knowledge_documents.RelatedStatusID = knowledge_status.ID
                                            WHERE knowledge_companies.CompanyID = $CompanyIDVal AND RelatedStatusID = 5
                                            ORDER BY Name ASC";

                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                    ?>
                                    <tbody>
                                        <?php while ($row = mysqli_fetch_array($result)) { ?>
                                            <tr class='text-sm text-secondary mb-0'>
                                                <td><a href="knowledge_view.php?docid=<?php echo $row['DocID']; ?>"><?php echo $row['Name'] ?></a></td>
                                                <td><?php echo $row['StatusName'] ?></td>
                                                <td><?php echo $myFormatForView = convertToDanishTimeFormat($row['ExpirationDate']); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="tab-pane" id="FilesTab">
                                <form action="../functions/cifileupload.php?userid=<?php echo $_SESSION['id']; ?>&elementref=6&elementid=<?php echo $CompanyID ?>&elementpath=companies" class="dropzone" id="dropzoneform"></form>
                                <table id="TableCompanyFiles" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("./footer.php"); ?>