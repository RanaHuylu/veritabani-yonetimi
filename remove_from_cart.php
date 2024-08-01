<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart_id'])) {
    $cartID = $_POST['cart_id'];

    $serverName = "DESKTOP-KO6819R";  // MSSQL sunucu adı veya IP adresi
    $connectionOptions = array(
        "Database" => "agu",  // Veritabanı adı
        "UID" => "",                    // Boş bırakın veya null
        "PWD" => "",                    // Boş bırakın veya null
        "ConnectionPooling" => 0        // Bağlantı havuzlamasını devre dışı bırakmak için
    );

    // MSSQL bağlantısını oluşturma
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    // Bağlantıyı kontrol etme
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $sql = "{CALL RemoveFromCart(?)}";
    $params = array($cartID);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_close($conn);

    header("Location: cart.php");
    exit();
} else {
    header("Location: cart.php");
    exit();
}
