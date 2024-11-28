<?php
$UserID = $_SESSION['id'];
?>

<script src="./assets/js/plugins/moment-with-locales.min.js"></script>
<script src="./assets/js/plugins/jquery.datetimepicker.full.js"></script>

<script>
  function addLanguageEntry() {
    var MainLanguage = document.getElementById("modalMainLanguageAdd").value;
    var daDK = document.getElementById("modaldaDKAdd").value;
    var deDE = document.getElementById("modaldeDEAdd").value;
    var esES = document.getElementById("modalesESAdd").value;
    var frFR = document.getElementById("modalfrFRAdd").value;
    var fiFI = document.getElementById("modalfiFIAdd").value;

    $.ajax({
      type: 'POST',
      url: './getdata.php',
      data: 'addLanguageEntry=1&MainLanguage=' + MainLanguage + '&daDK=' + daDK + '&deDE=' + deDE + '&esES=' + esES + '&frFR=' + frFR + '&fiFI=' + fiFI,
      beforeSend: function() {
        $('.submitBtn').attr("disabled", "disabled");
        $('.modal-body').css('opacity', '.5');
      },
      success: function(msg) {
        $("#editLanguagesEditModal").modal('hide');
        $('.submitBtn').removeAttr("disabled");
        $('.modal-body').css('opacity', '');
      }
    });
    location.href = "administration_languages.php";
  };

  function addTimeRegistration() {

    relatedtask = document.getElementById("ModalRelatedTask").value;
    description = document.getElementById("ModalDescription").value;
    timespend = document.getElementById("ModalTimeSpend").value;
    billable = document.getElementById("ModalBillable").value;
    dateperformed = document.getElementById("ModalDatePerformed").value;

    $.ajax({
      type: 'GET',
      url: './getdata.php',
      data: 'addtimeregistration=1&relatedtask=' + relatedtask + '&description=' + description + '&timespend=' + timespend + '&dateperformed=' + dateperformed + '&billable=' + billable,

      beforeSend: function() {
        $('.submitBtn').attr("disabled", "disabled");
        $('.modal-body').css('opacity', '.5');
      },
      success: function(msg) {
        $("#modalTimeRegistration").modal('hide');
        $('.submitBtn').removeAttr("disabled");
        $('.modal-body').css('opacity', '');
      }
    });
  }

  function changeTimeRegistration() {

    var registrationid = document.getElementById("ModalRegID").value;
    var relatedtask = document.getElementById("ModalRelatedTask").value;
    var description = $('#ModalDescription').trumbowyg('html');
    var timespend = document.getElementById("ModalTimeSpend").value;
    var billable = document.getElementById("ModalBillable").value;
    var dateperformed = document.getElementById("ModalDatePerformed").value;

    vData = {
      registrationid: registrationid,
      relatedtask: relatedtask,
      description: description,
      timespend: timespend,
      dateperformed: dateperformed,
      billable: billable
    }

    $.ajax({
      type: 'POST',
      url: './getdata.php?changetimeregistration',
      data: vData,

      beforeSend: function() {
        $('.timeregbtnUpdate').attr("disabled", "disabled");
        $('.timeregbtnCreate').attr("disabled", "disabled");
        $('.modal-body').css('opacity', '.5');
      },
      success: function(msg) {
        $("#modalTimeRegistration").modal('hide');
        $('.timeregbtnUpdate').removeAttr("disabled");
        $('.timeregbtnCreate').removeAttr("disabled");
        $('.modal-body').css('opacity', '');
      }
    });
  }

  function changeWorkFlowStep() {

    modalstepid = document.getElementById("ModalStepID").value;
    modalstepname = document.getElementById("ModalStepName").value;
    modaldescription = $("#ModalDescription").trumbowyg("html");
    modalstatus = document.getElementById("ModalStatus").value;
    modalresponsible = document.getElementById("ModalResponsible").value;
    modaldeadline = document.getElementById("ModalDeadline").value;

    $.ajax({
      type: 'GET',
      url: './getdata.php',
      data: 'changeWorkFlowStep=1&modalstepid=' + modalstepid + '&modalstepname=' + modalstepname + '&modaldescription=' + modaldescription + '&modalstatus=' + modalstatus + '&modalresponsible=' + modalresponsible + '&modaldeadline=' + modaldeadline,

      beforeSend: function() {
        $('.timeregbtnUpdate').attr("disabled", "disabled");
        $('.timeregbtnCreate').attr("disabled", "disabled");
        $('.modal-body').css('opacity', '.5');
      },
      success: function(msg) {
        $("#ModalWorkFlowStep").modal('hide');
        $('.timeregbtnUpdate').removeAttr("disabled");
        $('.timeregbtnCreate').removeAttr("disabled");
        $('.modal-body').css('opacity', '');
      }
    });
    setTimeout(function() {
      window.location.reload(true);
    }, 200);
  }

  function addWorkFlowStep() {

    modalstepid = document.getElementById("ModalStepID").value;
    modalstepname = document.getElementById("ModalStepName").value;
    modaldescription = document.getElementById("ModalDescription").value;
    modalstatus = document.getElementById("ModalStatus").value;
    modalresponsible = document.getElementById("ModalResponsible").value;
    modaldeadline = document.getElementById("ModalDeadline").value;
    elementid = document.getElementById("ModalElementID").value;
    elementtype = document.getElementById("ModalElementType").value;

    $.ajax({
      type: 'GET',
      url: './getdata.php',
      data: 'addWorkFlowStep=1&modalstepid=' + modalstepid + '&modalstepname=' + modalstepname + '&modaldescription=' + modaldescription + '&modalstatus=' + modalstatus + '&modalresponsible=' + modalresponsible + '&modaldeadline=' + modaldeadline + '&elementid=' + elementid + '&elementtype=' + elementtype,

      beforeSend: function() {
        $('.timeregbtnUpdate').attr("disabled", "disabled");
        $('.timeregbtnCreate').attr("disabled", "disabled");
        $('.modal-body').css('opacity', '.5');
      },
      success: function(msg) {
        $("#ModalWorkFlowStep").modal('hide');
        $('.timeregbtnUpdate').removeAttr("disabled");
        $('.timeregbtnCreate').removeAttr("disabled");
        $('.modal-body').css('opacity', '');
      }
    });
    setTimeout(function() {
      window.location.reload(true);
    }, 200);
  }

  function deleteWorkFlowStep() {

    modalstepid = document.getElementById("ModalStepID").value;

    $.ajax({
      type: 'GET',
      url: './getdata.php',
      data: 'deleteWorkFlowStep=1&modalstepid=' + modalstepid,

      beforeSend: function() {
        $('.modal-body').css('opacity', '.5');
      },
      success: function(msg) {
        $("#ModalWorkFlowStep").modal('hide');
        $('.modal-body').css('opacity', '');
      }
    });
    setTimeout(function() {
      window.location.reload(true);
    }, 200);
  }

  function updateTask() {

    var taskid = document.getElementById("ModalEditTaskID").value;
    var deadline = document.getElementById("ModalEditTaskDeadline").value;
    var tablename = document.getElementById("ModalEditTableName").value;

    $.ajax({
      type: 'GET',
      url: './getdata.php',
      data: 'updatetaskfrommodal=1&taskid=' + taskid + '&deadline=' + deadline,

      beforeSend: function() {
        $('.submitBtn').attr("disabled", "disabled");
        $('.modal-body').css('opacity', '.5');
      },
      success: function(msg) {
        $("#modalEditKanBanTaskModal").modal('hide');
        $("#" + tablename).DataTable().ajax.reload();
        pnotify('Deadline for task updated', 'success');
      }
    });
  }

  function sendMessage(from) {
    var message = document.getElementById("ModalMessageText").value;
    var to = document.getElementById("ModalRecieverField").value;
    $.ajax({
      type: 'POST',
      url: './getdata.php',
      data: 'sendMessageFromModal=1&to=' + to + '&message=' + message + '&from=' + from,

      beforeSend: function() {
        $('.submitBtn').attr("disabled", "disabled");
        $('.modal-body').css('opacity', '.5');
      },
      success: function(msg) {
        $("#modalNewMessage").modal('hide');
        $('.submitBtn').removeAttr("disabled");
        $('.modal-body').css('opacity', '');
      }
    });
  }
</script>


<script>
  $(function() {

    jQuery('#ModalDeadline').datetimepicker({
      format: 'd-m-Y H:i',
      prevButton: false,
      nextButton: false,
      step: 60,
      dayOfWeekStart: 1
    });
    //$.datetimepicker.setLocale('<?php echo $languageshort ?>');
  });
</script>