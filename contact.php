<?php
include 'config.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

if (isset($_POST['send'])) {

  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $number = $_POST['number'];
  $msg = mysqli_real_escape_string($conn, $_POST['message']);

  $check_query = "SELECT * FROM `message` WHERE name='$name' AND email='$email' AND number='$number' AND message='$msg'";
  $select_message = mysqli_query($conn, $check_query) or die('query failed');

  if (mysqli_num_rows($select_message) > 0) {
    $message[] = 'Message already sent!';
  } else {
    $insert_query = "INSERT INTO `message`(user_id, name, email, number, message) VALUES('$user_id', '$name', '$email', '$number', '$msg')";
    mysqli_query($conn, $insert_query) or die('query failed');
    $message[] = 'Message sent successfully!';
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Page</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">
</head>
<body>

<?php include 'user_header.php'; ?>

<section class="contact_us">
  <form action="" method="post">
    <h2>Contact Us!</h2>
    <input type="text" name="name" required placeholder="Enter your name">
    <input type="email" name="email" required placeholder="Enter your email">
    <input type="phone" name="number" required placeholder="Enter your number">
    <textarea name="message" placeholder="Enter your message" cols="30" rows="10" required></textarea>
    <input type="submit" value="Send Message" name="send" class="product_btn">
  </form>
</section>

<?php include 'footer.php'; ?>

<script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>
<script src="script.js"></script>

</body>
</html>
