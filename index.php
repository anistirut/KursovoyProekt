<?php
    session_start();
    include("./settings/connect_database.php");

    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $phone = preg_replace('/\D+/', '', $_POST['phone']);
        $password = $_POST['password'];

        if (empty($phone) || empty($password)) {
            $errors[] = "Пожалуйста, заполните все поля.";
        } else {
            $stmt = $mysqli->prepare("SELECT Id, Password, Role FROM Users WHERE Phone = ?");
            $stmt->bind_param("s", $phone);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['Password'])) {
                    $_SESSION['user'] = $user['Id'];

                    switch ($user['Role']) {
                        case 'client':
                            header("Location: client/client.php");
                            exit;
                        case 'courier':
                            header("Location: courier/courier.php");
                            exit;
                        case 'admin':
                            header("Location: admin/admin.php");
                            exit;
                        default:
                            $errors[] = "Неизвестная роль пользователя.";
                    }
                } else {
                    $errors[] = "Неверный пароль.";
                }
            } else {
                $errors[] = "Пользователь с таким номером телефона не найден.";
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
                <label for="phone" class="form-label">Номер телефона</label>
                <input 
                    type="tel"
                    class="form-control"
                    id="phone"
                    name="phone"
                    placeholder="+7 (___) ___-__-__"
                    pattern="^\+?[0-9\s\-\(\)]{10,20}$"
                    required
                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
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
<script>
    document.getElementById('phone').addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9+\-\(\)\s]/g, '');
    });
</script>