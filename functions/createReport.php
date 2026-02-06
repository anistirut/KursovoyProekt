<?php
    session_start();
    require_once __DIR__ . '/../vendor/autoload.php';
    include("../settings/connect_database.php");
    
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    if (!isset($_SESSION['user']) || $_SESSION['user'] == -1) {
        header("Location: ../index.php");
        exit;
    }

    $check = $mysqli->query("SELECT `Role` FROM `Users` WHERE `Id`=".$_SESSION['user']);
    $user = $check->fetch_assoc();
    if ($user['Role'] !== 'admin') {
        exit('Доступ запрещён');
    }

    $spreadsheet = new Spreadsheet();

    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Пользователи');

    $sheet->fromArray(
        ['ID', 'Фамилия', 'Имя', 'Отчество', 'Телефон', 'Роль'],
        null,
        'A1'
    );

    $users = $mysqli->query("SELECT `Id`, `Surname`, `Name`, `Patronomyc`, `Phone`, `Role` FROM `Users`");
    $row = 2;
    while ($u = $users->fetch_assoc()) {
        $sheet->fromArray(array_values($u), null, "A{$row}");
        $row++;
    }

    foreach ($sheet->getColumnIterator() as $column) {
        $sheet->getColumnDimension($column->getColumnIndex())
              ->setAutoSize(true);
    }


    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle('Блюда');

    $sheet->fromArray(
        ['ID', 'Название', 'Состав', 'Цена'],
        null,
        'A1'
    );

    $dishes = $mysqli->query("SELECT `Id`, `Name`, `Сompound`, `Price` FROM `Dishes`");
    $row = 2;
    while ($d = $dishes->fetch_assoc()) {
        $sheet->fromArray(array_values($d), null, "A{$row}");
        $row++;
    }

    foreach ($sheet->getColumnIterator() as $column) {
        $sheet->getColumnDimension($column->getColumnIndex())
              ->setAutoSize(true);
    }

    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle('Заказы');

    $sheet->fromArray(
        ['ID', 'Клиент', 'Официант', 'Сумма', 'Статус'],
        null,
        'A1'
    );

    $orders = $mysqli->query("SELECT 
            o.Id,
            CONCAT(c.Surname,' ',c.Name) AS Client,
            CONCAT(w.Surname,' ',w.Name) AS Waiter,
            o.TotalSum,
            o.Status
        FROM Orders o
        JOIN Users c ON c.Id = o.IdClient
        JOIN Users w ON w.Id = o.IdWaiter");

    $row = 2;
    while ($o = $orders->fetch_assoc()) {
        $sheet->fromArray(array_values($o), null, "A{$row}");
        $row++;
    }

    foreach ($sheet->getColumnIterator() as $column) {
        $sheet->getColumnDimension($column->getColumnIndex())
              ->setAutoSize(true);
    }

    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle('Аналитика');

    $avg = $mysqli->query("SELECT AVG(TotalSum) AS avg_check FROM Orders")->fetch_assoc()['avg_check'];

    $popular = $mysqli->query("SELECT d.Name, SUM(od.Quantity) AS qty
        FROM OrdersDishes od
        JOIN Dishes d ON d.Id = od.IdDishes
        GROUP BY d.Id
        ORDER BY qty DESC
        LIMIT 1")->fetch_assoc();

    $countOrders = $mysqli->query("SELECT COUNT(*) AS cnt FROM Orders")->fetch_assoc()['cnt'];

    $sheet->setCellValue('A1', 'Показатель');
    $sheet->setCellValue('B1', 'Значение');

    $sheet->setCellValue('A2', 'Средний чек');
    $sheet->setCellValue('B2', round($avg, 2).' ₽');

    $sheet->setCellValue('A3', 'Самое популярное блюдо');
    $sheet->setCellValue('B3', $popular ? $popular['Name'].' ('.$popular['qty'].' шт.)' : '—');

    $sheet->setCellValue('A4', 'Всего заказов');
    $sheet->setCellValue('B4', $countOrders);

    foreach ($sheet->getColumnIterator() as $column) {
        $sheet->getColumnDimension($column->getColumnIndex())
              ->setAutoSize(true);
    }

    $filename = 'restaurant_report_'.date('Y-m-d_H-i').'.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
?>