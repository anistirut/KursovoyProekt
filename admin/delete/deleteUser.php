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
                } elseif ($role == 'waiter') {
                    header("Location: ../../waiter/waiter.php");
                    exit;
                }
            }
        } else {
            header("Location: ../../index.php");
        }
    } else {
        header("Location: ../../index.php");
    }

    $userToDelete = $_GET["id"];

    if ($userToDelete === $_SESSION['user']) {
        header("Location: ../users.php?error=own");
        exit;
    }

    $query = $mysqli->query("DELETE FROM `Users` WHERE `Id` =".$userToDelete);
    header("Location: ../users.php?success=deleted");
    exit;
?>