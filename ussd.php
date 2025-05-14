<?php
session_start();

if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 1000;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$logFile = 'ussd_log.txt';
$logMessage = date('Y-m-d H:i:s') . " - Request: " . print_r($_POST, true) . "\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

$sessionId = $_POST['sessionId'] ?? '';
$serviceCode = $_POST['serviceCode'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text = $_POST['text'] ?? '';

function showMainMenu() {
    return "CON Welcome to My USSD App. Please select an option:\n1. Check Balance\n2. Buy Airtime\n3. Exit";
}

function checkBalance() {
    $balance = $_SESSION['balance'];
    return "CON Your current balance is: $balance\n1. Back to Menu";
}

if ($text == '') {
    $response = showMainMenu();
} else {
    $textArray = explode('*', $text);
    $level = count($textArray);

    switch ($textArray[0]) {
        case '1':
            if ($level == 1) {
                // User selected "Check Balance"
                $response = checkBalance();
            } elseif ($level == 2 && $textArray[1] == '1') {
                // Back to main menu after balance check
                $response = showMainMenu();
            } else {
                $response = "CON Invalid option.\n1. Back to Menu";
            }
            break;

        case '2':
            if ($level == 1) {
                $response = "CON Enter amount to buy airtime:\n1. Back to Menu";
            } elseif ($level == 2) {
                if ($textArray[1] == '1') {
                    $response = showMainMenu();
                } else {
                    $amount = $textArray[1];
                    if (is_numeric($amount) && $amount > 0) {
                        if ($amount <= $_SESSION['balance']) {
                            $_SESSION['balance'] -= $amount;
                            $newBalance = $_SESSION['balance'];
                            $response = "CON Airtime purchase successful!\nAmount: $amount\nNew Balance: $newBalance\n1. Back to Menu";
                        } else {
                            $response = "CON Insufficient balance!\nYour balance: " . $_SESSION['balance'] . "\n1. Back to Menu";
                        }
                    } else {
                        $response = "CON Invalid amount! Enter a valid number.\n1. Back to Menu";
                    }
                }
            } elseif ($level == 3 && $textArray[2] == '1') {
                $response = showMainMenu();
            } else {
                $response = "CON Invalid input.\n1. Back to Menu";
            }
            break;

        case '3':
            $response = "END Thank you for using My USSD App. Goodbye!";
            break;

        default:
            $response = "CON Invalid option selected.\n1. Back to Menu";
            break;
    }
}

$logMessage = date('Y-m-d H:i:s') . " - Response: " . $response . "\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

header('Content-type: text/plain');
echo $response;
?>