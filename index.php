<?php

include("./header.php");

?>
<?php

$Widget = [];
$UserSessionID = $_SESSION["id"];
$FullName = $_SESSION["userfullname"];
//Get User Widgets
$sql = "SELECT widgets.ID
        FROM widgets
        INNER JOIN widgets_users ON widgets.ID = widgets_users.WidgetID
        WHERE widgets_users.UserID = '$UserSessionID'";
$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

while ($row = mysqli_fetch_array($result)) {
  $Widget[] = $row['ID'];
}

$DocExpiresNumbers = $functions->getNumberOfDocExpireActions($UserSessionID);
$CMDBExpiredNumbers = getNumberOfCMDBExpired($UserSessionID);
$ChangeTasksNumbers = getNumberOfChangesActions($UserSessionID);

$SumAntalTasks = $DocExpiresNumbers + $ChangeTasksNumbers + $CMDBExpiredNumbers;

$ID = "";

?>
<script>
  $(document).ready(function() {

    $('.nav-link').removeClass('active');
    $('.tab-pane').removeClass('active');
    var activetab = localStorage.getItem("kanbantab");
    if (activetab == null) {
      activetab = "TabDoing";
      localStorage.setItem("kanbantab", activetab);
    }
    var activeNavLink = "navlink" + activetab;
    $('#' + activetab).toggleClass('active');
    $('#' + activeNavLink).toggleClass('active');

    ajaxurl = "getdata.php?getKanBanTasks=1&userid=<?php echo $UserSessionID; ?>&taskstatus=1&tablename=TableTabToDo";
    var TableTabToDo = $('#TableTabToDo').DataTable({
      "dom": 'frtip',
      "searching": true,
      "bFilter": true,
      "paging": true,
      "info": false,
      "pagingType": 'numbers',
      "processing": true,
      "deferRender": true,
      "pageLength": 15,
      "orderCellsTop": true,
      "fixedHeader": false,
      "autoWidth": false,
      "aaSorting": [],
      "responsive": true,
      "language": {
        url: './assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
      },
      "bStateSave": false,
      "displayLength": 15,
      "drawCallback": function(settings) {
        $("#TableTabToDo thead").remove();
      },
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
        },
        {
          "targets": 1,
          "data": "TaskContent",
          "width": "63%"
        }
      ]
    });

    ajaxurl = "getdata.php?getKanBanTasks=1&userid=<?php echo $UserSessionID; ?>&taskstatus=2&tablename=TableTabDoing";
    var TableTabDoing = $('#TableTabDoing').DataTable({
      "dom": 'frtip',
      "searching": true,
      "bFilter": true,
      "paging": true,
      "info": false,
      "pagingType": 'numbers',
      "processing": true,
      "deferRender": true,
      "pageLength": 15,
      "orderCellsTop": true,
      "fixedHeader": false,
      "autoWidth": false,
      "aaSorting": [],
      "responsive": true,
      "language": {
        url: './assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
      },
      "bStateSave": false,
      "displayLength": 15,
      "drawCallback": function(settings) {
        $("#TableTabDoing thead").remove();
      },
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
        },
        {
          "targets": 1,
          "data": "TaskContent",
          "width": "63%"
        }
      ]
    });

    ajaxurl = "getdata.php?getKanBanTasks=1&userid=<?php echo $UserSessionID; ?>&taskstatus=3&tablename=TableTabDone";
    var TableTabDone = $('#TableTabDone').DataTable({
      "dom": 'frtip',
      "searching": true,
      "bFilter": true,
      "paging": true,
      "info": false,
      "pagingType": 'numbers',
      "processing": true,
      "deferRender": true,
      "pageLength": 15,
      "orderCellsTop": true,
      "fixedHeader": false,
      "autoWidth": false,
      "aaSorting": [],
      "responsive": true,
      "language": {
        url: './assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
      },
      "bStateSave": false,
      "displayLength": 15,
      "drawCallback": function(settings) {
        $("#TableTabDone thead").remove();
      },
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
        "data": "TaskContent",
        "width": "63%"
      }]
    });

  });

  // Function to handle the dragstart event
  function drag(event) {
    event.dataTransfer.setData("text/plain", event.target.id);
  }

  function drop(event, status, table) {
    event.preventDefault();
    removeClassFromAll("drag-over");
    event.target.classList.remove('drag-over');
    var TaskId = event.dataTransfer.getData("text/plain");
    updateTaskStatus(TaskId, status, table);
  }

  function allowDrop(event) {
    event.preventDefault();
  }

  function dragEnter(event) {
    event.target.classList.add('drag-over');
  }

  function dragLeave(event) {
    event.target.classList.remove('drag-over');
  }

  var isDragging = false;
  var touchTarget;
  var touchStartX;
  var touchStartY;
  var touchThreshold = 10; // Adjust the threshold as needed

  // Function to handle the touchstart event
  function onTouchStart(event) {
    touchTarget = event.target;
    isDragging = false;
    touchStartX = event.touches[0].clientX;
    touchStartY = event.touches[0].clientY;
  }

  // Function to handle the touchmove event
  function onTouchMove(event) {
    if (isDragging) return;

    var touchCurrentX = event.touches[0].clientX;
    var touchCurrentY = event.touches[0].clientY;
    var deltaX = Math.abs(touchCurrentX - touchStartX);
    var deltaY = Math.abs(touchCurrentY - touchStartY);

    if (deltaX > touchThreshold || deltaY > touchThreshold) {
      isDragging = true;
      event.preventDefault();
    }
  }

  function onTouchEnter(event) {
    event.target.classList.add('drag-over');
  }

  function onTouchLeave(event) {
    event.target.classList.remove('drag-over');
  }

  // Function to handle the touchend event
  function onTouchEnd(event) {
    removeClassFromAll("drag-over");
    if (!isDragging) return;
    isDragging = false;
    var cardId = touchTarget.id;
    var droppableAreaId = event.target.id;

    touchTarget = null;
  }

  // Add event listeners for touch events
  document.addEventListener("touchstart", onTouchStart, {
    passive: true
  });
  document.addEventListener("touchmove", onTouchMove, {
    passive: true
  });
  document.addEventListener("touchEnter", onTouchEnter, {
    passive: true
  });
  document.addEventListener("touchLeave", onTouchLeave, {
    passive: true
  });
  document.addEventListener("touchend", onTouchEnd, {
    passive: true
  });
