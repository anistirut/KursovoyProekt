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

    $username = $read["Name"]. ' '. $read["Surname"];
    $dishesQuery = $mysqli->query("SELECT * FROM `Dishes` ORDER BY `Name` ASC");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Меню ресторана</title>
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
        .card:hover {
            transform: translateY(-5px); 
        }
        .card-img-top { 
            height: 180px; object-fit: cover; border-top-left-radius:12px; border-top-right-radius:12px; 
        }
        .quantity-input { 
            width: 70px; 
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Меню ресторана</a>
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
    <div class="container">
        <form method="post" action="checkout.php">
            <div class="row g-4">
                <?php while($dish = $dishesQuery->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <img src="../resources/img/<?= $dish['Img'] ?>" class="card-img-top" alt="<?= $dish['Name'] ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= $dish['Name'] ?></h5>
                                <p class="card-text"><?= $dish['Сompound'] ?></p>
                                <p class="card-text fw-bold"><?= $dish['Price'] ?> ₽</p>
                                <div class="mt-auto d-flex gap-2 align-items-center">
                                    <input type="number" min="0" value="0" name="dishes[<?= $dish['Id'] ?>]" class="form-control form-control-sm quantity-input" data-id="<?= $dish['Id'] ?>" data-price="<?= $dish['Price'] ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="mt-4 text-center">
                <button type="submit" class="btn btn-accent btn-lg">Оформить заказ</button>
            </div>
        </form>
    </div>
</body>
</html>