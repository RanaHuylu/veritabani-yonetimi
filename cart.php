<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// MSSQL connection details
$serverName = "DESKTOP-KO6819R"; // Update with your MSSQL server
$connectionOptions = array(
    "Database" => "agu", // Update with your database name
    "UID" => "", // Update with your MSSQL username
    "PWD" => "", // Update with your MSSQL password
    "ConnectionPooling" => 0 // Disable connection pooling
);

// Establish MSSQL connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

// Check connection
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Get user ID from session
$userID = $_SESSION['user_id'];

// Fetch cart items using stored procedure
$sql = "{CALL GetCartItems(?)}";
$params = array($userID);
$stmt = sqlsrv_query($conn, $sql, $params);

// Error handling for fetching cart items
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Initialize variables
$cartItems = [];
$totalPrice = 0;
$discount = 0;
$discountMessage = '';

// Process fetched cart items
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $cartItems[] = $row;
    $totalPrice += $row['TotalAmount'];
}

// Kupon kodu uygulama işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $couponCode = $_POST['coupon_code'];

    // Kuponu kontrol ve uygulama SQL sorgusu
    $couponSql = "SELECT Discount FROM Coupons 
                  WHERE CouponCode = ? 
                  AND StartDate <= GETDATE() 
                  AND EndDate >= GETDATE()";
    $couponParams = array($couponCode);
    $couponStmt = sqlsrv_query($conn, $couponSql, $couponParams);

    // Kupon sorgusunun başarıyla çalışıp çalışmadığını kontrol et
    if ($couponStmt !== false && sqlsrv_has_rows($couponStmt)) {
        $coupon = sqlsrv_fetch_array($couponStmt, SQLSRV_FETCH_ASSOC);
        $discount = $totalPrice * ($coupon['Discount'] / 100); // İndirim hesaplanır
        $discountMessage = "Kupon kodu uygulandı. İndirim: $discount TL";
        $totalPrice -= $discount; // Toplam tutardan indirim düşülür
        
        // Update Cart table with coupon discount and code
        $updateCartSql = "UPDATE Cart SET CouponDiscount = ?, CouponCode = ? WHERE UserID = ?";
        $updateCartParams = array($discount, $couponCode, $userID);
        $updateCartStmt = sqlsrv_query($conn, $updateCartSql, $updateCartParams);
    
        // Error handling for update operation
        if ($updateCartStmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    
        // Calculate updated TotalAmount after applying coupon discount
        $totalAmountAfterDiscount = $totalPrice;
    
        // Update Cart table with updated TotalAmount
        $updateTotalAmountSql = "UPDATE Cart SET TotalAmount = ? WHERE UserID = ?";
        $updateTotalAmountParams = array($totalAmountAfterDiscount, $userID);
        $updateTotalAmountStmt = sqlsrv_query($conn, $updateTotalAmountSql, $updateTotalAmountParams);
    
        // Error handling for update operation
        if ($updateTotalAmountStmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }
     else {
        $discountMessage = "Geçersiz veya süresi dolmuş kupon kodu."; // Hatalı kupon mesajı
    }
}


    // Sipariş tablosuna kupon indirimini eklemek için SQL sorgusu
    $updateOrderSql = "UPDATE Orders SET DiscountAmount = DiscountAmount + ? WHERE OrderID = ?";
    $updateOrderParams = array($discount, $orderID); // $orderID: Güncellenecek siparişin ID'si
    $updateOrderStmt = sqlsrv_query($conn, $updateOrderSql, $updateOrderParams);

    // SQL sorgusunun başarılı bir şekilde çalışıp çalışmadığını kontrol et
    if ($updateOrderStmt === false) {
        die(print_r(sqlsrv_errors(), true)); // Hata varsa hata mesajı gösterilir.
    }



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $cartItemID = $_POST['remove_item'];

    // SQL query to remove item from cart
    $removeCartItemSql = "EXEC RemoveFromCart @CartItemID = ?";
    $removeCartItemParams = array($cartItemID);
    $removeCartItemStmt = sqlsrv_query($conn, $removeCartItemSql, $removeCartItemParams);

    // Check if removal query executed successfully
    if ($removeCartItemStmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
}

// Confirm order functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // Insert order into Orders table
    $insertOrderSql = "INSERT INTO Orders (UserID, TotalAmount, DiscountAmount, CouponAmount) VALUES (?, ?, ?, ?)";
    $orderParams = array($userID, $totalPrice, $discount, $discount);
    $orderStmt = sqlsrv_query($conn, $insertOrderSql, $orderParams);

    // Check if order insertion was successful
    if ($orderStmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Get the newly inserted OrderID
    $orderIDSql = "SELECT SCOPE_IDENTITY() AS OrderID";
    $orderIDStmt = sqlsrv_query($conn, $orderIDSql);
    $orderID = sqlsrv_fetch_array($orderIDStmt, SQLSRV_FETCH_ASSOC)['OrderID'];

    // Clear user's cart after order confirmation
    $clearCartSql = "DELETE FROM Cart WHERE UserID = ?";
    $clearCartParams = array($userID);
    $clearCartStmt = sqlsrv_query($conn, $clearCartSql, $clearCartParams);

    // Check if cart clearing was successful
    if ($clearCartStmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Set success message for order confirmation
    $orderConfirmationMessage = "Siparişiniz başarıyla oluşturuldu!";
}


// Close MSSQL connection
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim</title>
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .total-price {
            text-align: right;
            font-size: 1.5em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <h1 class="site-heading text-center text-faded d-none d-lg-block">
            <span class="site-heading-upper text-primary mb-3">KAHU</span>
            <span class="site-heading-lower">KAHVE</span>
        </h1>
    </header>
    <nav class="navbar navbar-expand-lg navbar-dark py-lg-4" id="mainNav">
        <div class="container">
            <a class="navbar-brand text-uppercase fw-bold d-lg-none" href="index.html"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item px-lg-4"><a class="nav-link text-uppercase" href="index.html">AnaSayfa</a></li>
                    <li class="nav-item px-lg-4"><a class="nav-link text-uppercase" href="about.html">Hakkımızda</a></li>
                    <li class="nav-item px-lg-4"><a class="nav-link text-uppercase" href="products.php">Kahveler</a></li>
                    <li class="nav-item px-lg-4"><a class="nav-link text-uppercase" href="store.html">İletişim</a></li>
                    <li class="nav-item px-lg-4"><a class="nav-link text-uppercase" href="cart.php">Sepetim</a></li>
                    <li class="nav-item px-lg-4"><a href="logout.php" class="btn btn-primary">Çıkış Yap</a></li>
                    <li class="nav-item px-lg-4"><a class="nav-link text-uppercase" href="profilee.php">Profil</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <section class="page-section">
        <div class="container bg-white">
            <h2 class="section-heading text-center">Sepetim</h2>
            <div class="cart-items">
                <?php if (count($cartItems) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ürün Adı</th>
                                <th>Adet</th>
                                <th>Fiyat</th>
                                <th>Toplam Tutar</th>
                                <th>Görsel</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                                    <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($item['Price']); ?> TL</td>
                                    <td><?php echo htmlspecialchars($item['TotalAmount']); ?> TL</td>
                                    <td><img src="assets/img/<?php echo htmlspecialchars($item['Image']); ?>" alt="<?php echo htmlspecialchars($item['ProductName']); ?>" style="width: 50px;"></td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="remove_item" value="<?php echo htmlspecialchars($item['CartID']); ?>">
                                            <button type="submit">Kaldır</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Sepetinizde ürün bulunmamaktadır.</p>
                <?php endif; ?>
            </div>
            <div class="total-price">
                <?php if (!empty($discountMessage)): ?>
                    <p><?php echo $discountMessage; ?></p>
                <?php endif; ?>
                <p>Toplam Tutar: <?php echo $totalPrice; ?> TL</p>
            </div>
            <form method="post">
                <label for="coupon_code">Kupon Kodu:</label>
                <input type="text" id="coupon_code" name="coupon_code">
                <button type="submit" name="apply_coupon">Kupon Uygula</button>
            </form>
            <p id="discount_message"></p>
            <div class="total-price">
                <p>Toplam Tutar: <span id="total_price"><?php echo $totalPrice; ?> TL</span></p>
            </div>

            <form method="post">
                <button type="submit" name="confirm_order">Siparişi Onayla</button>
            </form>

            <?php if (isset($orderConfirmationMessage)): ?>
                <p><?php echo $orderConfirmationMessage; ?></p>
            <?php endif; ?>
        </div>
    </section>
    <footer class="footer text-faded text-center py-5">
        <div class="container">
            <p>KAHU KAHVE</p>
        </div>
    </footer>
</body>
</html>
