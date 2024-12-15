<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array) || in_array("100008", $group_array) || in_array("100007", $group_array)) {
} else {
  $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
  notgranted($CurrentPage);
}

?>
<?php
$ProjectID = $_GET["projectid"];
$ProjectUsers = [];
$UserSessionID = $_SESSION['id'];
$today = new DateTime();
$today = $today->format('Y-m-d');

$TotalHoursSpend = getProjectTotalHoursSpend($ProjectID);
$TotalHoursEstimatedOnSpend = getProjectTotalHoursEstimatedOnSpend($ProjectID);
$TotalHoursEstimated = getProjectTotalHoursEstimated($ProjectID);

$ProjectUsers = getProjectParticipants($ProjectID);

if (in_array($UserSessionID, $ProjectUsers)) {
} else {
  $Message = _("You are not participant of this project");
  notgrantedPage("projects.php", $Message);
}

$sql = "SELECT projects.ID, projects.Name, projects.Start, projects.Status, projects.RelatedCompanyID, projects_statuscodes.StatusName AS StatusName, projects.Deadline, projects.Description, projects.EstimatedBudget, projects.BudgetSpend, (SELECT CONCAT(users.firstname,' ', users.lastname) FROM users WHERE users.ID = projects.ProjectResponsible)
        AS ProjectResponsibleName, users.firstname, users.lastname, companies.Companyname, projects.CompletedDate, projects.EstimatedHours, projects.HoursSpend, projects.ProjectManager, projects.ProjectResponsible
        FROM projects
        LEFT JOIN projects_statuscodes ON projects.Status = projects_statuscodes.ID
        LEFT JOIN companies ON projects.RelatedCompanyID = companies.ID
        LEFT JOIN users ON projects.ProjectManager = users.ID
        WHERE projects.ID = '$ProjectID'";
$result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
while ($row = mysqli_fetch_array($result)) {
  $ProjectNameVal = $row["Name"];
  $ProjectStartVal = $row["Start"];
  $ProjectStatusID = $row['Status'];
  $ProjectStatusVal = $row['StatusName'];
  $ProjectDeadlineVal = $row['Deadline'];
  $ProjectDescriptionVal = $row['Description'];
  $ProjectBudgetSpendVal = $row['BudgetSpend'];
  $ProjectEstimatedHoursVal = $row['EstimatedHours'];
  $ProjectHoursSpendVal = $row['HoursSpend'];
  $ProjectManagerVal = $row['firstname'] . " " . $row['lastname'];
  $ProjectManagerIDVal = $row['ProjectManager'];
  $ProjectCompanyIDVal = $row['RelatedCompanyID'];
  $ProjectCompanynameVal = $row['Companyname'];
  $ProjectResponsibleVal = $row['ProjectResponsibleName'];
  $ProjectResponsibleIDVal = $row['ProjectResponsible'];
  $ProjectCompletedDateVal = $row['CompletedDate'];
}

?>
<?php

if ($ProjectStatusID == '8' && !in_array("10", $group_array)) {
  echo "<script>
          alert('This project is archived! - please contact an administrator to get the project restored.');
          window.location.href='projects.php';
        </script>";
} else {
}
?>
<script src="./assets/js/plugins/fullcalendar.min.js"></script>
<script src="./assets/js/plugins/moment.min.js"></script>
<link rel="stylesheet" type="text/css" href="./assets/js/plugins/gantt-master/dist/frappe-gantt.css">
<script src="./assets/js/plugins/gantt-master/dist/frappe-gantt.min.js"></script>
<style>
  table.dataTable td,
  table.dataTable th {
    font-size: 0.90em;
  }
