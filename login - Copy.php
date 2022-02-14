<?php session_start();
include('connection.php'); ?>
<!doctype html>  <!-- needs to have this, otherwise the bootstrap menu is distored a bit -->
<html>
<head>
    <?php include('navbar.php'); ?>
    <title>User login</title>
</head>
<body style="background-color: wheat">
<?php
$success = "N";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['username']) && !empty($_POST['password'])) {
        //Run some SQL query here to find that user
        $stmt = $dbh->prepare("SELECT * FROM `users` WHERE `username` = ? AND `password` = ?");
        if ($stmt->execute([
                $_POST['username'],
                hash('sha256', $_POST['password'])
            ]) && $stmt->rowCount() > 0) {
            $row = $stmt->fetchObject();
            $_SESSION['user_id'] = $row->id;
            $stmt->closeCursor();

            $success = "Y"; // set the indicator
            echo "<h1>You have successfully logged in.</h1>"; ?>

            <input type="button" value="Continue" OnClick="window.location.assign('index.php');">

        <?php
        } else {
            echo "<h1>Either username or password is incorrect!</h1>";
        }
    }
} else {
    if (isset($_SESSION['user_id'])) {
        $user_stmt = $dbh->prepare("SELECT * FROM `users` WHERE `id` = ?");
        if ($user_stmt->execute([$_SESSION['user_id']]) && $user_stmt->rowCount() == 1) {
            $success = "Y"; // set the indicator
            $user_stmt->closeCursor();
            echo "<h1>You have already logged in.</h1>"; ?>

        <input type="button" value="Continue" OnClick="window.location.assign('index.php');">

        <?php
        } else {
            echo "<h1>Your account does not exist!</h1>";
            session_destroy();
        }
    } else {
        echo "<h1>Please Login</h1>";
    }
}

if ($success == "N") {
?>

<form method="post">
    <label for="username">Username</label>
    <input type="text" id="username" name="username"/>
    <br>
    <label for="password">Password&nbsp;</label>  <!-- &nbsp; is a non breaking space -->
    <input type="password" id="password" name="password"/>
    <br>
    <input type="submit" value="Login"/>
</form>
</body>
</html>
<?php } ?>