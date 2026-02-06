<?php
    session_start();
    include("./settings/connect_database.php");
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $surname = trim($_POST["surname"]);
        $name = trim($_POST["name"]);
        $patronomyc = trim($_POST["patronomyc"]);
        $phone = preg_replace('/\D+/', '', $_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($surname) || empty($name) || empty($patronomyc)) {
            $errors[] = "Пожалуйста, заполните все поля ФИО.";
        }
        if (strlen($phone) < 10) {
            $errors[] = "Введите корректный номер телефона.";
        }
        if (strlen($password) < 6) {
            $errors[] = "Пароль должен быть не менее 6 символов.";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Пароли не совпадают.";
        }

        $query = $mysqli->query("SELECT * FROM `Users` WHERE `Phone`='".$phone."'");
        if ($query && $query->num_rows > 0) {
            $errors[] = "Пользователь с таким номером телефона уже зарегистрирован.";
        }

        if (empty($errors)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $mysqli->prepare("INSERT INTO `Users`(`Surname`, `Name`, `Patronomyc`, `Phone`, `Password`, `Role`) VALUES (?, ?, ?, ?, ?, 'client')");
            $stmt->bind_param("sssss", $surname, $name, $patronomyc, $phone, $passwordHash);
            if ($stmt->execute()) {
                $_SESSION['user'] = $stmt->insert_id;
                header("Location: client/client.php");
                exit;
            } else {
                $errors[] = "Ошибка регистрации: " . $mysqli->error;
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница регистрации</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="resources/css/style.css">
</head>
<body>
    <div class="card register-card shadow-sm">
    <div class="card-body p-4">
        <h3 class="text-center mb-4 login-title">Регистрация</h3>
        <p class="text-center text-muted mb-4">Онлайн-ресторан турецкой кухни</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="mb-3">
                <label for="surname" class="form-label">Фамилия</label>
                <input type="text" class="form-control" id="surname" name="surname" required>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Имя</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
                <label for="patronomyc" class="form-label">Отчество</label>
                <input type="text" class="form-control" id="patronomyc" name="patronomyc" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Номер телефона</label>
                <input 
                    type="tel"
                    class="form-control"
                    id="phone"
                    name="phone"
                    placeholder="+7 (___) ___-__-__"
                    required
                >
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label">Подтвердите пароль</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-danger btn-lg">Зарегистрироваться</button>
            </div>
        </form>

        <div class="text-center">
            <a href="index.php" class="login-link">Уже зарегистрированы?</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<script>
    document.getElementById('phone').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9+\-\(\)\s]/g, '');
    });
</script>