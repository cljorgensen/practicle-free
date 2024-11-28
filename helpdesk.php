<?php include("./header.php") ?>

<?php
$UserID = $_SESSION['id'];
$CompanyID = $_SESSION['companyid'];
?>
<script>
  $(document).ready(function() {
    getITSMDataRows(1, '1,2,3,4', <?php echo $UserID ?>);
    getTempFiles('<?php echo $UserID ?>', '1');
    getRequests(2, 'da_DK', 'hd');
  });


  function showhideIncidentTable() {
    var div = document.getElementById("TableIncidents");
    if (div.hidden == true) {
      div.hidden = false;
      getITSMDataRows(1, '1,2,3,4', <?php echo $UserID ?>);
    } else {
      div.hidden = true;
    }
  }

  function showhideRequestTable() {
    var div = document.getElementById("TableSectionRequests");
    if (div.hidden == true) {
      div.hidden = false;
      getITSMDataRows(2, '1,2,3,4', <?php echo $UserID ?>);
    } else {
      div.hidden = true;
    }
  }

  function getITSMDataRows(ITSMTypeID, StatusCodes, USERID) {
    var tablename = "itsm_table_" + ITSMTypeID;

    if ($.fn.DataTable.isDataTable("#" + tablename)) {
      $("#" + tablename).DataTable().clear().destroy();
    }

    const data = "";
    let CompanyID = <?php echo $CompanyID ?>;

    vData = {
      ITSMTypeID: ITSMTypeID,
      StatusCodes: StatusCodes,
      CompanyID: CompanyID,
      USERID: USERID
    };

    $.ajax({
      type: "POST",
      url: "./getdata.php?getITSMDataRows",
      data: vData,
      dataType: 'JSON',
      success: function(data) {
        if ($.fn.DataTable.isDataTable("#" + tablename)) {
          $("#" + tablename).DataTable().clear().destroy();
        }
        var rowData = data[0]; // Get first row data to build columns from.
        if (rowData) {
          // Itereate rowData's object keys to build column definition.
          Object.keys(rowData).forEach(function(key, index) {
            var newkey = key.replace('ImAPlaceHolder', ' ');
            columns.push({
              data: newkey,
              title: newkey
            });
          });

          var datatable = $("#" + tablename).DataTable({
            "dom": 'Bfrtip',
            "bFilter": true,
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
            "bSort": true,
            "ordering": true,
            "language": {
              "info": '_START_ <?php echo _("to"); ?> _END_ <?php echo _("of"); ?> _TOTAL_ <?php echo _("Total"); ?>',
              "searchPlaceholder": '<?php echo _("Search"); ?>',
              "search": '',
            },
            "bLengthChange": true,
            "displayLength": 25,
            "searching": true,
            "stateSave": false,

            "buttons": ['copy', 'excel'],
            data: data,
            columns: columns,
          });
        }
      }
    });
    columns = [];
  };

  function getTempFiles(UserID, ElementType) {
    var divid = "tempfiles" + ElementType;
    document.getElementById(divid).innerHTML = "";

    vData = {
      ElementType: ElementType,
      UserID: UserID
    };

    $.ajax({
      type: "POST",
      url: "./getdata.php?getTempFiles",
      data: vData,
      success: function(data) {
        var obj = JSON.parse(data);
        for (var i = 0; i < obj.length; i++) {
          var Content = obj[i].Content;
          document.getElementById(divid).innerHTML = Content;
        }
      }
    });
  };
