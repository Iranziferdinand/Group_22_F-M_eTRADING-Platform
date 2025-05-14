<?php
require_once 'sms.php';
require_once 'db.php';
require_once 'util.php';

class Menu {
    protected $text;
    protected $sessionId;
    protected $phoneNumber;
    protected $conn;
    protected $smsService;

    function __construct($text, $sessionId, $phoneNumber, $conn) {
        $this->text = $text;
        $this->sessionId = $sessionId;
        $this->phoneNumber = $phoneNumber;
        $this->conn = $conn;
        $this->smsService = new SmsService();
    }

    public function mainMenuUnregistered() {
        echo "CON Welcome to F&I E_TRADING PLATFORM\n1. REGISTER \n2. CONTACT US";
    }

    public function menuRegister($textArray) {
    $level = count($textArray);

    if ($level == 1) {
        echo "CON Enter your full User name";
    } elseif ($level == 2) {
        echo "CON Enter your Email";
    } elseif ($level == 3) {
        $name = trim($textArray[1]);
        $email = trim($textArray[2]);

        // Check if already registered
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE phone_number = ?");
        $stmt->execute([$this->phoneNumber]);
        if ($stmt->rowCount() > 0) {
            echo "END This phone number is already registered.";
            return;
        }

        // Insert new user
        $stmt = $this->conn->prepare("INSERT INTO users (Names, email, phone_number) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $this->phoneNumber])) {
            error_log("=== Registration Successful ===");
            error_log("Name: " . $name);
            error_log("Email: " . $email);
            error_log("Phone: " . $this->phoneNumber);
            
            // Send registration confirmation SMS
            $message = "Welcome to F&I E-Trading Platform! Your registration was successful. You can now start trading.";
            error_log("Attempting to send registration SMS...");
            $smsResult = $this->smsService->sendSMS($message, $this->phoneNumber);
            error_log("Registration SMS Result: " . print_r($smsResult, true));
            
            // After registration, show main menu for registered users
            echo "CON Dear $name, you have successfully registered.\n";
            $this->mainMenuRegistered(); // Calls the registered menu
        } else {
            error_log("Registration failed for phone: " . $this->phoneNumber);
            echo "END Registration failed. Please try again.";
        }
    }
}

    
    public function mainMenuRegistered() {
        echo "CON Welcome back to F&I E_trading platform\n1. Buyer Services\n2. Sellers services";
    }


    public function menuSellerServices($textArray) {
    $level = count($textArray);

    if ($level == 1) {
        echo "CON Seller Services:\n1. Add Product\n2. View My Products\n3. View Bought Products";
    } elseif ($level == 2) {
        switch ($textArray[1]) {
            case "1":
                echo "CON Enter product name:";
                break;
            case "2":
                $this->viewMyProducts();
                break;
            case "3":
                $this->viewBoughtProducts();
                break;
            default:
                echo "END Invalid choice. Try again.";
        }
    } elseif ($level == 3 && $textArray[1] == "1") {
        echo "CON Enter product price:";
    } elseif ($level == 4 && $textArray[1] == "1") {
        echo "CON Enter quantity:";
    } elseif ($level == 5 && $textArray[1] == "1") {
        $name = trim($textArray[2]);
        $price = trim($textArray[3]);
        $quantity = trim($textArray[4]);

        $stmt = $this->conn->prepare("INSERT INTO products (name, price, quantity, seller_phone) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $price, $quantity, $this->phoneNumber])) {
            echo "END Product added successfully!";
        } else {
            echo "END Failed to add product.";
        }
    }
}

public function viewMyProducts() {
    $stmt = $this->conn->prepare("SELECT name, price, quantity FROM products WHERE seller_phone = ?");
    $stmt->execute([$this->phoneNumber]);
    $products = $stmt->fetchAll();

    if (count($products) == 0) {
        echo "END You have no products listed.";
        return;
    }

    $response = "END Your Products:\n";
    foreach ($products as $p) {
        $response .= "{$p['name']} - {$p['price']} - Qty: {$p['quantity']}\n";
    }

    echo $response;
}




public function viewBoughtProducts() {
    $stmt = $this->conn->prepare("
        SELECT p.name, pr.buyer_phone 
        FROM purchases pr
        JOIN products p ON pr.product_id = p.id
        WHERE p.seller_phone = ?
    ");
    $stmt->execute([$this->phoneNumber]);
    $records = $stmt->fetchAll();

    if (count($records) == 0) {
        echo "END No products bought yet.";
        return;
    }

    $response = "END Bought Products:\n";
    foreach ($records as $r) {
        $response .= "{$r['name']} - Buyer: {$r['buyer_phone']}\n";
    }

    echo $response;
}








public function menuBuyerServices($textArray) {
    $level = count($textArray);

    if ($level == 1) {
        echo "CON Buyer Services:\n1. View All Products\n2. Buy Product\n3. View My Purchases";
    } elseif ($level == 2) {
        switch ($textArray[1]) {
            case "1":
                $stmt = $this->conn->prepare("SELECT id, name, price, quantity FROM products WHERE quantity > 0");
                $stmt->execute();
                $products = $stmt->fetchAll();

                if (count($products) == 0) {
                    echo "END No products available.";
                } else {
                    $response = "CON Products:\n";
                    foreach ($products as $index => $product) {
                        $response .= ($index+1) . ". " . $product['name'] . " @ " . $product['price'] . " RWF\n";
                    }
                    echo $response;
                }
                break;

            case "2":
                echo "CON Enter Product ID to Buy:";
                break;

            case "3":
                $stmt = $this->conn->prepare("SELECT p.name, p.price, b.created_at 
                                              FROM purchases b 
                                              JOIN products p ON b.product_id = p.id 
                                              WHERE b.buyer_phone = ?");
                $stmt->execute([$this->phoneNumber]);
                $purchases = $stmt->fetchAll();

                if (count($purchases) == 0) {
                    echo "END You have not purchased anything yet.";
                } else {
                    $response = "END Your Purchases:\n";
                    foreach ($purchases as $p) {
                        $response .= $p['name'] . " @ " . $p['price'] . " RWF\n";
                    }
                    echo $response;
                }
                break;

            default:
                echo "END Invalid option.";
                break;
        }

    } elseif ($level == 3 && $textArray[1] == "2") {
        // Handle Buy Product
        $productId = (int)$textArray[2];

        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ? AND quantity > 0");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            echo "END Invalid Product ID or out of stock.";
        } else {
            // Save purchase
            $insert = $this->conn->prepare("INSERT INTO purchases (buyer_phone, product_id) VALUES (?, ?)");
            if ($insert->execute([$this->phoneNumber, $productId])) {
                // Reduce quantity
                $update = $this->conn->prepare("UPDATE products SET quantity = quantity - 1 WHERE id = ?");
                $update->execute([$productId]);

                // Send purchase confirmation SMS to buyer
                $buyerMessage = "Thank you for your purchase! You have successfully bought {$product['name']} for {$product['price']} RWF.";
                $this->smsService->sendSMS($buyerMessage, $this->phoneNumber);

                // Send notification SMS to seller
                $sellerMessage = "Your product {$product['name']} has been purchased by {$this->phoneNumber} for {$product['price']} RWF.";
                $this->smsService->sendSMS($sellerMessage, $product['seller_phone']);

                echo "END You have successfully bought " . $product['name'] . ".";
            } else {
                echo "END Purchase failed. Try again.";
            }
        }
    }
}




}

    ?>