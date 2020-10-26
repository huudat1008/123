<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// include database and object file
include_once '../config/database.php';
include_once '../objects/trade.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare product object
$trade = new Trade($db);

// set ID
$account_id = isset($_GET['id']) ? $_GET['id'] : die();
$is_demo = isset($_GET['is_demo']) ? $_GET['is_demo'] : false;

// read the details of trade to be edited
$trade->getFirstBalanceOfUser($account_id, $is_demo);

if($trade->trade_id != null) {
    //create array
    $trade_arr = array(
        "id" => $trade->trade_id,
        "trade_status" => $trade->trade_status,
        "profit_loss" => $trade->profit_loss,
        "change_percen" => $trade->change_percen,
        "balance_after_trade_update" => $trade->balance_after_trade_update,
        "reset_percen" => $trade->reset_percen,
        "trade_amount" => $trade->trade_amount,
        "fee_percent" => $trade->fee_percent,
        "amount" => $trade->amount,
        "user_percen" => $trade->user_percen,
        "user_balance_before_trade" => $trade->user_balance_before_trade,
        "trade_balance_before_trade" => $trade->trade_balance_before_trade
    );

    // set response code - 200 OK
    http_response_code(200);
    // make it json format
    echo json_encode($trade_arr);
} else {
    // set response code - 404 Not found
    http_response_code(404);
    // tell the user product does not exist
    echo json_encode(array('message' => 'Trade does not exist.'));
}
?>