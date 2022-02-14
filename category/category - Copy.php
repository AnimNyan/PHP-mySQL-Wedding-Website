<?php session_start();
include('..\connection.php'); 
// initialise the max number of categories that can be added
$maxAdd = 3;
?>
<!doctype html>  <!-- needs to have this, otherwise the bootstrap menu is distored a bit -->
<html>
<head>
<?php include('..\navbar.php'); ?>
<script>
function Confirm_Delete(id) {
    // alert(id); // check
    var checkBox = document.getElementById(id);
    // alert(checkBox.checked);
    // only warn once for each submission for delete and/or update
    if (!document.cookie.split('; ').find(row => row.startsWith('deleteWarned'))) {
        if (checkBox.checked == true) { // only prompt & give warning if the checkbox is checked for delete
            var x = confirm("Are you sure you want to delete this category's record?\n\nWarning: related entries in child tables will also be deleted in cascade manner.");
            // the above return true if "OK" is chosen; return false if "Cancel" is chosen
            // set the cookie to a long time
            document.cookie = "deleteWarned=true; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            return x; }        
        else {return true;} // to allow the chosen action to proceed
    }
} // function Confirm_Delete(id)

function resetOnce() { 
  document.cookie = "deleteWarned=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
}
</script>
<title>Category page</title>
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

        //Now we'll process the POST request
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['adl'])) {
            // echo '<pre>' , var_dump($_POST) , '</pre>'; // check
            foreach ($_POST['adl'] as $adl) {
                if (isset($_POST['names'][$adl]) &&  !empty($_POST['names'][$adl])) {
                    $query = "insert into categories (name) values (:name)";
                    // echo "update query is $query<br>";
                    $stmt = $dbh->prepare($query);
                    if (!$stmt->execute([
                        'name' => $_POST['names'][$adl]
                    ])) {
                        $err = $stmt->errorInfo();
                        echo "Error occurred while adding the name of category - contact System Administrator. <br>Error is: <b>$err[2]</b>";
                        break;
                    }
                } else if (isset($_POST['names'][$adl]) && empty($_POST['names'][$adl])) {
                    echo "<h4 style=\"color:red;\">The name of a category to be added cannot be set to empty string.</h4>";
                    break;
                }
            }
        }

        // process update first. This will avoid generating error if this is done after deletion
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['updt'])) {
            // echo '<pre>' , var_dump($_POST) , '</pre>'; // check
            foreach ($_POST['updt'] as $updt) {
                if (isset($_POST['names'][$updt]) &&  !empty($_POST['names'][$updt])) {
                    $query = "UPDATE `categories` SET `name`=:name WHERE `id` = :updt";
                    // echo "update query is $query<br>";
                    $stmt = $dbh->prepare($query);
                    if (!$stmt->execute([
                        'name' => $_POST['names'][$updt],
                        'updt' => $updt
                    ])) {
                        $err = $stmt->errorInfo();
                        echo "Error occurred while updating the name of category id# $updt - contact System Administrator. <br>Error is: <b>$err[2]</b>";
                        break;
                    }
                } else if (isset($_POST['names'][$updt]) && empty($_POST['names'][$updt])) {
                    echo "<h4 style=\"color:red;\">The name of id# $updt cannot be set to empty string.</h4>";
                    break;
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['delt'])) {
            // echo '<pre>' , var_dump($_POST) , '</pre>'; // check
            //Noticed that we're adding questions marks (parameters) to the query
            //To match number of selected items in POST request
            $query_placeholders = trim(str_repeat("?,", count($_POST['delt'])), ",");
            $query = "DELETE FROM `categories` WHERE `id` in (" . $query_placeholders . ")";
            $stmt = $dbh->prepare($query);
            // echo "delete query is $query<br>";
            if ($stmt->execute($_POST['delt'])) echo "<h4>Selected categories have been deleted</h4>";
            else { 
                $err = $stmt->errorInfo();
                echo "Error occurred while deleting categories – contact System Administrator. <br>Error is: <b>".$err[2]."</b>";
            }
        } else {
            echo "<h4 style=\"color:blue;\">Please select at least one category to delete or update OR<br>add some new categories : </h4><br>";
        }
        // list the categories order by name
        $category_stmt = $dbh->prepare("SELECT * FROM `categories` order by name");
        if ($category_stmt->execute() && $category_stmt->rowCount() > 0) { ?>
            <form method="post">
                <input type="submit" value="Delete or update selected categories (Delete has priority over update)" onClick="resetOnce();">
                <table border="1" cellpadding="3">
                    <tr>
                        <th>Delete</th>
                        <th>Update</th>
                        <th>category ID</th>
                        <th>name</th>
                    </tr>
                    <?php while ($row = $category_stmt->fetchObject()) { ?>
                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" name="delt[]" value="<?= $row->id ?>" id="<?= $row->id ?>" onClick="return Confirm_Delete(<?= $row->id ?>);">
                            </td>
                            <td style="text-align: center">
                                <input type="checkbox" name="updt[]" value="<?= $row->id ?>"/>
                            </td>
                            <td><?= $row->id ?></td>
                            <td><input type="text" name="names[<?= $row->id ?>]" value="<?= $row->name ?>"/></td>
                        </tr>
                    <?php } ?>
                </table>
            </form>
        <?php } ?>

        <p> <br> </p> 
        <form method="post">
        <input type="submit" value="Add new categories"/>
        <table border="1" cellpadding="3">
        <tr>
        <th>Add</th>
        <th>category name</th>
        </tr>
        <?php 
        for ($i=0; $i<$maxAdd; $i++) { ?>
        <tr>
        <td style="text-align: center">
        <input type="checkbox" name="adl[]" value="<?= $i ?>">
        </td>
        <td><input type="text" name="names[]" value=""/></td>
        </tr>
        <?php } ?>
        </table>
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
