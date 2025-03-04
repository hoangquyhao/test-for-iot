<?php

include 'config.php';

session_start();

if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];
} else {
	$user_id = '';
}
;

if (isset($_POST['register'])) {

	$name = $_POST['name'];
	$name = filter_var($name, FILTER_SANITIZE_STRING);
	$email = $_POST['email'];
	$email = filter_var($email, FILTER_SANITIZE_STRING);
	$pass = sha1($_POST['pass']);
	$pass = filter_var($pass, FILTER_SANITIZE_STRING);
	$cpass = sha1($_POST['cpass']);
	$cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

	$select_user = $conn->prepare("SELECT * FROM `user` WHERE name = ? AND email = ?");
	$select_user->execute([$name, $email]);

	if ($select_user->rowCount() > 0) {
		$message[] = 'username or email already exists!';
	} else {
		if ($pass != $cpass) {
			$message[] = 'confirm password not matched!';
		} else {
			$insert_user = $conn->prepare("INSERT INTO `user`(name, email, password) VALUES(?,?,?)");
			$insert_user->execute([$name, $email, $cpass]);
			$message[] = 'registered successfully, login now please!';
		}
	}
}

if (isset($_POST['update_qty'])) {
	$cart_id = $_POST['cart_id'];
	$qty = $_POST['qty'];
	$qty = filter_var($qty, FILTER_SANITIZE_STRING);
	$update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
	$update_qty->execute([$qty, $cart_id]);
	$message[] = 'cart quantity updated!';
}

if (isset($_GET['delete_cart_item'])) {
	$delete_cart_id = $_GET['delete_cart_item'];
	$delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
	$delete_cart_item->execute([$delete_cart_id]);
	header('location:index.php');
}

if (isset($_GET['logout'])) {
	session_unset();
	session_destroy();
	header('location:index.php');
}

if (isset($_POST['add_to_cart'])) {

	if ($user_id == '') {
		$message[] = 'Please login first!';
	} else {

		$pid = $_POST['pid'];
		$name = $_POST['name'];
		$price = $_POST['price'];
		$image = $_POST['image'];
		$qty = $_POST['qty'];
		$qty = filter_var($qty, FILTER_SANITIZE_STRING);

		$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND name = ?");
		$select_cart->execute([$user_id, $name]);

		if ($select_cart->rowCount() > 0) {
			$message[] = 'Already added to cart';
		} else {
			$insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
			$insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
			$message[] = 'Added to cart!';
		}
	}
}


