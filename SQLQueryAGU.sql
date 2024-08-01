use agu
GO
CREATE TABLE Users (
    UserID INT PRIMARY KEY IDENTITY(1,1),
    UserName NVARCHAR(100) NOT NULL,
    Email NVARCHAR(100) NOT NULL,
    Address NVARCHAR(255),
    Phone NVARCHAR(50),
    Password NVARCHAR(255) NOT NULL
);
GO
CREATE TABLE Products (
    ProductID INT PRIMARY KEY IDENTITY(1,1),
    Name NVARCHAR(100) NOT NULL,
    Price DECIMAL(10, 2) NOT NULL,
    Image NVARCHAR(255) NOT NULL,
    Quantity INT NOT NULL DEFAULT 0
);
GO
CREATE TABLE Cart (
    CartID INT PRIMARY KEY IDENTITY(1,1),
    UserID INT NOT NULL,  
    ProductID INT NOT NULL,
    ProductName NVARCHAR(100) NOT NULL,
    Image NVARCHAR(255) NOT NULL,  
    Quantity INT NOT NULL,  
    Price DECIMAL(10, 2) NOT NULL,  
    TotalAmount DECIMAL(10, 2) NOT NULL,  
    CouponDiscount DECIMAL(10, 2),  
    IsConfirmed BIT DEFAULT 0,  
    CreatedAt DATETIME DEFAULT GETDATE(),  
    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (ProductID) REFERENCES Products(ProductID)
);
GO
CREATE TABLE Orders (
    OrderID INT PRIMARY KEY IDENTITY(1,1),
    UserID INT,
    TotalAmount DECIMAL(10, 2),
    DiscountAmount DECIMAL(10, 2) DEFAULT 0,
    OrderDate DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);
GO
CREATE TABLE Coupons (
    CouponID INT PRIMARY KEY IDENTITY(1,1),
    CouponCode NVARCHAR(50) NOT NULL,
    CouponName NVARCHAR(100) NOT NULL DEFAULT '',
    Discount DECIMAL(5, 2) NOT NULL,
    ExpirationDate DATETIME NOT NULL,
    UserID INT,
	StartDate DATETIME NOT NULL DEFAULT GETDATE(),
    EndDate DATETIME NOT NULL DEFAULT DATEADD(YEAR, 1, GETDATE()),
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);
GO
INSERT INTO Products (Name, Price, Image, Quantity)
VALUES ('Espresso', 80.00, '8.png', 0),
       ('Türk Kahvesi', 80.00, '9.png', 0),
       ('Latte', 80.00, '10.png', 0),
       ('Sýcak Çikolata', 80.00, '11.png', 0),
       ('Americano', 80.00, '12.png', 0),
       ('Cappuccino', 80.00, '5.png', 0),
       ('Filtre Kahve', 80.00, '3.png', 0),
       ('Sürpriz Seçim', 80.00, '2.png', 0);
GO
CREATE PROCEDURE AddUser
    @Username NVARCHAR(100),
    @Email NVARCHAR(100),
    @Address NVARCHAR(255),
    @Phone NVARCHAR(50),
    @Password NVARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO Users (UserName, Email, Password)
	VALUES (@Username, @Email, HASHBYTES('SHA2_512', @Password));
END;
GO
CREATE PROCEDURE ValidateUser
    @Username NVARCHAR(100),
    @Password NVARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;

    SELECT UserID, UserName, Email, Address, Phone
	FROM Users
	WHERE UserName = @Username 
	AND Password = HASHBYTES('SHA2_512', @Password);
END
GO
CREATE PROCEDURE LoginUser
    @Username NVARCHAR(100),
    @Password NVARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;

    -- Þifreyi SHA-512 ile hashleyin
    DECLARE @HashedPassword VARBINARY(64);
    SET @HashedPassword = HASHBYTES('SHA2_512', @Password);

    -- Kullanýcýyý doðrulayýn
    SELECT UserID, UserName, Email, Address, Phone
    FROM Users
    WHERE UserName = @Username 
      AND Password = @HashedPassword; -- Veritabanýnda þifre hash sütunu adý kontrol edilmeli
