<?php
include("./header.php");
?>
<?php include("./modals/modal_message_user.php") ?>
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
$MessageID = $_GET['messageid'];
$SessionUserID = $_SESSION['id'];
$sql = "SELECT messages.ID, messages.ToUserID, messages.FromUserID, CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName, messages.Message, messages.SendDate, messages.ReadDate 
            FROM messages
            INNER JOIN users ON messages.FromUserID = users.ID
            WHERE messages.ID = $MessageID AND messages.ToUserID = $SessionUserID";
$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
    $ToUserIDVal = $row['ToUserID'];
    $FromUserNameVal = $row['FullName'];
    $FromUserIDVal = $row['FromUserID'];
    $MessageVal = $row['Message'];
    $SendDateVal = $row['SendDate'];
}
?>
<script>
    $(document).ready(function() {
        var table = $('#myMessagesInbox').DataTable({
            sDom: 'Brtip',
            bFilter: true,
            paging: true,
            info: false,
            pagingType: 'numbers',
            processing: true,
            searching: true,
            deferRender: true,
            orderCellsTop: true,
            fixedHeader: true,
            autoWidth: false,
            aaSorting: [],
            responsive: true,
            buttons: [],
            pageLength: 10,
            language: {
                searchPlaceholder: "<?php echo _("Search"); ?>",
                search: "",
            },
            info: false,
            "order": []
        });

        var table = $('#myMessagesSent').DataTable({
            paging: true,
            dom: 'Bfrtip',
            buttons: [],
            searching: true,
            pageLength: 10,
            language: {
                searchPlaceholder: "<?php echo _("Search"); ?>",
                search: "",
            },
            info: false,
            "order": []
        });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            $($.fn.dataTable.tables(true)).css('width', '100%');
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust().draw();
        });
    });
</script>

<script>
    function seeMessage(messageid, type) {

        /*
            $.post('./getdata.php', {
            message: messageid
            });
*/
        var url = './getdata.php?getMessageInformations';
        $.ajax({
            url: url,
            data: {
                messageid: messageid
            },
            type: 'GET',
            success: function(data) {
                var obj = JSON.parse(data);
                for (var i = 0; i < obj.length; i++) {
                    var MessageID = obj[i].MessageID;
                    var FromUserID = obj[i].FromUserID;
                    var FromUserFullName = obj[i].FromUserFullName;
                    var Message = obj[i].Message;
                    var SendDate = obj[i].SendDate;

                    document.getElementById('modalDateSent').hidden = false;
                    document.getElementById('modalDateLabel').hidden = false;
                    markMessageAsRead(MessageID);
                    viewMessage(FromUserID, FromUserFullName, SendDate, Message);
                    document.getElementById('modalMessageID').value = MessageID;
                    document.getElementById('ModalViewMessageText').value = Message;
                    if (type === 'sent') {
                        document.getElementById('AnswerButton').hidden = true
                        document.getElementById('sendAnswerButton').hidden = true
                        document.getElementById('labelsender').innerHTML = '<?php echo _("Reciever") ?>';
                    } else {
                        document.getElementById('AnswerButton').hidden = false
                        document.getElementById('sendAnswerButton').hidden = true
                        document.getElementById('labelsender').innerHTML = '<?php echo _("Sender") ?>';
                    }
                }
            }
        });
    }

    function markMessageAsRead(messageid) {

        $.post('./getdata.php', {
            message: messageid
        });

    }
