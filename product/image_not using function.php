<?php session_start();
include('..\connection.php'); ?>
<!doctype html>  <!-- needs to have this, otherwise the bootstrap menu is distored a bit -->
<html>
<head>
<?php include('..\navbar.php'); ?>
<script>
function Confirm_Delete() {
    var x = confirm("Are you sure you want to delete this product's record?\n\nWarning: related entries in child tables will also be deleted in cascade manner.");
    // the above return true if "OK" is chosen; return false if "Cancel" is chosen
    return x;
}

function checkFileSize() {
    var fi = document.getElementById("fileInput");
   
    // console.log("In checkFileSize"); // check
    
    let numFiles = fi.files.length;

    // only execute check if a file has been selected to upload
    if (numFiles > 0){
        // console.log(numFiles); // check
        let totalFileSize= 0;
        
        let isOverSizeLimit = false;
        
        for (let i = 0; i < numFiles; i++) {
            // console.log(fi.files[i].name); // check
            // console.log(fi.files[i].size); // check

            let fileSize = fi.files[i].size;
            
            
            //if fileSize is greater than 2MB
            if (fileSize / 1000000 > 2) {
                let fileName = fi.files[i].name;
                alert("Upload error: Single File named: " + fileName + " is greater than 2MB.");
                isOverSizeLimit = true;
            }
            totalFileSize += fileSize;
        }
        //if the total filesize is greater than 7MB
        if (totalFileSize/ 1000000 > 7)
        {
            alert("Upload error: Total file size is greater than 7MB.");
            isOverSizeLimit = true;
        }
        
        //delete the files from the file input tag
        if (isOverSizeLimit) {
            // object is passed by reference
            fi.value="";
        }
        
    }
}

</script>

