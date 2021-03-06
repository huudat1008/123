<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/account.php';
  
// instantiate database and account object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$account = new Account($db);
  // set ID
$id = isset($_GET['id']) ? $_GET['id'] : die();
// query account
$stmt = $account->getInfoUserById($id);
$num = $stmt->rowCount();

// check if more than 0 record found
if($num>0){
    // retrieve our table contents
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_arr =array(
        "id" => $row['id'],
        "name" => $row['name'],
        "username" => $row['username'],
        "group_id" => $row['group_id'],
        "group_title" => $row['group_title'],
        "email" => $row['email'],
        "password" => $row['password'],
        "block" => $row['block'],
        "sendEmail" => $row['sendEmail'],
        "registerDate" => $row['registerDate'],
        "lastvisitDate" => $row['lastvisitDate'],
        "activation" => $row['activation'],
        "params" => $row['params'],
        "lastResetTime" => $row['lastResetTime'],
        "resetCount" => $row['resetCount'],
        "requireReset" => $row['requireReset'],
        "otpKey" => $row['otpKey'],
        "otep" => $row['otep']
    );
  
    // set response code - 200 OK
    http_response_code(200);
  
    // show account data in json format
    echo json_encode($user_arr);
} else {
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user no account found
    echo json_encode(
        array("message" => "No user found.")
    );
}