<?php
    session_start();
    include("../settings/connect_database.php");

    $surname = $_POST["Surname"];
    $name = $_POST["Name"];
    $patronomyc = $_POST["Patronomyc"];
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
        }
    }
?>