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
    if (checkBox.checked == true) { // only prompt & give warning if the checkbox is checked for delete
        var x = confirm("Are you sure you want to delete this product's record?\n\nWarning: related entries in child tables will also be deleted in cascade manner.");
        // the above return true if "OK" is chosen; return false if "Cancel" is chosen
        return x; }
        else {return true;} // to allow the chosen action to proceed
    }
</script>
<!-- for <input type="number" style="text-align: right"> -->
<link rel="stylesheet" href="number.css">
<title>Product page</title>
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
        // process update first. This will avoid generating error if this is done after deletion
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['updt'])) {
            // echo '<pre>' , var_dump($_POST) , '</pre>'; // check
            foreach ($_POST['updt'] as $updt) {
                if (isset($_POST['names'][$updt]) && !empty($_POST['names'][$updt]) && 
                    isset($_POST['prices'][$updt]) && !empty($_POST['prices'][$updt]) &&
                    isset($_POST['sprices'][$updt]) && !empty($_POST['sprices'][$updt]) ) {
                    $query = "UPDATE `products` SET `name`=:name, `purchase_price`=:price, 
                        `sale_price`=:sprice WHERE `id` = :updt";
                    // echo "update query is $query<br>";
                    $stmt = $dbh->prepare($query);
                    if (!$stmt->execute([
                        'name' => $_POST['names'][$updt],
                        'price' => $_POST['prices'][$updt],
                        'sprice' => $_POST['sprices'][$updt],
                        'updt' => $updt
                    ])) {
                        $err = $stmt->errorInfo();
                        echo "Error occurred while updating the name of product id# $updt - contact System Administrator. <br>Error is: <b>$err[2]</b>";
                        break;
                    }
                } else if (isset($_POST['names'][$updt]) && empty($_POST['names'][$updt]) &&
                    isset($_POST['prices'][$updt]) && empty($_POST['prices'][$updt]) &&
                    isset($_POST['sprices'][$updt]) && empty($_POST['sprices'][$updt]) ) {
                    echo "<h4 style=\"color:red;\">The name, price and sale price of product id# $updt cannot be set to empty string.</h4>";
                    break;
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['delt'])) {
            // echo '<pre>' , var_dump($_POST) , '</pre>'; // check
            //Noticed that we're adding questions marks (parameters) to the query
            //To match number of selected items in POST request
            $query_placeholders = trim(str_repeat("?,", count($_POST['delt'])), ",");
            $query = "DELETE FROM `products` WHERE `id` in (" . $query_placeholders . ")";
            $stmt = $dbh->prepare($query);
            // echo "delete query is $query<br>";
            if ($stmt->execute($_POST['delt'])) {
                echo "<h4>Selected products have been deleted</h4>";
                echo "<br> <a href=\"insert.php\"><button><h3 style=\"color:red;\"> Add a new product</h3></button></a>";
                echo "<h4 style=\"color:blue;\"><br>Please select at least one product to delete or update: </h4><br>";        
            } else { 
                $err = $stmt->errorInfo();
                echo "Error occurred while deleting products – contact System Administrator. <br>Error is: <b>$err[2]</b>";
            }             
        } else {
                echo "<br> <a href=\"insert.php\"><button><h3 style=\"color:red;\"> Add a new product</h3></button></a>";
                echo "<h4 style=\"color:blue;\"><br>Please select at least one product to delete or update: </h4><br>";
        } 
   
        // list the products order by name and filter if nec
        if (!empty($_POST["Action"]) && ($_POST["Action"] == "Filter") && ($_POST['category_id'] != 0)) {
            // echo '<pre>' , var_dump($_POST) , '</pre>'; // check
            $query = "select * from products where id in 
                (select product_id from categories_products where category_id = ?)";
            // echo "select query is \"$query\"<br>";
            $product_stmt = $dbh->prepare($query);
            if ($product_stmt->execute([$_POST['category_id']])) {
                echo "<h4>Products are filtered.</h4>";
            } else { 
                $err = $product_stmt->errorInfo();
                echo "Error occurred while filtering products – contact System Administrator. <br>Error is: <b>$err[2]</b>";
            }             
        } else { // no filter
            $product_stmt = $dbh->prepare("SELECT * FROM products order by name");
            if ($product_stmt->execute()) {
                echo "<h4>Products are NOT filtered.</h4>";
            } else { 
                $err = $product_stmt->errorInfo();
                echo "Error occurred while listing all products – contact System Administrator. <br>Error is: <b>$err[2]</b>";
            }   
        }

        // make the dropdown list using categories for filtering
        $query = "SELECT * FROM categories ORDER BY name";
        $category_stmt = $dbh->prepare($query);
        if ($category_stmt->execute() && $category_stmt->rowCount() > 0) { ?>
        <form method="post">
            <label for="category_id">Select products from a category</label>
            <select name="category_id" id="category_id">
                <option value="0">No filter</option>
                <?php while ($row = $category_stmt->fetchObject()){ ?>
                    <option value="<?= $row->id ?>"><?= $row->name ?></option>
                <?php } ?>
            </select>
            <input type="submit" name="Action" value="Filter">
        </form>
        <?php } 

        if ($product_stmt->rowCount() <= 0) {
            echo "<br> <h3 style=\"color:red;\">No product is found.</h3>";
        } else {  ?>
            <form method="post">
                <input type="submit" name="DelUp" value="Delete or update selected products (Delete has priority over update)"/>
                <table border="1" cellpadding="3">
                    <tr>
                        <th>Delete</th>
                        <th>Update</th>
                        <th>product ID</th>
                        <th>name</th>
                        <th>price</th>
                        <th>sale price</th>
                        <th>images</th>
                    </tr>
                    <?php while ($row = $product_stmt->fetchObject()) { ?>
                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" name="delt[]" value="<?php echo $row->id; ?>" id="<?= $row->id ?>" onClick="return Confirm_Delete(<?= $row->id ?>);">
                            </td>
                            <td style="text-align: center">
                                <input type="checkbox" name="updt[]" value="<?php echo $row->id; ?>"/>
                            </td>
                            <td style="text-align: right"><?= $row->id ?></td>
                            <td><input type="text" name="names[<?= $row->id ?>]" value="<?= $row->name ?>" size = "25" maxlength="64" required></td>
                            <td>
                                <input type="number" name="prices[<?= $row->id ?>]" value="<?= $row->purchase_price ?>"
                                min="0" max="9999999.99" step="0.01" size="10" required></td>
                            <td><input type="number" name="sprices[<?= $row->id ?>]" value="<?= $row->sale_price ?>" 
                                min="0" max="9999999.99" step="0.01" size="10" required></td>
                            <td><a href="image.php?id=<?= $row->id ?>">images</a></td>
                        </tr>
                    <?php } ?>
                </table>
            </form>
        <?php }
    } else {
        echo "<h1>Your account does not exist!</h1>";
        session_destroy();
    }
} // if (!isset($_SESSION['user_id'])) {
?>
</body>
</html>
