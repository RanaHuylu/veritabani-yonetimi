<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

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
// Formdan gelen verileri alın
$email = $_POST['email'];
$address = $_POST['address'];
$phone = $_POST['phone'];
$password = $_POST['password'];
$user_id = $_SESSION['user_id']; // Kullanıcı ID'sini oturumdan alın veya başka bir güvenli yöntemle belirleyin

// SQL Stored Procedure çağrısını hazırlayın
$sql = "{CALL UpdateUserProfile(?, ?, ?, ?, ?)}";
$params = array(
    array($user_id, SQLSRV_PARAM_IN),
    array($email, SQLSRV_PARAM_IN),
    array($address, SQLSRV_PARAM_IN),
    array($phone, SQLSRV_PARAM_IN),
    array($password, SQLSRV_PARAM_IN)
);
$stmt = sqlsrv_query($conn, $sql, $params);

// Sorguyu kontrol etme
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
} else {
    echo "Kullanıcı bilgileri başarıyla güncellendi!";
}

// Bağlantıyı kapatın
sqlsrv_close($conn);
?>