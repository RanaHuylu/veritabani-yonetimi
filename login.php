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
    $password = $_POST['password'];

    // SQL Stored Procedure çağrısını hazırlayın
    $sql = "{CALL LoginUser(?, ?)}";
    $params = array(
        array($username, SQLSRV_PARAM_IN),
        array($password, SQLSRV_PARAM_IN)
    );
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Kullanıcıyı alın
    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    // Kullanıcı varsa oturum başlat
    if ($user) {
        $_SESSION['user_id'] = $user['UserID'];    // Veritabanındaki sütun adını doğru olarak kontrol edin (örneğin UserID veya user_id)
        $_SESSION['username'] = $user['UserName']; // Veritabanındaki sütun adını doğru olarak kontrol edin (örneğin UserName veya username)
        $_SESSION['email'] = $user['Email'];       // Veritabanındaki sütun adını doğru olarak kontrol edin (örneğin Email veya email)
        $_SESSION['address'] = $user['Address'];   // Veritabanındaki sütun adını doğru olarak kontrol edin (örneğin Address veya address)
        $_SESSION['phone'] = $user['Phone'];       // Veritabanındaki sütun adını doğru olarak kontrol edin (örneğin Phone veya phone)

        header("Location: index.html");
        exit(); // Header yönlendirmesinden sonra kodun çalışmasını durdurun
    } else {
        echo "Geçersiz kullanıcı adı veya şifre.";
    }
}

sqlsrv_close($conn);
?>
