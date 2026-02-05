<?php
    session_start();
    include("../settings/connect_database.php");
    
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    $query = $mysqli->query("SELECT * FROM `Users` WHERE `Phone`='".$phone."' AND `Password`='".$password."';");
    
    $id = -1;

    while($read = $query->fetch_row()) {
        $id = $read[0];
    }

    if($id != -1) {
        $_SESSION['user'] = $id;
    }
?>