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

/** HANDLE LOGINS **/

// First, get the email and password
$email = $_POST['email'] != null ? $_POST['email'] : $_GET['email'];
$password = $_POST['password'] != null ? $_POST['password'] : $_GET['password'];

// Validate the email format
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    outputResponse(array("status" => "error", "type" => "invalid_email"));
}

// Clean the password
$password = trim($password);
if($password == '') {
    outputResponse(array("status" => "error", "type" => "invalid_password"));
}

// Connect to mysql
include("configuration.php");

// Hash the password
$password_hashed =  crypt($password, $salt);

// Submit the query
$stmt = $conn->prepare("SELECT user_verified, user_email, user_displayname, user_description, user_logohash, user_url, user_tags FROM ubcollaborate_users WHERE user_email=? AND user_password=? LIMIT 1");

$stmt->bind_param('ss', $email, $password_hashed);
$stmt->execute();
$stmt->bind_result($user_verified, $user_email, $user_displayname, $user_description, $user_logohash, $user_url, $user_tags);

while ($stmt->fetch())
{
    // If the user isn't verified yet, return an error
    $user_verified = $user_verified == '1' ? true : false;
    if(!$user_verified) outputResponse(array("status" => "error", "type" => "user_not_verified"));
    
    // The user is verified, output their data
    outputResponse(array("status" => "success",
                         "user_email" => $user_email,
                         "user_displayname" => $user_displayname,
                         "user_description" => $user_description,
                         "user_logohash"    => $user_logohash,
                         "user_url"         => $user_url,
                         "user_tags"        => $user_tags));
}
$stmt->close();

// If we get here, no rows were returned
outputResponse(array("status" => "error", "type" => "invalid_password"));

?>