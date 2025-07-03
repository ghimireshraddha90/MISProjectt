<?php
include 'config.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (isset($_POST['add_to_cart'])) {
  if (!$user_id) {
    header('Location: login.php');
    exit;
  }

  $pro_id = $_POST['product_id']; // Added product ID
  $pro_name = $_POST['product_name'];
  $pro_price = $_POST['product_price'];
  $pro_quantity = $_POST['product_quantity'];
  $pro_image = $_POST['product_image'];
  $available_quantity = $_POST['available_quantity']; // Added available quantity

  // Check if requested quantity exceeds available stock
  if ($pro_quantity > $available_quantity) {
    $message[] = 'Stock unavailable! Only ' . $available_quantity . ' items left.';
  } else {
    $check = mysqli_query($conn, "SELECT * FROM `cart` WHERE name='$pro_name' AND user_id='$user_id'") or die('query failed');

    if (mysqli_num_rows($check) > 0) {
      $message[] = 'Already added to cart!';
    } else {
      mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES ('$user_id','$pro_name','$pro_price','$pro_quantity','$pro_image')") or die('query2 failed');
      $message[] = 'Product added to cart!';
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Page</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">

</head>

<body>

  <?php
  include 'user_header.php';

  ?>


  <section class="home_cont">
    <div class="main_descrip">
      <h1>Bookshop</h1>
      <p>Explore, Discover, and Buy Your Favorite Books</p>
      <button>Discover More</button>
    </div>
  </section>

  <section class="products_cont">
    <div class="pro_box_cont">
      <?php
      $select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT 6") or die('query failed');

      if (mysqli_num_rows($select_products) > 0) {
        while ($fetch_products = mysqli_fetch_assoc($select_products)) {

      ?>
          <form action="" method="post" class="pro_box">
            <img src="./uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
            <h3><?php echo $fetch_products['name']; ?></h3>
            <p>Rs. <?php echo $fetch_products['price']; ?>/-</p>

            <!-- Display available stock -->
            <div class="stock-info <?php echo ($fetch_products['quantity'] <= 5) ? 'low-stock' : ''; ?>">
              Available: <?php echo $fetch_products['quantity']; ?> in stock
            </div>

            <input type="hidden" name="product_id" value="<?php echo $fetch_products['id']; ?>">
            <input type="hidden" name="product_name" value="<?php echo $fetch_products['name']; ?>">
            <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
            <input type="hidden" name="product_image" value="<?php echo $fetch_products['image']; ?>">
            <input type="hidden" name="available_quantity" value="<?php echo $fetch_products['quantity']; ?>">

            <!-- Only show quantity selector and add to cart button if stock is available -->
            <?php if ($fetch_products['quantity'] > 0): ?>
              <input type="number" name="product_quantity" min="1" max="<?php echo $fetch_products['quantity']; ?>" value="1">
              <input type="submit" value="Add to Cart" name="add_to_cart" class="product_btn">
            <?php else: ?>
              <div class="out-of-stock">Out of Stock</div>
            <?php endif; ?>
          </form>

      <?php
        }
      } else {
        echo '<p class="empty">No Products Added Yet !</p>';
      }
      ?>
    </div>
  </section>

  <section class="about_cont">
    <img src="about.jpg" alt="">
    <div class="about_descript">
      <h2>Discover Our Story</h2>
      <p>At Bookshop, we are passionate about connecting readers with captivating stories, inspiring ideas, and a world of knowledge. Our bookstore is more than just a place to buy books; it's a haven for book enthusiasts, where the love for literature thrives.
      </p>
      <button class="product_btn" onclick="window.location.href='about.php';">Read More</button>
    </div>
  </section>

  <section class="questions_cont">
    <div class="questions">
      <h2>Have Any Queries?</h2>
      <p>At Bookshop, we value your satisfaction and strive to provide exceptional customer service. If you have any questions, concerns, or inquiries, our dedicated team is here to assist you every step of the way.</p>
      <button class="product_btn" onclick="window.location.href='contact.php';">Contact Us</button>
    </div>

  </section>
  <?php
  include 'footer.php';
  ?>
  <script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>

  <script src="script.js"></script>

  <script>
    // Optional JavaScript to validate quantity before form submission
    document.addEventListener('DOMContentLoaded', function() {
      const forms = document.querySelectorAll('.pro_box');

      forms.forEach(form => {
        form.addEventListener('submit', function(e) {
          const quantityInput = this.querySelector('input[name="product_quantity"]');
          const availableQuantity = parseInt(this.querySelector('input[name="available_quantity"]').value);

          if (parseInt(quantityInput.value) > availableQuantity) {
            e.preventDefault();
            alert('Sorry, only ' + availableQuantity + ' items are available in stock.');
          }
        });
      });
    });
  </script>

</body>

</html>