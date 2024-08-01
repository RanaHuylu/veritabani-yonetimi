<?php

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

echo "MSSQL veritabanı bağlantısı başarıyla kuruldu.";

// Bağlantıyı kapatma
sqlsrv_close($conn);
?>
