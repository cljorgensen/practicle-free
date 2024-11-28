<?php

// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";

// Processing form data when form is submitted
if (isset($_POST['submit_password'])) {

    $username = ($_SESSION["username"]);

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter new password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials
    if (empty($password_err)) {
        // Prepare a select statement
        $sql = "SELECT id, Username, Password FROM Users WHERE Username='" . $username . "';";
        $saltsql = "SELECT SettingValue AS Salt FROM settings WHERE ID=4;";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind variables to the prepared statement as parameters
            //mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameters
            $param_username = $username;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                mysqli_stmt_store_result($stmt);

                // Check if username exists, if yes then verify password
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables
                    // Set parameters
                    $result = mysqli_query($conn, $saltsql);

                    if ($result->num_rows > 0) {
                        // output data of each row
                        while ($row = $result->fetch_assoc()) {
                            $salt  = $row["Salt"];
                        }
                    }
                    $saltedpassword = ($salt . $password);
                    $password = md5($saltedpassword);

                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);

                    $updatepassword = "UPDATE Users SET Password = '" . $password . "' WHERE Username = '" . $username . "';";
                    $callsleep = 0;
                    if ($conn->query($updatepassword) === TRUE) {
                        $redirectpagego = "<meta http-equiv='refresh' content='1';url=userprofile.php/><p><b><div class='alert alert-success'><strong>" . _("Password updated") . "</strong></div></b></p>";
                        echo $redirectpagego;
                    } else {
                        echo _("Error updating password") . ": " . $conn->error;
                    }
                } else {
                    // Display an error message if username doesn't exist
                    $username_err = _("No account found with that username");
                }
            } else {
                echo  _("Something went wrong, please try again later");
            }
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }
    // Close connection
    mysqli_close($conn);
}
?>
<meta charset="UTF-8">
<p>
    <h4><?php echo _("Change password") ?></h4>
</p>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
        <input type="password" name="password" class="form-control" placeholder="<?php echo _("Type new password") ?>">
        <span class="help-block"><?php echo $password_err; ?></span>
    </div>
    <div class="form-group">
        <button type="submit" name="submit_password" class="btn btn-sm btn-dark"><i class="fa fa-edit m-right-xs"></i> <?php echo _("Update") ?></button>
    </div>
</form>
</div>
</div>
</div>
</div>
<div class="clearfix"></div>