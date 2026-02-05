<?php
    session_start();
    include("./settings/connect_database.php");

    if(isset($_SESSION["user"]) && $_SESSION["user"] != -1) {
        $query = $mysqli->query("SELECT `Role` FROM `Users` WHERE `Id` = ".$_SESSION["user"]);

        if($query && $query->num_rows == 1) {
            $read = $query->fetch_assoc();
            $role = $read["Role"];

            if ($role == 'client') {
                header("Location: client/client.php");
                exit;
            } elseif ($role == 'waiter') {
                header("Location: waiter/waiter.php");
                exit;
            } elseif ($role == 'admin') {
                header("Location: admin/admin.php");
                exit;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница регистрации</title>
</head>
<body>
    
</body>
</html>