<?php
$UserID = $_SESSION['id'];
?>
<script>
  function SendMessageToGroup() {
    var Message = $('#ModalMessageGroupText').trumbowyg('html');
    var Group = document.getElementById("ModalRecieverGroup").value;
    var url = './getdata.php?sendGroupMessageFromModal';
    var From = '<?php echo $UserID; ?>';

    $.ajax({
      url: url,
      type: "POST",
      data: {
        Message: Message,
        Group: Group,
        From: From
      },
      success: function(response) {
        $("#modalNewMessageGroup").modal('hide');
        pnotify('Besked sendt til gruppe', 'success')
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus, errorThrown);
      }
    });
  }

  $(document).on("click", "input[name='email']", function() {
    thisRadio = $(this);
    if (thisRadio.hasClass("imChecked")) {
      thisRadio.removeClass("imChecked");
      thisRadio.prop('checked', false);
    } else {
      thisRadio.prop('checked', true);
      thisRadio.addClass("imChecked");
    };
  })
</script>
<!-- Modal Write Message To User -->
<div class="modal fade" id="modalNewMessageGroup" data-bs-focus="false" role="dialog" aria-labelledby="NewMessageGroupLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="NewMessageGroupLabel"><?php echo _('Message to group'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-md-8 col-sm-8 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="ModalRecieverGroup"><?php echo _("Recipient"); ?></label>
                  <select class="form-control" id="ModalRecieverGroup" name="ModalRecieverGroup">
                    <?php
                    $sql = "SELECT ID, GroupName
                        FROM usergroups
                        ORDER BY GroupName ASC";
                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    while ($row = mysqli_fetch_array($result)) {
                      $GroupID = $row['ID'];
                      $GroupName = $row['GroupName'];
                      echo "<option value='$GroupID'>$GroupName</option>";
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="input-group input-group-static mb-4" title="<?php echo $functions->translate("Notify by email") . "?"; ?>">
                  <label for="email"><?php echo $functions->translate("Notify") . "?"; ?></label>
                  <input type="radio" id="email" name="email" value="">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="ModalMessageGroupText"><?php echo $functions->translate('Message'); ?></label>
                  <textarea class="form-control" id="ModalMessageGroupText" name="ModalMessageGroupText" rows="20"></textarea>
                </div>
              </div>
            </div>
            <button class='btn btn-sm btn-success float-end' onclick="SendMessageToGroup()"><span class=''></span> <?php echo _("Send"); ?></button>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal Write Message To User-->