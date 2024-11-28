<?php
include("./header.php");
?>
<!-- page content -->
<style>
    .sticky {
        position: -webkit-sticky;
        position: sticky;
        top: 0;
        align-self: flex-start;
        /* <-- this is the fix */
    }
</style>
<?php
$NotificationID = $_GET['notificationid'];
$UserID = $_SESSION['id'];
$sql = "SELECT RelatedModuleID, RelatedTypeID, NotificationDate, NotificationSubject, NotificationBody, InternalUrl, ReadDate, RelatedUserID, Mailsent, RelatedTeamID, RelatedElementID 
            FROM notifications
            WHERE notifications.ID = $NotificationID AND RelatedUserID = $UserID";
$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
    $NotificationDateVal = $row['NotificationDate'];
    $NotificationSubjectVal = $row['NotificationSubject'];
    $NotificationBodyVal = $row['NotificationBody'];
    $InternalUrlVal = $row['InternalUrl'];
}
?>
<script>
    $(document).ready(function() {
        var table = $('#TableNotificationsUnread').DataTable({
            paging: true,
            dom: 'Bfrtip',
            buttons: [],
            searching: true,
            pageLength: 5,
            language: {
                searchPlaceholder: "<?php echo _("Search"); ?>",
                search: "",
            },
            info: false,
            "order": []
        });

        var table = $('#TableNotificationsAll').DataTable({
            paging: true,
            dom: 'Bfrtip',
            buttons: [],
            searching: true,
            pageLength: 5,
            language: {
                searchPlaceholder: "<?php echo _("Search"); ?>",
                search: "",
            },
            info: false,
            "order": []
        });


        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {

            $($.fn.dataTable.tables(true)).css('width', '100%');
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
        });

    });
</script>

<script>
    function seeNotification(NotificationID) {

        $.post('./getdata.php', {
            notification: NotificationID
        });

        var notification = document.getElementById("body" + NotificationID).innerHTML;
        document.getElementById("NotificationView").value = (notification);

        document.getElementById('QuickURL').style.visibility = 'visible';
        var url = document.getElementById("url" + NotificationID).innerHTML;
        document.getElementById("QuickURL").innerHTML = (url);

        var NotificationDate = document.getElementById("NotificationDate" + NotificationID).innerHTML;
        document.getElementById("NotificationDateView").value = (NotificationDate);

        document.getElementById("NotificationID").value = (NotificationID);

    }

    function markNotificationAsRead(NotificationID) {

        $.post('./getdata.php', {
            notification: NotificationID
        });

    }
