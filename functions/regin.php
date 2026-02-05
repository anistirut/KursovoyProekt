<?php
    session_start();
    include("../settings/connect_database.php");

    $surname = $_POST["surname"];
    $name = $_POST["name"];
    $patronomyc = $_POST["patronomyc"];
    $phone = preg_replace('/\D+/', '', $_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = $mysqli->query("SELECT * FROM `Users` WHERE `Phone`='".$phone."';");
    $id = -1;

    if($read = $query->fetch_row()) {
        echo $id;
    } else {
        $mysqli->query("INSERT INTO `Users`(`Surname`, `Name`, `Patronomyc`, `Phone`, `Password`, `Role`) VALUES ('{$surname}','{$name}','{$patronomyc}','{$phone}','{$password}','client')");
        
        $query = $mysqli->query("SELECT * FROM `Users` WHERE `Phone`='".$phone."' AND `Password`='".$password."';");
        $user_new = $query->fetch_row();
        $id = $user_new[0];

        if($id != -1) {
            $_SESSION['user'] = $id;

            if(isset($_SESSION["user"]) && $_SESSION["user"] != -1) {
            $query = $mysqli->query("SELECT `Role` FROM `Users` WHERE `Id` = ".$_SESSION["user"]);

                if($query && $query->num_rows == 1) {
                    $read = $query->fetch_assoc();
                    $role = $read["Role"];

                    if ($role == 'client') {
                        header("Location: ../client/client.php");
                        exit;
                    } elseif ($role == 'waiter') {
                        header("Location: ../waiter/waiter.php");
                        exit;
                    } elseif ($role == 'admin') {
                        header("Location: ../admin/admin.php");
                        exit;
                    }
                }
            }
        }
    }
?>