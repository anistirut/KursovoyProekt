<?php
    session_start();
    include("../settings/connect_database.php");

    if(isset($_SESSION['user'])) {
        if($_SESSION['user'] != -1) {
            $query = $mysqli->query("SELECT `Role`, `Name`, `Surname` FROM `Users` WHERE `Id` = ".$_SESSION["user"]);
            if($query && $query->num_rows == 1) {
                $read = $query->fetch_assoc();
                $role = $read["Role"];

                if ($role == 'admin') {
                    header("Location: ../admin/admin.php");
                    exit;
                } elseif ($role == 'courier') {
                    header("Location: ../courier/courier.php");
                    exit;
                }
            }
        } else {
            header("Location: ../index.php");
        }
    } else {
        header("Location: ../index.php");
    }

    $userId = $_SESSION['user'];
    $userQuery = $mysqli->query("SELECT * FROM Users WHERE Id = {$userId}");
    $user = $userQuery->fetch_assoc();
    $username = $read["Name"]. ' '. $read["Surname"];

    $statusMap = [
        'accepted'  => 'Принят',
        'progress'  => 'Готовится',
        'ready'     => 'Готов',
        'delivery'  => 'В доставке',
        'delivered' => 'Доставлен'
    ];


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $surname = trim($_POST['surname']);
        $patronomyc = trim($_POST['patronomyc']);
        $phone = trim($_POST['phone']);
        $password = trim($_POST['password']);

        if (!empty($password)) {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $mysqli->query("UPDATE `Users` SET `Name`='{$name}', `Surname`='{$surname}', `Patronomyc`='{$patronomyc}', `Password`='{$password}' WHERE `Id`={$userId}");
        } else {
            $mysqli->query("UPDATE `Users` SET `Name`='{$name}', `Surname`='{$surname}', `Patronomyc`='{$patronomyc}' WHERE `Id`={$userId}");
        }
        header("Location: profile.php");
        exit;
    }

    $ordersQuery = $mysqli->query("SELECT 
            o.Id, o.TotalSum, o.Address, o.Status, o.IdCourier,
            GROUP_CONCAT(d.Name, ' (', od.Quantity, 'шт)') AS Dishes
        FROM Orders o
        LEFT JOIN OrdersDishes od ON od.IdOrder = o.Id
        LEFT JOIN Dishes d ON d.Id = od.IdDishes
        WHERE o.IdClient = {$userId}
        GROUP BY o.Id
        ORDER BY o.Id DESC");
        
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
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
            <a class="navbar-brand" href="client.php">Меню ресторана</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a href="profile.php" class="btn btn-outline-primary">Личный кабинет</a>
                    </li>
                </ul>
                <span class="navbar-text me-3">Привет, <?= $username ?></span>
                <a href="../functions/logout.php" class="btn btn-outline-danger">Выйти</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h3>Ваши заказы</h3>
        <div class="table-responsive mb-5">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Блюда</th>
                        <th>Сумма</th>
                        <th>Адрес</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $ordersQuery->fetch_assoc()): ?>
                        <tr>
                            <td><?= $order['Dishes'] ?: '—' ?></td>
                            <td><?= $order['TotalSum'] ?> ₽</td>
                            <td><?= $order['Address'] ?></td>
                            <td><?= $statusMap[$order['Status']] ?? $order['Status'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <h3>Редактировать профиль</h3>
        <form method="post" class="mb-5">
            <input type="hidden" name="update_profile">
            <div class="mb-3">
                <label class="form-label">Имя</label>
                <input type="text" name="name" class="form-control" value="<?= $user['Name'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Фамилия</label>
                <input type="text" name="surname" class="form-control" value="<?= $user['Surname'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Отчество</label>
                <input type="text" name="patronomyc" class="form-control" value="<?= $user['Patronomyc'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Телефон</label>
                <input type="text" name="phone" class="form-control" value="<?= $user['Phone'] ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Новый пароль</label>
                <input type="password" name="password" class="form-control" placeholder="Оставьте пустым, если не хотите менять">
            </div>
            <button type="submit" class="btn btn-accent">Сохранить изменения</button>
        </form>
    </div>
</body>
</html>