END;
GO
CREATE PROCEDURE EkleUser
    @Username NVARCHAR(100),
    @Email NVARCHAR(100),
    @Address NVARCHAR(255),
    @Phone NVARCHAR(50),
    @Password NVARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;

    -- Þifreyi SHA-512 ile hashleyin
    DECLARE @HashedPassword VARBINARY(64);
    SET @HashedPassword = HASHBYTES('SHA2_512', @Password);

    -- Kullanýcýyý ekle
    INSERT INTO Users (UserName, Email, Address, Phone, Password)
    VALUES (@Username, @Email, @Address, @Phone, @HashedPassword);
END;
GO
CREATE PROCEDURE UpdateUserProfile
    @UserID INT,
    @Email NVARCHAR(100),
    @Address NVARCHAR(255),
    @Phone NVARCHAR(50),
    @Password NVARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE Users
    SET Email = @Email,
        Address = @Address,
        Phone = @Phone,
        Password = @Password
    WHERE UserID = @UserID;
END;
GO
CREATE PROCEDURE AddProduct
    @Name NVARCHAR(100),
    @Price DECIMAL(10, 2),
    @Image NVARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;

    INSERT INTO Products (Name, Price, Image)
    VALUES (@Name, @Price, @Image);
END;
GO
CREATE PROCEDURE GetProducts
AS
BEGIN
    SELECT * FROM Products;
END;

GO
ALTER TABLE Cart
ADD CouponCode NVARCHAR(50) NULL

GO
CREATE PROCEDURE AddToCart
    @UserID INT,
    @ProductID INT,
    @ProductName NVARCHAR(100),
    @Image NVARCHAR(255),
    @Quantity INT,
    @Price DECIMAL(10, 2),
    @TotalAmount DECIMAL(10, 2),
    @CouponCode NVARCHAR(50) = NULL
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @CouponDiscount DECIMAL(10, 2) = 0;
    DECLARE @FinalTotal DECIMAL(10, 2) = @TotalAmount;

    BEGIN TRY
        -- Kupon varsa indirim uygulayýn
        IF @CouponCode IS NOT NULL
        BEGIN
            DECLARE @Discount DECIMAL(5, 2);
            DECLARE @CurrentDate DATETIME = GETDATE();

            SELECT @Discount = Discount
            FROM Coupons
            WHERE CouponCode = @CouponCode
              AND StartDate <= @CurrentDate
              AND EndDate >= @CurrentDate;

            IF @Discount IS NOT NULL
            BEGIN
                SET @CouponDiscount = @TotalAmount * @Discount / 100;
                SET @FinalTotal = @TotalAmount - @CouponDiscount;
            END
        END

        INSERT INTO Cart (UserID, ProductID, ProductName, Image, Quantity, Price, TotalAmount, CouponDiscount, IsConfirmed, CreatedAt)
        VALUES (@UserID, @ProductID, @ProductName, @Image, @Quantity, @Price, @FinalTotal, @CouponDiscount, 0, GETDATE());

        SELECT 'Ürün sepete baþarýyla eklendi.' AS Message;
    END TRY
    BEGIN CATCH
        SELECT ERROR_MESSAGE() AS ErrorMessage;
    END CATCH;
END;

GO
CREATE PROCEDURE SepeteEkle
    @UserID INT,
    @ProductID INT,
    @ProductName NVARCHAR(100),
    @Image NVARCHAR(255),
    @Quantity INT,
    @Price DECIMAL(10, 2),
    @TotalAmount DECIMAL(10, 2),
    @CouponDiscount DECIMAL(10, 2),
    @CouponCode NVARCHAR(50)
AS
BEGIN
    SET NOCOUNT ON;

    -- Check if the same product exists in the cart for the user
    IF EXISTS (
        SELECT 1
        FROM cart
        WHERE UserID = @UserID AND ProductID = @ProductID
    )
    BEGIN
        -- Update existing item in the cart
        UPDATE cart
        SET Quantity = Quantity + @Quantity,
            TotalAmount = TotalAmount + @TotalAmount
        WHERE UserID = @UserID AND ProductID = @ProductID;
    END
    ELSE
    BEGIN
        -- Insert new item into the cart
        INSERT INTO cart (UserID, ProductID, ProductName, Image, Quantity, Price, TotalAmount, CouponDiscount, CouponCode)
        VALUES (@UserID, @ProductID, @ProductName, @Image, @Quantity, @Price, @TotalAmount, @CouponDiscount, @CouponCode);
    END

    SELECT @@ROWCOUNT AS RowsAffected; -- Return number of rows affected (0 or 1)
END
GO
CREATE PROCEDURE ConfirmCart
    @CartID INT
