  <!-- Navbar -->
  <script>
      function markAllReadNotifications(dummyvalue) {
          $.post('./getdata.php', {
              marknotificationsreadforuserid: dummyvalue
          });
      };

      function readAllNotifications() {
          var url = './getdata.php?getlatestnofication';
          $.ajax({
              url: url,
              data: {},
              type: 'POST',
              success: function(data) {
                  var obj = JSON.parse(data);
                  for (var i = 0; i < obj.length; i++) {
                      notificationid = obj[i].ID;
                      url = "notifications_inbox.php?notificationid=" + notificationid
                      window.location.href = url;
                  }
              }
          });
      }

      function markAllReadMessages(dummyvalue) {
          $.post('./getdata.php', {
              markmessagesreadforuserid: dummyvalue
          });
          pnotify('<?php echo _("All marked as read") ?>', 'success');
      };

      function readAllMessages() {
          var url = './getdata.php?getlatestmessage';
          $.ajax({
              url: url,
              data: {},
              type: 'POST',
              success: function(data) {
                  var obj = JSON.parse(data);
                  for (var i = 0; i < obj.length; i++) {
                      messageid = obj[i].ID;
                      url = "messages_inbox.php?messageid=" + messageid
                      window.location.href = url;
                  }
              }
          });
      }
  </script>
  <div id="AutoScrollLocation"></div>
  <?php
    if ($UserType == "1" || $UserType == "3") { ?>
      <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 border-radius-xl shadow-none" id="navbarBlur" data-scroll="true">
          <div class="container-fluid py-1 px-3">
              <div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none ">
                  <a href="javascript:;" class="nav-link text-body p-0">
                      <div class="sidenav-toggler-inner">
                          <i class="sidenav-toggler-line"></i>
                          <i class="sidenav-toggler-line"></i>
                          <i class="sidenav-toggler-line"></i>
                      </div>
                  </a>
              </div>
              <div class="spinner-border text-success" role="status" id="spinner" style="display: none;"></div>
              <div class="collapse navbar-collapse" id="navbar">
                  <div class="container">
                      <div class="d-flex justify-content-center">
                          <div class="input-group input-group-outline w-100" id="quicksearch">
                              <div id="searchlabel">
                                  <label class="form-label"><?php echo _("Search"); ?></label>
                              </div>
                              <input type="text" id="searchfield" name="searchfield" class="form-control" onfocus="expandSearch()">
                          </div>
                      </div>
                      <div id="search-results" class="search-results hidden"></div>
                  </div>

                  <ul class="navbar-nav justify-content-end">
                      <li class="nav-item">
                          <a href="./index.php" class="nav-link text-body p-0 position-relative">
                              <i class="material-icons me-sm-1">
                                  home
                              </i>
                          </a>
                      </li>

                      <li class="nav-item px-3">
                          <a href="javascript:toggleDarkmodeBtn();" class="nav-link text-body p-0">
                              <i class="material-icons fixed-plugin-button-nav cursor-pointer">
                                  arrow_drop_down_circle
                              </i>
                          </a>
                      </li>

                      <li class="nav-item dropdown pe-2">
                          <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="material-icons cursor-pointer">
                                  email
                              </i>
                              <?php
                                $UserID = $_SESSION["id"];
                                $NotificationNumbers = getMessagessNumber($UserID);
                                if ($NotificationNumbers == 0) {
                                    $color = "secondary";
                                } else {
                                    $color = "danger";
                                }
                                ?>
                              <span class="position-absolute top-5 start-100 translate-middle badge rounded-pill bg-<?php echo $color ?> border border-white small py-1 px-2">
                                  <span class='small'><?php echo $NotificationNumbers ?></span>
                                  <span class="visually-hidden">unread emails</span>
                              </span>
                          </a>
                          <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                              <li class="mb-2">
                                  <a class="dropdown-item border-radius-md" onclick='readAllMessages()'>
                                      <div class=" d-flex align-items-center py-1">
                                          <span class="material-icons">email</span>
                                          <div class="ms-2">
                                              <h6 class="text-sm font-weight-normal my-auto">
                                                  <?php echo _("Read"); ?>
                                              </h6>
                                          </div>
                                      </div>
                                  </a>
                              </li>
                              <li>
                                  <a class="dropdown-item border-radius-md" onclick="markAllReadMessages(this.id);">
                                      <div class="d-flex align-items-center py-1">
                                          <i class="fa-solid fa-check"></i>
                                          <div class="ms-2">
                                              <h6 class="text-sm font-weight-normal my-auto">
                                                  <?php echo _("Mark all as read"); ?>
                                              </h6>
                                          </div>
                                      </div>
                                  </a>
                              </li>
                          </ul>
                      </li>

                      <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                          <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                              <div class="sidenav-toggler-inner">
                                  <i class="sidenav-toggler-line"></i>
                                  <i class="sidenav-toggler-line"></i>
                                  <i class="sidenav-toggler-line"></i>
                              </div>
                          </a>
                      </li>
                  </ul>
              <?php } ?>
              </div>
          </div>
      </nav>
      <div class="container-fluid py-4">
          <div id="bodyContent">
              <?php include "./modals/modal_functions.php"; ?>