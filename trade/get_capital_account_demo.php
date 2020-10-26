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
$time_selection = isset($_GET['time_selection']) ? $_GET['time_selection'] : die();

// read the details of trade to be edited
$trade->getCaptitalAccountDemo($account_id, $time_selection);

if($trade->capital != null) {
    //create array
    $trade_arr = array(
        "capital" => $trade->capital
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