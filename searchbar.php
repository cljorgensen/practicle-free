<!-- Start Sidebar 2 -->
<div id="mySidebar" class="sidebar2">
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
  <ul class="nav nav-tabs nav-fill mb-3" role="tablist" id="activetablist">
    <li class="nav-item">
      <a class="nav-link active" data-bs-toggle="tab" active>
        <?php echo _("Search"); ?>
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="tab" href="#Guides">
        <?php echo _("Guides"); ?>
      </a>
    </li>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="searchtab">
      <div id='accordionSearch' role='tablist'>
        <?php
        echo "
        <div class='card-collapse'>
          <div class='card-header' role='tab' id='headingsearch7'>
            <h5 class='mb-0'>
              <a class='collapsed' data-bs-toggle='collapse' href='#collapsesearch7' aria-expanded='false' aria-controls='collapsesearch7'>
                <p class='category align-middle'><span i class='fa fa-question'></i></span> " ?><?php echo _("Help") ?><?php echo "
                <i class='fas fa-angle-down'></i></p>
              </a>
            </h5>
          </div>
          <div id='collapsesearch7' class='collapse' role='tabpanel' aria-labelledby='headingsearch7' data-parent='#accordionSearch'>
            <div class='card-body'>
              <div class='card-collapse'>
                <form method='post'>
                <div class='col-md-12 col-sm-12 col-xs-12 form-group top_search'>
                  <div class='input-group'>
                    <input type='text' id='searchstringHelp' name='searchstringHelp' class='form-control' placeholder='" ?><?php echo _("Search for"); ?><?php echo "...'>
                    <span class='input-group-btn'>
                    <button type='submit' name='do_searchhelp' class='btn btn-sm btn-success'>" . _("Search") . "</button>
                    </span>
                  </div>
                </div>
                </form>
                <div class='col-md-12 col-sm-12 col-xs-12'>
                  Quick help<br>
                </div>
                <table id='TableQuickHelp' cellspacing='0'>
                  <tbody>
                      <tr>
                      <td><a href='#viewgoogleauthenticator' data-toggle='modal' data-target='#viewgoogleauthenticator'>" . _('Google Authenticator') . "</a></td>
                      <td><a href='#viewguihelp' data-toggle='modal' data-target='#viewguihelp'>" . _('Gui') . "</a></td>
                      <td><a href='#viewshortcuts' data-toggle='modal' data-target='#viewshortcuts'>" . _('Shortcuts') . "</a></td>
                      </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>";
                                                                                                                                                          ?>
        <?php
        $ModuleID = 1;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        if (in_array("1", $ActiveModules)) {
          if (in_array("100001", $group_array) || in_array("3", $group_array) || in_array("5", $group_array)) {
            echo "
                  <div class='card-collapse'>
                    <div class='card-header' role='tab' id='headingsearch1'>
                      <h5 class='mb-0'>
                      <a class='collapsed' data-bs-toggle='collapse' href='#collapsesearch1' aria-expanded='false' aria-controls='collapsesearch1'>
                      <p class='category align-middle'><span i class='$ModuleIconName'></i></span> " ?><?php echo _("Search");
                                                                                                        echo " " . _("in");
                                                                                                        echo " " . _("incident");
                                                                                                        echo " " . _("solutions") ?><?php echo "
                      <i class='fas fa-angle-down'></i></p>
                    </a>
                      </h5>
                    </div>
                    <div id='collapsesearch1' class='collapse' role='tabpanel' aria-labelledby='headingsearch1' data-parent='#accordionSearch' style=''>
                      <div class='card-body'>
                        <form method='post'>
                        <div class='col-md-12 col-sm-12 col-xs-12 form-group top_search'>
                            <div class='input-group'>
                                <input type='text' id='incidentssearchstringsolutions' name='incidentssearchstringsolutions' class='form-control' placeholder='" ?><?php echo _("Search for"); ?><?php echo "...'>
                                <span class='input-group-btn'>
                                <button type='submit' name='do_search_solutions' class='btn btn-sm btn-success'>" . _("Search") . "</button>
                                </span>
                            </div>
                        </div>
                        </form>
                      </div>
                    </div>
                  </div>";
                                                                                                                                                                                                }
                                                                                                                                                                                              }
                                                                                                                                                                                                  ?>
        <?php
        $ModuleID = 5;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        if (in_array("5", $ActiveModules)) {
          if (in_array("100001", $group_array) || in_array("14", $group_array) || in_array("15", $group_array)) {
            echo "
                  <div class='card-collapse'>
                    <div class='card-header' role='tab' id='headingsearch2'>
                      <h5 class='mb-0'>
                        <a class='collapsed' data-bs-toggle='collapse' href='#collapsesearch2' aria-expanded='false' aria-controls='collapsesearch2'>
                          <p class='category align-middle'><span i class='$ModuleIconName'></i></span> " ?><?php echo _("Search");
                                                                                                            echo " " . _("in");
                                                                                                            echo " " . _("Problem");
                                                                                                            echo " " . _("descriptions") ?><?php echo "
                          <i class='fas fa-angle-down'></i></p>
                        </a>
                      </h5>
                    </div>
                    <div id='collapsesearch2' class='collapse' role='tabpanel' aria-labelledby='headingsearch2' data-parent='#accordionSearch' style=''>
                      <div class='card-body'>
                        <form method='post'>
                        <div class='col-md-12 col-sm-12 col-xs-12 form-group top_search'>
                            <div class='input-group'>
                                <input type='text' id='searchstringproblems' name='searchstringproblems' class='form-control' placeholder='" ?><?php echo _("Search for"); ?><?php echo "...'>
                                <span class='input-group-btn'>
                                <button type='submit' name='do_searchproblems' class='btn btn-sm btn-success'>" . _("Search") . "</button>
                                </span>
                            </div>
                        </div>
                        </form>
                      </div>
                    </div>
                  </div>";
                                                                                                                                                                            }
                                                                                                                                                                          }
                                                                                                                                                                              ?>
        <?php
        $ModuleID = 2;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        if (in_array("2", $ActiveModules)) {
          if (in_array("100001", $group_array) || in_array("8", $group_array) || in_array("11", $group_array)) {
            echo "
            <div class='card-collapse'>
              <div class='card-header' role='tab' id='headingsearch3'>
                <h5 class='mb-0'>
                  <a class='collapsed' data-bs-toggle='collapse' href='#collapsesearch3' aria-expanded='false' aria-controls='collapsesearch3'>
                    <p class='category align-middle'><span i class='$ModuleIconName'></i></span> " ?><?php echo _("Search in") . " " . _("change") . " " . _("description") ?><?php echo "
                    <i class='fas fa-angle-down'></i></p>
                  </a>
                </h5>
              </div>
              <div id='collapsesearch3' class='collapse' role='tabpanel' aria-labelledby='headingsearch3' data-parent='#accordionSearch'>
                <div class='card-body'>
                  <form method='post'>
                    <div class='col-md-12 col-sm-12 col-xs-12 form-group top_search'>
                      <div class='input-group'>
                        <input type='text' id='searchstringchanges' name='searchstringchanges' class='form-control' placeholder='" ?><?php echo _("Search for"); ?><?php echo "...'>
                        <span class='input-group-btn'>
                        <button type='submit' name='do_searchchanges' class='btn btn-sm btn-success'>" . _("Search") . "</button>
                        </span>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>";
                                                                                                                                                                  }
                                                                                                                                                                }
                                                                                                                                                                    ?>

        <?php
        $ModuleID = 3;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        if (in_array("3", $ActiveModules)) {
          if (in_array("100001", $group_array) || in_array("8", $group_array) || in_array("11", $group_array)) {
            echo "
              <div class='card-collapse'>
                <div class='card-header' role='tab' id='headingsearch4'>
                  <h5 class='mb-0'>
                    <a class='collapsed' data-bs-toggle='collapse' href='#collapsesearch4' aria-expanded='false' aria-controls='collapsesearch4'>
                      <p class='category align-middle'><span i class='$ModuleIconName'></i></span> " ?><?php echo _("Search document names") ?><?php echo "
                      <i class='fas fa-angle-down'></i></p>
                    </a>
                  </h5>
                </div>
                <div id='collapsesearch4' class='collapse' role='tabpanel' aria-labelledby='headingsearch4' data-parent='#accordionSearch'>
                  <div class='card-body'>
                    <form method='post'>
                      <div class='col-md-12 col-sm-12 col-xs-12 form-group top_search'>
                        <div class='input-group'>
                          <input type='text' id='searchstringdocName' name='searchstringdocName' class='form-control' placeholder='" ?><?php echo _("Search for"); ?><?php echo "...'>
                          <span class='input-group-btn'>
                          <button type='submit' name='do_searchdocName' class='btn btn-sm btn-success'>" . _("Search") . "</button>
                          </span>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>";
                                                                                                                                                                    }
                                                                                                                                                                  }
                                                                                                                                                                      ?>

        <?php
        $ModuleID = 3;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        if (in_array("3", $ActiveModules)) {
          if (in_array("100001", $group_array) || in_array("8", $group_array) || in_array("11", $group_array)) {
            echo "
              <div class='card-collapse'>
                <div class='card-header' role='tab' id='headingsearch5'>
                  <h5 class='mb-0'>
                    <a class='collapsed' data-bs-toggle='collapse' href='#collapsesearch5' aria-expanded='false' aria-controls='collapsesearch5'>
                      <p class='category align-middle'><span i class='$ModuleIconName'></i></span> " ?><?php echo _("Search document content") ?><?php echo "
                      <i class='fas fa-angle-down'></i></p>
                    </a>
                  </h5>
                </div>
                <div id='collapsesearch5' class='collapse' role='tabpanel' aria-labelledby='headingsearch5' data-parent='#accordionSearch'>
                  <div class='card-body'>
                    <form method='post'>
                      <div class='col-md-12 col-sm-12 col-xs-12 form-group top_search'>
                        <div class='input-group'>
                          <input type='text' id='searchstringdoccontent' name='searchstringdoccontent' class='form-control' placeholder='" ?><?php echo _("Search for"); ?><?php echo "...'>
                          <span class='input-group-btn'>
                          <button type='submit' name='do_searchdoccontent' class='btn btn-sm btn-success'>" . _("Search") . "</button>
                          </span>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>";
                                                                                                                                                                          }
                                                                                                                                                                        }
                                                                                                                                                                            ?>

        <?php
        $ModuleID = 3;
        $ModuleIconName = getModuleTypeIconName($ModuleID);
        if (in_array("3", $ActiveModules)) {
          if (in_array("100001", $group_array) || in_array("8", $group_array) || in_array("11", $group_array)) {
            echo "
              <div class='card-collapse'>
                <div class='card-header' role='tab' id='headingsearch6'>
                  <h5 class='mb-0'>
                    <a class='collapsed' data-bs-toggle='collapse' href='#collapsesearch6' aria-expanded='false' aria-controls='collapsesearch6'>
                      <p class='category align-middle'><span i class='$ModuleIconName'></i></span> " ?><?php echo _("Search document attachement content") ?><?php echo "
                      <i class='fas fa-angle-down'></i></p>
                    </a>
                  </h5>
                </div>
                <div id='collapsesearch6' class='collapse' role='tabpanel' aria-labelledby='headingsearch6' data-parent='#accordionSearch'>
                  <div class='card-body'>
                    <form method='post'>
                      <div class='col-md-12 col-sm-12 col-xs-12 form-group top_search'>
                        <div class='input-group'>
                          <input type='text' id='searchstringdocattcontent' name='searchstringdocattcontent' class='form-control' placeholder='" ?><?php echo _("Search for"); ?><?php echo "...'>
                          <span class='input-group-btn'>
                          <button type='submit' name='do_searchdocattcontent' class='btn btn-sm btn-success'>" . _("Search") . "</button>
                          </span>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
              </div>";
                                                                                                                                                                                }
                                                                                                                                                                              }
                                                                                                                                                                                  ?>
      </div>
      <?php
      $searchstringdocName = "";
      if (isset($_POST['do_searchhelp'])) {
        $searchstringhelp = $_POST['searchstringHelp']; ?>
        <div class="col-md-12 col-sm-12 col-xs-12 pull-center">
          <table id="tablesearchstringhelp" class="table table-responsive table-borderless table-hover" cellspacing="0">
            <thead>
              <tr>
                <th>Document ID</th>
                <th>Document Name</th>
              </tr>
            </thead>
            <?php

            $sql = "SELECT knowledge_documents.ID, Content, Name
              FROM knowledge_documents
              WHERE knowledge_documents.RelatedStatusID = 5 
              AND MATCH(Content) AGAINST ('$searchstringhelp'  IN NATURAL LANGUAGE MODE);";

            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            ?>
            <tbody>
              <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tr>
                  <td><?php echo "<a href='knowledge_view.php?docid=" . $row['ID'] . "'><button class='btn btn-sm btn-warning'></i>" . $row['ID'] . "</button></a>"; ?> </td>
                  <td><small><?php echo $row['Name']; ?></small></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>

      <?php
      if (isset($_POST['do_search_solutions'])) {
        $searchstringtickets = $_POST['incidentssearchstringsolutions']; ?>
        <div class="col-md-12 col-sm-12 col-xs-12 pull-center">
          <table id="searchresultssolutions" class="table table-responsive table-borderless table-hover" cellspacing="0">
            <thead>
              <tr>
                <th>ID</th>
                <th><?php echo _("Description") ?></th>
              </tr>
            </thead>
            <?php
            $sql = "SELECT tickets.ID, tickets.SolutionTextFullText, tickets.Subject
                  FROM tickets
                  WHERE MATCH(tickets.SolutionTextFullText) AGAINST ('$searchstringtickets' IN NATURAL LANGUAGE MODE);";

            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            ?>
            <tbody>
              <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tr>
                  <td><?php echo "<a href='incidents_view.php?elementid=" . $row['ID'] . "'><button class='btn btn-sm btn-warning'></i>" . $row['ID'] . "</button></a>"; ?> </td>
                  <td><?php echo $row['Subject']; ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>

      <?php
      if (isset($_POST['do_searchproblems'])) {
        $searchstringproblems = $_POST['searchstringproblems']; ?>
        <div class="col-md-12 col-sm-12 col-xs-12 pull-center">
          <table id="searchresultsproblems" class="table table-responsive table-borderless table-hover" cellspacing="0">
            <thead>
              <tr>
                <th>Problem ID</th>
                <th>Problem description</th>
              </tr>
            </thead>
            <?php

            $sql = "SELECT problems.ID, Description
        FROM problems
        WHERE MATCH(Description) AGAINST ('$searchstringproblems' IN NATURAL LANGUAGE MODE);";

            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            ?>
            <tbody>
              <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tr>
                  <td><?php echo "<a href='problems_view.php?elementid=" . $row['ID'] . "'><button class='btn btn-sm btn-warning'></i>" . $row['ID'] . "</button></a>"; ?> </td>
                  <td><?php echo $row['Description']; ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>

      <?php
      $searchstringchanges = "";
      if (isset($_POST['do_searchchanges'])) {
        $searchstringchanges = $_POST['searchstringchanges']; ?>
        <div class="col-md-12 col-sm-12 col-xs-12 pull-center">
          <table id="searchresultschanges" class="table table-responsive table-borderless table-hover" cellspacing="0">
            <thead>
              <tr>
                <th>Change ID</th>
                <th><?php echo _("Change") . " " . _("Description") ?></th>
              </tr>
            </thead>
            <?php

            $sql = "SELECT changes.ID, Subject
        FROM changes
        WHERE MATCH(Subject) AGAINST ('$searchstringchanges' IN NATURAL LANGUAGE MODE);";

            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            ?>
            <tbody>
              <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tr>
                  <td><?php echo "<a href='changes_view.php?elementid=" . $row['ID'] . "'><button class='btn btn-sm btn-warning'></i>" . $row['ID'] . "</button></a>"; ?> </td>
                  <td><?php echo $row['Subject']; ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>

      <?php
      $searchstringdocName = "";
      if (isset($_POST['do_searchdocName'])) {
        $searchstringdocName = $_POST['searchstringdocName']; ?>
        <div class="col-md-12 col-sm-12 col-xs-12 pull-center">
          <table id="searchresultsdocnames" class="table table-responsive table-borderless table-hover" cellspacing="0">
            <thead>
              <tr>
                <th>Document ID</th>
                <th>Document Name</th>
              </tr>
            </thead>
            <?php

            $sql = "SELECT knowledge_documents.ID, Name
              FROM knowledge_documents
              WHERE knowledge_documents.RelatedStatusID = 5 
              AND knowledge_documents.RelatedGroupID IN('" . implode("','", $group_array) . "')
              AND MATCH(Name) AGAINST ('$searchstringdocName' IN NATURAL LANGUAGE MODE);";

            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            ?>
            <tbody>
              <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tr>
                  <td><?php echo "<a href='knowledge_view.php?docid=" . $row['ID'] . "'><button class='btn btn-sm btn-warning'></i>" . $row['ID'] . "</button></a>"; ?> </td>
                  <td><?php echo $row['Name']; ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>

      <?php
      $searchstringdocContent = "";
      if (isset($_POST['do_searchdoccontent'])) {
        $searchstringdocContent = $_POST['searchstringdoccontent']; ?>
        <div class="col-md-12 col-sm-12 col-xs-12 pull-center">
          <table id="searchresultsdoccontents" class="table table-responsive table-borderless table-hover" cellspacing="0">
            <thead>
              <tr>
                <th>Document ID</th>
                <th>Document Content</th>
              </tr>
            </thead>
            <?php

            $sql = "SELECT knowledge_documents.ID, Content, ContentFullText 
                FROM knowledge_documents
                WHERE knowledge_documents.RelatedStatusID = 5 
                AND knowledge_documents.RelatedGroupID IN('" . implode("','", $group_array) . "')
                AND MATCH(ContentFullText ) AGAINST ('$searchstringdocContent' IN NATURAL LANGUAGE MODE);";

            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            ?>
            <tbody>
              <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tr>
                  <td><?php echo "<a href='knowledge_view.php?docid=" . $row['ID'] . "'><button class='btn btn-sm btn-warning'></i>" . $row['ID'] . "</button></a>"; ?> </td>
                  <td><?php echo $row['Content']; ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>
      <?php
      $searchstringdocAttContent = "";
      if (isset($_POST['do_searchdocattcontent'])) {
        $searchstringdocAttContent = $_POST['searchstringdocattcontent']; ?>
        <div class="col-md-12 col-sm-12 col-xs-12 pull-center">
          <table id="searchresultsdocattcontents" class="table table-responsive table-borderless table-hover" cellspacing="0">
            <thead>
              <tr>
                <th>Document ID</th>
                <th>Attachment Name</th>
                <th>Attachment Dato</th>
              </tr>
            </thead>
            <?php

            $sql = "SELECT knowledge_documents.ID, files_documents.FileContent, files_documents.FileNameOriginal, files_documents.Date
                FROM knowledge_documents 
                LEFT JOIN files_documents ON knowledge_documents.ID = files_documents.RelatedElementID
                WHERE knowledge_documents.RelatedStatusID = 5 
                AND knowledge_documents.RelatedGroupID IN('" . implode("','", $group_array) . "')
                AND MATCH(FileContent) AGAINST ('$searchstringdocAttContent' IN NATURAL LANGUAGE MODE);";

            $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
            ?>
            <tbody>
              <?php while ($row = mysqli_fetch_array($result)) { ?>
                <tr>
                  <td><?php echo "<a href='knowledge_view.php?docid=" . $row['ID'] . "'><button class='btn btn-sm btn-warning'></i>" . $row['ID'] . "</button></a>"; ?> </td>
                  <td><?php echo $row['FileNameOriginal']; ?></td>
                  <td><?php echo convertToDanishTimeFormat($row['Date']); ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } ?>
    </div>

    <div class="tab-pane" id="Guides">
    </div>
  </div>
</div>


<script>
  /* Set the width of the side navigation to 350px and the left margin of the page content to 350px and add a black background color to body */
  function openNav() {
    $(document).ready(function() {
      screen_width = document.documentElement.clientWidth;
      screen_heght = document.documentElement.clientHeight;

      if (screen_width > 1000) {
        document.getElementById("mySidebar").style.width = "400px";
        document.getElementById("wrapper").style.marginRight = "400px";
      }
    });
  }
  /* Set the width of the side navigation to 0 and the left margin of the page content to 0, and the background color of body to white */
  function closeNav() {
    document.getElementById("mySidebar").style.width = "0px";
    document.getElementById("wrapper").style.marginRight = "400px";
  }
</script>