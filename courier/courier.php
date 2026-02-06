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
                } elseif ($role == 'client') {
                    header("Location: ../client/client.php");
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
    $courierId = $_SESSION['user'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['orderId'], $_POST['status'])) {
        $orderId = $_POST['orderId'];
        $status = $_POST['status'];
        $mysqli->query("UPDATE `Orders` SET `Status`='{$status}' WHERE `Id`={$orderId} AND `IdCourier`={$courierId}");
        header("Location: courier.php");
        exit;
    }

    $ordersQuery = $mysqli->query("SELECT 
            o.Id, o.TotalSum, o.Address , o.Status,
            c.Name AS ClientName, c.Surname AS ClientSurname,
            GROUP_CONCAT(CONCAT(d.Name, ' (', od.Quantity, ')') SEPARATOR ', ') AS Dishes
        FROM Orders o
        JOIN Users c ON c.Id = o.IdClient
        LEFT JOIN OrdersDishes od ON od.IdOrder = o.Id
        LEFT JOIN Dishes d ON d.Id = od.IdDishes
        WHERE o.IdCourier = {$courierId}
        GROUP BY o.Id
        ORDER BY o.Id DESC");
?>  
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказы официанта</title>
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
            <a class="navbar-brand" href="courier.php">Заказы</a>
            <div class="collapse navbar-collapse" id="navbarWaiter">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                </ul>
                <span class="navbar-text me-3">Привет, <?= $username ?></span>
                <a href="../functions/logout.php" class="btn btn-outline-danger">Выйти</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h3>Мои заказы</h3>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
                        <th>Блюда</th>
                        <th>Сумма</th>
                        <th>Адрес</th>
                        <th>Статус</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($o = $ordersQuery->fetch_assoc()): ?>
                    <tr>
                        <td><?= $o['Id'] ?></td>
                        <td><?= $o['ClientSurname'].' '.$o['ClientName'] ?></td>
                        <td><?= $o['Dishes'] ?: '—' ?></td>
                        <td><?= $o['TotalSum'] ?> ₽</td>
                        <td><?= $o['Address'] ?></td>
                        <td><?= ucfirst($o['Status']) ?></td>
                        <td>
                            <form method="post" class="d-flex gap-1">
                                <input type="hidden" name="orderId" value="<?= $o['Id'] ?>">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="accepted" <?= $o['Status']=='accepted'?'selected':'' ?>>Принят</option>
                                    <option value="progress" <?= $o['Status']=='progress'?'selected':'' ?>>Готовится</option>
                                    <option value="ready" <?= $o['Status']=='ready'?'selected':'' ?>>Готов</option>
                                    <option value="delivery" <?= $o['Status']=='delivery'?'selected':'' ?>>В доставке</option>
                                    <option value="delivered" <?= $o['Status']=='delivered'?'selected':'' ?>>Доставлен</option>
                                </select>
                                <button class="btn btn-sm btn-primary">Сохранить</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>