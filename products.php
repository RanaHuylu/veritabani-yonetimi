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

$sql = "{CALL GetProducts()}";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$products = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $products[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>KAHU - KAHVE</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Lora:400,400i,700,700i" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .product-item {
            margin-bottom: 2rem;
        }

        .qty-btn {
            display: inline-flex;
            align-items: center;
        }

        .qty-btn button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            font-size: 16px;
            cursor: pointer;
        }

        .qty {
            margin: 0 8px;
            font-size: 16px;
        }

        .cart {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
        }
    </style>
    <script>
       document.addEventListener('DOMContentLoaded', function () {
        const addToCartButtons = document.querySelectorAll('.add-to-cart');

         addToCartButtons.forEach(button => {
        button.addEventListener('click', function () {
            const userID = <?php echo $_SESSION['user_id']; ?>;
            const productID = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            const price = parseFloat(this.getAttribute('data-product-price'));
            const image = this.getAttribute('data-product-image');
            const quantityInput = this.closest('.qty-btn').querySelector('#quantity');
            const qty = quantityInput ? parseInt(quantityInput.value) : 1; // Default quantity is 1 if not found
            const totalAmount = price * qty;
            const couponInput = document.querySelector('#coupon_code'); // Assuming you have an input with id 'coupon_code'
            const couponCode = couponInput ? couponInput.value : '';

            const formData = new FormData();
            formData.append('user_id', userID);
            formData.append('product_id', productID);
            formData.append('product_name', productName);
            formData.append('price', price);
            formData.append('image', image);
            formData.append('quantity', qty);
            formData.append('total_amount', totalAmount);
            formData.append('coupon_discount', 0); // Default to 0, update if you calculate discount
            formData.append('coupon_code', couponCode); // Add coupon code to FormData

            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim() === 'success') {
                    alert('Ürün sepete eklendi.');
                } else {
                    console.log('Sepete ekleme başarısız:', data);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
            });
        });
    });
});
    </script>
</head>
<body>
<header>
    <h1 class="site-heading text-center text-faded d-none d-lg-block">
        <span class="site-heading-upper text-primary mb-3">KAHU</span>
        <span class="site-heading-lower">KAHVE</span>
    </h1>
</header>
<!-- Navigation-->
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
    <div class="container">
        <div class="product-item">
            <div class="product-item-title d-flex">
                <div class="bg-faded p-5 d-flex ms-auto rounded">
                    <h2 class="section-heading mb-0">
                        <span class="section-heading-lower">Kahvelerimiz</span>
                    </h2>
                </div>
            </div>
            <img class="product-item-img mx-auto d-flex rounded img-fluid mb-3 mb-lg-0" src="assets/img/products-01.jpg" alt="..." />
            <div class="product-item-description d-flex me-auto">
                <div class="bg-faded p-5 rounded"><p class="mb-0">İşimizle gurur duyuyoruz ve bu her zaman ortaya çıkıyor. Bize herhangi bir içecek siparişi verdiğinizde, size yaşanmaya değer bir deneyim garanti ediyoruz. Dünyaca ünlü Venezuela Usulü Cappuccino'muz, ferahlatıcı buzlu bitki çayımız veya özel kaynaklardan temin edilen basit bir siyah kahve kupası olsun, her zaman daha fazlası için geri geleceksiniz.</p></div>
            </div>
        </div>
    </div>
</section>

<section class="text-gray-600 body-font">
    <div class="container px-5 py-24 mx-auto">
        <div class="flex flex-wrap -m-4">
            <?php foreach ($products as $product): ?>
                <div class="xl:w-1/4 md:w-1/2 p-4">
                    <div class="bg-gray-100 p-6 rounded-lg">
                        <img src="assets/img/<?php echo isset($product['Image']) ? htmlspecialchars($product['Image']) : 'default.jpg'; ?>" alt="<?php echo isset($product['Name']) ? htmlspecialchars($product['Name']) : 'Ürün adı yok'; ?>" class="w-full h-64 object-cover object-center mb-6 rounded-lg">
                        <h3 class="tracking-widest text-xs title-font font-medium text-gray-500 mb-1"><?php echo isset($product['Name']) ? htmlspecialchars($product['Name']) : 'Ürün adı yok'; ?></h3>
                        <h2 class="text-lg text-gray-900 font-medium title-font mb-4"><?php echo isset($product['Name']) ? htmlspecialchars($product['Name']) : 'Ürün adı yok'; ?></h2>
                        <p class="mt-1"><?php echo isset($product['Price']) ? number_format($product['Price'], 2) . ' ₺' : '0.00 ₺'; ?></p>
                        <div class="qty-btn">
                            <button class="add-to-cart"
                                    data-product-id="<?php echo htmlspecialchars($product['ProductID']); ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['Name']); ?>"
                                    data-product-price="<?php echo htmlspecialchars($product['Price']); ?>"
                                    data-product-image="<?php echo htmlspecialchars($product['Image']); ?>">
                                Sepete Ekle
                            </button>
                            <div class="qty ml-4">
                                <label for="quantity">Adet:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="10">
                            </div>
                        </div>
                        <p class="message"></p> <!-- Message container for success message -->
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<footer class="footer text-faded text-center py-5">
    <div class="container"><p class="m-0 small">KAHU KAHVE</p></div>
</footer>
<!-- Bootstrap core JS-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Core theme JS-->
<script src="js/scripts.js"></script>
</body>
</html>