AS
BEGIN
    UPDATE Cart
    SET IsConfirmed = 1
    WHERE CartID = @CartID;
END;
GO
ALTER TABLE Orders
ADD CouponAmount DECIMAL(10, 2) DEFAULT 0
GO
CREATE PROCEDURE ConfirmOrder
    @UserID INT
AS
BEGIN
    DECLARE @TotalAmount DECIMAL(10, 2) = 0;
    DECLARE @DiscountAmount DECIMAL(10, 2) = 0;

    -- Sepet toplam tutarýný hesapla
    SELECT @TotalAmount = SUM(TotalAmount)
    FROM Cart
    WHERE UserID = @UserID;

    -- Sipariþi oluþtur
    INSERT INTO Orders (UserID, TotalAmount, DiscountAmount)
    VALUES (@UserID, @TotalAmount, @DiscountAmount);

    -- Sepeti boþalt
    DELETE FROM Cart
    WHERE UserID = @UserID;

    PRINT 'Sipariþiniz baþarýyla oluþturuldu!';
END;
GO
CREATE PROCEDURE ApplyCoupon
    @UserID INT,
    @CouponCode NVARCHAR(50)
AS
BEGIN
    DECLARE @CurrentDate DATETIME = GETDATE();
    DECLARE @Discount DECIMAL(5, 2);

    -- Kuponun geçerliliðini kontrol et
    SELECT @Discount = Discount
    FROM Coupons
    WHERE CouponCode = @CouponCode
      AND UserID = @UserID
      AND ExpirationDate > @CurrentDate;

    IF @Discount IS NULL
    BEGIN
        PRINT 'Geçersiz kupon kodu veya süresi dolmuþ.';
        RETURN;
    END

    -- Ýndirim miktarýný Orders tablosuna ekleyelim
    UPDATE Orders
    SET DiscountAmount = @Discount
    WHERE UserID = @UserID
      AND DiscountAmount = 0; -- Sadece daha önce indirim eklenmemiþ sipariþlere uygula

    PRINT 'Kupon kodu uygulandý. Ýndirim: ' + CAST(@Discount AS NVARCHAR(10)) + ' TL';
END;
GO
CREATE PROCEDURE GetCartItems
    @UserID INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT CartID, ProductID, ProductName, Image, Quantity, Price, TotalAmount, CouponDiscount, IsConfirmed, CreatedAt
    FROM Cart
    WHERE UserID = @UserID AND IsConfirmed = 0;
END;
GO

CREATE PROCEDURE RemoveFromCart
    @CartItemID INT
AS
BEGIN
    SET NOCOUNT ON;

    DELETE FROM Cart
    WHERE CartID = @CartItemID;
END;
GO
CREATE TRIGGER trg_UpdateDiscount
ON Orders
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @UserID INT, @OrderID INT, @OrderCount INT;
    DECLARE @Discount DECIMAL(5, 2) = 0;

    SELECT @UserID = INSERTED.UserID, @OrderID = INSERTED.OrderID
    FROM INSERTED;

    SELECT @OrderCount = COUNT(*)
    FROM Orders
    WHERE UserID = @UserID;

    IF @OrderCount IN (1, 3, 5)
    BEGIN
        IF @OrderCount = 1
            SET @Discount = 30.00;
        ELSE IF @OrderCount = 3
            SET @Discount = 20.00;
        ELSE IF @OrderCount = 5
            SET @Discount = 10.00;

        UPDATE Orders
        SET DiscountAmount = @Discount, TotalAmount = TotalAmount - @Discount
        WHERE OrderID = @OrderID;
    END
END;
GO

SET IDENTITY_INSERT Coupons ON;

INSERT INTO Coupons (CouponID, CouponCode, CouponName, Discount, ExpirationDate, StartDate, EndDate)
VALUES 
    (1, 'KAHU', '004', 40.00, '2024-12-31', '2024-01-01', '2024-12-31'),
    (2, 'KAHUINDIRIM', '005', 50.00, '2024-12-31', '2024-01-01', '2024-12-31'),
    (3, 'RARU', '006', 65.00, '2024-12-31', '2024-01-01', '2024-12-31');

SET IDENTITY_INSERT Coupons OFF;
GO

