<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_POST['update_payment'])) {

   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $payment_status = filter_var($payment_status, FILTER_SANITIZE_STRING);
   $update_payment = $conn->prepare("UPDATE `orderseih` SET payment_status = ? WHERE id = ?");
   $update_payment->execute([$payment_status, $order_id]);
   $message[] = 'payment status updated!';
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orderseih` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:admin_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>orders</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom admin style link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

   <?php include 'admin_header.php' ?>

   <section class="orders">

      <h1 class="heading">placed orders</h1>

      <div class="box-container">

         <?php
         $select_orders = $conn->prepare("SELECT * FROM `orderseih`");
         $select_orders->execute();
         if ($select_orders->rowCount() > 0) {
            while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
         ?>
               <div class="box">
                  <p> Dining Table Number : <span><?= $fetch_orders['user_id']; ?></span> </p>
        
                  <p> Total products : <span><?= $fetch_orders['total_products']; ?></span> </p>
                  <p> Total price : <span><?= $fetch_orders['total_price']; ?></span> </p>
                 
                  <form action="" method="post">
                     <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                     <select name="payment_status" class="select">
                        <option selected disabled><?= $fetch_orders['payment_status']; ?></option>
                        <option value="pending">pending</option>
                        <option value="completed">completed</option>
                     </select>
                     <div class="flex-btn">
                        <input type="submit" value="update" class="option-btn" name="update_payment">
                        <a href="admin_orderseih.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this order?');">delete</a>
                     </div>
                     <button class="btn" onclick="return confirmPrint(<?= $fetch_orders['id']; ?>)">Print Bill</button>

                  </form>

               </div>
         <?php
            }
         } else {
            echo '<p class="empty">no orders placed yet!</p>';
         }
         ?>

      </div>

   </section>



   <script src="js/admin_script.js"></script>
   <script>
    function confirmPrint(orderId) {
        // Hiển thị hộp thoại xác nhận
        var confirmed = confirm('Are you sure you want to print the bill?');
        // Nếu người dùng xác nhận, thực hiện in hóa đơn
        if (confirmed) {
            printBill(orderId);
        }
        // Trả về false để ngăn chặn sự kiện mặc định của nút
        return false;
    }

    function printBill(orderId) {
        // Mở một cửa sổ mới để in hóa đơn
        var printWindow = window.open('print_bill.php?order_id=' + orderId, '_blank');
        // Kiểm tra nếu cửa sổ in đã được mở
        if (printWindow) {
            // Chờ cho cửa sổ in được tải hoàn toàn trước khi in
            printWindow.onload = function() {
                printWindow.print(); // In hóa đơn
            }
        } else {
            alert('Your browser blocked the popup window. Please allow popups for this site.');
        }
    }
</script>
</body>

</html>