<style>
img {
  max-width: 100%;
  height: auto;
}
</style>
<title>Image page</title>
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
        // echo '<pre>' , var_dump($_GET) , '</pre>'; // check
        $product_id = $_GET["id"];


        echo '<pre>', var_dump($_POST) , '</pre>'; // check
        echo '<pre>' , var_dump($_FILES) , '</pre>'; // check
    
        // upload files
        if (isset($_FILES['images'])) uploadFiles($product_id, $dbh);

        // update product
        if (isset($_POST["action"]) && ($_POST["action"] == "Update")) {
            $query = "UPDATE products SET 
            name = '$_POST[name]', 
            purchase_price = '$_POST[purchase_price]', 
            sale_price='$_POST[sale_price]'
            WHERE id=$product_id";
            // echo "This is the query to be executed: \"$query\"<br>";
            $stmt = $dbh->prepare($query);
            if (!$stmt->execute()) {
                $err = $stmt->errorInfo();
                echo "Error updating product in database – contact System Administrator. <br>Error is: <b> $err[2]</b>";
            }
        }

        // delete product
        if (isset($_POST["action"]) && ($_POST["action"] == "Delete")) {
            $query = "delete from products WHERE id=$product_id";
            // echo "This is the query to be executed: \"$query\"<br>";
            $stmt = $dbh->prepare($query);
            if ($stmt->execute()) {
                echo "<h3> The product's record has been successfully deleted.</h3>";
            } else {
                $err = $stmt->errorInfo();
                echo "Error deleting product in database – contact System Administrator. <br>Error is: <b> $err[2]</b>";
            }

            // go back to product page
            echo "<input type=\"button\" value=\"Go back to products page\"
            OnClick=\"window.location.assign('http://localhost:8080/product/product.php');\">";
            goto endOfFile;
        }

        // update the product's categories
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['updt'])) {
            // echo '<pre>' , var_dump($_POST) , '</pre>'; // check
            // to simplify the update situation, delete all the categories associated with product first
            $query = "delete from categories_products where product_id=$product_id";
            // echo "This is the query to be executed: \"$query\"<br>"; // check
            $stmt = $dbh->prepare($query);
            if (!$stmt->execute()) {
                $err = $stmt->errorInfo();
                echo "Error in clearing the categories associated with product_id# $product_id in database – contact System Administrator. <br>Error is: <b> $err[2]</b>";
            } 

            $query_placeholders = trim(str_repeat("(?,$product_id),", count($_POST['updt'])), ",");
            $query = "insert into categories_products (category_id, product_id) values " . $query_placeholders;
            $stmt = $dbh->prepare($query);
            // echo "The query is \"$query\"<br>"; // check
            if (!$stmt->execute($_POST['updt'])) {
                $err = $stmt->errorInfo();
                echo "Error occurred while linking the product_id# $product_id with respective categories - contact System Administrator. <br>Error is: <b>$err[2]</b>";
            }
        }

		$query = "SELECT * FROM products where id=$product_id";
		// echo "This is the query to be executed: \"$query\"";
        $product_stmt = $dbh->prepare($query);
        if (!$product_stmt->execute()) {
            $err = $product_stmt->errorInfo();
            echo "Error occurred while listing the product – contact System Administrator. <br>Error is: <b>$err[2]</b>";
        }

        // fetch the single row
        $row = $product_stmt->fetchObject(); ?>

        <p> </p>
        <form method="post">
            <table border="1" cellpadding="3">
                <tr>
                    <th>product ID</th>
                    <th>name</th>
                    <th>price</th>
                    <th>sale price</th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <td style="text-align: center"><?= $row->id ?></td>
                    <td><input type="text" name="name" value="<?= $row->name ?>" size = "25" maxlength="64" required></td>
                    <td><input type="number" name="purchase_price" value="<?= $row->purchase_price ?>"
                            min="0" max="9999999.99" step="0.01" size="10" required></td>
                    <td><input type="number" name="sale_price" value="<?= $row->sale_price ?>" 
                            min="0" max="9999999.99" step="0.01" size="10" required></td>
                    <td> <input type="submit" name="action" value="Delete" onClick="return Confirm_Delete();"> </td>
                    <td> <input type="submit" name="action" value="Update"> </td>
                </tr>
            </table>
        </form>

        <br>
        <?php
        // find category_id's for the product
  		$query = "select category_id from categories_products where product_id=$product_id";
        // echo "This is the query to be executed: \"$query\""; // check
        $cp_stmt = $dbh->prepare($query);
        if (!$cp_stmt->execute()) {
            $err = $cp_stmt->errorInfo();
            echo "Error occurred while listing the product – contact System Administrator. <br>Error is: <b>$err[2]</b>";
        }
        // make an array for the category_id's
        if ($cp_stmt->rowCount() > 0) {
            while ($row = $cp_stmt->fetchObject()) $cat_ids[] = $row->category_id;
            // echo '<pre>' , var_dump($cat_ids) , '</pre>'; // check
        }

        // list the categories order by name
        $category_stmt = $dbh->prepare("SELECT * FROM `categories` order by name");
        if ($category_stmt->execute() && $category_stmt->rowCount() > 0) { ?>
            <form method="post">
                <input type="submit" value="Choose the categories for the above product">
                <table border="1" cellpadding="3">
                    <tr>
                        <th>Chosen</th>
                        <th>category ID</th>
                        <th>name</th>
                    </tr>
                    <?php while ($row = $category_stmt->fetchObject()) { ?>
                        <tr>
                            <td style="text-align: center">
                                <input type="checkbox" name="updt[]" value="<?= $row->id ?>" 
                                <?= (isset($cat_ids)) ? (in_array($row->id, $cat_ids) ? 'checked' : '') : '' ; ?> >
                            </td>
                            <td style="text-align: center"><?= $row->id ?></td>
                            <td><input type="text" name="names[<?= $row->id ?>]" value="<?= $row->name ?>"/></td>
                        </tr>
                    <?php } ?>
                </table>
            </form>
        <?php }

        // list existing images if they exist, 3 images per row
        $image_stmt = $dbh->prepare("SELECT * FROM product_images where product_id=$product_id");
        if (!$image_stmt->execute()) {
            $err = $image_stmt->errorInfo();
            echo "Error occurred while selecting images of the product – contact System Administrator. <br>Error is: <b>$err[2]</b>";
        } else if ($image_stmt->rowCount() > 0) { ?>

        <hr>
        
        <h4>Existing images for the product</h4>
        <form method="post">
                <input type="submit" name="action" value="Delete the chosen images">
                <table border="1" cellpadding="3">
                    <tr>
                        <th>Delete</th>
                        <th>image</th>
                        <th>Delete</th>
                        <th>image</th>
                        <th>Delete</th>
                        <th>image</th>
                    </tr>
                    <?php 
                    $columnNumber=0; // initialise
                    echo '<tr>';
                    while ($row = $image_stmt->fetchObject()) {
                        echo '<td style="text-align: right"> <input type="checkbox" name="delt[]" value="' . $row->id . '" > </td>';
                        echo '<td><img src="../product_images/' . $row->filename . '" alt=""></td>';
                        $columnNumber++;
                        if ($columnNumber%3==0) echo '</tr><tr>'; 
                    } 
                    echo '</tr>';
                    ?>
                </table>
        </form>
        <?php } ?>
       

        <hr>
        <h4>Upload files</h4>
        <form method="post" enctype="multipart/form-data">
            <label for="fileInput">Select files to upload:</label>
            <input type="file" id="fileInput" name="images[]" multiple 
            accept=".jpeg, .jpg, .png, .gif"
            onChange="checkFileSize()">
            <input type="submit" name="action" value="Upload">
        </form>

        <?php
        endOfFile:
        echo "";
    } else {
        echo "<h1>Your account does not exist!</h1>";
        session_destroy();
    }
} // if (!isset($_SESSION['user_id'])) {

