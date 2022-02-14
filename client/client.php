<?php session_start();
include('..\connection.php'); ?>
<!doctype html>  <!-- needs to have this, otherwise the bootstrap menu is distored a bit -->
<html>
<head>
<?php include('..\navbar.php'); ?>

<title>Client page</title>
</head>
<body>
<?php
// not logged in
if (!isset($_SESSION['user_id'])) {
    // record the calling page
    $_SESSION['initial_page'] = $_SERVER['PHP_SELF'];
    // direct to login page
    header("Location: ..\login.php");
} else {
    // check the user's account has not been deleted in the meantime
    $user_stmt = $dbh->prepare("SELECT * FROM `users` WHERE `id` = ?");
    if ($user_stmt->execute([$_SESSION['user_id']]) && $user_stmt->rowCount() == 1) {
        $user = $user_stmt->fetchObject();
        // echo "<h1>" . $user->username . ", you are logged in.</h1>";
        $user_stmt->closeCursor();
        $client_stmt = $dbh->prepare("SELECT * FROM clients");
        if ($client_stmt->execute()) { ?>
            <br>
            <a href="insert.php"><button><h2 style="color:red;">Add a new client</h2></button></a>
            &nbsp; &nbsp; &nbsp;
            <a href="email.php"><button><h2 style="color:blue;">Send email to clients</h2></button></a>
            <p></p>
            
            <table border = "1">
                <tr>
                    <th>id</th>
                    <th>first_name</th>
                    <th>surname</th>
                    <th>address</th>
                    <th>phone</th>
                    <th>mobile</th>
                    <th>email</th>
                    <th>subscribed</th>
                    <th>update</th>
                    <th>delete</th>
                </tr>
                    <?php
                    while ($client = $client_stmt->fetch()): ?>
                        <tr>
                            <td><?php echo $client["id"]; ?></td>
                            <td><?php echo $client["first_name"]; ?></td>
                            <td><?php echo $client["surname"]; ?></td>
                            <td><?php echo $client["address"]; ?></td>
                            <td><?php echo $client["phone"]; ?></td>
                            <td><?php echo $client["mobile"]; ?></td>
                            <td><?php echo $client["email"]; ?></td>
                            <td><?php echo $client["subscribed"]; ?></td>
                            <td><a href="update.php?id=<?php echo $client["id"]; ?>">update</a></td>
                            <td><a href="delete.php?id=<?php echo $client["id"]; ?>">delete</a></td>
                        </tr>
                    <?php endwhile; $client_stmt->closeCursor(); ?>
                </table>
                <script>
                $("tr:odd").css("background-color", "lightgreen");
                </script>
         <?php   
        } // if ($client_stmt->execute()) {
    } else { 
        echo "<h1>Your account does not exist!</h1>";
        session_destroy();
    }
}
?>
</body>
</html>
