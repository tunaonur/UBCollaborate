<?php

/**
 * Outputs the given array as a JSON object and kills the script.
 */
function outputResponse($data) {
    header('Access-Control-Allow-Origin: *');  
    header("Content-type: text/json");
    
    echo json_encode($data);
    exit;
}

/** HANDLE FORGOTTEN PASSWORD VERIFICATION  **/

// Get all the data first
$user_id = $_GET['id'];
$user_guid = $_GET['guid'];

// Connect to MySQL
include("configuration.php");

// Check if this row exists in the forgottenpasswords table
$stmt = $conn->prepare("SELECT fpr_newpassword FROM ubcollaborate_forgottenpasswords WHERE fpr_userid=? AND fpr_guid=? LIMIT 1");
$stmt->bind_param('ss', $user_id, $user_guid);
$stmt->execute();
$stmt->bind_result($user_newpassword);

if($stmt->fetch() == false) {
    outputResponse(array("status" => "error"));
}
$stmt->close();

// We got the new password, now copy it over
$stmt = $conn->prepare("UPDATE ubcollaborate_users SET user_password=? WHERE user_id=?");
$stmt->bind_param('si', $user_newpassword, $user_id);
$stmt->execute();
$stmt->close();

// Now delete the fpr row
$stmt = $conn->prepare("DELETE FROM ubcollaborate_forgottenpasswords WHERE fpr_userid=? LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->close();

outputResponse(array("status" => "success"));

?>