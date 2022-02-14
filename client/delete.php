<?php session_start();
include('..\connection.php'); ?>
<!doctype html>  <!-- needs to have this, otherwise the bootstrap menu is distored a bit -->
<html>
<head>
<?php include('..\navbar.php'); ?>
<script>
	function Confirm_Delete() {
	    var x = confirm("Are you sure you want to delete this client's record?\n\nWarning: related entries in child tables will also be deleted in cascade manner.");
        // the above return true if "OK" is chosen; return false if "Cancel" is chosen
        return x;
	 }
</script>
<title>Delete client</title>
</head>
<body>
<h1>Delete a client's info</h1>
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

        $id = $_GET["id"];
		$query = "SELECT * FROM clients where id=$id";
		// echo "This is the query to be executed: " . $query;
        $stmt = $dbh->prepare($query);
        $stmt->execute();
		$row = $stmt -> fetch();


	// if "Delete" button has not been pressed yet
	if (empty($_GET["Action"])) : ?>
        <form method="get">
            <table align="left" cellpadding="3">
		<tr>
		    <td><b>Client ID</b></td> <td><input type="text" name="id"
							 value="<?php echo $row["id"]; ?>" readonly></td>
		</tr>
		<tr>
		    <td><b>First Name</b></td> <td><?php echo $row["first_name"]; ?></td>
		</tr>
		<tr>
		    <td><b>Surname</b></td> <td><?php echo $row["surname"]; ?></td>
		</tr>
		<tr>
		    <td><b>Address</b></td> <td><?php echo $row["address"]; ?></td>
		</tr>
		<tr>
		    <td><b>Phone</b></td> <td><?php echo $row["phone"]; ?></td>
		</tr>
		<tr>
		    <td><b>Mobile</b></td> <td><?php echo $row["mobile"]; ?></td>
		</tr>
		<tr>
		    <td><b>Email</b></td> <td><?php echo $row["email"]; ?></td>
		</tr>
		<tr>
		    <td><b>Subscribed</b></td> <td><?php echo $row["subscribed"]; ?></td>
		</tr>
		<tr>
		    <td><input type="submit" name="Action" value="Delete" onClick="return Confirm_Delete();"></td>
		    <td><input type="button" value="Return to Client List"
			       OnClick="window.location.assign('http://localhost:8080/client/client.php');"></td>
		</tr>
	    </table>
        </form>
	<?php elseif ($_GET["Action"] == "Delete"):
	// echo "In the delete part.";
	$query = "delete from clients where id=$id";
	// echo "This is the query to be executed: " . $query;
	$stmt = $dbh->prepare($query);
	if ($stmt->execute()) : 
		echo "<h3> The client's record has been successfully deleted.</h3>";
	else:
	// echo "<h3> Error in deleting the client's record.</h3>";
	$err = $stmt->errorInfo();
	echo "Error deleting record from database – contact System Administrator. <br />Error is: <b>".$err[2]."</b>";
	endif;
	if (isset($stmt)) $stmt->closeCursor();
	?>

	<input type="button" value="Go back to client list"
	       OnClick="window.location.assign('http://localhost:8080/client/client.php');">

    <?php
	endif;

	} else { // starting from here, it is for user login check
        echo "<h1>Your account does not exist!</h1>";
        session_destroy();
    }
}
?>
</body>
</html>