</style>
<script>
  let calendarInstance = null; // Global reference to the calendar instance

  function reloadCalendarObject() {
    const calendarEl = document.getElementById("calendar");

    // Initialize defaults
    let currentView = "dayGridMonth"; // Default view
    let currentDate = '<?php echo $today ?>'; // Default date

    if (calendarInstance) {
      // Safely retrieve current view and date
      currentView = calendarInstance.view ? calendarInstance.view.type : currentView;
      currentDate = calendarInstance.getDate ? calendarInstance.getDate() : currentDate;

      // Destroy the existing calendar instance to reinitialize
      calendarInstance.destroy();
    }

    // Initialize the calendar
    calendarInstance = new FullCalendar.Calendar(calendarEl, {
      locale: 'da',
      firstDay: 1,
      contentHeight: 'auto',
      initialView: currentView, // Use saved or default view
      initialDate: currentDate, // Use saved or default date
      weekNumbers: true,
      weekText: '',
      headerToolbar: {
        start: '',
        center: 'title',
        end: 'today prev,next,timeGridMonthly,timeGridWeekly'
      },
      buttonText: {
        today: '<?php echo _("today") ?>',
        month: '<?php echo _("month") ?>',
        week: '<?php echo _("week") ?>',
        day: '<?php echo _("day") ?>',
        list: '<?php echo _("list") ?>'
      },
      eventDidMount: function(info) {
        $(info.el).tooltip({
          title: info.event.title,
          placement: "top",
          trigger: "hover",
          container: "body"
        });
      },
      eventClick: function(info) {
        runModalEditProjectTask(info.event.id, <?php echo $ProjectID ?>);
      },
      eventDrop: function(info) {
        const tasktitle = info.event.title;
        const taskid = info.event.id;
        const startdate = moment(info.event.start).format('YYYY-MM-DD HH:mm');
        const enddate = moment(info.event.end).format('YYYY-MM-DD HH:mm');

        if (enddate === "Invalid date") {
          const message = "<?php echo _("Your tasks cannot have the exact same start and end date") ?>";
          pnotify(message, 'info');
          calendarInstance.render();
          return;
        }

        const vData = {
          taskid: taskid,
          startdate: startdate,
          enddate: enddate
        };

        $.ajax({
          type: 'GET',
          url: './getdata.php?updateProjectTaskDate',
          data: vData,
          success: function() {
            pnotify('Task moved', 'success');
          }
        });
        calendarInstance.render();
      },
      views: {
        timeGridWeekly: {
          type: 'timeGridWeek',
          buttonText: 'week'
        },
        timeGridMonthly: {
          type: 'dayGridMonth',
          buttonText: 'month'
        }
      },
      selectable: true,
      editable: true,
      events: []
    });

    // Fetch and add events to the calendar
    $.ajax({
      type: 'GET',
      url: './getdata.php?getProjectTasks',
      data: {
        projectid: projectid
      },
      success: function(data) {
        let obj;
        try {
          obj = JSON.parse(data);
        } catch (e) {
          return;
        }
        for (let i = 0; i < obj.length; i++) {
          const CalTaskID = obj[i].TaskID;
          const CalTitle = obj[i].TaskName;
          const CalStart = obj[i].CalStart;
          const CalEnd = obj[i].CalEnd;
          calendarInstance.addEvent({
            id: CalTaskID,
            title: CalTitle,
            start: CalStart,
            end: CalEnd
          });
        }
      }
    });

    calendarInstance.render();
  }


  function clearstate() {
    var table = $('#TableProjectTasks').DataTable();
    table.state.clear();
    setTimeout(function() {
      window.setTimeout(function() {
        window.location.reload(true);
      }, 200);
    }, 200);
  }

  $(document).ready(function() {
    var groupColumn = 9;

    var table = $('#TableProjectTasks').DataTable({
      "dom": 'Bfrtip',
      "searching": true,
      "filter": true,
      "paging": true,
      "info": false,
      "pagingType": 'numbers',
      "processing": true,
      "deferRender": true,
      "pageLength": 20,
      "orderCellsTop": true,
      "fixedHeader": false,
      "aaSorting": [],
      "responsive": true,
      "autoWidth": false,
      "ordering": false,
      "language": {
        url: './assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
      },
      "bLengthChange": false,
      "buttons": [{
        extend: 'excel',
        className: 'btn btn-sm btn-secondary'
      }, {
        extend: 'csv',
        className: 'btn btn-sm btn-secondary'
      }],
      "bStateSave": true,
      "iCookieDuration": 120,
      "order": [
        [groupColumn, "asc"]
      ],
      "displayLength": 20,
      "drawCallback": function(settings) {
        var api = this.api();
        var rows = api.rows({
          page: 'current'
        }).nodes();

        // Clear the tbody contents and unbind event handlers
        $('#TableProjectTasks tbody').empty().off();

        for (var i = 0; i < rows.length; i++) {
          var row = rows[i];
          var rowData = api.row(row).data();
          var taskId = rowData.TaskID.toString();

          // Add the id attribute to each row
          var escapedTaskId = taskId.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, '');
          row.setAttribute('id', escapedTaskId);
          row.setAttribute('draggable', 'true');
          row.classList.add('potential-parent-task');

          // Append the row to the tbody
          $('#TableProjectTasks tbody').append(row);
        }

        // Handle dragstart
        $('#TableProjectTasks tbody').on('dragstart', '.potential-parent-task', function(event) {
          var draggedTaskId = $(this).attr('id');
          event.originalEvent.dataTransfer.setData('text', draggedTaskId);
        });

        // Handle dragover event to allow dropping on potential-parent-task
        $('#TableProjectTasks tbody').on('dragover', '.potential-parent-task', function(event) {
          event.preventDefault();
          $(this).addClass('dragover');
        });

        // Handle dragleave event to remove the dragover class
        $('#TableProjectTasks tbody').on('dragleave', '.potential-parent-task', function(event) {
          event.preventDefault();
          $(this).removeClass('dragover');
        });

        // Handle drop event to handle the dropped row
        $('#TableProjectTasks tbody').on('drop', '.potential-parent-task', function(event) {
          activateSpinner();
          event.preventDefault();
          event.stopPropagation(); // Stop event propagation

          var draggedTaskId = event.originalEvent.dataTransfer.getData('text');
          var newParentTaskId = $(this).attr('id');

          // Make AJAX request to update the parentTask of the dragged task
          $.ajax({
            url: './getdata.php?setParentTaskIdOnTask',
            type: 'POST',
            data: {
              taskId: draggedTaskId,
              parentTaskId: newParentTaskId
            },
            success: function(response) {
              // Handle the response from the PHP script (e.g., display success message)
              pnotify("Child task created");
            },
            error: function(xhr, status, error) {
              // Handle AJAX error (e.g., display error message)
              console.error(error);
            },
            complete: function(response) {
              // Update the DataTable
              var table = $('#TableProjectTasks').DataTable();

              // Get the current page index
              var currentPageIndex = table.page();

              // Get the index of the dragged row
              var draggedRowIndex = table.row('#' + draggedTaskId).index();

              // Reload the data from the server
              table.ajax.reload(function() {
                // After reloading the data, switch to the page containing the dragged row
                table.page(Math.floor(draggedRowIndex / table.page.len())).draw('page');

                // Scroll to the dragged row
                var row = table.row(draggedRowIndex).node();
                if (row) {
                  row.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                  });

                  // Add a class to highlight the selected row
                  $(row).addClass('selectedRow');
                }
              });
              deactivateSpinner();
            }
          });
        });

        var sprintGroups = [];
        var currentSprint = '';

        api.column(groupColumn, {
          page: 'current'
        }).data().each(function(group, i) {
          var sprintname = group;
          var url = './getdata.php?getSprintIDFromName';
          var sprintLink = '<h6><a href=\"javascript:runModalCreateNewProjectSprint(<?php echo $ProjectID ?>,\'' + sprintname + '\');\">' + $('<div>').text(sprintname).html() + '</a></h6>';

          var parentTask = api.cell(i, 10).data();

          if (sprintname !== currentSprint) {
            sprintGroups.push({
              name: sprintname,
              link: sprintLink,
              rows: []
            });
            currentSprint = sprintname;
          }

          var sprintIndex = sprintGroups.length - 1;
          sprintGroups[sprintIndex].rows.push(rows[i]);
        });

        $('#TableProjectTasks tbody').empty();

        for (var i = 0; i < sprintGroups.length; i++) {
          var sprintGroup = sprintGroups[i];
          var sprintRows = sprintGroup.rows;

          $('#TableProjectTasks tbody').append(
            '<tr class="group parent"><td colspan="12">' + sprintGroup.link + '</td></tr>'
          );

          for (var j = 0; j < sprintRows.length; j++) {
            $('#TableProjectTasks tbody').append(sprintRows[j]);
          }
        }

        $('#TableProjectTasks tbody').on('click', 'tr.group.parent', function() {
          var currentOrder = table.order()[0];
          if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
            table.order([groupColumn, 'desc']).draw();
          } else {
            table.order([groupColumn, 'asc']).draw();
          }
        });
      },

      "fnStateSave": function(oSettings, oData) {
        localStorage.setItem('DataTables_' + window.location.pathname, JSON.stringify(oData));
      },
      "fnStateLoad": function(oSettings) {
        return JSON.parse(localStorage.getItem('DataTables_' + window.location.pathname));
      },
      ajax: {
        "url": "./getdata.php?getProjectTasks=1&projectid=<?php echo $ProjectID; ?>",
        "type": "GET",
        "dataSrc": "",
        error: function(data) {
          document.getElementById("TableProjectTasks_processing").hidden = true;
          return;
        }
      },
      "columnDefs": [{
          "targets": 0,
          "visible": false,
          "data": "TaskID"
        },
        {
          "targets": 1,
          "data": "RelatedTaskCategoryName",
          "render": function(data, type, row, meta) {
            let TaskCategoryDescription = row['RelatedTaskCategoryDescription'];
            let RelatedTaskCategoryName = row['RelatedTaskCategoryName'];
            let cleanText1 = RelatedTaskCategoryName ?
              TaskCategoryDescription.replace(/<\/?[^>]+(>|$)/g, "") :
              "none";

            return `<span data-bs-toggle="tooltip" data-bs-title="${cleanText1}">${RelatedTaskCategoryName || ""}</span>`;
          }
        },
        {
          "targets": 2,
          "data": null, // Placeholder column to prevent DataTables errors
          "render": function() {
            return ""; // Render as an empty column
          },
          "visible": false
        },
        {
          "targets": 3,
          "data": "Deadline",
          "createdCell": function(td, groupColumn) {
            $(td).attr("title", groupColumn);
          }
        },
        {
          "targets": 4,
          "data": "TaskName",
          "render": function(data, type, row, meta) {
            let indent = "&nbsp;".repeat(row['TaskDepth'] * 4);
            let descriptiontext = row['Description'] || "";
            let cleanText2 = descriptiontext.replace(/<\/?[^>]+(>|$)/g, "");
            let TaskName = row['TaskName'];

            let fieldtext2 = `<span data-bs-toggle="tooltip" data-bs-title="${cleanText2}">${indent}${TaskName}</span>`;
            let editLink = `<a href="javascript:runModalEditProjectTask(${row['TaskID']},${row['ProjectID']});" title="<?php echo _("View"); ?>"><i class="fa-solid fa-pen-to-square"></i></a>`;

            return `<a href="projects_tasks_view.php?projecttaskid=${row['TaskID']}">${fieldtext2}</a> ${editLink}`;
          }
        },
        {
          "targets": 5,
          "data": "Description",
          "visible": false
        },
        {
          "targets": 6,
          "data": "FullName"
        },
        {
          "targets": 7,
          "data": "StatusName",
          "render": function(data, type, row, meta) {
            let TaskDelayed = row['TaskDelayed'];
            let StatusName = row['StatusName'];
            let fieldtext3 = StatusName;
            if (TaskDelayed === "1") {
              fieldtext3 = StatusName + ' ' + '<i class="fa-solid fa-circle fa-xs" style="color:#D9001D" data-bs-toggle="tooltip" data-bs-title="<?php echo _("This task is delayed") ?>"></i>';
            }
            return fieldtext3;
          }
        },
        {
          "targets": 8,
          "data": "TaskProgress"
        },
        {
          "targets": 9,
          "visible": false,
          "searchable": true,
          "data": "SprintName"
        },
        {
          "targets": 10,
          "visible": false,
          "data": "ParentTask"
        }
      ]

    });

    // Enable droppable behavior for potential parent tasks
    $('.potential-parent-task').droppable({
      drop: function(event, ui) {
        var childTaskId = ui.draggable.attr('id');
        var potentialParentTaskId = $(this).attr('id');

        // Make AJAX request to update the parentTask value
        $.ajax({
          url: 'updateParentTask.php',
          type: 'POST',
          data: {
            childTaskId: childTaskId,
            potentialParentTaskId: potentialParentTaskId
          },
          success: function(response) {
            // Handle the response from the PHP script (e.g., display success message)
          },
          error: function(xhr, status, error) {
            // Handle AJAX error (e.g., display error message)
            console.error(error);
          }
        });
      }
    });

    $('#TableProjectTasks thead th').each(function() {
      var title = $(this).text();
      $(this).html('<input type=\"search\" class=\"form-control form-control-sm\" placeholder=\"' + title + '\"/>');
    });

    table.columns().every(function() {
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
  function updateProjectTicketRelation(ticketid) {
    projectid = "<?php echo $ProjectID ?>";
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?updateProjectTicketRelation=1&ticketid=" + ticketid + "&projectid=" + projectid, true);
    xmlhttp.send();
    setTimeout(function() {
      window.location.reload(true);
    }, 200);
  }

  function updateProjectChangeRelation(changeid) {
    projectid = "<?php echo $ProjectID ?>";
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?updateProjectChangeRelation=1&projectid=" + projectid + "&changeid=" + changeid, true);
    xmlhttp.send();
    setTimeout(function() {
      window.location.reload(true);
    }, 200);
  }

  function updateProjectProblemsRelation(problemid) {
    projectid = "<?php echo $ProjectID ?>";
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?updateProjectProblemsRelation=1&projectid=" + projectid + "&problemid=" + problemid, true);
    xmlhttp.send();
    setTimeout(function() {
      window.location.reload(true);
    }, 200);
  }

  function deleterelticket(relid) {
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?deleterelticketproject=" + relid, true);
    xmlhttp.send();
    setTimeout(function() {
      window.location.reload(true);
    }, 200);
  }

  function deleterelchange(relid) {
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?deleterelchangeproject=" + relid, true);
    xmlhttp.send();
    setTimeout(function() {
      window.location.reload(true);
    }, 200);
  }

  function deleterelproblem(relid) {
    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?deleterelproblemproject=" + relid, true);
    xmlhttp.send();
    setTimeout(function() {
      window.location.reload(true);
    }, 200);
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

  function archiveProject() {
    ProjectID = '<?php echo $ProjectID ?>';
    if (confirm("<?php echo _("Are you sure you want to archive the project? The project will unavailable and can only be restored by a project administrator"); ?>")) {
      if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
      } else {
        // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.open("GET", "getdata.php?archiveProject=" + ProjectID, true);
      xmlhttp.send();
      setTimeout(function() {
        window.location.href = ('projects.php');
      }, 200);
    }
  }

  function restoreToCurrentBaseline(UUID) {

    ProjectID = '<?php echo $ProjectID ?>';

    if (confirm("<?php echo _("Are you sure you want to restore this state? All existing data will be lost"); ?>")) {
      if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
      } else {
        // code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      }
      xmlhttp.open("GET", "getdata.php?restoreProjectBaseline=" + ProjectID + "&UUID=" + UUID, true);
      xmlhttp.send();
      setTimeout(function() {
        pnotify('Baseline restored', 'success');
      }, 200);
    }
  }

  function createProjetBaseline() {
    ProjectID = '<?php echo $ProjectID ?>';

    if (window.XMLHttpRequest) {
      // code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp = new XMLHttpRequest();
    } else {
      // code for IE6, IE5
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.open("GET", "getdata.php?createProjetBaseline=" + ProjectID, true);
    xmlhttp.send();
    setTimeout(function() {
      pnotify('Baseline created', 'success');
    }, 200);

  }

  function createICSFile() {
    ProjectID = '<?php echo $ProjectID ?>';
    $.ajax({
      type: 'GET',
      url: './getdata.php?createICSFromProject',
      data: {
        ProjectID: ProjectID
      },
      success: function(data) {
        obj = JSON.parse(data);
        for (var i = 0; i < obj.length; i++) {
          var filecontent = obj[i].filecontent;
          var hiddenElement = document.createElement('a');
          hiddenElement.href = 'data:attachment/text,' + encodeURIComponent(filecontent);
          hiddenElement.target = '_blank';
          hiddenElement.download = 'projecttasks.ics';
          hiddenElement.click();
        }

      }
    });
  }

  function pushProjectTasksXAhead() {

    const input = prompt("For how many days forward should the tasks be pushed forward?");

    ProjectID = '<?php echo $ProjectID ?>';
    $.ajax({
      type: 'GET',
      url: './getdata.php?createICSFromProject',
      data: {
        ProjectID: ProjectID
      },
      success: function(data) {
        obj = JSON.parse(data);
        for (var i = 0; i < obj.length; i++) {
          var filecontent = obj[i].filecontent;
          var hiddenElement = document.createElement('a');
          hiddenElement.href = 'data:attachment/text,' + encodeURIComponent(filecontent);
          hiddenElement.target = '_blank';
          hiddenElement.download = 'projecttasks.ics';
          hiddenElement.click();
        }

      }
    });
  }

  function addalltaskstotaskslists() {

    ProjectID = '<?php echo $ProjectID ?>';
    $.ajax({
      type: 'POST',
      url: './getdata.php?addallprojecttaskstotaskslists',
      data: {
        ProjectID: ProjectID
      },
      success: function(data) {
        obj = JSON.parse(data);
        for (var i = 0; i < obj.length; i++) {
          let ProjectTaskID = obj[i].ProjectTaskID;
          let Exists = obj[i].Exists;
          if (Exists === "Yes") {
            message = "Project tasks " + ProjectTaskID + " is allready created";
            pnotify(message, 'danger');
          }
          if (Exists === "No") {
            message = "Project tasks " + ProjectTaskID + " added to task board";
            pnotify(message, 'success');
          }
        }
      }
    });
  }
</script>
<?php
//Set important variables to work with
$ElementPath = "projects";
$ElementRef = "ProjectID";
$ElementGetValue = "projectid";
$RedirectPage = "projects_view.php?projectid=" . $ProjectID;
$RedirectPageUpload = "../projects_view.php?projectid=" . $ProjectID;

$ProjectTotalEstimatedHours = getProjectTotalEstimatedHours($ProjectID);
$ProjectCompletedEstimatedHours = getProjectCompletedEstimatedHours($ProjectID);
if ($ProjectCompletedEstimatedHours == 0) {
  $ProjectProgressHoursBased = 0;
} else {
  $ProjectProgressHoursBased = (($ProjectCompletedEstimatedHours) * 100) / $ProjectTotalEstimatedHours;
}

$ProjectTaskProgress = getProjectTaskProgress($ProjectID);
$NumberOfProjectTasks = getNumberOfProjectTasks($ProjectID);
$NumberOfProjectTasksCompleted = getNumberOfProjectTasksCompleted($ProjectID);
if ($NumberOfProjectTasks == 0) {
  $ProjectProgressTaskBased = 0;
} else {
  $ProjectProgressTaskBased = (($NumberOfProjectTasksCompleted) * 100) / $NumberOfProjectTasks;
}
$NumberOfProjectSprints = getNumberOfProjectSprints($ProjectID);

?>

<?php

if (isset($_GET['removealltasksfromtaskslists'])) {
  $sql = "SELECT project_tasks.ID AS ProjectTaskID, project_tasks.RelatedProject, project_tasks.TaskName, project_tasks.Description, project_tasks.Start, project_tasks.Deadline, project_tasks.Responsible, project_tasks.Status, project_tasks.EstimatedBudget, project_tasks.BudgetSpend, project_tasks.EstimatedHours, project_tasks.HoursSpend, project_tasks.Progress, project_tasks.CompletedDate, projects.Name AS ProjectName
          FROM project_tasks
          INNER JOIN projects ON project_tasks.RelatedProject = projects.ID
          WHERE project_tasks.RelatedProject = $ProjectID";
  $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
  while ($row = $result->fetch_assoc()) {
    $ProjectTaskID = $row["ProjectTaskID"];
    deleteProjectTaskFromTaskslist($ProjectTaskID);
  }
}

if (isset($_GET['addtofavoritelist'])) {
  $result = addProjectToFavoriteList($ProjectID, $UserSessionID, $ProjectNameVal);
  if ($result == "True") {
    echo "<script>pnotify('Project added to favorites','success')</script>";
  } else {
    echo "<script>pnotify('Project not added to favorites','danger')</script>";
  }
}

if (isset($_GET['addtowatchlist'])) {
  $result = addProjectToWatchList($ProjectID, $UserSessionID, $ProjectNameVal);
  if ($result == "True") {
    echo "<script>pnotify('Project $ProjectID added to watchlist','success')</script>";
  } else {
    echo "<script>pnotify('Project $ProjectID NOT added to watchlist','danger')</script>";
  }
}
?>
<script>
  $('document').ready(function() {
    var toggleTaskViewBtn = document.getElementById('toggleTaskViewBtn');
    var toggleCalendarBtn = document.getElementById('toggleCalendarBtn');
    toggleTaskViewBtn.style.display = 'none';
    toggleTaskViewBtn.removeAttribute("hidden");

  });

  function switchToCalendarView() {
    var DataTableBox = document.getElementById('ProjectDatatable');
    var CalendarBox = document.getElementById('calendarRow');
    var ganttView = document.getElementById('ganttView');
    var toggleTaskViewBtn = document.getElementById('toggleTaskViewBtn');
    var toggleCalendarBtn = document.getElementById('toggleCalendarBtn');

    if (DataTableBox.style.display === 'none') {
      DataTableBox.style.display = 'block';
      CalendarBox.style.display = 'none';
      ganttView.hidden = true;
      toggleCalendarBtn.style.display = 'block';
      toggleTaskViewBtn.style.display = 'none';
      $('#TableProjectTasks').DataTable().ajax.reload();
    } else {
      DataTableBox.style.display = 'none';
      CalendarBox.style.display = 'block';
      ganttView.hidden = true;
      toggleCalendarBtn.style.display = 'none';
      toggleTaskViewBtn.style.display = 'block';
      reloadCalendarObject();
    }

    projectid = '<?php echo $ProjectID ?>';

  };

  function switchToGanttView() {
    const ganttView = document.getElementById('ganttView');
    const DataTableBox = document.getElementById('ProjectDatatable');
    const CalendarBox = document.getElementById('calendarRow');
    const toggleTaskViewBtn = document.getElementById('toggleTaskViewBtn');
    const toggleCalendarBtn = document.getElementById('toggleCalendarBtn');

    ganttView.hidden = false;
    CalendarBox.style.display = 'none';
    DataTableBox.style.display = 'none';
    toggleCalendarBtn.style.display = 'block';
    toggleTaskViewBtn.style.display = 'none';

    projectid = '<?php echo $ProjectID ?>';

    getGanttView(projectid);

  };

  function getGanttView(projectid) {
    activateSpinner();
    $.ajax({
      type: 'GET',
      url: './getdata.php?getProjectTasks',
      data: {
        projectid: projectid
      },
      success: function(data) {
        if (!data || data.trim() === "") {
          pnotify('No data received', 'info');
          return;
        }
        var tasks = []; // Declare tasks array
        tasks.push({
          id: '0',
          name: '<?php echo $ProjectNameVal ?>',
          start: '<?php echo $ProjectStart ?>',
          end: '<?php echo $ProjectEnd ?>',
          progress: '<?php echo $ProjectProgressTaskBased ?>',
          dependencies: '',
          custom_class: 'task',
        });
        var milestoneTasks = []; // Declare milestones array
        var uniqueMilestoneTasks = []; // Declare uniqueMilestoneTasks array outside the function

        var obj;
        try {
          obj = JSON.parse(data);
        } catch (e) {
          return;
        }

        for (var i = 0; i < obj.length; i++) {
          var taskDependencies = ['0'];
          var TaskID = obj[i].TaskID.toString();
          var name = obj[i].TaskName;
          var ParentTask = obj[i].ParentTask;

          if (ParentTask) {
            ParentTask = ParentTask.toString();
            taskDependencies.push(ParentTask);
          }

          var start = obj[i].CalStart;
          var end = obj[i].CalEnd;
          // Convert start and end to Date objects
          var startDate = new Date(start.replace(' ', 'T'));
          var endDate = new Date(end.replace(' ', 'T'));

          // Compare dates
          if (startDate > endDate) {
            var urlToTask = "<a href='javascript: runModalEditProjectTask(" + TaskID + ", " + projectid + ");'><i class='fa-solid fa-pen fa-lg' title='<?php echo $functions->translate("Edit") ?>'></i></a>";
            var error = "<?php echo $functions->translate("Task") ?>: " + TaskID + " <?php echo $functions->translate("has end date before start date") ?><br><br>" + urlToTask;
            pnotify(error, 'danger');
          } else if (startDate.getTime() === endDate.getTime()) {
            var error = "<?php echo $functions->translate("Task") ?>: " + TaskID + " <?php echo $functions->translate("has the same end date and start date") ?><br><br>" + urlToTask;
            pnotify(error, 'danger');
          }
          var progress = obj[i].TaskProgress;
          var ParentTaskName = obj[i].ParentTaskName;
          var SprintID = obj[i].SprintID;
          var sprintname = obj[i].SprintName;
          var SprintDeadline = obj[i].SprintDeadline;

          // Check if the task is a milestone
          if (SprintID && sprintname !== "No Sprint") {
            milestoneTasks.push({
              id: 'milestone-' + SprintID,
              name: sprintname,
              start: SprintDeadline,
              end: SprintDeadline,
              progress: 100,
              dependencies: '',
              custom_class: 'milestone',
            });
          }

          tasks.push({
            id: TaskID,
            name: name,
            start: start,
            end: end,
            progress: progress,
            dependencies: taskDependencies,
          });
        }

        var uniqueSprintIDs = new Set();
        uniqueMilestoneTasks = milestoneTasks.filter(function(task) {
          if (!uniqueSprintIDs.has(task.id)) {
            uniqueSprintIDs.add(task.id);
            return true;
          }
          return false;
        });

        uniqueMilestoneTasks.forEach(function(task) {
          tasks.push({
            id: task.id,
            name: task.name,
            start: task.start,
            end: task.end,
            progress: task.progress,
            dependencies: task.dependencies,
            custom_class: 'milestone',
          });
        });

        renderGanttChart(tasks);
      },
      complete: function(data) {
        deactivateSpinner();
      },
      error: function(data) {
        deactivateSpinner();
      }

    });
  }

  function renderGanttChart(tasks) {
    // Clear the Gantt chart container
    var ganttContainer = document.getElementById("ganttContainer");
    if (ganttContainer) {
      ganttContainer.innerHTML = "";
    }
    // Create the Gantt chart
    var ganttChart = new Gantt("#ganttContainer", tasks, {
      header_height: 50,
      column_width: 30,
      step: 24,
      view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
      bar_height: 20,
      bar_corner_radius: 3,
      arrow_curve: 5,
      padding: 18,
      view_mode: 'Day',
      date_format: 'DD-MM-YYYY HH:mm',

      on_double_click: function(task) {
        if (task.custom_class !== 'milestone') {
          runModalEditProjectTask(task.id, <?php echo $ProjectID ?>);
        }
      },
      on_date_change: function(task, start, end) {
        var taskId = task.id;
        var start = moment(start).format('YYYY-MM-DD HH:mm');
        var end = moment(end).format('YYYY-MM-DD HH:mm');

        vData = {
          taskid: taskId,
          startdate: start,
          enddate: end
        };

        activateSpinner();
        $.ajax({
          type: 'GET',
          url: './getdata.php?updateProjectTaskDate',
          data: vData,
          success: function(data) {
            pnotify('Rescheduled', 'info');
          },
          complete: function(data) {
            deactivateSpinner();
          }
        });
      },
      on_progress_change: function(task, progress) {
        var taskId = task.id;
        vData = {
          projecttaskid: taskId,
          updateProjectTaskProgress: progress,
        };
        $.ajax({
          type: 'GET',
          url: './getdata.php?updateProjectTaskProgress',
          data: vData,
          success: function(data) {
            deactivateSpinner();
            pnotify('<?php echo $functions->translate("Progress updated") ?>', 'info');
          },
          complete: function(data) {

          }
        });
      },
      on_view_change: function(mode) {},
      custom_popup_html: function(task) {
        var startDate = moment(task._start).format('DD-MM-YYYY HH:mm');
        var endDate = moment(task._end).format('DD-MM-YYYY HH:mm');

        if (task.custom_class === 'milestone') {
          return `
      <div class="details-container">
        <h6><?php echo $functions->translate("Milestone") ?></h6><br>
        <small><?php echo $functions->translate("Deadline") ?>: ${endDate}</small><br>
      </div>
    `;
        } else {
          return `
      <div class="details-container">
        <p><a href="javascript:runModalEditProjectTask(${task.id},<?php echo $ProjectID ?>);"><i class="fa-solid fa-pen fa-lg" title='<?php echo $functions->translate("Edit") ?>'></i></a></p>
        <small><?php echo $functions->translate("Start") ?>: ${startDate}</small><br>
        <small><?php echo $functions->translate("Deadline") ?>: ${endDate}</small><br>
        <br>
        <small>${task.progress}% <?php echo $functions->translate("progress") ?></small>
      </div>
    `;
        }
      },
    });

    var viewWeek = document.getElementById('weekViewButton');
    if (viewWeek) {
      viewWeek.addEventListener('click', function() {
        ganttChart.change_view_mode('Week')
      });
    }

    var viewMonth = document.getElementById('monthViewButton');
    if (viewMonth) {
      viewMonth.addEventListener('click', function() {
        ganttChart.change_view_mode('Month')
      });
    }

    var viewDay = document.getElementById('dayViewButton');
    if (viewDay) {
      viewDay.addEventListener('click', function() {
        ganttChart.change_view_mode('Day')
      });
    }

    var hfDay = document.getElementById('HalfdayViewButton');
    if (hfDay) {
      hfDay.addEventListener('click', function() {
        ganttChart.change_view_mode('Half Day')
      });
    }

    document.getElementById('currentDateButton').addEventListener('click', goToCurrentDate);

    function goToCurrentDate() {
      ganttChart.change_view_mode('Day')
      var container = document.querySelector('.gantt-container');
      var currentDateMarker = container.querySelector('.today-highlight');

      if (currentDateMarker) {
        var containerWidth = container.offsetWidth;
        var markerWidth = currentDateMarker.getAttribute('width');
        var markerOffset = currentDateMarker.getAttribute('x');
        var scrollOffset = markerOffset - containerWidth / 2 + markerWidth / 2;
        container.scrollLeft = scrollOffset;
      }
    }
  }

  function getTasksForDateRange(startDate, endDate) {
    // Retrieve tasks from your data source for the specified date range
    // Example implementation:
    // - Filter tasks based on their start and end dates falling within the specified range
    // - Return the filtered tasks as an array

    // Placeholder example
    var allTasks = []; // Replace with your actual tasks data
    var filteredTasks = allTasks.filter(function(task) {
      var taskStartDate = moment(task.start_date, "DD-MM-YYYY HH:mm");
      var taskEndDate = moment(task.end_date, "DD-MM-YYYY HH:mm");

      if (!taskStartDate.isValid() || !taskEndDate.isValid()) {
        return false;
      }

      return taskStartDate.isBetween(startDate, endDate, null, '[]') || taskEndDate.isBetween(startDate, endDate, null, '[]');
    });

    return filteredTasks;
  }


  function addToKanBanTaskList() {
    activateSpinner();
    var elementid = "<?php echo $ProjectID ?>";
    var moduleid = "6";

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
            var message = "<?php echo _("Project") . " " ?> <?php echo $ProjectID ?> <?php echo " " . _("added to your task board") ?>";
            pnotify(message, "success");
          }
          if (Exists === 'Yes') {
            var message = "<?php echo _("Project") . " " ?> <?php echo $ProjectID ?> <?php echo " " . _("allready added to your task board") ?>";
            pnotify(message, "info");
          }
        }
      },
      complete: function(data) {
        deactivateSpinner();
      },
    });
  }

  $(document).ready(function() {
    getFiles(<?php echo $ProjectID ?>, 6, 'TableProjectFiles', '<?php echo $UserLanguageCode ?>');
    getITSMWorkFlows(6, <?php echo $ProjectID ?>);
    getActiveModuleWorkFlow(6, <?php echo $ProjectID ?>);
  });
