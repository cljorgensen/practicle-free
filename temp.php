<script>
  function updateUser() {

    let id = document.getElementById('id').value;
    let username = document.getElementById('username').value;
    let rolle = document.getElementById('rolle').value;
    let fname = document.getElementById('fname').value;
    let lname = document.getElementById('lname').value;
    let verify = document.getElementById('verify').value;

    $.ajax({
      type: "POST",
      url: "./getdata.php?updateUser",
      data: {
        id: id,
        username: username,
        rolle: rolle,
        fname: fname,
        lname: lname,
        verify: verify,
      },
      success: function(data) {
        obj = JSON.parse(data);
        for (var i = 0; i < obj.length; i++) {
          let Result = obj[i].Result;
          if (Result == "success") {
            console.log("Jubii det gik godt");
          } else {
            let Message = obj[i].Message;
            alert(Message);
          }
        }
      },
      error: function(xhr, status, error) {
        console.error("Error fetching user data:", error);
      },
      complete: function(data1) {
        alert('fedt - det lykkedes - jeg har nu gennemført data modtagelsen fra php');
      },
    });
  }
</script>

<?php
if (isset($_GET['updateUser'])) {

  $id = $_POST['id'];
  $username = $_POST['username'];
  $rolle = $_POST['rolle'];
  $fname = $_POST['fname'];
  $lname = $_POST['lname'];
  $verify = $_POST['verify'];

  $Result = updateUser($id, $username, $rolle, $fname, $lname, $verify);

  // Check result of updateUser function
  if ($Result === "Gemt") {
    echo json_encode(["Result" => "success"]);
  } else {
    echo json_encode(["Result" => "error", "Message" => "Failed to update user"]);
  }
}

function updateUser($id, $username, $rolle, $fname, $lname, $verify)
{
  global $conn;

  $sql = "UPDATE BCB_User
          SET username = ?, rolle = ?, fname = ?, lname = ?, verify = ?
          WHERE id = ?";

  try {
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
      throw new Exception('Prepare statement failed.');
    }

    mysqli_stmt_bind_param($stmt, "sssssi", $username, $rolle, $fname, $lname, $verify, $id);

    if (!mysqli_stmt_execute($stmt)) {
      throw new Exception('Execute statement failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_close($stmt);

    return "Gemt";
  } catch (Exception $e) {
    return "Fejl: " . $e->getMessage();
  }
}


?>