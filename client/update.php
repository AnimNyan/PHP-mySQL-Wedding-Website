<?php session_start();
include('..\connection.php'); ?>
<!doctype html>  <!-- needs to have this, otherwise the bootstrap menu is distored a bit -->
<html>
<head>
<?php include('..\navbar.php'); ?>
<link rel="stylesheet" href="..\styles.css">
<title>Update client</title>
</head>
<body>
<h1 style="text-align:center">Update a client's info</h1>
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
        $stmt = $dbh->prepare($query);
        $stmt->execute();
		$row = $stmt -> fetch();

	// populate variables with values if approp
	if (empty($_POST["Action"])) { // first time: use db data
	    $first_name = $row["first_name"];
	    $surname = $row["surname"];
	    $address = $row["address"];
	    $phone = $row["phone"];
		$mobile = $row["mobile"];
		$email = $row["email"];
		$subscribed = $row["subscribed"];
	} else { // later, use post data i.e. data provided in the update form
		if (!empty($_POST["first_name"])) {$first_name = $_POST["first_name"];} else { $first_name = "";}
		if (!empty($_POST["surname"])) {$surname = $_POST["surname"];} else { $surname = "";}
		if (!empty($_POST["address"])) {$address = $_POST["address"];} else { $address = "";}
		if (!empty($_POST["phone"])) {$phone = $_POST["phone"];} else { $phone = "";}
		if (!empty($_POST["mobile"])) {$mobile = $_POST["mobile"];} else { $mobile = "";}
		if (!empty($_POST["email"])) {$email = $_POST["email"];} else { $email = "";}
		if (!empty($_POST["subscribed"])) {$subscribed = $_POST["subscribed"];} else { $subscribed = "";}
	}

	// empty($_POST["id"]) should NOT be in the test as it is not input or updated
	if (empty($_POST["first_name"]) || empty($_POST["surname"]) ) :

	       // "Update" button has been pressed and not all obligatory boxes are filled
	       if (!empty($_POST["Action"]) && ($_POST["Action"] == "Update")) {
		   // echo "<p> In action loop. </p>"; ?>
	    
	    <script>
	     alert("first_name and/or surname are not filled. Please fill them up.");  // display string message
	    </script>
	<?php 
	}
	?>
        <form method="post">
		<table align="center" cellpadding="3">
		<tr>
		<td><b>Client ID</b></td> <td><input type="text" name="id" value="<?php echo $id; ?>" readonly></td> 
		</tr>
		<tr>
		    <td><b>First Name</b></td> <td><input type="text" name="first_name" value="<?php echo $first_name; ?>" required></td>
		</tr>
		<tr>
		    <td><b>Surname</b></td> <td><input type="text" name="surname" value="<?php echo $surname; ?>" required></td>
		</tr>
		<tr>
		    <td><b>Address</b></td> <td><input type="text" name="address" value="<?php echo $address; ?>"></td>
		</tr>
		<tr>
		    <td><b>phone</b></td> <td><input type="tel" name="phone" value="<?php echo $phone; ?>"
				placeholder="0370654331" pattern="0[2378][1-9][0-9]{7}"></td>
		</tr>
		<tr>
		    <td><b>mobile</b></td> <td><input type="tel" name="mobile" value="<?php echo $mobile; ?>"
				placeholder="0405711288" pattern="04[0-9]{8}"></td>
		</tr>
		<tr>
		    <td><b>email</b></td> <td><input type="email" name="email" value="<?php echo $email; ?>"></td>
		</tr>
		<tr>
		    <td><b>subscribed</b></td> <td><input type="number" name="subscribed" value="<?php echo $subscribed; ?>"
				min="0" max="1"></td>
		</tr>
		<tr> <td> </td>
			<td> <h5 style="color:red;">First name and Surname must be filled,<br> other boxes are optional.</h5> </td> </tr>
		<tr>
		    <td><input type="submit" name="Action" value="Update"></td>
		    <td><input type="button" value="Return to Client List"
			       OnClick="window.location.assign('http://localhost:8080/client/client.php');"></td>
		</tr>
	    </table>
        </form>
	<?php else:
	$query = "UPDATE clients SET 
          first_name = '$_POST[first_name]', 
          surname = '$_POST[surname]', 
          address='$_POST[address]',
          phone='$_POST[phone]',  
          mobile='$_POST[mobile]',
          email='$_POST[email]',
          subscribed='$_POST[subscribed]'
         WHERE id=$id";
	// echo "This is the query to be executed: " . $query;
	$stmt = $dbh->prepare($query);
	if ($stmt->execute()) :
	?>

	    <h3 style="text-align:center;color:blue;">The client's data has been successfully updated.</h3>
	    
	<?php

	else:
	$err = $stmt->errorInfo();
	echo "Error updating record in database – contact System Administrator. <br />Error is: <b>".$err[2]."</b>";
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
