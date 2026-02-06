<?php
    session_start();
    include("../settings/connect_database.php");
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == -1) {
        header("Location: ../index.php");
        exit;
    }
    
    $dishesSelected = $_POST['dishes'] ?? [];
    $dishesSelected = array_filter($dishesSelected, fn($q) => $q > 0);
    
    if (empty($dishesSelected)) {
        header("Location: client.php");
        exit;
    }
    
    $ids = implode(',', array_keys($dishesSelected));
    $dishesQuery = $mysqli->query("SELECT * FROM Dishes WHERE Id IN ($ids)");
    
    $total = 0;
    $dishes = [];
    while ($d = $dishesQuery->fetch_assoc()) {
        $qty = $dishesSelected[$d['Id']];
        $sum = $qty * $d['Price'];
        $total += $sum;
        $dishes[] = [
            'data' => $d,
            'qty' => $qty,
            'sum' => $sum
        ];
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://api-maps.yandex.ru/2.1/?apikey=96123de1-75f1-4536-b4f0-c9e2efc923ba&lang=ru_RU"></script>

</head>
<body class="bg-light">

<div class="container mt-4">
    <h3 class="mb-3">Ваш заказ</h3>

    <form method="post" action="createOrder.php">

        <?php foreach ($dishes as $item): ?>
            <div class="card mb-2">
                <div class="card-body d-flex justify-content-between">
                    <div>
                        <strong><?= $item['data']['Name'] ?></strong><br>
                        <?= $item['qty'] ?> × <?= $item['data']['Price'] ?> ₽
                    </div>
                    <div class="fw-bold"><?= $item['sum'] ?> ₽</div>
                </div>
            </div>

            <input type="hidden" name="dishes[<?= $item['data']['Id'] ?>]" value="<?= $item['qty'] ?>">
        <?php endforeach; ?>

        <!-- итог -->
        <div class="alert alert-secondary mt-3">
            <strong>Итого:</strong> <?= $total ?> ₽
        </div>

        <input type="hidden" name="total" value="<?= $total ?>">

        <div class="mb-3">
            <label class="form-label">Адрес доставки</label>
            <input type="text" name="address" id="address" class="form-control" required>
        </div>

        <div id="map" style="width:100%; height:400px;" class="mb-3"></div>

        <button class="btn btn-danger btn-lg w-100">Подтвердить заказ</button>
    </form>
</div>

<script>
ymaps.ready(() => {
    const map = new ymaps.Map("map", {
        center: [55.75, 37.61],
        zoom: 12
    });

    let placemark;

    map.events.add('click', e => {
        const coords = e.get('coords');

        if (!placemark) {
            placemark = new ymaps.Placemark(coords, {}, { draggable: true });
            map.geoObjects.add(placemark);
        } else {
            placemark.geometry.setCoordinates(coords);
        }

        ymaps.geocode(coords).then(res => {
            const address = res.geoObjects.get(0).getAddressLine();
            document.getElementById('address').value = address;
        });
    });
});
</script>

</body>
</html>
