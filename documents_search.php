<?php include("./header.php") ?>
<?php
if (in_array("100001", $group_array) || in_array("3", $group_array) || in_array("5", $group_array)) {
} else {
  $CurrentPage = htmlspecialchars(basename($_SERVER['PHP_SELF']));
	notgranted($CurrentPage);
}
?>
<?php
$SolutionText = "";
?>

<div class="row">
  <div class="col-md-4 col-sm-4 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header"><i class="fas fa-file-word fa-lg"></i> <a href="javascript:location.reload();"><?php echo _("Search"); ?></a>
          <div class="float-end">
            <ul class="navbar-nav justify-content-end">
              <li class="nav-item dropdown pe-2">
                <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                  &nbsp;&nbsp;<i class="fa fa-circle-chevron-down" data-bs-toggle="tooltip" data-bs-title="<?php echo _("Documents") ?>"></i>&nbsp;&nbsp;
                </a>
                <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="runmodalcreatenew()">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Create"); ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class="mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href='knowledge_overview.php'">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Categories"); ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class=" mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href='knowledge.php';">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("My Articles"); ?>
                          </h6>
                        </div>
                      </div>
                    </a>
                  </li>
                  <li class=" mb-2">
                    <a class="dropdown-item border-radius-md" onclick="window.location.href='documents_search.php'">
                      <div class="d-flex align-items-center py-1">
                        <div class="ms-2">
                          <h6 class="text-sm font-weight-normal my-auto">
                            <?php echo _("Search"); ?>
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
          <form action="documents_search.php" method="post">

            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="input-group input-group-static mb-4">
                <label for="documentssearchstringname"><?php echo _("Article name") ?></label>
                <input type="text" id="documentssearchstringname" name="documentssearchstringname" class="form-control">
              </div>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
              <button name="do_search_name" class="btn btn-sm btn-success" type="submit"><?php echo _("Search"); ?></button>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="input-group input-group-static mb-4">
                <label for="documentssearchstringcontent" class="ms-0"><?php echo _("Article content") ?></label>
                <input type="text" id="documentssearchstringcontent" name="documentssearchstringcontent" class="form-control">
              </div>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
              <button name="do_search_content" class="btn btn-sm btn-success" type="submit"><?php echo _("Search"); ?></button>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="input-group input-group-static mb-4">
                <label for="documentssearchstringattcontent" class="ms-0"><?php echo _("Article attachments") ?></label>
                <input type="text" id="documentssearchstringattcontent" name="documentssearchstringattcontent" class="form-control">
              </div>
            </div>

            <div class="col-md-12 col-sm-12 col-xs-12">
              <button name="do_search_attcontent" class="btn btn-sm btn-success" type="submit"><?php echo _("Search"); ?></button>
            </div>
          </form>
          <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <details>
              <summary><?php echo  _("Other searches") ?></summary>
              <br>
              <label><?php echo  _("Scan for CVR numbers in WIKI") ?></label><br>
              <button class="btn btn-sm btn-success" onclick="scanForCVRInWiki('');"><?php echo _("Scan"); ?></button>
            </details>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-8 col-sm-8 col-xs-12">
    <div class="card-group">
      <div class="card">
        <div class="card-header"> <?php echo _("Search results"); ?>
        </div>
        <div class="card-body">
          <div id="resultFromWikiCVRScan"></div>

          <script>
            $(document).ready(function() {
              <?php initiateStandardSearchTable("searchresults"); ?>
            });
          </script>

          <?php
          if (isset($_POST['do_search_name'])) {
            $searchstring = $_POST['documentssearchstringname'];
          ?>
            <div id="accordionName" role="tablist">
              <table id="searchresults" class="table table-responsive table-borderless table-hover" cellspacing="0">
                <thead>
                  <tr>
                    <th><?php echo _("ID") ?></th>
                    <th><?php echo _("Name") ?></th>
                  </tr>
                </thead>
                <?php

                $sql = "SELECT knowledge_documents.ID, Name
                        FROM knowledge_documents
                        WHERE knowledge_documents.RelatedStatusID = 5 
                        AND knowledge_documents.RelatedGroupID IN('" . implode("','", $group_array) . "')
                        AND MATCH(Name) AGAINST ('$searchstring' IN NATURAL LANGUAGE MODE);";

                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                ?>
                <tbody>
                  <?php while ($row = mysqli_fetch_array($result)) { ?>
                    <?php
                    $ID = $row['ID'];
                    $Name = $row['Name'];
                    $NameHeader = substr($row['Name'], 0, 100);
                    ?>

                    <tr>
                      <td width="10%"><?php echo "<a href='knowledge_view.php?docid=$ID'><button class='btn btn-sm btn-warning' data-bs-toggle='tooltip' data-bs-title='" . _("Open change") . "'></i>$ID</button></a>" ?> </td>
                      <td width="90%">
                        <div class="card-collapse">
                          <div class="card-header" role="tab" id="heading<?php echo $ID; ?>">
                            <h5 class="mb-0">
                              <a class="collapsed" data-bs-toggle="collapse" href="#collapse<?php echo $ID; ?>" aria-expanded="false" aria-controls="collapse<?php echo $ID; ?>">
                                <p class='category align-middle'><?php echo "<div class='col-md-12 col-sm-12 col-xs-12'>" . $NameHeader . "</div>"; ?></p>
                              </a>
                            </h5>
                          </div>
                          <div id="collapse<?php echo $ID; ?>" class="collapse" role="tabpanel" aria-labelledby="heading<?php echo $ID; ?>" data-parent="#accordionName">
                            <div class="card-body">
                              <div class='col-md-12 col-sm-12 col-xs-12'>
                                <?php
                                echo "<h5>" . _("Name") . "</h5> " . $Name . "<br><br>";
                                ?>
                              </div>
                            </div>
                          </div>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          <?php } ?>

          <?php
          if (isset($_POST['do_search_content'])) {
            $searchstring = $_POST['documentssearchstringcontent']; ?>
            <div id="accordionContent" role="tablist">
              <table id="searchresults" class="table table-responsive table-borderless table-hover" cellspacing="0">
                <thead>
                  <tr>
                    <th><?php echo _("ID") ?></th>
                    <th><?php echo _("Name") ?></th>
                  </tr>
                </thead>
                <?php
                $sql = "SELECT knowledge_documents.ID, Name, Content, ContentFullText 
                          FROM knowledge_documents
                          WHERE knowledge_documents.RelatedStatusID = 5 
                          AND knowledge_documents.RelatedGroupID IN('" . implode("','", $group_array) . "')
                          AND MATCH(ContentFullText ) AGAINST ('$searchstring' IN NATURAL LANGUAGE MODE);";

                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                ?>
                <tbody>
                  <?php while ($row = mysqli_fetch_array($result)) { ?>
                    <?php
                    $ID = $row['ID'];
                    $NameHeader = substr($row['Name'], 0, 100);
                    $Name = $row['Name'];
                    $Content = $row['Content'];
                    ?>
                    <tr>
                      <td width="10%"><?php echo "<a href='knowledge_view.php?docid=$ID'><button class='btn btn-sm btn-warning' data-bs-toggle='tooltip' data-bs-title='" . _("Open change") . "'></i>$ID</button></a>" ?> </td>
                      <td width="90%">
                        <div class="card-collapse">
                          <div class="card-header" role="tab" id="heading<?php echo $ID; ?>">
                            <h5 class="mb-0">
                              <a class="collapsed" data-bs-toggle="collapse" href="#collapse<?php echo $ID; ?>" aria-expanded="false" aria-controls="collapse<?php echo $ID; ?>">
                                <p class='category align-middle'><?php echo "<div class='col-md-12 col-sm-12 col-xs-12'>" . $NameHeader . "</div>"; ?></p>
                              </a>
                            </h5>
                          </div>
                          <div id="collapse<?php echo $ID; ?>" class="collapse" role="tabpanel" aria-labelledby="heading<?php echo $ID; ?>" data-parent="#accordionContent">
                            <div class="card-body">
                              <div class='col-md-12 col-sm-12 col-xs-12'>
                                <?php
                                echo "<h5>" . _("Name") . "</h5><p>" . $Name . "</p><br><br>";
                                echo "<h5>" . _("Content") . "</h5><p>" . substr($Content, 0, 500) . "</p>";
                                ?>
                              </div>
                            </div>
                          </div>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          <?php } ?>

          <?php
          if (isset($_POST['do_search_attcontent'])) {
            $searchstring = $_POST['documentssearchstringattcontent']; ?>
            <div id="accordionattcontent" role="tablist">
              <table id="searchresults" class="table table-responsive table-borderless table-hover" cellspacing="0">
                <thead>
                  <tr>
                    <th><?php echo _("ID") ?></th>
                    <th><?php echo _("Date") ?></th>
                    <th><?php echo _("Name") ?></th>
                  </tr>
                </thead>
                <?php
                $sql = "SELECT knowledge_documents.ID, files_documents.FileContent, files_documents.FileNameOriginal, files_documents.Date
                          FROM knowledge_documents 
                          LEFT JOIN files_documents ON knowledge_documents.ID = files_documents.RelatedElementID
                          WHERE knowledge_documents.RelatedStatusID = 5 
                          AND knowledge_documents.RelatedGroupID IN('" . implode("','", $group_array) . "')
                          AND MATCH(FileContent) AGAINST ('$searchstring' IN NATURAL LANGUAGE MODE);";

                $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
                ?>
                <tbody>
                  <?php while ($row = mysqli_fetch_array($result)) { ?>
                    <?php
                    $ID = $row['ID'];
                    $NameHeader = substr($row['FileNameOriginal'], 0, 100);
                    $Name = $row['FileNameOriginal'];
                    $Content = $row['FileContent'];
                    ?>
                    <tr>
                      <td width="10%"><?php echo "<a href='knowledge_view.php?docid=$ID'><button class='btn btn-sm btn-warning' data-bs-toggle='tooltip' data-bs-title='" . _("Open Document") . "'></i>$ID</button></a>" ?> </td>
                      <td width="20%"><?php echo convertToDanishTimeFormat($row['Date']); ?></td>
                      <td width="70%">
                        <div class="card-collapse">
                          <div class="card-header" role="tab" id="heading<?php echo $ID; ?>">
                            <h5 class="mb-0">
                              <a class="collapsed" data-bs-toggle="collapse" href="#collapse<?php echo $ID; ?>" aria-expanded="false" aria-controls="collapse<?php echo $ID; ?>">
                                <p class='category align-middle'><?php echo "<div class='col-md-12 col-sm-12 col-xs-12'>" . $NameHeader . "</div>"; ?></p>
                              </a>
                            </h5>
                          </div>
                          <div id="collapse<?php echo $ID; ?>" class="collapse" role="tabpanel" aria-labelledby="heading<?php echo $ID; ?>" data-parent="#accordionattcontent">
                            <div class="card-body">
                              <div class='col-md-12 col-sm-12 col-xs-12'>
                                <?php echo $Content ?>
                              </div>
                            </div>
                          </div>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("./knowledge_createnew.php"); ?>
<?php include("./footer.php") ?>