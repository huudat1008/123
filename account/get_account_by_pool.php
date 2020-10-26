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
$user_id = isset($_GET['id']) ? $_GET['id'] : die();
$domain_name = isset($_GET['domain']) ? $_GET['domain'] : die();
// read the details of product to be edited
$account->getAccountByUserPool($user_id, $domain_name);

if($account->account_id != null) {
    //create array
    $account_arr = array(
        "id" => $account->account_id,
        "user_id" => $account->user_id,
        "pool_id" => $account->pool_id,
        "name" => $account->name,
        "full_name" => $account->full_name,
        "username" => $account->username,
        "email" => $account->email,
        "groups" => $account->groups,
        "currentBalance" => $account->currentBalance,
        "joiningDate" => $account->joiningDate,
        "expiredTime" => $account->expiredTime,
        "lastvisitDate" => $account->lastvisitDate,
        "isChanged" => $account->isChanged,
        "displayName" => $account->displayName,
        "subcribemail" => $account->subcribemail,
        "subcribetype" => $account->subcribetype
    );

    // set response code - 200 OK
    http_response_code(200);
    // make it json format
    echo json_encode($account_arr);
} else {
    // set response code - 404 Not found
    http_response_code(404);
    // tell the user product does not exist
    echo json_encode(array('message' => 'Account does not exist.'));
}
?>