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

/** HANDLE GETTING COLLABS **/
include("configuration.php");

$stmt = $conn->prepare("SELECT collab_userid, collab_title, collab_description, collab_tags, collab_timestamp FROM ubcollaborate_collabs");
$stmt->execute();
$stmt->bind_result($collab_userid, $collab_title, $collab_description, $collab_tags, $collab_timestamp);

$results = array();

while($stmt->fetch())
{
    $row = array("collab_userid"      => $collab_userid,
                 "collab_title"       => $collab_title,
                 "collab_description" => $collab_description,
                 "collab_tags"        => $collab_tags,
                 "collab_timestamp"   => $collab_timestamp);
    array_push($results, $row);
}
$stmt->close();

outputResponse(array("status"  => "success",
                     "results" => $results));

?>