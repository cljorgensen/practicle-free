<?php
include("./header.php");
$TaskBoardUserID = $_GET["userid"];
?>
<script>
  $(document).ready(function() {
    ajaxurl = "getdata.php?getKanBanTasks=1&userid=<?php echo $TaskBoardUserID; ?>&taskstatus=1&tablename=TableTabToDo";
    var TableTabTodo = $('#TableTabToDo').DataTable({
      "dom": 'Bfrtip',
      "searching": false,
      "bFilter": true,
      "paging": true,
      "info": false,
      "pagingType": 'numbers',
      "processing": true,
      "deferRender": true,
      "pageLength": 10,
      "orderCellsTop": true,
      "fixedHeader": false,
      "autoWidth": false,
      "aaSorting": [],
      "responsive": true,
      "language": {
        "info": '_START_ <?php echo _("to"); ?> _END_ <?php echo _("of"); ?> _TOTAL_ <?php echo _("Total"); ?>',
        "searchPlaceholder": '<?php echo _("Search"); ?>',
        "search": '',
      },
      "buttons": ['copy', 'excel'],
      "bStateSave": false,
      "displayLength": 10,
      "ajax": {
        "url": ajaxurl,
        "type": "POST",
        "dataSrc": ""
      },
      "columnDefs": [{
        "targets": 0,
        "data": "TaskID",
        "width": "0%",
        "visible": false
      }, {
        "targets": 1,
        "data": "Deadline",
        "width": "15%"
      }, {
        "targets": 2,
        "data": "LinkToElement",
        "width": "15%"
      }, {
        "targets": 3,
        "data": "TaskContent",
        "width": "63%"
      }]
    });
    $('#TableTabToDo thead th').each(function() {
      var title = $(this).text();
      $(this).html('<input type=\"search\" class=\"form-control form-control-sm\" placeholder=\"' + title + '\"/>');
    });
    TableTabTodo.columns().every(function() {
      var that = this;
      $('input', this.header()).on('keyup change', function() {
        if (that.search() !== this.value) {
          that.search(this.value).draw();
        }
      });
    });
  });
</script>
<script>
  $(document).ready(function() {
    ajaxurl = "getdata.php?getKanBanTasks=1&userid=<?php echo $TaskBoardUserID; ?>&taskstatus=2&tablename=TableTabDoing";
    var TableTabDoing = $('#TableTabDoing').DataTable({
      "dom": 'Bfrtip',
      "searching": false,
      "bFilter": true,
      "paging": true,
      "info": false,
      "pagingType": 'numbers',
      "processing": true,
      "deferRender": true,
      "pageLength": 10,
      "orderCellsTop": true,
      "fixedHeader": false,
      "autoWidth": false,
      "aaSorting": [],
      "responsive": true,
      "language": {
        "info": '_START_ <?php echo _("to"); ?> _END_ <?php echo _("of"); ?> _TOTAL_ <?php echo _("Total"); ?>',
        "searchPlaceholder": '<?php echo _("Search"); ?>',
        "search": '',
      },
      "buttons": ['copy', 'excel'],
      "bStateSave": false,
      "displayLength": 10,
      "ajax": {
        "url": ajaxurl,
        "type": "POST",
        "dataSrc": ""
      },
      "columnDefs": [{
        "targets": 0,
        "data": "TaskID",
        "width": "0%",
        "visible": false
      }, {
        "targets": 1,
        "data": "Deadline",
        "width": "15%"
      }, {
        "targets": 2,
        "data": "LinkToElement",
        "width": "15%"
      }, {
        "targets": 3,
        "data": "TaskContent",
        "width": "63%"
      }]
    });
    $('#TableTabDoing thead th').each(function() {
      var title = $(this).text();
      $(this).html('<input type=\"search\" class=\"form-control form-control-sm\" placeholder=\"' + title + '\"/>');
    });

    TableTabDoing.columns().every(function() {
      var that = this;

      $('input', this.header()).on('keyup change', function() {
        if (that.search() !== this.value) {
          that
            .search(this.value)
            .draw();
        }
      });
    });
  });
