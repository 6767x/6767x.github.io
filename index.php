<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Website</title>
</head>
<body>

<?php
// Include the Discord logger (make sure the path is correct)
include("Discord.php");

// Create an instance and run the logger
$logger = new Discord();
$logger->Visitor();
?>

<h1>Welcome to My Website</h1>
<p>Your content goes here.</p>

</body>
</html>
