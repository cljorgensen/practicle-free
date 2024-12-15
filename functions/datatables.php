<?php

function createDefaultDataTable($tableName, $dynamicCall, $LanguageCode, $Entries, $Btns)
{
  switch ($Btns) {
    case 0:
      $Btns = "";
      break;
    case 1:
      $Btns = "{extend: 'copy', className: 'btn btn-sm btn-secondary'},{extend: 'excel', className: 'btn btn-sm btn-secondary'}";
      break;
    case 2:
      $Btns = "{extend: 'excel', className: 'btn btn-sm btn-secondary'},{extend: 'csv', className: 'btn btn-sm btn-secondary'}";
      break;
    case 3:
      $Btns = "{extend: 'copy', className: 'btn btn-sm btn-secondary'},{extend: 'excel', className: 'btn btn-sm btn-secondary'},{extend: 'csv', className: 'btn btn-sm btn-secondary'}";
      break;
    case 4:
      $Btns = "{extend: 'excel', className: 'btn btn-sm btn-secondary'},{extend: 'csv', className: 'btn btn-sm btn-secondary'},{extend: 'pdf', className: 'btn btn-sm btn-secondary'}";
      break;
    case 5:
      $Btns = "{extend: 'copy', className: 'btn btn-sm btn-secondary'},{extend: 'excel', className: 'btn btn-sm btn-secondary'},{extend: 'csv', className: 'btn btn-sm btn-secondary'},{extend: 'pdf', className: 'btn btn-sm btn-secondary'}";
      break;
    default:
      $Btns = "";
      break;
  }

  $script = "
    <script>
      function loadData($tableName) {
          activateSpinner();
          var columns = [];

          $.ajax({
              type: \"POST\",
              url: \"./getdata.php?$dynamicCall\",
              data: \"\",
              dataType: \"JSON\",
              success: function (data) {
                var rowData = data[0]; // Get first row data to build columns from.
                if ($.fn.DataTable.isDataTable(\"#$tableName\")) {
                  $(\"#$tableName\").DataTable().clear().destroy();
                }
                if (data.length !== 0) {
                  // Itereate rowData's object keys to build column definition.
                  Object.keys(rowData).forEach(function (keyChild, index) {
                    var newkeyChild = keyChild.replace(\"ImAPlaceHolder\", \" \");
                    columns.push({
                      data: newkeyChild,
                      title: newkeyChild,
                    });
                  });

                  var $tableName = $(\"#$tableName\").DataTable({
                    dom: \"Brftp\",
                    bFilter: true,
                    bDestroy: true,
                    paging: true,
                    info: false,
                    pagingType: \"numbers\",
                    processing: true,
                    pageLength: $Entries,
                    orderCellsTop: false,
                    fixedHeader: false,
                    autoWidth: false,
                    responsive: true,
                    ordering: true,
                    buttons: [$Btns],
                    displayLength: $Entries,
                    searching: true,
                    language: {
                      url: \"./assets/js/DataTables/Languages/$LanguageCode.json\",
                    },
                    data: data,
                    columnDefs: [
                      {
                        targets: 0,
                        width: \"35px\",
                      },
                    ],
                    columns: columns,
                  });
                }
                if (data.length == 0) {
                  if ($.fn.DataTable.isDataTable(\"#$tableName\")) {
                    $(\"#$tableName\").DataTable().clear().destroy();
                  }
                }
              },
              complete: function (data) {
                deactivateSpinner();
              },
              error: function (xhr, status, error) {
                deactivateSpinner();
                console.error(\"Error fetching Languages:\", error);
              },
          });
      }

      $(document).ready(function() {
        activateSpinner();
        var columns = [];
        $.ajax({
          type: \"POST\",
          url: \"./getdata.php?$dynamicCall\",
          data: \"\",
          dataType: \"JSON\",
          success: function (data) {
            var rowData = data[0]; // Get first row data to build columns from.
            if ($.fn.DataTable.isDataTable(\"#$tableName\")) {
              $(\"#$tableName\").DataTable().clear().destroy();
            }
            if (data.length !== 0) {
              // Itereate rowData's object keys to build column definition.
              Object.keys(rowData).forEach(function (keyChild, index) {
                var newkeyChild = keyChild.replace(\"ImAPlaceHolder\", \" \");
                columns.push({
                  data: newkeyChild,
                  title: newkeyChild,
                });
              });

              var $tableName = $(\"#$tableName\").DataTable({
                dom: \"Brftp\",
                bFilter: true,
                bDestroy: true,
                paging: true,
                info: false,
                pagingType: \"numbers\",
                processing: true,
                pageLength: $Entries,
                orderCellsTop: false,
                fixedHeader: false,
                autoWidth: false,
                responsive: true,
                ordering: true,
                buttons: [$Btns],
                displayLength: $Entries,
                searching: true,
                language: {
                  url: \"./assets/js/DataTables/Languages/$LanguageCode.json\",
                },
                data: data,
                columnDefs: [
                  {
                    targets: 0,
                    width: \"35px\",
                  },
                ],
                columns: columns,
              });
            }
            if (data.length == 0) {
              if ($.fn.DataTable.isDataTable(\"#$tableName\")) {
                $(\"#$tableName\").DataTable().clear().destroy();
              }
            }
          },
          complete: function (data) {
            deactivateSpinner();
          },
          error: function (xhr, status, error) {
            deactivateSpinner();
            console.error(\"Error fetching Languages:\", error);
          },
        });
      });
    </script>";

  return $script;
}

function initiateAdvancedViewTable($tablename)
{
  global $UserLanguageCode;
  echo "
   $('#$tablename').dataTable().fnDestroy();
    
    var table = $('#$tablename').DataTable( {
      sDom: 'QBlfrtip',
      bFilter: true,
      paging: true,
      info: false,
      pagingType: 'numbers',
      processing: true,
      deferRender: true,
      pageLength: 20,
      orderCellsTop: true,
      fixedHeader: false,
      autoWidth: false,
      aaSorting: [],
      responsive: true,
      bSort: true,
      searching: true,
      language: { url: '../assets/js/DataTables/Languages/$UserLanguageCode.json' },
      initComplete: function() {
          // Add input elements for column search
          this.api().columns().every(function() {
            var column = this;
            //var input = $(this).html('<input type=\"search\" class=\"form-control-dt\" placeholder=\"\"/>' + title + '')
            var input = $('<input type=\"text\" class=\"form-control-dt\" placeholder=\"". _("Search"). "\" />')
              .on('keyup change', function() {
                if (column.search() !== this.value) {
                  column.search(this.value).draw();
                }
              });
            $(column.header()).append(input);
          });
        },
      buttons: [
            {
              extend: \"copy\",
              className: \"btn btn-sm btn-secondary\",
              exportOptions: {
                rows: function (idx, data, node) {
                  return true; // Include all rows
                },
                format: {
                  header: function (data, columnIdx) {
                    return columnNames[columnIdx] || data; // Use dynamic column names
                  },
                  body: function (data, row, column, node) {
                    if ($(node).find(\"span[style*='display:none']\").length > 0) {
                      // Handle hidden span case (e.g., Unix timestamps)
                      return $(node).clone().children().remove().end().text().trim();
                    } else if ($(node).find(\"a\").length > 0) {
                      // Handle <a> tag case (e.g., links)
                      return $(node).find(\"a\").text().trim();
                    }
                    return data; // Return raw data for other cells
                  }
                }
              }
            },
            {
              extend: \"csv\",
              className: \"btn btn-sm btn-secondary\",
              text: \"CSV\",
              exportOptions: {
                rows: function (idx, data, node) {
                  return true; // Include all rows
                },
                format: {
                  header: function (data, columnIdx) {
                    return columnNames[columnIdx] || data; // Use dynamic column names
                  },
                  body: function (data, row, column, node) {
                    if ($(node).find(\"span[style*='display:none']\").length > 0) {
                      // Handle hidden span case (e.g., Unix timestamps)
                      return $(node).clone().children().remove().end().text().trim();
                    } else if ($(node).find(\"a\").length > 0) {
                      // Handle <a> tag case (e.g., links)
                      return $(node).find(\"a\").text().trim();
                    }
                    return data; // Return raw data for other cells
                  }
                }
              }
            },
            {
              extend: \"excel\",
              className: \"btn btn-sm btn-secondary\",
              exportOptions: {
                rows: function (idx, data, node) {
                  return true; // Include all rows
                },
                format: {
                  header: function (data, columnIdx) {
                    return columnNames[columnIdx] || data;
                  },
                  body: function (data, row, column, node) {
                    if ($(node).find(\"span[style*='display:none']\").length > 0) {
                      return $(node).clone().children().remove().end().text().trim();
                    } else if ($(node).find(\"a\").length > 0) {
                      return $(node).find(\"a\").text().trim();
                    }
                    return data;
                  }
                }
              }
            }
          ],
          searchBuilder: {
          },
          layout: {
            topStart: 'searchBuilder' // Ensure SearchBuilder is positioned at the top
          }
    } );
    ";
}

function initiateStandardSearchTable($tablename)
{
  global $UserLanguageCode;
  echo "// Extract column names dynamically from the table header
    var columnNames = [];
    $('#$tablename thead th').each(function () {
        columnNames.push($(this).text().trim());
    });

    var table = $('#$tablename').DataTable({
        paging: true,
        pagingType: 'numbers',
        processing: false,
        searching: true,
        deferRender: true,
        dom: 'Bfrtip',
        pageLength: 20,
        orderCellsTop: true,
        fixedHeader: false,
        bSort: true,
        autoWidth: false,
        destroy: true,
        aaSorting: [],
        responsive: true,
        aoColumns: $('#$tablename').find('thead tr th').map(function(){return $(this).data()}),
        language: { url: '../assets/js/DataTables/Languages/$UserLanguageCode.json' },
        buttons: [
            {
              extend: \"copy\",
              className: \"btn btn-sm btn-secondary\",
              exportOptions: {
                rows: function (idx, data, node) {
                  return true; // Include all rows
                },
                format: {
                  header: function (data, columnIdx) {
                    return columnNames[columnIdx] || data; // Use dynamic column names
                  },
                  body: function (data, row, column, node) {
                    if ($(node).find(\"span[style*='display:none']\").length > 0) {
                      // Handle hidden span case (e.g., Unix timestamps)
                      return $(node).clone().children().remove().end().text().trim();
                    } else if ($(node).find(\"a\").length > 0) {
                      // Handle <a> tag case (e.g., links)
                      return $(node).find(\"a\").text().trim();
                    }
                    return data; // Return raw data for other cells
                  }
                }
              }
            },
            {
              extend: \"csv\",
              className: \"btn btn-sm btn-secondary\",
              text: \"CSV\",
              exportOptions: {
                rows: function (idx, data, node) {
                  return true; // Include all rows
                },
                format: {
                  header: function (data, columnIdx) {
                    return columnNames[columnIdx] || data; // Use dynamic column names
                  },
                  body: function (data, row, column, node) {
                    if ($(node).find(\"span[style*='display:none']\").length > 0) {
                      // Handle hidden span case (e.g., Unix timestamps)
                      return $(node).clone().children().remove().end().text().trim();
                    } else if ($(node).find(\"a\").length > 0) {
                      // Handle <a> tag case (e.g., links)
                      return $(node).find(\"a\").text().trim();
                    }
                    return data; // Return raw data for other cells
                  }
                }
              }
            },
            {
              extend: \"excel\",
              className: \"btn btn-sm btn-secondary\",
              exportOptions: {
                rows: function (idx, data, node) {
                  return true; // Include all rows
                },
                format: {
                  header: function (data, columnIdx) {
                    return columnNames[columnIdx] || data;
                  },
                  body: function (data, row, column, node) {
                    if ($(node).find(\"span[style*='display:none']\").length > 0) {
                      return $(node).clone().children().remove().end().text().trim();
                    } else if ($(node).find(\"a\").length > 0) {
                      return $(node).find(\"a\").text().trim();
                    }
                    return data;
                  }
                }
              }
            }
          ],
    });
    ";
}

function initiateMediumViewTable($tablename)
{
  global $UserLanguageCode;
  // Escape the table name for JavaScript usage
  $escapedTablename = addslashes($tablename);
  $escapedUserLanguageCode = addslashes($UserLanguageCode);
  // Output the JavaScript code
  echo "
    var $escapedTablename = $('#$escapedTablename').DataTable({
      dom: 'Brftp',
      lengthMenu: [5, 10, 25, 50],
      pageLength: 5,
      pagingType: 'numbers',
      orderCellsTop: false,
      fixedHeader: false,
      ordering: false,
      autoWidth: false,
      responsive: true,
      drawCallback: function( settings ) {
        $('#$escapedTablename thead').remove();
      },
      language: { url: '../assets/js/DataTables/Languages/$escapedUserLanguageCode.json' },
      buttons: [],
      searching: true,
    });";
}

function initiateSimpleViewTable($tablename, $pageLength, $hideColumns)
{
  global $UserLanguageCode;

  // Convert PHP array of column indices to JavaScript array
  $hideColumnsJsArray = json_encode($hideColumns);

  echo "
    $('#$tablename').dataTable().fnDestroy();

    var table = $('#$tablename').DataTable({
        sDom: 'Blfrtip',
        bFilter: true,
        paging: true,
        info: false,
        pagingType: 'numbers',
        processing: true,
        searching: true,
        deferRender: true,
        pageLength: $pageLength,
        orderCellsTop: true,
        fixedHeader: false,
        autoWidth: false,
        aaSorting: [],
        responsive: true,
        buttons: [''],
        lengthChange: false,
        language: { url: '../assets/js/DataTables/Languages/$UserLanguageCode.json' },
        columnDefs: [
            {
                targets: $hideColumnsJsArray, // Target the columns from the hideColumns parameter
                visible: false, // Set visibility to false
                searchable: false // Optional: Exclude hidden columns from search
            }
        ]
    });
    ";
}

?>