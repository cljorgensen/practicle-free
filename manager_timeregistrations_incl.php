<?php
$UserID = $_SESSION['id'];
?>
<script>
  var weekOffset = 0;
  var dataTable;

  // Function to format the date as "DD-MM-YYYY"
  function formatDate(date) {
    var day = date.getDate().toString().padStart(2, '0');
    var month = (date.getMonth() + 1).toString().padStart(2, '0');
    var year = date.getFullYear().toString();
    return day + '-' + month + '-' + year;
  }

  // Function to get the start of the week for the given date
  function getStartOfWeek(date) {
    var diff = date.getDate() - date.getDay() + (date.getDay() === 0 ? -6 : 1);
    return new Date(date.setDate(diff));
  }

  // Function to set the week number and update table headers on initial load
  async function setInitialWeekNumber() {
    var today = new Date();
    var startOfWeekDate = new Date(today);
    startOfWeekDate.setDate(startOfWeekDate.getDate() + (weekOffset * 7));
    var startOfWeek = getStartOfWeek(startOfWeekDate);
    var weekNumber = getWeekNumber(startOfWeek); // Calculate the week number

    // Update the week number in the div element
    $("#weekNumber").text('<?php echo _("Week Number") ?>' + ' (' + weekNumber + ')');

    // Destroy the existing DataTable instance, if it exists
    if ($.fn.DataTable.isDataTable('#timeregistrationsTableNew')) {
      dataTable.destroy();
    }
    // Initialize the DataTable after updating the headers
    dataTable = $('#timeregistrationsTableNew').DataTable({
      "ajax": {
        "url": "./getdata.php?getAllTimeRegistrations&weekOffset=" + weekOffset,
        "dataSrc": "" // This is optional, leave empty if your data is directly an array, not a nested object.
      },
      "dom": 'Bfrtip',
      "Filter": true,
      "paging": true,
      "info": false,
      "pagingType": 'numbers',
      "processing": true,
      "deferRender": true,
      "pageLength": 25,
      "orderCellsTop": true,
      "fixedHeader": false,
      "autoWidth": false,
      "aaSorting": [],
      "responsive": true,
      "Sort": true,
      "ordering": true,
      "language": {
        url: './assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
      },
      "bLengthChange": true,
      "displayLength": 100,
      "searching": true,
      "stateSave": false,
      "buttons": ['copy', 'excel', 'csv'],
      // Add any other DataTable options you want to use
      "columns": [{
          "data": function(row) {
            return row[0]; // First column - Employee (ProfilePopOver + UserName)
          }
        },
        {
          "data": "1"
        }, // Second column - Monday
        {
          "data": "2"
        }, // Third column - Tuesday
        {
          "data": "3"
        }, // Fourth column - Wednesday
        {
          "data": "4"
        }, // Fifth column - Thursday
        {
          "data": "5"
        }, // Sixth column - Friday
        {
          "data": "6"
        }, // Seventh column - Saturday
        {
          "data": "7"
        } // Eighth column - Sunday
      ]
    });
    // Update the table headers with the correct dates based on the week offset
    updateTableHeader(weekOffset);
  }

  async function updateTableHeader(weekOffset) {
    var daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    var today = new Date();
    var startOfWeekDate = new Date(today);
    startOfWeekDate.setDate(startOfWeekDate.getDate() + (weekOffset * 7));
    var startOfWeek = getStartOfWeek(startOfWeekDate);
    var weekNumber = getWeekNumber(startOfWeek); // Calculate the week number

    // Translate the array of days in parallel using Promise.all()
    var translatedDays = await Promise.all(daysOfWeek.map(day => translate(day)));

    for (var index = 0; index < daysOfWeek.length; index++) {
      var date = new Date(startOfWeek);
      date.setDate(date.getDate() + index);
      var formattedDate = formatDate(date);
      var day = translatedDays[index].replace(/\\u([\dA-F]{4})/gi, function(match, grp) {
        return String.fromCharCode(parseInt(grp, 16));
      });
      day = day.replace(/["']/g, '');

      $("#timeregistrationsTableNew thead th:eq(" + (index + 1) + ")").text(day + ' (' + formattedDate + ')');
    }

    // Update the week number in the div element
    $("#weekNumber").text('<?php echo _("Week Number") ?>' + ' (' + weekNumber + ')');
  }


  // Function to get the week number for the given date
  function getWeekNumber(date) {
    var firstDayOfYear = new Date(date.getFullYear(), 0, 1);
    var pastDaysOfYear = (date - firstDayOfYear) / 86400000;
    return Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
  }

  // Function to go to the next week
  function nextWeek() {
    weekOffset++;
    reloadData(weekOffset);
    updateTableHeader(weekOffset);
  }

  // Function to go to the previous week
  function prevWeek() {
    weekOffset--;
    reloadData(weekOffset);
    updateTableHeader(weekOffset);
  }

  // Function to reload data for the specified week offset and update the table header
  async function reloadData() {
    // Add a cache buster to the AJAX request URL
    var cacheBuster = new Date().getTime();
    var reloadResult = await new Promise(resolve => dataTable.ajax.url("./getdata.php?getAllTimeRegistrations&weekOffset=" + weekOffset + "&cache=" + cacheBuster).load(resolve));
    updateTableHeader(weekOffset);
  }

  $(document).ready(function() {
    setInitialWeekNumber();

    // Attach event listeners to the buttons for scrolling between weeks
    $("#nextWeekBtn").on("click", nextWeek);
    $("#prevWeekBtn").on("click", prevWeek);

  });
</script>
<div class="card">
  <div class="card-header"><a href="javascript:location.reload(true);"><?php echo _("This weeks time registrations for team") . ": " . $_SESSION['Teamname']; ?></a>
  </div>
  <div class="card-body">
    <div id="weekNumber"></div>
    <div class="btn-group">
      <!-- Button to go to the previous week -->
      <button id="prevWeekBtn" class="btn btn-primary">&lt;&lt; <?php echo _("Previous"); ?></button>
      <!-- Button to go to the next week -->
      <button id="nextWeekBtn" class="btn btn-primary"><?php echo _("Next"); ?> &gt;&gt;</button>
    </div>
    <!-- Display the week number in this div -->

    <table id="timeregistrationsTableNew" class="table dt-responsive" cellspacing="0">
      <thead>
        <!-- Your existing table header -->
        <tr>
          <th><?php echo _("Employee") ?></th>
          <?php
          $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
          $startOfWeek = date('Y-m-d', strtotime("monday this week -1 week"));

          foreach ($daysOfWeek as $day) {
            $date = date('d-m-Y', strtotime($day, strtotime($startOfWeek)));
            $header = $functions->translate($day) . ' (' . $date . ')';
            echo '<th>' . $header . '</th>';
          }
          ?>
        </tr>
      </thead>
    </table>
  </div>
</div>