</script>
<div class="col-md-12 col-sm-12 col-xs-12">
  <div class='card-group'>
    <div class='card'>
      <div class="card-header">
        <div class="text-center">
          <h5><a href="helpdesk.php"> <?php echo _("Helpdesk") ?></a></h5>
        </div>
      </div>
      <div class='card-body'>
        <div class="nav-wrapper position-relative end-0">
          <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
            <li class="nav-item">
              <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#incidentstab" role="tab" aria-controls="preview" aria-selected="true" onclick="getITSMDataRows(1, '1,2,3,4', <?php echo $UserID ?>);">
                <i class="fa fa-ticket"></i>
                <?php echo _("Incidents") ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#requeststab" role="tab" aria-controls="preview" aria-selected="true" onclick="getITSMDataRows(2, '1,2,3,4', <?php echo $UserID ?>);">
                <i class="fa fa-retweet"></i>
                <?php echo _("Requests") ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#newstab" role="tab" aria-controls="code" aria-selected="false">
                <span class="material-icons align-middle mb-1">
                  info
                </span>
                <?php echo _("News") ?>
              </a>
            </li>
          </ul>
        </div>
        <div class="tab-content">
          <div class="tab-pane active" id="incidentstab">
            <p>
              <a class="btn btn-primary" data-bs-toggle="collapse" href="#CreateIncident" role="button" aria-expanded="false" aria-controls="CreateIncident" onclick="showhideIncidentTable();">
                <?php echo _("Create") ?>
              </a>
            </p>
            <div class="collapse" id="CreateIncident">
              <div class="card card-body">
                <div class="row">
                  <div class="col-lg-6 col-sm-12 col-xs-12">
                    <div class="input-group input-group-static mb-4">
                      <label for="Subject" title=""><?php echo _("Subject") ?></label>
                      <input type="text" class="form-control" id="Subject" name="Subject" value="" title="" autocomplete="off">
                    </div>
                    <div class="input-group input-group-static mb-4">
                      <label for="Description" title=""><?php echo _("Description") ?></label>
                      <textarea type="text" class="form-control" rows="5" id="Description" name="Description" value=""></textarea>
                    </div>
                    <button type="button" class="btn btn-sm btn-success float-right" onclick="createIncident()"><?php echo _("Create") ?></button>
                  </div>
                  <div class="col-lg-6 col-sm-6 col-xs-12">
                    <form action="../functions/cifileupload.php?userid=<?php echo $_SESSION['id']; ?>&elementref=1&elementid=temp&elementpath=temp" class="dropzone" id="dropzoneformHD1"></form>
                    <div id="tempfiles1"></div>
                  </div>
                </div>
              </div>
              <br><br>
            </div>
            <div id="TableIncidents">
              <div class="dropdown">
                <a href="#" class="btn bg-gradient-dark dropdown-toggle " data-bs-toggle="dropdown" id="navbarDropdownMenuLink">
                  <?php echo _("Filter"); ?>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                  <li>
                    <a class="dropdown-item" href="javascript:showCreateNewDiv();">
                      <a href="javascript:getITSMDataRows(1,'1,2,3,4',<?php echo $UserID ?>);"><?php echo _("My active"); ?></a>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="javascript:showCreateNewDiv();">
                      <a href="javascript:getITSMDataRows(1,'5,6,7,8',<?php echo $UserID ?>);"><?php echo _("My closed"); ?></a>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="javascript:showCreateNewDiv();">
                      <a href="javascript:getITSMDataRows(1,'1,2,3,4','none');"><?php echo _("All active"); ?></a>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="javascript:showCreateNewDiv();">
                      <a href="javascript:getITSMDataRows(1,'5,6,7,8','none');"><?php echo _("All closed"); ?></a>
                    </a>
                  </li>
                </ul>
              </div>
              <br><br>
              <table id="itsm_table_1" class="table table-borderless table-responsive table-hover" cellspacing="0">
              </table>
            </div>
          </div>
          <div class="tab-pane" id="requeststab">
            <p>
              <a class="btn btn-primary" data-bs-toggle="collapse" href="#CreateRequest" role="button" aria-expanded="false" aria-controls="CreateRequest" onclick="showhideRequestTable();">
                <?php echo _("Create") ?>
              </a>
            </p>
            <div class="collapse" id="CreateRequest">
              <div class="card card-body">
                <div class="row">
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <table id="TableRequestsViewHD" class="table table-borderless dt-responsive table-hover" cellspacing="0">
                    </table>
                    <form id="ITSMFormCreateHelpDesk"></form>
                    <button type="button" class="btn btn-sm btn-success" onclick="createRequest()"><?php echo _("Create") ?></button>
                    <div id="FormIDHelpDesk" hidden></div>
                    <div id="FormNameHelpDesk" hidden></div>
                  </div>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                    <form action="../functions/cifileupload.php?userid=<?php echo $_SESSION['id']; ?>&elementref=2&elementid=temp&elementpath=temp" class="dropzone" id="dropzoneformHD2"></form>
                    <div id="tempfiles2"></div>
                  </div>
                </div>
              </div>
              <br><br>
            </div>
            <div id="TableSectionRequests">
              <div class="dropdown" id="RequestDataRows">
                <a href="#" class="btn bg-gradient-dark dropdown-toggle " data-bs-toggle="dropdown" id="navbarDropdownMenuLink">
                  <?php echo _("Filter"); ?>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                  <li>
                    <a class="dropdown-item" href="javascript:showCreateNewDiv();">
                      <a href="javascript:getITSMDataRows(2,'1,2,3,4',<?php echo $UserID ?>);"><?php echo _("My active"); ?></a>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="javascript:showCreateNewDiv();">
                      <a href="javascript:getITSMDataRows(2,'5,6,7,8',<?php echo $UserID ?>);"><?php echo _("My closed"); ?></a>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="javascript:showCreateNewDiv();">
                      <a href="javascript:getITSMDataRows(2,'1,2,3,4','none');"><?php echo _("All active"); ?></a>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="javascript:showCreateNewDiv();">
                      <a href="javascript:getITSMDataRows(2,'5,6,7,8','none');"><?php echo _("All closed"); ?></a>
                    </a>
                  </li>
                </ul>
              </div>
              <br><br>
              <table id="itsm_table_2" class="table table-borderless table-responsive table-hover" cellspacing="0">
              </table>
            </div>
          </div>
          <!-- News -->
          <div class="tab-pane" id="newstab">
            <div class="card">
              <div class="card-body">
                <div class="row">
                  <table id="TableNews" class="table table-borderless dt-responsive" cellspacing="0">
                    <thead>
                      <tr>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $NewsGroup = $functions->getSettingValue(21);
                      $sql = "SELECT news.ID, Headline, Content, CONCAT(users.Firstname,' ',users.Lastname) AS FullName, users.ID AS UserID, DateCreated, RelatedCategory, CommentsAllowed, users.email, users.profilepicture
                              FROM news
                              LEFT JOIN users ON users.ID = news.CreatedByUserID
                              LEFT JOIN news_categories ON news_categories.ID = news.RelatedCategory
                              WHERE news.Active = 1 AND DateCreated <= NOW() AND news.RelatedCategory = $NewsGroup
                              ORDER BY DateCreated DESC
                              LIMIT 30;";

                      $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                      while ($row = mysqli_fetch_array($result)) {
                        if ($row['ID'] == '') {
                          echo 'No News yet';
                        } else {
                          $NewsID = $row['ID'];
                          $Headline = $row['Headline'];
                          $CreatedByUserFullName = $row['FullName'];
                          $CreatedByUserID = $row['UserID'];
                          $Content = $row['Content'];
                          $Email = $row['email'];
                          $ProfilePicture = $row['profilepicture'];
                          $myFormatForView = convertToDanishTimeFormat($row['DateCreated']);
                          $DateCreated = $myFormatForView;
                          $RelatedCategory =  $row['RelatedCategory'];
                          if (strlen($Headline) > 150) {
                            $Headline = substr($Headline, 0, 150) . "...";
                          }

                          echo "  <tr data-bs-toggle='collapse' href='#CollapseNews$NewsID' role='button' aria-expanded='false' aria-controls='CollapseNews$NewsID'>
                                  <td><div class='card card-body-dropdown'>
                                        <div class='accordion collapsed'><div class='text-sm text-start text-secondary text-wrap'>
                                          <div>$Headline</div>
                                        </div>
                                      </div>
                                      <div class='collapse text-wrap' id='CollapseNews$NewsID'>
                                          <br><p class='text-sm text-secondary mb-0'>" . html_entity_decode($Content) . "</p><br>" ?>
                          <p><a href='user_profile.php?userid=<?php echo $CreatedByUserID ?>&sessionuserid=<?php echo $CreatedByUserID ?>' data-bs-toggle='popover' data-bs-html='true' data-bs-trigger='hover' data-bs-content="
                                              <p class='text-center'><b><?php echo $CreatedByUserFullName ?></b><br><?php echo $Email ?><br><br>
                                              <p><img class='rounded-circle img-fluid' style='width: 150px;' src='./uploads/images/profilepictures/<?php echo $ProfilePicture ?>'></p>
                                              <p class='text-sm text-secondary mb-0 text-wrap'>"><?php echo $CreatedByUserFullName ?></a><br>
                          <div class='text-sm text-secondary mb-0'><?php echo $DateCreated ?></div>
                          </p>
                </div>
                </td>
                </tr>
            <?php
                        }
                      }
            ?>
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
<script src="./jspracticle/functions_itsm.js"></script>
<script>
  async function createIncident() {
    var ITSMTableName = "itsm_incidents";
    var RequestForm = "";
    var FormID = "";
    var RelatedCompanyID = <?php echo $CompanyID ?>;
    var Customer = <?php echo $UserID ?>;
    var Subject = document.getElementById("Subject").value;
    var Description = document.getElementById("Description").value;
    var ITSMForm = [];

    ITSMForm.push({
      name: 'RelatedCompanyID',
      value: '' + RelatedCompanyID + ''
    });
    ITSMForm.push({
      name: 'Customer',
      value: '' + Customer + ''
    });
    ITSMForm.push({
      name: 'Subject',
      value: '' + Subject + ''
    });
    ITSMForm.push({
      name: 'Description',
      value: '' + Description + ''
    });
    ITSMForm.push({
      name: 'Status',
      value: '1'
    });
    ITSMForm.push({
      name: 'Priority',
      value: '3'
    });

    createITSMEntry(ITSMTableName, ITSMForm, FormID, RequestForm);
    await new Promise(resolve => setTimeout(resolve, 1000));
    location.reload(true);
  }

  async function createRequest() {
    var ITSMTableName = "itsm_requests";
    var ITSMForm = [];
    var RequestForm = [];
    var RequestForm = $("#ITSMFormCreateHelpDesk").serializeArray();
    var FormID = document.getElementById("FormIDHelpDesk").innerHTML;
    var RelatedCompanyID = <?php echo $CompanyID ?>;
    var Customer = <?php echo $UserID ?>;
    var Subject = document.getElementById("FormNameHelpDesk").innerHTML;

    ITSMForm.push({
      name: 'RelatedCompanyID',
      value: '' + RelatedCompanyID + ''
    });
    ITSMForm.push({
      name: 'Customer',
      value: '' + Customer + ''
    });
    ITSMForm.push({
      name: 'Subject',
      value: '' + Subject + ''
    });
    ITSMForm.push({
      name: 'Status',
      value: '1'
    });
    ITSMForm.push({
      name: 'Priority',
      value: '3'
    });


    createITSMEntry(ITSMTableName, ITSMForm, FormID, RequestForm);
    await new Promise(resolve => setTimeout(resolve, 1000));
    location.reload(true);
  }

  function searchCustomerKnowledge() {
    if ($.fn.DataTable.isDataTable("#HelpdeskSearch")) {
      $("#HelpdeskSearch").DataTable().clear().destroy();
    }

    var SearchValue = document.getElementById("CustomerKnowledgeSearch").value;

    if (SearchValue == "") {
      return;
    }

    vData = {
      SearchValue: SearchValue,
    };
    var columnsSearch = [];
    $.ajax({
      type: "POST",
      url: "./getdata.php?HelpdeskSearch",
      data: vData,
      dataType: "JSON",
      success: function(datasearch) {
        var rowDataChildren = datasearch[0]; // Get first row data to build columns from.
        if (datasearch.length !== 0) {
          // Itereate rowData's object keys to build column definition.
          Object.keys(rowDataChildren).forEach(function(keyChild, index) {
            var newkeyChild = keyChild.replace("ImAPlaceHolder", " ");
            columnsSearch.push({
              data: newkeyChild,
              title: newkeyChild,
            });
          });

          var HelpdeskSearch = $("#HelpdeskSearch").DataTable({
            dom: "Bfrtip",
            bFilter: true,
            bDestroy: true,
            paging: true,
            info: false,
            pagingType: "numbers",
            processing: true,
            deferRender: true,
            pageLength: 5,
            orderCellsTop: true,
            fixedHeader: false,
            autoWidth: false,
            aaSorting: [],
            responsive: true,
            bSort: true,
            ordering: true,
            bLengthChange: false,
            buttons: [],
            displayLength: 10,
            searching: true,
            columnDefs: [{
              target: 0,
              visible: false
            }],
            data: datasearch,
            columns: columnsSearch,
          });
        }
      },
    });
  }
</script>

<?php include("./footer.php") ?>