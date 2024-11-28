<?php
include("./header.php");
?>
<!-- page content -->
<div class="right_col" role="main">
    <div class="">
        <div class="page-title">
            <div class="title_left"></div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="clearfix"></div>
            <div class="x_content">

                <div class="clearfix"></div>
                <script src="../vendors/jquery/dist/jquery-3.3.1.min.js"></script>
                <script>
                    $(document).ready(function() {
                        var table = $('#MyMessages').DataTable({
                            paging: true,
                            dom: 'Bfrtip',
                            pageLength: 10,
                            language: {
                                searchPlaceholder: "Search",
                                search: "",
                            },
                            buttons: ['excel']
                        });
                    });
                </script>
                <script>
                    function seeMessage(messageid) {

                        $.post('./getdata.php', {
                            message: messageid
                        });

                        var from = document.getElementById("from" + messageid).innerHTML;
                        document.getElementById("FromView").value = (from);

                        var message = document.getElementById("message" + messageid).innerHTML;
                        document.getElementById("MessageView").value = (message);

                        var senddate = document.getElementById("SendDate" + messageid).innerHTML;
                        document.getElementById("SendDateView").value = (senddate);

                        document.getElementById("MessageID").value = (messageid);

                    }

                    function replyMessage(messageid) {

                        var from = document.getElementById("from" + messageid).innerHTML;
                        document.getElementById("FromView").value = (from);

                        var message = document.getElementById("message" + messageid).innerHTML;
                        document.getElementById("MessageView").value = (message);

                        var senddate = document.getElementById("SendDate" + messageid).innerHTML;
                        document.getElementById("SendDateView").value = (senddate);

                        document.getElementById("MessageID").value = (messageid);

                    }
                </script>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <table id="MyMessages" class="table table-striped dt-responsive table-bordered table-hover" cellspacing="0">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <?php

                        $sql = "SELECT messages.ID AS MessageID, CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName, Message, SendDate, ReadDate 
                                FROM messages
                                INNER JOIN users ON messages.FromUserID = users.ID
                                WHERE messages.ToUserID='" . ($_SESSION["id"]) . "';";

                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                        ?>
                        <tbody>
                            <?php while ($row = mysqli_fetch_array($result)) {
                                $MessageID = $row['MessageID']
                            ?>
                                <tr>
                                    <?php echo "<div id='from" . $MessageID . "' style='display: none;'>" . $row['FullName'] . "</div>"; ?>
                                    <td><?php echo "<a id='" . $MessageID . "' href='javascript:void(0);' onclick='seeMessage(this.id)'><b>" . $row['FullName'] . "</b></a>"; ?> </td>
                                    <td><?php echo "<div id='message" . $MessageID . "'>" . $row['Message'] . "</div>"; ?></td>
                                    <td><?php echo "<div id='SendDate" . $MessageID . "'>" . $row['SendDate'] . "</div>"; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="clearfix"></div>
            <div class="x_content">
                <div class="form-group">
                    <div class="autocomplete=off col-md-3 col-sm-3 col-xs-12">
                        <input type="hidden" class="form-control" id="MessageID" name="MessageID" value="">
                    </div>
                    <div class="clearfix"></div>
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">From</label>
                    <div class="autocomplete=off col-md-12 col-sm-12 col-xs-12">
                        <input type="text" class="form-control" id="FromView" name="FromView" value="" readonly>
                    </div>
                    <div class="clearfix"></div>
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Date</label>
                    <div class="autocomplete=off col-md-12 col-sm-12 col-xs-12">
                        <input type="text" class="form-control" id="SendDateView" name="SendDateView" value="" readonly>
                    </div>
                    <div class="clearfix"></div>
                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Message</label>
                    <div class="autocomplete=off col-md-12 col-sm-12 col-xs-12">
                        <textarea id="MessageView" name="MessageView" rows="10" cols="30" class="resizable_textarea form-control" readonly></textarea>
                    </div>
                    <div class="clearfix"></div><br>
                    <button type='submit' id='Reply' name='Reply' class='btn btn-sm btn-success float-end' value='' onclick='replyMessage()'>Reply</button>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
<div class="clearfix"></div>
<!-- /page content -->

<?php include("./footer.php") ?>