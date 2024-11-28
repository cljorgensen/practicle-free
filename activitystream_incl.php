<script>
  $(document).ready(function() {

    var table = $('#activitiesTable').DataTable({
      dom: 'Brftp',
      searching: false,
      processing: true,
      serverSide: true,
      responsive: true,
      paging: true,
      pagingType: 'numbers',
      pageLength: 5,
      buttons: [],
      info: false,
      drawCallback: function(settings) {
        $("#activitiesTable thead").remove();
      },
      language: {
        url: '../assets/js/DataTables/Languages/<?php echo $UserLanguageCode ?>.json'
      },

      ajax: function(data, callback, settings) {
        fetch('getdata.php?getActivities', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
            },
            body: new URLSearchParams(data).toString()
          })
          .then(response => response.json())
          .then(json => callback(json))
          .catch(error => console.error('Error:', error));
      },

      columns: [{
        data: 'Headline',
        render: function(data, type, row) {
          var ID = row.ID;
          var FullName = row.FullName;
          var activityDate = row.Date;
          var Text = row.Text;
          var Url = row.Url;

          var formattedDate = new Date(activityDate).toLocaleString('da-DK', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
          });

          return `
              <div class="card card-body-dropdown text-wrap">
                <div data-bs-toggle="collapse" href="#collapseActivity${ID}" role="button" aria-expanded="false" aria-controls="collapseActivity${ID}">
                  <small class="float-left">${data}</small><small class="float-end">${FullName}</small>
                </div>
                <div class="text-wrap collapse" id="collapseActivity${ID}">
                  <br>
                  <p class='text-sm text-secondary mb-0 text-wrap'>Date: ${formattedDate}</p>
                  <br>
                  <p class='text-sm text-secondary mb-0 text-wrap'>${Text}</p>
                  <br>
                  <a href="${Url}" class="badge badge-pill bg-gradient-success" title="Open"><i class="fa fa-pen-to-square"></i></a>
                </div>
              </div>
            `;
        }
      }]
    });
  });
</script>


<table id="activitiesTable" class="table table-borderless dt-responsive" cellspacing="0" style="width: 100%;">
</table>