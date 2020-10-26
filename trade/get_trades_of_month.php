<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/trade.php';
  
// instantiate database and trade object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$trade = new Trade($db);
  // set ID
$account_id = isset($_GET['id']) ? $_GET['id'] : die();
$pool_id = isset($_GET['pool_id']) ? $_GET['pool_id'] : die();
$first_day_of_month = isset($_GET['first_day_of_month']) ? $_GET['first_day_of_month'] : null;
$last_day_of_month = isset($_GET['last_day_of_month']) ? $_GET['last_day_of_month'] : null;
$is_demo = isset($_GET['is_demo']) ? $_GET['is_demo'] : false;
// query trades
$stmt = $trade->getTradesOfMonth($account_id, $pool_id, $first_day_of_month, $last_day_of_month, $is_demo);

$num = $stmt->rowCount();
// check if more than 0 record found
if($num>0){
    // trades array
    $trades_arr=array();
    $trades_arr["records"]=array();
  
    // retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        extract($row);
  
        $trade_item=array(
            "id" => $id,
            "trade_amount" => $trade_amount,
            "fee_percent" => $fee_percent,
            "amount" => $amount,
            "profit_loss" => $profit_loss,
            "content" => $content,
            "time_started" => $time_started,
            "time_finalised" => $time_finalised,
            "trade_status" => $trade_status,
            "reset_percen" => $reset_percen,
            "content_result" => $content_result,
            "user_balance_before_trade" => $user_balance_before_trade,
            "user_balance_before_trade_fixed" => $user_balance_before_trade_fixed,
            "user_balance_after_trade_update" => $user_balance_after_trade_update,
            "user_balance_after_trade" => $user_balance_after_trade,
            "trade_balance_before_trade" => $trade_balance_before_trade,
            "balance_before_trade_fixed" => $balance_before_trade_fixed,
            "balance_after_trade_update" => $balance_after_trade_update,
            "balance_after_trade" => $balance_after_trade,
            "timestamp" => $timestamp,
            "day_of_month" => $day_of_month
        );
  
        array_push($trades_arr["records"], $trade_item);
    }
  
    // set response code - 200 OK
    http_response_code(200);
  
    // show trades data in json format
    echo json_encode($trades_arr);
} else {
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user no trades found
    echo json_encode(
        array("message" => "No trade found.")
    );
}