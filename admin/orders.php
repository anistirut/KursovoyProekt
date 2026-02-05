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
    $ordersQuery = $mysqli->query("SELECT 
            o.Id, o.TotalSum, o.Status,
            c.Name AS ClientName, c.Surname AS ClientSurname,
            w.Name AS WaiterName, w.Surname AS WaiterSurname,
            GROUP_CONCAT(CONCAT(d.Name, ' (', od.Quantity, ')') SEPARATOR ', ') AS Dishes
        FROM Orders o
        JOIN Users c ON c.Id = o.IdClient
        JOIN Users w ON w.Id = o.IdWaiter
        LEFT JOIN OrdersDishes od ON od.IdOrder = o.Id
        LEFT JOIN Dishes d ON d.Id = od.IdDishes
        GROUP BY o.Id
        ORDER BY o.Id DESC;");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управления заказами</title>
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
            <h3>Заказы</h3>
            <a href="add/addOrder.php" class="btn btn-accent">Добавить заказ</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
                        <th>Официант</th>
                        <th>Блюда</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($o = $ordersQuery->fetch_assoc()): ?>
                        <tr>
                            <td><?= $o['Id'] ?></td>
                            <td><?= $o['ClientName'].' '.$o['ClientSurname'] ?></td>
                            <td><?= $o['WaiterName'].' '.$o['WaiterSurname'] ?></td>
                            <td><?= $o['Dishes'] ?: '—' ?></td>
                            <td><?= $o['TotalSum'] ?> ₽</td>
                            <td><?= $o['Status'] ?></td>
                            <td>
                                <a href="edit/editOrder.php?id=<?= $o['Id'] ?>" class="btn btn-sm btn-primary">Редактировать</a>
                                <a href="delete/deleteOrder.php?id=<?= $o['Id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Удалить этот заказ?')">Удалить</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>