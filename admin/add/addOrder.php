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

    $username = $read["Name"]. ' '. $read["Surname"];
    $clients = $mysqli->query("SELECT `Id`, `Name`, `Surname` FROM `Users` WHERE `Role`='client'");
    $waiters = $mysqli->query("SELECT `Id`, `Name`, `Surname` FROM `Users` WHERE `Role`='waiter'");
    $dishes = $mysqli->query("SELECT `Id`, `Name`, `Price` FROM `Dishes`");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $client = $_POST['client'];
        $waiter = $_POST['waiter'];
        $status = $_POST['status'];

        $dishesSelected = $_POST['dishes'] ?? [];
        $mysqli->query("INSERT INTO `Orders` (`IdClient`, `IdWaiter`, `TotalSum`, `Status`) VALUES ({$client}, {$waiter}, 0, '{$status}')");

        $orderId = $mysqli->insert_id;
        $total = 0;

        foreach ($dishesSelected as $dishId => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) continue;

            $dish = $mysqli->query("SELECT Price FROM Dishes WHERE Id=$dishId")->fetch_assoc();
            $total += $dish['Price'] * $qty;

            $mysqli->query("INSERT INTO `OrdersDishes` (`IdOrder`, `IdDishes`, `Quantity`) VALUES ({$orderId}, {$dishId}, {$qty})");
        }
        $mysqli->query("UPDATE `Orders` SET `TotalSum`={$total} WHERE `Id`={$orderId}");

        header("Location: ../orders.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница добавления заказа</title>
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
        <h3>Добавить новый заказ</h3>

        <form method="post">
        <div class="mb-3">
            <label>Клиент</label>
            <select name="client" class="form-select" required>
                <?php while($c=$clients->fetch_assoc()): ?>
                    <option value="<?= $c['Id'] ?>">
                        <?= $c['Surname'].' '.$c['Name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Официант</label>
            <select name="waiter" class="form-select" required>
                <?php while($w=$waiters->fetch_assoc()): ?>
                    <option value="<?= $w['Id'] ?>">
                        <?= $w['Surname'].' '.$w['Name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <label class="form-label">Блюда</label>
        <?php while($d = $dishes->fetch_assoc()): ?>
            <div class="mb-2">
                <span><?= $d['Name'] ?> (<?= $d['Price'] ?> ₽)</span>
                <input type="number" name="dishes[<?= $d['Id'] ?>]" min="0" value="0" data-price="<?= $d['Price'] ?>" class="form-control dish-qty" style="width:80px; display:inline-block; margin-left:10px;">
            </div>
        <?php endwhile; ?>

        <div class="mb-3">
            <label>Итого</label>
            <input type="text" id="total" class="form-control" readonly value="0 ₽">
        </div>

        <div class="mb-3">
            <label>Статус</label>
            <select name="status" class="form-select">
                <option value="accepted">Принят</option>
                <option value="progress">Готовится</option>
                <option value="ready">Готов</option>
            </select>
        </div>

        <button class="btn btn-danger">Добавить</button>
        </form>
    </div>
</body>
</html>
<script>
    const qtyInputs = document.querySelectorAll('.dish-qty');
    const totalInput = document.getElementById('total');

    function calc() {
        let sum = 0;
        qtyInputs.forEach(input => {
            const price = parseFloat(input.dataset.price);
            const qty = parseInt(input.value) || 0;
            sum += price * qty;
        });
        totalInput.value = sum.toFixed(2) + ' ₽';
    }

    qtyInputs.forEach(i => i.addEventListener('input', calc));
    calc();
</script>