</script>

<div class="row mt-4">
  <div class="col-md-7 col-sm-7 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header">
          <a href="index.php" class="float-left" title="<?php echo _("This taskboard is a kanban based task board. You can add incidents, request, project tasks etc. to your taskboard to manage all your tasks on daily basis.") ?>">
            <i class="fa-solid fa-list-check"></i> <?php echo _("Taskboard") ?>
          </a>
        </div>
        <div class="card-body">
          <a href="javascript:createNewWorkFlowTask();" class="float-end" hidden="true"><i class="fa-solid fa-plus"></i></a>
          <div class="row">
            <div class="scrolling-menu-wrapper">
              <div class="arrow arrow-left">&#9664;</div>
              <div class="scrolling-menu">
                <ul class="nav nav-tabs justify-content-end" id="myTab" role="tablist">
                  <li class="nav-item active" ondragover="allowDrop(event)" ondrop="drop(event, 1,'TableTabToDo')" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)" title="<?php echo _("Tasks dropped here will put them in todo state"); ?>" onclick="setActiveTabLocalStorage('TabToDo');">
                    <a class="nav-link active" data-bs-toggle="tab" href="#TabToDo" id="navlinkTabToDo" role="tab">
                      <?php echo _("Queue"); ?>
                    </a>
                  </li>
                  <li class="nav-item" ondragover="allowDrop(event)" ondrop="drop(event, 2,'TableTabDoing')" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)" title="<?php echo _("Tasks dropped here will put them in doing state"); ?>" onclick="setActiveTabLocalStorage('TabDoing');">
                    <a class="nav-link" data-bs-toggle="tab" href="#TabDoing" id="navlinkTabDoing" role="tab">
                      <?php echo _("Doing"); ?>
                    </a>
                  </li>
                  <li class="nav-item" ondragover="allowDrop(event)" ondrop="drop(event, 3,'TableTabDone')" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)" title="<?php echo _("Tasks dropped here will put them in done state (tasks here will automatically be closed after 7 days)"); ?>" onclick="setActiveTabLocalStorage('TabDone');">
                    <a class="nav-link" data-bs-toggle="tab" href="#TabDone" id="navlinkTabDone" role="tab">
                      <?php echo _("Done"); ?>
                    </a>
                  </li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  <li class="nav-item ml-auto" ondragover="allowDrop(event)" ondrop="drop(event, 4,'TableTabDone')" ondragenter="dragEnter(event)" ondragleave="dragLeave(event)" title="<?php echo _("Tasks dropped here will close them"); ?>" onclick="setActiveTabLocalStorage('TabApprovals');">
                    <a class="nav-link" data-bs-toggle="tab" href="#TabApprovals" id="navlinkTabApprovals" role="tab">
                      <?php echo _("Follow-ups"); ?>
                      <?php
                      if ($SumAntalTasks == 0) {
                        echo "";
                      } else {
                        echo "<span class=\"badge rounded-pill bg-info\">$SumAntalTasks</span>";
                      }
                      ?>
                    </a>
                  </li>
                </ul>
              </div>
              <div class="arrow arrow-right">&#9654;</div>
            </div>
          </div>

          <div class="tab-content">
            <div class="tab-pane" id="TabToDo">
              <thead style="display: none;">
                <tr>
                  <th></th>
                </tr>
              </thead>
              <table id="TableTabToDo" class="table table-borderless table-responsive">
              </table>
            </div>

            <div class="tab-pane" id="TabDoing">
              <table id="TableTabDoing" class="table table-borderless table-responsive" cellspacing="0">
              </table>
            </div>

            <div class="tab-pane" id="TabDone">
              <table id="TableTabDone" class="table table-borderless table-responsive" cellspacing="0">
              </table>
            </div>

            <div class="tab-pane" id="TabApprovals">
              <div class="col-md-12 col-sm-12 col-xs-12">
                <a class="collapsed" data-bs-toggle="collapse" href="#collapseChangeTasks" aria-expanded="false" aria-controls="collapseChangeTasks">
                  <p><?php echo _("Change approvals"); ?>
                    <?php
                    if ($ChangeTasksNumbers == 0) {
                      echo "<span class='badge rounded-pill bg-secondary'>0</span>";
                    } else {
                      echo "<span class='badge rounded-pill bg-info'>$ChangeTasksNumbers</span>";
                    }
                    ?>
                  </p>
                </a>
                <div id="collapseChangeTasks" class="collapse" role="tabpanel" aria-labelledby="heading<?php echo $ID; ?>" data-parent="#headingChangeTasks">
                  <div class="card">
                    <div class="card-body">
                      <table id="TableChangeApprovals" class="table table-borderless dt-responsive" cellspacing="0">
                        <thead>
                          <tr>
                            <th><?php echo _("Subject"); ?></th>
                            <th></th>
                            <th><?php echo _("Status"); ?></th>
                          </tr>
                        </thead>
                        <tbody>
                          <div id="accordionTabChangeTasks" role="tablist">
                            <?php

                            $ClosedStatus = $functions->getITSMClosedStatus(3);

                            $sql = "SELECT itsm_changes.ID AS ChangeID, itsm_changes.Subject, itsm_changes.Status
                                    FROM itsm_changes
                                    INNER JOIN itsm_statuscodes ON itsm_changes.Status = itsm_statuscodes.ID
                                    WHERE itsm_changes.Authorizer = $UserSessionID AND itsm_changes.Status NOT IN (" . implode(",", $ClosedStatus) . ");";

                            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                            while ($row = mysqli_fetch_array($result)) {
                              $ID = $row['ChangeID'];
                              if ($ID == "") {
                                echo "No personal tasks";
                              } else {
                                $Subject = $row['Subject'];
                                $Status = $row['Status'];
                                $StatusName = $functions->translate(getITSMStatusName($Status, "3"));
                            ?>
                                <tr>
                                  <td width="60%"><?php echo $Subject; ?></td>
                                  <td width="10%"><?php echo "<a href=\"javascript:viewITSM('$ID','3','1','modal');\"><span class='badge badge-pill bg-gradient-success'><i class='fa fa-pen-to-square'></i></span></a>"; ?></td>
                                  <td width="20%"><?php echo $StatusName; ?></td>
                          </div>
                          </td>
                          </tr>
                        <?php } ?>
                      <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <br>
                </div>
              </div>

              <div class="col-md-12 col-sm-12 col-xs-12">
                <a class="collapsed" data-bs-toggle="collapse" href="#collapseCMDBExpires" aria-expanded="false" aria-controls="collapseCMDBExpires">
                  <p><?php echo _("Expired CI's"); ?>
                    <?php
                    if ($CMDBExpiredNumbers == 0) {
                      echo "<span class='badge rounded-pill bg-secondary'>0</span>";
                    } else {
                      echo "<span class='badge rounded-pill bg-info'>$CMDBExpiredNumbers</span>";
                    }
                    ?>
                  </p>
                </a>
                <div id="collapseCMDBExpires" class="collapse" role="tabpanel" aria-labelledby="heading<?php echo $ID; ?>" data-parent="#headingCMDBExpires">
                  <div class="card">
                    <div class="card-body">
                      <?php
                      $CMDBTypesArray = getCMDBTypes();
                      $TempResultArray = array();

                      foreach ($CMDBTypesArray as $Type) {
                        $CMDBTypeID = $Type['CMDBTypeID'];
                        $Name = $Type['Name'];
                        $TableName = $Type['TableName'];
                        $FieldName = $Type['FieldName'];
                        $TempResultArray = array_merge($TempResultArray, getExpiredForCMDBType($CMDBTypeID, $Name, $TableName, $FieldName, $UserSessionID));
                      }

                      usort($TempResultArray, function ($a, $b) {
                        $dateA = strtotime($a['EndDate']);
                        $dateB = strtotime($b['EndDate']);
                        return $dateA - $dateB;
                      });

                      echo '<table id="TableCMDBExpires" class="table table-borderless dt-responsive" cellspacing="0">
                              <thead>
                                  <tr>
                                      <th>' . _("Type") . '</th>
                                      <th>' . _("Name") . '</th>
                                      <th>' . _("End Date") . '</th>
                                  </tr>
                              </thead>
                              <tbody>';

                      foreach ($TempResultArray as $item) {
                        echo '<tr>';
                        echo '<td>' . $item['TypeName'] . '</td>';
                        echo '<td><a href="' . $item['Link'] . '">' . $item['CIName'] . '</a></td>';
                        echo '<td>' . convertToDanishDateTimeFormat($item['EndDate']) . '</td>';
                        echo '</tr>';
                      }

                      echo '</tbody>
                            </table>';

                      ?>
                    </div>
                  </div>
                  <br>
                </div>
              </div>

              <div class="col-md-12 col-sm-12 col-xs-12">
                <a class="collapsed" data-bs-toggle="collapse" href="#collapseDocumentTasks" aria-expanded="false" aria-controls="collapseDocumentTasks">

                  <p><?php echo _("Documents expired"); ?>
                    <?php
                    if ($DocExpiresNumbers == 0) {
                      echo "<span class='badge rounded-pill bg-secondary'>0</span>";
                    } else {
                      echo "<span class='badge rounded-pill bg-info'>$DocExpiresNumbers</span>";
                    }
                    ?>
                  </p>
                </a>

                <div id="collapseDocumentTasks" class="collapse" role="tabpanel" aria-labelledby="heading<?php echo $ID; ?>" data-parent="#headingDocumentTasks">
                  <div class="card">
                    <div class="card-body">
                      <table id="TableDocExpires" class="table table-borderless dt-responsive" cellspacing="0">
                        <thead>
                          <tr>
                            <th><?php echo _("Subject"); ?></th>
                            <th></th>
                            <th><?php echo _("Status"); ?></th>
                          </tr>
                        </thead>
                        <tbody>
                          <div id="accordionTabDocumentTasks" role="tablist">
                            <?php
                            $ClosedStatus = $functions->getITSMClosedStatus(7);
                            $UserSessionID = ($_SESSION["id"]);
                            $sql = "SELECT itsm_knowledge.ID, itsm_knowledge.Subject, itsm_knowledge.Status
                                    FROM itsm_knowledge
                                    INNER JOIN itsm_statuscodes ON itsm_knowledge.Status = itsm_statuscodes.ID
                                    WHERE itsm_knowledge.Responsible = $UserSessionID AND (itsm_knowledge.ExpirationDate < CURDATE()) AND itsm_knowledge.Status NOT IN (" . implode(",", $ClosedStatus) . ");";

                            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

                            while ($row = mysqli_fetch_array($result)) {
                              if ($row['ID'] == "") {
                                echo "No personal tasks";
                              } else {
                                $DocID = $row['ID'];
                                $Subject = $row['Subject'];
                                $Status = $row['Status'];
                                $StatusName = $functions->translate(getITSMStatusName($Status, "7"));
                            ?>
                                <tr>
                                  <td width="60%"><?php echo $Subject; ?></td>
                                  <td width="10%"><?php echo "<a href=\"javascript:viewITSM('$DocID','7','1','modal');\"><span class='badge badge-pill bg-gradient-success'><i class='fa fa-pen-to-square'></i></span></a>"; ?></td>
                                  <td width="20%"><?php echo $StatusName; ?></td>
                          </div>
                          </td>
                          </tr>
                        <?php } ?>
                      <?php } ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <br>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-5 col-sm-5 col-xs-12">

    <?php
    if (in_array("4", $Widget)) { ?>

      <div class="card mb-3">
        <div class="card-header">
          <i class="fa fa-clock"></i> <?php echo _("Todays time registration"); ?>
          <div class="float-end">
            <ul class="navbar-nav justify-content-end">
              <li class="nav-item dropdown pe-2">
                <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                  &nbsp;&nbsp;<i class="fa-solid fa-ellipsis-vertical" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Actions") ?>"></i>&nbsp;&nbsp;
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href=('timeregistrations_all.php?userid=<?php echo $UserSessionID ?>')">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Time registrations") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <?php
                  $GroupsAllowedArray = ["2", "100028"];

                  if (count(array_intersect($GroupsAllowedArray, $group_array)) > 0) {
                    echo "<li class='mb-2'>
                              <a class='dropdown-item border-radius-md' onclick=\"window.location.href=('administration_timeregistrations.php')\">
                                <div class='d-flex align-items-center py-1'>
                                  <div class='ms-2'>
                                    <h6 class='text-sm font-weight-normal my-auto'>
                                      " . _("Manage time registration") . "
                                    </h6>
                                  </div>
                                </div>
                              </a>
                            </li>";
                  }
                  ?>
                </ul>
              </li>
            </ul>
          </div>
        </div>
        <div class="card-body">
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <table id="TableTimeregistration" class="table table-borderless table-hover" cellspacing="0">
              <?php
              $UserSessionID = ($_SESSION["id"]);
              $sql = "SELECT time_registrations.ID AS TimeRegID, taskslist.ModuleName, time_registrations.RelatedUserID, users.Firstname, users.Lastname, time_registrations.RelatedTaskID, DATE_FORMAT(time_registrations.DateRegistered, '%Y-%m-%d'), 
                        time_registrations.TimeRegistered, time_registrations.Description, taskslist.GoToLink, taskslist.RelatedElementID, Billable, time_registrations.DateWorked
                        FROM time_registrations
                        LEFT JOIN taskslist ON time_registrations.RelatedTaskID = taskslist.ID
                        LEFT JOIN users ON time_registrations.RelatedUserID = users.ID
                        WHERE DATE_FORMAT(time_registrations.DateWorked, '%Y-%m-%d') = CURDATE() AND time_registrations.RelatedUserID = $UserSessionID";

              $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
              ?>
              <tbody>
                <?php while ($row = mysqli_fetch_array($result)) { ?>
                  <tr>
                    <td><a href="<?php echo $row['GoToLink']; ?>" title="<?php echo _('Open') ?>">
                        <p class="text-sm text-secondary mb-0"><?php echo $row['ModuleName'] . " " . $row['RelatedElementID']; ?></p>
                      </a></td>

                    <td onclick="runModalChangeTimeRegistration(<?php echo $row['TimeRegID']; ?>)"><a href='javascript:void(0);'>
                        <p class="text-sm text-secondary mb-0"><?php echo $row['TimeRegistered']; ?></p>
                      </a></td>

                  </tr>
                <?php } ?>
              </tbody>
            </table>
            <?php
            $UserSessionID = ($_SESSION["id"]);
            $sql = "SELECT ROUND(SUM(time_registrations.TimeRegistered)/60, 2) AS SumTimeToday
                            FROM time_registrations
                            LEFT JOIN taskslist ON time_registrations.RelatedTaskID = taskslist.ID
                            LEFT JOIN users ON time_registrations.RelatedUserID = users.ID
                            WHERE DATE_FORMAT(time_registrations.DateWorked, '%Y-%m-%d') = CURDATE() AND time_registrations.RelatedUserID = $UserSessionID";

            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            while ($row = mysqli_fetch_array($result)) {
              echo "<p class='text-xs text-secondary mb-0'> " . $row['SumTimeToday'] . " " . _("hours in total") . "</p>";
            }
            ?>
          </div>
        </div>
      </div>
    <?php } ?>
    <?php
    $NewsIcons = "";
    $NewsIcons .= "
            <div class='card mb-3'>
              <div class='card-header'>
                <i class='fa fa-newspaper'></i>" . " " . _('News') . "
                <div class='float-end'>
                  <ul class='navbar-nav justify-content-end'>
                    <li class='nav-item dropdown pe-2'>
                      <a href='javascript:;' class='nav-link text-body p-0 position-relative' id='dropdownMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                        &nbsp;&nbsp;<i class='fa-solid fa-ellipsis-vertical' data-bs-toggle=\"tooltip\" data-bs-title='" . _('Actions') . "'></i>&nbsp;&nbsp;
                      </a>
                      <ul class='dropdown-menu dropdown-menu-end p-2 me-sm-n4' aria-labelledby='dropdownMenuButton'>
                        <li class='mb-2'>
                          <a class='dropdown-item border-radius-md' onclick='window.location.href=\"news_all.php\"'>
                            <div class='d-flex align-items-center py-1'>
                              <div class='ms-2'>
                                <h6 class='text-sm font-weight-normal my-auto'>" . _('News archive') . "
                                </h6>
                              </div>
                            </div>
                          </a>
                        ";

    if (in_array("100020", $group_array)) {
      $NewsIcons .= "<a class='dropdown-item border-radius-md' onclick='window.location.href=\"newsmanager.php\"'>
                    <div class='d-flex align-items-center py-1'>
                      <div class='ms-2'>
                        <h6 class='text-sm font-weight-normal my-auto'>" . _('Administrate News') . "</h6>
                      </div>
                    </div>
                  </a>";
    }

    $NewsIcons .= "
                        </li>
                      </ul>
                    </li>
                  </ul>
                </div>
              </div>
              <div class='card-body'>
            ";

    echo $NewsIcons;
    ?>


    <table id="TableNews" class="table table-borderless dt-responsive" cellspacing="0" style="width: 100%;">
      <thead>
        <tr>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php

        $userSessionID = $_SESSION['id'];
        $memberofroles = $_SESSION['memberofroles'];
        if ($memberofroles) {
          $sqlRoles = "OR news_categories.RelatedRole IN (" . implode(',', $_SESSION['memberofroles']) . ")";
        } else {
          $sqlRoles = "";
        }

        $sql = "
    SELECT DISTINCT * FROM (
        (SELECT news.ID,
                Headline,
                Content,
                CONCAT(users.Firstname, ' ', users.Lastname) AS FullName,
                users.ID AS UserID,
                DateCreated,
                RelatedCategory,
                news.NewsWriter,
                users.email,
                users.profilepicture,
                CASE WHEN news_reads.NewsID IS NULL THEN 0 ELSE 1 END AS ReadStatus,
                CASE WHEN news_reads.NewsID IS NULL THEN NULL ELSE news_reads.ReadDate END AS ReadDate
          FROM news
          LEFT JOIN users ON news.NewsWriter = users.ID
          LEFT JOIN news_categories ON news.RelatedCategory = news_categories.ID
          LEFT JOIN news_reads ON news.ID = news_reads.NewsID AND news_reads.UserID = $userSessionID
          WHERE news.Active = 1
          AND (
              news_categories.RelatedGroupID = '' 
              OR news_categories.RelatedGroupID IN (" . implode(',', $_SESSION['memberofgroups']) . ")
              " . $sqlRoles . "
          )
          ORDER BY DateCreated DESC
          LIMIT 15)

        UNION ALL

        (SELECT news.ID,
                Headline,
                Content,
                CONCAT(users.Firstname, ' ', users.Lastname) AS FullName,
                users.ID AS UserID,
                DateCreated,
                RelatedCategory,
                news.NewsWriter,
                users.email,
                users.profilepicture,
                CASE WHEN news_reads.NewsID IS NULL THEN 0 ELSE 1 END AS ReadStatus,
                CASE WHEN news_reads.NewsID IS NULL THEN NULL ELSE news_reads.ReadDate END AS ReadDate
          FROM news
          LEFT JOIN users ON news.NewsWriter = users.ID
          LEFT JOIN news_categories ON news.RelatedCategory = news_categories.ID
          LEFT JOIN news_reads ON news.ID = news_reads.NewsID AND news_reads.UserID = $userSessionID
          WHERE news.Active = 1
          AND DateCreated <= NOW()
          AND DateCreated >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
          AND (
              news_categories.RelatedGroupID = '' 
              OR news_categories.RelatedGroupID IN (" . implode(',', $_SESSION['memberofgroups']) . ")
              " . $sqlRoles . "
          ))

    ) AS combined_results
    ORDER BY DateCreated DESC;
