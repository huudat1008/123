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
$pool_id = isset($_GET['pool_id']) ? $_GET['pool_id'] : die();
$first_day_of_month = isset($_GET['first_day_of_month']) ? $_GET['first_day_of_month'] : die();
// query products
$stmt = $user_trade->getUserTradeOfMonth($pool_id, $first_day_of_month);

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
            "profit_loss" => $profit_loss,
            "time_finalised" => $time_finalised,
            "change_percen" => $change_percen,
            "reset_percen" => $reset_percen,
            "amount_trade" => $amount_trade,
            "trade_status" => $trade_status,
            "user_percen" => $user_percen,
            "trade_balance_before_trade" => $trade_balance_before_trade,
            "user_balance_before_trade" => $user_balance_before_trade,
            "day_of_month" => $day_of_month
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