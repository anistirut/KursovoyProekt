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

        $query = $mysqli->query("SELECT `Role` FROM `Users` WHERE `Id` = ".$_SESSION["user"]);

        if($query && $query->num_rows == 1) {
            $read = $query->fetch_assoc();
            $role = $read["Role"];

            if ($role == 'client') {
                header("Location: ../client/client.php");
                exit;
            } elseif ($role == 'courier') {
                header("Location: ../courier/courier.php");
                exit;
            } elseif ($role == 'admin') {
                header("Location: ../admin/admin.php");
                exit;
            }
        }
    }
?>