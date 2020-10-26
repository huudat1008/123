<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// include database and object file
include_once '../config/database.php';
include_once '../objects/account.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare product object
$account = new Account($db);

// set ID
$email = isset($_GET['email']) ? $_GET['email'] : die();

// read the details of product to be edited
$stmt = $account->getUserIdByEmail($email);
$num = $stmt->rowCount();

if($num > 0) {
    $account_arr=array();
    $account_arr["records"]=array();
  
    // retrieve our table contents
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        extract($row);
  
        $trade_item=array(
            "id" => $id
        );
  
        array_push($account_arr["records"], $trade_item);
    }
    // set response code - 200 OK
    http_response_code(200);
    // make it json format
    echo json_encode($account_arr);
} else {
    // set response code - 404 Not found
    http_response_code(404);
    // tell the user product does not exist
    echo json_encode(array('message' => 'User does not exist.'));
}
?>