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
$select_date = isset($_GET['select_date']) ? $_GET['select_date'] : NULL;
// query trades
$stmt = $trade->getActionTradeByDate($account_id, $select_date);
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
            "content" => $content,
            "trade_status" => $trade_status,
            "message" => $message,
            "time_finalised" => $time_finalised,
            "type" => $type,
            "amount" => $amount,
            "profit_loss" => $profit_loss,
            "timestamp" => $timestamp
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