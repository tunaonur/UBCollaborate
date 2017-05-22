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
 * Generates 36 chars of a GUID.
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
$user_guid       = generateGUID();

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
$stmt = $conn->prepare("SELECT user_id FROM ubcollaborate_users WHERE user_email=? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($user_id);

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
$stmt->bind_param('sss', $user_id, $password_hashed, $user_guid);
$stmt->execute();
$stmt->close();

// Now send an email to the user
$subject = 'Password Reset Request from UBCollaborate';
$message = 'Password Reset Request from UBCollaborate' . '\n\n';
$message .= 'Please follow this link to reset your password:' . '\n';
$message .= 'https://www.ryanwirth.ca/misc/ubcollaborate/forgotpassword_verify.php?id=' . $user_id . "&guid=" . $user_guid;
$headers = 'From: noreply@ryanwirth.ca' . "\r\n" .
           'Reply-To: noreply@ryanwirth.ca' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

mail($email, $subject, $message, $headers);

outputResponse(array("status" => "success"));

?>