</script>
<script>
  $(document).ready(function() {
    ajaxurl = "getdata.php?getKanBanTasks=1&userid=<?php echo $TaskBoardUserID; ?>&taskstatus=3&tablename=TableTabDone";
    var TableTabDone = $('#TableTabDone').DataTable({
      "dom": 'Bfrtip',
      "searching": false,
      "bFilter": true,
      "paging": true,
      "info": false,
      "pagingType": 'numbers',
      "processing": true,
      "deferRender": true,
      "pageLength": 10,
      "orderCellsTop": true,
      "fixedHeader": false,
      "autoWidth": false,
      "aaSorting": [],
      "responsive": true,
      "language": {
        "info": '_START_ <?php echo _("to"); ?> _END_ <?php echo _("of"); ?> _TOTAL_ <?php echo _("Total"); ?>',
        "searchPlaceholder": '<?php echo _("Search"); ?>',
        "search": '',
      },
      "buttons": ['copy', 'excel'],
      "bStateSave": false,
      "displayLength": 10,
      "ajax": {
        "url": ajaxurl,
        "type": "POST",
        "dataSrc": ""
      },
      "columnDefs": [{
        "targets": 0,
        "data": "TaskID",
        "width": "0%",
        "visible": false
      }, {
        "targets": 1,
        "data": "Deadline",
        "width": "15%"
      }, {
        "targets": 2,
        "data": "LinkToElement",
        "width": "15%"
      }, {
        "targets": 3,
        "data": "TaskContent",
        "width": "63%"
      }]
    });
    $('#TableTabDone thead th').each(function() {
      var title = $(this).text();
      $(this).html('<input type=\"search\" class=\"form-control form-control-sm\" placeholder=\"' + title + '\"/>');
    });

    TableTabDone.columns().every(function() {
      var that = this;

      $('input', this.header()).on('keyup change', function() {
        if (that.search() !== this.value) {
          that
            .search(this.value)
            .draw();
        }
      });
    });
  });
</script>
<?php
$DocApprovalNumbers = getNumberOfDocApprovalActions($TaskBoardUserID);
$DocExpiresNumbers = $functions->getNumberOfDocExpireActions($TaskBoardUserID);
$ChangeTasksNumbers = getNumberOfChangesActions($TaskBoardUserID);
$CITasksNumbers = getNumberOfCIActions($TaskBoardUserID);

