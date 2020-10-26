<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/trade.php';
  
// instantiate database and account object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$trade = new Trade($db);
$time_started = isset($_GET['time_started']) ? $_GET['time_started'] : null;
$time_finalised = isset($_GET['time_finalised']) ? $_GET['time_finalised'] : null;

// query account
$stmt = $trade->getAllTrade($time_started, $time_finalised);
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
    $pool_arr["records"]=array();
    // retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        extract($row);
        $trade_item =array(
            "id" => $row['trade_id'],
            "title" => $row['title'],
            "content" => $row['content'],
            "content_result" => $row['content_result'],
            "pool_id" => $row['pool_id'],
            "amount" => $row['amount'],
            "profit_loss" => $row['profit_loss'],
            // "balance_before_trade" => $row['balance_before_trade'],
            // "balance_after_trade" => $row['balance_after_trade'],
            // "balance_after_trade_update" => $row['balance_after_trade_update'],
          	"trade_status" => $row['trade_status'],
            "time_started" => $row['time_started'],
            "time_finalised" => $row['time_finalised'],
          	// "updated_at" => $row['updated_at'],
            "link_score" => $row['link_score'],
            "game" => $row['game']
        );
        array_push($pool_arr["records"], $trade_item);
    }
    // set response code - 200 OK
    http_response_code(200);
  
    // show trade data in json format
    echo json_encode($pool_arr);
} else {
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user no trade found
    echo json_encode(
        array("message" => "No trade found.")
    );
}