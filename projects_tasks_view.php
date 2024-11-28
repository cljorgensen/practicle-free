<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array) || in_array("100008", $group_array) || in_array("100007", $group_array)) {
} else {
  $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
  notgranted($CurrentPage);
}

?>

<?php
//Set important variables to work with
$ProjectTaskID = $_GET["projecttaskid"];
$ProjectID = getRelatedProjectFromProjectTask($ProjectTaskID);
$ElementPath = "projecttasks";
$ElementRef = "ProjectTaskID";
$ElementGetValue = "projecttaskid";
$UserSessionID = $_SESSION["id"];
$RelatedManagerID = getRelatedProjectManager($ProjectTaskID);
$PrivateStatus = getProjectTaskPrivateStatus($ProjectTaskID);
$ModuleID = "13";

if ($PrivateStatus == "1" && $RelatedManagerID <> $UserSessionID) {
  $Allowed = getProjectTaskParticipantsForPrivate($ProjectTaskID, $UserSessionID);
  if (!empty($Allowed)) {
  } else {
    $Message = _("This task is private and you are not added as project task participant");
    notgrantedPage("projects_view.php?projectid=$ProjectID", $Message);
  }
} elseif ($PrivateStatus == "0" && $RelatedManagerID <> $UserSessionID) {
  $ProjectParticipants = getProjectParticipants($ProjectID);
  if (in_array($UserSessionID, $ProjectParticipants)) {
  } else {
    $Message = _("You are not added as project participant, so you can not see this project task");
    notgrantedPage("projects_view.php?projectid=$ProjectID", $Message);
  }
} else {
  //This is the project manager, allow to continue
}

//Get tickets pre values to be able to compare between pre and post
$sql = "SELECT project_tasks.ID AS ID, TaskName, project_tasks.Start, project_tasks.Description, project_tasks.Progress, project_tasks.EstimatedBudget, project_tasks.BudgetSpend,project_tasks.EstimatedHours, project_tasks.HoursSpend, project_tasks.Deadline, users.firstname, users.lastname
        , projects_statuscodes.StatusName, project_tasks.CompletedDate, projects.Name AS ProjectName, project_tasks.Responsible, project_tasks.RelatedCategory
        FROM project_tasks
        LEFT JOIN projects ON project_tasks.RelatedProject = projects.ID
        LEFT JOIN users ON project_tasks.Responsible = users.ID
        LEFT JOIN projects_statuscodes ON project_tasks.Status = projects_statuscodes.ID 
        WHERE project_tasks.ID = $ProjectTaskID;";
$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
  $ProjectTaskNameVal = $row["TaskName"];
  $ProjectTaskStartVal = $row["Start"];
  $ProjectTaskProgressVal = $row['Progress'];
  $ProjectTaskStatusVal = $row['StatusName'];
  $ProjectTaskDeadlineVal = $row['Deadline'];
  $ProjectTaskDescriptionVal = $row['Description'];
  $ProjectTaskEstimatedBudgetVal = $row['EstimatedBudget'];
  $ProjectTaskBudgetSpendVal = $row['BudgetSpend'];
  $ProjectTaskEstimatedHoursVal = $row['EstimatedHours'];
  $ProjectTaskHoursSpendVal = $row['HoursSpend'];
  $ProjectTaskResponsibleVal = $row['firstname'] . " " . $row['lastname'];
  $ProjectTaskResponsibleIDVal = $row['Responsible'];
  $ProjectTaskCompletedDateVal = $row['CompletedDate'];
  $ProjectTaskRelatedCategory = $row['RelatedCategory'];
  $ProjectNameVal = $row['ProjectName'];
}
$RedirectPage = "projects_tasks_view.php?projecttaskid=" . $ProjectTaskID;
$RedirectPageUpload = "../projects_tasks_view.php?projecttaskid=" . $ProjectTaskID;
?>

<?php
if (isset($_POST['submit_createProjectActivity'])) {
  $Subject = $_POST['ModalProjectActivityDescription'];
  $Subject = sanitizeTextAndBase64($Subject, $ProjectTaskID, $ModuleID);

  createNewProjectActivity($Subject, $ProjectID, $ProjectTaskID, $ProjectNameVal);
}
?>

