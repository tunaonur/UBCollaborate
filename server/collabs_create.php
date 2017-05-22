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

/** HANDLE CREATING A COLLAB **/

// Get all of the data
$email       = $_POST['email'] != "" ? $_POST['email'] : $_GET['email'];
$guid        = $_POST['guid'] != "" ? $_POST['guid'] : $_GET['guid'];
$title       = $_POST['title'] != "" ? $_POST['title'] : $_GET['title'];
$description = $_POST['description'] != "" ? $_POST['description'] : $_GET['description'];
$tags        = $_POST['tags'] != "" ? $_POST['tags'] : $_GET['tags'];

// Connect to the MySQL database
include("configuration.php");

// Attempt to get the user ID (verify the user)
$stmt = $conn->prepare("SELECT user_id FROM ubcollaborate_users WHERE user_email=? AND user_guid=? LIMIT 1");
$stmt->bind_params("ss", $email, $guid);
$stmt->execute();
$stmt->bind_result($user_id);

if($stmt->fetch() == false) outputResponse(array("status" => "error", "type" => "verification_failed"));
$stmt->close();

// The user was verified, insert a new row in the collabs table
$stmt = $conn->prepare("INSERT into ubcollaborate_collabs (collab_userid, collab_title, collab_description, collab_tags) VALUES(?, ?, ?, ?)");
$stmt->bind_params("isss", $user_id, $title, $description, $tags);
$stmt->execute();
$stmt->close();

outputResponse(array("status" => "success"));

?>