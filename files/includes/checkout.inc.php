<?php
// include "./includes/db.inc.php";

if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['checkout'])){
  if(strlen($_POST['fname']) < 1) $error[] = "First name is required!";
  else if(strlen($_POST['lname']) < 1) $error[] = "Last name is required!";
  else if (strlen($_POST['email']) < 1) $error[] = "Email is required";
  else if(strlen($_POST['num']) < 1) $error[] = "Your phone number is required";
  else if(strlen($_POST['address']) < 1) $error[] = "Please enter your address!";
  else{
    $fname = mysqli_real_escape_string($db,$_POST['fname']);
    $lname = mysqli_real_escape_string($db,$_POST['lname']);
    // $email = mysqli_real_escape_string($db,$_POST['email']);
    $email = $_SESSION['auth']['email'];
    $num = mysqli_real_escape_string($db,$_POST['num']);
    $address = mysqli_real_escape_string($db,$_POST['address']);
    $payment_mode = mysqli_real_escape_string($db,$_POST['payment_mode']);
    $letters = "abcdefghijklmnopqrstuvwxyz";
    $tracking_no = rand(1111,9999).substr(str_shuffle($letters),0,4).substr($num,2);
    $user_id = $_SESSION['auth']['id'];
    $total_price = 0;
    $total_price = $_SESSION['total'];

    //Prepare order sql statement
    $q = "INSERT INTO orders(tracking_no,user_id,fname,lname,email,phone,address,total_price,payment_mode) VALUES(?,?,?,?,?,?,?,?,?)";
    $stmt = $db->prepare($q) or die($db->error);
    $stmt->bind_param("sisssssis",$tracking_no,$user_id,$fname,$lname,$email,$num,$address,$total_price,$payment_mode);
    $stmt->execute();
    
    $orderId =$db->query('SELECT LAST_INSERT_ID() AS order_id')->fetch_assoc()['order_id'];//Get the last insirted id method 1
    $order_data=array(
      'tracking_no' => $tracking_no,
      'user_id' => $user_id,
      'fname' => $fname,
      'lname' => $lname,
      'email' => $email,
      'phone' => $num,
      'address' => $address,
      'total_price' => $total_price,
      'payment_mode' => $payment_mode,
      'order_id' => $orderId,
    );
    $_SESSION['auth_user'] = $order_data;
    $stmt->close();

    //Build a multi-rows SQL query to insert all cart items at once
    $values = array();
    foreach($_SESSION['cart'] as $item){
      $values[] = sprintf('(%s,%s,%s,%s)',
      $orderId,
      $item['productId'],
      $item['productPrice'],
      $item['qty']
    );
    }

    $query = sprintf("INSERT INTO orders_items(order_id,product_id,price,qty) VALUES %s",implode(',',$values));
    $db->query($query) or die($db->error);
    require "./includes/phpMailer.php";
    $db->close();    if($stmt){
      unset($_SESSION['cart'])
      ?>
      <script>
        window.location.href = "?ref2=thank-you";
      </script>
      <?php
    }
  }  
}

  //Get the last inserted id method 2
  // $order_id = $db->insert_id;

  //Loop through the cart items and add them to the order_itmes table
  // foreach($_SESSION['cart'] as $item){
  //   $product_id = $item['productId'];
  //   $qty = $item['qty'];
  //   $price = $item['productPrice'];
  // }

  // $query = "INSERT INTO orders_items(order_id,product_id,price,qty) VALUES(LAST_INSERT_ID(),?,?,?)";
  // $stm=$db->prepare($query) or die($db->error);
  // $stm->bind_param('sss',$product_id,$price,$qty);
  // $stm->execute();
  // $stm->close();


  // $product_query =$db->query("SELECT * FROM products WHERE id = '$product_id'  LIMIT 1") or die($db->error);
  // $data = mysqli_fetch_assoc($product_query);
  // $c_qty = $data['qty'];
  // $n_qty = $c_qty - $qty;
  // $update_query = $db->query("UPDATE products SET qty='$new_qty' WHERE id = '$product_id'");