// function to upload files
function uploadFiles($product_id, $dbh) {
    //all files total size must be less than 7MB
    if (array_sum($_FILES['images']['size'])/1000000<=7) {
        
        //as $key => $val to get both the key and value of an array into $key and $val
        //loop across all images that are uploaded using $key
        foreach ($_FILES['images']['error'] as $key => $errorIndex) {
            
            //three layers of arrays in $_FILES as multiple images are uploaded
            //error with 0, means file was uploaded successfully
            if (isset($_FILES['images']) && $_FILES['images']['error'][$key] == 0) {
                //image must be a jpeg/jpg/png/gif
                if (in_array($_FILES['images']['type'][$key], array('image/jpeg', 'image/jpg', 'image/png', 'image/gif'))) {
                    //sanitise filename by removing any directory names, prevent directory injection
                    // uniqid will generate a 13 characters unique identifier based on the current time in microseconds
                    $uniqueName = uniqid() . "_" . basename($_FILES['images']['name'][$key]);
                    
                    //single file is more than 2MB
                    if($_FILES['images']['size'][$key]/1000000<=2) {
                        $productImagePath = "C:/xampp/htdocs/product_images/";
                        
                        //if directory doesn't exist create it
                        if (!file_exists($productImagePath))
                        {
                            mkdir($productImagePath);
                        }
                        
                        //use "uploads/" instead of "C:/xampp/uploads/ if the files are on the remote desktop
                        $fileDestination =  $productImagePath . $uniqueName;
                        
                        // echo '<b>fileDestination</b> ' . $fileDestination . '<br>'; // check
                        // echo '<b>tmp filename</b> ' . $_FILES['images']['tmp_name'][$key]; // check
                        
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $fileDestination)) {
                            $query = "INSERT INTO product_images (product_id, filename) VALUES (?, ?)";
                            $stmt = $dbh->prepare($query);
                            // echo "<br> <b>This is the query to be executed:</b> $query"; // check
                            
                            if ($stmt->execute(array($product_id, $uniqueName))){
                                echo "<h4 style='color:blue'>File $uniqueName is uploaded and stored successfully!</h4>";
                            }
                            else {
                                $err= $stmt->errorInfo();
                                echo "</br> Error adding record of uploaded file $uniqueName to database – contact System Administrator. <br />Error is: <b>$err[2]</b>";
                            }
                            
                        } else {
                            echo "<h1>File cannot be stored to the final destination!</h1>";
                        }
                    }
                    //single file is not less than 2MB
                    else
                    {
                        echo "<h1 style='color:red'>Upload Error: Single file named: " . $uniqueName . " is over 2MB!</h1>";
                    }
                    
                } //if a file which is not a JPG/PNG/GIF is uploaded
                else {
                    echo "<h1 style='color:red'>Upload Error: Only JPG/JPEG/PNG/GIF file can be uploaded!</h1>";
                }
                // if file was not uploaded successfully
            } else {
                $phpFileUploadErrors = array(
                    0 => 'There is no error, the file uploaded with success',
                    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                    3 => 'The uploaded file was only partially uploaded',
                    4 => 'No file was uploaded',
                    6 => 'Missing a temporary folder',
                    7 => 'Failed to write file to disk.',
                    8 => 'A PHP extension stopped the file upload.',
                );
                // $errorIndex = $_FILES['images']['error'][$key];
                //if the error was no file was uploaded
                if ($errorIndex == 4) {
                    echo '<p> No file was uploaded. </p>';
                }else{
                    echo "<h1>Uploaded file cannot be processed!</h1>";
                    $errorMessage = (isset($_FILES['images']['error'][$key])) ? $phpFileUploadErrors[$errorIndex] : "Unknown error";
                    echo "<p>Error message: " . $errorMessage . "</p>";
                }
            }
        }
    }
    //if files are over size limit
    else
    {
        echo "<h1 style='color:red'>Upload Error: Total file size is over the 7MB limit!</h1>";
    }
}

?>

</body>
</html>
