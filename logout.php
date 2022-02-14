<?php session_start();
include('connection.php'); ?>
<!doctype html>  <!-- needs to have this, otherwise the bootstrap menu is distored a bit -->
<html>
<head>
    <?php include('navbar.php'); ?>
    <title>User logout</title>
</head>
<body style="background-color: wheat">
<body>
    <?php
    if (isset($_GET['action']) && $_GET['action'] == 'logout') {
	session_destroy();
	echo "<h1>You have logged out successfully. </h1>"; ?>

    <input type="button" value="Continue" OnClick="window.location.assign('index.php');">

    <?php
	// header("Location: index.php");
    } else {
	if (isset($_SESSION['user_id'])) {
            $user_stmt = $dbh->prepare("SELECT * FROM `users` WHERE `id` = ?");
            if ($user_stmt->execute([$_SESSION['user_id']]) && $user_stmt->rowCount() == 1) {
        $user = $user_stmt->fetchObject();
        $user_stmt->closeCursor();
		echo "<h1>" . $user->username . ", are you sure you want to log out?</h1>"; ?>

    <input type="button" value="Click here to logout" OnClick="window.location.assign('?action=logout');">
    <input type="button" value="Cancel" OnClick="window.location.assign('index.php');">

        <?php
            } else {
		        echo "<h1>Your account does not exist!</h1>";
		        session_destroy();
            }
        }
    }
    ?>
</body>
</html>
