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
$

// Connect to the MySQL database
include("configuration.php");

// Prepare the selection query for getting all of the open collaborations
$stmt = $conn->prepare("SELECT collab_userid, collab_title, collab_description, collab_tags, collab_timestamp FROM ubcollaborate_collabs ORDER BY " . $sortColumn . " DESC");
$stmt->execute();
$stmt->bind_result($collab_userid, $collab_title, $collab_description, $collab_tags, $collab_timestamp);

// For every collaboration that was retrieved, add it to the results array
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

// Output the results as json
outputResponse(array("status"  => "success",
                     "results" => $results));

?>