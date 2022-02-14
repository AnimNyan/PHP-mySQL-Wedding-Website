<?php session_start(); 
// record the calling page, the default one
$_SESSION['initial_page'] = $_SERVER['PHP_SELF'];
?>
<!doctype html>
<html>
  <head>
    <?php include('navbar.php') ?>
    <title>Home page</title>

    <style>
    body {
      background-image: url('heart-529607_1280.jpg');
      background-repeat: no-repeat;
      background-attachment: fixed; 
      background-size: cover; <!-- Automatically stretched if browser is big -->
    }
    .hero-text {
      text-align: center;
      position: absolute;
      top: 60%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: midnightblue;
    }
    </style>
  </head>

  <body>
  <div class="hero-text">
    <h1 style="font-size:50px">Welcome to Master of Wedding Pty Ltd</h1>
    <h3>Please use the menu near the top to go to different pages.</h3>
  </div>

  </body>
</html>