</script>
<?php
echo "<script>markMessageAsRead($MessageID);</script>";
?>
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="card">
                    <div class="card-body">
                        <div class="toolbar">
                            <button id="createNewMessage" name="createNewMessage" class='btn btn-sm btn-success float-end' onclick="NewMessage('<?php echo $UsersID; ?>')"><i class="fas fa-envelope"></i></button>
                        </div>
                        <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#messageinbox" role="tablist">
                                    <?php echo _("Inbox"); ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#messagesent" role="tablist">
                                    <?php echo _("Sent"); ?>
                                </a>
                        </ul>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="tab-content">
                                <div class="tab-pane active" id="messageinbox">
                                    <table id="myMessagesInbox" class="table dt-responsive table-bordered table-hover" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <?php
                                        $SessionUserID = $_SESSION["id"];
                                        $sql = "SELECT messages.ID AS MessageID, messages.Message AS MessageFull, SUBSTRING(messages.Message, 1, 60) AS MessageShort, 
                                                CONCAT(users.Firstname,' ',users.Lastname) AS FullName, messages.SendDate, messages.ReadDate AS ReadDate, 
                                                (SELECT CONCAT(users.Firstname,' ',users.Lastname) AS FullName FROM users INNER JOIN messages ON messages.FromUserID = users.ID WHERE messages.ID = MessageID) AS FromUser, FromUserID
                                                FROM messages 
                                                LEFT JOIN users ON messages.FromUserID = users.ID
                                                WHERE messages.ToUserID=$SessionUserID
                                                ORDER BY messages.SendDate DESC";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        ?>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                                <tr>
                                                    <?php $ReadDate = $row['ReadDate'];
                                                    $MessageID = $row['MessageID'];
                                                    $FromUser = $row['FromUser'];
                                                    $FromUserID = $row['FromUserID'];
                                                    $MessageShort = $row['MessageShort'];
                                                    if (strpos($MessageShort, 'base64') !== false) {
                                                        $MessageShort = "Indeholder billede";
                                                    }
                                                    $Senddate = convertToDanishTimeFormat($row['SendDate']);
                                                    ?>
                                                    <td>
                                                        <?php
                                                        if ($ReadDate == NULL) {
                                                            $icon = "fa fa-circle";
                                                        } else {
                                                            $icon = "fa fa-circle-o";
                                                        }
                                                        echo "<div id='from" . $MessageID . "' style='display: none;'>" . $FromUser . "</div><div id='fromuserid" . $MessageID . "' style='display: none;'>" . $FromUserID . "</div>
                                                        <span role='button'><a id='" . $MessageID . "' onclick=\"seeMessage(this.id,'recieved')\"><div class='mail_list'><div class='left'><i class='$icon'></i></div><div class='right'>" . $FromUser . "<small><div id='SendDate" . $MessageID . "'>" . $Senddate . "</div></small>
                                                        <p><div id='message" . $MessageID . "'>" . $MessageShort . ".</div></p></div></div></a></span>";
                                                        ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="tab-pane" role='tab' id="messagesent">
                                    <table id="myMessagesSent" class="table dt-responsive table-bordered table-hover" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <?php
                                        $SessionUserID = $_SESSION["id"];
                                        $sql = "SELECT messages.ID AS MessageID, messages.Message AS MessageFull, SUBSTRING(messages.Message, 1, 60) AS MessageShort, 
                                                CONCAT(users.Firstname,' ',users.Lastname) AS FullName, messages.SendDate, messages.ReadDate AS ReadDate, 
                                                (SELECT CONCAT(users.Firstname,' ',users.Lastname) AS FullName FROM users INNER JOIN messages ON messages.ToUserID = users.ID WHERE messages.ID = MessageID) AS ToUser, FromUserID
                                                FROM messages 
                                                LEFT JOIN users ON messages.ToUserID = users.ID
                                                WHERE messages.FromUserID = $SessionUserID
                                                ORDER BY messages.SendDate DESC";
                                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                                        ?>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_array($result)) { ?>
                                                <tr>
                                                    <?php $ReadDate = $row['ReadDate'];
                                                    $MessageID = $row['MessageID'];
                                                    $ToUser = $row['ToUser'];
                                                    $ToUserID = $row['FromUserID'];
                                                    $MessageShort = $row['MessageShort'];
                                                    if (strpos($MessageShort, 'base64') !== false) {
                                                        $MessageShort = "Indeholder billede";
                                                    }
                                                    $Senddate = convertToDanishTimeFormat($row['SendDate']);
                                                    ?>
                                                    <td>
                                                        <?php
                                                        if ($ReadDate == NULL) {
                                                            $icon = "fa fa-circle";
                                                        } else {
                                                            $icon = "fa fa-circle-o";
                                                        }
                                                        echo "<a class='nav-link active' data-toggle='tab' role='tablist'><div id='from" . $MessageID . "' style='display: none;'>" . $ToUser . "</div><div id='fromuserid" . $MessageID . "' style='display: none;'>" . $ToUserID . "</div>
                                                        <span id='" . $MessageID . "' onclick=\"seeMessage(this.id,'sent')\"><div class='mail_list'><div class='left'><i class='$icon'></i></div><div class='right'>" . $ToUser . "<small><div id='SendDate" . $MessageID . "'>" . $Senddate . "</div></small>
                                                        <p><div id='message" . $MessageID . "'>" . $MessageShort . ".</div></p></div></div></span</a>";
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
        </div>
    </div>
</div>
<!-- /CONTENT MAIL -->

<!-- /page content -->

<?php include("./footer.php") ?>