</script>
<?php
echo "<script>markNotificationAsRead($NotificationID);</script>";
?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="card">
                    <div class="card-body">
                        <div class="toolbar">
                            <!--        Here you can write extra buttons/actions for the toolbar              -->
                        </div>
                        <ul class="nav nav-tabs nav-fill mb-3" role="tablist" id="activetablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#TabUnread" role="tablist">
                                    <?php echo _("Unread"); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#TabAll" role="tablist">
                                    <?php echo _("All"); ?>
                                </a>
                        </ul>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="tab-content">
                                <div class="tab-pane active" id="TabUnread">
                                    <table id="TableNotificationsUnread" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <?php
                                        $UserID = $_SESSION["id"];
                                        $sql = "SELECT notifications.ID AS NotificationID, NotificationDate, NotificationSubject, NotificationBody, InternalUrl, ReadDate
                                                            FROM notifications
                                                            WHERE notifications.RelatedUserID='" . $UserID . "' AND ReadDate IS NULL
                                                            ORDER BY notifications.NotificationDate DESC";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        ?>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                                <tr>
                                                    <?php $NotificationDate = convertToDanishTimeFormat($row['NotificationDate']);
                                                    $NotificationID = $row['NotificationID'];
                                                    $NotificationSubject = $row['NotificationSubject'];
                                                    $NotificationBody = $row['NotificationBody'];
                                                    $InternalUrl = "<a href='" . $row['InternalUrl'] . "'>" . $row['InternalUrl'] . "</a>";
                                                    $ReadDate = $row['ReadDate'];
                                                    ?>
                                                    <td>
                                                        <?php
                                                        if ($ReadDate == NULL) {
                                                            $icon = "fa fa-circle";
                                                        } else {
                                                            $icon = "fa fa-circle-o";
                                                        }
                                                        echo "<a class='nav-link active' data-toggle='tab' role='tablist'>
                                                            <div id='from" . $NotificationID . "' style='display: none;'></div>
                                                            <div id='body" . $NotificationID . "' style='display: none;'>$NotificationBody</div>
                                                            <div id='url" . $NotificationID . "' style='display: none;'>$InternalUrl</div>
                                                            <span id='" . $NotificationID . "' onclick='seeNotification(this.id)'>
                                                                <div class'row'>
                                                                    <i class='$icon'></i><div id='NotificationDate" . $NotificationID . "'>" . $NotificationDate . "</div>
                                                                </div>
                                                                <p>
                                                                <div id='notification" . $NotificationID . "'>" . $NotificationSubject . ".
                                                                </div>
                                                                </p>
                                                            </span></a>";
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane" id="TabAll">
                                    <table id="TableNotificationsAll" class="table table-responsive table-borderless table-hover" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <?php
                                        $UserID = $_SESSION["id"];
                                        $sql = "SELECT notifications.ID AS NotificationID, NotificationDate, NotificationSubject, NotificationBody, InternalUrl, ReadDate
                                                            FROM notifications
                                                            WHERE notifications.RelatedUserID='" . $UserID . "' 
                                                            ORDER BY notifications.NotificationDate DESC";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        ?>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                                <tr>
                                                    <?php $NotificationDate = convertToDanishTimeFormat($row['NotificationDate']);
                                                    $NotificationID = $row['NotificationID'];
                                                    $NotificationSubject = $row['NotificationSubject'];
                                                    $NotificationBody = $row['NotificationBody'];
                                                    $InternalUrl = "<a href='" . $row['InternalUrl'] . "'>" . $row['InternalUrl'] . "</a>";
                                                    $ReadDate = $row['ReadDate'];
                                                    ?>
                                                    <td>
                                                        <?php
                                                        if ($ReadDate == NULL) {
                                                            $icon = "fa fa-circle";
                                                        } else {
                                                            $icon = "fa fa-circle-o";
                                                        }
                                                        echo "<a class='nav-link active' data-toggle='tab' role='tablist'>
                                                            <div id='from" . $NotificationID . "' style='display: none;'></div>
                                                            <div id='body" . $NotificationID . "' style='display: none;'>$NotificationBody</div>
                                                            <div id='url" . $NotificationID . "' style='display: none;'>$InternalUrl</div>
                                                            <span id='" . $NotificationID . "' onclick='seeNotification(this.id)'>
                                                                <div class'row'>
                                                                    <i class='$icon' pull-left></i><p class='float-end' id='NotificationDate" . $NotificationID . "'>$NotificationDate</p>
                                                                </div>
                                                                <p>
                                                                <div id='notification" . $NotificationID . "'>" . $NotificationSubject . ".
                                                                </div>
                                                                </p>
                                                            </span></a>";
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- CONTENT MAIL -->
            <div class="col-md-8 col-sm-8 col-xs-12">
                <div class="sticky">
                    <div class="card card-pricing card-raised">
                        <div class="card-body">
                            <div class="form-group">
                                <input type="hidden" class="form-control" id="NotificationID" name="NotificationID" value="<?php echo $NotificationID; ?>">
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?php echo _("Date"); ?></label>
                                <input type="text" id="NotificationDateView" name="NotificationDateView" class="form-control" value="<?php echo convertToDanishTimeFormat($NotificationDateVal); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?php echo _("Content"); ?></label>
                                <textarea id="NotificationView" name="NotificationView" rows="10" cols="30" class="resizable_textarea form-control" readonly><?php echo $NotificationBodyVal ?></textarea>
                            </div>
                            <br>
                            <div class="form-group text-center">
                                <a href='<?php echo ($InternalUrlVal); ?>'><button class="btn btn-sm btn-success d-flex align-items-center" id="QuickURL" name="QuickURL"><?php echo ($InternalUrlVal); ?></button></a>
                            </div>
                            <br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- /page content -->

<?php include("./footer.php") ?>