<?php
    session_start();
    include("../settings/connect_database.php");

    if(isset($_SESSION['user'])) {
        if($_SESSION['user'] != -1) {
            $query = $mysqli->query("SELECT `Role`, `Name`, `Surname` FROM `Users` WHERE `Id` = ".$_SESSION["user"]);
            if($query && $query->num_rows == 1) {
                $read = $query->fetch_assoc();
                $role = $read["Role"];

                if ($role == 'client') {
                    header("Location: ../client/client.php");
                    exit;
                } elseif ($role == 'waiter') {
                    header("Location: ../waiter/waiter.php");
                    exit;
                }
            }
        } else {
            header("Location: ../index.php");
        }
    } else {
        header("Location: ../index.php");
    }
    $username = $read["Name"]. ' '. $read["Surname"];
    $usersQuery = $mysqli->query("SELECT * FROM `Users` ORDER BY `Id`");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница управления пользователями</title>
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
        <a class="navbar-brand" href="admin.php">Admin Panel</a>
        <div class="collapse navbar-collapse" id="navbarAdmin">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="users.php">Пользователи</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="dishes.php">Блюда</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="orders.php">Заказы</a>
            </li>
          </ul>
          <span class="navbar-text me-3">Привет, <?= $username?></span>
          <a href="../functions/logout.php" class="btn btn-outline-danger">Выйти</a>
        </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Пользователи</h3>
            <a href="add/addUser.php" class="btn btn-accent">Добавить пользователя</a>
        </div>

        <?php if(isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
            <div class="alert alert-success">Пользователь успешно удалён!</div>
        <?php endif; ?>
        <?php if(isset($_GET['error']) && $_GET['error'] === 'own'): ?>
            <div class="alert alert-danger">Вы не можете удалить себя!</div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Фамилия</th>
                        <th>Имя</th>
                        <th>Отчество</th>
                        <th>Телефон</th>
                        <th>Роль</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = $usersQuery->fetch_assoc()): ?>
                        <tr>
                            <td><?= $u['Id'] ?></td>
                            <td><?= $u['Surname'] ?></td>
                            <td><?= $u['Name'] ?></td>
                            <td><?= $u['Patronomyc'] ?></td>
                            <td><?= $u['Phone'] ?></td>
                            <td><?= $u['Role'] ?></td>
                            <td>
                                <a href="edit/editUser.php?id=<?= $u['Id'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                                <a href="delete/deleteUser.php?id=<?= $u['Id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить этого пользователя?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>