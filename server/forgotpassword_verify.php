<?php

/** HANDLE FORGOTTEN PASSWORD VERIFICATION  **/
$success = false;

// Get all the data first
$user_guid = $_GET['guid'];
$fpr_guid  = $_GET['fpr'];

// Connect to MySQL
include("configuration.php");

// Check if this row exists in the forgottenpasswords table
$stmt = $conn->prepare("SELECT fpr_newpassword, fpr_userid FROM ubcollaborate_forgottenpasswords WHERE fpr_guid=? LIMIT 1");
$stmt->bind_param('s', $fpr_guid);
$stmt->execute();
$stmt->bind_result($user_newpassword, $user_id);


if($stmt->fetch() == false) {
    $success = false;
    $stmt->close();
} else {
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT user_id FROM ubcollaborate_users WHERE user_id=? AND user_guid=?");
    $stmt->bind_param("ss", $user_id, $user_guid);
    $stmt->execute();
    
    if($stmt->fetch() == false) {
        $success = false;
        $stmt->close();
    } else {
        $stmt->close();
        
        // Update the password
        $stmt = $conn->prepare("UPDATE ubcollaborate_users SET user_password=? WHERE user_id=? AND user_guid=?");
        $stmt->bind_param("sis", $user_newpassword, $user_id, $user_guid);
        $stmt->execute();
        $stmt->close();
        
        // Now delete the fpr row
        $stmt = $conn->prepare("DELETE FROM ubcollaborate_forgottenpasswords WHERE fpr_userid=? LIMIT 1");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();

        $success = true;
    }
}

?>

<html lang="en">
<head>
    <title>Password Reset - UBCollaborate</title>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Roboto,300|700');
        body {
            font-family:'Roboto', sans-serif;
            margin:0px;
            padding:0px;
            background-color:#E9E9E9;
        }
        
        a {
            text-decoration: none;
            color:#00A8C6;
        }
        
        div.header {
            width:100%;
            height:64px;
            line-height:66px;
            background-color:#00A8C6;
            color:#FFF;
            border-bottom:2px solid #0097B2;
        }
        
        div.header p {
            font-size:18px;
            font-weight: 700;
            margin-left:16px;
            text-shadow: 0px 1px 1px #0097B2;
        }
        
        div.content {
            font-size:14px;
            font-weight:300;
            padding:18px;
            color:#363636;
        }
    </style>
</head>
<body>

<div class="header">
    <p>Password Reset - UBCollaborate (<?php if($success) echo "Success"; else echo "Failure"; ?>)</p>
</div>
<div class="content">
    <?php
        if($success) {
            echo "<p>Your password has been reset successfully. You may now proceed to log in with your new password.</p>";
        } else {
            echo "<p>There was an error. You have either already reset your password or you have not submitted a forgotten password request.<p>";
            echo "<p>If you keep getting this message, please send us an email.</p>";
        }
    ?>
    <br/>
    <p>Regards,</p>
    <p>&mdash; UBCollaborate</p>
</div>

</body>


