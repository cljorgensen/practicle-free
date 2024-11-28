<!-- Start settings list -->
<?php
if (in_array("100001", $group_array)) {
} else {
    $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
    notgranted($CurrentPage);
}
?>
<script>
    $(document).ready(function() {
        <?php initiateMediumViewTable("demoBookings"); ?>
    });
</script>

<!-- the table view -->
<table id="demoBookings" class="table dt-responsive table-hover" cellspacing="0">
    <thead>
        <tr>
            <th><?php echo _("Instance") ?></th>
            <th></th>
            <th><?php echo _("Name") ?></th>
            <th><?php echo _("Email") ?></th>
            <th><?php echo _("Start") ?></th>
            <th><?php echo _("End") ?></th>
            <th><?php echo _("Status") ?></th>
            <th><?php echo _("Sidst renset") ?></th>
        </tr>
    </thead>
    <?php
    $sql = "SELECT ID, InstanceName, InstancePath, InstanceURL, CustomerName, CustomerEmail, DateStart, DateEnd, Status, LastCleared
			FROM bpage.demo_bookinger_instances";

    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    ?>
    <tbody>
        <?php while ($row = mysqli_fetch_array($result)) { ?>
            <?php
            $InstanceURL = $row["InstanceURL"];
            $BookingID = $row["ID"];
            $Status = $row["Status"];
            $StatusName = "";
            $LastCleared = convertToDanishDateTimeFormat($row["LastCleared"]);

            if ($Status == "0") {
                $StatusName = "Ledig";
            }
            if ($Status == "1") {
                $StatusName = "Reserveret";
            }
            if ($Status == "3") {
                $StatusName = "Reinstalleres";
            }

            $DateStart = $row["DateStart"];

            if (!empty($DateStart)) {
                $DateStart = convertToDanishDateTimeFormat($DateStart);
            } else {
                $DateStart = "";
            }

            $DateEnd = $row["DateEnd"];

            if (!empty($DateEnd)) {
                $DateEnd = convertToDanishDateTimeFormat($DateEnd);
            } else {
                $DateEnd = "";
            }
            ?>
            <tr class='text-sm text-secondary mb-0'>
                <td><a href="<?php echo $InstanceURL ?>" target="_new"><?php echo $row["InstanceName"]; ?></a></td>
                <td><?php
                    if ($Status == 1) {
                        echo "<span style='cursor:pointer' class='badge badge-pill bg-gradient-success' onclick='openEditDemoBookingModal($BookingID)'><i class='fa fa-pencil-alt'></i></span>";
                    } else {
                        echo "<span style='cursor:pointer' class='badge badge-pill bg-gradient-success' onclick='openAddDemoBookingModal($BookingID)'><i class='fa fa-plus'></i></span>";
                    }
                    ?>
                </td>
                <td><?php echo $row["CustomerName"]; ?></td>
                <td><?php echo $row["CustomerEmail"]; ?></td>
                <td><?php echo $DateStart; ?></td>
                <td><?php echo $DateEnd; ?></td>
                <td><?php echo $StatusName; ?></td>
                <td><?php echo $LastCleared; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<!-- Script for modal popup change settings -->
<script>
    function openEditDemoBookingModal(BookingID) {

        var url = './getdata.php?popModalDemoBooking=' + BookingID;

        $("#editBookingModal").modal('show')

        jQuery('#modalEditEnd').datetimepicker({
            format: 'd-m-Y H:i',
            prevButton: false,
            nextButton: false,
            step: 60,
            dayOfWeekStart: 1
        });

        $.ajax({
            url: url,
            data: {
                Data: BookingID
            },
            type: 'GET',
            success: function(data) {
                var obj = JSON.parse(data);
                for (var i = 0; i < obj.length; i++) {

                    document.getElementById('modalEditDemoBookingID').value = obj[i].BookingID;
                    document.getElementById('modalEditInstance').value = obj[i].InstanceName;
                    document.getElementById('modalEditCustomerName').value = obj[i].CustomerName;
                    document.getElementById('modalEditCustomerEmail').value = obj[i].CustomerEmail;

                    if (obj[i].Description) {
                        document.getElementById('modalEditDescription').value = obj[i].Description;
                    } else {
                        document.getElementById('modalEditDescription').value = "";
                    }
                    document.getElementById('modalEditStart').value = obj[i].DateStart;
                    document.getElementById('modalEditEnd').value = obj[i].DateEnd;
                }

            }
        });
    }

    function openAddDemoBookingModal(BookingID) {

        var url = './getdata.php?popModalDemoBooking=' + BookingID;

        $("#addBookingModal").modal('show')

        jQuery('#modalAddStart').datetimepicker({
            format: 'd-m-Y H:i',
            prevButton: false,
            nextButton: false,
            step: 60,
            dayOfWeekStart: 1
        });

        jQuery('#modalAddEnd').datetimepicker({
            format: 'd-m-Y H:i',
            prevButton: false,
            nextButton: false,
            step: 60,
            dayOfWeekStart: 1
        });

        $.ajax({
            url: url,
            data: {
                Data: BookingID
            },
            type: 'GET',
            success: function(data) {
                var obj = JSON.parse(data);
                for (var i = 0; i < obj.length; i++) {

                    document.getElementById('modalAddDemoBookingID').value = obj[i].BookingID;
                    document.getElementById('modalAddInstance').value = obj[i].InstanceName;
                    document.getElementById('modalAddCustomerName').value = obj[i].CustomerName;
                    document.getElementById('modalAddCustomerEmail').value = obj[i].CustomerEmail;

                    if (obj[i].Description) {
                        document.getElementById('modalAddDescription').value = obj[i].Description;
                    } else {
                        document.getElementById('modalAddDescription').value = "";
                    }
                    document.getElementById('modalAddStart').value = obj[i].DateStart;
                    document.getElementById('modalAddEnd').value = obj[i].DateEnd;
                }
            }
        });
    }

    function submitDeleteDemoBooking() {
        var demoBookingID = document.getElementById("modalEditDemoBookingID").value;

        $.ajax({
            type: 'GET',
            url: './getdata.php?submitDeleteDemoBooking=' + demoBookingID,
            data: {
                Data: demoBookingID
            },
            success: function(data) {
                $("#editBookingModal").modal('hide');
                window.location.reload();
            }
        });
    }

    function submitCreateDemoBooking() {

        var DemoBookingID = document.getElementById("modalAddDemoBookingID").value;
        var CustomerName = document.getElementById("modalAddCustomerName").value;
        var CustomerEmail = document.getElementById("modalAddCustomerEmail").value;
        var Description = document.getElementById("modalAddDescription").value;
        var Start = document.getElementById("modalAddStart").value;
        var End = document.getElementById("modalAddEnd").value;
        var Url = './getdata.php?submitCreateDemoBooking';

        $.ajax({
            type: 'GET',
            url: Url,
            data: {
                DemoBookingID: DemoBookingID,
                CustomerName: CustomerName,
                CustomerEmail: CustomerEmail,
                Description: Description,
                Start: Start,
                End: End
            },
            success: function(data) {
                $("#addBookingModal").modal('hide');
                window.location.reload();
            }
        });
    }

    function submitUpdateDemoBooking() {

        var DemoBookingID = document.getElementById("modalEditDemoBookingID").value;
        var CustomerName = document.getElementById("modalEditCustomerName").value;
        var CustomerEmail = document.getElementById("modalEditCustomerEmail").value;
        var Description = document.getElementById("modalEditDescription").value;
        var Start = document.getElementById("modalEditStart").value;
        var End = document.getElementById("modalEditEnd").value;
        var Url = './getdata.php?submitUpdateDemoBooking';

        $.ajax({
            type: 'GET',
            url: Url,
            data: {
                DemoBookingID: DemoBookingID,
                CustomerName: CustomerName,
                CustomerEmail: CustomerEmail,
                Description: Description,
                Start: Start,
                End: End
            },
            success: function(data) {
                $("#editBookingModal").modal('hide');
                window.location.reload();
            }
        });
    }
</script>
<!-- Modal edit demoBooking -->
<div class="modal fade" id="editBookingModal" tabindex="-1" role="dialog" aria-labelledby="editBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title font-weight-normal" id="editBookingModalLabel"><?php echo _('Edit Booking'); ?></h6>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="statusMsg"></p>

                <div class="col-lg-8 col-md-4 col-sm-4 col-xs-4">
                    <div class="input-group input-group-static mb-4">
                        <input type="text" class="form-control" id="modalEditDemoBookingID" readonly>
                    </div>
                </div>


                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalInstance"><?php echo _("Instance") ?></label>
                        <input type="text" class="form-control" id="modalEditInstance" readonly>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalCustomerName"><?php echo _("Customer Name") ?></label>
                        <input type="text" class="form-control" id="modalEditCustomerName">
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalCustomerEmail"><?php echo _("Customer Email") ?></label>
                        <input type="text" class="form-control" id="modalEditCustomerEmail">
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalEditDescription"><?php echo _("Description") ?></label>
                        <textarea type="text" class="form-control" id="modalEditDescription" rows="10"></textarea>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalStart"><?php echo _("Start") ?></label>
                        <input type="text" class="form-control" id="modalEditStart" readonly>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalEnd"><?php echo _("End") ?></label>
                        <input type="text" class="form-control" id="modalEditEnd" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-success float-end" onclick="submitUpdateDemoBooking()"><?php echo _("Save") ?></button>
                <button type="button" class="btn btn-sm btn-danger float-end" onclick="submitDeleteDemoBooking()"><?php echo _("Delete") ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Modal edit demoBooking -->
<!-- Modal edit demoBooking -->
<div class="modal fade" id="addBookingModal" tabindex="-1" role="dialog" aria-labelledby="addBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title font-weight-normal" id="addBookingModalLabel"><?php echo _('Add Booking'); ?></h6>
                <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="statusMsg"></p>
                <div class="input-group input-group-static mb-4">
                    <div class="col-lg-8 col-md-4 col-sm-4 col-xs-4">
                        <input type="text" class="form-control" id="modalAddDemoBookingID" readonly>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalInstance"><?php echo _("Instance") ?></label>
                        <input type="text" class="form-control" id="modalAddInstance" readonly>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalCustomerName"><?php echo _("Customer Name") ?></label>
                        <input type="text" class="form-control" id="modalAddCustomerName">
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalCustomerEmail"><?php echo _("Customer Email") ?></label>
                        <input type="text" class="form-control" id="modalAddCustomerEmail">
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalAddDescription"><?php echo _("Description") ?></label>
                        <textarea type="text" class="form-control" id="modalAddDescription" rows="10"></textarea>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalStart"><?php echo _("Start") ?></label>
                        <input type="text" class="form-control" id="modalAddStart" autocomplete="off">
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                        <label for="modalEnd"><?php echo _("End") ?></label>
                        <input type="text" class="form-control" id="modalAddEnd" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-danger float-end" onclick="submitCreateDemoBooking()"><?php echo _("Create") ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Modal edit demoBooking -->