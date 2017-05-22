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

/** HANDLE FORGOTTEN PASSWORD  **/

// Get all the data first
$email           = $_POST['email'] != "" ? $_POST['email'] : $_GET['email'];
$password        = $_POST['password'] != "" ? $_POST['password'] : $_GET['password'];
$password_verify = $_POST['password_verify'] != "" ? $_POST['password_verify'] : $_GET['password_verify'];
$fpr_guid        = generateGUID();

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

// First get the user's UID
$stmt = $conn->prepare("SELECT user_id, user_guid FROM ubcollaborate_users WHERE user_email=? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($user_id, $user_guid);

// If nothing was selected, give a generic, non-descript error
if($stmt->fetch() == false) {
    outputResponse(array("status" => "error",
                         "type" => "invalid_password"));
}
$stmt->close();

// Now check to make sure a request for this UID isn't already pending
$stmt = $conn->prepare("SELECT fpr_userid FROM ubcollaborate_forgottenpasswords WHERE fpr_userid=? LIMIT 1");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($user_id);

if($stmt->fetch() == true) {
    outputResponse(array("status" => "error",
                         "type"   => "reset_pending"));
}
$stmt->close();

// If we get here, the user id checks out and does not have a reset pending
$stmt = $conn->prepare("INSERT into ubcollaborate_forgottenpasswords (fpr_userid, fpr_newpassword, fpr_guid) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $user_id, $password_hashed, $fpr_guid);
$stmt->execute();
$stmt->close();

// Now send an email to the user
$subject = 'Password Reset - UBCollaborate';
$message = "
<html lang=\"en\"><head> <title>Password Reset - UBCollaborate</title> <style>@import url('https://fonts.googleapis.com/css?family=Roboto,300|700'); body{font-family:'Roboto', sans-serif; margin:0px; padding:0px; background-color:#E9E9E9;}a{text-decoration: none; color:#00A8C6;}div.header{width:100%; height:64px; line-height:66px; background-color:#00A8C6; color:#FFF; border-bottom:2px solid #0097B2;}div.header p{font-size:18px; font-weight: 700; margin-left:16px; text-shadow: 0px 1px 1px #0097B2;}div.content{font-size:14px; font-weight:300; padding:18px; color:#363636;}</style></head><body><div class=\"header\"> <p>Password Reset - UBCollaborate</p></div><div class=\"content\"> <p>This is a notification that someone attempted to reset your password on UBCollaborate. If that someone wasn't you, please send us an email immediately.</p><p><strong>If that someone was you</strong>, please click <a href=\"https://www.ryanwirth.ca/misc/ubcollaborate/forgotpassword_verify.php?guid=".$user_guid."&fpr=".$fpr_guid."\">here</a> to confirm your new password.</p><br/> <p>Regards,</p><p>&mdash; UBCollaborate</p></div></body>
";

// To send HTML mail, the Content-type header must be set
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=iso-8859-1';

$headers[] = 'From: UBCollaborate <noreply@ryanwirth.ca>';

mail($email, $subject, $message, implode("\r\n", $headers));

outputResponse(array("status" => "success"));

?>