
	<?php
	// get the newly inserted record from db & display to the user
	$client_id = $dbh->lastInsertId();
	$query = "select * from client where client_id=$client_id";
    $stmt = $dbh->prepare($query);
	/* echo $client_id; 
	echo "<p>The query is: " . $query . "</p>"; */
	if ($stmt->execute() || $stmt->rowCount() > 0) {
	     // echo "query is executed successfully";
	     $row = $stmt -> fetchObject();

    } else { // some errors
	    $err = $stmt->errorInfo();
	    echo "Error occurred while ..... – contact System Administrator. <br>Error is: <b>$err[2]</b>";
	?>

	<input type="button" value="Go back to client list" OnClick="window.location.assign('index.php');">

	<?php
    }
    ?>