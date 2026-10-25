<?php

function getCartItems() {
    $email = $_SESSION['user_email'];


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gesco_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    return [];
}
  $stmt_user = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1;");
    $user_email = $_SESSION['user_email'];
    $stmt_user->bind_param("s", $user_email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows === 0) return [];
    $user = $result_user->fetch_assoc();

     $cart_from_query = $conn->prepare("Select * from cart inner join items on cart.item_id = items.id where cart.user_id = ?;");
    $cart_from_query->bind_param("i", $user['user_id']);
    $cart_from_query->execute();
    $res = $cart_from_query->get_result();
   $cart_data = [];
   $cart_count = 0;
   $cart_total = 0;

    if ($res->num_rows >=0 ) {
         
  while ($row = $res->fetch_assoc()) {
    $cart_data[] = $row;
  }

 
    $cart_count_query = $conn->prepare("Select sum(cart.quantity) as tot, sum(cart.quantity * items.price) as tot_price from cart inner join items on items.id = cart.item_id where cart.user_id = ?;");
    $cart_count_query->bind_param("i", $user['user_id']);
    $cart_count_query->execute();
    $result_count = $cart_count_query->get_result();
    $cart_count_assc = $result_count->fetch_assoc();
    $cart_count = (int) $cart_count_assc['tot'];
    $cart_total = (float)  $cart_count_assc['tot_price'];

    return ['cart_data' => $cart_data, 'cart_total' => $cart_total];
}}

?>