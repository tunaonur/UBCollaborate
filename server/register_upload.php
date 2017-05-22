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

/** HANDLE REGISTRATION LOGO UPLOAD **/

if(!$_FILES['image']['name']) outputResponse(array("status" => "error",
                                                   "type" => "no_image_selected"));
if($_FILES['image']['error']) outputResponse(array("status" => "error",
                                                   "type" => "image_error"));
if($_FILES['image']['size'] > (10 * 1024000)) outputResponse(array("status" => "error",
                                                                   "type" => "image_too_large"));

// Determine a new filename 
$typeData = explode("/", $_FILES['image']['type']);
$fileType = $typeData[1];

$new_file_name = "logo_" . md5_file($_FILES['image']['tmp_name']) . "_" . time() . "." . $fileType;

// Now move the uploaded image to the hotlink dirname
move_uploaded_file($_FILES['image']['tmp_name'], 'hotlink-ok/'.$new_file_name);

outputResponse(array('status' => "success",
                     "logohash" => $new_file_name));
?>