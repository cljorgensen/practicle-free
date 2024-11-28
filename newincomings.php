<?php
require_once "./functions/functions.php";
require_once('./inc/dbconnection.php');
?>

<script>
    setInterval(function() {
        $("#incomings").load("newincomings.php");
    }, 60000);
</script>

<div id="incomings">
    <?php
    $sql = "";
    $sql = "SELECT Tickets.ID AS TicketID, Companyname, Subject, Status, DateCreated 
                FROM Tickets
                INNER JOIN Companies ON Tickets.RelatedCompanyID = Companies.ID
                WHERE Status='1' OR Status='2' ORDER BY Tickets.ID DESC";
    $result = mysqli_query($conn, $sql) or die('Query fail: ' . mysqli_error($conn));
    while ($row = mysqli_fetch_array($result)) {
        echo "<a href='incidents_view.php?elementid=" . $row['TicketID'] . "'><b>" . $row['TicketID'];
        echo " ";
        echo $row['Companyname'];
        echo "</b> ";
        $myFormatForView = convertToDanishTimeFormat($row['DateCreated']);
        echo "(" . $myFormatForView . ")";
        echo "<br>";
    }
    ?>
</div>