$SumAntalTasks = $DocApprovalNumbers + $DocExpiresNumbers + $ChangeTasksNumbers + $CITasksNumbers;
?>
<div class="col-md-12 col-sm-12 col-xs-12">
  <div class="card-group">
    <div class="card">
      <div class="card-header">
        <h6><a href="index.php" title="<?php echo _("This task board is a kanban-based task board. You can add events, project tasks, etc. to your task board which can help you manage, manage and prioritize your daily tasks.") ?>"><?php echo _("Taskboard") ?></a></h6>
        <div class="nav-wrapper position-relative end-0" id="activetablist">
          <ul class="nav nav-tabs nav-fill mb-3" id="home-tab" role="tablist">
            <li class="nav-item" role="presentation">
              <a class="nav-link mb-0 px-0 py-1" id="navlinkTabToDo" href="#TabToDo" data-bs-toggle="tab" data-bs-target="#TabToDo" type="button" role="tab" aria-controls="ToDo" aria-selected="false" onclick="setActiveTabLocalStorage('TabToDo');reloadDataTable('TableTabToDo')"><?php echo _("ToDo"); ?></a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link mb-0 px-0 py-1 active" id="navlinkTabDoing" href="#TabDoing" data-bs-toggle="tab" data-bs-target="#TabDoing" type="button" role="tab" aria-controls="TabDoing" aria-selected="true" onclick="setActiveTabLocalStorage('TabDoing');reloadDataTable('TableTabDoing')"><?php echo _("Doing"); ?></a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link mb-0 px-0 py-1" id="navlinkTabDone" href="#TabDone" data-bs-toggle="tab" data-bs-target="#TabDone" type="button" role="tab" aria-controls="TabDoing" aria-selected="false" onclick="setActiveTabLocalStorage('TabDone');reloadDataTable('TableTabDone')"><?php echo _("Done"); ?></a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link mb-0 px-0 py-1" id="navlinkTabApprovals" href="#TabApprovals" data-bs-toggle="tab" data-bs-target="#TabApprovals" type="button" role="tab" aria-controls="TabApprovals" aria-selected="false" onclick="setActiveTabLocalStorage('TabApprovals');"><?php echo _("Approvals"); ?>
                <?php
                if ($SumAntalTasks == 0) {
                  echo "";
                } else {
                  echo "<span class=\"badge rounded-pill bg-danger\">$SumAntalTasks</span>";
                  //echo "<span class='badge badge-sm badge-circle border border-white border-1'>$SumAntalTasks</span>";
                }
                ?>
              </a>
            </li>
          </ul>
        </div>
      </div>
      <div class="card-body">
        <div class="tab-content">
          <div class="tab-pane" id="TabToDo">
            <table id="TableTabToDo" class="table table-borderless" cellspacing="0">
              <thead>
                <tr>
                  <th><?php echo _("TaskID"); ?></th>
                  <th><?php echo _("Deadline"); ?></th>
                  <th><?php echo _("Type"); ?></th>
                  <th><?php echo _("Subject"); ?></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="tab-pane" id="TabDoing">
            <table id="TableTabDoing" class="table table-borderless" cellspacing="0">
              <thead>
                <tr>
                  <th><?php echo _("TaskID"); ?></th>
                  <th><?php echo _("Deadline"); ?></th>
                  <th><?php echo _("Type"); ?></th>
                  <th><?php echo _("Subject"); ?></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="tab-pane" id="TabDone">
            <table id="TableTabDone" class="table table-borderless" cellspacing="0">
              <thead>
                <tr>
                  <th><?php echo _("TaskID"); ?></th>
                  <th><?php echo _("Deadline"); ?></th>
                  <th><?php echo _("Type"); ?></th>
                  <th><?php echo _("Subject"); ?></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="tab-pane" id="TabApprovals">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="card-collapse">
                <div class="card-header" role="tab" id="headingCITasks">
                  <p class="mb-0">
                    <a class="collapsed" data-bs-toggle="collapse" href="#collapseCITasks" aria-expanded="false" aria-controls="collapseCITasks">
                      <?php
                      $Antal = getNumberOfExpiredCIs($TaskBoardUserID);
                      ?>
                      <p><?php echo _("Assets expired"); ?>
                        <?php
                        if ($Antal == 0) {
                          echo "<span class='badge rounded-pill bg-secondary'>0</span>";
                        } else {
                          echo "<span class='badge rounded-pill bg-danger'>$Antal</span>";
                        }
                        ?>
                      </p>
                    </a>
                  </p>
                </div>
              </div>
              <div id="collapseCITasks" class="collapse" role="tabpanel" aria-labelledby="heading<?php echo $ID; ?>" data-parent="#headingCITasks">
                <div class="card-body">
                  <table id="TableCIApprovals" class="table table-borderless dt-responsive" cellspacing="0">
                    <thead>
                      <tr>
                        <th><?php echo _("ID"); ?></th>
                        <th></th>
                        <th><?php echo _("Name"); ?></th>
                        <th><?php echo _("Class"); ?></th>
                        <th><?php echo _("Expires"); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <div id="accordionTabCITasks" role="tablist">
                        <?php
                        $sql = "SELECT ID, Name, Expires, RelatedClassID
                                  FROM cis
                                  WHERE (Expires < CURDATE())";
                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                        while ($row = mysqli_fetch_array($result)) {
                          $CIID = $row['ID'];
                          $Name = $row['Name'];
                          $CIClassID = $row['RelatedClassID'];
                          $Elementid = getRelatedCIElementID($CIID, $CIClassID);
                          $Url = getCITypeUrl($CIClassID);
                          $ClassName = getCIClassName($CIClassID);
                          $Expires = convertToDanishDateFormat($row['Expires']);
                        ?>
                          <tr class='text-sm text-secondary mb-0'>
                            <td width="5%"><a href='<?php echo $Url . "_view.php?elementid=" . $Elementid; ?>' title='<?php echo _("Open") ?>'><?php echo $Elementid; ?></a></td>
                            <td width="5%"><a href='<?php echo $Url . "_view.php?elementid=" . $Elementid; ?>'><span class='badge badge-pill bg-gradient-secondary'><i class='fa fa-folder-open'></i></span></a></td>
                            <td width="50%"><?php echo $Name; ?></td>
                            <td width="20%"><?php echo _($ClassName); ?></td>
                            <td width="20%"><?php echo $Expires; ?></td>
                      </div>
                      </td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="card-collapse">
              <div class="card-header" role="tab" id="headingChangeTasks">
                <p class="mb-0">
                  <a class="collapsed" data-bs-toggle="collapse" href="#collapseChangeTasks" aria-expanded="false" aria-controls="collapseChangeTasks">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                      <p><?php echo _("Change approvals"); ?>
                        <?php
                        if ($ChangeTasksNumbers == 0) {
                          echo "<span class='badge rounded-pill bg-secondary'>0</span>";
                        } else {
                          echo "<span class='badge rounded-pill bg-danger'>$ChangeTasksNumbers</span>";
                        }
                        ?>
                      </p>
                    </div>
                  </a>
                </p>
              </div>
              <div id="collapseChangeTasks" class="collapse" role="tabpanel" aria-labelledby="heading<?php echo $ID; ?>" data-parent="#headingChangeTasks">
                <div class="card-body">
                  <table id="TableChangeApprovals" class="table table-borderless dt-responsive" cellspacing="0">
                    <thead>
                      <tr>
                        <th><?php echo _("Change ID"); ?></th>
                        <th></th>
                        <th><?php echo _("Subject"); ?></th>
                        <th><?php echo _("Status"); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <div id="accordionTabChangeTasks" role="tablist">
                        <?php

                        $sql = "SELECT Changes.ID AS ChangeID, Changes.Subject, changes_statuscodes.StatusName AS Status
                                  FROM Changes
                                  INNER JOIN cab ON Changes.ID = cab.RelatedChangeID
                                  INNER JOIN cab_users ON cab.ID = cab_users.CABID
                                  INNER JOIN changes_statuscodes ON Changes.Status = changes_statuscodes.ID
                                  WHERE cab_users.UserID = $TaskBoardUserID AND Status = 2";
                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                        while ($row = mysqli_fetch_array($result)) {
                          if ($row['ChangeID'] == "") {
                            echo "No personal tasks";
                          } else {
                            $ChangeID = $row['ChangeID'];
                            $Subject = $row['Subject'];
                            $Status = $row['Status'];
                        ?>
                            <tr>
                              <td width="10%"><a href='<?php echo "changes_view.php?elementid=$ChangeID"; ?>' title='<?php echo _("Open Document") ?>'><?php echo $ChangeID; ?></a></td>
                              <td width="10%"><?php echo "<a href='changes_view.php?elementid=" . $ChangeID . "'><span class='badge badge-pill bg-gradient-secondary'><i class='fa fa-folder-open'></i></span></a>"; ?></td>
                              <td width="60%"><?php echo $Subject; ?></td>
                              <td width="20%"><?php echo $Status; ?></td>
                      </div>
                      </td>
                      </tr>
                    <?php } ?>
                  <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="card-collapse">
              <div class="card-header" role="tab" id="headingDocumentApprovalTasks">
                <p class="mb-0">
                  <a class="collapsed" data-bs-toggle="collapse" href="#collapseDocumentApprovalTasks" aria-expanded="false" aria-controls="collapseDocumentApprovalTasks">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                      <p><?php echo _("Document approvals"); ?>
                        <?php
                        if ($DocApprovalNumbers == 0) {
                          echo "<span class='badge rounded-pill bg-secondary'>0</span>";
                        } else {
                          echo "<span class='badge rounded-pill bg-danger'>$DocApprovalNumbers</span>";
                        }
                        ?>
                      </p>
                    </div>
                  </a>
                </p>
              </div>
              <div id="collapseDocumentApprovalTasks" class="collapse" role="tabpanel" aria-labelledby="heading<?php echo $ID; ?>" data-parent="#headingDocumentApprovalTasks">
                <div class="card-body">
                  <table id="TableDocApprovals" class="table table-borderless dt-responsive" cellspacing="0">
                    <thead>
                      <tr>
                        <th><?php echo _("Document ID"); ?></th>
                        <th></th>
                        <th><?php echo _("Document Name"); ?></th>
                        <th><?php echo _("Status"); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <div id="accordionTabDocumentApprovalTasks" role="tablist">
                        <?php
                        $sql = "SELECT knowledge_documents.ID, knowledge_documents.Name AS DocumentName, knowledge_status.Name AS Status
                                  FROM knowledge_documents
                                  INNER JOIN users ON knowledge_documents.RelatedReviewerID = users.id OR knowledge_documents.RelatedApproverID = users.id
                                  INNER JOIN knowledge_status ON knowledge_documents.RelatedStatusID = knowledge_status.ID
                                  WHERE users.ID = $TaskBoardUserID AND knowledge_documents.RelatedStatusID IN (3,4)";
                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                        while ($row = mysqli_fetch_array($result)) {
                          if ($row['ID'] == "") {
                            echo "No personal tasks";
                          } else {
                            $DocID = $row['ID'];
                            $DocumentName = $row['DocumentName'];
                            $Status = $row['Status'];
                        ?>
                            <tr>
                              <td width="10%"><a href='<?php echo "knowledge_view.php?docid=$DocID"; ?>' title='<?php echo _("Open Document") ?>'><?php echo $DocID; ?></a></td>
                              <td width="10%"><?php echo "<a href='knowledge_view.php?docid=" . $DocID . "'><span class='badge badge-pill bg-gradient-secondary'><i class='fa fa-folder-open'></i></span></a>"; ?></td>
                              <td width="60%"><?php echo $DocumentName; ?></td>
                              <td width="20%"><?php echo $Status; ?></td>
                      </div>
                      </td>
                      </tr>
                    <?php } ?>
                  <?php } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="card-collapse">
              <div class="card-header" role="tab" id="headingDocumentTasks">
                <p class="mb-0">
                  <a class="collapsed" data-bs-toggle="collapse" href="#collapseDocumentTasks" aria-expanded="false" aria-controls="collapseDocumentTasks">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                      <p><?php echo _("Document expired"); ?>
                        <?php
                        if ($DocExpiresNumbers == 0) {
                          echo "<span class='badge rounded-pill bg-secondary'>0</span>";
                        } else {
                          echo "<span class='badge rounded-pill bg-danger'>$DocExpiresNumbers</span>";
                        }
                        ?>
                      </p>
                    </div>
                  </a>
                </p>
              </div>
              <div id="collapseDocumentTasks" class="collapse" role="tabpanel" aria-labelledby="heading<?php echo $ID; ?>" data-parent="#headingDocumentTasks">
                <div class="card-body">
                  <table id="TableDocExpires" class="table table-borderless dt-responsive" cellspacing="0">
                    <thead>
                      <tr>
                        <th><?php echo _("Document ID"); ?></th>
                        <th></th>
                        <th><?php echo _("Document Name"); ?></th>
                        <th><?php echo _("Status"); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <div id="accordionTabDocumentTasks" role="tablist">
                        <?php
                        $TaskBoardUserID = ($_SESSION["id"]);
                        $sql = "SELECT knowledge_documents.ID, knowledge_documents.Name AS DocumentName, knowledge_status.Name AS Status
                                    FROM knowledge_documents
                                    INNER JOIN users ON knowledge_documents.RelatedReviewerID = users.id OR knowledge_documents.RelatedApproverID = users.id
                                    INNER JOIN knowledge_status ON knowledge_documents.RelatedStatusID = knowledge_status.ID
                                    WHERE users.ID = $TaskBoardUserID AND (knowledge_documents.ExpirationDate < CURDATE()) AND knowledge_documents.RelatedStatusID NOT IN (6)";
                        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                        while ($row = mysqli_fetch_array($result)) {
                          if ($row['ID'] == "") {
                            echo "No personal tasks";
                          } else {
                            $DocID = $row['ID'];
                            $DocumentName = $row['DocumentName'];
                            $Status = $row['Status'];
                        ?>
                            <tr>
                              <td width="10%"><a href='<?php echo "knowledge_view.php?docid=$DocID"; ?>' title='<?php echo _("Open Document") ?>'><?php echo $DocID; ?></a></td>
                              <td width="10%"><?php echo "<a href='knowledge_view.php?docid=" . $DocID . "'><span class='badge badge-pill bg-gradient-secondary'><i class='fa fa-folder-open'></i></span></a>"; ?></td>
                              <td width="60%"><?php echo $DocumentName; ?></td>
                              <td width="20%"><?php echo $Status; ?></td>
                      </div>
                      </td>
                      </tr>
                    <?php } ?>
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
<?php include("./footer.php") ?>