CREATE TRIGGER trg_OrderDiscountUpdate
ON Orders
AFTER INSERT
AS
BEGIN
    DECLARE @OrderID INT;
    DECLARE @Discount DECIMAL(10, 2);

    -- Yeni eklenen sipariþin ID'sini al
    SELECT @OrderID = OrderID FROM inserted;

    -- Yeni eklenen sipariþin kupon indirim miktarýný al
    SELECT @Discount = DiscountAmount FROM inserted;

    -- Güncelleme iþlemi: Sipariþ tablosundaki DiscountAmount sütununu güncelle
    UPDATE Orders
    SET DiscountAmount = DiscountAmount + @Discount
    WHERE OrderID = @OrderID;
END;
GO

CREATE TRIGGER trg_UpdateCouponAmountOnCartUpdate
ON Cart
AFTER INSERT, UPDATE
AS
BEGIN
    DECLARE @UserID INT;
    DECLARE @CouponCode NVARCHAR(50);
    DECLARE @TotalAmount DECIMAL(10, 2);
    DECLARE @CouponAmount DECIMAL(10, 2);

    -- Yeni eklenen veya güncellenen kart kaydýnýn bilgilerini al
    SELECT @UserID = UserID FROM inserted;

    -- Eðer kupon kodu girildiyse ve kullanýcý bu iþlemi yaptýysa
    IF EXISTS (SELECT 1 FROM inserted WHERE CouponCode IS NOT NULL AND UserID = @UserID)
    BEGIN
        -- Kupon kodunu al
        SELECT @CouponCode = CouponCode FROM inserted;

        -- Toplam tutarý al
        SELECT @TotalAmount = SUM(Price * Quantity)
        FROM Cart
        WHERE UserID = @UserID;

        -- Sepette uygulanan indirimi al
        SELECT @CouponAmount = ISNULL(SUM(CouponDiscount), 0)
        FROM Cart
        WHERE UserID = @UserID;

        -- Orders tablosunda ilgili sipariþi bul ve güncelle
        UPDATE Orders
        SET 
            CouponAmount = @CouponAmount,
            TotalAmount = @TotalAmount - @CouponAmount -- TotalAmount'dan CouponAmount düþülerek güncellenir
        WHERE OrderID IN (
            SELECT DISTINCT OrderID FROM inserted WHERE UserID = @UserID
        );
    END;
END;

DROP TRIGGER trg_UpdateCouponAmountOnCartUpdate;
go

DROP TRIGGER trg_UpdateCouponAmountOnCartUpdate;
GO
CREATE TRIGGER trg_UpdateCouponAmountOnCartUpdate
ON Orders
AFTER INSERT, UPDATE
AS
BEGIN
    DECLARE @OrderID INT;
    DECLARE @UserID INT;
    DECLARE @CouponDiscount DECIMAL(10, 2);

    -- Yeni eklenen veya güncellenen sipariþin bilgilerini al
    SELECT @OrderID = OrderID, @UserID = UserID
    FROM inserted;

    -- Sepette uygulanan toplam kupon indirimini al
    SELECT @CouponDiscount = ISNULL(SUM(CouponDiscount), 0)
    FROM Cart
    WHERE UserID = @UserID AND IsConfirmed = 1; -- Onaylanmýþ sepetleri kontrol et

    -- Orders tablosunda ilgili sipariþi güncelle
    UPDATE Orders
    SET CouponAmount = @CouponDiscount,
        TotalAmount = TotalAmount - @CouponDiscount
    WHERE OrderID = @OrderID;
END;

GO
CREATE TRIGGER trg_UpdateCouponAmountOnCartUpdate
ON Orders
AFTER INSERT, UPDATE
AS
BEGIN
    DECLARE @OrderID INT;
    DECLARE @UserID INT;
    DECLARE @CouponDiscount DECIMAL(10, 2);

    -- Yeni eklenen veya güncellenen sipariþin bilgilerini al
    SELECT @OrderID = OrderID, @UserID = UserID
    FROM inserted;

    -- Sepette uygulanan toplam kupon indirimini al
    SELECT @CouponDiscount = ISNULL(SUM(CouponDiscount), 0)
    FROM Cart
    WHERE UserID = @UserID AND IsConfirmed = 1; -- Onaylanmýþ sepetleri kontrol et

    -- Orders tablosunda ilgili sipariþi güncelle
    UPDATE Orders
    SET CouponAmount = @CouponDiscount,
        TotalAmount = TotalAmount - @CouponDiscount
    WHERE OrderID = @OrderID;
END;