<?php
//Function to let users add a project to their task list
if (isset($_GET['addtotaskslist'])) {
  addProjectTaskToTaskslist($ProjectTaskID, $UserSessionID, $ProjectTaskNameVal, $ProjectTaskResponsibleVal, $ProjectTaskStartVal, $ProjectTaskDeadlineVal, $ProjectID, $ProjectNameVal);
}

?>
<script>
  function updateProjectTasksTicketRelation(ticketid) {
    projecttaskid = "<?php echo $ProjectTaskID ?>";
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?updateProjectTasksTicketRelation=1&ticketid=" + ticketid + "&projecttaskid=" + projecttaskid, true);
    xmlhttp.send();
    location.reload(true);
  }

  function updateProjectTasksChangeRelation(changeid) {
    projecttaskid = "<?php echo $ProjectTaskID ?>";
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?updateProjectTaskChangeRelation=1&projecttaskid=" + projecttaskid + "&changeid=" + changeid, true);
    xmlhttp.send();
    location.reload(true);
  }

  function updateProjectTaskProblemsRelation(problemid) {
    projecttaskid = "<?php echo $ProjectTaskID ?>";
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?updateProjectTaskProblemsRelation=1&projecttaskid=" + projecttaskid + "&problemid=" + problemid, true);
    xmlhttp.send();
    location.reload(true);
  }

  function deleterelticket(relid) {
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?deleterelticketprojecttask=" + relid, true);
    xmlhttp.send();
    location.reload(true);
  }

  function deleterelchange(relid) {
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?deleterelchangeprojecttask=" + relid, true);
    xmlhttp.send();
    location.reload(true);
  }

  function deleterelproblem(relid) {
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?deleterelproblemprojecttask=" + relid, true);
    xmlhttp.send();
    location.reload(true);
  }

  function openproblemprojectrel(problemid) {
    var url = './problems_view.php?elementid=' + problemid
    window.location.href = url;
  }

  function openchangeprojectrel(changeid) {
    var url = './changes_view.php?elementid=' + changeid
    window.location.href = url;
  }

  function openticketprojectrel(ticketid) {
    var url = './incidents_view.php?elementid=' + ticketid
    window.location.href = url;
  }

  function pushProjectTasksXAhead() {

    const DaysToPush = prompt("<?php echo _("For how many days forward should tasks from this tasks start day be moved? Note: you can use minus to subtract days instead!") ?>");

    ProjectTaskID = '<?php echo $ProjectTaskID ?>';
    $.ajax({
      type: 'GET',
      url: './getdata.php?pushProjectTasksForwardXDays',
      data: {
        ProjectTaskID: ProjectTaskID,
        DaysToPush: DaysToPush
      },
      success: function(data) {
        pnotify('Task updated', 'success');
      }
    });
  }

  function addToKanBanTaskList() {
    var elementid = "<?php echo $ProjectTaskID ?>";
    var moduleid = "13";

    var url = "getdata.php?addToKanBanTaskList";

    var vData = {
      elementid: elementid,
      moduleid: moduleid
    };

    $.ajax({
      type: 'POST',
      url: url,
      data: vData,
      success: function(data) {
        obj = JSON.parse(data);
        for (var i = 0; i < obj.length; i++) {
          var Exists = obj[i].Exists;
          if (Exists === 'No') {
            var message = "<?php echo _("Project Task") . " " ?> <?php echo $ProjectTaskID ?> <?php echo " " . _("added to your task board") ?>";
            pnotify(message, "success");
          }
          if (Exists === 'Yes') {
            var message = "<?php echo _("Project Task") . " " ?> <?php echo $ProjectTaskID ?> <?php echo " " . _("allready added to your task board") ?>";
            pnotify(message, "info");
          }
        }
      }
    });
  }
  $(document).ready(function() {
    getITSMWorkFlows(13, <?php echo $ProjectTaskID ?>);
    getActiveModuleWorkFlow(13, <?php echo $ProjectTaskID ?>);
    getFiles(<?php echo $ProjectTaskID ?>, 13, 'TableProjectTaskFiles', '<?php echo $UserLanguageCode ?>');
  });
</script>

