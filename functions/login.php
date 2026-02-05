<?php
    session_start();
    include("../settings/connect_database.php");
    
    $phone = preg_replace('/\D+/', '', $_POST['phone']);
    $password = $_POST['password'];

    $query = $mysqli->query("SELECT * FROM `Users` WHERE `Phone`='".$phone."';");
    
    $id = -1;

    while($read = $query->fetch_row()) {
       if(password_verify($password, $read[5])) {
			$id = $read[0];
			break;
		}
    }

    if($id != -1) {
        $_SESSION['user'] = $id;
    }
    echo md5(md5($id));
?>