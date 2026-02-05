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
    <title>Страница авторизации</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="resources/css/style.css">
</head>
<body>
    <div class="card shadow-lg login-card">
    <div class="card-body p-4">
        <h3 class="text-center mb-4 login-title">Авторизация</h3>
        <p class="text-center text-muted mb-4">Онлайн-ресторан турецкой кухни</p>

        <form method="post" action="functions/login.php">
            <div class="mb-3">
                <label for="phone" class="form-label">Номер телефона</label>
                <input 
                    type="tel"
                    class="form-control"
                    id="phone"
                    name="phone"
                    placeholder="+7 (___) ___-__-__"
                    pattern="^\+?[0-9\s\-\(\)]{10,20}$"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-danger btn-lg">Войти</button>
            </div>
        </form>
        <div class="text-center">
            <a href="regin.php" class="register-link">
                Ещё не зарегистрированы?
            </a>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>