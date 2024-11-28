<?php
$UserID = $_SESSION['id'];
?>
<script>
  function answerMessage() {
    answerto = document.getElementById('modalSenderID').value
    sender = answerto = document.getElementById('modalSender').value
    date = answerto = document.getElementById('modalDateSent').value
    document.getElementById('AnswerButton').hidden = true
    document.getElementById('sendAnswerButton').hidden = false
    document.getElementById('modalDateSent').hidden = true;
    document.getElementById('modalDateLabel').hidden = true;
  }

  function sendAnswer() {
    let AnswerTo = document.getElementById('modalSenderID').value

    var editorModalViewMessageText = CKEDITOR.instances.ModalViewMessageText;

    // Check if editor instance exists
    if (editorModalViewMessageText) {
      // Get the HTML content from the editor
      var MessageText = editorModalViewMessageText.getData();
      console.log(MessageText); // Output the content to console or do whatever you want with it
    } else {
      console.error("CKEditor instance not found.");
    }

    let url = './getdata.php?sendMessageAnswer';

    $.ajax({
      url: url,
      data: {
        AnswerTo: AnswerTo,
        MessageText: MessageText
      },
      type: 'POST',
      success: function(data) {
        $("#modalViewModalMessage").modal('hide');
        pnotify('Besked sendt', 'success');
      },
    });
  }

  function deleteMessage() {
    if (confirm('Er du sikker?')) {
      let url = './getdata.php?deleteMessage';
      modalMessageID = document.getElementById('modalMessageID').value
      $.ajax({
        url: url,
        data: {
          modalMessageID: modalMessageID
        },
        type: 'GET',
        success: function(data) {
          $("#modalViewModalMessage").modal('hide');
          pnotify('Besked slettet', 'success');
          delayAndRefreshNow();
          window.location.reload();
        },
      });
    } else {}
  }
</script>
<!-- Modal View Message -->
<div class="modal fade" id="modalViewModalMessage" data-bs-focus="false" role="dialog" aria-labelledby="viewModalMessageLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title font-weight-normal" id="viewModalMessageLabel"><?php echo _('Message'); ?></h6>
        <button class="btn btn-link text-dark p-0 fixed-plugin-close-button" data-bs-dismiss="modal" aria-label="Close">
          <i class="material-icons">clear</i>
        </button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-body">
            <input type="text" class="form-control" id="modalMessageID" name="modalMessageID" hidden>
            <input type="text" class="form-control" id="modalSenderID" name="modalSenderID" hidden>
            <div class="row">
              <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                <div class="input-group input-group-static mb-4">
                  <label for="modalSender" class="control-label" id="labelsender"><?php echo _("Sender"); ?></label>
                  <input type="text" class="form-control" id="modalSender" name="modalSender" disabled>
                </div>
              </div>
              <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                <div class="input-group input-group-static mb-4">
                  <label for="modalDateSent" class="control-label" id="modalDateLabel"><?php echo _("Date"); ?></label>
                  <input type="text" class="form-control" id="modalDateSent" name="modalDateSent" disabled>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="ModalViewMessageText" class="control-label"><?php echo _("Message"); ?></label>
                  <textarea class="form-control" id="ModalViewMessageText" name="ModalViewMessageText" rows="10"></textarea>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="input-group input-group-static mb-4">
                  <label for="ModalMessageText"><?php echo _('Message'); ?></label>
                  <textarea class="form-control" id="ModalMessageText" name="ModalMessageText" rows="10"></textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class='btn btn-sm btn-danger float-end' onclick="deleteMessage()" id="DeleteButton"><span class=''></span> <?php echo _("Delete"); ?></button>
            <button class='btn btn-sm btn-success float-end' onclick="answerMessage()" id="AnswerButton"><span class=''></span> <?php echo _("Answer"); ?></button>
            <button class='btn btn-sm btn-success float-end' onclick="sendAnswer()" id="sendAnswerButton" hidden><span class=''></span> <?php echo _("Send"); ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End Modal View Message -->