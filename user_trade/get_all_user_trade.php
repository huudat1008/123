<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/user_trade.php';
  
// instantiate database and product object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$user_trade = new UserTrade($db);
  // set ID
$id = isset($_GET['id']) ? $_GET['id'] : die();
$is_demo = isset($_GET['is_demo']) ? $_GET['is_demo'] : false;
// query products
$stmt = $user_trade->getAllUserTrade($id, $is_demo);
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
    // products array
    $user_trade_arr=array();
    $user_trade_arr["records"]=array();
  
    // retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        extract($row);
  
        $user_trade_item=array(
            "id" => $id,
            "amount" => $amount,
            "change_percen" => $change_percen,
            "reset_percen" => $reset_percen,
            "amount_trade" => $amount_trade,
            "fee_percent" => $fee_percent,
            "profit_loss" => $profit_loss,
            "trade_balance_before_trade" => $trade_balance_before_trade,
            "user_balance_before_trade" => $user_balance_before_trade
        );
  
        array_push($user_trade_arr["records"], $user_trade_item);
    }
  
    // set response code - 200 OK
    http_response_code(200);
  
    // show products data in json format
    echo json_encode($user_trade_arr);
} else {
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user no products found
    echo json_encode(
        array("message" => "No products found.")
    );
}