<head>
  
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="home.css">

</head>



<header class="user_header">
  <div class="header_1">
    <div class="user_flex">
      <div class="logo_cont">
        <img src="book_logo.png" alt="">
        <a href="home.php" class="book_logo">Bookshop</a>
      </div>

      <nav class="navbar">
        <a href="home.php">Home</a>
        <a href="about.php">About</a>
        <a href="shop.php">Shop</a>
        <a href="contact.php">Contact</a>
        <a href="orders.php">Orders</a>
      </nav>

      <div class="last_part">
        <div class="loginorreg">
          <p><a href="login.php">New Login</a> | <a href="register.php">Register</a></p>
        </div>

        <div class="icons">
          <a class="fa-solid fa-magnifying-glass" href="search_page.php"></a>
          <div class="fas fa-user" id="user_btn"></div>

          <?php
          $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
          $cart_row_number = 0;

          if ($user_id) {
              $select_cart_number = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id='$user_id'") or die('Query failed');
              $cart_row_number = mysqli_num_rows($select_cart_number);
          }
          ?>

          <a href="cart.php">
            <i class="fas fa-shopping-cart"></i>
            <span class="quantity">(<?php echo $cart_row_number; ?>)</span>
          </a>

          <div class="fas fa-bars" id="user_menu_btn"></div>
        </div>
      </div>

      <div class="header_acc_box">
        <p>Username : <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Guest'); ?></span></p>
        <p>Email : <span><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'Not Available'); ?></span></p>
        <a href="logout.php" class="delete-btn">Logout</a>
      </div>
    </div>
  </div>
</header>
