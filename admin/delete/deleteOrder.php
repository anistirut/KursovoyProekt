<?php
    session_start();
    include("../../settings/connect_database.php");

    if(isset($_SESSION['user'])) {
        if($_SESSION['user'] != -1) {
            $query = $mysqli->query("SELECT `Role` FROM `Users` WHERE `Id` = ".$_SESSION["user"]);
            if($query && $query->num_rows == 1) {
                $read = $query->fetch_assoc();
                $role = $read["Role"];

                if ($role == 'client') {
                    header("Location: ../../client/client.php");
                    exit;
                } elseif ($role == 'courier') {
                    header("Location: ../../courier/courier.php");
                    exit;
                }
            }
        } else {
            header("Location: ../../index.php");
        }
    } else {
        header("Location: ../../index.php");
    }

    $id = $_GET['id'];

    $mysqli->query("DELETE FROM `Orders` WHERE `Id` =".$id);

    header("Location: ../orders.php");
    exit;
?>
