<?php session_start();
include('..\connection.php'); ?>
<!doctype html>  <!-- needs to have this, otherwise the bootstrap menu is distored a bit -->
<html>
<head>
<?php include('..\navbar.php'); ?>
<link rel="stylesheet" href="..\styles.css">
<title>Email page</title>
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

        // put new programme codes here
        if(!empty ($_POST['emails'])) {

            // the message
            $msg = $_POST['message'];
        
            // use wordwrap() if lines are longer than 70 characters
            $msg = wordwrap($msg, 70);
        
            $subject = $_POST['subject'];
        
            // join the email addresses with ', '
            $to = implode(', ', $_POST['emails']);
        
            echo "This is your email " . $to . " " . $subject . " " . $msg;
            // send email
            mail($to, $subject, $msg);
        } // if(!empty ($_POST['emails'])) {

        $query = "select first_name, surname, email from clients where subscribed=1 order by first_name, surname";

        $stmt = $dbh->prepare($query);
        $stmt -> execute();
        if (!$stmt->execute()) {
            $err = $stmt->errorInfo();
            echo "Error occurred while selecting clients subscribed to email communication - contact System Administrator. <br>Error is: <b>$err[2]</b>";
        }

        ?>
        <form method="post">
            <table border = "1" cellpadding="3">
                <tr>
                    <th>To: </th>
                    <th>First name</th>
                    <th>Surname</th>
                    <th>Email</th>
                </tr>
            <?php while ($row = $stmt->fetchObject()): ?>
                <tr>
                    <td><input type = "checkbox" name="emails[]" value="<?php echo $row->email; ?>" ></td>
                    <td><?php echo $row->first_name; ?></td>
                    <td><?php echo $row->surname; ?></td>
                    <td><?php echo $row->email; ?></td>
                </tr>
            <?php endwhile;
                $stmt->closeCursor(); ?>
            </table>
            <br> Please type your details in the spaces below <br> <br>
        
            <label for="subject">Subject</label> &nbsp;
            <input type="text" name="subject" size=50 required> <br>
        
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="10" cols="65" 
                    placeholder="Please type your message here." required></textarea> 
            
            <br> &nbsp; &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="Button" Value="Cancel" onclick="window.location.assign('http://localhost:8080/client/client.php')">
            <input type="Submit" Value="Submit">
        </form>

    <?php
    } else {
        echo "<h1>Your account does not exist!</h1>";
        session_destroy();
    }
} // if (!isset($_SESSION['user_id'])) {
?>

</body>
</html>
