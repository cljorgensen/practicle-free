<?php
include("./header.php");
?>
<!-- page content -->

<!-- Start task list -->
<script>
    function addSkill(str) {
        if (str == "") {
            document.getElementById("txtHint1").innerHTML = "";
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
                    document.getElementById("txtHint1").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "getdata.php?addskillid=" + str, true);
            xmlhttp.send();
        }
    }

    function removeSkill(str) {
        if (str == "") {
            document.getElementById("txtHint1").innerHTML = "";
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
                    document.getElementById("txtHint1").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "getdata.php?removeskillid=" + str, true);
            xmlhttp.send();
        }
    }

    function addPersSkill(str) {
        if (str == "") {
            document.getElementById("txtHint2").innerHTML = "";
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
                    document.getElementById("txtHint2").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "getdata.php?addpersskillid=" + str, true);
            xmlhttp.send();
        }
    }

    function removePersSkill(str) {
        if (str == "") {
            document.getElementById("txtHint2").innerHTML = "";
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
                    document.getElementById("txtHint3").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "getdata.php?removepersskillid=" + str, true);
            xmlhttp.send();
        }
    }

    function removeCertificate(str) {
        if (str == "") {
            document.getElementById("txtHint2").innerHTML = "";
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
                    document.getElementById("txtHint2").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "getdata.php?removeCertificate=" + str, true);
            xmlhttp.send();
        }
    }

    function addCertificate(str) {
        if (str == "") {
            document.getElementById("txtHint2").innerHTML = "";
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
                    document.getElementById("txtHint2").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "getdata.php?removeCertificate=" + str, true);
            xmlhttp.send();
        }
    }
</script>

<?php
// Check if form is submitted successfully 
if (isset($_POST["submit_skills"])) {
    // Check if any option is selected 
    if (isset($_POST["Skills"])) {
        // Retrieving each selected option 
        foreach ($_POST['Skills'] as $Skills) {
            echo "<script type='text/javascript'>addSkill('$Skills');</script>";
        }
    } else {
        echo "Select an option first!";
    }
}

// Check if form is submitted successfully 
if (isset($_POST["submit_removeskills"])) {
    // Check if any option is selected 
    if (isset($_POST["RemoveSkills"])) {
        // Retrieving each selected option 
        foreach ($_POST['RemoveSkills'] as $RemoveSkills)
            echo "<script type='text/javascript'>removeSkill('$RemoveSkills');</script>";
    } else {
        echo "Select an option first!";
    }
}

// Check if form is submitted successfully 
if (isset($_POST["submit_removePersSkill"])) {
    // Check if any option is selected 
    if (isset($_POST["removePersSkill"])) {
        // Retrieving each selected option 
        foreach ($_POST['removePersSkill'] as $removePersSkill)
            echo "<script type='text/javascript'>removePersSkill('$removePersSkill');</script>";
    } else
        echo "Select an option first!";
}

// Check if form is submitted successfully 
if (isset($_POST["submit_persskills"])) {
    // Check if any option is selected 
    if (isset($_POST["AddPersSkills"])) {
        // Retrieving each selected option 
        foreach ($_POST['AddPersSkills'] as $AddPersSkills)
            echo "<script type='text/javascript'>addPersSkill('$AddPersSkills');</script>";
    } else
        echo "Select an option first!";
}

// Check if form is submitted successfully 
if (isset($_POST["submit_removecertificate"])) {
    // Check if any option is selected 
    if (isset($_POST["PersCertificate"])) {
        // Retrieving each selected option 
        foreach ($_POST['PersCertificate'] as $PersCertificate)
            echo "<script type='text/javascript'>removeCertificate('$PersCertificate');</script>";
    } else
        echo "Select an option first!";
}

// Check if form is submitted successfully 
if (isset($_POST["submit_newcertificate"])) {
    $CertificateName = $_POST["CertificateName"];
    addNewCertificate($UserID, $CertificateName);
}

