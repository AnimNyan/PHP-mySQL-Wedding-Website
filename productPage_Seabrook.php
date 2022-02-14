<html>
<head>
    <title>FIT2104 lab 7 table</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<h1>Client</h1>
<?php
include("connection.php");

/*
if (!isset($_SESSION['user_id']))
{
    header("Location: login.php");
}
*/

//If the user has POSTed to delete
//delete the record
if ($_SERVER['REQUEST_METHOD'] === 'POST'&& !empty ($_POST["delete"]))
{
    $query_placeholders = trim(str_repeat("?,", count($_POST['delete'])), ",");
    $query = "DELETE FROM `products` WHERE `id` IN (" . $query_placeholders . ")";
    echo $query;
    $stmt = $dbh->prepare($query);

    if ($stmt->execute($_POST['delete'])) echo "<h3>Selected titles have been deleted</h3>";
    else {
        echo "<h3>Error occurred while deleting titles</h3>";
    }

    /*
    foreach($_POST["check"] as $id)
    {
        $stmt = $dbh->prepare("DELETE FROM products where id = '$id'");
        $stmt -> execute();
    }
    */

}

if ($_SERVER['REQUEST_METHOD'] === 'POST'&& !empty ($_POST["update"]))
{
    echo '<pre>' , var_dump($_POST) , '</pre>';
    //echo 'sale_price[0]'. $_POST['sale_price'][0];

    foreach($_POST["update"] as $id)
    {
        $query = "UPDATE products SET sale_price= :sale_price WHERE id = :id";
        $stmt = $dbh->prepare($query);
        //use $id -1 as arrays start at 0 not 1
        $idArrayNum = $id -1;

        if (!$stmt->execute([
            'sale_price' => $_POST['sale_price'][$idArrayNum],
            'id' => $id]))
        {
            echo "<h3>Error occurred while updating price of product id# $id!</h3>";
            break;
        }
    }
}
//if the user has not POSTed to update
//allow them to submit the form to update

$stmt = $dbh->prepare("SELECT * FROM products");
$stmt->execute();


?>

<!--Table to display product records-->
<form method="post">
    <table border = "1">
        <tr>

            <th>Product ID</th>
            <th>Product Name</th>
            <th>Purchase Price ($)</th>
            <th>Sale Price Update($) </th>
            <th>Update?</th>
            <th>Delete?</th>
        </tr>

        <?php

        while($row = $stmt -> fetchObject()):
            //while ($row = mysqli_fetch_row($result)):
            // <?= means php short for <?php echo -->
            ?>
            <tr>
                <td><?php echo $row -> id; ?></td>
                <td><?php echo $row -> name; ?></td>
                <td><?php echo $row -> purchase_price; ?></td>
                <td><input type="text" name="sale_price[]" value="<?php echo $row->sale_price; ?>"/></td>
                <td>
                    <input type="checkbox" name="update[]" value="<?php echo $row->id; ?>"/>
                </td>
                <td>
                    <input type="checkbox" name="delete[]" value="<?php echo $row->id;?>">
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <input type="Submit" Value="Delete">
</form>

<script>
    //  $("tr:odd").css("background-color", "#ADD8E6");
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
<script>
    $("#delete-button").click(function(){
        if (confirm("Are you sure you want to delete this client record?")){
            $('form').submit();
        } else {
            window.location = 'client.php';
            return false;
        }
    });</script>
</body>
</html>
