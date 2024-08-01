<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Kullanıcı bilgilerini kullanarak içeriği gösterin
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$address = $_SESSION['address'];
$phone = $_SESSION['phone'];
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
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
        <link href="css/styles.css" rel="stylesheet" />
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
                        <li class="nav-item px-lg-4"><a class="nav-link text-uppercase" href="profilee.php">Profil</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container mx-auto py-8">
            <h1 class="text-3xl text-center font-bold mb-6 text-white">Kullanıcı Profili</h1>
            <div class="max-w-md mx-auto bg-white rounded-lg overflow-hidden shadow-lg">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="ml-6">
                            <h2 class="text-xl font-bold text-gray-800"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                            <p class="text-gray-600"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                        </div>
                    </div>
                    <div class="border-t border-gray-200 mt-6 pt-4">
                        <p class="text-gray-700"><strong>Adres:</strong> <?php echo htmlspecialchars($_SESSION['address']); ?></p>
                        <p class="text-gray-700"><strong>Telefon Numarası:</strong> <?php echo htmlspecialchars($_SESSION['phone']); ?></p>
                    </div>
                </div>
                <div class="p-4 bg-gray-100 text-center">
                    <a href="update_profile.html" class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg font-semibold hover:bg-blue-600 transition duration-200">Bilgileri Güncelle</a>
                </div>
            </div>
        </div>
        

        <footer class="footer text-faded text-center py-5">
            <div class="container"><p class="m-0 small">Copyright &copy; Your Website 2024</p></div>
        </footer>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="js/scripts.js"></script>
    </body>
</html>