?>
<div class="right_col" role="main">
    <div class="row">
        <div class="col-md-4 col-sm-4 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <div id="responseandredirect1"></div>
                    <h4><i class="fa fa-graduation-cap"></i> <?php echo _("Professional skills of ") . " " . $_SESSION["userfullname"]; ?></h4>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <h3>Available</h3>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <form method='post'>
                                <select class="form-control" name='Skills[]' ondblclick="addSkill(this.value)" id="Skills" size="10" multiple>
                                    <?php
                                    $sql = "SELECT ID, Name
                                    FROM skills
                                    ORDER BY Name ASC";
                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                    while ($row = mysqli_fetch_array($result)) {
                                        echo "<option value='" . $row['ID'] . "'>" . $row['Name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <input type='submit' name='submit_skills' value='Add' class='btn btn-sm btn-dark float-end'>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <h3>Your skills</h3>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <form method='post'>
                                <select class="form-control" name='RemoveSkills[]' ondblclick="removeSkill(this.value)" id="RemoveSkills" size="10" multiple>
                                    <?php
                                    $sql = "SELECT hr_skills.ID, skills.Name, hr_skills.RelatedUserID
                                    FROM hr_skills 
                                    LEFT JOIN skills ON hr_skills.RelatedSkillID  = skills.ID
                                    WHERE hr_skills.RelatedUserID = '" . $UserID . "' AND skills.Name IS NOT NULL
                                    ORDER BY Name ASC";
                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                    while ($row = mysqli_fetch_array($result)) {
                                        echo "<option value='" . $row['ID'] . "'>" . $row['Name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <input type='submit' name='submit_removeskills' value='Remove' class='btn btn-sm btn-dark float-end'>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="txtHint1"><b></b></div>
            </div>
        </div>

        <div class="col-md-4 col-sm-4 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <div id="responseandredirect2"></div>
                    <h4><i class="fa fa-smile-o"></i> <?php echo _("Personal skills of ") . " " . $_SESSION["userfullname"]; ?></h3>

                        <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h3>Available</h4>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <form method='post'>
                                    <select class="form-control" name='AddPersSkills[]' ondblclick="addPersSkill(this.value)" id="PersSkills" size="10" multiple>
                                        <?php
                                        $sql = "SELECT ID, Name 
                                FROM personal_skills
                                ORDER BY Name ASC";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            echo "<option value='" . $row['ID'] . "'>" . $row['Name'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <input type='submit' name='submit_persskills' value='Add' class='btn btn-sm btn-dark float-end'>
                                </form>
                            </div>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h3>Your top 5 skills</h3>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <form method='post'>
                                <select class="form-control" name='removePersSkill[]' ondblclick="removePersSkill(this.value)" id="remPersonalSkills" size="10" multiple>
                                    <?php
                                    $sql = "SELECT hr_skills.ID, personal_skills.Name, hr_skills.RelatedUserID
                                FROM hr_skills 
                                LEFT JOIN personal_skills ON hr_skills.RelatedPersonalSkilID = personal_skills.ID
                                WHERE hr_skills.RelatedUserID = '" . $UserID . "' AND personal_skills.Name IS NOT NULL
                                ORDER BY Name ASC";
                                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                    while ($row = mysqli_fetch_array($result)) {
                                        echo "<option value='" . $row['ID'] . "'>" . $row['Name'] . "</option>";
                                    }
                                    ?>
                                </select>
                                <input type='submit' name='submit_removePersSkill' value='Remove' class='btn btn-sm btn-dark float-end'>
                            </form>
                        </div>
                    </div>
                </div>
                <div id="txtHint2"><b></b></div>
            </div>
        </div>

        <div class="col-md-4 col-sm-4 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <div id="responseandredirect2"></div>
                    <h4><i class="fa fa-file-text-o"></i> <?php echo _("Certificates of ") . " " . $_SESSION["userfullname"]; ?></h3>

                        <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h3>Available</h4>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <form action="administration_personal_skills.php" method="POST">
                                    <select class="form-control" name='PersCertificate[]' ondblclick="removeCertificate(this.value)" id="PersCertificate" size="10" multiple>
                                        <?php
                                        $sql = "SELECT ID, Name 
                                FROM personal_certificates 
                                WHERE personal_certificates.RelatedUserID = $UserID
                                ORDER BY Name ASC";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            echo "<option value='" . $row['ID'] . "'>" . $row['Name'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <input type='submit' name='submit_removecertificate' value='Remove' class='btn btn-sm btn-alert float-end'>
                                </form>
                            </div>
                    </div>
                </div>

                <div class="x_content">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h3>Add</h4>
                            <form action="administration_personal_skills.php" role="form" method="POST">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo _("Certificate Name"); ?></label>
                                <div class="autocomplete=off col-md-9 col-sm-9 col-xs-12">
                                    <input type="text" name="CertificateName" id="CertificateName" class="form-control" value="">
                                </div>
                                <input type='submit' name='submit_newcertificate' value='Add' class='btn btn-sm btn-dark float-end'>
                            </form>
                    </div>
                </div>
            </div>
            <div id="txtHint3"><b></b></div>
        </div>
    </div>
</div>
<div class="clearfix"></div>

<!-- /page content -->

<?php include("./footer.php") ?>