";
        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));

        while ($row = mysqli_fetch_array($result)) {
          if ($row['ID'] == '') {
            echo 'No News yet';
          } else {
            $NewsID = $row['ID'];
            $Headline = $row['Headline'];
            $NewsWriter = $row['NewsWriter'];

            // Conditional checks for the potentially empty columns
            $CreatedByUserFullName = empty($row['FullName']) ? 'System' : $row['FullName'];  // set to 'Anonymous' if empty
            $CreatedByUserID = empty($row['UserID']) ? 0 : $row['UserID'];  // set to 0 if empty
            $Email = empty($row['email']) ? '' : $row['email'];  // set to a default email if empty
            $ProfilePicture = empty($row['profilepicture']) ? 'default-profile-pic.png' : $row['profilepicture'];  // set to a default profile picture if empty

            $Content = $row['Content'];
            $myFormatForView = convertToDanishDateTimeFormat($row['DateCreated']);
            $DateCreated = $myFormatForView;
            $RelatedCategory =  $row['RelatedCategory'];
            $ReadDate = isset($row['ReadDate']) ? convertToDanishDateTimeFormat($row['ReadDate']) : '';

            $ReadStatus = $row['ReadStatus'];
            $readIcon = ($ReadStatus == 1) ? "<i class='fa-brands fa-readme' title='Read: $ReadDate'></i>" : "";

            if (strlen($Headline) > 150) {
              $Headline = substr($Headline, 0, 150) . "...";
            }
            if (empty($NewsWriter)) {
              $ProfilePopOver = "";
            } else {
              $ProfilePopOver = getProfilePopover($NewsWriter, $CreatedByUserID);
            }

            echo "<tr>
                    <td>
                      <div class=\"card card-body-dropdown\">                
                        <div data-bs-toggle=\"collapse\" href=\"#collapseNews$NewsID\" role=\"button\" aria-expanded=\"false\" aria-controls=\"collapseNews$NewsID\" onclick=\"updateNewsReadStatus($NewsID)\">
                          <small class=\"float-left text-wrap\">$Headline $readIcon</small>
                        </div>
                        <div class=\"collapse\" id=\"collapseNews$NewsID\">
                          <br>
                          <p class='text-sm text-secondary text-wrap'>" . html_entity_decode($Content) . "</p>                  
                          $ProfilePopOver
                          <p class='text-sm text-secondary text-wrap'>$DateCreated</p>
                        </div>
                      </div>
                    </td>
                  </tr>";
          }
        }
        ?>
      </tbody>
    </table>
  </div>
