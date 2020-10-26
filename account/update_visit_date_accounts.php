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

// get id of product to be edited
$data = json_decode(file_get_contents("php://input"));
// set ID property of product to be edited
$account->id = $data->id;
// set product property values
$account->lastvisitDate = $data->lastvisitDate;

if($account->updateVisitDateAccounts()) {
    // set response code - 200 OK
    http_response_code(200);
    // make it json format
    echo json_encode(true);
} else {
    // set response code - 404 Not found
    http_response_code(503);
    // tell the user product does not exist
    echo json_encode(false);
}
?>