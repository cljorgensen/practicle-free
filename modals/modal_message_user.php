<?php
$UserID = $_SESSION['id'];
?>
<script>
  function SendMessage(from) {
    var message = $('#ModalMessageText').trumbowyg('html');
    var to = document.getElementById("ModalRecieverField").value;
    var url = './getdata.php?sendMessageFromModal';
    vData = {
      message: message,
      to: to,
      from: from
    };

    $.ajax({
      url: url,
      type: "POST",
      data: vData,
      success: function(response) {
        $("#modalNewMessage").modal('hide');
        pnotify('Besked sendt', 'success')
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  }
</script>
<!-- Modal Write Message To User -->
<div class="modal fade" id="modalNewMessage" data-bs-focus="false" role="dialog" aria-labelledby="NewMessageLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="NewMessageLabel"><?php echo _('New message'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-body">
            <div class='form-group'>
              <div class="input-group input-group-static mb-4">
                <label for="ModalRecieverField"><?php echo _("Recipient"); ?></label>
                <select class="form-control" id="ModalRecieverField" name="ModalRecieverField">
                  <?php
                  $sql = "SELECT users.ID, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName
                      FROM users
                      ORDER BY users.Firstname ASC";
                  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                  while ($row = mysqli_fetch_array($result)) {
                    $ToUserID = $row['ID'];
                    $FullName = $row['FullName'];
                    echo "<option value='$ToUserID'>$FullName</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="input-group input-group-static mb-4">
              <label for="ModalMessageText"><?php echo _('Message'); ?></label>
              <textarea class="form-control" id="ModalMessageText" name="ModalMessageText" rows="10"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button class='btn btn-sm btn-success float-end' onclick="SendMessage('<?php echo $UserID; ?>')"><span class=''></span> <?php echo _("Send"); ?></button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal Write Message To User-->

</script>