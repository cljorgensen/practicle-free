<script>
  //Get screen size
  screen_width = document.documentElement.clientWidth;

  if (screen_width < 575) {
    viewport = 'sm'
  } else if (screen_width >= 575 && screen_width < 990) {
    viewport = 'med'
  } else if (screen_width >= 990) {
    viewport = 'lg'
  }

  $(document).ready(function() {
    //Redraw datatables on tab click
    $('[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
      $.fn.dataTable
        .tables({
          visible: true,
          api: true
        })
        .columns.adjust();
    })

    //Tab behavior - creates href tag in address bar in order to link to active tabs
    if (location.hash) {
      $("[href='" + location.hash + "']").tab("show");
      if (viewport !== 'sm') {
        document.getElementById("AutoScrollLocation").scrollIntoView();
      } else {
        document.getElementById("activetablist").scrollIntoView();
      }
    }
    $(document.body).on("click", "[data-bs-toggle='tab']",
      function(event) {

        location.hash = this.getAttribute("href");
        if (viewport !== 'sm') {
          document.getElementById("AutoScrollLocation").scrollIntoView();
        } else {
          document.getElementById("activetablist").scrollIntoView();
        }
      });

    $(window).on("popstate", function() {
      var anchor = location.hash || $("[data-bs-toggle='tab']").first().attr("href");
      $("[href='" + anchor + "']").tab("show");

      if (viewport !== 'sm') {
        document.getElementById("AutoScrollLocation").scrollIntoView();
      } else {
        document.getElementById("activetablist").scrollIntoView();
      }

    });

    //If internal comment is chosen, we set notify to no as help.
    $("#InternalComment").on('change', function(e) {
      var selectValue = (this.value);
      if (selectValue == 'Yes') {
        document.getElementById('NotifyCustomer').value = 'No';
        $('NotifyCustomer').select('NotifyCustomer');
      }
      if (selectValue == 'No') {
        document.getElementById('NotifyCustomer').value = 'Yes';
        $('NotifyCustomer').select('NotifyCustomer');
      }
    });
  });
</script>