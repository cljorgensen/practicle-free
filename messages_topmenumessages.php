<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
?>

<div id="MessagesQuery">
  <?php
  $sql = "SELECT messages.ID AS MessageID, messages.SendDate, messages.Message, messages.ReadDate, (SELECT CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName FROM users INNER JOIN messages ON messages.FromUserID = users.ID WHERE messages.ID = MessageID) AS FromUser,
              (SELECT CONCAT(users.firstname,' ',users.lastname,' (',users.username,')') AS FullName FROM users INNER JOIN messages ON messages.ToUserID = users.ID WHERE messages.ID = MessageID) AS ToUser
              FROM messages
              WHERE messages.ToUserID=" . ($_SESSION["id"]) . " AND messages.ReadDate IS NULL 
              ORDER BY SendDate DESC LIMIT 5";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo "<a class='dropdown-item' href='messages_inbox.php?messageid=" . $row['MessageID'] . "'>" . convertToDanishTimeFormat($row["SendDate"]) .  "<br>";
      echo $row["FromUser"] . "</a>";
    }
    mysqli_free_result($result);
  } else {
    echo "<a class='dropdown-item'>No unread messages...</a>";
  }
  ?>
</div>