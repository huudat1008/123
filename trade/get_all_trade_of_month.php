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
$id = isset($_GET['id']) ? $_GET['id'] : die();
$time_selection = isset($_GET['time_selection']) ? $_GET['time_selection'] : die();
$is_demo = isset($_GET['is_demo']) ? $_GET['is_demo'] : false;

// query trades
$stmt = $trade->getAllTradeOfMonth($id, $time_selection, $is_demo);
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
            "type" => $type,
            "trade_amount" => $trade_amount,
            "profit_loss" => $profit_loss,
            "trade_status" => $trade_status,
            "time_finalised" => $time_finalised,
            "fee_percent" => $fee_percent,
            "change_percen" => $change_percen,
            "reset_percen" => $reset_percen,
            "trade_balance_before_trade" => $trade_balance_before_trade,
            "user_amount" => $amount_trade,
            "ut_id" => $ut_id,
            "user_percen" => $user_percen,
            "WithdrawCapital" => $WithdrawCapital,
            "user_balance_before_trade" => $user_balance_before_trade,
            "user_balance_before_trade_fixed" => $user_balance_before_trade_fixed,
            "balance_after_trade" => $balance_after_trade
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