<div class="row">
  <div id="projecttaskitsmid" hidden><?php echo $ProjectTaskID; ?></div>
  <div id="projecttaskitsmtypeid" hidden><?php echo "13"; ?></div>
  <div id="projecttaskResponsible" hidden><?php echo $ProjectTaskResponsibleIDVal; ?></div>
  <div class="col-md-7 col-sm-7 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header"><i class="fa-solid fa-list-check"></i> <a href="projects_view.php?projectid=<?php echo $ProjectID ?>"><?php echo _("Project") ?></a> <a href="projects_tasks_view.php?projecttaskid=<?php echo $ProjectTaskID ?>"><i class="fa fa-angle-right fa-sm"></i> <i class="fa-solid fa-bars-progress"></i> <?php echo $ProjectTaskNameVal; ?></a>
          <div class="float-end">
            <ul class="navbar-nav justify-content-end">
              <li class="nav-item dropdown pe-2">
                <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                  &nbsp;&nbsp;<i class="fa-solid fa-ellipsis-vertical" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Actions") ?>"></i>&nbsp;&nbsp;
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="addITSMToKanban();">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Add to taskboard") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <?php $link = "projects_tasks_view.php?addtofavoritelist&projectid=$ProjectID&projecttaskid=$ProjectTaskID&projectname=$ProjectNameVal"; ?>
                    <a class="dropdown-item border-radius-md" onclick="window.location.href=('<?php echo $link ?>')">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Add as favorite") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="runModalEditProjectTask(<?php echo $ProjectTaskID ?>,<?php echo $ProjectID ?>);">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Edit") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="deleteProjectTask(<?php echo $ProjectTaskID ?>,<?php echo $ProjectID ?>);">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Delete") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2" data-bs-toggle="tooltip" data-bs-title="<?php echo _("This is used for pushing future tasks, including this, a number of days forward"); ?>">
                    <a class="dropdown-item border-radius-md" onclick="pushProjectTasksXAhead();">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Push tasks ahead") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
        <div class="card-body">
          <div class="progress">
            <div class="progress-bar bg-success" role="progressbar" aria-valuenow="<?php echo $ProjectTaskProgressVal; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $ProjectTaskProgressVal; ?>%;"></div>
          </div>
          <div class="container">
            <div class="row">
              <div class="col-md-3 col-sm-3 col-xs-12">
                <span class="name text-center"> <?php echo _('Estimated budget'); ?></span><br>
                <small><?php echo $ProjectTaskEstimatedBudgetVal; ?></small>
              </div>
              <div class="col-md-3 col-sm-3 col-xs-12">
                <span class="name"> <?php echo _('Amount spend'); ?> </span><br>
                <?php
                if ($ProjectTaskBudgetSpendVal > $ProjectTaskEstimatedBudgetVal) {
                  echo "<span class='badge bg-red'>" . $ProjectTaskBudgetSpendVal . "</span>";
                } else {
                  echo "<span class='badge bg-green'>" . $ProjectTaskBudgetSpendVal . "</span>";
                }
                ?>
              </div>
              <div class="col-md-3 col-sm-3 col-xs-12">
                <span class="name"> <?php echo _('Estimated Hours'); ?></span><br>
                <small><?php echo $ProjectTaskEstimatedHoursVal; ?></small>
              </div>
              <div class="col-md-3 col-sm-3 col-xs-12">
                <span class="name"> <?php echo _('Hours spend'); ?> </span><br>
                <?php
                $ProjectTaskHoursSpendVal = getProjectTaskTotalHoursSpend($ProjectTaskID);
                if ($ProjectTaskHoursSpendVal > $ProjectTaskEstimatedHoursVal) {
                  echo "<span class='badge bg-red'>" . $ProjectTaskHoursSpendVal . "</span>";
                } else {
                  echo "<span class='badge bg-green'>" . $ProjectTaskHoursSpendVal . "</span>";
                }
                ?>
              </div>
            </div>
          </div>
          <br>
          <br>
          <h6><?php echo _('Task activities'); ?>
            <button class="btn btn-sm btn-success float-end" type="button" onclick="runModalNewProjectActivity(<?php echo $ProjectTaskID; ?>)"><?php echo _("New activity"); ?></button>
          </h6>
          <br>
          <div id="accordionComments" role="tablist" aria-multiselectable='true'>

            <?php
            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            $CommentArray = array();
            ?>
            <?php while ($row = mysqli_fetch_array($result)) { ?>
              <?php
              $sql = "SELECT projects_tasks_conversations.ID AS ID, projects_tasks_conversations.RelatedProjectTaskID, projects_tasks_conversations.DateWritten, projects_tasks_conversations.Message, CONCAT(users.Firstname, ' ', users.Lastname) AS FullName
                      FROM projects_tasks_conversations
                      LEFT JOIN users ON projects_tasks_conversations.RelatedUserID = users.ID
                      WHERE projects_tasks_conversations.RelatedProjectTaskID = $ProjectTaskID
                      ORDER BY projects_tasks_conversations.DateWritten DESC;";
              $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
              while ($row = mysqli_fetch_array($result)) {
                if ($row[0] == "") {
                  echo "No Comments yet";
                }

                $CommentID = $row['ID'];
                $CommentUsersFullName = $row['FullName'];
                $CommentText = ltrim($row['Message']);
                $CommentDate = convertToDanishTimeFormat($row['DateWritten']);

                echo "<div class='card-group'><div class='card card-body-dropdown text-wrap'>
                        <a class='collapsed' data-bs-toggle='collapse' href='#collapse$CommentID' aria-expanded='true' aria-controls='collapse$CommentID'>
                          <p class='text-sm text-secondary mb-0 text-wrap'>" . $CommentDate . "<class='column float-end'> (" . $CommentUsersFullName . ")</p>
                        </a>
                        <div id='collapse$CommentID' class='accordion-collapse' role='tabpanel' aria-labelledby='heading$CommentID' data-parent='#accordionComments'>                          
                          <p id='text$CommentID' name='text$CommentID'>$CommentText</p>
                        </div>
                      </div>
                      </div>";
                $Comment = "text" . $CommentID;
                $CommentArray[] = $Comment;
              }
              ?>

          </div>
        </div>
      <?php } ?>
      <?php
      /*echo "
        <script>
          $(document).ready(function() {";
      foreach ($CommentArray as $item) {
        echo "$('#$item').trumbowyg({
                btns: 
                [
                    ['viewHTML'],
                    ['undo', 'redo'],
                    ['specialChars'],
                    ['foreColor', 'backColor'],
                    ['fullscreen']
                ],
                plugins: {
                    fontsize: {
                        sizeList: [
                            '14px',
                            '18px',
                            '22px',
                            '24px',
                            '26px',
                            '28px'
                        ],
                        allowCustomSize: false
                    },
                    lineheight: {
                        sizeList: [
                            '12px',
                            '14px',
                            '18px',
                            '22px'
                        ]
                    }
                }
            });$('#$item').trumbowyg('disable');";
      }
      echo "});
        </script>";*/
      ?>
      </div>
    </div>
  </div>
  <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header card-header-projects"><i class="fas fa-info-circle fa-2x"></i> <?php echo _("Details"); ?>
        </div>
        <div class="scrolling-menu-wrapper">
          <div class="arrow arrow-left">&#9664;</div>
          <div class="scrolling-menu">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#DetailsTab">
                  <?php echo _("Details"); ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#FilesTab">
                  <?php echo _("Files"); ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#PTWorkFlowTab">
                  <?php echo _("Workflows"); ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#RelationsTab">
                  <?php echo _("Relations"); ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#HistoryTab">
                  <?php echo _("History"); ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#TimeRegistrationsTab">
                  <?php echo _("Timeregistration"); ?>
                </a>
              </li>
            </ul>
          </div>
          <div class="arrow arrow-right">&#9654;</div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            </div>
            <!-- Tab panes -->
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <div class="tab-content">

                <div class="tab-pane active" id="DetailsTab">

                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="row">
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <p><b>
                            <h6><?php echo _('Task Description'); ?></h6>
                          </b>
                          <?php echo $ProjectTaskDescriptionVal; ?></p>
                      </div>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <p>
                        <h6><?php echo _('Task Responsible'); ?></h6>
                        <?php $Name = getProfilePopover($UserSessionID, $ProjectTaskResponsibleIDVal); ?>
                        <p><?php echo $Name ?></p>
                        </p>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="row">
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <p><b>
                            <h6><?php echo _('Task Status'); ?></h6>
                          </b>
                          <?php echo _($ProjectTaskStatusVal); ?>
                      </div>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <p><b>
                            <h6><?php echo _('Related category'); ?></h6>
                          </b>
                          <?php echo $CategoryName = getRelatedCategoryName($ProjectTaskRelatedCategory); ?></p>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="row">
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <p><b>
                            <h6><?php echo _('Task Started'); ?></h6>
                          </b>
                          <?php echo convertToDanishTimeFormat($ProjectTaskStartVal); ?></p>
                      </div>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <p><b>
                            <h6><?php echo _('Task Deadline'); ?></h6>
                          </b>
                          <?php echo convertToDanishTimeFormat($ProjectTaskDeadlineVal); ?></p>
                      </div>
                      <div class="col-md-6 col-sm-6 col-xs-12">
                        <?php
                        if (!empty($ProjectTaskCompletedDateVal)) {
                          echo "<p><b><h6>" ?><?php echo _('Project Task Completed'); ?><?php echo "</h6></b>"; ?>
                        <?php echo convertToDanishTimeFormat($ProjectTaskCompletedDateVal); ?><?php echo "</p>";
                                                                                            }
                                                                                              ?>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="row">
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <p><b>
                            <h6>
                              <?php
                              echo _('Task participants');
                              if ($RelatedManagerID == $UserSessionID) {
                                echo "&nbsp;<a href=\"javascript:runModalEditProjectTaskTeam($ProjectTaskID,$ProjectID);\"><i class=\"fa-solid fa-pen fa-xs\"></i></a>";
                              }
                              ?>
                            </h6>
                          </b>
                        <div class='row'>
                          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <div class="d-flex align-items-center justify-content-left">
                              <div class="avatar-group" id="teamavatargroup">
                                <script>
                                  var projecttaskid = '<?php echo $ProjectTaskID ?>';

                                  $.ajax({
                                    type: 'GET',
                                    url: './getdata.php?getProjectTaskTeamMembers',
                                    data: {
                                      projecttaskid: projecttaskid
                                    },
                                    success: function(data) {
                                      obj = JSON.parse(data);
                                      for (var i = 0; i < obj.length; i++) {
                                        var UserID = obj[i].UserID;
                                        if (UserID) {
                                          var FullName = obj[i].FullName;
                                          var Email = obj[i].Email;
                                          var ProfilePicture = obj[i].ProfilePicture;
                                          var dataBSTriggerContent = "<p class='text-center'><b>" + FullName + "</b><br>" + Email + "<br><br><p><img class='rounded-circle img-fluid' style='width: 150px;' src='./uploads/images/profilepictures/" + ProfilePicture + "'></p><p class = 'text-sm text-secondary mb-0 text-wrap'></a></p>";
                                          const el = document.createElement('a');
                                          profileurl = "javascript:runModalViewUnit(\"User\"," + UserID + ");";
                                          el.setAttribute('href', profileurl);
                                          el.setAttribute('data-bs-toggle', 'popover');
                                          el.setAttribute('data-bs-html', 'true');
                                          el.setAttribute('data-bs-trigger', 'hover');
                                          el.setAttribute('data-bs-content', dataBSTriggerContent);
                                          el.classList.add('avatar', 'avatar-sm', 'rounded-circle');
                                          el.innerHTML = "<img alt = 'Image placeholder 'src='./uploads/images/profilepictures/" + ProfilePicture + "' class='rounded-circle'>";
                                          const box = document.getElementById('teamavatargroup');
                                          box.appendChild(el);
                                        }
                                      }
                                    }
                                  });
                                </script>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane" id="TimeRegistrationsTab">
                  <table id="TableTimeRegistrations" class="table table-responsive table-borderless table-hover" cellspacing="0">
                    <thead>
                      <tr class='text-sm text-secondary mb-0'>
                        <th><?php echo _("User"); ?></th>
                        <th></th>
                        <th><?php echo _("Date performed"); ?></th>
                        <th><?php echo _("Time Registered"); ?></th>
                        <th><?php echo _("Description"); ?></th>
                      </tr>
                    </thead>
                    <?php
                    $sql = "SELECT time_registrations.DateWorked,time_registrations.TimeRegistered AS TimeRegistered, time_registrations.Description, users.Username, users.ID AS UserID
                                FROM time_registrations
                                LEFT JOIN taskslist ON taskslist.ID = time_registrations.RelatedTaskID
                                LEFT JOIN project_tasks ON taskslist.RelatedElementID = project_tasks.ID
                                LEFT JOIN users ON time_registrations.RelatedUserID = users.ID
                                WHERE project_tasks.ID = $ProjectTaskID AND taskslist.RelatedElementTypeID = 13";
                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    ?>
                    <tbody>
                      <?php while ($row = mysqli_fetch_array($result)) { ?>
                        <tr class='text-sm text-secondary mb-0'>
                          <?php $Username = $row['Username']; ?>
                          <?php $UsersID = $row['UserID']; ?>
                          <td><?php echo "<a href='user_profile.php?userid=$UsersID&sessionuserid=$UserSessionID'>$Username</a>" ?>
                          <td>
                          <td><?php echo convertToDanishTimeFormat($row['DateWorked']); ?></td>
                          <td><?php echo $row['TimeRegistered']; ?></td>
                          <td><?php echo $row['Description']; ?></td>
                        </tr>
                      <?php } ?>
                    </tbody>
                  </table>

                  <button class="btn btn-sm btn-dark float-end"><i class="fa fa-credit-card" onclick="CreateInvoice('<?php echo $ProjectTaskID; ?>')"></i> <?php echo _("Create Invoice"); ?></button>
                  <ul style="list-style-type:square;" id="invoicelist" name="invoicelist">
                  </ul>
                </div>
                <div class="tab-pane" id="FilesTab">
                  <form action="../functions/cifileupload.php?userid=<?php echo $_SESSION['id']; ?>&elementref=13&elementid=<?php echo $ProjectTaskID ?>&elementpath=projecttasks" class="dropzone" id="dropzoneform"></form>
                  <table id="TableProjectTaskFiles" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
                  </table>
                </div>

                <div class="tab-pane" id="PTWorkFlowTab">
                  <div class="row">
                    <a href="javascript:void(0);"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="collapse" data-bs-target="#collapseWFAdmin" aria-expanded="false" aria-controls="collapseWFAdmin"></i></a>
                    <div class="collapse" id="collapseWFAdmin">
                      <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                        <div class="input-group input-group-static mb-4">
                          <input class="form-control" autocomplete="off" list="ITSMPTWorkFlows" id="WorkflowInputPT" name="WorkflowInputPT" class="form-control" placeholder="<?php echo _("Select Workflow") ?>">
                          <datalist id="ITSMPTWorkFlows">
                          </datalist>
                        </div>
                      </div>
                      <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <div id="btnPTCreateWorkFlow"></div>
                      </div>
                      <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                        <div id="btnPTRemoveWorkFlow"></div>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <table id="TablePTWorkFlow" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
                    </table>
                  </div>
                </div>

                <div class="tab-pane" id="RelationsTab">

                </div>

                <div class="tab-pane" id="HistoryTab">
                  <table id="TableProjectTaskLog" class="table table-responsive table-borderless table-hover" cellspacing="0">
                    <thead>
                      <tr class='text-sm text-secondary mb-0'>
                        <th><?php echo _("Date"); ?></th>
                        <th><?php echo _("Value"); ?></th>
                        <th><?php echo _("From"); ?></th>
                        <th><?php echo _("To"); ?></th>
                        <th><?php echo _("User"); ?></th>
                      </tr>
                    </thead>
                    <?php
                    $sql = "SELECT project_tasks_audit_log.ID, project_tasks_audit_log.column_name, old_value, new_value, done_at, Users.Username AS Username
                          FROM project_tasks_audit_log
                          INNER JOIN Users ON project_tasks_audit_log.done_by = Users.ID 
                          WHERE project_tasks_audit_log.Projecttaskid = $ProjectTaskID 
                          ORDER BY done_at DESC";
                    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                    ?>
                    <tbody>
                      <?php while ($row = mysqli_fetch_array($result)) { ?>
                        <tr class='text-sm text-secondary mb-0'>
                          <td><?php $myFormatForView = convertToDanishTimeFormat($row['done_at']);
                              echo $myFormatForView; ?> </td>
                          <td><?php echo $row['column_name']; ?></td>
                          <td><?php echo $row['old_value']; ?></td>
                          <td><?php echo $row['new_value']; ?></td>
                          <td><?php echo $row['Username']; ?></td>
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
  <script>
    $(document).ready(function() {
      <?php initiateMediumViewTable("TableTimeRegistrations"); ?>
      <?php initiateMediumViewTable("Tablefileuploads"); ?>
      <?php initiateMediumViewTable("TableProjectTaskLog"); ?>
    });
  </script>
  <?php include("./modals/modal_projecttaskteam.php") ?>
  <?php include("./modals/modal_new_project_activity.php") ?>

  <?php include("./footer.php") ?>