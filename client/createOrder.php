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
    $userQuery = $mysqli->query("SELECT * FROM `Users` WHERE `Id` = {$userId}");
    $client = $userQuery->fetch_assoc();
    $username = $read["Name"]. ' '. $read["Surname"];
    $dishesQuery = $mysqli->query("SELECT * FROM `Dishes` ORDER BY `Name` ASC");
    $selected = $_POST['dishes'] ?? [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($selected)) {
        if (!empty($selected)) {
            $total = 0;

            $courierQuery = $mysqli->query("SELECT u.Id, COUNT(o.Id) AS orders_count
                FROM Users u
                LEFT JOIN Orders o ON o.IdCourier = u.Id
                WHERE u.Role = 'courier'
                GROUP BY u.Id
                ORDER BY orders_count ASC
                LIMIT 1");
            $courier = $courierQuery->fetch_assoc();
            $courierId = $courier['Id'] ?? 0;
            $address = $_POST['address'] ?? '';

            $mysqli->query("INSERT INTO `Orders` (`IdClient`, `IdCourier`, `TotalSum`, `Address`, `Status`) VALUES ({$client['Id']}, {$courierId}, 0, '{$address}', 'accepted')");
            $orderId = $mysqli->insert_id;

            foreach ($selected as $dishId => $qty) {
                $qty = (int)$qty;
                if ($qty <= 0) continue;

                $dish = $mysqli->query("SELECT `Price` FROM `Dishes` WHERE `Id`={$dishId}")->fetch_assoc();
                $total += $dish['Price'] * $qty;

                $mysqli->query("INSERT INTO `OrdersDishes` (`IdOrder`, `IdDishes`, `Quantity`) VALUES ({$orderId}, {$dishId}, {$qty})");
            }

            $mysqli->query("UPDATE `Orders` SET `TotalSum`={$total} WHERE `Id`={$orderId}");
            header("Location: client.php");
            exit;
        }
        $chosenDishes = [];
        if (!empty($selected)) {
            $ids = implode(',', array_map('intval', array_keys($selected)));
            $dishQuery = $mysqli->query("SELECT `Id`, `Name`, `Price` FROM `Dishes` WHERE `Id` = {$ids}");
            while ($d = $dishQuery->fetch_assoc()) {
                $d['Quantity'] = $selected[$d['Id']];
                $d['Total'] = $d['Price'] * $d['Quantity'];
                $chosenDishes[] = $d;
            }
        }

        $totalSum = array_sum(array_column($chosenDishes, 'Total'));
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа</title>
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
        .quantity-input { 
            width: 70px; 
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
            </div>
        </div>
    </nav>
    <div class="container">
        <h3 class="mb-4">Оформление заказа</h3>
        <?php if (empty($chosenDishes)): ?>
        <p>Вы не выбрали ни одного блюда. <a href="client.php">Вернуться к меню</a></p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Блюдо</th>
                        <th>Количество</th>
                        <th>Цена за единицу</th>
                        <th>Итого</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chosenDishes as $dish): ?>
                        <tr>
                            <td><?= htmlspecialchars($dish['Name']) ?></td>
                            <td><?= $dish['Quantity'] ?></td>
                            <td><?= number_format($dish['Price'], 2) ?> ₽</td>
                            <td><?= number_format($dish['Total'], 2) ?> ₽</td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Общая сумма:</strong></td>
                        <td><strong><?= number_format($totalSum, 2) ?> ₽</strong></td>
                    </tr>
                </tbody>
            </table>
            <form method="post">
                <?php foreach ($selected as $dishId => $qty): ?>
                    <input type="hidden" name="dishes[<?= $dishId ?>]" value="<?= $qty ?>">
                <?php endforeach; ?>
                <button class="btn btn-success">Подтвердить заказ</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

<script>
    const qtyInputs = document.querySelectorAll('.quantity-input');
    const totalSpan = document.getElementById('total-sum');

    function calc() {
        let total = 0;
        qtyInputs.forEach(input => {
            const price = parseFloat(input.dataset.price);
            const qty = parseInt(input.value) || 0;
            total += price * qty;
        });
        totalSpan.innerText = total.toFixed(2);
    }

    qtyInputs.forEach(input => input.addEventListener('input', calc));
    calc();
</script>