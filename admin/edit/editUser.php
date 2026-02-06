<?php
    session_start();
    include("../../settings/connect_database.php");

    if(isset($_SESSION['user'])) {
        if($_SESSION['user'] != -1) {
            $query = $mysqli->query("SELECT `Role`, `Name`, `Surname` FROM `Users` WHERE `Id` = ".$_SESSION["user"]);
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

    $username = $read["Name"]. ' '. $read["Surname"];
    $userToEdit = $_GET["id"];
    $query = $mysqli->query("SELECT * FROM `Users` WHERE `Id` =".$userToEdit);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $surname = trim($_POST['surname']);
        $name = trim($_POST['name']);
        $patronomyc = trim($_POST['patronomyc']);
        $phone = preg_replace('/\D+/', '', $_POST['phone']);
        $role = $_POST['role'];

        $password = $_POST['password'];

        if ($password) {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $mysqli->query("UPDATE `Users` SET `Surname`='{$surname}',`Name`='{$name}',`Patronomyc`='{$patronomyc}',`Password`='{$password}',`Role`='{$role}' WHERE `Id`= ".$userToEdit);
        } else {
            $mysqli->query("UPDATE `Users` SET `Surname`='{$surname}',`Name`='{$name}',`Patronomyc`='{$patronomyc}',`Role`='{$role}' WHERE `Id`= ".$userToEdit);
        }
        header("Location: ../users.php");
        exit;
    }

    $query = $mysqli->query("SELECT * FROM `Users` WHERE `Id` = ".$userToEdit);
    $user = $query->fetch_assoc();

    
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница изменения пользователя</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f7f7;
        }
        .navbar-brand {
            font-weight: 600;
            color: #b02a37 !important;
        }
        .btn-accent {
            background-color: #b02a37;
            border: none;
            color: white;
        }
        .btn-accent:hover {
            background-color: #8f1f2a;
        }
        .card {
            border-radius: 12px;
        }
    </style> 
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-4">
        <div class="container-fluid">
        <a class="navbar-brand" href="../admin.php">Admin Panel</a>
        <div class="collapse navbar-collapse" id="navbarAdmin">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="../users.php">Пользователи</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../dishes.php">Блюда</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../orders.php">Заказы</a>
            </li>
          </ul>
          <span class="navbar-text me-3">Привет, <?= $username?></span>
          <a href="../../functions/logout.php" class="btn btn-outline-danger">Выйти</a>
        </div>
        </div>
    </nav>
    <div class="container mt-4">
    <h3>Редактировать пользователя</h3>

    <form method="post" class="mt-3">
        <div class="mb-3">
            <label for="surname" class="form-label">Фамилия</label>
            <input type="text" class="form-control" id="surname" name="surname" value="<?= $user['Surname'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Имя</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= $user['Name'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="patronomyc" class="form-label">Отчество</label>
            <input type="text" class="form-control" id="patronomyc" name="patronomyc" value="<?= $user['Patronomyc'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Телефон</label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?= $user['Phone'] ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Новый пароль (оставьте пустым, чтобы не менять)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Роль</label>
            <select class="form-select" id="role" name="role" required>
                <option value="client" <?= $user['Role'] === 'client' ? 'selected' : '' ?>>client</option>
                <option value="courier" <?= $user['Role'] === 'courier' ? 'selected' : '' ?>>courier</option>
                <option value="admin" <?= $user['Role'] === 'admin' ? 'selected' : '' ?>>admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-accent">Сохранить изменения</button>
    </form>
</div>
</body>
</html>