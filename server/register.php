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

/**
 * Generates 32 chars of a GUID.
 *
 * @return String
 */
function generateGUID() {
    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
}

/** HANDLE REGISTRATION **/

// Get all the data first
$email           = $_GET['email'];
$password        = $_GET['password'];
$password_verify = $_GET['password_verify'];
$displayname     = $_GET['displayname'];
$logohash        = $_GET['logohash'];
$description     = $_GET['description'];
$url             = $_GET['url'];
$tags            = $_GET['tags'];
$guid            = generateGUID();

// Validate the email format
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    outputResponse(array("status" => "error", "type" => "invalid_email"));
}

// Make sure the passwords match
$password        = trim($password);
$password_verify = trim($password_verify);
if($password !== $password_verify) {
    outputResponse(array("status" => "error", "type" => "passwords_mismatch"));
}

// Connect to MySQL
include("configuration.php");

// Hash the password
$password_hashed = crypt($password, $salt);

// Submit a request to see if the email is in use
$stmt = $conn->prepare("SELECT user_email FROM ubcollaborate_users WHERE user_email=? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($user_email);

while($stmt->fetch())
{
    outputResponse(array("status" => "error",
                        "type" => "email_in_use"));
}
$stmt->close();

// The email is not in use if we get here
$stmt = $conn->prepare("INSERT into ubcollaborate_users (user_email, user_password, user_displayname, user_logohash, user_description, user_url, user_tags, user_guid) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sssssss', $email, $password_hashed, $displayname, $logohash, $description, $url, $tags, $guid);
$stmt->execute();

if($stmt->fetch()) outputResponse(array("status" => "success"));
else outputResponse(array("status" => "error"));

?>