</script>
<div class="row">
  <div id="projectitsmid" hidden><?php echo $ProjectID; ?></div>
  <div id="projectitsmtypeid" hidden><?php echo "6"; ?></div>
  <div id="projectResponsible" hidden><?php echo $ProjectManagerIDVal; ?></div>
  <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header" id="cardheaderforarchivestatus"><i class="fa-solid fa-list-check"></i> <a class="whiteheader" href="projects.php"> <?php echo _("Projects"); ?></a> <i class="fa fa-angle-right fa-sm"></i> <a class="whiteheader" href="projects_view.php?projectid=<?php echo $ProjectID; ?>"> <?php echo _($ProjectNameVal); ?></a>
          <div class="float-end">
            <ul class="navbar-nav justify-content-end">
              <li class="nav-item dropdown pe-2">
                <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                  &nbsp;&nbsp;<i class="fa-solid fa-circle-chevron-down" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Create") ?>"></i>&nbsp;&nbsp;
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="runModalCreateNewProject();">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Create project") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href=('projects.php')">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Open projects") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href=('projects_finished.php')">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Closed projects") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href=('projects_all.php')">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("All projects") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href=('projects_search.php')">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Search projects") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
          <div class="float-end" id="optionsmenu">
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
                    <a class="dropdown-item border-radius-md" onclick="runModalEditProject('<?php echo $ProjectID ?>');">
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
                    <a class="dropdown-item border-radius-md" onclick="runModalCreateNewProjectSprint(<?php echo $ProjectID ?>)">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Project Sprint") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="runModalCreateNewProjectTaskCategory()">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Project Task Categories") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <?php $link = "projects_view.php?addalltaskstotaskslists&projectid=$ProjectID"; ?>
                    <a class="dropdown-item border-radius-md" onclick="addalltaskstotaskslists();">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Create all project tasks on all task responsibles taskboards"); ?>">
                            <?php echo _("Generate project tasks") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <?php $link = "projects_view.php?removealltasksfromtaskslists&projectid=$ProjectID"; ?>
                    <a class="dropdown-item border-radius-md" onclick="window.location.href=('<?php echo $link ?>')">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Remove all project tasks on all task responsibles taskboards"); ?>">
                            <?php echo _("Remove project tasks") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2" data-bs-toggle="tooltip" data-bs-title="Create new project baseline">
                    <a class="dropdown-item border-radius-md" onclick="createProjetBaseline();">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Create baseline") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2" data-bs-toggle="tooltip" data-bs-title="<?php echo _("This will export project tasks to a ics file that you can import to your calendars"); ?>">
                    <a class="dropdown-item border-radius-md" onclick="createICSFile();">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Create ics file") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2" data-bs-toggle="tooltip" data-bs-title="<?php echo _("This will archive the project and remove it from all views and search indexes"); ?>">
                    <a class="dropdown-item border-radius-md" onclick="archiveProject();">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Archive project") ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
          <div class='float-end' id="toggleCalendarBtn">
            <ul class='navbar-nav justify-content-end'>
              <li class='nav-item dropdown pe-2'>
                <a href='javascript:void(0);' class='nav-link text-body p-0 position-relative' onclick="switchToCalendarView()" aria-expanded='false'>
                  &nbsp;&nbsp;<i class='fa-solid fa-calendar' data-bs-toggle="tooltip" data-bs-title="<?php echo _("Toggle calendar view") ?>"></i>&nbsp;&nbsp;
                </a>
              </li>
            </ul>
          </div>
          <div class="float-end" id="toggleTaskViewBtn" hidden>
            <ul class='navbar-nav justify-content-end'>
              <li class='nav-item dropdown pe-2'>
                <a href='javascript:void(0);' class='nav-link text-body p-0 position-relative' onclick="switchToCalendarView()" aria-expanded='false'>
                  &nbsp;&nbsp;<i class="fa-solid fa-list-check" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Toggle task view") ?>"></i>&nbsp;&nbsp;
                </a>
              </li>
            </ul>
          </div>
          <div class="float-end" id="toggleGanttViewBtn">
            <ul class='navbar-nav justify-content-end'>
              <li class='nav-item dropdown pe-2'>
                <a href='javascript:void(0);' class='nav-link text-body p-0 position-relative' onclick="switchToGanttView()" aria-expanded='false'>
                  &nbsp;&nbsp;<i class="fa-solid fa-chart-gantt" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Toggle Gantt view") ?>"></i>&nbsp;&nbsp;
                </a>
              </li>
            </ul>
          </div>
          <div class="float-end">
            <ul class="navbar-nav justify-content-end">
              <li class="nav-item dropdown pe-2">
                <a href="javascript:void(0);" onclick="runModalCreateProjectTask(<?php echo $ProjectID ?>,<?php echo $_SESSION['id'] ?>)" class="nav-link text-body p-0 position-relative" aria-expanded="false">
                  &nbsp;&nbsp;<i class="far far-dark fa-plus-square"></i>&nbsp;&nbsp;
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div class="card-body">
          <div class="col-xl-12" id="calendarRow">
            <div class="card card-calendar">
              <div class="calendar" data-bs-toggle="calendar" id="calendar"></div>
            </div>
          </div>
          <div class="col-xl-12" id="ganttView" hidden>
            <div>
              <button id="HalfdayViewButton" class="btn btn-secondary btn-sm"><?php echo _("½ Day") ?></button>
              <button id="dayViewButton" class="btn btn-secondary btn-sm"><?php echo _("Day") ?></button>
              <button id="weekViewButton" class="btn btn-secondary btn-sm"><?php echo _("Week") ?></button>
              <button id="monthViewButton" class="btn btn-secondary btn-sm"><?php echo _("Month") ?></button>
              <button id="currentDateButton" class="btn btn-secondary btn-sm"><?php echo _("Today") ?></button>
            </div>
            <div id="ganttContainer"></div>
          </div>
          <div class="row" id="ProjectDatatable">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <h6><?php echo _('Project Tasks'); ?></h6>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
              <table id="TableProjectTasks" class="table table-borderless table-responsive table-hover nowrap" width="100%">
                <thead>
                  <tr>
                    <th><?php echo _("ID"); ?></th>
                    <th></th>
                    <th class="all"></th>
                    <th><?php echo _("Deadline"); ?></th>
                    <th class="all"><?php echo _("Name"); ?></th>
                    <th><?php echo _("Description"); ?></th>
                    <th><?php echo _("Responsible"); ?></th>
                    <th><?php echo _("Status"); ?></th>
                    <th><?php echo _("Progress"); ?></th>
                    <th><?php echo _("Sprint"); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header card-header"><i class="fas fa-info-circle fa-lg"></i> <?php echo _("Details"); ?>
        </div>
        <div class="scrolling-menu-wrapper">
          <div class="arrow arrow-left">&#9664;</div>
          <div class="scrolling-menu">
            <ul class="nav nav-tabs" id="activetablist" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#DetailsTab">
                  <?php echo _("Details"); ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#ActivityTab">
                  <?php echo _("Activity"); ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#PWorkFlowTab">
                  <?php echo _("Workflows"); ?>
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#FilesTab">
                  <?php echo _("Files"); ?>
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
            </ul>
          </div>
          <div class="arrow arrow-right">&#9654;</div>
        </div>
        <div id="TabSection" class='card'>
          <div class="card-body">
            <div class="tab-content">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="tab-content">
                  <div class="tab-pane active" id="DetailsTab">
                    <div class="row">
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <div class="row">
                          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <p class='text-sm text-secondary mb-0' data-bs-toggle="tooltip" data-bs-title="<?php echo _("Total hours estimated and completed * 100 / Total hours estimated") ?>"><small><?php echo _("Estimated hours completed") . ":" ?> <?php echo ROUND($ProjectProgressHoursBased, 0) . "%"; ?></small></p>
                            <div class="progress" data-bs-toggle="tooltip" data-bs-title="<?php echo ROUND($ProjectProgressHoursBased, 0); ?> %">
                              <div class="progress-bar bg-success" role="progressbar" aria-valuenow="<?php echo $ProjectProgressHoursBased; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $ProjectProgressHoursBased; ?>%">
                              </div>
                            </div>
                          </div>
                          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <p class='text-sm text-secondary mb-0' data-bs-toggle="tooltip" data-bs-title="<?php echo _("Total tasks completed * 100 / Tasks") ?>"><small><?php echo _("Tasks completed") . ":" ?> <?php echo ROUND($ProjectProgressTaskBased, 0) . "%"; ?></small></p>
                            <div class="progress" data-bs-toggle="tooltip" data-bs-title="<?php echo ROUND($ProjectProgressTaskBased, 0); ?> %">
                              <div class="progress-bar bg-success" role="progressbar" aria-valuenow="<?php echo $ProjectProgressTaskBased; ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $ProjectProgressTaskBased; ?>%">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <p>
                        <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                          <?php echo _("Statistics") ?>
                        </a>
                      </p>
                      <div class="collapse" id="collapseExample">
                        <div class="card card-body">
                          <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                              <?php

                              $TotalHoursSpend = getProjectTotalHoursSpend($ProjectID);
                              if (empty($TotalHoursSpend)) {
                                $TotalHoursSpend = 0;
                              }

                              $TotalHoursEstimatedOnSpend = getProjectTotalHoursEstimatedOnSpendTasks($ProjectID);
                              if (empty($TotalHoursEstimatedOnSpend)) {
                                $TotalHoursEstimatedOnSpend = 0;
                              }

                              $TotalHoursEstimated = getProjectTotalHoursEstimated($ProjectID);
                              if (empty($TotalHoursEstimated)) {
                                $TotalHoursEstimated = 0;
                              }

                              ?>

                              <?php

                              if ($TotalHoursSpend !== 0 && $TotalHoursEstimated !== 0) {

                                $HoursSpendProcent = ($TotalHoursSpend * 100) / $TotalHoursEstimated;
                              } else {
                                $HoursSpendProcent = 0;
                              }

                              if ($HoursSpendProcent !== 0 && $TotalHoursEstimated !== 0) {
                                $HoursSpendEstimatedProcent = ($TotalHoursEstimatedOnSpend - $HoursSpendProcent) * 100 / $TotalHoursEstimated;
                              } else {
                                $HoursSpendEstimatedProcent = 0;
                              }

                              if ($HoursSpendEstimatedProcent !== 0 && $TotalHoursEstimated !== 0) {
                                $HoursEstimatedProcent = (($TotalHoursEstimated - $HoursSpendEstimatedProcent - $HoursSpendProcent) * 100 / $TotalHoursEstimated);
                              } else {
                                $HoursEstimatedProcent = 0;
                              }

                              ?>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-3 col-sm-3 col-xs-6">
                              <span class="text-sm text-secondary mb-0 text-center"> <?php echo _('Estimated budget'); ?></span><br>
                              <small><?php
                                      $ProjectEstimatedBudgetVal = getProjectTotalAmountEstimated($ProjectID);
                                      if (empty($ProjectEstimatedBudgetVal)) {
                                        $ProjectEstimatedBudgetVal = 0;
                                      }
                                      echo $ProjectEstimatedBudgetVal;
                                      ?>
                              </small>
                            </div>
                            <div class="col-md-3 col-sm-3 col-xs-6">
                              <span class='text-sm text-secondary mb-0'> <?php echo _('Amount spend'); ?> </span><br>
                              <?php
                              $ProjectBudgetSpendVal = getProjectTotalAmountSpend($ProjectID);
                              if (empty($ProjectBudgetSpendVal)) {
                                $ProjectBudgetSpendVal = 0;
                              }
                              if ($ProjectBudgetSpendVal > $ProjectEstimatedBudgetVal) {
                                echo "<span class='badge badge-pill bg-gradient-danger' data-bs-toggle='tooltip' data-placement='top'>$ProjectBudgetSpendVal</span></a>";
                              } else {
                                echo "<span class='badge badge-pill bg-gradient-success' data-bs-toggle='tooltip' data-placement='top'>$ProjectBudgetSpendVal</span></a>";
                              }
                              ?>
                            </div>
                            <div class="col-md-3 col-sm-3 col-xs-6">
                              <span class='text-sm text-secondary mb-0'> <?php echo _('Estimated Hours'); ?></span><br>
                              <small><?php
                                      $ProjectEstimatedHoursVal = getProjectTotalHoursEstimated($ProjectID);
                                      if (empty($ProjectEstimatedHoursVal)) {
                                        $ProjectEstimatedHoursVal = 0;
                                      }
                                      echo $ProjectEstimatedHoursVal;
                                      ?>
                              </small>
                            </div>
                            <div class="col-md-3 col-sm-3 col-xs-6">
                              <span class="text-sm text-secondary mb-0 text-center"> <?php echo _('Estimated Hours Completed'); ?></span><br>
                              <small><?php
                                      if ($ProjectCompletedEstimatedHours == "") {
                                        $ProjectCompletedEstimatedHours = "0";
                                      }
                                      echo $ProjectCompletedEstimatedHours;
                                      ?>
                              </small>
                            </div>
                          </div>
                          <div class="row">
                            <div class="col-md-3 col-sm-3 col-xs-6">
                              <span class='text-sm text-secondary mb-0' data-bs-toggle="tooltip" data-bs-title="<?php echo "Calculated from real timeregistrations" ?>"> <?php echo _('Real hours spend'); ?> </span><br>
                              <?php

                              if ($TotalHoursSpend > $ProjectEstimatedHoursVal) {
                                echo "<span class='badge badge-pill bg-gradient-danger' data-bs-toggle='tooltip' data-placement='top'>$TotalHoursSpend</span></a>";
                              } else {
                                echo "<span class='badge badge-pill bg-gradient-success' data-bs-toggle='tooltip' data-placement='top'>$TotalHoursSpend</span></a>";
                              }
                              ?>
                            </div>
                            <div class="col-md-3 col-sm-3 col-xs-6">
                              <span class="text-sm text-secondary mb-0 text-center"> <?php echo _('Number Of Project Tasks'); ?></span><br>
                              <small><?php
                                      if ($NumberOfProjectTasks == "") {
                                        $NumberOfProjectTasks = "0";
                                      }
                                      echo $NumberOfProjectTasks;
                                      ?>
                              </small>
                            </div>
                            <div class="col-md-3 col-sm-3 col-xs-6">
                              <span class='text-sm text-secondary mb-0'> <?php echo _('Project Tasks Completed'); ?></span><br>
                              <small><?php
                                      if (empty($NumberOfProjectTasksCompleted)) {
                                        $NumberOfProjectTasksCompleted = 0;
                                      }
                                      echo $NumberOfProjectTasksCompleted;
                                      ?>
                              </small>
                            </div>
                            <div class="col-md-3 col-sm-3 col-xs-6">
                              <span class='text-sm text-secondary mb-0'> <?php echo _('Number Of Sprints'); ?></span><br>
                              <small><?php
                                      if (empty($NumberOfProjectSprints)) {
                                        $NumberOfProjectSprints = 0;
                                      }
                                      echo $NumberOfProjectSprints;
                                      ?>
                              </small>
                            </div>
                          </div>
                        </div>
                        <br><br>
                      </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <h6><?php echo _('Project Description'); ?></h6>
                          <div class="input-group input-group-static mb-4">
                            <?php echo $ProjectDescriptionVal; ?>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <h6><?php echo _('Company'); ?></h6>
                          <p><a href="javascript:runModalViewUnit('Company',<?php echo $ProjectCompanyIDVal; ?>);"><?php echo $ProjectCompanynameVal; ?></a></p>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <h6><?php echo _('Status'); ?></h6>
                          <div class="input-group input-group-static mb-4">
                            <?php echo _($ProjectStatusVal) ?>
                          </div>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <h6><?php echo _('Project Deadline'); ?></h6>
                          <div class="input-group input-group-static mb-4">
                            <?php
                            if ($ProjectDeadlineVal < date('Y-m-d H:i:s')) {
                              echo "<div class='badge badge-pill bg-gradient-danger'>" . convertToDanishTimeFormat($ProjectDeadlineVal) . "</div>";
                            } else {
                              echo "<div class='badge badge-pill bg-gradient-success'>" . convertToDanishTimeFormat($ProjectDeadlineVal) . "</div>";
                            }
                            ?>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="row">
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <h6><?php echo _('Project Manager'); ?></h6>
                          <?php $Name = getProfilePopover($UserSessionID, $ProjectManagerIDVal); ?>
                          <p><?php echo $Name ?></p>
                        </div>
                        <div class="col-md-6 col-sm-6 col-xs-12" data-bs-toggle="tooltip" data-bs-title="<?php echo _("This is the economically responsible manager for the project") ?>">
                          <h6><?php echo _('Project Responsible'); ?></h6>
                          <?php $Name = getProfilePopover($UserSessionID, $ProjectResponsibleIDVal); ?>
                          <p><?php echo $Name ?></p>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                          <h6><?php echo _('Project participants'); ?> <?php if ($ProjectManagerIDVal == $UserSessionID) {
                                                                          echo "<a href=\"javascript:runModalEditProjectTeam($ProjectID);\"><i class=\"fa-solid fa-pen fa-xs\"></i></a>";
                                                                        } ?></h6>
                          <div class='row'>
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                              <div class="d-flex align-items-center justify-content-left">
                                <div class="avatar-group" id="teamavatargroup">
                                  <script>
                                    var projectid = '<?php echo $ProjectID ?>';

                                    $.ajax({
                                      type: 'GET',
                                      url: './getdata.php?getProjectTeamMembers',
                                      data: {
                                        projectid: projectid
                                      },
                                      success: function(data) {
                                        obj = JSON.parse(data);
                                        if (obj) {
                                          for (var i = 0; i < obj.length; i++) {
                                            var UserID = obj[i].UserID;
                                            if (UserID) {
                                              var FullName = obj[i].FullName;
                                              var Email = obj[i].Email;
                                              var ProfilePicture = obj[i].ProfilePicture;
                                              var dataBSTriggerContent = "<p class='text-center'><b>" + FullName + "</b><br>" + Email + "<br><br><p><img class='rounded-circle img-fluid' style='width: 150px;' src='./uploads/images/profilepictures/" + ProfilePicture + "'></p><p class = 'text-sm text-secondary mb-0 text-wrap'></a></p>";
                                              const el = document.createElement('a');
                                              profileurl = "javascript:runModalViewUnit(\"User\"," + UserID + ");";
                                              //profileurl = "javascript:runModalViewUnit(\"User\",$UsersID);" + UserID;
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
                  <div class="tab-pane" id="PWorkFlowTab">
                    <div class="row">
                      <a href="javascript:void(0);"><i class="fa-solid fa-circle-chevron-down float-right" data-bs-toggle="collapse" data-bs-target="#collapseWFAdmin" aria-expanded="false" aria-controls="collapseWFAdmin"></i></a>
                      <div class="collapse" id="collapseWFAdmin">
                        <div class="card">
                          <div class="card-body">
                            <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
                              <div class="input-group input-group-static mb-4">
                                <input class="form-control" autocomplete="off" list="ITSMPWorkFlows" id="WorkflowInputP" name="WorkflowInputP" class="form-control" placeholder="<?php echo _("Select Workflow") ?>">
                                <datalist id="ITSMPWorkFlows">
                                </datalist>
                              </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                              <div id="btnPCreateWorkFlow"></div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                              <div id="btnPRemoveWorkFlow"></div>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <table id="TablePWorkFlow" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="tab-pane" id="RelationsTab">
                  </div>

                  <div class="tab-pane" id="ActivityTab">
                    <h6><?php echo _("Latest activity") ?></h6>
                    <table id="ProjectActivity" class="table table-borderless compact" cellspacing="0">
                      <thead>
                        <tr>
                          <th></th>
                        </tr>
                      </thead>
                      <?php
                      $sql = "SELECT Message, DateWritten, CONCAT(users.Firstname,' ',users.Lastname,' (',users.Username,')') AS FullName, project_tasks.ID AS TaskID
                              FROM projects_tasks_conversations
                              LEFT JOIN project_tasks ON project_tasks.ID = projects_tasks_conversations.RelatedProjectTaskID
                              LEFT JOIN projects ON project_tasks.RelatedProject = projects.ID
                              LEFT JOIN users ON projects_tasks_conversations.RelatedUserID = users.ID
                              WHERE project_tasks.RelatedProject = $ProjectID
                              ORDER BY projects_tasks_conversations.DateWritten DESC
                              LIMIT 100;";
                      $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                      ?>
                      <tbody>
                        <?php while ($row = mysqli_fetch_array($result)) { ?>
                          <tr>
                            <td>
                              <div class="card card-body-dropdown text-wrap">
                                <div class="row g-md-3">
                                  <div class="col-md-12 col-sm-12 col-xs-12">

                                    <i class="fa-solid fa-comment"></i>
                                    <a href="projects_tasks_view.php?projectid=<?php echo $ProjectID . "&projecttaskid=" . $row['TaskID']; ?>">
                                      <small class="float-left"><?php echo $myFormatForView = convertToDanishTimeFormat($row['DateWritten']) ?></small> <small class="float-end"><?php echo $row['FullName']; ?></small>
                                    </a>
                                    <small><?php echo $row['Message']; ?></small>
                                  </div>

                                </div>
                              </div>
                            </td>
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                  <div class="tab-pane" id="FilesTab">
                    <form action="../functions/cifileupload.php?userid=<?php echo $_SESSION['id']; ?>&elementref=6&elementid=<?php echo $ProjectID ?>&elementpath=projects" class="dropzone" id="dropzoneform"></form>
                    <div class="card">
                      <div class="card-body">
                        <table id="TableProjectFiles" class="col-md-12 col-sm-12 col-xs-12" class="table table-responsive table-borderless table-hover" cellspacing="0">
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="tab-pane" id="HistoryTab">
                    <table id="TableProjectLog" class="table table-responsive table-borderless table-hover" cellspacing="0">
                      <thead>
                        <tr>
                          <th><?php echo _("Date"); ?></th>
                          <th><?php echo _("Value"); ?></th>
                          <th><?php echo _("From"); ?></th>
                          <th><?php echo _("To"); ?></th>
                          <th><?php echo _("User"); ?></th>
                        </tr>
                      </thead>
                      <?php
                      $sql = "SELECT projects_audit_log.id, projects_audit_log.Projectid, projects_audit_log.column_name, projects_audit_log.old_value, projects_audit_log.new_value, projects_audit_log.done_at, users.Username
                              FROM projects_audit_log
                              LEFT JOIN users ON projects_audit_log.done_by = users.ID
                              WHERE projects_audit_log.Projectid = $ProjectID 
                              ORDER BY done_at DESC";
                      $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                      ?>
                      <tbody>
                        <?php while ($row = mysqli_fetch_array($result)) { ?>
                          <tr>
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

                  <div class="tab-pane" id="BaselineTab">
                    <table id="TableBaseline" class="table table-responsive table-borderless table-hover" cellspacing="0">
                      <thead>
                        <tr>
                          <th><?php echo _("Date"); ?></th>
                          <th><?php echo _("Created By"); ?></th>
                          <th></th>
                        </tr>
                      </thead>
                      <?php
                      $sql = "SELECT projects_baselines.ID, projects_baselines.changed, projects_baselines.UUID, CONCAT(users.Firstname,' ',users.Lastname) AS FullName
                                FROM projects_baselines
                                LEFT JOIN users ON projects_baselines.updated_by = users.ID
                                WHERE projects_baselines.ID = $ProjectID
                                ORDER BY changed DESC";
                      $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                      ?>
                      <tbody>
                        <?php while ($row = mysqli_fetch_array($result)) { ?>
                          <?php $UUID = $row['UUID']; ?>
                          <tr class='text-sm text-secondary mb-0'>
                            <td><?php $myFormatForView = convertToDanishTimeFormat($row['changed']);
                                echo $myFormatForView; ?> </td>
                            <td><?php echo $row['FullName']; ?></td>
                            <td><?php echo "" //"<a href=\"javascript:restoreToCurrentBaseline('$UUID');\">" . _("restore") . "</a>"; 
                                ?></td>
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
</div>
<script>
  $(document).ready(function() {
    <?php initiateMediumViewTable("ProjectActivity"); ?>
    <?php initiateStandardSearchTable("TableProjectLog"); ?>
  });
</script>
<!-- Kanban scripts -->

<?php include("./modals/modal_projectteam.php") ?>
<?php include("./modals/modal_create_new_project_sprint.php") ?>
<?php include("./modals/modal_create_new_project_category.php") ?>
<?php include("./footer.php") ?>