<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serverName = "DESKTOP-KO6819R";  // MSSQL sunucu adı veya IP adresi
    $connectionOptions = array(
        "Database" => "agu",  // Veritabanı adı
        "UID" => "",                    // Boş bırakın veya null
        "PWD" => "",                    // Boş bırakın veya null
        "ConnectionPooling" => 0        // Bağlantı havuzlamasını devre dışı bırakmak için
    );

    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $userID = $_SESSION['user_id'];
    $productID = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $quantity = $_POST['quantity'];
    $totalAmount = $_POST['total_amount'];
    $couponDiscount = $_POST['coupon_discount'];
    $couponCode = $_POST['coupon_code']; 

    // Prepare the procedure call
    $sql = "{CALL SepeteEkle(?, ?, ?, ?, ?, ?, ?, ?, ?)}";
    $params = array(
        array($userID, SQLSRV_PARAM_IN),
        array($productID, SQLSRV_PARAM_IN),
        array($productName, SQLSRV_PARAM_IN),
        array($image, SQLSRV_PARAM_IN),
        array($quantity, SQLSRV_PARAM_IN),
        array($price, SQLSRV_PARAM_IN),
        array($totalAmount, SQLSRV_PARAM_IN),
        array($couponDiscount, SQLSRV_PARAM_IN),
        array($couponCode, SQLSRV_PARAM_IN)
    );

    // Execute the prepared statement
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if ($stmt === false) {
        echo 'fail';
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_execute($stmt)) {
        echo 'success';
    } else {
        echo 'fail';
        die(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_close($conn);
} else {
    echo 'Invalid request method';
}
?>
