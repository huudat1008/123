<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and user trade files
include_once '../config/database.php';
include_once '../objects/user_trade.php';
  
// instantiate database and user trade object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$user_trade = new UserTrade($db);
// set ID
$account_id = isset($_GET['id']) ? $_GET['id'] : die();
$is_demo = isset($_GET['is_demo']) ? $_GET['is_demo'] : false;

// query user trades
$user_trade->getUserTradePrincipal($account_id, $is_demo);

// check if more than 0 record found
if($user_trade->amount != null) {
    //create array
    $user_trades_arr = array(
        "amount" => $user_trade->amount,
        "type" => $user_trade->type,
        "time_started" => $user_trade->time_started,
        "time_finalised" => $user_trade->time_finalised
    );

    // set response code - 200 OK
    http_response_code(200);
  
    // show user trades data in json format
    echo json_encode($user_trades_arr);
} else {
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user no user trades found
    echo json_encode(
        array("message" => "No user trade found.")
    );
}