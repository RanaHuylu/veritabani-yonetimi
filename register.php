<?php
session_start();

$serverName = "DESKTOP-KO6819R";  // MSSQL sunucu adı veya IP adresi
$connectionOptions = array(
    "Database" => "agu",       // Veritabanı adı
    "UID" => "",                   // Veritabanı kullanıcı adı (gerekiyorsa doldurun)
    "PWD" => "",                   // Veritabanı şifresi (gerekiyorsa doldurun)
    "ConnectionPooling" => 0       // Bağlantı havuzlamasını devre dışı bırakmak için
);

// MSSQL bağlantısını oluşturma
$conn = sqlsrv_connect($serverName, $connectionOptions);

// Bağlantıyı kontrol etme
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    // SQL Stored Procedure çağrısını hazırlayın
    $sql = "{CALL EkleUser(?, ?, ?, ?, ?)}";
    $params = array(
        array($username, SQLSRV_PARAM_IN),
        array($email, SQLSRV_PARAM_IN),
        array($address, SQLSRV_PARAM_IN),
        array($phone, SQLSRV_PARAM_IN),
        array($password, SQLSRV_PARAM_IN)
    );
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Kullanıcı ekleme başarılı
    echo "Kullanıcı başarıyla eklendi.";
}

sqlsrv_close($conn);
?>