</div>

<?php
if (in_array("7", $Widget)) { ?>
  <br>
  <div class="card mb-3">
    <div class="card-body"><i class="fa fa-eye"></i> <?php echo _("Watchlist"); ?>
      <div class="float-end">
        <a href='javascript:;' data-bs-toggle='tooltip' data-placement='top' data-bs-toggle="tooltip" data-bs-title='<?php echo _("Go to watchlist") ?>' onclick='window.location.href="watchlist.php"'>
          <i class="fas fas-dark fa-pencil-alt"></i>
        </a>
      </div>
      <table id="WhatchlistTable" class="table table-bordered table-hover compact" cellspacing="0">
        <thead>
          <tr>
            <th></th>
            <th><?php echo _("Type") ?></th>
            <th><?php echo _("Number") ?></th>
            <th><?php echo _("Description") ?></th>
          </tr>
        </thead>
        <?php
        $sql = "SELECT watchlist.ID, watchlist.Name, modules.TypeIcon, watchlist.URL, watchlist.ElementName, watchlist.ElementID
                  FROM watchlist
                  LEFT JOIN modules On modules.ID = watchlist.RelatedModuleID
                  WHERE RelatedUserID ='" . $_SESSION["id"] . "';";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        ?>
        <tbody>
          <?php while ($row = mysqli_fetch_array($result)) { ?>
            <?php
            $GoToLink = $row['URL'];
            $RelatedElementID = $row['ElementID'];
            $PreviewLink = strtolower("preview_" . $row['ElementName'] . ".php?elementid=$RelatedElementID&sessionuserid=$UserSessionID");
            ?>
            <tr onclick="window.location='<?php echo $row['URL'] ?>'">
              <td><?php echo "<i class=\"" . $row['TypeIcon'] . "\">"; ?></td>
              <td><?php echo $row['ElementName'] ?></td>
              <td width="5%"><?php echo "<b class='thumbnail-preview'><a href='$GoToLink'>$RelatedElementID<span><iframe src='$PreviewLink' width='600px' height='800px' overflow='hidden' scrolling='no' frameBorder='0'></iframe></span></a></b>"; ?></td>
              <td><?php echo "<a href='" . $row['URL'] . "'><b>" . $row['Name'] . "</b></a>"; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
<?php } ?>

<?php
if (in_array("3", $Widget)) { ?>
  <div class="card mb-3">
    <div class="card-header"><i class='fa fa-stream'></i> <?php echo _("Activity Stream"); ?></div>
    <div class="card-body">
      <?php include("./activitystream_incl.php"); ?>
    </div>
  </div>
<?php } ?>

<?php
if (in_array("6", $Widget)) { ?>
  <div class="card mb-3">
    <div class="card-header">
      <i class='far fa-bookmark'></i> <?php echo _("Favorites"); ?>
      <div class="float-end">
        <a href='javascript:;' data-bs-toggle='tooltip' data-placement='top' data-bs-toggle="tooltip" data-bs-title='<?php echo _("Go to favorites") ?>' onclick='window.location.href="favorites.php"'>
          <i class="fas fas-dark fa-pencil-alt"></i>
        </a>
      </div>
    </div>
    <div class="card-body">
      <table id="FavoritesTable" class="table table-bordered table-hover compact" cellspacing="0">
        <thead>
          <tr>
            <th></th>
            <th><?php echo _("Type") ?></th>
            <th><?php echo _("Nummer") ?></th>
          </tr>
        </thead>
        <?php
        $sql = "SELECT favorites.ID, favorites.Name, modules.TypeIcon, favorites.URL, favorites.ElementName 
                FROM favorites
                LEFT JOIN modules On modules.ID = favorites.RelatedModuleID
                WHERE RelatedUserID ='" . $_SESSION["id"] . "';";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        ?>
        <tbody>
          <?php while ($row = mysqli_fetch_array($result)) { ?>
            <tr onclick="window.location='<?php echo $row['URL'] ?>'">
              <td><?php echo "<i class=\"" . $row['TypeIcon'] . "\">"; ?></td>
              <td><?php echo _($row['ElementName']); ?></td>
              <td><?php echo "<a href='" . $row['URL'] . "'><b>" . $row['Name'] . "</b></a>"; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
<?php } ?>

<?php
if (in_array("5", $Widget)) { ?>
  <div class="card mb-3">
    <div class="card-header">
      <i class='fa fa-user'></i> <?php echo _("Users Online"); ?>
      <div class='float-end'>
        <a href='javascript:;' data-toggle='tooltip' data-placement='top' data-bs-toggle="tooltip" data-bs-title='<?php echo _("Message Group") ?>' onclick="$('#modalNewMessageGroup').modal('show')">
          <i class='fa fa-dark fa-envelope messageenvelope'></i>
        </a>
      </div>
    </div>
    <div class="card-body">
      <table id="UsersOnline" class="table table-borderless table-hover" cellspacing="0">
        <thead hidden>
          <tr>
            <th><?php echo _("Name"); ?></th>
            <th></th>
          </tr>
        </thead>
        <?php
        $sql = "SELECT userid, sessioncreated, sessionrenewed, CONCAT(users.Firstname,' ',users.Lastname) AS Name, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName,users.ProfilePicture, users.email
                FROM sessions
                LEFT JOIN users ON sessions.userid = users.ID 
                WHERE sessionrenewed > (NOW() - INTERVAL 15 MINUTE)
                ORDER BY sessioncreated DESC;";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
        ?>
        <tbody>
          <?php while ($row = mysqli_fetch_array($result)) { ?>
            <tr>
              <?php
              $FullName = $row['FullName'];
              $Name = $row['Name'];
              $Email = $row['email'];
              $UsersID = $row['userid'];
              $ProfilePicture = $row['ProfilePicture'];
              $ProfilePopOver = getProfilePopover($UserSessionID, $UsersID);
              ?>
              <td><?php echo "$ProfilePopOver" ?></td>
              <td><i class="fa fa-dark fa-envelope" id="messageenvelope" data-bs-toggle="tooltip" data-bs-title='<?php echo _("Send message") ?>' onclick="NewMessage('<?php echo $row['userid']; ?>')"></i></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    function displayPopUp(element) {
      const el = document.getElementById(element);
      el.style.display = 'block';
    }

    function hidePopUp(element) {
      const el = document.getElementById(element);
      el.style.display = 'none';
    }
  </script>
<?php } ?>

<?php
if (in_array("1", $Widget)) { ?>
  <div class="card mb-3">
    <div class="card-header">
      <i class="fa fa-birthday-cake"></i> <?php echo _("Birthdays"); ?>
    </div>
    <div class="card-body">
      <table id="TableBirthdays" class="table table-responsive table-borderless table-hover" cellspacing="0">
        <thead hidden>
          <tr>
            <th><?php echo _("Name"); ?></th>
            <th></th>
            <th></th>
          </tr>
        </thead>
        <?php
        $sql = "SELECT ID AS UserID, Firstname, Lastname, Username, Birthday, users.ProfilePicture, CONCAT(users.Firstname,' ',users.Lastname) AS Name, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName, users.Email,
                      DATE_ADD(
                        Birthday, 
                          INTERVAL IF(DAYOFYEAR(Birthday) >= DAYOFYEAR(CURDATE()),
                              YEAR(CURDATE())-YEAR(Birthday),
                              YEAR(CURDATE())-YEAR(Birthday)+1
                          ) YEAR
                      ) AS next_birthday, TIMESTAMPDIFF(YEAR, Birthday, DATE_ADD(
                        Birthday, 
                          INTERVAL IF(DAYOFYEAR(Birthday) >= DAYOFYEAR(CURDATE()),
                              YEAR(CURDATE())-YEAR(Birthday),
                              YEAR(CURDATE())-YEAR(Birthday)+1
                          ) YEAR
                      )) AS YearsOld
                  FROM users
                  WHERE RelatedUserTypeID = 1 OR RelatedUserTypeID = 3 AND
                  Birthday IS NOT NULL
                  HAVING 
                  next_birthday BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                  ORDER BY next_birthday
                  LIMIT 1000;";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn)); ?>
        <tbody>
          <?php while ($row = mysqli_fetch_array($result)) { ?>
            <tr>
              <?php $FullName = $row['FullName']; ?>
              <?php $UsersID = $row['userid']; ?>
              <?php $ProfilePicture = $row['ProfilePicture']; ?>
              <?php $Name = $row['Name']; ?>
              <?php
              $email = $row['Email'];
              $ProfilePopOver = getProfilePopover($UserSessionID, $CreatedByUserID);
              ?>

              <td><?php echo $ProfilePopOver ?></td>
              <td><a href='javascript:void(0);'><?php echo convertToDanishDateFormat($row['next_birthday']); ?></a></td>
              <td><a href='javascript:void(0);'><?php echo $row['YearsOld'] . " years"; ?></a></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
<?php } ?>
<?php
if (in_array("2", $Widget)) { ?>
  <div class="card mb-3">
    <div class="card-header">
      <i class="fa fa-flag"></i> <?php echo _("Anniversarys"); ?>
    </div>
    <div class="card-body">
      <table id="TableAnniversarys" class="table table-responsive table-borderless table-hover" cellspacing="0">
        <thead hidden>
          <tr>
            <th><?php echo _("Name"); ?></th>
            <th></th>
            <th></th>
          </tr>
        </thead>
        <?php $sql = "SELECT ID AS UserID, Firstname, Lastname, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName, Username, StartDate, users.ProfilePicture, CONCAT(users.Firstname,' ',users.Lastname) AS Name, users.email AS Email,
                            DATE_ADD(
                              StartDate, 
                                INTERVAL IF(DAYOFYEAR(StartDate) >= DAYOFYEAR(CURDATE()),
                                    YEAR(CURDATE())-YEAR(StartDate),
                                    YEAR(CURDATE())-YEAR(StartDate)+1
                                ) YEAR
                            ) AS next_StartDate, TIMESTAMPDIFF(YEAR, StartDate, DATE_ADD(
                              StartDate, 
                                INTERVAL IF(DAYOFYEAR(StartDate) >= DAYOFYEAR(CURDATE()),
                                    YEAR(CURDATE())-YEAR(StartDate),
                                    YEAR(CURDATE())-YEAR(StartDate)+1
                                ) YEAR
                            )) AS EmployedYears
                        FROM users
                        WHERE RelatedUserTypeID = 1 OR RelatedUserTypeID = 3 AND
                        StartDate IS NOT NULL
                        HAVING 
                            next_StartDate BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                        ORDER BY next_StartDate
                        LIMIT 1000;";

        $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn)); ?>
        <tbody>
          <?php while ($row = mysqli_fetch_array($result)) { ?>
            <tr>
              <?php $FullName = $row['FullName']; ?>
              <?php $UsersID = $row['UserID']; ?>
              <?php $ProfilePicture = $row['ProfilePicture']; ?>
              <?php $Name = $row['Name']; ?>
              <?php
              $email = $row['Email'];
              $ProfilePopOver = getProfilePopover($UserSessionID, $CreatedByUserID);
              ?>

              <td><?php echo $ProfilePopOver ?></td>
              <td><?php echo convertToDanishDateFormat($row['next_StartDate']); ?></td>
              <td><?php echo $row['EmployedYears'] . " years"; ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
  </div>
<?php } ?>
</div>

<script>
  $(document).ready(function() {
    <?php initiateMediumViewTable("TableNews"); ?>
    <?php initiateMediumViewTable("UsersOnline"); ?>
    <?php initiateMediumViewTable("TableBirthdays"); ?>
    <?php initiateMediumViewTable("TableAnniversarys"); ?>
    <?php initiateMediumViewTable("TableDocApprovals"); ?>
    <?php initiateMediumViewTable("TableCMDBExpires"); ?>
    <?php initiateMediumViewTable("TableDocExpires"); ?>
    <?php initiateMediumViewTable("TableChangeApprovals"); ?>
    <?php initiateMediumViewTable("TableCIApprovals"); ?>

    if (document.body.contains(document.getElementById('messageenvelope'))) {
      document.getElementById("messageenvelope").style.cursor = "pointer";
    }
  });
</script>
<!-- /page content -->

<?php include("./modals/modal_timeregistration.php") ?>
<?php include("./modals/modal_update_task.php") ?>
<?php include("./modals/modal_message_user.php") ?>
<?php include("./modals/modal_message_group.php") ?>
<?php include("./footer.php") ?>