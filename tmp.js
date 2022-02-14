<form method="post" enctype="multipart/form-data">
<label for="fileInput">Select files to upload:</label>
<input type="file" id="fileInput" name="images[]" multiple 
accept=".jpeg, .jpg, .png, .gif"
onChange="checkFileSize()">
<input type="submit" value="Upload">
</form>

echo '<pre>' , var_dump($_FILES) , '</pre>'; // check

array(1) {
    ["images"]=>
    array(5) {
      ["name"]=>
      array(2) {
        [0]=>
        string(11) "Capture.GIF"
        [1]=>
        string(21) "heart-529607_640.jpeg"
      }
      ["type"]=>
      array(2) {
        [0]=>
        string(9) "image/gif"
        [1]=>
        string(10) "image/jpeg"
      }
      ["tmp_name"]=>
      array(2) {
        [0]=>
        string(24) "C:\xampp\tmp\php7106.tmp"
        [1]=>
        string(24) "C:\xampp\tmp\php7107.tmp"
      }
      ["error"]=>
      array(2) {
        [0]=>
        int(0)
        [1]=>
        int(0)
      }
      ["size"]=>
      array(2) {
        [0]=>
        int(639743)
        [1]=>
        int(47631)
      }
    }
  }
  
  fileDestination C:/xampp/htdocs/product_images/5f8d0f317b730Capture.GIF
  tmp name C:\xampp\tmp\php7106.tmp
  This is the query to be executed: INSERT INTO product_images (product_id, filename) VALUES (?, ?)
  File is uploaded and stored successfully!
  fileDestination C:/xampp/htdocs/product_images/5f8d0f317c1b3heart-529607_640.jpeg
  tmp name C:\xampp\tmp\php7107.tmp
  This is the query to be executed: INSERT INTO product_images (product_id, filename) VALUES (?, ?)
  File is uploaded and stored successfully!