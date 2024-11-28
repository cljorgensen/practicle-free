<?php include("./header.php"); ?>
<?php
if (in_array("100001", $group_array) || in_array("100012", $group_array) || in_array("100013", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<?php
//Set important variables to work with
$TicketID = $_GET["elementid"];
$TicketSubject = $_GET["ticketsubject"];
$TicketCompanyID = $_GET["companyid"];
$NewProblemID = "";
$UserID = $_SESSION["id"];
$UserFullName = $_SESSION["userfullname"];
?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="card">
                    <div class="card-header card-header-problems card-header-icon">
                        <div class="card-icon">
                            <i class='fa fa-bug fa-2x'></i>
                        </div>
                        <h4 class="card-title"> <?php echo _('Create problem based on ')._("incident") ?> <?php echo $TicketID; ?></h4>
                    </div>
                    <div class="card-body">

                        <?php
                        if (isset($_POST['create_problem'])) {
                            $ProblemName = $_POST["ProblemName"];
                            $ProblemDescription = $_POST["ProblemDescription"];
                            $ProblemCompany = $_POST["ProblemCompany"];
                            $ProblemResponsible = $_POST["ProblemResponsible"];
                            $ProblemPriority = $_POST["ProblemPriority"];
                            $ProblemStatus = $_POST["ProblemStatus"];
                            $ProblemDeadline = $_POST["ProblemDeadline"];

                            $NewProblemID = createProblemFromIncident($ProblemName, $ProblemDescription, $ProblemCompany, $ProblemResponsible, $ProblemPriority, $ProblemStatus, $ProblemDeadline, $TicketID);

                            echo "<script type='text/javascript'>
                window.location.href = 'problems_view.php?elementid=$NewProblemID';
            </script>";
                        }

                        ?>
                        <div class="form-group">
                            <form method="POST">
                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _('Name') ?></label>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <input type="text" class="form-control float-end" id="ProblemName" name="ProblemName" value="<?php echo _('Problem created from incident') ?> <?php echo $TicketID; ?>">
                                </div>

                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _('Description') ?></label>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <textarea id="ProblemDescription" name="ProblemDescription" rows="10" class="resizable_textarea form-control"><?php echo $TicketSubject; ?></textarea>
                                </div>

                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _('Deadline') ?></label>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <input type="text" class="form-control float-end" id="ProblemDeadline" name="ProblemDeadline" value="<?php echo $DateTimeNow = date('d-m-Y H:i:s'); ?>">
                                </div>

                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _('Affected Company') ?></label>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <select id="ProblemCompany" name="ProblemCompany" class="form-control" required>
                                        <?php
                                        $sql = "SELECT ID, CompanyName FROM companies WHERE Active=TRUE";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            if ($TicketCompanyID == $row['ID']) {
                                                echo "<option value='" . $row['ID'] . "' selected='select'>" . $row['CompanyName'] . "</option>";
                                            } else {
                                                echo "<option value='" . $row['ID'] . "'>" . $row['CompanyName'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _('Responsible') ?></label>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <select id="ProblemResponsible" name="ProblemResponsible" class="form-control" required>
                                        <?php
                                        $sql = "SELECT ID, FullName FROM ticketusers WHERE Active=TRUE";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            if ($ProblemResponsibleVal == $row['ID']) {
                                                echo "<option value='" . $row['ID'] . "' selected='select'>" . $row['FullName'] . "</option>";
                                            } else {
                                                echo "<option value='" . $row['ID'] . "'>" . $row['FullName'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _('Priority') ?></label>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <select id="ProblemPriority" name="ProblemPriority" class="form-control" required>
                                        <?php
                                        $sql = "SELECT ID, Name FROM ticketpriorities WHERE Active=TRUE";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            $Priority = $row['Name'];
                                            if ($ProblemPriorityVal == $row['ID']) {
                                                echo "<option value='" . $row['ID'] . "' selected='select'>" . _("$Priority") . "</option>";
                                            } else {
                                                echo "<option value='" . $row['ID'] . "'>" . _("$Priority") . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <label class="control-label col-md-12 col-sm-12 col-xs-12"><?php echo _('Status') ?></label>
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <select id="ProblemStatus" name="ProblemStatus" class="form-control" required>
                                        <?php
                                        $sql = "SELECT ID, StatusName FROM problemstatuscodes WHERE Active=TRUE";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        while ($row = mysqli_fetch_array($result)) {
                                            $StatusName = $row['StatusName'];
                                            if ($ProblemStatusVal == $row['ID']) {
                                                echo "<option value='" . $row['ID'] . "' selected='select'>" . _("$StatusName") . "</option>";
                                            } else {
                                                echo "<option value='" . $row['ID'] . "'>" . _("$StatusName") . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                        </div>

                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <button type="submit" name="create_problem" class="btn btn-sm btn-success float-end"><?php echo _('Create') ?></button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function() {

        jQuery('#ProblemDeadline').datetimepicker({
            format: 'd-m-Y H:i',
            prevButton: false,
            nextButton: false,
            step: 60,
            dayOfWeekStart: 1
        });
        $.datetimepicker.setLocale('<?php echo $languageshort ?>');
    });
</script>

<!-- /page content -->
<?php include("./footer.php"); ?>