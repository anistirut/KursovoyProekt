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
    $dishToEdit = $_GET["id"];
    $query = $mysqli->query("SELECT * FROM `Dishes` WHERE `Id` =".$dishToEdit);
    $dish = $query->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $compound = trim($_POST['compound']);
        $price = (float)$_POST['price'];
        $imgPath = $dish['Img'];

        if (!empty($_FILES['img']['name'])) {
            $uploadDir = '../../resources/img/';
            $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
            $newName = uniqid('dish_') . '.' . $ext;
            $fullPath = $uploadDir . $newName;

            move_uploaded_file($_FILES['img']['tmp_name'], $fullPath);

            $imgPath = $newName;
        }

        $mysqli->query("UPDATE `Dishes` SET `Name`='{$name}',`Сompound`='{$compound}',`Price`={$price},`Img`='$imgPath' WHERE `Id`= ".$dishToEdit);
        header("Location: ../dishes.php");
        exit;
    }

    
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница изменения блюда</title>
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
    <h3>Редактировать блюдо</h3>

    <form method="post" enctype="multipart/form-data" class="mt-3">
        <div class="mb-3">
            <label for="name" class="form-label">Название</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= $dish['Name'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="compound" class="form-label">Состав</label>
            <input type="text" class="form-control" id="compound" name="compound" value="<?= $dish['Сompound'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Цена</label>
            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?= $dish['Price'] ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Текущее изображение</label><br>
            <img src="../../resources/img/<?= $dish['Img'] ?>" style="width:120px; border-radius:10px;">
        </div>

        <div class="mb-3">
            <label for="img" class="form-label">Новое изображение</label>
            <input type="file" class="form-control" id="img" name="img" accept="image/*">
            <small class="text-muted">Оставьте пустым, если не хотите менять изображение</small>
        </div>
        <button type="submit" class="btn btn-accent">Сохранить изменения</button>
    </form>
</div>
</body>
</html>