if (isset($_POST['order'])) {

	if ($user_id == '') {
		$message[] = 'Please login first!';
	} else {
		$total_price = $_POST['total_price'];
		$total_products = $_POST['total_products'];

		$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
		$select_cart->execute([$user_id]);

		if ($select_cart->rowCount() > 0) {
			$insert_order = $conn->prepare("INSERT INTO `orderseih`(user_id, total_products, total_price, payment_status) VALUES(?,?,?,'pending')");
			$insert_order->execute([$user_id, $total_products, $total_price]);
			$delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
			$delete_cart->execute([$user_id]);
			$message[] = 'Order placed successfully!';
		} else {
			$message[] = 'Your cart is empty!';
		}
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Complete Responsive Pizza Shop Website Design</title>

	<!-- font awesome cdn link  -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

	<!-- custom css file link  -->
	<link rel="stylesheet" href="css/style.css">
	<style>
		.menu .box img {
			width: 220px;
			/* Đặt chiều rộng cố định */
			height: 220px;
			/* Đặt chiều cao cố định */
			border-radius: 25px;
			object-fit: cover;
			/* Cắt và giữ tỷ lệ ảnh */
		}

		.menu .box-container .box {
			border-radius: 25px;
		}
	</style>

</head>

<body>

	<?php
	if (isset($message)) {
		foreach ($message as $message) {
			echo '
         <div class="message">
            <span>' . $message . '</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
		}
	}
	?>

	<!-- header section starts  -->

	<header class="header">

		<section class="flex">

			<a href="#home" class="logo"><span>P</span>izza</a>

			<nav class="navbar">
				<a href="#home">Home</a>
				<a href="#about">About</a>
				<a href="#menu">Menu</a>
				<a href="#order">Order</a>
				<a href="#faq">FAQ</a>
			</nav>

			<div class="icons">
				<div id="menu-btn" class="fas fa-bars"></div>
				<div id="user-btn" class="fas fa-user"></div>
				<div id="order-btn" class="fas fa-box"></div>
				<?php
				$count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
				$count_cart_items->execute([$user_id]);
				$total_cart_items = $count_cart_items->rowCount();
				?>
				<div id="cart-btn" class="fas fa-shopping-cart"><span>(<?= $total_cart_items; ?>)</span></div>
			</div>

		</section>

	</header>

	<!-- header section ends -->

	<div class="user-account">

		<section>

			<div id="close-account"><span>close</span></div>

			<div class="user">
				<?php
				$select_user = $conn->prepare("SELECT * FROM `user` WHERE id = ?");
				$select_user->execute([$user_id]);
				if ($select_user->rowCount() > 0) {
					while ($fetch_user = $select_user->fetch(PDO::FETCH_ASSOC)) {
						echo '<p>welcome ! <span>' . $fetch_user['name'] . '</span></p>';
						echo '<a href="index.php?logout" class="btn">logout</a>';
					}
				} else {
					echo '<p><span>you are not logged in now!</span></p>';
				}
				?>
			</div>

			<div class="display-orders">
				<?php
				$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
				$select_cart->execute([$user_id]);
				if ($select_cart->rowCount() > 0) {
					while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
						echo '<p>' . $fetch_cart['name'] . ' <span>(' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')</span></p>';
					}
				} else {
					echo '<p><span>your cart is empty!</span></p>';
				}
				?>
			</div>

			<div class="flex">

				<form action="user_login.php" method="post">
					<h3>login now</h3>
					<input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
					<input type="password" name="pass" required class="box" placeholder="enter your password"
						maxlength="20">
					<input type="submit" value="login now" name="login" class="btn">
				</form>

				<form action="" method="post">
					<h3>register now</h3>
					<input type="text" name="name" oninput="this.value = this.value.replace(/\s/g, '')" required
						class="box" placeholder="enter your username" maxlength="20">
					<input type="email" name="email" required class="box" placeholder="enter your email" maxlength="50">
					<input type="password" name="pass" required class="box" placeholder="enter your password"
						maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
					<input type="password" name="cpass" required class="box" placeholder="confirm your password"
						maxlength="20" oninput="this.value = this.value.replace(/\s/g, '')">
					<input type="submit" value="register now" name="register" class="btn">
				</form>

			</div>

		</section>

	</div>

	<div class="my-orders">

		<section>

			<div id="close-orders"><span>close</span></div>

			<h3 class="title"> my orders </h3>

			<?php
			$select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
			$select_orders->execute([$user_id]);
			if ($select_orders->rowCount() > 0) {
				while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
					?>
					<div class="box">
						<p> placed on : <span><?= $fetch_orders['placed_on']; ?></span> </p>
						<p> name : <span><?= $fetch_orders['name']; ?></span> </p>
						<p> number : <span><?= $fetch_orders['number']; ?></span> </p>
						<p> address : <span><?= $fetch_orders['address']; ?></span> </p>
						<p> payment method : <span><?= $fetch_orders['method']; ?></span> </p>
						<p> total_orders : <span><?= $fetch_orders['total_products']; ?></span> </p>
						<p> total price : <span>$<?= $fetch_orders['total_price']; ?>/-</span> </p>
						<p> payment status : <span style="color:<?php if ($fetch_orders['payment_status'] == 'pending') {
							echo 'red';
						} else {
							echo 'green';
						}
						; ?>"><?= $fetch_orders['payment_status']; ?></span> </p>
					</div>
					<?php
				}
			} else {
				echo '<p class="empty">nothing ordered yet!</p>';
			}
			?>

		</section>

	</div>

	<div class="shopping-cart">

		<section>

			<div id="close-cart"><span>close</span></div>

			<?php
			$grand_total = 0;
			$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
			$select_cart->execute([$user_id]);
			if ($select_cart->rowCount() > 0) {
				while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
					$sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
					$grand_total += $sub_total;
					?>
					<div class="box">
						<a href="index.php?delete_cart_item=<?= $fetch_cart['id']; ?>" class="fas fa-times"
							onclick="return confirm('delete this cart item?');"></a>
						<img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
						<div class="content">
							<p> <?= $fetch_cart['name']; ?> <span>(<?= $fetch_cart['price']; ?> x
									<?= $fetch_cart['quantity']; ?>)</span></p>
							<form action="" method="post">
								<input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
								<input type="number" name="qty" class="qty" min="1" max="99"
									value="<?= $fetch_cart['quantity']; ?>"
									onkeypress="if(this.value.length == 2) return false;">
								<button type="submit" class="fas fa-edit" name="update_qty"></button>
							</form>
						</div>
					</div>
					<?php
				}
			} else {
				echo '<p class="empty"><span>your cart is empty!</span></p>';
			}
			?>

			<div class="cart-total"> grand total : <span>$<?= $grand_total; ?>/-</span></div>

			<a href="#order" class="btn">order now</a>

		</section>

	</div>

	<div class="home-bg">


		<div class="content">
			<!-- Content for each tab will go here -->
		</div>
		<section class="home" id="home">

			<div class="slide-container">

				<div class="slide active">
					<div class="image">
						<img src="images/home-img-1.png" alt="">
						<div class="btn-ordernowalign"><a href="#menu" class="btn-ordernow">Order Now</a>
						</div>
					</div>

					<div class="content">
						<h3>Unprecedented Delicious Taste</h3>
						<div class="fas fa-angle-left" onclick="prev()"></div>
						<div class="fas fa-angle-right" onclick="next()"></div>
					</div>
				</div>

				<div class="slide">
					<div class="image">
						<img src="images/home-img-2.png" alt="">
						<div class="btn-ordernowalign"><a href="#menu" class="btn-ordernow">Order Now</a>
						</div>
					</div>
					<div class="content">
						<h3>Explode with Pizza</h3>
						<div class="fas fa-angle-left" onclick="prev()"></div>
						<div class="fas fa-angle-right" onclick="next()"></div>
					</div>
				</div>

				<div class="slide">
					<div class="image">
						<img src="images/home-img-3.png" alt="">
						<div class="btn-ordernowalign"><a href="#menu" class="btn-ordernow">Order Now</a>
						</div>
					</div>
					<div class="content">
						<h3>Welcome to our Store</h3>
						<div class="fas fa-angle-left" onclick="prev()"></div>
						<div class="fas fa-angle-right" onclick="next()"></div>
					</div>
				</div>

			</div>

		</section>

	</div>

	<!-- about section starts  -->


	<!-- about section ends -->

	<!-- menu section starts  -->
	<section id="menu" class="menu">

		<h1 class="heading">Fash Food</h1>
		<div class="box-container" id="fast-food-container">
			<!-- Fast Food products will be inserted here -->
		</div>

		<h1 class="heading">Main Dish</h1>
		<div class="box-container" id="main-dish-container">
			<!-- Main Dish products will be inserted here -->
		</div>

		<h1 class="heading">Dessert</h1>
		<div class="box-container" id="dessert-container">
			<!-- Dessert products will be inserted here -->
		</div>

		<h1 class="heading">Rice</h1>
		<div class="box-container" id="rice-container">
			<!-- Rice products will be inserted here -->
		</div>

		<h1 class="heading">List Menu</h1>
		<div class="box-container" id="list-food-container">
			<!-- List Menu products will be inserted here -->
		</div>

	</section>

	<?php
	$select_products = $conn->prepare("SELECT * FROM `products`");
	$select_products->execute();
	if ($select_products->rowCount() > 0) {
		while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
			$product_type = $fetch_products['type'];
			$product_html = '
		<div class="box">
			<div class="price">' . $fetch_products['price'] . 'đ/1</div>
			<img src="uploaded_img/' . $fetch_products['image'] . '" alt="">
			<div class="name">' . $fetch_products['name'] . '</div>
			<form action="" method="post">
				<input type="hidden" name="pid" value="' . $fetch_products['id'] . '">
				<input type="hidden" name="name" value="' . $fetch_products['name'] . '">
				<input type="hidden" name="price" value="' . $fetch_products['price'] . '">
				<input type="hidden" name="image" value="' . $fetch_products['image'] . '">
				<input type="number" name="qty" class="qty" min="1" max="99"
					onkeypress="if(this.value.length == 2) return false;" value="1">
				<input type="submit" class="btn" name="add_to_cart" value="add to cart">
			</form>
		</div>
	';

			if ($product_type == 'fast-food') {
				echo "<script>document.getElementById('fast-food-container').innerHTML += `$product_html`;</script>";
			} elseif ($product_type == 'main-dish') {
				echo "<script>document.getElementById('main-dish-container').innerHTML += `$product_html`;</script>";
			} elseif ($product_type == 'dessert') {
				echo "<script>document.getElementById('dessert-container').innerHTML += `$product_html`;</script>";
			} elseif ($product_type == 'rice') {
				echo "<script>document.getElementById('rice-container').innerHTML += `$product_html`;</script>";
			} elseif ($product_type == 'list-food') {
				echo "<script>document.getElementById('list-food-container').innerHTML += `$product_html`;</script>";
			}
		}
	} else {
		echo '<p class="empty">no products added yet!</p>';
	}
	?>

	<!-- menu section ends -->

	<!-- order section starts  -->

	<section class="order" id="order">

		<h1 class="heading">Order Now</h1>

		<form action="" method="post">

			<div class="display-orders">

				<?php
				$grand_total = 0;
				$cart_item[] = '';
				$select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
				$select_cart->execute([$user_id]);
				if ($select_cart->rowCount() > 0) {
					while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
						$sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']);
						$grand_total += $sub_total;
						$cart_item[] = $fetch_cart['name'] . ' (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ') - ';
						$total_products = implode($cart_item);
						echo '<p>' . $fetch_cart['name'] . ' <span>(' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ')</span></p>';
					}
				} else {
					echo '<p class="empty"><span>Your cart is empty!</span></p>';
				}
				?>

			</div>

			<div class="grand-total"> grand total : <span>$<?= $grand_total; ?>/-</span></div>

			<input type="hidden" name="total_products" value="<?= $total_products; ?>">
			<input type="hidden" name="total_price" value="<?= $grand_total; ?>">

			<input type="submit" value="Order Now" class="btn" name="order" onclick="return confirmOrder()">

		</form>

	</section>
	<script>
		function confirmOrder() {
			return confirm("Are you sure you want to place this order?");
		}
	</script>


	<!-- order section ends -->

	<!-- faq section starts  -->

	<section class="faq" id="faq">

		<h1 class="heading">FAQ</h1>

		<div class="accordion-container">

			<div class="accordion active">
				<div class="accordion-heading">
					<span>how does it work?</span>
					<i class="fas fa-angle-down"></i>
				</div>
				<p class="accrodion-content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam
					accusantium maxime, doloremque iusto deleniti veritatis quos.
				</p>
			</div>

			<div class="accordion">
				<div class="accordion-heading">
					<span>how long does it take for delivery?</span>
					<i class="fas fa-angle-down"></i>
				</div>
				<p class="accrodion-content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam
					accusantium maxime, doloremque iusto deleniti veritatis quos.
				</p>
			</div>

			<div class="accordion">
				<div class="accordion-heading">
					<span>can I order for huge parties?</span>
					<i class="fas fa-angle-down"></i>
				</div>
				<p class="accrodion-content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam
					accusantium maxime, doloremque iusto deleniti veritatis quos.
				</p>
			</div>

			<div class="accordion">
				<div class="accordion-heading">
					<span>how much protein it contains?</span>
					<i class="fas fa-angle-down"></i>
				</div>
				<p class="accrodion-content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam
					accusantium maxime, doloremque iusto deleniti veritatis quos.
				</p>
			</div>


			<div class="accordion">
				<div class="accordion-heading">
					<span>is it cooked with oil?</span>
					<i class="fas fa-angle-down"></i>
				</div>
				<p class="accrodion-content">
					Lorem ipsum dolor sit amet consectetur adipisicing elit. Officiis, quas. Quidem minima veniam
					accusantium maxime, doloremque iusto deleniti veritatis quos.
				</p>
			</div>

		</div>

	</section>

	<!-- faq section ends -->

	<!-- footer section starts  -->

	<section class="footer">

		<div class="box-container">

			<div class="box">
				<i class="fas fa-phone"></i>
				<h3>phone number</h3>
				<p>+123-456-7890</p>
				<p>+111-222-3333</p>
			</div>

			<div class="box">
				<i class="fas fa-map-marker-alt"></i>
				<h3>our address</h3>
				<p>Tokyo Japan Tachikawashi 21130</p>
			</div>

			<div class="box">
				<i class="fas fa-clock"></i>
				<h3>opening hours</h3>
				<p>00:09am to 00:10pm</p>
			</div>

			<div class="box">
				<i class="fas fa-envelope"></i>
				<h3>email address</h3>
				<p>hoangquyhaook@gmail.com</p>
				<p>fillinpizza@gmail.com</p>
			</div>

		</div>

		<!-- <div class="credit">
		 &copy; copyright @ <?= date('Y'); ?> by <span>mr. web designer</span> | all rightss reserved!
	  </div> -->

	</section>

	<!-- footer section ends -->



	<!-- custom js file link  -->
	<script src="js/script.js"></script>


</body>

</html>