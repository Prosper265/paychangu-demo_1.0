<?php
// process-payment.php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// PayChangu API Configuration
// Use your keys from your PayChangu account by navigating to settings/api&webhook 
$secretKey = 'sec-test-Gv8qFhByQlrNHpZFUyltMTxTdkeEvnww';
$publicKey = 'pub-test-gVNpmcDiG8409qnbNR1nAMvs6Ut52hDU';

// Generate unique transaction reference
$tx_ref = 'TXN-' . time() . '-' . rand(1000, 9999);

// Payment data
$paymentData = [
    'public_key' => $publicKey,
    'tx_ref' => $tx_ref,
    'amount' => 1000,
    'currency' => 'MWK',
    'email' => 'prosblk2@gmail.com',
    'first_name' => 'Prosper',
    'last_name' => 'Black',
    'title' => 'iPhone 15 Pro Purchase',
    'description' => 'Payment for iPhone 15 Pro - Online Store',
    'callback_url' => 'https://shakingly-chalazian-marcelo.ngrok-free.dev/callback.php',
    'return_url' => 'https://shakingly-chalazian-marcelo.ngrok-free.dev/failed.php',
    'meta' => [
        'order_id' => '12345',
        'customer_id' => '67890',
        'source' => 'web_checkout'
    ]
];

// Debug: Log the request data
echo "<h3>Request Data:</h3>";
echo "<pre>" . print_r($paymentData, true) . "</pre>";

// Initialize cURL
$ch = curl_init('https://api.paychangu.com/payment');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($paymentData),
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . $secretKey,
        'Accept: application/json',
        'Content-Type: application/json'
    ),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'YourApp/1.0'
]);



$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($error) {
    exit("❌ cURL Error: $error");
}

$result = json_decode($response, true);

if ($status !== 200 && $status !== 201) {
    echo "<h3>❌ Request failed – HTTP $status</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . "</pre>";
    exit;
}

if (isset($result['data']['checkout_url'])) {
    header("Location: " . $result['data']['checkout_url']);
    exit;
}

exit("❌ Unexpected response structure: " . htmlspecialchars($response));